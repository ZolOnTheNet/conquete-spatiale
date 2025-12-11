# Ã°Å¸âºÂ Ã¯Â¸ï¿½ PLAN PRÃâ°-PHASE 2 : INFRASTRUCTURE AVANCÃâ°E
## ConquÃÂªte Spatiale

**Version :** 1.0
**Date crÃÂ©ation :** 2025-11-18
**Objectif :** Infrastructures nÃÂ©cessaires avant Phase 2 Ãâ°conomie

---

## Ã°Å¸Å½Â¯ OBJECTIFS PRINCIPAUX

Avant de dÃÂ©marrer la Phase 2 (Ãâ°conomie), implÃÂ©menter :

1. **SystÃÂ¨me de gestion BDD** avec reset/seed paramÃÂ©trable
2. **IntÃÂ©gration GAIA** pour ÃÂ©toiles rÃÂ©elles (donnÃÂ©es ESA)
3. **Backend administratif** avec carte stellaire interactive
4. **SystÃÂ¨me sauvegarde/restauration** avec versioning

---

## Ã°Å¸ââ¹ PHASE PRÃâ°-2.1 : SYSTÃËME DE GESTION BDD

### Objectif

Permettre de rÃÂ©initialiser et peupler la base de donnÃÂ©es de maniÃÂ¨re flexible avec diffÃÂ©rentes sources de donnÃÂ©es.

### FonctionnalitÃÂ©s

#### 1. Commande Artisan `db:reset-game`

**Commande :**
```bash
php artisan db:reset-game [--mode=] [--with-gaia] [--systems=N]
```

**Options :**
- `--mode=basic` : Reset complet avec donnÃÂ©es de test basiques (default)
- `--mode=gaia` : Reset avec donnÃÂ©es GAIA (ÃÂ©toiles rÃÂ©elles)
- `--mode=hybrid` : DonnÃÂ©es GAIA + systÃÂ¨mes procÃÂ©duraux
- `--with-gaia` : Ajouter donnÃÂ©es GAIA en plus des donnÃÂ©es actuelles
- `--systems=N` : Nombre de systÃÂ¨mes procÃÂ©duraux ÃÂ  gÃÂ©nÃÂ©rer (default: 20)
- `--force` : Forcer sans confirmation

**Fonctionnement :**
1. Demander confirmation (sauf si `--force`)
2. `php artisan migrate:fresh`
3. Seed selon mode choisi :
   - **basic** : `DatabaseSeeder` actuel
   - **gaia** : `GaiaSeeder` (nouvelles ÃÂ©toiles rÃÂ©elles)
   - **hybrid** : `GaiaSeeder` + `UniverseSeeder`

#### 2. Seeders RÃÂ©organisÃÂ©s

**Structure :**
```
database/seeders/
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ DatabaseSeeder.php           (orchestrateur principal)
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ CompteSeeder.php             (comptes de test)
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ PersonnageSeeder.php         (personnages de test)
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ UniverseSeeder.php           (gÃÂ©nÃÂ©ration procÃÂ©durale)
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GaiaSeeder.php               (donnÃÂ©es GAIA) [NEW]
Ã¢ââÃ¢ââ¬Ã¢ââ¬ DevSeeder.php                (donnÃÂ©es dÃÂ©veloppement)
```

