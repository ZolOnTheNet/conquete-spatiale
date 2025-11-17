# 🔧 CORRECTIONS IMPORTANTES
## Mises à Jour Système - 2025-11-01

---

## ⚠️ CORRECTIONS MAJEURES

Ces corrections modifient certains principes fondamentaux définis précédemment.

---

## 👤 PRINCIPE PERSONNAGE JOUEUR (PJ)

### ❌ ANCIEN PRINCIPE (INCORRECT)

> "Un joueur = Un vaisseau actif"

### ✅ NOUVEAU PRINCIPE (CORRECT)

**Un joueur = Un PJ dans l'univers**

**Règles :**
- Le joueur incarne un **Personnage Joueur (PJ)**
- Ce PJ ne peut conduire qu'**un vaisseau actif** à la fois
- Le joueur peut **"posséder" d'autres PJ secondaires**
- Permet de vivre des aventures avec des amis (PJ différents)
- Mais quand il joue → **un seul PJ actif**

### Implications

**Structure :**
```
JOUEUR (Compte)
    ├─ PJ Principal (actif par défaut)
    │   └─ Vaisseau actif
    ├─ PJ Secondaire 1
    │   └─ Vaisseau(x) possédé(s)
    ├─ PJ Secondaire 2
    │   └─ Vaisseau(x) possédé(s)
    └─ ...
```

**Gameplay :**
- Changement de PJ actif possible (procédure à définir)
- Chaque PJ a sa propre progression
- Chaque PJ a ses propres vaisseaux
- Possibilité de jouer différents archétypes
  - PJ1 : Explorateur
  - PJ2 : Marchand
  - PJ3 : Militaire

**Social :**
- Permet de rejoindre amis avec PJ adapté
- Exemple : Ami organise raid militaire
  - Joueur peut basculer sur son PJ combattant
  - Au lieu de son PJ explorateur habituel

---

## 🚀 MODULE MICRO-HE

### Principe

**Nouveau module de propulsion :** Module MicroHE

**Fonction :**
- Permet des **petits sauts intra-système**
- Portée adaptée à la taille des systèmes solaires
- Alternative au déplacement conventionnel lent

### Caractéristiques (À Étudier)

**Portée estimée :**
- 0.1 à 2 UA par saut
- Selon puissance module

**Avantages :**
- Plus rapide que conventionnel
- Moins coûteux que HE complet
- Idéal pour navigation intra-système

**Inconvénients :**
- Portée limitée
- Moins précis que conventionnel
- Temps de recharge entre sauts

**Coût énergétique :**
```
Consommation MicroHE = InitMicroHE + (Distance_UA × CoefMicroHE)

À définir :
- InitMicroHE : ~50 UE (estimation)
- CoefMicroHE : ~10-20
- Temps recharge : 1-2 PA entre sauts
```

**Usage typique :**
```
Vaisseau arrive dans système via HE
└─ Position : Périphérie (30 UA)
└─ Destination : Planète habitable (1 UA)

Option 1 : Conventionnel
- Distance : 29 UA = 2900 × 100 millions km
- Temps : 10-15 PA
- Coût : Variable selon vaisseau

Option 2 : MicroHE (avec module)
- Série de 15 sauts de ~2 UA
- Temps : 3-5 PA (avec recharges)
- Coût : ~750-1000 UE total
```

**À étudier en détail ultérieurement.**

---

## 📐 SYSTÈME DE COORDONNÉES

### ❌ ANCIEN SYSTÈME (SIMPLIFIÉ)

Coordonnées (x, y, z) réelles simples.

### ✅ NOUVEAU SYSTÈME (PRÉCIS)

**Coordonnées entières + décimales**

### Principe

**Secteur = Coordonnées entières**
```
Secteur (150, -23, 88)
```

**Position réelle = Coordonnées avec décimales**
```
Position (150.12, -23.14, 88.1)
```

### Structure

**Secteur (entier) :**
- Représente une "zone" de l'espace
- Taille d'un secteur : 1 × 1 × 1 (unité à définir : AL ou parsec)
- Utilisé pour génération procédurale
- Utilisé pour recherche/indexation

**Position réelle (décimale) :**
- Position précise à l'intérieur du secteur
- Partie décimale = sous-coordonnées (0.0 à 0.999...)
- Utilisé pour positionnement exact objets
- Utilisé pour calculs distances précis

### Exemples

**PoV (Point of Value) :**
```
PoV "Alpha Station"
├─ Secteur : (0, 0, 0)
└─ Position réelle : (0.12, 0.14, 0.1)

Interprétation :
- Dans le secteur central (0,0,0)
- À 12% dans l'axe X
- À 14% dans l'axe Y
- À 10% dans l'axe Z
```

**Vaisseau :**
```
Vaisseau "Explorer-01"
├─ Secteur : (150, -23, 88)
└─ Position réelle : (150.456, -23.789, 88.234)

Interprétation :
- Dans le secteur (150, -23, 88)
- À 45.6% dans l'axe X du secteur
- À 78.9% dans l'axe Y du secteur
- À 23.4% dans l'axe Z du secteur
```

### Implémentation Base de Données

```sql
CREATE TABLE objets_spatiaux (
    IdOS INT PRIMARY KEY,
    
    -- Secteur (entier)
    secteur_x INT NOT NULL,
    secteur_y INT NOT NULL,
    secteur_z INT NOT NULL,
    
    -- Position réelle (décimale)
    position_x DECIMAL(10,3) NOT NULL,
    position_y DECIMAL(10,3) NOT NULL,
    position_z DECIMAL(10,3) NOT NULL,
    
    -- Index sur secteur pour recherche rapide
    INDEX idx_secteur (secteur_x, secteur_y, secteur_z)
);
```

