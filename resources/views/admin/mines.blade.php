@extends('layouts.admin')

@section('title', 'Admin - Mines (MAME)')

@section('admin-title', 'MINES D'EXPLOITATION (MAME)')

@section('admin-content')
<!-- Messages de succès -->
            @if(session('success'))
            <div class="bg-green-900/50 border border-green-500 text-green-300 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Toutes les mines ({{ $mines->total() }})</h2>
            </div>

            <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Nom</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Planète</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Système</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Ressource</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Statut</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Production</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Stock</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Usure</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Propriétaire</th>
                            <th class="px-4 py-3 text-right text-xs text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($mines as $mine)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-sm text-cyan-400 font-bold">
                                {{ $mine->nom }}
                            </td>
                            <td class="px-4 py-3 text-sm text-white">
                                <a href="{{ route('admin.planetes.show', $mine->planete_id) }}" class="hover:text-cyan-400">
                                    {{ $mine->planete->nom }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-yellow-300">
                                {{ $mine->planete->systemeStellaire->nom ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-yellow-400">
                                {{ $mine->gisement->ressource->nom ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs
                                    @if($mine->statut == 'active') bg-green-900/50 text-green-400
                                    @elseif($mine->statut == 'inactive') bg-gray-900/50 text-gray-400
                                    @elseif($mine->statut == 'maintenance') bg-orange-900/50 text-orange-400
                                    @else bg-red-900/50 text-red-400
                                    @endif
                                ">
                                    {{ ucfirst($mine->statut) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-green-400">
                                {{ $mine->taux_extraction }} u/jour
                            </td>
                            <td class="px-4 py-3 text-sm text-blue-400">
                                {{ number_format($mine->stock_actuel) }} / {{ number_format($mine->capacite_stockage) }}
                                <span class="text-xs text-gray-500">
                                    ({{ $mine->capacite_stockage > 0 ? round(($mine->stock_actuel / $mine->capacite_stockage) * 100) : 0 }}%)
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="
                                    @if($mine->niveau_usure >= 80) text-red-400
                                    @elseif($mine->niveau_usure >= 50) text-orange-400
                                    @else text-green-400
                                    @endif
                                ">
                                    {{ $mine->niveau_usure }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-300">
                                {{ $mine->proprietaire->nom ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <div class="flex gap-1 justify-end">
                                    <button onclick="ravitaillerMine({{ $mine->id }})"
                                            class="bg-yellow-600/80 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs"
                                            title="Ravitailler">
                                        ⚡
                                    </button>
                                    <button onclick="maintenanceMine({{ $mine->id }})"
                                            class="bg-orange-600/80 hover:bg-orange-600 text-white px-2 py-1 rounded text-xs"
                                            title="Maintenance">
                                        🔧
                                    </button>
                                    <a href="{{ route('admin.planetes.show', $mine->planete_id) }}"
                                       class="bg-blue-600/80 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                       title="Voir planète">
                                        🌍
                                    </a>
                                    <button onclick="supprimerMine({{ $mine->id }}, '{{ $mine->nom }}')"
                                            class="bg-red-600/80 hover:bg-red-600 text-white px-2 py-1 rounded text-xs"
                                            title="Supprimer">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-gray-400">
                                Aucune mine trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($mines->hasPages())
            <div class="mt-6">
                {{ $mines->links() }}
            </div>
            @endif
@endsection
