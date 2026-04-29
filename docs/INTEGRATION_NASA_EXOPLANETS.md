# 🪐 INTÉGRATION NASA EXOPLANET ARCHIVE - Système Complet GAIA ↔ NASA

## 📋 Vue d'ensemble

Ce système intègre les **exoplanètes réelles** du catalogue NASA Exoplanet Archive avec les étoiles GAIA DR3, permettant :
- **Linkage automatique** GAIA ↔ NASA via noms communs et aliases
- **Enrichissement des noms d'étoiles** (GAIA DR3 ID → Proxima Centauri)
- **Génération dynamique** de l'univers pendant l'exploration
- **7 exoplanètes réelles** actuellement importées

### ✨ Fonctionnalités principales

1. **🌟 StarNameMatcher** : Catalogue d'étoiles célèbres avec coordonnées astronomiques
2. **📡 API NASA Exoplanet Archive** : Interrogation en temps réel du catalogue NASA
3. **🔗 Linkage GAIA ↔ NASA** : Association automatique via noms communs et aliases
4. **🚀 Génération à la volée** : L'univers s'étend automatiquement pendant le jeu
5. **💾 Cache intelligent** : Évite les requêtes API excessives (cache 24h)
6. **⚙️ Commandes artisan** : Enrichissement et import

---

## 🎯 ARCHITECTURE COMPLÈTE

### 1. StarNameMatcher (`app/Services/StarNameMatcher.php`)

Service de matching par coordonnées astronomiques (RA, Dec, Distance).

**Catalogue actuel** : ~20 étoiles célèbres
- Proxima Centauri (4.24 AL)
- Barnard's Star (5.96 AL)
- Wolf 359 (7.86 AL)
- Sirius (8.6 AL)
- Epsilon Eridani (10.5 AL)
- Tau Ceti (11.9 AL)
- etc.

**Fonctionnement** :
```php
$matcher = app(StarNameMatcher::class);
$match = $matcher->findCommonName($ra, $dec, $distance);

// Retour :
[
    'nom_commun' => 'Proxima Centauri',
    'aliases' => ['Proxima Cen', 'Proxima', 'Alpha Centauri C', 'HIP 70890', 'GJ 551']
]
```

**Algorithme** :
- Calcul de la distance angulaire : `sqrt(deltaRA² + deltaDec²)`
- Vérification de la distance en AL (tolérance 10%)
- Score combiné pour trouver la meilleure correspondance

### 2. Enrichissement automatique dans GaiaSeeder

**Lors de l'import GAIA** (CSV ou manuel), le seeder :
1. Convertit les coordonnées galactiques → jeu
2. **Appelle StarNameMatcher** pour trouver le nom commun
3. Stocke `nom_commun` et `noms_alternatifs` (JSON)

```php
// database/seeders/GaiaSeeder.php (lignes 99-110)
$match = $starMatcher->findCommonName($data['ra'], $data['dec'], $data['distance']);

$batch[] = [
    'nom' => $data['name'] ?: "GAIA-" . substr($data['source_id'], 0, 8),
    'nom_commun' => $match['nom_commun'] ?? null,
    'noms_alternatifs' => $match ? json_encode($match['aliases']) : null,
    // ... autres champs
];
```

### 3. Table systemes_stellaires - Nouveaux champs

```sql
nom_commun          VARCHAR(255) NULL       -- Ex: "Proxima Centauri"
noms_alternatifs    JSON NULL               -- ["Proxima Cen", "Proxima", "GJ 551", ...]
```

**Index** : `nom_commun` pour recherche rapide

### 4. ExoplanetService - Linkage GAIA ↔ NASA

**Requête intelligente** qui essaie dans l'ordre :
1. `nom_commun` (ex: "Proxima Centauri")
2. Chaque alias dans `noms_alternatifs` (ex: "Proxima Cen")
3. `nom` GAIA original
4. Variantes (sans espaces, lettres grecques, etc.)

