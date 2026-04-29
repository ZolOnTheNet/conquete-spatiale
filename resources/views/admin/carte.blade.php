@extends('layouts.admin')

@section('title', 'Admin - Carte de l\'Univers')

@section('admin-title', 'CARTE DE L\'UNIVERS')

@section('admin-content')
<style>
/* Tooltip personnalisé pour les systèmes stellaires */
.system-cell {
    position: relative;
}

.system-cell:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.95);
    color: #fbbf24;
    padding: 4px 8px;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 11px;
    z-index: 1000;
    pointer-events: none;
    border: 1px solid #fbbf24;
    margin-bottom: 2px;
}

.system-cell:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: #fbbf24;
    z-index: 1000;
    pointer-events: none;
}

/* Affichage des coordonnées survolées */
#coord-hover-display {
    color: #fbbf24;
    white-space: nowrap;
}

/* Étiquettes d'échelle */
.scale-label {
    font-size: 9px;
    color: #6b7280;
    font-weight: bold;
}

/* Canvas avec curseur crosshair */
#carte-canvas {
    cursor: crosshair;
}

/* Désactiver la sélection de texte sur les cartes */
#carte-text-view, #carte-graphic-view {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
</style>


            <!-- Contrôles de navigation -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3 mb-4">
                <div class="flex items-center gap-4 mb-2">
                    <!-- Coordonnées -->
                    <div class="flex items-center gap-2">
                        <label class="text-gray-400 text-sm">Coordonnées:</label>
                        <input type="number" id="coord-x" value="{{ $centerX }}" class="w-20 bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        <input type="number" id="coord-y" value="{{ $centerY }}" class="w-20 bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        <input type="number" id="coord-z" value="{{ $centerZ }}" class="w-20 bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        <button onclick="navigateToCoords()" class="bg-cyan-600 hover:bg-cyan-700 text-white px-3 py-1 rounded text-sm">
                            Aller
                        </button>

                        <!-- Navigation rapide -->
                        <div class="flex items-center gap-1 ml-2 text-xs">
                            @if($positionActuelle)
                            <a href="{{ route('admin.carte', ['x' => $positionActuelle['x'], 'y' => $positionActuelle['y'], 'z' => $positionActuelle['z'], 'plan' => $plan]) }}"
                               class="text-green-400 hover:text-green-300 underline">
                                Position actuelle
                            </a>
                            <span class="text-gray-600">|</span>
                            @endif
                            <a href="{{ route('admin.carte', ['x' => 0, 'y' => 0, 'z' => 0, 'plan' => $plan]) }}"
                               class="text-yellow-400 hover:text-yellow-300 underline">
                                Sol
                            </a>
                        </div>
                    </div>

                    <!-- Type de vue -->
                    <div class="flex items-center gap-2">
                        <label class="text-gray-400 text-sm">Vue:</label>
                        <button onclick="toggleViewMode('text')" id="btn-view-text" class="px-3 py-1 rounded text-sm bg-yellow-600 text-white">
                            Texte
                        </button>
                        <button onclick="toggleViewMode('graphic')" id="btn-view-graphic" class="px-3 py-1 rounded text-sm bg-gray-700 text-gray-300 hover:bg-gray-600">
                            Graphique
                        </button>
                    </div>

                    <!-- Sélection du plan -->
                    <div class="flex items-center gap-2 ml-auto">
                        <label class="text-gray-400 text-sm">Plan:</label>
                        <button onclick="changePlan('X')" class="plan-btn px-3 py-1 rounded text-sm {{ $plan === 'X' ? 'bg-yellow-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            X
                        </button>
                        <button onclick="changePlan('Y')" class="plan-btn px-3 py-1 rounded text-sm {{ $plan === 'Y' ? 'bg-yellow-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            Y
                        </button>
                        <button onclick="changePlan('Z')" class="plan-btn px-3 py-1 rounded text-sm {{ $plan === 'Z' ? 'bg-yellow-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            Z
                        </button>
                    </div>
                </div>

                <!-- Légende -->
                <div class="flex items-center gap-4 text-xs">
                    <div class="text-white"><span class="text-yellow-400 font-bold">*</span> = Système stellaire</div>
                    <div class="text-white"><span class="text-red-500 font-bold">::</span> = Obstacles</div>
                    <div class="text-cyan-300">Cliquez sur une étoile pour voir les détails du secteur →</div>
                </div>
            </div>

            <!-- Two Maps Side by Side -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Carte Niveau 1: Vue Secteurs (100 AL × 100 AL) -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3">
                    <h2 class="text-lg font-bold text-cyan-400 mb-2">Carte Univers (100 AL) <span class="text-xs text-gray-500">({{ count($grille) > 0 ? array_sum(array_map(fn($x) => count($x), array_map(fn($x) => array_merge(...array_values($x)), array_values($grille)))) : 0 }} systèmes)</span></h2>

                    <div class="bg-black border border-gray-700 rounded p-2 relative">
                        @php
                            // Déterminer les axes en fonction du plan
                            $halfSize = 50;

                            // Configuration des axes selon le plan choisi
                            if ($plan === 'Z') {
                                // Plan XY (Z fixe)
                                $hAxisLabel = 'X';
                                $vAxisLabel = 'Y';
                                $fixedAxis = 'Z';
                                $fixedValue = $centerZ;
                            } elseif ($plan === 'Y') {
                                // Plan XZ (Y fixe)
                                $hAxisLabel = 'X';
                                $vAxisLabel = 'Z';
                                $fixedAxis = 'Y';
                                $fixedValue = $centerY;
                            } else {
                                // Plan YZ (X fixe)
                                $hAxisLabel = 'Y';
                                $vAxisLabel = 'Z';
                                $fixedAxis = 'X';
                                $fixedValue = $centerX;
                            }
                        @endphp

                        <!-- Étiquettes des axes et coordonnées survolées -->
                        <div class="text-xs text-gray-500 mb-1 flex items-center justify-between">
                            <div>
                                <span class="text-cyan-400">Centrée sur:</span> X:{{ $centerX }} Y:{{ $centerY }} Z:{{ $centerZ }} |
                                {{ $hAxisLabel }} (horizontal) / {{ $vAxisLabel }} (vertical) | {{ $fixedAxis }} = {{ $fixedValue }} AL
                            </div>
                            <div id="coord-hover-display" class="text-xs text-yellow-400">
                                Survolez la carte
                            </div>
                        </div>

                        <!-- VUE TEXTE -->
                        <div id="carte-text-view" class="relative bg-black border border-gray-700 rounded p-2">
                            <!-- Grille proprement dite -->
                            <div class="font-mono text-sm leading-tight relative" style="letter-spacing: 0.05em;">
                                @for($v = $halfSize - 1; $v >= -$halfSize; $v--)
                                <div class="flex">
                                    @for($h = -$halfSize; $h < $halfSize; $h++)
                                        @php
                                            // Calculer les coordonnées absolues selon le plan
                                            if ($plan === 'Z') {
                                                $absX = $centerX + $h;
                                                $absY = $centerY + $v;
                                                $absZ = $centerZ;
                                            } elseif ($plan === 'Y') {
                                                $absX = $centerX + $h;
                                                $absY = $centerY;
                                                $absZ = $centerZ + $v;
                                            } else {
                                                $absX = $centerX;
                                                $absY = $centerY + $h;
                                                $absZ = $centerZ + $v;
                                            }

                                            // Les coordonnées AL entières SONT les secteurs (pas de division !)
                                            // La grille est indexée par secteur_x/y/z qui sont les coordonnées AL
                                            $secteurX = $absX;
                                            $secteurY = $absY;
                                            $secteurZ = $absZ;

                                            // Chercher un système à ces coordonnées AL
                                            $hasSystem = isset($grille[$secteurX][$secteurY][$secteurZ]);

                                            if ($hasSystem) {
                                                $systeme = $grille[$secteurX][$secteurY][$secteurZ];
                                                // Coordonnées AL entières = directement secteur_x/y/z (pas de calcul)
                                                $sysAbsX = intval($systeme->secteur_x);
                                                $sysAbsY = intval($systeme->secteur_y);
                                                $sysAbsZ = intval($systeme->secteur_z);

                                                // Afficher * sur la cellule AL entière du système
                                                if ($absX == $sysAbsX && $absY == $sysAbsY && $absZ == $sysAbsZ) {
                                                    $cellContent = '*';
                                                    $cellClass = 'text-yellow-400 cursor-pointer hover:bg-yellow-900/30 system-cell';
                                                    $cellTitle = "{$systeme->nom} (X:{$sysAbsX} Y:{$sysAbsY} Z:{$sysAbsZ} P:{$systeme->puissance})";
                                                    $cellData = "data-secteur-x='{$secteurX}' data-secteur-y='{$secteurY}' data-secteur-z='{$secteurZ}' data-system-id='{$systeme->id}' data-is-system='true'";
                                                } else {
                                                    $cellContent = '·';
                                                    $cellClass = 'text-gray-900 cursor-pointer hover:bg-gray-800';
                                                    $cellTitle = "AL: {$absX}, {$absY}, {$absZ}";
                                                    $cellData = "data-is-system='false'";
                                                }
                                            } else {
                                                $cellContent = '·';
                                                $cellClass = 'text-gray-900 cursor-pointer hover:bg-gray-800';
                                                $cellTitle = "AL: {$absX}, {$absY}, {$absZ}";
                                                $cellData = "data-is-system='false'";
                                            }
                                        @endphp
                                        <span class="{{ $cellClass }}"
                                              @if($hasSystem && $absX == $sysAbsX && $absY == $sysAbsY && $absZ == $sysAbsZ)
                                              onclick="clickCellSimple({{ $absX }}, {{ $absY }}, {{ $absZ }}, this)"
                                              @endif
                                              ondblclick="clickCellDouble({{ $absX }}, {{ $absY }}, {{ $absZ }}, this)"
                                              onmouseover="updateCoordDisplay({{ $absX }}, {{ $absY }}, {{ $absZ }}, '{{ $cellContent }}')"
                                              title="{{ $cellTitle }}"
                                              data-coord-x="{{ $absX }}"
                                              data-coord-y="{{ $absY }}"
                                              data-coord-z="{{ $absZ }}"
                                              {!! $cellData !!}>{{ $cellContent }}</span>@endfor
                                </div>
                            @endfor
                            </div>
                        </div>

                        <!-- VUE GRAPHIQUE -->
                        <div id="carte-graphic-view" class="hidden relative">
                            <canvas id="carte-canvas" width="1000" height="1000" class="border border-gray-700 rounded"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Carte Niveau 2: Vue Secteur (10 AL × 10 AL) -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3">
                    <h2 class="text-lg font-bold text-yellow-400 mb-2">Panneau de Contrôle</h2>

                    <!-- Formulaire de création de système -->
                    <div id="creation-systeme" class="bg-gray-900/50 border border-cyan-500/30 rounded-lg p-4 mb-4">
                        <h3 class="text-cyan-400 font-bold mb-3">Créer un Système Solaire</h3>

                        <form action="{{ route('admin.systeme.creer') }}" method="POST" class="space-y-3">
                            @csrf

                            <div>
                                <label class="block text-xs text-gray-200 mb-1">Nom du système</label>
                                <input type="text" name="nom" required
                                       class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm"
                                       placeholder="Ex: Alpha Centauri B">
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-200 mb-1">X (AL)</label>
                                    <input type="number" step="1" name="coord_x" id="create-coord-x" required
                                           value="{{ $centerX }}"
                                           oninput="checkExistingSystem()"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-200 mb-1">Y (AL)</label>
                                    <input type="number" step="1" name="coord_y" id="create-coord-y" required
                                           value="{{ $centerY }}"
                                           oninput="checkExistingSystem()"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-200 mb-1">Z (AL)</label>
                                    <input type="number" step="1" name="coord_z" id="create-coord-z" required
                                           value="{{ $centerZ }}"
                                           oninput="checkExistingSystem()"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                            </div>

                            <div id="coord-warning" class="text-red-500 text-xs hidden">
                                ⚠️ Un système existe déjà à ces coordonnées !
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-200 mb-1">Type spectral</label>
                                    <select name="type_etoile" required
                                            class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                        <option value="O">O (Bleue, très chaude)</option>
                                        <option value="B">B (Bleue-blanche)</option>
                                        <option value="A">A (Blanche)</option>
                                        <option value="F">F (Blanche-jaune)</option>
                                        <option value="G" selected>G (Jaune, type Soleil)</option>
                                        <option value="K">K (Orange)</option>
                                        <option value="M">M (Rouge, froide)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-200 mb-1">Nb planètes</label>
                                    <input type="number" name="nb_planetes" min="0" max="20"
                                           value="5"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-bold">
                                    ✨ Créer Système
                                </button>
                                <button type="button" onclick="resetCreateForm()"
                                        class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm">
                                    Réinitialiser
                                </button>
                            </div>

                            <div class="text-xs text-gray-500">
                                Cliquez sur la carte de gauche pour sélectionner les coordonnées
                            </div>
                        </form>
                    </div>

                    <!-- Détail Secteur (remplacera le formulaire quand on clique sur une étoile) -->
                    <div id="secteur-detail" class="hidden">
                        <!-- Le contenu sera chargé via AJAX -->
                    </div>
                </div>
            </div>

