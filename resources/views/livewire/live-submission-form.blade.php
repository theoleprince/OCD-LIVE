<div class="mx-auto w-full max-w-4xl">
    <div class="relative rounded-3xl border border-neutral-200 bg-white p-5 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.25)] sm:p-10">

        <div wire:loading.delay.grid style="display: none" class="absolute inset-0 z-10 place-items-center rounded-3xl bg-white/60 backdrop-blur-[1px]">
            <span class="h-8 w-8 animate-spin rounded-full border-2 border-brand-500 border-t-transparent"></span>
        </div>

        @if ($submitted)
            {{-- CONFIRMATION --}}
            <div class="py-4 text-center">
                <span class="mx-auto mb-6 grid h-16 w-16 place-items-center rounded-full bg-brand-600 text-white"><x-icon name="check" class="h-8 w-8" /></span>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Demande enregistrée</p>
                <h2 class="mt-3 font-display text-3xl text-ink sm:text-4xl">Merci {{ $fullName }} !</h2>
                <p class="mt-4 text-neutral-600">Votre demande a bien été reçue par OCD Live. Notre équipe va examiner votre diffusion avant toute publication.</p>
                <p class="mt-5 text-sm text-neutral-500">Référence de suivi</p>
                <p class="mt-1 font-mono text-2xl font-semibold tracking-wider text-ink">{{ $reference }}</p>
            </div>

            <div class="mx-auto mt-6 max-w-xl rounded-2xl border border-neutral-200 p-5 text-center sm:p-6">
                <p class="text-sm text-neutral-600">Conservez ce lien pour suivre l'avancement de votre demande à tout moment.</p>
                <p class="mt-3 break-all rounded-xl bg-neutral-50 px-4 py-3 font-mono text-sm text-ink">{{ $trackingUrl }}</p>
            </div>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $trackingUrl }}" class="btn-accent"><x-icon name="eye" class="h-5 w-5" />Suivre ma demande</a>
                <a href="{{ route('market.index') }}" class="btn-outline">Explorer les LIVE</a>
            </div>
        @else
            <x-wizard.heading step="1" title="Soumettre mon LIVE" subtitle="Quelques informations suffisent. Notre équipe vérifie chaque demande avant publication." />

            <p class="mb-3 mt-2 text-sm font-semibold text-ink">Vos informations</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Nom et prénom" model="fullName" required />
                <x-form.input label="Nom commercial / boutique" model="businessName" required />
                <x-form.input label="Téléphone ou WhatsApp" model="phone" type="tel" placeholder="+228 90 00 00 00" required />
                <x-form.select label="Pays" model="countryId" live required>
                    <option value="">Sélectionner…</option>
                    @foreach ($this->countries as $c) <option value="{{ $c->id }}">{{ $c->flag }} {{ $c->name }}</option> @endforeach
                </x-form.select>
                <x-form.select label="Ville" model="cityId" required>
                    <option value="">{{ $countryId ? 'Sélectionner…' : 'Choisissez d\'abord un pays' }}</option>
                    @foreach ($this->cities as $city) <option value="{{ $city->id }}">{{ $city->name }}</option> @endforeach
                </x-form.select>
                <div class="sm:col-span-2"><x-form.textarea label="Description de votre activité" model="description" rows="3" hint="Facultatif." /></div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-ink">Photo de profil <span class="font-normal text-neutral-400">(facultatif)</span></p>
                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-dashed border-neutral-300 p-4 hover:border-brand-500">
                        @if ($profileImage && method_exists($profileImage, 'isPreviewable') && $profileImage->isPreviewable())
                            <img src="{{ $profileImage->temporaryUrl() }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                        @else
                            <span class="grid h-14 w-14 place-items-center rounded-xl bg-neutral-100 text-neutral-400"><x-icon name="image" class="h-6 w-6" /></span>
                        @endif
                        <span class="text-sm text-neutral-600">{{ $profileImage ? 'Changer la photo' : 'Ajouter une photo' }}</span>
                        <input type="file" wire:model="profileImage" accept="image/png,image/jpeg,image/webp" class="sr-only">
                    </label>
                    @error('profileImage') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-ink">Logo commercial <span class="font-normal text-neutral-400">(facultatif)</span></p>
                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-dashed border-neutral-300 p-4 hover:border-brand-500">
                        @if ($logoImage && method_exists($logoImage, 'isPreviewable') && $logoImage->isPreviewable())
                            <img src="{{ $logoImage->temporaryUrl() }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                        @else
                            <span class="grid h-14 w-14 place-items-center rounded-xl bg-neutral-100 text-neutral-400"><x-icon name="image" class="h-6 w-6" /></span>
                        @endif
                        <span class="text-sm text-neutral-600">{{ $logoImage ? 'Changer le logo' : 'Ajouter votre logo' }}</span>
                        <input type="file" wire:model="logoImage" accept="image/png,image/jpeg,image/webp" class="sr-only">
                    </label>
                    @error('logoImage') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="mb-3 mt-8 text-sm font-semibold text-ink">Votre commerce</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.select label="Catégorie" model="categoryId" live required>
                    <option value="">Sélectionner…</option>
                    @foreach ($this->categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                </x-form.select>
                <x-form.select label="Sous-catégorie" model="subcategoryId">
                    <option value="">{{ $this->subcategories->isEmpty() ? 'Aucune sous-catégorie' : 'Sélectionner…' }}</option>
                    @foreach ($this->subcategories as $sub) <option value="{{ $sub->id }}">{{ $sub->name }}</option> @endforeach
                </x-form.select>
                <div class="sm:col-span-2"><x-form.textarea label="Description du produit ou service vendu" model="productDescription" rows="3" hint="10 caractères minimum." required /></div>
            </div>

            <p class="mb-3 mt-8 text-sm font-semibold text-ink">Votre LIVE</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="mb-1.5 block text-sm font-medium text-neutral-700">Plateforme</p>
                    <div class="field flex w-full items-center gap-2 bg-neutral-50 text-neutral-500">
                        <x-icon name="globe" class="h-4 w-4" />TikTok
                    </div>
                    <p class="mt-1 text-xs text-neutral-400">TikTok pour cette première version.</p>
                </div>
                <x-form.input label="Nom du compte TikTok" model="accountName" placeholder="@votrecompte" required />
                <div class="sm:col-span-2"><x-form.input label="Lien du LIVE ou du profil TikTok" model="liveUrl" placeholder="https://www.tiktok.com/@votrecompte/live" hint="Nous vérifions manuellement chaque diffusion : indiquer un lien ne confirme pas automatiquement qu'un live est en cours." required /></div>
                <x-form.select label="Créneau souhaité" model="scheduleId" required>
                    <option value="">Sélectionner…</option>
                    @foreach ($this->schedules as $s) <option value="{{ $s->id }}">{{ $s->label }}</option> @endforeach
                </x-form.select>
            </div>

            <div class="hidden" aria-hidden="true"><input type="text" wire:model="website" tabindex="-1" autocomplete="off"></div>

            <p class="mb-3 mt-8 text-sm font-semibold text-ink">Consentements</p>
            <div class="space-y-3">
                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="acceptTerms" class="mt-1 h-5 w-5 rounded border-neutral-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-neutral-700">J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="font-semibold underline">conditions d'utilisation</a> d'OCD Live Market.</span>
                </label>
                @error('acceptTerms') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="authorizePublication" class="mt-1 h-5 w-5 rounded border-neutral-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-neutral-700">J'autorise la publication des informations commerciales renseignées dans l'annuaire public, si ma demande est approuvée.</span>
                </label>
                @error('authorizePublication') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="confirmOwnership" class="mt-1 h-5 w-5 rounded border-neutral-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-neutral-700">Je confirme être autorisé(e) à partager ce lien et les informations transmises.</span>
                </label>
                @error('confirmOwnership') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @error('submit') <p class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p> @enderror

            <div class="mt-8 border-t border-neutral-100 pt-6 text-right">
                <button type="button" wire:click="submit" wire:loading.attr="disabled" class="btn-accent">Envoyer mon LIVE à OCD</button>
            </div>
        @endif
    </div>
</div>
