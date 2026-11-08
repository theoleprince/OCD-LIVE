@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    {{-- Monogramme OCD + onglet LIVE --}}
    <span class="relative inline-flex h-11 items-center rounded-xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent-500 px-2.5 shadow-lg shadow-brand-600/30">
        <span class="font-display text-[17px] leading-none tracking-tight text-white">OCD</span>
        <span class="absolute -bottom-2 -right-2 inline-flex items-center gap-1 rounded-md bg-accent-500 px-1.5 py-0.5 text-[9px] font-extrabold tracking-wider text-white ring-2 {{ $dark ? 'ring-navy' : 'ring-white' }}">
            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse-soft"></span>LIVE
        </span>
    </span>
    <span class="leading-none">
        <span class="block font-display text-lg {{ $dark ? 'text-white' : 'text-ink' }}">OCD <span class="text-brand-600 {{ $dark ? '!text-brand-300' : '' }}">Live</span></span>
        <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.12em] {{ $dark ? 'text-slate-400' : 'text-slate-500' }}">Les Opportunités C'est Dehors</span>
    </span>
</span>
