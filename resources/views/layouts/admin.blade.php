<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Tableau de bord') · Admin OCD LIVE</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-neutral-100 font-sans text-neutral-800 antialiased" x-data="{ nav: false }">
    @php
        $user = auth()->user();
        $isAdmin = $user->role === \App\Enums\UserRole::Admin;
        $unread = $user->unreadNotifications()->count();
        $links = array_filter([
            ['admin.dashboard', 'Tableau de bord', 'home', 'admin.dashboard'],
            ['admin.bookings.index', 'Réservations', 'ticket', 'admin.bookings.*'],
            ['admin.calendar', 'Calendrier', 'calendar', 'admin.calendar*'],
            ['admin.customers.index', 'Clients', 'users', 'admin.customers.*'],
            $isAdmin ? ['admin.packages.index', 'Packages', 'box', 'admin.packages.*'] : null,
            ['admin.notifications.index', 'Notifications', 'bell', 'admin.notifications.*'],
            $isAdmin ? ['admin.settings.edit', 'Paramètres', 'cog', 'admin.settings.*'] : null,
            $isAdmin ? ['admin.logs', 'Journal', 'list', 'admin.logs'] : null,
        ]);
    @endphp

    {{-- Barre latérale --}}
    <aside :class="nav ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink text-neutral-300 transition-transform duration-300 lg:translate-x-0">
        <div class="flex h-16 items-center px-5"><a href="{{ route('admin.dashboard') }}"><x-logo dark /></a></div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Administration">
            @foreach ($links as [$route, $label, $icon, $pattern])
                @php($active = request()->routeIs($pattern))
                <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $active ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/5 hover:text-white' }}">
                    <x-icon :name="$icon" class="h-5 w-5 {{ $active ? 'text-gold-400' : '' }}" />
                    <span class="flex-1">{{ $label }}</span>
                    @if ($route === 'admin.notifications.index' && $unread)
                        <span class="rounded-full bg-gold-500 px-2 py-0.5 text-[10px] font-bold text-ink">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4">
            <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
            <p class="text-xs text-neutral-500">{{ $user->role->label() }}</p>
            <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('home') }}" target="_blank" class="text-xs hover:text-gold-300">Voir le site ↗</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="inline-flex items-center gap-1.5 text-xs hover:text-gold-300"><x-icon name="logout" class="h-4 w-4" />Déconnexion</button>
                </form>
            </div>
        </div>
    </aside>
    <div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-neutral-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <button type="button" @click="nav = true" class="grid h-10 w-10 place-items-center rounded-lg border border-neutral-200 lg:hidden" aria-label="Ouvrir le menu"><x-icon name="menu" /></button>
            <h1 class="flex-1 truncate font-display text-lg text-ink sm:text-xl">@yield('title', 'Tableau de bord')</h1>
            @yield('actions')
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @if (session('status'))
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    <x-icon name="check" class="h-5 w-5 shrink-0" />{{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @livewireScripts
</body>
</html>