**DatabaseSeeder.php :**
```php
public function run()
{
    $mode = $this->command->option('mode') œ 'basic';

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

**ClÃÂ©s de configuration :**
- `generation_mode` : 'procedural' | 'gaia' | 'hybrid'
- `gaia_enabled` : true/false
- `gaia_radius_ly` : Rayon d'ÃÂ©toiles GAIA chargÃÂ©es (dÃÂ©faut: 100 AL)
- `procedural_density` : DensitÃÂ© gÃÂ©nÃÂ©ration procÃÂ©durale (dÃÂ©faut: 0.05)
- `known_space_radius` : Rayon espace connu (dÃÂ©faut: 50 AL)

---

## Ã°Å¸ââ¹ PHASE PRÃâ°-2.2 : INTÃâ°GRATION GAIA

### Contexte GAIA

**GAIA** (ESA) = Catalogue astronomique rÃÂ©el avec :
- Position 3D des ÃÂ©toiles
- Type spectral (O, B, A, F, G, K, M)
- Magnitude, luminositÃÂ©
- Distance en annÃÂ©es-lumiÃÂ¨re
- Noms/identifiants

**Sources de donnÃÂ©es :**
- Fichier CSV exportÃÂ© de GAIA DR3
- API GAIA (optionnel, pour mise ÃÂ  jour)
- Sous-ensemble filtrÃÂ© (ÃÂ©toiles < 100 AL)

### FonctionnalitÃÂ©s

#### 1. Importateur GAIA

**Commande :**
```bash
php artisan gaia:import [fichier.csv] [--radius=100] [--force]
```

**Fonctionnement :**
1. Lire fichier CSV GAIA
2. Filtrer ÃÂ©toiles dans rayon spÃÂ©cifiÃÂ©
3. Convertir coordonnÃÂ©es galactiques Ã¢â â coordonnÃÂ©es jeu
4. CrÃÂ©er `SystemeStellaire` pour chaque ÃÂ©toile
5. Marquer comme `source_gaia = true`

#### 2. Structure de DonnÃÂ©es

**Ajout ÃÂ  `systemes_stellaires` :**

```php
$table->boolean('source_gaia')->default(false);
$table->string('gaia_source_id')->nullable()->unique();
$table->decimal('gaia_ra', 12, 8)->nullable();  // Right Ascension
$table->decimal('gaia_dec', 12, 8)->nullable(); // Declination
$table->decimal('gaia_distance_ly', 10, 2)->nullable();
$table->decimal('gaia_magnitude', 8, 4)->nullable();
```

#### 3. Conversion CoordonnÃÂ©es

**De coordonnÃÂ©es galactiques (RA, DEC, Distance) vers (secteur_x, secteur_y, secteur_z) :**

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

#### 4. Nom des Ãâ°toiles

**Sources :**
- Nom GAIA officiel si disponible
- Nom catalogue (HD, HIP, etc.)
- Sinon : `GAIA-{source_id_court}`

**Ãâ°toiles cÃÂ©lÃÂ¨bres ÃÂ  nommer :**
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
            $this->command->warn('Fichier GAIA non trouvÃÂ©. GÃÂ©nÃÂ©ration procÃÂ©durale...');
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
                'gaia_magnitude' => $data['magnitude'] œ null,
                // Generate planets
                'nb_planetes' => rand(0, 12),
            ]);

            $count++;
        }

        fclose($file);

        $this->command->info("Ã¢Åâ¦ {$count} systÃÂ¨mes GAIA importÃÂ©s");
    }
}
```

#### 6. Fichier de DonnÃÂ©es GAIA

**Localisation :**
```
database/data/
Ã¢ââÃ¢ââ¬Ã¢ââ¬ gaia_nearby_stars.csv
```

**Format CSV :**
```csv
source_id,ra,dec,distance,spectral_type,magnitude,name
12345678,123.456,45.678,4.37,G2V,0.01,Alpha Centauri A
...
```

**Comment obtenir :**
1. TÃÂ©lÃÂ©charger depuis GAIA Archive (https://gea.esac.esa.int/archive/)
2. Filtrer ÃÂ©toiles < 100 AL
3. Exporter colonnes nÃÂ©cessaires
4. OU utiliser sous-ensemble prÃÂ©-filtrÃÂ© fourni

---

## Ã°Å¸ââ¹ PHASE PRÃâ°-2.3 : BACKEND ADMINISTRATIF

### Objectif

Interface d'administration pour visualiser et gÃÂ©rer l'univers.

### FonctionnalitÃÂ©s

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
            abort(403, 'AccÃÂ¨s refusÃÂ©. Vous devez ÃÂªtre administrateur.');
        }

        return $next($request);
    }
}
```

**Ajout ÃÂ  `comptes` :**
```php
$table->boolean('is_admin')->default(false);
```

#### 3. Carte Stellaire Interactive

**BibliothÃÂ¨que :** Three.js pour rendu 3D WebGL

**Vue : `resources/views/backend/carte.blade.php` :**
- Canvas Three.js
- Affichage systÃÂ¨mes stellaires (points 3D)
- Couleur selon type spectral
- Affichage position joueurs (icÃÂ´nes vaisseaux)
- Zoom, rotation, pan
- Clic sur systÃÂ¨me Ã¢â â dÃÂ©tails
- Filtres (type ÃÂ©toile, source GAIA, dÃÂ©couvert/non dÃÂ©couvert)

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

        sphere.position.set(systeme.x, systeme.z, systeme.y); // Y et Z inversÃÂ©s pour Three.js
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

## Ã°Å¸ââ¹ PHASE PRÃâ°-2.4 : SAUVEGARDE/RESTAURATION

### Objectif

Permettre sauvegarde complÃÂ¨te des donnÃÂ©es avec versioning et restauration.

### FonctionnalitÃÂ©s

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
- `--tables=all|comptes,personnages,...` : Tables ÃÂ  sauvegarder
- `--format=json|sql` : Format de sortie
- `--output=path` : Chemin de sortie (dÃÂ©faut: storage/backups/)
- `--force` : Forcer import sans confirmation
- `--merge` : Fusionner avec donnÃÂ©es existantes (sinon reset)

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
- Contient structure ET donnÃÂ©es
- Versioning pour compatibilitÃÂ©
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
        $tables = $options['tables'] œ $this->tables;
        $format = $options['format'] œ 'json';

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
        $merge = $options['merge'] œ false;

        if (!file_exists($path)) {
            throw new \RuntimeException("Fichier non trouvÃÂ©: {$path}");
        }

        $backup = json_decode(file_get_contents($path), true);

        // VÃÂ©rifier version
        $this->validateVersion($backup['meta']);

        // Reset si pas merge
        if (!$merge) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($backup['data'] as $table => $rows) {
                DB::table($table)->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Importer donnÃÂ©es
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

**FonctionnalitÃÂ©s :**
- Liste des sauvegardes existantes
- Bouton "CrÃÂ©er sauvegarde"
- Upload fichier de sauvegarde
- Bouton "Restaurer" pour chaque sauvegarde
- Affichage mÃÂ©tadonnÃÂ©es (date, version, tables)
- TÃÂ©lÃÂ©chargement sauvegarde

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

    <button type="submit">CrÃÂ©er Sauvegarde</button>
</form>
```

