@extends('layouts.app')

@section('title', 'Admin - Univers')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">ADMINISTRATION</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
            Retour au jeu
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
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Productions
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <h2 class="text-xl font-bold text-white mb-6">Exploration de l'Univers</h2>

            <!-- Formulaire de filtrage -->
            <form method="GET" action="{{ route('admin.univers') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <!-- Coordonnées de référence -->
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Coord X (AL)</label>
                        <input type="number" step="0.01" name="coord_x" value="{{ $coordX }}"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Coord Y (AL)</label>
                        <input type="number" step="0.01" name="coord_y" value="{{ $coordY }}"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Coord Z (AL)</label>
                        <input type="number" step="0.01" name="coord_z" value="{{ $coordZ }}"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>

                    <!-- Distance maximale -->
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Distance max (0=∞)</label>
                        <input type="number" step="0.01" min="0" name="max_distance" value="{{ $maxDistance }}"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>

                    <!-- Résultats par page -->
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Par page</label>
                        <select name="per_page" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ $perPage == 200 ? 'selected' : '' }}>200</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded text-sm">
                        🔍 Filtrer
                    </button>
                    <a href="{{ route('admin.univers') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔄 Réinitialiser
                    </a>
                </div>

                <!-- Conserver les paramètres de tri -->
                <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
            </form>

            <!-- Statistiques -->
            <div class="mb-4 text-sm text-gray-400">
                Total: <span class="text-white font-bold">{{ $systemes->total() }}</span> systèmes
                @if($maxDistance > 0)
                    dans un rayon de <span class="text-cyan-400">{{ $maxDistance }}</span> AL
                @endif
            </div>

            <!-- Tableau des systèmes -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'nom', 'sort_direction' => $sortBy == 'nom' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    Nom
                                    @if($sortBy == 'nom')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'type_etoile', 'sort_direction' => $sortBy == 'type_etoile' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    Type
                                    @if($sortBy == 'type_etoile')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'puissance', 'sort_direction' => $sortBy == 'puissance' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    Puissance
                                    @if($sortBy == 'puissance')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'detectabilite_base', 'sort_direction' => $sortBy == 'detectabilite_base' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    Détectabilité
                                    @if($sortBy == 'detectabilite_base')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'distance_squared', 'sort_direction' => $sortBy == 'distance_squared' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    Distance (AL)
                                    @if($sortBy == 'distance_squared')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                D_CAL (Seuil)
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'poi_connu', 'sort_direction' => $sortBy == 'poi_connu' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    POI
                                    @if($sortBy == 'poi_connu')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">
                                <a href="{{ route('admin.univers', array_merge(request()->all(), ['sort_by' => 'planetes_count', 'sort_direction' => $sortBy == 'planetes_count' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-cyan-400">
                                    Planètes
                                    @if($sortBy == 'planetes_count')
                                        <span class="text-cyan-400">{{ $sortDirection == 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Coordonnées</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($systemes as $systeme)
                        <tr class="hover:bg-gray-700/50 cursor-pointer" onclick="window.location='{{ route('admin.univers.show', $systeme->id) }}'">
                            <td class="px-4 py-3 text-sm text-white">{{ $systeme->nom }}</td>
                            <td class="px-4 py-3 text-sm text-yellow-400">{{ $systeme->type_etoile }}</td>
                            <td class="px-4 py-3 text-sm text-orange-400">
                                @if($systeme->puissance)
                                    {{ $systeme->puissance }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-cyan-300">
                                @if($systeme->detectabilite_base)
                                    {{ number_format($systeme->detectabilite_base, 2) }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-orange-300 font-bold">
                                {{ number_format(sqrt($systeme->distance_squared), 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-red-300 font-bold">
                                @php
                                    $distance = sqrt($systeme->distance_squared);
                                    $facteurDistanceAL = 15;
                                    $dCal = $systeme->detectabilite_base ? $systeme->detectabilite_base * ($distance / $facteurDistanceAL) : 0;
                                @endphp
                                {{ number_format($dCal, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($systeme->poi_connu)
                                    <span class="text-green-400">✓</span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-cyan-400">{{ $systeme->planetes_count }}</td>
                            <td class="px-4 py-3 text-sm text-gray-300">
                                {{ number_format($systeme->secteur_x * 10 + $systeme->position_x, 2) }},
                                {{ number_format($systeme->secteur_y * 10 + $systeme->position_y, 2) }},
                                {{ number_format($systeme->secteur_z * 10 + $systeme->position_z, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $systemes->links() }}
            </div>
        </main>
    </div>
</div>
@endsection
