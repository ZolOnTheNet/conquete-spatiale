@extends('layouts.app')

@section('title', 'Admin - Carte de l\'Univers')

@section('content')
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

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-auto">
            <!-- Contrôles de navigation -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-4 mb-4">
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
                        <label class="text-gray-400 text-sm">Plan d'affichage:</label>
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
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <div><span class="text-yellow-400 font-bold">*</span> = Système stellaire</div>
                    <div><span class="text-gray-600">::</span> = Obstacles</div>
                    <div><span class="text-gray-700">?</span> = Inconnu</div>
                    <div><span class="text-gray-900">·</span> = Vide</div>
                </div>
            </div>

            <!-- Carte -->
            <div class="bg-black border border-gray-700 rounded-lg p-2 inline-block">
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
                                        // Vérifier si le système est proche de cette coordonnée AL
                                        $sysAbsX = $systeme->secteur_x * 10 + $systeme->position_x;
                                        $sysAbsY = $systeme->secteur_y * 10 + $systeme->position_y;
                                        $sysAbsZ = $systeme->secteur_z * 10 + $systeme->position_z;

                                        // Afficher * si le système est à moins de 0.5 AL de cette position
                                        $distance = sqrt(
                                            pow($sysAbsX - $absX, 2) +
                                            pow($sysAbsY - $absY, 2) +
                                            pow($sysAbsZ - $absZ, 2)
                                        );

                                        if ($distance < 1) {
                                            $cellContent = '*';
                                            $cellClass = 'text-yellow-400 cursor-pointer hover:bg-yellow-900/30';
                                            $cellTitle = $systeme->nom;
                                        } else {
                                            $cellContent = '·';
                                            $cellClass = 'text-gray-900 cursor-pointer hover:bg-gray-800';
                                            $cellTitle = "AL: {$absX}, {$absY}, {$absZ}";
                                        }
                                    } else {
                                        $cellContent = '·';
                                        $cellClass = 'text-gray-900 cursor-pointer hover:bg-gray-800';
                                        $cellTitle = "AL: {$absX}, {$absY}, {$absZ}";
                                    }
                                @endphp
                                <span class="{{ $cellClass }}"
                                      onclick="clickCell({{ $absX }}, {{ $absY }}, {{ $absZ }})"
                                      title="{{ $cellTitle }}">{{ $cellContent }}</span>
                            @endfor
                        </div>
                    @endfor
                </div>

                <!-- Axe X en bas -->
                <div class="text-xs text-gray-600 mt-1 text-center">
                    {{ $centerX - $halfSize }} ← {{ $hAxisLabel }} → {{ $centerX + $halfSize }} AL
                </div>
            </div>
        </main>
    </div>
</div>

<script>
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
function clickCell(x, y, z) {
    const plan = '{{ $plan }}';
    window.location.href = `/admin/carte?x=${x}&y=${y}&z=${z}&plan=${plan}`;
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
