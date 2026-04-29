# Correctif du Système Orbital

**Date**: 30 décembre 2025
**Version**: 1.0

## Résumé des Problèmes Résolus

Lors de la vérification du système orbital, plusieurs bugs critiques ont été identifiés et corrigés :

### 🔴 Problème 1 : Distances Astronomiques Incorrectes
**Symptôme** : La Terre affichait une distance de 346 085 969 353.29 UA (346 billions UA) au lieu de ~1 UA
**PA requis** : 27 363 PA au lieu de 1-2 PA

**Causes identifiées** :
1. Le timestamp du jeu n'était pas passé aux méthodes de calcul orbital
2. Le calcul du timestamp retournait des valeurs négatives
3. Les positions des systèmes stellaires étaient non-nulles

**Corrections appliquées** :

#### 1.1 TimonerieController.php (ligne 710-723)
```php
// AVANT : Pas de timestamp passé
$distance = $planete->getDistanceDepuisVaisseau($vaisseau);
$planetePos = $planete->getPositionAbsolue();

// APRÈS : Timestamp du jeu passé correctement
$timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);
$distance = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);
$planetePos = $planete->getPositionAbsolue($timestampJours);
```

#### 1.2 GameTimeHelper.php (ligne 29-35)
```php
// AVANT : Ordre inversé donnant des valeurs négatives
public static function dateToJours(Carbon $date): float
{
    $reference = Carbon::parse(self::DATE_REFERENCE);
    return $date->floatDiffInDays($reference, false);
}

// APRÈS : Ordre corrigé pour valeurs positives
public static function dateToJours(Carbon $date): float
{
    $reference = Carbon::parse(self::DATE_REFERENCE);
    // Inverser l'ordre : reference->diff(date) au lieu de date->diff(reference)
    // pour obtenir un nombre positif quand date > reference
    return $reference->floatDiffInDays($date, false);
}
```

#### 1.3 Positions des Systèmes Stellaires
**Script créé** : `fix_systeme_positions.php`

**Problème** : Les systèmes stellaires avaient des positions non-nulles
- Sol : position (3 162 050, 3 162 050, 3 162 050) cUA
- Cela ajoutait ~31 620 UA à toutes les positions des planètes

**Solution** : Les systèmes stellaires doivent avoir position (0, 0, 0) cUA car leur emplacement est défini par `secteur_x/y/z` (en AL)

```php
foreach ($systemes as $systeme) {
    $systeme->position_x = 0;
    $systeme->position_y = 0;
    $systeme->position_z = 0;
    $systeme->save();
}
```

**Résultat** :
- ✅ Distances maintenant correctes : Terre à ~1 UA, Mars à ~1.5 UA, Jupiter à ~5 UA
- ✅ PA requis réalistes : 1-2 PA pour planètes internes, 5-10 PA pour externes

---

### 🔴 Problème 2 : Affichage Puissance Solaire Incorrecte
**Symptôme** : L'en-tête affichait 48.00 au lieu de 50 pour la puissance de Sol
**Base de données** : `systemes_stellaires.puissance = 50`

**Cause** : game-header.blade.php utilisait `$systeme->puissance_solaire` (48) au lieu de `$systeme->puissance` (50)

**Correction** : game-header.blade.php (ligne 82-83)
```php
// AVANT :
<span class="stat-item" title="Puissance solaire: {{ $systeme->puissance_solaire ?? 50 }}/100">
    ☀️ {{ number_format($systeme->puissance_solaire ?? 50, 2) }}

// APRÈS :
<span class="stat-item" title="Puissance solaire: {{ $systeme->puissance ?? 50 }}/100">
    ☀️ {{ number_format($systeme->puissance ?? 50, 2) }}
```

**Résultat** : ✅ Affichage correct de 50.00 pour Sol

---

### 🔴 Problème 3 : Planètes Statiques (Positions Non Mises à Jour)
**Symptôme** : Les planètes ne changeaient pas de position malgré l'avancement du temps

**Causes** :
1. Recalcul client-side désactivé dans timonerie.blade.php
2. 7 planètes sans vitesse angulaire dans la base

**Corrections** :

#### 3.1 Activation du Recalcul Client-Side
**Fichier** : `resources/views/game/navire/timonerie.blade.php` (ligne 335-338)
```javascript
// AVANT : Commenté
// if (typeof OrbitalCalculator !== 'undefined') {
//     mettreAJourDistancesPlanetes();
// }

// APRÈS : Activé
if (typeof OrbitalCalculator !== 'undefined') {
    console.log('✓ OrbitalCalculator chargé, calculs orbitaux disponibles');
    // Mettre à jour les distances au chargement (calcul client-side en temps réel)
    mettreAJourDistancesPlanetes();
    console.log('✓ Positions des planètes recalculées côté client');
}
```

#### 3.2 Correction des Données Orbitales Manquantes
**Script créé** : `fix_missing_orbital_data.php`

**Problème** : 7 exoplanètes importées de la NASA n'avaient pas de `vitesse_angulaire`

**Solution** : Calcul via la loi de Kepler : ω = 2π / T
```php
foreach ($planetes as $planete) {
    if ($planete->periode_orbitale > 0 && is_null($planete->vitesse_angulaire)) {
        $planete->vitesse_angulaire = (2 * M_PI) / $planete->periode_orbitale;
        $planete->save();
    }
}
```

**Résultat** :
- ✅ 7 planètes corrigées
- ✅ Les 1267 planètes ont maintenant toutes leurs données orbitales complètes

---

## Scripts de Vérification Créés

### test_orbital_system.php
Test complet du système orbital :
- Vérifie les données orbitales de toutes les planètes
- Simule la dépense de PA et l'avancement du temps
- Vérifie que les positions se mettent à jour correctement

