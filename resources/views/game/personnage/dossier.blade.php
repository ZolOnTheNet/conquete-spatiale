@extends('layouts.app')

@section('title', 'Dossier Personnage')

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

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">📋 DOSSIER PERSONNAGE</h2>

                {{-- Informations générales --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Informations générales</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Nom:</span>
                            <span class="text-white font-bold ml-2">{{ $personnage->nom }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Crédits:</span>
                            <span class="text-yellow-400 font-mono ml-2">{{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Points d'action:</span>
                            <span class="text-cyan-400 font-bold ml-2">{{ $personnage->points_action ?? 0 }} PA</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Niveau:</span>
                            <span class="text-green-400 ml-2">{{ $personnage->niveau ?? 1 }}</span>
                        </div>
                    </div>
                </div>

                {{-- Compétences --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4">Compétences</h3>
                    <p class="text-gray-400 text-sm italic">Système de compétences en développement...</p>
                </div>

                {{-- Historique --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-purple-300 mb-4">Historique</h3>
                    <p class="text-gray-400 text-sm italic">Journal de bord en développement...</p>
                </div>

            </div>
        </main>

        {{-- Console Droite Redimensionnable --}}
        <x-console-resizable>
            @include('game.partials.console')
        </x-console-resizable>
    </div>

</div>
@endsection
