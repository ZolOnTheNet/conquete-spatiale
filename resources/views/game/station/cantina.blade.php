@extends('layouts.app')

@section('title', 'Cantina')

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

                <h2 class="text-3xl font-orbitron text-orange-400 mb-6">🍺 CANTINA</h2>

                {{-- Joueurs présents --}}
                <div class="bg-gray-800/50 border border-orange-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-orange-300 mb-4">Pilotes présents</h3>
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">👤</div>
                            <div>
                                <h4 class="text-white font-bold">{{ $personnage->nom }}</h4>
                                <p class="text-gray-400 text-sm">C'est vous!</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm italic mt-4">Système multijoueur en développement...</p>
                </div>

                {{-- Rumeurs --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Rumeurs & Informations</h3>
                    <p class="text-gray-400 text-sm italic">Système de rumeurs en développement...</p>
                </div>

                {{-- Chat --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-purple-300 mb-4">Chat Local</h3>
                    <p class="text-gray-400 text-sm italic">Système de chat en développement...</p>
                </div>

            </div>
        </main>

        {{-- Console Droite --}}
        @include('game.partials.console')
    </div>

</div>
@endsection
