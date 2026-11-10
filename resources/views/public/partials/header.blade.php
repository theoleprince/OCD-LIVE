<header x-data="{ open: false, scrolled: false }" x-init="scrolled = window.scrollY > 10" @scroll.window="scrolled = window.scrollY > 10"
    :class="scrolled ? 'shadow-[0_8px_30px_-12px_rgba(15,23,42,0.18)]' : ''"
    class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 backdrop-blur transition-shadow duration-300">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:h-20 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" aria-label="OCD LIVE — accueil"><x-logo /></a>

        <nav class="hidden items-center gap-1 text-sm font-semibold text-slate-600 lg:flex" aria-label="Navigation principale">
            @foreach (['concept' => 'Le concept', 'services' => 'Nos services', 'fonctionnement' => 'Comment ça marche', 'packages' => 'Packages', 'galerie' => 'Galerie', 'faq' => 'FAQ', 'contact' => 'Contact'] as $anchor => $label)
                <a href="{{ route('home') }}#{{ $anchor }}" class="rounded-lg px-2.5 py-2 transition hover:bg-brand-50 hover:text-brand-700">{{ $label }}</a>
            @endforeach
            <a href="{{ route('market.index') }}" class="rounded-lg px-2.5 py-2 transition hover:bg-brand-50 hover:text-brand-700">Market</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('booking') }}" class="btn-accent hidden !py-2.5 sm:inline-flex">Réserver mon Live</a>
            <button type="button" @click="open = !open" class="grid h-11 w-11 place-items-center rounded-xl border border-slate-200 text-ink lg:hidden" :aria-expanded="open" aria-controls="menu-mobile" aria-label="Menu" data-testid="mobile-menu-button">
                <x-icon name="menu" class="h-5 w-5" x-show="!open" />
                <x-icon name="x" class="h-5 w-5" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <div id="menu-mobile" x-show="open" x-cloak x-transition.opacity.duration.200ms @click.outside="open = false" class="border-t border-slate-100 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-7xl flex-col px-4 py-4 text-base font-semibold" aria-label="Navigation mobile">
            @foreach (['concept' => 'Le concept', 'services' => 'Nos services', 'fonctionnement' => 'Comment ça marche', 'packages' => 'Packages', 'galerie' => 'Galerie', 'faq' => 'FAQ', 'contact' => 'Contact'] as $anchor => $label)
                <a href="{{ route('home') }}#{{ $anchor }}" @click="open = false" class="border-b border-slate-100 py-3.5 text-slate-700">{{ $label }}</a>
            @endforeach
            <a href="{{ route('market.index') }}" @click="open = false" class="border-b border-slate-100 py-3.5 text-slate-700">Market</a>
            <a href="{{ route('booking') }}" class="btn-accent mt-5 w-full">Réserver mon Live</a>
        </nav>
    </div>
</header>
