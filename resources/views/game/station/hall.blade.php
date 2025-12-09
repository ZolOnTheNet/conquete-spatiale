@extends('layouts.app')

@section('title', 'Hall Principal')

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

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">🏛️ HALL PRINCIPAL</h2>

                {{-- Informations station --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Informations Station</h3>
                    @if($station ?? null)
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-400">Station:</span>
                                <span class="text-white font-bold ml-2">{{ $station->nom }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Type:</span>
                                <span class="text-yellow-400 ml-2">{{ $station->type_etoile ?? 'Station orbitale' }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">Informations indisponibles</p>
                    @endif
                </div>

                {{-- Services disponibles --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4">Services Disponibles</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('station.hangar') }}" class="bg-gray-900/50 border border-gray-700 hover:border-cyan-500 rounded-lg p-4 transition">
                            <div class="text-2xl mb-2">🛠️</div>
                            <h4 class="text-white font-bold">Hangar</h4>
                            <p class="text-gray-400 text-sm">Réparations & Modules</p>
                        </a>
                        <a href="{{ route('station.marche') }}" class="bg-gray-900/50 border border-gray-700 hover:border-cyan-500 rounded-lg p-4 transition">
                            <div class="text-2xl mb-2">💰</div>
                            <h4 class="text-white font-bold">Marché</h4>
                            <p class="text-gray-400 text-sm">Commerce & Négoce</p>
                        </a>
                        <a href="{{ route('station.missions') }}" class="bg-gray-900/50 border border-gray-700 hover:border-cyan-500 rounded-lg p-4 transition">
                            <div class="text-2xl mb-2">📜</div>
                            <h4 class="text-white font-bold">Bureau des Missions</h4>
                            <p class="text-gray-400 text-sm">Contrats & Quêtes</p>
                        </a>
                        <a href="{{ route('station.cantina') }}" class="bg-gray-900/50 border border-gray-700 hover:border-cyan-500 rounded-lg p-4 transition">
                            <div class="text-2xl mb-2">🍺</div>
                            <h4 class="text-white font-bold">Cantina</h4>
                            <p class="text-gray-400 text-sm">Social & Rumeurs</p>
                        </a>
                    </div>
                </div>

                {{-- Actualités --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-purple-300 mb-4">Actualités de la station</h3>
                    <p class="text-gray-400 text-sm italic">Système d'actualités en développement...</p>
                </div>

            </div>
        </main>

        {{-- Console Droite --}}
        @include('game.partials.console')
    </div>

</div>
@endsection
