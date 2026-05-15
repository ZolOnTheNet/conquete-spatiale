# GDD — Vaisseaux : Modules, Systèmes & Constructeurs
## Conquête Spatiale — Document de référence

> Document vivant. Complémentaire à `GDD_Vaisseaux_Complet.md`.
> Dernière mise à jour : 2026-05-15

---

## 1. CONSTRUCTEURS & FABRICANTS

Le marché est dominé par quelques grands groupes industriels, chacun spécialisé dans un segment.
À chaque guilde correspond un constructeur principal, mais rien n'empêche d'acheter des composants d'une marque tierce.

### 1.1 Constructeurs de coques (châssis complets)

| Constructeur | Siège | Série principale | Réputation |
|---|---|---|---|
| **Lunastar** | Lunastar-station (orbite lunaire) | A (polyvalent) | Fiable, accessible, le plus vendu |
| **Meridian Cargo Group** *(MCG)* | Espérance | M (marchand) | Solide, lent, très grande capacité |
| **Aether Exploration Systems** *(AES)* | New-San Francisco | E (explorateur) | Haute technologie, prix élevé |
| **Vortex Defense Industries** *(VDI)* | Inconnue (militaire) | F (forces) | Militaire, exportation restreinte |
| **Hullworks Cooperative** | Mobile (flottille) | Occasion / hybride | Remis à neuf, bricolé mais pas cher |

> **Lore — Lunastar :** La société Lunastar est doublement présente dans la vie des nouveaux joueurs : elle exploite la station de départ en orbite lunaire *Lunastar-station*, et elle fabrique les coques de Série A — les vaisseaux polyvalents d'entrée de gamme les plus répandus dans l'espace humain. Ce n'est pas un hasard : Lunastar a bâti son empire en vendant des vaisseaux aux colons qui quittaient la station, puis en rachetant et revendant les mêmes appareils une fois usés.

### 1.2 Fabricants de moteurs

| Fabricant | Spécialité | Gamme |
|---|---|---|
| **Kronos Engines** | Moteurs universels MN + HE | Entrée & milieu de gamme |
| **Ashvelt & Partners** | Moteurs à combustible haute performance | Professionnel, commerce |
| **Graviton Power Systems** | Systèmes d'extraction énergétique (Type B) | Standard à haute perf |
| **Stratos Drive Corp** | Moteurs HE longue portée | Explorateurs & fret interstellaire |
| **Titan Propulsion Works** | Moteurs lourds pour gros vaisseaux | Frégates, destroyers |

### 1.3 Fabricants de systèmes et modules

| Fabricant | Spécialité |
|---|---|
| **StellarTech** | Systèmes informatiques, démons logiciels, scanners |
| **OmniShield** | Boucliers, blindage, systèmes défensifs |
| **DataCore Systems** | Modules atlas, bases de données, communications |
| **Nexus Arms** | Armement embarqué |
| **BioSurvival Corp** | Systèmes de survie, modules médicaux |
| **VoidCraft Mods** | Modules artisanaux / modifiés, souvent illégaux |

---

## 2. MOTEURS CONVENTIONNELS (Mode Normal / MN)

Le moteur conventionnel gère les déplacements intra-système : approche, arrimage, manœuvres, décollage.

**Formule de consommation :**
```
Consommation MN = Init_MN + (Masse × Distance / Vitesse_MN)
PA MN = Consommation / Vitesse × Coef_PAMN / 100
```

### 2.1 Catalogue

