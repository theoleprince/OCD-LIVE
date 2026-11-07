@props(['step', 'title', 'subtitle' => null])
<div class="mb-8">
    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-600">Étape {{ $step }} / 8</p>
    <h2 class="mt-2 font-display text-2xl text-ink sm:text-3xl">{{ $title }}</h2>
    @if ($subtitle)
        <p class="mt-2 text-sm text-neutral-500">{{ $subtitle }}</p>
    @endif
</div>
