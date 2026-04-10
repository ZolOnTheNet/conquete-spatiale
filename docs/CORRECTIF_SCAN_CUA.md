# CORRECTIF: Système de Scan et Coordonnées cUA

## Problèmes identifiés

### 1. Positions systèmes stellaires en AL au lieu de cUA ✅ CORRIGÉ
- **Problème**: Les positions étaient stockées comme fractions d'AL (0.45, 0.81, etc.)
- **Solution**: Migration + correction du converter Gaia
- **Fichiers modifiés**:
  - `app/Services/GaiaCoordinateConverter.php` ✅
  - `database/migrations/2025_12_29_215056_convert_systemes_stellaires_positions_to_cua.php` ✅

### 2. Calculs de distance trop compliqués ⚠️ EN COURS
- **Problème**: Le code additionne secteurs (AL) + positions (cUA) après conversion
- **Solution**: Utiliser DEUX règles distinctes

## Règles de détection correctes

### Règle 1: Systèmes stellaires (Inter-secteur)
```
Distance = sqrt(
    (secteur_x2 - secteur_x1)² +
    (secteur_y2 - secteur_y1)² +
    (secteur_z2 - secteur_z1)²
)
// Distance en AL, positions ignorées

score_detection = (distance_AL / 10) × detectabilite_base
```

### Règle 2: POI locaux (Intra-secteur) - Planètes, Stations, Mines
```
IF (secteur_x == secteur_x2 && secteur_y == secteur_y2 && secteur_z == secteur_z2) {
    Distance = sqrt(
        (position_x2 - position_x1)² +
        (position_y2 - position_y1)² +
        (position_z2 - position_z1)²
    )
    // Distance en cUA, secteurs ignorés

    score_detection = (distance_cUA / 1000) × detectabilite_base
} ELSE {
    // Pas dans le même secteur → non détectable
    score_detection = INFINITY
}
```

## Fichiers à corriger

### ✅ FAIT
1. `app/Services/GaiaCoordinateConverter.php`
   - Conversion AL → cUA dans `galacticToGame()`
   - Conversion inverse cUA → AL dans `gameToGalactic()`

2. `database/migrations/2025_12_29_215056_convert_systemes_stellaires_positions_to_cua.php`
   - Migration pour convertir 211 systèmes existants

### 🔧 À FAIRE
3. `app/Models/SystemeStellaire.php`
   - Méthode `getScoreDetection()` : utiliser SEULEMENT les secteurs (AL)
   - Méthode `calculerDistance()` : également simplifier

4. `app/Models/Planete.php`
   - Méthode `getScoreDetection()` : vérifier même secteur, puis utiliser SEULEMENT positions (cUA)
   - Formule: `(distance_cUA / 1000) × detectabilite`

5. `app/Models/Station.php`
   - Méthode `getScoreDetection()` : même logique que Planete

6. `app/Models/Mine.php`
   - Méthode `getScoreDetection()` : même logique que Planete

7. `app/Models/ObjetSpatial.php`
   - Méthode `getScoreDetection()` : à vérifier et corriger

8. `app/Http/Controllers/ScanController.php`
   - Méthode `trouverObjetsDetectables()` : simplifier calculs de distance
   - Méthode `mettreAJourScans()` : simplifier calculs de distance
   - Supprimer les conversions AL→cUA + additions

## Conversion: 1 AL = 6,324,100 cUA

### Exemples de conversion
- 0.45 AL = 2,845,845 cUA
- 0.81 AL = 5,122,521 cUA
- 1.0 AL = 6,324,100 cUA

### Formules
- **AL → cUA**: `cUA = AL × 6,324,100`
- **cUA → AL**: `AL = cUA / 6,324,100`

## Tests de vérification

Après implémentation, vérifier:
1. ✅ Positions systèmes en cUA (> 1M pour la plupart)
2. ✅ Conversion Gaia génère cUA
3. 🔧 Scan détecte systèmes avec formule AL
4. 🔧 Scan détecte planètes avec formule cUA
5. 🔧 Pas d'addition secteur+position dans les calculs
