@props(['src', 'alt', 'ratio' => 'aspect-[4/3]'])
@php($exists = file_exists(public_path($src)))
<div {{ $attributes->merge(['class' => $ratio.' relative overflow-hidden rounded-2xl bg-gradient-to-br from-sand-100 to-sand-200']) }}>
    @if ($exists)
        <img src="{{ asset($src) }}" alt="{{ $alt }}" loading="lazy" class="h-full w-full object-cover">
    @else
        <div class="flex h-full w-full flex-col items-center justify-center gap-3 p-6 text-center text-brand-700/70">
            <span class="grid h-12 w-12 place-items-center rounded-xl bg-white/60 text-brand-600"><x-icon name="photos" class="h-6 w-6" /></span>
            <p class="text-xs font-semibold leading-snug">{{ $alt }}</p>
        </div>
    @endif
</div>
