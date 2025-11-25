# 🎯›¸ VAISSEAUX - SPÉCIFICATIONS COMPLÏˆTES
## Jeu de Conquête Galactique

---

## ⚠️ DISCLAIMER
Données issues du wiki - Valeurs de référence à équilibrer lors des tests.

---

## 🎯Ž¯ Principe Fondamental

**Un joueur = Un vaisseau actif**
- Le joueur peut posséder plusieurs vaisseaux
- Seul l'actif est utilisable en jeu
- Les autres sont stockés (hangars, bases)

---

## 🎯“‹ Caractéristiques d'un Vaisseau

### Attributs Principaux

**Transport :**
- **Soute de transport** : marchandises OU personnes (reconvertible)
- **Volume** : Taille de l'objet
- **Masse** : Poids du vaisseau (vide + variable)

**Systèmes :**
- **Système informatique** : Ordinateurs sous contrôle IA
- **Type de propulsion** : Détermine fonctionnement principal
- **Réserve d'énergie** : Selon type de propulsion

**Combat/Défense :**
- **Emplacements défenses** : Armes embarquées
- **Résistance** : En US (Unités de Structure)

**Propriété :**
- **Propriétaire** : N'est pas obligatoirement le pilote
- Si locataire â†’ touche **1/3 des bénéfices**

**Fonctionnel :**
- **Emplacements fonctionnels** : Boucliers, blindage, etc.

---

## 🎯”§ Les 12 Emplacements

Chaque vaisseau possède des emplacements pour :

1. **Poste de pilotage**
2. **Moteur** (conventionnel)
3. **Moteur HE** (Hyper-Espace)
4. **Boucliers**
5. **Système informatique**
6. **Réserve d'énergie**
7. **Réserve de combustible** (si propulsion à combustible)
8. **Soute** (cargo)
9. **Armements liés** (au pilotage)
10. **Système d'armements**
11. **Système de survie**
12. **Structure et blindage**

---

## 🎯“¦ Système de Soute

### Principe

**Unité de base : Le Cargo**
- Marchandises mesurées en **unité de cargo**
- Soute contient un nombre de cargos disponibles
- Espace relativement protégé
- Peut être fractionné qu'en nombre de cargo
- **Certains modules de vaisseaux prennent 1+ places cargo**

### Transport de Personnel (3 Niveaux)

#### 1. Module Succinct
- **Capacité** : 10 personnes par cargo
- **Coût** : Minime
- **Durabilité** : Utilisation 1-2 fois seulement

#### 2. Module Régulier
- **Capacité** : 5 personnes par cargo
- **Durabilité** : Permanent (tant que module présent)

#### 3. Module 1ère Classe
- **Capacité** : 3 personnes par cargo
- **Coût** : Cher
- **Durabilité** : Permanent (tant que module présent)

---

## âš™ï¸ Système de Propulsion

### Principe - Deux Moteurs Couplés

**1. Moteur de Saut "Canon de Distortion" (HE)**
- Propulse vaisseau dans hyper-espace
- Fonctionne comme projectile de canon
- **Difficile de l'arrêter** (gameplay)
- Besoin énergie très important
- Peut durer plusieurs jours

**2. Moteur Conventionnel**
- Déplacement espace conventionnel
- Plus flexible
- Permet : approches, arrimages, décollages
- Grandes distances = forte consommation
- Meilleurs = moteurs à combustible

**Les deux moteurs sont couplés** et utilisent une **réserve d'énergie commune**.

---

## 🎯”‹ Type A : Propulsion à Combustible

### Principe

- Transforme combustible â†’ énergie (propulsion + "électrique")
- Produit **toujours** de l'énergie
- **Pas de rechargement** pour mode conventionnel
- Doit accumuler énergie dans réserve pour saut HE

**âœ… Avantage :** Cargo de combustible peut alimenter moteur (moins bien)

### 3 Types de Combustibles

#### 1. Uranium
- **Stabilité** : Stable dans le temps
- **Problèmes** : Graves (radiations)
- **Taux d'efficacité** : 0.75

#### 2. Plasma (Hydrogène)
- **Stabilité** : Plus instable
- **Problèmes** : Moins catastrophiques (pas de radiation)
- **Taux d'efficacité** : 0.80

#### 3. Tyberium â­
- **Origine** : Trouvé dans anneaux de Saturne
- **Rapport énergétique** : Très bon
- **Surnom** : "Pétrole des temps modernes"
- **Stabilité** : Très stable et peu dangereux
- **Taux d'efficacité** : 0.90

