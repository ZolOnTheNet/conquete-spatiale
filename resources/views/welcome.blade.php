@extends('layouts.app')

@section('title', 'Conquete Spatiale - Accueil')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="p-6">
        <h1 class="text-4xl md:text-6xl font-orbitron font-bold text-center text-cyan-400 glow-text">
            CONQUETE SPATIALE
        </h1>
        <p class="text-center text-gray-400 mt-2">Exploration Galactique - Interface Web</p>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Login Form -->
            <div class="bg-gray-900/80 border border-cyan-500/30 rounded-lg p-8 backdrop-blur-sm">
                <h2 class="text-2xl font-orbitron text-cyan-400 mb-6 text-center">CONNEXION</h2>

                @if(session('error'))
                    <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm text-gray-400 mb-1">Email</label>
                        <input type="email" id="email" name="email" required
                               class="w-full bg-gray-800 border border-gray-600 rounded px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
                               value="{{ old('email') }}">
                        @error('email')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm text-gray-400 mb-1">Mot de passe</label>
                        <input type="password" id="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-600 rounded px-4 py-2 text-white focus:border-cyan-500 focus:outline-none">
                        @error('password')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember"
                               class="rounded bg-gray-800 border-gray-600 text-cyan-500 focus:ring-cyan-500">
                        <label for="remember" class="ml-2 text-sm text-gray-400">Se souvenir de moi</label>
                    </div>

                    <button type="submit"
                            class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 px-4 rounded transition-colors">
                        ENTRER
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-500 text-sm">Pas encore de compte ?</p>
                    <a href="{{ route('register.form') }}" class="text-cyan-400 hover:text-cyan-300">
                        Creer un compte
                    </a>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-gray-900/60 border border-gray-700 rounded-lg p-4 text-sm text-gray-400">
                <p class="mb-2"><strong class="text-cyan-400">Compte test:</strong></p>
                <p>Email: test@test.com</p>
                <p>Mot de passe: password</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="p-4 text-center text-gray-600 text-sm">
        <p>Conquete Spatiale v1.0 - Developpement</p>
    </footer>
</div>
@endsection
