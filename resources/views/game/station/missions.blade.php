@extends('layouts.app')

@section('title', 'Bureau des Missions')

@section('content')
<div class="h-screen flex flex-col bg-gray-900">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$personnage->vaisseauActif"
        :systeme="$systeme ?? null"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Menu latéral gauche --}}
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $personnage->vaisseauActif ?? null,
            'compte' => auth()->user()
        ])

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-purple-400 mb-6">📜 BUREAU DES MISSIONS</h2>

                {{-- Missions actives --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4">Missions en Cours</h3>
                    <p class="text-gray-400 text-sm italic">Aucune mission active</p>
                </div>

                {{-- Missions disponibles --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-purple-300 mb-4">Missions Disponibles</h3>
                    <p class="text-gray-400 text-sm italic">Système de missions en développement...</p>
                </div>

                {{-- Historique --}}
                <div class="bg-gray-800/50 border border-gray-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-gray-300 mb-4">Missions Complétées</h3>
                    <p class="text-gray-400 text-sm italic">Historique non disponible</p>
                </div>

            </div>
        </main>

        {{-- Console Droite --}}
        @include('game.partials.console')
    </div>

</div>
@endsection