---

## â˜€ï¸ Type B : Propulsion à Extraction d'Énergie

### Principe

- Extrait énergie **directement des étoiles**
- Gratuit et renouvelable â™»ï¸
- Quantité variable selon position/étoiles
- Dangers moindres que combustible

**âŒ Inconvénient :** Nécessite **temps de rechargement** (conventionnel ET HE)

### 3 Types d'Extraction

#### 1. À Micro-Panneaux (Standard)
- Panneaux "solaires" déployés
- **Vaisseau ne peut PAS bouger** durant rechargement
- Propulsion standard et classique

#### 2. À Voile Solaire
- Comme bateaux à voile des mers d'antan
- Plusieurs mâts + voiles d'argent-stellaire
- **Vaisseau PEUT se déplacer** durant rechargement

#### 3. À Matière Noire
- Longue traînée noire obscurcissant le ciel
- Très encombrant
- Pour **gros vaisseaux**
- Moins sensible à la masse
- Nécessite plusieurs hommes pour maintien

---

## 🎯“ Formules de Calcul Propulsion

### Mode Conventionnel

**Principe distances :**
- 1 UE pour 100 millions de km (0.1 UA)
- 1 UE pour 0.1 PA
- 1 UE pour 1000 tonnes de masse (1 Mt)
- Système solaire â‰ˆ 50 UA
- Distances stockées en centaines de millions de km (max 750)

**Formules :**
```
Consommation conventionnelle = Init_Conventionnel + (Masse Ï— Distance / Vitesse)

Nb PA = Consommation / Vitesse Ï— Coef_PAMN / 100

Avec :
- Init_Conventionnel = 0
- Coef_PAMN = 100
```

**Exemple :**
```
Propulsion vitesse : 100
Vaisseau : 5000 t
Distance : 100 UC (10 milliards km)
â†’ Consommation = 5000 / 100 = 50 UE pour 1 PA
```

---

### Mode Hyper-Espace

**Formules :**
```
Consommation HE = Init_Hyperespace + (Coef_HE/100) Ï— (Masse/Vitesse) Ï— Distance

Nombre PA = 1 + Coef_PAHE Ï— Distance

Avec :
- Init_HE = 200
- Coef_HE = 50
- Coef_PAHE = 20
```

---

## 🎯“Š Paramètres des Propulsions

Chaque propulsion possède :

**Général :**
- **Mode** : Combustible / Énergétique
- **Réserve** : Quantité UE stockable
- **Part panne** : % du moteur dans les pannes

**Vitesses :**
- **Vitesse conventionnelle** : Vitesse référence mode normal
- **Vitesse saut** : Pour bonds HE

**Combustible (si applicable) :**
- **Combustible** : Réserve de combustible
- **Efficacité** : Transformation combustible â†’ énergie par PA
- **Type combustible** : Minerai utilisable
- **Récupération** : Points combustible dans 1 cargo

**Coefficients :**
- **Init_Conventionnel** : Coût initial mode normal (0)
- **Init_Hyperespace** : Coût initial saut HE (200)
- **Coef_Conventionnel** : MultiplicateurÏ—100 dépense énergie mode normal
- **Coef_Hyperespace** : MultiplicateurÏ—100 dépense énergie mode HE
- **Coef_PAMN** : MultiplicateurÏ—100 PA mode normal (100)
- **Coef_PAHE** : MultiplicateurÏ—100 PA mode HE (20)

---

## 🎯ŒŸ Distances de Référence (Système Solaire)

| Planète | Distance (UA) |
|---------|---------------|
| Mercure | 0.38 |
| Vénus | 0.72 |
| Terre | 1.00 |
| Mars | 1.52 |
| Jupiter | 5.21 |
| Saturne | 9.54 |
| Uranus | 19.18 |
| Neptune | 30.11 |

**Note :** 1 UA = 1.5 milliards de km

---

## 🎯’» Programmes Informatiques

Le système informatique est un élément important du vaisseau.

### Programmes Identifiés

1. **Système de pilotage**
   - Statut : **OBLIGATOIRE**

2. **Système de visée multiple**
   - Statut : Optionnel

3. **Calcul de trajectoire**
   - Statut : **OBLIGATOIRE**

4. **Détecteurs objets spatiaux**
   - Statut : **Fortement recommandé**
   - Importance : **Très important pour découvrir nouvelles destinations**

---

## 🎯š€ Modèles de Vaisseaux

### Principe Fondamental

