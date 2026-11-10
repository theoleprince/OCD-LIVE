@extends('layouts.admin')

@section('title', 'Publications actives')

@section('content')
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Référence</th><th>Vendeur</th><th>Pays</th><th>Catégorie</th><th>Modérateur</th><th>Validée le</th><th>Expire le</th><th></th></tr></thead>
                <tbody>
                    @forelse ($lives as $live)
                        <tr class="hover:bg-neutral-50">
                            <td><a href="{{ route('admin.market.submissions.show', $live) }}" class="font-mono font-semibold text-ink hover:text-brand-700">{{ $live->reference }}</a></td>
                            <td>{{ $live->seller->business_name }}</td>
                            <td>{{ $live->country->flag }} {{ $live->city->name }}</td>
                            <td>{{ $live->category->name }}</td>
                            <td>{{ $live->moderator?->name ?? '—' }}</td>
                            <td>{{ $live->approved_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $live->expires_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.market.lives.remove', $live) }}" onsubmit="return confirm('Retirer immédiatement cette publication ?')">
                                    @csrf<button class="text-xs text-red-600 hover:underline">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-neutral-500">Aucune publication active pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $lives->links() }}</div>
@endsection
