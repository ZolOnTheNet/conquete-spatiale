@extends('layouts.game-hud')

@section('title', "Carte de l'Univers")

@push('hud-styles')
<style>
#coord-hover-display { color: var(--warning); white-space: nowrap; }

.sector-unknown {
    background: radial-gradient(circle at 30% 40%, rgba(100,100,120,0.3), rgba(40,40,50,0.5)),
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

#carte-canvas { cursor: crosshair; display:block; }

.plan-btn-active { background: rgba(251,191,36,0.2); color: var(--warning); border: 1px solid rgba(251,191,36,0.5); }
.plan-btn-inactive { background: transparent; color: var(--text-muted); border: 1px solid var(--border-subtle); }
.plan-btn-inactive:hover { color: var(--text-primary); border-color: var(--border-strong); }
</style>
@endpush

@section('hud-content')
<div class="hud-page-title">Spatiocarte</div>

{{-- Onglets --}}
<div style="display:flex;margin-bottom:12px;border:1px solid var(--border-subtle);">
    <span style="padding:8px 20px;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--data);border-right:1px solid var(--border-subtle);">🗺 Carte</span>
    <a href="{{ route('personnage.spatiocarte', ['onglet' => 'atlas']) }}" style="padding:8px 20px;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);text-decoration:none;border-right:1px solid var(--border-subtle);transition:color 0.12s;" onmouseover="this.style.color='var(--data)'" onmouseout="this.style.color='var(--text-muted)'">📚 Atlas</a>
</div>

{{-- Contrôles --}}
<div class="hud-panel" style="margin-bottom:12px;">
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:10px;">
        {{-- Coordonnées --}}
        <div style="display:flex;align-items:center;gap:6px;font-family:var(--mono);font-size:11px;">
            <span style="color:var(--text-muted);">Secteur</span>
            <input type="number" id="coord-x" value="{{ $centerX }}"
                   style="width:64px;background:rgba(5,7,12,0.6);border:1px solid var(--border-subtle);color:var(--text-primary);font-family:var(--mono);font-size:11px;padding:4px 6px;outline:none;" />
            <input type="number" id="coord-y" value="{{ $centerY }}"
                   style="width:64px;background:rgba(5,7,12,0.6);border:1px solid var(--border-subtle);color:var(--text-primary);font-family:var(--mono);font-size:11px;padding:4px 6px;outline:none;" />
            <input type="number" id="coord-z" value="{{ $centerZ }}"
                   style="width:64px;background:rgba(5,7,12,0.6);border:1px solid var(--border-subtle);color:var(--text-primary);font-family:var(--mono);font-size:11px;padding:4px 6px;outline:none;" />
            <button onclick="navigateToCoords()"
                    style="font-family:var(--mono);font-size:10px;letter-spacing:0.08em;text-transform:uppercase;padding:4px 12px;background:transparent;color:var(--data);border:1px solid var(--data);cursor:pointer;transition:background 0.12s;"
                    onmouseover="this.style.background='rgba(127,212,255,0.1)'" onmouseout="this.style.background='transparent'">Aller</button>
        </div>

        {{-- Liens rapides --}}
        <div style="font-family:var(--mono);font-size:10px;display:flex;gap:12px;align-items:center;">
            @if($positionActuelle)
            <a href="{{ route('carte', ['x' => $positionActuelle['x'], 'y' => $positionActuelle['y'], 'z' => $positionActuelle['z'], 'plan' => $plan]) }}"
               style="color:var(--success);text-decoration:none;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--success)'">Position actuelle</a>
            <span style="color:var(--border-strong);">|</span>
            @endif
            <a href="{{ route('carte', ['x' => 0, 'y' => 0, 'z' => 0, 'plan' => $plan]) }}"
               style="color:var(--warning);text-decoration:none;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--warning)'">Sol</a>
        </div>

        {{-- Sélection du plan --}}
        <div style="display:flex;align-items:center;gap:6px;margin-left:auto;font-family:var(--mono);font-size:10px;">
            <span style="color:var(--text-muted);">Plan</span>
            <button onclick="changePlan('X')" class="{{ $plan === 'X' ? 'plan-btn-active' : 'plan-btn-inactive' }}" style="padding:4px 10px;font-family:var(--mono);font-size:10px;cursor:pointer;transition:all 0.12s;">X</button>
            <button onclick="changePlan('Y')" class="{{ $plan === 'Y' ? 'plan-btn-active' : 'plan-btn-inactive' }}" style="padding:4px 10px;font-family:var(--mono);font-size:10px;cursor:pointer;transition:all 0.12s;">Y</button>
            <button onclick="changePlan('Z')" class="{{ $plan === 'Z' ? 'plan-btn-active' : 'plan-btn-inactive' }}" style="padding:4px 10px;font-family:var(--mono);font-size:10px;cursor:pointer;transition:all 0.12s;">Z</button>
        </div>
    </div>

    {{-- Légende --}}
    <div style="display:flex;flex-wrap:wrap;gap:16px;font-family:var(--mono);font-size:10px;color:var(--text-secondary);align-items:center;">
        <div style="display:flex;align-items:center;gap:6px;">
            <span style="display:inline-block;width:14px;height:14px;" class="sector-has-system"></span>
            <span><span style="color:var(--warning);font-weight:700;">★</span> Système découvert</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <span style="display:inline-block;width:14px;height:14px;border:1px solid var(--border-subtle);" class="sector-known-empty"></span>
            <span>Secteur exploré (vide)</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <span style="display:inline-block;width:14px;height:14px;border:1px solid var(--border-subtle);" class="sector-unknown"></span>
            <span>Secteur inexploré</span>
        </div>
        <span style="color:var(--data);margin-left:auto;">Cliquez sur une étoile pour voir les détails →</span>
    </div>
</div>

{{-- Carte + Détail côte à côte --}}
<div style="display:grid;grid-template-columns:auto 1fr;gap:12px;align-items:start;">

    {{-- Carte canvas --}}
    <div class="hud-panel" style="min-width:0;">
        <div class="hud-panel-title" style="display:flex;justify-content:space-between;align-items:center;">
            <span>Vue des systèmes — {{ $halfSize * 2 }} AL
                @if($isAdmin ?? false)
                    <span style="color:var(--warning);font-size:9px;margin-left:6px;">[Mode Admin]</span>
                @endif
            </span>
            <div id="coord-hover-display" style="font-size:10px;">Survolez la carte</div>
        </div>

        {{-- Axes --}}
        @php
            if ($plan === 'Z') { $hAxisLabel = 'X'; $vAxisLabel = 'Y'; $fixedAxis = 'Z'; $fixedValue = $centerZ; }
            elseif ($plan === 'Y') { $hAxisLabel = 'X'; $vAxisLabel = 'Z'; $fixedAxis = 'Y'; $fixedValue = $centerY; }
            else { $hAxisLabel = 'Y'; $vAxisLabel = 'Z'; $fixedAxis = 'X'; $fixedValue = $centerX; }
        @endphp
        <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-bottom:6px;">
            {{ $hAxisLabel }} (→) / {{ $vAxisLabel }} (↑) | {{ $fixedAxis }} = {{ $fixedValue }}
        </div>

        <canvas id="carte-canvas" width="600" height="600"></canvas>

        <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-top:4px;text-align:center;">
            {{ $centerX - $halfSize }} ← {{ $hAxisLabel }} → {{ $centerX + $halfSize }}
        </div>

        {{-- Ancienne grille texte (cachée, référence interne) --}}
        <div class="font-mono text-xs leading-none hidden" style="display:none;letter-spacing:0;">
            @for($v = $halfSize - 1; $v >= -$halfSize; $v--)
                <div class="flex">
                    @for($h = -$halfSize; $h < $halfSize; $h++)
                        @php
                            if ($plan === 'Z') { $secteurX = $centerX + $h; $secteurY = $centerY + $v; $secteurZ = $centerZ; }
                            elseif ($plan === 'Y') { $secteurX = $centerX + $h; $secteurY = $centerY; $secteurZ = $centerZ + $v; }
                            else { $secteurX = $centerX; $secteurY = $centerY + $h; $secteurZ = $centerZ + $v; }
                            $hasSystem = isset($grille[$secteurX][$secteurY][$secteurZ]);
                            $isKnownSector = isset($knownSectors["{$secteurX},{$secteurY},{$secteurZ}"]);
                            if ($hasSystem) {
                                $systeme = $grille[$secteurX][$secteurY][$secteurZ];
                                $sysSecteurX = $systeme->secteur_x; $sysSecteurY = $systeme->secteur_y; $sysSecteurZ = $systeme->secteur_z;
                                if ($secteurX == $sysSecteurX && $secteurY == $sysSecteurY && $secteurZ == $sysSecteurZ) {
                                    $cellContent = '*'; $cellClass = 'text-yellow-400 cursor-pointer hover:bg-yellow-900/30 system-cell sector-has-system';
                                    $cellTitle = "{$systeme->nom} (Secteur: {$sysSecteurX}, {$sysSecteurY}, {$sysSecteurZ})";
                                    $cellData = "data-secteur-x='{$secteurX}' data-secteur-y='{$secteurY}' data-secteur-z='{$secteurZ}' data-system-id='{$systeme->id}' data-is-system='true'";
                                } else {
                                    $cellContent = '·'; $cellClass = 'text-gray-700 cursor-pointer hover:bg-gray-800 sector-known-empty';
                                    $cellTitle = "Secteur: {$secteurX}, {$secteurY}, {$secteurZ} (Exploré - vide)"; $cellData = "data-is-system='false'";
                                }
                            } else {
                                $cellContent = '·';
                                if ($isKnownSector) { $cellClass = 'text-gray-500 cursor-pointer hover:bg-gray-700 sector-known-empty'; $cellTitle = "Secteur: {$secteurX}, {$secteurY}, {$secteurZ} (Exploré - vide)"; }
                                else { $cellClass = 'text-gray-600 cursor-pointer hover:bg-gray-700 sector-unknown'; $cellTitle = "Secteur: {$secteurX}, {$secteurY}, {$secteurZ} (Inexploré)"; }
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
    </div>

    {{-- Détail secteur --}}
    <div class="hud-panel" id="secteur-detail" style="min-height:200px;">
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;color:var(--text-muted);text-align:center;">
            <div style="font-size:28px;margin-bottom:12px;">✦</div>
            <div style="font-family:var(--mono);font-size:11px;">Cliquez sur une étoile (★) dans la carte<br>pour afficher les détails du secteur</div>
        </div>
    </div>
</div>
@endsection

@push('hud-scripts')
<script>
function updateCoordDisplay(x, y, z, cellContent) {
    const display = document.getElementById('coord-hover-display');
    let desc = cellContent === '*'
        ? '<span style="color:var(--warning);">★ Système découvert</span>'
        : '<span style="color:var(--text-muted);">○ Zone inexplorée</span>';
    display.innerHTML = `(${x}, ${y}, ${z}) | ${desc}`;
}

function navigateToCoords() {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    const plan = '{{ $plan }}';
    window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
}

function changePlan(newPlan) {
    const x = document.getElementById('coord-x').value;
    const y = document.getElementById('coord-y').value;
    const z = document.getElementById('coord-z').value;
    window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${newPlan}`;
}

function clickCellSimple(x, y, z, element) {
    if (element.dataset.isSystem === 'true') {
        loadSecteurDetail(element.dataset.secteurX, element.dataset.secteurY, element.dataset.secteurZ);
    }
}

function clickCell(x, y, z, element) {
    const plan = '{{ $plan }}';
    if (element.dataset.isSystem === 'true') {
        loadSecteurDetail(element.dataset.secteurX, element.dataset.secteurY, element.dataset.secteurZ);
    }
    window.location.href = `/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
}

function loadSecteurDetail(x, y, z) {
    const detailDiv = document.getElementById('secteur-detail');
    detailDiv.innerHTML = '<div style="padding:40px;text-align:center;font-family:var(--mono);font-size:11px;color:var(--data);">Chargement...</div>';

    fetch(`/carte/secteur/${x}/${y}/${z}`)
        .then(response => response.text())
        .then(html => {
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const scripts = temp.querySelectorAll('script');
            const scriptContents = [];
            scripts.forEach(script => {
                scriptContents.push(script.textContent);
                script.remove();
            });
            detailDiv.innerHTML = temp.innerHTML;
            scriptContents.forEach(scriptContent => {
                try {
                    const scriptEl = document.createElement('script');
                    scriptEl.textContent = scriptContent;
                    document.body.appendChild(scriptEl);
                    document.body.removeChild(scriptEl);
                } catch (e) {
                    console.error('Error executing script:', e);
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            detailDiv.innerHTML = '<div style="padding:40px;text-align:center;color:var(--danger);font-family:var(--mono);font-size:11px;">Erreur de chargement</div>';
        });
}

document.addEventListener('keydown', function(e) {
    if (!e.shiftKey) return;
    const x = parseInt(document.getElementById('coord-x').value);
    const y = parseInt(document.getElementById('coord-y').value);
    const z = parseInt(document.getElementById('coord-z').value);
    const plan = '{{ $plan }}';
    let newX = x, newY = y, newZ = z;

    if (e.key === 'ArrowUp') {
        if (plan === 'Z') newY += 10; else newZ += 10;
    } else if (e.key === 'ArrowDown') {
        if (plan === 'Z') newY -= 10; else newZ -= 10;
    } else if (e.key === 'ArrowLeft') {
        if (plan !== 'X') newX -= 10; else newY -= 10;
    } else if (e.key === 'ArrowRight') {
        if (plan !== 'X') newX += 10; else newY += 10;
    } else {
        return;
    }
    e.preventDefault();
    window.location.href = `/carte?x=${newX}&y=${newY}&z=${newZ}&plan=${plan}`;
});

// ===== CANVAS MAP =====
function drawGraphicMap() {
    const canvas = document.getElementById('carte-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const cellSize = 28;
    const halfSize = {{ $halfSize }};
    const plan = '{{ $plan }}';
    const centerX = {{ $centerX }};
    const centerY = {{ $centerY }};
    const centerZ = {{ $centerZ }};
    const gridSize = halfSize * 2;

    const knownSectors = {!! json_encode($knownSectors ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    const grille = {!! json_encode($grille ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    canvas.width = gridSize * cellSize;
    canvas.height = gridSize * cellSize;

    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    function seededRandom(seed) {
        const x = Math.sin(seed) * 10000;
        return x - Math.floor(x);
    }

    const seed = centerX * 1000 + centerY * 100 + centerZ * 10;

    for (let v = halfSize - 1; v >= -halfSize; v--) {
        for (let h = -halfSize; h < halfSize; h++) {
            let absX, absY, absZ;

            if (plan === 'Z') {
                absX = centerX + h; absY = centerY + v; absZ = centerZ;
            } else if (plan === 'Y') {
                absX = centerX + h; absY = centerY; absZ = centerZ + v;
            } else {
                absX = centerX; absY = centerY + h; absZ = centerZ + v;
            }

            const key = `${absX},${absY},${absZ}`;
            const isKnown = knownSectors.hasOwnProperty(key);
            const hasSystem = grille[absX] && grille[absX][absY] && grille[absX][absY][absZ];

            const canvasX = (h + halfSize) * cellSize;
            const canvasY = (halfSize - 1 - v) * cellSize;

            if (!isKnown) {
                ctx.fillStyle = '#2a2a35';
                ctx.fillRect(canvasX, canvasY, cellSize, cellSize);
                ctx.fillStyle = 'rgba(180,180,190,0.3)';
                for (let i = 0; i < 3; i++) {
                    const sx = canvasX + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 7) * cellSize;
                    const sy = canvasY + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 11) * cellSize;
                    ctx.fillRect(sx, sy, 1, 1);
                }
            } else if (hasSystem) {
                ctx.fillStyle = '#000000';
                ctx.fillRect(canvasX, canvasY, cellSize, cellSize);
                ctx.fillStyle = 'rgba(255,255,255,0.5)';
                for (let i = 0; i < 5; i++) {
                    const sx = canvasX + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 7) * cellSize;
                    const sy = canvasY + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 11) * cellSize;
                    const size = seededRandom(seed + absX + absY + absZ + i * 13) > 0.8 ? 2 : 1;
                    ctx.fillRect(sx, sy, size, size);
                }
                ctx.fillStyle = '#fbbf24';
                ctx.font = 'bold 18px monospace';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('★', canvasX + cellSize / 2, canvasY + cellSize / 2);
            } else {
                ctx.fillStyle = '#0a0a0f';
                ctx.fillRect(canvasX, canvasY, cellSize, cellSize);
                ctx.fillStyle = 'rgba(255,255,255,0.4)';
                for (let i = 0; i < 4; i++) {
                    const sx = canvasX + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 7) * cellSize;
                    const sy = canvasY + seededRandom(seed + absX * 100 + absY * 10 + absZ + i * 11) * cellSize;
                    ctx.fillRect(sx, sy, 1, 1);
                }
            }

            if ((absX % 5 === 0) || (absY % 5 === 0) || (absZ % 5 === 0)) {
                ctx.strokeStyle = 'rgba(74,158,255,0.1)';
                ctx.lineWidth = 1;
                ctx.strokeRect(canvasX, canvasY, cellSize, cellSize);
            }
        }
    }

    // Position joueur
    const playerPos = {!! json_encode(['x' => $positionActuelle['x'] ?? $centerX, 'y' => $positionActuelle['y'] ?? $centerY, 'z' => $positionActuelle['z'] ?? $centerZ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    let playerH = 0, playerV = 0;

    if (plan === 'Z') { playerH = playerPos.x - centerX; playerV = playerPos.y - centerY; }
    else if (plan === 'Y') { playerH = playerPos.x - centerX; playerV = playerPos.z - centerZ; }
    else { playerH = playerPos.y - centerY; playerV = playerPos.z - centerZ; }

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

    function canvasToCoords(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const mouseX = (e.clientX - rect.left) * scaleX;
        const mouseY = (e.clientY - rect.top) * scaleY;
        const h = Math.floor(mouseX / cellSize) - halfSize;
        const v = halfSize - 1 - Math.floor(mouseY / cellSize);
        let absX, absY, absZ;
        if (plan === 'Z') { absX = centerX + h; absY = centerY + v; absZ = centerZ; }
        else if (plan === 'Y') { absX = centerX + h; absY = centerY; absZ = centerZ + v; }
        else { absX = centerX; absY = centerY + h; absZ = centerZ + v; }
        return { absX, absY, absZ };
    }

    canvas.addEventListener('mousemove', function(e) {
        const { absX, absY, absZ } = canvasToCoords(e);
        const hasSystem = grille[absX] && grille[absX][absY] && grille[absX][absY][absZ];
        updateCoordDisplay(absX, absY, absZ, hasSystem ? '*' : '·');
    });

    canvas.addEventListener('click', function(e) {
        const { absX, absY, absZ } = canvasToCoords(e);
        if (grille[absX] && grille[absX][absY] && grille[absX][absY][absZ]) {
            loadSecteurDetail(absX, absY, absZ);
        }
    });

    canvas.addEventListener('dblclick', function(e) {
        const { absX, absY, absZ } = canvasToCoords(e);
        window.location.href = `/carte?x=${absX}&y=${absY}&z=${absZ}&plan=${plan}`;
    });

    canvas.addEventListener('mouseleave', function() {
        const display = document.getElementById('coord-hover-display');
        if (display) display.innerHTML = 'Survolez la carte';
    });
}

window.addEventListener('load', function() {
    drawGraphicMap();
    setupCanvasInteractions();
});
</script>
@endpush
