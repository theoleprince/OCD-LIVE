<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();
    }

    public function test_booking_cannot_be_confirmed_without_a_received_payment(): void
    {
        $booking = app(BookingService::class)->create($this->bookingData());

        $this->actingAs($this->admin())->post(route('admin.bookings.confirm', $booking))->assertSessionHasErrors('status');
        $this->assertSame(BookingStatus::PendingPayment, $booking->fresh()->status);
    }

    public function test_recording_a_payment_moves_booking_to_review_then_admin_confirms(): void
    {
        $admin = $this->admin();
        $booking = app(BookingService::class)->create($this->bookingData());

        // La case de certification est obligatoire.
        $this->actingAs($admin)->post(route('admin.bookings.payment', $booking), ['method' => 'mobile_money'])->assertSessionHasErrors('confirm_received');
        $this->assertFalse($booking->fresh()->isPaid());

        $this->actingAs($admin)->post(route('admin.bookings.payment', $booking), [
            'method' => 'mobile_money', 'reference' => 'TMONEY-889', 'confirm_received' => 1,
        ])->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::PendingReview, $booking->status);
        $payment = $booking->payments()->first();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame($admin->id, $payment->recorded_by);
        $this->assertNotNull($payment->paid_at);

        $this->actingAs($admin)->post(route('admin.bookings.confirm', $booking))->assertSessionHasNoErrors();
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);

        $this->actingAs($admin)->post(route('admin.bookings.complete', $booking));
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_failed_payment_keeps_booking_pending_and_opens_a_new_attempt(): void
    {
        $booking = app(BookingService::class)->create($this->bookingData());

        $this->actingAs($this->admin())->post(route('admin.bookings.payment-failed', $booking), ['note' => 'Solde insuffisant']);

        $this->assertSame(BookingStatus::PendingPayment, $booking->fresh()->status);
        $this->assertSame(1, $booking->payments()->where('status', 'FAILED')->count());
        $this->assertSame(1, $booking->payments()->where('status', 'PENDING')->count());
    }

    public function test_dashboard_revenue_counts_only_paid_non_demo_payments(): void
    {
        $admin = $this->admin();
        $service = app(BookingService::class);
        $paid = $service->create($this->bookingData());
        $service->create($this->bookingData(['starts_at' => '2026-12-02 10:00']));
        $demo = $service->create($this->bookingData(['starts_at' => '2026-12-03 10:00', 'is_demo' => true]));

        $this->actingAs($admin)->post(route('admin.bookings.payment', $paid), ['method' => 'cash', 'confirm_received' => 1]);
        $this->actingAs($admin)->post(route('admin.bookings.payment', $demo), ['method' => 'cash', 'confirm_received' => 1]);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSeeInOrder(['Revenus réellement encaissés', '15 000 FCFA']);
        $this->assertSame(15000, (int) \App\Models\Payment::where('status', 'PAID')->whereHas('booking', fn ($q) => $q->where('is_demo', false))->sum('amount'));
    }

    public function test_expired_unpaid_bookings_are_released(): void
    {
        $booking = app(BookingService::class)->create($this->bookingData());
        $this->atLomeTime('2026-11-25 10:00'); // > 72 h plus tard

        $this->artisan('ocd:expire-unpaid')->assertSuccessful();

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_payment_driver_returns_instructions_and_never_marks_paid(): void
    {
        $booking = app(BookingService::class)->create($this->bookingData());
        $initiation = app(\App\Payments\PaymentManager::class)->driver()->initiate($booking->payments()->first());

        $this->assertNull($initiation->redirectUrl);
        $this->assertNotEmpty($initiation->instructions);
        $this->assertFalse($booking->fresh()->isPaid());
    }
}
