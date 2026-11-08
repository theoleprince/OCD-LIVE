@extends('layouts.public')

@section('title', $title)
@section('description', $title.' de la plateforme OCD Live — OCD, Les Opportunités C\'est Dehors.')

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-24">
        <p class="eyebrow">Informations légales</p>
        <h1 class="section-title">{{ $title }}</h1>
        <p class="mt-3 text-sm text-neutral-500">Dernière mise à jour : {{ date_fr(\Carbon\Carbon::parse('2026-11-07')) }}. Document à faire valider par un conseil juridique avant le lancement commercial.</p>
        <div class="legal mt-10 space-y-6 leading-relaxed text-neutral-700">
            @include('public.legal.'.$page)
        </div>
    </article>
@endsection
