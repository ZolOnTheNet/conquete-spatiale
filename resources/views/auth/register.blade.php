@extends('layouts.app')

@section('title', 'Conquete Spatiale - Inscription')

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
            <!-- Register Form -->
            <div class="bg-gray-900/80 border border-cyan-500/30 rounded-lg p-8 backdrop-blur-sm">
                <h2 class="text-2xl font-orbitron text-cyan-400 mb-6 text-center">INSCRIPTION</h2>

                @if($errors->any())
                    <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm text-gray-400 mb-1">Nom de pilote</label>
                        <input type="text" id="name" name="name" required
                               class="w-full bg-gray-800 border border-gray-600 rounded px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
                               value="{{ old('name') }}">
                    </div>

                    <div>
                        <label for="email" class="block text-sm text-gray-400 mb-1">Email</label>
                        <input type="email" id="email" name="email" required
                               class="w-full bg-gray-800 border border-gray-600 rounded px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
                               value="{{ old('email') }}">
                    </div>

                    <div>
                        <label for="password" class="block text-sm text-gray-400 mb-1">Mot de passe</label>
                        <input type="password" id="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-600 rounded px-4 py-2 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm text-gray-400 mb-1">Confirmer mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full bg-gray-800 border border-gray-600 rounded px-4 py-2 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <button type="submit"
                            class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 px-4 rounded transition-colors">
                        CREER MON COMPTE
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-500 text-sm">Deja un compte ?</p>
                    <a href="{{ route('home') }}" class="text-cyan-400 hover:text-cyan-300">
                        Se connecter
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="p-4 text-center text-gray-600 text-sm">
        <p>Conquete Spatiale v1.0 - Developpement</p>
    </footer>
</div>
@endsection
