# ðŸ”­ SYSTÃˆME DE DÃ‰COUVERTE
## Jeu de ConquÃªte Galactique

---

## âš ï¸ DISCLAIMER
Algorithme de recherche et dÃ©couverte des systÃ¨mes stellaires (PoV brillants).

---

## ðŸŽ¯ Principe Fondamental

**BasÃ© sur la puissance solaire** (avec un minimum de 10).

**IdÃ©e centrale :**
> Plus le joueur cherche de systÃ¨mes, plus il a de chances de voir :
> - Les moins gros
> - Les plus distants
> - Ceux qui peuvent Ãªtre cachÃ©s

---

## ðŸ” MÃ©canisme de Recherche

### Principe

Pour **1 PA**, le systÃ¨me informatique d'un vaisseau peut lancer une recherche d'un secteur ayant un soleil (ou quelque chose).

**RÃ©pÃ©tition :**
- Peut rÃ©pÃ©ter le calcul tant qu'il ne fait pas d'autres actions incompatibles
- GÃ©nÃ©ralement : **se dÃ©placer** est incompatible

**DÃ©couverte :**
- Il faut cumuler un certain nombre de **points de tÃ¢che**
- Quand le seuil est atteint â†’ secteur est **"dÃ©couvert"**

---

## ðŸ“ Formules

### 1. Seuil de DÃ©couverte

```
Seuil = 500 + (Distance Ã— 100)
```

**OÃ¹ :**
- **Distance** : Distance entre position actuelle et secteur cible (en AL ou UA selon Ã©chelle)
- Constante 500 = base de difficultÃ©

**Exemple :**
```
Distance = 4.245
Seuil = 500 + (4.245 Ã— 100) = 924.5 â‰ˆ 925
```

---

### 2. Bonus/Malus DÃ©couverte (Points de TÃ¢che Initiaux)

```
Points de TÃ¢che Initiaux = PSol + (6 - Distance) Ã— 10
```

**OÃ¹ :**
- **PSol** : Puissance solaire du systÃ¨me cible
- **6** : Constante correspondant Ã  un saut maximum (portÃ©e max recherche)
- **Distance** : Distance au systÃ¨me

**Logique :**
- SystÃ¨me proche et puissant â†’ Points initiaux Ã©levÃ©s
- SystÃ¨me lointain et faible â†’ Points initiaux faibles (voire nÃ©gatifs)

**Exemple :**
```
PSol = 50
Distance = 4.245
Points initiaux = 50 + (6 - 4.245) Ã— 10
                = 50 + (1.755 Ã— 10)
                = 50 + 17.55
                = 67.55
```

---

### 3. LancÃ© du Calcul (par PA)

```
LancÃ© = (SysExpl) D (2 Ã— PSol)
```

**OÃ¹ :**
- **SysExpl** : SystÃ¨me informatique d'exploration (1 Ã  10)
- **D** : DÃ©
- **PSol** : Puissance solaire (minimum 10)

**Traduction :**
- Lancer **SysExpl** dÃ©s de **(2 Ã— PSol)** faces
- Additionner les rÃ©sultats
- Ajouter cette valeur aux points de tÃ¢che cumulÃ©s

**Minimum :**
- Si PSol < 5 â†’ utiliser PSol = 5 (donc 2 Ã— 5 = D10 minimum)

**Exemple :**
```
SysExpl = 1
PSol = 50
LancÃ© par PA = 1D100 (car 2 Ã— 50 = 100)
```

---

## ðŸ“Š Exemple Complet

### Situation

**SystÃ¨me cible :**
- PSol = 50
- Distance = 4.245 AL

**Vaisseau joueur :**
- SysExpl = 1 (explorateur dÃ©butant)

### Calculs

**1. Seuil de dÃ©couverte :**
```
Seuil = 500 + (4.245 Ã— 100) = 924.5 â‰ˆ 925
```

**2. Points de tÃ¢che initiaux :**
```
Points initiaux = 50 + (6 - 4.245) Ã— 10 = 67.55 â‰ˆ 68
```

