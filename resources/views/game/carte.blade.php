@extends('layouts.app')

@section('title', 'Carte de l\'Univers')

@push('styles')
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

/* Fonds étoilés pour les secteurs */
.sector-unknown {
    background: radial-gradient(circle at 30% 40%, rgba(100, 100, 120, 0.3), rgba(40, 40, 50, 0.5)),
                radial-gradient(1px 1px at 20% 30%, white, transparent),
                radial-gradient(1px 1px at 60% 70%, white, transparent),
                radial-gradient(1px 1px at 80% 20%, rgba(255,255,255,0.5), transparent),
                radial-gradient(1px 1px at 40% 80%, rgba(255,255,255,0.5), transparent);
    background-size: 100% 100%, 200% 200%, 300% 300%, 150% 150%, 250% 250%;
    background-color: #3a3a45;
}

.sector-known-empty {
    background: radial-gradient(1px 1px at 15% 25%, white, transparent),
                radial-gradient(1px 1px at 65% 75%, white, transparent),
                radial-gradient(1px 1px at 85% 15%, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 35% 85%, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 50% 50%, rgba(255,255,255,0.4), transparent);
    background-size: 200% 200%, 300% 300%, 150% 150%, 250% 250%, 180% 180%;
    background-color: #0a0a0f;
}

.sector-has-system {
    background: radial-gradient(1px 1px at 15% 25%, white, transparent),
                radial-gradient(1px 1px at 65% 75%, white, transparent),
                radial-gradient(1px 1px at 85% 15%, rgba(255,255,255,0.6), transparent);
    background-size: 200% 200%, 300% 300%, 150% 150%;
    background-color: #000000;
}
</style>
@endpush