| Modèle | Marque | V. MN | Init | Coef PA | Masse moteur | Prix | Compatible |
|---|---|---|---|---|---|---|---|
| **MC-50 Compact** | Kronos | 0.5 | 0 | 120 | 200 t | 2 500 cr | A-0, petits vaisseaux |
| **MC-100 Standard** | Kronos | 1.0 | 0 | 100 | 500 t | 5 000 cr | A-0, A-1 |
| **MC-250 Performer** | Kronos | 2.5 | 0 | 100 | 800 t | 12 000 cr | A-1, M-1 |
| **MC-400 Freighter** | Ashvelt | 3.0 | 0 | 95 | 1 200 t | 22 000 cr | M-1, lourds |
| **MC-500 Rapide** | Lunastar | 5.0 | 0 | 90 | 1 200 t | 28 000 cr | A-1+ |
| **MC-700 Pursuit** | VDI | 7.0 | 0 | 80 | 1 500 t | 60 000 cr | F-series |
| **MC-900 Elite** | VDI | 9.0 | 0 | 75 | 2 000 t | 130 000 cr | F-series, grandes coques |
| **MC-ECO Frugal** | Graviton | 0.8 | 0 | 130 | 300 t | 3 500 cr | Petits vaisseaux, économique |

> **Note :** Le `Coef PA` > 100 signifie plus de PA consommés par unité d'énergie (moteur moins efficace).

---

## 3. MOTEURS HYPER-ESPACE (Canon de Distortion / HE)

Le moteur HE propulse le vaisseau en hyper-espace comme un projectile de canon. Difficile à arrêter une fois lancé. Très énergivore.

**Formule de consommation :**
```
Consommation HE = Init_HE + (Coef_HE/100) × (Masse / Vitesse_HE) × Distance
PA HE = 1 + Coef_PAHE × Distance
```

### 3.1 Catalogue

| Modèle | Marque | V. HE | Init HE | Coef HE | Coef PAHE | Prix | Compatible |
|---|---|---|---|---|---|---|---|
| **HE-50 Micro** | Kronos | 0.5 | 250 | 65 | 25 | 4 500 cr | A-0, parasites |
| **HE-100 Standard** | Kronos | 1.0 | 200 | 50 | 20 | 8 000 cr | A-1, entrée de gamme |
| **HE-200 Merchant** | Ashvelt | 1.5 | 190 | 48 | 22 | 16 000 cr | M-1, fret |
| **HE-350 Explorer** | Stratos | 2.5 | 160 | 42 | 18 | 38 000 cr | E-series, AES |
| **HE-500 Horizon** | AES | 3.5 | 150 | 40 | 17 | 58 000 cr | E-series haute gamme |
| **HE-600 Strike** | VDI | 5.0 | 130 | 38 | 15 | 95 000 cr | F-series |
| **HE-900 Phantom** | VDI | 7.5 | 120 | 32 | 12 | 200 000 cr | F-series militaire, rare |
| **HE-ECO Basic** | Lunastar | 0.7 | 220 | 60 | 22 | 6 000 cr | Polyvalent économique |

---

## 4. SYSTÈMES DE RECHARGEMENT / PROPULSION ÉNERGÉTIQUE (Type B)

Pour les vaisseaux à extraction énergétique. Le système de rechargement remplace la réserve de combustible.

### 4.1 Micro-panneaux solaires (vaisseau immobile pendant recharge)

| Modèle | Marque | Puissance extraite (par PA) | Masse | Prix |
|---|---|---|---|---|
| **GVT-MP100 Standard** | Graviton | Puissance_étoile × 1.0 | 200 t | 4 000 cr |
| **GVT-MP200 Amélioré** | Graviton | Puissance_étoile × 1.5 | 300 t | 9 000 cr |
| **GVT-MP500 Haute Densité** | AES | Puissance_étoile × 2.5 | 450 t | 24 000 cr |
| **SunLeaf Compact** | Lunastar | Puissance_étoile × 0.8 | 150 t | 2 500 cr |

### 4.2 Voiles solaires (déplacement possible pendant recharge)