**Formulaire Import :**
```html
<form action="{{ route('backend.sauvegarde.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="backup_file" accept=".json,.sql" required>

    <label>
        <input type="checkbox" name="merge">
        Fusionner avec donnÃÂ©es existantes (sinon remplace tout)
    </label>

    <button type="submit">Restaurer</button>
</form>
```

---

## Ã°Å¸âÅ  RÃâ°CAPITULATIF

### Ce qui sera livrÃÂ©

1. Ã¢Åâ¦ **Commande `db:reset-game`** avec modes (basic, gaia, hybrid)
2. Ã¢Åâ¦ **Seeders rÃÂ©organisÃÂ©s** (GaiaSeeder, UniverseSeeder, etc.)
3. Ã¢Åâ¦ **IntÃÂ©gration GAIA** (import ÃÂ©toiles rÃÂ©elles, conversion coordonnÃÂ©es)
4. Ã¢Åâ¦ **Backend administratif** avec carte 3D interactive (Three.js)
5. Ã¢Åâ¦ **SystÃÂ¨me sauvegarde/restauration** avec versioning JSON/SQL

### Structure Fichiers

```
app/
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ Console/Commands/
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ DbResetGame.php
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GaiaImport.php
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ BackupExport.php
Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ BackupImport.php
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ Http/
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ Controllers/
Ã¢ââ   Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ BackendController.php
Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ Middleware/
Ã¢ââ       Ã¢ââÃ¢ââ¬Ã¢ââ¬ IsAdmin.php
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ Services/
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ BackupService.php
Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ GaiaCoordinateConverter.php
database/
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ data/
Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ gaia_nearby_stars.csv
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ migrations/
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ xxxx_add_gaia_fields_to_systemes_stellaires.php
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ xxxx_add_is_admin_to_comptes.php
Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ xxxx_create_universe_configs_table.php
Ã¢ââÃ¢ââ¬Ã¢ââ¬ seeders/
    Ã¢âÅÃ¢ââ¬Ã¢ââ¬ DatabaseSeeder.php (refactor)
    Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GaiaSeeder.php (new)
    Ã¢ââÃ¢ââ¬Ã¢ââ¬ UniverseSeeder.php (refactor)
resources/
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ views/backend/
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ dashboard.blade.php
Ã¢ââ   Ã¢âÅÃ¢ââ¬Ã¢ââ¬ carte.blade.php
Ã¢ââ   Ã¢ââÃ¢ââ¬Ã¢ââ¬ sauvegarde.blade.php
Ã¢ââÃ¢ââ¬Ã¢ââ¬ js/backend/
    Ã¢ââÃ¢ââ¬Ã¢ââ¬ carte3d.js
routes/
Ã¢ââÃ¢ââ¬Ã¢ââ¬ backend.php (new)
storage/
Ã¢ââÃ¢ââ¬Ã¢ââ¬ backups/ (directory)
```

### Ordre d'ImplÃÂ©mentation

1. **Jour 1 (4h)** : SystÃÂ¨me BDD + Seeders
2. **Jour 2 (6h)** : IntÃÂ©gration GAIA
3. **Jour 3 (8h)** : Backend + Carte 3D
4. **Jour 4 (4h)** : Sauvegarde/Restauration

**Total estimÃÂ© : 22h (~3 jours)**

---

## Ã°Å¸Å½Â¯ APRÃËS PRÃâ°-PHASE 2

Une fois ces infrastructures en place :

1. **Synchroniser branche avec dev**
2. **DÃÂ©marrer Phase 2 : Ãâ°conomie de Base**

---

**Document vivant - DerniÃÂ¨re mise ÃÂ  jour : 2025-11-18**
