# Bilan Technique et Propositions de Refactorisation

**Date :** 31 décembre 2025
**Analysé par :** Claude Code (Opus 4.5)
**Projet :** Conquête Spatiale - Laravel 12.x / PHP 8.3

---

## Table des Matières

1. [Résumé Exécutif](#1-résumé-exécutif)
2. [Analyse des Modèles](#2-analyse-des-modèles)
3. [Analyse des Contrôleurs](#3-analyse-des-contrôleurs)
4. [Analyse des Services et Helpers](#4-analyse-des-services-et-helpers)
5. [Analyse de la Base de Données](#5-analyse-de-la-base-de-données)
6. [Problèmes de Cohérence Globale](#6-problèmes-de-cohérence-globale)
7. [Plan de Refactorisation Proposé](#7-plan-de-refactorisation-proposé)
8. [Annexes](#8-annexes)

---

## 1. Résumé Exécutif

### État Général du Projet

Le projet Conquête Spatiale est un jeu web Laravel **ambitieux et fonctionnel** présentant des mécaniques de jeu sophistiquées (système orbital, scans progressifs, système de PA, combat). Cependant, le développement rapide a créé une **dette technique significative** nécessitant une attention particulière.

### Points Forts
- Architecture Eloquent bien utilisée (relations, morphs)
- Mécaniques de jeu élaborées (orbites, détection, navigation)
- Intégration de données réelles (GAIA, NASA Exoplanets)
- Services spécialisés pour certaines fonctionnalités

### Points Critiques
- **Modèles "God Object"** : Vaisseau (850 lignes), Personnage (580 lignes)
- **Contrôleurs Fat** : AdminController (846 lignes), GameController (3000+ lignes)
- **Duplication massive** : Logique de détection répétée 6 fois
- **Incohérence des unités** : Mélange AL/UA/cUA dans les coordonnées
- **38 fichiers de test** dans la racine à nettoyer

### Métriques Clés

| Composant | Fichiers | Lignes Totales | Problèmes |
|-----------|----------|----------------|-----------|
| Modèles | 31 | ~8,000 | 6 critiques |
| Contrôleurs | 15 | ~6,500 | 4 critiques |
| Services | 8 | ~2,500 | 2 critiques |
| Helpers | 4 | ~550 | 1 critique |
| Migrations | 61 | ~3,000 | 2 critiques |

---

## 2. Analyse des Modèles

### 2.1 Inventaire Complet (31 modèles)

#### Modèles Principaux
| Modèle | Lignes | Complexité | Responsabilités |
|--------|--------|------------|-----------------|
| `Vaisseau` | 850 | ★★★★★ | Combat, scan, orbite, inventaire, propulsion |
| `Personnage` | 583 | ★★★★☆ | PA, scan, découverte, temps, compétences |
| `ObjetSpatial` | 555 | ★★★★☆ | Position, détection, navigation |
| `Planete` | 622 | ★★★☆☆ | Orbite, propriétés, génération |
| `Mine` | 599 | ★★★☆☆ | Extraction, position, accès |
| `Station` | 319 | ★★★☆☆ | Position, détection, marchés |

#### Modèles Secondaires
- `SystemeStellaire`, `ZoneSpatiale`, `Secteur` : Hiérarchie spatiale
- `Arme`, `Bouclier`, `Combat` : Système de combat
- `Marche`, `Produit`, `Inventaire` : Économie
- `Faction`, `Reputation`, `Mission` : Progression
- `Decouverte`, `ScanProgress` : Exploration

### 2.2 Problèmes Critiques

#### A. Modèle `Ennemi` Manquant
```php
// Combat.php - Relation orpheline
public function ennemi(): BelongsTo {
    return $this->belongsTo(Ennemi::class); // MODÈLE NON TROUVÉ
}
```
**Impact :** Erreur runtime si le système de combat est utilisé.

#### B. Logique Dupliquée (6 occurrences)
La même logique de détection est répétée dans :
- `ObjetSpatial::getScoreDetection()`
- `Station::getScoreDetection()`
- `Planete::getScoreDetection()`
- `Mine::getScoreDetection()`
- `ZoneSpatiale::getScoreDetection()`
- `SystemeStellaire::calculerDetectabilite()`

**Solution :** Créer un trait `Detectable`.

#### C. God Models (Trop de Responsabilités)

**Vaisseau.php (850 lignes) contient :**
- Propulsion conventionnelle
- Sauts hyperspatiaux
- Système de combat
- Gestion des scans
- Mécanique orbitale
- Gestion de l'inventaire
- Calculs énergétiques

**Proposition :** Extraire en traits ou services :
- `VaisseauPropulsionTrait`
- `VaisseauCombatTrait`
- `VaisseauScanTrait`
- `OrbitalMechanicsTrait`

#### D. Valeurs Hardcodées
```php
// Vaisseau.php - 21 tiers hardcodés
public function getDiceFormula(): string {
    if ($this->puissance_scan <= 10) return '1d4';
    if ($this->puissance_scan <= 20) return '1d6';
    // ... 19 autres conditions
}
```

**Solution :** Déplacer vers `config/game.php`.

### 2.3 Relations Manquantes

| Modèle Source | Relation Manquante | Type |
|---------------|-------------------|------|
| `Personnage` | → `Combat` | HasMany |
| `Vaisseau` | → `Combat` | HasMany |
| `Planete` | → `Decouverte` | HasMany |
| `Base` | → `Personnage` (propriétaire) | BelongsTo |

### 2.4 Conventions de Nommage

**Problème :** Mélange français/anglais incohérent

| Français | Anglais | Mixte |
|----------|---------|-------|
| `lancerDes()` | `getScoreDetection()` | `marquerDecouvert()` |
| `consommerEnergie()` | `clearCache()` | `peutAcceder()` |
| `vitesse_conventionnelle` | `cache_timestamp` | `coef_dommages` |

**Recommandation :** Standardiser sur l'anglais pour le code, garder le français pour l'UI.

---

## 3. Analyse des Contrôleurs

### 3.1 Inventaire (15 contrôleurs)

| Contrôleur | Lignes | Responsabilités | Statut |
|------------|--------|-----------------|--------|
| `GameController` | 3000+ | Mécaniques de jeu complètes | CRITIQUE |
| `AdminController` | 846 | 24 fonctions différentes | CRITIQUE |
| `TimonerieController` | 1012 | Navigation, sauts, amarrage | À refactoriser |
| `ScanController` | 564 | Système de scan | OK |
| `StationController` | ~300 | Gestion des stations | OK |
| `GarageController` | ~200 | Réparations | OK |
| `VaisseauController` | 222 | Affichage vaisseau | OK |
| `PersonnageController` | 120 | Gestion personnage | OK |
| `MarcheController` | ~150 | Commerce | OK |
| `RavitaillementController` | ~100 | Ravitaillement | OK |
| `ComController` | 80 | Communications | OK |
| `JeuController` | 44 | Profil joueur | OK |

### 3.2 Problèmes Critiques

#### A. Duplication du Pattern d'Extraction du Personnage (30+ occurrences)
```php
// Pattern répété partout
$personnage = $request->attributes->get('personnage');
```

**Solution :** Créer un middleware ou une classe `PersonnageResolver`.

#### B. Incohérence d'Authentification

**Style A (6 contrôleurs) :**
```php
$personnage = Auth::user()->personnageActif;
```

**Style B (5 contrôleurs) :**
```php
$personnage = $request->attributes->get('personnage');
```

**Solution :** Choisir UN seul pattern et l'appliquer partout.

#### C. Fat Controllers

**AdminController (846 lignes) gère :**
- Gestion des comptes
- Gestion de l'univers
- Calculs de puissance/détectabilité
- Gestion des planètes
- Cartographie 2D/3D
- CRUD des gisements
- CRUD des mines
- Création de systèmes

**Proposition :** Découper en :
- `AdminAccountController`
- `AdminUniverseController`
- `AdminPlanetController`
- `AdminMineController`
- `AdminCartographyController`

#### D. Problèmes N+1

```php
// ScanController - Charge TOUS les systèmes
$systemes = SystemeStellaire::all();
foreach ($systemes as $systeme) {
    // Calcul de distance pour chaque système
}
```

**Solution :** Utiliser des clauses `where` pour limiter les résultats.

### 3.3 Injection de Dépendances Incohérente

**Bon (injection constructeur) :**
```php
// TimonerieController
public function __construct(
    NavigationService $navigationService,
    UniverseGeneratorService $universeGenerator
) { ... }
```

**Mauvais (service locator) :**
```php
// PersonnageController
$contextService = app(\App\Services\GameContextService::class);
```

---

## 4. Analyse des Services et Helpers

### 4.1 Inventaire des Services (8 fichiers)

| Service | Lignes | Responsabilité | Statut |
|---------|--------|----------------|--------|
| `UniverseGeneratorService` | 554 | Génération univers | TROP GROS |
| `ExoplanetService` | 423 | API NASA + DB | MIXTE |
| `BackupService` | 355 | Backup/restore | OK |
| `SimbadService` | 239 | API SIMBAD | OK |
| `StarNameMatcher` | 227 | Catalogue étoiles | MAUVAIS TYPE |
| `NavigationService` | 198 | Calculs navigation | OK |
| `GaiaCoordinateConverter` | 163 | Conversion coords | OK |
| `GameContextService` | 75 | Contexte menu | TROP SIMPLE |

### 4.2 Inventaire des Helpers (4 fichiers)

| Helper | Lignes | Responsabilité | Statut |
|--------|--------|----------------|--------|
| `PersonnageLocation` | 300 | Localisation personnage | DEVRAIT ÊTRE SERVICE |
| `CoordinatesHelper` | 149 | Conversions unités | PARFAIT |
| `GameTimeHelper` | 103 | Temps in-game | OK |

### 4.3 Problèmes Critiques

#### A. UniverseGeneratorService Trop Gros (554 lignes)

Gère 6+ responsabilités distinctes :
- Génération de systèmes
- Génération de planètes
- Système solaire hardcodé
- Expansion de l'univers
- Génération de secteurs
- Import d'exoplanètes

**Proposition de découpage :**
```
UniverseGeneratorService →
├── SystemGenerationService
├── PlanetGenerationService
├── SolarSystemService
├── UniverseExpansionService
└── SectorGeneratorService
```

#### B. Logique de Distance Dupliquée (4 emplacements)

```php
// NavigationService.php
sqrt($dx * $dx + $dy * $dy + $dz * $dz)

// ObjetSpatial.php
calculerDistance() // Même calcul

// CoordinatesHelper.php
distance3D() // Même calcul

// GaiaCoordinateConverter.php
calculateDistance() // Même calcul
```

**Solution :** Centraliser dans `CoordinatesHelper::distance3D()`.

#### C. PersonnageLocation N'est Pas un Helper

Avec 300 lignes et de la logique métier complexe (menu de 158 lignes), ce fichier devrait être un Service.

---

## 5. Analyse de la Base de Données

### 5.1 Statistiques

- **Migrations :** 61 fichiers
- **Tables principales :** ~25
- **Seeders :** 13 fichiers

### 5.2 Problème Critique : Incohérence des Unités de Coordonnées

**Situation actuelle :**
```
AL (Années-lumière) → Utilisé dans certains calculs
UA (Unités Astronomiques) → Utilisé pour les orbites
cUA (centi-UA) → Nouveau standard (partiellement migré)

Conversion : 1 AL = 63,241 UA = 6,324,100 cUA
```

**Tables affectées :**
| Table | Champs | Unité Actuelle | Unité Cible |
|-------|--------|----------------|-------------|
| `systemes_stellaires` | position_x/y/z | cUA (converti) | OK |
| `objets_spatiaux` | position_x/y/z | decimal (mixte?) | cUA |
| `planetes` | position_orbitale | UA | UA (OK pour orbites) |
| `stations` | orbite_rayon_ua | UA | UA (OK) |
| `zones_spatiales` | rayon_min/max | cUA | OK |

**Impact :** Calculs de distance incorrects si les unités sont mélangées.

### 5.3 Duplication de Stockage des Positions

Les Stations ont 3 façons de déterminer leur position :
1. Via `planete_id` → FK vers planetes
2. Via `orbite_rayon_ua` + `orbite_angle` (mécanique orbitale)
3. Via `objet_spatial_id` → objets_spatiaux (NOUVEAU)

**Risque :** Désynchronisation des données.

### 5.4 Indexes Manquants

**Foreign Keys sans index :**
- `personnages.compte_id`
- `gisements.decouvert_par`
- `mines.installateur_id`

**Champs fréquemment filtrés sans index :**
- `stations.accessible`
- `missions.actif`
- `vaisseaux.arrime_a_station_id`
- `personnages.dans_station_id`

### 5.5 Fichiers de Test à Nettoyer (38 fichiers)

```
Racine du projet :
├── check_*.php (13 fichiers)
├── test_*.php (15 fichiers)
├── verify_*.php (3 fichiers)
├── fix_*.php (5 fichiers)
├── debug_*.php (1 fichier)
└── analyse_*.php (1 fichier)
```

**Recommandation :** Déplacer vers `database/scripts/` ou supprimer.

---

## 6. Problèmes de Cohérence Globale

### 6.1 Manque de Vision Architecturale Unifiée

| Aspect | État Actuel | État Souhaité |
|--------|-------------|---------------|
| Langue du code | Français/Anglais mixte | Anglais (code) + Français (UI) |
| Récupération personnage | 2 patterns différents | 1 seul pattern via middleware |
| Calcul de distance | 4 implémentations | 1 helper centralisé |
| Détection/Scan | 6 implémentations | 1 trait `Detectable` |
| Unités de coordonnées | AL/UA/cUA mixtes | cUA partout (sauf orbites en UA) |

### 6.2 Documentation Dispersée

Le dossier `docs/` contient 27 fichiers mais :
- Pas de document d'architecture globale
- Pas de diagramme de relations
- Correctifs datés mais pas de changelog unifié

### 6.3 Couplage Fort

```
Vaisseau ←→ Personnage ←→ Combat
    ↓           ↓           ↓
ObjetSpatial ← Station ← Mission
    ↓           ↓
Planete ←→ SystemeStellaire
```

Les dépendances circulaires rendent le refactoring difficile.

---

## 7. Plan de Refactorisation Proposé

### Phase 1 : Corrections Critiques (Immédiat)

#### 1.1 Créer le modèle `Ennemi` manquant
```bash
php artisan make:model Ennemi -m
```

#### 1.2 Nettoyer les fichiers de test
```bash
mkdir database/scripts
mv *.php database/scripts/ 2>/dev/null || true
# Garder uniquement artisan, composer.json, etc.
```

#### 1.3 Standardiser l'extraction du personnage
```php
// Créer app/Http/Middleware/InjectPersonnage.php
public function handle($request, Closure $next) {
    $request->merge([
        'personnage' => Auth::user()?->personnageActif
    ]);
    return $next($request);
}
```

### Phase 2 : Traits et Centralisation (Court terme)

#### 2.1 Créer le trait `Detectable`
```php
// app/Traits/Detectable.php
trait Detectable {
    public function getScoreDetection(...): float { ... }
    public function calculerDetectabilite(): float { ... }
    public function marquerDecouvert(): void { ... }
}
```

**Appliquer à :** ObjetSpatial, Station, Planete, Mine, ZoneSpatiale

#### 2.2 Créer le trait `Positionable`
```php
// app/Traits/Positionable.php
trait Positionable {
    public function getPositionAbsolue(): array { ... }
    public function getPositionEffective($timestamp): array { ... }
    public function calculerDistance($autre): float { ... }
}
```

#### 2.3 Centraliser les calculs de distance
```php
// Modifier CoordinatesHelper.php
public static function distance3D(...): float {
    // Implémentation unique
}

// Supprimer les duplications dans:
// - NavigationService
// - GaiaCoordinateConverter
// - ObjetSpatial
```

### Phase 3 : Découpage des Gros Fichiers (Moyen terme)

#### 3.1 Découper AdminController
```
AdminController (846 lignes) →
├── Admin/AccountController (~100 lignes)
├── Admin/UniverseController (~200 lignes)
├── Admin/PlanetController (~150 lignes)
├── Admin/MineController (~150 lignes)
└── Admin/CartographyController (~150 lignes)
```

#### 3.2 Découper UniverseGeneratorService
```
UniverseGeneratorService (554 lignes) →
├── Services/Universe/SystemGeneratorService
├── Services/Universe/PlanetGeneratorService
├── Services/Universe/SolarSystemService
└── Services/Universe/SectorGeneratorService
```

#### 3.3 Extraire la logique de Vaisseau
```
Vaisseau.php (850 lignes) →
├── Vaisseau.php (~300 lignes, core)
├── Traits/VaisseauPropulsion.php
├── Traits/VaisseauCombat.php
├── Traits/VaisseauScan.php
└── Traits/VaisseauOrbital.php
```

### Phase 4 : Optimisations (Long terme)

#### 4.1 Ajouter les indexes manquants
```php
// Migration
Schema::table('personnages', function (Blueprint $table) {
    $table->index('compte_id');
    $table->index('dans_station_id');
});

Schema::table('vaisseaux', function (Blueprint $table) {
    $table->index('arrime_a_station_id');
});
```

#### 4.2 Résoudre l'incohérence des coordonnées
1. Documenter clairement les unités attendues
2. Créer des méthodes de conversion explicites
3. Migrer progressivement vers cUA

#### 4.3 Implémenter des Services manquants
- `CharacterCreationService`
- `CombatService`
- `DiscoveryService`
- `ShipMovementService`

### Phase 5 : Documentation (Continu)

#### 5.1 Créer ARCHITECTURE.md
- Diagramme des relations entre modèles
- Flux de données principal
- Conventions de nommage adoptées

#### 5.2 Maintenir CHANGELOG.md
- Historique des modifications majeures
- Migrations importantes
- Breaking changes

---

## 8. Annexes

### A. Commandes Utiles

```bash
# Vérifier la syntaxe PHP de tous les fichiers
find app -name "*.php" -exec php -l {} \;

# Lister les routes
php artisan route:list --name=station

# Analyser les modèles
php artisan model:show Vaisseau

# Vider les caches
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

### B. Fichiers les Plus Critiques à Refactoriser

1. `app/Models/Vaisseau.php` (850 lignes)
2. `app/Http/Controllers/AdminController.php` (846 lignes)
3. `app/Http/Controllers/GameController.php` (3000+ lignes)
4. `app/Services/UniverseGeneratorService.php` (554 lignes)
5. `app/Models/Personnage.php` (583 lignes)

### C. Priorités de Correction

| Priorité | Action | Effort | Impact |
|----------|--------|--------|--------|
| P0 | Créer modèle Ennemi | 1h | Critique |
| P0 | Nettoyer fichiers racine | 30min | Hygiène |
| P1 | Créer trait Detectable | 4h | Réduction duplication |
| P1 | Standardiser auth pattern | 2h | Cohérence |
| P2 | Découper AdminController | 8h | Maintenabilité |
| P2 | Ajouter indexes | 2h | Performance |
| P3 | Découper UniverseGenerator | 6h | Maintenabilité |
| P3 | Résoudre unités coords | 8h | Intégrité données |

---

## Conclusion

Le projet Conquête Spatiale est **fonctionnel et ambitieux** mais souffre d'une dette technique accumulée lors du développement rapide. Les problèmes principaux sont :

1. **Duplication de code** (détection, distance, auth)
2. **Fichiers trop gros** (God Objects/Fat Controllers)
3. **Incohérences** (langue, unités, patterns)
4. **Fichiers orphelins** (38 scripts de test)

En suivant le plan de refactorisation proposé, le projet gagnera en :
- **Maintenabilité** : Code plus facile à comprendre et modifier
- **Testabilité** : Composants découplés testables unitairement
- **Performance** : Indexes appropriés et requêtes optimisées
- **Fiabilité** : Moins de bugs liés aux duplications

**Effort estimé total :** 40-60 heures de développement sur 4-6 semaines.

---

## 9. Instructions Actionables pour Claude Code

Cette section contient des instructions précises et du code prêt à l'emploi pour implémenter les corrections.

### 9.1 TÂCHE P0-1 : Créer le Modèle Ennemi

**Fichier à créer :** `app/Models/Ennemi.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ennemi extends Model
{
    use HasFactory;

    protected $table = 'ennemis';

    protected $fillable = [
        'nom',
        'type',
        'niveau',
        'points_vie_max',
        'points_vie_actuels',
        'attaque',
        'defense',
        'vitesse',
        'experience_donnee',
        'credits_donnes',
        'loot_table',
        'description',
    ];

    protected $casts = [
        'loot_table' => 'array',
    ];

    public function combats(): HasMany
    {
        return $this->hasMany(Combat::class);
    }
}
```

**Migration à créer :** `database/migrations/YYYY_MM_DD_create_ennemis_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ennemis', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type')->default('pirate'); // pirate, alien, drone, etc.
            $table->integer('niveau')->default(1);
            $table->integer('points_vie_max')->default(100);
            $table->integer('points_vie_actuels')->default(100);
            $table->integer('attaque')->default(10);
            $table->integer('defense')->default(5);
            $table->integer('vitesse')->default(5);
            $table->integer('experience_donnee')->default(50);
            $table->integer('credits_donnes')->default(100);
            $table->json('loot_table')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ennemis');
    }
};
```

**Commandes à exécuter :**
```bash
php artisan make:migration create_ennemis_table
# Copier le contenu ci-dessus dans la migration
php artisan migrate
```

---

### 9.2 TÂCHE P0-2 : Nettoyer les Fichiers de Test de la Racine

**Script de nettoyage (à exécuter manuellement) :**

```bash
# Créer le dossier de destination
mkdir -p database/scripts

# Déplacer les fichiers check_*
mv check_columns.php database/scripts/ 2>/dev/null
mv check_db_planets.php database/scripts/ 2>/dev/null
mv check_distances_planetes.php database/scripts/ 2>/dev/null
mv check_objet_spatial.php database/scripts/ 2>/dev/null
mv check_orbital_data.php database/scripts/ 2>/dev/null
mv check_planete_columns.php database/scripts/ 2>/dev/null
mv check_raw_distances.php database/scripts/ 2>/dev/null
mv check_sol.php database/scripts/ 2>/dev/null
mv check_sol_detail.php database/scripts/ 2>/dev/null
mv check_sol_nearby_planets.php database/scripts/ 2>/dev/null
mv check_stations.php database/scripts/ 2>/dev/null
mv check_systems_status.php database/scripts/ 2>/dev/null

# Déplacer les fichiers test_*
mv test_distances_finales.php database/scripts/ 2>/dev/null
mv test_dynamic_generation.php database/scripts/ 2>/dev/null
mv test_nasa_nearby.php database/scripts/ 2>/dev/null
mv test_nasa_proxima.php database/scripts/ 2>/dev/null
mv test_nasa_simple.php database/scripts/ 2>/dev/null
mv test_orbital_system.php database/scripts/ 2>/dev/null
mv test_other_stars_nasa.php database/scripts/ 2>/dev/null
mv test_proxima_planets.php database/scripts/ 2>/dev/null
mv test_proxima_raw_data.php database/scripts/ 2>/dev/null
mv test_proxima_variants.php database/scripts/ 2>/dev/null
mv test_seeder_conversions.php database/scripts/ 2>/dev/null
mv test_simbad_api.php database/scripts/ 2>/dev/null
mv test_simple_positions.php database/scripts/ 2>/dev/null
mv test_timonerie_distances.php database/scripts/ 2>/dev/null
mv test_with_procgen.php database/scripts/ 2>/dev/null

# Déplacer les autres fichiers utilitaires
mv verify_positions_cua.php database/scripts/ 2>/dev/null
mv verify_systeme_positions.php database/scripts/ 2>/dev/null
mv fix_missing_orbital_data.php database/scripts/ 2>/dev/null
mv fix_systeme_positions.php database/scripts/ 2>/dev/null
mv fix_test_personnage.php database/scripts/ 2>/dev/null
mv debug_scan_sol.php database/scripts/ 2>/dev/null
mv analyse_detectabilite_sol.php database/scripts/ 2>/dev/null
mv list_gaia_stars.php database/scripts/ 2>/dev/null
mv count_10al.php database/scripts/ 2>/dev/null

# Supprimer le fichier malformé (0 bytes)
rm -f "E:devlogphplaravelconquete-spatialeappHttpControllersStationController.php" 2>/dev/null
```

---

### 9.3 TÂCHE P1-1 : Créer le Trait Detectable

**Fichier à créer :** `app/Traits/Detectable.php`

```php
<?php

namespace App\Traits;

use App\Helpers\CoordinatesHelper;
use App\Models\Personnage;

trait Detectable
{
    /**
     * Calcule le score de détection de cet objet par un personnage
     *
     * @param Personnage $personnage Le personnage qui tente la détection
     * @param float $distanceCua Distance en cUA entre l'objet et le personnage
     * @param int $puissanceScan Puissance du scanner utilisé
     * @return float Score de détection (0-100+)
     */
    public function getScoreDetection(Personnage $personnage, float $distanceCua, int $puissanceScan): float
    {
        // Récupérer la détectabilité de base de l'objet
        $detectabiliteBase = $this->detectabilite_base ?? 0;

        // Facteur de distance (plus c'est loin, plus c'est dur)
        $facteurDistance = 1;
        if ($distanceCua > 0) {
            // Conversion en UA pour le calcul
            $distanceUa = CoordinatesHelper::cuaToUa($distanceCua);
            $facteurDistance = max(0.1, 1 - ($distanceUa / 1000));
        }

        // Score = (détectabilité * puissance_scan * facteur_distance) / 100
        $score = ($detectabiliteBase * $puissanceScan * $facteurDistance) / 100;

        return round($score, 2);
    }

    /**
     * Calcule la détectabilité effective de l'objet
     * Prend en compte les modificateurs (camouflage, taille, émissions, etc.)
     *
     * @return float Détectabilité effective
     */
    public function calculerDetectabilite(): float
    {
        $base = $this->detectabilite_base ?? 0;

        // Modificateur de masse (plus gros = plus détectable)
        $masse = $this->masse ?? 0;
        $modMasse = $masse > 0 ? log10($masse + 1) * 5 : 0;

        // Modificateur d'activité (si applicable)
        $modActivite = 0;
        if (property_exists($this, 'est_actif') && $this->est_actif) {
            $modActivite = 10;
        }

        return max(0, $base + $modMasse + $modActivite);
    }

    /**
     * Marque l'objet comme découvert (POI connu)
     *
     * @return void
     */
    public function marquerDecouvert(): void
    {
        if (property_exists($this, 'poi_connu')) {
            $this->poi_connu = true;
            $this->save();
        }
    }

    /**
     * Vérifie si l'objet est découvert
     *
     * @return bool
     */
    public function estDecouvert(): bool
    {
        return $this->poi_connu ?? false;
    }
}
```

**Fichiers à modifier pour utiliser le trait :**

1. `app/Models/ObjetSpatial.php` - Ajouter :
```php
use App\Traits\Detectable;

class ObjetSpatial extends Model
{
    use HasFactory, Detectable;
    // ... supprimer les méthodes getScoreDetection, calculerDetectabilite, marquerDecouvert
}
```

2. `app/Models/Station.php` - Ajouter :
```php
use App\Traits\Detectable;

class Station extends Model
{
    use HasFactory, Detectable;
    // ... supprimer les méthodes dupliquées
}
```

3. `app/Models/Planete.php` - Ajouter :
```php
use App\Traits\Detectable;

class Planete extends Model
{
    use HasFactory, Detectable;
    // ... supprimer les méthodes dupliquées
}
```

4. `app/Models/Mine.php` - Ajouter :
```php
use App\Traits\Detectable;

class Mine extends Model
{
    use HasFactory, Detectable;
    // ... supprimer les méthodes dupliquées
}
```

5. `app/Models/ZoneSpatiale.php` - Ajouter :
```php
use App\Traits\Detectable;

class ZoneSpatiale extends Model
{
    use HasFactory, Detectable;
    // ... supprimer les méthodes dupliquées
}
```

---

### 9.4 TÂCHE P1-2 : Standardiser l'Extraction du Personnage

**Fichier à créer :** `app/Http/Middleware/InjectPersonnage.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectPersonnage
{
    /**
     * Injecte le personnage actif dans la requête
     */
    public function handle(Request $request, Closure $next): Response
    {
        $personnage = null;

        if (Auth::check()) {
            $user = Auth::user();
            $personnage = $user->personnageActif;
        }

        // Injecter dans les attributs de la requête (méthode standard)
        $request->attributes->set('personnage', $personnage);

        // Aussi disponible via $request->personnage pour commodité
        $request->merge(['personnage' => $personnage]);

        return $next($request);
    }
}
```

**Modifier `app/Http/Kernel.php`** (ou `bootstrap/app.php` pour Laravel 11+) :

```php
// Dans les middlewareGroups 'web', ajouter :
\App\Http\Middleware\InjectPersonnage::class,
```

**Pattern unifié à utiliser dans TOUS les contrôleurs :**

```php
// AVANT (incohérent) :
$personnage = Auth::user()->personnageActif;
// OU
$personnage = $request->attributes->get('personnage');

// APRÈS (unifié) :
$personnage = $request->attributes->get('personnage');

// Avec vérification si nécessaire :
$personnage = $request->attributes->get('personnage');
if (!$personnage) {
    return redirect()->route('personnage.selection')
        ->with('error', 'Aucun personnage actif.');
}
```

---

### 9.5 TÂCHE P1-3 : Centraliser les Calculs de Distance

**Modifier `app/Helpers/CoordinatesHelper.php`** - Ajouter cette méthode si elle n'existe pas déjà ou la rendre plus complète :

```php
/**
 * Calcule la distance 3D entre deux points
 *
 * @param int $x1 Position X du point 1 (en cUA)
 * @param int $y1 Position Y du point 1 (en cUA)
 * @param int $z1 Position Z du point 1 (en cUA)
 * @param int $x2 Position X du point 2 (en cUA)
 * @param int $y2 Position Y du point 2 (en cUA)
 * @param int $z2 Position Z du point 2 (en cUA)
 * @return float Distance en cUA
 */
public static function distance3D(
    int $x1, int $y1, int $z1,
    int $x2, int $y2, int $z2
): float {
    $dx = $x2 - $x1;
    $dy = $y2 - $y1;
    $dz = $z2 - $z1;

    return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
}

/**
 * Calcule la distance entre deux tableaux de position
 *
 * @param array $pos1 ['x' => int, 'y' => int, 'z' => int] en cUA
 * @param array $pos2 ['x' => int, 'y' => int, 'z' => int] en cUA
 * @return float Distance en cUA
 */
public static function distanceEntrePositions(array $pos1, array $pos2): float
{
    return self::distance3D(
        $pos1['x'] ?? $pos1['position_x'] ?? 0,
        $pos1['y'] ?? $pos1['position_y'] ?? 0,
        $pos1['z'] ?? $pos1['position_z'] ?? 0,
        $pos2['x'] ?? $pos2['position_x'] ?? 0,
        $pos2['y'] ?? $pos2['position_y'] ?? 0,
        $pos2['z'] ?? $pos2['position_z'] ?? 0
    );
}

/**
 * Calcule la distance totale (secteur + position locale)
 *
 * @param array $pos1 Position complète avec secteur_x/y/z et position_x/y/z
 * @param array $pos2 Position complète avec secteur_x/y/z et position_x/y/z
 * @return float Distance en cUA
 */
public static function distanceTotale(array $pos1, array $pos2): float
{
    // Convertir les secteurs en cUA (1 secteur = 1 AL = 6,324,100 cUA)
    $cuaParAl = 6324100;

    $x1 = ($pos1['secteur_x'] ?? 0) * $cuaParAl + ($pos1['position_x'] ?? 0);
    $y1 = ($pos1['secteur_y'] ?? 0) * $cuaParAl + ($pos1['position_y'] ?? 0);
    $z1 = ($pos1['secteur_z'] ?? 0) * $cuaParAl + ($pos1['position_z'] ?? 0);

    $x2 = ($pos2['secteur_x'] ?? 0) * $cuaParAl + ($pos2['position_x'] ?? 0);
    $y2 = ($pos2['secteur_y'] ?? 0) * $cuaParAl + ($pos2['position_y'] ?? 0);
    $z2 = ($pos2['secteur_z'] ?? 0) * $cuaParAl + ($pos2['position_z'] ?? 0);

    return sqrt(
        pow($x2 - $x1, 2) +
        pow($y2 - $y1, 2) +
        pow($z2 - $z1, 2)
    );
}
```

**Fichiers à modifier pour utiliser CoordinatesHelper :**

1. `app/Services/NavigationService.php` - Remplacer le calcul manuel par :
```php
use App\Helpers\CoordinatesHelper;

// Remplacer :
// $distance = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
// Par :
$distance = CoordinatesHelper::distance3D($x1, $y1, $z1, $x2, $y2, $z2);
```

2. `app/Services/GaiaCoordinateConverter.php` - Supprimer `calculateDistance()` et utiliser CoordinatesHelper

3. `app/Models/ObjetSpatial.php` - Si une méthode `calculerDistance()` existe, la faire déléguer à CoordinatesHelper

---

### 9.6 TÂCHE P2-1 : Ajouter les Indexes Manquants

**Migration à créer :** `database/migrations/YYYY_MM_DD_add_missing_indexes.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexes sur personnages
        Schema::table('personnages', function (Blueprint $table) {
            $table->index('compte_id');
            $table->index('dans_station_id');
            $table->index('vaisseau_actif_id');
        });

        // Indexes sur vaisseaux
        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->index('arrime_a_station_id');
        });

        // Indexes sur stations
        Schema::table('stations', function (Blueprint $table) {
            $table->index('accessible');
        });

        // Indexes sur missions
        Schema::table('missions', function (Blueprint $table) {
            $table->index('actif');
        });

        // Indexes sur gisements
        Schema::table('gisements', function (Blueprint $table) {
            $table->index('decouvert_par');
            $table->index('exploite_par');
        });

        // Indexes sur mines
        Schema::table('mines', function (Blueprint $table) {
            $table->index('installateur_id');
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropIndex(['compte_id']);
            $table->dropIndex(['dans_station_id']);
            $table->dropIndex(['vaisseau_actif_id']);
        });

        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->dropIndex(['arrime_a_station_id']);
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->dropIndex(['accessible']);
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->dropIndex(['actif']);
        });

        Schema::table('gisements', function (Blueprint $table) {
            $table->dropIndex(['decouvert_par']);
            $table->dropIndex(['exploite_par']);
        });

        Schema::table('mines', function (Blueprint $table) {
            $table->dropIndex(['installateur_id']);
        });
    }
};
```

---

### 9.7 Checklist de Vérification

Après chaque tâche, exécuter ces commandes pour vérifier :

```bash
# Vérifier la syntaxe PHP
php -l app/Models/Ennemi.php
php -l app/Traits/Detectable.php
php -l app/Http/Middleware/InjectPersonnage.php

