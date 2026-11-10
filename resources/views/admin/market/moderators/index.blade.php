@extends('layouts.admin')

@section('title', 'Modérateurs')

@section('content')
    @if (session('generated_password'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Mot de passe généré pour <strong>{{ session('generated_for') }}</strong> (affiché une seule fois) :
            <span class="font-mono font-semibold">{{ session('generated_password') }}</span>
        </div>
    @endif

    <section class="card mb-4 p-5 sm:p-6">
        <h2 class="font-semibold text-ink">Ajouter un modérateur</h2>
        <form method="POST" action="{{ route('admin.market.moderators.store') }}" class="mt-3 grid gap-3 sm:grid-cols-3">
            @csrf
            <input name="name" required placeholder="Nom" class="field-sm" maxlength="150">
            <input name="email" type="email" required placeholder="E-mail" class="field-sm" maxlength="190">
            <button class="btn-primary btn-sm">Créer</button>
        </form>
    </section>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead><tr><th>Nom</th><th>E-mail</th><th>Statut</th><th>Dernière connexion</th><th></th></tr></thead>
            <tbody>
                @forelse ($moderators as $m)
                    <tr>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->email }}</td>
                        <td><span class="badge {{ $m->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-neutral-100 text-neutral-500 ring-neutral-200' }}">{{ $m->is_active ? 'Actif' : 'Désactivé' }}</span></td>
                        <td>{{ $m->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="flex gap-3">
                            <form method="POST" action="{{ route('admin.market.moderators.toggle', $m) }}">
                                @csrf<button class="text-xs text-brand-700 hover:underline">{{ $m->is_active ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.market.moderators.reset', $m) }}" onsubmit="return confirm('Réinitialiser le mot de passe de {{ $m->email }} ?')">
                                @csrf<button class="text-xs text-neutral-500 hover:underline">Réinitialiser le mot de passe</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-12 text-center text-neutral-500">Aucun modérateur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
