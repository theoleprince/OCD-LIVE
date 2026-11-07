@extends('layouts.public')

@section('description', 'Présentez vos produits et services en direct sur les réseaux de House of Challenge. Live en présentiel au studio de Lomé ou à distance depuis toute l\'Afrique. Réservez votre émission OCD Live.')

@section('content')
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(60rem_30rem_at_50%_-10%,rgba(201,162,77,0.16),transparent)]"></div>
        <div class="mx-auto max-w-7xl px-4 pb-16 pt-14 text-center sm:px-6 sm:pt-20 lg:px-8 lg:pb-24">
            <p class="reveal inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-600">
                <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse-soft"></span>Émissions commerciales en direct · dès le 1er décembre 2026
            </p>
            <h1 class="reveal mx-auto mt-7 max-w-4xl font-display text-[2.6rem] leading-[1.05] text-ink sm:text-6xl lg:text-7xl">
                Votre produit mérite <span class="italic text-gold-600">d'être vu.</span>
            </h1>
            <p class="reveal mx-auto mt-6 max-w-2xl text-base leading-relaxed text-neutral-600 sm:text-lg">
                Donnez une nouvelle dimension à votre business grâce aux émissions commerciales OCD Live.
            </p>
            <div class="reveal mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('booking') }}" class="btn-primary">Réserver mon Live <x-icon name="arrow-right" class="h-4 w-4" /></a>
                <a href="#packages" class="btn-outline">Découvrir les packages</a>
            </div>

            {{-- Deux grandes cartes --}}
            <div class="mt-16 grid gap-5 text-left md:grid-cols-2 lg:mt-20">
                <article class="reveal group relative flex flex-col overflow-hidden rounded-3xl bg-ink p-7 text-white sm:p-10">
                    <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gold-500/20 blur-3xl transition-transform duration-700 group-hover:scale-125"></div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl border border-gold-400/40 text-gold-300"><x-icon name="studio" class="h-6 w-6" /></span>
                    <p class="mt-8 text-[11px] font-semibold uppercase tracking-[0.22em] text-gold-400">Studio · Lomé, Togo</p>
                    <h2 class="mt-2 font-display text-2xl sm:text-3xl">Live en présentiel – Lomé</h2>
                    <p class="mt-4 flex-1 leading-relaxed text-neutral-300">Venez dans notre studio à Lomé, présentez vos produits et profitez de notre régie professionnelle pour faire connaître votre activité.</p>
                    <a href="{{ route('booking', ['mode' => 'onsite']) }}" class="btn-gold mt-8 self-start">RÉSERVER EN PRÉSENTIEL</a>
                </article>

                <article class="reveal group relative flex flex-col overflow-hidden rounded-3xl border border-neutral-200 bg-white p-7 sm:p-10">
                    <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gold-200/50 blur-3xl transition-transform duration-700 group-hover:scale-125"></div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-ink text-gold-400"><x-icon name="globe" class="h-6 w-6" /></span>
                    <p class="mt-8 text-[11px] font-semibold uppercase tracking-[0.22em] text-gold-600">Afrique · International</p>
                    <h2 class="mt-2 font-display text-2xl text-ink sm:text-3xl">Live à distance – Afrique et international</h2>
                    <p class="mt-4 flex-1 leading-relaxed text-neutral-600">Vous êtes au Cameroun, au Sénégal, au Tchad, en Centrafrique ou ailleurs ? Participez à nos émissions depuis votre domicile ou votre commerce avec l'accompagnement de notre assistante basée à Lomé.</p>
                    <a href="{{ route('booking', ['mode' => 'remote']) }}" class="btn-primary mt-8 self-start">RÉSERVER À DISTANCE</a>
                </article>
            </div>
        </div>
    </section>

    {{-- CONCEPT --}}
    <section id="concept" class="scroll-mt-20 bg-neutral-50 py-20 sm:py-28">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="reveal">
                <p class="eyebrow">Le concept</p>
                <h2 class="section-title">La vitrine en direct des entrepreneurs africains.</h2>
                <p class="mt-6 leading-relaxed text-neutral-600">OCD Live est une plateforme panafricaine de marketing et de présentation commerciale en direct, portée par <strong class="text-ink">House of Challenge</strong> et animée par <strong class="text-ink">{{ config('ocd.host') }}</strong>.</p>
                <p class="mt-4 leading-relaxed text-neutral-600">Commerçants, restaurateurs, artisans et entreprises présentent leurs produits et services lors d'émissions diffusées sur les réseaux sociaux de House of Challenge. L'objectif : vous donner de la visibilité, faire connaître votre activité et vos coordonnées, et créer de vraies opportunités de vente.</p>
            </div>
            <div class="reveal grid grid-cols-2 gap-4">
                @foreach ([['megaphone', 'Présentez', 'Vos produits et services à l\'antenne.'], ['eye', 'Faites connaître', 'Votre marque, vos coordonnées, votre adresse.'], ['wallet', 'Vendez', 'Créez des opportunités commerciales.'], ['chart', 'Développez', 'Votre business au-delà de vos frontières.']] as [$icon, $title, $text])
                    <div class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
                        <x-icon :name="$icon" class="h-7 w-7 text-gold-600" />
                        <p class="mt-4 font-display text-lg text-ink">{{ $title }}</p>
                        <p class="mt-1 text-sm text-neutral-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- COMMENT ÇA MARCHE --}}
    <section id="fonctionnement" class="scroll-mt-20 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow">Comment ça marche ?</p>
                <h2 class="section-title">Quatre étapes jusqu'à l'antenne.</h2>
            </div>
            <ol class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Choisissez votre formule', 'Présentiel à Lomé ou à distance, puis le package adapté à vos objectifs.'],
                    ['Réservez votre créneau', 'Sélectionnez une date et un horaire disponibles dans le calendrier en ligne.'],
                    ['Présentez votre business', 'Renseignez vos produits, vos coordonnées et envoyez vos visuels.'],
                    ['Passez en direct', 'Après paiement et validation, notre équipe prépare votre émission avec vous.'],
                ] as $i => [$title, $text])
                    <li class="reveal relative rounded-2xl border border-neutral-200 p-6">
                        <span class="font-display text-5xl text-gold-500/30">0{{ $i + 1 }}</span>
                        <p class="mt-3 font-semibold text-ink">{{ $title }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-neutral-500">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- PACKAGES --}}
    <section id="packages" class="scroll-mt-20 bg-ink py-20 text-white sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow !text-gold-400">Nos packages</p>
                <h2 class="section-title !text-white">Une offre pour chaque ambition.</h2>
                <p class="mt-5 text-neutral-400">Tarifs en francs CFA. Chaque package est accessible en présentiel comme à distance.</p>
            </div>

            <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($packages as $package)
                    <article class="reveal flex flex-col rounded-3xl border p-7 transition duration-300 hover:-translate-y-1 {{ $package->is_featured ? 'border-gold-500 bg-gradient-to-b from-gold-500/15 to-transparent' : 'border-white/10 bg-white/[0.03]' }}" data-testid="package-card">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">{{ $package->duration_text }}</p>
                            @if ($package->is_featured)
                                <span class="rounded-full bg-gold-500 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-ink">Populaire</span>
                            @endif
                        </div>
                        <h3 class="mt-3 font-display text-2xl">{{ $package->name }}</h3>
                        @if ($package->tagline) <p class="mt-1 text-sm text-neutral-400">{{ $package->tagline }}</p> @endif
                        <p class="mt-6 font-display text-4xl text-gold-300">{{ number_format($package->price, 0, ',', ' ') }} <span class="font-sans text-sm font-medium text-neutral-400">FCFA</span></p>
                        <ul class="mt-6 flex-1 space-y-3 text-sm text-neutral-300">
                            @foreach ($package->features ?? [] as $feature)
                                <li class="flex gap-3"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-gold-400" />{{ $feature }}</li>
                            @endforeach
                        </ul>
                        @if ($package->terms)
                            <p class="mt-6 border-t border-white/10 pt-4 text-xs leading-relaxed text-neutral-500">{{ $package->terms }}</p>
                        @endif
                        <a href="{{ route('booking', ['package' => $package->slug]) }}" class="{{ $package->is_featured ? 'btn-gold' : 'btn-light' }} mt-7 w-full">Choisir {{ $package->name }}</a>
                    </article>
                @empty
                    <p class="col-span-full text-center text-neutral-400">Les packages seront publiés très prochainement.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- POURQUOI --}}
    <section class="py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow">Pourquoi choisir OCD Live ?</p>
                <h2 class="section-title">Une production professionnelle au service de votre visibilité.</h2>
            </div>
            <div class="mt-14 grid gap-px overflow-hidden rounded-3xl border border-neutral-200 bg-neutral-200 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['studio', 'Régie professionnelle', 'Studio équipé à Lomé, cadrage, son et lumière pris en charge par notre équipe technique.'],
                    ['megaphone', 'Diffusion House of Challenge', 'Vos produits présentés en direct sur les comptes de House of Challenge.'],
                    ['globe', 'Panafricain', 'Participez depuis Lomé ou depuis n\'importe quel pays, avec un accompagnement à distance.'],
                    ['phone', 'Vos coordonnées à l\'antenne', 'Numéro de téléphone, adresse commerciale et réseaux sociaux mis en avant.'],
                    ['users', 'Accompagnement humain', 'Une assistante commerciale à Lomé prépare votre passage avec vous.'],
                    ['shield', 'Réservation fiable', 'Créneau garanti sans double réservation, confirmation après paiement et validation.'],
                ] as [$icon, $title, $text])
                    <div class="reveal bg-white p-7 sm:p-8">
                        <x-icon :name="$icon" class="h-7 w-7 text-gold-600" />
                        <p class="mt-5 font-semibold text-ink">{{ $title }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-neutral-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="scroll-mt-20 bg-neutral-50 py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center">
                <p class="eyebrow">Questions fréquentes</p>
                <h2 class="section-title">Tout ce qu'il faut savoir.</h2>
            </div>
            <div class="mt-12 divide-y divide-neutral-200 rounded-3xl border border-neutral-200 bg-white" x-data="{ open: 0 }">
                @foreach ([
                    ['Qu\'est-ce qu\'une émission OCD Live ?', 'C\'est une émission commerciale diffusée en direct sur les réseaux sociaux de House of Challenge, pendant laquelle vous présentez vos produits ou services, vos coordonnées et votre adresse commerciale.'],
                    ['Puis-je participer sans venir à Lomé ?', 'Oui. Avec le Live à distance, vous intervenez depuis votre domicile, votre boutique ou votre entreprise. Notre assistante basée à Lomé organise un test de connexion et vous accompagne pendant l\'émission.'],
                    ['Quand ma réservation est-elle confirmée ?', 'Votre créneau est réservé provisoirement dès l\'envoi de la demande. La réservation est confirmée après réception du paiement et validation par notre équipe. Vous recevez alors un e-mail de confirmation.'],
                    ['Comment se passe le paiement ?', 'Pour le lancement, notre équipe vous contacte sur WhatsApp pour le règlement (Mobile Money ou espèces au studio). Le paiement en ligne par Mobile Money et carte bancaire arrive prochainement.'],
                    ['Les ventes sont-elles garanties ?', 'OCD Live vous apporte de la visibilité et des opportunités commerciales. Les ventes dépendent de votre offre et de votre public : nous ne garantissons pas de volume de ventes.'],
                    ['Je vends des produits alimentaires, que dois-je prévoir ?', 'Le formulaire vous demande comment vos produits sont préparés, conservés et présentés afin que nous organisions une présentation dans de bonnes conditions d\'hygiène.'],
                    ['Puis-je modifier ou annuler ma réservation ?', 'Contactez-nous sur WhatsApp ou par e-mail avec votre référence. Les conditions sont précisées dans nos conditions générales.'],
                ] as $i => [$q, $a])
                    <div>
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left" :aria-expanded="open === {{ $i }}">
                            <span class="font-medium text-ink">{{ $q }}</span>
                            <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-gold-600 transition-transform duration-300" x-bind:class="open === {{ $i }} && 'rotate-180'" />
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="px-6 pb-6 text-sm leading-relaxed text-neutral-600">{{ $a }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CONTACT --}}
    <section id="contact" class="scroll-mt-20 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal overflow-hidden rounded-3xl bg-ink px-6 py-14 text-center text-white sm:px-12 sm:py-20">
                <p class="eyebrow !text-gold-400">Contact</p>
                <h2 class="section-title mx-auto max-w-2xl !text-white">Parlons de votre prochain passage à l'antenne.</h2>
                <p class="mx-auto mt-5 max-w-xl text-neutral-400">Une question sur les packages ou la participation à distance ? Notre équipe à Lomé vous répond.</p>
                <div class="mt-10 grid gap-4 text-left sm:grid-cols-3">
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('contact_whatsapp')) }}" target="_blank" rel="noopener" class="rounded-2xl border border-white/10 p-5 transition hover:border-gold-400">
                        <x-icon name="whatsapp" class="h-6 w-6 text-gold-400" /><p class="mt-3 text-sm text-neutral-400">WhatsApp</p><p class="font-medium">+{{ preg_replace('/\D+/', '', setting('contact_whatsapp')) }}</p>
                    </a>
                    <a href="mailto:{{ setting('contact_email') }}" class="rounded-2xl border border-white/10 p-5 transition hover:border-gold-400">
                        <x-icon name="mail" class="h-6 w-6 text-gold-400" /><p class="mt-3 text-sm text-neutral-400">E-mail</p><p class="break-all font-medium">{{ setting('contact_email') }}</p>
                    </a>
                    <div class="rounded-2xl border border-white/10 p-5">
                        <x-icon name="map" class="h-6 w-6 text-gold-400" /><p class="mt-3 text-sm text-neutral-400">Studio</p><p class="font-medium">{{ setting('contact_address') }}</p>
                    </div>
                </div>
                <a href="{{ route('booking') }}" class="btn-gold mt-10">Réserver mon Live</a>
                <p class="mt-10 font-display text-sm uppercase tracking-[0.3em] text-neutral-500">Présentez · Faites connaître · Vendez · Développez</p>
            </div>
        </div>
    </section>
@endsection
