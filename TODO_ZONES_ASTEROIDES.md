# TODO - Système Zones Spatiales & Architecture Objets

**Date :** 2025-12-31
**Contexte :** Mise en place zones d'astéroïdes + clarification architecture Station/Mine

---

## ✅ DÉJÀ CRÉÉ (Pas encore exécuté)

### Fichiers créés dans les 2 derniers échanges :

1. `database/migrations/2025_12_31_120000_create_zones_spatiales_table.php`
2. `database/migrations/2025_12_31_120001_add_parent_to_objets_spatiaux.php`
3. `app/Models/ZoneSpatiale.php`
4. `app/Models/ObjetSpatial.php` (modifié - parent polymorphique)
5. `app/Http/Controllers/ZoneSpatiale Controller.php`
6. `docs/game-design/GDD_Asteroides.md`
7. `docs/game-design/GDD_VISUALISATION_3D.md`

---

## 🚨 PROBLÈMES IDENTIFIÉS

### 1. Station n'hérite PAS d'ObjetSpatial (INCORRECT)

**Actuellement :**
```php
Station {
    // Table séparée sans objet_spatial_id
    planete_id,
    systeme_stellaire_id,
    orbite_rayon_ua,
    orbite_angle,
    // ❌ Pas de position_x/y/z
    // ❌ Pas de secteur_x/y/z
}
```

**Devrait être :**
```php
Station {
    objet_spatial_id (FK), // ← Manquant !
    // Données spécifiques station
    type_station,
    capacite_amarrage,
    commerciale, industrielle, etc.
}

ObjetSpatial (pour station) {
    type: 'station',
    secteur_x/y/z,
    position_x/y/z,
    parent_type: 'Planete' ou 'SystemeStellaire' ou null,
    parent_id,
}
```

### 2. Mine n'hérite PAS d'ObjetSpatial (INCORRECT)

**Actuellement :**
```php
Mine {
    // Table séparée sans objet_spatial_id
    planete_id,
    orbite_rayon_ua,
    // ❌ Pas de position propre
}
```

**Devrait être :**
```php
Mine {
    objet_spatial_id (FK), // ← Manquant !
    type_mine: 'mame' ou 'evoluee',
    gisement_id,
    // Données spécifiques mine
}

ObjetSpatial (pour mine) {
    type: 'mine',
    secteur_x/y/z,
    position_x/y/z,
    parent_type: 'Planete' ou 'Station',
    parent_id,
}
```

### 3. Sémantique "parent" vs "attache_a"

**Clarification utilisateur :**
- `parent` = "contenu dans" (physique ET logique)
- PAS besoin de `attache_a` séparé
- Un objet dans son parent hérite sa position

**Action :** Ne PAS créer attache_a, garder parent unique

---

## 📋 PLAN DE CORRECTION

### Phase 1 : Migrations à exécuter (déjà créées)

```bash
# 1. Exécuter migrations zones spatiales
php artisan migrate

# Résultat attendu :
# - Table zones_spatiales créée
# - Colonnes parent_type/parent_id ajoutées à objets_spatiaux
```

### Phase 2 : Corriger Station (objet_spatial_id)

```bash
# 2. Créer migration pour ajouter objet_spatial_id à stations
# FICHIER À CRÉER : database/migrations/2025_12_31_140000_add_objet_spatial_id_to_stations.php

php artisan make:migration add_objet_spatial_id_to_stations

# 3. Créer ObjetSpatial pour chaque Station existante
php artisan tinker
>>> include 'scripts/migrate_stations_to_objets_spatiaux.php';

# 4. Exécuter migration
php artisan migrate
```

### Phase 3 : Corriger Mine (objet_spatial_id)

```bash
# 5. Créer migration pour ajouter objet_spatial_id à mines
# FICHIER À CRÉER : database/migrations/2025_12_31_140001_add_objet_spatial_id_to_mines.php

php artisan make:migration add_objet_spatial_id_to_mines

# 6. Créer ObjetSpatial pour chaque Mine existante
php artisan tinker
>>> include 'scripts/migrate_mines_to_objets_spatiaux.php';

# 7. Exécuter migration
php artisan migrate
```

### Phase 4 : Modifier modèles Eloquent

```bash
# 8. Modifier app/Models/Station.php
# Ajouter : objet_spatial_id, relation objetSpatial()

# 9. Modifier app/Models/Mine.php
# Ajouter : objet_spatial_id, relation objetSpatial()

# 10. Modifier app/Models/ObjetSpatial.php
# Ajouter : relations station(), mine()
```

