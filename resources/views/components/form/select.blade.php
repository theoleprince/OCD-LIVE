@props(['label', 'model', 'live' => false, 'required' => false])
@php($id = 'f-'.str_replace('.', '-', $model))
<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-neutral-700">
        {{ $label }} @if ($required)<span class="text-brand-600">*</span>@endif
    </label>
    <select id="{{ $id }}" {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $model }}" {{ $attributes->merge(['class' => 'field w-full']) }}>
        {{ $slot }}
    </select>
    @error($model) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
