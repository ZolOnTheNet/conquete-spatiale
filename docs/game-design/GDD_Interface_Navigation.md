# ? INTERFACE ET NAVIGATION
## Jeu de Conquête Galactique - Console Web

---

##  Table des Matières

1. [Vue d'Ensemble](#vue-densemble)
2. [En-tête de Jeu (Header)](#en-tête-de-jeu-header)
3. [Structure du Menu Principal](#structure-du-menu-principal)
4. [Menu Personnage](#menu-personnage)
5. [Menu Navire/Station](#menu-navirestation)
6. [Menu Jeu](#menu-jeu)
7. [Menu Admin](#menu-admin)
8. [Comparaison avec Lunastar](#comparaison-avec-lunastar)
9. [Notes d'Implémentation](#notes-dimplémentation)

---

## ? Vue d'Ensemble

### Inspiration

Le système de navigation s'inspire de **Lunastar** avec une structure de menu à trois niveaux :
- **Niveau 1** : Grandes catégories (non cliquables)
- **Niveau 2** : Sous-menus cliquables
- **Niveau 3** : Contenus et actions

### Principes

- **Navigation claire** : Menu toujours visible et structuré
- **Contextuel** : Le menu "Navire" devient "Station" selon le contexte
- **Cohérent** : Même structure que Lunastar mais avec notre terminologie
- **Extensible** : Ajout facile du menu Admin

---

##  En-tête de Jeu (Header)

L'en-tête du jeu affiche les informations essentielles en permanence, inspiré de **Lunastar**. Il occupe **moins de 3 lignes de hauteur** pour rester compact et informatif.

### Structure Visuelle

L'en-tête est organisé en **4 COLONNES** sur maximum 3 lignes de hauteur :

```
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
? [COL 1: JOUEUR]          [COL 2: POSITION/SYST?ME]  [COL 3: SECTEUR]  [COL 4: VAISSEAU]               ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
? Joueur                   x: 4, y: 2, z: 9                ?          USS Exploreur NCC-7609-C         ?
? NomDuJoueur (Position)   Vulcanus  ?  Sol                         ? 92%  œ 100%  ? 100% ? 136 650 ?
? 22 CR ? PA:24          œ 80  œ 70   14                           ? Cible: Vulcania               ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
```

**Note :** Les informations des colonnes 2 et 4 utilisent des tooltips pour afficher les détails complets au survol.

### Organisation des Informations

L'en-tête est divisé en **4 COLONNES** :

####  COLONNE 1 : Informations Joueur (Gauche)

**Sur 3 lignes verticales :**
1. **Nom du joueur**
2. ? **Vos Crédits** : 22 749 332 CR (formaté avec espaces)
3. ? **Vos Points d'actions** : 24

####  COLONNE 2 : Position & Système Stellaire (Centre-gauche)

**Bloc supérieur - Position :**
1. **Coordonnées** : x: 4, y: 2, z: 9
2. **Nom du système** : Vulcanus

**Bloc inférieur - Caractéristiques système (icônes uniquement, détails en tooltip) :**
3. œ **Puissance** : 80 (tooltip: "Puissance solaire: 80/100")
4. œ **Astéroïdes** : 70 (tooltip: "Danger astéroïdes: 70/100")
5.  **POI** : 14 (tooltip: "Planètes et points d'intérêt: 14")
6. ? **Réseau porteur** : Système Solaire

**Note :** Les 3 premières valeurs (Puissance, Astéroïdes, POI) sont affichées sur **une seule ligne** avec uniquement les icônes et valeurs chiffrées.

#### ? COLONNE 3 : Icône Secteur (Centre-droit)

- **Icône visuelle du secteur actuel** (à détailler ultérieurement)
- Représentation graphique distinctive du type de secteur

####  COLONNE 4 : Vaisseau (Droite)

**Ligne 1 :**
- **Nom du vaisseau** : USS Exploreur NCC-7609-C

**Ligne 2 - ?tats en pourcentages (icônes uniquement, détails en tooltip) :**
- ? **92%** (tooltip: "?nergie: 819/890 [+80]")
- œ **100%** (tooltip: "Structure: 1020/1020")
- ? **100%** (tooltip: "Bouclier: 75/75")

**Ligne 3 :**
- ? **136 650** (tooltip: "Unitek (pièces imprimante 3D)")

**Ligne 4 :**
- ? **Cible actuelle** : Vulcania

**Note :** Les états du vaisseau sont affichés uniquement en pourcentage, les valeurs complètes apparaissent dans les tooltips au survol.

---

### Maquette Détaillée Lunastar (Reproduction Exacte)

Voici la reproduction exacte de l'en-tête Lunastar en **disposition 3 colonnes** :

```
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
?                                                                                       ?
?  COLONNE 1 (Gauche)          COLONNE 2 (Centre)              COLONNE 3 (Droite)     ?
?  œœœœœœœœ?           œœœœœœœœœ              œœœœœœœœ?       ?
?                                                                                       ?
?  NomDuJoueur                 x: 4 , y: 2 , z: 9              USS Exploreur          ?
?  Vos Crédits: 22 749 332     Vulcanus                        NCC 7609-C             ?
?  Vos Points d'actions: 24                                                           ?
?                              œ Puissance solaire: 80        ? Energie: 819/890 [+80] ?
?                              œ Danger astéroïdes: 70        œ Structure: [1020/1020] 100% ?
?                               Nombre de planètes: 14       ? Bouclier: [75/75] 100% ?
?                              ? Réseau: Système Solaire     ? Pièces: 136 650       ?
?                                                              ? Cible: Vulcania      ?
?                                                                                       ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
```

**Caractéristiques importantes :**
- **3 colonnes distinctes** côte à côte
- **Hauteur compacte** : moins de 3 lignes effectives
- **Alignement vertical** : chaque colonne s'étend vers le bas
- **Espacement** : colonnes bien espacées pour lisibilité

### Notre Adaptation Optimisée (4 colonnes avec tooltips)

```
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
? ? JOUEUR          SYST?ME         ? SECTEUR          VAISSEAU                           ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
?                                                                                               ?
? Jean Dupont       Vulcanus (4,2,9)      [?]          USS Exploreur NCC-7609-C              ?
? ? 22 749 332 CR  œ 80 œ 70  14                     ? 92%  œ 100%  ? 100%               ?
? ? PA: 24          ? Système Solaire                  ? 136 650                            ?
?                                                        ? ? Vulcania                         ?
?                                                                                               ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
```

**Améliorations par rapport à Lunastar :**
- **4 colonnes** au lieu de 3 (ajout de la colonne icône secteur)
- **Affichage compact** : icônes + valeurs uniquement pour système et vaisseau
- **Tooltips** : informations détaillées au survol
  - Colonne 2 : "œ 80" ? tooltip: "Puissance solaire: 80/100"
  - Colonne 4 : "? 92%" ? tooltip: "?nergie: 819/890 [+80]"
- **?conomie d'espace** : permet d'afficher plus d'infos dans moins de hauteur

---

### Spécifications Techniques

#### Données à Afficher

**Joueur :**
- `personnage.nom` ou `personnage.pseudo`
- `personnage.credits` (formaté avec espaces : 22 749 332)
- `personnage.points_action_actuels` / `personnage.points_action_max`

**Position :**
- `vaisseau.position_x`, `vaisseau.position_y`, `vaisseau.position_z`
- `systeme_actuel.nom`

**Système Stellaire :**
- `systeme.puissance_solaire` (0-100)
- `systeme.danger_asteroides` (0-100)
- `systeme.nombre_planetes`
- `reseau_satellite.nom` ou "Aucun réseau"

**Secteur :**
- `secteur.nom`
- `secteur.icone` ou `secteur.type` (pour déterminer l'icône à afficher)

**Vaisseau :**
- `vaisseau.nom`
- `vaisseau.energie_actuelle` / `vaisseau.energie_max`
- `vaisseau.regeneration_energie` (par tour/heure)
- `vaisseau.structure_actuelle` / `vaisseau.structure_max`
- `vaisseau.bouclier_actuel` / `vaisseau.bouclier_max`
- `vaisseau.pieces_detachees` (Unitek - pièces pour imprimante 3D)
- `vaisseau.cible_actuelle.nom` (si existe)

**Note sur l'affichage :**
- Les valeurs système (puissance, astéroïdes, planètes) sont affichées sous forme compacte avec tooltips
- Les stats vaisseau sont affichées en pourcentages avec tooltips pour les valeurs complètes

#### Mise à Jour Temps Réel

Certaines valeurs doivent être mises à jour dynamiquement :

**En temps réel (via WebSocket/AJAX) :**
- ? ?nergie du vaisseau (si régénération active)
- œ Structure (si en réparation)
- ? Bouclier (si en recharge)
- ? Cible actuelle (si changement)

**Après action :**
- ? Crédits (après transaction)
- ? Points d'Action (après utilisation)
-  Position (après déplacement)
- ? Pièces détachées (après réparation)

#### Code HTML/Blade Exemple

```blade
{{-- resources/views/components/game-header.blade.php --}}
<header class="game-header">
    {{-- Disposition en 4 COLONNES --}}
    <div class="header-columns">

        {{-- COLONNE 1 : JOUEUR --}}
        <div class="header-column header-player">
            <div class="column-title">? JOUEUR</div>
            <div class="player-name">{{ $personnage->nom }}</div>
            <div class="player-credits">
                ? {{ number_format($personnage->credits, 0, ',', ' ') }} CR
            </div>
            <div class="player-actions">
                ? PA: {{ $personnage->points_action_actuels }}
            </div>
        </div>

        {{-- COLONNE 2 : SYST?ME --}}
        <div class="header-column header-system">
            <div class="column-title"> SYST?ME</div>

            {{-- Bloc Position --}}
            <div class="system-position">
                <div class="system-coords">
                    {{ $systeme->nom }} ({{ $vaisseau->position_x }}, {{ $vaisseau->position_y }}, {{ $vaisseau->position_z }})
                </div>
            </div>

            {{-- Bloc Caractéristiques COMPACT (icônes + valeurs, détails en tooltip) --}}
            <div class="system-stats-compact">
                <span class="stat-item" title="Puissance solaire: {{ $systeme->puissance_solaire }}/100">
                    œ {{ $systeme->puissance_solaire }}
                </span>
                <span class="stat-item" title="Danger astéroïdes: {{ $systeme->danger_asteroides }}/100">
                    œ {{ $systeme->danger_asteroides }}
                </span>
                <span class="stat-item" title="Planètes et POI: {{ $systeme->nombre_planetes }}">
                     {{ $systeme->nombre_planetes }}
                </span>
            </div>

            <div class="system-network">
                ? {{ $reseauSatellite?->nom œ 'Aucun réseau' }}
            </div>
        </div>

        {{-- COLONNE 3 : IC?NE SECTEUR --}}
        <div class="header-column header-sector">
            <div class="column-title">? SECTEUR</div>
            <div class="sector-icon">
                {{-- Icône du secteur (à définir selon le type) --}}
                <span class="sector-visual" title="{{ $secteur->nom œ 'Secteur' }}">
                    ?
                </span>
            </div>
        </div>

        {{-- COLONNE 4 : VAISSEAU --}}
        <div class="header-column header-ship">
            <div class="column-title"> VAISSEAU</div>

            <div class="ship-name">{{ $vaisseau->nom }}</div>

            {{-- Stats COMPACT (pourcentages uniquement, détails en tooltip) --}}
            <div class="ship-stats-compact">
                @php
                    $energyPercent = round(($vaisseau->energie_actuelle / $vaisseau->energie_max) * 100);
                    $structurePercent = round(($vaisseau->structure_actuelle / $vaisseau->structure_max) * 100);
                    $shieldPercent = round(($vaisseau->bouclier_actuel / $vaisseau->bouclier_max) * 100);
                @endphp

                <span class="stat-item"
                      title="?nergie: {{ $vaisseau->energie_actuelle }}/{{ $vaisseau->energie_max }}@if($vaisseau->regeneration_energie > 0) [+{{ $vaisseau->regeneration_energie }}]@endif">
                    ? {{ $energyPercent }}%
                </span>
                <span class="stat-item"
                      title="Structure: {{ $vaisseau->structure_actuelle }}/{{ $vaisseau->structure_max }}">
                    œ {{ $structurePercent }}%
                </span>
                <span class="stat-item"
                      title="Bouclier: {{ $vaisseau->bouclier_actuel }}/{{ $vaisseau->bouclier_max }}">
                    ? {{ $shieldPercent }}%
                </span>
            </div>

            <div class="ship-parts" title="Unitek - Pièces pour imprimante 3D">
                ? {{ number_format($vaisseau->pieces_detachees, 0, ',', ' ') }}
            </div>

            @if($vaisseau->cible_actuelle)
            <div class="ship-target">
                ? ? {{ $vaisseau->cible_actuelle->nom }}
            </div>
            @endif
        </div>

    </div>
</header>
```

#### Style CSS

```css
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
    grid-template-columns: 1.2fr 1.5fr 0.8fr 1.5fr; /* 4 colonnes avec proportion adaptée */
    gap: 1.5rem;
    color: #e0e0e0;
    align-items: start; /* Alignement haut pour chaque colonne */
}

/* Style commun pour chaque colonne */
.header-column {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

/* Titres de colonnes (optionnels) */
.column-title {
    font-size: 0.75rem;
    color: #4a9eff;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.25rem;
    opacity: 0.8;
}

/* œœœœœœœœœœœœœœœœœœœœœœœœœ?
   COLONNE 1 : JOUEUR
   œœœœœœœœœœœœœœœœœœœœœœœœœ? */

.header-player {
    /* Colonne gauche */
}

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

/* œœœœœœœœœœœœœœœœœœœœœœœœœ?
   COLONNE 2 : SYST?ME
   œœœœœœœœœœœœœœœœœœœœœœœœœ? */

.header-system {
    /* Colonne centre-gauche */
}

.system-coords {
    color: #ff6b9d;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

/* Stats COMPACT : affichage en ligne avec tooltips */
.system-stats-compact {
    display: flex;
    gap: 0.75rem;
    font-size: 0.85rem;
}

.system-stats-compact .stat-item {
    cursor: help; /* Indique que tooltip est disponible */
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

/* œœœœœœœœœœœœœœœœœœœœœœœœœ?
   COLONNE 3 : SECTEUR
   œœœœœœœœœœœœœœœœœœœœœœœœœ? */

.header-sector {
    /* Colonne centre-droit */
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

/* œœœœœœœœœœœœœœœœœœœœœœœœœ?
   COLONNE 4 : VAISSEAU
   œœœœœœœœœœœœœœœœœœœœœœœœœ? */

.header-ship {
    /* Colonne droite */
}

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
    cursor: help; /* Indique que tooltip est disponible */
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

/* Barres de progression optionnelles */
.progress-bar {
    display: inline-block;
    width: 100px;
    height: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    position: relative;
    vertical-align: middle;
    margin-left: 0.5rem;
}

.progress-bar-fill {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    background: linear-gradient(90deg, #4a9eff 0%, #2196f3 100%);
    transition: width 0.3s ease;
}

.progress-bar-fill.energy {
    background: linear-gradient(90deg, #ffeb3b 0%, #ffc107 100%);
}

.progress-bar-fill.structure {
    background: linear-gradient(90deg, #00bcd4 0%, #0097a7 100%);
}

.progress-bar-fill.shield {
    background: linear-gradient(90deg, #2196f3 0%, #1976d2 100%);
}

/* Animation pour les valeurs qui changent */
@keyframes value-update {
    0% { color: #4a9eff; transform: scale(1.1); }
    100% { color: inherit; transform: scale(1); }
}

.value-updated {
    animation: value-update 0.5s ease;
}

/* œœœœœœœœœœœœœœœœœœœœœœœœœ?
   TOOLTIPS
   œœœœœœœœœœœœœœœœœœœœœœœœœ? */

/* Note : Utilisez Bootstrap Tooltip ou une bibliothèque similaire
   pour gérer les tooltips HTML. Les attributs "title" natifs peuvent
   être améliorés avec data-bs-toggle="tooltip" de Bootstrap. */

/* œœœœœœœœœœœœœœœœœœœœœœœœœ?
   RESPONSIVE - MOBILE
   œœœœœœœœœœœœœœœœœœœœœœœœœ? */

@media (max-width: 768px) {
    .game-header {
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    /* Colonnes empilées verticalement sur mobile */
    .header-columns {
        grid-template-columns: 1fr; /* 1 seule colonne sur mobile */
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

    /* Compact stats restent sur une ligne même sur mobile */
    .ship-stats-compact,
    .system-stats-compact {
        font-size: 0.75rem;
        gap: 0.5rem;
    }

    /* Icône secteur plus petite sur mobile */
    .sector-visual {
        font-size: 1.5rem;
    }
}

/* Version tablette */
@media (min-width: 769px) and (max-width: 1024px) {
    .header-columns {
        grid-template-columns: 1fr 1.5fr 0.8fr 1.5fr; /* Garde 4 colonnes mais ajuste */
        gap: 1rem;
    }

    .game-header {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
    }
}
```

#### JavaScript pour Mise à Jour Temps Réel

```javascript
// public/js/game-header.js

class GameHeader {
    constructor() {
        this.updateInterval = 5000; // 5 secondes
        this.init();
    }

    init() {
        this.startAutoUpdate();
    }

    startAutoUpdate() {
        setInterval(() => {
            this.updateShipStatus();
        }, this.updateInterval);
    }

    async updateShipStatus() {
        try {
            const response = await fetch('/api/vaisseau/status');
            const data = await response.json();

            this.updateEnergy(data.energie_actuelle, data.energie_max, data.regeneration);
            this.updateStructure(data.structure_actuelle, data.structure_max);
            this.updateShield(data.bouclier_actuel, data.bouclier_max);
            this.updateTarget(data.cible_actuelle);
        } catch (error) {
            console.error('Erreur mise à jour en-tête:', error);
        }
    }

    updateEnergy(current, max, regen) {
        // Mise à jour en mode COMPACT (pourcentage uniquement)
        const element = document.querySelector('.ship-stats-compact .stat-item:nth-child(1)');
        const percentage = Math.round((current / max) * 100);
        const oldValue = element.textContent;
        const newValue = `? ${percentage}%`;

        // Mise à jour du tooltip
        const tooltipText = `?nergie: ${current}/${max}${regen > 0 ? ' [+' + regen + ']' : ''}`;
        element.setAttribute('title', tooltipText);

        if (oldValue !== newValue) {
            element.textContent = newValue;
            element.classList.add('value-updated');
            setTimeout(() => element.classList.remove('value-updated'), 500);
        }
    }

    updateStructure(current, max) {
        // Mise à jour en mode COMPACT (pourcentage uniquement)
        const element = document.querySelector('.ship-stats-compact .stat-item:nth-child(2)');
        const percentage = Math.round((current / max) * 100);
        element.textContent = `œ ${percentage}%`;

        // Mise à jour du tooltip
        element.setAttribute('title', `Structure: ${current}/${max}`);
    }

    updateShield(current, max) {
        // Mise à jour en mode COMPACT (pourcentage uniquement)
        const element = document.querySelector('.ship-stats-compact .stat-item:nth-child(3)');
        const percentage = Math.round((current / max) * 100);
        element.textContent = `? ${percentage}%`;

        // Mise à jour du tooltip
        element.setAttribute('title', `Bouclier: ${current}/${max}`);
    }

    updateTarget(target) {
        const container = document.querySelector('.ship-target');
        if (target) {
            if (!container) {
                const headerShip = document.querySelector('.header-ship');
                const targetDiv = document.createElement('div');
                targetDiv.className = 'ship-target';
                targetDiv.textContent = `? Cible: ${target.nom}`;
                headerShip.appendChild(targetDiv);
            } else {
                container.textContent = `? Cible: ${target.nom}`;
            }
        } else {
            if (container) {
                container.remove();
            }
        }
    }
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', () => {
    new GameHeader();
});
```

---

### Variantes d'Affichage

#### Version Compacte (Mobile)

```
œœœœœœœœœœœœœœœœœœœœ?
? Jean Dupont  ? 22.7M  ? 24          ?
?  Vulcanus (4,2,9)                   ?
? œ 80 œ 70  14  ? Système Solaire ?
œœœœœœœœœœœœœœœœœœœœ?
? ? Secteur Central                    ?
œœœœœœœœœœœœœœœœœœœœ?
?  USS Exploreur                      ?
? ? 92%  œ 100%  ? 100%              ?
? ? 136 650  ? ? Vulcania            ?
œœœœœœœœœœœœœœœœœœœœ?
```

**Note mobile :** Les valeurs compactes avec tooltips sont préservées, mais les tooltips peuvent être déclenchés par un appui long sur mobile.

#### Version ?tendue (Grand écran) - 4 colonnes

```
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
? ? JOUEUR                 SYST?ME                  ? SECTEUR          VAISSEAU                     ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
?                                                                                                         ?
? Jean Dupont              Vulcanus (4, 2, 9)             [?]          USS Exploreur NCC-7609-C        ?
? ? 22 749 332 CR         œ 80  œ 70   14                           ? 92%  œ 100%  ? 100%         ?
? ? PA: 24                 ? Réseau: Système Solaire                   ? 136 650                      ?
?                                                                        ? ? Vulcania                   ?
?                                                                                                         ?
œœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœœ?
```

**Interactions :**
- **Survol icônes système** : Affiche "Puissance solaire: 80/100", "Danger astéroïdes: 70/100", etc.
- **Survol icône secteur** : Affiche le nom et type du secteur
- **Survol stats vaisseau** : Affiche "?nergie: 819/890 [+80]", "Structure: 1020/1020", etc.
- **Survol pièces** : Affiche "Unitek - Pièces pour imprimante 3D"

---

### Notes d'Implémentation

1. **Persistance** : L'en-tête doit être présent sur TOUTES les pages du jeu
2. **Performance** : Utiliser le cache pour les données système (puissance solaire, planètes)
3. **Responsive** : Adapter l'affichage selon la taille d'écran (4 colonnes ? 1 colonne sur mobile)
4. **Accessibilité** : Ajouter des attributs ARIA pour les lecteurs d'écran
5. **Animation** : Animer subtilement les changements de valeurs
6. **Tooltips** :
   - Utiliser Bootstrap Tooltip ou une bibliothèque similaire
   - Initialiser avec `data-bs-toggle="tooltip"` pour améliorer les tooltips natifs
   - Sur mobile : prévoir un appui long pour afficher les tooltips
7. **Affichage compact** :
   - Colonne 2 (Système) : œ 80, œ 70,  14 sur une seule ligne
   - Colonne 4 (Vaisseau) : ? 92%, œ 100%, ? 100% sur une seule ligne
   - Détails complets uniquement dans les tooltips
8. **Icône secteur** :
   - Colonne 3 dédiée à l'icône visuelle du secteur
   - Design et variantes d'icônes à définir selon types de secteurs
   - Prévoir un système de mapping secteur ? icône

---

## ? Structure du Menu Principal

```
œœœœœœœœœœœœœœœœœœœœœ?
?  CONQU?TE GALACTIQUE                    ?
œœœœœœœœœœœœœœœœœœœœœ?
?                                         ?
?   PERSONNAGE (non cliquable)          ?
?     œ Dossier                         ?
?     œ Spatiocarte                     ?
?     œ Gestion                         ?
?                                         ?
?   NAVIRE/STATION (non cliquable)      ?
?     œ Timonerie                       ?
?     œ Ingénierie                      ?
?     œ COM                             ?
?     œ Soute                           ?
?     œ ?quipage                        ?
?                                         ?
?  œ JEU (non cliquable)                 ?
?     œ Profil                          ?
?     œ Quitter                         ?
?                                         ?
?  ? ADMIN (non cliquable) [si admin]   ?
?     œ Dashboard                       ?
?     œ Carte Univers                   ?
?     œ Gestion Joueurs                 ?
?     œ Logs Système                    ?
?     œ Outils Debug                    ?
?                                         ?
œœœœœœœœœœœœœœœœœœœœœ?
```

---

##  Menu PERSONNAGE

Menu dédié à la gestion du personnage et de sa position dans l'univers.

### 1. Dossier
**Route :** `/personnage/dossier`

**Contenu :**
- Informations du personnage
  - Nom, faction, niveau
  - Crédits disponibles
  - Réputation
  - Compétences
- Historique d'actions récentes
- Statistiques de jeu
- Journal de bord

**Exemple d'affichage :**
```
œœœœœœœœœœœœœœœœœœœ?
? DOSSIER PERSONNEL                   ?
œœœœœœœœœœœœœœœœœœœ?
? Commandant : John Doe               ?
? Faction : Fédération Terrienne      ?
? Niveau : 15                         ?
? Crédits : 125,450 CR                ?
?                                     ?
? Réputation :                        ?
?  - Fédération : +75 (Respecté)      ?
?  - Pirates : -30 (Hostile)          ?
?                                     ?
? Compétences :                       ?
?  - Navigation : œœœœœ 8/10     ?
?  - Combat : œœœœœ 6/10         ?
?  - Commerce : œœœœœ 9/10       ?
œœœœœœœœœœœœœœœœœœœ?
```

### 2. Spatiocarte
**Route :** `/personnage/spatiocarte`

**Contenu :**
- Carte de l'univers connu
- Position actuelle (secteur, système, coordonnées)
- Systèmes découverts
- Routes d'hyperespace connues
- Points d'intérêt (stations, planètes, etc.)
- Filtres et zoom

**Fonctionnalités :**
-  Zoom/Dézoom
- ? Centrer sur position actuelle
-  Définir destination
- œ Afficher/Masquer couches (routes, stations, etc.)
-  Statistiques de découverte

### 3. Gestion
**Route :** `/personnage/gestion`

**Contenu :**
- Gestion des biens immobiliers
  - Habitations sur planètes
  - Hangars loués
  - Contrats de stockage
- Gestion des vaisseaux possédés
  - Liste des navires
  - Statut (actif, entreposé)
  - Localisation
- Gestion financière
  - Comptes bancaires
  - Investissements
  - Prêts en cours
- Contrats et missions en cours

---

##  Menu NAVIRE/STATION

Menu contextuel qui change selon la localisation du joueur.

### Contexte : ? bord d'un Navire

Titre du menu : ** NAVIRE**

#### 1. Timonerie
**Route :** `/navire/timonerie`

**Contenu :**
- **Navigation & Déplacement**
  - Position actuelle (x, y, z)
  - Destination sélectionnée
  - Distance restante
  - Temps de trajet estimé
  - Commandes de vol (avancer, tourner, arrêter)
  - Saut hyperespace

- **Radar & Scan** (intégré)
  - Objets détectés à proximité
    - Vaisseaux
    - Stations
    - Planètes
    - Astéroïdes
    - Anomalies
  - Distance et direction
  - Niveau de détail selon capacité radar
  - Scan détaillé (action)

**Interface :**
```
œœœœœœœœœœœœœœœœœœœœœœ?
? TIMONERIE                                 ?
œœœœœœœœœœœœœœœœœœœœœœ?
? Position : Secteur A-12                  ?
? Coords : X:1245.3 Y:987.6 Z:234.1        ?
? Destination : Station Alpha               ?
? Distance : 234.5 AL                       ?
? ETA : 2h 34min                           ?
?                                          ?
? [? Avancer] [? Stop] [? Hypersaut]      ?
?                                          ?
? œœœœœœœœœœœœœœœœœœœ?  ?
? RADAR - Portée : 50 AL                   ?
? œœœœœœœœœœœœœœœœœœœœœ
? œ  Vaisseau inconnu - 12.3 AL (NE)     ?
? ? Station Commerciale - 45.7 AL (S)     ?
?  Planète terraformée - 23.1 AL (O)     ?
?                                          ?
? [ Scanner Contact]                     ?
œœœœœœœœœœœœœœœœœœœœœœ?
```

#### 2. Ingénierie
**Route :** `/navire/ingenierie`

**Contenu :**
- ?tat des systèmes du vaisseau
  - Moteurs (propulsion, hyperpropulsion)
  - Générateurs d'énergie
  - Boucliers
  - Systèmes de survie
  - Armement
- Niveau d'intégrité de la coque
- Consommation énergétique
- Réparations nécessaires
- Améliorations disponibles
- Diagnostics et alertes

**Interface :**
```
œœœœœœœœœœœœœœœœœœœœœœ?
? ING?NIERIE                                ?
œœœœœœœœœœœœœœœœœœœœœœ?
? ?tat Général : œœœœœ 80%            ?
?                                          ?
? Systèmes :                               ?
?  ? Générateur Principal   : œœœœœ 85% ?
?   Moteurs Sublumiques   : œœœœœ 100% ?
?  ? Hyperpropulseur       : œœœœœ 70% ?
?  œ Boucliers             : œœœœœ 50% ?
?  ? Armes                 : œœœœœ 80% ?
?  ? Vie Support           : œœœœœ 100% ?
?                                          ?
? Coque : œœœœœ 75%                   ?
?                                          ?
? œ Alerte : Hyperpropulseur nécessite    ?
?           maintenance (72h max)          ?
?                                          ?
? [? Réparer] [œ Améliorer]              ?
œœœœœœœœœœœœœœœœœœœœœœ?
```

#### 3. COM
**Route :** `/navire/com`

**Contenu :**
- Communications radio/subspace
  - Messages reçus (inbox)
  - Envoyer message
  - Contacts
  - Fréquences publiques/privées
- Actualités galactiques
- Alertes et communiqués officiels
- Signaux de détresse
- Transactions commerciales
- Négociations avec contacts

**Catégories de messages :**
- ? Messages personnels
- ? Annonces publiques
- ? Alertes de sécurité
- ? Offres commerciales
- œ SOS / Détresse
- ? Actualités

#### 4. Soute
**Route :** `/navire/soute`

**Contenu :**
- Inventaire de la cargaison
  - Marchandises
  - Ressources
  - ?quipements
  - Objets spéciaux
- Capacité de stockage
  - Tonnage utilisé / total
  - Compartiments spéciaux (réfrigéré, blindé, etc.)
- Gestion du cargo
  - Trier, filtrer
  - Larguer cargo
  - Transférer vers station/autre vaisseau

**Interface :**
```
œœœœœœœœœœœœœœœœœœœœœœ?
? SOUTE                                     ?
œœœœœœœœœœœœœœœœœœœœœœ?
? Capacité : 450/800 tonnes (56%)          ?
?                                          ?
? Cargo :                                  ?
?                                          ?
? ? Minerai de fer         : 150t         ?
?  Cristaux énergétiques  : 25t          ?
? œ Denrées alimentaires   : 80t          ?
? ? Pièces détachées       : 45t          ?
?  Médicaments            : 30t          ?
? ? Cellules énergie       : 120t         ?
?                                          ?
? Compartiments spéciaux :                 ?
? œ Réfrigéré : 50/100t                   ?
?  Blindé : 0/50t                        ?
?                                          ?
? [œ Transférer] [? Larguer]             ?
œœœœœœœœœœœœœœœœœœœœœœ?
```

#### 5. ?quipage
**Route :** `/navire/equipage`

**Contenu :**
- Liste des membres d'équipage
  - Nom, rôle, compétences
  - Statut (moral, santé, fatigue)
  - Salaire
- Postes disponibles / Recrutement
- Gestion des assignations
  - Affectation aux postes
  - Rotation des quarts
- Formation et amélioration
- Relations et événements d'équipage

**Interface :**
```
œœœœœœœœœœœœœœœœœœœœœœ?
? ?QUIPAGE                                  ?
œœœœœœœœœœœœœœœœœœœœœœ?
? Membres : 8/12                           ?
?                                          ?
? œœ Lt. Sarah Connor - Pilote            ?
?    Navigation: œœœœœ 8/10           ?
?    Moral:  Excellent                   ?
?                                          ?
? œ John "Sparks" Miller - Ingénieur     ?
?    Réparation: œœœœœ 9/10           ?
?    Moral:  Correct                     ?
?                                          ?
? œœ Dr. Elena Vasquez - Médecin          ?
?    Médecine: œœœœœ 6/10             ?
?    Moral:  Bon                         ?
?                                          ?
? Postes vacants : 4                       ?
?  - Artilleur (recommandé)                ?
?  - Navigateur Junior                     ?
?  - Mécanicien                            ?
?  - Officier scientifique                 ?
?                                          ?
? [? Recruter] [ Gérer Assignations]    ?
œœœœœœœœœœœœœœœœœœœœœœ?
```

### Contexte : ? bord d'une Station

Titre du menu : **? STATION**

Le menu change pour refléter le contexte d'une station spatiale :

#### 1. Hall Principal
**Route :** `/station/hall`
- Informations sur la station
- Services disponibles
- Départ vers vaisseau

#### 2. Hangar
**Route :** `/station/hangar`
- Gestion des vaisseaux amarrés
- Services de réparation et maintenance
- Achat/Vente de vaisseaux
- Modification et améliorations

#### 3. Marché
**Route :** `/station/marche`
- Commerce de marchandises
- Offres et demandes
- Prix du marché
- Contrats commerciaux

#### 4. Bureau des Missions
**Route :** `/station/missions`
- Missions disponibles
- Missions en cours
- Réputation et récompenses

#### 5. Cantina
**Route :** `/station/cantina`
- Recrutement d'équipage
- Rumeurs et informations
- Rencontres avec PNJ

---

## œ Menu JEU

Menu de gestion méta-jeu (hors contexte du personnage).

### 1. Profil
**Route :** `/jeu/profil`

**Contenu :**
- Informations du compte joueur
  - Pseudo
  - Email (masqué)
  - Date d'inscription
  - Dernière connexion
- Personnages du compte
  - Liste des personnages créés
  - Personnage actif
  - Créer nouveau personnage
  - Supprimer personnage
- Paramètres du jeu
  - Notifications
  - Préférences d'affichage
  - Raccourcis clavier
  - Son/Musique
- Statistiques globales
  - Temps de jeu total
  - Achievements débloqués
  - Records personnels

### 2. Quitter
**Route :** `/logout`

**Contenu :**
- Confirmation de déconnexion
- Sauvegarde automatique
- Message de sortie

---

## ? Menu ADMIN

Menu spécial visible uniquement pour les administrateurs.

### 1. Dashboard
**Route :** `/admin/dashboard`

**Contenu :**
- Vue d'ensemble du jeu
  - Joueurs connectés
  - Statistiques serveur
  - Activité récente
  - Alertes système
- Graphiques et métriques
  - ?volution joueurs
  - Activité économique
  - Performance technique

### 2. Carte Univers
**Route :** `/admin/carte`

**Contenu :**
- Carte complète de l'univers
  - Tous les secteurs
  - Tous les systèmes
  - Tous les objets
- Mode édition
  - Créer/Modifier objets spatiaux
  - Ajuster positions
  - Configurer propriétés
- Vue des joueurs
  - Position de tous les joueurs
  - Vaisseaux actifs
  - Activité en temps réel

### 3. Gestion Joueurs
**Route :** `/admin/joueurs`

**Contenu :**
- Liste des comptes
  - Rechercher joueur
  - Voir détails compte/personnages
  - ?diter informations
  - Bannir/Débannir
  - Modifier crédits/ressources
- Modération
  - Messages signalés
  - Actions suspectes
  - Logs d'actions joueurs

### 4. Logs Système
**Route :** `/admin/logs`

**Contenu :**
- Logs d'application
  - Erreurs PHP/Laravel
  - Requêtes SQL lentes
  - Exceptions
- Logs d'activité
  - Connexions
  - Transactions importantes
  - Actions critiques
- Filtres et recherche

### 5. Outils Debug
**Route :** `/admin/debug`

**Contenu :**
- Outils de développement
  - Génération de données test
  - Reset secteurs
  - Simulation événements
  - Tests de performance
- Cache management
  - Vider cache
  - Voir contenu cache
  - Statistiques cache
- Base de données
  - Backup/Restore
  - Migrations
  - Queries directes (avec précaution)

---

##  Comparaison avec Lunastar

### Tableau de Correspondance

| **Lunastar** | **Conquête Galactique** | **Notes** |
|--------------|-------------------------|-----------|
| **Menu Personnage** | | |
| Dossier | Dossier | ? Identique |
| Spatiocarte | Spatiocarte | ? Identique |
| Gestion | Gestion | ? Identique |
| **Menu Vaisseau** | **Menu Navire/Station** |  Contextuel |
| Timonerie | Timonerie | ? Navigation + Radar intégré |
| Radar | *(intégré dans Timonerie)* |  Fusionné pour simplifier |
| Systèmes | Ingénierie |  Renommé (plus évocateur) |
| Radio | COM |  Renommé (terme SF standard) |
| Soutes | Soute | ? Quasi-identique |
| ?quipage | ?quipage | ? Identique |
| **Menu Jeu** | | |
| Profil | Profil | ? Identique |
| Quitter | Quitter | ? Identique |
| **Ajouts** | | |
| *(absent)* | Menu Admin | ? Nouveau (avec sous-menus) |
| *(absent)* | Mode Station | ? Nouveau (menu contextuel) |

### Différences Principales

1. **Radar intégré dans Timonerie**
   - Lunastar : 2 menus séparés
   - Nous : 1 menu unifié (plus fluide)

2. **Renommages**
   - "Systèmes" ? "Ingénierie" (plus immersif)
   - "Radio" ? "COM" (communications, standard SF)
   - "Vaisseau" ? "Navire" (pour éviter confusion avec objet)

3. **Menu Contextuel Station**
   - Le menu "Navire" devient "Station" avec contenus différents
   - Adapté au contexte du joueur

4. **Menu Admin Complet**
   - Absent de Lunastar (ou non montré)
   - Essentiel pour la gestion du jeu

---

## ? Notes d'Implémentation

### Structure HTML/CSS Recommandée

```html
<nav class="main-menu">
    <!-- Personnage -->
    <div class="menu-section">
        <h3 class="menu-title"> PERSONNAGE</h3>
        <ul class="menu-items">
            <li><a href="/personnage/dossier">Dossier</a></li>
            <li><a href="/personnage/spatiocarte">Spatiocarte</a></li>
            <li><a href="/personnage/gestion">Gestion</a></li>
        </ul>
    </div>

    <!-- Navire/Station (contextuel) -->
    <div class="menu-section">
        <h3 class="menu-title">
            @if($context === 'navire')
                 NAVIRE
            @else
                ? STATION
            @endif
        </h3>
        <ul class="menu-items">
            @if($context === 'navire')
                <li><a href="/navire/timonerie">Timonerie</a></li>
                <li><a href="/navire/ingenierie">Ingénierie</a></li>
                <li><a href="/navire/com">COM</a></li>
                <li><a href="/navire/soute">Soute</a></li>
                <li><a href="/navire/equipage">?quipage</a></li>
            @else
                <li><a href="/station/hall">Hall Principal</a></li>
                <li><a href="/station/hangar">Hangar</a></li>
                <li><a href="/station/marche">Marché</a></li>
                <li><a href="/station/missions">Missions</a></li>
                <li><a href="/station/cantina">Cantina</a></li>
            @endif
        </ul>
    </div>

    <!-- Jeu -->
    <div class="menu-section">
        <h3 class="menu-title">œ JEU</h3>
        <ul class="menu-items">
            <li><a href="/jeu/profil">Profil</a></li>
            <li><a href="/logout">Quitter</a></li>
        </ul>
    </div>

    <!-- Admin (si admin) -->
    @if(auth()->user()->is_admin)
    <div class="menu-section menu-admin">
        <h3 class="menu-title">? ADMIN</h3>
        <ul class="menu-items">
            <li><a href="/admin/dashboard">Dashboard</a></li>
            <li><a href="/admin/carte">Carte Univers</a></li>
            <li><a href="/admin/joueurs">Gestion Joueurs</a></li>
            <li><a href="/admin/logs">Logs Système</a></li>
            <li><a href="/admin/debug">Outils Debug</a></li>
        </ul>
    </div>
    @endif
</nav>
```

### Gestion du Contexte Navire/Station

```php
// Dans un Middleware ou Service
class GameContextService
{
    public function getMenuContext(Personnage $personnage): string
    {
        // Si le personnage est dans un vaisseau
        if ($personnage->vaisseau_actif_id) {
            return 'navire';
        }

        // Si le personnage est dans une station
        if ($personnage->station_actuelle_id) {
            return 'station';
        }

        // Défaut
        return 'navire';
    }
}
```

### Routes Laravel

```php
// routes/web.php

// Groupe authentifié
Route::middleware(['auth'])->group(function () {

    // Menu Personnage
    Route::prefix('personnage')->group(function () {
        Route::get('/dossier', [PersonnageController::class, 'dossier'])->name('personnage.dossier');
        Route::get('/spatiocarte', [PersonnageController::class, 'spatiocarte'])->name('personnage.spatiocarte');
        Route::get('/gestion', [PersonnageController::class, 'gestion'])->name('personnage.gestion');
    });

    // Menu Navire
    Route::prefix('navire')->group(function () {
        Route::get('/timonerie', [NavireController::class, 'timonerie'])->name('navire.timonerie');
        Route::get('/ingenierie', [NavireController::class, 'ingenierie'])->name('navire.ingenierie');
        Route::get('/com', [NavireController::class, 'com'])->name('navire.com');
        Route::get('/soute', [NavireController::class, 'soute'])->name('navire.soute');
        Route::get('/equipage', [NavireController::class, 'equipage'])->name('navire.equipage');
    });

    // Menu Station
    Route::prefix('station')->group(function () {
        Route::get('/hall', [StationController::class, 'hall'])->name('station.hall');
        Route::get('/hangar', [StationController::class, 'hangar'])->name('station.hangar');
        Route::get('/marche', [StationController::class, 'marche'])->name('station.marche');
        Route::get('/missions', [StationController::class, 'missions'])->name('station.missions');
        Route::get('/cantina', [StationController::class, 'cantina'])->name('station.cantina');
    });

    // Menu Jeu
    Route::prefix('jeu')->group(function () {
        Route::get('/profil', [JeuController::class, 'profil'])->name('jeu.profil');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Menu Admin
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/carte', [AdminController::class, 'carte'])->name('admin.carte');
        Route::get('/joueurs', [AdminController::class, 'joueurs'])->name('admin.joueurs');
        Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
        Route::get('/debug', [AdminController::class, 'debug'])->name('admin.debug');
    });
});
```

### Style CSS Recommandé

```css
.main-menu {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    padding: 1rem;
    border-right: 2px solid #0f3460;
    min-height: 100vh;
    width: 250px;
}

.menu-section {
    margin-bottom: 2rem;
}

.menu-title {
    font-family: 'Orbitron', sans-serif;
    color: #4a9eff;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #0f3460;
    cursor: default; /* Non cliquable */
}

.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-items li {
    margin: 0.25rem 0;
}

.menu-items a {
    display: block;
    padding: 0.5rem 1rem;
    color: #e0e0e0;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.menu-items a:hover {
    background: rgba(74, 158, 255, 0.1);
    color: #4a9eff;
    padding-left: 1.25rem;
}

.menu-items a.active {
    background: rgba(74, 158, 255, 0.2);
    color: #4a9eff;
    border-left: 3px solid #4a9eff;
}

.menu-admin {
    border-top: 2px solid #ff6b6b;
    padding-top: 1rem;
}

.menu-admin .menu-title {
    color: #ff6b6b;
}

.menu-admin .menu-items a:hover {
    background: rgba(255, 107, 107, 0.1);
    color: #ff6b6b;
}
```

---

## ? Checklist de Validation

Vous avez demandé de vérifier qu'on n'a rien oublié. Voici la checklist :

### Menu Personnage
- ? Dossier
- ? Spatiocarte
- ? Gestion

### Menu Navire (dans vaisseau)
- ? Timonerie (navigation + radar)
- ? Ingénierie (ex-Systèmes)
- ? COM (ex-Radio)
- ? Soute
- ? ?quipage

### Menu Station (dans station)
- ? Hall Principal
- ? Hangar
- ? Marché
- ? Bureau des Missions
- ? Cantina

### Menu Jeu
- ? Profil
- ? Quitter

### Menu Admin
- ? Dashboard
- ? Carte Univers
- ? Gestion Joueurs
- ? Logs Système
- ? Outils Debug

### Fonctionnalités Transverses
- ? Menu contextuel (Navire/Station)
- ? Titres non cliquables
- ? Sous-menus cliquables
- ? Menu Admin visible uniquement si admin
- ? Routes définies
- ? Style CSS cohérent

---

##  Prochaines ?tapes d'Implémentation

1. **Phase 1 : Structure de base**
   - Créer le layout principal avec menu
   - Implémenter les routes de base
   - Créer les contrôleurs vides

2. **Phase 2 : Menu Personnage**
   - Implémenter Dossier
   - Implémenter Spatiocarte (version basique)
   - Implémenter Gestion

3. **Phase 3 : Menu Navire**
   - Implémenter Timonerie (navigation)
   - Implémenter Timonerie (radar/scan)
   - Implémenter Ingénierie
   - Implémenter COM
   - Implémenter Soute
   - Implémenter ?quipage

4. **Phase 4 : Menu Station**
   - Implémenter Hall
   - Implémenter Hangar
   - Implémenter Marché
   - Implémenter Missions
   - Implémenter Cantina

5. **Phase 5 : Menu Admin**
   - Implémenter Dashboard
   - Implémenter Carte Univers
   - Implémenter Gestion Joueurs
   - Implémenter Logs
   - Implémenter Debug Tools

---

##  Références

- [GDD_Central.md](./GDD_Central.md) - Document central
- [GDD_Architecture_Technique.md](./GDD_Architecture_Technique.md) - Architecture technique
- [GDD_Vaisseaux_Complet.md](./GDD_Vaisseaux_Complet.md) - Système de vaisseaux
- [SYSTEME_STATIONS.md](./SYSTEME_STATIONS.md) - Système de stations
- Inspiration : **Lunastar** (jeu de référence)

---

**Dernière mise à jour :** 2025-01-25
