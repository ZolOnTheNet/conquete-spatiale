@extends('layouts.admin')

@section('title', 'Admin - Planètes')

@section('admin-title', 'ADMINISTRATION')

@section('admin-content')
<h2 class="text-xl font-bold text-white mb-6">Planètes et Objets Célestes</h2>

            <!-- Formulaire de filtrage -->
            <form method="GET" action="{{ route('admin.planetes') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <!-- Nom de la planète -->
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Nom planète</label>
                        <input type="text" name="nom_planete" value="{{ $filters['nom_planete'] ?? '' }}" placeholder="Recherche partielle..."
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>

                    <!-- Nom du système -->
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Nom système</label>
                        <input type="text" name="nom_systeme" value="{{ $filters['nom_systeme'] ?? '' }}" placeholder="Nom GAIA ou commun..."
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>

                    <!-- Type de planète -->
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Type</label>
                        <select name="type" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                            <option value="">Tous les types</option>
                            <option value="terrestre" {{ ($filters['type'] ?? '') == 'terrestre' ? 'selected' : '' }}>Terrestre</option>
                            <option value="tellurique" {{ ($filters['type'] ?? '') == 'tellurique' ? 'selected' : '' }}>Tellurique</option>
                            <option value="gazeuse" {{ ($filters['type'] ?? '') == 'gazeuse' ? 'selected' : '' }}>Gazeuse</option>
                            <option value="glacee" {{ ($filters['type'] ?? '') == 'glacee' ? 'selected' : '' }}>Glacée</option>
                            <option value="oceanique" {{ ($filters['type'] ?? '') == 'oceanique' ? 'selected' : '' }}>Océanique</option>
                            <option value="desertique" {{ ($filters['type'] ?? '') == 'desertique' ? 'selected' : '' }}>Désertique</option>
                            <option value="volcanique" {{ ($filters['type'] ?? '') == 'volcanique' ? 'selected' : '' }}>Volcanique</option>
                        </select>
                    </div>

                    <!-- Items par page -->
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Par page</label>
                        <select name="per_page" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                            <option value="25" {{ ($filters['per_page'] ?? 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($filters['per_page'] ?? 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($filters['per_page'] ?? 25) == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ ($filters['per_page'] ?? 25) == 200 ? 'selected' : '' }}>200</option>
                        </select>
                    </div>
                </div>

                <!-- Coordonnées (ligne 2) -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Coord X (AL)</label>
                        <input type="number" step="1" name="coord_x" value="{{ $filters['coord_x'] ?? '' }}" placeholder="0"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Coord Y (AL)</label>
                        <input type="number" step="1" name="coord_y" value="{{ $filters['coord_y'] ?? '' }}" placeholder="0"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Coord Z (AL)</label>
                        <input type="number" step="1" name="coord_z" value="{{ $filters['coord_z'] ?? '' }}" placeholder="0"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-200 mb-1">Distance max (AL)</label>
                        <input type="number" step="1" min="0" name="max_distance" value="{{ $filters['max_distance'] ?? '' }}" placeholder="0 = ∞"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>

                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-200 mb-1">POI Connu</label>
                            <select name="poi_connu" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                                <option value="">Tous</option>
                                <option value="true" {{ ($filters['poi_connu'] ?? '') == 'true' ? 'selected' : '' }}>Oui</option>
                                <option value="false" {{ ($filters['poi_connu'] ?? '') == 'false' ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-200 mb-1">NASA</label>
                            <select name="source_nasa" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                                <option value="">Tous</option>
                                <option value="true" {{ ($filters['source_nasa'] ?? '') == 'true' ? 'selected' : '' }}>Oui</option>
                                <option value="false" {{ ($filters['source_nasa'] ?? '') == 'false' ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded text-sm">
                        🔍 Filtrer
                    </button>
                    <a href="{{ route('admin.planetes') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔄 Réinitialiser
                    </a>
                </div>

                <!-- Statistiques -->
                <div class="mt-3 text-sm text-gray-400">
                    Total: <span class="text-white font-bold">{{ $planetes->total() }}</span> planète(s)
                </div>
            </form>

            <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">Nom</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">Système</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">Type</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">Rayon</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">Détectabilité</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">POI Connu</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-200">Accessible</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($planetes as $planete)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.planetes.show', $planete->id) }}"
                                   class="text-white hover:text-cyan-300 underline hover:no-underline"
                                   title="Voir les détails de la planète">
                                    {{ $planete->nom }}
                                </a>
                                @if($planete->source_nasa_exoplanet)
                                    <span class="ml-2 bg-green-600 text-white px-2 py-0.5 rounded text-xs">NASA</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($planete->systemeStellaire)
                                    <a href="{{ route('admin.univers.show', $planete->systemeStellaire->id) }}"
                                       class="text-yellow-300 hover:text-yellow-200 underline hover:no-underline"
                                       title="Voir le système stellaire">
                                        @if($planete->systemeStellaire->nom_commun)
                                            {{ $planete->systemeStellaire->nom_commun }}
                                            <span class="text-xs text-gray-500">({{ Str::limit($planete->systemeStellaire->nom, 20) }})</span>
                                        @else
                                            {{ $planete->systemeStellaire->nom }}
                                        @endif
                                    </a>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-blue-400">
                                @if($planete->type_planete)
                                    {{ $planete->type_planete }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-purple-400">
                                @if($planete->rayon)
                                    {{ number_format($planete->rayon, 2) }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-cyan-300 font-semibold">
                                @if($planete->detectabilite_base)
                                    {{ number_format($planete->detectabilite_base, 2) }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($planete->poi_connu)
                                    <span class="text-green-400">✓</span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($planete->accessible)
                                    <span class="text-green-400">Oui</span>
                                @else
                                    <span class="text-red-400" title="{{ $planete->raison_inaccessible }}">Non</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $planetes->links() }}
            </div>
@endsection