@section('content')
<div class="h-screen flex flex-col">
    <!-- Header 4 Colonnes -->
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$personnage->vaisseauActif ?? null"
        :systeme="null"
        :secteur="null"
    />

    <!-- Main Layout -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Menu Gauche -->
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $personnage->vaisseauActif ?? null,
            'compte' => auth()->user()
        ])

        <!-- Contenu Principal Carte -->
        <main class="flex-1 overflow-auto p-4 bg-gray-900" style="max-width: 100%;">
            <!-- En-tête avec Onglets -->
            <div class="mb-4 bg-gray-800/50 border-b border-cyan-500/30">
                <div class="px-4 py-3 flex items-center justify-between">
                    <h2 class="text-2xl font-orbitron text-cyan-400">SPATIOCARTE</h2>
                    <div class="text-sm text-gray-400">
                        Systèmes découverts: <span class="text-yellow-400">{{ count(array_merge(...array_values(array_map(fn($x) => array_merge(...array_values($x)), array_values($grille))))) }}</span>
                    </div>
                </div>

                <!-- Onglets -->
                <div class="flex border-t border-cyan-500/30">
                    <a href="{{ route('carte') }}"
                       class="px-6 py-3 text-sm font-semibold text-cyan-400 bg-cyan-900/30 border-b-2 border-cyan-400">
                        🗺️ Carte
                    </a>
                    <a href="{{ route('personnage.spatiocarte', ['onglet' => 'atlas']) }}"
                       class="px-6 py-3 text-sm font-semibold text-gray-400 hover:text-cyan-400 hover:bg-cyan-900/20 transition border-l border-cyan-500/30">
                        📚 Atlas
                    </a>
                </div>
            </div>

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
                        <a href="{{ route('carte', ['x' => $positionActuelle['x'], 'y' => $positionActuelle['y'], 'z' => $positionActuelle['z'], 'plan' => $plan]) }}"
                           class="text-green-400 hover:text-green-300 underline">
                            Position actuelle
                        </a>
                        <span class="text-gray-600">|</span>
                        @endif
                        <a href="{{ route('carte', ['x' => 0, 'y' => 0, 'z' => 0, 'plan' => $plan]) }}"
                           class="text-yellow-400 hover:text-yellow-300 underline">
                            Sol
                        </a>
                        <span class="text-gray-600">|</span>
                        <a href="{{ route('dashboard') }}"
                           class="text-cyan-400 hover:text-cyan-300 underline">
                            Retour Dashboard
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
            <div class="flex items-center gap-6 text-xs flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 sector-has-system border border-yellow-500"></span>
                    <span class="text-white"><span class="text-yellow-400 font-bold">*</span> = Système découvert</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 sector-known-empty border border-gray-600"></span>
                    <span class="text-white">Secteur exploré (vide)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 sector-unknown border border-gray-500"></span>
                    <span class="text-white">Secteur inexploré</span>
                </div>
                <div class="text-cyan-300 ml-auto">Cliquez sur une étoile (*) pour voir les détails →</div>
            </div>
        </div>

        <!-- Two Maps Side by Side -->
        <div class="grid grid-cols-1 2xl:grid-cols-2 gap-4">
            <!-- Carte Niveau 1: Vue Secteurs -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-3">
                <h2 class="text-lg font-bold text-cyan-400 mb-2">
                    Systèmes Découverts ({{ $halfSize * 2 }} AL)
                    @if($isAdmin ?? false)
                        <span class="text-xs text-yellow-400">[Mode Admin]</span>
                    @endif
                </h2>

                <div class="bg-black border border-gray-700 rounded p-2 relative">
                    @php
                        // Déterminer les axes en fonction du plan
                        // $halfSize est passé par le contrôleur (5 pour joueur, 50 pour admin)

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
                            {{ $hAxisLabel }} (horizontal) / {{ $vAxisLabel }} (vertical) | {{ $fixedAxis }} = {{ $fixedValue }}
                        </div>
                        <div id="coord-hover-display" class="text-xs text-yellow-400">
                            Survolez la carte
                        </div>
                    </div>

                    @php
                        // $knownSectors est maintenant passé par le contrôleur
                        // Il contient uniquement les secteurs avec des systèmes découverts
                    @endphp

                    <!-- CARTE GRAPHIQUE CANVAS -->
                    <div class="relative bg-black border border-gray-700 rounded p-2">
                        <canvas id="carte-canvas" width="600" height="600" class="border border-gray-700 rounded" style="cursor: crosshair;"></canvas>
                    </div>

                    <!-- ANCIENNE GRILLE TEXTE (cachée, pour référence) -->
                    <div class="font-mono text-xs leading-none hidden" style="letter-spacing: 0;">
                        @for($v = $halfSize - 1; $v >= -$halfSize; $v--)
                            <div class="flex">
                                @for($h = -$halfSize; $h < $halfSize; $h++)
                                    @php
                                        // Calculer les coordonnées de secteur selon le plan
                                        if ($plan === 'Z') {
                                            $secteurX = $centerX + $h;
                                            $secteurY = $centerY + $v;
                                            $secteurZ = $centerZ;
                                        } elseif ($plan === 'Y') {
                                            $secteurX = $centerX + $h;
                                            $secteurY = $centerY;
                                            $secteurZ = $centerZ + $v;
                                        } else {
                                            $secteurX = $centerX;
                                            $secteurY = $centerY + $h;
                                            $secteurZ = $centerZ + $v;
                                        }

                                        // Chercher un système découvert dans ce secteur
                                        $hasSystem = isset($grille[$secteurX][$secteurY][$secteurZ]);

                                        // Vérifier si le secteur est connu (précalculé)
                                        $isKnownSector = isset($knownSectors["{$secteurX},{$secteurY},{$secteurZ}"]);

                                        if ($hasSystem) {
                                            $systeme = $grille[$secteurX][$secteurY][$secteurZ];
                                            // Coordonnées du secteur du système
                                            $sysSecteurX = $systeme->secteur_x;
                                            $sysSecteurY = $systeme->secteur_y;
                                            $sysSecteurZ = $systeme->secteur_z;

                                            // Afficher * sur la cellule du secteur du système
                                            if ($secteurX == $sysSecteurX && $secteurY == $sysSecteurY && $secteurZ == $sysSecteurZ) {
                                                $cellContent = '*';
                                                $cellClass = 'text-yellow-400 cursor-pointer hover:bg-yellow-900/30 system-cell sector-has-system';
                                                $cellTitle = "{$systeme->nom} (Secteur: {$sysSecteurX}, {$sysSecteurY}, {$sysSecteurZ})";
                                                $cellData = "data-secteur-x='{$secteurX}' data-secteur-y='{$secteurY}' data-secteur-z='{$secteurZ}' data-system-id='{$systeme->id}' data-is-system='true'";
                                            } else {
                                                $cellContent = '·';
                                                $cellClass = 'text-gray-700 cursor-pointer hover:bg-gray-800 sector-known-empty';
                                                $cellTitle = "Secteur: {$secteurX}, {$secteurY}, {$secteurZ} (Exploré - vide)";
                                                $cellData = "data-is-system='false'";
                                            }
                                        } else {
                                            $cellContent = '·';
                                            if ($isKnownSector) {
                                                $cellClass = 'text-gray-500 cursor-pointer hover:bg-gray-700 sector-known-empty';
                                                $cellTitle = "Secteur: {$secteurX}, {$secteurY}, {$secteurZ} (Exploré - vide)";
                                            } else {
                                                $cellClass = 'text-gray-600 cursor-pointer hover:bg-gray-700 sector-unknown';
                                                $cellTitle = "Secteur: {$secteurX}, {$secteurY}, {$secteurZ} (Inexploré)";
                                            }
                                            $cellData = "data-is-system='false'";
                                        }
                                    @endphp
                                    <span class="{{ $cellClass }}"
                                          ondblclick="clickCell({{ $secteurX }}, {{ $secteurY }}, {{ $secteurZ }}, this)"
                                          @if($hasSystem && $secteurX == $sysSecteurX && $secteurY == $sysSecteurY && $secteurZ == $sysSecteurZ)
                                          onclick="clickCellSimple({{ $secteurX }}, {{ $secteurY }}, {{ $secteurZ }}, this)"
                                          @endif
                                          onmouseover="updateCoordDisplay({{ $secteurX }}, {{ $secteurY }}, {{ $secteurZ }}, '{{ $cellContent }}')"
                                          title="{{ $cellTitle }}"
                                          data-coord-x="{{ $secteurX }}"
                                          data-coord-y="{{ $secteurY }}"
                                          data-coord-z="{{ $secteurZ }}"
                                          {!! $cellData !!}>{{ $cellContent }}</span>
                                @endfor
                            </div>
                        @endfor
                    </div>

                    <!-- Axe en bas -->
                    <div class="text-xs text-gray-600 mt-1 text-center">
                        Secteur {{ $centerX - $halfSize }} ← {{ $hAxisLabel }} → {{ $centerX + $halfSize }}
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
        </main>

        <!-- Console Droite Redimensionnable -->
        <x-console-resizable>
            @include('game.partials.console')
        </x-console-resizable>
    </div>
