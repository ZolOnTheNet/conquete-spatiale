# GDD - Système de Détection et Scan v2.0

**Version:** 2.0
**Date:** 2025-12-28
**Statut:** OFFICIEL - Ce document remplace tous les documents précédents sur la détection

---

## ⚠️ DOCUMENTS OBSOLÈTES

Les documents suivants sont **OBSOLÈTES** et ne doivent plus être utilisés comme référence :

- `GDD_Systeme_Decouverte.md` - Système de points de tâche (trop complexe, non implémenté)
- `SYSTEME_DETECTION_SCAN.md` - Système simplifié (incomplet, formules incorrectes)

**Ce document (GDD_SYSTEME_DETECTION_V2.0) est désormais la référence unique et officielle.**

---

## 1. Vue d'ensemble

Le système de détection permet aux joueurs de découvrir progressivement les systèmes stellaires et planètes dans leur zone de scan. Il combine :

1. **Un système de score de détection** basé sur la distance et la détectabilité des objets
2. **Un système de progression des dés** basé sur la puissance du scanner
3. **Un système de jets de compétence optionnel** basé sur Daggerheart (Finesse/Savoir)

---

## 2. Formules de base

### 2.1 Détectabilité (calculée à la création)

**Pour les systèmes stellaires :**
```
detectabilite_base = (200 - puissance) / 3
```

Avec `puissance` = puissance de l'étoile (10-200)

**Pour les planètes :**
```
detectabilite_base = 100 - (rayon × 8)
```

Avec `rayon` en rayons terrestres (0.5-12.0)

### 2.2 Score de détection

```
score_detection = (distance / 10) × detectabilite_base
```

- `distance` en Années-Lumière (AL) pour les systèmes stellaires
- `distance` en Unités Astronomiques (UA) pour les planètes dans un système

**Cas particulier - POI déjà connus :**
```
Si poi_connu = true, alors score_detection = 0
```

Les POI déjà connus (systèmes, planètes, stations, mines) apparaissent **automatiquement** dans les scans avec un seuil de 0, sans nécessiter d'accumulation de points de scan.

**Objectif du design :**
- Systèmes stellaires proches (1-4 AL) : 6-16 scans en moyenne
- Planètes proches (< 10 UA) : 1-10 scans
- Planètes lointaines (> 30 UA) : 20-80 scans
- **POI connus : 0 scan (apparition automatique)**

### 2.3 Condition de détection

**IMPORTANT : Le CUMUL est UNIQUE et indépendant des objets**

```
cumul_scan = somme de tous les jets de dés effectués
detecte = cumul_scan >= score_detection
```

- Le **CUMUL** est stocké sur le vaisseau (`vaisseau.scan_niveau_actuel`)
- C'est une valeur UNIQUE qui augmente à chaque scan
- TOUS les objets à portée sont comparés au MÊME cumul
- Si `cumul >= score_detection` d'un objet, cet objet est détecté

**Exemple :**
- Scan 1 : jet = 10 → cumul = 10 → Mercure détectée (score = 0.6)
- Scan 2 : jet = 13 → cumul = 23 → Uranus détectée (score = 23.0)
- Scan 3 : jet = 15 → cumul = 38 → Neptune détectée (score = 36.1)

Pour les POI connus (score = 0), la condition est toujours vraie dès le premier scan.

---

## 3. Système de dés (Scanner)

### 3.1 Puissance du scanner

La puissance effective du scanner se calcule :

```php
puissance_scan_effective = puissance_scan + bonus_scan
```

- `puissance_scan` : Puissance de base du scanner (10-100+)
- `bonus_scan` : Bonus temporaire provenant de jets de compétence (0-12)

### 3.2 Progression des dés

La puissance du scanner détermine la **formule de dés** utilisée lors du jet de scan :

| Puissance | Formule de dés | Moyenne | Min | Max |
|-----------|---------------|---------|-----|-----|
| 1-4       | 1d4           | 2.5     | 1   | 4   |
| 5-9       | 2d4           | 5.0     | 2   | 8   |
| 10-14     | 3d4           | 7.5     | 3   | 12  |
| 15-19     | 1d6 + 2d4     | 8.5     | 4   | 14  |
| 20-24     | 2d6 + 2d4     | 12.0    | 6   | 20  |
| 25-29     | 1d8 + 2d6     | 11.5    | 5   | 20  |
| 30-34     | 2d8 + 2d6     | 16.0    | 8   | 28  |
| 35-39     | 1d10 + 2d8    | 14.5    | 7   | 26  |
| 40-44     | 2d10 + 2d8    | 20.0    | 12  | 36  |
| 45-49     | 1d12 + 2d10   | 17.5    | 9   | 32  |
| 50-59     | 2d12 + 2d10   | 24.0    | 16  | 44  |
| 60-69     | 1d15 + 2d12   | 20.0    | 11  | 39  |
| 70-79     | 2d15 + 2d12   | 28.5    | 20  | 54  |
| 80-89     | 1d20 + 2d15   | 26.5    | 15  | 50  |
| 90-99     | 2d20 + 2d15   | 36.5    | 28  | 70  |
| 100-109   | 1d24 + 2d20   | 33.0    | 21  | 64  |
| 110-119   | 2d24 + 2d20   | 45.5    | 40  | 88  |
| 120-129   | 3d24 + 2d20   | 58.0    | 59  | 112 |
| 130-139   | 4d24 + 2d20   | 70.5    | 78  | 136 |
| 140+      | 5d24 + 2d20   | 83.0    | 97  | 160 |

**Principe de design :**
- Les combinaisons de petits dés sont plus fiables (variance réduite)
- 2d4 (moyenne 5.0) est préférable à 1d8 (moyenne 4.5)
- La progression est linéaire par palier de 5 points

---

## 4. Système de jets de compétence (Optionnel)

Le joueur peut effectuer un **jet de compétence** durant les scans pour obtenir un bonus temporaire.

### 4.1 Compétences utilisées

**Deux compétences au choix :**

1. **Finesse** : Manipulation précise des instruments de scan
2. **Savoir** : Connaissance théorique des phénomènes astronomiques

### 4.2 Mécanique Daggerheart

**Jet de compétence :**
```
jet_competence = 2d12 + modificateur_competence
```

- `modificateur_competence` = valeur de la compétence (Finesse ou Savoir)
- Le joueur lance **2d12**
- Un dé est le **dé d'Espoir** (Hope)
- Un dé est le **dé de Peur** (Fear)

### 4.3 Difficulté

La difficulté dépend du nombre d'objet à scanner, pour une échelle entre 5 et 25 :
c'est a dire nombre de PoI locaux, plus le nombre de PoI (stellaire) atteignable par la porté du scannere


### 4.4 Résultats et dé de bonus/malus

Le jet de compétence détermine **QUEL dé** sera lancé à chaque scan (pas le résultat).

**Réussite (jet >= difficulté) :**

La **marge de réussite** détermine le dé de bonus :
```
Marge = Total du jet - Difficulté
```

