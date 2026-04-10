{{-- Visualisation SVG du système stellaire - Adaptation de game/carte-secteur.blade.php --}}
<div class="bg-black border border-gray-600 rounded p-2 relative flex flex-col" style="height: 600px; max-height: 600px;">
    <svg id="systeme-map-{{ $systeme->id }}" width="100%" height="100%" viewBox="0 0 600 600" class="systeme-svg flex-1" style="max-height: 550px;">
        <!-- Grille de fond -->
        <defs>
            <pattern id="grid-{{ $systeme->id }}" width="60" height="60" patternUnits="userSpaceOnUse">
                <path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(100,100,100,0.2)" stroke-width="1"/>
            </pattern>
        </defs>
        <rect width="600" height="600" fill="url(#grid-{{ $systeme->id }})"/>

        <!-- Axes de référence -->
        <line x1="300" y1="0" x2="300" y2="600" stroke="rgba(100,150,200,0.3)" stroke-width="1" stroke-dasharray="5,5"/>
        <line x1="0" y1="300" x2="600" y2="300" stroke="rgba(100,150,200,0.3)" stroke-width="1" stroke-dasharray="5,5"/>

        <!-- Texte d'échelle -->
        <text x="10" y="20" fill="rgba(200,200,200,0.7)" font-size="12">{{ $systeme->nom_commun ?? $systeme->nom }}</text>
        <text x="10" y="590" fill="rgba(200,200,200,0.5)" font-size="10">
            Système stellaire | Échelle: distances en Unités Astronomiques (UA)
        </text>

        @php
            // Centrage du système
            $centerX = 300;
            $centerY = 300;

            // Calculer l'échelle basée sur la planète la plus éloignée
            $planetesPlusEloignee = $systeme->planetes->sortByDesc('distance_etoile')->first();
            $indexMax = max($systeme->planetes->count() - 1, 1);
            $radiusMaxPx = 60 + ($indexMax * 35);

            if ($planetesPlusEloignee && $radiusMaxPx > 0) {
                // distance_etoile est en cUA, donc diviser par 100 pour obtenir UA
                $scaleUA = (($planetesPlusEloignee->distance_etoile / 100) / $radiusMaxPx) * 60;
            } else {
                $scaleUA = 10;
            }

            $sysX = $centerX;
            $sysY = $centerY;
        @endphp

        <!-- Système stellaire (étoile) au centre -->
        <circle cx="{{ $sysX }}" cy="{{ $sysY }}" r="10" fill="yellow" stroke="orange" stroke-width="2">
            <animate attributeName="r" values="10;12;10" dur="2s" repeatCount="indefinite"/>
        </circle>
        <text x="{{ $sysX }}" y="{{ $sysY - 18 }}" fill="yellow" font-size="16" text-anchor="middle" font-weight="bold">☉</text>
        <text x="{{ $sysX }}" y="{{ $sysY + 28 }}" fill="white" font-size="11" text-anchor="middle" font-weight="bold">{{ $systeme->type_etoile }}</text>

        <!-- Planètes en orbite autour du système -->
        @foreach($systeme->planetes as $index => $planete)
            @php
                // Disposer les planètes en cercle autour du système
                $angle = ($index / max($systeme->planetes->count(), 1)) * 2 * M_PI;
                $orbitRadius = 60 + ($index * 35);
                $planetX = $sysX + cos($angle) * $orbitRadius;
                $planetY = $sysY + sin($angle) * $orbitRadius;

                // Couleur selon le type de planète
                $planetColors = [
                    'terrestre' => '#8B4513',
                    'tellurique' => '#8B4513',
                    'gazeuse' => '#4169E1',
                    'glacee' => '#87CEEB',
                    'oceanique' => '#1E90FF',
                    'desertique' => '#DEB887',
                    'volcanique' => '#FF4500',
                ];
                $planetColor = $planetColors[$planete->type] ?? '#808080';
            @endphp

            <!-- Orbite -->
            <circle cx="{{ $sysX }}" cy="{{ $sysY }}" r="{{ $orbitRadius }}"
                    fill="none" stroke="rgba(100,100,100,0.3)" stroke-width="1" stroke-dasharray="3,3"/>

            <!-- Planète -->
            <a href="{{ route('admin.planetes.show', $planete->id) }}" target="_blank">
                <circle cx="{{ $planetX }}" cy="{{ $planetY }}" r="6" fill="{{ $planetColor }}" stroke="white" stroke-width="1.5"
                        class="planet-clickable" style="cursor: pointer;"
                        onmouseover="this.setAttribute('r', 8)"
                        onmouseout="this.setAttribute('r', 6)"/>
                <text x="{{ $planetX }}" y="{{ $planetY - 12 }}" fill="white" font-size="9" text-anchor="middle"
                      style="pointer-events: none;">{{ $planete->nom }}</text>
                <text x="{{ $planetX }}" y="{{ $planetY + 18 }}" fill="rgba(200,200,200,0.6)" font-size="7" text-anchor="middle"
                      style="pointer-events: none;">{{ number_format($planete->distance_etoile / 100, 2) }} UA</text>

                @if($planete->source_nasa_exoplanet)
                    <text x="{{ $planetX }}" y="{{ $planetY + 28 }}" fill="lime" font-size="10" text-anchor="middle"
                          style="pointer-events: none;" title="Exoplanète NASA">🪐</text>
                @endif
            </a>
        @endforeach

        <!-- Étiquettes des axes -->
        <text x="590" y="295" fill="rgba(200,200,200,0.7)" font-size="11">X+</text>
        <text x="305" y="15" fill="rgba(200,200,200,0.7)" font-size="11">Y+</text>

        <!-- Échelle approximative -->
        <line x1="20" y1="570" x2="80" y2="570" stroke="white" stroke-width="2"/>
        <text x="50" y="565" fill="white" font-size="9" text-anchor="middle">≈ {{ number_format($scaleUA, 1) }} UA</text>
        <line x1="20" y1="572" x2="20" y2="568" stroke="white" stroke-width="1"/>
        <line x1="80" y1="572" x2="80" y2="568" stroke="white" stroke-width="1"/>
    </svg>

    <!-- Contrôles de zoom -->
    <div class="absolute bottom-4 right-4 flex flex-col gap-1 bg-gray-900/80 border border-gray-600 rounded p-1">
        <button onclick="zoomIn_{{ $systeme->id }}()"
                class="w-8 h-8 bg-cyan-600 hover:bg-cyan-700 text-white rounded text-sm font-bold"
                title="Zoom avant (+10%)">
            +
        </button>
        <button onclick="zoomOut_{{ $systeme->id }}()"
                class="w-8 h-8 bg-cyan-600 hover:bg-cyan-700 text-white rounded text-sm font-bold"
                title="Zoom arrière (-10%)">
            −
        </button>
        <button onclick="resetZoom_{{ $systeme->id }}()"
                class="w-8 h-8 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-xs font-bold"
                title="Réinitialiser la vue">
            R
        </button>
        <div id="zoom-level-{{ $systeme->id }}" class="text-xs text-center text-cyan-400 mt-1">
            100%
        </div>
    </div>

    <div class="text-xs text-gray-500 text-center mt-2">
        🪐 Cliquez sur une planète pour voir ses détails
    </div>
