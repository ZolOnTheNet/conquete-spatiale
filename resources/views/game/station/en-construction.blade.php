@extends('layouts.app')

@section('title', $titre . ' - En construction')

@section('content')
<div class="h-screen flex flex-col bg-gray-900">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$personnage->vaisseauActif"
        :systeme="$station->systemeStellaire"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Menu latéral gauche --}}
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $personnage->vaisseauActif,
            'compte' => auth()->user()
        ])

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-4xl mx-auto">

                {{-- Message en construction --}}
                <div class="bg-gradient-to-r from-yellow-900/50 to-orange-900/50 border border-yellow-500/30 rounded-lg p-12 text-center">
                    <div class="text-8xl mb-6">🚧</div>
                    <h2 class="text-4xl font-orbitron text-yellow-400 mb-4">{{ $titre }}</h2>
                    <p class="text-xl text-gray-300 mb-6">En cours de construction</p>
                    <p class="text-gray-400 mb-8">Cette fonctionnalité n'est pas encore implémentée.</p>

                    <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-8 text-left">
                        <h3 class="text-lg font-orbitron text-cyan-400 mb-3">📋 Prévu :</h3>
                        <p class="text-gray-300">{{ $description }}</p>
                    </div>

                    <a href="{{ route('station.menu') }}" class="inline-block bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-8 rounded-lg transition">
                        ← Retour au menu de la station
                    </a>
                </div>

            </div>
        </main>

        {{-- Console --}}
        @include('game.partials.console', [
            'personnage' => $personnage
        ])

    </div>
</div>
@endsection
