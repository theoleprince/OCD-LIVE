@extends('layouts.admin')

@section('title', 'Clients')

@section('actions')
    @if (auth()->user()->role === \App\Enums\UserRole::Admin)
        <a href="{{ route('admin.customers.export') }}" class="btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /><span class="hidden sm:inline">Exporter CSV</span></a>
    @endif
@endsection

@section('content')
    <form method="GET" class="card mb-4 flex gap-2 p-4">
        <input type="search" name="q" value="{{ $term }}" placeholder="Nom, e-mail, téléphone, pays, business…" class="field-sm flex-1" aria-label="Rechercher un client">
        <button class="btn-primary btn-sm">Rechercher</button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Client</th><th>Contact</th><th>Pays</th><th>Business</th><th class="text-right">Réservations</th><th>Inscrit le</th></tr></thead>
                <tbody>
                    @forelse ($customers as $c)
                        <tr class="hover:bg-neutral-50">
                            <td><a href="{{ route('admin.customers.show', $c) }}" class="font-medium text-ink hover:text-gold-700">{{ $c->full_name }}</a>
                                @if ($c->is_demo)<span class="badge ml-1 bg-amber-50 text-amber-700 ring-amber-200">DÉMO</span>@endif</td>
                            <td><p>{{ $c->phone }}</p><p class="text-xs text-neutral-500">{{ $c->email }}</p></td>
                            <td>{{ $c->country }} · {{ $c->city }}</td>
                            <td>{{ $c->businesses->pluck('name')->implode(', ') }}</td>
                            <td class="text-right">{{ $c->bookings_count }}</td>
                            <td>{{ $c->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-neutral-500">Aucun client.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