</div>

<script>
// État du zoom pour ce système spécifique
(function() {
    const systemeId = '{{ $systeme->id }}';
    const state = {
        scale: 1.0,
        centerX: 300,
        centerY: 300,
        viewBoxWidth: 600,
        viewBoxHeight: 600
    };

    function updateViewBox() {
        const svg = document.getElementById('systeme-map-' + systemeId);
        if (!svg) return;

        const width = state.viewBoxWidth / state.scale;
        const height = state.viewBoxHeight / state.scale;
        const x = state.centerX - (width / 2);
        const y = state.centerY - (height / 2);

        svg.setAttribute('viewBox', x + ' ' + y + ' ' + width + ' ' + height);

        const zoomPercent = Math.round(state.scale * 100);
        const zoomLevelEl = document.getElementById('zoom-level-' + systemeId);
        if (zoomLevelEl) {
            zoomLevelEl.textContent = zoomPercent + '%';
        }
    }

    window['zoomIn_' + systemeId] = function() {
        state.scale = Math.min(state.scale * 1.1, 10);
        updateViewBox();
    };

    window['zoomOut_' + systemeId] = function() {
        state.scale = Math.max(state.scale / 1.1, 0.5);
        updateViewBox();
    };

    window['resetZoom_' + systemeId] = function() {
        state.scale = 1.0;
        state.centerX = 300;
        state.centerY = 300;
        updateViewBox();
    };
})();
</script>
