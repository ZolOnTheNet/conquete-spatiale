# Architecture - Objets Spatiaux

**Version:** 2.0 (Corrigée)
**Date:** 31 décembre 2025
**Statut:** SPÉCIFICATION TECHNIQUE

---

## 📋 Principe Fondamental

**RÈGLE D'OR** : Tout objet physique dans l'espace EST un ObjetSpatial ou HÉRITE d'un ObjetSpatial.

```
ObjetSpatial = Position 3D complète (secteur + locale)
Tables détails = Propriétés spécifiques (capacité, armement, etc.)
```

---

## 🏗️ Hiérarchie Complète

### Schéma Général

```
┌─────────────────────────────────────┐
│ ObjetSpatial (table unique)         │
│ ─────────────────────────────────   │
│ • id                                │
│ • type: 'vaisseau', 'station', etc. │
│ • secteur_x/y/z (AL)                │
│ • position_x/y/z (cUA)              │
│ • parent_type/parent_id             │
│ • detectabilite_base                │
│ • poi_connu                         │
│ • masse, volume, resistance         │
└─────────────────────────────────────┘
           │
           │ FK objet_spatial_id
           ▼
┌──────────────────────────────────────────────┐
│ Tables Détails                               │
├──────────────────────────────────────────────┤
│ • Vaisseau    → Armement, cargo, moteur      │
│ • Station     → Capacité, services           │
│ • Mine        → Extraction, gisement         │
│ • Base        → Défenses, production         │
└──────────────────────────────────────────────┘
```

### Types d'Objets Spatiaux

| Type | Table Détails | FK | Description |
|------|--------------|-----|-------------|
| `vaisseau` | `vaisseaux` | ✅ `objet_spatial_id` | Vaisseau joueur/NPC |
| `station` | `stations` | ✅ `objet_spatial_id` | Station spatiale (orbitale/fixe) |
| `mine` | `mines` | ✅ `objet_spatial_id` | Mine MAME ou évoluée |
| `base` | `bases` | ✅ `objet_spatial_id` | Base militaire/commerciale |
| `asteroide` | - | - | Astéroïde (pas de détails) |
| `asteroide_notable` | - | - | Astéroïde nommé (Cérès, Vesta) |
| `debris` | - | - | Débris spatial |
| `epave` | - | - | Vaisseau détruit |
| `balise` | - | - | Balise de navigation |

---

## 🔗 Relations "parent" (Contenu Dans)

### Sémantique

**parent** = "Contenu dans" (logique ET physique)

Un objet avec parent hérite la position de son parent (ou la calcule en fonction).

### Cas d'Usage

#### 1. Astéroïde dans Zone

```php
ObjetSpatial {
    type: 'asteroide_notable',
    nom: 'Cérès',
    parent_type: 'App\Models\ZoneSpatiale',
    parent_id: 1, // Ceinture Principale

    // Position propre (orbite)
    secteur_x: 0,
    secteur_y: 0,
    secteur_z: 0,
    position_x: 277, // 2.77 UA en cUA
    position_y: 0,
    position_z: 0,
}

// Sémantique : "Cérès est DANS la Ceinture Principale"
// Position : Propre (orbite indépendante)
```

#### 2. Station en Orbite Planète

```php
ObjetSpatial {
    type: 'station',
    nom: 'Station Europa',
    parent_type: 'App\Models\Planete',
    parent_id: 5, // Jupiter

    // Position calculée depuis planète + offset orbital
    secteur_x: 0,
    secteur_y: 0,
    secteur_z: 0,
    position_x: 520 + offset_x, // Position Jupiter + offset
    position_y: 0 + offset_y,
    position_z: 0,
}

Station {
    objet_spatial_id: 42,
    orbite_rayon_ua: 0.05, // 5000 km
    orbite_angle: 1.57, // 90° en radians
}

// Sémantique : "Station est DANS le système de Jupiter (orbite autour)"
// Position : Calculée (planète + offset orbital)
```

