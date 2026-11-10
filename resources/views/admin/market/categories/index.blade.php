@extends('layouts.admin')

@section('title', 'Catégories commerciales')

@section('content')
    <section class="card mb-4 p-5 sm:p-6">
        <h2 class="font-semibold text-ink">Ajouter une catégorie ou sous-catégorie</h2>
        <form method="POST" action="{{ route('admin.market.categories.store') }}" class="mt-3 grid gap-3 sm:grid-cols-3">
            @csrf
            <input name="name" required placeholder="Nom" class="field-sm" maxlength="120">
            <select name="parent_id" class="field-sm">
                <option value="">Catégorie principale (aucun parent)</option>
                @foreach ($topLevel as $t) <option value="{{ $t->id }}">Sous-catégorie de : {{ $t->name }}</option> @endforeach
            </select>
            <button class="btn-primary btn-sm">Ajouter</button>
        </form>
    </section>

    <div class="space-y-3">
        @foreach ($categories as $category)
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2 font-semibold text-ink">{{ $category->name }} <span class="badge {{ $category->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-neutral-100 text-neutral-500 ring-neutral-200' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></span>
                    <form method="POST" action="{{ route('admin.market.categories.toggle', $category) }}">
                        @csrf<button class="btn-outline btn-sm">{{ $category->is_active ? 'Désactiver' : 'Activer' }}</button>
                    </form>
                </div>
                @if ($category->children->isNotEmpty())
                    <ul class="mt-3 flex flex-wrap gap-2 border-t border-neutral-100 pt-3">
                        @foreach ($category->children as $child)
                            <li class="inline-flex items-center gap-2 rounded-full border border-neutral-200 px-3 py-1 text-xs">
                                {{ $child->name }}
                                <form method="POST" action="{{ route('admin.market.categories.toggle', $child) }}" class="inline">
                                    @csrf<button class="{{ $child->is_active ? 'text-emerald-600' : 'text-neutral-400' }}">{{ $child->is_active ? '●' : '○' }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
@endsection