| Modèle | Marque | Puissance extraite | Masse | Prix | Note |
|---|---|---|---|---|---|
| **GVT-VS100 Brise** | Graviton | Puissance_étoile × 1.2 | 800 t | 18 000 cr | 2 mâts |
| **GVT-VS300 Alizé** | Graviton | Puissance_étoile × 2.0 | 1 400 t | 42 000 cr | 4 mâts |
| **Argète-Sail** | AES | Puissance_étoile × 3.0 | 2 000 t | 90 000 cr | Voile en argète, très efficace |

> Les voiles en argète-stellaire captent un spectre élargi, y compris les naines rouges.

### 4.3 Extracteurs de matière noire (gros vaisseaux)

| Modèle | Marque | Puissance extraite | Masse | Équipage min. | Prix |
|---|---|---|---|---|---|
| **GVT-MN100 Trail** | Graviton | Puissance_étoile × 2.0 (indépendant distance) | 3 000 t | 3 | 150 000 cr |
| **Void-Anchor Mk2** | Titan | Puissance_étoile × 3.5 (indépendant distance) | 8 000 t | 6 | 400 000 cr |

---

## 5. RÉSERVOIRS DE COMBUSTIBLE (Type A)

### 5.1 Catalogue

| Modèle | Marque | Carburant | Capacité | Efficacité | Récupération/cargo | Masse | Prix |
|---|---|---|---|---|---|---|---|
| **AVP-U200 Uranium** | Ashvelt | Uranium | 200 u | 0.75 | 150 u | 400 t | 6 000 cr |
| **AVP-U500 Uranium+** | Ashvelt | Uranium | 500 u | 0.78 | 150 u | 900 t | 14 000 cr |
| **AVP-P300 Plasma** | Ashvelt | Plasma (H₂) | 300 u | 0.80 | 120 u | 300 t | 9 000 cr |
| **AVP-P600 Plasma+** | Ashvelt | Plasma (H₂) | 600 u | 0.82 | 120 u | 550 t | 18 000 cr |
| **AVP-T250 Tyberium** | Ashvelt | Tyberium | 250 u | 0.90 | 100 u | 250 t | 15 000 cr |
| **AVP-T500 Tyberium+** | Ashvelt | Tyberium | 500 u | 0.90 | 100 u | 450 t | 28 000 cr |
| **RenFuel Flex** | Hullworks | Plasma ou Uranium | 200 u | 0.72 | 130 u | 350 t | 5 000 cr |

> Le Tyberium est le plus efficace et le moins dangereux mais reste rare — disponible principalement dans les systèmes à anneaux planétaires.

---

## 6. MODULES EN SOUTE (Emplacements convertis)

Un vaisseau peut sacrifier des baies de cargaison pour installer des modules. Chaque module occupe **1 ou plusieurs soutes**. Aucun module ne peut occuper les 12 emplacements fonctionnels (qui sont fixes par la coque).

### 6.1 Transport de personnel

| Module | Soutes | Capacité | Durabilité | Prix |
|---|---|---|---|---|
| **Kit Passager Succinct** | 1 | 10 personnes / soute | 1-2 utilisations | 500 cr |
| **Module Passager Standard** | 1 | 5 personnes / soute | Permanent | 3 000 cr |
| **Cabines 1ère Classe** | 1 | 3 personnes / soute | Permanent | 9 000 cr |
| **Transports de Troupe** | 1 | 20 personnes / soute | Permanent, inconfortable | 2 000 cr |

### 6.2 Science & Exploration

| Module | Soutes | Effet | Prix |
|---|---|---|---|
| **Laboratoire d'Analyse** | 1 | Bonus puissance scan +20, analyse ressources | 18 000 cr |
| **Module Sondage** | 1 | Permet sondage planétaire, +portée scan 1 AL | 25 000 cr |
| **Télescope Long-Portée** | 2 | +portée scan 3 AL, nécessite déploiement (arrêt) | 40 000 cr |

### 6.3 Combat & Défense

