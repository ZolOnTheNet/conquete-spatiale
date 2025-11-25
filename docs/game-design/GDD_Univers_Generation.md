# 🎯ŒŒ UNIVERS ET GÉNÉRATION PROCÉDURALE
## Jeu de Conquête Galactique

---

## ⚠️ DISCLAIMER
Données issues du wiki - Algorithmes à implémenter et tester.

---

## 🎯Ž¯ Moteur Générique Multi-Univers

### Objectif

Créer un **moteur assez complexe** pour supporter différents univers de science-fiction.

### Univers Envisagés

**1. Monde de KÂ² (Zaib)**
- Zones se déplacent
- Système magique

**2. Style Lunastars**
- Case par case
- Déplacement suivant un axe à la fois

**3. Style Solar Empire**
- Passage d'un lieu à un autre
- Vecteurs d'hyperespace (routes)

---

## 🎯“ Principes Généraux

### Représentation de l'Univers

**Système 3D :**
- Coordonnées (x, y, z) en **réels simples**
- Partie fractionnelle **pas forcément accessible** au joueur

**Point de départ :**
- Terre OU ville "Initiale"

**Coordonnées entières (x, y, z) :**
- Représentent une **zone**
- Peuvent contenir différents objets :
  - Bases
  - Filons (gisements)
  - Vaisseaux
  - Points of Value (PoV)

---

### Contenus

**Objets et Zones :**
- Un objet peut **pointer sur une zone**
- Crée d'autres espaces (sous-terrain, donjons, etc.)
- Objets relatifs au style de jeu
- Coordonnées plus précises possibles pour un objet

---

### Générateur

**Génération :**
- **Au fur et à mesure**
- Exception : coordonnées décidées par MJ

**MJ peut :**
- Définir **grandes zones modèles**
- Déterminent variations d'une zone
- Cubes de 10, 20, 30 zones de côté
- Univers peut devenir très grand rapidement

**Déclenchement génération :**
- Arrivée dans coordonnée **frontière**
- OU arrivée dans coordonnée du "nouveau cube" modèle

---

## 🎯ŒŸ Classification des Étoiles

### Types Spectraux

| Classe | Température | Couleur | Puissance |
|--------|-------------|---------|-----------|
| **O** | > 25 000 K | Bleue | 150 - 200 |
| **B** | 10 000 - 25 000 K | Bleue-blanche | 100 - 140 |
| **A** | 7 500 - 10 000 K | Blanche | 80 - 100 |
| **F** | 6 000 - 7 500 K | Jaune-blanche | 60 - 80 |
| **G** | 5 000 - 6 000 K | Jaune | 40 - 60 |
| **K** | 3 500 - 5 000 K | Jaune-orange | 30 - 40 |
| **M** | < 3 500 K | Rouge | 20 - 30 |

### Courbe de Gauss (Distribution)

**Sur 20 éléments :**
```
Type    O   B   A   F   G   K   M
Indice  7   6   5   4   3   2   1
Nombre  1   1   3   4   6   3   2
```

**Utilisation :**
- Distribution naturelle des types d'étoiles
- G (type solaire) = le plus fréquent
- O/B (géantes bleues) = très rares
- M (naines rouges) = assez rares dans ce modèle

---

## â›ï¸ Gisements (Astéroïdes)

### Principe

**Par gisement :**
- **1 seule mine autonome** installable
- Exception : mines pirates possibles

### Rendement

**Facteurs :**
- Augmente progressivement vers **extérieur de l'univers** (10 à 50%)
- Plus facile de produire dès **présence humaine** (bases)

**Exploitation par MAME :**
- Ne produit **pas systématiquement** un cargo
- Tirage aléatoire vis-à-vis du rendement

**Exemple :**
```
Rendement gisement : 30%
Roll 1D100 :
- â‰¤ 30 : Production de 1 cargo
- > 30 : Pas de production ce tour
```

---

## 🎯”§ GÉNÉRATEUR SIMPLE D'UNIVERS

### Principe

Produire une zone de **N Ï— N Ï— N** avec un modèle en paramètre.

Pour 10 â†’ 1000 cubes : suivre **courbe de Gauss** autour d'un soleil de puissance donnée.

---

