@props(['dark' => false])
{{-- Logotype fidèle au panneau du studio : "OCD" en orange, "Live" en noir (blanc sur fond sombre) --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="leading-none">
        <span class="flex items-baseline gap-1.5">
            <span class="font-display text-2xl font-black italic tracking-tight text-brand-600">OCD</span>
            <span class="font-display text-2xl font-black tracking-tight {{ $dark ? 'text-white' : 'text-ink' }}">Live</span>
        </span>
        <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.12em] {{ $dark ? 'text-slate-400' : 'text-slate-500' }}">Les Opportunités C'est Dehors</span>
    </span>
</span>