| Module | Soutes | Effet | Prix |
|---|---|---|---|
| **Tourelle Auxiliaire** | 1 | +1 emplacement d'arme (fixe, limité à gauche/droite) | 22 000 cr |
| **Bouclier Secondaire** | 1 | Bouclier d'appoint 100 pts, non régénérant | 12 000 cr |
| **Module Leurres** | 1 | Peut déployer 3 leurres par combat | 8 000 cr |
| **EMP Tactique** | 1 | Désactive systèmes ennemis, usage unique / escale | 15 000 cr |
| **Module Furtivité** | 2 | Réduit détectabilité -50% au scan adverse | 80 000 cr |

### 6.4 Logistique & Maintenance

| Module | Soutes | Effet | Prix |
|---|---|---|---|
| **Atelier de Bord** | 1 | +score entretien en vol, répare pannes faibles | 14 000 cr |
| **Module Médical** | 1 | Soins équipage, +récup PA personnage (+1 PA/h) | 10 000 cr |
| **Mini-Forge** | 1 | Transformation matières 1er niveau en vol | 30 000 cr |
| **Réservoir Carburant Extra** | 1 | +200 u du combustible actuel | 3 000 cr (+ cargaison) |
| **Hangar Parasite** | 2 | Peut embarquer et transporter un vaisseau A-0 | 45 000 cr |

### 6.5 Commerce & Administration

| Module | Soutes | Effet | Prix |
|---|---|---|---|
| **Salle de Marché** | 1 | Permet transactions marchandes à distance (-5% prix) | 18 000 cr |
| **Module de Représentation** | 1 | Bonus influence diplomatique, accès à certaines stations | 25 000 cr |

---

## 7. SYSTÈME INFORMATIQUE (SI)

Le système informatique est le cerveau du vaisseau. Il détermine le nombre de **places de service** disponibles pour les démons logiciels.

### 7.1 Modules informatiques (emplacement n°5 des 12 slots)

| Modèle | Marque | Places de service | Prix | Note |
|---|---|---|---|---|
| **SI-3 Nano** | StellarTech | 3 | 2 000 cr | A-0, drones, vaisseaux parasites |
| **SI-5 Standard** | StellarTech | 5 | 6 000 cr | Défaut A-1, la plupart des petits vaisseaux |
| **SI-7 Pro** | StellarTech | 7 | 16 000 cr | M-1, E-series entrée |
| **SI-10 Master** | StellarTech | 10 | 38 000 cr | E-series avancé, F-series |
| **SI-10 Tactical** | DataCore | 10 | 55 000 cr | Optimisé combat, bonus visée |
| **SI-8 Deep Space** | AES | 8 | 28 000 cr | Optimisé exploration, bonus atlas |

> Un module informatique détérioré peut perdre des places de service (panne système).
> On ne peut pas avoir plus de 10 places de services par module informatique.

---

## 8. LES DÉMONS (Programmes de service)

Un **démon** est un programme logiciel s'exécutant en tâche de fond sur le système informatique du vaisseau. Il occupe un certain nombre de **places de service**. Certains sont obligatoires dès la mise en service.

Un démon peut être mis en veille (libère les places de service mais perd sa fonction), remplacé ou mis à jour en station.

### 8.1 Démons préinstallés (obligatoires / sur tout vaisseau opérationnel)

Ces trois démons sont fournis avec tout vaisseau neuf ou remis à neuf.

---

#### 🟢 GESTION CAPTEURS — 1 place de service

> *"Votre interface entre le vaisseau et l'univers."*

**Marque :** StellarTech / DataCore Systems
**Rôle :** Gère l'ensemble des capteurs passifs et actifs du vaisseau.

**Fonctions couvertes :**
- **Communications** : réception/émission des signaux radio, relais inter-systèmes
- **Détection** : radar passif de proximité, identification des objets spatiaux environnants
- **Status** : supervision de l'état général du vaisseau (affichage des jauges coque/énergie/bouclier)
- **Survie** : atmosphère, pression, température, systèmes de survie de l'équipage