#### 3. Mine MAME sur Astéroïde

```php
ObjetSpatial {
    type: 'mine',
    nom: 'Mine Vesta #3',
    parent_type: 'App\Models\ObjetSpatial', // Astéroïde Vesta
    parent_id: 150,

    // Position = même que astéroïde (posée dessus)
    secteur_x: 0,
    secteur_y: 0,
    secteur_z: 0,
    position_x: 236, // Même que Vesta
    position_y: 0,
    position_z: 0,
}

Mine {
    objet_spatial_id: 78,
    type_mine: 'mame',
    gisement_id: 12,
}

// Sémantique : "Mine est SUR l'astéroïde Vesta"
// Position : Héritée (même que parent)
```

#### 4. Mine Évoluée attachée à Station

```php
ObjetSpatial {
    type: 'mine',
    nom: 'Mine Europa Station',
    parent_type: 'App\Models\Station',
    parent_id: 10,

    // Position = même que station
    secteur_x: 0,
    secteur_y: 0,
    secteur_z: 0,
    position_x: 520,
    position_y: 50,
    position_z: 0,
}

Mine {
    objet_spatial_id: 92,
    type_mine: 'evoluee',
    station_id: 10, // Même que parent (redondance)
}

// Sémantique : "Mine est ATTACHÉE à la station"
// Position : Héritée (même que station)
```

#### 5. Vaisseau amarré à Station

```php
ObjetSpatial {
    type: 'vaisseau',
    nom: 'Falcon Heavy',
    parent_type: 'App\Models\Station',
    parent_id: 10,

    // Position = même que station (amarré)
    secteur_x: 0,
    secteur_y: 0,
    secteur_z: 0,
    position_x: 520,
    position_y: 50,
    position_z: 0,
}

Vaisseau {
    objet_spatial_id: 33,
    // État amarrage
}

// Sémantique : "Vaisseau est AMARRÉ à la station"
// Position : Héritée (même que station)
```

#### 6. Cargo dans Vaisseau

```php
ObjetSpatial {
    type: 'cargo',
    nom: 'Container Minerai',
    parent_type: 'App\Models\ObjetSpatial', // Vaisseau
    parent_id: 33,

    // Position = même que vaisseau (dans soute)
    secteur_x: 0,
    secteur_y: 0,
    secteur_z: 0,
    position_x: 520,
    position_y: 50,
    position_z: 0,
}

// Sémantique : "Cargo est DANS le vaisseau"
// Position : Héritée (même que vaisseau)
```

---

## 📐 Système de Positions

### Double Système de Coordonnées

**Tout objet a 6 coordonnées** :

```php
ObjetSpatial {
    // Position sectoriale (grossière, en AL)
    secteur_x: int,  // -∞ à +∞
    secteur_y: int,
    secteur_z: int,

    // Position locale (précise, en cUA dans secteur)
    position_x: int, // 0 à 6,324,100 (0 à 10 AL)
    position_y: int,
    position_z: int,
}
```

### Conversion

```php
// CoordinatesHelper
1 UA = 100 cUA (centi-Unités Astronomiques)
1 AL = 63,241 UA = 6,324,100 cUA

// Position absolue en cUA
$abs_x = ($secteur_x × 6,324,100) + $position_x
$abs_y = ($secteur_y × 6,324,100) + $position_y
$abs_z = ($secteur_z × 6,324,100) + $position_z
```

### Calcul Position Effective

**Si objet a parent, position peut être héritée/calculée** :

