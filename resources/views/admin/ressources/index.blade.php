@extends('layouts.admin')

@section('title', 'Admin - Ressources')

@section('admin-title', 'RESSOURCES')

@section('admin-content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-white">Liste des Ressources ({{ $ressources->total() }})</h2>
    <div class="flex gap-2">
        <a href="{{ route('admin.gisements.index') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
            Gisements
        </a>
        <a href="{{ route('admin.ressources.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            + Nouvelle Ressource
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
            <input type="text" name="nom" value="{{ $filters['nom'] ?? '' }}" placeholder="Rechercher..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Code</label>
            <input type="text" name="code" value="{{ $filters['code'] ?? '' }}" placeholder="Code..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Catégorie</label>
            <select name="categorie" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                <option value="">Toutes</option>
                <option value="metaux" {{ ($filters['categorie'] ?? '') == 'metaux' ? 'selected' : '' }}>Métaux</option>
                <option value="gaz" {{ ($filters['categorie'] ?? '') == 'gaz' ? 'selected' : '' }}>Gaz</option>
                <option value="elementaire" {{ ($filters['categorie'] ?? '') == 'elementaire' ? 'selected' : '' }}>Élémentaire</option>
                <option value="chimie" {{ ($filters['categorie'] ?? '') == 'chimie' ? 'selected' : '' }}>Chimie</option>
                <option value="exotique" {{ ($filters['categorie'] ?? '') == 'exotique' ? 'selected' : '' }}>Exotique</option>
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
            <a href="{{ route('admin.ressources.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">Réinitialiser</a>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Categorie</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Rarete</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Prix base</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Gisements</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($ressources as $ressource)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3 font-mono text-cyan-400">{{ $ressource->code }}</td>
                            <td class="px-4 py-3 text-white">{{ $ressource->nom }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs bg-gray-600 text-gray-200">{{ ucfirst($ressource->categorie) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-2 bg-gray-700 rounded">
                                        <div class="h-2 rounded {{ $ressource->rarete > 50 ? 'bg-green-500' : ($ressource->rarete > 20 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                            style="width: {{ $ressource->rarete }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $ressource->rarete }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-yellow-400">{{ number_format($ressource->prix_base, 2) }} Cr</td>
                            <td class="px-4 py-3 text-gray-300">{{ $ressource->gisements_count }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.ressources.edit', $ressource) }}" class="text-cyan-400 hover:text-cyan-300 text-sm">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune ressource trouvee</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<div class="mt-4">{{ $ressources->links() }}</div>
@endsection