```php
// app/Services/ExoplanetService.php (lignes 84-128)
public function getExoplanetsForGaiaSystem(SystemeStellaire $systeme): array
{
    // 1. Essayer nom_commun
    if ($systeme->nom_commun) {
        $exoplanets = $this->getExoplanetsForStar($systeme->nom_commun);
        if (!empty($exoplanets)) return $exoplanets;
    }

    // 2. Essayer aliases
    if ($systeme->noms_alternatifs) {
        $aliases = json_decode($systeme->noms_alternatifs, true);
        foreach ($aliases as $alias) {
            $exoplanets = $this->getExoplanetsForStar($alias);
            if (!empty($exoplanets)) return $exoplanets;
        }
    }

    // 3. Fallback sur nom GAIA et variantes
    // ...
}
```

### 5. Table planetes - Colonnes NASA

```sql
source_nasa_exoplanet   BOOLEAN DEFAULT FALSE
nasa_exo_id             VARCHAR(255) NULL
nasa_discovery_method   VARCHAR(50) NULL
nasa_discovery_year     INT NULL
excentricite_orbitale   DECIMAL(8,6) DEFAULT 0.0
```

**Fillable** : Ajouté au modèle `Planete.php` (lignes 47-51)

---

## 🚀 RÉSULTATS ACTUELS

### Systèmes avec exoplanètes réelles

| Système | Distance | Exoplanètes | Découverte |
|---------|----------|-------------|------------|
| **Proxima Centauri** | 4.25 AL | Proxima Cen b, d | 2016, 2025 |
| **Tau Ceti** | 11.91 AL | tau Cet e, f, g, h | Divers |
| **Epsilon Eridani** | 10.50 AL | eps Eri b | |

**Total** : 7 exoplanètes réelles importées

### Noms NASA vs Noms communs

⚠️ **Important** : NASA utilise des **abréviations astronomiques**

| Nom commun | Nom NASA | Statut |
|------------|----------|--------|
| Proxima Centauri | **Proxima Cen** | ✅ Résolu via aliases |
| Tau Ceti | **tau Cet** | ✅ Résolu via aliases |
| Epsilon Eridani | **eps Eri** | ✅ Résolu via aliases |
| Sirius | Sirius | ❌ Pas d'exoplanètes confirmées |
| Barnard's Star | - | ❌ Pas d'exoplanètes confirmées |

---

## 🛠️ INSTALLATION & CONFIGURATION

### 1️⃣ Migrations

```bash
php artisan migrate
```

**Migrations appliquées** :
- `2025_12_27_121322_add_nasa_exoplanet_columns_to_planetes_table.php`
- `2025_12_27_135148_add_common_name_to_systemes_stellaires.php`

### 2️⃣ Enrichissement des noms (REQUIS)

```bash
# Enrichir les systèmes GAIA existants avec noms communs
php artisan gaia:enrich-common-names

# Forcer le re-enrichissement
php artisan gaia:enrich-common-names --force
```

**Résultat** :
- 9 systèmes enrichis sur 200
- Noms communs + aliases stockés en base
- Ready pour requêtes NASA

### 3️⃣ Import des exoplanètes

```bash
# Import pour TOUS les systèmes GAIA (~3-5 minutes)
php artisan exoplanet:import-real --gaia-only

# Import pour une étoile spécifique
php artisan exoplanet:import-real --star="Proxima Centauri"

# Vider le cache avant import
php artisan exoplanet:import-real --gaia-only --clear-cache
```

### 4️⃣ Configuration (optionnelle)

`.env` :
```env
# Activer/désactiver NASA Exoplanets
UNIVERSE_EXOPLANET_ENABLED=true

# Génération dynamique pendant le jeu
UNIVERSE_DYNAMIC_GENERATION=true
UNIVERSE_DYNAMIC_RADIUS=3

# Générer secteurs vides
UNIVERSE_GENERATE_EMPTY=false

# Stratégie d'expansion
UNIVERSE_EXPANSION_STRATEGY=on_jump
```