### Avantages

**Performance :**
- Recherche rapide par secteur (entiers)
- Indexation efficace
- Génération procédurale simplifiée

**Précision :**
- Position exacte dans secteur
- Calculs distances précis
- Collisions détectables

**Gameplay :**
- Secteur = "zone" jouable
- Génération à la demande par secteur
- Transition secteur = événement

### Calculs

**Distance entre deux objets :**
```python
def distance(obj1, obj2):
    dx = obj1.position_x - obj2.position_x
    dy = obj1.position_y - obj2.position_y
    dz = obj1.position_z - obj2.position_z
    return sqrt(dx*dx + dy*dy + dz*dz)
```

**Même secteur ? :**
```python
def meme_secteur(obj1, obj2):
    return (
        obj1.secteur_x == obj2.secteur_x and
        obj1.secteur_y == obj2.secteur_y and
        obj1.secteur_z == obj2.secteur_z
    )
```

**Changement de secteur (lors déplacement) :**
```python
def normaliser_position(obj):
    # Si position >= 1.0 → changer de secteur
    while obj.position_x >= 1.0:
        obj.secteur_x += 1
        obj.position_x -= 1.0
    
    while obj.position_x < 0.0:
        obj.secteur_x -= 1
        obj.position_x += 1.0
    
    # Idem pour Y et Z
```

---

## ⚙️ TÂCHES DE TRAITEMENT

### Principe

**Système de tâches asynchrones** que le moteur exécutera ou fera exécuter par les joueurs.

**En fonction de certains critères** (à définir).

### Concept

**Tâches :**
- Actions qui prennent du temps
- Peuvent s'exécuter en arrière-plan
- Peuvent être déléguées

**Exemples de tâches :**
- Production usine
- Réparation vaisseau
- Recherche technologique
- Construction module base
- Exploration automatique (drones)
- Commerce automatique (IA)

### Types de Tâches

**1. Tâches Automatiques (Moteur)**
```
Gérées entièrement par le serveur :
- Tick économique (production/consommation)
- Déplacements IA
- Événements temporels
- Génération procédurale
```

**2. Tâches Semi-Automatiques**
```
Lancées par joueur, exécutées par serveur :
- Production module lancée
- Réparation en cours
- Voyage automatique vers destination
- Scan continu zone
```

**3. Tâches Joueur**
```
Nécessitent actions joueur :
- Décisions commerciales
- Combats
- Négociations
- Exploration active
```

### Critères de Délégation

**Complexité :**
- Simple → Automatique
- Complexe → Semi-automatique ou Joueur

**Risque :**
- Sûr → Automatique
- Risqué → Joueur

**Importance :**
- Routine → Automatique
- Stratégique → Joueur

### Système de Queue

**Chaque PJ a une queue de tâches :**
```
Queue Tâches PJ-01
├─ Tâche 1 : Réparation boucliers (3 PA restants)
├─ Tâche 2 : Production Uniteks (10 PA restants)
└─ Tâche 3 : Voyage vers Alpha-745 (25 PA restants)
```

**Exécution :**
- À chaque tour (ou tick temps réel)
- Moteur décompte PA de chaque tâche
- Quand PA = 0 → Tâche complète

### Interface Joueur

```
> tasks list

TÂCHES EN COURS :
1. [====>....] Réparation boucliers - 3/8 PA
2. [==>......] Production 50 Uniteks - 10/45 PA
3. [>........] Voyage Alpha-745 - 25/30 PA

> tasks cancel 2
Annuler production Uniteks ? [Oui/Non]
Progression perdue : 10 PA
```

### À Détailler Ultérieurement

**Aspects à développer :**
- Priorités des tâches
- Interruptions
- Dépendances entre tâches
- Échecs de tâches
- Optimisations joueur
- Délégation à d'autres joueurs/IA

---

## 📋 DOCUMENTS À METTRE À JOUR

### Liste des Documents Affectés

**1. GDD_Vaisseaux_Complet.md**
- ✓ Correction : PJ pilote vaisseau (pas joueur = vaisseau)
- ✓ Ajout : Module MicroHE
- ✓ Ajout : Système coordonnées

**2. GDD_Architecture_Technique.md**
- ✓ Correction : Classe Compte → PJ Principal + Secondaires
- ✓ Ajout : Classe Tâche
- ✓ Ajout : Tables coordonnées (secteur + position)

**3. GDD_Systeme_Decouverte.md**
- ✓ Nouveau document créé
- ✓ Algorithme découverte systèmes

**4. Tous les documents mentionnant "joueur = vaisseau"**
- À corriger vers "PJ pilote vaisseau"

---

## 🎯 PROCHAINES ACTIONS

### Immédiat

- [x] Créer GDD_Systeme_Decouverte.md
- [x] Documenter corrections importantes
- [ ] Mettre à jour documents existants

### Court Terme

- [ ] Étudier en détail module MicroHE
- [ ] Spécifier système tâches complet
- [ ] Implémenter système coordonnées en SQL
- [ ] Tester algorithme découverte

### Moyen Terme

- [ ] Équilibrer valeurs MicroHE
- [ ] Créer interface gestion tâches
- [ ] Optimiser recherche par secteur
- [ ] Tests performance coordonnées

---

**Document vivant - Dernière mise à jour : 2025-11-01**
