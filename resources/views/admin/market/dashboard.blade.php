@extends('layouts.admin')

@section('title', 'OCD LIVE MARKET — Tableau de bord')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Soumissions totales', $stats['total'], 'list'],
            ['Aujourd\'hui', $stats['today'], 'calendar'],
            ['En attente', $stats['pending'], 'clock'],
            ['En cours d\'examen', $stats['under_review'], 'eye'],
            ['Approuvées', $stats['approved'], 'check'],
            ['Publiées actuellement', $stats['published_now'], 'shield'],
            ['Expirées', $stats['expired'], 'x'],
            ['Refusées', $stats['rejected'], 'trash'],
        ] as [$label, $value, $icon])
            <div class="card p-5">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-600"><x-icon :name="$icon" class="h-5 w-5" /></span>
                <p class="mt-3 font-display text-3xl text-ink">{{ $value }}</p>
                <p class="text-sm text-neutral-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <section class="card p-5 sm:p-6">
            <h2 class="font-semibold text-ink">Vendeurs par pays</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($byCountry as $c)
                    <li class="flex items-center justify-between"><span>{{ $c->flag }} {{ $c->name }}</span><span class="font-semibold text-ink">{{ $c->submissions_count }}</span></li>
                @endforeach
            </ul>
        </section>
        <section class="card p-5 sm:p-6">
            <h2 class="font-semibold text-ink">Lives par catégorie</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($byCategory as $cat)
                    <li class="flex items-center justify-between"><span>{{ $cat->name }}</span><span class="font-semibold text-ink">{{ $cat->submissions_count }}</span></li>
                @endforeach
            </ul>
        </section>
        <section class="card p-5 sm:p-6">
            <h2 class="font-semibold text-ink">Validations par modérateur</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($byModerator as $row)
                    <li class="flex items-center justify-between"><span>{{ $row->moderator?->name ?? 'Inconnu' }}</span><span class="font-semibold text-ink">{{ $row->validations_count }}</span></li>
                @empty
                    <li class="text-neutral-500">Aucune validation enregistrée.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.market.submissions.index') }}" class="btn-primary btn-sm">Voir les demandes</a>
        <a href="{{ route('admin.market.lives.index') }}" class="btn-outline btn-sm">Publications actives</a>
        <a href="{{ route('admin.market.reports.index') }}" class="btn-outline btn-sm">Signalements</a>
    </div>
@endsection
