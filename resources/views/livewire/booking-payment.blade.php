<div class="mx-auto max-w-2xl">
    {{-- Récapitulatif --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-neutral-50 px-5 py-4 ring-1 ring-neutral-200">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-neutral-500">Montant à régler</p>
            <p class="font-display text-2xl text-ink">{{ money_fcfa($booking->amount) }}</p>
        </div>
        <div class="text-right text-sm text-neutral-600">
            <p class="font-mono font-semibold text-ink">{{ $booking->reference }}</p>
            <p>{{ $booking->package->name }}</p>
        </div>
    </div>

    @if ($paid)
        <div class="mt-6 rounded-2xl bg-emerald-50 p-6 text-center ring-1 ring-emerald-200">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-600 text-white"><x-icon name="check" class="h-6 w-6" /></span>
            <p class="mt-3 text-lg font-semibold text-emerald-900">Paiement reçu, merci !</p>
            <p class="mt-1 text-sm text-emerald-800">Notre équipe valide votre réservation et vous confirme votre passage par e-mail et WhatsApp.</p>
        </div>
    @elseif (! $payable)
        <div class="mt-6 rounded-2xl bg-neutral-50 p-6 text-center ring-1 ring-neutral-200">
            <p class="font-semibold text-ink">Cette réservation n'est plus en attente de paiement.</p>
            <p class="mt-1 text-sm text-neutral-600">Statut : {{ $booking->status->label() }}. Pour toute question, contactez-nous sur WhatsApp en indiquant votre référence.</p>
        </div>
    @elseif ($stage === 'waiting')
        <div wire:poll.5s="refreshStatus" class="mt-6 rounded-2xl border border-brand-200 bg-brand-50/60 p-6 text-center">
            <span class="mx-auto block h-10 w-10 animate-spin rounded-full border-4 border-brand-200 border-t-brand-600" aria-hidden="true"></span>
            <p class="mt-4 text-lg font-semibold text-ink">Validez le paiement sur votre téléphone</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-neutral-600">
                Une demande de {{ money_fcfa($booking->amount) }} a été envoyée au <strong class="text-ink">{{ $phone }}</strong> ({{ $methods[$method]['label'] ?? 'Mobile Money' }}).
                Confirmez-la avec votre code secret : cette page se met à jour automatiquement.
            </p>
            <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                <button type="button" wire:click="payOnFedaPayPage" class="btn-outline !py-2.5 text-sm">Je n'ai rien reçu : payer sur la page sécurisée</button>
                <button type="button" wire:click="changeMethod" class="btn-ghost !py-2.5 text-sm">Changer de mode de paiement</button>
            </div>
        </div>
    @elseif ($stage === 'manual')
        <div class="mt-6 rounded-2xl border border-neutral-200 p-6">
            @if ($method === 'transfer')
                <p class="flex items-center gap-2 font-semibold text-ink"><x-icon name="bank" class="h-5 w-5 text-brand-600" />Paiement par virement bancaire</p>
                @if (filled(setting('bank_transfer_details')))
                    <div class="mt-4 whitespace-pre-line rounded-xl bg-neutral-50 p-4 font-mono text-sm text-ink ring-1 ring-neutral-200">{{ setting('bank_transfer_details') }}</div>
                @else
                    <p class="mt-3 text-sm text-neutral-600">Demandez-nous nos coordonnées bancaires sur WhatsApp : nous vous les transmettons immédiatement.</p>
                @endif
                <ol class="mt-4 space-y-2 text-sm text-neutral-600">
                    <li>1. Effectuez un virement de <strong class="text-ink">{{ money_fcfa($booking->amount) }}</strong> en indiquant la référence <strong class="font-mono text-ink">{{ $booking->reference }}</strong>.</li>
                    <li>2. Envoyez-nous la preuve de virement sur WhatsApp.</li>
                    <li>3. Votre réservation est confirmée dès réception des fonds (24 à 48 h).</li>
                </ol>
            @else
                <p class="flex items-center gap-2 font-semibold text-ink"><x-icon name="cash" class="h-5 w-5 text-brand-600" />Paiement en espèces au studio</p>
                <ol class="mt-4 space-y-2 text-sm text-neutral-600">
                    <li>1. Rendez-vous au <strong class="text-ink">{{ setting('contact_address') }}</strong> avant le {{ $booking->payment_due_at ? date_fr($booking->payment_due_at, 'j F à H\hi') : 'délai indiqué' }}.</li>
                    <li>2. Réglez <strong class="text-ink">{{ money_fcfa($booking->amount) }}</strong> en indiquant la référence <strong class="font-mono text-ink">{{ $booking->reference }}</strong> ; un reçu vous est remis.</li>
                    <li>3. Prévenez-nous de votre passage sur WhatsApp pour être accueilli.</li>
                </ol>
            @endif
            <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('contact_whatsapp')) }}?text={{ urlencode('Bonjour OCD Live, je règle ma réservation '.$booking->reference.' par '.($method === 'transfer' ? 'virement' : 'espèces au studio').'.') }}" target="_blank" rel="noopener" class="btn-accent !py-2.5 text-sm"><x-icon name="whatsapp" class="h-4 w-4" />Nous écrire sur WhatsApp</a>
                <button type="button" wire:click="changeMethod" class="btn-ghost !py-2.5 text-sm">Changer de mode de paiement</button>
            </div>
        </div>
    @else
        <p class="mt-8 font-semibold text-ink">Choisissez votre mode de paiement</p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Mode de paiement">
            @foreach ($methods as $key => $m)
                <button type="button" role="radio" aria-checked="{{ $method === $key ? 'true' : 'false' }}"
                    @if ($m['available']) wire:click="choose('{{ $key }}')" @else disabled @endif
                    @class([
                        'group relative flex items-center gap-4 rounded-2xl border bg-white p-4 text-left transition',
                        'border-brand-600 ring-2 ring-brand-600/20' => $method === $key,
                        'border-neutral-200 hover:border-brand-300' => $method !== $key && $m['available'],
                        'cursor-not-allowed border-neutral-200 opacity-55' => ! $m['available'],
                    ])>
                    <span class="flex h-10 w-20 shrink-0 items-center justify-center gap-1.5">
                        @if (! empty($m['logo']))
                            <x-payment-image :src="$m['logo']" :alt="$m['label']" />
                        @elseif (! empty($m['logos']))
                            @foreach ($m['logos'] as $logo)
                                <x-payment-image :src="$logo" />
                            @endforeach
                        @else
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-sand-100 text-brand-600"><x-icon :name="$m['icon']" class="h-5 w-5" /></span>
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block font-semibold text-ink">{{ $m['label'] }}</span>
                        <span class="block text-xs text-neutral-500">{{ $m['available'] ? $m['hint'] : 'Bientôt disponible' }}</span>
                    </span>
                    <span @class(['absolute right-4 top-4 grid h-5 w-5 place-items-center rounded-full border-2', 'border-brand-600 bg-brand-600 text-white' => $method === $key, 'border-neutral-300' => $method !== $key])>
                        @if ($method === $key)<x-icon name="check" class="h-3 w-3" />@endif
                    </span>
                </button>
            @endforeach
        </div>
        @error('method') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror

        @if ($method && isset($methods[$method]))
            @php($selected = $methods[$method])
            <div class="mt-5 rounded-2xl border border-neutral-200 p-5">
                @if ($selected['type'] === 'mobile')
                    <label for="pay-phone" class="mb-1.5 block text-sm font-medium text-neutral-700">Numéro {{ $selected['label'] }} à débiter</label>
                    <div class="flex">
                        <span class="inline-flex items-center rounded-l-xl border border-r-0 border-neutral-300 bg-neutral-50 px-3 text-sm font-semibold text-neutral-600">+228</span>
                        <input id="pay-phone" type="tel" inputmode="numeric" autocomplete="tel-national" wire:model="phone" placeholder="90 12 34 56" class="field w-full !rounded-l-none" @error('phone') aria-invalid="true" @enderror>
                    </div>
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-neutral-500">Vous recevrez une demande de confirmation sur ce téléphone : validez-la avec votre code secret Mobile Money.</p>
                    <button type="button" wire:click="pay" wire:loading.attr="disabled" class="btn-accent mt-4 w-full justify-center">
                        <span wire:loading.remove wire:target="pay">Payer {{ money_fcfa($booking->amount) }}</span>
                        <span wire:loading wire:target="pay">Envoi de la demande…</span>
                    </button>
                @elseif ($selected['type'] === 'card')
                    <p class="text-sm text-neutral-600">Vous allez être redirigé vers la page de paiement sécurisée FedaPay pour saisir votre carte Visa ou Mastercard, puis ramené ici automatiquement.</p>
                    <button type="button" wire:click="pay" wire:loading.attr="disabled" class="btn-accent mt-4 w-full justify-center">
                        <span wire:loading.remove wire:target="pay">Payer {{ money_fcfa($booking->amount) }} par carte</span>
                        <span wire:loading wire:target="pay">Redirection…</span>
                    </button>
                @else
                    <p class="text-sm text-neutral-600">{{ $method === 'transfer' ? 'Vous effectuez le virement depuis votre banque ; votre réservation est confirmée à réception des fonds.' : 'Vous réglez directement au studio OCD Live à Lomé, avant la date limite de paiement.' }}</p>
                    <button type="button" wire:click="pay" class="btn-primary mt-4 w-full justify-center">Continuer avec ce mode</button>
                @endif
            </div>
        @endif

        <p class="mt-5 flex items-center justify-center gap-2 text-xs text-neutral-500">
            <x-icon name="shield" class="h-4 w-4 text-emerald-600" />
            @if ($onlineAvailable)
                Paiements en ligne sécurisés par FedaPay. OCD Live n'a jamais accès à vos codes ni à votre carte.
            @else
                Le paiement en ligne (Moov Money, Mixx by Yas, carte) arrive très bientôt.
            @endif
        </p>
        @if ($booking->payment_due_at)
            <p class="mt-1 text-center text-xs text-neutral-500">Créneau réservé jusqu'au {{ date_fr($booking->payment_due_at, 'j F Y à H\hi') }} en l'absence de paiement.</p>
        @endif
    @endif
</div>
