# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Conquête Spatiale** — a turn-based galactic exploration web game built with Laravel 12. PHP 8.3+, MariaDB/MySQL, Blade templates, TailwindCSS, Vanilla JS. The game universe uses real ESA GAIA DR3 star data or procedural generation.

Test account: `test` / `password` — character "John Stark", ship "Explorer-01".

---

## Commands

### Development

```bash
# Start full dev stack (server + queue + logs + vite)
composer dev

# Start just the PHP server
php artisan serve

# Fresh DB with all seeders
php artisan migrate:fresh --seed --seeder=GameSeeder

# Run tests
composer test
# Or: php artisan test
# Single test: php artisan test --filter=TestClassName
```

### Code Quality

```bash
# PHP syntax check
php -l app/Http/Controllers/SomeController.php

# Blade lint
vendor/bin/blade-lint resources/views/

# Laravel Pint (code style)
vendor/bin/pint
```

### Game-Specific Artisan Commands

```bash
# Import real GAIA DR3 star data from ESA
php artisan gaia:import-real --radius=200 --limit=3000
php artisan gaia:import-real --insecure   # if SSL error

# Generate procedural stars (if ESA API unavailable)
php artisan gaia:generate --count=500 --radius=150

# Recalculate stellar/planet values after formula changes
php artisan cs:recalculer-valeurs --dry-run  # always dry-run first
php artisan cs:recalculer-valeurs --type=systemes|planetes|all
php artisan systemes:recalculer-puissance --filter=G

# Backups (always before mass updates)
php artisan backup:create
php artisan backup:list
php artisan backup:restore

# Debug / maintenance
php artisan db:reset-game
php artisan fix:solar-system-discoveries
```

### Tinker Debugging

```php
// Check a model's DB columns (not temp attributes)
$m->getAttributes();

// Log SQL queries
\DB::enableQueryLog();
// ... code ...
dd(\DB::getQueryLog());

// Check loaded relations
$m->getRelations();
$m->relationLoaded('planetes');
```

---

## Architecture

### MVC Layout

```
app/
  Http/
    Controllers/       # Route handlers
    Middleware/        # EnsureHasActivePersonnage, EnsureIsAdmin, IsAdmin
  Models/              # Eloquent models
  Services/            # Business logic (Navigation, UniverseGenerator, GameContext, Backup)
  Helpers/             # DistanceHelper, GameTimeHelper, PersonnageLocation
  Traits/              # HasInventaire (polymorphic inventory)
  Console/Commands/    # Custom artisan commands

routes/
  web.php              # Main game routes
  backend.php          # Admin routes
  api.php              # JSON API endpoints

resources/views/
  game/                # Main game views (console, timonerie, navire/*, station/*)
  admin/               # Admin panel
  backend/             # Backend views
```

### Key Controllers

| Controller | Responsibility |
|---|---|
| `GameController` | Console command execution, character selection/creation, map |
| `TimonerieController` | Hyperspace navigation UI (the main navigation interface) |
| `VaisseauController` | Ship state, cargo, crew |
| `StationController` | Station hall, hangar, market, missions, cantina |
| `PersonnageController` | Character sheet, spatiocarte |
| `AdminController` | Universe map, system grid |
| `BackendController` | Admin backend |

### Key Services

| Service | Responsibility |
|---|---|
| `NavigationService` | Hyperspace jumps, approach/docking, jump calculations |
| `UniverseGeneratorService` | Star system generation (basic, GAIA, hybrid modes) |
| `GameContextService` | Shared game state retrieval |
| `BackupService` | DB backup/restore |
| `GaiaCoordinateConverter` | ESA GAIA → game coordinate conversion |

### Core Model Relations

```
Compte (user account, stores credits)
  └── hasMany: Personnage
  └── belongsTo: personnagePrincipal

Personnage (player character — stats, PA, skills)
  └── belongsTo: Compte
  └── belongsTo: vaisseauActif
  └── hasMany: Decouverte (discovered systems)

Vaisseau (ship — hull, shields, weapon slots, position)
  └── belongsTo: Personnage
  └── belongsTo: ObjetSpatial (spatial position)
  └── belongsToMany: Arme (3 weapon slots via arme_1_id, arme_2_id, arme_3_id)
  └── belongsTo: Bouclier
  └── morphMany: Inventaire

SystemeStellaire (star system)
  └── hasMany: Planete
  └── hasMany: Decouverte

Planete
  └── belongsTo: SystemeStellaire
  └── hasMany: Gisement (resource deposits)
  └── morphMany: Marche
```

### Middleware Flow

`auth` → `personnage.actif` (`EnsureHasActivePersonnage`) → injects `$request->attributes->get('personnage')` into all protected routes. Controllers always retrieve the personnage this way.

---

## Coordinate System — CRITICAL RULES

