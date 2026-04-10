@extends('layouts.app')

@section('title', 'Garage')

@section('content')
<div class="h-screen flex flex-col bg-gray-900">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau"
        :systeme="null"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Menu latéral gauche --}}
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'compte' => auth()->user()
        ])

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-orbitron text-orange-400">🔧 GARAGE - {{ $station->nom }}</h2>
                    <a href="{{ route('station.hangar') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition">
                        ← Retour au Hangar
                    </a>
                </div>

                {{-- État du vaisseau --}}
                <div class="bg-gray-800/50 border border-orange-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-orange-300 mb-4">État du Vaisseau</h3>

                    @php
                        $objetSpatial = $vaisseau->objetSpatial;
                        $nomVaisseau = $objetSpatial->nom ?? 'Vaisseau sans nom';
                    @endphp

                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 mb-4">
                        <h4 class="text-white font-bold text-lg mb-3">{{ $nomVaisseau }}</h4>

                        <div class="space-y-3">
                            {{-- Coque --}}
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Intégrité Coque:</span>
                                <div class="flex items-center gap-4">
                                    <div class="w-48 bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-green-500 h-full transition-all"
                                             style="width: {{ $diagnostics['pourcentage_coque'] }}%"></div>
                                    </div>
                                    <span class="text-white font-mono">
                                        {{ $diagnostics['coque_actuelle'] }}/{{ $diagnostics['coque_max'] }}
                                        ({{ round($diagnostics['pourcentage_coque']) }}%)
                                    </span>
                                </div>
                            </div>

                            {{-- Dommages --}}
                            @if($diagnostics['dommages_coque'] > 0)
                                <div class="bg-red-900/20 border border-red-500/30 rounded p-3">
                                    <p class="text-red-400">
                                        ⚠️ {{ $diagnostics['dommages_coque'] }} points de dommages structurels détectés
                                    </p>
                                </div>
                            @endif

                            {{-- Pannes --}}
                            @if($diagnostics['nb_pannes'] > 0)
                                <div class="bg-yellow-900/20 border border-yellow-500/30 rounded p-3">
                                    <p class="text-yellow-400 mb-2">⚠️ {{ $diagnostics['nb_pannes'] }} panne(s) système(s):</p>
                                    <ul class="text-sm text-gray-300 space-y-1 ml-4">
                                        @foreach($diagnostics['pannes'] as $panneId => $panne)
                                            <li>• {{ $panne['nom'] ?? 'Panne inconnue' }} (Complexité: {{ $panne['complexite'] ?? 1 }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($diagnostics['dommages_coque'] == 0 && $diagnostics['nb_pannes'] == 0)
                                <div class="bg-green-900/20 border border-green-500/30 rounded p-3">
                                    <p class="text-green-400">✓ Vaisseau en parfait état - Aucune réparation nécessaire</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Services de réparation --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Services de Réparation</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Réparation Coque --}}
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <h4 class="text-white font-semibold mb-2">🛡️ Réparation Coque</h4>
                            <p class="text-gray-400 text-sm mb-3">
                                Répare tous les dommages structurels du vaisseau
                            </p>

                            @if($diagnostics['dommages_coque'] > 0)
                                <div class="text-yellow-400 mb-2">
                                    Coût: {{ number_format($diagnostics['cout_coque'], 0, ',', ' ') }} crédits
                                </div>
                                <form method="POST" action="{{ route('garage.reparer-coque') }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
                                        Réparer la coque
                                    </button>
                                </form>
                            @else
                                <p class="text-green-400">✓ Coque en parfait état</p>
                            @endif
                        </div>

                        {{-- Réparation Pannes --}}
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <h4 class="text-white font-semibold mb-2">⚙️ Réparation Systèmes</h4>
                            <p class="text-gray-400 text-sm mb-3">
                                Répare toutes les pannes système
                            </p>

                            @if($diagnostics['nb_pannes'] > 0)
                                <div class="text-yellow-400 mb-2">
                                    Coût: {{ number_format($diagnostics['cout_pannes'], 0, ',', ' ') }} crédits
                                </div>
                                @foreach($diagnostics['pannes'] as $panneId => $panne)
                                    <form method="POST" action="{{ route('garage.reparer-panne') }}" class="mb-2">
                                        @csrf
                                        <input type="hidden" name="panne_id" value="{{ $panneId }}">
                                        <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1.5 rounded transition text-sm">
                                            Réparer: {{ $panne['nom'] ?? 'Panne' }}
                                        </button>
                                    </form>
                                @endforeach
                            @else
                                <p class="text-green-400">✓ Tous les systèmes fonctionnels</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Réparation complète --}}
                @if($diagnostics['cout_total'] > 0)
                    <div class="bg-gradient-to-r from-purple-900/30 to-blue-900/30 border border-purple-500/30 rounded-lg p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl text-purple-300 mb-2">⚡ Réparation Complète</h3>
                                <p class="text-gray-400 text-sm">
                                    Répare tous les dommages et pannes en une seule intervention
                                </p>
                                <div class="text-yellow-400 font-bold text-lg mt-2">
                                    Coût total: {{ number_format($diagnostics['cout_total'], 0, ',', ' ') }} crédits
                                </div>
                            </div>
                            <form method="POST" action="{{ route('garage.reparer-tout') }}">
                                @csrf
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                                    🔧 Tout Réparer
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </main>

        {{-- Console Droite --}}
        @include('game.partials.console')
    </div>

</div>
@endsection
