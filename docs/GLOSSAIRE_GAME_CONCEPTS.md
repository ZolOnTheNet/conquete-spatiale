# 📖 GLOSSAIRE DES CONCEPTS DU JEU

**Date** : 2025-12-27
**Version** : 1.0

---

## 🎯 Introduction

Ce document explique les concepts et mécaniques clés du jeu de conquête spatiale.

---

## 🌟 SYSTÈMES STELLAIRES

### POI Connu (Point Of Interest Connu)

**Type** : Booléen (`true` / `false`)
**Champ BD** : `poi_connu` dans `systemes_stellaires`, `planetes`, `mines`

**Définition** : Indique si un objet spatial est **connu et visible** pour les joueurs.

**Fonctionnement** :
- `poi_connu = true` → L'objet apparaît dans l'interface du jeu (carte, liste, navigation)
- `poi_connu = false` → L'objet existe en base de données mais est **invisible** pour les joueurs

**Exemples** :
- **Système Sol** : `poi_connu = true` (connu dès le départ du jeu)
- **Proxima Centauri** : `poi_connu = true` (étoile célèbre, connue initialement)
- **Systèmes procéduraux GAIA** : `poi_connu = false` (à découvrir par exploration)
- **Exoplanètes NASA** : `poi_connu = false` par défaut (doivent être scannées)

**Système de découverte** (à implémenter) :
Quand un joueur scanne/découvre un système, le champ `poi_connu` passe de `false` à `true`.

### Détectabilité de Base

**Type** : Float (0-200+)
**Champ BD** : `detectabilite_base` dans `systemes_stellaires`, `planetes`

**Définition** : Score de **facilité de détection** d'un objet spatial. Plus le score est élevé, plus l'objet est facile à détecter.

**Facteurs** :
- **Étoiles** : Basé sur la puissance (type spectral)
  - Étoiles de type O/B : Très brillantes, détectabilité ~100-200
  - Étoiles de type G (Soleil) : Détectabilité ~40-60
  - Étoiles de type M (naines rouges) : Détectabilité ~20-30

- **Planètes** : Basé sur rayon et distance à l'étoile
  - Grosses planètes proches : Haute détectabilité
  - Petites planètes lointaines : Basse détectabilité

**Utilisation** :
Système D_CAL (Distance Calibrée) pour déterminer si un joueur peut détecter un objet :
```
D_CAL = detectabilite_base × (distance_AL / facteur_distance)
Seuil = 100
Si D_CAL ≥ Seuil → Objet détectable
```

### Puissance (Étoile)

**Type** : Integer (1-200)
**Champ BD** : `puissance` dans `systemes_stellaires`

**Définition** : **Luminosité relative** de l'étoile, basée sur son type spectral.

**Plages par type spectral** :
| Type | Température | Couleur | Puissance | Exemple |
|------|------------|---------|-----------|---------|
| O | >30,000 K | Bleue | 150-200 | Étoiles massives rares |
| B | 10,000-30,000 K | Bleu-blanc | 100-140 | Rigel |
| A | 7,500-10,000 K | Blanc | 80-100 | Sirius |
| F | 6,000-7,500 K | Blanc-jaune | 60-80 | Procyon |
| G | 5,200-6,000 K | Jaune | 40-60 | **Soleil** |
| K | 3,700-5,200 K | Orange | 30-40 | Epsilon Eridani |
| M | <3,700 K | Rouge | 20-30 | Proxima Centauri |

**Génération** : Aléatoire dans la plage du type spectral + bruit procédural.

---

## 🪐 PLANÈTES & RESSOURCES

### Richesse (Gisement)

**Type** : Integer (0-100 %)
**Champ BD** : `richesse` dans `gisements`

**Définition** : **Rendement** d'un gisement de ressources. Représente la concentration/qualité du minerai.

**❌ CE N'EST PAS** : Le prix de vente de la ressource
**✅ C'EST** : Un multiplicateur de production

**Fonctionnement** :
```php
// Code: app/Models/Gisement.php:80-81
$rendement = $this->richesse;
```

**Exemples** :
- **Richesse 100%** : Gisement optimal, rendement maximal
- **Richesse 50%** : Gisement moyen, rendement moitié du max
- **Richesse 20%** : Gisement pauvre, rendement faible

**Génération** : Aléatoire entre 20% et 100% lors de la création du gisement.

**Impact sur la production** :
```
Production_réelle = Production_base × (richesse / 100)
```

Si un mineur exploite un gisement de Fer avec richesse 80%, il obtiendra 80% de la production théorique maximale.

### Type de Planète

**Types disponibles** :
- `terrestre` : Rocheuse, similaire à la Terre
- `tellurique` : Rocheuse sans atmosphère (type Mercure/Mars)
- `gazeuse` : Géante gazeuse (type Jupiter/Saturne)
- `glacee` : Monde gelé (type Europe/Titan)
- `oceanique` : Couverte d'eau
- `desertique` : Aride, sèche
- `volcanique` : Activité volcanique intense