Every spatial object has 6 coordinate fields: `secteur_x/y/z` (integers, light-years) + `position_x/y/z` (decimals 0.0–1.0, sub-sector position).

**NEVER multiply `secteur_x/y/z` by 10. NEVER divide by 10.** The sector values ARE the light-year coordinates directly.

```php
// CORRECT
$x_carte = $systeme->secteur_x;  // e.g. 4 = 4 light-years

// WRONG
$x_carte = $systeme->secteur_x * 10;  // BUG!

// Grid indexing: Vega Aurigae at secteur_z=4 is in $grille[0][0][4], NOT [0][0][0]
```

Full 3D distance calculation must use both parts:
```php
$dx = ($a->secteur_x + $a->position_x) - ($b->secteur_x + $b->position_x);
```

---

## Game Systems

### Points d'Action (PA)
- 1 PA = 0.5 in-game day; regenerates 1 PA/hour real time; max 36 PA
- In-game time only advances when a player's PA reach 0 and they are offline
- Costs: scan = 1 PA, extract per 10k units = 1 PA, crafting per multiplier = 1 PA

### Game Time
- Universe epoch: 1 January 3000 00:00
- `GameTimeHelper::getDateActuelleJeu($personnage)` computes current in-game date

### Console Command System (`GameController`)
```
executeCommand() → processCommand() → match($action) → private methods
```
All in-game commands (`help`, `status`, `saut`, `attaquer`, etc.) are handled in `GameController` via a large `match` switch. Adding a new command means adding a branch there.

### Timonerie (Navigation UI)
The `TimonerieController` / `resources/views/game/navire/timonerie.blade.php` is the main navigation interface. It uses Three.js for a full-screen HUD displaying the star system. Jump calculations use the DHmini system (Daggerheart-based dice mechanics).

### Navigation — Hyperspace Jump Formula
```
Score d'Erreur = 50 - (2d12 + Connaissance + Astrogation + Ordinateur + Module)
Delta X = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 100) × Distance
```
Implemented in `NavigationService`.

### Combat System
Damage formula: `Dégâts_Coque = Base × (1 - Resistance/100) - Absorption(Bouclier)`
Enemy AI tactics: `agressif`, `defensif`, `equilibre`, `fuite`

### Economy
- 21 resource types (metals, gas, elementary, chemical, exotic)
- 11 crafting recipes in 4 categories (raffinage, alliage, composant, avance)
- Dynamic prices via `PrixMarche` with buy/sell multipliers per market type

---

## Eloquent Pitfalls to Avoid

**1. Temporary attributes on models** — Never assign display-only properties directly to an Eloquent model if any subsequent code may call `save()` or `update()`. Use a stdClass DTO instead:
```php
// WRONG: $planete->icone = '🌍'; then $planete->getDonneesOrbitales() may save
// CORRECT:
$poi = (object)['id' => $planete->id, 'icone' => '🌍', ...];
```

**2. @json() in Blade with complex expressions** — `@json()` cannot contain ternary operators with inline arrays. Prepare the array in the controller and pass a simple variable.

**3. Verify relations exist** before using `whereHas()` — check `DESCRIBE table_name` to confirm the FK column exists.

**4. Pass required objects as parameters** rather than loading them via relations inside methods, to avoid null-dereference errors.

---

## Seeders

Run individually or all via `GameSeeder`:

| Seeder | Data |
|---|---|
| `GameSeeder` | Test account, character, ship |
| `RessourceSeeder` | 21 resource types |
| `RecetteSeeder` | 11 crafting recipes |
| `EquipementSeeder` | 11 weapons, 7 shields |
| `EnnemiSeeder` | 10 enemy types |
| `GaiaSeeder` | Real star data (from CSV) |
| `UniverseSeeder` | Sol + nearby systems |
| `MarcheSeeder` | 4 market types |

---

## Configuration Files

- `config/game.php` — PA rates, combat params, universe settings, scan range
- `config/universe.php` — generation mode (`basic`, `gaia`, `hybrid`), GAIA source
- `database/data/gaia_nearby_stars.csv` — GAIA CSV used by `GaiaSeeder`

---

## Documentation

All GDD (Game Design Documents) are in `docs/game-design/`. Key references:
- `docs/game-design/GDD_Central.md` — index
- `docs/game-design/SYSTEME_COORDONNEES.md` — coordinate rules (critical)
- `docs/game-design/GDD_Navigation.md` — hyperspace jump math
- `docs/game-design/SYSTEM_JEU_DHmini.md` — dice system (Daggerheart adaptation)
- `docs/game-design/SYSTEME_TEMPOREL.md` — PA/time system
- `docs/ASTUCES_DEVELOPPEMENT.md` — Eloquent pitfalls with examples
- `Commande_artisan_cs.md` — custom artisan command reference
