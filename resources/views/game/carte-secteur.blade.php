@if($systemes->count() > 0)
    @foreach($systemes as $systeme)
    <div class="mb-4">
        <!-- Informations du système -->
        <div class="mb-3 p-2 bg-gray-900/50 rounded">
            <h3 class="text-lg font-bold text-yellow-400 mb-1">
                {{ $systeme->nom }}
            </h3>
            <div class="flex gap-3 text-xs text-gray-400">
                <div>Type: <span class="text-white">{{ $systeme->type_etoile }}</span></div>
                <div>Planètes: <span class="text-green-400">{{ $systeme->planetes->count() }}</span></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                Secteur ({{ $x }}, {{ $y }}, {{ $z }}) | Position: ({{ number_format($systeme->position_x, 2) }}, {{ number_format($systeme->position_y, 2) }}, {{ number_format($systeme->position_z, 2) }}) AL
            </div>
        </div>

        <!-- Carte visuelle du système -->
        <div class="bg-black border border-gray-600 rounded p-2 relative">
            <svg id="sector-map-{{ $systeme->id }}" width="100%" height="500" viewBox="0 0 600 600" class="sector-svg">
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
                <text x="10" y="20" fill="rgba(200,200,200,0.7)" font-size="12">Secteur ({{ $x }}, {{ $y }}, {{ $z }})</text>
                <text x="10" y="590" fill="rgba(200,200,200,0.5)" font-size="10">
                    Système au centre | Échelle: distances en Unités Astronomiques (UA)
                </text>

                @php
                    // Convertir la position du système en coordonnées SVG
                    // Le système est centré au milieu du SVG (300, 300)
                    $centerX = 300;
                    $centerY = 300;

                    // Calculer l'échelle basée sur la planète la plus éloignée
                    // Espacement visuel: 60px + (index * 35px)
                    // Pour calculer l'échelle réelle en UA
                    $planetesPlusEloignee = $systeme->planetes->sortByDesc('distance_etoile')->first();
                    $indexMax = $systeme->planetes->count() - 1;
                    $radiusMaxPx = 60 + ($indexMax * 35); // Position visuelle de la planète la plus éloignée

                    if ($planetesPlusEloignee && $radiusMaxPx > 0) {
                        // Calculer combien de UA correspondent à 60 pixels (taille de la barre d'échelle)
                        $scaleUA = ($planetesPlusEloignee->distance_etoile / $radiusMaxPx) * 60;
                    } else {
                        $scaleUA = 10; // Valeur par défaut
                    }

                    // Placer le système au centre du SVG
                    $sysX = $centerX;
                    $sysY = $centerY;
                @endphp

                <!-- Système stellaire (étoile) au centre -->
                <circle cx="{{ $sysX }}" cy="{{ $sysY }}" r="10" fill="yellow" stroke="orange" stroke-width="2">
                    <animate attributeName="r" values="10;12;10" dur="2s" repeatCount="indefinite"/>
                </circle>
                <text x="{{ $sysX }}" y="{{ $sysY - 18 }}" fill="yellow" font-size="16" text-anchor="middle" font-weight="bold">☉</text>
                <text x="{{ $sysX }}" y="{{ $sysY + 28 }}" fill="white" font-size="11" text-anchor="middle" font-weight="bold">{{ $systeme->nom }}</text>

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
                    <circle cx="{{ $planetX }}" cy="{{ $planetY }}" r="6" fill="{{ $planetColor }}" stroke="white" stroke-width="1.5"
                            class="planet-clickable" style="cursor: pointer;"
                            onclick="zoomToPlanet_{{ $systeme->id }}({{ $planetX }}, {{ $planetY }})"
                            onmouseover="this.setAttribute('r', 8)"
                            onmouseout="this.setAttribute('r', 6)"/>
                    <text x="{{ $planetX }}" y="{{ $planetY - 12 }}" fill="white" font-size="9" text-anchor="middle"
                          style="pointer-events: none;">{{ $planete->nom }}</text>
                    <text x="{{ $planetX }}" y="{{ $planetY + 18 }}" fill="rgba(200,200,200,0.6)" font-size="7" text-anchor="middle"
                          style="pointer-events: none;">{{ number_format($planete->distance_etoile, 1) }} UA</text>

                    @if($planete->accessible)
                        <text x="{{ $planetX }}" y="{{ $planetY + 28 }}" fill="lime" font-size="10" text-anchor="middle"
                              style="pointer-events: none;">✓</text>
                    @endif
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

            <!-- Contrôles de zoom (en bas à droite du SVG) -->
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
                const svg = document.getElementById('sector-map-' + systemeId);
                if (!svg) return;

                // Calculer les dimensions du viewBox en fonction du zoom
                const width = state.viewBoxWidth / state.scale;
                const height = state.viewBoxHeight / state.scale;

                // Calculer les coordonnées du coin supérieur gauche pour centrer sur centerX, centerY
                const x = state.centerX - (width / 2);
                const y = state.centerY - (height / 2);

                svg.setAttribute('viewBox', x + ' ' + y + ' ' + width + ' ' + height);

                // Mettre à jour l'affichage du niveau de zoom
                const zoomPercent = Math.round(state.scale * 100);
                const zoomLevelEl = document.getElementById('zoom-level-' + systemeId);
                if (zoomLevelEl) {
                    zoomLevelEl.textContent = zoomPercent + '%';
                }
            }

            window['zoomIn_' + systemeId] = function() {
                state.scale = Math.min(state.scale * 1.1, 10); // Max 10x
                updateViewBox();
            };

            window['zoomOut_' + systemeId] = function() {
                state.scale = Math.max(state.scale / 1.1, 0.5); // Min 0.5x
                updateViewBox();
            };

            window['resetZoom_' + systemeId] = function() {
                state.scale = 1.0;
                state.centerX = 300;
                state.centerY = 300;
                updateViewBox();
            };

            window['zoomToPlanet_' + systemeId] = function(planetX, planetY) {
                // Centrer sur la planète
                state.centerX = planetX;
                state.centerY = planetY;

                // Zoomer à 200% (2x)
                state.scale = 2.0;

                updateViewBox();
            };
        })();
        </script>

        <!-- Liste des planètes avec détails complets -->
        @if($systeme->planetes->count() > 0)
        <div class="mt-3">
            <h4 class="text-xs font-bold text-gray-400 mb-2">Planètes du système ({{ $systeme->planetes->count() }}):</h4>
            <div class="space-y-2">
                @foreach($systeme->planetes as $planete)
                @php
                    // Accéder aux relations via getRelation pour éviter conflit avec attributs
                    try {
                        $gisementsRelation = $planete->getRelation('gisements');
                    } catch (\Exception $e) {
                        $gisementsRelation = collect();
                    }
                    if (!$gisementsRelation) {
                        $gisementsRelation = collect();
                    }

                    try {
                        $stationsRelation = $planete->getRelation('stations');
                    } catch (\Exception $e) {
                        $stationsRelation = collect();
                    }
                    if (!$stationsRelation) {
                        $stationsRelation = collect();
                    }
                @endphp
                <div class="bg-gray-900/50 border border-gray-700 rounded p-3 text-xs hover:border-cyan-500 transition-colors">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-bold text-cyan-400 text-sm">
                            {{ $planete->nom }}
                            @if($planete->accessible)
                                <span class="text-green-400 ml-1">✓</span>
                            @endif
                            @if($planete->habitable)
                                <span class="text-blue-400 ml-1">🌍</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-gray-400 mb-2">
                        <div><span class="text-gray-500">Type:</span> {{ ucfirst($planete->type) }}</div>
                        <div><span class="text-gray-500">Distance:</span> {{ number_format($planete->distance_etoile, 2) }} UA</div>
                        <div><span class="text-gray-500">Rayon:</span> {{ number_format($planete->rayon, 2) }} R⊕</div>
                        <div><span class="text-gray-500">Masse:</span> {{ number_format($planete->masse, 2) }} M⊕</div>
                        <div><span class="text-gray-500">Gravité:</span> {{ number_format($planete->gravite, 2) }} g</div>
                        <div><span class="text-gray-500">Température:</span> {{ $planete->temperature_moyenne }}°C</div>
                    </div>

                    @if($planete->a_atmosphere)
                    <div class="text-gray-400 mb-2">
                        <span class="text-gray-500">Atmosphère:</span> {{ $planete->composition_atmosphere ?? 'Inconnue' }}
                    </div>
                    @endif

                    @if($planete->habitee)
                    <div class="text-green-400 mb-2">
                        <span class="text-gray-500">Population:</span> {{ number_format($planete->population) }} habitants
                    </div>
                    @endif

                    <!-- Gisements de la planète -->
                    @if($gisementsRelation->count() > 0)
                    <div class="mt-2 pt-2 border-t border-gray-700">
                        <div class="text-gray-500 text-xs mb-1">Gisements ({{ $gisementsRelation->count() }}):</div>
                        <div class="grid grid-cols-2 gap-1">
                            @foreach($gisementsRelation as $gisement)
                            <div class="bg-gray-800/50 rounded px-2 py-1 text-xs">
                                <span class="text-yellow-400">{{ $gisement->ressource->nom }}</span>
                                <span class="text-gray-500">x{{ $gisement->quantite }}</span>
                                @if($gisement->qualite)
                                <span class="text-cyan-400">(Q{{ $gisement->qualite }})</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="text-gray-600 text-xs italic">Aucun gisement détecté</div>
                    @endif

                    <!-- Stations orbitales -->
                    @if($stationsRelation->count() > 0)
                    <div class="mt-2 pt-2 border-t border-gray-700">
                        <div class="text-gray-500 text-xs mb-1">Stations orbitales ({{ $stationsRelation->count() }}):</div>
                        <div class="space-y-1">
                            @foreach($stationsRelation as $station)
                            <div class="bg-gray-800/50 rounded px-2 py-1 text-xs text-cyan-400">
                                {{ $station->nom }} - {{ $station->type }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
@else
    <div class="text-center p-8">
        <div class="text-gray-400 text-lg mb-2">Secteur inexploré</div>
        <div class="text-gray-500 text-sm">Vous n'avez pas encore découvert de système dans le secteur ({{ $x }}, {{ $y }}, {{ $z }})</div>
    </div>
@endif
