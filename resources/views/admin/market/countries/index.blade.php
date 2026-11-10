@extends('layouts.admin')

@section('title', 'Pays et villes')

@section('content')
    <section class="card mb-4 p-5 sm:p-6">
        <h2 class="font-semibold text-ink">Ajouter un pays</h2>
        <form method="POST" action="{{ route('admin.market.countries.store') }}" class="mt-3 grid gap-3 sm:grid-cols-4">
            @csrf
            <input name="name" required placeholder="Nom du pays" class="field-sm sm:col-span-2" maxlength="100">
            <input name="iso_code" required placeholder="Code ISO (ex. TG)" class="field-sm" maxlength="2">
            <input name="timezone" required placeholder="Fuseau (ex. Africa/Lome)" class="field-sm" maxlength="64">
            <button class="btn-primary btn-sm sm:col-span-4">Ajouter</button>
        </form>
    </section>

    <div class="space-y-3">
        @foreach ($countries as $country)
            <details class="card p-4 sm:p-5">
                <summary class="flex cursor-pointer items-center justify-between gap-3">
                    <span class="flex items-center gap-2 font-semibold text-ink">{{ $country->flag }} {{ $country->name }} <span class="badge {{ $country->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-neutral-100 text-neutral-500 ring-neutral-200' }}">{{ $country->is_active ? 'Actif' : 'Inactif' }}</span></span>
                    <span class="text-sm text-neutral-500">{{ $country->cities_count }} ville(s) · {{ $country->sellers_count }} vendeur(s)</span>
                </summary>

                <div class="mt-4 border-t border-neutral-100 pt-4">
                    <form method="POST" action="{{ route('admin.market.countries.toggle', $country) }}" class="inline">
                        @csrf<button class="btn-outline btn-sm">{{ $country->is_active ? 'Désactiver' : 'Activer' }}</button>
                    </form>

                    <p class="mt-4 text-sm font-semibold text-ink">Villes</p>
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($country->cities as $city)
                            <li class="inline-flex items-center gap-1 rounded-full border border-neutral-200 px-3 py-1 text-xs">
                                {{ $city->name }}
                                <form method="POST" action="{{ route('admin.market.countries.cities.toggle', [$country, $city]) }}" class="inline">
                                    @csrf<button class="ml-1 {{ $city->is_active ? 'text-emerald-600' : 'text-neutral-400' }}">{{ $city->is_active ? '●' : '○' }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('admin.market.countries.cities.store', $country) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input name="name" required placeholder="Nouvelle ville" class="field-sm flex-1" maxlength="100">
                        <button class="btn-outline btn-sm">Ajouter</button>
                    </form>
                </div>
            </details>
        @endforeach
    </div>
@endsection
