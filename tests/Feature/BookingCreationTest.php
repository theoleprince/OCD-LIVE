<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\BookingWizard;
use App\Models\Booking;
use App\Models\BookingAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class BookingCreationTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();
        Storage::fake('local');
    }

    private function filledWizard()
    {
        $wizard = Livewire::test(BookingWizard::class)
            ->call('selectMode', 'REMOTE')->assertSet('step', 2)
            ->call('selectPackage', $this->starter()->id)->assertSet('step', 3)
            ->call('selectDate', '2026-12-01')->assertSet('step', 4)
            ->call('selectTime', '10:00')->assertSet('step', 5)
            ->set('lastName', 'Dupont')->set('firstName', 'Jean')
            ->set('country', 'Cameroun')->set('city', 'Douala')
            ->set('phone', '+237 600 00 00 01')->set('whatsapp', '+237 600 00 00 01')
            ->set('email', 'jean@example.com')->set('connectionType', '4g')->set('needsAssistance', true)
            ->call('next')->assertHasNoErrors()->assertSet('step', 6)
            ->set('businessName', 'Commerce alimentaire')->set('sector', 'Mode & textile')
            ->set('description', 'Vente de pagnes et vêtements sur mesure.')
            ->set('offerNature', 'Pagnes, robes')
            ->set('businessPhone', '+237 600 00 00 02')->set('businessAddress', 'Akwa, Douala')
            ->set('productsCount', 2)
            ->set('products', [['name' => 'Pagne wax', 'quantity' => '30']]);

        // L'upload Livewire utilise une URL signée à durée limitée : on libère l'horloge le temps de l'envoi.
        \Illuminate\Support\Carbon::setTestNow();
        \Carbon\CarbonImmutable::setTestNow();
        $wizard->set('logo', UploadedFile::fake()->image('logo.png', 200, 200));
        $this->atLomeTime();

        return $wizard->call('next')->assertHasNoErrors()->assertSet('step', 7);
    }

    public function test_full_wizard_creates_a_pending_payment_booking(): void
    {
        $this->filledWizard()
            ->set('acceptTerms', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', 8)
            ->assertSee('En attente de paiement');

        $booking = Booking::with(['customer', 'business', 'package', 'payments'])->sole();
        $this->assertMatchesRegularExpression('/^OCD-2026-\d{4}$/', $booking->reference);
        $this->assertSame(BookingStatus::PendingPayment, $booking->status);
        $this->assertSame('2026-12-01 10:00', $booking->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-12-01 11:30', $booking->ends_at->format('Y-m-d H:i'));
        $this->assertSame(15000, $booking->amount);
        $this->assertSame('REMOTE', $booking->mode->value);
        $this->assertSame('Africa/Douala', $booking->participant_timezone);
        $this->assertTrue($booking->needs_assistance);

        // Paiement en attente uniquement — jamais de paiement simulé comme réussi.
        $this->assertCount(1, $booking->payments);
        $this->assertSame(PaymentStatus::Pending, $booking->payments->first()->status);
        $this->assertFalse($booking->isPaid());

        // Logo stocké sur le disque privé avec un nom aléatoire.
        $logo = BookingAttachment::sole();
        $this->assertSame('local', $logo->disk);
        $this->assertStringStartsWith('bookings/'.$booking->id.'/', $logo->path);
        $this->assertStringNotContainsString('logo.png', $logo->path);
        Storage::disk('local')->assertExists($logo->path);
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->filledWizard()->call('submit')->assertHasErrors(['acceptTerms']);
        $this->assertSame(0, Booking::count());
    }

    public function test_required_personal_fields_are_validated(): void
    {
        Livewire::test(BookingWizard::class)
            ->call('selectMode', 'ONSITE')
            ->call('selectPackage', $this->starter()->id)
            ->call('selectDate', '2026-12-01')
            ->call('selectTime', '10:00')
            ->set('email', 'pas-un-email')
            ->call('next')
            ->assertHasErrors(['lastName', 'firstName', 'city', 'phone', 'whatsapp', 'email'])
            ->assertSet('step', 5);
    }

    public function test_food_sector_requires_preparation_and_conservation(): void
    {
        $this->filledWizard()
            ->call('goTo', 6)
            ->set('sector', 'Alimentation & restauration')
            ->call('next')
            ->assertHasErrors(['foodPreparation', 'foodConservation']);
    }

    public function test_unavailable_date_cannot_be_selected(): void
    {
        Livewire::test(BookingWizard::class)
            ->call('selectMode', 'ONSITE')
            ->call('selectPackage', $this->starter()->id)
            ->call('selectDate', '2026-12-06') // dimanche fermé
            ->assertHasErrors('date')
            ->assertSet('step', 3);
    }

    public function test_booking_page_preselects_mode_and_package_from_query(): void
    {
        $this->get('/reserver?mode=remote&package=starter-live')->assertOk()->assertSee('Choisissez votre date');
    }

    public function test_steps_cannot_be_skipped(): void
    {
        Livewire::test(BookingWizard::class)->call('goTo', 7)->assertSet('step', 1);
    }
}
