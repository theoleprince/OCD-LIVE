@extends('layouts.public')

@section('title', 'OCD LIVE MARKET — L\'Afrique qui vend en direct')
@section('description', 'Découvrez les commerçants, entrepreneurs et entreprises africains qui présentent leurs produits et services en direct sur TikTok, vérifiés par OCD Live.')

@section('content')
    {{-- BANNIÈRE --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-ink via-brand-800 to-brand-700 text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-white">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse-soft"></span>OCD LIVE MARKET
            </p>
            <h1 class="mt-5 font-display text-4xl leading-[1.05] sm:text-5xl">L'Afrique qui vend en direct.</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-white/80">Découvrez les commerçants, entrepreneurs et entreprises africains qui présentent leurs produits et services en direct.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#lives" class="btn-accent !px-7 !py-4 text-base">Explorer les LIVE</a>
                <a href="{{ route('market.submit') }}" class="btn-light !px-7 !py-4 text-base">Soumettre mon LIVE</a>
            </div>
        </div>
    </section>

    <section id="lives" class="scroll-mt-10 bg-neutral-50 py-14 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- PAYS --}}
            <div class="mb-8 flex gap-2 overflow-x-auto pb-2">
                @foreach ($countries as $c)
                    <a href="{{ route('market.country', $c) }}" class="flex shrink-0 items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-2 text-sm font-semibold text-ink transition hover:border-brand-400">
                        <span>{{ $c->flag }}</span>{{ $c->name }}
                        <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-500">{{ $c->submissions_count }}</span>
                    </a>
                @endforeach
            </div>

            {{-- FILTRES --}}
            <form method="GET" action="{{ route('market.index') }}" class="card mb-10 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-6">
                <div class="sm:col-span-2 lg:col-span-2">
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Vendeur, produit, ville…" class="field w-full">
                </div>
                <select name="country" class="field w-full" onchange="this.form.submit()">
                    <option value="">Tous les pays</option>
                    @foreach ($countries as $c) <option value="{{ $c->id }}" @selected(($filters['country'] ?? null) == $c->id)>{{ $c->flag }} {{ $c->name }}</option> @endforeach
                </select>
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
                <div class="flex gap-2 sm:col-span-2 lg:col-span-6">
                    <button type="submit" class="btn-primary">Filtrer</button>
                    <a href="{{ route('market.index') }}" class="btn-outline">Réinitialiser</a>
                </div>
            </form>

            {{-- RÉSULTATS --}}
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
