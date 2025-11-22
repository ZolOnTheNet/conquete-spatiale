@if($systemes->count() > 0)
    @foreach($systemes as $systeme)
    <div class="mb-4">
        <!-- Informations du système -->
        <div class="mb-3 p-2 bg-gray-900/50 rounded">
            <h3 class="text-lg font-bold text-yellow-400 mb-1">
                <a href="{{ route('admin.univers.show', $systeme->id) }}" class="hover:text-yellow-300">
                    {{ $systeme->nom }}
                </a>
            </h3>
            <div class="flex gap-3 text-xs text-gray-400">
                <div>Type: <span class="text-white">{{ $systeme->type_etoile }}</span></div>
                <div>Puissance: <span class="text-cyan-400">{{ $systeme->puissance }}</span></div>
                <div>Planètes: <span class="text-green-400">{{ $systeme->planetes->count() }}</span></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                Secteur ({{ $x }}, {{ $y }}, {{ $z }}) | Position intra-secteur: ({{ number_format($systeme->position_x, 2) }}, {{ number_format($systeme->position_y, 2) }}, {{ number_format($systeme->position_z, 2) }}) AL
            </div>
        </div>

        <!-- Carte visuelle du système -->
        <div class="bg-black border border-gray-600 rounded p-2">
            <svg width="100%" height="500" viewBox="0 0 600 600">
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
                    <circle cx="{{ $planetX }}" cy="{{ $planetY }}" r="6" fill="{{ $planetColor }}" stroke="white" stroke-width="1.5"/>
                    <text x="{{ $planetX }}" y="{{ $planetY - 12 }}" fill="white" font-size="9" text-anchor="middle">{{ $planete->nom }}</text>
                    <text x="{{ $planetX }}" y="{{ $planetY + 18 }}" fill="rgba(200,200,200,0.6)" font-size="7" text-anchor="middle">{{ number_format($planete->distance_etoile, 1) }} UA</text>

                    @if($planete->accessible)
                        <text x="{{ $planetX }}" y="{{ $planetY + 28 }}" fill="lime" font-size="10" text-anchor="middle">✓</text>
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
        </div>

        <!-- Liste des planètes -->
        @if($systeme->planetes->count() > 0)
        <div class="mt-3">
            <h4 class="text-xs font-bold text-gray-400 mb-2">Planètes du système:</h4>
            <div class="grid grid-cols-2 gap-2">
                @foreach($systeme->planetes as $planete)
                <div class="bg-gray-900/50 border border-gray-700 rounded p-2 text-xs">
                    <div class="font-bold text-cyan-400">{{ $planete->nom }}</div>
                    <div class="text-gray-400">{{ ucfirst($planete->type) }}</div>
                    <div class="text-gray-500">{{ number_format($planete->distance_etoile, 2) }} UA | R:{{ number_format($planete->rayon, 1) }} M:{{ number_format($planete->masse, 1) }}</div>
                    @if($planete->accessible)
                        <div class="text-green-400 text-xs">✓ Accessible</div>
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
        <div class="text-gray-400 text-lg mb-2">Secteur vide</div>
        <div class="text-gray-500 text-sm">Aucun système stellaire dans le secteur ({{ $x }}, {{ $y }}, {{ $z }})</div>
    </div>
@endif