| Marge de réussite | Dé de bonus | Relancé à chaque scan |
|-------------------|-------------|----------------------|
| 0-2               | +1d4        | ✓                    |
| 3-5               | +1d6        | ✓                    |
| 6-8               | +1d8        | ✓                    |
| 9-11              | +1d10       | ✓                    |
| 12+               | +1d12       | ✓                    |

**Complication mineure** si Peur > Espoir (narratif, n'affecte pas le bonus)

**Échec (jet < difficulté) :**
- **Malus fixe** : **-1d6** relancé à chaque scan
- Peu importe Espoir/Peur : c'est toujours -1d6

### 4.5 Utilisation du dé de bonus/malus

**Le dé est déterminé une fois, mais RELANCÉ à chaque scan :**

1. **Jet de compétence** (une seule fois) :
   - Détermine QUEL dé sera utilisé (1d4, 1d6, 1d8, etc.)
   - Ce dé reste le même jusqu'à réinitialisation

2. **Chaque scan** :
   - Lance les dés du scanner (ex: 3d4)
   - Lance le dé de bonus/malus (ex: 1d6) **← nouveau résultat à chaque fois**
   - Total du scan = dés scanner + résultat du dé de bonus/malus
   - Cumule avec les scans précédents

**Réinitialisation :**
- Le dé de bonus/malus est réinitialisé quand :
  - Le vaisseau change de position de scan
  - Le joueur décide de refaire un nouveau jet de compétence

**Exemple :**
```
Jet de compétence : 2d12 + 2 = 18, difficulté 15 → marge = 3 → 1d6 de bonus

Scan 1 :
  - Scanner : 3d4 = [3,2,4] = 9
  - Bonus : 1d6 = 4
  - Total : 9 + 4 = 13
  - Cumul : 13

Scan 2 :
  - Scanner : 3d4 = [4,3,2] = 9
  - Bonus : 1d6 = 2 (nouveau jet du 1d6)
  - Total : 9 + 2 = 11
  - Cumul : 13 + 11 = 24

Scan 3 :
  - Scanner : 3d4 = [4,4,3] = 11
  - Bonus : 1d6 = 5 (nouveau jet du 1d6)
  - Total : 11 + 5 = 16
  - Cumul : 24 + 16 = 40
```

---

## 5. Trois modes de scan

### 5.1 Scan simple (bouton "📡 Scan")

**Pas de jet de compétence**, lance directement les dés du scanner. **MAIS** continue d'utiliser le bonus existant si présent :

```
jet_scanner = lancerDesScan(puissance_scan_effective)
bonus = lancerBonusScan() (si un bonus existe d'un jet précédent, il est RELANCÉ)
cumul_scans += (jet_scanner + bonus)
detecte = cumul_scans >= score_detection
```

**Comportement important :**
- Ne fait PAS de nouveau jet de compétence
- Continue d'utiliser le dé de bonus du dernier jet de compétence (s'il existe)
- Le dé de bonus est RELANCÉ à chaque scan (nouveau résultat)
- Continue le cumul des scans précédents

**Avantages :**
- Rapide et simple
- Pas de risque de malus supplémentaire
- Continue le cumul ET le bonus des scans précédents
- Permet de chaîner "Scan & Réglage" puis plusieurs "Scan" pour profiter du bonus

**Inconvénients :**
- Ne peut pas améliorer un bonus existant (pour ça, refaire un jet de compétence)
- Si vous avez un malus (-1d6), il persiste tant que vous ne refaites pas de jet

### 5.2 Scan avec Réglage (bouton "🔧 Scan & Réglage")

**Avec jet de compétence Finesse** :

```
1. jet_competence = 2d12 + finesse (détermine le TYPE de dé bonus)
2. Calculer bonus/malus selon résultat et Espoir/Peur
3. jet_scanner = lancerDesScan(puissance_scan_effective)
4. bonus = lancerBonusScan() (le dé de bonus est RELANCÉ à chaque scan)
5. cumul_scans += (jet_scanner + bonus)
6. detecte = cumul_scans >= score_detection
```

**Avantages :**
- Bonus potentiel important (+2.5 à +6.5 en moyenne)
- Progression via la compétence Finesse du personnage
- Le dé de bonus est relancé à chaque scan (nouveaux résultats)

**Inconvénients :**
- Risque de malus (-3.5 en moyenne avec -1d6)
- Nécessite un bon score en Finesse

### 5.3 Scan avec Astronomie (bouton "🌟 Scan & Astro")

**Avec jet de compétence Savoir** :

```
1. jet_competence = 2d12 + savoir (détermine le TYPE de dé bonus)
2. Calculer bonus/malus selon résultat et Espoir/Peur
3. jet_scanner = lancerDesScan(puissance_scan_effective)
4. bonus = lancerBonusScan() (le dé de bonus est RELANCÉ à chaque scan)
5. cumul_scans += (jet_scanner + bonus)
6. detecte = cumul_scans >= score_detection
```

**Avantages :**
- Bonus potentiel important (+2.5 à +6.5 en moyenne)
- Progression via la compétence Savoir du personnage
- Le dé de bonus est relancé à chaque scan (nouveaux résultats)
- Continue le cumul même après avoir utilisé "Scan simple"

**Inconvénients :**
- Risque de malus (-3.5 en moyenne avec -1d6)
- Nécessite un bon score en Savoir

**IMPORTANT :** Le cumul continue entre tous les types de scan. Si vous faites "Scan simple" puis "Scan & Réglage", les résultats s'additionnent

---

## 6. Progression des scans

### 6.1 Table `scan_progress`

Chaque scan en cours est enregistré dans la base de données :

```sql
CREATE TABLE scan_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    personnage_id BIGINT UNSIGNED NOT NULL,
    systeme_stellaire_id BIGINT UNSIGNED NULL,
    planete_id BIGINT UNSIGNED NULL,

    score_detection DECIMAL(10,2) NOT NULL,
    cumul_scans DECIMAL(10,2) DEFAULT 0,
    nb_scans_effectues INT DEFAULT 0,

    bonus_dice_type INT DEFAULT 0,  -- Type de dé de bonus (4, 6, 8, 10, 12) ou 0 si pas de bonus, négatif si malus (-6)

    detecte BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (personnage_id) REFERENCES personnages(id) ON DELETE CASCADE,
    FOREIGN KEY (systeme_stellaire_id) REFERENCES systemes_stellaires(id) ON DELETE CASCADE,
    FOREIGN KEY (planete_id) REFERENCES planetes(id) ON DELETE CASCADE
);
```

### 6.2 Logique de progression

1. **Premier scan** : Création d'une ligne dans `scan_progress` avec `cumul_scans = 0`
2. **Chaque scan** : Lancer les dés du scanner + bonus éventuel du jet de compétence
3. **Cumul** : Ajouter le résultat au `cumul_scans` : `cumul_scans = cumul_scans + nouveau_jet`
4. **Détection** : Quand `cumul_scans >= score_detection`, l'objet est détecté et marqué comme découvert

**Exemple :**
```
Score de détection requis : 25

Scan 1: jet = 8 → cumul = 8 (8 < 25, pas encore détecté)
Scan 2: jet = 5 → cumul = 8 + 5 = 13 (13 < 25, pas encore détecté)
Scan 3: jet = 7 → cumul = 13 + 7 = 20 (20 < 25, pas encore détecté)
Scan 4: jet = 6 → cumul = 20 + 6 = 26 (26 >= 25, DÉTECTÉ !)
```

