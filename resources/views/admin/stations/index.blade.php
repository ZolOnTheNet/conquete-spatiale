@extends('layouts.admin')

@section('title', 'Admin - Stations')

@section('admin-title', 'STATIONS')

@section('admin-content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-white">Liste des Stations ({{ $stations->total() }})</h2>
    <a href="{{ route('admin.stations.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        + Nouvelle Station
    </a>
</div>

<!-- Filtres de recherche -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
    <form method="GET" action="{{ route('admin.stations.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
            <input type="text" name="nom" value="{{ $filters['nom'] ?? '' }}"
                placeholder="Rechercher..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Type</label>
            <select name="type" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="">Tous les types</option>
                <option value="orbitale" {{ ($filters['type'] ?? '') == 'orbitale' ? 'selected' : '' }}>Orbitale</option>
                <option value="spatiale" {{ ($filters['type'] ?? '') == 'spatiale' ? 'selected' : '' }}>Spatiale</option>
                <option value="miniere" {{ ($filters['type'] ?? '') == 'miniere' ? 'selected' : '' }}>Minière</option>
                <option value="militaire" {{ ($filters['type'] ?? '') == 'militaire' ? 'selected' : '' }}>Militaire</option>
                <option value="commerciale" {{ ($filters['type'] ?? '') == 'commerciale' ? 'selected' : '' }}>Commerciale</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Système</label>
            <select name="systeme_id" id="systeme-select" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="">Tous les systèmes</option>
                @foreach($systemes as $systeme)
                    <option value="{{ $systeme->id }}" {{ ($filters['systeme_id'] ?? '') == $systeme->id ? 'selected' : '' }}>
                        {{ $systeme->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Planète</label>
            <select name="planete_id" id="planete-select" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="">Toutes les planètes</option>
                @foreach($planetes as $planete)
                    <option value="{{ $planete->id }}" {{ ($filters['planete_id'] ?? '') == $planete->id ? 'selected' : '' }}>
                        {{ $planete->nom }}
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
            <a href="{{ route('admin.stations.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Systeme</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Planete</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Services</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Capacite</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($stations as $station)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.stations.edit', $station) }}" class="text-white hover:text-cyan-400">
                                    {{ $station->nom }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-300">{{ $station->type }}</td>
                            <td class="px-4 py-3 text-cyan-400">{{ $station->systemeStellaire->nom ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ $station->planete->nom ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    @if($station->commerciale)<span class="px-1 py-0.5 rounded text-xs bg-green-500/20 text-green-400">Commerce</span>@endif
                                    @if($station->ravitaillement)<span class="px-1 py-0.5 rounded text-xs bg-blue-500/20 text-blue-400">Carburant</span>@endif
                                    @if($station->reparations)<span class="px-1 py-0.5 rounded text-xs bg-yellow-500/20 text-yellow-400">Reparations</span>@endif
                                    @if($station->medical)<span class="px-1 py-0.5 rounded text-xs bg-red-500/20 text-red-400">Medical</span>@endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-300">{{ $station->capacite_amarrage }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.stations.edit', $station) }}" class="text-cyan-400 hover:text-cyan-300 text-sm">Modifier</a>
                                    <form action="{{ route('admin.stations.destroy', $station) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette station ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Suppr</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune station trouvee</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<div class="mt-4">{{ $stations->links() }}</div>
@endsection
