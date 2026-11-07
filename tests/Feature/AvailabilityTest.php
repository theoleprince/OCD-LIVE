<?php

namespace Tests\Feature;

use App\Models\AvailabilitySlot;
use App\Models\Setting;
use App\Models\SlotBlock;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    private AvailabilityService $availability;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();
        $this->availability = app(AvailabilityService::class);
    }

    private function day(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date, 'Africa/Lome');
    }

    public function test_slots_fit_the_package_duration_inside_opening_hours(): void
    {
        // 09:00 → 21:00, pas de 30 min, 90 min : dernier départ 19:30.
        $slots = $this->availability->slotsForDate($this->day('2026-12-01'), 90);

        $this->assertSame('09:00', $slots[0]['start']->format('H:i'));
        $this->assertSame('19:30', end($slots)['start']->format('H:i'));
        $this->assertSame('21:00', end($slots)['end']->format('H:i'));

        // 6 h : dernier départ 15:00.
        $long = $this->availability->slotsForDate($this->day('2026-12-01'), 360);
        $this->assertSame('15:00', end($long)['start']->format('H:i'));
    }

    public function test_no_slots_before_launch_date_or_on_closed_days(): void
    {
        $this->assertSame([], $this->availability->slotsForDate($this->day('2026-11-28'), 90));
        // 6 décembre 2026 = dimanche, fermé par défaut.
        $this->assertSame([], $this->availability->slotsForDate($this->day('2026-12-06'), 90));
        $this->assertSame(AvailabilityService::DAY_CLOSED, $this->availability->dayStatus($this->day('2026-12-06'), 90));
    }

    public function test_admin_can_open_a_closed_day_and_close_an_open_one(): void
    {
        AvailabilitySlot::create(['date' => '2026-12-06', 'is_open' => true, 'opens_at' => '14:00', 'closes_at' => '18:00']);
        AvailabilitySlot::create(['date' => '2026-12-07', 'is_open' => false]);

        $sunday = $this->availability->slotsForDate($this->day('2026-12-06'), 90);
        $this->assertSame('14:00', $sunday[0]['start']->format('H:i'));
        $this->assertSame('16:30', end($sunday)['start']->format('H:i'));
        $this->assertSame([], $this->availability->slotsForDate($this->day('2026-12-07'), 90));
    }

    public function test_existing_booking_and_preparation_buffer_make_slots_unavailable(): void
    {
        app(BookingService::class)->create($this->bookingData(['starts_at' => '2026-12-01 10:00'])); // 10:00 – 11:30

        $slots = collect($this->availability->slotsForDate($this->day('2026-12-01'), 90))
            ->mapWithKeys(fn ($s) => [$s['start']->format('H:i') => $s['available']]);

        $this->assertTrue($slots['08:00'] ?? true); // hors horaires, absent
        $this->assertFalse($slots['09:00']);          // finit 10:30 > 09:30 (début − marge)
        $this->assertFalse($slots['11:30']);          // dans la marge de 30 min
        $this->assertTrue($slots['12:00']);           // fin + 30 min de marge
    }

    public function test_blocked_period_removes_slots(): void
    {
        SlotBlock::create(['starts_at' => '2026-12-01 13:00', 'ends_at' => '2026-12-01 15:00', 'reason' => 'Maintenance']);

        $this->assertFalse($this->availability->isSlotAvailable(CarbonImmutable::parse('2026-12-01 12:00', 'Africa/Lome'), 90));
        $this->assertTrue($this->availability->isSlotAvailable(CarbonImmutable::parse('2026-12-01 15:00', 'Africa/Lome'), 90));
    }

    public function test_full_day_is_reported_as_complete(): void
    {
        Setting::put('opening_time', '09:00');
        Setting::put('closing_time', '10:30');
        app(BookingService::class)->create($this->bookingData(['starts_at' => '2026-12-01 09:00']));

        $this->assertSame(AvailabilityService::DAY_FULL, $this->availability->dayStatus($this->day('2026-12-01'), 90));

        $december = collect($this->availability->monthCalendar(2026, 12, 90))->flatten(1)->keyBy('date');
        $this->assertSame('full', $december['2026-12-01']['status']);
        $this->assertSame('available', $december['2026-12-02']['status']);
        $this->assertSame('closed', $december['2026-12-06']['status']);
    }

    public function test_minimum_notice_hides_imminent_slots(): void
    {
        $this->atLomeTime('2026-12-01 08:00');

        $this->assertFalse($this->availability->isSlotAvailable(CarbonImmutable::parse('2026-12-01 10:00', 'Africa/Lome'), 90));
        $this->assertTrue($this->availability->isSlotAvailable(CarbonImmutable::parse('2026-12-02 10:00', 'Africa/Lome'), 90));
    }
}
