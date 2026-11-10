@extends('layouts.public')

@section('title', 'Soumettre mon LIVE')
@section('description', 'Soumettez votre diffusion TikTok à OCD Live Market. Après vérification par notre équipe, votre LIVE est publié dans l\'annuaire panafricain des commerçants en direct.')

@section('content')
    <section class="bg-neutral-50 pb-20 pt-10 sm:pt-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <p class="eyebrow justify-center">OCD LIVE MARKET</p>
                <h1 class="section-title">Soumettre mon LIVE</h1>
                <p class="mt-4 text-neutral-600">Renseignez vos informations, notre équipe vérifie manuellement votre diffusion avant toute publication.</p>
            </div>
            <livewire:live-submission-form />
        </div>
    </section>
@endsection
