<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\SlotBlock;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(private AvailabilityService $availability) {}

    public function index(Request $request): View
    {
        $tz = config('ocd.timezone');
        $month = $request->query('month');
        $first = $month && preg_match('/^\d{4}-\d{2}$/', $month)
            ? CarbonImmutable::createFromFormat('Y-m-d', $month.'-01', $tz)->startOfDay()
            : $this->availability->firstBookableDate()->startOfMonth();

        $gridStart = $first->startOfWeek(CarbonImmutable::MONDAY);
        $gridEnd = $first->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);

        $overrides = AvailabilitySlot::whereDate('date', '>=', $gridStart)->whereDate('date', '<=', $gridEnd)
            ->get()->keyBy(fn ($o) => $o->date->toDateString());
        $bookings = Booking::with(['customer:id,first_name,last_name', 'business:id,name', 'package:id,name'])
            ->blocking()
            ->whereDate('booking_date', '>=', $gridStart)->whereDate('booking_date', '<=', $gridEnd)
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn ($b) => $b->booking_date->toDateString());
        $blocks = SlotBlock::where('starts_at', '<', $gridEnd)->where('ends_at', '>', $gridStart)->orderBy('starts_at')->get()
            ->groupBy(fn ($b) => $b->starts_at->toDateString());

        $days = [];
        for ($d = $gridStart; $d->lte($gridEnd); $d = $d->addDay()) {
            $key = $d->toDateString();
            $days[] = [
                'date' => $d,
                'in_month' => $d->month === $first->month,
                'config' => $this->availability->dayConfig($d, $overrides[$key] ?? null),
                'bookings' => $bookings[$key] ?? collect(),
                'blocks' => $blocks[$key] ?? collect(),
            ];
        }

        $selected = $request->query('day') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->query('day'))
            ? collect($days)->first(fn ($d) => $d['date']->toDateString() === $request->query('day'))
            : null;

        return view('admin.calendar.index', [
            'first' => $first,
            'days' => $days,
            'selected' => $selected,
            'override' => $selected ? ($overrides[$selected['date']->toDateString()] ?? null) : null,
        ]);
    }

    public function updateDay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'state' => ['required', Rule::in(['default', 'open', 'closed'])],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i', 'after:opens_at'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $slot = AvailabilitySlot::whereDate('date', $data['date'])->first() ?? new AvailabilitySlot(['date' => $data['date']]);
        $slot->fill([
            'is_open' => ['default' => null, 'open' => true, 'closed' => false][$data['state']],
            'opens_at' => $data['opens_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'note' => $data['note'] ?? null,
        ])->save();

        AdminActivityLog::record('calendar.day_updated', $slot, $data);

        $existing = Booking::blocking()->whereDate('booking_date', $data['date'])->count();
        $message = 'Journée mise à jour.';
        if ($existing && $data['state'] === 'closed') {
            $message .= " Attention : $existing réservation(s) existent déjà ce jour-là et restent actives.";
        }

        return redirect()->route('admin.calendar', ['month' => substr($data['date'], 0, 7), 'day' => $data['date']])->with('status', $message);
    }

    public function storeBlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i', 'after:start'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $tz = config('ocd.timezone');
        $start = CarbonImmutable::parse($data['date'].' '.$data['start'], $tz);
        $end = CarbonImmutable::parse($data['date'].' '.$data['end'], $tz);

        $conflict = Booking::blocking()->overlapping($start, $end)->first();
        if ($conflict) {
            throw ValidationException::withMessages(['start' => 'Ce créneau chevauche la réservation '.$conflict->reference.'. Annulez-la ou choisissez un autre horaire.']);
        }

        $block = SlotBlock::create(['starts_at' => $start, 'ends_at' => $end, 'reason' => $data['reason'] ?? null, 'created_by' => $request->user()->id]);
        AdminActivityLog::record('calendar.block_created', $block, $data);

        return redirect()->route('admin.calendar', ['month' => $start->format('Y-m'), 'day' => $data['date']])->with('status', 'Créneau bloqué.');
    }

    public function destroyBlock(SlotBlock $block): RedirectResponse
    {
        $date = $block->starts_at;
        AdminActivityLog::record('calendar.block_deleted', $block, ['starts_at' => (string) $block->starts_at, 'ends_at' => (string) $block->ends_at]);
        $block->delete();

        return redirect()->route('admin.calendar', ['month' => $date->format('Y-m'), 'day' => $date->toDateString()])->with('status', 'Blocage supprimé.');
    }
}
