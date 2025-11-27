@extends('layouts.app')

@section('title', 'Admin - Carte de l\'Univers')

@section('content')
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
</style>

<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4">
        <h1 class="text-2xl font-orbitron text-red-400">CARTE DE L'UNIVERS</h1>
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
                <a href="{{ route('admin.carte') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Carte
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content - Two Maps Side by Side -->
        <main class="flex-1 p-4 overflow-auto">
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
                                {{ $hAxisLabel }} (horizontal) / {{ $vAxisLabel }} (vertical) | {{ $fixedAxis }} = {{ $fixedValue }} AL
                            </div>
                            <div id="coord-hover-display" class="text-xs text-yellow-400">
                                Survolez la carte
                            </div>
                        </div>

                        <!-- Grille de la carte -->
                        <div class="font-mono text-xs leading-none" style="letter-spacing: 0;">
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

                                            // Convertir en coordonnées de secteur
                                            $secteurX = floor($absX / 10);
                                            $secteurY = floor($absY / 10);
                                            $secteurZ = floor($absZ / 10);

                                            // Chercher un système dans ce secteur
                                            $hasSystem = isset($grille[$secteurX][$secteurY][$secteurZ]);

                                            if ($hasSystem) {
                                                $systeme = $grille[$secteurX][$secteurY][$secteurZ];
                                                // Coordonnées absolues du système (entières)
                                                $sysAbsX = intval($systeme->secteur_x * 10 + $systeme->position_x);
                                                $sysAbsY = intval($systeme->secteur_y * 10 + $systeme->position_y);
                                                $sysAbsZ = intval($systeme->secteur_z * 10 + $systeme->position_z);

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
                                              onclick="clickCell({{ $absX }}, {{ $absY }}, {{ $absZ }}, this)"
                                              onmouseover="updateCoordDisplay({{ $absX }}, {{ $absY }}, {{ $absZ }}, '{{ $cellContent }}')"
                                              title="{{ $cellTitle }}"
                                              data-coord-x="{{ $absX }}"
                                              data-coord-y="{{ $absY }}"
                                              data-coord-z="{{ $absZ }}"
                                              {!! $cellData !!}>{{ $cellContent }}</span>
                                    @endfor
                                </div>
                            @endfor
                        </div>

                        <!-- Axe X en bas -->
                        <div class="text-xs text-gray-600 mt-1 text-center">
                            {{ $centerX - $halfSize }} ← {{ $hAxisLabel }} → {{ $centerX + $halfSize }} AL
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
                                <label class="block text-xs text-gray-400 mb-1">Nom du système</label>
                                <input type="text" name="nom" required
                                       class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white text-sm"
                                       placeholder="Ex: Alpha Centauri B">
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">X (AL)</label>
                                    <input type="number" step="0.01" name="coord_x" id="create-coord-x" required
                                           value="0"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Y (AL)</label>
                                    <input type="number" step="0.01" name="coord_y" id="create-coord-y" required
                                           value="0"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Z (AL)</label>
                                    <input type="number" step="0.01" name="coord_z" id="create-coord-z" required
                                           value="0"
                                           class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Type spectral</label>
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
                                    <label class="block text-xs text-gray-400 mb-1">Nb planètes</label>
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
        </main>
    </div>
</div>

<script>
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

// Clic sur une cellule de la carte
function clickCell(x, y, z, element) {
    const plan = '{{ $plan }}';
    const isSystem = element.dataset.isSystem === 'true';

    if (isSystem) {
        // Si c'est un système, charger le niveau 2 via AJAX
        const secteurX = element.dataset.secteurX;
        const secteurY = element.dataset.secteurY;
        const secteurZ = element.dataset.secteurZ;
        loadSecteurDetail(secteurX, secteurY, secteurZ);
    } else {
        // Sinon, mettre à jour les coordonnées du formulaire de création
        document.getElementById('create-coord-x').value = x;
        document.getElementById('create-coord-y').value = y;
        document.getElementById('create-coord-z').value = z;

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

// Support clavier pour navigation
document.addEventListener('keydown', function(e) {
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