<script>
// Grille des systèmes existants (passée depuis PHP)
const existingSystems = @json($grille);

// Vérifier si un système existe déjà aux coordonnées saisies
function checkExistingSystem() {
    const x = parseInt(document.getElementById('create-coord-x').value);
    const y = parseInt(document.getElementById('create-coord-y').value);
    const z = parseInt(document.getElementById('create-coord-z').value);

    // Les coordonnées saisies SONT déjà les coordonnées secteur (AL entières)
    // Pas de calcul nécessaire !
    const secteurX = x;
    const secteurY = y;
    const secteurZ = z;

    // Vérifier si un système existe dans ce secteur
    const warning = document.getElementById('coord-warning');
    const submitBtn = document.querySelector('button[type="submit"]');

    if (existingSystems[secteurX] &&
        existingSystems[secteurX][secteurY] &&
        existingSystems[secteurX][secteurY][secteurZ]) {

        const system = existingSystems[secteurX][secteurY][secteurZ];
        warning.innerHTML = `⚠️ Un système existe déjà à ces coordonnées : <strong>${system.nom}</strong> (X:${system.abs_x} Y:${system.abs_y} Z:${system.abs_z})`;
        warning.classList.remove('hidden');

        // Changer la couleur des inputs
        document.getElementById('create-coord-x').classList.add('border-red-500', 'text-red-400');
        document.getElementById('create-coord-y').classList.add('border-red-500', 'text-red-400');
        document.getElementById('create-coord-z').classList.add('border-red-500', 'text-red-400');

        // Désactiver le bouton de soumission
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        warning.classList.add('hidden');

        // Rétablir la couleur normale des inputs
        document.getElementById('create-coord-x').classList.remove('border-red-500', 'text-red-400');
        document.getElementById('create-coord-y').classList.remove('border-red-500', 'text-red-400');
        document.getElementById('create-coord-z').classList.remove('border-red-500', 'text-red-400');

        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// Mettre à jour l'affichage des coordonnées survolées
function updateCoordDisplay(x, y, z, cellContent) {
    const display = document.getElementById('coord-hover-display');
    let contentDesc = '';

    if (cellContent === '*') {
        contentDesc = '<span class="text-yellow-400">★ Système</span>';
    } else if (cellContent === '::') {
        contentDesc = '<span class="text-red-500">⚠ Obstacle</span>';
    } else {
        contentDesc = '<span class="text-gray-400">○ Vide</span>';
    }

    display.innerHTML = `X: ${x} Y: ${y} Z: ${z} | ${contentDesc}`;
}

// Navigation vers des coordonnées spécifiques
function navigateToCoords() {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    const plan = '{{ $plan }}';
    window.location.href = `/admin/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
}

// Changement de plan d'affichage
function changePlan(newPlan) {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    window.location.href = `/admin/carte?x=${x}&y=${y}&z=${z}&plan=${newPlan}`;
}

// Simple clic sur une cellule avec système (affiche uniquement les détails)
function clickCellSimple(x, y, z, element) {
    const isSystem = element.dataset.isSystem === 'true';

    if (isSystem) {
        // Charger le niveau 2 via AJAX sans recentrer
        const secteurX = element.dataset.secteurX;
        const secteurY = element.dataset.secteurY;
        const secteurZ = element.dataset.secteurZ;
        loadSecteurDetail(secteurX, secteurY, secteurZ);
    }
}

// Double-clic sur une cellule (centre la carte et affiche les détails si c'est un système)
function clickCellDouble(x, y, z, element) {
    const plan = '{{ $plan }}';
    const isSystem = element.dataset.isSystem === 'true';

    if (isSystem) {
        // Si c'est un système, charger le niveau 2 via AJAX et recentrer
        const secteurX = element.dataset.secteurX;
        const secteurY = element.dataset.secteurY;
        const secteurZ = element.dataset.secteurZ;
        loadSecteurDetail(secteurX, secteurY, secteurZ);
    } else {
        // Sinon, mettre à jour les coordonnées du formulaire de création
        document.getElementById('create-coord-x').value = x;
        document.getElementById('create-coord-y').value = y;
        document.getElementById('create-coord-z').value = z;

        // Vérifier si un système existe déjà
        checkExistingSystem();

        // Afficher le formulaire de création et masquer le détail secteur
        document.getElementById('creation-systeme').classList.remove('hidden');
        document.getElementById('secteur-detail').classList.add('hidden');

        // Flash visuel pour indiquer la sélection
        const form = document.getElementById('creation-systeme');
        form.classList.add('ring-2', 'ring-cyan-400');
        setTimeout(() => {
            form.classList.remove('ring-2', 'ring-cyan-400');
        }, 500);
    }

    // Dans tous les cas, recentrer la carte sur la cellule double-cliquée
    window.location.href = `/admin/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
}

// Charger les détails d'un secteur via AJAX
function loadSecteurDetail(x, y, z) {
    const detailDiv = document.getElementById('secteur-detail');
    const creationDiv = document.getElementById('creation-systeme');

    // Masquer le formulaire de création et afficher le détail secteur
    creationDiv.classList.add('hidden');
    detailDiv.classList.remove('hidden');
    detailDiv.innerHTML = '<div class="text-cyan-400 py-8">Chargement...</div>';

    fetch(`/admin/carte/secteur/${x}/${y}/${z}`)
        .then(response => response.text())
        .then(html => {
            // Créer un élément temporaire pour parser le HTML
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Extraire et supprimer tous les scripts
            const scripts = temp.querySelectorAll('script');
            const scriptContents = [];
            scripts.forEach(script => {
                scriptContents.push(script.textContent);
                script.remove();
            });

            // Injecter le HTML sans les scripts
            detailDiv.innerHTML = temp.innerHTML;

            // Exécuter les scripts dans l'ordre
            scriptContents.forEach(scriptContent => {
                try {
                    const scriptEl = document.createElement('script');
                    scriptEl.textContent = scriptContent;
                    document.body.appendChild(scriptEl);
                    // Nettoyer immédiatement
                    document.body.removeChild(scriptEl);
                } catch (e) {
                    console.error('Error executing script:', e);
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            detailDiv.innerHTML = '<div class="text-red-400 py-8">Erreur de chargement</div>';
        });
}

// Réinitialiser le formulaire de création
function resetCreateForm() {
    document.getElementById('create-coord-x').value = 0;
    document.getElementById('create-coord-y').value = 0;
    document.getElementById('create-coord-z').value = 0;
    document.querySelector('input[name="nom"]').value = '';
    document.querySelector('select[name="type_etoile"]').value = 'G';
    document.querySelector('input[name="nb_planetes"]').value = 5;
}

// Récupérer le mode de vue depuis l'URL ou localStorage
const urlParams = new URLSearchParams(window.location.search);
let currentViewMode = urlParams.get('mode') || localStorage.getItem('carteViewMode') || 'text';

// Appliquer le mode au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    if (currentViewMode === 'graphic') {
        toggleViewMode('graphic');
    }

    // Vérifier au chargement si les coordonnées du centre correspondent à un système existant
    checkExistingSystem();
});

function toggleViewMode(mode) {
    currentViewMode = mode;

    // Sauvegarder le mode dans localStorage
    localStorage.setItem('carteViewMode', mode);

    const textView = document.getElementById('carte-text-view');
    const graphicView = document.getElementById('carte-graphic-view');
    const btnText = document.getElementById('btn-view-text');
    const btnGraphic = document.getElementById('btn-view-graphic');
    const canvas = document.getElementById('carte-canvas');

    if (mode === 'text') {
        textView.classList.remove('hidden');
        graphicView.classList.add('hidden');
        btnText.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
        btnText.classList.add('bg-yellow-600', 'text-white');
        btnGraphic.classList.remove('bg-yellow-600', 'text-white');
        btnGraphic.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
    } else {
        textView.classList.add('hidden');
        graphicView.classList.remove('hidden');
        btnText.classList.remove('bg-yellow-600', 'text-white');
        btnText.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
        btnGraphic.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
        btnGraphic.classList.add('bg-yellow-600', 'text-white');

        // Attendre que le canvas soit visible avant de dessiner
        setTimeout(() => {
            // Dessiner la carte graphique
            drawGraphicMap();

            // Configurer les événements canvas (une seule fois)
            if (canvas && !canvas.dataset.eventsSetup) {
                setupCanvasEvents();
                canvas.dataset.eventsSetup = 'true';
            }
        }, 50);
    }
}

// Variables globales pour le canvas
let canvasImageCache = {};
let canvasLastClick = 0;
let canvasClickTimer = null;

// Dessiner la carte graphique avec canvas
function drawGraphicMap() {
    const canvas = document.getElementById('carte-canvas');
    const ctx = canvas.getContext('2d');

    // Configuration - Taille de la carte = 100x100 cellules
    const cellSize = 10; // pixels par cellule AL (agrandi de 6 à 10)
    const halfSize = 50;
    const plan = '{{ $plan }}';
    const centerX = {{ $centerX }};
    const centerY = {{ $centerY }};
    const centerZ = {{ $centerZ }};
    const gridSize = 100; // 100 AL

    // Ajuster la taille du canvas
    canvas.width = gridSize * cellSize;
    canvas.height = gridSize * cellSize;

    // Effacer le canvas
    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Dessiner le fond étoilé (points pseudo-aléatoires avec seed basé sur les coordonnées)
    ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';

    // Utiliser un générateur pseudo-aléatoire avec seed pour avoir toujours le même fond pour les mêmes coordonnées
    function seededRandom(seed) {
        const x = Math.sin(seed) * 10000;
        return x - Math.floor(x);
    }

    const seed = centerX * 1000 + centerY * 100 + centerZ * 10;
    for (let i = 0; i < 800; i++) {
        const x = Math.floor(seededRandom(seed + i * 2) * canvas.width);
        const y = Math.floor(seededRandom(seed + i * 2 + 1) * canvas.height);
        const size = seededRandom(seed + i * 3) > 0.9 ? 2 : 1; // Quelques étoiles plus grandes
        ctx.fillRect(x, y, size, size);
    }

    // Dessiner la grille tous les 10 AL
    ctx.strokeStyle = 'rgba(59, 130, 246, 0.15)';
    ctx.lineWidth = 1;
    for (let i = 0; i <= gridSize; i += 10) {
        // Lignes verticales
        ctx.beginPath();
        ctx.moveTo(i * cellSize, 0);
        ctx.lineTo(i * cellSize, canvas.height);
        ctx.stroke();

        // Lignes horizontales
        ctx.beginPath();
        ctx.moveTo(0, i * cellSize);
        ctx.lineTo(canvas.width, i * cellSize);
        ctx.stroke();
    }

    // Étiquettes d'échelle à l'intérieur du canvas
    ctx.fillStyle = '#6b7280';
    ctx.font = '9px monospace';

    // Étiquettes horizontales (en bas)
    ctx.textAlign = 'center';
    ctx.textBaseline = 'bottom';
    for (let i = 0; i <= gridSize; i += 10) {
        const coord = (plan === 'Z' ? centerX : (plan === 'Y' ? centerX : centerY)) + (i - halfSize);
        ctx.fillText(coord, i * cellSize, canvas.height - 2);
    }

    // Étiquettes verticales (à gauche)
    ctx.textAlign = 'left';
    ctx.textBaseline = 'top';
    for (let i = 0; i <= gridSize; i += 10) {
        const coord = (plan === 'Z' ? centerY : (plan === 'Y' ? centerZ : centerZ)) + (halfSize - i);
        ctx.fillText(coord, 2, i * cellSize + 2);
    }

    // Charger les images et dessiner les systèmes
    const imagesToLoad = ['bg_S.jpg', 'bg_SV.jpg', 'bg_A.jpg', 'bg_V.jpg'];
    let imagesLoaded = Object.keys(canvasImageCache).length;

    if (imagesLoaded === 0) {
        imagesToLoad.forEach(imgName => {
            const img = new Image();
            img.onload = () => {
                canvasImageCache[imgName] = img;
                imagesLoaded++;
                if (imagesLoaded === imagesToLoad.length) {
                    renderSystems();
                }
            };
            img.onerror = () => {
                console.error('Erreur chargement image:', imgName);
                imagesLoaded++;
                if (imagesLoaded === imagesToLoad.length) {
                    renderSystems();
                }
            };
            img.src = '/images/carte_icones/' + imgName;
        });
    } else {
        renderSystems();
    }

    function renderSystems() {
        // Parcourir tous les systèmes
        for (let v = halfSize - 1; v >= -halfSize; v--) {
            for (let h = -halfSize; h < halfSize; h++) {
                let absX, absY, absZ;

                if (plan === 'Z') {
                    absX = centerX + h;
                    absY = centerY + v;
                    absZ = centerZ;
                } else if (plan === 'Y') {
                    absX = centerX + h;
                    absY = centerY;
                    absZ = centerZ + v;
                } else {
                    absX = centerX;
                    absY = centerY + h;
                    absZ = centerZ + v;
                }

                // Les coordonnées AL entières SONT les secteurs (pas de division !)
                // La grille est indexée par secteur_x/y/z qui sont les coordonnées AL
                const secteurX = absX;
                const secteurY = absY;
                const secteurZ = absZ;

                if (existingSystems[secteurX] &&
                    existingSystems[secteurX][secteurY] &&
                    existingSystems[secteurX][secteurY][secteurZ]) {

                    const system = existingSystems[secteurX][secteurY][secteurZ];

                    // Les coordonnées AL entières sont directement secteur_x, secteur_y, secteur_z
                    // (pas de calcul, les positions décimales sont pour la navigation précise)
                    const sysAbsX = parseInt(system.secteur_x);
                    const sysAbsY = parseInt(system.secteur_y);
                    const sysAbsZ = parseInt(system.secteur_z);

                    // Comparer avec les coordonnées entières de la grille
                    if (absX === sysAbsX && absY === sysAbsY && absZ === sysAbsZ) {
                        // Dessiner le système au centre de sa cellule AL
                        const canvasX = (h + halfSize) * cellSize + cellSize / 2;
                        const canvasY = (halfSize - 1 - v) * cellSize + cellSize / 2;

                        // Choisir l'image selon le type de système
                        let imgName = 'bg_S.jpg';
                        if (system.planetes && system.planetes.length > 0) {
                            imgName = 'bg_SV.jpg';
                        }

                        // Taille minimum pour que tous les soleils soient visibles
                        const minSize = Math.max(cellSize, 4); // Au moins 4 pixels

                        if (canvasImageCache[imgName]) {
                            ctx.drawImage(canvasImageCache[imgName], canvasX - minSize/2, canvasY - minSize/2, minSize, minSize);
                        } else {
                            // Fallback: dessiner un cercle jaune (minimum 2 pixels de rayon)
                            ctx.fillStyle = '#fbbf24';
                            ctx.beginPath();
                            ctx.arc(canvasX, canvasY, Math.max(minSize/3, 2), 0, Math.PI * 2);
                            ctx.fill();
                        }
                    }
                }
            }
        }
    }
}

// Gestion des événements sur le canvas
function setupCanvasEvents() {
    const canvas = document.getElementById('carte-canvas');
    if (!canvas) return;

    const cellSize = 10; // Doit correspondre à drawGraphicMap
    const halfSize = 50;
    const plan = '{{ $plan }}';
    const centerX = {{ $centerX }};
    const centerY = {{ $centerY }};
    const centerZ = {{ $centerZ }};

    // Mousemove pour afficher les coordonnées
    canvas.addEventListener('mousemove', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const gridX = Math.floor(x / cellSize) - halfSize;
        const gridY = halfSize - 1 - Math.floor(y / cellSize);

        let absX, absY, absZ;
        if (plan === 'Z') {
            absX = centerX + gridX;
            absY = centerY + gridY;
            absZ = centerZ;
        } else if (plan === 'Y') {
            absX = centerX + gridX;
            absY = centerY;
            absZ = centerZ + gridY;
        } else {
            absX = centerX;
            absY = centerY + gridX;
            absZ = centerZ + gridY;
        }

        // absX/Y/Z SONT déjà les coordonnées secteur (AL entières)
        const secteurX = absX;
        const secteurY = absY;
        const secteurZ = absZ;

        let cellContent = '·';
        if (existingSystems[secteurX] &&
            existingSystems[secteurX][secteurY] &&
            existingSystems[secteurX][secteurY][secteurZ]) {
            const system = existingSystems[secteurX][secteurY][secteurZ];
            // Les coordonnées AL entières sont directement secteur_x/y/z
            const sysAbsX = parseInt(system.secteur_x);
            const sysAbsY = parseInt(system.secteur_y);
            const sysAbsZ = parseInt(system.secteur_z);
            if (absX === sysAbsX && absY === sysAbsY && absZ === sysAbsZ) {
                cellContent = '*';
            }
        }

        updateCoordDisplay(absX, absY, absZ, cellContent);
    });

    // Click et double-click
    canvas.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const gridX = Math.floor(x / cellSize) - halfSize;
        const gridY = halfSize - 1 - Math.floor(y / cellSize);

        let absX, absY, absZ;
        if (plan === 'Z') {
            absX = centerX + gridX;
            absY = centerY + gridY;
            absZ = centerZ;
        } else if (plan === 'Y') {
            absX = centerX + gridX;
            absY = centerY;
            absZ = centerZ + gridY;
        } else {
            absX = centerX;
            absY = centerY + gridX;
            absZ = centerZ + gridY;
        }

        // absX/Y/Z SONT déjà les coordonnées secteur (AL entières)
        const secteurX = absX;
        const secteurY = absY;
        const secteurZ = absZ;

        // Détection double-click manuel (pour éviter les conflits avec Deepl)
        const now = Date.now();
        const timeSinceLastClick = now - canvasLastClick;

        if (timeSinceLastClick < 400) {
            // Double-click détecté
            clearTimeout(canvasClickTimer);
            canvasLastClick = 0;
            handleCanvasDoubleClick(absX, absY, absZ, secteurX, secteurY, secteurZ);
        } else {
            // Premier click - attendre pour voir si double-click
            canvasLastClick = now;
            canvasClickTimer = setTimeout(() => {
                handleCanvasSingleClick(absX, absY, absZ, secteurX, secteurY, secteurZ);
            }, 400);
        }
    });
}

