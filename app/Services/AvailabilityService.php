<?php

namespace App\Services;

use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\SlotBlock;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Calcule les journées et créneaux réservables à partir :
 *  - des réglages hebdomadaires (settings),
 *  - des réglages par date (availability_slots),
 *  - des blocages ponctuels (slot_blocks),
 *  - des réservations existantes + marge de préparation.
 */
class AvailabilityService
{
    public const DAY_AVAILABLE = 'available';
    public const DAY_FULL = 'full';
    public const DAY_CLOSED = 'closed';
    public const DAY_PAST = 'past';

    public function timezone(): string
    {
        return config('ocd.timezone');
    }

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone());
    }

    public function bufferMinutes(): int
    {
        return max(0, (int) setting('buffer_minutes'));
    }

    public function stepMinutes(): int
    {
        return max(15, (int) setting('slot_step_minutes'));
    }

    public function firstBookableDate(): CarbonImmutable
    {
        $start = CarbonImmutable::parse(setting('booking_start_date'), $this->timezone())->startOfDay();
        $today = $this->now()->startOfDay();

        return $start->greaterThan($today) ? $start : $today;
    }

    public function lastBookableDate(): CarbonImmutable
    {
        return $this->firstBookableDate()->addDays((int) setting('booking_horizon_days'));
    }

    /** Réglages effectifs d'une journée : ouverte ? horaires ? */
    public function dayConfig(CarbonImmutable $date, ?AvailabilitySlot $override = null): array
    {
        $weekdays = array_map('intval', array_filter(explode(',', (string) setting('open_weekdays'))));
        $open = in_array($date->dayOfWeekIso, $weekdays, true);
        $opens = (string) setting('opening_time');
        $closes = (string) setting('closing_time');
        $note = null;

        if ($override) {
            if ($override->is_open !== null) {
                $open = $override->is_open;
            }
            $opens = $override->opens_at ? substr($override->opens_at, 0, 5) : $opens;
            $closes = $override->closes_at ? substr($override->closes_at, 0, 5) : $closes;
            $note = $override->note;
        }

        return [
            'open' => $open,
            'opens' => $opens,
            'closes' => $closes,
            'note' => $note,
            'overridden' => (bool) $override,
        ];
    }

    /**
     * Créneaux d'une journée pour une durée donnée.
     *
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable, available: bool}>
     */
    public function slotsForDate(CarbonImmutable $date, int $durationMinutes, ?int $ignoreBookingId = null, ?array $preloaded = null): array
    {
        $date = $date->setTimezone($this->timezone())->startOfDay();

        if ($date->lt($this->firstBookableDate()) || $date->gt($this->lastBookableDate())) {
            return [];
        }

        $override = $preloaded
            ? ($preloaded['overrides'][$date->toDateString()] ?? null)
            : AvailabilitySlot::query()->whereDate('date', $date)->first();

        $config = $this->dayConfig($date, $override);

        if (! $config['open']) {
            return [];
        }

        $dayStart = $date->setTimeFromTimeString($config['opens']);
        $dayEnd = $date->setTimeFromTimeString($config['closes']);

        $bookings = $preloaded
            ? $preloaded['bookings']->filter(fn ($b) => $b->starts_at->lt($dayEnd->addDay()) && $b->ends_at->gt($dayStart->subDay()))
            : $this->bookingsBetween($dayStart->subDay(), $dayEnd->addDay(), $ignoreBookingId);

        $blocks = $preloaded
            ? $preloaded['blocks']
            : $this->blocksBetween($dayStart, $dayEnd);

        $minStart = $this->now()->addHours((int) setting('min_notice_hours'));
        $slots = [];

        for ($start = $dayStart; $start->addMinutes($durationMinutes)->lte($dayEnd); $start = $start->addMinutes($this->stepMinutes())) {
            $end = $start->addMinutes($durationMinutes);
            $slots[] = [
                'start' => $start,
                'end' => $end,
                'available' => $start->gte($minStart) && ! $this->conflicts($start, $end, $bookings, $blocks),
            ];
        }

        return $slots;
    }

    /** Vérification d'un créneau précis (utilisée dans la transaction de réservation). */
    public function isSlotAvailable(CarbonImmutable $start, int $durationMinutes, ?int $ignoreBookingId = null): bool
    {
        $start = $start->setTimezone($this->timezone());

        foreach ($this->slotsForDate($start->startOfDay(), $durationMinutes, $ignoreBookingId) as $slot) {
            if ($slot['start']->equalTo($start)) {
                return $slot['available'];
            }
        }

        return false;
    }

    /**
     * Calendrier mensuel : semaines (lundi → dimanche) avec statut de chaque jour.
     */
    public function monthCalendar(int $year, int $month, int $durationMinutes): array
    {
        $first = CarbonImmutable::create($year, $month, 1, 0, 0, 0, $this->timezone());
        $last = $first->endOfMonth()->startOfDay();
        $gridStart = $first->startOfWeek(CarbonImmutable::MONDAY);
        $gridEnd = $last->endOfWeek(CarbonImmutable::SUNDAY)->startOfDay();

        $preloaded = [
            'overrides' => AvailabilitySlot::query()
                ->whereDate('date', '>=', $gridStart)->whereDate('date', '<=', $gridEnd)
                ->get()->keyBy(fn ($o) => $o->date->toDateString())->all(),
            'bookings' => $this->bookingsBetween($gridStart->subDay(), $gridEnd->addDays(2)),
            'blocks' => $this->blocksBetween($gridStart, $gridEnd->addDay()),
        ];

        $weeks = [];
        for ($day = $gridStart; $day->lte($gridEnd); $day = $day->addDay()) {
            $status = $this->dayStatus($day, $durationMinutes, $preloaded);
            $weeks[intdiv($gridStart->diffInDays($day), 7)][] = [
                'date' => $day->toDateString(),
                'day' => $day->day,
                'in_month' => $day->month === $month,
                'status' => $status,
            ];
        }

        return $weeks;
    }

    public function dayStatus(CarbonImmutable $day, int $durationMinutes, ?array $preloaded = null): string
    {
        if ($day->lt($this->firstBookableDate()) || $day->gt($this->lastBookableDate())) {
            return $day->lt($this->now()->startOfDay()) ? self::DAY_PAST : self::DAY_CLOSED;
        }

        $override = $preloaded ? ($preloaded['overrides'][$day->toDateString()] ?? null) : AvailabilitySlot::query()->whereDate('date', $day)->first();

        if (! $this->dayConfig($day, $override)['open']) {
            return self::DAY_CLOSED;
        }

        $slots = $this->slotsForDate($day, $durationMinutes, null, $preloaded);

        return collect($slots)->contains('available', true) ? self::DAY_AVAILABLE : self::DAY_FULL;
    }

    private function conflicts(CarbonImmutable $start, CarbonImmutable $end, Collection $bookings, Collection $blocks): bool
    {
        $buffer = $this->bufferMinutes();

        foreach ($bookings as $booking) {
            // La marge de préparation s'applique avant et après chaque émission.
            if ($start->lt($booking->ends_at->copy()->addMinutes($buffer)) && $end->gt($booking->starts_at->copy()->subMinutes($buffer))) {
                return true;
            }
        }

        foreach ($blocks as $block) {
            if ($start->lt($block->ends_at) && $end->gt($block->starts_at)) {
                return true;
            }
        }

        return false;
    }

    private function bookingsBetween(CarbonImmutable $from, CarbonImmutable $to, ?int $ignoreBookingId = null): Collection
    {
        return Booking::query()
            ->blocking()
            ->overlapping($from, $to)
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->get(['id', 'starts_at', 'ends_at']);
    }

    private function blocksBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return SlotBlock::query()
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->get(['id', 'starts_at', 'ends_at']);
    }
}
