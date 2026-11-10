@props(['live'])
<article class="card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-sand-100 to-sand-200">
        @if ($live->seller->profile_image)
            <img src="{{ asset('storage/'.$live->seller->profile_image) }}" alt="{{ $live->seller->business_name }}" loading="lazy" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full flex-col items-center justify-center gap-2 text-brand-700/70">
                <span class="grid h-12 w-12 place-items-center rounded-xl bg-white/60 text-brand-600"><x-icon name="users" class="h-6 w-6" /></span>
            </div>
        @endif
        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-2.5 py-1 text-[11px] font-extrabold text-white shadow">
            <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse-soft"></span>EN DIRECT
        </span>
    </div>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $live->category->name }}</p>
        <h3 class="mt-1 font-display text-lg text-ink">{{ $live->seller->business_name }}</h3>
        <p class="text-sm text-neutral-500">{{ $live->seller->full_name }}</p>
        <p class="mt-2 flex items-center gap-1.5 text-sm text-neutral-500"><x-icon name="map" class="h-4 w-4" />{{ $live->country->flag }} {{ $live->city->name }}, {{ $live->country->name }}</p>
        <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-neutral-600">{{ $live->product_description }}</p>

        <p class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
            <x-icon name="shield" class="h-4 w-4" />Vérifié par OCD Live · {{ $live->approved_at?->format('d/m/Y H:i') }}
        </p>

        <a href="{{ $live->live_url }}" target="_blank" rel="noopener" class="btn-accent mt-4 w-full">
            <x-icon name="arrow-right" class="h-4 w-4" />Voir le LIVE
        </a>

        <details class="mt-2 text-xs text-neutral-400">
            <summary class="cursor-pointer select-none hover:text-neutral-600">Signaler ce contenu</summary>
            <form method="POST" action="{{ route('market.report', $live) }}" class="mt-2 space-y-2">
                @csrf
                <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                <select name="reason" required class="field-sm w-full">
                    <option value="">Motif…</option>
                    <option value="produit_illicite">Produit illicite ou contrefaçon</option>
                    <option value="contenu_trompeur">Contenu trompeur ou escroquerie</option>
                    <option value="live_inactif">Le live n'est pas actif</option>
                    <option value="autre">Autre</option>
                </select>
                <textarea name="description" rows="2" placeholder="Précisez (facultatif)" class="field-sm w-full"></textarea>
                <button type="submit" class="btn-outline btn-sm w-full">Envoyer le signalement</button>
            </form>
        </details>
    </div>
</article>
