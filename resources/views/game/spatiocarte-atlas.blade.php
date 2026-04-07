@extends('layouts.app')

@section('title', 'Atlas Spatial')

@section('content')
<div class="h-screen flex flex-col">
    <!-- Header 4 Colonnes -->
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau"
        :systeme="null"
        :secteur="null"
    />

    <!-- Main Layout -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Menu Gauche -->
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'compte' => $compte
        ])

        <!-- Contenu Principal Atlas -->
        <main class="flex-1 overflow-auto p-4 bg-gray-900">
            <!-- En-tête avec Onglets -->
            <div class="mb-4 bg-gray-800/50 border-b border-cyan-500/30">
                <div class="px-4 py-3">
                    <h2 class="text-2xl font-orbitron text-cyan-400">SPATIOCARTE</h2>
                </div>

                <!-- Onglets -->
                <div class="flex border-t border-cyan-500/30">
                    <a href="{{ route('personnage.spatiocarte', ['onglet' => 'carte']) }}"
                       class="px-6 py-3 text-sm font-semibold text-gray-400 hover:text-cyan-400 hover:bg-cyan-900/20 transition border-r border-cyan-500/30">
                        Carte
                    </a>
                    <a href="{{ route('personnage.spatiocarte', ['onglet' => 'atlas']) }}"
                       class="px-6 py-3 text-sm font-semibold text-cyan-400 bg-cyan-900/30 border-b-2 border-cyan-400">
                        Atlas
                    </a>
                </div>
            </div>

            <!-- Contenu Atlas -->
            <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-cyan-300">
                        Systèmes et POI Découverts ({{ $decouvertes->count() }})
                    </h3>
                    <div class="text-sm text-gray-400">
                        Triés par distance
                    </div>
                </div>

                @if($decouvertes->count() > 0)
                    <!-- Tableau des découvertes -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-900/50 border-b border-cyan-500/30">
                                <tr>
                                    <th class="px-4 py-3 text-left text-cyan-400">Nom</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Type</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Coordonnées Secteur</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Position Précise</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Distance</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Puissance</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Planètes</th>
                                    <th class="px-4 py-3 text-left text-cyan-400">Ressources</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($decouvertes as $data)
                                    @php
                                        $systeme = $data['systeme'];
                                        $decouverte = $data['decouverte'];
                                    @endphp
                                    <tr class="hover:bg-cyan-900/10 transition">
                                        <!-- Nom -->
                                        <td class="px-4 py-3">
                                            <a href="{{ route('carte', ['x' => $systeme->secteur_x, 'y' => $systeme->secteur_y, 'z' => $systeme->secteur_z]) }}"
                                               class="text-yellow-400 hover:text-yellow-300 font-semibold">
                                                {{ $systeme->nom }}
                                            </a>
                                        </td>

                                        <!-- Type -->
                                        <td class="px-4 py-3">
                                            @if($decouverte->type_etoile_connu)
                                                <span class="px-2 py-1 bg-purple-900/30 text-purple-300 rounded text-xs">
                                                    {{ $systeme->type_etoile ?? 'N/A' }}
                                                </span>
                                            @else
                                                <span class="text-gray-500 text-xs">Inconnu</span>
                                            @endif
                                        </td>

                                        <!-- Coordonnées Secteur -->
                                        <td class="px-4 py-3 font-mono text-gray-300">
                                            {{ $data['coords_secteur'] }}
                                        </td>

                                        <!-- Position Précise -->
                                        <td class="px-4 py-3 font-mono text-gray-400 text-xs">
                                            @if($decouverte->coordonnees_connues)
                                                {{ $data['coords_position'] }}
                                            @else
                                                <span class="text-gray-600">???</span>
                                            @endif
                                        </td>

                                        <!-- Distance -->
                                        <td class="px-4 py-3 text-cyan-300">
                                            {{ number_format($data['distance'], 2) }} AL
                                        </td>

                                        <!-- Puissance Solaire -->
                                        <td class="px-4 py-3">
                                            @if($systeme->puissance_solaire)
                                                <span class="text-yellow-400" title="Puissance solaire: {{ $systeme->puissance_solaire }}/100">
                                                      {{ $systeme->puissance_solaire }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>

                                        <!-- Planètes -->
                                        <td class="px-4 py-3">
                                            @if($decouverte->nb_planetes_connu)
                                                <span class="text-blue-400">
                                                    {{ $systeme->planetes->count() }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">?</span>
                                            @endif
                                        </td>

                                        <!-- Ressources -->
                                        <td class="px-4 py-3">
                                            @php
                                                $ressources = $systeme->planetes->flatMap->gisements->pluck('ressource.nom')->unique();
                                            @endphp
                                            @if($ressources->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($ressources->take(3) as $ressource)
                                                        <span class="px-2 py-1 bg-green-900/30 text-green-300 rounded text-xs"
                                                              title="{{ $ressource }}">
                                                            {{ $ressource }}
                                                        </span>
                                                    @endforeach
                                                    @if($ressources->count() > 3)
                                                        <span class="px-2 py-1 bg-gray-700 text-gray-300 rounded text-xs"
                                                              title="{{ $ressources->slice(3)->implode(', ') }}">
                                                            +{{ $ressources->count() - 3 }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-500 text-xs">Aucune connue</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Aucune découverte -->
                    <div class="text-center py-12">
                        <div class="text-gray-500 text-lg mb-2">Aucun système découvert</div>
                        <p class="text-gray-600 text-sm">
                            Explorez l'univers pour découvrir de nouveaux systèmes stellaires
                        </p>
                    </div>
                @endif
            </div>
        </main>

        <!-- Console Droite Redimensionnable -->
        <x-console-resizable>
            @include('game.partials.console')
        </x-console-resizable>
    </div>
</div>
@endsection
