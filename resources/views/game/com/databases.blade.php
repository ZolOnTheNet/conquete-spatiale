@extends('layouts.app')

@section('title', 'Communications')

@section('content')
<div class="h-screen flex flex-col">
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau ?? null"
        :systeme="$systemeActuel ?? null"
        :secteur="null"
    />

    <div class="flex-1 flex overflow-hidden">
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau ?? null,
            'compte' => auth()->user()
        ])

        <main class="flex-1 overflow-auto p-6 bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">📡 COMMUNICATIONS</h2>
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6">
                    <p class="text-gray-400">Système de communications</p>
                    <p class="text-sm text-gray-500 mt-2">À implémenter selon GDD</p>
                </div>
            </div>
        </main>

        @include('game.partials.console')
    </div>
</div>
@endsection
