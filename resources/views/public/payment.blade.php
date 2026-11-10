@extends('layouts.public')

@section('title', 'Paiement de la réservation '.$booking->reference)
@section('noindex', 'noindex')

@section('content')
    <section class="bg-neutral-50 pb-20 pt-10 sm:pt-14">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 max-w-2xl text-center">
                <p class="eyebrow">Paiement</p>
                <h1 class="section-title">Réglez votre réservation</h1>
                <p class="mt-4 text-neutral-600">Bonjour {{ $booking->customer->first_name }}, votre émission du {{ date_fr($booking->starts_at) }} ({{ $booking->time_range }}, heure de Lomé) est confirmée dès réception du paiement.</p>
            </div>

            @if (session('payment_status') === 'failed')
                <p class="mx-auto mb-6 max-w-2xl rounded-xl bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">Le paiement n'a pas abouti (refusé ou annulé). Aucun montant n'a été débité : vous pouvez réessayer ou choisir un autre mode.</p>
            @elseif (session('payment_status') === 'pending')
                <p class="mx-auto mb-6 max-w-2xl rounded-xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200">Votre paiement est en cours de traitement. Cette page affichera la confirmation dès qu'il sera validé ; vous pouvez l'actualiser dans quelques instants.</p>
            @endif

            <div class="card p-5 sm:p-8">
                <livewire:booking-payment :token="$booking->payment_token" />
            </div>
        </div>
    </section>
@endsection