**Idées de base :**
- Il existe des modèles de vaisseaux
- Toujours possibilité d'augmenter caractéristique (modules, améliorations, programmes)
- **Vaisseau inférieur peut égaler supérieur** au prix de :
  - Sacrifices de fonctionnement/performances
  - OU quantité financière plus importante
- **Vaisseaux faillibles** : pannes récurrentes possibles
- **Toujours réparable** (possibilité de se faire aider)

**Organisation :**
- À chaque guilde â†’ un ou plusieurs modèles de vaisseaux

---

## 🎯“ Série A - Polyvalents

### A-0 : Micro-vaisseau Parasite

**Type :** Vaisseau parasite par excellence

**Caractéristiques :**
- **Taille** : 1 (Remorquable)
- **Cargo** : Aucun
- **Capacité** : 2 personnes
- **Puissance de feu** : Faible
- **Masse** : Minimale

**Rôle :**
- Aller chercher un autre vaisseau
- Transit PoV vers PoV (même secteur)
- Vaisseau vers vaisseau
- **Peut s'accrocher** à tous vaisseaux (sauf autre A-0)

---

### A-1 : Modèle de Base

**Type :** Vaisseau polyvalent starter

**Caractéristiques :**
- **Masse** : 5000 t
- **Soutes** : 3 cargos
- **Emplacements libres** : 5
- **Système informatique** : 5
- **Fiabilité** : Moyenne
- **Volume** : 10
- **Propulsion** : À micro-panneaux (extraction énergétique)
- **Réserve** : 600 UE
- **Prix** : 30 000 cr

**Rôle :**
- Vaisseau de départ
- Polyvalence
- Évolutif

---

## 🎯“¦ Série M - Marchands

**Guilde :** Guilde des Marchands

**Objectif :** Transport de marchandise avant tout

**Sacrifices :**
- Compétences réduites
- Puissance réduite
- ManÅ“uvrabilité réduite

**Gains :**
- **Cargo maximisé**
- **Défense accrue**

### M-1 : Modèle de Base Marchand

**Caractéristiques :**
- (À définir selon équilibrage)
- Cargo supérieur à A-1
- Défenses correctes
- Moins agile

---

## 🎯”­ Série E - Explorateurs

**Guilde :** Guilde des Explorateurs

**Objectif :** Découverte et cartographie

**Caractéristiques attendues :**
- Détecteurs améliorés
- Portée accrue
- Autonomie longue
- Cargo réduit

---

## âš”ï¸ Série F - Forces d'Intervention

**Guilde :** Forces d'Intervention / Militaires

**Objectif :** Combat et sécurité

**Caractéristiques attendues :**
- Armement lourd
- Blindage renforcé
- ManÅ“uvrabilité élevée
- Cargo minimal

---

## 🎯“Š Classes de Vaisseaux (Tailles)

### Petits Vaisseaux
- Multi-rôles
- Chasseurs
- Transporteurs légers
- **Équipage** : 1-2 personnes

### Vaisseaux Moyens
- Normalement : équipage de **plus d'une personne**
- Multi-rôles avancés
- Corvettes
- Cargos moyens
- **Équipage** : 2-10 personnes

### Gros Vaisseaux
- Frégates
- Destroyers
- Cargos lourds
- Stations mobiles
- **Équipage** : 10+ personnes

---

## 🎯› ï¸ Pannes et Maintenance

### Système de Pannes

**Facteurs influençant pannes :**
- **Vétusté** : Augmente probabilité pannes
- **Part panne** : % du moteur dans les pannes
- **Complexité fonctionnelle** : Difficulté de réparation
- **Score Panne** : Augmente à chaque panne selon gravité
- **Score Entretien** : Augmente à chaque entretien (bonus)

**Formule Réparation (exemple) :**
```
Jet = Score Entretien + Réparation + 1D100 
> 
Taux Panne + Réparation manuelle vaisseau + Vétusté/(X00)
```

### Pannes Actuelles

Chaque vaisseau possède un **tableau des pannes à réparer**.

Types de pannes possibles :
- Moteur endommagé
- Fuite coque
- Système informatique défaillant
- Boucliers HS
- Armes bloquées
- Capteurs défectueux

---

## 🎯’¡ Idées Complémentaires

**Possibilités futures :**
- Personnalisation visuelle vaisseaux
- Noms personnalisés
- Historique du vaisseau
- Réputation du vaisseau (célèbre/recherché)
- Assurance vaisseaux
- Marché occasion vaisseaux
- Épaves récupérables

---

**Document vivant - Dernière mise à jour : 2025-11-01**

