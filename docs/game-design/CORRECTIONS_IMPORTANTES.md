# üîß CORRECTIONS IMPORTANTES
## Mises  Jour Système - 2025-11-01

---

## ⚠️📋 CORRECTIONS MAJEURES

Ces corrections modifient certains principes fondamentaux définis précédemment.

---

## üë§ PRINCIPE PERSONNAGE JOUEUR (PJ)

### œùå ANCIEN PRINCIPE (INCORRECT)

> "Un joueur = Un vaisseau actif"

### œúÖ NOUVEAU PRINCIPE (CORRECT)

**Un joueur = Un PJ dans l'univers**

**Règles :**
- Le joueur incarne un **Personnage Joueur (PJ)**
- Ce PJ ne peut conduire qu'**un vaisseau actif**  la fois
- Le joueur peut **"posséder" d'autres PJ secondaires**
- Permet de vivre des aventures avec des amis (PJ différents)
- Mais quand il joue œÜí **un seul PJ actif**

### Implications

**Structure :**
```
JOUEUR (Compte)
    œîúœîÄ PJ Principal (actif par défaut)
    œîÇ   œîîœîÄ Vaisseau actif
    œîúœîÄ PJ Secondaire 1
    œîÇ   œîîœîÄ Vaisseau(x) possédé(s)
    œîúœîÄ PJ Secondaire 2
    œîÇ   œîîœîÄ Vaisseau(x) possédé(s)
    œîîœîÄ ...
```

**Gameplay :**
- Changement de PJ actif possible (procédure  définir)
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

## üöÄ MODULE MICRO-HE

### Principe

**Nouveau module de propulsion :** Module MicroHE

**Fonction :**
- Permet des **petits sauts intra-système**
- Portée adaptée  la taille des systèmes solaires
- Alternative au déplacement conventionnel lent

### Caractéristiques (Ä êtudier)

**Portée estimée :**
- 0.1  2 UA par saut
- Selon puissance module

**Avantages :**
- Plus rapide que conventionnel
- Moins coªteux que HE complet
- Idéal pour navigation intra-système

**Inconvénients :**
- Portée limitée
- Moins précis que conventionnel
- Temps de recharge entre sauts

**Coªt énergétique :**
```
Consommation MicroHE = InitMicroHE + (Distance_UA ó CoefMicroHE)

Ä définir :
- InitMicroHE : ~50 UE (estimation)
- CoefMicroHE : ~10-20
- Temps recharge : 1-2 PA entre sauts
```

**Usage typique :**
```
Vaisseau arrive dans système via HE
œîîœîÄ Position : Périphérie (30 UA)
œîîœîÄ Destination : Planète habitable (1 UA)

Option 1 : Conventionnel
- Distance : 29 UA = 2900 ó 100 millions km
- Temps : 10-15 PA
- Coªt : Variable selon vaisseau

Option 2 : MicroHE (avec module)
- Série de 15 sauts de ~2 UA
- Temps : 3-5 PA (avec recharges)
- Coªt : ~750-1000 UE total
```

**Ä étudier en détail ultérieurement.**

---

## üìê SYSTÈME DE COORDONNÉES

### œùå ANCIEN SYSTÈME (SIMPLIFIê)

Coordonnées (x, y, z) réelles simples.

### œúÖ NOUVEAU SYSTÈME (PRêCIS)

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
- Taille d'un secteur : 1 ó 1 ó 1 (unité  définir : AL ou parsec)
- Utilisé pour génération procédurale
- Utilisé pour recherche/indexation

**Position réelle (décimale) :**
- Position précise  l'intérieur du secteur
- Partie décimale = sous-coordonnées (0.0  0.999...)
- Utilisé pour positionnement exact objets
- Utilisé pour calculs distances précis

### Exemples

**PoV (Point of Value) :**
```
PoV "Alpha Station"
œîúœîÄ Secteur : (0, 0, 0)
œîîœîÄ Position réelle : (0.12, 0.14, 0.1)

Interprétation :
- Dans le secteur central (0,0,0)
- Ä 12% dans l'axe X
- Ä 14% dans l'axe Y
- Ä 10% dans l'axe Z
```

