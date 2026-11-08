@extends('layouts.admin')

@section('title', 'Calendrier')

@section('content')
    @php
        $prev = $first->subMonth()->format('Y-m');
        $next = $first->addMonth()->format('Y-m');
    @endphp

    <div class="grid gap-4 xl:grid-cols-3">
        <section class="card p-4 sm:p-6 xl:col-span-2">
            <div class="mb-5 flex items-center justify-between">
                <a href="{{ route('admin.calendar', ['month' => $prev]) }}" class="grid h-10 w-10 place-items-center rounded-full border border-neutral-200 hover:border-ink" aria-label="Mois précédent"><x-icon name="chevron-left" class="h-4 w-4" /></a>
                <h2 class="font-display text-xl text-ink">{{ ucfirst($first->locale('fr')->translatedFormat('F Y')) }}</h2>
                <a href="{{ route('admin.calendar', ['month' => $next]) }}" class="grid h-10 w-10 place-items-center rounded-full border border-neutral-200 hover:border-ink" aria-label="Mois suivant"><x-icon name="chevron-right" class="h-4 w-4" /></a>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $d)<span class="py-1">{{ $d }}</span>@endforeach
            </div>
            <div class="grid grid-cols-7 gap-1">
                @foreach ($days as $day)
                    @php
                        $key = $day['date']->toDateString();
                        $isSelected = $selected && $selected['date']->toDateString() === $key;
                    @endphp
                    <a href="{{ route('admin.calendar', ['month' => $first->format('Y-m'), 'day' => $key]) }}"
                        @class([
                            'flex min-h-[4.5rem] flex-col rounded-xl border p-1.5 text-left text-xs transition sm:min-h-[6rem] sm:p-2',
                            'opacity-40' => ! $day['in_month'],
                            'border-ink ring-2 ring-brand-500/40' => $isSelected,
                            'border-neutral-200 bg-white hover:border-brand-400' => ! $isSelected && $day['config']['open'],
                            'border-neutral-100 bg-neutral-100 text-neutral-400' => ! $isSelected && ! $day['config']['open'],
                        ])>
                        <span class="flex items-center justify-between">
                            <span class="font-semibold {{ $day['date']->isToday() ? 'rounded bg-brand-500 px-1 text-ink' : '' }}">{{ $day['date']->day }}</span>
                            @if ($day['config']['overridden'])<span class="h-1.5 w-1.5 rounded-full bg-brand-500" title="Réglage spécifique"></span>@endif
                        </span>
                        @if (! $day['config']['open'])
                            <span class="mt-auto hidden text-[10px] sm:block">Fermé</span>
                        @endif
                        @foreach ($day['bookings']->take(3) as $b)
                            <span class="mt-1 truncate rounded px-1 py-0.5 text-[10px] {{ $b->status->value === 'CONFIRMED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $b->starts_at->format('H:i') }}<span class="hidden sm:inline"> {{ $b->business->name }}</span></span>
                        @endforeach
                        @if ($day['bookings']->count() > 3)<span class="text-[10px] text-neutral-500">+{{ $day['bookings']->count() - 3 }}</span>@endif
                        @if ($day['blocks']->isNotEmpty())<span class="mt-1 truncate rounded bg-neutral-800 px-1 py-0.5 text-[10px] text-white">{{ $day['blocks']->count() }} bloc.</span>@endif
                    </a>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap gap-4 text-xs text-neutral-500">
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-100"></span>Confirmée</span>
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-amber-100"></span>En attente</span>
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-neutral-800"></span>Blocage</span>
                <span class="flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>Réglage spécifique</span>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.settings.edit') }}" class="ml-auto text-brand-700 hover:text-ink">Horaires hebdomadaires →</a>
                @endif
            </div>
        </section>

        <aside class="space-y-4">
            @if (! $selected)
                <div class="card p-6 text-sm text-neutral-500">Sélectionnez un jour pour l'ouvrir, le fermer, modifier ses horaires ou bloquer un créneau.</div>
            @else
                @php($cfg = $selected['config'])
                <section class="card p-5 sm:p-6">
                    <h2 class="font-display text-lg text-ink">{{ date_fr($selected['date']) }}</h2>
                    <p class="text-sm text-neutral-500">{{ $cfg['open'] ? 'Ouvert de '.$cfg['opens'].' à '.$cfg['closes'] : 'Fermé' }}{{ $cfg['note'] ? ' · '.$cfg['note'] : '' }}</p>

                    <form method="POST" action="{{ route('admin.calendar.day') }}" class="mt-5 space-y-3" x-data="{ state: '{{ $override?->is_open === null ? 'default' : ($override->is_open ? 'open' : 'closed') }}' }">
                        @csrf
                        <input type="hidden" name="date" value="{{ $selected['date']->toDateString() }}">
                        <div class="grid grid-cols-3 gap-1 rounded-xl bg-neutral-100 p-1 text-sm">
                            @foreach (['default' => 'Par défaut', 'open' => 'Ouvrir', 'closed' => 'Fermer'] as $v => $l)
                                <label class="cursor-pointer rounded-lg py-2 text-center" :class="state === '{{ $v }}' ? 'bg-white font-semibold shadow-sm' : 'text-neutral-500'">
                                    <input type="radio" name="state" value="{{ $v }}" x-model="state" class="sr-only">{{ $l }}
                                </label>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-2 gap-2" x-show="state !== 'closed'">
                            <label class="text-xs text-neutral-500">Ouverture<input type="time" name="opens_at" value="{{ $override?->opens_at ? substr($override->opens_at, 0, 5) : '' }}" class="field-sm mt-1 w-full"></label>
                            <label class="text-xs text-neutral-500">Fermeture<input type="time" name="closes_at" value="{{ $override?->closes_at ? substr($override->closes_at, 0, 5) : '' }}" class="field-sm mt-1 w-full"></label>
                        </div>
                        <p class="text-xs text-neutral-400" x-show="state !== 'closed'">Laissez vide pour reprendre les horaires par défaut ({{ setting('opening_time') }} – {{ setting('closing_time') }}).</p>
                        <input name="note" value="{{ $override?->note }}" placeholder="Note (ex. jour férié)" class="field-sm w-full" maxlength="255">
                        <button class="btn-primary btn-sm w-full">Enregistrer la journée</button>
                    </form>
                </section>

                <section class="card p-5 sm:p-6">
                    <h3 class="font-semibold text-ink">Bloquer un créneau</h3>
                    <form method="POST" action="{{ route('admin.calendar.blocks.store') }}" class="mt-3 space-y-3">
                        @csrf
                        <input type="hidden" name="date" value="{{ $selected['date']->toDateString() }}">
                        <div class="grid grid-cols-2 gap-2">
                            <label class="text-xs text-neutral-500">De<input type="time" name="start" required class="field-sm mt-1 w-full"></label>
                            <label class="text-xs text-neutral-500">À<input type="time" name="end" required class="field-sm mt-1 w-full"></label>
                        </div>
                        <input name="reason" placeholder="Motif (maintenance, émission spéciale…)" class="field-sm w-full" maxlength="255">
                        <button class="btn-outline btn-sm w-full">Bloquer</button>
                    </form>
                    @if ($selected['blocks']->isNotEmpty())
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($selected['blocks'] as $block)
                                <li class="flex items-center justify-between rounded-lg bg-neutral-50 px-3 py-2">
                                    <span>{{ $block->starts_at->format('H:i') }} – {{ $block->ends_at->format('H:i') }} <span class="text-neutral-500">{{ $block->reason }}</span></span>
                                    <form method="POST" action="{{ route('admin.calendar.blocks.destroy', $block) }}">@csrf @method('DELETE')<button class="text-xs text-red-600 hover:underline">Retirer</button></form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card p-5 sm:p-6">
                    <h3 class="font-semibold text-ink">Réservations du jour</h3>
                    <ul class="mt-3 space-y-2">
                        @forelse ($selected['bookings'] as $b)
                            <li><a href="{{ route('admin.bookings.show', $b) }}" class="flex items-center justify-between gap-2 rounded-lg border border-neutral-100 px-3 py-2 text-sm hover:border-brand-400">
                                <span><strong>{{ $b->time_range }}</strong> · {{ $b->business->name }}<br><span class="text-xs text-neutral-500">{{ $b->package->name }} · {{ $b->mode->shortLabel() }}</span></span>
                                <span class="badge {{ $b->status->badgeClasses() }}">{{ $b->status->label() }}</span>
                            </a></li>
                        @empty
                            <li class="text-sm text-neutral-500">Aucune réservation.</li>
                        @endforelse
                    </ul>
                </section>
            @endif
        </aside>
    </div>
@endsection
