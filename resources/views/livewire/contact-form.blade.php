<div class="card p-6 sm:p-8">
    @if ($sent)
        <div class="flex flex-col items-center gap-3 py-10 text-center">
            <span class="grid h-14 w-14 place-items-center rounded-full bg-accent-100 text-accent-600"><x-icon name="check" class="h-7 w-7" /></span>
            <p class="font-display text-xl text-ink">Message envoyé !</p>
            <p class="max-w-sm text-sm text-slate-500">Merci, notre équipe à Lomé revient vers vous rapidement.</p>
            <button type="button" wire:click="$set('sent', false)" class="btn-ghost mt-2">Envoyer un autre message</button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <p class="font-display text-xl text-ink">Écrivez-nous</p>
            <x-form.input label="Votre e-mail" model="email" type="email" autocomplete="email" hint="Pour recevoir notre réponse." required />
            <x-form.textarea label="Votre message" model="message" rows="4" required />
            <details class="rounded-xl border border-neutral-200 p-4" @if($errors->hasAny(['fullName', 'phone'])) open @endif>
                <summary class="cursor-pointer text-sm font-semibold text-brand-700">Ajouter mon nom ou mon téléphone (facultatif)</summary>
                <div class="mt-4 space-y-4">
                    <x-form.input label="Votre nom" model="fullName" autocomplete="name" />
                    <x-form.input label="Téléphone" model="phone" type="tel" autocomplete="tel" />
                </div>
            </details>
            <div class="hidden" aria-hidden="true"><input type="text" wire:model="website" tabindex="-1" autocomplete="off"></div>
            @error('submit') <p class="text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Envoyer le message</span>
                <span wire:loading wire:target="submit">Envoi en cours…</span>
            </button>
        </form>
    @endif
</div>
