@extends('layouts.public')

@section('title', 'Suivi de ma demande')
@section('description', 'Suivez l\'état de votre demande de diffusion OCD Live Market.')
@section('noindex', 'noindex')

@php
    $status = $submission->status;
    $message = match ($status) {
        \App\Enums\LiveSubmissionStatus::Pending => 'Votre demande a bien été reçue par OCD Live. Notre équipe va examiner votre diffusion avant toute publication.',
        \App\Enums\LiveSubmissionStatus::UnderReview => 'Votre demande est en cours d\'examen par notre équipe de modération.',
        \App\Enums\LiveSubmissionStatus::Approved => 'Félicitations ! Votre LIVE a été validé par OCD Live et est maintenant publié sur notre plateforme.',
        \App\Enums\LiveSubmissionStatus::Rejected => 'Votre demande OCD Live n\'a pas été validée. Motif : '.($submission->rejection_reason ?: 'non précisé').'.',
        \App\Enums\LiveSubmissionStatus::Expired => 'Votre période de visibilité sur OCD Live est terminée. Vous pouvez soumettre un nouveau LIVE.',
        \App\Enums\LiveSubmissionStatus::Cancelled => 'Cette demande a été annulée.',
    };
@endphp

@section('content')
    <section class="bg-neutral-50 py-16 sm:py-20">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="card p-6 sm:p-10">
                <p class="eyebrow">Suivi de la demande {{ $submission->reference }}</p>
                <span class="badge mt-4 {{ $status->badgeClasses() }}">{{ $status->label() }}</span>
                <p class="mt-5 text-lg leading-relaxed text-ink">{{ $message }}</p>

                <dl class="mt-8 divide-y divide-neutral-100 rounded-2xl border border-neutral-200">
                    @foreach ([
                        'Vendeur' => $submission->seller->business_name,
                        'Pays / Ville' => $submission->country->flag.' '.$submission->country->name.' — '.$submission->city->name,
                        'Catégorie' => $submission->category->name,
                        'Compte TikTok' => $submission->account_name,
                        'Soumise le' => $submission->submitted_at?->format('d/m/Y H:i'),
                        'Publiée le' => $submission->published_at?->format('d/m/Y H:i'),
                        'Expire le' => $submission->expires_at?->format('d/m/Y H:i'),
                    ] as $label => $value)
                        @if ($value)
                            <div class="grid gap-1 px-5 py-3.5 sm:grid-cols-3 sm:gap-4">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-neutral-400 sm:text-sm sm:normal-case sm:tracking-normal">{{ $label }}</dt>
                                <dd class="text-ink sm:col-span-2">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                @if ($submission->isPublished())
                    <a href="{{ $submission->live_url }}" target="_blank" rel="noopener" class="btn-accent mt-8 w-full">Voir le LIVE</a>
                @endif

                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('market.submit') }}" class="btn-outline">Soumettre un nouveau LIVE</a>
                    <a href="{{ route('market.index') }}" class="btn-ghost">Explorer les LIVE</a>
                </div>
            </div>
        </div>
    </section>
@endsection
