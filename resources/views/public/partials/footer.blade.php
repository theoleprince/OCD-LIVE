<footer class="bg-ink text-neutral-400">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 md:grid-cols-12">
            <div class="md:col-span-5">
                <x-logo dark />
                <p class="mt-6 max-w-sm text-sm leading-relaxed">La plateforme panafricaine de présentation commerciale en direct. Présentez. Faites connaître. Vendez. Développez votre business.</p>
                <a href="{{ route('booking') }}" class="btn-gold mt-8">Réserver mon Live</a>
            </div>
            <div class="md:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white">Navigation</p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li><a href="{{ route('home') }}#packages" class="hover:text-gold-300">Nos packages</a></li>
                    <li><a href="{{ route('booking', ['mode' => 'onsite']) }}" class="hover:text-gold-300">Live en présentiel</a></li>
                    <li><a href="{{ route('booking', ['mode' => 'remote']) }}" class="hover:text-gold-300">Live à distance</a></li>
                    <li><a href="{{ route('home') }}#faq" class="hover:text-gold-300">Questions fréquentes</a></li>
                </ul>
            </div>
            <div class="md:col-span-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white">Contact</p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li class="flex gap-3"><x-icon name="map" class="h-5 w-5 shrink-0 text-gold-500" />{{ setting('contact_address') }}</li>
                    <li class="flex gap-3"><x-icon name="phone" class="h-5 w-5 shrink-0 text-gold-500" /><a href="tel:{{ preg_replace('/[^\d+]/', '', setting('contact_phone')) }}" class="hover:text-gold-300">{{ setting('contact_phone') }}</a></li>
                    <li class="flex gap-3"><x-icon name="mail" class="h-5 w-5 shrink-0 text-gold-500" /><a href="mailto:{{ setting('contact_email') }}" class="hover:text-gold-300">{{ setting('contact_email') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} OCD Live — House of Challenge. Tous droits réservés.</p>
            <div class="flex gap-6">
                <a href="{{ route('legal.terms') }}" class="hover:text-gold-300">Conditions générales</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-gold-300">Confidentialité</a>
            </div>
        </div>
    </div>
</footer>