### Phase 5 : Ajouter routes API

```bash
# 11. Ajouter routes dans routes/web.php
# Routes pour ZoneSpatiale Controller

# 12. Tester endpoints
php artisan route:list | grep zone
```

### Phase 6 : Tests et validation

```bash
# 13. Créer tests unitaires
php artisan make:test ZoneSpatiale Test
php artisan make:test StationObjetSpatialTest
php artisan make:test MineObjetSpatialTest

# 14. Exécuter tests
php artisan test
```

---

## 🏗️ ARCHITECTURE CORRIGÉE

### Hiérarchie Objets Spatiaux

```
ObjetSpatial (table unique pour positions)
├── type: 'vaisseau' → Vaisseau (table détails)
├── type: 'station' → Station (table détails)  ← CORRECTION
├── type: 'mine' → Mine (table détails)        ← CORRECTION
├── type: 'base' → Base (table détails)
├── type: 'asteroide' → (pas de table détails)
├── type: 'debris' → (pas de table détails)
└── type: 'epave' → (pas de table détails)
```

### Relations "parent" (contenu dans)

```
Station en orbite planète :
  ObjetSpatial {
    type: 'station',
    parent_type: 'Planete',
    parent_id: 3,
    secteur_x/y/z: hérité de système
    position_x/y/z: calculé depuis orbite planète
  }

Mine MAME sur astéroïde :
  ObjetSpatial {
    type: 'mine',
    parent_type: 'ObjetSpatial', // Astéroïde
    parent_id: 150,
    secteur_x/y/z: même que astéroïde
    position_x/y/z: même que astéroïde (posée dessus)
  }

Mine attachée à station :
  ObjetSpatial {
    type: 'mine',
    parent_type: 'Station',
    parent_id: 10,
    secteur_x/y/z: même que station
    position_x/y/z: même que station
  }

Vaisseau amarré à station :
  ObjetSpatial {
    type: 'vaisseau',
    parent_type: 'Station',
    parent_id: 10,
    secteur_x/y/z: même que station
    position_x/y/z: même que station
  }

Astéroïde dans zone :
  ObjetSpatial {
    type: 'asteroide',
    parent_type: 'ZoneSpatiale',
    parent_id: 1,
    secteur_x/y/z: système de la zone
    position_x/y/z: position orbitale propre
  }
```

### Positions locale ET sectoriale

**RÈGLE : Tout objet a les 6 coordonnées**

```php
ObjetSpatial {
    // Position sectoriale (AL)
    secteur_x: int,
    secteur_y: int,
    secteur_z: int,

    // Position locale (cUA dans secteur)
    position_x: int,
    position_y: int,
    position_z: int,
}

// Position absolue = (secteur × 6324100) + position
// Ex: Secteur (0,0,0) + Position (250,0,0)
//     = 250 cUA = 2.5 UA depuis étoile
```

---

## 📝 FICHIERS À CRÉER

### 1. Migration Station → ObjetSpatial

**Fichier :** `database/migrations/2025_12_31_140000_add_objet_spatial_id_to_stations.php`

```php
<?php
// Migration pour lier Station à ObjetSpatial
public function up(): void
{
    Schema::table('stations', function (Blueprint $table) {
        $table->foreignId('objet_spatial_id')
              ->nullable()
              ->after('id')
              ->constrained('objets_spatiaux')
              ->onDelete('cascade');

        $table->index('objet_spatial_id');
    });
}
```

### 2. Migration Mine → ObjetSpatial

**Fichier :** `database/migrations/2025_12_31_140001_add_objet_spatial_id_to_mines.php`

```php
<?php
// Migration pour lier Mine à ObjetSpatial
public function up(): void
{
    Schema::table('mines', function (Blueprint $table) {
        $table->foreignId('objet_spatial_id')
              ->nullable()
              ->after('id')
              ->constrained('objets_spatiaux')
              ->onDelete('cascade');

        $table->index('objet_spatial_id');
    });
}
```

### 3. Script migration données Station

**Fichier :** `scripts/migrate_stations_to_objets_spatiaux.php`