**Avec bonus de jet de compétence :**
```
Score de détection requis : 25
Bonus fixe du jet de compétence : +3

Scan 1: jet = 8 + 3 = 11 → cumul = 11 (11 < 25, pas encore détecté)
Scan 2: jet = 5 + 3 = 8 → cumul = 11 + 8 = 19 (19 < 25, pas encore détecté)
Scan 3: jet = 7 + 3 = 10 → cumul = 19 + 10 = 29 (29 >= 25, DÉTECTÉ !)
```

---

## 7. Base de données

### 7.1 Nouvelle table : `scan_progress`

Voir section 6.1 ci-dessus.

### 7.2 Relations polymorphiques (scan_progress)

Le système utilise des **relations polymorphiques** pour permettre de scanner différents types d'objets avec la même table :

```php
// Dans ScanProgress.php
public function detectable()
{
    return $this->morphTo();
}
```

**Types d'objets détectables :**
- `SystemeStellaire` - Systèmes stellaires lointains
- `Planete` - Planètes dans les systèmes proches
- `Station` - Stations spatiales
- `Mine` - Mines sur les planètes
- `ObjetSpatial` - Autres vaisseaux et objets spatiaux

Chaque type implémente la méthode `getScoreDetection()` qui calcule son score de détection selon sa propre formule.

### 7.3 Modifications table `personnages`

Ajout de nouveaux champs :

```sql
ALTER TABLE personnages ADD COLUMN finesse INT DEFAULT 0;
ALTER TABLE personnages ADD COLUMN savoir INT DEFAULT 0;
ALTER TABLE personnages ADD COLUMN maitrise INT DEFAULT 0;

ALTER TABLE personnages ADD COLUMN scan_bonus_dice_type INT DEFAULT 0;
```

**Note :** Le champ `scan_bonus_dice_type` stocke le TYPE de dé de bonus/malus provenant du dernier jet de compétence :
- `0` : pas de bonus/malus
- `4, 6, 8, 10, 12` : bonus de +1d4, +1d6, +1d8, +1d10, +1d12
- `-6` : malus de -1d6

Ce dé est relancé à chaque scan jusqu'à ce que le joueur change de position ou refasse un jet de compétence.

### 7.3 Champs existants utilisés

**Table `systemes_stellaires` :**
- `detectabilite_base` (DECIMAL 8,2)
- `puissance` (INT)
- `secteur_x`, `secteur_y`, `secteur_z` (INT)
- `position_x`, `position_y`, `position_z` (DECIMAL 10,6)

**Table `planetes` :**
- `detectabilite_base` (DECIMAL 8,2)
- `rayon` (DECIMAL 8,2)
- `distance_etoile` (DECIMAL 10,2)

**Table `vaisseaux` :**
- `portee_scan` (DECIMAL 8,2) - portée en AL
- `puissance_scan` (INT) - puissance de base du scanner (10-100+)
- `bonus_scan` (INT) - bonus temporaire d'équipement (0-12)
- `scan_niveau_actuel` (INT) - OBSOLÈTE - ce champ ne doit PAS être utilisé dans les calculs

**Note importante :** Le champ `scan_niveau_actuel` existe dans la base mais NE DOIT PAS affecter la formule de dés. La formule de scan reste CONSTANTE pendant tous les scans - seuls les RÉSULTATS s'accumulent.

### 7.5 Intégration avec la Spatiocarte

**Ajout automatique à la spatiocarte lors de la détection :**

Quand un objet est détecté (cumul_scans >= score_detection), il est **automatiquement ajouté à la spatiocarte du joueur** via la méthode `ajouterASpatiocarte()` :

