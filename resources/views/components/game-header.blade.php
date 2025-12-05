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
@endphp

<header class="game-header">
    <div class="header-columns">

        {{-- COLONNE 1 : JOUEUR --}}
        <div class="header-column header-player">
            <div class="column-title">👤 JOUEUR</div>
            <div class="player-name">{{ $personnage->nom }}</div>
            <div class="player-credits">
                💰 {{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR
            </div>
            <div class="player-actions">
                ⚡ PA: {{ $personnage->points_action ?? 0 }}
            </div>
        </div>

        {{-- COLONNE 2 : SYSTÈME --}}
        <div class="header-column header-system">
            <div class="column-title">📍 SYSTÈME</div>

            @if($objetSpatial)
            {{-- Bloc Position --}}
            <div class="system-position">
                <div class="system-coords">
                    @if($systeme)
                        {{ $systeme->nom }}
                    @else
                        Espace profond
                    @endif
                    ({{ $objetSpatial->secteur_x }}, {{ $objetSpatial->secteur_y }}, {{ $objetSpatial->secteur_z }})
                </div>
            </div>

            {{-- Bloc Caractéristiques COMPACT (icônes + valeurs, détails en tooltip) --}}
            @if($systeme)
            <div class="system-stats-compact">
                <span class="stat-item" title="Puissance solaire: {{ $systeme->puissance_solaire ?? 50 }}/100">
                    ☀️ {{ $systeme->puissance_solaire ?? 50 }}
                </span>
                <span class="stat-item" title="Danger: Variable">
                    ☄️ 50
                </span>
                <span class="stat-item" title="Planètes et POI: {{ $systeme->nb_planetes ?? 0 }}">
                    🌍 {{ $systeme->nb_planetes ?? 0 }}
                </span>
            </div>
            @endif

            <div class="system-network">
                📡 {{ 'Aucun réseau' }}
            </div>
            @else
            <div class="text-gray-500 text-xs">Aucune position</div>
            @endif
        </div>

        {{-- COLONNE 3 : ICÔNE SECTEUR --}}
        <div class="header-column header-sector">
            <div class="column-title">🔷 SECTEUR</div>
            <div class="sector-icon">
                <span class="sector-visual" title="{{ $secteur ?? 'Secteur inconnu' }}">
                    🔷
                </span>
            </div>
        </div>

        {{-- COLONNE 4 : VAISSEAU --}}
        <div class="header-column header-ship">
            <div class="column-title">🚀 VAISSEAU</div>

            @if($vaisseau)
            <div class="ship-name">{{ $vaisseau->nom ?? 'Sans nom' }}</div>

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
            </div>

            <div class="ship-parts" title="Unitek - Pièces pour imprimante 3D">
                🔧 {{ number_format(0, 0, ',', ' ') }}
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
    padding: 0.75rem 1.5rem;
    font-family: 'Share Tech Mono', monospace;
    font-size: 0.85rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
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
    gap: 0.25rem;
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

.player-credits {
    color: #ffd700;
    font-size: 0.9rem;
}

.player-actions {
    color: #00ff88;
    font-size: 0.9rem;
}

/* ═══════════════════════════════════════════════════
   COLONNE 2 : SYSTÈME
   ═══════════════════════════════════════════════════ */

.system-coords {
    color: #ff6b9d;
    font-weight: bold;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
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

.system-network {
    color: #9370db;
    font-size: 0.85rem;
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
