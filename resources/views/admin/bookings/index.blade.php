@extends('layouts.admin')

@section('title', 'Réservations')

@section('content')
    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Référence, client, business…" class="field-sm lg:col-span-2" aria-label="Rechercher">
        <select name="status" class="field-sm" aria-label="Statut">
            <option value="">Tous les statuts</option>
            @foreach (\App\Enums\BookingStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="mode" class="field-sm" aria-label="Mode">
            <option value="">Tous les modes</option>
            @foreach (\App\Enums\ParticipationMode::cases() as $m)
                <option value="{{ $m->value }}" @selected(($filters['mode'] ?? '') === $m->value)>{{ $m->label() }}</option>
            @endforeach
        </select>
        <select name="package" class="field-sm" aria-label="Package">
            <option value="">Tous les packages</option>
            @foreach ($packages as $p)
                <option value="{{ $p->id }}" @selected((int) ($filters['package'] ?? 0) === $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="btn-primary btn-sm flex-1">Filtrer</button>
            @if (array_filter($filters))<a href="{{ route('admin.bookings.index') }}" class="btn-outline btn-sm">×</a>@endif
        </div>
        <div class="flex items-center gap-2 text-sm sm:col-span-2 lg:col-span-3">
            <label for="from" class="text-neutral-500">Du</label><input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="field-sm flex-1">
            <label for="to" class="text-neutral-500">au</label><input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="field-sm flex-1">
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr><th>Référence</th><th>Client</th><th>Business</th><th>Pays</th><th>Mode</th><th>Package</th><th>Date</th><th>Heure</th><th class="text-right">Montant</th><th>Statut</th><th>Paiement</th></tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $b)
                        <tr class="cursor-pointer hover:bg-neutral-50" onclick="window.location='{{ route('admin.bookings.show', $b) }}'">
                            <td><a href="{{ route('admin.bookings.show', $b) }}" class="font-mono font-semibold text-ink hover:text-gold-700">{{ $b->reference }}</a>
                                @if ($b->is_demo)<span class="badge ml-1 bg-amber-50 text-amber-700 ring-amber-200">DÉMO</span>@endif</td>
                            <td>{{ $b->customer->full_name }}</td>
                            <td class="max-w-[12rem] truncate">{{ $b->business->name }}</td>
                            <td>{{ $b->customer->country }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1.5"><x-icon :name="$b->mode->value === 'REMOTE' ? 'globe' : 'studio'" class="h-4 w-4 text-gold-600" />{{ $b->mode->shortLabel() }}</span>
                                @if ($b->needs_assistance)<span class="badge ml-1 bg-sky-50 text-sky-700 ring-sky-200">assistance</span>@endif
                            </td>
                            <td>{{ $b->package->name }}</td>
                            <td>{{ $b->starts_at->format('d/m/Y') }}</td>
                            <td>{{ $b->time_range }}</td>
                            <td class="text-right font-medium">{{ money_fcfa($b->amount) }}</td>
                            <td><span class="badge {{ $b->status->badgeClasses() }}">{{ $b->status->label() }}</span></td>
                            <td><span class="badge {{ $b->paymentStatus()->badgeClasses() }}">{{ $b->paymentStatus()->label() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="py-12 text-center text-neutral-500">Aucune réservation ne correspond à ces critères.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>
@endsection
