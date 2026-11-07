<div class="mx-auto w-full max-w-4xl" x-data x-on:scroll-top.window="window.scrollTo({ top: $el.offsetTop - 90, behavior: 'smooth' })">

    {{-- Barre de progression --}}
    @if ($step < 8)
        <ol class="no-scrollbar -mx-4 mb-8 flex snap-x gap-2 overflow-x-auto px-4 sm:mx-0 sm:grid sm:grid-cols-7 sm:overflow-visible sm:px-0">
            @foreach (array_slice(\App\Livewire\BookingWizard::STEPS, 0, 7, true) as $n => $label)
                @php($done = $n < $step)
                <li class="snap-start">
                    <button type="button" wire:click="goTo({{ $n }})" @disabled($n > $step)
                        class="group flex w-full min-w-[6.5rem] flex-col gap-2 text-left disabled:cursor-not-allowed">
                        <span class="h-1 rounded-full transition-colors duration-500 {{ $n <= $step ? 'bg-gold-500' : 'bg-neutral-200' }}"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] {{ $n === $step ? 'text-ink' : ($done ? 'text-neutral-500 group-hover:text-ink' : 'text-neutral-400') }}">
                            {{ $n }}. {{ $label }}
                        </span>
                    </button>
                </li>
            @endforeach
        </ol>
    @endif

    <div class="relative rounded-3xl border border-neutral-200 bg-white p-5 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.25)] sm:p-10">

        <div wire:loading.delay.grid class="absolute inset-0 z-10 grid place-items-center rounded-3xl bg-white/60 backdrop-blur-[1px]">
            <span class="h-8 w-8 animate-spin rounded-full border-2 border-gold-500 border-t-transparent"></span>
        </div>

        {{-- ÉTAPE 1 : MODE --}}
        @if ($step === 1)
            <x-wizard.heading step="1" title="Comment souhaitez-vous participer ?" subtitle="Les deux formules donnent accès aux mêmes émissions commerciales." />

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([['ONSITE', 'Live en présentiel', 'Au studio OCD Live de Lomé, avec notre régie professionnelle.', 'studio'], ['REMOTE', 'Live à distance', 'Depuis votre domicile, boutique ou entreprise, partout en Afrique et dans le monde.', 'globe']] as [$value, $title, $text, $icon])
                    <button type="button" wire:click="selectMode('{{ $value }}')"
                        class="group rounded-2xl border p-6 text-left transition hover:-translate-y-0.5 hover:border-gold-400 hover:shadow-lg {{ $mode === $value ? 'border-gold-500 ring-2 ring-gold-500/30' : 'border-neutral-200' }}">
                        <span class="mb-5 inline-grid h-12 w-12 place-items-center rounded-xl bg-ink text-gold-400"><x-icon :name="$icon" class="h-6 w-6" /></span>
                        <span class="block font-display text-xl text-ink">{{ $title }}</span>
                        <span class="mt-2 block text-sm leading-relaxed text-neutral-600">{{ $text }}</span>
                    </button>
                @endforeach
            </div>
            @error('mode') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror
        @endif

        {{-- ÉTAPE 2 : PACKAGE --}}
        @if ($step === 2)
            <x-wizard.heading step="2" title="Choisissez votre package" subtitle="La durée du package détermine les créneaux proposés dans le calendrier." />

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($this->packages as $p)
                    <button type="button" wire:click="selectPackage({{ $p->id }})" wire:key="pkg-{{ $p->id }}"
                        class="flex flex-col rounded-2xl border p-5 text-left transition hover:border-gold-400 hover:shadow-md {{ $packageId === $p->id ? 'border-gold-500 ring-2 ring-gold-500/30' : 'border-neutral-200' }}">
                        <span class="flex items-start justify-between gap-3">
                            <span>
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-gold-600">{{ $p->duration_text }}</span>
                                <span class="mt-1 block font-display text-lg text-ink">{{ $p->name }}</span>
                            </span>
                            <span class="shrink-0 whitespace-nowrap text-right font-semibold text-ink">{{ $p->formatted_price }}</span>
                        </span>
                        <ul class="mt-4 space-y-1.5 text-sm text-neutral-600">
                            @foreach (array_slice($p->features ?? [], 0, 5) as $f)
                                <li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-gold-500" />{{ $f }}</li>
                            @endforeach
                        </ul>
                        @if ($p->terms)
                            <span class="mt-4 block rounded-lg bg-neutral-50 px-3 py-2 text-xs leading-relaxed text-neutral-500">{{ $p->terms }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
            @error('packageId') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror
        @endif

        {{-- ÉTAPE 3 : CALENDRIER --}}
        @if ($step === 3)
            <x-wizard.heading step="3" title="Choisissez votre date" subtitle="Heure de Lomé (GMT). Les dates disponibles tiennent compte de la durée de votre package : {{ $this->selectedPackage?->duration_text }}." />

            <div class="mx-auto max-w-md">
                <div class="mb-4 flex items-center justify-between">
                    <button type="button" wire:click="previousMonth" class="grid h-10 w-10 place-items-center rounded-full border border-neutral-200 hover:border-ink" aria-label="Mois précédent"><x-icon name="chevron-left" class="h-4 w-4" /></button>
                    <p class="font-display text-lg text-ink">{{ $monthLabel }}</p>
                    <button type="button" wire:click="nextMonth" class="grid h-10 w-10 place-items-center rounded-full border border-neutral-200 hover:border-ink" aria-label="Mois suivant"><x-icon name="chevron-right" class="h-4 w-4" /></button>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    @foreach (['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'] as $d) <span class="py-2">{{ $d }}</span> @endforeach
                </div>

                <div class="grid grid-cols-7 gap-1">
                    @foreach ($this->calendar as $week)
                        @foreach ($week as $day)
                            @php($available = $day['in_month'] && $day['status'] === 'available')
                            <button type="button" wire:key="d-{{ $day['date'] }}"
                                @if ($available) wire:click="selectDate('{{ $day['date'] }}')" @else disabled @endif
                                @class([
                                    'relative aspect-square rounded-xl text-sm transition',
                                    'invisible' => ! $day['in_month'],
                                    'bg-white font-semibold text-ink ring-1 ring-neutral-200 hover:bg-ink hover:text-white' => $available && $date !== $day['date'],
                                    'bg-ink font-semibold text-gold-300' => $date === $day['date'],
                                    'cursor-not-allowed bg-neutral-100 text-neutral-300 line-through' => $day['in_month'] && in_array($day['status'], ['closed', 'past']),
                                    'cursor-not-allowed bg-gold-50 text-gold-700/60' => $day['in_month'] && $day['status'] === 'full',
                                ])
                                title="{{ ['available' => 'Disponible', 'full' => 'Complet', 'closed' => 'Indisponible', 'past' => 'Passé'][$day['status']] }}">
                                {{ $day['day'] }}
                                @if ($day['in_month'] && $day['status'] === 'full')
                                    <span class="absolute inset-x-0 bottom-1 text-[8px] font-bold uppercase tracking-wide">complet</span>
                                @endif
                            </button>
                        @endforeach
                    @endforeach
                </div>

                <div class="mt-5 flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs text-neutral-500">
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded ring-1 ring-neutral-300"></span>Disponible</span>
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-gold-100"></span>Complet</span>
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-neutral-200"></span>Indisponible</span>
                </div>
                @error('date') <p class="mt-3 text-center text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- ÉTAPE 4 : HORAIRES --}}
        @if ($step === 4)
            <x-wizard.heading step="4" title="Choisissez votre créneau"
                subtitle="{{ date_fr(\Carbon\CarbonImmutable::parse($date)) }} · durée {{ $this->selectedPackage?->duration_text }} · heure de Lomé" />

            @error('time') <p class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p> @enderror

            @php($free = collect($this->timeSlots)->where('available', true))
            @if ($free->isEmpty())
                <p class="rounded-xl bg-neutral-50 p-6 text-center text-neutral-600">Plus aucun créneau libre ce jour-là. <button type="button" wire:click="goTo(3)" class="font-semibold text-ink underline">Choisir une autre date</button></p>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($this->timeSlots as $slot)
                        @php($t = $slot['start']->format('H:i'))
                        <button type="button" wire:key="t-{{ $t }}" @if ($slot['available']) wire:click="selectTime('{{ $t }}')" @else disabled @endif
                            @class([
                                'rounded-xl border px-3 py-3 text-center transition',
                                'border-neutral-200 hover:border-ink hover:bg-ink hover:text-white' => $slot['available'] && $time !== $t,
                                'border-ink bg-ink text-gold-300' => $time === $t,
                                'cursor-not-allowed border-dashed border-neutral-200 text-neutral-300 line-through' => ! $slot['available'],
                            ])>
                            <span class="block font-semibold">{{ $slot['start']->format('H\hi') }} – {{ $slot['end']->format('H\hi') }}</span>
                            @if ($slot['available'] && ($local = $this->localTime($slot['start'])))
                                <span class="mt-0.5 block text-[11px] opacity-70">{{ $local }} chez vous</span>
                            @endif
                        </button>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-neutral-500">Une marge de préparation de {{ setting('buffer_minutes') }} minutes est réservée entre deux émissions.</p>
            @endif
        @endif

        {{-- ÉTAPE 5 : INFORMATIONS PERSONNELLES --}}
        @if ($step === 5)
            <x-wizard.heading step="5" title="Vos informations" subtitle="Elles nous permettent de vous contacter pour organiser votre passage." />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Nom" model="lastName" autocomplete="family-name" required />
                <x-form.input label="Prénom" model="firstName" autocomplete="given-name" required />
                <x-form.select label="Pays" model="country" live required>
                    <option value="">Sélectionner…</option>
                    @foreach (array_keys(config('ocd.countries')) as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                </x-form.select>
                <x-form.input label="Ville" model="city" autocomplete="address-level2" required />
                <x-form.input label="Téléphone" model="phone" type="tel" autocomplete="tel" placeholder="+228 90 00 00 00" required />
                <x-form.input label="WhatsApp" model="whatsapp" type="tel" placeholder="+237 6 00 00 00 00" required />
                <div class="sm:col-span-2"><x-form.input label="Adresse e-mail" model="email" type="email" autocomplete="email" required /></div>
            </div>

            @if ($mode === 'REMOTE')
                <div class="mt-8 rounded-2xl border border-gold-200 bg-gold-50/50 p-5 sm:p-6">
                    <p class="mb-1 flex items-center gap-2 font-semibold text-ink"><x-icon name="globe" class="h-5 w-5 text-gold-600" />Participation à distance</p>
                    <p class="mb-5 text-sm text-neutral-600">Notre assistante commerciale basée à Lomé vous accompagne avant et pendant l'émission.</p>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.select label="Votre fuseau horaire" model="timezone" live required>
                            @foreach (collect(config('ocd.countries'))->unique()->sort() as $tz) <option value="{{ $tz }}">{{ $tz }}</option> @endforeach
                        </x-form.select>
                        <x-form.select label="Type de connexion internet" model="connectionType" required>
                            <option value="">Sélectionner…</option>
                            @foreach (config('ocd.connection_types') as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                        </x-form.select>
                        <label class="flex items-start gap-3 sm:col-span-2">
                            <input type="checkbox" wire:model.live="needsAssistance" class="mt-1 h-5 w-5 rounded border-neutral-300 text-ink focus:ring-gold-500">
                            <span class="text-sm text-neutral-700">J'ai besoin d'une assistance (installation, test de connexion, cadrage, éclairage…)</span>
                        </label>
                        @if ($needsAssistance)
                            <div class="sm:col-span-2"><x-form.textarea label="Précisez votre besoin" model="assistanceNotes" rows="3" /></div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- ÉTAPE 6 : INFORMATIONS COMMERCIALES --}}
        @if ($step === 6)
            <x-wizard.heading step="6" title="Votre business" subtitle="Ces informations seront présentées à l'antenne pour faire connaître votre activité." />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Nom du business" model="businessName" required />
                <x-form.select label="Secteur d'activité" model="sector" live required>
                    <option value="">Sélectionner…</option>
                    @foreach (config('ocd.sectors') as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                </x-form.select>
                <div class="sm:col-span-2"><x-form.textarea label="Description de l'activité" model="description" rows="4" hint="20 caractères minimum." required /></div>
                <div class="sm:col-span-2"><x-form.textarea label="Nature des produits ou services" model="offerNature" rows="2" required /></div>
                <x-form.input label="Téléphone commercial" model="businessPhone" type="tel" required />
                <x-form.input label="Adresse commerciale" model="businessAddress" required />
            </div>

            <p class="mb-3 mt-8 text-sm font-semibold text-ink">Réseaux sociaux <span class="font-normal text-neutral-400">(facultatif)</span></p>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.input label="Facebook" model="socials.facebook" placeholder="facebook.com/…" />
                <x-form.input label="Instagram" model="socials.instagram" placeholder="@votrecompte" />
                <x-form.input label="TikTok" model="socials.tiktok" placeholder="@votrecompte" />
                <x-form.input label="Site web" model="socials.website" placeholder="https://…" />
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="mb-2 text-sm font-semibold text-ink">Logo <span class="font-normal text-neutral-400">(JPG, PNG, WebP – 4 Mo max)</span></p>
                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-dashed border-neutral-300 p-4 hover:border-gold-500">
                        @if ($logo && method_exists($logo, 'isPreviewable') && $logo->isPreviewable())
                            <img src="{{ $logo->temporaryUrl() }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                        @else
                            <span class="grid h-14 w-14 place-items-center rounded-xl bg-neutral-100 text-neutral-400"><x-icon name="image" class="h-6 w-6" /></span>
                        @endif
                        <span class="text-sm text-neutral-600">{{ $logo ? 'Changer le logo' : 'Ajouter votre logo' }}</span>
                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp" class="sr-only">
                    </label>
                    @error('logo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-ink">Photos des produits <span class="font-normal text-neutral-400">({{ config('ocd.uploads.max_product_photos') }} max)</span></p>
                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-dashed border-neutral-300 p-4 hover:border-gold-500">
                        <span class="grid h-14 w-14 place-items-center rounded-xl bg-neutral-100 text-neutral-400"><x-icon name="photos" class="h-6 w-6" /></span>
                        <span class="text-sm text-neutral-600">Ajouter des photos</span>
                        <input type="file" wire:model="photos" multiple accept="image/png,image/jpeg,image/webp" class="sr-only">
                    </label>
                    @if ($photos)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($photos as $i => $photo)
                                <div class="relative" wire:key="ph-{{ $i }}">
                                    @if (method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                                        <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-16 w-16 rounded-lg object-cover">
                                    @endif
                                    <button type="button" wire:click="removePhoto({{ $i }})" class="absolute -right-1.5 -top-1.5 grid h-5 w-5 place-items-center rounded-full bg-ink text-xs text-white" aria-label="Retirer">×</button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('photos') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('photos.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input label="Nombre de produits à présenter" model="productsCount" type="number" min="1" required
                        hint="{{ $this->selectedPackage?->max_products ? 'Maximum '.$this->selectedPackage->max_products.' pour ce package.' : '' }}" />
                </div>
                <p class="mb-3 mt-6 text-sm font-semibold text-ink">Produits et quantités disponibles</p>
                <div class="space-y-3">
                    @foreach ($products as $i => $product)
                        <div class="flex gap-2" wire:key="prod-{{ $i }}">
                            <input type="text" wire:model="products.{{ $i }}.name" placeholder="Produit" aria-label="Nom du produit" class="field flex-1">
                            <input type="number" min="0" wire:model="products.{{ $i }}.quantity" placeholder="Qté" aria-label="Quantité disponible" class="field w-24">
                            <button type="button" wire:click="removeProduct({{ $i }})" class="grid w-11 shrink-0 place-items-center rounded-xl border border-neutral-200 text-neutral-400 hover:text-ink" aria-label="Retirer le produit"><x-icon name="trash" class="h-4 w-4" /></button>
                        </div>
                        @error("products.$i.quantity") <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @endforeach
                </div>
                <button type="button" wire:click="addProduct" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-gold-700 hover:text-ink"><x-icon name="plus" class="h-4 w-4" />Ajouter un produit</button>
            </div>

            @if ($this->isFood)
                <div class="mt-8 rounded-2xl border border-gold-200 bg-gold-50/50 p-5 sm:p-6">
                    <p class="mb-4 font-semibold text-ink">Produits alimentaires</p>
                    <div class="grid gap-5">
                        <x-form.textarea label="Préparation des produits" model="foodPreparation" rows="2" hint="Préparés sur place, à l'avance, emballés…" required />
                        <x-form.textarea label="Conservation" model="foodConservation" rows="2" hint="Température, durée de conservation, chaîne du froid…" required />
                        <x-form.textarea label="Conditions de présentation" model="foodPresentation" rows="2" hint="Matériel nécessaire, dressage, dégustation…" />
                    </div>
                </div>
            @endif
        @endif

        {{-- ÉTAPE 7 : RÉCAPITULATIF --}}
        @if ($step === 7)
            <x-wizard.heading step="7" title="Vérifiez votre demande" subtitle="Vous pourrez encore échanger avec notre équipe après l'envoi." />

            <dl class="divide-y divide-neutral-100 rounded-2xl border border-neutral-200">
                @foreach ([
                    'Client' => $firstName.' '.$lastName,
                    'Contact' => $phone.' · WhatsApp '.$whatsapp.' · '.$email,
                    'Business' => $businessName.' — '.$sector,
                    'Pays' => $country.', '.$city,
                    'Mode de participation' => \App\Enums\ParticipationMode::from($mode)->label(),
                    'Date' => $start ? date_fr($start) : '',
                    'Heure' => $start ? $start->format('H\hi').' – '.$end->format('H\hi').' (heure de Lomé)'.(($l = $this->localTime($start)) ? ' · '.$l.' chez vous' : '') : '',
                    'Package' => $this->selectedPackage?->name,
                    'Durée' => $this->selectedPackage?->duration_text,
                ] as $label => $value)
                    <div class="grid gap-1 px-5 py-3.5 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-neutral-400 sm:text-sm sm:normal-case sm:tracking-normal">{{ $label }}</dt>
                        <dd class="text-ink sm:col-span-2">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="grid gap-1 bg-ink px-5 py-4 text-white sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm text-neutral-300">Prix</dt>
                    <dd class="font-display text-2xl text-gold-300 sm:col-span-2">{{ $this->selectedPackage?->formatted_price }}</dd>
                </div>
            </dl>

            <div class="mt-6"><x-form.textarea label="Message pour l'équipe (facultatif)" model="notes" rows="3" /></div>

            <div class="hidden" aria-hidden="true"><input type="text" wire:model="website" tabindex="-1" autocomplete="off"></div>

            <label class="mt-6 flex items-start gap-3">
                <input type="checkbox" wire:model="acceptTerms" class="mt-1 h-5 w-5 rounded border-neutral-300 text-ink focus:ring-gold-500">
                <span class="text-sm text-neutral-700">J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="font-semibold underline">conditions générales</a> et la <a href="{{ route('legal.privacy') }}" target="_blank" class="font-semibold underline">politique de confidentialité</a>. Je comprends que ma réservation sera confirmée après paiement et validation.</span>
            </label>
            @error('acceptTerms') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('submit') <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p> @enderror
        @endif

        {{-- ÉTAPE 8 : PAIEMENT EN ATTENTE --}}
        @if ($step === 8)
            <div class="py-4 text-center">
                <span class="mx-auto mb-6 grid h-16 w-16 place-items-center rounded-full bg-ink text-gold-400"><x-icon name="check" class="h-8 w-8" /></span>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-600">Demande enregistrée</p>
                <h2 class="mt-3 font-display text-3xl text-ink sm:text-4xl">Merci {{ $firstName }} !</h2>
                <p class="mt-3 text-neutral-600">Votre référence</p>
                <p class="mt-1 font-mono text-2xl font-semibold tracking-wider text-ink">{{ $reference }}</p>
                <p class="mx-auto mt-4 inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-1.5 text-sm font-medium text-amber-800 ring-1 ring-amber-200">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>En attente de paiement
                </p>
            </div>

            <div class="mx-auto mt-6 max-w-xl rounded-2xl border border-neutral-200 p-5 sm:p-6">
                <p class="mb-3 font-semibold text-ink">Prochaines étapes</p>
                <ol class="space-y-3 text-sm text-neutral-600">
                    @foreach ($paymentInstructions as $i => $line)
                        <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-gold-100 text-xs font-bold text-gold-800">{{ $i + 1 }}</span>{{ $line }}</li>
                    @endforeach
                </ol>
                <p class="mt-5 rounded-xl bg-neutral-50 p-3 text-xs text-neutral-500">Le paiement en ligne (Mobile Money et carte bancaire) sera disponible prochainement.</p>
            </div>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('contact_whatsapp')) }}?text={{ urlencode('Bonjour OCD Live, je viens de réserver (référence '.$reference.').') }}" target="_blank" rel="noopener" class="btn-gold"><x-icon name="whatsapp" class="h-5 w-5" />Nous écrire sur WhatsApp</a>
                <a href="{{ route('home') }}" class="btn-outline">Retour à l'accueil</a>
            </div>
        @endif

        {{-- Navigation --}}
        @if ($step > 1 && $step < 8)
            <div class="mt-10 flex flex-col-reverse gap-3 border-t border-neutral-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" wire:click="back" class="btn-ghost"><x-icon name="chevron-left" class="h-4 w-4" />Retour</button>
                @if ($step >= 5 && $step <= 6)
                    <button type="button" wire:click="next" x-on:click="$dispatch('scroll-top')" class="btn-primary">Continuer<x-icon name="chevron-right" class="h-4 w-4" /></button>
                @elseif ($step === 7)
                    <button type="button" wire:click="submit" wire:loading.attr="disabled" class="btn-gold">Valider ma demande de réservation</button>
                @endif
            </div>
        @endif
    </div>

    @if ($step < 8 && $this->selectedPackage)
        <p class="mt-4 text-center text-xs text-neutral-500">
            {{ $mode ? \App\Enums\ParticipationMode::from($mode)->label().' · ' : '' }}{{ $this->selectedPackage->name }} · {{ $this->selectedPackage->formatted_price }}
            @if ($start) · {{ date_fr($start, 'j M') }} {{ $start->format('H\hi') }} @endif
        </p>
    @endif
</div>
