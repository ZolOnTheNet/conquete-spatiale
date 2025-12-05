@extends('layouts.app')

@section('title', 'Équipage')

@section('content')
<div class="h-screen flex flex-col bg-gray-900">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau"
        :systeme="$systemeActuel ?? null"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Menu latéral gauche --}}
        <x-main-menu
            context="navire"
            :personnage="$personnage"
            :isAdmin="false"
        />

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">👥 ÉQUIPAGE</h2>

                {{-- Capitaine --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4 flex items-center gap-2">
                        <span>⭐</span>
                        <span>Capitaine</span>
                    </h3>
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                        <div class="flex items-center gap-4">
                            <div class="text-4xl">👤</div>
                            <div>
                                <h4 class="text-white font-bold text-lg">{{ $personnage->nom }}</h4>
                                <p class="text-gray-400 text-sm">Commandant du vaisseau</p>
                                <p class="text-cyan-400 text-sm mt-1">Niveau {{ $personnage->niveau ?? 1 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Membres d'équipage --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Membres d'équipage</h3>
                    <p class="text-gray-400 text-sm italic">Système de recrutement d'équipage en développement...</p>
                    <p class="text-gray-500 text-xs mt-2">
                        Les membres d'équipage pourront être recrutés dans les stations et amélioreront
                        les capacités de votre vaisseau (navigation, combat, ingénierie, etc.)
                    </p>
                </div>

                {{-- Capacité d'équipage --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-purple-300 mb-4">Capacité du vaisseau</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">Équipage</span>
                                <span class="text-white">1 / {{ $vaisseau->capacite_equipage ?? 10 }}</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ (1 / ($vaisseau->capacite_equipage ?? 10)) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs mt-3">
                        Places disponibles: {{ ($vaisseau->capacite_equipage ?? 10) - 1 }}
                    </p>
                </div>

            </div>
        </main>

    </div>

</div>
@endsection