**Désactiver ce démon :** communications coupées, alerte de proximité aveugle, tableau de bord muet.

---

#### 🟢 PROPULSION & PILOTAGE — 1 place de service

> *"Le copilote invisible."*

**Marque :** Kronos Engines / Graviton Power Systems
**Rôle :** Interface logicielle entre le pilote et les systèmes de propulsion.

**Fonctions couvertes :**
- **Gestion des propulseurs** : régulation des poussées conventionnelles, gestion thermique
- **Déploiement des panneaux solaires / voiles** : automatisation du déploiement/repli (Type B)
- **Assistance au pilotage** : correcteurs d'assiette, frein orbital, sécurités de crash
- **Calcul de trajectoire de base** : route simple, approche station, calcul de saut

**Désactiver ce démon :** le pilotage manuel reste possible mais pénalisé ; pas de déploiement automatique des panneaux ; saut HE impossible.

---

#### 🟢 ATLAS & BASE DE DONNÉES — 1 place de service

> *"La mémoire collective des étoiles connues."*

**Marque :** DataCore Systems
**Rôle :** Gestionnaire de données cartographiques et de connaissances du vaisseau.

**Fonctions couvertes :**
- **Atlas galactique** : cartes des systèmes découverts, positions, distances, types d'étoiles
- **Bibliothèque de navigation** : historique des routes effectuées, points de passage enregistrés
- **Base de données ressources** : gisements connus, prix marchés enregistrés, fiches stations
- **Journal de bord** : log automatique des événements de vol

**Désactiver ce démon :** pas de spatiocarte, pas d'historique, pas de base de données marchés.

---

### 8.2 Démons optionnels — Combat & Tactique

| Démon | Places | Marque | Fonctions | Prix |
|---|---|---|---|---|
| **Système de Visée Multiple** | 2 | Nexus Arms | Vise plusieurs cibles simultanément, bonus précision +10 | 12 000 cr |
| **Reconnaissance Tactique** | 3 | VDI / DataCore | Analyse adversaire, prédit tactique ennemie, donne son type AI | 25 000 cr |
| **Protocole Sécurité** | 1 | OmniShield | Détection intrusions, verrouillage accès, alerte sabotage | 8 000 cr |
| **Gestion Bouclier Avancée** | 1 | OmniShield | Optimise regénération bouclier, résistance sélective par type d'arme | 15 000 cr |
| **Brouilleur Tactique** | 2 | VoidCraft | Perturbe scanners adverses, réduit précision ennemie -15% | 30 000 cr |

### 8.3 Démons optionnels — Navigation & Exploration

| Démon | Places | Marque | Fonctions | Prix |
|---|---|---|---|---|
| **Calcul Trajectoire Avancé** | 1 | Stratos / AES | Réduit l'erreur de saut HE, bonus au jet d'astrogation | 10 000 cr |
| **Navigation Autonome** | 2 | StellarTech | Pilotage automatique sur route pré-calculée (manœuvres simples) | 20 000 cr |
| **Analyse Spectrométrique** | 1 | AES | Identifie composition étoiles et planètes depuis l'espace, bonus découverte | 14 000 cr |
| **Cartographie Active** | 2 | DataCore | Enrichit l'atlas automatiquement pendant le vol, partage avec guilde | 18 000 cr |
| **Détecteur Anomalies** | 1 | AES | Signale épaves, champs de déchets, objets inhabituels dans la portée scan | 9 000 cr |

### 8.4 Démons optionnels — Logistique & Commerce

| Démon | Places | Marque | Fonctions | Prix |
|---|---|---|---|---|
| **IA Commerce** | 1 | DataCore | Analyse les prix marchés connus, recommande les routes commerciales | 7 000 cr |
| **Démon de Maintenance** | 2 | StellarTech | Monitoring pannes, alerte précoce, réduit probabilité panne +score entretien | 16 000 cr |
| **Gestion Équipage** | 1 | BioSurvival | Plannings, moral, besoins de l'équipage, alerte mutinerie | 6 000 cr |
| **Optimiseur de Soute** | 1 | MCG | Réduit masse variable effective -5% (meilleur arrimage) | 5 000 cr |

