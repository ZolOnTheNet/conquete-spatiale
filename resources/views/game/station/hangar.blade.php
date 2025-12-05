@extends('layouts.app')

@section('title', 'Hangar')

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
        <x-main-menu
            context="station"
            :personnage="$personnage"
            :isAdmin="$isAdmin ?? false"
        />

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-yellow-400 mb-6">🛠️ HANGAR</h2>

                {{-- État du vaisseau --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4">Votre vaisseau</h3>
                    @if($personnage->vaisseauActif)
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <h4 class="text-white font-bold text-lg mb-2">{{ $personnage->vaisseauActif->nom ?? 'Vaisseau sans nom' }}</h4>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Coque:</span>
                                    <span class="text-green-400 ml-2">{{ $personnage->vaisseauActif->coque_actuelle ?? 100 }}/{{ $personnage->vaisseauActif->coque_max ?? 100 }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Bouclier:</span>
                                    <span class="text-cyan-400 ml-2">{{ $personnage->vaisseauActif->bouclier_actuel ?? 0 }}/{{ $personnage->vaisseauActif->bouclier_max ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Énergie:</span>
                                    <span class="text-yellow-400 ml-2">{{ $personnage->vaisseauActif->energie_actuelle ?? 0 }}/{{ $personnage->vaisseauActif->energie_max ?? 100 }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">Aucun vaisseau actif</p>
                    @endif
                </div>

                {{-- Services de réparation --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Services de Réparation</h3>
                    <p class="text-gray-400 text-sm italic">Système de réparation en développement...</p>
                </div>

                {{-- Modules disponibles --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-purple-300 mb-4">Modules & Améliorations</h3>
                    <p class="text-gray-400 text-sm italic">Système d'amélioration en développement...</p>
                </div>

            </div>
        </main>

    </div>

</div>
@endsection
