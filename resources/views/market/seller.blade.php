@extends('layouts.public')

@section('title', $seller->business_name.' — OCD LIVE MARKET')
@section('description', $seller->description ?: ($seller->business_name.' sur OCD Live Market, '.$seller->city->name.', '.$seller->country->name.'.'))

@section('content')
    <section class="bg-neutral-50 py-14 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('market.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm font-semibold text-neutral-500 hover:text-ink"><x-icon name="chevron-left" class="h-4 w-4" />Explorer les LIVE</a>

            <div class="card overflow-hidden">
                <div class="relative aspect-[16/7] bg-gradient-to-br from-sand-100 to-sand-200">
                    @if ($seller->profile_image)
                        <img src="{{ asset('storage/'.$seller->profile_image) }}" alt="{{ $seller->business_name }}" class="h-full w-full object-cover">
                    @endif
                </div>
                <div class="p-6 sm:p-8">
                    <h1 class="font-display text-3xl text-ink">{{ $seller->business_name }}</h1>
                    <p class="mt-1 text-neutral-500">{{ $seller->full_name }}</p>
                    <p class="mt-2 flex items-center gap-1.5 text-sm text-neutral-500"><x-icon name="map" class="h-4 w-4" />{{ $seller->country->flag }} {{ $seller->city->name }}, {{ $seller->country->name }}</p>
                    @if ($seller->description)
                        <p class="mt-5 leading-relaxed text-neutral-600">{{ $seller->description }}</p>
                    @endif

                    <h2 class="mt-8 font-display text-xl text-ink">Lives en cours</h2>
                    @if ($lives->isEmpty())
                        <p class="mt-3 text-neutral-500">Aucun LIVE en direct pour le moment.</p>
                    @else
                        <div class="mt-4 grid gap-6 sm:grid-cols-2">
                            @foreach ($lives as $live)
                                <x-market.live-card :live="$live" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