</div>

@push('scripts')
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

    display.innerHTML = `Secteur: (${x}, ${y}, ${z}) | ${contentDesc}`;
}

// Navigation vers des coordonnées spécifiques
function navigateToCoords() {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    const plan = '{{ $plan }}';
    window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
}

// Changement de plan d'affichage
function changePlan(newPlan) {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${newPlan}`;
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
function clickCell(x, y, z, element) {
    const plan = '{{ $plan }}';
    const isSystem = element.dataset.isSystem === 'true';

    if (isSystem) {
        // Si c'est un système, charger le niveau 2 via AJAX et recentrer
        const secteurX = element.dataset.secteurX;
        const secteurY = element.dataset.secteurY;
        const secteurZ = element.dataset.secteurZ;
        loadSecteurDetail(secteurX, secteurY, secteurZ);
    }

    // Dans tous les cas, recentrer la carte sur la cellule double-cliquée
    window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
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
    window.location.href = `/carte?x=${newX}&y=${newY}&z=${newZ}&plan=${plan}`;
});

// ===== DESSIN CARTE GRAPHIQUE CANVAS =====
function drawGraphicMap() {
    const canvas = document.getElementById('carte-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const cellSize = 28; // pixels par cellule AL (21x21 = ~600px)
    const halfSize = {{ $halfSize }};
    const plan = '{{ $plan }}';
    const centerX = {{ $centerX }};
    const centerY = {{ $centerY }};
    const centerZ = {{ $centerZ }};
    const gridSize = halfSize * 2; // 21 AL

    // Secteurs connus (précalculés côté PHP)
    const knownSectors = {!! json_encode($knownSectors ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    // Grille des systèmes
    const grille = {!! json_encode($grille ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    // Ajuster taille canvas
    canvas.width = gridSize * cellSize;
    canvas.height = gridSize * cellSize;

    // Fond noir
    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Dessiner le fond étoilé
    function seededRandom(seed) {
        const x = Math.sin(seed) * 10000;
        return x - Math.floor(x);
    }

    const seed = centerX * 1000 + centerY * 100 + centerZ * 10;

    // Parcourir chaque cellule pour dessiner le fond approprié
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

            const key = `${absX},${absY},${absZ}`;
            const isKnown = knownSectors.hasOwnProperty(key);
            const hasSystem = grille[absX] && grille[absX][absY] && grille[absX][absY][absZ];

            const canvasX = (h + halfSize) * cellSize;
            const canvasY = (halfSize - 1 - v) * cellSize;

            // Dessiner fond de la cellule
            if (!isKnown) {
                // Secteur inconnu : gris avec quelques étoiles
                ctx.fillStyle = '#2a2a35';
                ctx.fillRect(canvasX, canvasY, cellSize, cellSize);

                // Quelques étoiles grisées
                ctx.fillStyle = 'rgba(180, 180, 190, 0.3)';
                for (let i = 0; i < 3; i++) {
                    const sx = canvasX + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 7) * cellSize;
                    const sy = canvasY + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 11) * cellSize;
                    ctx.fillRect(sx, sy, 1, 1);
                }
            } else if (hasSystem) {
                // Secteur avec système : noir avec étoiles blanches
                ctx.fillStyle = '#000000';
                ctx.fillRect(canvasX, canvasY, cellSize, cellSize);

                ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
                for (let i = 0; i < 5; i++) {
                    const sx = canvasX + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 7) * cellSize;
                    const sy = canvasY + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 11) * cellSize;
                    const size = seededRandom(seed + absX + absY + absZ + i * 13) > 0.8 ? 2 : 1;
                    ctx.fillRect(sx, sy, size, size);
                }

                // Dessiner le système (étoile jaune)
                ctx.fillStyle = '#fbbf24';
                ctx.font = 'bold 18px monospace';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('★', canvasX + cellSize / 2, canvasY + cellSize / 2);
            } else {
                // Secteur connu vide : noir avec étoiles
                ctx.fillStyle = '#0a0a0f';
                ctx.fillRect(canvasX, canvasY, cellSize, cellSize);

                ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
                for (let i = 0; i < 4; i++) {
                    const sx = canvasX + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 7) * cellSize;
                    const sy = canvasY + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 11) * cellSize;
                    ctx.fillRect(sx, sy, 1, 1);
                }
            }

            // Grille tous les 5 AL
            if ((absX % 5 === 0) || (absY % 5 === 0) || (absZ % 5 === 0)) {
                ctx.strokeStyle = 'rgba(74, 158, 255, 0.1)';
                ctx.lineWidth = 1;
                ctx.strokeRect(canvasX, canvasY, cellSize, cellSize);
            }
        }
    }

    // Marquer position du joueur
    const playerPos = {!! json_encode(['x' => $positionActuelle['x'] ?? $centerX, 'y' => $positionActuelle['y'] ?? $centerY, 'z' => $positionActuelle['z'] ?? $centerZ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    let playerH = 0, playerV = 0;

    if (plan === 'Z') {
        playerH = playerPos.x - centerX;
        playerV = playerPos.y - centerY;
    } else if (plan === 'Y') {
        playerH = playerPos.x - centerX;
        playerV = playerPos.z - centerZ;
    } else {
        playerH = playerPos.y - centerY;
        playerV = playerPos.z - centerZ;
    }

    if (Math.abs(playerH) < halfSize && Math.abs(playerV) < halfSize) {
        const px = (playerH + halfSize) * cellSize + cellSize / 2;
        const py = (halfSize - 1 - playerV) * cellSize + cellSize / 2;

        ctx.strokeStyle = '#00ff00';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.arc(px, py, cellSize / 3, 0, Math.PI * 2);
        ctx.stroke();
    }
}

// Ajouter l'interactivité au canvas
function setupCanvasInteractions() {
    const canvas = document.getElementById('carte-canvas');
    if (!canvas) return;

    const cellSize = 28;
    const halfSize = {{ $halfSize }};
    const plan = '{{ $plan }}';
    const centerX = {{ $centerX }};
    const centerY = {{ $centerY }};
    const centerZ = {{ $centerZ }};
    const grille = {!! json_encode($grille ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    // Afficher les coordonnées au survol
    canvas.addEventListener('mousemove', function(e) {
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Convertir position canvas en coordonnées grille
        const gridX = Math.floor(mouseX / cellSize);
        const gridY = Math.floor(mouseY / cellSize);

        // Convertir en coordonnées AL
        const h = gridX - halfSize;
        const v = halfSize - 1 - gridY;

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

        // Vérifier si c'est un système
        const hasSystem = grille[absX] && grille[absX][absY] && grille[absX][absY][absZ];
        const cellContent = hasSystem ? '*' : '·';

        updateCoordDisplay(absX, absY, absZ, cellContent);
    });

    // Gérer le simple clic (afficher détails)
    canvas.addEventListener('click', function(e) {
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Convertir position canvas en coordonnées grille
        const gridX = Math.floor(mouseX / cellSize);
        const gridY = Math.floor(mouseY / cellSize);

        // Convertir en coordonnées AL
        const h = gridX - halfSize;
        const v = halfSize - 1 - gridY;

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

        // Vérifier si c'est un système
        const systeme = grille[absX] && grille[absX][absY] && grille[absX][absY][absZ];
        if (systeme) {
            // Afficher les détails sans recentrer
            loadSecteurDetail(absX, absY, absZ);
        }
    });

    // Gérer le double-clic (recentrer la carte)
    canvas.addEventListener('dblclick', function(e) {
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Convertir position canvas en coordonnées grille
        const gridX = Math.floor(mouseX / cellSize);
        const gridY = Math.floor(mouseY / cellSize);

        // Convertir en coordonnées AL
        const h = gridX - halfSize;
        const v = halfSize - 1 - gridY;

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

        // Recentrer la carte sur cette position
        window.location.href = `/carte?x=${absX}&y=${absY}&z=${absZ}&plan=${plan}`;
    });

    // Gérer la souris qui quitte le canvas
    canvas.addEventListener('mouseleave', function() {
        const display = document.getElementById('coord-hover-display');
        if (display) {
            display.innerHTML = 'Survolez la carte';
        }
    });
}

// Dessiner au chargement et configurer l'interactivité
window.addEventListener('load', function() {
    drawGraphicMap();
    setupCanvasInteractions();
});
</script>
@endpush
@endsection
