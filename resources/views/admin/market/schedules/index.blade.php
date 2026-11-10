@extends('layouts.admin')

@section('title', 'Créneaux horaires')

@section('content')
    <section class="card mb-4 p-5 sm:p-6">
        <h2 class="font-semibold text-ink">Ajouter un créneau</h2>
        <p class="mt-1 text-xs text-neutral-400">Les horaires sont interprétés dans le fuseau du pays du vendeur.</p>
        <form method="POST" action="{{ route('admin.market.schedules.store') }}" class="mt-3 grid gap-3 sm:grid-cols-4">
            @csrf
            <input name="name" required placeholder="Nom (ex. Matin)" class="field-sm" maxlength="60">
            <input name="start_time" type="time" required class="field-sm">
            <input name="end_time" type="time" required class="field-sm">
            <button class="btn-primary btn-sm">Ajouter</button>
        </form>
    </section>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead><tr><th>Nom</th><th>Horaire</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @foreach ($schedules as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ substr($s->start_time, 0, 5) }} – {{ substr($s->end_time, 0, 5) }}</td>
                        <td><span class="badge {{ $s->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-neutral-100 text-neutral-500 ring-neutral-200' }}">{{ $s->is_active ? 'Actif' : 'Inactif' }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.market.schedules.toggle', $s) }}">
                                @csrf<button class="text-xs text-brand-700 hover:underline">{{ $s->is_active ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
