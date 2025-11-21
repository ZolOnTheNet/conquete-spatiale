@extends('layouts.app')

@section('title', 'Admin - Détails Système')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">SYSTÈME: {{ $systeme->nom }}</h1>
        </div>
        <a href="{{ route('admin.univers') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
            ← Retour à l'univers
        </a>
    </header>

    <div class="flex-1 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900/80 border-r border-red-500/20 p-4">
            <nav class="space-y-2">
                <a href="{{ route('admin.index') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Dashboard
                </a>
                <a href="{{ route('admin.comptes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Comptes
                </a>
                <a href="{{ route('admin.univers') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Univers
                </a>
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Planètes
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Messages de succès -->
            @if(session('success'))
            <div class="bg-green-900/50 border border-green-500 text-green-300 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <!-- Informations du système -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Informations stellaires</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Nom</div>
                        <div class="text-white font-bold">{{ $systeme->nom }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Type spectral</div>
                        <div class="text-yellow-400 font-bold">{{ $systeme->type_etoile }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Couleur</div>
                        <div class="text-white">{{ $systeme->couleur }}</div>
                    </div>
                    <div class="col-span-3">
                        <div class="text-xs text-gray-400 mb-2">Puissance</div>
                        <div class="flex gap-2 items-center">
                            <form method="POST" action="{{ route('admin.univers.update-puissance', $systeme->id) }}" class="flex gap-2 items-center flex-1">
                                @csrf
                                <input type="number" name="puissance" value="{{ $systeme->puissance }}" min="1" max="200"
                                       class="w-32 bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                    💾 Sauvegarder
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.univers.recalculer-puissance', $systeme->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded text-sm">
                                    🎲 Recalculer (type {{ substr($systeme->type_etoile, 0, 1) }})
                                </button>
                            </form>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            @php
                                $typeClass = strtoupper(substr($systeme->type_etoile, 0, 1));
                                $plages = [
                                    'O' => [150, 200], 'B' => [100, 140], 'A' => [80, 100],
                                    'F' => [60, 80], 'G' => [40, 60], 'K' => [30, 40], 'M' => [20, 30]
                                ];
                                $plage = $plages[$typeClass] ?? [40, 60];
                            @endphp
                            Plage pour type {{ $typeClass }}: {{ $plage[0] }}-{{ $plage[1] }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Détectabilité de base</div>
                        <div class="text-cyan-300 font-bold">
                            @if($systeme->detectabilite_base)
                                {{ number_format($systeme->detectabilite_base, 2) }}
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">POI Connu</div>
                        <div>
                            @if($systeme->poi_connu)
                                <span class="text-green-400">✓ Oui</span>
                            @else
                                <span class="text-gray-500">Non</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Coordonnées (secteur)</div>
                        <div class="text-gray-300">
                            {{ $systeme->secteur_x }}, {{ $systeme->secteur_y }}, {{ $systeme->secteur_z }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Position intra-secteur</div>
                        <div class="text-gray-300">
                            {{ number_format($systeme->position_x, 2) }},
                            {{ number_format($systeme->position_y, 2) }},
                            {{ number_format($systeme->position_z, 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Coordonnées absolues (AL)</div>
                        <div class="text-cyan-400 font-bold">
                            {{ number_format($systeme->secteur_x * 10 + $systeme->position_x, 2) }},
                            {{ number_format($systeme->secteur_y * 10 + $systeme->position_y, 2) }},
                            {{ number_format($systeme->secteur_z * 10 + $systeme->position_z, 2) }}
                        </div>
                    </div>
                    @if($systeme->source_gaia)
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Source GAIA</div>
                        <div class="text-purple-400 font-mono text-xs">{{ $systeme->gaia_source_id }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">RA / Dec</div>
                        <div class="text-gray-300 text-sm">
                            {{ number_format($systeme->gaia_ra, 4) }}° / {{ number_format($systeme->gaia_dec, 4) }}°
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Distance GAIA (AL)</div>
                        <div class="text-orange-300 font-bold">{{ number_format($systeme->gaia_distance_ly, 2) }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Planètes -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h2 class="text-xl font-bold text-white mb-4">
                    Planètes ({{ $systeme->planetes->count() }})
                </h2>

                @if($systeme->planetes->count() > 0)
                    <div class="space-y-4">
                        @foreach($systeme->planetes as $planete)
                        <div class="bg-gray-900/50 border border-gray-600 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-white">{{ $planete->nom }}</h3>
                                    <div class="text-sm text-gray-400">Type: <span class="text-cyan-400">{{ ucfirst($planete->type) }}</span></div>
                                </div>
                                <div class="text-right">
                                    @if($planete->accessible)
                                        <span class="text-green-400 text-sm">✓ Accessible</span>
                                    @else
                                        <span class="text-red-400 text-sm">✗ Inaccessible</span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm mb-3">
                                <div>
                                    <div class="text-gray-400">Distance étoile</div>
                                    <div class="text-white">{{ number_format($planete->distance_etoile, 2) }} UA</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Rayon</div>
                                    <div class="text-white">{{ number_format($planete->rayon, 2) }} R⊕</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Masse</div>
                                    <div class="text-white">{{ number_format($planete->masse, 2) }} M⊕</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Atmosphère</div>
                                    <div class="text-white">{{ $planete->a_atmosphere ? 'Oui' : 'Non' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Population</div>
                                    <div class="text-white">{{ number_format($planete->population) }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Détectabilité</div>
                                    <div class="text-cyan-300">{{ number_format($planete->detectabilite_base, 2) }}</div>
                                </div>
                            </div>

                            @if(!$planete->accessible && $planete->raison_inaccessible)
                            <div class="text-sm text-red-300 mb-3">
                                <span class="text-gray-400">Raison:</span> {{ $planete->raison_inaccessible }}
                            </div>
                            @endif

                            <!-- Gisements -->
                            @php
                                // Accéder à la relation via getRelation pour éviter conflit avec attribut
                                try {
                                    $gisementsRelation = $planete->getRelation('gisements');
                                } catch (\Exception $e) {
                                    $gisementsRelation = collect();
                                }
                                if (!$gisementsRelation) {
                                    $gisementsRelation = collect();
                                }
                            @endphp
                            @if($gisementsRelation->count() > 0)
                            <div class="mt-3 border-t border-gray-600 pt-3">
                                <div class="text-sm text-gray-400 mb-2">Gisements ({{ $gisementsRelation->count() }})</div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($gisementsRelation as $gisement)
                                    <div class="bg-gray-800 rounded px-2 py-1 text-xs">
                                        <span class="text-yellow-400">{{ $gisement->ressource->nom }}</span>
                                        <span class="text-gray-400">- {{ number_format($gisement->quantite_restante) }} unités</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Stations -->
                            @php
                                // Accéder à la relation via getRelation pour éviter conflit
                                try {
                                    $stationsRelation = $planete->getRelation('stations');
                                } catch (\Exception $e) {
                                    $stationsRelation = collect();
                                }
                                if (!$stationsRelation) {
                                    $stationsRelation = collect();
                                }
                            @endphp
                            @if($stationsRelation->count() > 0)
                            <div class="mt-3 border-t border-gray-600 pt-3">
                                <div class="text-sm text-gray-400 mb-2">Stations ({{ $stationsRelation->count() }})</div>
                                <div class="space-y-2">
                                    @foreach($stationsRelation as $station)
                                    <div class="bg-gray-800 rounded px-3 py-2 text-sm">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="text-white font-bold">{{ $station->nom }}</span>
                                                <span class="text-gray-400 ml-2">{{ ucfirst(str_replace('_', ' ', $station->type)) }}</span>
                                            </div>
                                            @if($station->accessible)
                                                <span class="text-green-400 text-xs">✓ Accessible</span>
                                            @else
                                                <span class="text-red-400 text-xs">✗ Inaccessible</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            Capacité: {{ $station->capacite_amarrage }} vaisseaux
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-400 py-8">
                        Aucune planète dans ce système
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection
