@extends('layouts.admin')

@section('title', $customer->full_name)

@section('content')
    <div class="grid gap-4 lg:grid-cols-3">
        <section class="card p-5 sm:p-6">
            <h2 class="font-semibold text-ink">Coordonnées</h2>
            @if ($customer->is_demo)<p class="mt-2"><span class="badge bg-amber-50 text-amber-700 ring-amber-200">DONNÉE DE DÉMONSTRATION</span></p>@endif
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-neutral-500">Téléphone</dt><dd><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></dd></div>
                <div><dt class="text-neutral-500">WhatsApp</dt><dd><a href="{{ $customer->whatsapp_url }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline">{{ $customer->whatsapp }}</a></dd></div>
                <div><dt class="text-neutral-500">E-mail</dt><dd class="break-all"><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></dd></div>
                <div><dt class="text-neutral-500">Localisation</dt><dd>{{ $customer->city }}, {{ $customer->country }}</dd></div>
                <div><dt class="text-neutral-500">Fuseau horaire</dt><dd>{{ $customer->timezone }}</dd></div>
                <div><dt class="text-neutral-500">Client depuis</dt><dd>{{ $customer->created_at->format('d/m/Y') }}</dd></div>
            </dl>
        </section>

        <div class="space-y-4 lg:col-span-2">
            @foreach ($customer->businesses as $biz)
                <section class="card p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">{{ $biz->sector }}</p>
                    <h2 class="mt-1 font-display text-xl text-ink">{{ $biz->name }}</h2>
                    <p class="mt-3 whitespace-pre-line text-sm text-neutral-600">{{ $biz->description }}</p>
                    <p class="mt-3 text-sm"><span class="text-neutral-500">Coordonnées commerciales :</span> {{ $biz->business_phone }} · {{ $biz->business_address }}</p>
                    @if ($biz->products->isNotEmpty())
                        <p class="mt-4 text-sm font-semibold text-ink">Produits</p>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($biz->products as $p)
                                <li class="rounded-full bg-neutral-100 px-3 py-1 text-xs">{{ $p->name }}@if ($p->quantity_available !== null) · {{ $p->quantity_available }} dispo @endif</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach

            <section class="card overflow-hidden">
                <h2 class="p-5 pb-3 font-semibold text-ink sm:px-6">Historique des réservations</h2>
                <div class="overflow-x-auto">
                    <table class="table-admin">
                        <thead><tr><th>Référence</th><th>Date</th><th>Package</th><th>Mode</th><th class="text-right">Montant</th><th>Statut</th></tr></thead>
                        <tbody>
                            @forelse ($customer->bookings as $b)
                                <tr class="hover:bg-neutral-50">
                                    <td><a href="{{ route('admin.bookings.show', $b) }}" class="font-mono font-semibold hover:text-gold-700">{{ $b->reference }}</a></td>
                                    <td>{{ $b->starts_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $b->package->name }}</td>
                                    <td>{{ $b->mode->shortLabel() }}</td>
                                    <td class="text-right">{{ money_fcfa($b->amount) }}</td>
                                    <td><span class="badge {{ $b->status->badgeClasses() }}">{{ $b->status->label() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-neutral-500">Aucune réservation.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