function handleCanvasSingleClick(absX, absY, absZ, secteurX, secteurY, secteurZ) {
    // Vérifier si c'est un système
    if (existingSystems[secteurX] &&
        existingSystems[secteurX][secteurY] &&
        existingSystems[secteurX][secteurY][secteurZ]) {

        const system = existingSystems[secteurX][secteurY][secteurZ];
        // Les coordonnées AL entières sont directement secteur_x/y/z
        const sysAbsX = parseInt(system.secteur_x);
        const sysAbsY = parseInt(system.secteur_y);
        const sysAbsZ = parseInt(system.secteur_z);

        if (absX === sysAbsX && absY === sysAbsY && absZ === sysAbsZ) {
            // Afficher les détails du système
            loadSecteurDetail(secteurX, secteurY, secteurZ);
        }
    }
}

function handleCanvasDoubleClick(absX, absY, absZ, secteurX, secteurY, secteurZ) {
    const plan = '{{ $plan }}';

    // Vérifier si c'est un système
    if (existingSystems[secteurX] &&
        existingSystems[secteurX][secteurY] &&
        existingSystems[secteurX][secteurY][secteurZ]) {

        const system = existingSystems[secteurX][secteurY][secteurZ];
        // Les coordonnées AL entières sont directement secteur_x/y/z
        const sysAbsX = parseInt(system.secteur_x);
        const sysAbsY = parseInt(system.secteur_y);
        const sysAbsZ = parseInt(system.secteur_z);

        if (absX === sysAbsX && absY === sysAbsY && absZ === sysAbsZ) {
            // Afficher les détails ET recentrer
            loadSecteurDetail(secteurX, secteurY, secteurZ);
        }
    }

    // Recentrer la carte
    window.location.href = `/admin/carte?x=${absX}&y=${absY}&z=${absZ}&plan=${plan}`;
}

