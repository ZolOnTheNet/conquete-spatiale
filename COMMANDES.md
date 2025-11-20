# Commandes Console - Conquete Spatiale

## Navigation & Exploration

| Commande | Alias | Description | Exemple |
|----------|-------|-------------|---------|
| `help` | `aide` | Afficher l'aide | `help` |
| `status` | `statut` | Statut du personnage | `status` |
| `position` | `pos` | Position actuelle | `position` |
| `vaisseau` | `ship` | Infos du vaisseau | `vaisseau` |
| `lancer` | `roll` | Lancer des des (2d12) | `lancer pilotage` |
| `deplacer` | `move` | Deplacement conventionnel | `deplacer 1 0 0` |
| `saut` | `jump` | Saut hyperespace | `saut 5 3 2` |
| `scan` | `scanner` | Scanner la zone | `scan` |
| `carte` | `map` | Carte des systemes decouverts | `carte` |

## Economie & Ressources

| Commande | Alias | Description | Exemple |
|----------|-------|-------------|---------|
| `scan-planete` | `scanp` | Scanner gisements d'une planete | `scan-planete Sol 3` |
| `extraire` | `mine` | Extraire ressources d'un gisement | `extraire 5 1000` |
| `inventaire` | `inv` | Afficher inventaire du vaisseau | `inventaire` |

## Marches

| Commande | Alias | Description | Exemple |
|----------|-------|-------------|---------|
| `marche` | `market` | Voir le marche local | `marche` |
| `prix` | `prices` | Voir les prix | `prix FER` |
| `acheter` | `buy` | Acheter des ressources | `acheter FER 500` |
| `vendre` | `sell` | Vendre des ressources | `vendre FER 500` |

## Fabrication

| Commande | Alias | Description | Exemple |
|----------|-------|-------------|---------|
| `recettes` | `recipes` | Voir les recettes disponibles | `recettes raffinage` |
| `fabriquer` | `craft` | Fabriquer une recette | `fabriquer RAFF_BAUXITE 2` |

### Categories de recettes
- `raffinage` - Transformation brute (minerai -> metal)
- `alliage` - Combinaison de metaux
- `composant` - Pieces detachees
- `avance` - High-tech, exotique

## Combat

### Equipement & Etat

| Commande | Alias | Description | Exemple |
|----------|-------|-------------|---------|
| `armes` | `weapons` | Voir les armes disponibles | `armes` |
| `boucliers` | `shields` | Voir les boucliers disponibles | `boucliers` |
| `equiper` | `equip` | Equiper arme ou bouclier | `equiper arme LASER_MK1 1` |
| `etat-combat` | `combat` | Voir etat combat du vaisseau | `etat-combat` |
| `reparer` | `repair` | Reparer la coque | `reparer 50` |

### Combat PvE

| Commande | Alias | Description | Exemple |
|----------|-------|-------------|---------|
| `scanner-ennemis` | `scane` | Scanner la zone pour des ennemis | `scanner-ennemis` |
| `ennemis` | `enemies` | Encyclopedie des ennemis | `ennemis` |
| `attaquer` | `attack` | Attaquer l'ennemi en combat | `attaquer` |
| `fuir` | `flee` | Tenter de fuir le combat | `fuir` |

### Deroulement du combat
1. Utilisez `scanner-ennemis` pour detecter des menaces
2. Si un ennemi apparait, utilisez `attaquer` pour combattre
3. Chaque tour: vous attaquez puis l'ennemi riposte
4. Victoire = recompenses (credits + XP)
5. Utilisez `fuir` pour tenter d'echapper (risque de degats)

### Equipement
- **Armes**: 3 slots disponibles (1-3)
- **Bouclier**: 1 slot
- Verification du niveau requis

### Types d'armes
- `laser` - Rapide, precis, degats moyens
- `canon` - Lent, degats eleves
- `missile` - Tres lent, tres gros degats
- `plasma` - Degats sur duree
- `emp` - Desactive systemes

### Types de boucliers
- `energie` - Standard, vulnerable EMP
- `coque` - Blindage, bon vs canon/missile
- `regeneratif` - Regeneration rapide
- `adaptatif` - Equilibre contre tout

---

## Ressources disponibles (21)

### Metaux (10)
- FER, ALUMINIUM, BAUXITE, GRAPHITE, ZINC
- NICKEL, NIOBIUM, TUNGSTENE, PLATINE, URANIUM

### Gaz (2)
- HYDROGENE, OXYGENE

