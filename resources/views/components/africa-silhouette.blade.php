{{-- Silhouette décorative du continent africain, rétro-éclairée — écho du mur du studio OCD Live --}}
@props(['class' => 'h-64 w-64'])
<div {{ $attributes->merge(['class' => 'pointer-events-none relative '.$class]) }} aria-hidden="true">
    <svg viewBox="0 0 100 100" class="absolute inset-0 h-full w-full scale-110 text-accent-400/40 blur-2xl" fill="currentColor">
        <path d="M38,4 C50,2 62,4 68,10 C72,16 70,22 75,26 C82,30 88,34 86,42 C84,50 76,52 78,60 C80,68 74,74 70,80 C66,86 60,90 56,94 C52,90 48,92 44,88 C40,84 42,78 36,74 C30,70 28,64 26,58 C24,52 28,48 24,42 C20,36 14,34 12,28 C10,22 16,20 14,14 C12,8 20,10 24,6 C28,2 34,6 38,4 Z" />
    </svg>
    <svg viewBox="0 0 100 100" class="relative h-full w-full text-sand-100" fill="currentColor">
        <path d="M38,4 C50,2 62,4 68,10 C72,16 70,22 75,26 C82,30 88,34 86,42 C84,50 76,52 78,60 C80,68 74,74 70,80 C66,86 60,90 56,94 C52,90 48,92 44,88 C40,84 42,78 36,74 C30,70 28,64 26,58 C24,52 28,48 24,42 C20,36 14,34 12,28 C10,22 16,20 14,14 C12,8 20,10 24,6 C28,2 34,6 38,4 Z" />
        <ellipse cx="82" cy="71" rx="3.2" ry="8.5" transform="rotate(20 82 71)" />
    </svg>
</div>