// Support clavier pour navigation (Maj+Flèches)
document.addEventListener('keydown', function(e) {
    // Ne réagir que si Shift est pressé
    if (!e.shiftKey) return;

    const x = parseInt(document.getElementById('coord-x').value);
    const y = parseInt(document.getElementById('coord-y').value);
    const z = parseInt(document.getElementById('coord-z').value);
    const plan = '{{ $plan }}';

    let newX = x, newY = y, newZ = z;

    if (e.key === 'ArrowUp') {
        if (plan === 'Z') newY += 10;
        else if (plan === 'Y') newZ += 10;
        else newZ += 10;
    } else if (e.key === 'ArrowDown') {
        if (plan === 'Z') newY -= 10;
        else if (plan === 'Y') newZ -= 10;
        else newZ -= 10;
    } else if (e.key === 'ArrowLeft') {
        if (plan === 'Z') newX -= 10;
        else if (plan === 'Y') newX -= 10;
        else newY -= 10;
    } else if (e.key === 'ArrowRight') {
        if (plan === 'Z') newX += 10;
        else if (plan === 'Y') newX += 10;
        else newY += 10;
    } else {
        return;
    }

    e.preventDefault();
    window.location.href = `/admin/carte?x=${newX}&y=${newY}&z=${newZ}&plan=${plan}`;
});
</script>
@endsection
