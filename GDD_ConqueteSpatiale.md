# GAME DESIGN DOCUMENT - CONQUÊTE SPATIALE
## Guide de Référence Rapide pour Claude Code

**Version:** 1.0
**Dernière MAJ:** 2025-12-08
**Projet:** Jeu web de conquête galactique (Laravel + Console web)

---

## TABLE DES MATIÈRES

1. [RÈGLES CRITIQUES](#règles-critiques) ⚠️ **LIRE EN PREMIER**
2. [Vue d'Ensemble](#vue-densemble)
3. [Architecture Technique](#architecture-technique)
4. [Interface Utilisateur](#interface-utilisateur)
5. [Système de Coordonnées](#système-de-coordonnées)
6. [Navigation et Déplacements](#navigation-et-déplacements)
7. [Vaisseaux et Équipements](#vaisseaux-et-équipements)
8. [Économie](#économie)
9. [Découverte et Exploration](#découverte-et-exploration)
10. [Documents Détaillés](#documents-détaillés)

---

## ⚠️ RÈGLES CRITIQUES

### 🚨 COORDONNÉES - NE PAS MULTIPLIER PAR 10 !

**RÈGLE ABSOLUE :** Les coordonnées `secteur_x/y/z` NE SONT PAS multipliées par 10.

```php
// ❌ ERREUR FATALE - Ne JAMAIS faire ça !
$abs_x = $systeme->secteur_x * 10 + $systeme->position_x;

// ✅ CORRECT
$abs_x = $systeme->secteur_x;  // Directement la coordonnée en AL
```

**Pourquoi ?**
- `secteur_x/y/z` = Coordonnées en Années-Lumière (valeurs ENTIÈRES)
- `position_x/y/z` = Position décimale DANS le secteur (0.0 à 1.0)
- La carte affiche directement `secteur_x/y/z`

**Exemple :**
```
Système "Vega Aurigae":
  secteur_x = 0, secteur_y = 0, secteur_z = 4
  position_x = 0.962, position_y = 0.779, position_z = 0.530

Coordonnées CARTE : (0, 0, 4) AL  ← Afficher ça
Distance réelle : 4.53 AL         ← secteur_z + position_z
```

**Voir:** `docs/game-design/SYSTEME_COORDONNEES.md` pour détails complets

---

### 🚨 PRINCIPE PJ (Personnage Joueur)

**Un joueur = Un PJ qui pilote un vaisseau**

```
JOUEUR (Compte)
  ├─ PJ Principal (actif par défaut)
  │   └─ Vaisseau actif
  ├─ PJ Secondaire 1
  │   └─ Vaisseau(x) possédé(s)
  └─ PJ Secondaire 2
      └─ Vaisseau(x) possédé(s)
```

❌ **ANCIEN:** "Un joueur = Un vaisseau"
✅ **CORRECT:** "Un PJ pilote un vaisseau à la fois"

---

### 🚨 VALEURS NUMÉRIQUES INDICATIVES

**TOUS les chiffres, coûts, durées sont des SUGGESTIONS non validées.**
- À tester et équilibrer lors de l'implémentation
- Les formules sont des exemples à adapter

---

## VUE D'ENSEMBLE

### Concept du Jeu

**Genre:** Jeu web de conquête galactique multijoueur asynchrone
**Interface:** Console web (commandes + visualisation)
**Rythme:** Tour par tour (1 tour = 1/2 jour in-game)
**Technologie:** Laravel 11 + Blade + Tailwind CSS

### Piliers de Gameplay

1. **Exploration** - Découvrir systèmes stellaires inconnus
2. **Économie** - Chaîne production complexe, commerce
3. **Navigation** - Hyper-espace + déplacement conventionnel
4. **Combat** - Affrontements tactiques tour par tour
5. **Conquête** - Bases spatiales, expansion territoriale

### État Actuel du Projet

**Phase:** Pré-Phase 2 (Infrastructure interface)
**Implémenté:**
- ✅ Génération univers (données GAIA DR3)
- ✅ Carte 3D interactive (Three.js)
- ✅ Système authentification
- ✅ Models Eloquent (Vaisseau, SystemeStellaire, ObjetSpatial, etc.)

**En cours:**
- 🔄 Interface de jeu (header + menus contextuels)
- 🔄 Système navigation (Timonerie)
- 🔄 Routes et contrôleurs

---

## ARCHITECTURE TECHNIQUE

### Stack Technique

```
Laravel 11.31.0
├─ PHP 8.4
├─ MySQL/MariaDB
├─ Blade Templates
├─ Tailwind CSS
└─ Three.js (carte 3D)
```

### Structure Laravel

```
app/
├─ Http/
│  ├─ Controllers/
│  │  ├─ AdminController.php      # Gestion admin/carte
│  │  ├─ PersonnageController.php # Gestion personnage
│  │  ├─ VaisseauController.php   # Gestion vaisseau
│  │  ├─ StationController.php    # Gestion station
│  │  ├─ JeuController.php        # Utilitaires jeu
│  │  └─ TimonerieController.php  # Navigation
│  └─ Middleware/
│     └─ Authenticate.php
├─ Models/
│  ├─ User.php
│  ├─ Personnage.php
│  ├─ ObjetSpatial.php           # Parent abstrait
│  ├─ Vaisseau.php               # Hérite ObjetSpatial
│  ├─ SystemeStellaire.php
│  ├─ Planete.php
│  └─ Base.php                   # Hérite ObjetSpatial
└─ View/
   └─ Components/
      └─ GameHeader.php

resources/views/
├─ components/
│  └─ game-header.blade.php      # Header 4 colonnes
├─ layouts/
│  └─ app.blade.php              # Layout principal
├─ personnage/
│  ├─ dossier.blade.php
│  └─ gestion.blade.php
├─ navire/
│  ├─ timonerie.blade.php
│  └─ equipage.blade.php
├─ station/
│  ├─ hall.blade.php
│  ├─ hangar.blade.php
│  └─ marche.blade.php
└─ jeu/
   ├─ carte.blade.php
   └─ profil.blade.php
```

### Routes Principales

```php
// Routes authentifiées (web.php)
Route::middleware('auth')->group(function () {
    // Admin
    Route::get('/admin/univers', [AdminController::class, 'univers']);

    // Navigation
    Route::get('/navire/timonerie', [VaisseauController::class, 'timonerie']);
    Route::post('/navire/sauter', [TimonerieController::class, 'effectuerSaut']);
    Route::post('/navire/deplacer', [TimonerieController::class, 'deplacerConventionnel']);
    Route::post('/navire/amarrer', [TimonerieController::class, 'amarrer']);

    // Personnage
    Route::get('/personnage/dossier', [PersonnageController::class, 'dossier']);
    Route::get('/personnage/gestion', [PersonnageController::class, 'gestion']);

    // Station
    Route::get('/station/hall', [StationController::class, 'hall']);
    Route::get('/station/hangar', [StationController::class, 'hangar']);

    // Jeu
    Route::get('/jeu/carte', [JeuController::class, 'carte']);
    Route::get('/jeu/profil', [JeuController::class, 'profil']);
});
```

### Models Eloquent Clés

#### ObjetSpatial (Parent)

```php
abstract class ObjetSpatial extends Model
{
    protected $table = 'CS_objets_spatiaux';
    protected $primaryKey = 'IdOS';

    // Coordonnées (6 champs)
    protected $fillable = [
        'secteur_x', 'secteur_y', 'secteur_z',    // INT (AL entières)
        'position_x', 'position_y', 'position_z',  // DECIMAL (0.0-1.0)
        'nom', 'type_objet',
    ];

    // Relations
    public function systeme() {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_id');
    }
}
```

#### Vaisseau

```php
class Vaisseau extends ObjetSpatial
{
    protected $table = 'CS_vaisseaux';

    protected $fillable = [
        'nom', 'personnage_id',
        'energie_actuelle', 'energie_max',
        'coque_actuelle', 'coque_max',
        'bouclier_actuel', 'bouclier_max',
        'reserve', 'combustible_actuel',
        // ... autres attributs
    ];

    public function personnage() {
        return $this->belongsTo(Personnage::class);
    }

    public function objetSpatial() {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
    }
}
```

#### SystemeStellaire

```php
class SystemeStellaire extends Model
{
    protected $table = 'CS_systemes_stellaires';
    protected $primaryKey = 'IdSys';

    protected $fillable = [
        'nom', 'secteur_x', 'secteur_y', 'secteur_z',
        'position_x', 'position_y', 'position_z',
        'puissance_solaire', 'classe_etoile',
        'nb_planetes', // Calculé dynamiquement
    ];

    public function planetes() {
        return $this->hasMany(Planete::class, 'systeme_id');
    }

    public function objetsSpatiaux() {
        return $this->hasMany(ObjetSpatial::class, 'systeme_id');
    }
}
```

---

## INTERFACE UTILISATEUR

### Structure Globale

```
┌─────────────────────────────────────────────────────────┐
│ HEADER (4 colonnes, 3-4 lignes max)                    │
├─────────────────────────────────────────────────────────┤
│ MENU PRINCIPAL (contextuel)                            │
│ [Personnage] [Navire/Station] [Jeu] [Admin?]          │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ CONTENU PRINCIPAL                                       │
│                                                         │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Header de Jeu (4 Colonnes)

**Fichier:** `resources/views/components/game-header.blade.php`

```
╔═══════════════════════════════════════════════════════════════════╗
║ 👤 JOUEUR         📍 SYSTÈME        🔷 SECTEUR    🚀 VAISSEAU    ║
╠═══════════════════════════════════════════════════════════════════╣
║                                                                   ║
║ Jean Dupont       Vulcanus (4,2,9)     [🔷]    USS Exploreur     ║
║ 💰 22.7M CR       ☀️ 80 ☄️ 70 🌍 14            ⚡ 92% 🛡️ 100%      ║
║ ⚡ PA: 24          📡 Rés. Sol                🔧 136,650          ║
║                                               🎯 → Vulcania       ║
╚═══════════════════════════════════════════════════════════════════╝
```

**Note:** Header compact avec CR et PA sur la même ligne (ligne 2).

**Colonnes:**

1. **JOUEUR** (gauche)
   - Ligne 1: Nom complet (prénom + nom)
   - Ligne 2: 💰 Crédits (formaté) + ⚡ PA (sur la même ligne)

2. **SYSTÈME** (centre-gauche)
   - Nom système + coordonnées `(secteur_x, y, z)`
   - ☀️ Puissance solaire (tooltip: valeur/100)
   - ☄️ Danger astéroïdes (tooltip: valeur/100)
   - 🌍 Nombre planètes (tooltip: "Planètes et POI")
   - 📡 Réseau satellite ou "Aucun réseau"

3. **SECTEUR** (centre-droit)
   - Icône visuelle du secteur actuel
   - À définir selon type de secteur

4. **VAISSEAU** (droite)
   - Nom du vaisseau
   - ⚡ Énergie % (tooltip: actuelle/max [+regen])
   - 🛡️ Structure % (tooltip: actuelle/max)
   - 🔰 Bouclier % (tooltip: actuel/max)
   - 🔧 Pièces Unitek
   - 🎯 Cible actuelle (si existe)

**Variables Blade nécessaires:**

```php
@props([
    'personnage',           // Model Personnage
    'vaisseau' => null,     // Model Vaisseau
    'systeme' => null,      // Model SystemeStellaire
    'secteur' => null       // String ou Model Secteur
])
```

### Menu Principal

**Inspiration:** Lunastar (structure 3 niveaux)

**Structure:**

```
PERSONNAGE                NAVIRE/STATION           JEU                 ADMIN
├─ Dossier               ├─ Timonerie            ├─ Carte            ├─ Univers
└─ Gestion               ├─ Équipage             ├─ Profil           ├─ Stats
                         ├─ Soute                └─ Aide             └─ Logs
                         └─ Équipement

                         [Station]
                         ├─ Hall
                         ├─ Hangar
                         ├─ Marché
                         ├─ Missions
                         └─ Cantina
```

**Contextuel:** Le menu "Navire" devient "Station" quand amarré.

**Routes associées:** Voir section Architecture > Routes

---

## SYSTÈME DE COORDONNÉES

### Structure des Données

**Chaque objet spatial a 6 champs de coordonnées:**

```sql
-- Coordonnées AL ENTIÈRES (Années-Lumière)
secteur_x INT NOT NULL,
secteur_y INT NOT NULL,
secteur_z INT NOT NULL,

-- Position DÉCIMALE dans le secteur (0.0 à 1.0)
position_x DECIMAL(10,3) NOT NULL,
position_y DECIMAL(10,3) NOT NULL,
position_z DECIMAL(10,3) NOT NULL
```

### Signification

**`secteur_x/y/z`:**
- Coordonnées en Années-Lumière (valeurs ENTIÈRES)
- C'est ce qui s'affiche sur la carte
- Exemple: `secteur_z = 4` → système à 4 AL sur axe Z

**`position_x/y/z`:**
- Position précise DANS le secteur
- Valeurs décimales entre 0.0 et 1.0
- Utilisé pour navigation fine et calculs de distance
- PAS affiché sur carte principale

### Exemples Concrets

```php
// Sol (Système Solaire)
secteur_x = 0, secteur_y = 0, secteur_z = 0
position_x = 0.000, position_y = 0.000, position_z = 0.000
→ Coordonnées carte: (0, 0, 0) AL

// Vega Aurigae (système test)
secteur_x = 0, secteur_y = 0, secteur_z = 4
position_x = 0.962, position_y = 0.779, position_z = 0.530
→ Coordonnées carte: (0, 0, 4) AL
→ Distance réelle: 4.53 AL (4 + 0.530)
```

### Calculs Courants

**Distance 3D:**
```php
function distance(ObjetSpatial $obj1, ObjetSpatial $obj2): float {
    $dx = ($obj1->secteur_x + $obj1->position_x) -
          ($obj2->secteur_x + $obj2->position_x);
    $dy = ($obj1->secteur_y + $obj1->position_y) -
          ($obj2->secteur_y + $obj2->position_y);
    $dz = ($obj1->secteur_z + $obj1->position_z) -
          ($obj2->secteur_z + $obj2->position_z);

    return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
}
```

**Même secteur:**
```php
function memeSecteur(ObjetSpatial $obj1, ObjetSpatial $obj2): bool {
    return $obj1->secteur_x === $obj2->secteur_x &&
           $obj1->secteur_y === $obj2->secteur_y &&
           $obj1->secteur_z === $obj2->secteur_z;
}
```

**Déplacement avec normalisation:**
```php
// Déplacer de 0.5 AL sur axe X
$vaisseau->position_x += 0.5;

// Normaliser si position >= 1.0 (changement de secteur)
if ($vaisseau->position_x >= 1.0) {
    $vaisseau->secteur_x += floor($vaisseau->position_x);
    $vaisseau->position_x = fmod($vaisseau->position_x, 1.0);
}

// Normaliser si position < 0.0
if ($vaisseau->position_x < 0.0) {
    $vaisseau->secteur_x += floor($vaisseau->position_x);
    $vaisseau->position_x = 1.0 + fmod($vaisseau->position_x, 1.0);
}
```

### Indexation Grille

```php
// Construction grille 3D (AdminController)
$grille = [];
foreach ($systemes as $systeme) {
    $x = $systeme->secteur_x;
    $y = $systeme->secteur_y;
    $z = $systeme->secteur_z;

    $grille[$x][$y][$z] = $systeme;
}

// Recherche d'un système
$secteurX = 0;
$secteurY = 0;
$secteurZ = 4;
$systeme = $grille[$secteurX][$secteurY][$secteurZ] ?? null;
// Trouve Vega Aurigae ✓
```

**Voir:** `docs/game-design/SYSTEME_COORDONNEES.md` pour guide complet

---

## NAVIGATION ET DÉPLACEMENTS

### Types de Propulsion

1. **Hyper-Espace (HE)**
   - Sauts inter-stellaires (longue distance)
   - Consommation: InitHE + (Distance × CoefHE)
   - Nécessite: Module Hyper-Espace

2. **MicroHE** (à étudier)
   - Petits sauts intra-système (0.1-2 UA)
   - Alternative au conventionnel lent
   - Module spécifique requis

3. **Conventionnel**
   - Déplacement sub-lumière (intra-système)
   - Lent mais précis
   - Types: Combustible ou Extraction énergétique

### Timonerie (Navigation)

**Fichier:** `app/Http/Controllers/TimonerieController.php`

**Actions principales:**

1. **Sauts Hyperspatiaux**
   ```php
   public function effectuerSaut(Request $request)
   {
       // 1. Vérifier disponibilité module HE
       // 2. Calculer distance cible
       // 3. Calculer consommation énergie
       // 4. Jet de Navigation (compétence PJ)
       // 5. Arrivée avec déviation possible
       // 6. Phase orientation post-saut
   }
   ```

2. **Déplacement Conventionnel**
   ```php
   public function deplacerConventionnel(Request $request)
   {
       // S'approcher d'une cible (planète, station, etc.)
       // Vitesse selon type moteur
       // Consommation énergie ou combustible
   }
   ```

3. **Amarrage**
   ```php
   public function amarrer(Request $request)
   {
       // Vérifier proximité cible
       // Vérifier compatibilité amarrage
       // Changer contexte -> Station
   }
   ```

### Découverte de Systèmes

**Algorithme:**
- Basé sur puissance solaire (min 10)
- Formule seuil: `500 + (Distance × 100)`
- Points tâche cumulatifs
- Plus on cherche → plus on trouve (petits/distants/cachés)

**Voir:** `docs/game-design/GDD_Systeme_Decouverte.md`

---

## VAISSEAUX ET ÉQUIPEMENTS

### Emplacements Vaisseau

**12 emplacements principaux:**
1. Pilotage
2. Moteur conventionnel
3. Moteur HE (Hyper-Espace)
4. Boucliers
5. Armement principal
6. Armement secondaire
7. Senseurs
8. Informatique
9. Soute/Cargo
10. Médical
11. Équipage
12. Modules spéciaux

### Système Soute

**3 niveaux transport personnel:**
- Niveau 1: Transport basique
- Niveau 2: Transport amélioré
- Niveau 3: Transport avancé

### Types Propulsion

**Combustible:**
- Consomme combustible physique
- Réserves à gérer

**Extraction énergétique:**
- Consomme énergie du vaisseau
- Régénération passive

**Voir:** `docs/game-design/GDD_Vaisseaux_Complet.md`

---

## ÉCONOMIE

### Système Économique

**Nœuds économiques:**
```
Hommes + Machines → Production
```

### Ressources

- **21 matières premières minières**
- Chaîne transformation industrielle complète
- 3 niveaux médicaments
- Système personnel et productivité

### Bases Spatiales

**L'Arche (module maître):**
- 5 modules standard + production énergie
- Extension par arches supplémentaires
- 13 types de modules (Antenne, Bar, Mine, Habitation, etc.)
- Système gestionnaire (changement possible)

**Voir:**
- `docs/game-design/GDD_Economie_Complete.md`
- `docs/game-design/GDD_Bases_Spatiales.md`

---

## DÉCOUVERTE ET EXPLORATION

### Système de Détection

- Fond d'étoiles dynamique
- Système par accumulation
- Piège des galaxies lointaines
- Capacités évolutives des vaisseaux
- Marché de l'information

### Génération Procédurale

**Moteur multi-univers:**
- Classification étoiles (O à M) + Courbe de Gauss
- Générateur simple (N×N×N, courbe Gauss)
- Générateur à chemins (routes entre systèmes)
- Gisements et rendement

**Données réelles:**
- Utilise GAIA DR3 (ESA)
- Système Sol à (0,0,0)
- ~18k systèmes dans 100 AL actuellement

**Carte joueur:**
- **Taille:** 21×21 AL (rayon 10 AL)
- **Centrée** sur position du vaisseau par défaut
- Affiche uniquement les systèmes **découverts**
- Plans de vue: XY, XZ, YZ
- **TODO:** Implémenter affichage graphique 3D (Three.js) comme la carte admin (`backend/carte.blade.php`)

**Voir:**
- `docs/game-design/GDD_Univers_Generation.md`
- `resources/views/backend/carte.blade.php` (exemple d'implémentation Three.js)

---

## DOCUMENTS DÉTAILLÉS

### Documents Game Design

**Localisation:** `docs/game-design/`

**Index central:**
- `GDD_Central.md` - Index et structure documentaire

**Documents principaux:**
- `GDD_Conquete_Galactique.md` (2609 lignes) - Document exhaustif complet
- `GDD_Interface_Navigation.md` (1593 lignes) - Interface et navigation détaillées
- `GDD_Architecture_Technique.md` (661 lignes) - Classes et architecture
- `SYSTEME_COORDONNEES.md` - Guide complet coordonnées
- `CORRECTIONS_IMPORTANTES.md` - Corrections majeures 2025-11

**Thématiques:**
- `GDD_Vaisseaux_Complet.md` - Specs vaisseaux et modules
- `GDD_Economie_Complete.md` - Système économique
- `GDD_Bases_Spatiales.md` - Stations et bases
- `GDD_Univers_Generation.md` - Génération procédurale
- `GDD_Systeme_Decouverte.md` - Algorithme découverte
- `GDD-PLAN-PHASE2-EconomieBase.md` (1446 lignes) - Plan Phase 2
- `GDD-PLAN-PrePhase2-Infrastructure.md` (855 lignes) - Plan Pré-Phase 2

**Systèmes spécifiques:**
- `SYSTEME_STATIONS.md` - Stations spatiales
- `SYSTEME_TEMPOREL.md` - Gestion du temps
- `SYSTEME_DETECTION_SCAN.md` - Détection et scan
- `SYSTEME_ECONOMIQUE.md` - Économie détaillée
- `SYSTEME_MINES_MAME.md` - Mines et extraction

**Contexte et outils:**
- `CONTEXT.md` - Contexte projet
- `GUIDE_DEMARRAGE.md` - Guide démarrage
- `BILAN_SYSTEME_CONTEXTUEL.md` (743 lignes) - Bilan technique
- `GAIA_CSV_GENERATION.md` - Import données GAIA
- `IDEES_FUTURES.md` - Idées et évolutions

### Documents Techniques

**Localisation:** `docs/`

- `INSTALLATION.md` - Installation projet
- `COMMANDES.md` - Commandes artisan
- `COMMANDES_ADMIN.md` - Commandes admin
- `TODO_PROJET.md` - Liste tâches projet
- `BILAN_TECHNIQUE.md` - Bilan technique

**Racine projet:**
- `README.md` - README principal
- `TODO.md` - TODO global
- `GUIDE_IMPORT_GAIA.md` - Guide import GAIA
- `Commande_artisan_cs.md` - Commandes custom artisan

---

## CONVENTIONS DE CODE

### Nommage

**Classes:** PascalCase
```php
class SystemeStellaire extends Model
class TimonerieController extends Controller
```

**Méthodes:** camelCase
```php
public function effectuerSaut()
public function deplacerConventionnel()
```

**Variables:** camelCase
```php
$vaisseauActif
$systemeDestination
```

**Tables SQL:** snake_case avec préfixe `CS_`
```sql
CS_systemes_stellaires
CS_vaisseaux
CS_objets_spatiaux
```

**Routes:** kebab-case
```php
Route::get('/navire/timonerie', ...);
Route::get('/personnage/dossier', ...);
```

**Vues Blade:** kebab-case
```
game-header.blade.php
personnage/dossier.blade.php
```

### Patterns Laravel

**Controllers:** Logique métier minimale
```php
public function timonerie()
{
    $personnage = Auth::user()->personnage;
    $vaisseau = $personnage->vaisseauActif;
    $systeme = $vaisseau->objetSpatial->systeme;

    return view('navire.timonerie', compact(
        'personnage', 'vaisseau', 'systeme'
    ));
}
```

**Models:** Business logic
```php
class Vaisseau extends ObjetSpatial
{
    public function calculerConsommationSaut(float $distance): int
    {
        // Logique métier dans le model
        return $this->initHE + ($distance * $this->coefHE);
    }
}
```

**Views:** Présentation uniquement
```blade
<div class="header-column">
    <div>{{ $personnage->nom }}</div>
    <div>{{ number_format($personnage->credits, 0, ',', ' ') }} CR</div>
</div>
```

---

## GLOSSAIRE RAPIDE

**AL** - Année-Lumière
**UA** - Unité Astronomique (distance Terre-Soleil)
**PA** - Points d'Action
**PJ** - Personnage Joueur
**HE** - Hyper-Espace
**CR** - Crédits (monnaie)
**OS** - Objet Spatial (ObjetSpatial)
**POI** - Point Of Interest (planète, station, etc.)
**Unitek** - Pièces détachées pour imprimante 3D

**Secteur** - Coordonnées AL entières (zone)
**Position** - Coordonnées décimales (précision dans secteur)
**Carte** - Interface 3D visualisation univers
**Timonerie** - Interface navigation vaisseau
**Amarrage** - Connexion à une station

---

## CHECKLIST DÉVELOPPEMENT

Avant de commiter du code :

**Coordonnées:**
- [ ] Je n'ai PAS multiplié `secteur_x/y/z` par 10
- [ ] Je n'ai PAS divisé les coordonnées par 10
- [ ] J'utilise `secteur_x/y/z` directement pour affichage carte
- [ ] J'utilise `position_x/y/z` pour navigation précise
- [ ] Mes calculs de distance utilisent secteur + position

**Code Laravel:**
- [ ] Controllers légers (logique dans Models)
- [ ] Validation Request objects pour forms complexes
- [ ] Relations Eloquent définies
- [ ] Nommage cohérent (conventions ci-dessus)
- [ ] Pas de logique dans les vues Blade

**Interface:**
- [ ] Header 4 colonnes respecté
- [ ] Tooltips pour infos détaillées
- [ ] Responsive (mobile-friendly)
- [ ] Icônes cohérentes (émojis ou Font Awesome)

**Tests:**
- [ ] Testé avec système Vega Aurigae (0,0,4)
- [ ] Testé avec vaisseau à position décimale
- [ ] Testé changement de secteur

---

## CONTACTS ET RESSOURCES

**Projet:** Conquête Spatiale
**Framework:** Laravel 11
**Documentation:** `docs/` et `docs/game-design/`

**Inspirations:**
- Lunastar: https://v2.lunastars.net
- GAIA DR3: https://gea.esac.esa.int/archive/

---

**Ce document est un index rapide. Consulter les documents détaillés dans `docs/game-design/` pour spécifications complètes.**

**Dernière mise à jour:** 2025-12-08
