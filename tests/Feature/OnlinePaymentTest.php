<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\BookingPayment;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    private const API = 'https://sandbox-api.fedapay.com/v1';

    /** Statut renvoyé par la fausse API FedaPay lors de la relecture d'une transaction. */
    private string $remoteStatus = 'pending';

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();

        config([
            'payments.fedapay.secret_key' => 'sk_sandbox_test',
            'payments.fedapay.environment' => 'sandbox',
            'payments.fedapay.webhook_secret' => 'wh_secret_test',
        ]);

        Http::fake(function (Request $request) {
            $url = $request->url();

            return match (true) {
                $request->method() === 'POST' && $url === self::API.'/transactions' => Http::response(['v1/transaction' => ['id' => 4242, 'reference' => 'trx_ABC', 'status' => 'pending', 'amount' => $request['amount']]], 201),
                str_ends_with($url, '/transactions/4242/token') => Http::response(['token' => 'tok_123', 'url' => 'https://sandbox-process.fedapay.com/tok_123']),
                str_ends_with($url, '/transactions/moov_tg'), str_ends_with($url, '/transactions/togocel') => Http::response([['reference' => 'pi_1', 'status' => 'pending']]),
                $request->method() === 'GET' && str_ends_with($url, '/transactions/4242') => Http::response(['v1/transaction' => ['id' => 4242, 'reference' => 'trx_ABC', 'status' => $this->remoteStatus, 'amount' => Booking::first()->amount]]),
                default => Http::response(['message' => 'not faked'], 500),
            };
        });
    }

    private function booking(): Booking
    {
        return app(BookingService::class)->create($this->bookingData());
    }

    public function test_every_booking_gets_an_unguessable_payment_page(): void
    {
        $booking = $this->booking();

        $this->assertSame(40, strlen($booking->payment_token));
        $this->get(route('payment.show', $booking->payment_token))
            ->assertOk()
            ->assertSee($booking->reference)
            ->assertSee('Moov Money (Flooz)')
            ->assertSee('Mixx by Yas')
            ->assertSee('images/paiement/moov-money.jpg')
            ->assertSee('images/paiement/visa.svg')
            ->assertSee('noindex', false);

        $this->get('/paiement/'.str_repeat('x', 40))->assertNotFound();
    }

    public function test_mobile_money_sends_a_request_to_the_phone_and_waits_for_validation(): void
    {
        $booking = $this->booking();

        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->call('choose', 'moov_money')
            ->set('phone', '90 12 34 56')
            ->call('pay')
            ->assertHasNoErrors()
            ->assertSet('stage', 'waiting');

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/transactions/moov_tg')
            && $r['token'] === 'tok_123' && $r['phone_number']['number'] === '+22890123456' && $r['phone_number']['country'] === 'TG');
        Http::assertSent(fn (Request $r) => $r->url() === self::API.'/transactions' && $r['amount'] === $booking->amount && $r->hasHeader('Authorization', 'Bearer sk_sandbox_test'));

        $payment = $booking->payments()->first();
        $this->assertSame('fedapay', $payment->provider);
        $this->assertSame('moov_money', $payment->method);
        $this->assertSame(4242, $payment->meta['fedapay_transaction_id']);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
    }

    public function test_payment_is_recorded_only_once_fedapay_confirms_it(): void
    {
        $booking = $this->booking();
        $component = Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->call('choose', 'mixx')->set('phone', '+228 70 00 00 01')->call('pay')->assertSet('stage', 'waiting');

        $component->call('refreshStatus');
        $this->assertFalse($booking->fresh()->isPaid());

        $this->remoteStatus = 'approved';
        $component->call('refreshStatus')->assertSee('Paiement reçu');

        $booking->refresh();
        $this->assertTrue($booking->isPaid());
        $this->assertSame(BookingStatus::PendingReview, $booking->status);
        $this->assertSame('trx_ABC', $booking->payments()->first()->provider_reference);
        $this->assertTrue(AdminActivityLog::where('action', 'payment.online.paid')->exists());
    }

    public function test_declined_payment_lets_the_customer_try_again(): void
    {
        $booking = $this->booking();
        $component = Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->call('choose', 'moov_money')->set('phone', '90123456')->call('pay');

        $this->remoteStatus = 'declined';
        $component->call('refreshStatus')->assertSet('stage', 'choose')->assertHasErrors('phone');

        $this->assertSame(BookingStatus::PendingPayment, $booking->fresh()->status);
        $this->assertSame(1, $booking->payments()->where('status', PaymentStatus::Failed->value)->count());
        $this->assertSame(1, $booking->payments()->where('status', PaymentStatus::Pending->value)->count());
    }

    public function test_invalid_phone_number_is_rejected_before_calling_fedapay(): void
    {
        $booking = $this->booking();

        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->call('choose', 'moov_money')->set('phone', '12')->call('pay')
            ->assertHasErrors('phone')->assertSet('stage', 'choose');

        Http::assertNothingSent();
    }

    public function test_card_payment_redirects_to_the_fedapay_secure_page(): void
    {
        $booking = $this->booking();

        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->call('choose', 'card')->call('pay')
            ->assertRedirect('https://sandbox-process.fedapay.com/tok_123');

        Http::assertSent(fn (Request $r) => $r->url() === self::API.'/transactions'
            && $r['callback_url'] === route('payment.return', $booking->payment_token));
    }

    public function test_return_from_fedapay_rereads_the_status_instead_of_trusting_the_url(): void
    {
        $booking = $this->booking();
        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])->call('choose', 'card')->call('pay');

        // Un client qui forge ?status=approved ne fait pas passer le paiement.
        $this->get(route('payment.return', $booking->payment_token).'?id=4242&status=approved')
            ->assertRedirect(route('payment.show', $booking->payment_token));
        $this->assertFalse($booking->fresh()->isPaid());

        $this->remoteStatus = 'approved';
        $this->get(route('payment.return', $booking->payment_token).'?id=4242&status=approved');
        $this->assertTrue($booking->fresh()->isPaid());
    }

    public function test_webhook_requires_a_valid_signature_and_rereads_the_transaction(): void
    {
        $booking = $this->booking();
        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])->call('choose', 'card')->call('pay');
        $this->remoteStatus = 'approved';

        $payload = json_encode(['name' => 'transaction.approved', 'entity' => ['id' => 4242, 'status' => 'approved']]);

        $this->call('POST', route('payment.webhook'), [], [], [], ['HTTP_X_FEDAPAY_SIGNATURE' => 't='.time().',s=forged', 'CONTENT_TYPE' => 'application/json'], $payload)
            ->assertStatus(400);
        $this->assertFalse($booking->fresh()->isPaid());

        $t = time();
        $signature = hash_hmac('sha256', $t.'.'.$payload, 'wh_secret_test');
        $this->call('POST', route('payment.webhook'), [], [], [], ['HTTP_X_FEDAPAY_SIGNATURE' => "t=$t,s=$signature", 'CONTENT_TYPE' => 'application/json'], $payload)
            ->assertOk();

        $this->assertTrue($booking->fresh()->isPaid());
        Http::assertSent(fn (Request $r) => $r->method() === 'GET' && str_ends_with($r->url(), '/transactions/4242'));
    }

    public function test_manual_methods_show_instructions_without_calling_fedapay(): void
    {
        $booking = $this->booking();

        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->call('choose', 'cash')->call('pay')
            ->assertSet('stage', 'manual')
            ->assertSee('Paiement en espèces au studio')
            ->assertSee($booking->reference);

        $this->assertSame('cash', $booking->payments()->first()->method);
        $this->assertFalse($booking->fresh()->isPaid());
        Http::assertNothingSent();
    }

    public function test_online_methods_are_unavailable_without_fedapay_keys(): void
    {
        config(['payments.fedapay.secret_key' => null]);
        $booking = $this->booking();

        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->assertSee('Bientôt disponible')
            ->call('choose', 'moov_money')->set('phone', '90123456')->call('pay')
            ->assertHasErrors('method');

        Http::assertNothingSent();
    }

    public function test_paid_or_cancelled_bookings_cannot_be_paid_again(): void
    {
        $booking = $this->booking();
        $booking->update(['status' => BookingStatus::Cancelled]);

        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])
            ->assertSee('plus en attente de paiement')
            ->call('choose', 'card')->call('pay')
            ->assertNoRedirect();

        Http::assertNothingSent();
    }

    public function test_expiry_command_keeps_a_booking_whose_online_payment_succeeded(): void
    {
        $booking = $this->booking();
        Livewire::test(BookingPayment::class, ['token' => $booking->payment_token])->call('choose', 'card')->call('pay');
        $this->remoteStatus = 'approved';

        $this->travel(4)->days();
        Artisan::call('ocd:expire-unpaid');

        $booking->refresh();
        $this->assertNotSame(BookingStatus::Cancelled, $booking->status);
        $this->assertTrue($booking->isPaid());
    }

    public function test_payment_page_embeds_the_payment_component(): void
    {
        $booking = $this->booking();

        $this->get(route('payment.show', $booking->payment_token))->assertSeeLivewire(BookingPayment::class);
    }
}
