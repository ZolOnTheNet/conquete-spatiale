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
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $personnage->vaisseauActif ?? null,
            'compte' => auth()->user()
        ])

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-yellow-400 mb-6">🛠️ HANGAR</h2>

                {{-- État du vaisseau --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl text-yellow-300">Votre vaisseau</h3>
                        @if($personnage->vaisseauActif)
                            <form method="POST" action="{{ route('station.embarquer') }}">
                                @csrf
                                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded transition">
                                    🚀 Embarquer (retourner au vaisseau)
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($personnage->vaisseauActif)
                        @php
                            $vaisseau = $personnage->vaisseauActif;
                            $objetSpatial = $vaisseau->objetSpatial;
                            $nomVaisseau = $objetSpatial->nom ?? 'Vaisseau sans nom';
                        @endphp
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <h4 class="text-white font-bold text-lg mb-2">{{ $nomVaisseau }}</h4>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Coque:</span>
                                    <span class="text-green-400 ml-2">{{ $vaisseau->coque_actuelle ?? 100 }}/{{ $vaisseau->coque_max ?? 100 }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Bouclier:</span>
                                    <span class="text-cyan-400 ml-2">{{ $vaisseau->bouclier_actuel ?? 0 }}%</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Énergie:</span>
                                    <span class="text-yellow-400 ml-2">{{ $vaisseau->energie_actuelle ?? 0 }}/{{ $vaisseau->reserve ?? 100 }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">Aucun vaisseau actif</p>
                    @endif
                </div>

                {{-- Services de réparation --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl text-cyan-300">Services de Réparation</h3>
                        @if($personnage->vaisseauActif)
                            <a href="{{ route('garage.index') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded transition">
                                🔧 Accéder au Garage
                            </a>
                        @endif
                    </div>
                    <p class="text-gray-400 text-sm">
                        Le garage de cette station vous permet de réparer votre vaisseau, remplacer des composants endommagés et effectuer des maintenances.
                    </p>

                    @if($personnage->vaisseauActif)
                        @php
                            $vaisseau = $personnage->vaisseauActif;
                            $dommagesCoque = ($vaisseau->coque_max - $vaisseau->coque_actuelle);
                            $nbPannes = is_array($vaisseau->pannes_actuelles) ? count($vaisseau->pannes_actuelles) : 0;
                        @endphp

                        @if($dommagesCoque > 0 || $nbPannes > 0)
                            <div class="mt-4 bg-red-900/20 border border-red-500/30 rounded p-3">
                                <p class="text-red-400 font-semibold mb-2">⚠️ Réparations nécessaires :</p>
                                <ul class="text-sm text-gray-300 space-y-1">
                                    @if($dommagesCoque > 0)
                                        <li>• Coque endommagée : {{ $dommagesCoque }} points à réparer</li>
                                    @endif
                                    @if($nbPannes > 0)
                                        <li>• {{ $nbPannes }} panne(s) système détectée(s)</li>
                                    @endif
                                </ul>
                            </div>
                        @else
                            <div class="mt-4 bg-green-900/20 border border-green-500/30 rounded p-3">
                                <p class="text-green-400">✓ Vaisseau en parfait état</p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Modules disponibles --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-purple-300 mb-4">Modules & Améliorations</h3>
                    <p class="text-gray-400 text-sm italic">Système d'amélioration en développement...</p>
                </div>

            </div>
        </main>

        {{-- Console Droite --}}
        @include('game.partials.console')
    </div>

</div>
@endsection
