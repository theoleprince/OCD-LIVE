@extends('layouts.public')

@section('title', 'Connexion administration')
@section('noindex', '1')

@section('content')
    <section class="grid min-h-[70vh] place-items-center bg-neutral-50 px-4 py-16">
        <div class="w-full max-w-md rounded-3xl border border-neutral-200 bg-white p-8 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.25)] sm:p-10">
            <p class="eyebrow">Espace équipe</p>
            <h1 class="mt-3 font-display text-3xl text-ink">Connexion</h1>
            <p class="mt-2 text-sm text-neutral-500">Accès réservé à l'équipe OCD Live.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-neutral-700">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field w-full" @error('email') aria-invalid="true" @enderror>
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-neutral-700">Mot de passe</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="field w-full">
                </div>
                <label class="flex items-center gap-2 text-sm text-neutral-600">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-neutral-300">Rester connecté
                </label>
                <button type="submit" class="btn-primary w-full">Se connecter</button>
            </form>
        </div>
    </section>
@endsection
