@extends('layouts.admin')

@section('title', 'Journal des opérations')

@section('content')
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Objet</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td>{{ __('logs.'.$log->action) }}</td>
                            <td>
                                @if ($log->subject_type === (new \App\Models\Booking)->getMorphClass() && $log->subject_id)
                                    <a href="{{ route('admin.bookings.show', $log->subject_id) }}" class="text-gold-700 hover:underline">Réservation #{{ $log->subject_id }}</a>
                                @elseif ($log->subject_type)
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                @else — @endif
                            </td>
                            <td class="font-mono text-xs text-neutral-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-neutral-500">Aucune opération enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
