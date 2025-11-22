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
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(0, 0, 0, 0.9);
    border: 2px solid #fbbf24;
    padding: 6px 10px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    color: #fbbf24;
    z-index: 1000;
    min-width: 150px;
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
                    <h2 class="text-lg font-bold text-cyan-400 mb-2">Niveau 1: Carte Univers (100 AL)</h2>

                    <div class="bg-black border border-gray-700 rounded p-2 relative">
                        <!-- Affichage des coordonnées survolées -->
                        <div id="coord-hover-display">
                            Survolez la carte
                        </div>
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

                        <!-- Étiquettes des axes -->
                        <div class="text-xs text-gray-500 mb-1 text-center">
                            {{ $hAxisLabel }} (horizontal) / {{ $vAxisLabel }} (vertical) | {{ $fixedAxis }} = {{ $fixedValue }} AL
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
                                                // Coordonnées absolues du système
                                                $sysAbsX = $systeme->secteur_x * 10 + $systeme->position_x;
                                                $sysAbsY = $systeme->secteur_y * 10 + $systeme->position_y;
                                                $sysAbsZ = $systeme->secteur_z * 10 + $systeme->position_z;

                                                // Arrondir les coordonnées du système pour trouver la cellule AL la plus proche
                                                $nearestCellX = round($sysAbsX);
                                                $nearestCellY = round($sysAbsY);
                                                $nearestCellZ = round($sysAbsZ);

                                                // Afficher * seulement sur la cellule AL la plus proche du système
                                                if ($absX == $nearestCellX && $absY == $nearestCellY && $absZ == $nearestCellZ) {
                                                    $cellContent = '*';
                                                    $cellClass = 'text-yellow-400 cursor-pointer hover:bg-yellow-900/30 system-cell';
                                                    $cellTitle = "{$systeme->nom} (X:" . number_format($sysAbsX, 2) . " Y:" . number_format($sysAbsY, 2) . " Z:" . number_format($sysAbsZ, 2) . " P:{$systeme->puissance})";
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
                    <h2 class="text-lg font-bold text-yellow-400 mb-2">Niveau 2: Détail Secteur</h2>
                    <div id="secteur-detail" class="text-center text-gray-500 py-8">
                        Cliquez sur une étoile (*) dans la carte de gauche pour afficher les détails du secteur
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

    display.innerHTML = `
        <div class="font-bold text-cyan-300 mb-1">Coordonnées:</div>
        <div>X: ${x} AL</div>
        <div>Y: ${y} AL</div>
        <div>Z: ${z} AL</div>
        <div class="mt-1 pt-1 border-t border-gray-600">${contentDesc}</div>
    `;
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
        // Sinon, juste recentrer la carte
        window.location.href = `/admin/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
    }
}

// Charger les détails d'un secteur via AJAX
function loadSecteurDetail(x, y, z) {
    const detailDiv = document.getElementById('secteur-detail');
    detailDiv.innerHTML = '<div class="text-cyan-400 py-8">Chargement...</div>';

    fetch(`/admin/carte/secteur/${x}/${y}/${z}`)
        .then(response => response.text())
        .then(html => {
            // La vue retourne directement le HTML sans layout
            detailDiv.innerHTML = html;
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
    window.location.href = `/admin/carte?x=${newX}&y=${newY}&z=${newZ}&plan=${plan}`;
});
</script>
@endsection