```php
<?php
use App\Models\Station;
use App\Models\ObjetSpatial;
use App\Models\Planete;

foreach (Station::all() as $station) {
    // Calculer position depuis orbite planète
    $position = [
        'secteur_x' => 0,
        'secteur_y' => 0,
        'secteur_z' => 0,
        'position_x' => 0,
        'position_y' => 0,
        'position_z' => 0,
    ];

    if ($station->planete_id) {
        $planete = Planete::find($station->planete_id);
        if ($planete) {
            $posPlanete = $planete->getPositionOrbitale();
            $rayonCua = $station->orbite_rayon_ua * 100;
            $angle = $station->orbite_angle;

            $position = [
                'secteur_x' => $planete->systemeStellaire->secteur_x ?? 0,
                'secteur_y' => $planete->systemeStellaire->secteur_y ?? 0,
                'secteur_z' => $planete->systemeStellaire->secteur_z ?? 0,
                'position_x' => $posPlanete['x'] + ($rayonCua * cos($angle)),
                'position_y' => $posPlanete['y'] + ($rayonCua * sin($angle)),
                'position_z' => $posPlanete['z'],
            ];
        }
    } elseif ($station->systeme_stellaire_id) {
        $systeme = SystemeStellaire::find($station->systeme_stellaire_id);
        $position = [
            'secteur_x' => $systeme->secteur_x ?? 0,
            'secteur_y' => $systeme->secteur_y ?? 0,
            'secteur_z' => $systeme->secteur_z ?? 0,
            'position_x' => 0,
            'position_y' => 0,
            'position_z' => 0,
        ];
    }

    // Créer ObjetSpatial
    $objet = ObjetSpatial::create([
        'nom' => $station->nom,
        'type' => 'station',
        'secteur_x' => $position['secteur_x'],
        'secteur_y' => $position['secteur_y'],
        'secteur_z' => $position['secteur_z'],
        'position_x' => $position['position_x'],
        'position_y' => $position['position_y'],
        'position_z' => $position['position_z'],
        'parent_type' => $station->planete_id ? Planete::class : null,
        'parent_id' => $station->planete_id,
        'detectabilite_base' => $station->detectabilite_base ?? 40,
        'poi_connu' => $station->poi_connu ?? true,
    ]);

    // Lier station à objet
    $station->objet_spatial_id = $objet->id;
    $station->save();

    echo "Station #{$station->id} ({$station->nom}) → ObjetSpatial #{$objet->id}\n";
}
```

### 4. Script migration données Mine

**Fichier :** `scripts/migrate_mines_to_objets_spatiaux.php`

```php
<?php
use App\Models\Mine;
use App\Models\ObjetSpatial;
use App\Models\Planete;

foreach (Mine::all() as $mine) {
    // Calculer position
    $position = [
        'secteur_x' => 0,
        'secteur_y' => 0,
        'secteur_z' => 0,
        'position_x' => 0,
        'position_y' => 0,
        'position_z' => 0,
    ];

    if ($mine->planete_id) {
        $planete = Planete::find($mine->planete_id);
        if ($planete) {
            $posPlanete = $planete->getPositionOrbitale();
            $position = [
                'secteur_x' => $planete->systemeStellaire->secteur_x ?? 0,
                'secteur_y' => $planete->systemeStellaire->secteur_y ?? 0,
                'secteur_z' => $planete->systemeStellaire->secteur_z ?? 0,
                'position_x' => $posPlanete['x'],
                'position_y' => $posPlanete['y'],
                'position_z' => $posPlanete['z'],
            ];
        }
    }

    // Créer ObjetSpatial
    $objet = ObjetSpatial::create([
        'nom' => $mine->nom,
        'type' => 'mine',
        'secteur_x' => $position['secteur_x'],
        'secteur_y' => $position['secteur_y'],
        'secteur_z' => $position['secteur_z'],
        'position_x' => $position['position_x'],
        'position_y' => $position['position_y'],
        'position_z' => $position['position_z'],
        'parent_type' => $mine->planete_id ? Planete::class : ($mine->base_id ? Station::class : null),
        'parent_id' => $mine->planete_id ?? $mine->base_id,
        'detectabilite_base' => $mine->detectabilite_base ?? 50,
        'poi_connu' => $mine->poi_connu ?? false,
    ]);

    // Lier mine à objet
    $mine->objet_spatial_id = $objet->id;
    $mine->save();

    echo "Mine #{$mine->id} ({$mine->nom}) → ObjetSpatial #{$objet->id}\n";
}
```

### 5. Routes API Zones

**Fichier :** `routes/web.php` (ajouter)

