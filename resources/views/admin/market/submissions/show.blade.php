@extends('layouts.admin')

@section('title', 'Demande '.$submission->reference)

@section('actions')
    <a href="{{ route('admin.market.submissions.index') }}" class="btn-outline btn-sm hidden sm:inline-flex">← Liste</a>
@endsection

@section('content')
    @php
        $closed = in_array($submission->status->value, \App\Enums\LiveSubmissionStatus::closed(), true);
        $seller = $submission->seller;
    @endphp

    <div class="card mb-4 flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-lg font-semibold text-ink">{{ $submission->reference }}</span>
                <span class="badge {{ $submission->status->badgeClasses() }}">{{ $submission->status->label() }}</span>
            </div>
            <p class="mt-1 text-sm text-neutral-500">Soumise le {{ $submission->submitted_at?->format('d/m/Y H:i') }} · {{ $submission->country->flag }} {{ $submission->city->name }}, {{ $submission->country->name }} · {{ $submission->category->name }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ $submission->live_url }}" target="_blank" rel="noopener" class="btn-outline btn-sm"><x-icon name="arrow-right" class="h-4 w-4" />Ouvrir le LIVE / profil TikTok</a>
        </div>
    </div>

    @unless ($closed)
        <div class="card mb-4 p-5 sm:p-6">
            <h2 class="font-semibold text-ink">Actions de modération</h2>
            <p class="mt-1 text-sm text-neutral-500">Vérifiez manuellement le lien avant toute décision : lien accessible, compte correspondant au vendeur, live réellement actif, contenu commercial et conforme.</p>

            <div class="mt-4 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.market.submissions.approve', $submission) }}">
                    @csrf<button class="btn-accent btn-sm">Approuver</button>
                </form>

                <details class="relative">
                    <summary class="btn-outline btn-sm inline-flex cursor-pointer list-none select-none">Mettre en attente</summary>
                    <form method="POST" action="{{ route('admin.market.submissions.hold', $submission) }}" class="absolute z-10 mt-2 w-72 space-y-2 rounded-xl border border-neutral-200 bg-white p-4 shadow-xl">
                        @csrf
                        <textarea name="comment" rows="2" placeholder="Commentaire interne (facultatif)" class="field-sm w-full"></textarea>
                        <button class="btn-primary btn-sm w-full">Confirmer la mise en attente</button>
                    </form>
                </details>

                <details class="relative">
                    <summary class="btn-danger btn-sm inline-flex cursor-pointer list-none select-none">Refuser</summary>
                    <form method="POST" action="{{ route('admin.market.submissions.reject', $submission) }}" class="absolute z-10 mt-2 w-72 space-y-2 rounded-xl border border-neutral-200 bg-white p-4 shadow-xl">
                        @csrf
                        <input name="reason" required placeholder="Motif du refus (visible par le vendeur)" class="field-sm w-full" maxlength="255">
                        <textarea name="comment" rows="2" placeholder="Commentaire interne (facultatif)" class="field-sm w-full"></textarea>
                        <button class="btn-danger btn-sm w-full">Confirmer le refus</button>
                    </form>
                </details>
            </div>
        </div>
    @endunless

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Vendeur</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-neutral-500">Nom et prénom</dt><dd class="font-medium text-ink">{{ $seller->full_name }}</dd></div>
                    <div><dt class="text-neutral-500">Nom commercial</dt><dd>{{ $seller->business_name }}</dd></div>
                    <div><dt class="text-neutral-500">Téléphone / WhatsApp</dt><dd><a href="tel:{{ $seller->phone }}" class="hover:underline">{{ $seller->phone }}</a></dd></div>
                    <div><dt class="text-neutral-500">Pays / ville</dt><dd>{{ $submission->country->name }}, {{ $submission->city->name }}</dd></div>
                    @if ($seller->description)<div class="sm:col-span-2"><dt class="text-neutral-500">Description</dt><dd class="whitespace-pre-line">{{ $seller->description }}</dd></div>@endif
                </dl>
                @if ($seller->profile_image || $seller->logo_image)
                    <div class="mt-4 flex gap-3">
                        @if ($seller->profile_image)<img src="{{ asset('storage/'.$seller->profile_image) }}" alt="Photo de profil" class="h-20 w-20 rounded-xl border border-neutral-200 object-cover">@endif
                        @if ($seller->logo_image)<img src="{{ asset('storage/'.$seller->logo_image) }}" alt="Logo" class="h-20 w-20 rounded-xl border border-neutral-200 object-cover">@endif
                    </div>
                @endif
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Live</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-neutral-500">Plateforme</dt><dd>{{ ucfirst($submission->platform) }}</dd></div>
                    <div><dt class="text-neutral-500">Compte</dt><dd>{{ $submission->account_name }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-neutral-500">Lien</dt><dd class="break-all"><a href="{{ $submission->live_url }}" target="_blank" rel="noopener" class="text-brand-700 hover:underline">{{ $submission->live_url }}</a></dd></div>
                    <div><dt class="text-neutral-500">Catégorie</dt><dd>{{ $submission->category->name }}{{ $submission->subcategory ? ' — '.$submission->subcategory->name : '' }}</dd></div>
                    <div><dt class="text-neutral-500">Créneau souhaité</dt><dd>{{ $submission->schedule?->label ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-neutral-500">Description du produit / service</dt><dd class="whitespace-pre-line">{{ $submission->product_description }}</dd></div>
                </dl>
            </section>

            @if ($submission->status === \App\Enums\LiveSubmissionStatus::Rejected)
                <section class="card p-5 sm:p-6"><h2 class="font-semibold text-ink">Motif du refus</h2><p class="mt-2 text-sm">{{ $submission->rejection_reason }}</p></section>
            @endif
        </div>

        <div class="space-y-4">
            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Commentaire interne</h2>
                <p class="text-xs text-neutral-400">Jamais visible publiquement ni par le vendeur.</p>
                <form method="POST" action="{{ route('admin.market.submissions.comment', $submission) }}" class="mt-3 space-y-2">
                    @csrf
                    <textarea name="comment" rows="4" class="field-sm w-full">{{ old('comment', $submission->internal_comment) }}</textarea>
                    <button class="btn-outline btn-sm w-full">Enregistrer</button>
                </form>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold text-ink">Historique</h2>
                <ol class="mt-4 space-y-3 border-l border-neutral-200 pl-4 text-sm">
                    <li><p class="font-medium">Demande créée</p><p class="text-xs text-neutral-500">{{ $submission->submitted_at?->format('d/m/Y H:i') }}</p></li>
                    @foreach ($history->reverse() as $log)
                        <li><p class="font-medium">{{ __('logs.'.$log->action) }}</p><p class="text-xs text-neutral-500">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->user?->name ?? 'Système' }}</p></li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
@endsection
