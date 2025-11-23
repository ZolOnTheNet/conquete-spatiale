@extends('layouts.app')

@section('title', 'Admin - Mines (MAME)')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">MINES D'EXPLOITATION (MAME)</h1>
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
                <a href="{{ route('admin.univers') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Univers
                </a>
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Planètes
                </a>
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Productions
                </a>
                <a href="{{ route('admin.mines') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Mines (MAME)
                </a>
                <a href="{{ route('admin.carte') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Carte
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
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
        </main>
    </div>
</div>

<!-- JavaScript pour actions mines -->
<script>
// Ravitailler une mine
function ravitaillerMine(mineId) {
    const html = `
        <div id="dialog-ravitailler" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
            <div class="bg-gray-800 border border-gray-600 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Ravitailler la mine</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Énergie (unités)</label>
                        <input type="number" id="ravit-energie" value="1000" min="0"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Pièces de rechange</label>
                        <input type="number" id="ravit-pieces-rechange" value="50" min="0"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Pièces d'usure</label>
                        <input type="number" id="ravit-pieces-usure" value="100" min="0"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button onclick="submitRavitailler(${mineId})" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-bold">
                        Ravitailler
                    </button>
                    <button onclick="document.getElementById('dialog-ravitailler').remove()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

function submitRavitailler(mineId) {
    const energie = document.getElementById('ravit-energie').value;
    const piecesRechange = document.getElementById('ravit-pieces-rechange').value;
    const piecesUsure = document.getElementById('ravit-pieces-usure').value;

    fetch(`/admin/mines/${mineId}/ravitailler`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            energie: energie,
            pieces_rechange: piecesRechange,
            pieces_usure: piecesUsure
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            alert('Erreur: ' + (result.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur de connexion');
    });
}

// Maintenance d'une mine
function maintenanceMine(mineId) {
    if (!confirm('Effectuer la maintenance de cette mine? (Réinitialise l\'usure à 0%)')) {
        return;
    }

    fetch(`/admin/mines/${mineId}/maintenance`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Erreur: ' + (result.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur de connexion');
    });
}

// Supprimer une mine
function supprimerMine(mineId, nom) {
    if (!confirm(`Supprimer définitivement la mine "${nom}"?`)) {
        return;
    }

    fetch(`/admin/mines/${mineId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Erreur lors de la suppression');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur de connexion');
    });
}
</script>
@endsection
