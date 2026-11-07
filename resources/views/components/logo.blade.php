@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="relative grid h-9 w-9 place-items-center rounded-full border {{ $dark ? 'border-gold-400/60' : 'border-ink' }}">
        <span class="font-display text-[13px] font-bold tracking-tight {{ $dark ? 'text-white' : 'text-ink' }}">O</span>
        <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-gold-500 ring-2 {{ $dark ? 'ring-ink' : 'ring-white' }} animate-pulse-soft"></span>
    </span>
    <span class="leading-none">
        <span class="block font-display text-lg font-bold tracking-[0.08em] {{ $dark ? 'text-white' : 'text-ink' }}">OCD <span class="text-gold-500">LIVE</span></span>
        <span class="block text-[9px] font-medium uppercase tracking-[0.28em] {{ $dark ? 'text-neutral-400' : 'text-neutral-500' }}">House of Challenge</span>
    </span>
</span>
