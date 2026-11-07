<header x-data="{ open: false, scrolled: false }" x-init="scrolled = window.scrollY > 10" @scroll.window="scrolled = window.scrollY > 10"
    :class="scrolled ? 'border-neutral-200 bg-white/90 backdrop-blur-md' : 'border-transparent bg-white'"
    class="sticky top-0 z-40 border-b transition-colors duration-300">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:h-20 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" aria-label="OCD LIVE — accueil"><x-logo /></a>

        <nav class="hidden items-center gap-8 text-sm text-neutral-600 lg:flex" aria-label="Navigation principale">
            <a href="{{ route('home') }}#concept" class="hover:text-ink">Le concept</a>
            <a href="{{ route('home') }}#fonctionnement" class="hover:text-ink">Comment ça marche</a>
            <a href="{{ route('home') }}#packages" class="hover:text-ink">Packages</a>
            <a href="{{ route('home') }}#faq" class="hover:text-ink">FAQ</a>
            <a href="{{ route('home') }}#contact" class="hover:text-ink">Contact</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('booking') }}" class="btn-primary hidden !py-2.5 sm:inline-flex">Réserver mon Live</a>
            <button type="button" @click="open = !open" class="grid h-11 w-11 place-items-center rounded-full border border-neutral-200 lg:hidden" :aria-expanded="open" aria-controls="menu-mobile" aria-label="Menu" data-testid="mobile-menu-button">
                <x-icon name="menu" class="h-5 w-5" x-show="!open" />
                <x-icon name="x" class="h-5 w-5" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <div id="menu-mobile" x-show="open" x-cloak x-transition.opacity.duration.200ms @click.outside="open = false" class="border-t border-neutral-100 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-7xl flex-col px-4 py-4 text-base" aria-label="Navigation mobile">
            @foreach (['concept' => 'Le concept', 'fonctionnement' => 'Comment ça marche', 'packages' => 'Packages', 'faq' => 'FAQ', 'contact' => 'Contact'] as $anchor => $label)
                <a href="{{ route('home') }}#{{ $anchor }}" @click="open = false" class="border-b border-neutral-100 py-3.5 text-neutral-700">{{ $label }}</a>
            @endforeach
            <a href="{{ route('booking') }}" class="btn-primary mt-5 w-full">Réserver mon Live</a>
        </nav>
    </div>
</header>
