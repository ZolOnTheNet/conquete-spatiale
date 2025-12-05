@extends('layouts.app')

@section('title', 'Gestion Personnage')

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
            :context="$context ?? 'navire'"
            :personnage="$personnage"
            :isAdmin="$isAdmin ?? false"
        />

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">⚙️ GESTION PERSONNAGE</h2>

                {{-- Paramètres du compte --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Paramètres du compte</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-400 text-sm">Nom du personnage</label>
                            <input type="text" value="{{ $personnage->nom }}" disabled
                                   class="w-full bg-gray-900/50 border border-gray-700 rounded px-3 py-2 text-white mt-1" />
                        </div>
                        <p class="text-gray-400 text-sm italic">La gestion du nom et des paramètres sera disponible prochainement.</p>
                    </div>
                </div>

                {{-- Vaisseaux --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4">Vaisseaux possédés</h3>
                    @if($personnage->vaisseauActif)
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-white font-bold">{{ $personnage->vaisseauActif->nom ?? 'Vaisseau sans nom' }}</h4>
                                    <p class="text-gray-400 text-sm">{{ $personnage->vaisseauActif->modele ?? 'Modèle inconnu' }}</p>
                                </div>
                                <span class="px-3 py-1 bg-green-900/30 text-green-400 text-sm rounded">✓ Actif</span>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm italic">Aucun vaisseau actif</p>
                    @endif
                </div>

                {{-- Actions dangereuses --}}
                <div class="bg-gray-800/50 border border-red-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-red-300 mb-4">Zone dangereuse</h3>
                    <p class="text-gray-400 text-sm mb-4">Ces actions sont irréversibles.</p>
                    <button disabled class="bg-red-600/50 text-red-200 px-4 py-2 rounded cursor-not-allowed">
                        Supprimer le personnage (désactivé)
                    </button>
                </div>

            </div>
        </main>

    </div>

</div>
@endsection