### Entrée (Paramètres du Modèle)

**Densités :**
- **Densité de soleils** : % donnant idée du nombre de soleils
- **Densité d'astéroïdes**
- **Densité de mines**
- **Densité d'étoiles minières**

**Puissances moyennes :**
- **Puissance moyenne des soleils** : échelle 20 à 140 (défaut 50)
- **Puissance moyenne des astéroïdes** : échelle 50 à 150 (défaut 70)

**Position :**
- **Position X, Y, Z la plus petite** du cube

**Autres (pas utilisés pour l'instant) :**
- Nombre de vents/courants
- Leurs longueurs moyennes

---

### Méthode

#### 1. Calcul Nombre de Soleils

**Courbe de Gauss :**
- Option A : Utiliser tableau pré-calculé selon types
- Option B : Calculer tableau
  - Puissance moyenne = centre
  - Écart-type = 30 (exemple)

**Résultat :** Tableau (puissance de soleil Ï— nombre)

**Structure tableau :**
```
Indice le plus bas = puissance 20
Pas de colonne = 10 (20, 30, 40, ...)
Exemple :
Puissance | Nombre
    20    |   1
    30    |   2
    40    |   5
    50    |   8  â† pic (centre)
    60    |   5
    70    |   2
    80    |   1
```

**Somme du tableau** = nombre total de soleils à distribuer

---

#### 2. Calcul Pas de Progression

```
Pas = Nombre de secteurs / Nombre de soleils
```

**Pas Aléatoire** (évite coïncidences) :
```
Pas aléatoire = Pas/2 + 1D(Pas)
```

---

#### 3. Parcours des Secteurs

**Principe :** Méthode des "petits chevaux" (le dernier avance)

**Étapes :**
1. Tous commencent en **position 0**
2. Prendre premier type d'élément en position minimum
3. Ajouter **Pas/2 + 1D(Pas)** à sa position
4. Pour cette position : transformer secteur en secteur de type élément
5. Mettre à zéro champ de tri si négatif
6. **Réduire de 1** le nombre d'éléments du type choisi

**Choix du type (soleil) :**
- **Option A (aléatoire)** : 1D(nombre de types)
- **Option B (méthodique)** : Plus grande â†’ plus petite puissance

---

#### 4. Même Principe pour Astéroïdes

**Créer tableau :**
- Danger min = 50
- Représente nombre Ï— danger
- Connaître nombre total d'astéroïdes
- Définir pas â†’ "pas aléatoire"
- Parcourir et distribuer

---

### Initialisation

**Premier parcours avec création :**
- Si secteur n'existe pas : **générer N Ï— N Ï— N secteurs**
- Utiliser un champ (ex: puissance solaire)
- Nombre aléatoire **négatif** : -1 à -Nâ´
  - Formule : **-1D(Nâ´)**

**But :** Ordre de tirage aléatoire pour parcours

---

### Après Traitement (Post-production)

**Traitements optionnels :**

1. **Création de vents** (génération peut être longue)
2. **Création liens entre soleils** (méthode primaire)
3. **Création zones de forte puissance** (énergies)
4. **Création secteurs spéciaux**

**Nettoyage :**
- Secteurs avec puissance **négative** = secteurs vides
- Peuvent être supprimés selon style univers

**Numérotation :**
```
Exemple : s001x02n00003

001   = numéro de génération
02    = modèle type choisi
00003 = numéro dans parcours
```

Peut être faite dans parcours principal si on ne numérotise pas espaces vides.

---

## 🎯›¤ï¸ GÉNÉRATEUR À CHEMINS D'UNIVERS

### Principe

**Coordonnées secteur** pas d'une grande importance.

Entre objets notables â†’ il existe un **chemin**.

**Caractéristiques :**
- Chaque objet a un chemin
- Chemin empruntable **des deux côtés** (bidirectionnel)

### Méthode

**Pour un secteur :**
1. Sélectionner tous soleils **en dessous d'une distance**
2. Créer les arcs (chemins) entre eux

**Résultat :**
- Graphe de connexions
- Navigation par chemins prédéfinis
- Style "Solar Empire"

**Avantages :**
- Navigation simplifiée
- Contrôle routes stratégiques
- Goulots d'étranglement tactiques

**Inconvénients :**
- Moins de liberté exploration
- Prédictibilité

---

## 🎯Œ Références d'Univers

### ZAIB (Monde KÂ²)

**Contexte :**
- "Bulle des vents" = petit univers
- **Portiques** : permettent passage bulle à bulle

**Moyens de transport :**
- Navires
- Vaisseaux
- **Insekts** : insectes très grands
  - Transport marchandises + hommes
  - Type maritime (vivent dans l'air)
  - Type aérien

**Nature :** Monde **magique**

**Application possible :**
- Zones mobiles
- Portails entre zones
- Magie comme système énergétique

---

### LUNASTARS

**Type :** MMO navigateur (HTML + PHP)

**Principe :**
- 1 joueur = 1 personnage = 1 vaisseau mono-place
- Pas de classe fixe
- Styles : marchand, explorateur, militaire, aventurier

**Vaisseaux :**
- Modules
- Uniteks
- Soute

**Objets déployables :**
- MAMEs (Mines Automatiques)
- Antennes Hyper-relais
- Bars

#### Points Forts

- Missions
- Aide énergie/uniteks vaisseau à vaisseau
- Système gestion d'énergie
- Système maladie
- Réputation, XP, compétences améliorables

**Interaction PvE :**
- Astéroïde événementiel (tirage aléatoire)
- Tempêtes et vents
- Création mine
- Création station élémentaire (bar)

#### Points Faibles

**Problèmes de conception :**

1. **Orienté Environnement > Joueur**
   - Aide entre joueurs limitée
   - Uniquement dons uniteks/énergie

2. **Système missions favorise entropie**
   - Lien Terre â†” base Vulcania
   - Toutes ressources autour Vulcania
   - Autres exploitations non compétitives

**Leçons pour notre jeu :**
- âœ… Favoriser interaction joueurs
- âœ… Économie décentralisée
- âœ… Plusieurs hubs importants

---

### SINS OF A SOLAR EMPIRE

**Type :** Jeu temps réel de stratégie

**Principe :**
- 3 races
- Conquête progressive univers à découvrir
- Espaces de jeu = systèmes solaires
- **Reliés par vecteur d'hyperespace**
- Vecteur = route entre deux points

**Application :**
- Générateur à chemins
- Routes stratégiques
- Contrôle territorial

---

## 🎯Ž² Variantes de Génération

### Génération Hybride

**Combiner les deux générateurs :**
1. Générateur simple pour créer zones
2. Générateur à chemins pour créer routes principales
3. Navigation libre OU par chemins selon contexte

**Avantages :**
- Flexibilité
- Routes connues ET exploration libre
- Stratégie + découverte

---

### Zones Spéciales

**Types possibles :**
- Nébuleuses (visibilité réduite)
- Champs astéroïdes denses
- Anomalies gravitationnelles
- Trous noirs
- Zones de tempête
- Ruines anciennes
- Portails mystérieux

**Génération :**
- Par MJ (placées manuellement)
- OU procédural (% de chance)

---

## 🎯—ºï¸ Secteurs et Coordonnées

### Système de Coordonnées

**Format :** (x, y, z)
- Entiers = zones principales
- Décimales = positions précises dans zone

**Exemple :**
```
(150.5, -23.2, 88.0)

150   = zone X
-23   = zone Y
88    = zone Z
0.5   = position dans zone X (50%)
0.2   = position dans zone Y (20%)
0.0   = centre zone Z
```

---

### Stockage

**Base de données :**
- Stocker uniquement zones **générées/visitées**
- Zones vides supprimées (optimisation)
- Génération à la demande

**Tables :**
```sql
secteurs (
    id,
    coord_x, coord_y, coord_z,
    type (vide/soleil/astéroïde/etc),
    puissance,
    danger,
    découvert_par,
    date_génération
)
```

---

## 🎯’¡ Idées Futures

**Évolutions possibles :**
- Univers dynamique (étoiles vieillissent)
- Événements cosmiques (supernovae)
- Migration civilisations
- Expansion/contraction zones contrôlées
- Phénomènes temporels
- Dimensions parallèles

---

**Document vivant - Dernière mise à jour : 2025-11-01**
