@extends('layouts.admin')

@section('title', 'Signalements')

@section('content')
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Demande</th><th>Motif</th><th>Description</th><th>Contact</th><th>Statut</th><th>Reçu le</th><th></th></tr></thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr class="hover:bg-neutral-50">
                            <td><a href="{{ route('admin.market.submissions.show', $report->submission) }}" class="font-mono font-semibold text-ink hover:text-brand-700">{{ $report->submission->reference }}</a></td>
                            <td>{{ $report->reason }}</td>
                            <td class="max-w-xs truncate">{{ $report->description }}</td>
                            <td>{{ $report->reporter_contact ?? '—' }}</td>
                            <td><span class="badge {{ $report->status === 'OPEN' ? 'bg-amber-50 text-amber-800 ring-amber-200' : 'bg-emerald-50 text-emerald-800 ring-emerald-200' }}">{{ $report->status === 'OPEN' ? 'Ouvert' : 'Résolu' }}</span></td>
                            <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($report->status === 'OPEN')
                                    <form method="POST" action="{{ route('admin.market.reports.resolve', $report) }}">
                                        @csrf<button class="text-xs text-brand-700 hover:underline">Marquer résolu</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-neutral-500">Aucun signalement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $reports->links() }}</div>
@endsection
