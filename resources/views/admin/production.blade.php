@extends('layouts.app')

@section('title', 'Admin - Productions')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-orbitron text-red-400">GESTION PRODUCTIONS</h1>
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
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                <a href="{{ route('admin.carte') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Carte
                </a>
                    Productions
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 overflow-y-auto">
            <!-- System Selector -->
            <div class="mb-4 flex items-center gap-4">
                <label class="text-sm text-gray-400">Système:</label>
                <select id="system-selector" class="bg-gray-800 border border-gray-700 rounded px-3 py-1 text-sm text-white">
                    <option value="">-- Sélectionner un système --</option>
                    @foreach($systemes as $systeme)
                        <option value="{{ $systeme->id }}" {{ request('system_id') == $systeme->id ? 'selected' : '' }}>
                            {{ $systeme->nom }} ({{ $systeme->secteur_x }},{{ $systeme->secteur_y }},{{ $systeme->secteur_z }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if(isset($planetes) && $planetes->count() > 0)
                <h2 class="text-lg font-bold text-white mb-3">Planètes du système {{ $systeme_actuel->nom }}</h2>

                <!-- Planets Display - Condensed -->
                <div class="space-y-3">
                    @foreach($planetes as $planete)
                        <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3">
                            <!-- Planet Header - Compact -->
                            <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-700/50">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-sm font-bold text-yellow-400">{{ $planete->nom }}</h3>
                                    <span class="text-xs text-gray-500">{{ $planete->type }}</span>
                                    <span class="text-xs text-gray-600">
                                        ({{ number_format($planete->rayon, 2) }}R, {{ number_format($planete->masse, 2) }}M)
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $planete->gisements->count() }} gisements
                                </div>
                            </div>

                            @if($planete->gisements->count() > 0)
                                <!-- Gisements Table - Ultra Condensed -->
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="text-gray-500 border-b border-gray-700/50">
                                                <th class="text-left py-1 px-2">Ressource</th>
                                                <th class="text-left py-1 px-2">Richesse</th>
                                                <th class="text-left py-1 px-2">Quantité Totale</th>
                                                <th class="text-left py-1 px-2">Quantité Restante</th>
                                                <th class="text-left py-1 px-2">Position</th>
                                                <th class="text-left py-1 px-2">Statut</th>
                                                <th class="text-right py-1 px-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-700/30">
                                            @foreach($planete->gisements as $gisement)
                                                <tr class="hover:bg-gray-700/20" data-gisement-id="{{ $gisement->id }}">
                                                    <!-- Ressource Type - Editable -->
                                                    <td class="py-1 px-2">
                                                        <div class="flex items-center gap-1">
                                                            <select class="ressource-select bg-gray-900/50 border border-gray-700 rounded px-2 py-0.5 text-xs text-white w-32"
                                                                    data-field="ressource_id">
                                                                @foreach($ressources as $ressource)
                                                                    <option value="{{ $ressource->id }}"
                                                                            {{ $gisement->ressource_id == $ressource->id ? 'selected' : '' }}>
                                                                        {{ $ressource->nom }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <!-- Richesse - Editable -->
                                                    <td class="py-1 px-2">
                                                        <div class="flex items-center gap-1">
                                                            <input type="number"
                                                                   class="richesse-input bg-gray-900/50 border border-gray-700 rounded px-2 py-0.5 text-xs text-cyan-400 w-16"
                                                                   data-field="richesse"
                                                                   value="{{ $gisement->richesse }}"
                                                                   min="1" max="100">
                                                            <span class="text-gray-600">%</span>
                                                            <button class="recalc-btn text-blue-400 hover:text-blue-300 px-1"
                                                                    data-field="richesse"
                                                                    title="Recalculer richesse">
                                                                ⟲
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <!-- Quantité Totale - Editable -->
                                                    <td class="py-1 px-2">
                                                        <div class="flex items-center gap-1">
                                                            <input type="number"
                                                                   class="qty-total-input bg-gray-900/50 border border-gray-700 rounded px-2 py-0.5 text-xs text-green-400 w-24"
                                                                   data-field="quantite_totale"
                                                                   value="{{ $gisement->quantite_totale }}">
                                                            <button class="recalc-btn text-blue-400 hover:text-blue-300 px-1"
                                                                    data-field="quantite_totale"
                                                                    title="Recalculer quantité totale">
                                                                ⟲
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <!-- Quantité Restante - Editable -->
                                                    <td class="py-1 px-2">
                                                        <div class="flex items-center gap-1">
                                                            <input type="number"
                                                                   class="qty-remain-input bg-gray-900/50 border border-gray-700 rounded px-2 py-0.5 text-xs text-yellow-400 w-24"
                                                                   data-field="quantite_restante"
                                                                   value="{{ $gisement->quantite_restante }}">
                                                            <button class="recalc-btn text-blue-400 hover:text-blue-300 px-1"
                                                                    data-field="quantite_restante"
                                                                    title="Recalculer quantité restante">
                                                                ⟲
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <!-- Position -->
                                                    <td class="py-1 px-2 text-gray-400">
                                                        {{ number_format($gisement->latitude, 2) }},
                                                        {{ number_format($gisement->longitude, 2) }}
                                                    </td>

                                                    <!-- Statut -->
                                                    <td class="py-1 px-2">
                                                        @if($gisement->en_exploitation)
                                                            <span class="text-green-400">Exploité</span>
                                                        @elseif($gisement->decouvert)
                                                            <span class="text-yellow-400">Découvert</span>
                                                        @else
                                                            <span class="text-gray-600">Non découvert</span>
                                                        @endif
                                                    </td>

                                                    <!-- Save Button -->
                                                    <td class="py-1 px-2 text-right">
                                                        <button class="save-btn bg-green-600/80 hover:bg-green-600 text-white px-2 py-0.5 rounded text-xs"
                                                                data-gisement-id="{{ $gisement->id }}">
                                                            Sauvegarder
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-xs text-gray-500 py-2">Aucun gisement sur cette planète</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 text-center">
                    <p class="text-gray-400">Sélectionnez un système pour afficher les planètes et leurs productions.</p>
                </div>
            @endif
        </main>
    </div>
</div>

<!-- JavaScript for AJAX Updates -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // System selector change
    const systemSelector = document.getElementById('system-selector');
    if (systemSelector) {
        systemSelector.addEventListener('change', function() {
            if (this.value) {
                window.location.href = '{{ route("admin.production") }}?system_id=' + this.value;
            } else {
                window.location.href = '{{ route("admin.production") }}';
            }
        });
    }

    // Recalculate buttons
    document.querySelectorAll('.recalc-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const field = this.dataset.field;
            const gisementId = row.dataset.gisementId;

            // Recalculate logic based on field type
            if (field === 'richesse') {
                // Random richesse between 20-100
                const newValue = Math.floor(Math.random() * 81) + 20;
                row.querySelector(`[data-field="${field}"]`).value = newValue;
            } else if (field === 'quantite_totale') {
                // Recalculate based on ressource rarity
                const ressourceSelect = row.querySelector('.ressource-select');
                const selectedOption = ressourceSelect.options[ressourceSelect.selectedIndex];
                // Random quantity - could be enhanced with rarity logic
                const newValue = Math.floor(Math.random() * 15000000) + 1000000;
                row.querySelector(`[data-field="${field}"]`).value = newValue;
            } else if (field === 'quantite_restante') {
                // Set to total quantity
                const totalQty = row.querySelector('[data-field="quantite_totale"]').value;
                row.querySelector(`[data-field="${field}"]`).value = totalQty;
            }
        });
    });

    // Save buttons
    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const gisementId = this.dataset.gisementId;

            // Collect data
            const data = {
                ressource_id: row.querySelector('.ressource-select').value,
                richesse: row.querySelector('.richesse-input').value,
                quantite_totale: row.querySelector('.qty-total-input').value,
                quantite_restante: row.querySelector('.qty-remain-input').value,
                _token: '{{ csrf_token() }}'
            };

            // Save via AJAX
            fetch(`/admin/production/gisement/${gisementId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Visual feedback
                    this.textContent = '✓ Sauvegardé';
                    this.classList.remove('bg-green-600/80', 'hover:bg-green-600');
                    this.classList.add('bg-gray-600');
                    setTimeout(() => {
                        this.textContent = 'Sauvegarder';
                        this.classList.remove('bg-gray-600');
                        this.classList.add('bg-green-600/80', 'hover:bg-green-600');
                    }, 2000);
                } else {
                    alert('Erreur lors de la sauvegarde: ' + (result.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur de connexion lors de la sauvegarde');
            });
        });
    });
});
</script>
@endsection
