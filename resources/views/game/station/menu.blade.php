@extends('layouts.app')

@section('title', $station->nom . ' - Menu Station')

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
            <div class="max-w-6xl mx-auto">

                {{-- Titre station --}}
                <div class="bg-gradient-to-r from-cyan-900/50 to-purple-900/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h2 class="text-3xl font-orbitron text-cyan-400 mb-2">🏭 {{ $station->nom }}</h2>
                    <p class="text-gray-300">{{ $station->description }}</p>

                    <div class="mt-4 flex gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Type:</span>
                            <span class="text-cyan-300 ml-2">{{ ucfirst($station->type) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Capacité:</span>
                            <span class="text-green-300 ml-2">{{ $station->capacite_amarrage }} vaisseaux</span>
                        </div>
                        @if($station->planete_id)
                        <div>
                            <span class="text-gray-400">Orbite:</span>
                            <span class="text-yellow-300 ml-2">{{ $station->planete->nom }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Services disponibles --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

                    @if($station->commerciale)
                    <a href="{{ route('marche.index') }}" class="bg-gray-800/50 border border-green-500/30 hover:border-green-500 rounded-lg p-6 transition group">
                        <div class="flex items-center mb-3">
                            <span class="text-4xl mr-4">🛒</span>
                            <h3 class="text-xl font-orbitron text-green-400">Marché</h3>
                        </div>
                        <p class="text-gray-400 text-sm">Acheter et vendre des marchandises</p>
                        <div class="mt-3 text-right text-green-500 group-hover:text-green-400 font-mono text-sm">
                            Accéder →
                        </div>
                    </a>
                    @endif

                    @if($station->reparations)
                    <a href="{{ route('garage.index') }}" class="bg-gray-800/50 border border-orange-500/30 hover:border-orange-500 rounded-lg p-6 transition group">
                        <div class="flex items-center mb-3">
                            <span class="text-4xl mr-4">🔧</span>
                            <h3 class="text-xl font-orbitron text-orange-400">Garage</h3>
                        </div>
                        <p class="text-gray-400 text-sm">Réparations et améliorations</p>
                        <div class="mt-3 text-right text-orange-500 group-hover:text-orange-400 font-mono text-sm">
                            Accéder →
                        </div>
                    </a>
                    @endif

                    @if($station->ravitaillement)
                    <a href="{{ route('ravitaillement.index') }}" class="bg-gray-800/50 border border-blue-500/30 hover:border-blue-500 rounded-lg p-6 transition group">
                        <div class="flex items-center mb-3">
                            <span class="text-4xl mr-4">⛽</span>
                            <h3 class="text-xl font-orbitron text-blue-400">Ravitaillement</h3>
                        </div>
                        <p class="text-gray-400 text-sm">Carburant, eau, oxygène</p>
                        <div class="mt-3 text-right text-blue-500 group-hover:text-blue-400 font-mono text-sm">
                            Accéder →
                        </div>
                    </a>
                    @endif

                    @if($station->medical)
                    <a href="{{ route('station.hopital') }}" class="bg-gray-800/50 border border-red-500/30 hover:border-red-500 rounded-lg p-6 transition group">
                        <div class="flex items-center mb-3">
                            <span class="text-4xl mr-4">🏥</span>
                            <h3 class="text-xl font-orbitron text-red-400">Hôpital</h3>
                        </div>
                        <p class="text-gray-400 text-sm">Soins et traitements médicaux</p>
                        <div class="mt-3 text-right text-red-500 group-hover:text-red-400 font-mono text-sm">
                            Accéder →
                        </div>
                    </a>
                    @endif

                    @if($station->industrielle)
                    <a href="{{ route('station.industrie') }}" class="bg-gray-800/50 border border-purple-500/30 hover:border-purple-500 rounded-lg p-6 transition group">
                        <div class="flex items-center mb-3">
                            <span class="text-4xl mr-4">🏭</span>
                            <h3 class="text-xl font-orbitron text-purple-400">Industrie</h3>
                        </div>
                        <p class="text-gray-400 text-sm">Raffinage et fabrication</p>
                        <div class="mt-3 text-right text-purple-500 group-hover:text-purple-400 font-mono text-sm">
                            Accéder →
                        </div>
                    </a>
                    @endif

                    {{-- Comptoirs (toujours disponible) --}}
                    <a href="{{ route('station.missions') }}" class="bg-gray-800/50 border border-yellow-500/30 hover:border-yellow-500 rounded-lg p-6 transition group">
                        <div class="flex items-center mb-3">
                            <span class="text-4xl mr-4">📋</span>
                            <h3 class="text-xl font-orbitron text-yellow-400">Comptoirs</h3>
                        </div>
                        <p class="text-gray-400 text-sm">Missions, guildes, informations</p>
                        <div class="mt-3 text-right text-yellow-500 group-hover:text-yellow-400 font-mono text-sm">
                            Accéder →
                        </div>
                    </a>

                </div>

                {{-- Retour au vaisseau --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6">
                    <h3 class="text-xl font-orbitron text-cyan-400 mb-4">🚀 Retour au vaisseau</h3>
                    <p class="text-gray-300 mb-4">Embarquer dans votre vaisseau pour reprendre le voyage.</p>

                    <form action="{{ route('station.embarquer') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-6 rounded-lg transition">
                            Embarquer dans {{ $personnage->vaisseauActif->modele }}
                        </button>
                    </form>
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
