<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\LiveReport;
use App\Models\LiveSchedule;
use App\Models\LiveSubmission;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class MarketController extends Controller
{
    /** GET /market — annuaire public des lives approuvés et non expirés. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'integer'],
            'city' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
            'subcategory' => ['nullable', 'integer'],
            'schedule' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
        ]);

        $lives = $this->filteredLives($filters)->paginate(12)->withQueryString();

        return view('market.index', [
            'lives' => $lives,
            'filters' => $filters,
            'countries' => Country::active()->ordered()->withCount([
                'submissions' => fn ($q) => $q->published(),
            ])->get(),
            'categories' => Category::active()->topLevel()->ordered()->get(),
            'cities' => $filters['country'] ?? null ? \App\Models\City::where('country_id', $filters['country'])->active()->ordered()->get() : collect(),
            'schedules' => LiveSchedule::active()->ordered()->get(),
        ]);
    }

    /** GET /market/{country} — page pays. */
    public function country(Request $request, Country $country): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
            'subcategory' => ['nullable', 'integer'],
            'schedule' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
        ]);
        $filters['country'] = $country->id;

        $lives = $this->filteredLives($filters)->paginate(12)->withQueryString();

        return view('market.country', [
            'country' => $country,
            'lives' => $lives,
            'filters' => $filters,
            'cities' => $country->cities()->active()->ordered()->get(),
            'categories' => Category::active()->topLevel()->ordered()->get(),
            'schedules' => LiveSchedule::active()->ordered()->get(),
            'sellerCount' => Seller::where('country_id', $country->id)->count(),
        ]);
    }

    /** GET /market/vendeur/{seller} — fiche publique facultative d'un vendeur. */
    public function seller(Seller $seller): View
    {
        abort_unless($seller->hasPublicProfile(), 404);

        $seller->load(['country', 'city']);

        return view('market.seller', [
            'seller' => $seller,
            'lives' => $seller->activeSubmissions()->with(['seller', 'country', 'city', 'category', 'schedule'])->latest('published_at')->get(),
        ]);
    }

    /** GET /market/submit — formulaire de soumission (Livewire). */
    public function submitForm(): View
    {
        return view('market.submit');
    }

    /** GET /market/suivi/{token} — suivi de statut, lecture seule, recherche uniquement par jeton. */
    public function track(string $token): View
    {
        $submission = LiveSubmission::query()
            ->where('tracking_token', $token)
            ->with(['seller', 'category', 'country', 'city'])
            ->firstOrFail();

        return view('market.track', ['submission' => $submission]);
    }

    /** POST /market/lives/{submission}/signaler — signalement public d'un contenu problématique. */
    public function report(Request $request, LiveSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'reporter_contact' => ['nullable', 'string', 'max:190'],
            'website' => ['max:0'], // pot de miel
        ]);

        $key = 'market-report:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('status', 'Trop de signalements envoyés depuis cette connexion. Merci de réessayer plus tard.');
        }
        RateLimiter::hit($key, 3600);

        LiveReport::create([
            'submission_id' => $submission->id,
            'reporter_contact' => $data['reporter_contact'] ?? null,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'status' => 'OPEN',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', 'Merci, votre signalement a été transmis à l\'équipe OCD Live.');
    }

    private function filteredLives(array $filters)
    {
        return LiveSubmission::query()
            ->published()
            ->with(['seller', 'country', 'city', 'category', 'subcategory', 'schedule'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('account_name', 'like', "%$term%")
                        ->orWhere('product_description', 'like', "%$term%")
                        ->orWhereHas('seller', fn ($s) => $s->where('full_name', 'like', "%$term%")
                            ->orWhere('business_name', 'like', "%$term%"));
                });
            })
            ->when($filters['country'] ?? null, fn ($q, $v) => $q->where('country_id', $v))
            ->when($filters['city'] ?? null, fn ($q, $v) => $q->where('city_id', $v))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['subcategory'] ?? null, fn ($q, $v) => $q->where('subcategory_id', $v))
            ->when($filters['schedule'] ?? null, fn ($q, $v) => $q->where('schedule_id', $v))
            ->when($filters['date'] ?? null, fn ($q, $v) => $q->whereDate('published_at', $v))
            ->orderByDesc('published_at');
    }
}