**Sortie attendue** :
```
=== SIMULATION DÉPENSE DE PA ===
Dépense de 10 PA (= 5 jours in-game)...
✓ Nouvelle date: 13 Jan 3000 12:00

✅ Toutes les planètes ont leurs données orbitales
✅ Le temps avance correctement à chaque dépense de PA
✅ Les positions des planètes se mettent à jour automatiquement
```

### test_timonerie_distances.php
Test spécifique des calculs de distance dans la Timonerie :
- Place un vaisseau à 0.1 UA de l'étoile Sol
- Calcule les distances vers toutes les planètes connues
- Vérifie que les distances sont cohérentes

**Sortie attendue** :
```
🌍 Terre
   Distance étoile: 1 UA
   Distance vaisseau: 0.91 UA
   PA requis: 1
   ✅ Distance cohérente
```

### test_simple_positions.php
Vérification étape par étape des calculs de position :
- Affiche chaque étape du calcul orbital
- Compare le calcul manuel avec getPositionAbsolue()
- Utile pour déboguer les problèmes de position

**Sortie attendue** :
```
Système Sol (rechargé):
  Secteur: (0, 0, 0) AL
  Position: (0, 0, 0) cUA

Position absolue (UA): X=0.92, Y=0.39
```

### check_orbital_data.php
Audit des données orbitales dans la base :
- Compte les planètes sans données orbitales
- Liste les planètes problématiques

### fix_systeme_positions.php
Correction automatique des positions des systèmes :
- Remet toutes les positions système à (0, 0, 0)
- Affiche le nombre de systèmes corrigés

### fix_missing_orbital_data.php
Calcul automatique des vitesses angulaires manquantes :
- Utilise la formule ω = 2π / T
- Met à jour les 7 planètes problématiques

---

## Validation Finale

### Tests de Régression Réussis ✅

**Test 1 : Distances Réalistes**
```
Mercure : 0.29 UA → 1 PA requis
Vénus   : 0.62 UA → 1 PA requis
Terre   : 0.91 UA → 1 PA requis
Mars    : 1.54 UA → 1 PA requis
Jupiter : 5.14 UA → 3 PA requis
Saturne : 9.44 UA → 5 PA requis
Uranus  : 19.16 UA → 10 PA requis
```

**Test 2 : Mouvement Orbital**
Après 5 jours (10 PA dépensés) :
- Mercure : rotation de 20° (période 89 jours)
- Vénus : rotation de 8° (période 223 jours)
- Terre : rotation de 5° (période 365 jours)
- Mars : rotation de 3° (période 684 jours)
- Jupiter : rotation de 0.4° (période 4331 jours)

**Test 3 : Système Temporel**
```
Date initiale : 08 Jan 3000 12:00
PA dépensés  : 10 PA
Jours avancés : 5 jours (1 PA = 0.5 jour)
Date finale  : 13 Jan 3000 12:00 ✓
```

---

## Impact et Recommandations

### Impact sur le Gameplay
1. **Navigation réaliste** : Les déplacements nécessitent maintenant des PA cohérents avec les distances réelles
2. **Orbites dynamiques** : Les planètes changent de position au fil du temps, rendant la navigation plus stratégique
3. **Fenêtres de transfert** : Certaines destinations seront plus proches à certains moments (comme dans Kerbal Space Program)

### Recommandations Futures
1. **Affichage des trajectoires** : Montrer les orbites des planètes sur la carte
2. **Prédiction de position** : Permettre au joueur de voir où sera une planète dans X jours
3. **Optimisation de route** : Suggérer le meilleur moment pour voyager vers une planète
4. **Événements orbitaux** : Alertes quand deux corps célestes sont en conjonction/opposition

### Tests Supplémentaires Recommandés
1. ☐ Tester avec des personnages multiples pour vérifier l'indépendance temporelle
2. ☐ Vérifier les performances avec 1000+ planètes affichées simultanément
3. ☐ Tester les cas limites (vitesses angulaires très élevées/faibles)
4. ☐ Valider le comportement en cas de voyage hyperspatial (saut temporel)

---

## Annexes

### Formules Utilisées

**Position orbitale** :
```
θ(t) = θ₀ + (ω × t)
x(t) = d × cos(θ(t))
y(t) = d × sin(θ(t))
```

**Vitesse angulaire** :
```
ω = 2π / T [rad/jour]
```

**Période orbitale (Loi de Kepler)** :
```
T = 365.25 × √(d³) [jours]
d : distance en UA
```

**Conversion d'unités** :
```
1 AL = 63 241 UA = 6 324 100 cUA
1 UA = 100 cUA
```

### Fichiers Modifiés

**Modèles** :
- `app/Models/Personnage.php` : Ajout de avancerTempsJeu() et modification de consommerPA()

**Contrôleurs** :
- `app/Http/Controllers/TimonerieController.php` : Ajout du timestamp aux calculs orbitaux

**Helpers** :
- `app/Helpers/GameTimeHelper.php` : Correction du calcul dateToJours()

**Vues** :
- `resources/views/components/game-header.blade.php` : Correction affichage puissance solaire
- `resources/views/game/navire/timonerie.blade.php` : Activation recalcul orbital

**Migrations** :
- `database/migrations/2025_12_30_130118_add_derniere_connexion_to_personnages_table.php`

**Scripts de maintenance** :
- `fix_systeme_positions.php`
- `fix_missing_orbital_data.php`
- `check_orbital_data.php`
- `test_orbital_system.php`
- `test_timonerie_distances.php`
- `test_simple_positions.php`

---

**Statut** : ✅ TOUS LES PROBLÈMES RÉSOLUS
**Date de validation** : 30 décembre 2025
