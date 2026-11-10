@props(['src' => null, 'srcs' => [], 'label', 'caption' => null, 'icon' => 'wallet'])
@php($images = array_values(array_filter($src ? [$src] : $srcs, fn ($path) => file_exists(public_path($path)))))
<figure class="flex h-24 flex-col items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3" title="{{ $label }}">
    <div class="flex h-10 items-center justify-center gap-2">
        @forelse ($images as $image)
            <x-payment-image :src="$image" :alt="count($images) === 1 ? $label : ''" />
        @empty
            <span class="grid h-10 w-10 place-items-center rounded-lg bg-sand-100 text-brand-600"><x-icon :name="$icon" class="h-5 w-5" /></span>
        @endforelse
    </div>
    <figcaption class="text-center text-xs font-semibold leading-tight text-ink">{{ $caption ?? $label }}</figcaption>
</figure>
