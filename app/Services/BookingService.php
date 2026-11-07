<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\ParticipationMode;
use App\Enums\PaymentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Payment;
use App\Notifications\BookingReceivedNotification;
use App\Notifications\NewBookingNotification;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * Crée une réservation en statut PENDING_PAYMENT.
     *
     * La disponibilité est revérifiée côté serveur à l'intérieur d'une transaction
     * qui verrouille (SELECT … FOR UPDATE) la ligne de la journée concernée :
     * deux demandes simultanées sur la même date sont donc sérialisées.
     *
     * @param  array{logo?: ?UploadedFile, photos?: UploadedFile[]}  $files
     *
     * @throws SlotUnavailableException
     */
    public function create(array $data, array $files = []): Booking
    {
        $package = Package::query()->active()->findOrFail($data['package_id']);
        $start = CarbonImmutable::parse($data['starts_at'], config('ocd.timezone'));
        $end = $start->addMinutes($package->duration_minutes);
        $mode = ParticipationMode::from($data['mode']);

        $this->ensureDayRow($start);
        setting('buffer_minutes'); // charge les paramètres hors transaction

        if (DB::getDriverName() === 'mysql') {
            // Après l'attente du verrou, les lectures doivent voir les réservations validées entre-temps.
            DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        }

        $booking = DB::transaction(function () use ($data, $package, $start, $end, $mode) {
            // Toute première instruction de la transaction : verrou exclusif sur la journée.
            AvailabilitySlot::query()->whereDate('date', $start->toDateString())->lockForUpdate()->first();

            if (! $this->availability->isSlotAvailable($start, $package->duration_minutes)) {
                throw new SlotUnavailableException;
            }

            $customer = $this->resolveCustomer($data['customer']);
            $business = $this->resolveBusiness($customer, $data['business']);

            $booking = Booking::create([
                'reference' => 'TMP-'.Str::random(20), // remplacée juste après par OCD-AAAA-NNNN
                'customer_id' => $customer->id,
                'business_id' => $business->id,
                'package_id' => $package->id,
                'mode' => $mode,
                'booking_date' => $start->toDateString(),
                'starts_at' => $start,
                'ends_at' => $end,
                'duration_minutes' => $package->duration_minutes,
                'amount' => $package->price,
                'currency' => 'XOF',
                'status' => BookingStatus::PendingPayment,
                'participant_timezone' => $mode === ParticipationMode::Remote ? ($data['remote']['timezone'] ?? $customer->timezone) : config('ocd.timezone'),
                'connection_type' => $mode === ParticipationMode::Remote ? ($data['remote']['connection_type'] ?? null) : null,
                'needs_assistance' => $mode === ParticipationMode::Remote && ! empty($data['remote']['needs_assistance']),
                'assistance_notes' => $mode === ParticipationMode::Remote ? ($data['remote']['assistance_notes'] ?? null) : null,
                'products_count' => (int) ($data['business']['products_count'] ?? 1),
                'customer_notes' => $data['notes'] ?? null,
                'payment_due_at' => now()->addHours((int) setting('payment_hold_hours')),
                'is_demo' => (bool) ($data['is_demo'] ?? false),
                'ip_address' => request()?->ip(),
            ]);

            $booking->update(['reference' => sprintf('OCD-%d-%04d', now()->year, $booking->id)]);

            Payment::create([
                'booking_id' => $booking->id,
                'provider' => config('payments.default', 'manual'),
                'amount' => $booking->amount,
                'currency' => 'XOF',
                'status' => PaymentStatus::Pending,
            ]);

            return $booking;
        }, 3);

        $this->storeAttachments($booking, $files);
        $this->notify($booking->fresh(['customer', 'business.products', 'package']));

        return $booking;
    }

    /** Garantit l'existence de la ligne de la journée, qui sert de verrou (hors transaction). */
    private function ensureDayRow(CarbonImmutable $start): void
    {
        $date = $start->toDateString();

        if (! AvailabilitySlot::query()->whereDate('date', $date)->exists()) {
            try {
                AvailabilitySlot::query()->create(['date' => $date]);
            } catch (QueryException) {
                // Création concurrente (index unique) : la ligne existe désormais.
            }
        }
    }

    private function resolveCustomer(array $input): Customer
    {
        $input['email'] = Str::lower(trim($input['email']));

        $existing = Customer::query()
            ->where('email', $input['email'])
            ->where('phone', $input['phone'])
            ->where('is_demo', false)
            ->first();

        if ($existing) {
            $existing->update(collect($input)->only(['whatsapp', 'country', 'city', 'timezone'])->all());

            return $existing;
        }

        return Customer::create([...$input, 'is_demo' => false]);
    }

    private function resolveBusiness(Customer $customer, array $input): Business
    {
        $sector = $input['sector'];
        $attributes = [
            'name' => $input['name'],
            'sector' => $sector,
            'description' => $input['description'],
            'offer_nature' => $input['offer_nature'],
            'business_phone' => $input['business_phone'],
            'business_address' => $input['business_address'],
            'social_links' => array_filter($input['social_links'] ?? []) ?: null,
            'is_food' => in_array($sector, config('ocd.food_sectors'), true),
            'food_preparation' => $input['food_preparation'] ?? null,
            'food_conservation' => $input['food_conservation'] ?? null,
            'food_presentation' => $input['food_presentation'] ?? null,
            'is_demo' => (bool) $customer->is_demo,
        ];

        $business = $customer->businesses()->whereRaw('LOWER(name) = ?', [Str::lower($input['name'])])->first();
        $business ? $business->update($attributes) : $business = $customer->businesses()->create($attributes);

        $products = collect($input['products'] ?? [])->filter(fn ($p) => filled($p['name'] ?? null));
        if ($products->isNotEmpty()) {
            $business->products()->delete();
            foreach ($products as $p) {
                $business->products()->create([
                    'name' => $p['name'],
                    'quantity_available' => filled($p['quantity'] ?? null) ? (int) $p['quantity'] : null,
                ]);
            }
        }

        return $business;
    }

    private function storeAttachments(Booking $booking, array $files): void
    {
        $directory = 'bookings/'.$booking->id;
        $store = function (UploadedFile $file, string $type) use ($booking, $directory) {
            // Nom aléatoire + disque privé : jamais de nom de fichier fourni par le client dans le chemin.
            $path = $file->storeAs($directory, Str::random(40).'.'.$file->extension(), 'local');

            return $booking->attachments()->create([
                'type' => $type,
                'disk' => 'local',
                'path' => $path,
                'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        };

        if (! empty($files['logo'])) {
            $logo = $store($files['logo'], 'logo');
            $booking->business->update(['logo_path' => $logo->path]);
        }

        foreach ($files['photos'] ?? [] as $photo) {
            $store($photo, 'product_photo');
        }
    }

    private function notify(Booking $booking): void
    {
        try {
            $admins = User::query()->where('is_active', true)->get();
            Notification::send($admins, new NewBookingNotification($booking));

            if ($extra = setting('admin_notification_email')) {
                Notification::route('mail', $extra)->notify(new NewBookingNotification($booking, mailOnly: true));
            }

            $booking->customer->notify(new BookingReceivedNotification($booking));
        } catch (\Throwable $e) {
            // Un incident d'envoi d'e-mail ne doit jamais faire perdre la réservation.
            Log::error('Notification de réservation non envoyée', ['booking' => $booking->reference, 'error' => $e->getMessage()]);
        }
    }
}
