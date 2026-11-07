<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $fullTitle = $pageTitle ? $pageTitle.' | OCD LIVE' : 'OCD LIVE — Votre produit mérite d\'être vu';
        $description = trim($__env->yieldContent('description')) ?: 'OCD Live, la plateforme panafricaine de présentation commerciale en direct de House of Challenge. Présentez vos produits en live depuis notre studio de Lomé ou à distance, partout en Afrique.';
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if (trim($__env->yieldContent('noindex')))
        <meta name="robots" content="noindex, nofollow">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="OCD LIVE">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-ocd-live.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0b0b0c">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen bg-white font-sans text-neutral-800 antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-ink focus:px-4 focus:py-2 focus:text-white">Aller au contenu</a>

    @include('public.partials.header')

    <main id="contenu">
        @yield('content')
    </main>

    @include('public.partials.footer')

    @livewireScripts
</body>
</html>
