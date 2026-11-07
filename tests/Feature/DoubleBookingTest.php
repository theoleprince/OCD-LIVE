<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Exceptions\SlotUnavailableException;
use App\Livewire\BookingWizard;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\BookingWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class DoubleBookingTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();
    }

    public function test_the_same_slot_cannot_be_booked_twice(): void
    {
        $service = app(BookingService::class);
        $service->create($this->bookingData());

        $this->expectException(SlotUnavailableException::class);
        $service->create($this->bookingData(['customer' => ['email' => 'autre@example.com']]));
    }

    public function test_overlapping_slot_with_another_package_is_refused(): void
    {
        $service = app(BookingService::class);
        $service->create($this->bookingData(['starts_at' => '2026-12-01 14:00'])); // 14:00 – 15:30

        $business = \App\Models\Package::where('slug', 'business-live')->first(); // 4 h
        try {
            $service->create($this->bookingData(['package_id' => $business->id, 'starts_at' => '2026-12-01 10:00'])); // 10:00 – 14:00 + marge
            $this->fail('Un chevauchement avec la marge de préparation aurait dû être refusé.');
        } catch (SlotUnavailableException) {
            $this->assertSame(1, Booking::count());
        }
    }

    public function test_cancelled_booking_frees_the_slot(): void
    {
        $service = app(BookingService::class);
        $first = $service->create($this->bookingData());
        app(BookingWorkflow::class)->cancel($first, 'test');

        $second = $service->create($this->bookingData(['customer' => ['email' => 'autre@example.com']]));
        $this->assertSame(BookingStatus::PendingPayment, $second->status);
    }

    public function test_wizard_reports_a_slot_taken_meanwhile(): void
    {
        $wizard = Livewire::test(BookingWizard::class)
            ->call('selectMode', 'ONSITE')
            ->call('selectPackage', $this->starter()->id)
            ->call('selectDate', '2026-12-01')
            ->call('selectTime', '10:00')
            ->set('lastName', 'Ama')->set('firstName', 'Koffi')->set('country', 'Togo')->set('city', 'Lomé')
            ->set('phone', '+228 90 00 00 00')->set('whatsapp', '+228 90 00 00 00')->set('email', 'ama@example.com')
            ->call('next')
            ->set('businessName', 'Atelier')->set('sector', 'Mode & textile')
            ->set('description', 'Création de vêtements en pagne.')->set('offerNature', 'Vêtements')
            ->set('businessPhone', '+228 90 00 00 01')->set('businessAddress', 'Bè, Lomé')
            ->call('next')
            ->set('acceptTerms', true);

        // Un autre client prend le créneau entre-temps.
        app(BookingService::class)->create($this->bookingData());

        $wizard->call('submit')->assertHasErrors('time')->assertSet('step', 4)->assertSet('time', null);
        $this->assertSame(1, Booking::count());
    }
}