# Vérifier que les migrations passent
php artisan migrate --pretend

# Vérifier les routes
php artisan route:list

# Vider les caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Tester que l'application démarre
php artisan serve --port=8001
# Ouvrir http://localhost:8001 dans le navigateur
```

---

### 9.8 Ordre d'Exécution Recommandé

```
1. [ ] P0-1 : Créer modèle Ennemi + migration
2. [ ] P0-2 : Nettoyer fichiers de test (déplacer vers database/scripts/)
3. [ ] P1-1 : Créer trait Detectable
4. [ ] P1-1b : Appliquer trait aux 5 modèles (ObjetSpatial, Station, Planete, Mine, ZoneSpatiale)
5. [ ] P1-2 : Créer middleware InjectPersonnage
6. [ ] P1-2b : Mettre à jour les contrôleurs pour utiliser le pattern unifié
7. [ ] P1-3 : Centraliser calculs de distance dans CoordinatesHelper
8. [ ] P2-1 : Créer migration pour indexes manquants
9. [ ] TESTS : Vérifier que tout fonctionne
```

---

### 9.9 Fichiers Clés à Connaître

| Fichier | Rôle | Lignes | À Refactoriser |
|---------|------|--------|----------------|
| `app/Models/Vaisseau.php` | Vaisseau du joueur | ~850 | Oui (extraire traits) |
| `app/Models/Personnage.php` | Personnage joueur | ~583 | Oui (extraire logique) |
| `app/Models/ObjetSpatial.php` | Base géométrique | ~555 | Oui (utiliser trait) |
| `app/Http/Controllers/AdminController.php` | Admin complet | ~846 | Oui (découper) |
| `app/Http/Controllers/TimonerieController.php` | Navigation | ~1012 | Partiellement |
| `app/Services/UniverseGeneratorService.php` | Génération univers | ~554 | Oui (découper) |
| `app/Helpers/CoordinatesHelper.php` | Conversions | ~149 | Enrichir |
| `config/game.php` | Config jeu | Variable | Ajouter constantes |

---

### 9.10 Constantes de Conversion à Connaître

```php
// À mettre dans config/game.php ou CoordinatesHelper
const CUA_PAR_UA = 100;           // 1 UA = 100 cUA
const UA_PAR_AL = 63241;          // 1 AL = 63,241 UA
const CUA_PAR_AL = 6324100;       // 1 AL = 6,324,100 cUA

// Exemple d'utilisation
$distanceEnCua = $distanceEnAl * CUA_PAR_AL;
$distanceEnUa = $distanceEnCua / CUA_PAR_UA;
```

---

*Document généré le 31 décembre 2025 par Claude Code (Opus 4.5)*
*Section 9 ajoutée pour permettre l'exécution par Claude Code Sonnet*