**Pour les systèmes stellaires :**
- Création d'une entrée dans la table `decouvertes`
- `coordonnees_connues` = true
- `type_etoile_connu` = true (puissance de l'étoile visible)
- `nb_planetes_connu` = **false** (nombre de planètes inconnu tant que non visité)
- `visite` = false
- Calcul et stockage de la distance de découverte

**Pour les planètes, stations, mines :**
- Le flag `poi_connu` est mis à true sur l'objet
- Ces objets apparaissent dans la spatiocarte locale quand le joueur est dans le système

**Règle importante :** Un système détecté mais **non visité** ne révèle QUE la puissance de son étoile. Le nombre et les types de planètes restent inconnus jusqu'à la première visite.

### 7.6 Portées de détection par type d'objet

**Règle générale :**

1. **Systèmes stellaires** : Détectables si `distance <= portee_scan` (longue portée, inter-secteur)
2. **POI locaux (planètes, stations, mines)** : Détectables UNIQUEMENT si :
   - Le vaisseau est **dans le même secteur** que le système parent (secteur_x, secteur_y, secteur_z identiques)
   - ET le système est à portée (`distance <= portee_scan`)

**Implémentation dans `ScanController::trouverObjetsDetectables()` :**

```php
// Systèmes stellaires - détection inter-secteur
foreach ($systemes as $systeme) {
    $distance = calculerDistance(...);
    if ($distance <= $portee) {
        // Système détectable (n'importe quel secteur)
    }
}

// Planètes - SEULEMENT dans le même secteur
foreach ($planetes as $planete) {
    $systeme = $planete->systemeStellaire;

    // Vérifier que le vaisseau est dans le MÊME SECTEUR
    if ($objetSpatial->secteur_x != $systeme->secteur_x ||
        $objetSpatial->secteur_y != $systeme->secteur_y ||
        $objetSpatial->secteur_z != $systeme->secteur_z) {
        continue; // Pas dans le même secteur, skip
    }

    $distance = calculerDistance(...);
    if ($distance <= $portee) {
        // Planète détectable
    }
}

// Stations et Mines - même logique (même secteur requis)
```

**Rationale :**

- **Coordonnées entières de secteur** : Les secteurs (coordonnées entières) définissent des zones de l'espace. Les POI locaux ne sont scannables que dans leur secteur.
- **Détection longue portée vs locale** : On peut détecter un système stellaire à distance (plusieurs secteurs), mais pour scanner ses planètes/stations, il faut être dans le même secteur.
- **Séparation des échelles** : Systèmes = échelle galactique, POI = échelle locale

**⚠️ ATTENTION :** Les anciennes versions du code avaient deux bugs :
1. Divisions arbitraires (`portee/10` pour planètes, `portee/5` pour stations) - **CORRIGÉ**
2. Pas de vérification du secteur, permettant de scanner des planètes d'autres secteurs - **CORRIGÉ**

---

## 8. Implémentation PHP

### 8.1 Calcul du score de détection

**Dans `SystemeStellaire.php` :**

```php
/**
 * Calcule le score de détection depuis une position donnée
 *
 * @param float $fromX Position X du vaisseau (secteur_x + position_x)
 * @param float $fromY Position Y du vaisseau (secteur_y + position_y)
 * @param float $fromZ Position Z du vaisseau (secteur_z + position_z)
 * @return float Score de détection requis
 */
public function getScoreDetection(float $fromX, float $fromY, float $fromZ): float
{
    $myX = $this->secteur_x + $this->position_x;
    $myY = $this->secteur_y + $this->position_y;
    $myZ = $this->secteur_z + $this->position_z;

    $dx = $myX - $fromX;
    $dy = $myY - $fromY;
    $dz = $myZ - $fromZ;

    $distance = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

    return ($distance / 10) * $this->detectabilite_base;
}
```

**Dans `Planete.php` :**

```php
/**
 * Calcule le score de détection pour une planète
 * (basé sur la distance orbitale, pas la distance 3D)
 *
 * @return float Score de détection requis
 */
public function getScoreDetection(): float
{
    // Pour les planètes, on utilise la distance_etoile (en UA)
    // car le joueur scanne depuis le système
    return ($this->distance_etoile / 10) * $this->detectabilite_base;
}
```

### 8.2 Lancer les dés du scanner

**Dans `Vaisseau.php` :**

```php
/**
 * Retourne la formule de dés basée sur la puissance du scanner
 *
 * @return array ['formula' => '2d12+2d10', 'dice' => [['nb' => 2, 'faces' => 12], ...]]
 */
public function getDiceFormula(): array
{
    $puissance = $this->getPuissanceScanEffective();

    $tiers = [
        ['min' => 1, 'max' => 4, 'dice' => [[1, 4]]],
        ['min' => 5, 'max' => 9, 'dice' => [[2, 4]]],
        ['min' => 10, 'max' => 14, 'dice' => [[3, 4]]],
        ['min' => 15, 'max' => 19, 'dice' => [[1, 6], [2, 4]]],
        ['min' => 20, 'max' => 24, 'dice' => [[2, 6], [2, 4]]],
        ['min' => 25, 'max' => 29, 'dice' => [[1, 8], [2, 6]]],
        ['min' => 30, 'max' => 34, 'dice' => [[2, 8], [2, 6]]],
        ['min' => 35, 'max' => 39, 'dice' => [[1, 10], [2, 8]]],
        ['min' => 40, 'max' => 44, 'dice' => [[2, 10], [2, 8]]],
        ['min' => 45, 'max' => 49, 'dice' => [[1, 12], [2, 10]]],
        ['min' => 50, 'max' => 59, 'dice' => [[2, 12], [2, 10]]],
        ['min' => 60, 'max' => 69, 'dice' => [[1, 15], [2, 12]]],
        ['min' => 70, 'max' => 79, 'dice' => [[2, 15], [2, 12]]],
        ['min' => 80, 'max' => 89, 'dice' => [[1, 20], [2, 15]]],
        ['min' => 90, 'max' => 99, 'dice' => [[2, 20], [2, 15]]],
        ['min' => 100, 'max' => 109, 'dice' => [[1, 24], [2, 20]]],
        ['min' => 110, 'max' => 119, 'dice' => [[2, 24], [2, 20]]],
        ['min' => 120, 'max' => 129, 'dice' => [[3, 24], [2, 20]]],
        ['min' => 130, 'max' => 139, 'dice' => [[4, 24], [2, 20]]],
        ['min' => 140, 'max' => 9999, 'dice' => [[5, 24], [2, 20]]],
    ];

    foreach ($tiers as $tier) {
        if ($puissance >= $tier['min'] && $puissance <= $tier['max']) {
            $formula = [];
            $diceArray = [];

            foreach ($tier['dice'] as [$nb, $faces]) {
                $formula[] = "{$nb}d{$faces}";
                $diceArray[] = ['nb' => $nb, 'faces' => $faces];
            }

            return [
                'formula' => implode('+', $formula),
                'dice' => $diceArray
            ];
        }
    }

    // Fallback
    return ['formula' => '1d4', 'dice' => [['nb' => 1, 'faces' => 4]]];
}

/**
 * Lance les dés du scanner et retourne le résultat
 *
 * @return array ['total' => int, 'details' => string, 'rolls' => array]
 */
public function lancerDesScan(): array
{
    $formula = $this->getDiceFormula();
    $total = 0;
    $details = [];
    $allRolls = [];

    foreach ($formula['dice'] as $die) {
        $nb = $die['nb'];
        $faces = $die['faces'];
        $rolls = [];

        for ($i = 0; $i < $nb; $i++) {
            $roll = rand(1, $faces);
            $rolls[] = $roll;
            $total += $roll;
        }

        $allRolls[] = [
            'dice' => "{$nb}d{$faces}",
            'rolls' => $rolls,
            'sum' => array_sum($rolls)
        ];

        $details[] = "{$nb}d{$faces}: [" . implode(', ', $rolls) . "] = " . array_sum($rolls);
    }

    return [
        'total' => $total,
        'formula' => $formula['formula'],
        'details' => implode(' + ', $details),
        'rolls' => $allRolls
    ];
}

/**
 * Retourne la puissance effective du scanner (base + bonus temporaire)
 */
public function getPuissanceScanEffective(): int
{
    return $this->puissance_scan + $this->bonus_scan;
}
```

### 8.3 Jet de compétence

**Dans `Personnage.php` :**

```php
/**
 * Lance un jet de compétence pour le scan
 *
 * @param string $competence 'finesse' ou 'savoir'
 * @param int $difficulte Difficulté du jet (12-25)
 * @return array ['succes' => bool, 'espoir' => int, 'peur' => int, 'total' => int, 'marge' => int, 'dice_type' => int, 'complication' => bool]
 */
public function lancerJetScan(string $competence, int $difficulte): array
{
    $modificateur = $this->{$competence} ?? 0;

    // Lancer 2d12
    $deEspoir = rand(1, 12);
    $dePeur = rand(1, 12);
    $total = $deEspoir + $dePeur + $modificateur;

    $succes = $total >= $difficulte;
    $marge = $total - $difficulte;
    $complication = false;

    // Déterminer le TYPE de dé de bonus/malus
    if ($succes) {
        // Réussite - déterminer le dé selon la marge
        if ($marge <= 2) {
            $this->scan_bonus_dice_type = 4;  // 1d4
        } elseif ($marge <= 5) {
            $this->scan_bonus_dice_type = 6;  // 1d6
        } elseif ($marge <= 8) {
            $this->scan_bonus_dice_type = 8;  // 1d8
        } elseif ($marge <= 11) {
            $this->scan_bonus_dice_type = 10; // 1d10
        } else {
            $this->scan_bonus_dice_type = 12; // 1d12
        }

        // Complication mineure si Peur > Espoir
        if ($dePeur > $deEspoir) {
            $complication = true;
        }
    } else {
        // Échec - malus de 1d6
        $this->scan_bonus_dice_type = -6;
    }

    $this->save();

    return [
        'succes' => $succes,
        'espoir' => $deEspoir,
        'peur' => $dePeur,
        'total' => $total,
        'modificateur' => $modificateur,
        'difficulte' => $difficulte,
        'marge' => $marge,
        'dice_type' => $this->scan_bonus_dice_type,
        'dice_label' => $this->getScanBonusDiceLabel(),
        'complication' => $complication,
    ];
}

/**
 * Retourne le label du dé de bonus/malus
 */
public function getScanBonusDiceLabel(): string
{
    if ($this->scan_bonus_dice_type == 0) {
        return 'Aucun';
    } elseif ($this->scan_bonus_dice_type < 0) {
        return '-1d' . abs($this->scan_bonus_dice_type);
    } else {
        return '+1d' . $this->scan_bonus_dice_type;
    }
}

/**
 * Lance le dé de bonus/malus (appelé à chaque scan)
 */
public function lancerBonusScan(): int
{
    if ($this->scan_bonus_dice_type == 0) {
        return 0;
    } elseif ($this->scan_bonus_dice_type < 0) {
        // Malus
        return -rand(1, abs($this->scan_bonus_dice_type));
    } else {
        // Bonus
        return rand(1, $this->scan_bonus_dice_type);
    }
}

/**
 * Réinitialise le dé de bonus de scan (quand le vaisseau change de position ou refait un jet)
 */
public function reinitialiserBonusScan(): void
{
    $this->scan_bonus_dice_type = 0;
    $this->save();
}
```

### 8.4 Contrôleur de scan

**Dans `ScanController.php` :**

```php
/**
 * Scan simple (sans jet de compétence)
 */
public function scanSimple(Request $request)
{
    $personnage = $request->attributes->get('personnage');
    $vaisseau = $personnage->vaisseauActif;

    if (!$vaisseau) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être à bord d\'un vaisseau pour scanner.'
        ], 400);
    }

    // Position du vaisseau
    $vaisseauX = $vaisseau->scan_secteur_x + $vaisseau->scan_position_x;
    $vaisseauY = $vaisseau->scan_secteur_y + $vaisseau->scan_position_y;
    $vaisseauZ = $vaisseau->scan_secteur_z + $vaisseau->scan_position_z;

    // Récupérer les systèmes dans la portée
    $portee = $vaisseau->portee_scan;
    $systemes = SystemeStellaire::where('poi_connu', false)->get()->filter(function($systeme) use ($vaisseauX, $vaisseauY, $vaisseauZ, $portee) {
        $sysX = $systeme->secteur_x + $systeme->position_x;
        $sysY = $systeme->secteur_y + $systeme->position_y;
        $sysZ = $systeme->secteur_z + $systeme->position_z;

        $distance = sqrt(
            pow($sysX - $vaisseauX, 2) +
            pow($sysY - $vaisseauY, 2) +
            pow($sysZ - $vaisseauZ, 2)
        );

        return $distance <= $portee;
    });

    $resultats = [];

    foreach ($systemes as $systeme) {
        // Lancer les dés
        $jet = $vaisseau->lancerDesScan();

        // Score de détection
        $scoreDetection = $systeme->getScoreDetection($vaisseauX, $vaisseauY, $vaisseauZ);

        // Récupérer ou créer la progression
        $progress = ScanProgress::firstOrCreate([
            'personnage_id' => $personnage->id,
            'systeme_stellaire_id' => $systeme->id,
        ], [
            'score_detection' => $scoreDetection,
            'cumul_scans' => 0,
            'nb_scans_effectues' => 0,
        ]);

        // Mise à jour de la progression - CUMUL des jets
        $progress->nb_scans_effectues++;
        $progress->cumul_scans += $jet['total'];

        // Détection ?
        if ($progress->cumul_scans >= $scoreDetection && !$progress->detecte) {
            $progress->detecte = true;
            $systeme->poi_connu = true;
            $systeme->save();

            $resultats[] = [
                'systeme' => $systeme->nom_commun ?? $systeme->nom,
                'detecte' => true,
                'jet' => $jet,
                'score_requis' => $scoreDetection,
                'cumul' => $progress->cumul_scans,
            ];
        } else {
            $resultats[] = [
                'systeme' => $systeme->nom_commun ?? $systeme->nom,
                'detecte' => false,
                'jet' => $jet,
                'score_requis' => $scoreDetection,
                'cumul' => $progress->cumul_scans,
            ];
        }

        $progress->save();
    }

    return response()->json(['resultats' => $resultats]);
}

/**
 * Scan avec jet de Réglage (Finesse)
 */
public function scanAvecReglage(Request $request)
{
    $personnage = $request->attributes->get('personnage');
    $vaisseau = $personnage->vaisseauActif;

    if (!$vaisseau) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être à bord d\'un vaisseau pour scanner.'
        ], 400);
    }

    // Difficulté du jet
    $difficulte = $request->input('difficulte', config('game.scan.difficulte_reglage', 12));

    // Jet de compétence finesse
    $jetCompetence = $personnage->lancerJetScan('finesse', $difficulte);

    // Lancer les dés du scanner
    $scanResult = $vaisseau->lancerDesScan();

    // Lancer le dé de bonus (relancé à chaque scan)
    $bonusResult = $personnage->lancerBonusScan();
    $bonusLabel = $personnage->getScanBonusDiceLabel();

    // Total du scan
    $totalScan = $scanResult['total'] + $bonusResult;

    // Trouver les objets détectables et mettre à jour les progrès
    // (voir implémentation complète dans ScanController.php)

    return response()->json([
        'success' => true,
        'jet_competence' => $jetCompetence,
        'scan' => $scanResult,
        'bonus' => [
            'resultat' => $bonusResult,
            'formule' => $bonusLabel,
        ],
        'total' => $totalScan,
        'objets_detectes' => $resultats['nouveaux_detectes'],
        'progres' => $resultats['progres'],
    ]);
}

/**
 * Scan avec jet d'Astronomie (Savoir)
 */
public function scanAvecAstro(Request $request)
{
    $personnage = $request->attributes->get('personnage');
    $vaisseau = $personnage->vaisseauActif;

    if (!$vaisseau) {
        return response()->json(['error' => 'Pas de vaisseau'], 400);
    }

    // Calculer la difficulté basée sur la distance moyenne
    $vaisseauX = $vaisseau->scan_secteur_x + $vaisseau->scan_position_x;
    $vaisseauY = $vaisseau->scan_secteur_y + $vaisseau->scan_position_y;
    $vaisseauZ = $vaisseau->scan_secteur_z + $vaisseau->scan_position_z;

    $portee = $vaisseau->portee_scan;
    $systemes = SystemeStellaire::where('poi_connu', false)->get()->filter(function($systeme) use ($vaisseauX, $vaisseauY, $vaisseauZ, $portee) {
        $sysX = $systeme->secteur_x + $systeme->position_x;
        $sysY = $systeme->secteur_y + $systeme->position_y;
        $sysZ = $systeme->secteur_z + $systeme->position_z;

        $distance = sqrt(
            pow($sysX - $vaisseauX, 2) +
            pow($sysY - $vaisseauY, 2) +
            pow($sysZ - $vaisseauZ, 2)
        );

        return $distance <= $portee;
    });

    if ($systemes->isEmpty()) {
        return response()->json(['error' => 'Aucun système à scanner'], 400);
    }

    // Calculer distance moyenne
    $distanceMoyenne = $systemes->avg(function($systeme) use ($vaisseauX, $vaisseauY, $vaisseauZ) {
        $sysX = $systeme->secteur_x + $systeme->position_x;
        $sysY = $systeme->secteur_y + $systeme->position_y;
        $sysZ = $systeme->secteur_z + $systeme->position_z;

        return sqrt(
            pow($sysX - $vaisseauX, 2) +
            pow($sysY - $vaisseauY, 2) +
            pow($sysZ - $vaisseauZ, 2)
        );
    });

    // Difficulté basée sur la distance
    $difficulte = match(true) {
        $distanceMoyenne <= 2 => 12,
        $distanceMoyenne <= 5 => 15,
        $distanceMoyenne <= 10 => 18,
        $distanceMoyenne <= 20 => 21,
        default => 25,
    };

    // Jet de compétence
    $jetCompetence = $personnage->lancerJetScan($request->competence, $difficulte);

    // Ensuite, même logique que scanStandard mais avec le bonus
    $resultats = [];

    foreach ($systemes as $systeme) {
        $jet = $vaisseau->lancerDesScan();

        // Lancer le dé de bonus (relancé à chaque scan)
        $bonusRoll = $personnage->lancerBonusScan();
        $jetTotal = $jet['total'] + $bonusRoll;

        $scoreDetection = $systeme->getScoreDetection($vaisseauX, $vaisseauY, $vaisseauZ);

        $progress = ScanProgress::firstOrCreate([
            'personnage_id' => $personnage->id,
            'systeme_stellaire_id' => $systeme->id,
        ], [
            'score_detection' => $scoreDetection,
            'cumul_scans' => 0,
            'nb_scans_effectues' => 0,
            'bonus_dice_type' => $personnage->scan_bonus_dice_type,
        ]);

        $progress->nb_scans_effectues++;
        $progress->bonus_dice_type = $personnage->scan_bonus_dice_type;

        // CUMUL des jets
        $progress->cumul_scans += $jetTotal;

        if ($progress->cumul_scans >= $scoreDetection && !$progress->detecte) {
            $progress->detecte = true;
            $systeme->poi_connu = true;
            $systeme->save();

            $resultats[] = [
                'systeme' => $systeme->nom_commun ?? $systeme->nom,
                'detecte' => true,
                'jet_scanner' => $jet,
                'bonus_dice' => $personnage->getScanBonusDiceLabel(),
                'bonus_roll' => $bonusRoll,
                'jet_total' => $jetTotal,
                'score_requis' => $scoreDetection,
                'cumul' => $progress->cumul_scans,
            ];
        } else {
            $resultats[] = [
                'systeme' => $systeme->nom_commun ?? $systeme->nom,
                'detecte' => false,
                'jet_scanner' => $jet,
                'bonus_dice' => $personnage->getScanBonusDiceLabel(),
                'bonus_roll' => $bonusRoll,
                'jet_total' => $jetTotal,
                'score_requis' => $scoreDetection,
                'cumul' => $progress->cumul_scans,
            ];
        }

        $progress->save();
    }

    return response()->json([
        'jet_competence' => $jetCompetence,
        'resultats' => $resultats,
    ]);
}
```

---

## 9. Exemples de gameplay

### 9.1 Système Solaire (POI connu, référence)

**Objets dans le Système Solaire :**

| Planète  | Distance (UA) | Rayon (R⊕) | Détectabilité | Score (distance/10 × déte) | Scans (puissance 10) |
|----------|---------------|------------|---------------|---------------------------|---------------------|
| Mercure  | 0.39          | 0.38       | 96.96         | 3.78                      | 1                   |
| Vénus    | 0.72          | 0.95       | 92.40         | 6.65                      | 1                   |
| Terre    | 1.0           | 1.0        | 92.00         | 9.20                      | 2                   |
| Mars     | 1.52          | 0.53       | 95.76         | 14.56                     | 2                   |
| Jupiter  | 5.2           | 11.2       | 10.40         | 5.41                      | 1                   |
| Saturne  | 9.54          | 9.45       | 24.40         | 23.28                     | 4                   |
| Uranus   | 19.19         | 4.0        | 68.00         | 130.49                    | 18                  |
| Neptune  | 30.07         | 3.88       | 68.96         | 207.35                    | 28                  |

**Avec scanner puissance 50 (moyenne 24) :**
- Mercure, Vénus, Terre, Mars, Jupiter : 1 scan
- Saturne : 1-2 scans
- Uranus : 6 scans
- Neptune : 9 scans

### 9.2 Systèmes stellaires proches (Alpha Centauri, etc.)

**Systèmes proches du Soleil :**

| Système         | Distance (AL) | Type | Puissance | Détectabilité | Score | Scans (puissance 10) | Scans (puissance 50) |
|-----------------|---------------|------|-----------|---------------|-------|---------------------|---------------------|
| Alpha Centauri  | 4.37          | G    | 50        | 50.00         | 21.85 | 3                   | 1                   |
| Barnard's Star  | 5.96          | M    | 25        | 58.33         | 34.76 | 5                   | 2                   |
| Wolf 359        | 7.86          | M    | 20        | 60.00         | 47.16 | 7                   | 2                   |
| Lalande 21185   | 8.29          | M    | 25        | 58.33         | 48.37 | 7                   | 3                   |
| Sirius          | 8.58          | A    | 85        | 38.33         | 32.89 | 5                   | 2                   |

### 9.3 Progression typique avec scan_jet

**Scénario :** Scanner Alpha Centauri (distance 4.37 AL, score de détection = 21.85)

**Vaisseau de départ** : puissance_scan = 10 (3d4, moyenne 7.5)

**Personnage** : Finesse = 2

**Distance 4.37 AL → Difficulté 15 (Modéré)**

---

**JET DE COMPÉTENCE (fait une seule fois au début) :**
- Compétence : Finesse (+2)
- 2d12 + 2 = [11, 7] + 2 = 20 → **Réussite !**
- Difficulté : 15 → **Marge = 5**
- Marge 3-5 → **Dé de bonus = 1d6** (relancé à chaque scan)

---

**SCANS (avec 1d6 de bonus relancé à chaque fois) :**

**Scan 1 :**
- Scanner : 3d4 = [3, 2, 4] = 9
- Bonus : 1d6 = **4**
- Total : 9 + 4 = **13**
- Cumul : **13** / 21.85 → Pas encore détecté

**Scan 2 :**
- Scanner : 3d4 = [4, 3, 2] = 9
- Bonus : 1d6 = **2** (nouveau jet du 1d6)
- Total : 9 + 2 = **11**
- Cumul : 13 + 11 = **24** / 21.85 → **DÉTECTÉ !**

---

**Résultat :** 2 scans pour détecter avec scan_jet (+ 1 jet de compétence au début)

**Comparaison avec scan standard (sans jet de compétence) :**
- Moyenne de 3d4 = 7.5
- Il faudrait environ **21.85 / 7.5 ≈ 3 scans** en moyenne
- Avec variance, **2-4 scans** seraient nécessaires

**Avantage du jet de compétence :**
- Le dé de bonus (1d6 dans cet exemple, moyenne +3.5) réduit le nombre de scans nécessaires
- Meilleure marge = meilleur dé de bonus (1d4 → 1d6 → 1d8 → 1d10 → 1d12)
- Risque : si le jet échoue, on a un malus de -1d6 à chaque scan

---

## 10. Configuration

### 10.1 Fichier `config/game.php`

```php
return [
    // ... existing config ...

    'scan' => [
        // Système de détection v2.0
        'detection_formula' => 'distance_div_10_times_detectabilite',

        // Compétences
        'competences' => ['finesse', 'savoir'],

        // Difficulté par distance (AL)
        'difficultes' => [
            ['max' => 2, 'valeur' => 12, 'nom' => 'Facile'],
            ['max' => 5, 'valeur' => 15, 'nom' => 'Modéré'],
            ['max' => 10, 'valeur' => 18, 'nom' => 'Difficile'],
            ['max' => 20, 'valeur' => 21, 'nom' => 'Très difficile'],
            ['max' => 9999, 'valeur' => 25, 'nom' => 'Héroïque'],
        ],

        // Progression du dé de bonus selon la marge de réussite
        'bonus_dice_progression' => [
            ['marge_min' => 0, 'marge_max' => 2, 'dice' => 4],   // 1d4
            ['marge_min' => 3, 'marge_max' => 5, 'dice' => 6],   // 1d6
            ['marge_min' => 6, 'marge_max' => 8, 'dice' => 8],   // 1d8
            ['marge_min' => 9, 'marge_max' => 11, 'dice' => 10], // 1d10
            ['marge_min' => 12, 'marge_max' => 999, 'dice' => 12], // 1d12
        ],

        // Malus en cas d'échec
        'malus_dice' => 6, // -1d6
    ],
];
```

---

## 11. Routes et commandes

### 11.1 Routes web/API

**Dans `routes/web.php` :**

```php
Route::middleware(['auth', 'personnage'])->group(function () {
    // Scan simple (sans jet de compétence)
    Route::post('/navire/scan/simple', [ScanController::class, 'scanSimple'])
        ->name('navire.scan.simple');

    // Scan avec jet de Réglage (Finesse)
    Route::post('/navire/scan/reglage', [ScanController::class, 'scanAvecReglage'])
        ->name('navire.scan.reglage');

    // Scan avec jet d'Astronomie (Savoir)
    Route::post('/navire/scan/astro', [ScanController::class, 'scanAvecAstro'])
        ->name('navire.scan.astro');

    // Réinitialiser le bonus de scan
    Route::post('/navire/scan/reset-bonus', [ScanController::class, 'reinitialiserBonus'])
        ->name('navire.scan.reset-bonus');

    // Lister les scans en cours
    Route::get('/navire/scan/liste', [ScanController::class, 'listeScans'])
        ->name('navire.scan.liste');
});
```

### 11.2 Commandes Artisan (optionnel)

**Commande pour tester le scan en CLI :**

```bash
php artisan game:scan {personnage_id} [--avec-jet] [--competence=finesse]
```

---

## 12. Tests et équilibrage

### 12.1 Objectifs d'équilibrage

**Systèmes stellaires proches (1-4 AL) :**
- Scanner de base (puissance 10) : 6-16 scans
- Scanner amélioré (puissance 50) : 1-4 scans
- Avec jets de compétence et bonus : réduction de 20-40%

**Planètes dans un système :**
- Planètes proches (< 5 UA) : 1-5 scans
- Planètes moyennes (5-20 UA) : 5-15 scans
- Planètes lointaines (> 20 UA) : 15-60 scans

### 12.2 Tests unitaires

**Tester le calcul de score de détection :**

```php
public function test_score_detection_systeme()
{
    $systeme = SystemeStellaire::factory()->create([
        'puissance' => 50,
        'detectabilite_base' => 50.0,
        'secteur_x' => 0,
        'secteur_y' => 0,
        'secteur_z' => 0,
        'position_x' => 4.37,
        'position_y' => 0,
        'position_z' => 0,
    ]);

    $score = $systeme->getScoreDetection(0, 0, 0);

    // (4.37 / 10) × 50 = 21.85
    $this->assertEqualsWithDelta(21.85, $score, 0.01);
}
```

**Tester la formule de dés :**

```php
public function test_dice_formula_progression()
{
    $vaisseau = Vaisseau::factory()->create(['puissance_scan' => 10, 'bonus_scan' => 0]);
    $formula = $vaisseau->getDiceFormula();
    $this->assertEquals('3d4', $formula['formula']);

    $vaisseau->puissance_scan = 50;
    $formula = $vaisseau->getDiceFormula();
    $this->assertEquals('2d12+2d10', $formula['formula']);
}
```

---

## 13. Interface utilisateur

### 13.1 Affichage du scanner

**Dans la vue du vaisseau :**

```blade
<div class="scanner-info">
    <h3>Scanner</h3>
    <p>Puissance: {{ $vaisseau->puissance_scan }}{{ $vaisseau->bonus_scan > 0 ? " + {$vaisseau->bonus_scan}" : '' }}</p>
    <p>Portée: {{ $vaisseau->portee_scan }} AL</p>
    <p>Formule de dés: {{ $vaisseau->getDiceFormula()['formula'] }}</p>

    @if($personnage->scan_bonus_actuel != 0)
        <p class="bonus-actuel {{ $personnage->scan_bonus_actuel > 0 ? 'text-green' : 'text-red' }}">
            Bonus actuel: {{ $personnage->scan_bonus_actuel > 0 ? '+' : '' }}{{ $personnage->scan_bonus_actuel }}
        </p>
    @endif
</div>
```

### 13.2 Boutons de scan (Console droite)

**Implémentation actuelle dans `resources/views/game/partials/console.blade.php` :**

```blade
<!-- Trois boutons de scan dans la console -->
<div class="grid grid-cols-3 gap-2">
    <button onclick="effectuerScan('simple')"
            class="bg-purple-600/80 hover:bg-purple-600 border border-purple-500/50 px-2 py-1.5 rounded text-xs text-white transition"
            title="Scan simple: uniquement les dés du scanner">
        📡 Scan
    </button>
    <button onclick="effectuerScan('reglage')"
            class="bg-cyan-600/80 hover:bg-cyan-600 border border-cyan-500/50 px-2 py-1.5 rounded text-xs text-white transition"
            title="Scan + jet de Finesse pour bonus">
        🔧 Scan & Réglage
    </button>
    <button onclick="effectuerScan('astro')"
            class="bg-blue-600/80 hover:bg-blue-600 border border-blue-500/50 px-2 py-1.5 rounded text-xs text-white transition"
            title="Scan + jet de Savoir pour bonus">
        🌟 Scan & Astro
    </button>
</div>
```

**Les boutons sont placés dans la console droite** de l'interface, pas dans le contenu principal. Cela permet au joueur d'accéder rapidement aux scans depuis n'importe quelle page du jeu.

### 13.3 Résultats de scan (Console)

**Implémentation actuelle dans `console.blade.php` :**

```javascript
async function effectuerScan(type) {
    const routes = {
        'simple': '{{ route("navire.scan.simple") }}',
        'reglage': '{{ route("navire.scan.reglage") }}',
        'astro': '{{ route("navire.scan.astro") }}'
    };

    const route = routes[type];
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        appendToConsole('> Lancement du scan...', 'text-cyan-400');

        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        // Afficher le titre
        const titres = {
            'simple': '📡 SCAN SIMPLE',
            'reglage': '🔧 SCAN + RÉGLAGE',
            'astro': '🌟 SCAN + ASTRO'
        };
        appendToConsole('═══════════════════════════════', 'text-purple-400');
        appendToConsole(titres[type], 'text-purple-400');
        appendToConsole('═══════════════════════════════', 'text-purple-400');

        // Jet de compétence (si applicable)
        if (data.jet_competence) {
            const jet = data.jet_competence;
            appendToConsole('', 'text-gray-500');
            appendToConsole('JET DE COMPÉTENCE:', 'text-cyan-300');
            const resultat = jet.succes ? '✓ Réussite' : '✗ Échec';
            const couleur = jet.succes ? 'text-green-400' : 'text-red-400';
            appendToConsole(resultat, couleur);
            appendToConsole(`Jet: ${jet.espoir} (Espoir) + ${jet.peur} (Peur) + ${jet.modificateur} = ${jet.total} vs ${jet.difficulte}`, 'text-gray-300');
            appendToConsole(`Marge: ${jet.marge >= 0 ? '+' : ''}${jet.marge}`, 'text-gray-300');
            appendToConsole(`Dé bonus: ${jet.dice_label}`, 'text-cyan-400');
            if (jet.complication) {
                appendToConsole('⚠️ Complication (Peur > Espoir)', 'text-yellow-400');
            }
        }

        // Résultat du scan
        appendToConsole('', 'text-gray-500');
        appendToConsole('SCAN:', 'text-cyan-300');
        appendToConsole(`Formule: ${data.scan.formula}`, 'text-purple-400');
        appendToConsole(`Détails: ${data.scan.details}`, 'text-gray-300');

        // Bonus de compétence
        if (data.bonus && typeof data.bonus === 'object') {
            appendToConsole(`Bonus: ${data.bonus.formule} = ${data.bonus.resultat}`, 'text-cyan-400');
        }

        const totalJet = data.total || data.scan.total;
        appendToConsole(`Total du jet : ${totalJet}`, 'text-purple-300');

        // Afficher le CUMUL (maximum cumul_apres)
        if (data.progres && data.progres.length > 0) {
            const maxCumul = Math.max(...data.progres.map(p => p.cumul_apres));
            appendToConsole(`CUMUL: ${maxCumul.toFixed(1)}`, 'text-cyan-300');
        }

        // Objets nouvellement détectés
        if (data.objets_detectes && data.objets_detectes.length > 0) {
            appendToConsole('', 'text-gray-500');
            appendToConsole('🎉 NOUVEAUX OBJETS DÉTECTÉS !', 'text-green-400');
            data.objets_detectes.forEach(obj => {
                appendToConsole(`• ${obj.type}: ${obj.nom}`, 'text-green-300');
            });
        }

        // TOUS les objets scannés (format simplifié)
        if (data.progres && data.progres.length > 0) {
            appendToConsole('', 'text-gray-500');
            appendToConsole('OBJETS SCANNÉS:', 'text-cyan-300');

            data.progres.forEach(p => {
                const couleur = p.detecte ? 'text-green-400' :
                              p.pourcentage > 75 ? 'text-yellow-400' :
                              p.pourcentage > 25 ? 'text-orange-400' : 'text-gray-400';
                const detecte = p.nouveau_detecte ? ' 🎉 DÉTECTÉ!' : '';
                const etat = p.detecte ? ' ✓' : '';

                // Format simplifié: Type: Nom score_requis (pourcentage%)
                // Le cumul est affiché en haut dans "CUMUL:", pas besoin de le répéter ici
                appendToConsole(
                    `${p.type}: ${p.nom} ${p.score_requis.toFixed(1)} (${p.pourcentage}%)${etat}${detecte}`,
                    couleur
                );
            });
        }

        appendToConsole('', 'text-gray-500');
        appendToConsole(`${data.progres ? data.progres.length : 0} objet(s) scanné(s)`, 'text-gray-500');
        appendToConsole('═══════════════════════════════', 'text-purple-400');

    } catch (error) {
        appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
    }
}
</script>
```

**Affichage dans la console :**
- Les résultats s'affichent directement dans la console droite
- Affichage du "Total du jet" suivi du "CUMUL" (cumul maximal des objets scannés)
- Format simplifié pour les objets : `Type: Nom score_requis (pourcentage%)`
- Couleurs adaptées selon la progression (vert = détecté, jaune = proche, orange = moyen, gris = loin)
- Mise en évidence des nouveaux objets détectés avec emoji 🎉
- Le cumul individuel n'est plus affiché par objet (redondant avec le CUMUL global)

---

## 14. Évolutions futures

### 14.1 Améliorations possibles

1. **Équipements de scan spécialisés** :
   - Scanner longue portée (portée +50%, puissance -20%)
   - Scanner haute résolution (portée -30%, puissance +40%)
   - Scanner quantique (bonus fixe +1d6)

2. **Compétences supplémentaires** :
   - **Intuition** : Réduire la difficulté de 2
   - **Patience** : Relancer un dé défavorable
   - **Analyse** : Voir le score de détection exact avant de scanner

3. **Événements aléatoires** :
   - Interférence stellaire : difficulté +3 pour ce scan
   - Fenêtre favorable : bonus +1d6 gratuit
   - Anomalie gravitationnelle : détection automatique d'un POI proche

4. **Missions de scan** :
   - "Cartographier le secteur X" : détecter N systèmes dans une zone
   - "Trouver une planète habitable" : détecter une planète de type terrestre
   - "Localiser l'anomalie" : détecter un POI spécifique

### 14.2 Optimisations techniques

1. **Cache des scores de détection** : Précalculer et mettre en cache les scores pour chaque vaisseau
2. **Batch scanning** : Scanner plusieurs objets en un seul appel API
3. **Background jobs** : Scanner en arrière-plan pendant que le joueur fait autre chose

---

## 15. Changelog

**v2.0 (2025-12-28) :**
- Remplacement complet du système de points de tâche
- Nouvelle formule simplifiée : `(distance / 10) × detectabilite_base`
- Système de progression des dés (21 paliers)
- Intégration du système de jets de compétence Daggerheart
- Mécanique Espoir/Peur avec bonus/malus cumulatifs
- Trois modes de scan : simple, avec Réglage (Finesse), avec Astronomie (Savoir)
- Le dé de bonus est RELANCÉ à chaque scan (pas un bonus fixe)
- Les résultats s'accumulent entre tous les types de scan
- Ajout automatique des objets détectés à la spatiocarte
- Système polymorphique pour scanner différents types d'objets (systèmes, planètes, stations, mines)

**v1.x (obsolète) :**
- Voir `GDD_Systeme_Decouverte.md` (obsolète)
- Voir `SYSTEME_DETECTION_SCAN.md` (obsolète)

---

## 16. Références

- **Daggerheart SRD** : https://daggerheart.com/srd
- **NASA Exoplanet Archive** : https://exoplanetarchive.ipac.caltech.edu/
- **GAIA Mission** : https://www.cosmos.esa.int/web/gaia
- **Documentation Laravel** : https://laravel.com/docs

---

**FIN DU DOCUMENT**
