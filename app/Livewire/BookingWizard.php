<?php

namespace App\Livewire;

use App\Enums\ParticipationMode;
use App\Exceptions\SlotUnavailableException;
use App\Models\Package;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookingWizard extends Component
{
    use WithFileUploads;

    public const STEPS = [
        1 => 'Mode',
        2 => 'Package',
        3 => 'Date',
        4 => 'Horaire',
        5 => 'Vous',
        6 => 'Business',
        7 => 'Récapitulatif',
        8 => 'Paiement',
    ];

    public int $step = 1;

    // Étapes 1 à 4
    public ?string $mode = null;
    public ?int $packageId = null;
    public int $calYear = 2026;
    public int $calMonth = 12;
    public ?string $date = null;
    public ?string $time = null;

    // Étape 5 : informations personnelles
    public string $lastName = '';
    public string $firstName = '';
    public string $country = '';
    public string $city = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $timezone = 'Africa/Lome';
    public string $connectionType = '';
    public bool $needsAssistance = false;
    public string $assistanceNotes = '';

    // Étape 6 : informations commerciales
    public string $businessName = '';
    public string $sector = '';
    public string $description = '';
    public string $offerNature = '';
    public string $businessPhone = '';
    public string $businessAddress = '';
    public array $socials = ['facebook' => '', 'instagram' => '', 'tiktok' => '', 'website' => ''];
    public $logo = null;
    public array $photos = [];
    public int $productsCount = 1;
    public array $products = [['name' => '', 'quantity' => '']];
    public string $foodPreparation = '';
    public string $foodConservation = '';
    public string $foodPresentation = '';

    // Étape 7
    public string $notes = '';
    public bool $acceptTerms = false;
    public string $website = ''; // pot de miel anti-robot (doit rester vide)

    // Étape 8
    public ?string $reference = null;
    #[Locked]
    public ?string $paymentToken = null;

    public function mount(?string $mode = null, ?string $package = null): void
    {
        $mode = $mode ?? request()->query('mode');
        $package = $package ?? request()->query('package');

        if (in_array(strtoupper((string) $mode), ['ONSITE', 'REMOTE'], true)) {
            $this->selectMode(strtoupper($mode));
        }

        $first = app(AvailabilityService::class)->firstBookableDate();
        $this->calYear = $first->year;
        $this->calMonth = $first->month;

        if ($package && $found = Package::active()->where('slug', $package)->first()) {
            $this->packageId = $found->id;

            if ($this->mode) {
                $this->goTo(3);
            }
        }
    }

    // ───────────────────────── Sélections ─────────────────────────

    public function selectMode(string $mode): void
    {
        $this->mode = ParticipationMode::from($mode)->value;

        if ($this->mode === ParticipationMode::Onsite->value) {
            $this->country = $this->country ?: 'Togo';
            $this->timezone = config('ocd.timezone');
        }

        $this->goTo($this->packageId ? 3 : 2);
    }

    public function selectPackage(int $id): void
    {
        $package = Package::active()->findOrFail($id);

        if ($this->packageId !== $package->id) {
            // La durée change : le créneau précédent n'est plus valable.
            $this->time = null;
        }

        $this->packageId = $package->id;
        $this->goTo(3);
    }

    public function selectDate(string $date): void
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! $this->selectedPackage) {
            return;
        }

        $day = CarbonImmutable::createFromFormat('Y-m-d', $date, config('ocd.timezone'));

        if (app(AvailabilityService::class)->dayStatus($day->startOfDay(), $this->selectedPackage->duration_minutes) !== AvailabilityService::DAY_AVAILABLE) {
            $this->addError('date', 'Cette date n\'est pas disponible.');

            return;
        }

        $this->date = $day->toDateString();
        $this->time = null;
        $this->goTo(4);
    }

    public function selectTime(string $time): void
    {
        $available = collect($this->timeSlots)->first(fn ($s) => $s['available'] && $s['start']->format('H:i') === $time);

        if (! $available) {
            $this->addError('time', 'Ce créneau n\'est pas disponible.');

            return;
        }

        $this->time = $time;
        $this->goTo(5);
    }

    public function previousMonth(): void
    {
        $current = CarbonImmutable::create($this->calYear, $this->calMonth, 1);
        $min = app(AvailabilityService::class)->firstBookableDate()->startOfMonth();

        if ($current->gt($min)) {
            $prev = $current->subMonth();
            [$this->calYear, $this->calMonth] = [$prev->year, $prev->month];
        }
    }

    public function nextMonth(): void
    {
        $current = CarbonImmutable::create($this->calYear, $this->calMonth, 1);
        $max = app(AvailabilityService::class)->lastBookableDate()->startOfMonth();

        if ($current->lt($max)) {
            $next = $current->addMonth();
            [$this->calYear, $this->calMonth] = [$next->year, $next->month];
        }
    }

    public function updatedCountry(string $value): void
    {
        if ($tz = config("ocd.countries.$value")) {
            $this->timezone = $tz;
        }
    }

    public function addProduct(): void
    {
        if (count($this->products) < 20) {
            $this->products[] = ['name' => '', 'quantity' => ''];
        }
    }

    public function removeProduct(int $index): void
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products) ?: [['name' => '', 'quantity' => '']];
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    // ───────────────────────── Navigation ─────────────────────────

    public function next(): void
    {
        $this->validate($this->rulesForStep($this->step), [], $this->attributeNames());
        $this->goTo($this->step + 1);
    }

    public function back(): void
    {
        if ($this->step > 1 && $this->step < 8) {
            $this->step--;
        }
    }

    public function goTo(int $step): void
    {
        // On ne peut aller qu'à une étape dont les précédentes sont valides.
        for ($s = 1; $s < $step; $s++) {
            if (! $this->stepIsComplete($s)) {
                $this->step = $s;

                return;
            }
        }

        $this->step = min(max($step, 1), 7);
    }

    public function stepIsComplete(int $step): bool
    {
        return validator($this->all(), $this->rulesForStep($step))->passes();
    }

    // ───────────────────────── Envoi ─────────────────────────

    public function submit(BookingService $bookings): void
    {
        if ($this->website !== '') {
            abort(422);
        }

        $key = 'booking-submit:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            $this->addError('submit', 'Trop de demandes envoyées. Merci de réessayer dans quelques minutes.');

            return;
        }

        foreach (array_keys(self::STEPS) as $s) {
            if ($s < 8 && ! $this->stepIsComplete($s)) {
                $this->step = $s;
                $this->validate($this->rulesForStep($s), [], $this->attributeNames());
            }
        }

        RateLimiter::hit($key, 600);

        try {
            $booking = $bookings->create($this->payload(), [
                'logo' => $this->logo,
                'photos' => $this->photos,
            ]);
        } catch (SlotUnavailableException $e) {
            $this->time = null;
            $this->step = 4;
            $this->addError('time', $e->getMessage());
            unset($this->timeSlots);

            return;
        }

        $this->reference = $booking->reference;
        $this->paymentToken = $booking->payment_token;
        $this->reset('logo', 'photos');
        $this->step = 8;
    }

    private function payload(): array
    {
        $isFood = $this->isFood;

        return [
            'mode' => $this->mode,
            'package_id' => $this->packageId,
            'starts_at' => $this->date.' '.$this->time,
            'notes' => $this->notes ?: null,
            'customer' => [
                'first_name' => trim($this->firstName),
                'last_name' => trim($this->lastName),
                'email' => trim($this->email),
                'phone' => trim($this->phone),
                'whatsapp' => trim($this->whatsapp) ?: trim($this->phone),
                'country' => $this->country,
                'city' => trim($this->city),
                'timezone' => $this->timezone,
            ],
            'remote' => [
                'timezone' => $this->timezone,
                'connection_type' => $this->connectionType ?: null,
                'needs_assistance' => $this->needsAssistance,
                'assistance_notes' => $this->assistanceNotes ?: null,
            ],
            'business' => [
                'name' => trim($this->businessName),
                'sector' => $this->sector,
                'description' => trim($this->description),
                'offer_nature' => trim($this->offerNature) ?: trim($this->description),
                'business_phone' => trim($this->businessPhone) ?: trim($this->phone),
                'business_address' => trim($this->businessAddress),
                'social_links' => array_map('trim', $this->socials),
                'products_count' => $this->productsCount,
                'products' => $this->products,
                'food_preparation' => $isFood ? ($this->foodPreparation ?: null) : null,
                'food_conservation' => $isFood ? ($this->foodConservation ?: null) : null,
                'food_presentation' => $isFood ? ($this->foodPresentation ?: null) : null,
            ],
        ];
    }

    // ───────────────────────── Validation ─────────────────────────

    protected function rulesForStep(int $step): array
    {
        $phone = ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ().\-]{6,25}$/'];
        $image = ['image', 'mimes:'.implode(',', config('ocd.uploads.mimes')), 'max:'.config('ocd.uploads.max_kb')];

        return match ($step) {
            1 => ['mode' => ['required', Rule::enum(ParticipationMode::class)]],
            2 => ['packageId' => ['required', 'integer', Rule::exists('packages', 'id')->where('is_active', true)->whereNull('deleted_at')]],
            3 => ['date' => ['required', 'date_format:Y-m-d']],
            4 => ['time' => ['required', 'date_format:H:i']],
            5 => [
                'lastName' => ['required', 'string', 'max:100'],
                'firstName' => ['required', 'string', 'max:100'],
                'country' => ['required', Rule::in(array_keys(config('ocd.countries')))],
                'city' => ['required', 'string', 'max:100'],
                'phone' => $phone,
                'whatsapp' => ['nullable', ...array_slice($phone, 1)],
                'email' => ['required', 'email:rfc', 'max:190'],
                'timezone' => ['required', 'timezone:all'],
                'connectionType' => ['nullable', Rule::in(array_keys(config('ocd.connection_types')))],
                'needsAssistance' => ['boolean'],
                'assistanceNotes' => ['nullable', 'string', 'max:1000'],
            ],
            6 => [
                'businessName' => ['required', 'string', 'max:150'],
                'sector' => ['required', Rule::in(config('ocd.sectors'))],
                'description' => ['required', 'string', 'min:20', 'max:2000'],
                'offerNature' => ['nullable', 'string', 'max:1000'],
                'businessPhone' => ['nullable', ...array_slice($phone, 1)],
                'businessAddress' => ['nullable', 'string', 'max:255'],
                'socials.*' => ['nullable', 'string', 'max:255'],
                'logo' => ['nullable', ...$image],
                'photos' => ['array', 'max:'.config('ocd.uploads.max_product_photos')],
                'photos.*' => $image,
                'productsCount' => ['required', 'integer', 'min:1', 'max:'.($this->selectedPackage?->max_products ?: 100)],
                'products' => ['array', 'max:20'],
                'products.*.name' => ['nullable', 'string', 'max:150'],
                'products.*.quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
                'foodPreparation' => [$this->isFood ? 'required' : 'nullable', 'string', 'max:1000'],
                'foodConservation' => [$this->isFood ? 'required' : 'nullable', 'string', 'max:1000'],
                'foodPresentation' => ['nullable', 'string', 'max:1000'],
            ],
            7 => [
                'notes' => ['nullable', 'string', 'max:1000'],
                'acceptTerms' => ['accepted'],
            ],
            default => [],
        };
    }

    protected function attributeNames(): array
    {
        return [
            'mode' => 'mode de participation', 'packageId' => 'package', 'date' => 'date', 'time' => 'créneau',
            'lastName' => 'nom', 'firstName' => 'prénom', 'country' => 'pays', 'city' => 'ville',
            'phone' => 'téléphone', 'whatsapp' => 'WhatsApp', 'email' => 'adresse e-mail', 'timezone' => 'fuseau horaire',
            'connectionType' => 'type de connexion', 'businessName' => 'nom du business', 'sector' => 'secteur d\'activité',
            'description' => 'description de l\'activité', 'offerNature' => 'nature des produits ou services',
            'businessPhone' => 'téléphone commercial', 'businessAddress' => 'adresse commerciale', 'logo' => 'logo',
            'photos' => 'photos', 'photos.*' => 'photo', 'productsCount' => 'nombre de produits',
            'products.*.quantity' => 'quantité', 'foodPreparation' => 'préparation', 'foodConservation' => 'conservation',
            'acceptTerms' => 'conditions générales',
        ];
    }

    // ───────────────────────── Données calculées ─────────────────────────

    #[Computed]
    public function packages()
    {
        return Package::active()->ordered()->get();
    }

    #[Computed]
    public function selectedPackage(): ?Package
    {
        return $this->packageId ? Package::active()->find($this->packageId) : null;
    }

    #[Computed]
    public function calendar(): array
    {
        if (! $this->selectedPackage) {
            return [];
        }

        return app(AvailabilityService::class)->monthCalendar($this->calYear, $this->calMonth, $this->selectedPackage->duration_minutes);
    }

    #[Computed]
    public function timeSlots(): array
    {
        if (! $this->date || ! $this->selectedPackage) {
            return [];
        }

        return app(AvailabilityService::class)->slotsForDate(
            CarbonImmutable::parse($this->date, config('ocd.timezone')),
            $this->selectedPackage->duration_minutes,
        );
    }

    #[Computed]
    public function isFood(): bool
    {
        return in_array($this->sector, config('ocd.food_sectors'), true);
    }

    public function localTime(CarbonImmutable $time): ?string
    {
        if ($this->timezone === config('ocd.timezone')) {
            return null;
        }

        return $time->setTimezone($this->timezone)->format('H\hi');
    }

    public function render()
    {
        $start = $this->date && $this->time ? CarbonImmutable::parse($this->date.' '.$this->time, config('ocd.timezone')) : null;

        return view('livewire.booking-wizard', [
            'start' => $start,
            'end' => $start && $this->selectedPackage ? $start->addMinutes($this->selectedPackage->duration_minutes) : null,
            'monthLabel' => ucfirst(CarbonImmutable::create($this->calYear, $this->calMonth, 1)->locale('fr')->translatedFormat('F Y')),
        ]);
    }
}
