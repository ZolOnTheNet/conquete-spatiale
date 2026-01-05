@extends('layouts.admin')

@section('title', 'Admin - Mines')

@section('admin-title', 'MINES')

@section('admin-content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-white">Liste des Mines ({{ $mines->total() }})</h2>
    <a href="{{ route('admin.mines-admin.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        + Nouvelle Mine
    </a>
</div>

@if(session('success'))
    <div class="bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<!-- Filtres de recherche -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
    <form method="GET" action="{{ route('admin.mines-admin.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
            <input type="text" name="nom" value="{{ $filters['nom'] ?? '' }}" placeholder="Rechercher..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
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
            <label class="block text-sm font-medium text-gray-300 mb-1">Propriétaire</label>
            <input type="text" name="proprietaire" value="{{ $filters['proprietaire'] ?? '' }}" placeholder="Nom..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
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
            <a href="{{ route('admin.mines-admin.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">Réinitialiser</a>
        </div>
    </form>
</div>

<!-- Filtres de statut -->
<div class="mb-4 flex gap-4">
            <a href="{{ route('admin.mines-admin.index') }}" class="px-3 py-1 rounded {{ !request('statut') ? 'bg-red-500 text-white' : 'bg-gray-700 text-gray-300' }}">
                Toutes
            </a>
            <a href="{{ route('admin.mines-admin.index', ['statut' => 'active']) }}" class="px-3 py-1 rounded {{ request('statut') == 'active' ? 'bg-green-500 text-white' : 'bg-gray-700 text-gray-300' }}">
                Actives
            </a>
            <a href="{{ route('admin.mines-admin.index', ['statut' => 'inactive']) }}" class="px-3 py-1 rounded {{ request('statut') == 'inactive' ? 'bg-gray-500 text-white' : 'bg-gray-700 text-gray-300' }}">
                Inactives
            </a>
            <a href="{{ route('admin.mines-admin.index', ['statut' => 'maintenance']) }}" class="px-3 py-1 rounded {{ request('statut') == 'maintenance' ? 'bg-yellow-500 text-white' : 'bg-gray-700 text-gray-300' }}">
                Maintenance
            </a>
        </div>

        <!-- Table -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Planete</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Ressource</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Proprietaire</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($mines as $mine)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-white">{{ $mine->nom }}</td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $mine->planete->nom ?? 'N/A' }}
                                <span class="text-xs text-gray-500">({{ $mine->planete->systemeStellaire->nom ?? '' }})</span>
                            </td>
                            <td class="px-4 py-3 text-cyan-400">{{ $mine->gisement->ressource->nom ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ $mine->proprietaire->nom ?? 'Aucun' }}</td>
                            <td class="px-4 py-3">
                                @switch($mine->statut)
                                    @case('active')
                                        <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Active</span>
                                        @break
                                    @case('inactive')
                                        <span class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-400">Inactive</span>
                                        @break
                                    @case('maintenance')
                                        <span class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-400">Maintenance</span>
                                        @break
                                    @default
                                        <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-400">{{ $mine->statut }}</span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ number_format($mine->stock_actuel ?? 0) }} / {{ number_format($mine->capacite_stockage ?? 0) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.mines-admin.edit', $mine) }}" class="text-cyan-400 hover:text-cyan-300 text-sm">Modifier</a>
                                    <form action="{{ route('admin.mines-admin.maintenance', $mine) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-yellow-400 hover:text-yellow-300 text-sm">Maintenance</button>
                                    </form>
                                    <form action="{{ route('admin.mines-admin.destroy', $mine) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette mine ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Suppr</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune mine trouvee</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<!-- Pagination -->
<div class="mt-4">
    {{ $mines->links() }}
</div>
@endsection
