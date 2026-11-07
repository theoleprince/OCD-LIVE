@extends('layouts.public')

@section('title', 'Réserver mon Live')
@section('description', 'Réservez votre émission OCD Live en quelques minutes : mode de participation, package, date, horaire et présentation de votre business.')

@section('content')
    <section class="bg-neutral-50 pb-20 pt-10 sm:pt-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <p class="eyebrow">Réservation</p>
                <h1 class="section-title">Réservez votre passage en direct</h1>
                <p class="mt-4 text-neutral-600">Quelques minutes suffisent. Votre créneau est bloqué dès l'envoi, puis confirmé après paiement et validation.</p>
            </div>
            <livewire:booking-wizard />
        </div>
    </section>
@endsection