```php
// ObjetSpatial.php
public function getPositionEffective(?float $timestampJours = null): array
{
    // Si objet en orbite (vaisseau), calculer position orbitale
    if ($this->vaisseau && $this->vaisseau->orbite_planete_id) {
        return $this->vaisseau->getPositionOrbitale($timestampJours);
    }

    // Si parent existe, vérifier héritage position
    if ($this->parent_type && $this->parent_id) {
        $parent = $this->parent;

        // Planète → calculer position orbitale
        if ($parent instanceof Planete) {
            $posPlanete = $parent->getPositionOrbitale($timestampJours);

            // Si station en orbite planète, ajouter offset
            if ($this->type === 'station' && $this->station) {
                $rayonCua = $this->station->orbite_rayon_ua * 100;
                $angle = $this->station->orbite_angle;

                return [
                    'secteur_x' => $parent->systemeStellaire->secteur_x ?? 0,
                    'secteur_y' => $parent->systemeStellaire->secteur_y ?? 0,
                    'secteur_z' => $parent->systemeStellaire->secteur_z ?? 0,
                    'position_x' => $posPlanete['x'] + ($rayonCua * cos($angle)),
                    'position_y' => $posPlanete['y'] + ($rayonCua * sin($angle)),
                    'position_z' => $posPlanete['z'],
                ];
            }

            // Sinon, position planète directe (mine sur planète)
            return [
                'secteur_x' => $parent->systemeStellaire->secteur_x ?? 0,
                'secteur_y' => $parent->systemeStellaire->secteur_y ?? 0,
                'secteur_z' => $parent->systemeStellaire->secteur_z ?? 0,
                'position_x' => $posPlanete['x'],
                'position_y' => $posPlanete['y'],
                'position_z' => $posPlanete['z'],
            ];
        }

        // ZoneSpatiale → position propre (astéroïde dans zone)
        if ($parent instanceof ZoneSpatiale) {
            return $this->getPosition();
        }

        // ObjetSpatial → hériter position parent (récursif)
        if ($parent instanceof ObjetSpatial) {
            return $parent->getPositionEffective($timestampJours);
        }

        // Station → via objet_spatial_id
        if ($parent instanceof Station && $parent->objetSpatial) {
            return $parent->objetSpatial->getPositionEffective($timestampJours);
        }
    }

    // Sinon, position propre stockée
    return [
        'secteur_x' => $this->secteur_x,
        'secteur_y' => $this->secteur_y,
        'secteur_z' => $this->secteur_z,
        'position_x' => $this->position_x,
        'position_y' => $this->position_y,
        'position_z' => $this->position_z,
    ];
}
```

---

## 🔄 Flux de Déplacement

### Cas 1 : Vaisseau se déplace

```
1. Vaisseau déplace son ObjetSpatial
   ObjetSpatial.position_x/y/z modifié

2. Objets dans cargo suivent automatiquement
   parent_type = 'ObjetSpatial' (vaisseau)
   → Position calculée = position vaisseau
```

### Cas 2 : Station orbite planète

```
1. Planète bouge (orbite autour étoile)
   Planete.cache_position_x/y/z mis à jour

2. Station suit planète (orbite autour)
   parent_type = 'Planete'
   → Position calculée = planète + offset orbital

3. Vaisseaux amarrés suivent station
   parent_type = 'Station'
   → Position calculée = station
```

### Cas 3 : Mine sur astéroïde

```
1. Astéroïde bouge (orbite dans zone)
   ObjetSpatial (astéroïde).position_x/y/z mis à jour

2. Mine suit astéroïde
   parent_type = 'ObjetSpatial' (astéroïde)
   → Position calculée = astéroïde
```

---

## 📊 Tables et Relations

### ObjetSpatial (table centrale)

```sql
CREATE TABLE objets_spatiaux (
  id BIGINT PRIMARY KEY,

  -- Type
  type VARCHAR(50), -- 'vaisseau', 'station', 'mine', etc.
  nom VARCHAR(255),

  -- Position sectoriale (AL)
  secteur_x INT,
  secteur_y INT,
  secteur_z INT,

  -- Position locale (cUA)
  position_x INT,
  position_y INT,
  position_z INT,
  azimut DECIMAL(5,2), -- Orientation 0-360°

  -- Parent polymorphique (contenu dans)
  parent_type VARCHAR(255) NULL,
  parent_id BIGINT NULL,
  INDEX idx_parent (parent_type, parent_id),

  -- Détection
  detectabilite_base DECIMAL(10,2),
  poi_connu BOOLEAN,

  -- Physique
  masse INT,
  volume INT,
  resistance INT,

  -- Metadata
  proprietaire_id BIGINT NULL,
  contenu_dans BIGINT NULL, -- Deprecated, utiliser parent

  timestamps
);
```

