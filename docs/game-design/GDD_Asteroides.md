# GDD - Système des Astéroïdes et Zones Spatiales

**Version:** 2.0
**Date:** 31 décembre 2025
**Statut:** SPÉCIFICATION TECHNIQUE COMPLÈTE
**Auteur:** Claude Code

---

## 📋 Table des Matières

1. [Vue d'Ensemble](#vue-densemble)
2. [Problématique et Contexte](#problématique-et-contexte)
3. [Approches Étudiées](#approches-étudiées)
4. [Solution Retenue : Approche Hybride](#solution-retenue--approche-hybride)
5. [Architecture Technique](#architecture-technique)
6. [Décisions de Conception](#décisions-de-conception)
7. [Système de Détection](#système-de-détection)
8. [Intégration Gameplay](#intégration-gameplay)
9. [Visualisation 2D/3D](#visualisation-2d3d)
10. [Bilan et Recommandations](#bilan-et-recommandations)

---

## 1. Vue d'Ensemble

### 1.1 Définition

Dans l'espace connu, les champs d'astéroïdes sont des zones étendues contenant de nombreux objets rocheux en orbite autour d'une étoile. Ils peuvent être :

- **Continus** : Ceinture complète faisant le tour du système (ex: Ceinture principale du Soleil)
- **Segmentés** : Arc de cercle partiel (ex: Anneaux de Saturne, zone de débris localisée)

### 1.2 Objectifs du Système

1. **Performance** : Gérer des zones contenant virtuellement des milliers d'astéroïdes sans surcharge BD
2. **Réalisme** : Représenter fidèlement les ceintures d'astéroïdes réelles
3. **Gameplay** : Offrir de l'exploration, du minage, des risques de navigation
4. **Scalabilité** : Permettre de créer facilement de nouvelles zones dans n'importe quel système

### 1.3 Types de Zones Supportées

| Type | Description | Exemple réel |
|------|-------------|--------------|
| `ceinture_asteroides` | Anneau dense entre 2 planètes | Ceinture principale (2.2-3.2 UA) |
| `nuage_debris` | Zone diffuse de débris | Nuage d'Oort |
| `nebuleuse` | Gaz et poussières | Nébuleuse sombre |

---

## 2. Problématique et Contexte

### 2.1 Contraintes Techniques

**Problème #1 : Nombre d'objets**
```
Ceinture d'astéroïdes réaliste :
- Millions d'astéroïdes (réalité physique)
- Impossible de stocker en BD individuellement

Solution classique :
- 100 astéroïdes × N systèmes = 1000+ entrées
- Performance queries dégradée
- Mémoire excessive
```

**Problème #2 : Détection et Scan**
```
Pour chaque scan :
  foreach (astéroïdes dans portée) {
    calculer distance
    comparer au seuil
  }

Si 1000 astéroïdes → 1000 calculs par scan
→ Temps de réponse > 500ms inacceptable
```

**Problème #3 : Visualisation**
```
Canvas 2D : Dessiner 1000 points
SVG : Créer 1000 éléments <circle>
3D : Générer 1000 meshes

→ Rendu lent, interface saccadée
```

### 2.2 Besoins Gameplay

1. **Exploration** : Découvrir progressivement les zones et objets notables
2. **Navigation** : Traverser une zone = ralentissement + risque collision
3. **Minage** : Exploiter des astéroïdes riches en minerais
4. **Combat** : Se cacher dans les champs d'astéroïdes
5. **Événements** : Rencontres, découvertes, dangers

---

## 3. Approches Étudiées

### 3.1 Approche A : Objets Individuels

**Principe** : Créer chaque astéroïde comme un `ObjetSpatial` distinct avec orbite.

```
Avantages :
✅ Réutilise code existant (orbites, détection)
✅ Précision maximale (chaque objet unique)
✅ Interactions riches (minage, amarrage)

Inconvénients :
❌ 100+ entrées BD par ceinture
❌ Performance dégradée (boucles multiples)
❌ Complexité visuelle (100 labels)
❌ Pas de notion de "zone" globale
```

**Verdict** : ❌ **Rejeté** - Scalabilité insuffisante

---

### 3.2 Approche B : Zone Unique Abstraite

**Principe** : Une seule entrée `ZoneSpatiale` représentant toute la ceinture.

```
Structure :
- rayon_min / rayon_max (anneau toroïdal)
- azimut_debut / azimut_fin (arc de cercle)
- densite (nombre virtuel)

Avantages :
✅ 1 seule entrée BD
✅ Performance excellente
✅ Visualisation claire (anneau)
✅ Réaliste (vraies ceintures)

Inconvénients :
❌ Pas d'objets individuels
❌ Impossible de cibler un astéroïde précis
❌ Pas d'interaction fine (minage)
```

**Verdict** : 🟡 **Intéressant** - Mais manque de profondeur gameplay

---

### 3.3 Approche C : HYBRIDE (Retenue) ✅

**Principe** : Zone globale + Objets notables + Génération procédurale

```
Niveaux :
1. Zone toroïdale (1 entrée BD)
   └─ Représente la ceinture entière

2. Astéroïdes notables (5-10 entrées BD)
   └─ Objets uniques nommés, minables

3. Génération procédurale (0 entrée BD)
   └─ Astéroïdes temporaires autour du vaisseau
```

**Avantages combinés** :
- ✅ Performance (1 zone + 10 notables = 11 objets max)
- ✅ Gameplay riche (exploration + minage + navigation)
- ✅ Visualisation claire (anneau + points d'intérêt)
- ✅ Scalable (génération à la demande)
- ✅ Réaliste (ceinture + gros astéroïdes nommés)

**Verdict** : ✅ **RETENU** - Meilleur compromis performance/gameplay

---

## 4. Solution Retenue : Approche Hybride

### 4.1 Architecture à 3 Niveaux

```
┌─────────────────────────────────────────────┐
│ NIVEAU 1 : ZONE SPATIALE                   │
│ • Table: zones_spatiales                   │
│ • 1 entrée par ceinture                    │
│ • Géométrie: rayon_min/max + azimut        │
│ • Détection facile (score bas)            │
└─────────────────────────────────────────────┘
                    │
                    │ parent_type/parent_id
                    ▼
┌─────────────────────────────────────────────┐
│ NIVEAU 2 : ASTÉROÏDES NOTABLES             │
│ • Table: objets_spatiaux                   │
│ • 5-10 entrées par ceinture                │
│ • Noms: Cérès, Vesta, Pallas...            │
│ • Orbites réelles (via planetes)           │
│ • Détection difficile (après zone)         │
└─────────────────────────────────────────────┘
                    │
                    │ Génération à la demande
                    ▼
┌─────────────────────────────────────────────┐
│ NIVEAU 3 : ASTÉROÏDES PROCÉDURAUX         │
│ • Pas stockés en BD                        │
│ • Générés autour du vaisseau               │
│ • Seed déterministe (même position = même) │
│ • Rayon: 0.5 UA autour vaisseau            │
│ • Affichage visuel uniquement              │
└─────────────────────────────────────────────┘
```

### 4.2 Exemple : Ceinture Principale du Soleil

```
1. Zone Globale
   Nom: "Ceinture Principale"
   Type: ceinture_asteroides
   Rayon: 2.2 - 3.2 UA (220 - 320 cUA)
   Azimut: 0° - 360° (cercle complet)
   Densité: 50
   Detectabilité: 30 (facile à détecter)

2. Notables (5 astéroïdes)
   - Cérès (945 km, 2.77 UA, masse 50000)
   - Vesta (525 km, 2.36 UA, masse 25000)
   - Pallas (512 km, 2.77 UA, masse 20000)
   - Hygiea (434 km, 3.14 UA, masse 15000)
   - Interamnia (326 km, 3.06 UA, masse 10000)

3. Procéduraux
   Générés quand vaisseau < 1 UA d'un notable
   20 astéroïdes aléatoires dans rayon 0.5 UA
   Seed = zone.id × 1000 + position
```

---

## 5. Architecture Technique

### 5.1 Structure de Données

#### Table : `zones_spatiales`

```sql
CREATE TABLE zones_spatiales (
  id BIGINT PRIMARY KEY,
  type VARCHAR(50),                    -- 'ceinture_asteroides', etc.
  nom VARCHAR(255) NULL,               -- "Ceinture Principale"
  systeme_stellaire_id BIGINT FK,

  -- Géométrie (anneau toroïdal)
  rayon_min INT,                       -- cUA (ex: 220)
  rayon_max INT,                       -- cUA (ex: 320)
  azimut_debut DECIMAL(10,8),          -- radians (ex: 0)
  azimut_fin DECIMAL(10,8),            -- radians (ex: 6.28318531 = 2π)

  -- Gameplay
  densite INT DEFAULT 50,
  vitesse_traversee_modif DECIMAL(5,2) DEFAULT 0.5,  -- -50% vitesse
  risque_collision DECIMAL(5,2) DEFAULT 0.01,        -- 1% par UA

  -- Détection
  detectabilite_base DECIMAL(10,2) DEFAULT 30,
  poi_connu BOOLEAN DEFAULT FALSE,

  -- Hiérarchie (optionnel)
  parent_type VARCHAR(255) NULL,
  parent_id BIGINT NULL,

  -- Métadonnées
  description TEXT NULL,
  proprietes JSON NULL,
  timestamps
);
```

#### Modification : `objets_spatiaux` (parent polymorphique)

```sql
ALTER TABLE objets_spatiaux ADD COLUMN (
  parent_type VARCHAR(255) NULL,     -- 'App\Models\ZoneSpatiale'
  parent_id BIGINT NULL,             -- ID de la zone
  INDEX idx_objets_parent (parent_type, parent_id)
);
```

### 5.2 Modèles Eloquent

#### ZoneSpatiale

```php
Relations :
- systeme() : BelongsTo SystemeStellaire
- objetNotables() : MorphMany ObjetSpatial
- asteroideNotables() : filtre sur type 'asteroide'
- parent() : MorphTo (zone imbriquée)
- sousZones() : MorphMany ZoneSpatiale

Méthodes géométriques :
- contientPosition(x, y) : bool
- distanceABordure(x, y) : int
- getScoreDetection(x, y) : float

Méthodes gameplay :
- calculerRisqueCollision(distance_ua) : float
- getModificateurVitesse() : float
- genererAsteroidesProceduraux(x, y, rayon, nb) : array

Helpers affichage :
- getNomComplet() : string
- getIcone() : string
- getRayonsUA() : array
- getAzimutsDegres() : array
- estCercleComplet() : bool
```

#### ObjetSpatial (modifié)

```php
Nouveaux champs :
- parent_type : string|null
- parent_id : int|null

Nouvelles relations :
- parent() : MorphTo
- enfants() : MorphMany ObjetSpatial

Nouvelles méthodes :
- estDansZone() : bool
- getZoneParente() : ?ZoneSpatiale
- aUnParent() : bool

Modification getScoreDetection() :
  Si parent = ZoneSpatiale non découverte
    → score = 999999 (invisible)
```

### 5.3 Hiérarchie Complète

```
SystemeStellaire
├── ZoneSpatiale (Ceinture Principale)
│   ├── ObjetSpatial (Cérès)
│   │   └── parent_type = 'App\Models\ZoneSpatiale'
│   │   └── parent_id = 1
│   ├── ObjetSpatial (Vesta)
│   └── ObjetSpatial (Pallas)
│
├── Planete (Mars)
│   └── Station (Phobos Station)
│       └── planete_id = mars.id
│
└── Planete (Jupiter)
    └── Mine (Europa Mine)
        └── planete_id = jupiter.id
```

**Cohérence des relations parent** :

| Modèle | Stockage parent | Méthode |
|--------|-----------------|---------|
| **Planete** | `systeme_stellaire_id` | FK classique |
| **Station** | `planete_id` ou `systeme_stellaire_id` | FK classique |
| **Mine** | `planete_id` ou `base_id` | FK classique |
| **Vaisseau** | `objet_spatial_id` → parent polymorphique | Héritage via ObjetSpatial |
| **Base** | `objet_spatial_id` → parent polymorphique | Héritage via ObjetSpatial |
| **Astéroïde** | `parent_type/parent_id` | Polymorphique vers ZoneSpatiale |

**Note** : Seuls les modèles liés à `objets_spatiaux` utilisent la relation polymorphique parent. Les autres (Planete, Station, Mine) ont déjà leurs FK dédiées.

---

## 6. Décisions de Conception

### 6.1 Pourquoi Radians en BD ?

**Question** : Stocker angles en degrés ou radians ?

**Décision** : ✅ **RADIANS en BD, degrés pour affichage**

**Justification** :

```
1. Calculs mathématiques (PHP/JS)
   cos(angle), sin(angle) → attendent radians
   Conversion deg→rad à chaque calcul = coût CPU

2. Cohérence avec code existant
   Planete.angle_orbital_initial → déjà en radians
   OrbitalCalculator.js → travaille en radians

3. Précision des calculs
   π radians = valeur exacte
   180° = conversion arbitraire

4. Performance
   Évite deg2rad() à chaque frame d'animation
```

**Implémentation** :

```php
// Stockage BD
'azimut_debut' => 'decimal:10,8'  // Ex: 3.14159265 rad

// Affichage Blade
{{ number_format($zone->azimut_debut * 180 / pi(), 2) }}°
// Affiche: 180.00°

// Input utilisateur
$zone->azimut_debut = $request->azimut_deg * M_PI / 180;
```

---

### 6.2 Pourquoi Relation Polymorphique Parent ?

**Question** : FK classique vs relation polymorphique ?

**Décision** : ✅ **Polymorphique (`parent_type` / `parent_id`)**

**Justification** :

```
Flexibilité :
  Astéroïde → parent = ZoneSpatiale
  Station orbitale → parent = Planete (via ObjetSpatial)
  Débris → parent = Station détruite
  Vaisseau amarré → parent = Base

Impossible avec FK classique :
  FK ne peut pointer que vers 1 table

Alternative FK multiple :
  zone_spatiale_id, planete_id, station_id, base_id
  → 4 colonnes NULL, logique complexe

Polymorphique :
  2 colonnes (type + id)
  → Pointe vers N'IMPORTE QUELLE table
```

**Trade-offs** :

```
✅ Avantages :
  - Ultra flexible
  - Scalable (nouveaux types parents = 0 migration)
  - Code propre (1 seule relation parent())

❌ Inconvénients :
  - Pas de FK constraint en BD (vérif app)
  - Requêtes un peu plus complexes
  - Index composite nécessaire
```

**Notre cas** : ✅ Avantages > Inconvénients

---

### 6.3 Pourquoi Approche Hybride ?

**Question** : Zone pure vs Objets individuels vs Hybride ?

**Décision** : ✅ **HYBRIDE (Zone + Notables + Procéduraux)**

**Analyse comparative** :

| Critère | Zone Seule | Objets Individuels | **HYBRIDE** |
|---------|------------|-------------------|-------------|
| **Performance BD** | 🟢 1 entrée | 🔴 100+ entrées | 🟢 10 entrées |
| **Performance Scan** | 🟢 1 calcul | 🔴 100 calculs | 🟢 10 calculs |
| **Réalisme** | 🟢 Ceinture | 🟡 Nuage | 🟢 Ceinture + notables |
| **Gameplay** | 🔴 Basique | 🟢 Riche | 🟢 Très riche |
| **Minage** | ❌ Impossible | 🟢 Précis | 🟢 Notables minables |
| **Visualisation** | 🟢 Anneau | 🔴 Chaotique | 🟢 Anneau + POI |
| **Scalabilité** | 🟢 Infinie | 🔴 Limitée | 🟢 Infinie |
| **Complexité code** | 🟢 Simple | 🔴 Élevée | 🟡 Moyenne |

**Scénarios couverts** :

```
✅ Vue de loin (10 UA+)
   → Affiche zone comme anneau

✅ Vue rapprochée (1-10 UA)
   → Affiche zone + notables détectés

✅ Dans la zone (< 1 UA notable)
   → Affiche notables + procéduraux autour

✅ Minage
   → Cible un notable (nom, caractéristiques)

✅ Navigation
   → Zone modifie vitesse + risque collision
```

---

### 6.4 Pourquoi Génération Procédurale ?

**Question** : Tout stocker vs Générer à la demande ?

**Décision** : ✅ **Génération procédurale avec seed déterministe**

**Justification** :

```
Problème :
  Ceinture réaliste = milliers d'astéroïdes
  Impossible de tous stocker en BD

Solution procédurale :
  Générer astéroïdes autour vaisseau (rayon 0.5 UA)
  Seed = hash(zone_id + position_vaisseau)
  → Même position = mêmes astéroïdes

Avantages :
  ✅ 0 entrée BD
  ✅ Densité visuelle illimitée
  ✅ Déterministe (reproductible)
  ✅ Léger (calcul < 10ms)

Inconvénients :
  ❌ Pas persistés (pas d'interaction)
  ❌ Seulement pour affichage

Mitigation :
  Si joueur veut miner un procédural
  → Le promouvoir en "notable" (insertion BD)
```

**Implémentation** :

```php
// ZoneSpatiale.php
public function genererAsteroidesProceduraux(
    int $posX,
    int $posY,
    int $rayonRecherche = 5000,  // 50 UA
    int $nombreMax = 20
): array {
    // Seed déterministe
    $seed = $this->id * 1000 + (int)($posX / 100) * 10 + (int)($posY / 100);
    mt_srand($seed);

    $asteroides = [];
    for ($i = 0; $i < $nombreMax; $i++) {
        // Position aléatoire autour du point central
        $angle = mt_rand() / mt_getrandmax() * 2 * M_PI;
        $distance = mt_rand() / mt_getrandmax() * $rayonRecherche;

        $asteroides[] = [
            'nom' => "Astéroïde #" . ($this->id * 1000 + $i),
            'position_x' => $posX + (int)($distance * cos($angle)),
            'position_y' => $posY + (int)($distance * sin($angle)),
            'masse' => mt_rand(100, 5000),
            'procedural' => true, // Flag
        ];
    }

    mt_srand(); // Reset seed
    return $asteroides;
}
```

---

## 7. Système de Détection

### 7.1 Formules de Détection

#### Zone Spatiale (Niveau 1)

```
Score = (distance_bordure_cUA / 1000) × detectabilite_base

Où :
- distance_bordure = min(
    |distance_vaisseau - rayon_min|,
    |distance_vaisseau - rayon_max|
  )
- detectabilite_base ≈ 30 (zones plus faciles que objets)

Exemple :
  Vaisseau à (0, 0)
  Zone : rayon 220-320 cUA
  Distance à bordure = 220 cUA
  Score = (220 / 1000) × 30 = 6.6

  → Détecté après ~7 scans (cumul moyen 10-15)
```

#### Astéroïde Notable (Niveau 2)

```
Score = (distance_cUA / 1000) × detectabilite_base

RÈGLE PARENT :
  Si parent.type = ZoneSpatiale && !parent.poi_connu
    → score = 999999 (invisible)

Où :
- distance_cUA = distance 3D directe
- detectabilite_base ≈ 60 (objets plus difficiles)

Exemple :
  Cérès à (245, 0, 0)
  Vaisseau à (0, 0, 0)
  Distance = 245 cUA = 2.45 UA

  Si zone PAS découverte :
    → score = 999999 (invisible)

  Si zone découverte :
    → score = (245 / 1000) × 60 = 14.7
    → Détecté après ~15 scans
```

### 7.2 Progression de Détection

**Scénario** : Joueur scanne depuis position (0, 0, 0)

```
SCAN 1 : cumul = 15
├─ Zone Ceinture : score = 6.6 ✅ DÉTECTÉE
│  └─ Affichage : Anneau sur carte
│  └─ Message : "Zone détectée : Ceinture Principale (2.2-3.2 UA)"
│  └─ Info : "Continuez à scanner pour découvrir les astéroïdes notables"
└─ Cérès : score = 999999 ❌ Invisible (parent non découvert)

SCAN 2 : cumul = 28
├─ Zone Ceinture : déjà découverte (poi_connu = true)
└─ Cérès : score = 14.7 ✅ DÉTECTÉ
   └─ Affichage : Point dans anneau
   └─ Message : "Astéroïde découvert : Cérès (masse 50000, 2.77 UA)"
   └─ Bouton : [Approcher] [Miner]

SCAN 3 : cumul = 43
└─ Vesta : score = 35.4 ✅ DÉTECTÉ
   └─ Affichage : Nouveau point dans anneau
```

### 7.3 Affichage Partiel Intelligent

**Timonerie - Liste POI** :

```
État 1 (avant scan) :
┌─ Déplacements Conventionnels ─┐
│ Aucun POI détecté             │
└───────────────────────────────┘

État 2 (zone détectée, notables cachés) :
┌─ Déplacements Conventionnels ─────────┐
│ 🌌 Ceinture Principale 2.2-3.2 UA ▼  │
│    ⚠️ Scannez pour découvrir les     │
│       astéroïdes notables (5 cachés) │
└───────────────────────────────────────┘

État 3 (1 notable détecté) :
┌─ Déplacements Conventionnels ─────────┐
│ 🌌 Ceinture Principale 2.2-3.2 UA ▼  │
│    ├─ 🪨 Cérès 💎 ──── 2.77 UA      │
│    │   [➡️ Approcher] [⛏️ Miner]     │
│    └─ ⚠️ 4 astéroïdes cachés         │
└───────────────────────────────────────┘

État 4 (tous notables détectés) :
┌─ Déplacements Conventionnels ─────────┐
│ 🌌 Ceinture Principale 2.2-3.2 UA ▲  │
│    ├─ 🪨 Cérès 💎 ──── 2.77 UA      │
│    ├─ 🪨 Vesta ────── 2.36 UA       │
│    ├─ 🪨 Pallas ───── 2.77 UA       │
│    ├─ 🪨 Hygiea ───── 3.14 UA       │
│    └─ 🪨 Interamnia ── 3.06 UA       │
└───────────────────────────────────────┘
```

---

## 8. Intégration Gameplay

### 8.1 Navigation dans une Zone

**Effet** : Traverser une ceinture d'astéroïdes modifie la navigation

```php
// Dans TimonerieController

// 1. Vérifier si vaisseau dans zone
$zone = ZoneSpatiale::where('systeme_stellaire_id', $systeme->id)
    ->get()
    ->first(fn($z) => $z->contientPosition($vaisseau->position_x, $vaisseau->position_y));

if ($zone) {
    // 2. Appliquer modificateur vitesse
    $vitesseNormale = $vaisseau->vitesse_max;
    $vitesseZone = $vitesseNormale * $zone->getModificateurVitesse();
    // Ex: 100 UA/h × 0.5 = 50 UA/h

    // 3. Calculer risque collision
    $distanceParcourue = calculerDistance($depart, $arrivee);
    $risque = $zone->calculerRisqueCollision($distanceParcourue / 100); // cUA → UA

    if (mt_rand() / mt_getrandmax() < $risque) {
        // Collision !
        $dommages = mt_rand(10, 50);
        $vaisseau->subirDommages($dommages);

        return response()->json([
            'success' => false,
            'message' => "Collision avec astéroïde ! Dommages : {$dommages}",
        ]);
    }
}
```

**Affichage Timonerie** :

```blade
@if($zone && $vaisseauDansZone)
<div class="bg-amber-900/30 border border-amber-500/50 rounded px-2 py-1 mb-2 text-xs text-amber-200">
    ⚠️ Vous êtes dans {{ $zone->getNomComplet() }}
    <br>
    Vitesse réduite de {{ (1 - $zone->vitesse_traversee_modif) * 100 }}%
    <br>
    Risque collision : {{ $zone->risque_collision * 100 }}% par UA
</div>
@endif
```

### 8.2 Minage d'Astéroïdes

**Workflow** :

```
1. Détecter astéroïde notable
2. S'approcher (distance < 0.05 UA)
3. Lancer commande minage
4. Extraire minerais selon composition
5. Stocker dans cargo vaisseau
```

**Implémentation** :

```php
// MiningController
public function commencerMinage(int $asteroideId)
{
    $asteroide = ObjetSpatial::findOrFail($asteroideId);
    $vaisseau = auth()->user()->personnage->vaisseau;

    // 1. Vérifier distance
    $distance = $vaisseau->calculerDistance($asteroide);
    if ($distance > 500) { // 0.05 UA = 500 cUA
        return response()->json([
            'error' => 'Trop loin de l\'astéroïde (max 0.05 UA)',
        ], 400);
    }

    // 2. Vérifier équipement
    if (!$vaisseau->aEquipement('foreuse')) {
        return response()->json([
            'error' => 'Foreuse requise',
        ], 400);
    }

    // 3. Extraire minerais
    $gisements = $asteroide->proprietes['minerais'] ?? [
        'fer' => mt_rand(100, 1000),
        'nickel' => mt_rand(50, 500),
    ];

    $quantiteExtraite = min(
        $gisements['fer'],
        $vaisseau->cargo_disponible
    );

    $vaisseau->ajouterCargo('fer', $quantiteExtraite);

    return response()->json([
        'success' => true,
        'minerai' => 'fer',
        'quantite' => $quantiteExtraite,
        'cargo_restant' => $vaisseau->cargo_disponible,
    ]);
}
```

### 8.3 Événements dans les Zones

**Exemples d'événements** :

```
1. Découverte de gisement rare
   → Astéroïde riche en platine

2. Rencontre de pirates
   → Embuscade dans ceinture

3. Épave abandonnée
   → Vaisseau détruit à récupérer

4. Anomalie gravitationnelle
   → Astéroïde avec gravité anormale
```

**Implémentation** :

```php
// EventService
public function declencherEvenementZone(ZoneSpatiale $zone, Vaisseau $vaisseau)
{
    if (!$zone->contientPosition($vaisseau->position_x, $vaisseau->position_y)) {
        return null;
    }

    // Probabilité événement = 5% par heure dans zone
    $chanceEvent = 0.05 * $tempsPasse;

    if (mt_rand() / mt_getrandmax() < $chanceEvent) {
        $eventType = collect([
            'gisement_rare' => 0.3,
            'pirates' => 0.2,
            'epave' => 0.25,
            'anomalie' => 0.25,
        ])->random();

        return Event::create([
            'type' => $eventType,
            'zone_spatiale_id' => $zone->id,
            'vaisseau_id' => $vaisseau->id,
            'position_x' => $vaisseau->position_x,
            'position_y' => $vaisseau->position_y,
            'donnees' => generateEventData($eventType),
        ]);
    }
}
```

---

## 9. Visualisation 2D/3D

### 9.1 Canvas 2D (Vue Secteur)

**Affichage zone** :

```javascript
// carte-secteur.blade.php - Canvas 2D
function dessinerZoneSpatiale(zone) {
    const ctx = canvas.getContext('2d');
    const centerX = 300; // Centre canvas (étoile)
    const centerY = 300;

    // Échelle : 1 pixel = X cUA
    const echelle = 600 / (systeme.taille * 100); // Ex: 1px = 10 cUA

    // Convertir rayons en pixels
    const rayonMinPx = zone.rayon_min * echelle;
    const rayonMaxPx = zone.rayon_max * echelle;

    // Anneau externe
    ctx.beginPath();
    ctx.arc(centerX, centerY, rayonMaxPx, 0, 2 * Math.PI);
    ctx.strokeStyle = 'rgba(200, 150, 100, 0.5)';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Anneau interne
    ctx.beginPath();
    ctx.arc(centerX, centerY, rayonMinPx, 0, 2 * Math.PI);
    ctx.strokeStyle = 'rgba(200, 150, 100, 0.5)';
    ctx.stroke();

    // Remplissage semi-transparent
    ctx.fillStyle = 'rgba(200, 150, 100, 0.1)';
    ctx.fill();

    // Label zone
    ctx.fillStyle = '#CCC';
    ctx.font = '12px Arial';
    ctx.fillText(
        zone.nom,
        centerX + rayonMinPx + 10,
        centerY
    );
}

// Dessiner astéroïdes notables
function dessinerAsteroideNotable(asteroide) {
    const posX = centerX + (asteroide.position_x * echelle);
    const posY = centerY + (asteroide.position_y * echelle);

    // Point notable
    ctx.beginPath();
    ctx.arc(posX, posY, 3, 0, 2 * Math.PI);
    ctx.fillStyle = '#FFD700'; // Or
    ctx.fill();

    // Label
    ctx.fillStyle = '#FFF';
    ctx.font = '10px Arial';
    ctx.fillText(asteroide.nom, posX + 5, posY - 5);
}
```

### 9.2 SVG (Vue Détaillée)

**Affichage arc de zone** :

```blade
{{-- carte-secteur.blade.php - SVG --}}
@foreach($zones as $zone)
<svg viewBox="0 0 600 600">
    {{-- Arc de cercle zone --}}
    <defs>
        <radialGradient id="zone-gradient-{{ $zone->id }}">
            <stop offset="0%" stop-color="#886633" stop-opacity="0.1"/>
            <stop offset="100%" stop-color="#886633" stop-opacity="0.3"/>
        </radialGradient>
    </defs>

    {{-- Anneau --}}
    <path d="
        M {{ 300 + $zone->rayon_max }} 300
        A {{ $zone->rayon_max }} {{ $zone->rayon_max }} 0 1 0 {{ 300 - $zone->rayon_max }} 300
        A {{ $zone->rayon_max }} {{ $zone->rayon_max }} 0 1 0 {{ 300 + $zone->rayon_max }} 300
        M {{ 300 + $zone->rayon_min }} 300
        A {{ $zone->rayon_min }} {{ $zone->rayon_min }} 0 1 1 {{ 300 - $zone->rayon_min }} 300
        A {{ $zone->rayon_min }} {{ $zone->rayon_min }} 0 1 1 {{ 300 + $zone->rayon_min }} 300
        Z
    " fill="url(#zone-gradient-{{ $zone->id }})"
       stroke="#886633"
       stroke-width="1"
       stroke-dasharray="5,5"/>

    {{-- Label zone --}}
    <text x="300" y="{{ 300 - $zone->rayon_max - 10 }}"
          text-anchor="middle"
          fill="#CCC"
          font-size="14">
        {{ $zone->getNomComplet() }}
    </text>

    {{-- Astéroïdes notables --}}
    @foreach($zone->asteroideNotables as $ast)
        @if($ast->poi_connu)
        <circle cx="{{ $ast->cache_position_x }}"
                cy="{{ $ast->cache_position_y }}"
                r="4"
                fill="#FFD700"
                class="asteroide-notable"
                data-asteroide-id="{{ $ast->id }}">
            <title>{{ $ast->nom }} ({{ number_format($ast->masse) }} kg)</title>
        </circle>
        <text x="{{ $ast->cache_position_x + 6 }}"
              y="{{ $ast->cache_position_y - 6 }}"
              fill="#FFD700"
              font-size="10">
            {{ $ast->nom }}
        </text>
        @endif
    @endforeach
</svg>
@endforeach
```

### 9.3 Spacekit.js / Three.js (Vue 3D)

**Rendu zone avec particles** :

```javascript
// Spacekit.js
const viz = new Spacekit.Simulation(document.getElementById('canvas-3d'));

// Zone comme système de particules
viz.createObject('Ceinture Principale', {
  particleSize: 0.5,
  shape: {
    shapeType: 'RING',
    radius: (zone.rayon_min + zone.rayon_max) / 200, // Moyenne en UA
    thickness: (zone.rayon_max - zone.rayon_min) / 200, // Épaisseur en UA
  },
  particles: {
    count: zone.densite * 100, // Ex: 50 × 100 = 5000 particules
    distribution: 'ring',
    color: 0x886633,
    opacity: 0.3,
  }
});

// Astéroïdes notables comme sphères
zone.asteroideNotables.forEach(ast => {
  viz.createSphere(ast.nom, {
    radius: 0.0005, // ~500 km
    color: 0xCCCCCC,
    orbit: {
      semiMajorAxis: ast.distance_etoile / 100, // cUA → UA
      period: ast.periode_orbitale,
      inclination: 0,
    },
    labelText: ast.nom,
    labelColor: 0xFFD700,
  });
});

// Highlight zone si vaisseau dedans
function updateZoneHighlight(vaisseauPos) {
  const distance = Math.sqrt(vaisseauPos.x**2 + vaisseauPos.y**2);

  if (distance >= zone.rayon_min / 100 && distance <= zone.rayon_max / 100) {
    // Vaisseau DANS la zone → highlight
    particleSystem.material.color.setHex(0xFF8800);
    particleSystem.material.opacity = 0.5;
  } else {
    particleSystem.material.color.setHex(0x886633);
    particleSystem.material.opacity = 0.3;
  }
}
```

---

## 10. Bilan et Recommandations

### 10.1 Points Forts du Système

✅ **Performance optimale**
- 1 zone + 5-10 notables = ~10 objets BD par ceinture
- Queries rapides (< 10ms)
- Scalable à l'infini (génération procédurale)

✅ **Réalisme scientifique**
- Géométrie toroïdale = vraies ceintures
- Astéroïdes notables nommés (Cérès, Vesta...)
- Orbites réelles (réutilise mécanique planètes)

✅ **Gameplay riche**
- Exploration progressive (zone → notables)
- Navigation impactée (vitesse, risques)
- Minage ciblé (notables)
- Événements dynamiques

✅ **Visualisation claire**
- 2D : Anneau + points d'intérêt
- 3D : Particles + sphères notables
- Affichage partiel intelligent

✅ **Architecture flexible**
- Relation polymorphique (parent N types)
- Zones imbriquées possibles
- Extensible (nouveaux types zones)

### 10.2 Limitations et Mitigations

⚠️ **Limite 1 : Astéroïdes procéduraux non persistés**

```
Problème : Impossible d'interagir avec procéduraux
Mitigation : Promouvoir en notable si interaction
```

⚠️ **Limite 2 : Pas de FK constraint (polymorphique)**

```
Problème : Intégrité référentielle non garantie par BD
Mitigation : Validation Eloquent + tests automatisés
```

⚠️ **Limite 3 : Complexité requêtes (MorphTo)**

```
Problème : Requêtes polymorphiques plus lentes
Mitigation : Index composites + eager loading
```

### 10.3 Prochaines Étapes

**Phase 1 : Implémentation de base** (Prioritaire)
- [x] Migration zones_spatiales
- [x] Migration parent polymorphique
- [x] Modèle ZoneSpatiale
- [x] Modification ObjetSpatial
- [x] Contrôleur ZoneSpatiale
- [ ] Routes API zones
- [ ] Tests unitaires

**Phase 2 : Intégration Timonerie**
- [ ] Afficher zones dans liste POI
- [ ] Liste repliable notables
- [ ] Indicateur "dans zone"
- [ ] Modificateur vitesse
- [ ] Risque collision

**Phase 3 : Système de Scan**
- [ ] Détection zones (score bordure)
- [ ] Détection notables (après zone)
- [ ] Affichage partiel progressif
- [ ] Compteur notables cachés

**Phase 4 : Visualisation**
- [ ] Canvas 2D : anneau + notables
- [ ] SVG : arc + labels
- [ ] 3D : particles Spacekit.js
- [ ] Génération procédurale affichage

**Phase 5 : Gameplay**
- [ ] Minage astéroïdes
- [ ] Événements zones
- [ ] Missions liées zones
- [ ] Achats/ventes minerais

### 10.4 Recommandations Finales

**Pour l'implémentation** :

1. ✅ Commencer par créer 1-2 zones tests (Ceinture Principale, Kuiper)
2. ✅ Utiliser radians en BD (cohérence calculs)
3. ✅ Relation polymorphique (flexibilité)
4. ✅ Eager loading systématique (`with(['asteroideNotables'])`)
5. ✅ Index composites sur parent_type/parent_id

**Pour le gameplay** :

1. Zone = obstacle tactique (ralentissement, cache)
2. Notables = objectifs (minage, exploration)
3. Procéduraux = immersion visuelle
4. Événements = surprise, rejouabilité

**Pour la performance** :

1. Cache zones par système (1h TTL)
2. Génération procédurale lazy (à la demande)
3. Limiter notables à 10 max par zone
4. Index BD optimisés

---

## Annexes

### A. Exemples de Zones Réelles

| Nom | Type | Rayon (UA) | Azimut | Notables |
|-----|------|-----------|--------|----------|
| Ceinture Principale | Ceinture | 2.2 - 3.2 | 0° - 360° | Cérès, Vesta, Pallas |
| Ceinture de Kuiper | Ceinture | 30 - 55 | 0° - 360° | Pluton, Makemake |
| Anneaux de Saturne | Arc | 0.0014 - 0.0008 | 0° - 360° | Gaps (Cassini, Encke) |
| Nuage d'Oort | Nuage | 2000 - 50000 | 0° - 360° | Comètes lointaines |

### B. Glossaire

- **cUA** : centi-Unité Astronomique (1 UA = 100 cUA)
- **Anneau toroïdal** : Forme de donut (2 cercles concentriques)
- **Polymorphique** : Relation pointant vers différents types
- **Procédural** : Généré algorithmiquement (vs stocké)
- **Seed** : Graine aléatoire déterministe
- **POI** : Point Of Interest (point d'intérêt)
- **FK** : Foreign Key (clé étrangère)

### C. Références

**Code source** :
- `app/Models/ZoneSpatiale.php`
- `app/Models/ObjetSpatial.php`
- `app/Http/Controllers/ZoneSpatiale Controller.php`
- `database/migrations/2025_12_31_120000_create_zones_spatiales_table.php`
- `database/migrations/2025_12_31_120001_add_parent_to_objets_spatiaux.php`

**Documentation liée** :
- `GDD_VISUALISATION_3D.md` - Visualisation 3D (Spacekit.js)
- `GDD_SYSTEME_DETECTION_V2.md` - Système de scan et détection
- `SYSTEME_ORBITAL.md` - Mécanique orbitale
- `SYSTEME_COORDONNEES.md` - Conversions AL/UA/cUA

---

**Document maintenu par :** Claude Code
**Dernière mise à jour :** 2025-12-31
**Version :** 2.0 (Spécification technique complète)
