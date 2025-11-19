# Bilan Technique - Conquete Spatiale

## Vue d'ensemble

Jeu web d'exploration galactique au tour par tour developpe avec Laravel 12. Interface console avec systeme de commandes texte et visualisation graphique.

---

## Stack Technique

### Backend

| Composant | Choix | Version | Justification |
|-----------|-------|---------|---------------|
| Framework | Laravel | 12.x | Framework PHP robuste, ORM Eloquent, migrations |
| PHP | PHP | 8.3+ | Types stricts, performances, match expressions |
| Base de donnees | SQLite | 3.x | Simplicite dev, migration facile vers MySQL/PostgreSQL |
| Auth | Laravel Sanctum | - | Tokens SPA, sessions, API tokens |
| Cache | File | - | Simple pour dev, Redis en production |

### Frontend

| Composant | Choix | Justification |
|-----------|-------|---------------|
| Templates | Blade | Natif Laravel, performances |
| CSS | TailwindCSS | Utilitaire, rapide a prototyper |
| JavaScript | Vanilla JS | Leger, pas de dependance framework |
| AJAX | Fetch API | Natif, promesses |

---

## Architecture Base de Donnees

### Tables Principales (26 tables)

#### Comptes & Personnages
- `comptes` - Comptes utilisateurs (email, mot de passe, is_admin)
- `personnages` - Personnages jouables (stats, PA, experience)
- `personal_access_tokens` - Tokens Sanctum

#### Univers & Navigation
- `systemes_stellaires` - Etoiles (type, couleur, temperature, coordonnees)
- `planetes` - Planetes (type, atmosphere, temperature, habitabilite)
- `decouvertes` - Systemes decouverts par personnage
- `universe_configs` - Configuration generation univers

#### Vaisseaux & Equipement
- `vaisseaux` - Vaisseaux (coque, bouclier, coordonnees, slots armes)
- `objets_spatiaux` - Objets de l'espace (type, position)
- `armes` - Types d'armes (degats, portee, cadence)
- `boucliers` - Types de boucliers (points, resistance, regeneration)

#### Economie
- `ressources` - 21 types de ressources (metaux, gaz, exotiques)
- `gisements` - Gisements sur planetes (quantite, richesse)
- `inventaires` - Inventaires polymorphiques (vaisseau, base, usine)
- `marches` - Marches commerciaux (type, multiplicateurs)
- `prix_marches` - Prix dynamiques par ressource

#### Production
- `recettes` - 11 recettes de fabrication (ingredients, produits)
- `usines` - Usines de production (type, efficacite)

#### Combat
- `ennemis` - 10 types d'ennemis (stats, IA, recompenses)
- `combats` - Combats en cours (tours, degats, butin)

---

## Modeles Eloquent

### Relations Principales

```
Compte
├── hasMany: Personnage
└── belongsTo: personnagePrincipal

Personnage
├── belongsTo: Compte
├── hasMany: Vaisseau
├── hasMany: Decouverte
└── belongsTo: vaisseauActif

Vaisseau
├── belongsTo: Personnage
├── belongsTo: ObjetSpatial
├── hasMany: Inventaire (morph)
├── belongsTo: Arme (x3 slots)
└── belongsTo: Bouclier

SystemeStellaire
├── hasMany: Planete
└── hasMany: Decouverte

Planete
├── belongsTo: SystemeStellaire
├── hasMany: Gisement
└── morphMany: Marche (localisation)
```

### Traits Reutilisables

- `HasInventaire` - Gestion inventaire polymorphique (Vaisseau, Usine)

---

## Systeme de Commandes

### Architecture

```
GameController::executeCommand()
    └── processCommand()
        └── match($action) => methodes privees
```

### Commandes Implementees (35+)

#### Navigation (8)
- `help/aide` - Afficher aide
- `status/statut` - Statut personnage
- `position/pos` - Position actuelle
- `vaisseau/ship` - Info vaisseau
- `deplacer/move` - Deplacement conventionnel
- `saut/jump` - Saut hyperespace
- `scan/scanner` - Scanner systemes
- `carte/map` - Carte decouverte

#### Economie (8)
- `scan-planete/scanp` - Scanner gisements
- `extraire/mine` - Extraire ressources
- `inventaire/inv` - Voir inventaire
- `marche/market` - Marche local
- `prix/prices` - Prix ressources
- `acheter/buy` - Acheter
- `vendre/sell` - Vendre
- `fabriquer/craft` - Fabrication

#### Combat (9)
- `armes/weapons` - Liste armes
- `boucliers/shields` - Liste boucliers
- `equiper/equip` - Equiper equipement
- `etat-combat/combat` - Etat vaisseau
- `reparer/repair` - Reparer coque
- `scanner-ennemis/scane` - Detecter ennemis
- `ennemis/enemies` - Encyclopedie
- `attaquer/attack` - Attaquer
- `fuir/flee` - Fuir combat

---

## Systeme de Combat

### Mecanique de Degats

```
Degats = Base * (1 - Resistance/100)
Absorption = min(Bouclier, Degats)
Degats_Coque = Degats - Absorption
```

### Types d'Armes (5)
- `laser` - Rapide, precis, degats moyens
- `canon` - Lent, degats eleves
- `missile` - Tres lent, tres gros degats
- `plasma` - Degats sur duree
- `emp` - Desactive systemes

### Types de Boucliers (4)
- `energie` - Standard, faible vs EMP
- `coque` - Blindage, bon vs balistique
- `regeneratif` - Regeneration elevee
- `adaptatif` - Equilibre

