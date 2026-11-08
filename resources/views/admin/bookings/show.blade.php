@extends('layouts.admin')

@section('title', 'Réservation '.$booking->reference)

@section('actions')
    <a href="{{ route('admin.bookings.index') }}" class="btn-outline btn-sm hidden sm:inline-flex">← Liste</a>
@endsection

@section('content')
    @php
        $c = $booking->customer;
        $biz = $booking->business;
        $paid = $booking->payments->contains(fn ($p) => $p->status === \App\Enums\PaymentStatus::Paid);
        $hasPending = $booking->payments->contains(fn ($p) => $p->status === \App\Enums\PaymentStatus::Pending);
        $closed = in_array($booking->status, [\App\Enums\BookingStatus::Cancelled, \App\Enums\BookingStatus::Completed], true);
    @endphp

    {{-- En-tête --}}
    <div class="card mb-4 flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-lg font-semibold text-ink">{{ $booking->reference }}</span>
                <span class="badge {{ $booking->status->badgeClasses() }}">{{ $booking->status->label() }}</span>
                @if ($booking->is_demo)<span class="badge bg-amber-50 text-amber-700 ring-amber-200">DONNÉE DE DÉMONSTRATION</span>@endif
            </div>
            <p class="mt-1 text-sm text-neutral-500">{{ date_fr($booking->starts_at) }} · {{ $booking->time_range }} (Lomé) · {{ $booking->package->name }} · {{ money_fcfa($booking->amount) }}</p>
            @if ($booking->participant_time_range)<p class="text-sm text-neutral-500">Heure du participant : {{ $booking->participant_time_range }}</p>@endif
        </div>

        @unless ($closed)
            <div class="flex flex-wrap gap-2">
                @if ($booking->status === \App\Enums\BookingStatus::Confirmed)
                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">@csrf<button class="btn-primary btn-sm">Marquer terminée</button></form>
                @else
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">@csrf
                        <button class="btn-accent btn-sm" @disabled(! $paid) title="{{ $paid ? '' : 'Enregistrez d\'abord le paiement' }}">Confirmer la réservation</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" x-data onsubmit="const r = prompt('Motif de l\'annulation (facultatif) :'); if (r === null) return false; this.reason.value = r;">
                    @csrf<input type="hidden" name="reason"><button class="btn-danger btn-sm">Annuler</button>
                </form>
            </div>
        @endunless
    </div>
    @if (! $paid && ! $closed)
        <p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">La confirmation n'est possible qu'après l'enregistrement d'un paiement réellement reçu.</p>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            {{-- Client --}}
            <section class="card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-ink">Client</h2>
                    <a href="{{ route('admin.customers.show', $c) }}" class="text-sm text-brand-700 hover:text-ink">Fiche client →</a>
                </div>
                <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-neutral-500">Nom</dt><dd class="font-medium text-ink">{{ $c->full_name }}</dd></div>
                    <div><dt class="text-neutral-500">Pays / ville</dt><dd>{{ $c->country }}, {{ $c->city }}</dd></div>
                    <div><dt class="text-neutral-500">Téléphone</dt><dd><a href="tel:{{ $c->phone }}" class="hover:underline">{{ $c->phone }}</a></dd></div>
                    <div><dt class="text-neutral-500">WhatsApp</dt><dd><a href="{{ $c->whatsapp_url }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline">{{ $c->whatsapp }}</a></dd></div>
                    <div class="sm:col-span-2"><dt class="text-neutral-500">E-mail</dt><dd><a href="mailto:{{ $c->email }}" class="hover:underline">{{ $c->email }}</a></dd></div>
                </dl>
            </section>

            {{-- Distance --}}
            <section class="card p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-semibold text-ink">
                    <x-icon :name="$booking->mode->value === 'REMOTE' ? 'globe' : 'studio'" class="h-5 w-5 text-brand-600" />{{ $booking->mode->label() }}
                </h2>
                @if ($booking->mode->value === 'REMOTE')
                    <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-neutral-500">Fuseau</dt><dd>{{ $booking->participant_timezone }}</dd></div>
                        <div><dt class="text-neutral-500">Connexion</dt><dd>{{ config('ocd.connection_types.'.$booking->connection_type, '—') }}</dd></div>
                        <div><dt class="text-neutral-500">Assistance</dt><dd>{{ $booking->needs_assistance ? 'Oui' : 'Non' }}</dd></div>
                        @if ($booking->assistance_notes)<div class="sm:col-span-2"><dt class="text-neutral-500">Besoin exprimé</dt><dd class="whitespace-pre-line">{{ $booking->assistance_notes }}</dd></div>@endif
                    </dl>
                @else
                    <p class="mt-2 text-sm text-neutral-500">Le client se présente au studio de Lomé.</p>
                @endif
            </section>

            {{-- Business --}}
            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Business</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-neutral-500">Nom</dt><dd class="font-medium text-ink">{{ $biz->name }}</dd></div>
                    <div><dt class="text-neutral-500">Secteur</dt><dd>{{ $biz->sector }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-neutral-500">Description</dt><dd class="whitespace-pre-line">{{ $biz->description }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-neutral-500">Produits / services</dt><dd class="whitespace-pre-line">{{ $biz->offer_nature }}</dd></div>
                    <div><dt class="text-neutral-500">Téléphone commercial</dt><dd>{{ $biz->business_phone }}</dd></div>
                    <div><dt class="text-neutral-500">Adresse commerciale</dt><dd>{{ $biz->business_address }}</dd></div>
                    @foreach ($biz->social_links ?? [] as $network => $value)
                        @if ($value)<div><dt class="text-neutral-500">{{ ucfirst($network) }}</dt><dd class="break-all">{{ $value }}</dd></div>@endif
                    @endforeach
                    <div><dt class="text-neutral-500">Produits à présenter</dt><dd>{{ $booking->products_count }}</dd></div>
                </dl>

                @if ($biz->products->isNotEmpty())
                    <table class="table-admin mt-5 rounded-xl border border-neutral-100">
                        <thead><tr><th>Produit</th><th class="text-right">Quantité disponible</th></tr></thead>
                        <tbody>@foreach ($biz->products as $p)<tr><td>{{ $p->name }}</td><td class="text-right">{{ $p->quantity_available ?? '—' }}</td></tr>@endforeach</tbody>
                    </table>
                @endif

                @if ($biz->is_food)
                    <div class="mt-5 rounded-xl bg-brand-50 p-4 text-sm">
                        <p class="font-semibold text-ink">Produits alimentaires</p>
                        <p class="mt-2"><span class="text-neutral-500">Préparation :</span> {{ $biz->food_preparation ?: '—' }}</p>
                        <p class="mt-1"><span class="text-neutral-500">Conservation :</span> {{ $biz->food_conservation ?: '—' }}</p>
                        <p class="mt-1"><span class="text-neutral-500">Présentation :</span> {{ $biz->food_presentation ?: '—' }}</p>
                    </div>
                @endif

                @if ($booking->attachments->isNotEmpty())
                    <p class="mt-6 text-sm font-semibold text-ink">Fichiers transmis</p>
                    <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-5">
                        @foreach ($booking->attachments as $file)
                            <a href="{{ route('admin.bookings.attachment', [$booking, $file]) }}" target="_blank" class="group block">
                                <img src="{{ route('admin.bookings.attachment', [$booking, $file]) }}" alt="{{ $file->original_name }}" loading="lazy" class="aspect-square w-full rounded-xl border border-neutral-200 object-cover group-hover:border-brand-500">
                                <span class="mt-1 block truncate text-[11px] text-neutral-500">{{ $file->type === 'logo' ? 'Logo' : 'Photo' }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            @if ($booking->customer_notes)
                <section class="card p-5 sm:p-6">
                    <h2 class="font-semibold text-ink">Message du client</h2>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $booking->customer_notes }}</p>
                </section>
            @endif
        </div>

        <div class="space-y-4">
            {{-- Paiements --}}
            <section class="card p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-semibold text-ink"><x-icon name="wallet" class="h-5 w-5 text-brand-600" />Paiements</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($booking->payments as $payment)
                        <li class="rounded-xl border border-neutral-100 p-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium">{{ money_fcfa($payment->amount) }}</span>
                                <span class="badge {{ $payment->status->badgeClasses() }}">{{ $payment->status->label() }}</span>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500">
                                {{ \App\Models\Payment::methodLabels()[$payment->method] ?? 'Mode non renseigné' }}
                                @if ($payment->provider_reference) · réf. {{ $payment->provider_reference }} @endif
                                @if ($payment->paid_at) · {{ $payment->paid_at->format('d/m/Y H:i') }} @endif
                                @if ($payment->recorder) · par {{ $payment->recorder->name }} @endif
                            </p>
                            @if (! empty($payment->meta['note']))<p class="mt-1 text-xs text-neutral-500">{{ $payment->meta['note'] }}</p>@endif
                            @if ($payment->status === \App\Enums\PaymentStatus::Paid && auth()->user()->role === \App\Enums\UserRole::Admin)
                                <form method="POST" action="{{ route('admin.bookings.refund', [$booking, $payment]) }}" onsubmit="return confirm('Marquer ce paiement comme remboursé ?')" class="mt-2">@csrf
                                    <button class="text-xs text-red-600 hover:underline">Marquer remboursé</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if ($booking->payment_due_at && $booking->status === \App\Enums\BookingStatus::PendingPayment)
                    <p class="mt-3 text-xs text-neutral-500">Créneau retenu jusqu'au {{ $booking->payment_due_at->format('d/m/Y à H:i') }}.</p>
                @endif

                @if (! $closed && ! $paid)
                    <form method="POST" action="{{ route('admin.bookings.payment', $booking) }}" class="mt-5 space-y-3 border-t border-neutral-100 pt-5">
                        @csrf
                        <p class="text-sm font-semibold text-ink">Enregistrer un paiement reçu</p>
                        <select name="method" required class="field-sm w-full" aria-label="Moyen de paiement">
                            @foreach (\App\Models\Payment::methodLabels() as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                        <input name="reference" placeholder="Référence de transaction" class="field-sm w-full" maxlength="120">
                        <input name="note" placeholder="Note (facultatif)" class="field-sm w-full" maxlength="500">
                        <label class="flex items-start gap-2 text-xs text-neutral-600"><input type="checkbox" name="confirm_received" value="1" required class="mt-0.5 rounded">Je certifie avoir vérifié la réception de {{ money_fcfa($booking->amount) }}.</label>
                        <button class="btn-primary btn-sm w-full">Enregistrer le paiement</button>
                    </form>
                    @if ($hasPending)
                        <form method="POST" action="{{ route('admin.bookings.payment-failed', $booking) }}" class="mt-2" onsubmit="return confirm('Marquer la tentative de paiement comme échouée ?')">@csrf
                            <button class="w-full text-center text-xs text-neutral-500 hover:text-red-600">Signaler un paiement échoué</button>
                        </form>
                    @endif
                @endif
            </section>

            {{-- Notes internes --}}
            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Suivi interne</h2>
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="mt-4 space-y-3">
                    @csrf @method('PATCH')
                    <label class="block text-sm text-neutral-600" for="admin_notes">Notes</label>
                    <textarea id="admin_notes" name="admin_notes" rows="4" class="field-sm w-full">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                    <label class="block text-sm text-neutral-600" for="stream_link">Lien de connexion / diffusion</label>
                    <input id="stream_link" name="stream_link" type="url" value="{{ old('stream_link', $booking->stream_link) }}" placeholder="https://…" class="field-sm w-full">
                    <p class="text-xs text-neutral-400">Saisie manuelle : aucune intégration automatique avec un outil de visioconférence n'est encore configurée.</p>
                    <button class="btn-outline btn-sm w-full">Enregistrer</button>
                </form>
            </section>

            {{-- Historique --}}
            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Historique</h2>
                <ol class="mt-4 space-y-3 border-l border-neutral-200 pl-4 text-sm">
                    <li><p class="font-medium">Demande créée</p><p class="text-xs text-neutral-500">{{ $booking->created_at->format('d/m/Y H:i') }}</p></li>
                    @foreach ($history->reverse() as $log)
                        <li><p class="font-medium">{{ __('logs.'.$log->action) }}</p><p class="text-xs text-neutral-500">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->user?->name ?? 'Système' }}</p></li>
                    @endforeach
                    @if ($booking->cancellation_reason)<li><p class="text-xs text-neutral-500">Motif d'annulation : {{ $booking->cancellation_reason }}</p></li>@endif
                </ol>
            </section>
        </div>
    </div>
@endsection
