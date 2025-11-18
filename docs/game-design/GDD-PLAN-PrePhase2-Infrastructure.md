# 🛠️ PLAN PRÉ-PHASE 2 : INFRASTRUCTURE AVANCÉE
## Conquête Spatiale

**Version :** 1.0
**Date création :** 2025-11-18
**Objectif :** Infrastructures nécessaires avant Phase 2 Économie

---

## 🎯 OBJECTIFS PRINCIPAUX

Avant de démarrer la Phase 2 (Économie), implémenter :

1. **Système de gestion BDD** avec reset/seed paramétrable
2. **Intégration GAIA** pour étoiles réelles (données ESA)
3. **Backend administratif** avec carte stellaire interactive
4. **Système sauvegarde/restauration** avec versioning

---

## 📋 PHASE PRÉ-2.1 : SYSTÈME DE GESTION BDD

### Objectif

Permettre de réinitialiser et peupler la base de données de manière flexible avec différentes sources de données.

### Fonctionnalités

#### 1. Commande Artisan `db:reset-game`

**Commande :**
```bash
php artisan db:reset-game [--mode=] [--with-gaia] [--systems=N]
```

**Options :**
- `--mode=basic` : Reset complet avec données de test basiques (default)
- `--mode=gaia` : Reset avec données GAIA (étoiles réelles)
- `--mode=hybrid` : Données GAIA + systèmes procéduraux
- `--with-gaia` : Ajouter données GAIA en plus des données actuelles
- `--systems=N` : Nombre de systèmes procéduraux à générer (default: 20)
- `--force` : Forcer sans confirmation

**Fonctionnement :**
1. Demander confirmation (sauf si `--force`)
2. `php artisan migrate:fresh`
3. Seed selon mode choisi :
   - **basic** : `DatabaseSeeder` actuel
   - **gaia** : `GaiaSeeder` (nouvelles étoiles réelles)
   - **hybrid** : `GaiaSeeder` + `UniverseSeeder`

#### 2. Seeders Réorganisés

**Structure :**
```
database/seeders/
├── DatabaseSeeder.php           (orchestrateur principal)
├── CompteSeeder.php             (comptes de test)
├── PersonnageSeeder.php         (personnages de test)
├── UniverseSeeder.php           (génération procédurale)
├── GaiaSeeder.php               (données GAIA) [NEW]
└── DevSeeder.php                (données développement)
```

**DatabaseSeeder.php :**
```php
public function run()
{
    $mode = $this->command->option('mode') ?? 'basic';

    // Always seed accounts and characters
    $this->call([
        CompteSeeder::class,
        PersonnageSeeder::class,
    ]);

    // Seed universe according to mode
    match($mode) {
        'basic' => $this->call(UniverseSeeder::class),
        'gaia' => $this->call(GaiaSeeder::class),
        'hybrid' => $this->call([
            GaiaSeeder::class,
            UniverseSeeder::class,
        ]),
        default => $this->call(UniverseSeeder::class),
    };
}
```

#### 3. Table de Configuration Univers

**Migration :** `create_universe_configs_table`

