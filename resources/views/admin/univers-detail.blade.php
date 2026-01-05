@extends('layouts.admin')

@section('title', 'Admin - Détails Système')

@section('admin-title', 'SYSTÈME: {{ $systeme->nom }}')

@section('admin-content')
<!-- Messages de succès -->
            @if(session('success'))
            <div class="bg-green-900/50 border border-green-500 text-green-300 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <!-- Informations du système + Visualisation -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Informations stellaires</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Colonne gauche : Informations -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-200 mb-1">Nom</div>
                            <div class="text-white font-bold">{{ $systeme->nom }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-200 mb-1">Type spectral</div>
                            <div class="text-yellow-400 font-bold">{{ $systeme->type_etoile }}</div>
                        </div>
                        <div>
                        <div class="text-xs text-gray-200 mb-1">Couleur</div>
                        <div class="text-white">{{ $systeme->couleur }}</div>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <div class="text-xs text-gray-200 mb-2">Puissance</div>
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
                        <div class="text-xs text-gray-200 mb-1">Détectabilité de base</div>
                        <div class="text-cyan-300 font-bold">
                            @if($systeme->detectabilite_base)
                                {{ number_format($systeme->detectabilite_base, 2) }}
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-200 mb-1">POI Connu</div>
                        <div>
                            @if($systeme->poi_connu)
                                <span class="text-green-400">✓ Oui</span>
                            @else
                                <span class="text-gray-500">Non</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-200 mb-1">Coordonnées (secteur)</div>
                        <div class="text-gray-300">
                            {{ $systeme->secteur_x }}, {{ $systeme->secteur_y }}, {{ $systeme->secteur_z }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-200 mb-1">Position intra-secteur (UA)</div>
                        <div class="text-gray-300">
                            {{ number_format($systeme->position_x/100, 2) }},
                            {{ number_format($systeme->position_y/100, 2) }},
                            {{ number_format($systeme->position_z/100, 2) }}
                            <span class="text-xs text-gray-500">UA</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-200 mb-1">Coordonnées absolues (AL)</div>
                        <div class="text-cyan-400 font-bold">
                            {{ $systeme->secteur_x }},
                            {{ $systeme->secteur_y }},
                            {{ $systeme->secteur_z }}
                            <span class="text-xs text-gray-500">AL</span>
                        </div>
                    </div>
                    @if($systeme->source_gaia)
                    <div>
                        <div class="text-xs text-gray-200 mb-1">Source GAIA</div>
                        <div class="text-purple-400 font-mono text-xs">{{ $systeme->gaia_source_id }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-200 mb-1">RA / Dec</div>
                        <div class="text-gray-300 text-sm">
                            {{ number_format($systeme->gaia_ra, 4) }}° / {{ number_format($systeme->gaia_dec, 4) }}°
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-200 mb-1">Distance GAIA (AL)</div>
                        <div class="text-orange-300 font-bold">{{ number_format($systeme->gaia_distance_ly, 2) }}</div>
                    </div>
                    @endif

                    {{-- Nom célèbre --}}
                    @if($systeme->nom_commun)
                    <div class="col-span-1 md:col-span-2 bg-purple-900/20 border border-purple-500/30 rounded p-3">
                        <div class="text-xs text-purple-200 mb-1">🌟 Nom célèbre</div>
                        <div class="text-purple-300 font-bold text-lg">{{ $systeme->nom_commun }}</div>

                        @if($systeme->noms_alternatifs)
                            @php
                                $aliases = is_string($systeme->noms_alternatifs)
                                    ? json_decode($systeme->noms_alternatifs, true)
                                    : $systeme->noms_alternatifs;
                            @endphp
                            @if($aliases && count($aliases) > 0)
                                <div class="mt-2 text-xs text-gray-400">
                                    Noms alternatifs: {{ implode(', ', $aliases) }}
                                </div>
                            @endif
                        @endif
                    </div>
                    @endif

                    {{-- Nombre d'exoplanètes NASA --}}
                    @php
                        $nasaPlanetsCount = $systeme->planetes()->where('source_nasa_exoplanet', true)->count();
                    @endphp
                    @if($nasaPlanetsCount > 0)
                    <div class="col-span-1 md:col-span-2 bg-green-900/20 border border-green-500/30 rounded p-3">
                        <div class="text-green-300 font-bold">
                            🪐 {{ $nasaPlanetsCount }} exoplanète(s) réelle(s) NASA
                        </div>
                    </div>
                    @endif
                    </div>{{-- Fin grid infos --}}

                    <!-- Colonne droite : Visualisation du système -->
                    <div class="h-full">
                        @include('admin.partials.systeme-visualisation', ['systeme' => $systeme])
                    </div>
                </div>{{-- Fin grid 2 colonnes --}}
            </div>

            <!-- Planètes - Affichage condensé -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3">
                <h2 class="text-lg font-bold text-white mb-2">
                    Planètes ({{ $systeme->planetes->count() }})
                </h2>

                @if($systeme->planetes->count() > 0)
                    <div class="space-y-2">
                        @foreach($systeme->planetes as $planete)
                        <div class="bg-gray-900/50 border border-gray-600 rounded p-2">
                            <!-- En-tête planète compact -->
                            <div class="flex items-center justify-between mb-1 pb-1 border-b border-gray-700/50">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.planetes.show', $planete->id) }}"
                                       class="text-sm font-bold text-yellow-400 hover:text-yellow-300 underline hover:no-underline"
                                       title="Voir les détails de la planète">
                                        {{ $planete->nom }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ ucfirst($planete->type) }}</span>
                                    <span class="text-xs text-gray-600">({{ number_format($planete->rayon, 1) }}R, {{ number_format($planete->masse, 1) }}M)</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
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
                                    <span class="text-gray-500">{{ $gisementsRelation->count() }} gisements</span>
                                    @if($planete->accessible)
                                        <span class="text-green-400">✓</span>
                                    @else
                                        <span class="text-red-400">✗</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Gisements éditables -->
                            @if($gisementsRelation->count() > 0)
                            <div class="mt-1">
                                <table class="w-full text-xs">
                                    <thead class="text-gray-500 border-b border-gray-700/50">
                                        <tr>
                                            <th class="text-left py-1 px-1">Ressource</th>
                                            <th class="text-left py-1 px-1">Richesse</th>
                                            <th class="text-left py-1 px-1">Qté Totale</th>
                                            <th class="text-left py-1 px-1">Qté Restante</th>
                                            <th class="text-right py-1 px-1">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/30">
                                        @foreach($gisementsRelation as $gisement)
                                        <tr class="hover:bg-gray-700/20" data-gisement-id="{{ $gisement->id }}">
                                            <!-- Type ressource -->
                                            <td class="py-1 px-1">
                                                <select class="ressource-select bg-gray-900/50 border border-gray-700 rounded px-1 py-0.5 text-xs text-white w-24"
                                                        data-field="ressource_id"
                                                        data-gisement-id="{{ $gisement->id }}">
                                                    @foreach(\App\Models\Ressource::orderBy('nom')->get() as $ressource)
                                                        <option value="{{ $ressource->id }}"
                                                                {{ $gisement->ressource_id == $ressource->id ? 'selected' : '' }}>
                                                            {{ $ressource->nom }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Richesse -->
                                            <td class="py-1 px-1">
                                                <div class="flex items-center gap-0.5">
                                                    <input type="number"
                                                           class="richesse-input bg-gray-900/50 border border-gray-700 rounded px-1 py-0.5 text-xs text-cyan-400 w-12"
                                                           data-field="richesse"
                                                           data-gisement-id="{{ $gisement->id }}"
                                                           value="{{ $gisement->richesse }}"
                                                           min="1" max="100">
                                                    <span class="text-gray-600 text-xs">%</span>
                                                    <button class="recalc-btn text-blue-400 hover:text-blue-300 px-0.5 text-xs"
                                                            data-field="richesse"
                                                            data-gisement-id="{{ $gisement->id }}"
                                                            title="Recalculer richesse">⟲</button>
                                                </div>
                                            </td>

                                            <!-- Quantité totale -->
                                            <td class="py-1 px-1">
                                                <div class="flex items-center gap-0.5">
                                                    <input type="number"
                                                           class="qty-total-input bg-gray-900/50 border border-gray-700 rounded px-1 py-0.5 text-xs text-green-400 w-20"
                                                           data-field="quantite_totale"
                                                           data-gisement-id="{{ $gisement->id }}"
                                                           value="{{ $gisement->quantite_totale }}">
                                                    <button class="recalc-btn text-blue-400 hover:text-blue-300 px-0.5 text-xs"
                                                            data-field="quantite_totale"
                                                            data-gisement-id="{{ $gisement->id }}"
                                                            title="Recalculer quantité totale">⟲</button>
                                                </div>
                                            </td>

                                            <!-- Quantité restante -->
                                            <td class="py-1 px-1">
                                                <div class="flex items-center gap-0.5">
                                                    <input type="number"
                                                           class="qty-remain-input bg-gray-900/50 border border-gray-700 rounded px-1 py-0.5 text-xs text-yellow-400 w-20"
                                                           data-field="quantite_restante"
                                                           data-gisement-id="{{ $gisement->id }}"
                                                           value="{{ $gisement->quantite_restante }}">
                                                    <button class="recalc-btn text-blue-400 hover:text-blue-300 px-0.5 text-xs"
                                                            data-field="quantite_restante"
                                                            data-gisement-id="{{ $gisement->id }}"
                                                            title="Recalculer quantité restante">⟲</button>
                                                </div>
                                            </td>

                                            <!-- Bouton sauvegarder -->
                                            <td class="py-1 px-1 text-right">
                                                <button class="save-gisement-btn bg-green-600/80 hover:bg-green-600 text-white px-2 py-0.5 rounded text-xs"
                                                        data-gisement-id="{{ $gisement->id }}">
                                                    💾
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="text-xs text-gray-500 py-1">Aucun gisement</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-400 py-4 text-sm">
                        Aucune planète dans ce système
                    </div>
                @endif
            </div>
@endsection
