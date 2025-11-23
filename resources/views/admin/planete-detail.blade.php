@extends('layouts.app')

@section('title', 'Admin - Détails Planète')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">PLANÈTE: {{ $planete->nom }}</h1>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('admin.univers.show', $planete->systeme_stellaire_id) }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
                ← Retour au système {{ $planete->systemeStellaire->nom }}
            </a>
            <a href="{{ route('admin.planetes') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
                Liste des planètes
            </a>
        </div>
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
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Planètes
                </a>
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Productions
                </a>
                <a href="{{ route('admin.mines') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
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

            <!-- Formulaire d'édition des informations de la planète -->
            <form method="POST" action="{{ route('admin.planetes.update', $planete->id) }}">
                @csrf

                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white">Informations de la planète</h2>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-bold">
                            💾 Sauvegarder les modifications
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Nom -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Nom</label>
                            <input type="text" name="nom" value="{{ $planete->nom }}" required
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Type</label>
                            <select name="type" required
                                    class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                                <option value="terrestre" {{ $planete->type == 'terrestre' ? 'selected' : '' }}>Terrestre</option>
                                <option value="tellurique" {{ $planete->type == 'tellurique' ? 'selected' : '' }}>Tellurique</option>
                                <option value="gazeuse" {{ $planete->type == 'gazeuse' ? 'selected' : '' }}>Gazeuse</option>
                                <option value="glacee" {{ $planete->type == 'glacee' ? 'selected' : '' }}>Glacée</option>
                                <option value="oceanique" {{ $planete->type == 'oceanique' ? 'selected' : '' }}>Océanique</option>
                                <option value="desertique" {{ $planete->type == 'desertique' ? 'selected' : '' }}>Désertique</option>
                                <option value="volcanique" {{ $planete->type == 'volcanique' ? 'selected' : '' }}>Volcanique</option>
                            </select>
                        </div>

                        <!-- Distance à l'étoile -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Distance à l'étoile (UA)</label>
                            <input type="number" name="distance_etoile" value="{{ $planete->distance_etoile }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Rayon -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Rayon (R⊕)</label>
                            <input type="number" name="rayon" value="{{ $planete->rayon }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Masse -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Masse (M⊕)</label>
                            <input type="number" name="masse" value="{{ $planete->masse }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Gravité -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Gravité (g)</label>
                            <input type="number" name="gravite" value="{{ $planete->gravite }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Température moyenne -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Température moyenne (°C)</label>
                            <input type="number" name="temperature_moyenne" value="{{ $planete->temperature_moyenne }}" step="1"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Période orbitale -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Période orbitale (jours)</label>
                            <input type="number" name="periode_orbitale" value="{{ $planete->periode_orbitale }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Période de rotation -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Période de rotation (heures)</label>
                            <input type="number" name="periode_rotation" value="{{ $planete->periode_rotation }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Atmosphère -->
                        <div class="col-span-3">
                            <label class="text-xs text-gray-400 mb-1 block flex items-center gap-2">
                                <input type="checkbox" name="a_atmosphere" value="1" {{ $planete->a_atmosphere ? 'checked' : '' }}
                                       class="bg-gray-900 border border-gray-600 rounded">
                                Possède une atmosphère
                            </label>
                        </div>

                        <!-- Composition atmosphère -->
                        <div class="col-span-3">
                            <label class="text-xs text-gray-400 mb-1 block">Composition atmosphère</label>
                            <input type="text" name="composition_atmosphere" value="{{ $planete->composition_atmosphere }}"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Pression atmosphérique -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Pression atmosphérique (atm)</label>
                            <input type="number" name="pression_atmospherique" value="{{ $planete->pression_atmospherique }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Taux oxygène -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Taux oxygène (%)</label>
                            <input type="number" name="taux_oxygene" value="{{ $planete->taux_oxygene }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Toxicité atmosphère -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Toxicité atmosphère (%)</label>
                            <input type="number" name="toxicite_atmosphere" value="{{ $planete->toxicite_atmosphere }}" step="0.01"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Drapeaux booléens -->
                        <div class="col-span-3">
                            <div class="grid grid-cols-3 gap-4">
                                <label class="text-xs text-gray-400 flex items-center gap-2">
                                    <input type="checkbox" name="habitable" value="1" {{ $planete->habitable ? 'checked' : '' }}
                                           class="bg-gray-900 border border-gray-600 rounded">
                                    Habitable
                                </label>
                                <label class="text-xs text-gray-400 flex items-center gap-2">
                                    <input type="checkbox" name="habitee" value="1" {{ $planete->habitee ? 'checked' : '' }}
                                           class="bg-gray-900 border border-gray-600 rounded">
                                    Habitée
                                </label>
                                <label class="text-xs text-gray-400 flex items-center gap-2">
                                    <input type="checkbox" name="accessible" value="1" {{ $planete->accessible ? 'checked' : '' }}
                                           class="bg-gray-900 border border-gray-600 rounded">
                                    Accessible
                                </label>
                            </div>
                        </div>

                        <!-- Population -->
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Population</label>
                            <input type="number" name="population" value="{{ $planete->population }}" step="1"
                                   class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        </div>

                        <!-- Description -->
                        <div class="col-span-3">
                            <label class="text-xs text-gray-400 mb-1 block">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">{{ $planete->description }}</textarea>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Gisements de la planète -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-6">
                @php
                    // Accéder à la relation via getRelation pour éviter conflit avec attribut
                    try {
                        $gisementsRelation = $planete->getRelation('gisements');
                    } catch (\Exception $e) {
                        $gisementsRelation = collect();
                    }
                    if (!$gisementsRelation) {
                        $gisementsRelation = collect();
                    }
                @endphp

                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">
                        Gisements ({{ $gisementsRelation->count() }})
                    </h2>
                    <button onclick="creerGisement()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-bold">
                        ➕ Créer un gisement
                    </button>
                </div>

                @if($gisementsRelation->count() > 0)
                <div class="mt-1">
                    <table class="w-full text-xs">
                        <thead class="text-gray-500 border-b border-gray-700/50">
                            <tr>
                                <th class="text-left py-2 px-2">Ressource</th>
                                <th class="text-left py-2 px-2">Richesse</th>
                                <th class="text-left py-2 px-2">Qté Totale</th>
                                <th class="text-left py-2 px-2">Qté Restante</th>
                                <th class="text-left py-2 px-2">Mines</th>
                                <th class="text-right py-2 px-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/30">
                            @foreach($gisementsRelation as $gisement)
                            <tr class="hover:bg-gray-700/20" data-gisement-id="{{ $gisement->id }}">
                                <!-- Type ressource -->
                                <td class="py-2 px-2">
                                    <select class="ressource-select bg-gray-900/50 border border-gray-700 rounded px-2 py-1 text-xs text-white w-32"
                                            data-field="ressource_id"
                                            data-gisement-id="{{ $gisement->id }}">
                                        @foreach(\App\Models\Ressource::orderBy('nom')->get() as $ressource)
                                            <option value="{{ $ressource->id }}"
                                                    {{ $gisement->ressource_id == $ressource->id ? 'selected' : '' }}>
                                                {{ $ressource->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Richesse -->
                                <td class="py-2 px-2">
                                    <div class="flex items-center gap-1">
                                        <input type="number"
                                               class="richesse-input bg-gray-900/50 border border-gray-700 rounded px-2 py-1 text-xs text-cyan-400 w-16"
                                               data-field="richesse"
                                               data-gisement-id="{{ $gisement->id }}"
                                               value="{{ $gisement->richesse }}"
                                               min="1" max="100">
                                        <span class="text-gray-600 text-xs">%</span>
                                        <button class="recalc-btn text-blue-400 hover:text-blue-300 px-1 text-xs"
                                                data-field="richesse"
                                                data-gisement-id="{{ $gisement->id }}"
                                                title="Recalculer richesse">⟲</button>
                                    </div>
                                </td>

                                <!-- Quantité totale -->
                                <td class="py-2 px-2">
                                    <div class="flex items-center gap-1">
                                        <input type="number"
                                               class="qty-total-input bg-gray-900/50 border border-gray-700 rounded px-2 py-1 text-xs text-green-400 w-24"
                                               data-field="quantite_totale"
                                               data-gisement-id="{{ $gisement->id }}"
                                               value="{{ $gisement->quantite_totale }}">
                                        <button class="recalc-btn text-blue-400 hover:text-blue-300 px-1 text-xs"
                                                data-field="quantite_totale"
                                                data-gisement-id="{{ $gisement->id }}"
                                                title="Recalculer quantité totale">⟲</button>
                                    </div>
                                </td>

                                <!-- Quantité restante -->
                                <td class="py-2 px-2">
                                    <div class="flex items-center gap-1">
                                        <input type="number"
                                               class="qty-remain-input bg-gray-900/50 border border-gray-700 rounded px-2 py-1 text-xs text-yellow-400 w-24"
                                               data-field="quantite_restante"
                                               data-gisement-id="{{ $gisement->id }}"
                                               value="{{ $gisement->quantite_restante }}">
                                        <button class="recalc-btn text-blue-400 hover:text-blue-300 px-1 text-xs"
                                                data-field="quantite_restante"
                                                data-gisement-id="{{ $gisement->id }}"
                                                title="Recalculer quantité restante">⟲</button>
                                    </div>
                                </td>

                                <!-- Nombre de mines -->
                                <td class="py-2 px-2">
                                    @php
                                        try {
                                            $minesRelation = $gisement->getRelation('mines');
                                        } catch (\Exception $e) {
                                            $minesRelation = collect();
                                        }
                                        if (!$minesRelation) {
                                            $minesRelation = collect();
                                        }
                                    @endphp
                                    <span class="text-gray-400 text-xs">{{ $minesRelation->count() }} mine(s)</span>
                                </td>

                                <!-- Boutons actions -->
                                <td class="py-2 px-2 text-right">
                                    <div class="flex gap-1 justify-end">
                                        <button class="save-gisement-btn bg-green-600/80 hover:bg-green-600 text-white px-3 py-1 rounded text-xs"
                                                data-gisement-id="{{ $gisement->id }}">
                                            💾 Sauvegarder
                                        </button>
                                        <button onclick="creerMine({{ $gisement->id }}, '{{ $gisement->ressource->nom }}')"
                                                class="bg-blue-600/80 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                            ⛏️ Créer mine
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="text-center text-gray-400 py-4 text-sm">
                        Aucun gisement sur cette planète
                    </div>
                @endif
            </div>

            <!-- Mines d'exploitation (MAME) -->
            @php
                // Accéder à la relation mines
                try {
                    $minesRelation = $planete->getRelation('mines');
                } catch (\Exception $e) {
                    $minesRelation = collect();
                }
                if (!$minesRelation) {
                    $minesRelation = collect();
                }
            @endphp
            @if($minesRelation->count() > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">
                    Mines d'exploitation - MAME ({{ $minesRelation->count() }})
                </h2>

                <div class="space-y-2">
                    @foreach($minesRelation as $mine)
                    <div class="bg-gray-900/50 border border-gray-600 rounded p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-cyan-400 font-bold">{{ $mine->nom }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <span>Gisement: <span class="text-yellow-400">{{ $mine->gisement->ressource->nom }}</span></span>
                                    <span class="mx-2">|</span>
                                    <span>Emplacement: {{ ucfirst($mine->emplacement) }}</span>
                                    <span class="mx-2">|</span>
                                    <span>Statut: <span class="
                                        @if($mine->statut == 'active') text-green-400
                                        @elseif($mine->statut == 'inactive') text-gray-400
                                        @elseif($mine->statut == 'maintenance') text-orange-400
                                        @else text-red-400
                                        @endif
                                    ">{{ ucfirst($mine->statut) }}</span></span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span>Extraction: {{ $mine->taux_extraction }} u/jour</span>
                                    <span class="mx-2">|</span>
                                    <span>Stock: {{ number_format($mine->stock_actuel) }} / {{ number_format($mine->capacite_stockage) }}</span>
                                    <span class="mx-2">|</span>
                                    <span>Usure: {{ $mine->niveau_usure }}%</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="ravitaillerMine({{ $mine->id }})"
                                        class="bg-yellow-600/80 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                    ⚡ Ravitailler
                                </button>
                                <button onclick="maintenanceMine({{ $mine->id }})"
                                        class="bg-orange-600/80 hover:bg-orange-600 text-white px-3 py-1 rounded text-xs">
                                    🔧 Maintenance
                                </button>
                                <button onclick="supprimerMine({{ $mine->id }}, '{{ $mine->nom }}')"
                                        class="bg-red-600/80 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    🗑️ Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Stations orbitales -->
            @php
                // Accéder à la relation via getRelation pour éviter conflit avec attribut
                try {
                    $stationsRelation = $planete->getRelation('stations');
                } catch (\Exception $e) {
                    $stationsRelation = collect();
                }
                if (!$stationsRelation) {
                    $stationsRelation = collect();
                }
            @endphp
            @if($stationsRelation->count() > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h2 class="text-xl font-bold text-white mb-4">
                    Stations orbitales ({{ $stationsRelation->count() }})
                </h2>

                <div class="space-y-2">
                    @foreach($stationsRelation as $station)
                    <div class="bg-gray-900/50 border border-gray-600 rounded p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-cyan-400 font-bold">{{ $station->nom }}</div>
                                <div class="text-xs text-gray-400">Type: {{ $station->type }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </main>
    </div>
</div>

<!-- JavaScript pour édition gisements et mines -->
<script>
// Créer un nouveau gisement
function creerGisement() {
    const planeteId = {{ $planete->id }};
    const ressources = @json($ressources);

    // Créer dialogue HTML pour saisir les infos
    const html = `
        <div id="dialog-gisement" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
            <div class="bg-gray-800 border border-gray-600 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Créer un gisement</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Ressource</label>
                        <select id="gisement-ressource" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                            ${ressources.map(r => `<option value="${r.id}">${r.nom}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Richesse (%)</label>
                        <input type="number" id="gisement-richesse" value="${Math.floor(Math.random() * 81) + 20}" min="1" max="100"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Quantité totale</label>
                        <input type="number" id="gisement-quantite" value="${Math.floor(Math.random() * 15000000) + 1000000}"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button onclick="submitCreerGisement()" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold">
                        Créer
                    </button>
                    <button onclick="document.getElementById('dialog-gisement').remove()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

function submitCreerGisement() {
    const planeteId = {{ $planete->id }};
    const ressourceId = document.getElementById('gisement-ressource').value;
    const richesse = document.getElementById('gisement-richesse').value;
    const quantite = document.getElementById('gisement-quantite').value;

    fetch('{{ route('admin.production.gisement.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            planete_id: planeteId,
            ressource_id: ressourceId,
            richesse: richesse,
            quantite_totale: quantite
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

// Créer une mine sur un gisement
function creerMine(gisementId, ressourceNom) {
    const planeteId = {{ $planete->id }};
    const planeteNom = '{{ $planete->nom }}';

    const html = `
        <div id="dialog-mine" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
            <div class="bg-gray-800 border border-gray-600 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Créer une mine (MAME)</h3>
                <p class="text-sm text-gray-400 mb-4">Gisement de <span class="text-yellow-400">${ressourceNom}</span></p>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Nom de la mine</label>
                        <input type="text" id="mine-nom" value="MAME-${ressourceNom}-${planeteNom}-${Math.floor(Math.random() * 1000)}"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Emplacement</label>
                        <select id="mine-emplacement" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                            <option value="surface">Surface</option>
                            <option value="orbite">Orbite</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Taux d'extraction (u/jour)</label>
                        <input type="number" id="mine-taux" value="100" min="1" step="0.01"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Capacité de stockage</label>
                        <input type="number" id="mine-capacite" value="10000" min="100"
                               class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button onclick="submitCreerMine(${gisementId})" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-bold">
                        Créer
                    </button>
                    <button onclick="document.getElementById('dialog-mine').remove()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

function submitCreerMine(gisementId) {
    const planeteId = {{ $planete->id }};
    const nom = document.getElementById('mine-nom').value;
    const emplacement = document.getElementById('mine-emplacement').value;
    const taux = document.getElementById('mine-taux').value;
    const capacite = document.getElementById('mine-capacite').value;

    fetch('{{ route('admin.mines.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            nom: nom,
            planete_id: planeteId,
            gisement_id: gisementId,
            emplacement: emplacement,
            taux_extraction: taux,
            capacite_stockage: capacite
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

document.addEventListener('DOMContentLoaded', function() {
    // Boutons recalculer
    document.querySelectorAll('.recalc-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const gisementId = this.dataset.gisementId;
            const field = this.dataset.field;
            const row = this.closest('tr');

            if (field === 'richesse') {
                // Richesse aléatoire 20-100
                const newValue = Math.floor(Math.random() * 81) + 20;
                row.querySelector(`[data-field="${field}"][data-gisement-id="${gisementId}"]`).value = newValue;
            } else if (field === 'quantite_totale') {
                // Quantité aléatoire basée sur rareté
                const newValue = Math.floor(Math.random() * 15000000) + 1000000;
                row.querySelector(`[data-field="${field}"][data-gisement-id="${gisementId}"]`).value = newValue;
            } else if (field === 'quantite_restante') {
                // Copier la quantité totale
                const totalQty = row.querySelector(`[data-field="quantite_totale"][data-gisement-id="${gisementId}"]`).value;
                row.querySelector(`[data-field="${field}"][data-gisement-id="${gisementId}"]`).value = totalQty;
            }
        });
    });

    // Boutons sauvegarder
    document.querySelectorAll('.save-gisement-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const gisementId = this.dataset.gisementId;
            const row = this.closest('tr');

            // Collecter les données
            const data = {
                ressource_id: row.querySelector(`[data-field="ressource_id"][data-gisement-id="${gisementId}"]`).value,
                richesse: row.querySelector(`[data-field="richesse"][data-gisement-id="${gisementId}"]`).value,
                quantite_totale: row.querySelector(`[data-field="quantite_totale"][data-gisement-id="${gisementId}"]`).value,
                quantite_restante: row.querySelector(`[data-field="quantite_restante"][data-gisement-id="${gisementId}"]`).value,
                _token: '{{ csrf_token() }}'
            };

            // Sauvegarder via AJAX
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
                    // Feedback visuel
                    this.textContent = '✓ Sauvegardé';
                    this.classList.remove('bg-green-600/80', 'hover:bg-green-600');
                    this.classList.add('bg-gray-600');
                    setTimeout(() => {
                        this.textContent = '💾 Sauvegarder';
                        this.classList.remove('bg-gray-600');
                        this.classList.add('bg-green-600/80', 'hover:bg-green-600');
                    }, 2000);
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur de connexion');
            });
        });
    });
});
</script>
@endsection