**3. LancÃ© par PA :**
```
1D100 par PA dÃ©pensÃ©
```

**4. Estimation :**
```
Points restants Ã  gagner = 925 - 68 = 857
Moyenne par lancÃ© (1D100) = 50.5
Nombre PA estimÃ© = 857 / 50.5 â‰ˆ 17 PA
```

**RÃ©sultat :** DÃ©tection en environ **17-18 PA** pour un explorateur dÃ©butant.

---

## ðŸš€ Impact SystÃ¨me Exploration

### Niveau SysExpl (1-10)

**Comparaison :**

| SysExpl | LancÃ©/PA | Moyenne/PA | PA estimÃ©s (ex ci-dessus) |
|---------|----------|------------|---------------------------|
| 1 | 1D100 | 50.5 | ~17 PA |
| 3 | 3D100 | 151.5 | ~6 PA |
| 5 | 5D100 | 252.5 | ~3-4 PA |
| 10 | 10D100 | 505 | ~2 PA |

**Conclusion :**
- SysExpl Ã©levÃ© = dÃ©tection beaucoup plus rapide
- Investir dans exploration = rentable pour dÃ©couvrir nouveaux systÃ¨mes

---

## ðŸŒŸ Facteurs InfluenÃ§ant DÃ©couverte

### 1. Puissance Solaire (PSol)

**Effet :**
- PSol Ã©levÃ© â†’ Plus facile Ã  dÃ©tecter
- PSol faible â†’ Plus difficile

**Exemples types :**
- GÃ©ante bleue (Type O) : PSol 150-200 â†’ TrÃ¨s facile
- Naine rouge (Type M) : PSol 20-30 â†’ TrÃ¨s difficile

---

### 2. Distance

**Effet :**
- Distance faible â†’ Bonus points initiaux Ã©levÃ©
- Distance Ã©levÃ©e â†’ Malus (points initiaux faibles voire nÃ©gatifs)

**Exemple :**
```
PSol = 30 (naine rouge)
Distance = 5.5 AL (au-delÃ  saut max 6)

Points initiaux = 30 + (6 - 5.5) Ã— 10
                = 30 + 5
                = 35

Seuil = 500 + (5.5 Ã— 100) = 1050

â†’ TrÃ¨s difficile Ã  dÃ©tecter !
```

---

### 3. Ã‰quipement

**SystÃ¨me Informatique Exploration (SysExpl) :**
- Modules amÃ©liorÃ©s
- Programmes spÃ©cialisÃ©s
- Upgrades vaisseau

**Bonus possibles :**
- Antennes amÃ©liorÃ©es
- Senseurs longue portÃ©e
- IA analyse avancÃ©e

---

## ðŸ”„ Algorithme de Recherche

### Environnement

**DonnÃ©es nÃ©cessaires :**
- Position systÃ¨me initial de recherche (vaisseau)
- Connaissance distance maximum du saut (portÃ©e)
- OU distance zone d'effet

---

### Calcul (CÃ´tÃ© Serveur)

**Ã‰tape 1 : SÃ©lection secteurs**
```sql
SELECT 
    secteur_id,
    coord_x, coord_y, coord_z,
    PSol,
    SQRT(
        POWER(coord_x - vaisseau_x, 2) +
        POWER(coord_y - vaisseau_y, 2) +
        POWER(coord_z - vaisseau_z, 2)
    ) AS distance
FROM secteurs
WHERE distance <= portee_max
  AND PSol >= 10
  AND non_decouvert_par_joueur
ORDER BY distance ASC;
```

**Ã‰tape 2 : Calcul seuil et points initiaux**
Pour chaque secteur :
```
seuil[i] = 500 + (distance[i] Ã— 100)
points_tache[i] = PSol[i] + (6 - distance[i]) Ã— 10
```

