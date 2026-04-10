# œ­ SYSTœME DE D?COUVERTE
## Jeu de Conquête Galactique

---

## ⚠️ DOCUMENT OBSOLÈTE - NE PLUS UTILISER

**Ce document est OBSOLÈTE et ne doit plus être utilisé comme référence.**

**Version actuelle : Voir [`GDD_SYSTEME_DETECTION_V2.md`](./GDD_SYSTEME_DETECTION_V2.md)**

Le système décrit ci-dessous (points de tâche) n'a jamais été implémenté et a été remplacé par un système plus simple et équilibré dans la v2.0.

---

## œ? DISCLAIMER
Algorithme de recherche et découverte des systèmes stellaires (PoV brillants).

---

## ? Principe Fondamental

**Basé sur la puissance solaire** (avec un minimum de 10).

**Idée centrale :**
> Plus le joueur cherche de systèmes, plus il a de chances de voir :
> - Les moins gros
> - Les plus distants
> - Ceux qui peuvent être cachés

---

## ? Mécanisme de Recherche

### Principe

Pour **1 PA**, le système informatique d'un vaisseau peut lancer une recherche d'un secteur ayant un soleil (ou quelque chose).

**Répétition :**
- Peut répéter le calcul tant qu'il ne fait pas d'autres actions incompatibles
- Généralement : **se déplacer** est incompatible

**Découverte :**
- Il faut cumuler un certain nombre de **points de tâche**
- Quand le seuil est atteint le secteur est **"découvert"**

---

## œ? Formules

### 1. Seuil de Découverte (faux)

```
Seuil = 500 + (Distance œ 100)
detectabilite_base = (200 - Puissance_Etoile) / 3
```

**Où :**
- **Distance** : Distance entre position actuelle et secteur cible (en AL ou UA selon échelle)
- Constante 500 = base de difficulté

**Exemple :**
```
Distance = 4.245
Seuil = 500 + (4.245 œ 100) = 924.5 ✓ 925
```

---

### 2. Bonus/Malus Découverte (Points de Tâche Initiaux)

```
Points de Tâche Initiaux = PSol + (6 - Distance) œ 10
```

**Où :**
- **PSol** : Puissance solaire du système cible
- **6** : Constante correspondant à un saut maximum (portée max recherche)
- **Distance** : Distance au système

**Logique :**
- Système proche et puissant ✓ Points initiaux élevés
- Système lointain et faible ✓ Points initiaux faibles (voire négatifs)

**Exemple :**
```
PSol = 50
Distance = 4.245
Points initiaux = 50 + (6 - 4.245) œ 10
                = 50 + (1.755 œ 10)
                = 50 + 17.55
                = 67.55
```

---

### 3. Lancé du Calcul (par PA)

```
Lancé = (SysExpl) D (2 œ PSol)
```

**Où :**
- **SysExpl** : Système informatique d'exploration (1 à 10)
- **D** : Dé
- **PSol** : Puissance solaire (minimum 10)

**Traduction :**
- Lancer **SysExpl** dés de **(2 œ PSol)** faces
- Additionner les résultats
- Ajouter cette valeur aux points de tâche cumulés

**Minimum :**
- Si PSol < 5 ✓ utiliser PSol = 5 (donc 2 œ 5 = D10 minimum)

**Exemple :**
```
SysExpl = 1
PSol = 50
Lancé par PA = 1D100 (car 2 œ 50 = 100)
```

---

## œŠ Exemple Complet

### Situation

**Système cible :**
- PSol = 50
- Distance = 4.245 AL

**Vaisseau joueur :**
- SysExpl = 1 (explorateur débutant)

### Calculs

**1. Seuil de découverte :**
```
Seuil = 500 + (4.245 œ 100) = 924.5 ✓ 925
```

**2. Points de tâche initiaux :**
```
Points initiaux = 50 + (6 - 4.245) œ 10 = 67.55 ✓ 68
```

**3. Lancé par PA :**
```
1D100 par PA dépensé
```

**4. Estimation :**
```
Points restants à gagner = 925 - 68 = 857
Moyenne par lancé (1D100) = 50.5
Nombre PA estimé = 857 / 50.5 ✓ 17 PA
```

**Résultat :** Détection en environ **17-18 PA** pour un explorateur débutant.

---

## ?š? Impact Système Exploration

### Niveau SysExpl (1-10)

**Comparaison :**

| SysExpl | Lancé/PA | Moyenne/PA | PA estimés (ex ci-dessus) |
|---------|----------|------------|---------------------------|
| 1 | 1D100 | 50.5 | ~17 PA |
| 3 | 3D100 | 151.5 | ~6 PA |
| 5 | 5D100 | 252.5 | ~3-4 PA |
| 10 | 10D100 | 505 | ~2 PA |

**Conclusion :**
- SysExpl élevé = détection beaucoup plus rapide
- Investir dans exploration = rentable pour découvrir nouveaux systèmes

---

## œŸ Facteurs Influençant Découverte