```php
// Routes Zones Spatiales
Route::prefix('api/zones')->middleware('auth')->group(function() {
    Route::get('/systeme/{systemeId}', [ZoneSpatiale Controller::class, 'index']);
    Route::post('/', [ZoneSpatiale Controller::class, 'store']);
    Route::get('/{zoneId}', [ZoneSpatiale Controller::class, 'show']);
    Route::put('/{zoneId}', [ZoneSpatiale Controller::class, 'update']);
    Route::delete('/{zoneId}', [ZoneSpatiale Controller::class, 'destroy']);

    Route::post('/{zoneId}/notables', [ZoneSpatiale Controller::class, 'ajouterNotable']);
    Route::post('/{zoneId}/verifier-presence', [ZoneSpatiale Controller::class, 'verifierPresenceVaisseau']);
    Route::post('/{zoneId}/generer-proceduraux', [ZoneSpatiale Controller::class, 'genererAsteroidesProceduraux']);
    Route::post('/{zoneId}/marquer-decouvert', [ZoneSpatiale Controller::class, 'marquerDecouvert']);
});
```

---

## 🔧 ORDRE D'EXÉCUTION RECOMMANDÉ

### Étape 1 : Zones spatiales (déjà créé)

```bash
cd E:\devlog\php\laravel\conquete-spatiale

# 1. Vérifier fichiers créés
ls database/migrations/2025_12_31_12*
ls app/Models/ZoneSpatiale.php
ls app/Http/Controllers/ZoneSpatiale\ Controller.php

# 2. Exécuter migrations zones
php artisan migrate

# 3. Vérifier tables
php artisan db:show
```

### Étape 2 : Créer migrations Station/Mine

```bash
# 4. Créer fichiers migration (manuellement ou via make:migration)
# Copier contenu depuis ce TODO

# 5. Créer scripts migration données
mkdir -p scripts
# Copier scripts depuis ce TODO
```

### Étape 3 : Migrer données existantes

```bash
# 6. Exécuter migration Station
php artisan migrate --path=database/migrations/2025_12_31_140000_add_objet_spatial_id_to_stations.php

# 7. Migrer données Station
php artisan tinker
>>> include 'scripts/migrate_stations_to_objets_spatiaux.php';
>>> exit

# 8. Exécuter migration Mine
php artisan migrate --path=database/migrations/2025_12_31_140001_add_objet_spatial_id_to_mines.php

# 9. Migrer données Mine
php artisan tinker
>>> include 'scripts/migrate_mines_to_objets_spatiaux.php';
>>> exit
```

### Étape 4 : Modifier modèles

```bash
# 10. Modifier app/Models/Station.php
# 11. Modifier app/Models/Mine.php
# 12. Modifier app/Models/ObjetSpatial.php
```

### Étape 5 : Ajouter routes et tester

```bash
# 13. Ajouter routes dans routes/web.php

# 14. Tester routes
php artisan route:list | grep zone

# 15. Tests manuels
# Créer zone test, vérifier détection, etc.
```

---

## 📚 DOCUMENTATION ASSOCIÉE

### Documents à consulter :
- `docs/game-design/GDD_Asteroides.md` - Architecture zones complète
- `docs/game-design/GDD_SYSTEME_DETECTION_V2.md` - Système détection
- `docs/game-design/SYSTEME_COORDONNEES.md` - Conversions AL/UA/cUA

### Documents à créer/mettre à jour :
- [ ] `docs/ARCHITECTURE_OBJETS_SPATIAUX.md` - Hiérarchie complète objets
- [ ] `docs/MIGRATION_STATION_MINE.md` - Procédure migration Station/Mine
- [ ] Mettre à jour `GDD_Bases_Spatiales.md` avec nouvelle architecture
- [ ] Mettre à jour `GDD_SYSTEME_MINES_MAME.md` avec parent polymorphique

---

## ⚠️ POINTS D'ATTENTION

1. **Stations existantes** : Vérifier que toutes ont soit planete_id soit systeme_stellaire_id
2. **Mines existantes** : Vérifier cohérence planete_id vs base_id
3. **Performance** : Index sur objet_spatial_id dans stations et mines
4. **Rétrocompatibilité** : Garder planete_id dans Station pour requêtes legacy
5. **Tests** : Tester amarrage vaisseau après migration Station

---

## ✅ CHECKLIST FINALE

- [ ] Migrations zones exécutées
- [ ] Migrations Station/Mine créées
- [ ] Scripts migration données créés
- [ ] Données Station migrées
- [ ] Données Mine migrées
- [ ] Modèles Station/Mine modifiés
- [ ] Modèle ObjetSpatial complété
- [ ] Routes API ajoutées
- [ ] Tests créés et validés
- [ ] Documentation mise à jour
- [ ] Seeder zones exemple créé

---

**Auteur :** Claude Code
**Date création :** 2025-12-31
**Dernière mise à jour :** 2025-12-31
