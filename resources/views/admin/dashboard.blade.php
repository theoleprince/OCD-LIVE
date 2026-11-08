@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')
    @if ($stats['demo'])
        <p class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            La base contient {{ $stats['demo'] }} réservation(s) de <strong>démonstration</strong> (marquées « DÉMO »). Elles sont exclues des revenus encaissés.
        </p>
    @endif

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Réservations', $stats['total'], 'ticket', route('admin.bookings.index')],
            ['Aujourd\'hui', $stats['today'], 'calendar', route('admin.bookings.index', ['from' => now()->toDateString(), 'to' => now()->toDateString()])],
            ['En attente', $stats['pending'], 'clock', route('admin.bookings.index', ['status' => 'PENDING_PAYMENT'])],
            ['Confirmées', $stats['confirmed'], 'check', route('admin.bookings.index', ['status' => 'CONFIRMED'])],
        ] as [$label, $value, $icon, $href])
            <a href="{{ $href }}" class="card p-5 transition hover:border-brand-400">
                <div class="flex items-center justify-between text-neutral-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">{{ $label }}</span>
                    <x-icon :name="$icon" class="h-5 w-5 text-brand-600" />
                </div>
                <p class="mt-3 font-display text-3xl text-ink">{{ number_format($value, 0, ',', ' ') }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-6 text-white">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-100">Revenus réellement encaissés</p>
            <p class="mt-3 font-display text-3xl text-white" data-testid="revenue">{{ money_fcfa($stats['revenue']) }}</p>
            <p class="mt-2 text-xs text-brand-100">Somme des paiements au statut « Payé » (hors démo).</p>
        </div>
        <div class="card p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Clients</p>
            <p class="mt-3 font-display text-3xl text-ink">{{ $stats['customers'] }}</p>
            <p class="mt-2 text-xs text-neutral-500">{{ $stats['businesses'] }} entreprise(s) enregistrée(s)</p>
        </div>
        <div class="card p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">À traiter</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li class="flex justify-between"><span>Paiements attendus</span><strong>{{ $stats['pending_payment'] }}</strong></li>
                <li class="flex justify-between"><span>À valider</span><strong>{{ $stats['pending_review'] }}</strong></li>
                <li class="flex justify-between"><span>Assistance à distance</span><strong>{{ $remoteAssistance }}</strong></li>
            </ul>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-5">
        <section class="card p-6 lg:col-span-2">
            <h2 class="font-semibold text-ink">Répartition par package</h2>
            <p class="text-xs text-neutral-500">Réservations non annulées</p>
            @php($max = max(1, $byPackage->max('bookings_count')))
            <ul class="mt-5 space-y-4">
                @foreach ($byPackage as $p)
                    <li>
                        <div class="flex justify-between text-sm"><span>{{ $p->name }} @if ($p->trashed())<span class="text-xs text-neutral-400">(archivé)</span>@endif</span><strong>{{ $p->bookings_count }}</strong></div>
                        <div class="mt-1.5 h-2 rounded-full bg-neutral-100"><div class="h-2 rounded-full bg-brand-500" style="width: {{ round($p->bookings_count / $max * 100) }}%"></div></div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="card overflow-hidden lg:col-span-3">
            <div class="flex items-center justify-between p-6 pb-4">
                <h2 class="font-semibold text-ink">Prochaines émissions</h2>
                <a href="{{ route('admin.calendar') }}" class="text-sm font-medium text-brand-700 hover:text-ink">Calendrier →</a>
            </div>
            @forelse ($upcoming as $b)
                <a href="{{ route('admin.bookings.show', $b) }}" class="flex items-center gap-4 border-t border-neutral-100 px-6 py-3.5 hover:bg-neutral-50">
                    <div class="w-14 shrink-0 text-center">
                        <p class="text-xs uppercase text-neutral-500">{{ $b->starts_at->locale('fr')->translatedFormat('M') }}</p>
                        <p class="font-display text-xl text-ink">{{ $b->starts_at->format('d') }}</p>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-ink">{{ $b->business->name }} @if ($b->is_demo)<span class="badge bg-amber-50 text-amber-700 ring-amber-200">DÉMO</span>@endif</p>
                        <p class="truncate text-xs text-neutral-500">{{ $b->time_range }} · {{ $b->package->name }} · {{ $b->mode->shortLabel() }}</p>
                    </div>
                    <span class="badge {{ $b->status->badgeClasses() }}">{{ $b->status->label() }}</span>
                </a>
            @empty
                <p class="border-t border-neutral-100 px-6 py-8 text-center text-sm text-neutral-500">Aucune émission programmée.</p>
            @endforelse
        </section>
    </div>
@endsection
