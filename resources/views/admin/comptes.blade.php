@extends('layouts.admin')

@section('title', 'Admin - Comptes')

@section('admin-title', 'COMPTES')

@section('admin-content')
<h2 class="text-xl font-bold text-white mb-6">Gestion des Comptes</h2>

<!-- Filtres de recherche -->
<div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
    <form method="GET" action="{{ route('admin.comptes') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
            <input type="text" name="nom" value="{{ $filters['nom'] ?? '' }}"
                placeholder="Rechercher par nom..."
                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
            <input type="text" name="email" value="{{ $filters['email'] ?? '' }}"
                placeholder="Rechercher par email..."
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
            <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded text-sm">
                Rechercher
            </button>
            <a href="{{ route('admin.comptes') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-900/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs text-gray-200">ID</th>
                <th class="px-4 py-3 text-left text-xs text-gray-200">Nom</th>
                <th class="px-4 py-3 text-left text-xs text-gray-200">Email</th>
                <th class="px-4 py-3 text-left text-xs text-gray-200">Personnages</th>
                <th class="px-4 py-3 text-left text-xs text-gray-200">Admin</th>
                <th class="px-4 py-3 text-left text-xs text-gray-200">Cree le</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @foreach($comptes as $compte)
            <tr class="hover:bg-gray-700/30">
                <td class="px-4 py-3 text-sm text-gray-300">{{ $compte->id }}</td>
                <td class="px-4 py-3 text-sm text-white">{{ $compte->nom_login }}</td>
                <td class="px-4 py-3 text-sm text-gray-300">{{ $compte->adresse_mail }}</td>
                <td class="px-4 py-3 text-sm text-cyan-400">{{ $compte->personnages->count() }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($compte->is_admin)
                        <span class="text-red-400">Oui</span>
                    @else
                        <span class="text-gray-500">Non</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $compte->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $comptes->links() }}
</div>
@endsection