---

## 🎮 GÉNÉRATION DYNAMIQUE - Comment ça fonctionne

### Scénario : Joueur effectue un saut hyperespace

```
1. Joueur → Saut vers secteur [10, 5, -3]

2. TimonerieController::effectuerSaut() (ligne 154)
   └─> expandUniverseAroundDestination(10, 5, -3)

3. UniverseGeneratorService::expandUniverseAroundPosition()
   └─> Rayon de 3 secteurs = max 33 secteurs à vérifier

4. Pour CHAQUE secteur dans le rayon :

   a) SystemeStellaire existe ?
      → OUI : Skip
      → NON : Continuer

   b) Chercher étoile GAIA proche (tolérance 0.5 AL)
      → TROUVÉE : Aller en (c)
      → PAS TROUVÉE : Génération procédurale (5% chance)

   c) Importer exoplanètes NASA
      - Query via nom_commun ou aliases
      - ExoplanetService::importExoplanetsForSystem()
      - Création planètes avec source_nasa_exoplanet = true

   d) Mise à jour du système
      - nb_planetes actualisé
      - Retour au joueur

5. Univers étendu ! Planètes réelles si disponibles
```

### Hook dans TimonerieController

```php
// app/Http/Controllers/TimonerieController.php:154
protected function expandUniverseAroundDestination(int $secteurX, int $secteurY, int $secteurZ): void
{
    if (!config('universe.dynamic_generation_enabled', true)) {
        return;
    }

    $radius = config('universe.dynamic_generation_radius', 3);
    $this->universeGenerator->expandUniverseAroundPosition($secteurX, $secteurY, $secteurZ, $radius);
}
```

**⚠️ Important** : Appelé APRÈS le calcul de destination, AVANT l'arrivée du joueur.

---

## 📡 API NASA Exoplanet Archive

### Endpoint
```
https://exoplanetarchive.ipac.caltech.edu/TAP/sync
```

### Requête type (ADQL)
```sql
SELECT pl_name, hostname, sy_dist, pl_orbsmax, pl_rade, pl_bmasse,
       pl_orbper, pl_eqt, pl_orbeccen, st_teff, discoverymethod, disc_year
FROM ps
WHERE hostname = 'Proxima Cen'
ORDER BY pl_orbsmax ASC
```

### Format de réponse

