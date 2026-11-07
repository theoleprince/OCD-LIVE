@props(['label', 'model', 'rows' => 3, 'hint' => null, 'required' => false])
@php($id = 'f-'.str_replace('.', '-', $model))
<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-neutral-700">
        {{ $label }} @if ($required)<span class="text-gold-600">*</span>@endif
    </label>
    <textarea id="{{ $id }}" rows="{{ $rows }}" wire:model="{{ $model }}" {{ $attributes->merge(['class' => 'field w-full']) }}></textarea>
    @if ($hint) <p class="mt-1 text-xs text-neutral-400">{{ $hint }}</p> @endif
    @error($model) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
