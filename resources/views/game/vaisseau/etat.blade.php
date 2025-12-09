@extends('layouts.app')

@section('title', 'État du Vaisseau')

@section('content')
<div class="h-screen flex flex-col">
    <!-- Header 4 Colonnes -->
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau"
        :systeme="$systemeActuel ?? null"
        :secteur="null"
    />

    <!-- Main Layout -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Menu Gauche -->
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'compte' => auth()->user()
        ])

        <!-- Contenu Principal -->
        <main class="flex-1 overflow-auto p-6 bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">ÉTAT DU VAISSEAU</h2>

                @if(isset($partial))
                    @include('game.vaisseau.partials.etat')
                @else
                    <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6">
                        <p class="text-gray-400">Informations sur l'état du vaisseau</p>
                        <p class="text-sm text-gray-500 mt-2">À implémenter selon GDD</p>
                    </div>
                @endif
            </div>
        </main>

        <!-- Console Droite -->
        @include('game.partials.console')
    </div>
</div>
@endsection
