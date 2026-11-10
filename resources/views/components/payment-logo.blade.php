@props(['src' => null, 'label', 'icon' => 'wallet'])
@php($exists = $src && file_exists(public_path($src)))
<div class="flex h-16 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3">
    @if ($exists)
        <img src="{{ asset($src) }}" alt="{{ $label }}" class="max-h-7 w-auto object-contain">
    @else
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-sand-100 text-brand-600"><x-icon :name="$icon" class="h-4.5 w-4.5" /></span>
        <span class="text-sm font-bold leading-tight text-ink">{{ $label }}</span>
    @endif
</div>
