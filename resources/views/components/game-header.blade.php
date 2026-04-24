{{-- Composant Header Jeu - 4 colonnes selon GDD --}}
@props(['personnage', 'vaisseau' => null, 'systeme' => null, 'secteur' => null])

@php
    // Récupérer l'objet spatial du vaisseau
    $objetSpatial = $vaisseau?->objetSpatial;

    // Calculer les pourcentages pour le vaisseau
    $energiePercent = $vaisseau && $vaisseau->reserve > 0
        ? round(($vaisseau->energie_actuelle / $vaisseau->reserve) * 100)
        : 0;
    $coquePercent = $vaisseau && $vaisseau->coque_max > 0
        ? round(($vaisseau->coque_actuelle / $vaisseau->coque_max) * 100)
        : 0;
    $bouclierPercent = $vaisseau && $vaisseau->bouclier_id
        ? round(($vaisseau->bouclier_actuel / 100) * 100)
        : 0;

    // Déterminer l'icône du secteur selon contenu
    // bg_A : astéroïdes seuls
    // bg_S : soleil seul
    // bg_SV : soleil + planètes
    // bg_V : mines/planètes seules
    // bg_O : vide (espace profond)
    $secteurIcon = 'bg_O.jpg'; // Par défaut : vide
    $secteurLabel = 'Espace vide';

    if ($systeme) {
        $nbPlanetes = $systeme->planetesPrimaires ? $systeme->planetesPrimaires->count() : 0;
        $hasAsteroids = false; // TODO: détecter astéroïdes

        if ($nbPlanetes > 0) {
            $secteurIcon = 'bg_SV.jpg'; // Soleil + planètes
            $secteurLabel = 'Système avec planètes';
        } else {
            $secteurIcon = 'bg_S.jpg'; // Soleil seul
            $secteurLabel = 'Système sans planètes';
        }
    }
@endphp

