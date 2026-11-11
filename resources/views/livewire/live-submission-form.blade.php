<div class="mx-auto w-full max-w-2xl">
    <div class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-lg sm:p-8">
        @if ($submitted)
            <div class="py-4 text-center">
                <span class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-brand-600 text-white"><x-icon name="check" class="h-8 w-8" /></span>
                <h2 class="font-display text-3xl text-ink">Votre demande est envoyée !</h2>
                <p class="mt-4 text-neutral-600">Notre équipe va vérifier votre live avant de le publier.</p>
                <p class="mt-5 text-sm text-neutral-500">Votre référence</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ $reference }}</p>
                <p class="mt-5 text-sm text-neutral-600">Gardez ce lien pour retrouver votre demande.</p>
                <a href="{{ $trackingUrl }}" class="mt-3 block break-all rounded-xl bg-neutral-50 p-3 text-sm text-brand-700 underline">{{ $trackingUrl }}</a>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ $trackingUrl }}" class="btn-accent">Suivre ma demande</a>
                    <a href="{{ route('market.index') }}" class="btn-outline">Retour au Market</a>
                </div>
            </div>
        @else
            <form wire:key="market-form-{{ $step }}" wire:submit="{{ $step < 4 ? 'next' : 'submit' }}" novalidate>
                <p class="text-sm font-semibold text-brand-700" aria-live="polite">Étape {{ $step }} sur 4</p>
                <div class="mt-3 flex gap-2" aria-hidden="true">
                    @for ($i = 1; $i <= 4; $i++)
                        <span class="h-1.5 flex-1 rounded-full {{ $i <= $step ? 'bg-brand-600' : 'bg-neutral-100' }}"></span>
                    @endfor
                </div>
                <div wire:key="market-step-{{ $step }}" class="mt-7">
                    @if ($step === 1)
                        <h2 class="font-display text-2xl text-ink">Que vendez-vous en live ?</h2>
                        <p class="mb-6 mt-2 text-sm text-neutral-500">Copiez le lien de votre compte ou de votre live depuis TikTok.</p>
                        <div class="space-y-5">
                            <x-form.input label="Votre lien TikTok" model="liveUrl" type="url" placeholder="https://www.tiktok.com/@votreboutique/live" required />
                            <x-form.textarea label="Que proposez-vous ?" model="productDescription" rows="3" placeholder="Exemple : robes, chemises et tissus africains." required />
                        </div>
                    @elseif ($step === 2)
                        <h2 class="font-display text-2xl text-ink">Comment vous contacter ?</h2>
                        <p class="mb-6 mt-2 text-sm text-neutral-500">Un seul numéro suffit pour vous joindre.</p>
                        <div class="space-y-5">
                            <x-form.input label="Nom de votre boutique ou de votre activité" model="businessName" placeholder="Exemple : Boutique Ama" required />
                            <x-form.input label="Téléphone ou WhatsApp" model="phone" type="tel" autocomplete="tel" placeholder="+228 90 00 00 00" required />
                        </div>
                    @elseif ($step === 3)
                        <h2 class="font-display text-2xl text-ink">Où êtes-vous ?</h2>
                        <p class="mb-6 mt-2 text-sm text-neutral-500">Les visiteurs pourront vous retrouver près de chez eux.</p>
                        <div class="space-y-5">
                            <x-form.select label="Pays" model="countryId" live required>
                                <option value="">Choisir mon pays</option>
                                @foreach ($this->countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->flag }} {{ $country->name }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.select label="Ville" model="cityId" required>
                                <option value="">{{ $countryId ? 'Choisir ma ville' : 'Choisissez d’abord votre pays' }}</option>
                                @foreach ($this->cities as $city)<option value="{{ $city->id }}">{{ $city->name }}</option>@endforeach
                            </x-form.select>
                        </div>
                    @else
                        <h2 class="font-display text-2xl text-ink">Vous y êtes presque !</h2>
                        <p class="mb-6 mt-2 text-sm text-neutral-500">Choisissez la rubrique dans laquelle apparaîtra votre live.</p>
                        <x-form.select label="Votre activité" model="categoryId" live required>
                            <option value="">Choisir une activité</option>
                            @foreach ($this->categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
                        </x-form.select>
                        <details class="mt-6 rounded-2xl border border-neutral-200 p-4" @if($errors->hasAny(['fullName', 'description', 'profileImage', 'logoImage', 'accountName', 'scheduleId', 'subcategoryId'])) open @endif>
                            <summary class="cursor-pointer text-sm font-semibold text-brand-700">Ajouter une photo ou des précisions (facultatif)</summary>
                            <div class="mt-5 space-y-5">
                                <x-form.input label="Votre nom" model="fullName" autocomplete="name" />
                                <x-form.textarea label="Quelques mots sur votre boutique" model="description" rows="2" />
                                <div>
                                    <label for="market-photo" class="mb-2 block text-sm font-medium text-neutral-700">Photo de votre activité</label>
                                    <input id="market-photo" type="file" wire:model="profileImage" accept="image/png,image/jpeg,image/webp" class="field w-full">
                                    @error('profileImage') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="market-logo" class="mb-2 block text-sm font-medium text-neutral-700">Logo</label>
                                    <input id="market-logo" type="file" wire:model="logoImage" accept="image/png,image/jpeg,image/webp" class="field w-full">
                                    @error('logoImage') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <x-form.input label="Nom du compte TikTok" model="accountName" placeholder="@votreboutique" hint="Récupéré depuis votre lien habituel. À préciser si vous utilisez un lien TikTok raccourci." />
                                @if ($this->subcategories->isNotEmpty())
                                    <x-form.select label="Préciser votre activité" model="subcategoryId">
                                        <option value="">Ne pas préciser</option>
                                        @foreach ($this->subcategories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
                                    </x-form.select>
                                @endif
                                <x-form.select label="Moment préféré pour votre live" model="scheduleId">
                                    <option value="">Ne pas préciser</option>
                                    @foreach ($this->schedules as $schedule)<option value="{{ $schedule->id }}">{{ $schedule->label }}</option>@endforeach
                                </x-form.select>
                            </div>
                        </details>
                        <label class="mt-6 flex items-start gap-3">
                            <input type="checkbox" wire:model="acceptAll" class="mt-1 h-5 w-5 shrink-0 rounded border-neutral-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm leading-relaxed text-neutral-700">J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="font-semibold underline">conditions d'utilisation</a>, j'autorise la publication de mes informations commerciales et je confirme avoir le droit de partager ce live.</span>
                        </label>
                        @if ($errors->hasAny(['acceptTerms', 'authorizePublication', 'confirmOwnership']))
                            <p class="mt-2 text-sm text-red-600">Cochez la case pour autoriser l'envoi et la publication de votre demande.</p>
                        @endif
                    @endif
                </div>
                <div class="hidden" aria-hidden="true"><input type="text" wire:model="website" tabindex="-1" autocomplete="off"></div>
                @error('submit') <p class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $message }}</p> @enderror
                <div class="mt-8 flex items-center justify-between gap-3 border-t border-neutral-100 pt-6">
                    @if ($step > 1)
                        <button type="button" wire:click="back" wire:loading.attr="disabled" class="btn-ghost">Retour</button>
                    @else
                        <a href="{{ route('market.index') }}" class="btn-ghost">Retour au Market</a>
                    @endif
                    <button type="submit" wire:loading.attr="disabled" class="btn-accent">
                        <span wire:loading.remove>{{ $step < 4 ? 'Continuer' : 'Envoyer mon live' }}</span>
                        <span wire:loading>Un instant…</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