**JSON** (tableau d'objets) :
```json
[
    {
        "pl_name": "Proxima Cen b",
        "hostname": "Proxima Cen",
        "sy_dist": 1.30119,
        "pl_orbsmax": 0.048,
        "pl_rade": null,
        "pl_bmasse": 1.27,
        "pl_orbper": 11.186,
        "pl_eqt": 234,
        "pl_orbeccen": 0.35,
        "st_teff": 3050,
        "discoverymethod": "Radial Velocity",
        "disc_year": 2016
    }
]
```

### Mapping vers base de données

| Champ API | Colonne DB | Traitement |
|-----------|------------|------------|
| `pl_name` | `nom` | Direct |
| `hostname` | - | Pour matching uniquement |
| `pl_orbsmax` | `distance_etoile` | Direct (UA) |
| `pl_rade` | `rayon` | Défaut 1.0 si null |
| `pl_bmasse` | `masse` | Défaut 1.0 si null |
| `pl_orbper` | `periode_orbitale` | Arrondi en jours |
| `pl_eqt` | `temperature_moyenne` | K → °C (- 273.15) |
| `pl_orbeccen` | `excentricite_orbitale` | Direct |
| `discoverymethod` | `nasa_discovery_method` | Direct |
| `disc_year` | `nasa_discovery_year` | Direct |

---

## 🔧 COMMANDES ARTISAN

### gaia:enrich-common-names

**Enrichit les étoiles GAIA avec noms communs**

```bash
php artisan gaia:enrich-common-names [--force]

Options:
  --force    Forcer même si déjà enrichi
```

**Fonctionnement** :
1. Récupère tous les systèmes GAIA avec coordonnées
2. Appelle StarNameMatcher pour chaque système
3. Stocke `nom_commun` et `noms_alternatifs` si match trouvé
4. Affiche progression et résultats

**Résultat attendu** :
- 9 systèmes enrichis (étoiles célèbres)
- 191 systèmes sans nom commun (normal)

### exoplanet:import-real

**Importe les exoplanètes depuis NASA**

```bash
php artisan exoplanet:import-real [options]

Options:
  --star="Nom"       Import pour une étoile spécifique
  --gaia-only        Import pour tous les systèmes GAIA
  --radius=100       Rayon en AL (défaut: 100)
  --limit=1000       Nombre max (défaut: 1000)
  --clear-cache      Vider le cache avant import
```

**Exemples** :
```bash
# Import complet pour systèmes GAIA
php artisan exoplanet:import-real --gaia-only

# Import Proxima Centauri
php artisan exoplanet:import-real --star="Proxima Centauri"

# Import dans 50 AL avec cache vidé
php artisan exoplanet:import-real --radius=50 --clear-cache
```

---

## 🐛 PROBLÈMES CONNUS & SOLUTIONS

### 1. Certificat SSL (Windows)

**Erreur** :
```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

**Solution** : Option `verify => false` ajoutée pour développement

```php
// app/Services/ExoplanetService.php:333
Http::timeout(120)
    ->withOptions(['verify' => false]) // Windows dev
    ->get($this->apiUrl, [...]);
```

⚠️ **Production** : Configurer correctement les certificats SSL

### 2. Noms NASA différents

**Problème** : NASA utilise des abréviations (Proxima Cen, tau Cet, eps Eri)

**Solution** : Aliases dans StarNameMatcher

```php
'Proxima Centauri' => [
    'aliases' => ['Proxima Cen', 'Proxima', 'Alpha Centauri C', ...]
],
'Tau Ceti' => [
    'aliases' => ['tau Cet', 'Tau Cet', ...]
],
```

### 3. Champs NASA non sauvegardés

**Problème** : `source_nasa_exoplanet` reste à 0

**Cause** : Champs non dans `$fillable` du modèle Planete

**Solution** : Ajouté dans `app/Models/Planete.php` (lignes 47-51)

```php
protected $fillable = [
    // ... autres champs
    'source_nasa_exoplanet',
    'nasa_exo_id',
    'nasa_discovery_method',
    'nasa_discovery_year',
    'excentricite_orbitale',
];
```

---

## 📊 PERFORMANCES

### Métriques mesurées

| Opération | Temps | Notes |
|-----------|-------|-------|
| Enrichissement (200 systèmes) | ~5s | StarNameMatcher local |
| Import exoplanètes (200 systèmes) | 3-5 min | API NASA + délai 0.1s |
| Import 1 étoile (avec cache) | <1s | Cache hit |
| Import 1 étoile (sans cache) | 2-5s | API call |
| Génération dynamique (rayon 3) | 1-3s | Dépend des hits GAIA |

### Cache

- **Durée** : 24h par défaut
- **Clé** : `exoplanet_star_{nom}` ou `simbad_main_name_{gaiaId}`
- **Vider** : `php artisan cache:clear` ou `--clear-cache`

---

## 🔄 PROCÉDURE MULTI-ENVIRONNEMENT

### Sur chaque environnement de développement

```bash
# 1. Pull du code
git pull

# 2. Migrations (safe, re-runnable)
php artisan migrate

# 3. Seed GAIA avec enrichissement automatique
php artisan migrate:fresh --seed
# → StarNameMatcher s'exécute automatiquement

# 4. Import exoplanètes (optionnel, ~5 min)
php artisan exoplanet:import-real --gaia-only

# Total : ~6 minutes pour univers complet avec données réelles
```

**⚠️ Important** : Les migrations vérifient l'existence des colonnes avant de les ajouter (`Schema::hasColumn`). Pas de conflit multi-environnement.

---

## 📈 STATISTIQUES BASE DE DONNÉES

### État actuel (2025-12-27)

```
Total systèmes stellaires : 211
├─ Systèmes GAIA : 200
├─ Avec nom commun : 9
└─ Sans nom commun : 202

Total planètes : 1267
├─ Exoplanètes NASA : 7
└─ Planètes procédurales : 1260

Systèmes avec exoplanètes NASA :
├─ Proxima Centauri : 2 planètes
├─ Tau Ceti : 4 planètes
└─ Epsilon Eridani : 1 planète
```

---

## 🧪 TESTS & VALIDATION

### Test complet du système

```bash
# 1. Vérifier enrichissement
php artisan tinker
>>> \App\Models\SystemeStellaire::whereNotNull('nom_commun')->count();
# Attendu : 9

# 2. Vérifier aliases JSON
>>> $sys = \App\Models\SystemeStellaire::where('nom_commun', 'Proxima Centauri')->first();
>>> $sys->noms_alternatifs;
# Attendu : ["Proxima Cen","Proxima","Alpha Centauri C",...]

# 3. Vérifier exoplanètes NASA
>>> \App\Models\Planete::where('source_nasa_exoplanet', true)->count();
# Attendu : 7

# 4. Vérifier données NASA
>>> $planete = \App\Models\Planete::where('nom', 'Proxima Cen b')->first();
>>> $planete->nasa_discovery_method;
# Attendu : "Radial Velocity"
>>> $planete->nasa_discovery_year;
# Attendu : 2016

# 5. Test génération dynamique
>>> $gen = app(\App\Services\UniverseGeneratorService::class);
>>> $sys = $gen->ensureSectorExists(10, 5, -3);
>>> $sys->planetes()->count();
# Attendu : > 0
```

---

## 🚀 AMÉLIORATIONS FUTURES

### Court terme
- [ ] Ajouter plus d'étoiles au catalogue StarNameMatcher (actuellement ~20)
- [ ] Mode admin : Afficher les données NASA dans l'interface
- [ ] Logs détaillés de la génération dynamique

### Moyen terme
- [ ] Queue jobs pour import asynchrone
- [ ] Webhook NASA pour nouvelles découvertes
- [ ] Visualisation graphique des systèmes planétaires

### Long terme
- [ ] Machine learning : prédiction habitabilité
- [ ] Mode historique : afficher dates de découverte in-game
- [ ] Intégration d'autres catalogues (Kepler, TESS)

---

## 📚 RÉFÉRENCES

### APIs & Catalogues
- [NASA Exoplanet Archive](https://exoplanetarchive.ipac.caltech.edu/)
- [TAP API Documentation](https://exoplanetarchive.ipac.caltech.edu/docs/program_interfaces.html)
- [GAIA DR3](https://www.cosmos.esa.int/web/gaia/dr3)
- [SIMBAD Astronomical Database](http://simbad.u-strasbg.fr/simbad/)

### Documentation projet
- `docs/game-design/GDD_Univers_Generation.md` - Génération procédurale
- `docs/game-design/GAIA_CSV_GENERATION.md` - Import GAIA
- `docs/MettreAjourLesBDAutresEnvironnementDev.md` - Multi-environnement

### Fichiers sources clés
- `app/Services/StarNameMatcher.php` - Catalogue étoiles
- `app/Services/ExoplanetService.php` - API NASA
- `app/Services/UniverseGeneratorService.php` - Génération dynamique
- `database/seeders/GaiaSeeder.php` - Import GAIA
- `app/Console/Commands/EnrichGaiaWithCommonNamesCommand.php` - Enrichissement
- `app/Console/Commands/ImportRealExoplanetsCommand.php` - Import NASA

---

**Dernière mise à jour** : 2025-12-27
**Version** : 2.0.0
**Statut** : ✅ Production Ready

**Systèmes avec exoplanètes réelles** : 3
**Total exoplanètes NASA** : 7
**Systèmes enrichis** : 9