**Ã‰tape 3 : Pour chaque PA dÃ©pensÃ©**
```
FOR EACH secteur IN liste_secteurs:
    lancÃ© = SUM( (SysExpl) D (2 Ã— PSol) )
    points_tache[secteur] += lancÃ©
    
    IF points_tache[secteur] >= seuil[secteur]:
        â†’ SECTEUR DÃ‰COUVERT !
        â†’ Retirer de la liste de recherche
        â†’ Notifier joueur
```

**Ã‰tape 4 : RÃ©sultat**
```
Liste des secteurs dÃ©tectables par le systÃ¨me
```

---

## ðŸŽ® Gameplay

### Interface Joueur

**Commande recherche :**
```
> scan_systems

Lancement scan longue portÃ©e...
PA disponibles : 10
SysExpl : 3

Recherche en cours... (1 PA dÃ©pensÃ©)
ðŸŽ² 3D100 : 45 + 78 + 23 = 146 points

Secteur Alpha-745 : 213/925 points
Secteur Beta-392 : 180/1050 points
Secteur Gamma-118 : 421/750 points

Continuer ? [Oui/Non/ArrÃªter]
```

**DÃ©couverte :**
```
> scan_systems (suite)

PA dÃ©pensÃ© : 5 (5 restants)

âœ“ SYSTÃˆME DÃ‰COUVERT !
Secteur Gamma-118 dÃ©tectÃ© !
- Type : Ã‰toile jaune (G)
- PSol : 45
- Distance : 2.5 AL
- CoordonnÃ©es : (125, -34, 88)

Ajouter aux favoris ? [Oui/Non]
Continuer recherche ? [Oui/Non]
```

---

## ðŸŒŒ SystÃ¨mes CachÃ©s

### Principe

Certains systÃ¨mes peuvent Ãªtre **cachÃ©s** :
- DerriÃ¨re nÃ©buleuse
- OccultÃ©s par autre objet
- TrÃ¨s faibles (PSol < 15)
- TrÃ¨s distants

**Modification formule :**
```
Seuil_cachÃ© = Seuil Ã— Multiplicateur_cachette

Exemples :
- NÃ©buleuse lÃ©gÃ¨re : Ã—1.5
- NÃ©buleuse dense : Ã—2.0
- Occultation : Ã—3.0
```

---

## ðŸ’¡ StratÃ©gies

### Pour Explorateurs

**Optimiser dÃ©couverte :**
1. Investir dans SysExpl Ã©levÃ© (5-10)
2. Chercher par zones (mÃ©thodique)
3. Prioriser systÃ¨mes proches d'abord
4. Revenir avec meilleur Ã©quipement pour systÃ¨mes difficiles

**Revente donnÃ©es :**
- SystÃ¨mes dÃ©couverts = vendables
- Prix selon raretÃ©/intÃ©rÃªt

---

### Pour DÃ©veloppeurs

**Ajustements possibles :**
- Modifier constante 500 (difficultÃ© base)
- Modifier constante 6 (portÃ©e max)
- Modifier multiplicateur distance (100)
- Ajouter bonus/malus selon Ã©quipement

---

## ðŸ“Š Tableau RÃ©capitulatif

### Formules ComplÃ¨tes

| Ã‰lÃ©ment | Formule | Description |
|---------|---------|-------------|
| **Seuil** | 500 + (Distance Ã— 100) | Points requis pour dÃ©couverte |
| **Points initiaux** | PSol + (6 - Distance) Ã— 10 | Avantage de dÃ©part |
| **LancÃ©/PA** | (SysExpl) D (2 Ã— PSol) | Points gagnÃ©s par PA |
| **PA estimÃ©s** | (Seuil - Points initiaux) / Moyenne lancÃ© | Estimation durÃ©e |

---

## ðŸ”® Ã‰volutions Futures

**IdÃ©es possibles :**
- Scan passif (automatique, lent)
- Scan actif (rapide, coÃ»te Ã©nergie)
- CoopÃ©ration joueurs (scan partagÃ©)
- Zones dÃ©jÃ  scannÃ©es (bonus)
- Anomalies dÃ©tectables
- Artefacts cachÃ©s

---

**Document vivant - DerniÃ¨re mise Ã  jour : 2025-11-01**
