@extends('layouts.admin')

@section('title', 'Demandes OCD LIVE MARKET')

@section('content')
    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Référence, vendeur, compte TikTok…" class="field-sm lg:col-span-2" aria-label="Rechercher">
        <select name="status" class="field-sm" aria-label="Statut">
            <option value="">Tous les statuts</option>
            @foreach (\App\Enums\LiveSubmissionStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="country" class="field-sm" aria-label="Pays">
            <option value="">Tous les pays</option>
            @foreach ($countries as $c) <option value="{{ $c->id }}" @selected((int) ($filters['country'] ?? 0) === $c->id)>{{ $c->flag }} {{ $c->name }}</option> @endforeach
        </select>
        <select name="category" class="field-sm" aria-label="Catégorie">
            <option value="">Toutes les catégories</option>
            @foreach ($categories as $cat) <option value="{{ $cat->id }}" @selected((int) ($filters['category'] ?? 0) === $cat->id)>{{ $cat->name }}</option> @endforeach
        </select>
        <div class="flex gap-2">
            <button class="btn-primary btn-sm flex-1">Filtrer</button>
            @if (array_filter($filters))<a href="{{ route('admin.market.submissions.index') }}" class="btn-outline btn-sm">×</a>@endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Référence</th><th>Vendeur</th><th>Pays</th><th>Catégorie</th><th>Créneau souhaité</th><th>Statut</th><th>Modérateur</th><th>Soumise le</th></tr></thead>
                <tbody>
                    @forelse ($submissions as $s)
                        <tr class="cursor-pointer hover:bg-neutral-50" onclick="window.location='{{ route('admin.market.submissions.show', $s) }}'">
                            <td><a href="{{ route('admin.market.submissions.show', $s) }}" class="font-mono font-semibold text-ink hover:text-brand-700">{{ $s->reference }}</a></td>
                            <td>{{ $s->seller->business_name }}</td>
                            <td>{{ $s->country->flag }} {{ $s->country->name }}</td>
                            <td>{{ $s->category->name }}</td>
                            <td>{{ $s->schedule?->label }}</td>
                            <td><span class="badge {{ $s->status->badgeClasses() }}">{{ $s->status->label() }}</span></td>
                            <td>{{ $s->moderator?->name ?? '—' }}</td>
                            <td>{{ $s->submitted_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-neutral-500">Aucune demande ne correspond à ces critères.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $submissions->links() }}</div>
@endsection
