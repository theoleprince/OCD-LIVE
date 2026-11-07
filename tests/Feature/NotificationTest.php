<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingReceivedNotification;
use App\Notifications\NewBookingNotification;
use App\Services\BookingService;
use App\Services\BookingWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
    }

    public function test_new_booking_notifies_admins_and_acknowledges_customer(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $booking = app(BookingService::class)->create($this->bookingData());

        Notification::assertSentTo($admin, NewBookingNotification::class, function ($n, $channels) use ($booking) {
            $lines = $n->lines();

            return in_array('database', $channels) && in_array('mail', $channels)
                && $lines['Référence'] === $booking->reference
                && $lines['Client'] === 'Jean Dupont'
                && $lines['Pays'] === 'Cameroun'
                && $lines['Ville'] === 'Douala'
                && $lines['Package'] === 'Starter Live'
                && $lines['Montant'] === '15 000 FCFA'
                && $lines['Mode'] === 'Distance'
                && str_contains($lines['Heure'], '10h00 – 11h30')
                && str_contains($lines['Coordonnées commerciales'], 'Akwa, Douala');
        });
        Notification::assertSentTo(Customer::sole(), BookingReceivedNotification::class);
        Notification::assertNotSentTo(Customer::sole(), BookingConfirmedNotification::class);
    }

    public function test_notifications_appear_in_the_dashboard(): void
    {
        $admin = $this->admin();
        config(['mail.default' => 'array']);

        $booking = app(BookingService::class)->create($this->bookingData());

        $this->assertSame(1, $admin->unreadNotifications()->count());
        $this->actingAs($admin)->get('/admin/notifications')->assertOk()
            ->assertSee('Nouvelle réservation '.$booking->reference)
            ->assertSee('Cameroun');

        $id = $admin->notifications()->first()->id;
        $this->actingAs($admin)->post(route('admin.notifications.read', $id))->assertRedirect(route('admin.bookings.show', $booking));
        $this->assertSame(0, $admin->unreadNotifications()->count());
    }

    public function test_confirmation_email_is_sent_only_when_booking_is_confirmed(): void
    {
        Notification::fake();
        $this->actingAs($this->admin());
        $booking = app(BookingService::class)->create($this->bookingData());
        $workflow = app(BookingWorkflow::class);

        $workflow->recordPayment($booking, 'mobile_money', 'MM-123');
        Notification::assertNotSentTo($booking->customer, BookingConfirmedNotification::class);

        $workflow->confirm($booking->fresh());
        Notification::assertSentTo($booking->customer, BookingConfirmedNotification::class);
    }

    public function test_a_mail_failure_does_not_lose_the_booking(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1]);
        $this->admin();

        $booking = app(BookingService::class)->create($this->bookingData());

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'PENDING_PAYMENT']);
    }
}