```php
Schema::create('universe_configs', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique(); // 'generation_mode', 'gaia_enabled', etc.
    $table->text('value');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

**Clés de configuration :**
- `generation_mode` : 'procedural' | 'gaia' | 'hybrid'
- `gaia_enabled` : true/false
- `gaia_radius_ly` : Rayon d'étoiles GAIA chargées (défaut: 100 AL)
- `procedural_density` : Densité génération procédurale (défaut: 0.05)
- `known_space_radius` : Rayon espace connu (défaut: 50 AL)

---

## 📋 PHASE PRÉ-2.2 : INTÉGRATION GAIA

### Contexte GAIA

**GAIA** (ESA) = Catalogue astronomique réel avec :
- Position 3D des étoiles
- Type spectral (O, B, A, F, G, K, M)
- Magnitude, luminosité
- Distance en années-lumière
- Noms/identifiants

**Sources de données :**
- Fichier CSV exporté de GAIA DR3
- API GAIA (optionnel, pour mise à jour)
- Sous-ensemble filtré (étoiles < 100 AL)

### Fonctionnalités

#### 1. Importateur GAIA

**Commande :**
```bash
php artisan gaia:import [fichier.csv] [--radius=100] [--force]
```

**Fonctionnement :**
1. Lire fichier CSV GAIA
2. Filtrer étoiles dans rayon spécifié
3. Convertir coordonnées galactiques → coordonnées jeu
4. Créer `SystemeStellaire` pour chaque étoile
5. Marquer comme `source_gaia = true`

#### 2. Structure de Données

**Ajout à `systemes_stellaires` :**

```php
$table->boolean('source_gaia')->default(false);
$table->string('gaia_source_id')->nullable()->unique();
$table->decimal('gaia_ra', 12, 8)->nullable();  // Right Ascension
$table->decimal('gaia_dec', 12, 8)->nullable(); // Declination
$table->decimal('gaia_distance_ly', 10, 2)->nullable();
$table->decimal('gaia_magnitude', 8, 4)->nullable();
```

#### 3. Conversion Coordonnées

**De coordonnées galactiques (RA, DEC, Distance) vers (secteur_x, secteur_y, secteur_z) :**

```php
class GaiaCoordinateConverter
{
    public static function galacticToGame(
        float $ra,        // Right Ascension (degrees)
        float $dec,       // Declination (degrees)
        float $distanceLy // Distance (light-years)
    ): array {
        // Convert to radians
        $raRad = deg2rad($ra);
        $decRad = deg2rad($dec);

        // Spherical to Cartesian
        $x = $distanceLy * cos($decRad) * cos($raRad);
        $y = $distanceLy * cos($decRad) * sin($raRad);
        $z = $distanceLy * sin($decRad);

        // Center on Sol (0, 0, 0)
        return [
            'secteur_x' => (int)floor($x),
            'secteur_y' => (int)floor($y),
            'secteur_z' => (int)floor($z),
            'position_x' => $x - floor($x),
            'position_y' => $y - floor($y),
            'position_z' => $z - floor($z),
        ];
    }
}
```

#### 4. Nom des Étoiles

**Sources :**
- Nom GAIA officiel si disponible
- Nom catalogue (HD, HIP, etc.)
- Sinon : `GAIA-{source_id_court}`

**Étoiles célèbres à nommer :**
- Sol (notre Soleil) : (0, 0, 0)
- Alpha Centauri : ~4.37 AL
- Sirius : ~8.6 AL
- Proxima Centauri : ~4.24 AL
- Etc.

#### 5. GaiaSeeder

**database/seeders/GaiaSeeder.php :**

```php
class GaiaSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('data/gaia_nearby_stars.csv');

        if (!file_exists($csvPath)) {
            $this->command->warn('Fichier GAIA non trouvé. Génération procédurale...');
            return;
        }

        $radius = config('universe.gaia_radius_ly', 100);

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            // Filter by distance
            if ($data['distance'] > $radius) continue;

            // Convert coordinates
            $coords = GaiaCoordinateConverter::galacticToGame(
                (float)$data['ra'],
                (float)$data['dec'],
                (float)$data['distance']
            );

            // Create system
            SystemeStellaire::create([
                'nom' => $data['name'] ?: "GAIA-" . substr($data['source_id'], 0, 8),
                'secteur_x' => $coords['secteur_x'],
                'secteur_y' => $coords['secteur_y'],
                'secteur_z' => $coords['secteur_z'],
                'position_x' => $coords['position_x'],
                'position_y' => $coords['position_y'],
                'position_z' => $coords['position_z'],
                'type_etoile' => $this->mapSpectralType($data['spectral_type']),
                'couleur' => $this->getColorFromType($data['spectral_type']),
                'source_gaia' => true,
                'gaia_source_id' => $data['source_id'],
                'gaia_ra' => $data['ra'],
                'gaia_dec' => $data['dec'],
                'gaia_distance_ly' => $data['distance'],
                'gaia_magnitude' => $data['magnitude'] ?? null,
                // Generate planets
                'nb_planetes' => rand(0, 12),
            ]);

            $count++;
        }

        fclose($file);

        $this->command->info("✅ {$count} systèmes GAIA importés");
    }
}
```

#### 6. Fichier de Données GAIA

**Localisation :**
```
database/data/
└── gaia_nearby_stars.csv
```

**Format CSV :**
```csv
source_id,ra,dec,distance,spectral_type,magnitude,name
12345678,123.456,45.678,4.37,G2V,0.01,Alpha Centauri A
...
```

**Comment obtenir :**
1. Télécharger depuis GAIA Archive (https://gea.esac.esa.int/archive/)
2. Filtrer étoiles < 100 AL
3. Exporter colonnes nécessaires
4. OU utiliser sous-ensemble pré-filtré fourni

---

## 📋 PHASE PRÉ-2.3 : BACKEND ADMINISTRATIF

### Objectif

Interface d'administration pour visualiser et gérer l'univers.

### Fonctionnalités

#### 1. Routes Backend

**routes/backend.php :**
```php
Route::prefix('backend')
    ->middleware(['auth', 'admin'])
    ->name('backend.')
    ->group(function () {
        Route::get('/dashboard', [BackendController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/carte', [BackendController::class, 'carte'])
            ->name('carte');

        Route::get('/api/systemes', [BackendController::class, 'apiSystemes'])
            ->name('api.systemes');

        Route::get('/api/joueurs', [BackendController::class, 'apiJoueurs'])
            ->name('api.joueurs');

        Route::post('/api/teleport', [BackendController::class, 'apiTeleport'])
            ->name('api.teleport');

        Route::get('/sauvegarde', [BackendController::class, 'sauvegarde'])
            ->name('sauvegarde');

        Route::post('/sauvegarde/export', [BackendController::class, 'exportSauvegarde'])
            ->name('sauvegarde.export');

        Route::post('/sauvegarde/import', [BackendController::class, 'importSauvegarde'])
            ->name('sauvegarde.import');
    });
```

#### 2. Middleware Admin

**app/Http/Middleware/IsAdmin.php :**
```php
class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $compte = $request->user();

        if (!$compte || !$compte->is_admin) {
            abort(403, 'Accès refusé. Vous devez être administrateur.');
        }

        return $next($request);
    }
}
```

**Ajout à `comptes` :**
```php
$table->boolean('is_admin')->default(false);
```

#### 3. Carte Stellaire Interactive

**Bibliothèque :** Three.js pour rendu 3D WebGL

**Vue : `resources/views/backend/carte.blade.php` :**
- Canvas Three.js
- Affichage systèmes stellaires (points 3D)
- Couleur selon type spectral
- Affichage position joueurs (icônes vaisseaux)
- Zoom, rotation, pan
- Clic sur système → détails
- Filtres (type étoile, source GAIA, découvert/non découvert)

**API Endpoint :**
```php
public function apiSystemes(Request $request)
{
    $radius = $request->input('radius', 100);
    $centerX = $request->input('center_x', 0);
    $centerY = $request->input('center_y', 0);
    $centerZ = $request->input('center_z', 0);

    $systemes = SystemeStellaire::selectRaw("
        *,
        SQRT(
            POW(secteur_x + position_x - ?, 2) +
            POW(secteur_y + position_y - ?, 2) +
            POW(secteur_z + position_z - ?, 2)
        ) as distance
    ", [$centerX, $centerY, $centerZ])
    ->having('distance', '<=', $radius)
    ->orderBy('distance')
    ->get();

    return response()->json([
        'systemes' => $systemes->map(function ($sys) {
            return [
                'id' => $sys->id,
                'nom' => $sys->nom,
                'x' => $sys->secteur_x + $sys->position_x,
                'y' => $sys->secteur_y + $sys->position_y,
                'z' => $sys->secteur_z + $sys->position_z,
                'type' => $sys->type_etoile,
                'couleur' => $sys->couleur,
                'source_gaia' => $sys->source_gaia,
                'nb_planetes' => $sys->nb_planetes,
            ];
        }),
    ]);
}

public function apiJoueurs(Request $request)
{
    $personnages = Personnage::with('objetSpatial')->get();

    return response()->json([
        'joueurs' => $personnages->map(function ($perso) {
            $os = $perso->objetSpatial;
            return [
                'id' => $perso->id,
                'nom' => $perso->nom_complet,
                'x' => $os->secteur_x + $os->position_x,
                'y' => $os->secteur_y + $os->position_y,
                'z' => $os->secteur_z + $os->position_z,
                'vaisseau' => $perso->vaisseauActif?->modele,
            ];
        }),
    ]);
}
```

#### 4. Carte Three.js

**JavaScript : `resources/js/backend/carte3d.js` :**

```javascript
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

class Carte3D {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 10000);
        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.controls = new OrbitControls(this.camera, this.renderer.domElement);

        this.init();
    }

    init() {
        // Setup renderer
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.setClearColor(0x000000);
        this.container.appendChild(this.renderer.domElement);

        // Camera position
        this.camera.position.set(0, 50, 100);
        this.camera.lookAt(0, 0, 0);

        // Controls
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;

        // Grid helper
        const gridHelper = new THREE.GridHelper(200, 20);
        this.scene.add(gridHelper);

        // Axes helper
        const axesHelper = new THREE.AxesHelper(50);
        this.scene.add(axesHelper);

        // Ambient light
        const ambientLight = new THREE.AmbientLight(0x404040);
        this.scene.add(ambientLight);

        this.animate();
    }

    addSysteme(systeme) {
        // Couleur selon type spectral
        const couleurs = {
            'O': 0x9BB0FF, // Bleu
            'B': 0xAABFFF, // Bleu-blanc
            'A': 0xCAD7FF, // Blanc
            'F': 0xF8F7FF, // Jaune-blanc
            'G': 0xFFF4EA, // Jaune (Sol)
            'K': 0xFFD2A1, // Orange
            'M': 0xFFCC6F, // Rouge
        };

        const typeSpectral = systeme.type.charAt(0);
        const couleur = couleurs[typeSpectral] || 0xFFFFFF;

        // Taille selon source
        const taille = systeme.source_gaia ? 0.8 : 0.5;

        const geometry = new THREE.SphereGeometry(taille, 16, 16);
        const material = new THREE.MeshBasicMaterial({ color: couleur });
        const sphere = new THREE.Mesh(geometry, material);

        sphere.position.set(systeme.x, systeme.z, systeme.y); // Y et Z inversés pour Three.js
        sphere.userData = systeme;

        this.scene.add(sphere);
    }

    addJoueur(joueur) {
        const geometry = new THREE.ConeGeometry(1, 2, 8);
        const material = new THREE.MeshBasicMaterial({ color: 0x00FF00 });
        const cone = new THREE.Mesh(geometry, material);

        cone.position.set(joueur.x, joueur.z + 2, joueur.y);
        cone.userData = joueur;

        this.scene.add(cone);
    }

    loadData() {
        fetch('/backend/api/systemes?radius=100')
            .then(res => res.json())
            .then(data => {
                data.systemes.forEach(sys => this.addSysteme(sys));
            });

        fetch('/backend/api/joueurs')
            .then(res => res.json())
            .then(data => {
                data.joueurs.forEach(joueur => this.addJoueur(joueur));
            });
    }

    animate() {
        requestAnimationFrame(() => this.animate());
        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }
}

