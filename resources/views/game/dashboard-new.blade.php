@extends('layouts.app')

@section('title', 'Conquete Spatiale - Dashboard')

@section('content')
<div class="h-screen flex flex-col">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau"
        :systeme="$systeme"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden bg-gray-900">

        {{-- Menu latéral gauche --}}
        <x-main-menu
            :context="$context"
            :personnage="$personnage"
            :isAdmin="$isAdmin"
        />

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                {{-- Message de bienvenue --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h2 class="text-2xl font-orbitron text-cyan-400 mb-4">
                        Bienvenue, {{ $personnage->nom }} !
                    </h2>
                    <p class="text-gray-300 mb-4">
                        Vous êtes actuellement
                        @if($context === 'station')
                            <span class="text-yellow-400 font-bold">amarré à une station</span>.
                        @else
                            <span class="text-cyan-400 font-bold">à bord de votre vaisseau</span>.
                        @endif
                    </p>

                    @if($vaisseau && $vaisseau->objetSpatial)
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Position :</span>
                            <span class="text-yellow-400 font-mono">
                                Secteur ({{ $vaisseau->objetSpatial->secteur_x }}, {{ $vaisseau->objetSpatial->secteur_y }}, {{ $vaisseau->objetSpatial->secteur_z }})
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Système :</span>
                            <span class="text-cyan-400">{{ $systeme->nom ?? 'Espace profond' }}</span>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Raccourcis rapides --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    {{-- Carte --}}
                    <a href="{{ route('carte') }}"
                       class="bg-gray-800/50 hover:bg-gray-800/70 border border-cyan-500/30 rounded-lg p-6 transition-all hover:scale-105">
                        <div class="text-3xl mb-2">🗺️</div>
                        <h3 class="text-lg font-bold text-cyan-400 mb-2">Carte</h3>
                        <p class="text-gray-400 text-sm">Explorez l'univers découvert</p>
                    </a>

                    {{-- Timonerie / Navigation --}}
                    @if($context === 'navire')
                    <a href="{{ route('navire.timonerie') }}"
                       class="bg-gray-800/50 hover:bg-gray-800/70 border border-cyan-500/30 rounded-lg p-6 transition-all hover:scale-105">
                        <div class="text-3xl mb-2">🚀</div>
                        <h3 class="text-lg font-bold text-cyan-400 mb-2">Timonerie</h3>
                        <p class="text-gray-400 text-sm">Navigation et déplacements</p>
                    </a>
                    @else
                    <a href="{{ route('station.hall') }}"
                       class="bg-gray-800/50 hover:bg-gray-800/70 border border-cyan-500/30 rounded-lg p-6 transition-all hover:scale-105">
                        <div class="text-3xl mb-2">🏭</div>
                        <h3 class="text-lg font-bold text-cyan-400 mb-2">Hall Station</h3>
                        <p class="text-gray-400 text-sm">Services de la station</p>
                    </a>
                    @endif

                    {{-- Dossier personnage --}}
                    <a href="{{ route('personnage.dossier') }}"
                       class="bg-gray-800/50 hover:bg-gray-800/70 border border-cyan-500/30 rounded-lg p-6 transition-all hover:scale-105">
                        <div class="text-3xl mb-2">📊</div>
                        <h3 class="text-lg font-bold text-cyan-400 mb-2">Dossier</h3>
                        <p class="text-gray-400 text-sm">Votre personnage</p>
                    </a>

                </div>

                {{-- Stats rapides --}}
                <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div class="bg-gray-800/30 border border-gray-700 rounded-lg p-4">
                        <div class="text-gray-400 text-xs mb-1">Crédits</div>
                        <div class="text-yellow-400 text-xl font-bold">
                            {{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR
                        </div>
                    </div>

                    <div class="bg-gray-800/30 border border-gray-700 rounded-lg p-4">
                        <div class="text-gray-400 text-xs mb-1">Points d'Action</div>
                        <div class="text-green-400 text-xl font-bold">
                            {{ $personnage->points_action ?? 0 }} / {{ $personnage->max_points_action ?? 0 }}
                        </div>
                    </div>

                    @if($vaisseau)
                    <div class="bg-gray-800/30 border border-gray-700 rounded-lg p-4">
                        <div class="text-gray-400 text-xs mb-1">Énergie Vaisseau</div>
                        <div class="text-cyan-400 text-xl font-bold">
                            {{ round(($vaisseau->energie_actuelle / $vaisseau->reserve) * 100) }}%
                        </div>
                    </div>

                    <div class="bg-gray-800/30 border border-gray-700 rounded-lg p-4">
                        <div class="text-gray-400 text-xs mb-1">Structure</div>
                        <div class="text-blue-400 text-xl font-bold">
                            {{ round(($vaisseau->coque_actuelle / $vaisseau->coque_max) * 100) }}%
                        </div>
                    </div>
                    @endif

                </div>

            </div>
        </main>

    </div>

</div>
@endsection