### Elementaires (4)
- SABLES, ARGILES, GLACES, BITUMES

### Chimie (1)
- ELEMENTS_CHIMIQUES

### Exotiques (4)
- NACRETOILE, ARGETOILE, PLAZETOILE, TYRETOILE

---

## Armes disponibles (11)

| Code | Nom | Type | Degats | Precision | Niveau |
|------|-----|------|--------|-----------|--------|
| LASER_MK1 | Laser Mk1 | laser | 5-10 | 85% | 1 |
| LASER_MK2 | Laser Mk2 | laser | 8-15 | 80% | 2 |
| LASER_LOURD | Laser Lourd | laser | 15-25 | 75% | 3 |
| CANON_MK1 | Canon Mk1 | canon | 15-25 | 70% | 2 |
| CANON_MK2 | Canon Mk2 | canon | 25-40 | 65% | 3 |
| CANON_SIEGE | Canon de Siege | canon | 50-80 | 55% | 5 |
| MISSILE_MK1 | Lance-Missiles Mk1 | missile | 30-50 | 60% | 3 |
| MISSILE_LOURD | Torpilles Lourdes | missile | 60-100 | 50% | 4 |
| PLASMA_MK1 | Projecteur Plasma | plasma | 10-20 | 75% | 3 |
| EMP_MK1 | Canon EMP | emp | 5-10 | 90% | 4 |

---

## Boucliers disponibles (7)

| Code | Nom | Type | Points | Regen | Niveau |
|------|-----|------|--------|-------|--------|
| BOUCLIER_MK1 | Bouclier Mk1 | energie | 50 | 3 | 1 |
| BOUCLIER_MK2 | Bouclier Mk2 | energie | 100 | 5 | 2 |
| BOUCLIER_MK3 | Bouclier Mk3 | energie | 200 | 8 | 4 |
| BLINDAGE_MK1 | Blindage Mk1 | coque | 80 | 0 | 2 |
| BLINDAGE_MK2 | Blindage Mk2 | coque | 150 | 0 | 4 |
| REGEN_MK1 | Bouclier Regeneratif | regeneratif | 60 | 10 | 3 |
| ADAPT_MK1 | Bouclier Adaptatif | adaptatif | 120 | 5 | 5 |

---

## Ennemis disponibles (10)

### Pirates (3)

| Code | Nom | Niveau | Difficulte | Coque | Zones |
|------|-----|--------|------------|-------|-------|
| PIRATE_ECLAIREUR | Eclaireur Pirate | 1 | Facile | 50 | 1-5 |
| PIRATE_CHASSEUR | Chasseur Pirate | 2 | Moyen | 80 | 1-6 |
| PIRATE_MARAUDEUR | Maraudeur Pirate | 3 | Moyen | 120 | 2-7 |

### Drones (2)

| Code | Nom | Niveau | Difficulte | Coque | Zones |
|------|-----|--------|------------|-------|-------|
| DRONE_SENTINELLE | Drone Sentinelle | 1 | Facile | 30 | 1-4 |
| DRONE_COMBAT | Drone de Combat | 3 | Moyen | 60 | 2-6 |

### Contrebandiers (1)

| Code | Nom | Niveau | Difficulte | Coque | Zones |
|------|-----|--------|------------|-------|-------|
| CONTREBANDIER_RAPIDE | Coureur Contrebandier | 2 | Moyen | 70 | 2-7 |

### Mercenaires (2)

| Code | Nom | Niveau | Difficulte | Coque | Zones |
|------|-----|--------|------------|-------|-------|
| MERCENAIRE_VETERAN | Mercenaire Veteran | 4 | Difficile | 150 | 3-8 |
| MERCENAIRE_ELITE | Mercenaire Elite | 5 | Difficile | 200 | 4-10 |

### Boss (2)

| Code | Nom | Niveau | Difficulte | Coque | Zones |
|------|-----|--------|------------|-------|-------|
| BOSS_CAPITAINE_PIRATE | Capitaine Scarface | 5 | Boss | 300 | 5-10 |
| BOSS_DRONE_NEXUS | Nexus Prime | 6 | Boss | 250 | 6-10 |

---

## Couts

- **Reparation coque**: 10 credits/point
- **Extraction**: 1 PA par tranche de 10000 unites
- **Scan planete**: 1 PA
- **Fabrication**: 1 PA par multiplicateur

---

## Notes

- Les commandes sont insensibles a la casse
- Utilisez `help` pour voir l'aide complete en jeu
- Les PA (Points d'Action) se regenerent automatiquement