// Init
const carte = new Carte3D('carte-container');
carte.loadData();
```

---

## 📋 PHASE PRÉ-2.4 : SAUVEGARDE/RESTAURATION

### Objectif

Permettre sauvegarde complète des données avec versioning et restauration.

### Fonctionnalités

#### 1. Commandes Artisan

**Export :**
```bash
php artisan backup:export [--tables=all] [--format=json] [--output=path]
```

**Import :**
```bash
php artisan backup:import [fichier] [--force] [--merge]
```

**Options :**
- `--tables=all|comptes,personnages,...` : Tables à sauvegarder
- `--format=json|sql` : Format de sortie
- `--output=path` : Chemin de sortie (défaut: storage/backups/)
- `--force` : Forcer import sans confirmation
- `--merge` : Fusionner avec données existantes (sinon reset)

#### 2. Format de Sauvegarde

**Structure JSON :**
```json
{
  "meta": {
    "version": "1.0",
    "created_at": "2025-11-18T12:00:00Z",
    "game_version": "0.1.0",
    "database_schema_version": "2025_11_18_101857"
  },
  "schema": {
    "comptes": {
      "columns": ["id", "email", "password", "is_admin", "created_at"],
      "types": ["bigint", "varchar", "varchar", "boolean", "timestamp"]
    },
    "personnages": {
      "columns": [...],
      "types": [...]
    },
    ...
  },
  "data": {
    "comptes": [
      {"id": 1, "email": "test@test.com", ...},
      ...
    ],
    "personnages": [
      ...
    ],
    ...
  }
}
```

**Avantages :**
- Contient structure ET données
- Versioning pour compatibilité
- Portable entre environnements
- Lisible/modifiable manuellement

#### 3. Gestionnaire de Sauvegarde

**app/Services/BackupService.php :**

```php
class BackupService
{
    protected array $tables = [
        'comptes',
        'personnages',
        'objets_spatiaux',
        'vaisseaux',
        'bases',
        'systemes_stellaires',
        'planetes',
        'decouvertes',
        'universe_configs',
    ];