<header class="game-header">
    <div class="header-columns">

        {{-- COLONNE 1 : JOUEUR --}}
        <div class="header-column header-player">
            <div class="column-title">👤 JOUEUR</div>
            <div class="player-name">
                {{ $personnage->prenom ?? '' }} {{ $personnage->nom }}
            </div>
            <div class="player-stats">
                <span class="player-credits">💰 {{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR</span>
                <span class="player-actions ml-3">⚡ PA: {{ $personnage->points_action ?? 0 }}</span>
            </div>
        </div>

        {{-- COLONNE 2 : SYSTÈME --}}
        <div class="header-column header-system">
            <div class="column-title">📍 SYSTÈME</div>

            @if($objetSpatial)
                {{-- Position et Réseau sur la même ligne --}}
                <div class="system-coords">
                    @if($systeme)
                        {{ $systeme->nom }}
                    @else
                        Espace profond
                    @endif
                    ({{ $objetSpatial->secteur_x }}, {{ $objetSpatial->secteur_y }}, {{ $objetSpatial->secteur_z }})
                    <span class="system-network-inline">
                        @if($objetSpatial->secteur_x == 0 && $objetSpatial->secteur_y == 0 && $objetSpatial->secteur_z == 0)
                            📡 Sol
                        @else
                            📡 Aucun réseau
                        @endif
                    </span>
                </div>

                {{-- Bloc Caractéristiques COMPACT (icônes + valeurs, détails en tooltip) --}}
                <div class="system-stats-compact">
                    @if($systeme)
                        <span class="stat-item" title="Puissance solaire: {{ $systeme->puissance_solaire ?? 50 }}/100">
                            ☀️ {{ number_format($systeme->puissance_solaire ?? 50, 2) }}
                        </span>
                        <span class="stat-item" title="Danger: Variable">
                            ☄️ 50
                        </span>
                        <span class="stat-item" title="Planètes et POI: {{ $systeme->planetesPrimaires ? $systeme->planetesPrimaires->count() : 0 }}">
                            🌍 {{ $systeme->planetesPrimaires ? $systeme->planetesPrimaires->count() : 0 }}
                        </span>
                    @else
                        <span class="stat-item" title="Puissance solaire: 0/100">
                            ☀️ 0.00
                        </span>
                        <span class="stat-item" title="Danger: Variable">
                            ☄️ 0
                        </span>
                        <span class="stat-item" title="Planètes et POI: 0">
                            🌍 0
                        </span>
                    @endif
                </div>
            @else
                <div class="text-gray-500 text-xs">Aucune position</div>
            @endif
        </div>

        {{-- COLONNE 3 : ICÔNE SECTEUR --}}
        <div class="header-column header-sector">
            <div class="sector-icon">
                <img src="{{ asset('images/carte_icones/' . $secteurIcon) }}"
                     alt="{{ $secteurLabel }}"
                     title="{{ $secteurLabel }}"
                     class="sector-visual-img">
            </div>
        </div>

        {{-- COLONNE 4 : VAISSEAU --}}
        <div class="header-column header-ship">
            <div class="column-title">🚀 VAISSEAU</div>

            @if($vaisseau)
            <div class="ship-name">{{ $objetSpatial->nom ?? 'Sans nom' }}</div>

            {{-- Stats COMPACT (pourcentages uniquement, détails en tooltip) --}}
            <div class="ship-stats-compact">
                <span class="stat-item"
                      title="Énergie: {{ $vaisseau->energie_actuelle ?? 0 }}/{{ $vaisseau->reserve ?? 0 }}">
                    ⚡ {{ $energiePercent }}%
                </span>
                <span class="stat-item"
                      title="Structure: {{ $vaisseau->coque_actuelle ?? 0 }}/{{ $vaisseau->coque_max ?? 0 }}">
                    🛡️ {{ $coquePercent }}%
                </span>
                <span class="stat-item"
                      title="Bouclier: {{ $vaisseau->bouclier_actuel ?? 0 }}/100">
                    🔰 {{ $bouclierPercent }}%
                </span>
                <span class="ship-parts" title="Unitek - Pièces pour imprimante 3D">
                    🔧 {{ number_format(0, 0, ',', ' ') }}
                </span>

            </div>


                @if(false)
                <div class="ship-target">
                    🎯 → Cible
                </div>
                @endif
            @else
            <div class="text-gray-500 text-xs">Aucun vaisseau</div>
            @endif
        </div>

    </div>
</header>

<style>
/* En-tête principal */
.game-header {
    background: linear-gradient(135deg, #0f1419 0%, #1a1f2e 100%);
    border-bottom: 2px solid #4a9eff;
    padding: 0.5rem 1.5rem;
    font-family: 'Share Tech Mono', monospace;
    font-size: 0.85rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

/* Container des 4 colonnes */
.header-columns {
    display: grid;
    grid-template-columns: 1.2fr 1.5fr 0.8fr 1.5fr;
    gap: 1.5rem;
    color: #e0e0e0;
    align-items: start;
}

/* Style commun pour chaque colonne */
.header-column {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

/* Titres de colonnes */
.column-title {
    font-size: 0.75rem;
    color: #4a9eff;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.25rem;
    opacity: 0.8;
}

/* ═══════════════════════════════════════════════════
   COLONNE 1 : JOUEUR
   ═══════════════════════════════════════════════════ */

.player-name {
    color: #4a9eff;
    font-weight: bold;
    font-size: 1rem;
}

.player-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
}

.player-credits {
    color: #ffd700;
}

.player-actions {
    color: #00ff88;
}

/* ═══════════════════════════════════════════════════
   COLONNE 2 : SYSTÈME
   ═══════════════════════════════════════════════════ */

.system-coords {
    color: #ff6b9d;
    font-weight: bold;
    font-size: 0.85rem;
}

.system-network {
    color: #9370db;
    font-size: 0.75rem;
}

.system-network-inline {
    color: #9370db;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

/* Stats COMPACT : affichage en ligne avec tooltips */
.system-stats-compact {
    display: flex;
    gap: 0.75rem;
    font-size: 0.85rem;
}

.system-stats-compact .stat-item {
    cursor: help;
    transition: color 0.2s, transform 0.2s;
}

.system-stats-compact .stat-item:hover {
    color: #4a9eff;
    transform: scale(1.1);
}

/* ═══════════════════════════════════════════════════
   COLONNE 3 : SECTEUR
   ═══════════════════════════════════════════════════ */

.header-sector {
    display: flex;
    align-items: center;
    justify-content: center;
}

.sector-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.sector-visual {
    font-size: 2rem;
    cursor: help;
    transition: transform 0.2s;
}

.sector-visual:hover {
    transform: scale(1.15);
}

.sector-visual-img {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    border: 1px solid #4a9eff;
    cursor: help;
    transition: transform 0.2s, border-color 0.2s;
    object-fit: cover;
}

.sector-visual-img:hover {
    transform: scale(1.1);
    border-color: #6bb6ff;
}

/* ═══════════════════════════════════════════════════
   COLONNE 4 : VAISSEAU
   ═══════════════════════════════════════════════════ */

.ship-name {
    color: #4a9eff;
    font-weight: bold;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

/* Stats COMPACT : affichage en ligne avec tooltips */
.ship-stats-compact {
    display: flex;
    gap: 0.75rem;
    font-size: 0.85rem;
}

.ship-stats-compact .stat-item {
    cursor: help;
    transition: color 0.2s, transform 0.2s;
}

.ship-stats-compact .stat-item:hover {
    color: #4a9eff;
    transform: scale(1.05);
}

.ship-parts {
    color: #9e9e9e;
    font-size: 0.85rem;
    cursor: help;
}

.ship-target {
    color: #ff5252;
    margin-top: 0.25rem;
    font-weight: bold;
    font-size: 0.9rem;
}

/* Animation pour les valeurs qui changent */
@keyframes value-update {
    0% { color: #4a9eff; transform: scale(1.1); }
    100% { color: inherit; transform: scale(1); }
}

.value-updated {
    animation: value-update 0.5s ease;
}

/* ═══════════════════════════════════════════════════
   RESPONSIVE - MOBILE
   ═══════════════════════════════════════════════════ */

@media (max-width: 768px) {
    .game-header {
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    .header-columns {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .column-title {
        font-size: 0.7rem;
    }

    .player-name,
    .ship-name {
        font-size: 0.9rem;
    }

    .system-coords {
        font-size: 0.85rem;
    }

    .ship-stats-compact,
    .system-stats-compact {
        font-size: 0.75rem;
        gap: 0.5rem;
    }

    .sector-visual {
        font-size: 1.5rem;
    }
}
</style>
