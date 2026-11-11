<?php

namespace App\Livewire;

use App\Exceptions\DuplicateSubmissionException;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\LiveSchedule;
use App\Services\SellerSubmissionService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class LiveSubmissionForm extends Component
{
    use WithFileUploads;

    // Informations personnelles
    public string $fullName = '';
    public string $businessName = '';
    public string $phone = '';
    public ?int $countryId = null;
    public ?int $cityId = null;
    public string $description = '';

    // Informations commerciales
    public ?int $categoryId = null;
    public ?int $subcategoryId = null;
    public string $productDescription = '';

    // Informations du LIVE
    public string $accountName = '';
    public string $liveUrl = '';
    public ?int $scheduleId = null;

    // Facultatif
    public $profileImage = null;
    public $logoImage = null;

    // Consentements
    public bool $acceptTerms = false;
    public bool $authorizePublication = false;
    public bool $confirmOwnership = false;
    public string $website = ''; // pot de miel anti-robot (doit rester vide)

    // Résultat
    public bool $submitted = false;
    public ?string $reference = null;
    public ?string $trackingUrl = null;
    public int $step = 1;
    public bool $acceptAll = false;

    public function updatedAcceptAll(bool $value): void
    {
        $this->acceptTerms = $this->authorizePublication = $this->confirmOwnership = $value;
    }

    public function next(): void
    {
        $this->validate($this->stepRules($this->step), [], $this->attributeNames());
        $this->step = min(4, $this->step + 1);
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    private function stepRules(int $step): array
    {
        $fields = match ($step) {
            1 => ['liveUrl', 'productDescription'],
            2 => ['businessName', 'phone'],
            3 => ['countryId', 'cityId'],
            default => ['categoryId'],
        };

        return array_intersect_key($this->rules(), array_flip($fields));
    }

    public function updatedCountryId(): void
    {
        $this->cityId = null;
    }

    public function updatedCategoryId(): void
    {
        $this->subcategoryId = null;
    }

    public function submit(SellerSubmissionService $service): void
    {
        if ($this->website !== '') {
            abort(422);
        }

        $key = 'market-submit:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('submit', 'Trop de demandes envoyées depuis cette connexion. Merci de réessayer dans quelques minutes.');

            return;
        }

        if ($this->acceptAll) {
            $this->acceptTerms = $this->authorizePublication = $this->confirmOwnership = true;
        }
        if (preg_match('~^/@([a-zA-Z0-9._]+)(?:/|$)~', parse_url(trim($this->liveUrl), PHP_URL_PATH) ?? '', $account)) {
            $this->accountName = '@'.$account[1];
        }
        try {
            $this->validate($this->rules(), [], $this->attributeNames());
        } catch (ValidationException $e) {
            $this->step = 4;
            foreach ([1, 2, 3] as $step) {
                if (array_intersect_key($e->errors(), $this->stepRules($step))) {
                    $this->step = $step;
                    break;
                }
            }
            throw $e;
        }
        RateLimiter::hit($key, 1800);

        try {
            $submission = $service->create($this->payload(), [
                'profile_image' => $this->profileImage,
                'logo_image' => $this->logoImage,
            ]);
        } catch (DuplicateSubmissionException $e) {
            $this->addError('submit', $e->getMessage());

            return;
        }

        $this->reference = $submission->reference;
        $this->trackingUrl = route('market.track', $submission->tracking_token);
        $this->submitted = true;
    }

    private function payload(): array
    {
        return [
            'seller' => [
                'full_name' => trim($this->fullName) ?: null,
                'business_name' => trim($this->businessName),
                'phone' => trim($this->phone),
                'country_id' => $this->countryId,
                'city_id' => $this->cityId,
                'description' => $this->description ?: null,
                'consents' => [
                    'terms' => $this->acceptTerms,
                    'publication' => $this->authorizePublication,
                    'ownership' => $this->confirmOwnership,
                    'accepted_at' => now()->toIso8601String(),
                ],
            ],
            'category_id' => $this->categoryId,
            'subcategory_id' => $this->subcategoryId,
            'product_description' => trim($this->productDescription),
            'live' => [
                'account_name' => trim($this->accountName),
                'url' => trim($this->liveUrl),
            ],
            'schedule_id' => $this->scheduleId,
            'consents' => [
                'terms' => $this->acceptTerms,
                'publication' => $this->authorizePublication,
                'ownership' => $this->confirmOwnership,
                'accepted_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function rules(): array
    {
        $phone = ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ().\-]{6,25}$/'];
        $image = ['nullable', 'image', 'mimes:'.implode(',', config('ocd.uploads.mimes')), 'max:'.config('ocd.uploads.max_kb')];

        return [
            'fullName' => ['nullable', 'string', 'max:150'],
            'businessName' => ['required', 'string', 'max:150'],
            'phone' => $phone,
            'countryId' => ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
            'cityId' => ['required', 'integer', Rule::exists('cities', 'id')->where('country_id', $this->countryId)->where('is_active', true)],
            'description' => ['nullable', 'string', 'max:2000'],
            'categoryId' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)->whereNull('parent_id')],
            'subcategoryId' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('parent_id', $this->categoryId)],
            'productDescription' => ['required', 'string', 'min:10', 'max:2000'],
            'accountName' => ['required', 'string', 'max:100'],
            'liveUrl' => ['required', 'string', 'max:255', 'regex:/^https?:\/\/(www\.|vm\.|vt\.|m\.)?tiktok\.com\/.+/i'],
            'scheduleId' => ['nullable', 'integer', Rule::exists('live_schedules', 'id')->where('is_active', true)],
            'profileImage' => $image,
            'logoImage' => $image,
            'acceptTerms' => ['accepted'],
            'authorizePublication' => ['accepted'],
            'confirmOwnership' => ['accepted'],
        ];
    }

    private function attributeNames(): array
    {
        return [
            'fullName' => 'nom et prénom', 'businessName' => 'nom commercial', 'phone' => 'téléphone ou WhatsApp',
            'countryId' => 'pays', 'cityId' => 'ville', 'categoryId' => 'catégorie', 'subcategoryId' => 'sous-catégorie',
            'productDescription' => 'description du produit ou service', 'accountName' => 'nom du compte TikTok',
            'liveUrl' => 'lien du LIVE ou du profil TikTok', 'scheduleId' => 'créneau souhaité',
            'profileImage' => 'photo de profil', 'logoImage' => 'logo', 'acceptTerms' => 'conditions d\'utilisation',
            'authorizePublication' => 'autorisation de publication', 'confirmOwnership' => 'autorisation de partage',
        ];
    }

    #[Computed]
    public function countries()
    {
        return Country::active()->ordered()->get();
    }

    #[Computed]
    public function cities()
    {
        return $this->countryId ? City::where('country_id', $this->countryId)->active()->ordered()->get() : collect();
    }

    #[Computed]
    public function categories()
    {
        return Category::active()->topLevel()->ordered()->get();
    }

    #[Computed]
    public function subcategories()
    {
        return $this->categoryId ? Category::where('parent_id', $this->categoryId)->active()->ordered()->get() : collect();
    }

    #[Computed]
    public function schedules()
    {
        return LiveSchedule::active()->ordered()->get();
    }

    public function render()
    {
        return view('livewire.live-submission-form');
    }
}