### IA Ennemis

Tactiques:
- `agressif` - Attaque toujours
- `defensif` - Regenere si bas
- `equilibre` - Standard
- `fuite` - Fuit si blesse

---

## Systeme Economique

### Ressources (21)

| Categorie | Ressources |
|-----------|------------|
| Metaux (10) | FER, ALUMINIUM, BAUXITE, GRAPHITE, ZINC, NICKEL, NIOBIUM, TUNGSTENE, PLATINE, URANIUM |
| Gaz (2) | HYDROGENE, OXYGENE |
| Elementaires (4) | SABLES, ARGILES, GLACES, BITUMES |
| Chimie (1) | ELEMENTS_CHIMIQUES |
| Exotiques (4) | NACRETOILE, ARGETOILE, PLAZETOILE, TYRETOILE |

### Recettes (11)

| Categorie | Recettes |
|-----------|----------|
| Raffinage (4) | RAFF_BAUXITE, ELECTRO_GLACES, RAFF_BITUMES, FUSION_SABLES |
| Alliage (2) | ALLIAGE_ACIER, ALLIAGE_CONDUCTEUR |
| Composant (2) | COMP_CARBURANT, COMP_ELECTRONIQUE |
| Avance (3) | ADV_REACTEUR, ADV_CRISTAL, ADV_STELLAIRE |

### Marches

Types:
- `commercial` - Standard
- `minier` - Specialise brut
- `industriel` - Specialise transforme
- `contrebande` - Marche noir

Prix dynamiques avec multiplicateurs achat/vente et taxe.

---

## Generation Univers

### Modes

- `basic/procedural` - Generation aleatoire
- `gaia` - Import donnees reelles ESA GAIA
- `hybrid` - GAIA + procedural (defaut)

### Algorithmes

- Distribution Titius-Bode pour distances planetes
- Loi de Kepler pour periodes orbitales
- Zone habitable selon type etoile
- Temperature selon distance et puissance stellaire

---

## Points d'Action (PA)

### Configuration

```php
'pa' => [
    'depart' => 24,
    'max' => 36,
    'recuperation_taux' => 1,      // PA/heure
    'recuperation_delai' => 60,    // minutes
]
```

### Couts

| Action | PA |
|--------|-----|
| Scan systeme | 1 |
| Scan planete | 1 |
| Extraction (par 10k) | 1 |
| Fabrication (par mult) | 1 |

---

## Services

### UniverseGeneratorService

- `genererSystemeAleatoire()` - Systeme procedural
- `genererSystemeSolaire()` - Sol et planetes
- `genererSystemesVoisins()` - Systemes proches
- `genererPlanetesSysteme()` - Planetes d'un systeme

---

## Seeders

| Seeder | Donnees |
|--------|---------|
| GameSeeder | Compte test, personnage, vaisseau |
| RessourceSeeder | 21 ressources |
| RecetteSeeder | 11 recettes |
| EquipementSeeder | 11 armes, 7 boucliers |
| EnnemiSeeder | 10 types ennemis |
| GaiaSeeder | Etoiles reelles |
| UniverseSeeder | Sol + systemes voisins |
| MarcheSeeder | 4 marches |

---

## Middleware

### EnsureHasActivePersonnage

- Verifie authentification
- Verifie personnage principal
- Retourne JSON pour requetes AJAX
- Injecte personnage dans request

---

## Routes

### Web (authentifiees)

```
GET  /dashboard        - Tableau de bord
GET  /game             - Interface console
POST /command          - Executer commande
GET  /personnage       - Selection personnage
POST /personnage       - Creer personnage
```

### API

```
GET  /api/status       - Statut personnage
GET  /api/vaisseau     - Info vaisseau
GET  /api/inventaire   - Inventaire
```

---

## Configuration

### game.php

```php
return [
    'personnage' => [...],
    'pa' => [...],
    'combat' => [...],
    'univers' => [...],
    'scan' => [...],
];
```

### universe.php

```php
return [
    'generation_mode' => 'hybrid',
    'gaia' => [...],
];
```

---

## Securite

- Authentification Sanctum
- CSRF protection
- Validation Request
- Middleware authorization
- Pas de secrets en dur

---

## Performance

- Eager loading relations
- Index sur colonnes frequentes
- Cache configuration
- Transactions pour operations complexes

---

## Tests

Structure preparee dans `tests/`:
- Feature tests pour endpoints
- Unit tests pour services
- A developper selon priorites

---

## Deploiement

### Developpement

```bash
php artisan serve
php artisan migrate:fresh --seed
```

### Production

- MySQL/PostgreSQL au lieu de SQLite
- Redis pour cache/sessions
- Queue worker pour taches longues
- HTTPS obligatoire

---

## Evolutions Prevues

### Phase 4 - Missions
- Systeme de quetes
- Reputation factions
- Achievements

### Phase 5 - Multijoueur
- Guildes
- Commerce P2P
- Chat

### Phase 6 - Colonisation
- Bases planetaires
- Gestion colonies
- Controle territoires

---

## Conclusion

Architecture solide et modulaire permettant:
- Ajout facile de nouvelles commandes
- Extension du systeme economique
- Integration futures mecaniques PvP
- Support multi-univers (prevu dans GDD)

Code maintenable avec separation claire:
- Controllers pour routing
- Services pour logique metier
- Models pour donnees
- Seeders pour initialisation