### Tables Détails

```sql
-- Vaisseau (détails)
CREATE TABLE vaisseaux (
  id BIGINT PRIMARY KEY,
  objet_spatial_id BIGINT FK, -- ✅ Lien vers position

  -- Armement
  puissance_feu INT,
  boucliers INT,

  -- Cargo
  cargo_max INT,
  cargo_actuel INT,

  -- Moteur
  vitesse_max DECIMAL,
  energie_max INT,

  -- Orbite optionnelle
  orbite_planete_id BIGINT NULL,
  orbite_rayon_ua DECIMAL,
  orbite_angle_initial DECIMAL,

  timestamps
);

-- Station (détails)
CREATE TABLE stations (
  id BIGINT PRIMARY KEY,
  objet_spatial_id BIGINT FK, -- ✅ Lien vers position

  -- Services
  capacite_amarrage INT,
  commerciale BOOLEAN,
  industrielle BOOLEAN,

  -- Orbite (si satellisée)
  planete_id BIGINT NULL,
  orbite_rayon_ua DECIMAL NULL,
  orbite_angle DECIMAL NULL,

  -- OU fixe dans système
  systeme_stellaire_id BIGINT NULL,

  timestamps
);

-- Mine (détails)
CREATE TABLE mines (
  id BIGINT PRIMARY KEY,
  objet_spatial_id BIGINT FK, -- ✅ Lien vers position

  -- Type
  type_mine ENUM('mame', 'evoluee'),

  -- Extraction
  gisement_id BIGINT,
  taux_extraction DECIMAL,
  stock_actuel INT,

  -- Parent (planète/station)
  planete_id BIGINT NULL,
  base_id BIGINT NULL,

  timestamps
);
```

---

## 🎯 Règles de Conception

### 1. Tout objet physique = ObjetSpatial

```
✅ Vaisseau → ObjetSpatial
✅ Station → ObjetSpatial
✅ Mine → ObjetSpatial
✅ Astéroïde → ObjetSpatial
✅ Débris → ObjetSpatial

❌ Planète → Table séparée (trop spécifique)
❌ Système → Table séparée (conteneur logique)
```

### 2. Parent = Contenu Dans

```
Parent = conteneur logique ET/OU physique

Exemples :
- Vaisseau dans station → parent = Station (amarré)
- Mine sur planète → parent = Planete (posée)
- Astéroïde dans zone → parent = ZoneSpatiale (appartenance)
```

### 3. Position Héritée vs Propre

```
Position HÉRITÉE (suit parent) :
- Vaisseau amarré
- Cargo dans vaisseau
- Mine attachée à station

Position PROPRE (orbite indépendante) :
- Astéroïde dans zone (orbite dans ceinture)
- Station en orbite (offset depuis planète, pas exactement parent)
```

### 4. Double Coordonnées Obligatoires

```
TOUJOURS remplir :
- secteur_x/y/z (position grossière)
- position_x/y/z (position fine)

Même si position héritée, stocker pour cache
```

---

## 📚 Références

**Code source** :
- `app/Models/ObjetSpatial.php`
- `app/Models/Station.php`
- `app/Models/Mine.php`
- `app/Models/Vaisseau.php`
- `database/migrations/*_add_objet_spatial_id_to_*.php`
- `scripts/migrate_*_to_objets_spatiaux.php`

**Documentation** :
- `docs/TODO_ZONES_ASTEROIDES.md`
- `docs/game-design/GDD_Asteroides.md`
- `docs/game-design/SYSTEME_COORDONNEES.md`

---

**Document maintenu par :** Claude Code
**Dernière mise à jour :** 2025-12-31
