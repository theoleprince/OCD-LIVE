@extends('layouts.admin')

@section('title', $package->exists ? 'Modifier '.$package->name : 'Nouveau package')

@section('content')
    <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="card max-w-3xl space-y-5 p-5 sm:p-8">
        @csrf
        @if ($package->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium">Nom *</label>
                <input id="name" name="name" value="{{ old('name', $package->name) }}" required class="field w-full">
            </div>
            <div>
                <label for="slug" class="mb-1.5 block text-sm font-medium">Identifiant URL</label>
                <input id="slug" name="slug" value="{{ old('slug', $package->slug) }}" placeholder="généré automatiquement" class="field w-full">
            </div>
            <div class="sm:col-span-2">
                <label for="tagline" class="mb-1.5 block text-sm font-medium">Accroche</label>
                <input id="tagline" name="tagline" value="{{ old('tagline', $package->tagline) }}" class="field w-full">
            </div>
            <div>
                <label for="price" class="mb-1.5 block text-sm font-medium">Prix (FCFA) *</label>
                <input id="price" name="price" type="number" min="0" step="500" value="{{ old('price', $package->price) }}" required class="field w-full">
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="hidden" name="price_validated" value="0"><input type="checkbox" name="price_validated" value="1" @checked(old('price_validated', $package->price_validated)) class="rounded">Tarif validé par la direction</label>
            </div>
            <div>
                <label for="duration_minutes" class="mb-1.5 block text-sm font-medium">Durée du créneau en direct (minutes) *</label>
                <input id="duration_minutes" name="duration_minutes" type="number" min="15" max="720" step="15" value="{{ old('duration_minutes', $package->duration_minutes) }}" required class="field w-full">
                <p class="mt-1 text-xs text-neutral-500">Utilisée pour bloquer le calendrier (ex. 90 = 1 h 30).</p>
            </div>
            <div>
                <label for="duration_label" class="mb-1.5 block text-sm font-medium">Libellé de durée affiché</label>
                <input id="duration_label" name="duration_label" value="{{ old('duration_label', $package->duration_label) }}" placeholder="ex. Un mois" class="field w-full">
            </div>
            <div>
                <label for="max_products" class="mb-1.5 block text-sm font-medium">Nombre max. de produits</label>
                <input id="max_products" name="max_products" type="number" min="1" value="{{ old('max_products', $package->max_products) }}" placeholder="illimité" class="field w-full">
            </div>
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium">Description</label>
            <textarea id="description" name="description" rows="3" class="field w-full">{{ old('description', $package->description) }}</textarea>
        </div>
        <div>
            <label for="features_text" class="mb-1.5 block text-sm font-medium">Prestations (une par ligne)</label>
            <textarea id="features_text" name="features_text" rows="6" class="field w-full">{{ old('features_text', implode("\n", $package->features ?? [])) }}</textarea>
        </div>
        <div>
            <label for="terms" class="mb-1.5 block text-sm font-medium">Modalités particulières</label>
            <textarea id="terms" name="terms" rows="3" class="field w-full" placeholder="Conditions, calendrier contractuel, disponibilité de l'animateur…">{{ old('terms', $package->terms) }}</textarea>
        </div>

        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label for="sort_order" class="mb-1.5 block text-sm font-medium">Ordre d'affichage *</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $package->sort_order) }}" required class="field w-full">
            </div>
            <label class="flex items-center gap-2 text-sm sm:pt-8"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active)) class="rounded">Actif (proposé à la réservation)</label>
            <label class="flex items-center gap-2 text-sm sm:pt-8"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $package->is_featured)) class="rounded">Mis en avant</label>
        </div>

        <div class="flex gap-3 border-t border-neutral-100 pt-5">
            <button class="btn-primary">Enregistrer</button>
            <a href="{{ route('admin.packages.index') }}" class="btn-ghost">Annuler</a>
        </div>
    </form>
@endsection