**Couleurs SVG** (visualisation carte) :
- Terrestre/Tellurique : Marron (#8B4513)
- Gazeuse : Bleu (#4169E1)
- Glacée : Bleu ciel (#87CEEB)
- Océanique : Bleu foncé (#1E90FF)
- Désertique : Beige (#DEB887)
- Volcanique : Rouge-orange (#FF4500)

---

## 📡 DONNÉES NASA & GAIA

### Source GAIA

**Champ BD** : `source_gaia` (boolean), `gaia_source_id`, `gaia_ra`, `gaia_dec`, `gaia_distance_ly`

**Définition** : Étoiles importées du catalogue ESA GAIA DR3 (vraies étoiles proches du Soleil).

**Catalogue** : ~200 étoiles dans un rayon de 20 AL autour du Soleil.

**Linkage avec NASA** :
Les noms GAIA (ex: "GAIA-1234") ne correspondent pas aux noms célèbres.
→ Service `StarNameMatcher` fait la correspondance via coordonnées (RA, Dec, Distance)

### Source NASA Exoplanet

**Champ BD** : `source_nasa_exoplanet` (boolean) dans `planetes`

**Définition** : Exoplanètes réelles importées du NASA Exoplanet Archive.

**Données supplémentaires** :
- `nasa_exo_id` : Identifiant NASA de l'exoplanète
- `nasa_discovery_method` : Méthode de découverte (Transit, Radial Velocity, etc.)
- `nasa_discovery_year` : Année de découverte
- `excentricite_orbitale` : Excentricité de l'orbite (0 = circulaire, >0 = elliptique)

**Exemples** :
- Proxima Cen b (Proxima Centauri b) - Découverte 2016, Radial Velocity
- tau Cet e (Tau Ceti e) - Découverte 2012, Radial Velocity

**Affichage** :
- Badge vert "NASA" dans les listes admin
- Badge "🪐 EXOPLANÈTE RÉELLE NASA" dans les détails
- Section complète avec métadonnées de découverte

### Nom Commun (Common Name)

**Champ BD** : `nom_commun`, `noms_alternatifs` (JSON) dans `systemes_stellaires`

**Définition** : Nom célèbre/traditionnel d'une étoile (vs identifiant GAIA).

**Exemples** :
- Nom GAIA : "GAIA DR3 5853498713190525696"
  Nom commun : **"Proxima Centauri"**

- Nom GAIA : "GAIA DR3 ..."
  Nom commun : **"Tau Ceti"**

**Aliases** : Variantes de noms utilisées par différents catalogues :
```json
{
  "Proxima Centauri": ["Proxima Cen", "Proxima", "Alpha Centauri C", "V645 Centauri", "HIP 70890", "GJ 551"]
}
```

**Matching** :
Service `StarNameMatcher` compare les coordonnées (RA, Dec, Distance) pour identifier les étoiles célèbres parmi les données GAIA.

---

## 🧭 SYSTÈME DE COORDONNÉES

### Secteur vs Position

**⚠️ RÈGLE CRITIQUE : PAS DE MULTIPLICATION PAR 10 !**

**Coordonnées d'un objet** :
- `secteur_x`, `secteur_y`, `secteur_z` : Coordonnées AL **entières** directement
- `position_x`, `position_y`, `position_z` : Position **décimale dans le secteur** (0.0 à 1.0)

**Coordonnées absolues (AL)** :
```
coord_absolue_x = secteur_x + position_x
coord_absolue_y = secteur_y + position_y
coord_absolue_z = secteur_z + position_z
```

**Exemple - Vega Aurigae** :
- Secteur : (0, 0, 4)
- Position : (0.962, 0.779, 0.530)
- **Absolu** : (0.962, 0.779, 4.530) AL
- **Carte affiche** : (0, 0, 4) AL (secteurs entiers)

### Distance en Années-Lumière (AL)

**Formule** :
```php
$distance = sqrt(
    pow($x2 - $x1, 2) +
    pow($y2 - $y1, 2) +
    pow($z2 - $z1, 2)
);
```

**Unité Astronomique (UA)** :
- 1 UA ≈ 150 millions de km (distance Terre-Soleil)
- Utilisée pour distances **intra-système** (planètes autour de l'étoile)
- 1 AL ≈ 63,241 UA

---

## 📊 RÉSUMÉ DES CHAMPS CRITIQUES

| Champ | Type | Signification | Exemple |
|-------|------|---------------|---------|
| `poi_connu` | Boolean | Objet visible pour joueurs | `true` = visible |
| `detectabilite_base` | Float | Facilité de détection | 50.0 |
| `puissance` | Integer | Luminosité étoile | 45 (type G) |
| `richesse` | Integer % | Rendement gisement | 75% |
| `source_gaia` | Boolean | Étoile réelle GAIA | `true` |
| `source_nasa_exoplanet` | Boolean | Exoplanète réelle NASA | `true` |
| `nom_commun` | String | Nom célèbre | "Proxima Centauri" |

---

## 🔗 Références

- **Documentation NASA** : [NASA Exoplanet Archive](https://exoplanetarchive.ipac.caltech.edu/)
- **Documentation GAIA** : [ESA GAIA Mission](https://www.cosmos.esa.int/web/gaia)
- **Documentation intégration** : `docs/INTEGRATION_NASA_EXOPLANETS.md`
- **Code StarNameMatcher** : `app/Services/StarNameMatcher.php`
- **Code ExoplanetService** : `app/Services/ExoplanetService.php`

---

*Document généré pour le projet Conquête Spatiale - Laravel 12*
