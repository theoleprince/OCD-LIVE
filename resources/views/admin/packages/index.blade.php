@extends('layouts.admin')

@section('title', 'Packages')

@section('actions')
    <a href="{{ route('admin.packages.create') }}" class="btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />Nouveau</a>
@endsection

@section('content')
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($packages as $p)
            <article class="card flex flex-col p-5 {{ $p->trashed() ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs text-neutral-500">#{{ $p->sort_order }} · {{ $p->duration_text }} ({{ $p->duration_minutes }} min de créneau)</p>
                        <h2 class="mt-1 font-display text-xl text-ink">{{ $p->name }}</h2>
                    </div>
                    @if ($p->trashed())
                        <span class="badge bg-neutral-100 text-neutral-500 ring-neutral-200">Archivé</span>
                    @else
                        <span class="badge {{ $p->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-neutral-100 text-neutral-500 ring-neutral-200' }}">{{ $p->is_active ? 'Actif' : 'Inactif' }}</span>
                    @endif
                </div>
                <p class="mt-3 font-display text-2xl text-ink">{{ $p->formatted_price }}</p>
                <p class="mt-1 text-xs {{ $p->price_validated ? 'text-emerald-700' : 'text-amber-700' }}">{{ $p->price_validated ? 'Tarif validé' : 'Tarif proposé — à valider' }}</p>
                <ul class="mt-4 flex-1 space-y-1 text-sm text-neutral-600">
                    @foreach ($p->features ?? [] as $f)<li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" />{{ $f }}</li>@endforeach
                </ul>
                <p class="mt-4 text-xs text-neutral-500">{{ $p->bookings_count }} réservation(s)</p>
                <div class="mt-4 flex flex-wrap gap-2 border-t border-neutral-100 pt-4">
                    @if ($p->trashed())
                        <form method="POST" action="{{ route('admin.packages.restore', $p->id) }}">@csrf<button class="btn-outline btn-sm">Restaurer</button></form>
                    @else
                        <a href="{{ route('admin.packages.edit', $p) }}" class="btn-primary btn-sm">Modifier</a>
                        <form method="POST" action="{{ route('admin.packages.toggle', $p) }}">@csrf<button class="btn-outline btn-sm">{{ $p->is_active ? 'Désactiver' : 'Activer' }}</button></form>
                        <form method="POST" action="{{ route('admin.packages.destroy', $p) }}" onsubmit="return confirm('Archiver ce package ? Il ne sera plus proposé, les réservations existantes sont conservées.')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Archiver</button></form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endsection
