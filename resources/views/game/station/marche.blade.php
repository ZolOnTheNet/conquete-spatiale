@extends('layouts.app')

@section('title', 'Marché')

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

                <h2 class="text-3xl font-orbitron text-green-400 mb-6">💰 MARCHÉ</h2>

                {{-- Crédits du joueur --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Vos crédits:</span>
                        <span class="text-yellow-400 font-mono text-2xl">{{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR</span>
                    </div>
                </div>

                {{-- Marchandises à vendre --}}
                <div class="bg-gray-800/50 border border-green-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-green-300 mb-4">Marchandises Disponibles</h3>
                    <p class="text-gray-400 text-sm italic">Système de commerce en développement...</p>
                </div>

                {{-- Votre cargaison --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Votre Cargaison</h3>
                    <p class="text-gray-400 text-sm italic">Accédez à votre soute depuis le menu Navire</p>
                </div>

            </div>
        </main>

    </div>

</div>
@endsection
