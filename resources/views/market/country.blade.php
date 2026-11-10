@extends('layouts.public')

@section('title', $country->name.' — OCD LIVE MARKET')
@section('description', 'Découvrez les commerçants, entrepreneurs et entreprises de '.$country->name.' en direct sur TikTok, vérifiés par OCD Live.')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-br from-ink via-brand-800 to-brand-700 text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
            <a href="{{ route('market.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-white/70 hover:text-white"><x-icon name="chevron-left" class="h-4 w-4" />Tous les pays</a>
            <p class="font-display text-5xl">{{ $country->flag }}</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">{{ $country->name }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-white/80">{{ $sellerCount }} vendeur(s) référencé(s) sur OCD Live Market.</p>
        </div>
    </section>

    <section class="bg-neutral-50 py-14 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('market.country', $country) }}" class="card mb-10 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2">
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Vendeur, produit, ville…" class="field w-full">
                </div>
                <select name="city" class="field w-full">
                    <option value="">Toutes les villes</option>
                    @foreach ($cities as $city) <option value="{{ $city->id }}" @selected(($filters['city'] ?? null) == $city->id)>{{ $city->name }}</option> @endforeach
                </select>
                <select name="category" class="field w-full">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $cat) <option value="{{ $cat->id }}" @selected(($filters['category'] ?? null) == $cat->id)>{{ $cat->name }}</option> @endforeach
                </select>
                <select name="schedule" class="field w-full">
                    <option value="">Tous les créneaux</option>
                    @foreach ($schedules as $s) <option value="{{ $s->id }}" @selected(($filters['schedule'] ?? null) == $s->id)>{{ $s->label }}</option> @endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
                    <button type="submit" class="btn-primary">Filtrer</button>
                    <a href="{{ route('market.country', $country) }}" class="btn-outline">Réinitialiser</a>
                </div>
            </form>

            @if ($lives->isEmpty())
                <div class="rounded-2xl border border-dashed border-neutral-300 bg-white py-20 text-center">
                    <span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-neutral-100 text-neutral-400"><x-icon name="eye" class="h-6 w-6" /></span>
                    <p class="text-neutral-600">Aucun LIVE disponible pour ces critères. Revenez bientôt découvrir de nouveaux vendeurs.</p>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($lives as $live)
                        <x-market.live-card :live="$live" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $lives->links() }}</div>
            @endif
        </div>
    </section>
@endsection