### 8.5 Démons illégaux / de contrebande *(vendus uniquement dans certaines cantinas)*

| Démon | Places | Fonctions | Note |
|---|---|---|---|
| **Fantôme de Transpondeur** | 1 | Masque ou falsifie l'identifiant IFF du vaisseau | Illégal dans la plupart des systèmes |
| **Override Douanier** | 2 | Bypass les demandes d'inspection automatisées des stations | Illégal, expulsion si découvert |
| **Démon de Piratage** | 3 | Tente de prendre le contrôle de systèmes vaisseaux adjacents | Interdit partout |

---

## 9. RÉCAPITULATIF : PLACES DE SERVICE PAR CONFIGURATION

### Exemple — Vaisseau A-1 de base (SI-5, 5 places)

| # | Démon | Places | Statut |
|---|---|---|---|
| 1 | Gestion Capteurs | 1 | Obligatoire |
| 2 | Propulsion & Pilotage | 1 | Obligatoire |
| 3 | Atlas & Base de Données | 1 | Obligatoire |
| 4 | *Libre* | 1 | Disponible |
| 5 | *Libre* | 1 | Disponible |

**Places restantes : 2** pour des démons optionnels.

---

### Exemple — E-series explorateur (SI-8 Deep Space, 8 places)

| # | Démon | Places | Statut |
|---|---|---|---|
| 1 | Gestion Capteurs | 1 | Obligatoire |
| 2 | Propulsion & Pilotage | 1 | Obligatoire |
| 3 | Atlas & Base de Données | 1 | Obligatoire |
| 4 | Analyse Spectrométrique | 1 | Installé |
| 5-6 | Cartographie Active | 2 | Installé |
| 7 | Détecteur Anomalies | 1 | Installé |
| 8 | *Libre* | 1 | Disponible |

**Places restantes : 1**

---

### Exemple — F-series militaire (SI-10 Tactical, 10 places)

| # | Démon | Places | Statut |
|---|---|---|---|
| 1 | Gestion Capteurs | 1 | Obligatoire |
| 2 | Propulsion & Pilotage | 1 | Obligatoire |
| 3 | Atlas & Base de Données | 1 | Obligatoire |
| 4-5 | Système de Visée Multiple | 2 | Installé |
| 6-8 | Reconnaissance Tactique | 3 | Installé |
| 9 | Gestion Bouclier Avancée | 1 | Installé |
| 10 | Protocole Sécurité | 1 | Installé |

**Places restantes : 0** — configuration saturée.

---

## 10. INTÉGRATION EN JEU (notes de développement)

### Modèle de données suggéré

```
vaisseaux.system_informatique     → INT (nombre de places max, lié au module SI)
vaisseaux.programmes              → JSON [{ "id": "atlas", "nom": "Atlas & BDD", "places": 1, "actif": true }, ...]
```

### Règles de validation

- Total `places` des démons actifs ≤ `system_informatique`
- Les 3 démons obligatoires ne peuvent pas être supprimés (seulement mis en veille si places insuffisantes → pénalités)
- Un démon en veille n'a aucun effet mais n'occupe plus de place
- Installer un démon nécessite d'être amarré à une station avec un module Garage

### Affichage dans l'ingénierie (à implémenter)

Section "Informatique" à ajouter dans la page `/navire/ingenierie` :
- Barre d'occupation : X / Y places de service utilisées
- Liste des démons actifs avec leurs fonctions
- Alerte si démon obligatoire en veille (dysfonctionnement)

---

*Document créé le 2026-05-15 — à compléter lors de l'implémentation des modules achetables en station.*
