@extends('layouts.admin')

@section('title', 'Admin - Productions')

@section('admin-title', 'GESTION PRODUCTIONS')

@section('admin-content')
<h2 class="text-xl font-bold text-white mb-6">Gestion des Productions (Gisements)</h2>

<!-- Filtres de recherche -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
    <form method="GET" action="{{ route('admin.production') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Système</label>
            <input type="text" name="systeme" value="{{ $filters['systeme'] ?? '' }}"
                placeholder="Nom du système..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Planète</label>
            <input type="text" name="planete" value="{{ $filters['planete'] ?? '' }}"
                placeholder="Nom de la planète..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Ressource</label>
            <select name="ressource_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="">Toutes les ressources</option>
                @foreach($ressources as $ressource)
                    <option value="{{ $ressource->id }}" {{ ($filters['ressource_id'] ?? '') == $ressource->id ? 'selected' : '' }}>
                        {{ $ressource->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Par page</label>
            <select name="per_page" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="20" {{ ($filters['per_page'] ?? 20) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ ($filters['per_page'] ?? 20) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ ($filters['per_page'] ?? 20) == 100 ? 'selected' : '' }}>100</option>
                <option value="200" {{ ($filters['per_page'] ?? 20) == 200 ? 'selected' : '' }}>200</option>
                <option value="all" {{ ($filters['per_page'] ?? 20) == 'all' ? 'selected' : '' }}>Tous</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded text-sm">
                Rechercher
            </button>
            <a href="{{ route('admin.production') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Table des gisements -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-900/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Système</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Planète</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Ressource</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Richesse</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Quantité</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Restant</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($gisements as $gisement)
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-sm text-cyan-400">
                        {{ $gisement->planete->systemeStellaire->nom ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-white">
                        {{ $gisement->planete->nom ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-yellow-400">
                        {{ $gisement->ressource->nom ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-2 bg-gray-700 rounded">
                                <div class="h-2 rounded bg-yellow-500" style="width: {{ $gisement->richesse }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400">{{ $gisement->richesse }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        {{ number_format($gisement->quantite_totale) }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @php
                            $pct = $gisement->quantite_totale > 0 ? ($gisement->quantite_restante / $gisement->quantite_totale) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-24 h-2 bg-gray-700 rounded">
                                <div class="h-2 rounded {{ $pct > 50 ? 'bg-green-500' : ($pct > 20 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400">{{ number_format($gisement->quantite_restante) }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if($gisement->decouvert)
                            <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Découvert</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-400">Non découvert</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucun gisement trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $gisements->links() }}</div>

@endsection