    public function export(array $options = []): string
    {
        $tables = $options['tables'] ?? $this->tables;
        $format = $options['format'] ?? 'json';

        $backup = [
            'meta' => $this->getMeta(),
            'schema' => $this->getSchema($tables),
            'data' => $this->getData($tables),
        ];

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.' . $format;
        $path = storage_path('backups/' . $filename);

        match($format) {
            'json' => file_put_contents($path, json_encode($backup, JSON_PRETTY_PRINT)),
            'sql' => file_put_contents($path, $this->generateSql($backup)),
            default => throw new \InvalidArgumentException("Format invalide: {$format}"),
        };

        return $path;
    }

    public function import(string $path, array $options = []): void
    {
        $merge = $options['merge'] ?? false;

        if (!file_exists($path)) {
            throw new \RuntimeException("Fichier non trouvé: {$path}");
        }

        $backup = json_decode(file_get_contents($path), true);

        // Vérifier version
        $this->validateVersion($backup['meta']);

        // Reset si pas merge
        if (!$merge) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($backup['data'] as $table => $rows) {
                DB::table($table)->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Importer données
        DB::transaction(function () use ($backup) {
            foreach ($backup['data'] as $table => $rows) {
                foreach ($rows as $row) {
                    DB::table($table)->insert($row);
                }
            }
        });
    }

    protected function getMeta(): array
    {
        return [
            'version' => '1.0',
            'created_at' => now()->toIso8601String(),
            'game_version' => config('app.version', '0.1.0'),
            'database_schema_version' => $this->getLatestMigration(),
        ];
    }

    protected function getSchema(array $tables): array
    {
        $schema = [];

        foreach ($tables as $table) {
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            $types = [];

            foreach ($columns as $column) {
                $type = DB::getSchemaBuilder()->getColumnType($table, $column);
                $types[] = $type;
            }

            $schema[$table] = [
                'columns' => $columns,
                'types' => $types,
            ];
        }

        return $schema;
    }

    protected function getData(array $tables): array
    {
        $data = [];

        foreach ($tables as $table) {
            $data[$table] = DB::table($table)->get()->toArray();
        }

        return $data;
    }
}
```

#### 4. Interface Backend

**Vue : `resources/views/backend/sauvegarde.blade.php` :**

**Fonctionnalités :**
- Liste des sauvegardes existantes
- Bouton "Créer sauvegarde"
- Upload fichier de sauvegarde
- Bouton "Restaurer" pour chaque sauvegarde
- Affichage métadonnées (date, version, tables)
- Téléchargement sauvegarde

**Formulaire Export :**
```html
<form action="{{ route('backend.sauvegarde.export') }}" method="POST">
    @csrf
    <label>
        <input type="checkbox" name="tables[]" value="all" checked>
        Toutes les tables
    </label>

