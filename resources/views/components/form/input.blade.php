@props(['label', 'model', 'type' => 'text', 'hint' => null, 'live' => false, 'required' => false])
@php($id = 'f-'.str_replace('.', '-', $model))
<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-neutral-700">
        {{ $label }} @if ($required)<span class="text-brand-600">*</span>@endif
    </label>
    <input id="{{ $id }}" type="{{ $type }}" {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $model }}"
        {{ $attributes->merge(['class' => 'field w-full']) }} @error($model) aria-invalid="true" @enderror>
    @if ($hint) <p class="mt-1 text-xs text-neutral-400">{{ $hint }}</p> @endif
    @error($model) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
