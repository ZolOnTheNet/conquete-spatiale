@extends('layouts.admin')

@section('title', 'Admin - Gisements')

@section('admin-title', 'GISEMENTS')

@section('admin-content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-white">Liste des Gisements ({{ $gisements->total() }})</h2>
    <div class="flex gap-2">
        <a href="{{ route('admin.ressources.index') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
            Ressources
        </a>
        <a href="{{ route('admin.gisements.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            + Nouveau Gisement
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Système</label>
            <input type="text" name="systeme" value="{{ $filters['systeme'] ?? '' }}" placeholder="Nom du système..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Planète</label>
            <input type="text" name="planete" value="{{ $filters['planete'] ?? '' }}" placeholder="Nom de la planète..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Ressource</label>
            <select name="ressource_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="">Toutes</option>
                @foreach($ressources as $ressource)
                    <option value="{{ $ressource->id }}" {{ ($filters['ressource_id'] ?? '') == $ressource->id ? 'selected' : '' }}>{{ $ressource->nom }}</option>
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
            <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded text-sm">Rechercher</button>
            <a href="{{ route('admin.gisements.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">Réinitialiser</a>
        </div>
    </form>
</div>

        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filtres -->
        <div class="mb-4 flex gap-4 items-center">
            <span class="text-gray-400">Filtrer par ressource:</span>
            <a href="{{ route('admin.gisements.index') }}" class="px-3 py-1 rounded {{ !request('ressource_id') ? 'bg-cyan-500 text-white' : 'bg-gray-700 text-gray-300' }}">
                Toutes
            </a>
            @foreach($ressources as $res)
                <a href="{{ route('admin.gisements.index', ['ressource_id' => $res->id]) }}"
                    class="px-3 py-1 rounded {{ request('ressource_id') == $res->id ? 'bg-cyan-500 text-white' : 'bg-gray-700 text-gray-300' }}">
                    {{ $res->nom }}
                </a>
            @endforeach
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Planete</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Systeme</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Ressource</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Richesse</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Quantite restante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Progression</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Decouvert</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($gisements as $gisement)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3">
                                @if($gisement->planete)
                                    <a href="{{ route('admin.planetes.show', $gisement->planete->id) }}" class="text-white hover:text-cyan-400 underline">
                                        {{ $gisement->planete->nom }}
                                    </a>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($gisement->planete && $gisement->planete->systemeStellaire)
                                    <a href="{{ route('admin.univers.show', $gisement->planete->systemeStellaire->id) }}" class="text-gray-300 hover:text-cyan-400 underline">
                                        {{ $gisement->planete->systemeStellaire->nom }}
                                    </a>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-cyan-400">{{ $gisement->ressource->nom ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-12 h-2 bg-gray-700 rounded">
                                        <div class="h-2 rounded bg-yellow-500" style="width: {{ $gisement->richesse }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $gisement->richesse }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ number_format($gisement->quantite_restante) }}
                                <span class="text-xs text-gray-500">/ {{ number_format($gisement->quantite_totale) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $pct = $gisement->quantite_totale > 0 ? ($gisement->quantite_restante / $gisement->quantite_totale) * 100 : 0;
                                @endphp
                                <div class="w-24 h-2 bg-gray-700 rounded">
                                    <div class="h-2 rounded {{ $pct > 50 ? 'bg-green-500' : ($pct > 20 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($gisement->decouvert)
                                    <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Oui</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-400">Non</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucun gisement trouve</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<div class="mt-4">{{ $gisements->links() }}</div>
@endsection