    <label>
        Format:
        <select name="format">
            <option value="json">JSON</option>
            <option value="sql">SQL</option>
        </select>
    </label>

    <button type="submit">Créer Sauvegarde</button>
</form>
```

**Formulaire Import :**
```html
<form action="{{ route('backend.sauvegarde.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="backup_file" accept=".json,.sql" required>

    <label>
        <input type="checkbox" name="merge">
        Fusionner avec données existantes (sinon remplace tout)
    </label>

    <button type="submit">Restaurer</button>
</form>
```

---

## 📊 RÉCAPITULATIF

### Ce qui sera livré

1. ✅ **Commande `db:reset-game`** avec modes (basic, gaia, hybrid)
2. ✅ **Seeders réorganisés** (GaiaSeeder, UniverseSeeder, etc.)
3. ✅ **Intégration GAIA** (import étoiles réelles, conversion coordonnées)
4. ✅ **Backend administratif** avec carte 3D interactive (Three.js)
5. ✅ **Système sauvegarde/restauration** avec versioning JSON/SQL

### Structure Fichiers

```
app/
├── Console/Commands/
│   ├── DbResetGame.php
│   ├── GaiaImport.php
│   ├── BackupExport.php
│   └── BackupImport.php
├── Http/
│   ├── Controllers/
│   │   └── BackendController.php
│   └── Middleware/
│       └── IsAdmin.php
├── Services/
│   ├── BackupService.php
│   └── GaiaCoordinateConverter.php
database/
├── data/
│   └── gaia_nearby_stars.csv
├── migrations/
│   ├── xxxx_add_gaia_fields_to_systemes_stellaires.php
│   ├── xxxx_add_is_admin_to_comptes.php
│   └── xxxx_create_universe_configs_table.php
└── seeders/
    ├── DatabaseSeeder.php (refactor)
    ├── GaiaSeeder.php (new)
    └── UniverseSeeder.php (refactor)
resources/
├── views/backend/
│   ├── dashboard.blade.php
│   ├── carte.blade.php
│   └── sauvegarde.blade.php
└── js/backend/
    └── carte3d.js
routes/
└── backend.php (new)
storage/
└── backups/ (directory)
```

### Ordre d'Implémentation

1. **Jour 1 (4h)** : Système BDD + Seeders
2. **Jour 2 (6h)** : Intégration GAIA
3. **Jour 3 (8h)** : Backend + Carte 3D
4. **Jour 4 (4h)** : Sauvegarde/Restauration

**Total estimé : 22h (~3 jours)**

---

## 🎯 APRÈS PRÉ-PHASE 2

Une fois ces infrastructures en place :

1. **Synchroniser branche avec dev**
2. **Démarrer Phase 2 : Économie de Base**

---

**Document vivant - Dernière mise à jour : 2025-11-18**