### 1. Puissance Solaire (PSol)

**Effet :**
- PSol élevé ✓ Plus facile à détecter
- PSol faible ✓ Plus difficile

**Exemples types :**
- Géante bleue (Type O) : PSol 150-200 ✓ Très facile
- Naine rouge (Type M) : PSol 20-30 ✓ Très difficile

---

### 2. Distance

**Effet :**
- Distance faible ✓ Bonus points initiaux élevé
- Distance élevée ✓ Malus (points initiaux faibles voire négatifs)

**Exemple :**
```
PSol = 30 (naine rouge)
Distance = 5.5 AL (au-delà saut max 6)

Points initiaux = 30 + (6 - 5.5) œ 10
                = 30 + 5
                = 35

Seuil = 500 + (5.5 œ 100) = 1050

✓ Très difficile à détecter !
```

---

### 3. ?quipement

**Système Informatique Exploration (SysExpl) :**
- Modules améliorés
- Programmes spécialisés
- Upgrades vaisseau

**Bonus possibles :**
- Antennes améliorées
- Senseurs longue portée
- IA analyse avancée

---

## œ? Algorithme de Recherche

### Environnement

**Données nécessaires :**
- Position système initial de recherche (vaisseau)
- Connaissance distance maximum du saut (portée)
- OU distance zone d'effet

---

### Calcul (Côté Serveur)

**?tape 1 : Sélection secteurs**
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

**?tape 2 : Calcul seuil et points initiaux**
Pour chaque secteur :
```
seuil[i] = 500 + (distance[i] œ 100)
points_tache[i] = PSol[i] + (6 - distance[i]) œ 10
```

**?tape 3 : Pour chaque PA dépensé**
```
FOR EACH secteur IN liste_secteurs:
    lancé = SUM( (SysExpl) D (2 œ PSol) )
    points_tache[secteur] += lancé

    IF points_tache[secteur] >= seuil[secteur]:
        ✓ SECTEUR D?COUVERT !
        ✓ Retirer de la liste de recherche
        ✓ Notifier joueur
```

**?tape 4 : Résultat**
```
Liste des secteurs détectables par le système
```

---

## ?Ž® Gameplay

### Interface Joueur

**Commande recherche :**
```
> scan_systems

Lancement scan longue portée...
PA disponibles : 10
SysExpl : 3

Recherche en cours... (1 PA dépensé)
?Ž² 3D100 : 45 + 78 + 23 = 146 points

Secteur Alpha-745 : 213/925 points
Secteur Beta-392 : 180/1050 points
Secteur Gamma-118 : 421/750 points

Continuer ? [Oui/Non/Arrêter]
```

**Découverte :**
```
> scan_systems (suite)

PA dépensé : 5 (5 restants)

✓ SYSTœME D?COUVERT !
Secteur Gamma-118 détecté !
- Type : ?toile jaune (G)
- PSol : 45
- Distance : 2.5 AL
- Coordonnées : (125, -34, 88)

Ajouter aux favoris ? [Oui/Non]
Continuer recherche ? [Oui/Non]
```

---

## œ? Systèmes Cachés

### Principe

Certains systèmes peuvent être **cachés** :
- Derrière nébuleuse
- Occultés par autre objet
- Très faibles (PSol < 15)
- Très distants

**Modification formule :**
```
Seuil_caché = Seuil œ Multiplicateur_cachette

Exemples :
- Nébuleuse légère : œ1.5
- Nébuleuse dense : œ2.0
- Occultation : œ3.0
```

---

## œ¡ Stratégies

### Pour Explorateurs

**Optimiser découverte :**
1. Investir dans SysExpl élevé (5-10)
2. Chercher par zones (méthodique)
3. Prioriser systèmes proches d'abord
4. Revenir avec meilleur équipement pour systèmes difficiles

**Revente données :**
- Systèmes découverts = vendables
- Prix selon rareté/intérêt

---

### Pour Développeurs

**Ajustements possibles :**
- Modifier constante 500 (difficulté base)
- Modifier constante 6 (portée max)
- Modifier multiplicateur distance (100)
- Ajouter bonus/malus selon équipement

---

## œŠ Tableau Récapitulatif

### Formules Complètes

| ?lément | Formule | Description |
|---------|---------|-------------|
| **Seuil** | 500 + (Distance œ 100) | Points requis pour découverte |
| **Points initiaux** | PSol + (6 - Distance) œ 10 | Avantage de départ |
| **Lancé/PA** | (SysExpl) D (2 œ PSol) | Points gagnés par PA |
| **PA estimés** | (Seuil - Points initiaux) / Moyenne lancé | Estimation durée |

---

## œ® ?volutions Futures

**Idées possibles :**
- Scan passif (automatique, lent)
- Scan actif (rapide, coûte énergie)
- Coopération joueurs (scan partagé)
- Zones déjà scannées (bonus)
- Anomalies détectables
- Artefacts cachés

---

**Document vivant - Dernière mise à jour : 2025-11-01**
