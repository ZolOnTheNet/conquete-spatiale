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

<div class="h-full flex flex-col">
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
                    <button onclick="navigateToPosition({{ $positionActuelle['x'] }}, {{ $positionActuelle['y'] }}, {{ $positionActuelle['z'] }})"
                       class="text-green-400 hover:text-green-300 underline">
                        Position actuelle
                    </button>
                    <span class="text-gray-600">|</span>
                    @endif
                    <button onclick="navigateToPosition(0, 0, 0)"
                       class="text-yellow-400 hover:text-yellow-300 underline">
                        Sol
                    </button>
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
            <div class="text-white"><span class="text-yellow-400 font-bold">*</span> = Système découvert</div>
            <div class="text-white"><span class="text-gray-600 font-bold">·</span> = Zone inexplorée</div>
            <div class="text-cyan-300">Cliquez sur une étoile pour voir les détails du secteur →</div>
        </div>
    </div>

    <!-- Two Maps Side by Side -->
    <div class="grid grid-cols-2 gap-4 flex-1">
        <!-- Carte Niveau 1: Vue Secteurs (100 AL × 100 AL) -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3">
            <h2 class="text-lg font-bold text-cyan-400 mb-2">Systèmes Découverts (100 AL)</h2>

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

                                    // Chercher un système découvert dans ce secteur
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
                                            $cellTitle = "{$systeme->nom} (X:{$sysAbsX} Y:{$sysAbsY} Z:{$sysAbsZ})";
                                            $cellData = "data-secteur-x='{$secteurX}' data-secteur-y='{$secteurY}' data-secteur-z='{$secteurZ}' data-system-id='{$systeme->id}' data-is-system='true'";
                                        } else {
                                            $cellContent = '·';
                                            $cellClass = 'text-gray-700 cursor-pointer hover:bg-gray-800';
                                            $cellTitle = "AL: {$absX}, {$absY}, {$absZ} (Inexplorée)";
                                            $cellData = "data-is-system='false'";
                                        }
                                    } else {
                                        $cellContent = '·';
                                        $cellClass = 'text-gray-700 cursor-pointer hover:bg-gray-800';
                                        $cellTitle = "AL: {$absX}, {$absY}, {$absZ} (Inexplorée)";
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
            <h2 class="text-lg font-bold text-yellow-400 mb-2">Détail Secteur</h2>
            <div id="secteur-detail" class="text-center text-gray-500 py-8">
                Cliquez sur une étoile (*) dans la carte de gauche pour afficher les détails du secteur
            </div>
        </div>
    </div>
</div>

<script>
// Mettre à jour l'affichage des coordonnées survolées
function updateCoordDisplay(x, y, z, cellContent) {
    const display = document.getElementById('coord-hover-display');
    let contentDesc = '';

    if (cellContent === '*') {
        contentDesc = '<span class="text-yellow-400">★ Système découvert</span>';
    } else {
        contentDesc = '<span class="text-gray-400">○ Zone inexplorée</span>';
    }

    display.innerHTML = `X: ${x} Y: ${y} Z: ${z} | ${contentDesc}`;
}

// Navigation vers des coordonnées spécifiques (AJAX version)
function navigateToCoords() {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    const plan = '{{ $plan }}';

    // Utiliser loadView pour charger via AJAX si disponible
    if (typeof loadView === 'function') {
        loadView('carte', 'Carte', `?x=${x}&y=${y}&z=${z}&plan=${plan}`);
    } else {
        window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
    }
}

// Navigation vers une position spécifique
function navigateToPosition(x, y, z) {
    const plan = '{{ $plan }}';

    if (typeof loadView === 'function') {
        loadView('carte', 'Carte', `?x=${x}&y=${y}&z=${z}&plan=${plan}`);
    } else {
        window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
    }
}

// Changement de plan d'affichage (AJAX version)
function changePlan(newPlan) {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;

    if (typeof loadView === 'function') {
        loadView('carte', 'Carte', `?x=${x}&y=${y}&z=${z}&plan=${newPlan}`);
    } else {
        window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${newPlan}`;
    }
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
        // Sinon, juste recentrer la carte
        if (typeof loadView === 'function') {
            loadView('carte', 'Carte', `?x=${x}&y=${y}&z=${z}&plan=${plan}`);
        } else {
            window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
        }
    }
}

// Charger les détails d'un secteur via AJAX
function loadSecteurDetail(x, y, z) {
    const detailDiv = document.getElementById('secteur-detail');
    detailDiv.innerHTML = '<div class="text-cyan-400 py-8">Chargement...</div>';

    fetch(`/carte/secteur/${x}/${y}/${z}`)
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

    if (typeof loadView === 'function') {
        loadView('carte', 'Carte', `?x=${newX}&y=${newY}&z=${newZ}&plan=${plan}`);
    } else {
        window.location.href = `/carte?x=${newX}&y=${newY}&z=${newZ}&plan=${plan}`;
    }
});
</script>
