@extends('layouts.admin')

@section('title', 'Notifications')

@section('actions')
    <form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf<button class="btn-outline btn-sm">Tout marquer comme lu</button></form>
@endsection

@section('content')
    <div class="space-y-3">
        @forelse ($notifications as $n)
            <article class="card p-5 {{ $n->read_at ? '' : 'border-gold-400 ring-1 ring-gold-400/30' }}">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="flex items-center gap-2 font-semibold text-ink">
                            @unless ($n->read_at)<span class="h-2 w-2 rounded-full bg-gold-500"></span>@endunless
                            {{ $n->data['title'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-neutral-500">{{ $n->created_at->locale('fr')->diffForHumans() }} · {{ $n->created_at->format('d/m/Y H:i') }}</p>
                        @if (! empty($n->data['lines']))
                            <dl class="mt-3 grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                                @foreach ($n->data['lines'] as $label => $value)
                                    <div class="flex gap-2"><dt class="shrink-0 text-neutral-500">{{ $label }} :</dt><dd class="min-w-0 break-words">{{ $value }}</dd></div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}">@csrf<button class="btn-primary btn-sm whitespace-nowrap">Ouvrir</button></form>
                </div>
            </article>
        @empty
            <div class="card p-10 text-center text-neutral-500">Aucune notification pour le moment.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
