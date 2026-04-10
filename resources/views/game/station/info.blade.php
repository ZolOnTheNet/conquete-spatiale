@extends('layouts.app')

@section('title', $station->nom . ' - Informations')

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

                {{-- En-tête station --}}
                <div class="bg-gradient-to-r from-cyan-900/50 to-purple-900/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h2 class="text-3xl font-orbitron text-cyan-400 mb-2">🏭 {{ $station->nom }}</h2>
                    <p class="text-gray-300">{{ $station->description }}</p>
                </div>

                {{-- Informations générales --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4 font-orbitron">Informations Générales</h3>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Type:</span>
                            <span class="text-cyan-300 ml-2">{{ ucfirst($station->type) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Capacité d'amarrage:</span>
                            <span class="text-green-300 ml-2">{{ $station->capacite_amarrage }} vaisseaux</span>
                        </div>

                        @if($station->planete_id)
                        <div>
                            <span class="text-gray-400">En orbite de:</span>
                            <span class="text-yellow-300 ml-2">{{ $station->planete->nom }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Rayon orbital:</span>
                            <span class="text-cyan-300 ml-2 font-mono">{{ number_format($station->orbite_rayon_ua, 3) }} UA</span>
                        </div>
                        @elseif($station->systeme_stellaire_id)
                        <div>
                            <span class="text-gray-400">Système:</span>
                            <span class="text-yellow-300 ml-2">{{ $station->systemeStellaire->nom }}</span>
                        </div>
                        @endif

                        @if($station->faction_id)
                        <div>
                            <span class="text-gray-400">Faction:</span>
                            <span class="text-purple-300 ml-2">{{ $station->faction->nom }}</span>
                        </div>
                        @if($station->reputation_requise > 0)
                        <div>
                            <span class="text-gray-400">Réputation requise:</span>
                            <span class="text-orange-300 ml-2">{{ $station->reputation_requise }}</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Services disponibles --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4 font-orbitron">Services Disponibles</h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @if($station->commerciale)
                        <div class="flex items-center gap-2 text-green-400">
                            <span class="text-2xl">🛒</span>
                            <span>Marché</span>
                        </div>
                        @endif

                        @if($station->reparations)
                        <div class="flex items-center gap-2 text-orange-400">
                            <span class="text-2xl">🔧</span>
                            <span>Garage</span>
                        </div>
                        @endif

                        @if($station->ravitaillement)
                        <div class="flex items-center gap-2 text-blue-400">
                            <span class="text-2xl">⛽</span>
                            <span>Ravitaillement</span>
                        </div>
                        @endif

                        @if($station->medical)
                        <div class="flex items-center gap-2 text-red-400">
                            <span class="text-2xl">🏥</span>
                            <span>Hôpital</span>
                        </div>
                        @endif

                        @if($station->industrielle)
                        <div class="flex items-center gap-2 text-purple-400">
                            <span class="text-2xl">🏭</span>
                            <span>Industrie</span>
                        </div>
                        @endif

                        @if($station->militaire)
                        <div class="flex items-center gap-2 text-yellow-400">
                            <span class="text-2xl">⚔️</span>
                            <span>Militaire</span>
                        </div>
                        @endif

                        <div class="flex items-center gap-2 text-cyan-400">
                            <span class="text-2xl">📋</span>
                            <span>Comptoirs</span>
                        </div>
                    </div>
                </div>

                {{-- Accessibilité --}}
                <div class="bg-gray-800/50 border border-{{ $station->accessible ? 'green' : 'red' }}-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-{{ $station->accessible ? 'green' : 'red' }}-400 mb-4 font-orbitron">
                        @if($station->accessible)
                            ✅ Station Accessible
                        @else
                            ❌ Station Inaccessible
                        @endif
                    </h3>

                    @if(!$station->accessible && $station->raison_inaccessible)
                    <p class="text-gray-300">{{ $station->raison_inaccessible }}</p>
                    @elseif($station->accessible)
                    <p class="text-gray-300">Cette station est ouverte à tous les vaisseaux.</p>
                    @endif
                </div>

                {{-- Retour --}}
                <div class="mt-6">
                    <a href="{{ route('carte') }}" class="text-cyan-400 hover:text-cyan-300 transition">
                        ← Retour à la carte
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
