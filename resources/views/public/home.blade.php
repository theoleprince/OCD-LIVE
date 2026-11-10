@extends('layouts.public')

@section('description', 'Présentez vos produits et services en direct sur le compte TikTok House of Challenge. Live en présentiel au studio de Lomé ou à distance depuis toute l\'Afrique. Réservez votre émission OCD Live.')

@section('content')
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -left-40 -top-40 h-[30rem] w-[30rem] rounded-full bg-brand-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-32 top-32 h-[26rem] w-[26rem] rounded-full bg-accent-200/50 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-12 sm:px-6 sm:pt-16 lg:grid-cols-2 lg:px-8 lg:pb-24 lg:pt-20">
            <div class="text-center lg:text-left">
                <p class="reveal inline-flex items-center gap-2 rounded-full bg-red-50 px-4 py-1.5 text-xs font-bold text-red-600 ring-1 ring-red-100">
                    <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse-soft"></span>Émissions en direct · dès le 1er décembre 2026
                </p>
                <h1 class="reveal mt-6 font-display text-[2.6rem] leading-[1.05] text-ink sm:text-6xl">
                    Votre produit mérite <span class="bg-gradient-to-r from-brand-600 to-accent-500 bg-clip-text text-transparent">d'être vu.</span>
                </h1>
                <p class="reveal mx-auto mt-6 max-w-xl text-lg leading-relaxed text-slate-600 lg:mx-0">
                    Donnez une nouvelle dimension à votre business grâce aux émissions commerciales OCD Live.
                </p>
                <div class="reveal mt-8 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
                    <a href="{{ route('booking') }}" class="btn-accent !px-7 !py-4 text-base">Réserver mon Live <x-icon name="arrow-right" class="h-5 w-5" /></a>
                    <a href="#packages" class="btn-outline !px-7 !py-4 text-base">Voir les packages</a>
                </div>
                <ul class="reveal mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm font-semibold text-slate-600 lg:justify-start">
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-5 w-5 text-accent-500" />Studio pro à Lomé</li>
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-5 w-5 text-accent-500" />Participation à distance</li>
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-5 w-5 text-accent-500" />Diffusion TikTok</li>
                </ul>
            </div>

            {{-- Le présentateur devant la carte d'Afrique rétro-éclairée, écho du mur du studio --}}
            <div class="reveal relative mx-auto w-full max-w-sm lg:max-w-md">
                <div class="relative flex aspect-[4/5] w-full flex-col items-center overflow-hidden rounded-[2rem] bg-gradient-to-b from-ink to-neutral-800 shadow-2xl shadow-ink/25">
                    <x-africa-silhouette class="absolute left-1/2 top-[4%] h-[72%] w-[72%] -translate-x-1/2" />
                    <img src="{{ asset('images/hero/presentateur-studio-hero.jpg') }}" alt="Présentateur OCD Live, micro en main, devant la carte d'Afrique rétro-éclairée du studio" class="relative z-10 mt-auto h-[58%] w-full rounded-t-[1.5rem] object-cover object-top">
                </div>
                <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 whitespace-nowrap rounded-md bg-red-500 px-2.5 py-1.5 text-[11px] font-extrabold text-white shadow-lg">
                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse-soft"></span>EN DIRECT
                </span>
                <div class="absolute -left-4 bottom-16 hidden animate-float rounded-2xl bg-white p-3 shadow-xl ring-1 ring-sand-200 sm:block">
                    <p class="flex items-center gap-2 text-xs font-bold text-ink"><span class="grid h-8 w-8 place-items-center rounded-lg bg-accent-100 text-accent-600"><x-icon name="megaphone" class="h-4 w-4" /></span>Visibilité en direct</p>
                </div>
                <div class="absolute -right-4 top-14 hidden animate-float rounded-2xl bg-white p-3 shadow-xl ring-1 ring-sand-200 [animation-delay:1.5s] sm:block">
                    <p class="flex items-center gap-2 text-xs font-bold text-ink"><span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-100 text-brand-600"><x-icon name="globe" class="h-4 w-4" /></span>Partout en Afrique</p>
                </div>
            </div>
        </div>

        {{-- Deux grandes cartes --}}
        <div class="relative mx-auto grid max-w-7xl gap-5 px-4 pb-20 sm:px-6 md:grid-cols-2 lg:px-8">
            <article class="reveal group relative flex flex-col overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 p-7 text-white shadow-xl shadow-brand-700/20 sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 transition-transform duration-700 group-hover:scale-125"></div>
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/15 text-white"><x-icon name="studio" class="h-7 w-7" /></span>
                <p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-brand-100">Studio · Lomé, Togo</p>
                <h2 class="mt-2 font-display text-2xl sm:text-3xl">Live en présentiel – Lomé</h2>
                <p class="mt-4 flex-1 leading-relaxed text-brand-50/90">Venez dans notre studio à Lomé, présentez vos produits et profitez de notre régie professionnelle pour faire connaître votre activité.</p>
                <a href="{{ route('booking', ['mode' => 'onsite']) }}" class="btn-light mt-8 self-start !px-6">RÉSERVER EN PRÉSENTIEL <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </article>

            <article class="reveal group relative flex flex-col overflow-hidden rounded-3xl bg-gradient-to-br from-accent-500 to-accent-700 p-7 text-white shadow-xl shadow-accent-700/20 sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 transition-transform duration-700 group-hover:scale-125"></div>
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/15 text-white"><x-icon name="globe" class="h-7 w-7" /></span>
                <p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-accent-100">Afrique · International</p>
                <h2 class="mt-2 font-display text-2xl sm:text-3xl">Live à distance – Afrique et international</h2>
                <p class="mt-4 flex-1 leading-relaxed text-accent-50/90">Vous êtes au Cameroun, au Sénégal, au Tchad, en Centrafrique ou ailleurs ? Participez à nos émissions depuis votre domicile ou votre commerce avec l'accompagnement de notre assistante basée à Lomé.</p>
                <a href="{{ route('booking', ['mode' => 'remote']) }}" class="btn mt-8 self-start bg-white !px-6 text-accent-700 hover:bg-accent-50">RÉSERVER À DISTANCE <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </article>
        </div>
    </section>

    {{-- CONCEPT --}}
    <section id="concept" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="reveal">
                <p class="eyebrow"><span class="h-1.5 w-6 rounded-full bg-brand-600"></span>Le concept</p>
                <h2 class="section-title">La vitrine en direct des entrepreneurs africains.</h2>
                <p class="mt-6 text-lg leading-relaxed text-slate-600">OCD Live est une plateforme panafricaine de marketing et de présentation commerciale en direct, portée par <strong class="text-ink">OCD — Les Opportunités C'est Dehors</strong> et animée par <strong class="text-ink">{{ config('ocd.host') }}</strong>.</p>
                <p class="mt-4 leading-relaxed text-slate-600">Commerçants, restaurateurs, artisans et entreprises présentent leurs produits et services lors d'émissions diffusées en direct sur le compte TikTok <strong class="text-ink">House of Challenge</strong>. L'objectif : vous donner de la visibilité, faire connaître votre activité et vos coordonnées, et créer de vraies opportunités de vente.</p>
                <x-photo src="images/concept/entrepreneuse-boutique.jpg" alt="Entrepreneuse africaine présentant ses produits dans sa boutique" ratio="aspect-[16/10]" class="mt-8" />
            </div>
            <div class="reveal grid grid-cols-2 gap-4">
                @foreach ([
                    ['megaphone', 'Présentez', 'Vos produits et services à l\'antenne.', 'bg-brand-100 text-brand-600'],
                    ['eye', 'Faites connaître', 'Votre marque, vos coordonnées, votre adresse.', 'bg-accent-100 text-accent-600'],
                    ['wallet', 'Vendez', 'Créez des opportunités commerciales.', 'bg-sand-200 text-ink'],
                    ['chart', 'Développez', 'Votre business au-delà de vos frontières.', 'bg-ink text-white'],
                ] as [$icon, $title, $text, $chip])
                    <div class="card p-5 transition hover:-translate-y-1 hover:shadow-lg sm:p-6">
                        <span class="grid h-12 w-12 place-items-center rounded-xl {{ $chip }}"><x-icon :name="$icon" class="h-6 w-6" /></span>
                        <p class="mt-4 text-lg font-extrabold text-ink">{{ $title }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- NOS SERVICES --}}
    <section id="services" class="scroll-mt-20 bg-sand-50 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow justify-center">Nos services</p>
                <h2 class="section-title">Ce que nous mettons à votre service.</h2>
            </div>
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['images/services/live-presentiel.jpg', 'Live en présentiel — Studio de Lomé', 'Présentez vos produits dans notre studio professionnel : cadrage, son et lumière pris en charge par notre équipe technique.'],
                    ['images/services/live-distance.jpg', 'Live à distance — Partout en Afrique', 'Participez depuis votre commerce ou votre domicile, où que vous soyez, avec l\'accompagnement de notre assistante basée à Lomé.'],
                    ['images/services/production-contenus.jpg', 'Production de contenus promotionnels', 'Vidéo promotionnelle et publications pour prolonger la visibilité de votre passage à l\'antenne.'],
                    ['images/services/accompagnement-commercial.jpg', 'Accompagnement commercial & relais', 'Suivi personnalisé et relais sur les réseaux pour transformer votre passage en opportunités durables.'],
                ] as [$src, $title, $text])
                    <div class="reveal card p-3 transition hover:-translate-y-1 hover:shadow-lg">
                        <x-photo :src="$src" :alt="$title" ratio="aspect-[4/3]" />
                        <div class="p-4">
                            <p class="text-lg font-extrabold text-ink">{{ $title }}</p>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- COMMENT ÇA MARCHE --}}
    <section id="fonctionnement" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow justify-center">Comment ça marche ?</p>
                <h2 class="section-title">Quatre étapes jusqu'à l'antenne.</h2>
            </div>
            <div class="relative mt-14">
                <div class="absolute left-[12%] right-[12%] top-7 hidden h-0.5 bg-gradient-to-r from-brand-200 via-brand-400 to-accent-400 lg:block"></div>
                <ol class="relative grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
                    @foreach ([
                        ['Choisissez votre formule', 'Présentiel à Lomé ou à distance, puis le package adapté à vos objectifs.'],
                        ['Réservez votre créneau', 'Sélectionnez une date et un horaire disponibles dans le calendrier en ligne.'],
                        ['Présentez votre business', 'Renseignez vos produits, vos coordonnées et envoyez vos visuels.'],
                        ['Passez en direct', 'Après paiement et validation, notre équipe prépare votre émission avec vous.'],
                    ] as $i => [$title, $text])
                        <li class="reveal text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl text-xl font-extrabold text-white shadow-lg {{ $i === 3 ? 'bg-ink shadow-ink/30' : 'bg-brand-600 shadow-brand-600/30' }}">{{ $i + 1 }}</span>
                            <p class="mt-5 text-lg font-extrabold text-ink">{{ $title }}</p>
                            <p class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-slate-500">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- PACKAGES --}}
    <section id="packages" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow justify-center">Nos packages</p>
                <h2 class="section-title">Une offre pour chaque ambition.</h2>
                <p class="mt-4 text-slate-600">Tarifs en francs CFA. Chaque package est accessible en présentiel comme à distance.</p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($packages as $package)
                    @php($featured = $package->is_featured)
                    <article data-testid="package-card" @class([
                        'reveal relative flex flex-col rounded-3xl p-7 transition duration-300 hover:-translate-y-1',
                        'bg-gradient-to-br from-brand-600 to-brand-800 text-white shadow-2xl shadow-brand-700/30' => $featured,
                        'border border-slate-200 bg-white hover:border-brand-300 hover:shadow-xl' => ! $featured,
                    ])>
                        @if ($featured)
                            <span class="absolute -top-3 left-7 rounded-full bg-accent-500 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-white shadow">Le plus choisi</span>
                        @endif
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold {{ $featured ? 'bg-white/15 text-white' : 'bg-brand-50 text-brand-700' }}"><x-icon name="clock" class="h-4 w-4" />{{ $package->duration_text }}</span>
                        </div>
                        <h3 class="mt-4 font-display text-2xl {{ $featured ? 'text-white' : 'text-ink' }}">{{ $package->name }}</h3>
                        @if ($package->tagline) <p class="mt-1 text-sm {{ $featured ? 'text-brand-100' : 'text-slate-500' }}">{{ $package->tagline }}</p> @endif
                        <p class="mt-6 font-display text-4xl {{ $featured ? 'text-white' : 'text-ink' }}">{{ number_format($package->price, 0, ',', ' ') }} <span class="text-base font-bold {{ $featured ? 'text-brand-100' : 'text-slate-400' }}">FCFA</span></p>
                        <ul class="mt-6 flex-1 space-y-3 text-sm {{ $featured ? 'text-brand-50' : 'text-slate-600' }}">
                            @foreach ($package->features ?? [] as $feature)
                                <li class="flex gap-3">
                                    <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full {{ $featured ? 'bg-accent-400 text-white' : 'bg-accent-100 text-accent-600' }}"><x-icon name="check" class="h-3.5 w-3.5" /></span>{{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        @if ($package->terms)
                            <p class="mt-6 rounded-xl p-3 text-xs leading-relaxed {{ $featured ? 'bg-white/10 text-brand-50' : 'bg-slate-50 text-slate-500' }}">{{ $package->terms }}</p>
                        @endif
                        <a href="{{ route('booking', ['package' => $package->slug]) }}" class="{{ $featured ? 'btn-accent' : 'btn-primary' }} mt-7 w-full">Choisir {{ $package->name }}</a>
                    </article>
                @empty
                    <p class="col-span-full text-center text-slate-500">Les packages seront publiés très prochainement.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- POURQUOI --}}
    <section class="bg-gradient-to-b from-brand-50/70 to-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow justify-center">Pourquoi choisir OCD Live ?</p>
                <h2 class="section-title">Une production professionnelle au service de votre visibilité.</h2>
            </div>
            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['studio', 'Régie professionnelle', 'Studio équipé à Lomé, cadrage, son et lumière pris en charge par notre équipe technique.', 'bg-brand-600'],
                    ['megaphone', 'Diffusion en direct sur TikTok', 'Vos produits présentés en direct sur le compte TikTok House of Challenge.', 'bg-accent-500'],
                    ['globe', 'Panafricain', 'Participez depuis Lomé ou depuis n\'importe quel pays, avec un accompagnement à distance.', 'bg-ink'],
                    ['phone', 'Vos coordonnées à l\'antenne', 'Numéro de téléphone, adresse commerciale et réseaux sociaux mis en avant.', 'bg-brand-700'],
                    ['users', 'Accompagnement humain', 'Une assistante commerciale à Lomé prépare votre passage avec vous.', 'bg-accent-600'],
                    ['shield', 'Réservation fiable', 'Créneau garanti sans double réservation, confirmation après paiement et validation.', 'bg-ink'],
                ] as [$icon, $title, $text, $chip])
                    <div class="reveal card p-7 transition hover:-translate-y-1 hover:shadow-lg">
                        <span class="grid h-12 w-12 place-items-center rounded-xl text-white shadow-md {{ $chip }}"><x-icon :name="$icon" class="h-6 w-6" /></span>
                        <p class="mt-5 text-lg font-extrabold text-ink">{{ $title }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- GALERIE --}}
    <section id="galerie" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="eyebrow justify-center">Galerie</p>
                <h2 class="section-title">Dans les coulisses de nos émissions.</h2>
            </div>
            <div class="mt-14 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ([
                    ['images/galerie/01-presentateur-camera.jpg', 'Présentateur OCD Live au micro, studio de Lomé'],
                    ['images/galerie/02-entrepreneuse-produits.jpg', 'Entrepreneuse préparant ses produits avant son passage'],
                    ['images/galerie/03-commercant-live.jpg', 'Commerçant en direct devant la caméra'],
                    ['images/galerie/04-equipe-production.jpg', 'Équipe de production en régie'],
                    ['images/galerie/05-entrepreneur-smartphone.jpg', 'Participation à distance depuis un smartphone'],
                    ['images/galerie/06-studio-equipement.jpg', 'Notre studio équipé : lumière, son, caméras'],
                    ['images/galerie/07-multi-entrepreneurs.jpg', 'Entrepreneurs réunis lors d\'une émission OCD Live'],
                ] as [$src, $caption])
                    <x-photo :src="$src" :alt="$caption" ratio="aspect-square" class="reveal" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center">
                <p class="eyebrow justify-center">Questions fréquentes</p>
                <h2 class="section-title">Tout ce qu'il faut savoir.</h2>
            </div>
            <div class="mt-12 space-y-3" x-data="{ open: 0 }">
                @foreach ([
                    ['Qu\'est-ce qu\'une émission OCD Live ?', 'C\'est une émission commerciale diffusée en direct sur le compte TikTok House of Challenge, pendant laquelle vous présentez vos produits ou services, vos coordonnées et votre adresse commerciale.'],
                    ['Puis-je participer sans venir à Lomé ?', 'Oui. Avec le Live à distance, vous intervenez depuis votre domicile, votre boutique ou votre entreprise. Notre assistante basée à Lomé organise un test de connexion et vous accompagne pendant l\'émission.'],
                    ['Quand ma réservation est-elle confirmée ?', 'Votre créneau est réservé provisoirement dès l\'envoi de la demande. La réservation est confirmée après réception du paiement et validation par notre équipe. Vous recevez alors un e-mail de confirmation.'],
                    ['Comment se passe le paiement ?', 'Pour le lancement, notre équipe vous contacte sur WhatsApp pour le règlement (Mobile Money ou espèces au studio). Le paiement en ligne par Mobile Money et carte bancaire arrive prochainement.'],
                    ['Les ventes sont-elles garanties ?', 'OCD Live vous apporte de la visibilité et des opportunités commerciales. Les ventes dépendent de votre offre et de votre public : nous ne garantissons pas de volume de ventes.'],
                    ['Je vends des produits alimentaires, que dois-je prévoir ?', 'Le formulaire vous demande comment vos produits sont préparés, conservés et présentés afin que nous organisions une présentation dans de bonnes conditions d\'hygiène.'],
                    ['Puis-je modifier ou annuler ma réservation ?', 'Contactez-nous sur WhatsApp ou par e-mail avec votre référence. Les conditions sont précisées dans nos conditions générales.'],
                ] as $i => [$q, $a])
                    <div class="card overflow-hidden transition" :class="open === {{ $i }} && 'border-brand-300 shadow-md'">
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left" :aria-expanded="open === {{ $i }}">
                            <span class="font-bold text-ink">{{ $q }}</span>
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full transition duration-300" :class="open === {{ $i }} ? 'rotate-180 bg-brand-600 text-white' : 'bg-brand-50 text-brand-600'"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="px-6 pb-6 text-sm leading-relaxed text-slate-600">{{ $a }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- MOYENS DE PAIEMENT --}}
    <section class="pb-20 sm:pb-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="reveal rounded-[2rem] border border-slate-200 bg-white p-8 sm:p-10">
                <div class="mx-auto max-w-xl text-center">
                    <p class="eyebrow justify-center">Moyens de paiement</p>
                    <h2 class="mt-3 font-display text-2xl text-ink sm:text-3xl">Réglez selon ce qui vous convient.</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-500">Au lancement, le règlement est coordonné avec notre équipe sur WhatsApp une fois votre créneau réservé — voir « Comment se passe le paiement ? » ci-dessus.</p>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <x-payment-logo src="images/paiement/flooz.png" label="Flooz" icon="phone" />
                    <x-payment-logo src="images/paiement/tmoney.png" label="T-Money" icon="phone" />
                    <x-payment-logo src="images/paiement/visa.svg" label="Visa" icon="wallet" />
                    <x-payment-logo src="images/paiement/mastercard.svg" label="Mastercard" icon="wallet" />
                    <x-payment-logo src="images/paiement/virement.svg" label="Virement bancaire" icon="bank" />
                    <x-payment-logo label="Espèces au studio" icon="cash" />
                </div>
            </div>
        </div>
    </section>

    {{-- CONTACT --}}
    <section id="contact" class="scroll-mt-20 pb-20 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-5 lg:items-stretch">
                <div class="reveal relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-600 via-brand-700 to-accent-600 px-6 py-14 text-center text-white sm:px-10 sm:py-16 lg:col-span-3 lg:py-20 lg:text-left">
                    <div class="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-white/10"></div>
                    <div class="pointer-events-none absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-100">Contact</p>
                        <h2 class="mx-auto mt-3 max-w-xl font-display text-3xl sm:text-4xl lg:mx-0">Parlons de votre prochain passage à l'antenne.</h2>
                        <p class="mx-auto mt-4 max-w-xl text-brand-50/90 lg:mx-0">Une question sur les packages ou la participation à distance ? Notre équipe à Lomé vous répond.</p>
                        <div class="mt-10 grid gap-4 text-left sm:grid-cols-3">
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('contact_whatsapp')) }}" target="_blank" rel="noopener" class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 transition hover:bg-white/20">
                                <x-icon name="whatsapp" class="h-6 w-6" /><p class="mt-3 text-sm text-brand-100">WhatsApp</p><p class="font-bold">+{{ preg_replace('/\D+/', '', setting('contact_whatsapp')) }}</p>
                            </a>
                            <a href="mailto:{{ setting('contact_email') }}" class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 transition hover:bg-white/20">
                                <x-icon name="mail" class="h-6 w-6" /><p class="mt-3 text-sm text-brand-100">E-mail</p><p class="break-all font-bold">{{ setting('contact_email') }}</p>
                            </a>
                            <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20">
                                <x-icon name="map" class="h-6 w-6" /><p class="mt-3 text-sm text-brand-100">Studio</p><p class="font-bold">{{ setting('contact_address') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('booking') }}" class="btn mt-10 bg-white !px-8 !py-4 text-base text-brand-700 hover:bg-brand-50">Réserver mon Live <x-icon name="arrow-right" class="h-5 w-5" /></a>
                        <p class="mt-10 text-xs font-bold uppercase tracking-[0.3em] text-brand-100/80">Présentez · Faites connaître · Vendez · Développez</p>
                    </div>
                </div>

                <div class="reveal lg:col-span-2">
                    <livewire:contact-form />
                </div>
            </div>
        </div>
    </section>
@endsection