**Vaisseau :**
```
Vaisseau "Explorer-01"
œîúœîÄ Secteur : (150, -23, 88)
œîîœîÄ Position réelle : (150.456, -23.789, 88.234)

Interprétation :
- Dans le secteur (150, -23, 88)
- Ä 45.6% dans l'axe X du secteur
- Ä 78.9% dans l'axe Y du secteur
- Ä 23.4% dans l'axe Z du secteur
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
- Génération  la demande par secteur
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
    # Si position >= 1.0 œÜí changer de secteur
    while obj.position_x >= 1.0:
        obj.secteur_x += 1
        obj.position_x -= 1.0
    
    while obj.position_x < 0.0:
        obj.secteur_x -= 1
        obj.position_x += 1.0
    
    # Idem pour Y et Z
```

---

## œôÔ∏è TÇCHES DE TRAITEMENT

### Principe

**Système de t¢ches asynchrones** que le moteur exécutera ou fera exécuter par les joueurs.

**En fonction de certains critères** ( définir).

### Concept

**T¢ches :**
- Actions qui prennent du temps
- Peuvent s'exécuter en arrière-plan
- Peuvent être déléguées

**Exemples de t¢ches :**
- Production usine
- Réparation vaisseau
- Recherche technologique
- Construction module base
- Exploration automatique (drones)
- Commerce automatique (IA)

### Types de T¢ches

**1. T¢ches Automatiques (Moteur)**
```
Gérées entièrement par le serveur :
- Tick économique (production/consommation)
- Déplacements IA
- êvénements temporels
- Génération procédurale
```

**2. T¢ches Semi-Automatiques**
```
Lancées par joueur, exécutées par serveur :
- Production module lancée
- Réparation en cours
- Voyage automatique vers destination
- Scan continu zone
```

**3. T¢ches Joueur**
```
Nécessitent actions joueur :
- Décisions commerciales
- Combats
- Négociations
- Exploration active
```

### Critères de Délégation

**Complexité :**
- Simple œÜí Automatique
- Complexe œÜí Semi-automatique ou Joueur

**Risque :**
- Sªr œÜí Automatique
- Risqué œÜí Joueur

**Importance :**
- Routine œÜí Automatique
- Stratégique œÜí Joueur

### Système de Queue

**Chaque PJ a une queue de t¢ches :**
```
Queue T¢ches PJ-01
œîúœîÄ T¢che 1 : Réparation boucliers (3 PA restants)
œîúœîÄ T¢che 2 : Production Uniteks (10 PA restants)
œîîœîÄ T¢che 3 : Voyage vers Alpha-745 (25 PA restants)
```

**Exécution :**
- Ä chaque tour (ou tick temps réel)
- Moteur décompte PA de chaque t¢che
- Quand PA = 0 œÜí T¢che complète

### Interface Joueur

```
> tasks list

TÇCHES EN COURS :
1. [====>....] Réparation boucliers - 3/8 PA
2. [==>......] Production 50 Uniteks - 10/45 PA
3. [>........] Voyage Alpha-745 - 25/30 PA

> tasks cancel 2
Annuler production Uniteks ? [Oui/Non]
Progression perdue : 10 PA
```

### Ä Détailler Ultérieurement

**Aspects  développer :**
- Priorités des t¢ches
- Interruptions
- Dépendances entre t¢ches
- êchecs de t¢ches
- Optimisations joueur
- Délégation  d'autres joueurs/IA

---

## üìã DOCUMENTS Ä METTRE Ä JOUR

### Liste des Documents Affectés

**1. GDD_Vaisseaux_Complet.md**
- œúì Correction : PJ pilote vaisseau (pas joueur = vaisseau)
- œúì Ajout : Module MicroHE
- œúì Ajout : Système coordonnées

**2. GDD_Architecture_Technique.md**
- œúì Correction : Classe Compte œÜí PJ Principal + Secondaires
- œúì Ajout : Classe T¢che
- œúì Ajout : Tables coordonnées (secteur + position)

**3. GDD_Systeme_Decouverte.md**
- œúì Nouveau document créé
- œúì Algorithme découverte systèmes

**4. Tous les documents mentionnant "joueur = vaisseau"**
- Ä corriger vers "PJ pilote vaisseau"

---

## 📋 PROCHAINES ACTIONS

### Immédiat

- [x] Créer GDD_Systeme_Decouverte.md
- [x] Documenter corrections importantes
- [ ] Mettre  jour documents existants

### Court Terme

- [ ] êtudier en détail module MicroHE
- [ ] Spécifier système t¢ches complet
- [ ] Implémenter système coordonnées en SQL
- [ ] Tester algorithme découverte

### Moyen Terme

- [ ] êquilibrer valeurs MicroHE
- [ ] Créer interface gestion t¢ches
- [ ] Optimiser recherche par secteur
- [ ] Tests performance coordonnées

---

**Document vivant - Dernière mise  jour : 2025-11-01**
