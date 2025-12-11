# GAME DESIGN DOCUMENT
## Jeu d'Exploration Galactique - Interface Web

---

## ÄÅ¸ââ¹ TABLE DES MATIÃËRES

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture modulaire multi-univers](#architecture-modulaire)
3. [SystÃÂ¨me de jeu core](#systÃÂ¨me-de-jeu-core)
4. [Interface utilisateur](#interface-utilisateur)
5. [Navigation et dÃÂ©placements](#navigation-et-dÃÂ©placements)
6. [DÃÂ©tection et exploration](#dÃÂ©tection-et-exploration)
7. [Vaisseaux et ÃÂ©quipements](#vaisseaux-et-ÃÂ©quipements)
8. [Ãâ°conomie et ressources](#ÃÂ©conomie-et-ressources)
9. [Combat et zones de contrÃÂ´le](#combat-et-zones-de-contrÃÂ´le)
10. [RÃÂ©putation et factions](#rÃÂ©putation-et-factions)
11. [GÃÂ©nÃÂ©ration procÃÂ©durale](#gÃÂ©nÃÂ©ration-procÃÂ©durale)
12. [Points d'intÃÂ©rÃÂªt (PoV)](#points-dintÃÂ©rÃÂªt-pov)
13. [Architecture technique](#architecture-technique)

---

## ÄÅ¸ÅÅ VUE D'ENSEMBLE

### Concept
Jeu web d'exploration galactique au tour par tour, avec interface graphique et systÃÂ¨me de commandes. Les joueurs explorent la Voie LactÃÂ©e en utilisant des donnÃÂ©es rÃÂ©elles (GAIA, NASA) combinÃÂ©es ÃÂ  de la gÃÂ©nÃÂ©ration procÃÂ©durale.

### Piliers de gameplay
- **Exploration** : DÃÂ©couvrir des systÃÂ¨mes stellaires inconnus avec risques et rÃÂ©compenses
- **Ãâ°conomie** : ChaÃÂ®ne de production complexe (extraction Ã¢â â raffinage Ã¢â â production)
- **Commerce** : Empire commercial, routes, nÃÂ©goce de ressources et informations
- **Combat** : Affrontements tactiques tour par tour avec gestion de ressources
- **Diplomatie** : SystÃÂ¨me de rÃÂ©putation avec guildes et factions

**Note importante :** Le jeu ne se concentre pas sur la conquÃÂªte territoriale classique et la construction d'empire militaire. L'accent est mis sur l'exploration, le commerce, et la construction d'un rÃÂ©seau d'influence ÃÂ©conomique. Les mÃÂ©caniques de contrÃÂ´le territorial sont prÃÂ©vues pour dÃÂ©veloppement ultÃÂ©rieur, mais pas prioritaires au dÃÂ©but.

### Format
- **Interface** : Web avec visualisation graphique + console de commandes
- **Rythme** : Tour par tour (1 tour = 1 jour in-game)
- **Mode de jeu** : **ASYNCHRONE** - Les joueurs ne jouent pas en mÃÂªme temps
- **Multijoueur** : Persistant, joueurs humains + IA
- **Progression** : Du vaisseau starter ÃÂ  un rÃÂ©seau commercial ÃÂ©tendu

**Implication du mode asynchrone :**
- Les joueurs jouent ÃÂ  leur propre rythme
- Combat PvP nÃÂ©cessite une **mÃÂ©canique asymÃÂ©trique automatique**
- RÃÂ¨gles de comportement de dÃÂ©fense/attaque prÃÂ©dÃÂ©finies
- Seuils de fuite automatique
- SystÃÂ¨me gÃÂ¨re les combats entre joueurs absents

---

## ÄÅ¸âÂ§ ARCHITECTURE MODULAIRE MULTI-UNIVERS

### Objectif
Le moteur de jeu doit supporter plusieurs univers de science-fiction sans modification majeure du code core.

### Univers supportÃÂ©s (prÃÂ©vus)
1. **Archiluminique** (univers original du jeu)
2. **ConquÃÂªte Spatiale** (proche de la rÃÂ©alitÃÂ©, vaisseaux avec hyper-espace)
3. **Star Wars** (Guerre Civile Galactique, etc.)
4. **Warhammer 40K** (Imperium, Chaos, Xenos)
5. **Star Citizen** (UEE, systÃÂ¨mes Stanton, etc.)

### Couches d'abstraction

```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   CONTENU UNIVERS (modules)         Ã¢ââ
Ã¢ââ   - Factions                         Ã¢ââ
Ã¢ââ   - Vaisseaux                        Ã¢ââ
Ã¢ââ   - Technologies                     Ã¢ââ
Ã¢ââ   - Lore / Ãâ°vÃÂ©nements                Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
              Ã¢â â
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   RÃËGLES UNIVERS                     Ã¢ââ
Ã¢ââ   - Vitesses FTL                     Ã¢ââ
Ã¢ââ   - Types d'armes                    Ã¢ââ
Ã¢ââ   - Ressources spÃÂ©cifiques           Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
              Ã¢â â
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   MOTEUR CORE (universel)            Ã¢ââ
Ã¢ââ   - Navigation                       Ã¢ââ
Ã¢ââ   - DÃÂ©tection                        Ã¢ââ
Ã¢ââ   - Combat (systÃÂ¨me de dÃÂ©s)          Ã¢ââ
Ã¢ââ   - Ãâ°conomie                         Ã¢ââ
Ã¢ââ   - RÃÂ©putation                       Ã¢ââ
Ã¢ââ   - GÃÂ©nÃÂ©ration procÃÂ©durale           Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

### Configuration par univers
Fichiers de configuration JSON/YAML dÃÂ©finissant :
- Noms des factions
- Stats des vaisseaux
- Arbres technologiques
- ParamÃÂ¨tres de balance (vitesses, coÃÂ»ts, etc.)

---

## ÄÅ¸ï¿½Â² SYSTÃËME DE JEU CORE

### SystÃÂ¨me de dÃÂ©s : Daggerheart (2D12)

**MÃÂ©canisme central :**

Le systÃÂ¨me utilise des **DÃÂ©s de DualitÃÂ©** :
- **2d12** de couleurs diffÃÂ©rentes : un dÃÂ© d'**Espoir** (Hope) et un dÃÂ© de **Peur** (Fear)
- Formule : `Somme 2d12 + Trait + Modificateurs` vs DifficultÃÂ©

**RÃÂ©solution d'action :**

1. **Lancer les 2d12** (Hope + Fear)
2. **ADDITIONNER les deux dÃÂ©s** + Trait + Modificateurs
   - **Trait** : valeur du trait appropriÃÂ© (AgilitÃÂ©, Force, Finesse, Instinct, PrÃÂ©sence, Connaissance)
   - **Modificateurs** : bonus d'ÃÂ©quipement, circonstances, etc.
   - **Note** : Les compÃÂ©tences seront dÃÂ©taillÃÂ©es ultÃÂ©rieurement
3. **Comparer au seuil de difficultÃÂ©** :
   - Ã¢â°Â¥ Seuil : **SuccÃÂ¨s**
   - < Seuil : **Ãâ°chec**

4. **GÃÂ©nÃÂ©ration de jetons** (indÃÂ©pendant du succÃÂ¨s) :
   - Si **dÃÂ© d'Espoir > dÃÂ© de Peur** Ã¢â â gÃÂ©nÃÂ¨re des **jetons d'Espoir** pour les joueurs
   - Si **dÃÂ© de Peur > dÃÂ© d'Espoir** Ã¢â â gÃÂ©nÃÂ¨re des **jetons de Peur** pour le systÃÂ¨me (cachÃÂ©)
   - Si **ÃÂ©galitÃÂ© (sauf 1-1)** Ã¢â â **CRITIQUE !** RÃÂ©ussite exceptionnelle + gÃÂ©nÃÂ¨re **1 jeton d'Espoir**
   - Si **1-1** Ã¢â â **CATASTROPHE !** GÃÂ©nÃÂ¨re **1 jeton de Peur** (systÃÂ¨me)

**Points clÃÂ©s :**
- Le rÃÂ©sultat = **Somme des 2 dÃÂ©s** + modificateurs (pas le meilleur)
- On peut **rÃÂ©ussir avec Peur** (somme ÃÂ©levÃÂ©e mais Fear > Hope Ã¢â â jeton systÃÂ¨me)
- On peut **ÃÂ©chouer avec Espoir** (somme faible mais Hope > Fear Ã¢â â jeton joueur)
- **Ãâ°galitÃÂ© (sauf 1-1)** = **CRITIQUE** Ã¢â â RÃÂ©ussite exceptionnelle + jeton Espoir
- **Double 1 (1-1)** = **CATASTROPHE** Ã¢â â GÃÂ©nÃÂ¨re jeton de Peur (complications assurÃÂ©es)
- Les jetons sont une ressource narrative pour influencer l'histoire

---

### Les 6 Traits

Chaque personnage/vaisseau possÃÂ¨de 6 traits (valeur numÃÂ©rique) :

**Traits physiques :**
- **AgilitÃÂ©** - Mouvement, esquive, dextÃÂ©ritÃÂ©
- **Force** - Combat physique, puissance
- **Finesse** - PrÃÂ©cision, discrÃÂ©tion, manipulation

**Traits mentaux :**
- **Instinct** - Intuition, survie, perception
- **PrÃÂ©sence** - Charisme, leadership, intimidation
- **Connaissance** - Ãâ°rudition, analyse, technologie

**Note importante :**
- Les **Traits** reprÃÂ©sentent les **valeurs minimales** des compÃÂ©tences
- Les **CompÃÂ©tences** sont utilisÃÂ©es pour les jets de dÃÂ©s spÃÂ©cifiques
- Toutes les compÃÂ©tences n'ajouteront pas nÃÂ©cessairement au jet de dÃÂ©s

---

### Les CompÃÂ©tences

Chaque compÃÂ©tence est associÃÂ©e ÃÂ  un **Trait de base**. Lors d'un jet, on utilise :
- La valeur de la **compÃÂ©tence** (si applicable)
- Le **trait associÃÂ©** comme valeur minimale

**Liste des compÃÂ©tences :**

**CompÃÂ©tences de Navigation & Technique :**
- **Astrogation** (Connaissance) - Calculs hyperespace, navigation stellaire
- **Pilotage** (AgilitÃÂ©) - ManÃâuvres vaisseau, combat spatial
- **Informatique** (Connaissance) - SystÃÂ¨mes informatiques, piratage
- **MÃÂ©canique** (Finesse) - RÃÂ©parations, maintenance, bricolage

**CompÃÂ©tences Sociales :**
- **Charme** (PrÃÂ©sence) - SÃÂ©duction, manipulation douce
- **Coercition** (Force) - Intimidation, menaces
- **Commandement** (Instinct) - Leadership, tactique d'ÃÂ©quipe
- **NÃÂ©gociation** (PrÃÂ©sence) - Commerce, diplomatie

**CompÃÂ©tences de Survie & Perception :**
- **Perception** (Instinct) - DÃÂ©tection, vigilance, observation
- **Survie** (Instinct) - Environnements hostiles, dÃÂ©brouillardise
- **MÃÂ©decine** (Connaissance) - Soins, chirurgie, biologie

**CompÃÂ©tences de Combat :**
- **Artillerie** (Finesse) - Canons, tourelles, armes ÃÂ©nergÃÂ©tiques
- **Arme Lourde** (Finesse) - Railguns, torpilles, armes lourdes
- **Missile** (Instinct) - Guidage missiles, tactique de tir

**CompÃÂ©tences SpÃÂ©ciales :**
- **MarchÃÂ© Noir** (Connaissance) - Contacts illÃÂ©gaux, contrebande, ressources rares

---

### SystÃÂ¨me asymÃÂ©trique : Joueurs vs SystÃÂ¨me

**Joueurs :**
- Utilisent **2d12** (DÃÂ©s de DualitÃÂ©)
- GÃÂ©nÃÂ¨rent des jetons d'Espoir (visibles, ressource du joueur)
- GÃÂ©nÃÂ¨rent des jetons de Peur (invisibles, capital du systÃÂ¨me)

**SystÃÂ¨me/IA (adversaires, environnement) :**
- Utilise **1d20** pour contrÃÂ´ler les ennemis
- Accumule les jetons de Peur (CACHÃâ°S du joueur)
- Utilise automatiquement les jetons pour crÃÂ©er :
  - Complications narratives
  - Ãâ°vÃÂ©nements imprÃÂ©vus
  - Dangers et embuscades
  - Renforcer ennemis
  - Rendre l'environnement hostile

**Capital de Peur (invisible) :**
Le joueur ne voit PAS combien de jetons de Peur le systÃÂ¨me possÃÂ¨de. Cela crÃÂ©e de la tension et de l'incertitude.

**SystÃÂ¨me de dÃÂ©clenchement :**

```
Ãâ¬ chaque action significative (ou fin de tour) :
1. Jet de dÃÂ©s : 1d60 (ou liÃÂ© ÃÂ  fiabilitÃÂ© vaisseau)
2. Si rÃÂ©sultat < Capital Peur accumulÃÂ© Ã¢â â Ãâ°VÃâ°NEMENT
3. Ãâ°vÃÂ©nement consomme X jetons de Peur selon importance
4. Si pas d'ÃÂ©vÃÂ©nement Ã¢â â Capital Peur continue d'augmenter
```

**Formule de dÃÂ©clenchement :**
```
Jet : 1d60 (ou 1dX selon fiabilitÃÂ© vaisseau)
Capital Peur accumulÃÂ© : N jetons

Si Jet < N Ã¢â â Ãâ°vÃÂ©nement dÃÂ©clenchÃÂ©
```

**Exemple :**
```
Capital Peur : 15 jetons
FiabilitÃÂ© vaisseau : Standard (1d60)
Jet : 1d60 = 12
12 < 15 Ã¢â â Ãâ°VÃâ°NEMENT !

Ã¢â â SystÃÂ¨me dÃÂ©clenche "Embuscade pirate" (coÃÂ»t 8 jetons)
Ã¢â â Capital Peur restant : 15 - 8 = 7 jetons
```

**Ãâ°vÃÂ©nements et coÃÂ»ts en Peur :**

```
COMPLICATIONS MINEURES (coÃÂ»t 2-5 jetons)
Ã¢âÅÃ¢ââ¬ Panne mineure (coÃÂ»t 1-2 PA rÃÂ©paration)
Ã¢âÅÃ¢ââ¬ DÃÂ©viation navigation (+0.1-0.3 AL)
Ã¢âÅÃ¢ââ¬ Contact radio parasite / fausse alerte
Ã¢ââÃ¢ââ¬ Micro-mÃÂ©tÃÂ©orite (-5 HP)

COMPLICATIONS MAJEURES (coÃÂ»t 6-10 jetons)
Ã¢âÅÃ¢ââ¬ Embuscade pirate (1-3 ennemis)
Ã¢âÅÃ¢ââ¬ Anomalie spatiale (obstacle navigation)
Ã¢âÅÃ¢ââ¬ Panne systÃÂ¨me critique (arme/bouclier/moteur)
Ã¢ââÃ¢ââ¬ Rencontre hostile imprÃÂ©vue

Ãâ°VÃâ°NEMENTS CRITIQUES (coÃÂ»t 11-20 jetons)
Ã¢âÅÃ¢ââ¬ Embuscade coordonnÃÂ©e (5+ ennemis)
Ã¢âÅÃ¢ââ¬ Catastrophe environnementale (tempÃÂªte, radiation)
Ã¢âÅÃ¢ââ¬ Trahison/sabotage interne
Ã¢ââÃ¢ââ¬ Apparition ÃÂ©lite/boss ennemi

Ãâ°VÃâ°NEMENTS MAJEURS (coÃÂ»t 20+ jetons)
Ã¢âÅÃ¢ââ¬ Flotte ennemie (10+ vaisseaux)
Ã¢âÅÃ¢ââ¬ DÃÂ©sastre systÃÂ¨me (supernova, trou noir)
Ã¢âÅÃ¢ââ¬ Intervention faction majeure
Ã¢ââÃ¢ââ¬ Arc narratif dÃÂ©clenchÃÂ©
```

**FiabilitÃÂ© du vaisseau (modificateur) :**

```
Vaisseau neuf/bien entretenu : 1d60 (standard)
Vaisseau usÃÂ© : 1d50 (ÃÂ©vÃÂ©nements plus frÃÂ©quents)
Vaisseau dÃÂ©labrÃÂ© : 1d40 (trÃÂ¨s instable)
Vaisseau militaire : 1d80 (trÃÂ¨s fiable)
Vaisseau prototype : 1d100 (extrÃÂªmement fiable)
```

**Ã¢Å¡Â Ã¯Â¸ï¿½ COHÃâ°RENCE NARRATIVE - RÃËGLES IMPORTANTES :**

**1. Persistance des ÃÂ©vÃÂ©nements gÃÂ©nÃÂ©rÃÂ©s :**

```
Ãâ°vÃÂ©nement crÃÂ©ÃÂ© Ã¢â â StockÃÂ© en base de donnÃÂ©es
Ã¢âÅÃ¢ââ¬ Position exacte (systÃÂ¨me, coordonnÃÂ©es)
Ã¢âÅÃ¢ââ¬ Type (flotte pirate, anomalie, etc.)
Ã¢âÅÃ¢ââ¬ DurÃÂ©e de vie / Persistance
Ã¢ââÃ¢ââ¬ Ãâ°tat (actif, en mouvement, disparu)

Exemple : Flotte pirate gÃÂ©nÃÂ©rÃÂ©e
Ã¢âÅÃ¢ââ¬ CrÃÂ©ÃÂ©e ÃÂ  : SystÃÂ¨me Alpha, secteur B-4
Ã¢âÅÃ¢ââ¬ Reste lÃÂ  : 10-30 tours minimum
Ã¢âÅÃ¢ââ¬ Peut se dÃÂ©placer : Selon IA/patrouille
Ã¢âÅÃ¢ââ¬ DisparaÃÂ®t si : DÃÂ©truite OU ÃÂ©vÃÂ©nement timer ÃÂ©coulÃÂ©
```

**2. Ãâ°vÃÂ©nements localisÃÂ©s :**

Les ÃÂ©vÃÂ©nements sont **liÃÂ©s ÃÂ  un lieu spÃÂ©cifique** :

```
TYPE 1 : Ãâ°vÃÂ©nements fixes (persistent longtemps)
Ã¢âÅÃ¢ââ¬ Flotte pirate Ã¢â â Reste dans secteur 20-50 tours
Ã¢âÅÃ¢ââ¬ Champ d'astÃÂ©roÃÂ¯des Ã¢â â Permanent (jusqu'ÃÂ  exploitation)
Ã¢âÅÃ¢ââ¬ Anomalie spatiale Ã¢â â Reste 50-100 tours
Ã¢ââÃ¢ââ¬ Base ennemie Ã¢â â Permanente (jusqu'ÃÂ  destruction)

TYPE 2 : Ãâ°vÃÂ©nements temporaires (disparaissent)
Ã¢âÅÃ¢ââ¬ TempÃÂªte solaire Ã¢â â 5-10 tours
Ã¢âÅÃ¢ââ¬ Nuage ionisÃÂ© Ã¢â â 10-20 tours
Ã¢âÅÃ¢ââ¬ Passage flotte commerciale Ã¢â â 2-5 tours
Ã¢ââÃ¢ââ¬ Signal de dÃÂ©tresse Ã¢â â 5-15 tours

TYPE 3 : Ãâ°vÃÂ©nements vaisseau (suivent le joueur)
Ã¢âÅÃ¢ââ¬ Panne systÃÂ¨me Ã¢â â Jusqu'ÃÂ  rÃÂ©paration
Ã¢âÅÃ¢ââ¬ Trahison ÃÂ©quipage Ã¢â â Ãâ°vÃÂ©nement narratif unique
Ã¢âÅÃ¢ââ¬ Malus temporaire Ã¢â â DurÃÂ©e dÃÂ©finie
Ã¢ââÃ¢ââ¬ Poursuite ennemie Ã¢â â Jusqu'ÃÂ  combat/fuite
```

**3. VÃÂ©rification de cohÃÂ©rence avant gÃÂ©nÃÂ©ration :**

```
Avant de dÃÂ©clencher un ÃÂ©vÃÂ©nement :
1. VÃÂ©rifier la position du joueur
2. VÃÂ©rifier les ÃÂ©vÃÂ©nements dÃÂ©jÃÂ  actifs dans la zone
3. Choisir un ÃÂ©vÃÂ©nement compatible avec le contexte
4. Si flotte gÃÂ©nÃÂ©rÃÂ©e Ã¢â â CrÃÂ©er entitÃÂ© persistante en BDD
5. Marquer l'ÃÂ©vÃÂ©nement avec timestamp et durÃÂ©e

Exemple :
- Joueur dans systÃÂ¨me paisible (zone Empire)
  Ã¢â â Pas de grosse flotte pirate (incohÃÂ©rent)
  Ã¢â â PlutÃÂ´t : panne, petite patrouille pirate isolÃÂ©e
  
- Joueur dans espace sauvage
  Ã¢â â Flotte pirate cohÃÂ©rente
  Ã¢â â StockÃÂ©e en BDD avec position et patrouille
```

**4. Recyclage d'ÃÂ©vÃÂ©nements existants :**

```
Si ÃÂ©vÃÂ©nement Peur doit se dÃÂ©clencher :
1. Chercher ÃÂ©vÃÂ©nements actifs prÃÂ¨s de la position joueur
2. Si ÃÂ©vÃÂ©nement compatible existe Ã¢â â L'utiliser (rencontre)
3. Sinon Ã¢â â CrÃÂ©er nouvel ÃÂ©vÃÂ©nement

Exemple :
- Flotte pirate gÃÂ©nÃÂ©rÃÂ©e tour 15 ÃÂ  Alpha-B4
- Joueur arrive Alpha-B3 au tour 20
- Capital Peur dÃÂ©clenche ÃÂ©vÃÂ©nement
Ã¢â â Au lieu de crÃÂ©er nouvelle flotte
Ã¢â â Utiliser la flotte existante (elle patrouille)
Ã¢â â "Vous ÃÂªtes dÃÂ©tectÃÂ© par la flotte pirate !"
```

**ImplÃÂ©mentation technique :**

```javascript
// Table base de donnÃÂ©es
fear_events (
  id,
  type,
  position_system_id,
  position_x, position_y, position_z,
  created_turn,
  expires_turn,
  status (active/expired/destroyed),
  data_json
)

// Fonction dÃÂ©clenchement
function checkFearEvent(player) {
  // Jet de fiabilitÃÂ©
  const reliability_die = player.ship.reliability_die; // ex: 60
  const roll = random(1, reliability_die);
  const fear_capital = system.fear_tokens;
  
  if (roll < fear_capital) {
    // Ãâ°vÃÂ©nement dÃÂ©clenchÃÂ© !
    
    // 1. Chercher ÃÂ©vÃÂ©nements existants proches
    const nearby_events = db.query(`
      SELECT * FROM fear_events 
      WHERE position_system_id = ? 
      AND status = 'active'
      AND expires_turn > ?
    `, [player.system_id, current_turn]);
    
    // 2. Si ÃÂ©vÃÂ©nement compatible existe, l'utiliser
    if (nearby_events.length > 0) {
      const event = selectCompatibleEvent(nearby_events);
      triggerExistingEvent(player, event);
    } else {
      // 3. Sinon, crÃÂ©er nouvel ÃÂ©vÃÂ©nement
      const event = generateNewEvent(player, fear_capital);
      
      // 4. Si ÃÂ©vÃÂ©nement persistant, stocker en BDD
      if (event.persistent) {
        db.insert('fear_events', {
          type: event.type,
          position_system_id: player.system_id,
          position_x: player.x,
          created_turn: current_turn,
          expires_turn: current_turn + event.duration,
          status: 'active',
          data_json: JSON.stringify(event.data)
        });
      }
      
      triggerEvent(player, event);
    }
    
    // 5. Consommer jetons Peur
    system.fear_tokens -= event.fear_cost;
  }
}

// Nettoyage pÃÂ©riodique
function cleanupExpiredEvents() {
  db.query(`
    UPDATE fear_events 
    SET status = 'expired' 
    WHERE expires_turn < ?
  `, [current_turn]);
}
```

---

### Jetons d'Espoir et de Peur

**Jetons d'Espoir (ressource joueurs - VISIBLE) :**
- DÃÂ©pensÃÂ©s volontairement par le joueur pour :
  - **Relancer les dÃÂ©s** (2d12)
  - **Activer un talent** (ÃÂ  dÃÂ©finir ultÃÂ©rieurement)
  - **Obtenir certains effets environnementaux** (ÃÂ  dÃÂ©finir)
  - Survivre ÃÂ  la mort (mÃÂ©canique ÃÂ  dÃÂ©finir)

**Commandes :**
```
> check_hope
Jetons d'Espoir disponibles : 3

> use_hope reroll
Jeton d'Espoir dÃÂ©pensÃÂ© (Reste : 2)
Relance des dÃÂ©s autorisÃÂ©e
```

**Jetons de Peur (ressource systÃÂ¨me - CACHÃâ°) :**
- AccumulÃÂ©s automatiquement quand Fear > Hope
- DÃÂ©pensÃÂ©s automatiquement par le systÃÂ¨me pour :
  - DÃÂ©clencher embuscades
  - Activer capacitÃÂ©s ennemies
  - Introduire complications (pannes, anomalies)
  - Faire intervenir renforts ennemis
  - CrÃÂ©er ÃÂ©vÃÂ©nements narratifs
- Le joueur ne voit que les **effets** (pas le compteur)

---

### Exemples d'application

#### DÃÂ©tection d'embuscade
```
Action : DÃÂ©tecter une embuscade pirate ÃÂ  l'approche d'un systÃÂ¨me
CompÃÂ©tence : Perception (Instinct)
DifficultÃÂ© : 14 (furtivitÃÂ© ennemie)

Jet : 2d12 + Perception + Bonus dÃÂ©tecteurs
- DÃÂ© Hope : 7
- DÃÂ© Fear : 9
- Perception : +2
- Instinct (trait minimum) : +3
- Bonus dÃÂ©tecteurs : +2
Ã¢â â RÃÂ©sultat : (7 + 9) + 2 + 2 = 20 Ã¢â°Â¥ 14 = SUCCÃËS
(Note : On utilise Perception +2, pas Instinct +3, car Perception > trait minimum)

Mais Fear > Hope (9 > 7) Ã¢â â +1 jeton de Peur (CACHÃâ°)
Ã¢â â Embuscade dÃÂ©tectÃÂ©e ÃÂ  temps !
Ã¢â â Mais le systÃÂ¨me accumule de la Peur...

Affichage console :
> approach asteroid_belt

ÄÅ¸ï¿½Â² Hope: 7  |  Fear: 9
RÃÂ©sultat: (7 + 9) + 2 + 2 = 20
Ã¢Åâ DÃÂ©tection rÃÂ©ussie !

Ã¢Å¡Â Ã¯Â¸ï¿½ ALERTE : 3 vaisseaux pirates dÃÂ©tectÃÂ©s en embuscade !
Position : 2.1 UA, secteur Gamma
Option : Ãâ°viter [2 PA] | Engager combat | Fuir

[SystÃÂ¨me : +1 Peur stockÃÂ©]
```

#### Combat spatial
```
Action : Attaquer un vaisseau ennemi
CompÃÂ©tence : Artillerie (Finesse)
DifficultÃÂ© : Seuil d'Ãâ°vasion ennemi (10 + AgilitÃÂ© + Armure)

Exemple contre Corvette pirate :
- Seuil d'Ãâ°vasion : 10 + 2 (AgilitÃÂ©) + 2 (Armure) = 14

Jet : 2d12 + Artillerie + Bonus arme
- DÃÂ© Hope : 11
- DÃÂ© Fear : 5
- Artillerie : +3
- Finesse (trait minimum) : +4
- Bonus canons : +1
Ã¢â â RÃÂ©sultat : (11 + 5) + 4 + 1 = 21 Ã¢â°Â¥ 14 = SUCCÃËS
(Note : On utilise Finesse +4, pas Artillerie +3, car trait > compÃÂ©tence)

Hope > Fear (11 > 5) Ã¢â â +1 jeton d'Espoir pour le joueur
Ã¢â â TouchÃÂ© ! Et le joueur gagne une ressource narrative

Affichage console :
> attack pirate_corvette laser_cannons

ÄÅ¸ï¿½Â² Hope: 11  |  Fear: 5
RÃÂ©sultat: (11 + 5) + 4 + 1 = 21
DÃÂ©fense cible: 14
Ã¢Åâ TOUCHÃâ° - 32 dÃÂ©gÃÂ¢ts infligÃÂ©s

Ã¢Åâ Jeton d'Espoir gagnÃÂ© ! (Total : 3)
Corvette pirate : 68/100 HP
```

#### Saut hyper-espace
```
Action : Saut FTL vers systÃÂ¨me inconnu
CompÃÂ©tence : Astrogation (Connaissance)
DifficultÃÂ© : 13 (selon distance/conditions)

Jet : 2d12 + Astrogation + QualitÃÂ© Drive
- DÃÂ© Hope : 6
- DÃÂ© Fear : 9
- Astrogation : +4
- Connaissance (trait minimum) : +2
- Drive : +3
Ã¢â â RÃÂ©sultat : (6 + 9) + 4 + 3 = 22 Ã¢â°Â¥ 13 = SUCCÃËS
(Note : On utilise Astrogation +4, pas Connaissance +2)

Mais Fear > Hope (9 > 6) Ã¢â â +1 jeton de Peur
Ã¢â â ArrivÃÂ©e rÃÂ©ussie, mais complication possible :
  - DÃÂ©viation mineure de trajectoire
  - Rencontre imprÃÂ©vue
  - SystÃÂ¨me endommagÃÂ© par le saut
```

#### NÃÂ©gociation avec guilde
```
Action : Obtenir meilleur prix pour donnÃÂ©es
CompÃÂ©tence : NÃÂ©gociation (PrÃÂ©sence)
DifficultÃÂ© : 15

Jet : 2d12 + NÃÂ©gociation
- DÃÂ© Hope : 10
- DÃÂ© Fear : 10
- NÃÂ©gociation : +3
- PrÃÂ©sence (trait minimum) : +3
Ã¢â â RÃÂ©sultat : (10 + 10) + 3 = 23 Ã¢â°Â¥ 15 = SUCCÃËS
(Note : On utilise NÃÂ©gociation +3, ÃÂ©gal au trait)

Hope = Fear (10-10) ET Ã¢â°Â  1-1 Ã¢â â **CRITIQUE !**
Ã¢â â +1 jeton d'Espoir
Ã¢â â NÃÂ©gociation exceptionnelle ! Bonus supplÃÂ©mentaire :
  - Prix +25% au lieu de +10%
  - AccÃÂ¨s donnÃÂ©es premium offert
  - RÃÂ©putation guilde +20 (au lieu de +10)

Affichage console :
> negotiate data_sale cartographers_guild

ÄÅ¸ï¿½Â² Hope: 10  |  Fear: 10
RÃÂ©sultat: (10 + 10) + 3 = 23
Ã¢ÅâÃ¢Åâ CRITIQUE ! RÃÂ©ussite exceptionnelle !

Ã¢Åâ Jeton d'Espoir gagnÃÂ© ! (Total : 3)

Prix obtenu : 15 000 cr (+25%)
Bonus : AccÃÂ¨s cartes premium dÃÂ©bloquÃÂ©
```

#### Catastrophe (1-1)
```
Action : RÃÂ©parer systÃÂ¨me endommagÃÂ© sous le feu
CompÃÂ©tence : MÃÂ©canique (Finesse)
DifficultÃÂ© : 14

Jet : 2d12 + MÃÂ©canique
- DÃÂ© Hope : 1
- DÃÂ© Fear : 1
- MÃÂ©canique : +2
- Finesse (trait minimum) : +4
Ã¢â â RÃÂ©sultat : (1 + 1) + 4 = 6 < 14 = Ãâ°CHEC
(Note : On utilise Finesse +4, pas MÃÂ©canique +2)

Hope = Fear = 1-1 Ã¢â â CATASTROPHE !
Ã¢â â +1 jeton de Peur (systÃÂ¨me, cachÃÂ©)
Ã¢â â RÃÂ©paration ÃÂ©choue catastrophiquement :
  - SystÃÂ¨me totalement HS (au lieu de juste endommagÃÂ©)
  - Surcharge Ã¢â â DÃÂ©gÃÂ¢ts supplÃÂ©mentaires (-15 HP)
  - Temps perdu (2 PA perdus)

Affichage console :
> repair shields

ÄÅ¸ï¿½Â² Hope: 1  |  Fear: 1
RÃÂ©sultat: (1 + 1) + 4 = 6
Ã¢ÅâÃ¢Åâ CATASTROPHE !

[SystÃÂ¨me : +1 Peur stockÃÂ©]

Ã¢Å¡Â Ã¯Â¸ï¿½ Surcharge critique !
Boucliers : Hors service total
DÃÂ©gÃÂ¢ts : -15 HP coque
PA perdus : 2
```

---

### Adaptation pour le jeu vidÃÂ©o

**Calculs automatiques :**
- Le serveur lance les 2d12
- Affiche les rÃÂ©sultats (Hope: X, Fear: Y)
- Indique succÃÂ¨s/ÃÂ©chec
- GÃÂ©nÃÂ¨re jetons automatiquement

**Affichage console :**
```
> scan_for_threats

Scan de menaces (Instinct +3, DÃÂ©tecteurs +2)...
ÄÅ¸ï¿½Â² Hope: 8  |  Fear: 10
RÃÂ©sultat: (8 + 10) + 3 + 2 = 23
Ã¢Åâ SUCCÃËS - Aucune menace immÃÂ©diate dÃÂ©tectÃÂ©e

[SystÃÂ¨me : +1 Peur accumulÃÂ©]
(Le joueur ne voit pas cette ligne - Peur cachÃÂ©)

> check_hope
Jetons d'Espoir disponibles : 2

> use_hope navigation_bonus
Jeton d'Espoir dÃÂ©pensÃÂ© (+2 au prochain jet de navigation)
Jetons restants : 1
```

**Utilisation automatique Peur par le systÃÂ¨me :**
```
[Joueur fait plusieurs actions avec Fear > Hope]
[SystÃÂ¨me accumule 5 jetons de Peur]

> jump_hyperspace target_system_gamma

Calcul de saut...
ÄÅ¸ï¿½Â² Hope: 10  |  Fear: 6
Ã¢Åâ Saut rÃÂ©ussi

[SystÃÂ¨me dÃÂ©pense 3 jetons Peur]

Ã¢Å¡Â Ã¯Â¸ï¿½ Ãâ°VÃâ°NEMENT : Sortie d'hyperespace perturbÃÂ©e !
Champ d'astÃÂ©roÃÂ¯des non rÃÂ©pertoriÃÂ© dÃÂ©tectÃÂ©
Micro-dÃÂ©gÃÂ¢ts : -5 HP coque
Position : +0.3 AL de dÃÂ©viation

(Le joueur ne sait pas que c'ÃÂ©tait causÃÂ© par les jetons Peur)
```

---

## ÄÅ¸âÂ¥Ã¯Â¸ï¿½ INTERFACE UTILISATEUR

### Vue d'ensemble

L'interface est composÃÂ©e de **trois zones principales** :
- **Panneau de navigation ÃÂ  gauche** : SystÃÂ¨me d'onglets thÃÂ©matiques
- **Zone d'affichage centrale** : Informations contextuelles, visualisations
- **Console ÃÂ  droite** : Messages + saisie commandes + boutons raccourcis

**SchÃÂ©ma de layout :**
```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ  [LOGO/TITRE DU JEU]                          [USER INFO] [PA:10]Ã¢ââ
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âÂ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âÂ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âÂ¤
Ã¢ââ            Ã¢ââ                             Ã¢ââ                       Ã¢ââ
Ã¢ââ  ONGLETS   Ã¢ââ    ZONE D'AFFICHAGE        Ã¢ââ  ZONE MESSAGES        Ã¢ââ
Ã¢ââ  (MENUS)   Ã¢ââ    CENTRALE                 Ã¢ââ  (Dialogue IA)        Ã¢ââ
Ã¢ââ            Ã¢ââ                             Ã¢ââ  Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½  Ã¢ââ
Ã¢ââ Ã¢âÅÃ¢ââ¬ Lieu    Ã¢ââ  Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½ Ã¢ââ  Ã¢ââ > Bienvenue     Ã¢ââ  Ã¢ââ
Ã¢ââ Ã¢âÅÃ¢ââ¬ Service Ã¢ââ  Ã¢ââ                       Ã¢ââ Ã¢ââ  Ã¢ââ > Scout LI-200  Ã¢ââ  Ã¢ââ
Ã¢ââ Ã¢âÅÃ¢ââ¬PersonnelÃ¢ââ  Ã¢ââ   COCKPIT / CARTE     Ã¢ââ Ã¢ââ  Ã¢ââ > Sol, Terre    Ã¢ââ  Ã¢ââ
Ã¢ââ Ã¢ââÃ¢ââ¬ Jeu     Ã¢ââ  Ã¢ââ   RADAR / DONNÃâ°ES     Ã¢ââ Ã¢ââ  Ã¢ââ > 10 PA dispos  Ã¢ââ  Ã¢ââ
Ã¢ââ            Ã¢ââ  Ã¢ââ                       Ã¢ââ Ã¢ââ  Ã¢ââ ...             Ã¢ââ  Ã¢ââ
Ã¢ââ Sous-menus:Ã¢ââ  Ã¢ââ   (Contextuel selon   Ã¢ââ Ã¢ââ  Ã¢ââ [Historique]    Ã¢ââ  Ã¢ââ
Ã¢ââ Ã¢â¬Â¢ Pont     Ã¢ââ  Ã¢ââ    onglet sÃÂ©lectionnÃÂ©)Ã¢ââ Ã¢ââ  Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË  Ã¢ââ
Ã¢ââ Ã¢â¬Â¢ Soute    Ã¢ââ  Ã¢ââ                       Ã¢ââ Ã¢ââ                       Ã¢ââ
Ã¢ââ Ã¢â¬Â¢ Machines Ã¢ââ  Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË Ã¢ââ  ZONE SAISIE          Ã¢ââ
Ã¢ââ Ã¢â¬Â¢ ...      Ã¢ââ                             Ã¢ââ  Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½  Ã¢ââ
Ã¢ââ            Ã¢ââ                             Ã¢ââ  Ã¢ââ > _             Ã¢ââ  Ã¢ââ
Ã¢ââ            Ã¢ââ                             Ã¢ââ  Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË  Ã¢ââ
Ã¢ââ            Ã¢ââ                             Ã¢ââ  [BOUTONS RACCOURCIS] Ã¢ââ
Ã¢ââ            Ã¢ââ                             Ã¢ââ  [Scan][Jump][Attack] Ã¢ââ
Ã¢ââ            Ã¢ââ                             Ã¢ââ  [Dock][Trade][Help]  Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âÂ´Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âÂ´Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

---

### 1. Panneau de navigation (Gauche)

**4 Grands thÃÂ¨mes avec sous-menus :**

#### ÄÅ¸âï¿½ LIEU
Interaction avec le lieu actuel (Station ou Vaisseau)

```
Lieu
Ã¢âÅÃ¢ââ¬ Vaisseau (si dans vaisseau)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Pont
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Soute
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Quartiers ÃÂ©quipage
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Salle des machines
Ã¢ââ   Ã¢ââÃ¢ââ¬ SystÃÂ¨mes (armement, boucliers, etc.)
Ã¢ââ
Ã¢ââÃ¢ââ¬ Station (si amarrÃÂ©/ÃÂ  quai)
    Ã¢âÅÃ¢ââ¬ Hangar / Docks
    Ã¢âÅÃ¢ââ¬ MarchÃÂ© / Commerce
    Ã¢âÅÃ¢ââ¬ Chantier naval (rÃÂ©parations, upgrades)
    Ã¢âÅÃ¢ââ¬ Quartier administratif (missions, guildes)
    Ã¢âÅÃ¢ââ¬ Cantina / Espaces sociaux
    Ã¢ââÃ¢ââ¬ Zones spÃÂ©ciales (selon station)
```

#### ÄÅ¸âºÂ Ã¯Â¸ï¿½ SERVICE
CompÃÂ©tences communes et gestion

```
Service
Ã¢âÅÃ¢ââ¬ Spatio-carte
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Carte galactique
Ã¢ââ   Ã¢âÅÃ¢ââ¬ SystÃÂ¨me actuel (vue dÃÂ©taillÃÂ©e)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Routes connues
Ã¢ââ   Ã¢ââÃ¢ââ¬ Points d'intÃÂ©rÃÂªt dÃÂ©couverts
Ã¢ââ
Ã¢âÅÃ¢ââ¬ Gestion vaisseaux
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Flotte personnelle
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Statut / RÃÂ©parations
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Ãâ°quipements / Upgrades
Ã¢ââ   Ã¢ââÃ¢ââ¬ Assignations ÃÂ©quipage
Ã¢ââ
Ã¢âÅÃ¢ââ¬ Gestion bases/mines
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Liste installations
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Production / Ressources
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Personnel assignÃÂ©
Ã¢ââ   Ã¢ââÃ¢ââ¬ DÃÂ©fenses
Ã¢ââ
Ã¢âÅÃ¢ââ¬ Communication
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Messages / Guildes
Ã¢ââ   Ã¢âÅÃ¢ââ¬ MarchÃÂ© (offres/demandes)
Ã¢ââ   Ã¢ââÃ¢ââ¬ Intel / Rapports
Ã¢ââ
Ã¢ââÃ¢ââ¬ Ãâ°conomie
    Ã¢âÅÃ¢ââ¬ Inventaire global
    Ã¢âÅÃ¢ââ¬ Finances
    Ã¢âÅÃ¢ââ¬ Routes commerciales
    Ã¢ââÃ¢ââ¬ Contrats actifs
```

#### ÄÅ¸âÂ¥ PERSONNEL
Gestion du personnel du vaisseau

```
Personnel
Ã¢âÅÃ¢ââ¬ PJ Principal
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Fiche personnage
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Traits / CompÃÂ©tences
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Ãâ°quipement personnel
Ã¢ââ   Ã¢ââÃ¢ââ¬ Historique / RÃÂ©putation
Ã¢ââ
Ã¢âÅÃ¢ââ¬ PJ Secondaires (ÃÂ©quipage nommÃÂ©)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Liste ÃÂ©quipage
Ã¢ââ   Ã¢âÅÃ¢ââ¬ SpÃÂ©cialisations
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Affectations postes
Ã¢ââ   Ã¢ââÃ¢ââ¬ Moral / Ãâ°tat
Ã¢ââ
Ã¢ââÃ¢ââ¬ Personnel non-joueur
    Ã¢âÅÃ¢ââ¬ Effectifs (nombre par rÃÂ´le)
    Ã¢âÅÃ¢ââ¬ Recrutement
    Ã¢âÅÃ¢ââ¬ Formation
    Ã¢ââÃ¢ââ¬ Besoins (alimentation, confort, etc.)
```

#### ÄÅ¸ï¿½Â® JEU
MÃÂ©ta-jeu et paramÃÂ¨tres

```
Jeu
Ã¢âÅÃ¢ââ¬ Options
Ã¢âÅÃ¢ââ¬ ParamÃÂ¨tres
Ã¢âÅÃ¢ââ¬ Tutoriel / Aide
Ã¢âÅÃ¢ââ¬ Statistiques
Ã¢âÅÃ¢ââ¬ SuccÃÂ¨s / Objectifs
Ã¢ââÃ¢ââ¬ Sauvegarde / Quitter
```

---

### 2. Zone d'affichage centrale

**Affichage contextuel selon l'onglet/sous-menu sÃÂ©lectionnÃÂ© ÃÂ  gauche.**

#### Exemples d'affichages :

**LIEU > Vaisseau > Pont :**
```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   VUE COCKPIT DU VAISSEAU           Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   [SchÃÂ©ma 3D ou vue cockpit]        Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Indicateurs HP coque : 85/100   Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Boucliers : 100%                Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Ãâ°nergie rÃÂ©acteur : 75%          Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Carburant : 450/500             Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   RADAR LOCAL                       Ã¢ââ
Ã¢ââ   [Carte 2D locale 360ÃÂ°]            Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Objets dÃÂ©tectÃÂ©s dans 5 UA       Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

**SERVICE > Spatio-carte > SystÃÂ¨me actuel :**
```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   SYSTÃËME SOL                       Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   [Carte 2D/3D du systÃÂ¨me]          Ã¢ââ
Ã¢ââ   Ã¢Ëâ° Soleil (centre)                 Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Mercure (0.39 UA)               Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ VÃÂ©nus (0.72 UA)                 Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Terre (1.0 UA) Ã¢â ï¿½ VOUS ÃÅ TES ICI  Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Mars (1.52 UA)                  Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Ceinture astÃÂ©roÃÂ¯des (2.7 UA)    Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Jupiter (5.2 UA)                Ã¢ââ
Ã¢ââ   ...                               Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   Boutons :                         Ã¢ââ
Ã¢ââ   [Zoom +/-] [Vue 3D] [Routes]      Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

**SERVICE > Gestion vaisseaux :**
```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   SCOUT LI-200 "Explorateur"        Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   [SchÃÂ©ma vaisseau avec modules]    Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   MODULES INSTALLÃâ°S :               Ã¢ââ
Ã¢ââ   Ã¢âÅÃ¢ââ¬ DÃÂ©tecteurs Mk II (Slot 1)      Ã¢ââ
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Canons Laser (Slot 2)          Ã¢ââ
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Boucliers Standard (Slot 3)    Ã¢ââ
Ã¢ââ   Ã¢ââÃ¢ââ¬ Drive Hyper-espace Mk I        Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   STATISTIQUES :                    Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ HP : 85/100                     Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Cargo : 15/50 unitÃÂ©s            Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Ãâ°quipage : 3/4                  Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Entretien : Bon ÃÂ©tat            Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   [RÃÂ©parer] [Upgrade] [Vendre]      Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

**PERSONNEL > PJ Principal :**
```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   CAPITAINE JEAN MERCIER            Ã¢ââ
Ã¢ââ   [Portrait/Avatar]                 Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   TRAITS :                          Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ AgilitÃÂ© : 3                     Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Force : 2                       Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Finesse : 4                     Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Instinct : 5                    Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ PrÃÂ©sence : 3                    Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Connaissance : 4                Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   COMPÃâ°TENCES : (ÃÂ  dÃÂ©finir)         Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Navigation : 6                  Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Combat : 4                      Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ NÃÂ©gociation : 5                 Ã¢ââ
Ã¢ââ   ...                               Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   RÃâ°PUTATION :                      Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Empire Terrien : +250 (Ami)     Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Cartographes : +180 (Connu)     Ã¢ââ
Ã¢ââ   Ã¢â¬Â¢ Pirates : -50 (MÃÂ©fiant)         Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

**LIEU > Station > MarchÃÂ© :**
```
Ã¢âÅÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âï¿½
Ã¢ââ   MARCHÃâ° - STATION ALPHA CENTAURI   Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   ACHETER :                         Ã¢ââ
Ã¢ââ   Item              Prix    Stock   Ã¢ââ
Ã¢ââ   Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ
Ã¢ââ   Carburant         50cr    1000u   Ã¢ââ
Ã¢ââ   PiÃÂ¨ces dÃÂ©tachÃÂ©es  200cr   50u     Ã¢ââ
Ã¢ââ   Nourriture (std)  10cr    500u    Ã¢ââ
Ã¢ââ   Munitions laser   150cr   100u    Ã¢ââ
Ã¢ââ   ...                               Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   VENDRE :                          Ã¢ââ
Ã¢ââ   Votre Item        Prix    QtÃÂ©     Ã¢ââ
Ã¢ââ   Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ
Ã¢ââ   Minerai fer       80cr    25u     Ã¢ââ
Ã¢ââ   DonnÃÂ©es cartes    500cr   3u      Ã¢ââ
Ã¢ââ   ...                               Ã¢ââ
Ã¢ââ                                     Ã¢ââ
Ã¢ââ   CrÃÂ©dits : 12 450 cr               Ã¢ââ
Ã¢ââÃ¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢âË
```

---

### 3. Console (Droite)

#### Zone Messages (Haut)
- **Dialogue avec l'IA du systÃÂ¨me**
- Affichage des actions, rÃÂ©sultats, ÃÂ©vÃÂ©nements
- Historique scrollable
- Codes couleur :
  - Blanc : Informations neutres
  - Vert : SuccÃÂ¨s, gains
  - Jaune : Avertissements
  - Rouge : Dangers, ÃÂ©checs, dÃÂ©gÃÂ¢ts
  - Bleu : Communications, messages guildes
  - Violet : Ãâ°vÃÂ©nements spÃÂ©ciaux, critiques

#### Zone Saisie (Milieu)
- **Champ texte pour commandes**
- Commandes introduites par **mot-clÃÂ© au dÃÂ©but**
- Auto-complÃÂ©tion suggÃÂ©rÃÂ©e
- Historique commandes (flÃÂ¨ches haut/bas)

#### Boutons Raccourcis (Bas)
**Actions ÃÂ©videntes et standards (ÃÂ  dÃÂ©finir prÃÂ©cisÃÂ©ment ultÃÂ©rieurement)**

Exemples de boutons contextuels :

**En navigation :**
```
[Scan] [Jump] [Dock] [Auto-pilot]
```

**En combat :**
```
[Attack] [Evade] [Target] [Flee]
```

**Ãâ¬ la station :**
```
[Trade] [Repair] [Upgrade] [Missions]
```

**GÃÂ©nÃÂ©raux (toujours visibles) :**
```
[Help] [Status] [End Turn]
```

**Exemples de commandes textuelles :**

```
NAVIGATION
> jump [systÃÂ¨me]          : Saut hyper-espace
> travel [planÃÂ¨te]        : DÃÂ©placement conventionnel
> scan                    : Scanner zone actuelle
> dock [station]          : Amarrage

COMBAT
> attack [cible] [arme]   : Attaquer
> evade                   : ManÃâuvre ÃÂ©vasive
> target [systÃÂ¨me]        : Cibler systÃÂ¨me spÃÂ©cifique
> flee                    : Fuir le combat

COMMERCE
> buy [item] [quantitÃÂ©]   : Acheter
> sell [item] [quantitÃÂ©]  : Vendre
> trade [destination]     : Ãâ°tablir route commerciale
> market                  : Afficher marchÃÂ© local

GESTION
> repair [systÃÂ¨me]        : RÃÂ©parer
> upgrade [systÃÂ¨me]       : AmÃÂ©liorer
> assign [personnel] [poste] : Assigner ÃÂ©quipage
> status                  : Ãâ°tat vaisseau/ÃÂ©quipage

SOCIAL
> contact [faction]       : Contacter
> negotiate               : NÃÂ©gocier
> accept_mission [id]     : Accepter mission
> reputation              : Voir rÃÂ©putations

SYSTÃËME
> help [commande]         : Aide
> history                 : Historique actions
> check_hope              : Voir jetons Espoir
> end_turn                : Finir le tour
```

---

### IntÃÂ©gration des 3 zones

**Flux d'interaction :**

1. **SÃÂ©lection onglet/menu (Gauche)** Ã¢â â Change affichage central
2. **Visualisation/Clic ÃÂ©lÃÂ©ment (Centre)** Ã¢â â PrÃÂ©-remplit commande (Droite)
3. **Validation commande (Droite)** Ã¢â â RÃÂ©sultat affichÃÂ© dans messages (Droite) + mise ÃÂ  jour affichage (Centre)

**Exemple de flux complet :**
```
1. Joueur clique "Service > Spatio-carte > SystÃÂ¨me actuel" (Gauche)
   Ã¢â â Centre affiche carte du systÃÂ¨me Sol

2. Joueur clique sur "Mars" dans la carte (Centre)
   Ã¢â â Console prÃÂ©-remplit : "> travel Mars_"

3. Joueur valide ou modifie et appuie EntrÃÂ©e
   Ã¢â â Message console : "ÄÅ¸ï¿½Â² Navigation rÃÂ©ussie, arrivÃÂ©e Mars dans 3 tours"
   Ã¢â â Carte centrale met ÃÂ  jour position
```

**Raccourcis clavier :**
- `Tab` : Focus sur zone saisie
- `Ctrl+H` : Afficher/masquer panneau gauche
- `Ctrl+M` : Afficher/masquer console droite
- `F1` : Aide contextuelle
- `Espace` : End turn

**Responsive :**
- Petits ÃÂ©crans : Panneau gauche se rÃÂ©tracte (icÃÂ´nes)
- Console droite peut se minimiser
- Zone centrale reste prioritaire

---

## ÄÅ¸Å¡â¬ NAVIGATION ET DÃâ°PLACEMENTS

### Tour par tour
- **1 tour = 1 jour in-game**
- **Points d'Action (PA)** par tour : 10-15 (selon vaisseau/ÃÂ©quipage)

### Types de dÃÂ©placement

#### 1. Hyper-espace (inter-stellaire)
**CaractÃÂ©ristiques :**
- PortÃÂ©e : 3-6 annÃÂ©es-lumiÃÂ¨re par saut
- Direction : Choisie par le joueur (vecteur 3D)
- Puissance : ParamÃÂ©trable (distance = f(puissance))
- **ImprÃÂ©cision** : Scatter alÃÂ©atoire autour de la cible
  - PrÃÂ©cision = f(qualitÃÂ© drive, calculs navigation)
  
**ArrÃÂªts forcÃÂ©s :**
Le saut s'interrompt si obstacle non dÃÂ©tectÃÂ© :
- Champ d'astÃÂ©roÃÂ¯des dense
- GravitÃÂ© planÃÂ©taire/stellaire
- Anomalie spatiale
- **Pas ÃÂ  la demande du joueur** (sauf abandon saut)

**CoÃÂ»t :**
- Minimum : 3-5 PA pour calculs + saut
- Carburant hyperespace consommÃÂ©
- Temps de recharge entre sauts

#### 2. Conventionnel (intra-systÃÂ¨me)
**CaractÃÂ©ristiques :**
- Entre objets d'un mÃÂªme systÃÂ¨me
- Plus lent mais prÃÂ©cis
- CoÃÂ»t : 1-5 PA selon distance

**Vitesses :**
- Subluminique : 0.01-0.3c (fraction vitesse lumiÃÂ¨re)
- Transit planÃÂ¨te Ã¢â â planÃÂ¨te : quelques heures ÃÂ  jours

### Phase d'orientation (post-saut)

**AprÃÂ¨s un saut hyper-espace :**
1. **Scan obligatoire (Ã¢â°Â¥1 PA)** pour dÃÂ©terminer position
2. Calcul fond d'ÃÂ©toiles visible
3. Reconnaissance patterns connus (ÃÂ©toiles brillantes)
4. Triangulation Ã¢â â Position estimÃÂ©e

**CoÃÂ»t variable :**
- 1 PA : Orientation basique (ÃÂ±2 AL d'erreur)
- 2-3 PA : Scan approfondi (ÃÂ±0.5 AL)
- +X PA si zone complexe (nÃÂ©buleuse, champ dense)

---

## ÄÅ¸âÂ­ DÃâ°TECTION ET EXPLORATION

### Fond d'ÃÂ©toiles dynamique

**Concept :**
Le vaisseau "voit" ce que ses capteurs dÃÂ©tectent depuis sa position actuelle.

**Calcul :**
Pour chaque ÃÂ©toile GAIA dans un rayon de X AL :
1. Distance au vaisseau
2. Magnitude apparente depuis cette position
3. Position angulaire relative (RA/Dec)
4. Identifiable si assez brillante

**Rendu visuel :**
- Canvas/WebGL avec points d'ÃÂ©toiles
- DensitÃÂ© rÃÂ©aliste (milliers de points)
- Objets dÃÂ©tectÃÂ©s en surbrillance colorÃÂ©e

### Objets dÃÂ©tectables

| Type | Couleur UI | DifficultÃÂ© dÃÂ©tection |
|------|------------|---------------------|
| Ãâ°toiles identifiÃÂ©es | ÄÅ¸Å¸Â¡ Jaune/Orange | Facile |
| PlanÃÂ¨tes connues | ÄÅ¸âÂµ Bleu | Moyen |
| Bases/Stations | ÄÅ¸Å¸Â¢ Vert | Moyen |
| Zones extraction | ÄÅ¸Å¸Â  Orange pulsant | Difficile |
| Champs astÃÂ©roÃÂ¯des | ÄÅ¸âÂ´ Rouge diffus | Variable |
| **Galaxies lointaines** | ÄÅ¸Å¸Â£ Violet | **PIÃËGE** |

### PiÃÂ¨ge des galaxies
- Visuellement similaires ÃÂ  ÃÂ©toiles brillantes
- Saut vers galaxie Ã¢â â Ãâ°chec catastrophique
- Position rÃÂ©sultante alÃÂ©atoire (perdu)
- AmÃÂ©lioration capteurs = distinguer ÃÂ©toiles/galaxies

### CapacitÃÂ©s ÃÂ©volutives vaisseau

| Niveau | DÃÂ©tection | PrÃÂ©cision navigation |
|--------|-----------|---------------------|
| Base | Ãâ°toiles >mag 3 | ÃÂ±2 AL |
| AmÃÂ©liorÃÂ© | +PlanÃÂ¨tes gÃÂ©antes | ÃÂ±1 AL |
| AvancÃÂ© | +AstÃÂ©roÃÂ¯des, bases | ÃÂ±0.5 AL |
| Expert | Distingue galaxies | ÃÂ±0.1 AL |

### Satellites de communication

**Niveaux technologiques :**

```
Niveau 1 : PortÃÂ©e systÃÂ¨me (0.5 AL)
Ã¢âÅÃ¢ââ¬ Couvre 1 systÃÂ¨me solaire
Ã¢âÅÃ¢ââ¬ Cartographie locale
Ã¢ââÃ¢ââ¬ CoÃÂ»t faible, dÃÂ©ploiement rapide

Niveau 2 : PortÃÂ©e ÃÂ©tendue (1.5 AL)  
Ã¢âÅÃ¢ââ¬ Couvre systÃÂ¨mes voisins proches
Ã¢âÅÃ¢ââ¬ CrÃÂ©e "corridors sÃÂ»rs"
Ã¢ââÃ¢ââ¬ CoÃÂ»t moyen

Niveau 3 : PortÃÂ©e longue (3 AL max)
Ã¢âÅÃ¢ââ¬ RÃÂ©seau inter-stellaire
Ã¢âÅÃ¢ââ¬ Relais communications
Ã¢ââÃ¢ââ¬ CoÃÂ»t ÃÂ©levÃÂ©, tech avancÃÂ©e
```

**BÃÂ©nÃÂ©fices zone couverte :**
- Pas de scan requis (0 PA ÃÂ©conomisÃÂ©s)
- Navigation prÃÂ©cise (pas d'erreur)
- Alertes temps rÃÂ©el (flottes, ÃÂ©vÃÂ©nements)
- Calculs hyper-espace optimisÃÂ©s

**ModÃÂ¨le ÃÂ©conomique satellites :**
```
PropriÃÂ©taire : Joueur/Guilde
Ã¢âÅÃ¢ââ¬ Politique d'accÃÂ¨s
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Gratuit pour membres guilde
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Abonnement pour alliÃÂ©s (X cr/tour)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Paiement ÃÂ  l'usage pour neutres
Ã¢ââ   Ã¢ââÃ¢ââ¬ BloquÃÂ© pour ennemis
Ã¢âÅÃ¢ââ¬ Revenus passifs
Ã¢ââÃ¢ââ¬ ContrÃÂ´le stratÃÂ©gique (cible militaire)
```

---

## ÄÅ¸âºÂ¸ VAISSEAUX ET Ãâ°QUIPEMENTS

### Fabricant starter : **Luna Industries**
*Hommage ÃÂ  Lunastars (https://v2.lunastars.net)*

**Slogan :** *"L'espace pour tous"*

**Gamme produits :**
```
Luna Industries
Ã¢âÅÃ¢ââ¬ LI-100 "Sparrow" : Shuttle 800 cr
Ã¢âÅÃ¢ââ¬ LI-200 "Scout" : Explorateur 2000 cr
Ã¢âÅÃ¢ââ¬ LI-250 "Hauler" : Cargo 2500 cr
Ã¢ââÃ¢ââ¬ LI-300 "Interceptor" : Combat lÃÂ©ger 3500 cr
```

### Classes de vaisseaux
*InspirÃÂ© Star Citizen (robertspaceindustries.com)*

#### 1. EXPLORATION ÄÅ¸âÂ­
```
Taille S (solo) : 1-2 siÃÂ¨ges
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 2D8/PA
Ã¢âÅÃ¢ââ¬ Vitesse hyper-espace : Standard
Ã¢âÅÃ¢ââ¬ Cargo : Minimal
Ã¢ââÃ¢ââ¬ Ex : Scout lÃÂ©ger

Taille M (ÃÂ©quipage) : 2-6 siÃÂ¨ges
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 3D10/PA
Ã¢âÅÃ¢ââ¬ PortÃÂ©e : Excellente
Ã¢âÅÃ¢ââ¬ Labo analyse : Oui
Ã¢ââÃ¢ââ¬ Ex : Explorateur longue-portÃÂ©e

Taille L (grand ÃÂ©quipage) : 6-20 siÃÂ¨ges
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 4D12+5/PA
Ã¢âÅÃ¢ââ¬ Cartographie avancÃÂ©e
Ã¢âÅÃ¢ââ¬ Drones dÃÂ©ployables
Ã¢ââÃ¢ââ¬ Ex : Vaisseau reconnaissance
```

#### 2. COMBAT Ã¢Å¡âÃ¯Â¸ï¿½
```
Taille S : Chasseur
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 1D6/PA (faible)
Ã¢âÅÃ¢ââ¬ Armement : Fort
Ã¢âÅÃ¢ââ¬ ManÃâuvrabilitÃÂ© : Excellente
Ã¢ââÃ¢ââ¬ RÃÂ´le : Interception

Taille M : Corvette
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 2D6/PA
Ã¢âÅÃ¢ââ¬ Blindage : Bon
Ã¢âÅÃ¢ââ¬ Ãâ°quipage : 4-8
Ã¢ââÃ¢ââ¬ RÃÂ´le : Patrouille

Taille L : FrÃÂ©gate/Destroyer
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 2D8/PA
Ã¢âÅÃ¢ââ¬ Armement lourd
Ã¢âÅÃ¢ââ¬ Hangar : petits vaisseaux
Ã¢ââÃ¢ââ¬ RÃÂ´le : ContrÃÂ´le spatial
```

#### 3. COMMERCE & TRANSPORT ÄÅ¸âÂ¦
```
Taille M : Cargo lÃÂ©ger
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 1D4/PA (minimal)
Ã¢âÅÃ¢ââ¬ Soutes : 100-500 unitÃÂ©s
Ã¢âÅÃ¢ââ¬ DÃÂ©fense : Faible
Ã¢ââÃ¢ââ¬ Ãâ°conomique

Taille L : Cargo lourd
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 1D6/PA
Ã¢âÅÃ¢ââ¬ Soutes : 1000-5000 unitÃÂ©s
Ã¢âÅÃ¢ââ¬ Ãâ°quipage : 10-30
Ã¢ââÃ¢ââ¬ Rentable longues distances

Taille XL : Transport masse
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 1D4/PA
Ã¢âÅÃ¢ââ¬ Soutes : 10 000+ unitÃÂ©s
Ã¢âÅÃ¢ââ¬ Escorte nÃÂ©cessaire
Ã¢ââÃ¢ââ¬ Lignes commerciales
```

#### 4. RECHERCHE ÄÅ¸âÂ¬
```
Taille M : Laboratoire mobile
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 3D8/PA (senseurs scientifiques)
Ã¢âÅÃ¢ââ¬ Analyse : SpectromÃÂ©trie, ÃÂ©chantillons
Ã¢âÅÃ¢ââ¬ Bonus identification PoV
Ã¢ââÃ¢ââ¬ RÃÂ´le : Ãâ°tudes planÃÂ©taires

Taille L : Station recherche mobile
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 4D10/PA
Ã¢âÅÃ¢ââ¬ Labs multiples
Ã¢âÅÃ¢ââ¬ Stationnement longue durÃÂ©e
Ã¢ââÃ¢ââ¬ RÃÂ´le : Recherche approfondie
```

#### 5. CONSTRUCTION ÄÅ¸ï¿½âÃ¯Â¸ï¿½
```
Taille L : Navire-usine
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 1D6/PA
Ã¢âÅÃ¢ââ¬ Fabrique satellites, drones
Ã¢âÅÃ¢ââ¬ Extraction ressources basique
Ã¢ââÃ¢ââ¬ Lent mais polyvalent

Taille XL : Constructor capital
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 2D6/PA
Ã¢âÅÃ¢ââ¬ Construit stations, bases
Ã¢âÅÃ¢ââ¬ Ãâ°quipage : 50-100
Ã¢ââÃ¢ââ¬ Infrastructure majeure
```

#### 6. DONNÃâ°ES & COMMUNICATION ÄÅ¸âÂ¡
```
Taille S : Relais mobile
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 1D8/PA
Ã¢âÅÃ¢ââ¬ PortÃÂ©e com' : 5 AL
Ã¢âÅÃ¢ââ¬ FurtivitÃÂ© : Bonne
Ã¢ââÃ¢ââ¬ Espionnage/Intel

Taille M : Vaisseau SIGINT
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 4D6/PA (passif)
Ã¢âÅÃ¢ââ¬ Interception communications
Ã¢âÅÃ¢ââ¬ Guerre ÃÂ©lectronique
Ã¢ââÃ¢ââ¬ Militaire spÃÂ©cialisÃÂ©

Taille L : Hub communication
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 2D8/PA
Ã¢âÅÃ¢ââ¬ PortÃÂ©e : 10 AL
Ã¢âÅÃ¢ââ¬ Stockage donnÃÂ©es massif
Ã¢ââÃ¢ââ¬ NÃâud rÃÂ©seau guilde
```

#### 7. STATIONS MOBILES ÄÅ¸âºÂ°Ã¯Â¸ï¿½
```
Taille XL : Station mobile
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 3D8/PA
Ã¢âÅÃ¢ââ¬ Hangar : 5-10 petits vaisseaux
Ã¢âÅÃ¢ââ¬ Autonomie : mois/annÃÂ©es
Ã¢âÅÃ¢ââ¬ Ãâ°quipage : 100-500
Ã¢ââÃ¢ââ¬ RÃÂ´le : Base avancÃÂ©e

Taille Capital : CitÃÂ©-vaisseau
Ã¢âÅÃ¢ââ¬ DÃÂ©tection : 4D10+10/PA
Ã¢âÅÃ¢ââ¬ Population : 1000-5000
Ã¢âÅÃ¢ââ¬ Autosuffisante
Ã¢âÅÃ¢ââ¬ Flotte intÃÂ©grÃÂ©e
Ã¢ââÃ¢ââ¬ SiÃÂ¨ge guilde/faction
```

### SystÃÂ¨me d'ÃÂ©quipements

**Slots selon taille :**
```
Taille vaisseau = Nombre slots
Ã¢âÅÃ¢ââ¬ S : 1-2 slots dÃÂ©tection
Ã¢âÅÃ¢ââ¬ M : 2-4 slots dÃÂ©tection
Ã¢âÅÃ¢ââ¬ L : 4-8 slots dÃÂ©tection
Ã¢ââÃ¢ââ¬ XL/Capital : 8-16 slots dÃÂ©tection
```

**Types dÃÂ©tecteurs :**
```
Ã¢âÅÃ¢ââ¬ Passif (1 slot) : 1D6, furtif
Ã¢âÅÃ¢ââ¬ Actif Standard (1 slot) : 1D8
Ã¢âÅÃ¢ââ¬ Longue portÃÂ©e (2 slots) : 1D10
Ã¢âÅÃ¢ââ¬ Militaire (2 slots) : 1D12
Ã¢ââÃ¢ââ¬ Scientifique (3 slots) : 2D8 + bonus identification
```

**Synergie :** 
- 3 dÃÂ©tecteurs actifs sur M = 3D8/PA cumulÃÂ©s

### Ãâ°conomie vaisseaux

**Prix indicatifs (Archiluminique) :**
```
DÃâ°PART (accessibles tour 1)
Ã¢âÅÃ¢ââ¬ Shuttle lÃÂ©ger : 500-1000 cr
Ã¢âÅÃ¢ââ¬ Scout basique : 1500-2500 cr
Ã¢âÅÃ¢ââ¬ Cargo starter : 2000-3000 cr
Ã¢ââÃ¢ââ¬ Chasseur occasion : 3000-4000 cr

PROGRESSION
Ã¢âÅÃ¢ââ¬ Vaisseaux M : 10K-50K cr
Ã¢âÅÃ¢ââ¬ Vaisseaux L : 100K-500K cr
Ã¢ââÃ¢ââ¬ Capitaux XL : 5M-50M+ cr
```

**Gestion flotte :**
- Achat, vente (40-60% valeur neuve)
- Stockage hangar (10K cr/mois/vaisseau)
- Assurance (5-10% valeur/an, rembourse 80%)
- AmÃÂ©lioration modulaire (slots)

---

## ÄÅ¸âÂ° Ãâ°CONOMIE ET RESSOURCES

### ChaÃÂ®ne de production (3 niveaux)

```
EXTRACTION (sites naturels)
Ã¢âÅÃ¢ââ¬ Minerais bruts
Ã¢âÅÃ¢ââ¬ Glace/Eau
Ã¢âÅÃ¢ââ¬ Gaz
Ã¢âÅÃ¢ââ¬ MatiÃÂ¨re organique
Ã¢ââÃ¢ââ¬ MatiÃÂ¨re exotique (rare)
    Ã¢â â
RAFFINAGE (stations/vaisseaux-usine)
Ã¢âÅÃ¢ââ¬ MÃÂ©taux communs (fer, aluminium)
Ã¢âÅÃ¢ââ¬ MÃÂ©taux rares (titane, platine)
Ã¢âÅÃ¢ââ¬ PolymÃÂ¨res
Ã¢âÅÃ¢ââ¬ Carburants
Ã¢âÅÃ¢ââ¬ ComposÃÂ©s chimiques
Ã¢âÅÃ¢ââ¬ Bio-nutriments
Ã¢ââÃ¢ââ¬ MatÃÂ©riaux exotiques
    Ã¢â â
PRODUCTION (usines spÃÂ©cialisÃÂ©es)
Ã¢âÅÃ¢ââ¬ Produits SIMPLES (1 composant)
Ã¢âÅÃ¢ââ¬ Produits INTERMÃâ°DIAIRES (2-4 composants)
Ã¢ââÃ¢ââ¬ Produits COMPLEXES (5+ composants + intermÃÂ©diaires)
```

### Domaines ÃÂ©conomiques

#### 1. ALIMENTATION ÄÅ¸ï¿½Â½Ã¯Â¸ï¿½
```
Basique (Simple)
Ã¢âÅÃ¢ââ¬ Croquettes nutritives
Ã¢ââÃ¢ââ¬ CoÃÂ»t : 1 cr/unitÃÂ© | Moral : AffectÃÂ©

Standard (IntermÃÂ©diaire)
Ã¢âÅÃ¢ââ¬ Repas spatiaux
Ã¢ââÃ¢ââ¬ CoÃÂ»t : 5 cr/unitÃÂ© | Moral : Satisfait

QualitÃÂ© (Complexe)
Ã¢âÅÃ¢ââ¬ UnitÃÂ©s synthÃÂ©tiseur gourmet
Ã¢ââÃ¢ââ¬ CoÃÂ»t : 20 cr/unitÃÂ© | Moral : Heureux

Luxe (Complexe+)
Ã¢âÅÃ¢ââ¬ Repas rÃÂ©els (agriculture spatiale)
Ã¢ââÃ¢ââ¬ CoÃÂ»t : 100+ cr/unitÃÂ© | Moral : Loyal
```

#### 2. Ãâ°NERGIE Ã¢Å¡Â¡
```
Ã¢âÅÃ¢ââ¬ Cellules standard (10 jours autonomie)
Ã¢âÅÃ¢ââ¬ Batteries haute capacitÃÂ© (30 jours)
Ã¢ââÃ¢ââ¬ RÃÂ©acteurs fusion (100+ jours)
```

#### 3. ARMEMENT ÄÅ¸âÂ«
```
Simple : Armes lÃÂ©gÃÂ¨res, munitions
IntermÃÂ©diaire : Tourelles, missiles, boucliers
Complexe : Lasers militaires, torpilles plasma
```

#### 4. CONFORT ÄÅ¸âºï¿½Ã¯Â¸ï¿½
```
Minimal : Couchettes | Moral : -10%
Standard : Cabines | Moral : neutre
Luxe : Suites, gravitÃÂ© artificielle | Moral : +20%
```

#### 5. Ãâ°LECTRONIQUE ÄÅ¸âÂ»
```
Simple : Circuits, capteurs
IntermÃÂ©diaire : Ordinateurs, navigation
Complexe : IA tactiques, serveurs
```

#### 6. MÃâ°CANIQUE ÄÅ¸âÂ§
```
Simple : PiÃÂ¨ces dÃÂ©tachÃÂ©es, outils
IntermÃÂ©diaire : Moteurs subluminiques
Complexe : Drives hyper-espace
```

#### 7. SANTÃâ° ÄÅ¸ï¿½Â¥
```
Basique : Trousses premiers soins
Standard : Medkits avancÃÂ©s, scanners
AvancÃÂ© : RÃÂ©gÃÂ©nÃÂ©rateurs tissulaires, nano-mÃÂ©decine
```

#### 8. DIVERTISSEMENT ÄÅ¸ï¿½Â®
```
Minimal : Holovids | Moral : +5%
Standard : BibliothÃÂ¨ques, VR | Moral : +15%
Premium : Holodeck immersif | Moral : +30%
```

#### 9. Ãâ°DUCATION ÄÅ¸âÅ¡
```
Basique : Manuels | +1% XP ÃÂ©quipage
Standard : Simulateurs, IA tuteurs | +3% XP
AvancÃÂ© : Labs recherche | +5% XP + dÃÂ©couvertes
```

#### 10. CONSTRUCTION ÄÅ¸ï¿½âÃ¯Â¸ï¿½
```
Simple : Structures basiques
IntermÃÂ©diaire : Stations modulaires
Complexe : Installations capitales
```

#### 11. DONNÃâ°ES/INTEL ÄÅ¸âÅ 
```
Simple : Cartes basiques
IntermÃÂ©diaire : DonnÃÂ©es marchÃÂ©, routes
Complexe : Intel militaire, brevets tech
```

#### 12. LUXE ÄÅ¸âï¿½
```
Simple : Souvenirs, art mineur
IntermÃÂ©diaire : Ãâuvres d'art, vins
Complexe : AntiquitÃÂ©s, artefacts
```

### SystÃÂ¨me de personnel

**Besoins par production :**
```
Ã¢âÅÃ¢ââ¬ Produits Simples : 1-2 ouvriers/usine
Ã¢âÅÃ¢ââ¬ IntermÃÂ©diaires : 5-10 techniciens/usine
Ã¢ââÃ¢ââ¬ Complexes : 20-50 spÃÂ©cialistes/usine
```

**SpÃÂ©cialisations :**
- Mineurs (extraction)
- IngÃÂ©nieurs (raffinage)
- Techniciens (production simple/intermÃÂ©diaire)
- Scientifiques (production complexe)
- MÃÂ©decins (santÃÂ©)
- Soldats (armement/sÃÂ©curitÃÂ©)
- Administrateurs (gestion)

**Formule productivitÃÂ© :**
```
ProductivitÃÂ© = f(Moral, Ãâ°ducation, Ãâ°quipement)
Moral = f(Alimentation, Confort, SantÃÂ©, Divertissement, Salaire)
```

### MarchÃÂ© de l'information

**PoV dÃÂ©couverts = vendables :**

```
Valeur selon :
Ã¢âÅÃ¢ââ¬ RaretÃÂ© (nouveau vs connu)
Ã¢âÅÃ¢ââ¬ Type (planÃÂ¨te habitable > astÃÂ©roÃÂ¯de)
Ã¢âÅÃ¢ââ¬ Ressources potentielles
Ã¢âÅÃ¢ââ¬ Position stratÃÂ©gique
Ã¢ââÃ¢ââ¬ Ãâge info (fraÃÂ®che = cher)

Acheteurs :
Ã¢âÅÃ¢ââ¬ Guilde Cartographes
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Prix standard
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Diffusion publique (dÃÂ©lai quelques tours)
Ã¢ââ   Ã¢ââÃ¢ââ¬ CrÃÂ©dibilitÃÂ© = meilleur prix futur
Ã¢ââ
Ã¢âÅÃ¢ââ¬ Guildes spÃÂ©cialisÃÂ©es
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Mineurs Ã¢â â astÃÂ©roÃÂ¯des riches
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Militaires Ã¢â â bases ennemies
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Scientifiques Ã¢â â anomalies
Ã¢ââ   Ã¢ââÃ¢ââ¬ Prix premium, diffusion restreinte
Ã¢ââ
Ã¢ââÃ¢ââ¬ Joueurs/Corporations privÃÂ©es
    Ã¢âÅÃ¢ââ¬ NÃÂ©gociation libre
    Ã¢âÅÃ¢ââ¬ ExclusivitÃÂ© totale possible
    Ã¢ââÃ¢ââ¬ Espionnage ÃÂ©conomique
```

---

## Ã¢Å¡âÃ¯Â¸ï¿½ COMBAT ET ZONES DE CONTRÃâLE

### Zones de contrÃÂ´le

#### ZONE 1 : ESPACE EMPIRE (0-100 AL Soleil)
```
SÃÂ©curitÃÂ© : HAUTE
Ã¢âÅÃ¢ââ¬ Couverture satellite : 100%
Ã¢âÅÃ¢ââ¬ Patrouilles militaires : frÃÂ©quentes
Ã¢ââÃ¢ââ¬ Temps rÃÂ©ponse : 1-3 tours

RÃÂ¨gles d'engagement :
Ã¢Åâ Attaque pirates (marquÃÂ©s rouge)
Ã¢Åâ Attaque ennemis dÃÂ©clarÃÂ©s Empire
Ã¢Åâ LÃÂ©gitime dÃÂ©fense (aprÃÂ¨s 1er tir reÃÂ§u)
Ã¢Åâ Tir en premier sur civil/neutre
Ã¢Åâ Attaque autoritÃÂ©s/police

Infractions Ã¢â â ConsÃÂ©quences :
Ã¢âÅÃ¢ââ¬ Tir illÃÂ©gal : Amende + rÃÂ©putation -50
Ã¢âÅÃ¢ââ¬ Meurtre civil : Prison + rÃÂ©putation -200 + bounty
Ã¢ââÃ¢ââ¬ Attaque autoritÃÂ© : Wanted permanent + flotte
```

#### ZONE 2 : ESPACE COLONISÃâ°
```
SÃÂ©curitÃÂ© : MOYENNE
Ã¢âÅÃ¢ââ¬ Couverture satellite : 60-80%
Ã¢âÅÃ¢ââ¬ Patrouilles : occasionnelles
Ã¢ââÃ¢ââ¬ Temps rÃÂ©ponse : 5-10 tours

RÃÂ¨gles : Similaires Empire, application variable
```

#### ZONE 3 : FRONTIÃËRE
```
SÃÂ©curitÃÂ© : FAIBLE
Ã¢âÅÃ¢ââ¬ Couverture satellite : 20-40%
Ã¢âÅÃ¢ââ¬ Patrouilles : rares
Ã¢ââÃ¢ââ¬ Temps rÃÂ©ponse : 20+ tours

RÃÂ¨gles : Loi du plus fort
DÃÂ©tection : Peu probable
```

#### ZONE 4 : ESPACE SAUVAGE
```
SÃÂ©curitÃÂ© : NULLE
Ã¢âÅÃ¢ââ¬ Couverture satellite : 0%
Ã¢âÅÃ¢ââ¬ Patrouilles : inexistantes
Ã¢ââÃ¢ââ¬ Pas d'autoritÃÂ©

RÃÂ¨gles :
Ã¢âÅÃ¢ââ¬ AUCUNE loi
Ã¢âÅÃ¢ââ¬ PvP libre
Ã¢âÅÃ¢ââ¬ Ressources riches
Ã¢ââÃ¢ââ¬ DÃÂ©tection : Impossible
```

### SystÃÂ¨me de combat (tour par tour)

**Initiative : Le Projecteur (Spotlight)**

Daggerheart n'utilise **PAS d'initiative traditionnelle.**

```
SystÃÂ¨me du Projecteur (adaptÃÂ© pour jeu solo/multi) :

1. TOUR JOUEUR(S)
   Ã¢âÅÃ¢ââ¬ Joueur actif (a le Projecteur)
   Ã¢ââ   Ã¢âÅÃ¢ââ¬ Effectue ses actions (dÃÂ©pense PA)
   Ã¢ââ   Ã¢ââÃ¢ââ¬ Si multi-joueurs : passe Projecteur ÃÂ  alliÃÂ©
   Ã¢ââ
   Ã¢ââÃ¢ââ¬ Fin tour joueur(s)

2. TOUR SYSTÃËME/IA
   Ã¢âÅÃ¢ââ¬ Active les adversaires (1d20 pour leurs actions)
   Ã¢âÅÃ¢ââ¬ Utilise automatiquement jetons de Peur (cachÃÂ©s) :
   Ã¢ââ   Ã¢âÅÃ¢ââ¬ Activer ennemi supplÃÂ©mentaire (1 jeton)
   Ã¢ââ   Ã¢âÅÃ¢ââ¬ CapacitÃÂ© spÃÂ©ciale ennemie (1-3 jetons)
   Ã¢ââ   Ã¢âÅÃ¢ââ¬ Renforcer attaque (1 jeton = +1d6 dÃÂ©gÃÂ¢ts)
   Ã¢ââ   Ã¢ââÃ¢ââ¬ Introduire complication (2 jetons)
   Ã¢ââ
   Ã¢ââÃ¢ââ¬ Projecteur retourne au(x) joueur(s)

Cycle continu jusqu'ÃÂ  fin combat (fuite/reddition/destruction)
```

**Adaptation selon mode de jeu :**
- **Solo/PvE** : Alternance automatique joueur Ã¢â â systÃÂ¨me
- **Multi/Coop** : Joueurs se passent Projecteur Ã¢â â systÃÂ¨me
- **PvP Asynchrone** : SystÃÂ¨me gÃÂ¨re le combat avec comportements prÃÂ©dÃÂ©finis

---

### Combat PvP Asynchrone

**ProblÃÂ©matique :**
Les joueurs ne sont pas connectÃÂ©s en mÃÂªme temps. Quand un joueur A attaque un joueur B absent, le systÃÂ¨me doit gÃÂ©rer automatiquement la dÃÂ©fense du joueur B.

**Solution : Comportements de combat prÃÂ©dÃÂ©finis**

Chaque joueur dÃÂ©finit des **rÃÂ¨gles de comportement** pour son vaisseau/flotte :

```
COMPORTEMENT DE DÃâ°FENSE (ÃÂ  dÃÂ©velopper)
Ã¢âÅÃ¢ââ¬ StratÃÂ©gie : Offensive / DÃÂ©fensive / Fuite
Ã¢âÅÃ¢ââ¬ Seuil de fuite : % HP restants (ex: fuir si <30% HP)
Ã¢âÅÃ¢ââ¬ PrioritÃÂ©s de cible : Plus proche / Plus faible / Plus dangereux
Ã¢âÅÃ¢ââ¬ Utilisation capacitÃÂ©s : Conservateur / Agressif
Ã¢ââÃ¢ââ¬ Gestion PA : Attaque prioritaire / DÃÂ©fense prioritaire

COMPORTEMENT D'ATTAQUE (ÃÂ  dÃÂ©velopper)
Ã¢âÅÃ¢ââ¬ Approche : Frontal / Flanc / Distance
Ã¢âÅÃ¢ââ¬ SÃÂ©lection armes : Selon distance / Selon cible
Ã¢âÅÃ¢ââ¬ Utilisation jetons Espoir : Jamais / Si critique / Toujours
Ã¢ââÃ¢ââ¬ Condition de dÃÂ©sengagement : Jamais / Si dÃÂ©gÃÂ¢ts lourds

SEUILS DE FUITE AUTOMATIQUE
Ã¢âÅÃ¢ââ¬ HP < X% : Fuite immÃÂ©diate
Ã¢âÅÃ¢ââ¬ Adversaires > Y : Fuite si en infÃÂ©rioritÃÂ© numÃÂ©rique
Ã¢âÅÃ¢ââ¬ SystÃÂ¨mes critiques HS : Fuite si moteurs/armes dÃÂ©truites
Ã¢ââÃ¢ââ¬ Objectif atteint : Se retirer aprÃÂ¨s mission accomplie
```

**RÃÂ©solution d'un combat asynchrone :**

1. Joueur A initie l'attaque contre joueur B (absent)
2. Le systÃÂ¨me charge les comportements prÃÂ©dÃÂ©finis de B
3. Combat simulÃÂ© tour par tour selon les rÃÂ¨gles :
   - Jet de dÃÂ©s pour chaque action (2d12 + compÃÂ©tence)
   - Application des comportements de dÃÂ©fense de B
   - VÃÂ©rification seuils de fuite
4. RÃÂ©sultat enregistrÃÂ© (victoire/dÃÂ©faite/fuite)
5. Joueur B reÃÂ§oit rapport ÃÂ  sa prochaine connexion

**Note :** Les dÃÂ©tails mÃÂ©caniques du combat asymÃÂ©trique sont ÃÂ  dÃÂ©velopper ultÃÂ©rieurement.

**Phases d'un tour (joueur avec Projecteur) :**

```
Ã¢âÅÃ¢ââ¬ Phase 1 : MOUVEMENT (coÃÂ»t PA)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Rapprocher/Ãâ°loigner (1-3 PA)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ ManÃâuvre ÃÂ©vasive (2 PA, +dÃÂ©fense)
Ã¢ââ   Ã¢ââÃ¢ââ¬ Interception (3 PA, bloque fuite)
Ã¢ââ
Ã¢âÅÃ¢ââ¬ Phase 2 : ACTIONS (PA restants)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Attaque arme (coÃÂ»t variable)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Scan ennemi (1 PA)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Contremesures (2 PA)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ RÃÂ©parations urgentes (3 PA)
Ã¢ââ   Ã¢âÅÃ¢ââ¬ Utiliser jeton Espoir (bonus/capacitÃÂ©)
Ã¢ââ   Ã¢ââÃ¢ââ¬ Charger armes lourdes (variable)
Ã¢ââ
Ã¢ââÃ¢ââ¬ Phase 3 : RÃâ°SOLUTION
    Ã¢âÅÃ¢ââ¬ Calcul dÃÂ©gÃÂ¢ts
    Ã¢âÅÃ¢ââ¬ Check systÃÂ¨mes endommagÃÂ©s
    Ã¢âÅÃ¢ââ¬ GÃÂ©nÃÂ©ration jetons (Hope/Fear)
    Ã¢ââÃ¢ââ¬ Moral ÃÂ©quipage
```

**MÃÂ©canique attaque/dÃÂ©fense :**

```
Attaque (systÃÂ¨me 2D12 Daggerheart) :
Ã¢âÅÃ¢ââ¬ Jet : 2d12 + Trait (Force/Finesse) + Bonus arme
Ã¢âÅÃ¢ââ¬ SOMME des deux dÃÂ©s + modificateurs
Ã¢âÅÃ¢ââ¬ Comparer au Seuil d'Ãâ°vasion cible
Ã¢ââ   Ã¢ââÃ¢ââ¬ Seuil Ãâ°vasion = 10 + AgilitÃÂ© cible + Armure cible
Ã¢âÅÃ¢ââ¬ Ã¢â°Â¥ Seuil : TouchÃÂ©
Ã¢âÅÃ¢ââ¬ < Seuil : RatÃÂ©
Ã¢ââ
Ã¢ââÃ¢ââ¬ GÃÂ©nÃÂ©ration jetons (dÃÂ©s individuels) :
    Ã¢âÅÃ¢ââ¬ Hope > Fear : +1 jeton Espoir (visible, joueur)
    Ã¢ââÃ¢ââ¬ Fear > Hope : +1 jeton Peur (cachÃÂ©, systÃÂ¨me)

Exemple d'attaque :
> attack pirate_corvette cannon_laser

Cible : Corvette pirate
- Seuil d'Ãâ°vasion : 10 + 2 (AgilitÃÂ©) + 2 (Armure) = 14

ÄÅ¸ï¿½Â² Hope: 9  |  Fear: 11
Finesse +4, Canons +2
RÃÂ©sultat: (9 + 11) + 4 + 2 = 26
DÃÂ©fense cible: 14
Ã¢Åâ TOUCHÃâ° - 25 dÃÂ©gÃÂ¢ts

[SystÃÂ¨me : +1 Peur stockÃÂ© - Fear > Hope]
Ã¢â â Le systÃÂ¨me accumule de la Peur...
   Peut dÃÂ©clencher : renfort, manÃâuvre risquÃÂ©e, etc.

Types d'armes :
Ã¢âÅÃ¢ââ¬ Canons laser (2 PA, moyenne portÃÂ©e, prÃÂ©cis)
Ã¢âÅÃ¢ââ¬ Missiles (3 PA, longue portÃÂ©e, contrable)
Ã¢âÅÃ¢ââ¬ Railgun (4 PA, ÃÂ©norme dÃÂ©gÃÂ¢ts, lent)
Ã¢âÅÃ¢ââ¬ Torpilles (5 PA, dÃÂ©gÃÂ¢ts zone, anti-capital)
Ã¢ââÃ¢ââ¬ EMP (3 PA, dÃÂ©sactive systÃÂ¨mes)

DÃÂ©fense :
Ã¢âÅÃ¢ââ¬ Seuil Ãâ°vasion = 10 + AgilitÃÂ© + Armure
Ã¢âÅÃ¢ââ¬ Blindage (absorbe X dÃÂ©gÃÂ¢ts/tour)
Ã¢âÅÃ¢ââ¬ Boucliers (PA rechargeable)
Ã¢ââÃ¢ââ¬ ManÃâuvres actives (coÃÂ»t PA, +bonus temporaire)
```

**Dommages & SystÃÂ¨mes :**

```
Points de Coque (HP) :
Ã¢âÅÃ¢ââ¬ S : 50-100 HP
Ã¢âÅÃ¢ââ¬ M : 150-300 HP
Ã¢âÅÃ¢ââ¬ L : 400-800 HP
Ã¢âÅÃ¢ââ¬ XL : 1000-2000 HP
Ã¢ââÃ¢ââ¬ Capital : 5000+ HP

DÃÂ©gÃÂ¢ts critiques (% HP restant) :
Ã¢âÅÃ¢ââ¬ 75% : SystÃÂ¨me -25% efficacitÃÂ©
Ã¢âÅÃ¢ââ¬ 50% : Fuite, -1 PA/tour
Ã¢âÅÃ¢ââ¬ 25% : SystÃÂ¨me majeur HS
Ã¢âÅÃ¢ââ¬ 0% : Destruction OU reddition

SystÃÂ¨mes ciblables (si scan rÃÂ©ussi) :
Ã¢âÅÃ¢ââ¬ Moteurs (immobilise)
Ã¢âÅÃ¢ââ¬ Armes (dÃÂ©sarme)
Ã¢âÅÃ¢ââ¬ Senseurs (aveugle)
Ã¢âÅÃ¢ââ¬ GÃÂ©nÃÂ©rateur (coupe boucliers)
Ã¢ââÃ¢ââ¬ Pont (moral ÃÂ©quipage)
```

### Types ennemis PNJ

```
Pirates solitaires (S-M)
Ã¢âÅÃ¢ââ¬ IA : Opportuniste
Ã¢âÅÃ¢ââ¬ Fuit si <40% HP
Ã¢ââÃ¢ââ¬ Butin : Moyen

Gangs pirates (3-6 vaisseaux)
Ã¢âÅÃ¢ââ¬ IA : CoordonnÃÂ©e
Ã¢âÅÃ¢ââ¬ Fuit si leader dÃÂ©truit
Ã¢ââÃ¢ââ¬ Butin : Bon

Cartels (10+ vaisseaux + base)
Ã¢âÅÃ¢ââ¬ IA : Tactique
Ã¢âÅÃ¢ââ¬ Renforts si base attaquÃÂ©e
Ã¢ââÃ¢ââ¬ Butin : Excellent

RenÃÂ©gats/Mercenaires
Ã¢âÅÃ¢ââ¬ IA : Variable selon contrat
Ã¢âÅÃ¢ââ¬ Ãâ°quipement militaire
Ã¢ââÃ¢ââ¬ Ne fuit jamais si payÃÂ©

AutoritÃÂ©s (Police/Militaire)
Ã¢âÅÃ¢ââ¬ IA : LÃÂ©galiste
Ã¢âÅÃ¢ââ¬ Scan d'abord
Ã¢ââÃ¢ââ¬ Appel renforts si perdant
```

---

## ÄÅ¸ï¿½âºÃ¯Â¸ï¿½ RÃâ°PUTATION ET FACTIONS

### SystÃÂ¨me de rÃÂ©putation

**Par guilde/faction indÃÂ©pendante :**

```
Paliers rÃÂ©putation (0-25000+ pts) :
Ã¢âÅÃ¢ââ¬ Ãâ°tranger (0) : AccÃÂ¨s basique
Ã¢âÅÃ¢ââ¬ Connu (500) : -5% prix, missions Rang 1
Ã¢âÅÃ¢ââ¬ Ami (2000) : -10% prix, missions Rang 2, accÃÂ¨s donnÃÂ©es
Ã¢âÅÃ¢ââ¬ RespectÃÂ© (5000) : -15% prix, missions Rang 3, ÃÂ©quipements spÃÂ©ciaux
Ã¢âÅÃ¢ââ¬ HonorÃÂ© (10000) : -20% prix, missions Rang 4, blueprints rares
Ã¢ââÃ¢ââ¬ LÃÂ©gende (25000) : -25% prix, missions uniques, siÃÂ¨ge guilde ?

RÃÂ©putation nÃÂ©gative possible (ennemi)
```

**Guildes principales :**

```
GUILDES IMPÃâ°RIALES (IA au dÃÂ©but)
Ã¢âÅÃ¢ââ¬ Empire Terrien (militaire/admin)
Ã¢âÅÃ¢ââ¬ Guilde des Cartographes
Ã¢âÅÃ¢ââ¬ Guilde des Marchands
Ã¢âÅÃ¢ââ¬ AcadÃÂ©mie Scientifique
Ã¢ââÃ¢ââ¬ Autres selon univers

GUILDES JOUEURS (crÃÂ©ables)
Ã¢âÅÃ¢ââ¬ GÃÂ©rÃÂ©es par joueurs
Ã¢âÅÃ¢ââ¬ Peuvent dÃÂ©finir propres rÃÂ¨gles
Ã¢âÅÃ¢ââ¬ Commerce, exploration, militaire, etc.
Ã¢ââÃ¢ââ¬ Territoires contrÃÂ´lÃÂ©s
```

**Actions affectant rÃÂ©putation :**

```
Ã¢âÅÃ¢ââ¬ Tuer pirate : Empire +10, Pirates -20
Ã¢âÅÃ¢ââ¬ Tuer civil neutre : Toutes factions -100
Ã¢âÅÃ¢ââ¬ Mission guilde rÃÂ©ussie : +50-200 pts
Ã¢âÅÃ¢ââ¬ Trahison contrat : -200-500 pts
Ã¢âÅÃ¢ââ¬ Partage donnÃÂ©es : Cartographes +5-50
Ã¢ââÃ¢ââ¬ Commerce rÃÂ©gulier : +1-5 pts/transaction
```

---

## ÄÅ¸ÅÅ GÃâ°NÃâ°RATION PROCÃâ°DURALE

### Sources de donnÃÂ©es rÃÂ©elles

**Bases exploitables :**

1. **ESA GAIA** (Ãâ°toiles)
   - 1.8+ milliards d'ÃÂ©toiles cartographiÃÂ©es
   - Positions 3D, distances, mouvements
   - Type spectral, magnitude, tempÃÂ©rature
   - API TAP accessible

2. **NASA Exoplanet Archive** (PlanÃÂ¨tes connues)
   - 29 000+ exoplanÃÂ¨tes confirmÃÂ©es
   - Masse, rayon, pÃÂ©riode orbitale
   - Distance ÃÂ©toile hÃÂ´te
   - API REST/TAP

3. **JPL Small-Body Database** (AstÃÂ©roÃÂ¯des/ComÃÂ¨tes)
   - Tous astÃÂ©roÃÂ¯des/comÃÂ¨tes systÃÂ¨me solaire
   - ParamÃÂ¨tres orbitaux
   - Composition physique
   - API JSON

4. **JPL Horizons** (Ãâ°phÃÂ©mÃÂ©rides)
   - Positions prÃÂ©cises temps rÃÂ©el
   - PlanÃÂ¨tes, lunes, astÃÂ©roÃÂ¯des
   - Calculs orbitaux
   - API REST

### Algorithme de gÃÂ©nÃÂ©ration systÃÂ¨mes

**Principe :**
```
Seed = GAIA source_id de l'ÃÂ©toile
Ã¢â â GÃÂ©nÃÂ©ration reproductible identique pour tous
```

**Budget de masse :**
```
Budget = Masse_ÃÂ©toile (MÃ¢Ëâ°) Ãâ 50 unitÃÂ©s

Exemple :
- 1 MÃ¢Ëâ° (type Soleil) = 50 unitÃÂ©s
- 0.5 MÃ¢Ëâ° (naine rouge) = 25 unitÃÂ©s
- 2 MÃ¢Ëâ° (type A) = 100 unitÃÂ©s
```

### Profils stellaires

#### TYPE O/B (GÃÂ©antes bleues) - 15-60 MÃ¢Ëâ°
```
Budget : 80-200 unitÃÂ©s
Zone habitable : 50-100 UA (trop lointaine)
GÃÂ©nÃÂ©ration :
Ã¢âÅÃ¢ââ¬ 30% : GÃÂ©ante gazeuse massive (50-80 u)
Ã¢âÅÃ¢ââ¬ 40% : Ceintures astÃÂ©roÃÂ¯des ÃÂ©paisses (10-20 u)
Ã¢âÅÃ¢ââ¬ 20% : PlanÃÂ¨tes rocheuses irradiÃÂ©es (5-15 u)
Ã¢ââÃ¢ââ¬ 10% : Vide
IntÃÂ©rÃÂªt : MinÃÂ©ral riche, dangereux
```

#### TYPE A (Blanches) - 1.4-2.1 MÃ¢Ëâ°
```
Budget : 40-80 unitÃÂ©s
Zone habitable : 4-10 UA
GÃÂ©nÃÂ©ration :
Ã¢âÅÃ¢ââ¬ 40% : GÃÂ©ante gazeuse (30-50 u)
Ã¢âÅÃ¢ââ¬ 30% : Telluriques intÃÂ©rieures (10-20 u)
Ã¢âÅÃ¢ââ¬ 20% : Ceintures (5-15 u)
Ã¢ââÃ¢ââ¬ 10% : Mini-systÃÂ¨me
IntÃÂ©rÃÂªt : Commerce, bases militaires
```

#### TYPE F (Jaune-blanc) - 1.04-1.4 MÃ¢Ëâ°
```
Budget : 35-60 unitÃÂ©s
Zone habitable : 1.5-3 UA
GÃÂ©nÃÂ©ration ÃÂ©quilibrÃÂ©e :
Ã¢âÅÃ¢ââ¬ 35% : 1-2 gazeuses (20-40 u)
Ã¢âÅÃ¢ââ¬ 40% : 2-4 telluriques (15-30 u)
Ã¢âÅÃ¢ââ¬ 15% : Ceinture (5-10 u)
Ã¢ââÃ¢ââ¬ 10% : Lunes multiples
IntÃÂ©rÃÂªt : Colonies potentielles
```

#### TYPE G (Solaire) - 0.8-1.04 MÃ¢Ëâ°
```
Budget : 30-50 unitÃÂ©s
Zone habitable : 0.9-1.5 UA Ã¢Â­ï¿½ OPTIMAL
GÃÂ©nÃÂ©ration type SystÃÂ¨me Solaire :
Ã¢âÅÃ¢ââ¬ 25% : 1-2 gazeuses extÃÂ©rieures (15-30 u)
Ã¢âÅÃ¢ââ¬ 45% : 3-5 telluriques (12-25 u)
Ã¢âÅÃ¢ââ¬ 20% : Ceinture astÃÂ©roÃÂ¯des (5-10 u)
Ã¢ââÃ¢ââ¬ 10% : SystÃÂ¨me riche lunes
IntÃÂ©rÃÂªt : COLONISATION PRIORITAIRE
```

#### TYPE K (Orange) - 0.45-0.8 MÃ¢Ëâ°
```
Budget : 20-40 unitÃÂ©s
Zone habitable : 0.3-0.9 UA (proche)
GÃÂ©nÃÂ©ration compacte :
Ã¢âÅÃ¢ââ¬ 30% : 1 gazeuse moyenne (10-20 u)
Ã¢âÅÃ¢ââ¬ 50% : 2-4 telluriques rapprochÃÂ©es (10-25 u)
Ã¢âÅÃ¢ââ¬ 15% : Ceinture fine (3-8 u)
Ã¢ââÃ¢ââ¬ 5% : SystÃÂ¨me pauvre
IntÃÂ©rÃÂªt : Stable, longue durÃÂ©e vie
```

#### TYPE M (Naine rouge) - 0.08-0.45 MÃ¢Ëâ°
```
Budget : 10-30 unitÃÂ©s
Zone habitable : 0.05-0.3 UA (trÃÂ¨s proche)
GÃÂ©nÃÂ©ration minimaliste :
Ã¢âÅÃ¢ââ¬ 20% : 1 petite gazeuse (5-15 u)
Ã¢âÅÃ¢ââ¬ 60% : 1-3 telluriques verrouillÃÂ©es (5-20 u)
Ã¢âÅÃ¢ââ¬ 15% : AstÃÂ©roÃÂ¯des ÃÂ©pars (2-5 u)
Ã¢ââÃ¢ââ¬ 5% : Vide
IntÃÂ©rÃÂªt : Nombreuses, verrouillage gravitationnel
```

#### NAINES BRUNES - 0.01-0.08 MÃ¢Ëâ°
```
Budget : 5-15 unitÃÂ©s
Pas de zone habitable
GÃÂ©nÃÂ©ration rare :
Ã¢âÅÃ¢ââ¬ 40% : 1-2 planÃÂ¨tes errantes capturÃÂ©es
Ã¢ââÃ¢ââ¬ 60% : Vide
IntÃÂ©rÃÂªt : Cachettes, bases clandestines
```

### CoÃÂ»ts budgÃÂ©taires par objet

```
GÃâ°ANTES GAZEUSES
Ã¢âÅÃ¢ââ¬ Super-Jupiter (>10 MJ) : 40-60 u + 2D6 lunes
Ã¢âÅÃ¢ââ¬ Jupiter (1-10 MJ) : 25-40 u + 2D4 lunes
Ã¢ââÃ¢ââ¬ Neptune (0.1-1 MJ) : 15-25 u + 1D4 lunes

PLANÃËTES TELLURIQUES
Ã¢âÅÃ¢ââ¬ Super-Terre (>2 MT) : 12-20 u + 1D3 lunes
Ã¢âÅÃ¢ââ¬ Terrestre (0.5-2 MT) : 8-15 u + 1D2 lunes
Ã¢ââÃ¢ââ¬ Mars-like (<0.5 MT) : 5-10 u + 0-2 lunes

LUNES
Ã¢âÅÃ¢ââ¬ Majeure (Titan/GanymÃÂ¨de) : 3-8 u
Ã¢âÅÃ¢ââ¬ Standard (Lune) : 2-5 u
Ã¢ââÃ¢ââ¬ Petite : 1-3 u

CEINTURES ASTÃâ°ROÃï¿½DES
Ã¢âÅÃ¢ââ¬ Dense : 10-20 u (2D6 sites extraction)
Ã¢âÅÃ¢ââ¬ Moyenne : 5-10 u (1D6 sites)
Ã¢ââÃ¢ââ¬ Ãâ°parse : 2-5 u (1D3 sites)

OBJETS SPÃâ°CIAUX
Ã¢âÅÃ¢ââ¬ PlanÃÂ¨te ocÃÂ©an : 10-18 u
Ã¢âÅÃ¢ââ¬ PlanÃÂ¨te dÃÂ©sertique : 8-14 u
Ã¢âÅÃ¢ââ¬ Monde glacÃÂ© : 6-12 u
Ã¢âÅÃ¢ââ¬ PlanÃÂ¨te volcanique : 7-13 u
Ã¢ââÃ¢ââ¬ PlanÃÂ¨te morte : 4-8 u
```

### Implantations humaines

**Zones spatiales (<200 AL Soleil) :**

```
ZONE 1 : CÃâUR EMPIRE (0-100 AL)
Ã¢âÅÃ¢ââ¬ DensitÃÂ© : Forte (80% systÃÂ¨mes G/K colonisÃÂ©s)
Ã¢âÅÃ¢ââ¬ ContrÃÂ´le : Empire Terrien centralisÃÂ©
Ã¢âÅÃ¢ââ¬ Population : ~50 milliards
Ã¢âÅÃ¢ââ¬ SÃÂ©curitÃÂ© : Haute
Ã¢ââÃ¢ââ¬ SystÃÂ¨mes majeurs :
    Ã¢âÅÃ¢ââ¬ Sol (Terre) : 10 milliards
    Ã¢âÅÃ¢ââ¬ Alpha Centauri : 5 milliards
    Ã¢ââÃ¢ââ¬ 50-100 autres colonies

ZONE 2 : FRONTIÃËRE COLONIALE (100-150 AL)
Ã¢âÅÃ¢ââ¬ DensitÃÂ© : Moyenne (40% habitÃÂ©s)
Ã¢âÅÃ¢ââ¬ ContrÃÂ´le : Mixte (Gouverneurs + Guildes)
Ã¢âÅÃ¢ââ¬ Population : ~10 milliards
Ã¢âÅÃ¢ââ¬ SÃÂ©curitÃÂ© : Moyenne ÃÂ  faible
Ã¢ââÃ¢ââ¬ Colonies indÃÂ©pendantes

ZONE 3 : ESPACE PIONNIER (150-200 AL)
Ã¢âÅÃ¢ââ¬ DensitÃÂ© : Faible (10% habitÃÂ©s)
Ã¢âÅÃ¢ââ¬ ContrÃÂ´le : Factions, Guildes, Cartels
Ã¢âÅÃ¢ââ¬ Population : ~1 milliard
Ã¢âÅÃ¢ââ¬ SÃÂ©curitÃÂ© : Nulle
Ã¢ââÃ¢ââ¬ Avant-postes isolÃÂ©s

AU-DELÃâ¬ 200 AL : TERRA INCOGNITA
Ã¢âÅÃ¢ââ¬ Quelques ÃÂ©claireurs
Ã¢âÅÃ¢ââ¬ Bases secrÃÂ¨tes (rumeurs)
Ã¢ââÃ¢ââ¬ Futur contenu (aliens ?)
```

**GÃÂ©nÃÂ©ration colonies :**

```
Pour planÃÂ¨te habitable :
Ã¢âÅÃ¢ââ¬ Roll 1D100 selon distance Terre :
Ã¢ââ   Ã¢âÅÃ¢ââ¬ 0-50 AL : 80% colonisÃÂ©e
Ã¢ââ   Ã¢âÅÃ¢ââ¬ 50-100 AL : 60% colonisÃÂ©e
Ã¢ââ   Ã¢âÅÃ¢ââ¬ 100-150 AL : 30% colonisÃÂ©e
Ã¢ââ   Ã¢âÅÃ¢ââ¬ 150-200 AL : 10% colonisÃÂ©e
Ã¢ââ   Ã¢ââÃ¢ââ¬ >200 AL : 2% colonisÃÂ©e
Ã¢ââ
Ã¢ââÃ¢ââ¬ Si colonisÃÂ©e, taille (1D100) :
    Ã¢âÅÃ¢ââ¬ 1-20 : Avant-poste (100-1000)
    Ã¢âÅÃ¢ââ¬ 21-50 : Petite colonie (1K-50K)
    Ã¢âÅÃ¢ââ¬ 51-80 : Colonie ÃÂ©tablie (50K-1M)
    Ã¢âÅÃ¢ââ¬ 81-95 : Monde mineur (1M-100M)
    Ã¢ââÃ¢ââ¬ 96-100 : Monde majeur (100M-5B)
```

---

## ÄÅ¸âï¿½ POINTS D'INTÃâ°RÃÅ T (PoV)

### DÃÂ©finition
**Point of Value (PoV)** : Tout objet/entitÃÂ© que la base de donnÃÂ©es doit stocker car il a une valeur (stratÃÂ©gique, ÃÂ©conomique, scientifique).

### Types de PoV

#### PoV HYPERSPATIAUX (>1 AL, entre systÃÂ¨mes)
```
Naturels :
Ã¢âÅÃ¢ââ¬ Ãâ°toiles (GAIA source principale)
Ã¢âÅÃ¢ââ¬ Naines brunes
Ã¢âÅÃ¢ââ¬ Nuages interstellaires
Ã¢ââÃ¢ââ¬ Trous noirs vagabonds (trÃÂ¨s rare)

Artificiels :
Ã¢âÅÃ¢ââ¬ Stations relais lointaines
Ã¢âÅÃ¢ââ¬ Vaisseaux en transit
Ã¢âÅÃ¢ââ¬ Balises navigation
Ã¢ââÃ¢ââ¬ Champs de mines (piÃÂ¨ges)
```

#### PoV LOCAUX (dans systÃÂ¨me, <1 AL)
```
Naturels :
Ã¢âÅÃ¢ââ¬ PlanÃÂ¨tes (telluriques, gazeuses)
Ã¢âÅÃ¢ââ¬ Lunes
Ã¢âÅÃ¢ââ¬ Ceintures astÃÂ©roÃÂ¯des
Ã¢âÅÃ¢ââ¬ ComÃÂ¨tes
Ã¢ââÃ¢ââ¬ Anomalies (nuages, champs magnÃÂ©tiques)

Artificiels :
Ã¢âÅÃ¢ââ¬ Bases (actives, abandonnÃÂ©es, ruines)
Ã¢âÅÃ¢ââ¬ Satellites/Stations
Ã¢âÅÃ¢ââ¬ Mines/Extraction
Ã¢âÅÃ¢ââ¬ Ãâ°paves vaisseaux
Ã¢ââÃ¢ââ¬ Balises/Relais
```

### SystÃÂ¨me de dÃÂ©tection PoV

**MÃÂ©canique en 2 phases :**

#### PHASE 1 : ACCUMULATION
```
Chaque tour en mode dÃÂ©tection : +XDY (selon ÃÂ©quipement)
Somme cumulÃÂ©e : S
Condition : S Ã¢â°Â¥ Valeur_Recherche du PoV
Si atteint Ã¢â â Passage Phase 2
```

#### PHASE 2 : RÃâ°SOLUTION
```
1 jet de dÃÂ©s (1D% ou 2D12)
Seuil selon type objet :
Ã¢âÅÃ¢ââ¬ Passif (planÃÂ¨te, ÃÂ©pave) : 90%+ succÃÂ¨s
Ã¢âÅÃ¢ââ¬ Actif furtif (base camouflÃÂ©e) : 50%
Ã¢ââÃ¢ââ¬ Contremesures actives : 20%

SuccÃÂ¨s Ã¢â â PoV dÃÂ©tectÃÂ© et rÃÂ©vÃÂ©lÃÂ© au joueur
```

### Valeurs de recherche (exemples)

| Objet | Valeur Recherche | Notes |
|-------|------------------|-------|
| Ãâ°toile | 5-15 | Selon magnitude |
| PlanÃÂ¨te gÃÂ©ante | 20-30 | Proche = facile |
| PlanÃÂ¨te tellurique | 40-60 | Petite, sombre |
| Lune | 60-80 | TrÃÂ¨s petite |
| Ceinture astÃÂ©roÃÂ¯des | 30-50 | Zone ÃÂ©tendue |
| Base active | 50-100 | Ãâ°missions + contremesures |
| Base fantÃÂ´me | 100-200 | Contremesures militaires |
| Ãâ°pave | 80-120 | Passive, petite |
| Station relais | 30-60 | Ãâ°missions fortes |

### Ãâ°quipement dÃÂ©tection

```
Niveau Base : 1D6/PA
Niveau AmÃÂ©liorÃÂ© : 2D6/PA
Niveau AvancÃÂ© : 3D6/PA
Niveau Militaire : 4D6+bonus/PA

Modificateurs :
Ã¢âÅÃ¢ââ¬ +bonus si zone cartographiÃÂ©e
Ã¢âÅÃ¢ââ¬ -malus si brouillage actif
Ã¢ââÃ¢ââ¬ +bonus si intel prÃÂ©alable
```

### MarchÃÂ© de l'information

**PoV dÃÂ©couverts = vendables :**

```
Acheteurs :
Ã¢âÅÃ¢ââ¬ Guilde Cartographes
Ã¢ââ   Ã¢ââÃ¢ââ¬ Diffusion publique (quelques tours dÃÂ©lai)
Ã¢ââ
Ã¢âÅÃ¢ââ¬ Guildes spÃÂ©cialisÃÂ©es
Ã¢ââ   Ã¢ââÃ¢ââ¬ Diffusion restreinte (membres)
Ã¢ââ
Ã¢ââÃ¢ââ¬ Joueurs/Corporations privÃÂ©es
    Ã¢ââÃ¢ââ¬ ExclusivitÃÂ© totale possible
```

**Valeur dÃÂ©pend de :**
- RaretÃÂ© (nouveau systÃÂ¨me vs dÃÂ©jÃÂ  connu)
- Type (planÃÂ¨te habitable > astÃÂ©roÃÂ¯de banal)
- Ressources potentielles
- Position stratÃÂ©gique
- FraÃÂ®cheur de l'information

---

## ÄÅ¸âÂ» ARCHITECTURE TECHNIQUE

### Stack technique

```
FRONTEND
Ã¢âÅÃ¢ââ¬ HTML/CSS/JavaScript
Ã¢âÅÃ¢ââ¬ Interface console (commandes texte)
Ã¢âÅÃ¢ââ¬ Canvas/WebGL (visualisation optionnelle)
Ã¢ââÃ¢ââ¬ Framework : Vanilla JS ou React/Vue lÃÂ©ger

BACKEND
Ã¢âÅÃ¢ââ¬ Node.js + Express (API REST)
Ã¢âÅÃ¢ââ¬ Python (optionnel, pour scripts GAIA)
Ã¢ââÃ¢ââ¬ WebSocket (temps rÃÂ©el si nÃÂ©cessaire)

BASE DE DONNÃâ°ES
Ã¢âÅÃ¢ââ¬ MySQL/PostgreSQL (principal)
Ã¢âÅÃ¢ââ¬ IndexedDB (cache client-side)
Ã¢ââÃ¢ââ¬ Redis (cache serveur, sessions)

APIs EXTERNES
Ã¢âÅÃ¢ââ¬ ESA GAIA TAP (ÃÂ©toiles)
Ã¢âÅÃ¢ââ¬ NASA Exoplanet Archive (planÃÂ¨tes)
Ã¢âÅÃ¢ââ¬ JPL Horizons (ÃÂ©phÃÂ©mÃÂ©rides)
Ã¢ââÃ¢ââ¬ JPL Small-Body DB (astÃÂ©roÃÂ¯des)
```

### Tables base de donnÃÂ©es (schÃÂ©ma indicatif)

```sql
-- JOUEURS
players (
    id, name, credits, 
    current_system_id, current_position_x/y/z,
    pa_remaining, current_turn,
    created_at
)

-- VAISSEAUX
ships (
    id, owner_id, ship_class, ship_name,
    hull_points, max_hull,
    cargo_capacity, current_cargo,
    equipment_json,
    docked_at_station_id
)

-- SYSTÃËMES STELLAIRES
systems (
    id, gaia_source_id,
    ra, dec, distance_ly,
    spectral_type, mass_solar,
    explored, discovered_by_player_id,
    discovery_turn,
    control_faction_id
)

-- PLANÃËTES/OBJETS
planets (
    id, system_id,
    type (telluric/gas/asteroid_belt/etc),
    orbit_au, mass, radius,
    habitable, population,
    generated_data_json
)

-- BASES/COLONIES
installations (
    id, planet_id,
    type (colony/mine/station/etc),
    owner_faction_id,
    population, infrastructure_level,
    production_json
)

-- POINTS D'INTÃâ°RÃÅ T
pov (
    id, system_id,
    type (hyperspatial/local),
    position_x/y/z,
    detection_value,
    discovered_by_player_id,
    data_json
)

-- SATELLITES
satellites (
    id, system_id, 
    owner_id, 
    tech_level (1/2/3),
    range_al,
    operational
)

-- Ãâ°VÃâ°NEMENTS PEUR (persistance et cohÃÂ©rence narrative)
fear_events (
    id, 
    type (pirate_fleet/anomaly/disaster/etc),
    position_system_id,
    position_x, position_y, position_z,
    created_turn,
    expires_turn,
    status (active/expired/destroyed),
    data_json (dÃÂ©tails spÃÂ©cifiques ÃÂ©vÃÂ©nement),
    fear_cost_consumed
)

-- RÃâ°PUTATION
reputation (
    player_id, faction_id,
    points, rank
)

-- GUILDES
guilds (
    id, name, type,
    leader_player_id,
    controlled_systems_count,
    treasury
)

-- MISSIONS/CONTRATS
contracts (
    id, issuer_faction_id,
    type, difficulty,
    reward_credits, reward_reputation,
    target_system_id,
    expires_turn
)

-- HISTORIQUE TOURS
game_turns (
    turn_number, 
    date_ingame,
    events_json
)

-- MARCHÃâ°
market_listings (
    id, seller_id, item_type,
    quantity, price_per_unit,
    location_system_id
)
```

### Workflow requÃÂªtes GAIA

```
1. Joueur entre en zone inexplorÃÂ©e
2. Backend vÃÂ©rifie si systÃÂ¨me en DB
3. Si non :
   a. RequÃÂªte GAIA TAP (ÃÂ©toile + voisines)
   b. Parse donnÃÂ©es (type, masse, position)
   c. GÃÂ©nÃÂ©ration procÃÂ©durale systÃÂ¨me (seed = source_id)
   d. Stockage DB
4. Si oui : Chargement depuis DB
5. Retour donnÃÂ©es au client
```

**Exemple requÃÂªte GAIA (ADQL) :**

```sql
SELECT source_id, ra, dec, parallax, 
       phot_g_mean_mag, teff_gspphot
FROM gaiadr3.gaia_source
WHERE DISTANCE(
    POINT('ICRS', ra, dec), 
    POINT('ICRS', 150.0, -30.0)
) < 1.0
AND parallax > 0
ORDER BY phot_g_mean_mag ASC
LIMIT 100
```

### Optimisations

**Cache agressif :**
- SystÃÂ¨mes explorÃÂ©s stockÃÂ©s DB
- Fond d'ÃÂ©toiles prÃÂ©-calculÃÂ© par position
- Ãâ°phÃÂ©mÃÂ©rides planÃÂ¨tes calculÃÂ©es ÃÂ  la demande

**Calculs distribuÃÂ©s :**
- GÃÂ©nÃÂ©ration procÃÂ©durale cÃÂ´tÃÂ© serveur
- Rendu visuel cÃÂ´tÃÂ© client
- WebWorkers pour calculs lourds

**Pagination donnÃÂ©es :**
- Chargement zone par zone
- Pas de chargement galaxie entiÃÂ¨re
- Secteurs de 10-50 AL

---

## ÄÅ¸âï¿½ NOTES DE DÃâ°VELOPPEMENT

### PrioritÃÂ©s Phase 1 (MVP)
1. Ã¢Åâ¦ SystÃÂ¨me navigation tour par tour
2. Ã¢Åâ¦ DÃÂ©tection basique (1D6)
3. Ã¢Åâ¦ GÃÂ©nÃÂ©ration procÃÂ©durale simple (ÃÂ©toiles GAIA)
4. Ã¢Åâ¦ Combat PvE basique
5. Ã¢Åâ¦ Ãâ°conomie simplifiÃÂ©e (3-5 ressources)
6. Ã¢Åâ¦ Interface console fonctionnelle

### Phase 2 (Expansion)
- SystÃÂ¨me rÃÂ©putation complet
- Guildes joueurs
- GÃÂ©nÃÂ©ration planÃÂ¨tes avancÃÂ©e
- MarchÃÂ© dynamique
- PvP

### Phase 3 (Endgame)
- Stations mobiles
- Construction
- Diplomatie complexe
- Ãâ°vÃÂ©nements galactiques
- Multi-univers (Star Wars, etc.)

### SystÃÂ¨me de dÃÂ©s ÃÂ  finaliser
**SystÃÂ¨me retenu : Daggerheart (2D12)**

**CaractÃÂ©ristiques finales :**
- Somme des 2d12 + modificateurs vs DifficultÃÂ©
- Jetons d'Espoir (visibles, ressource joueur)
- Jetons de Peur (CACHÃâ°S, capital systÃÂ¨me)
- SystÃÂ¨me utilise automatiquement Peur pour gÃÂ©nÃÂ©rer aventure

**ImplÃÂ©mentation technique :**
```javascript
// Jet de dÃÂ©s
function rollDaggerheart(trait, bonus, difficulty) {
  const hope = random(1, 12);
  const fear = random(1, 12);
  const result = hope + fear + trait + bonus;
  
  let critical = false;
  
  // GÃÂ©nÃÂ©ration jetons
  if (hope > fear) {
    player.hope_tokens++;
    notifyPlayer("Ã¢Åâ Jeton d'Espoir gagnÃÂ© !");
  } else if (fear > hope) {
    system.fear_tokens++; // CACHÃâ°
    // Note : DÃÂ©clenchement vÃÂ©rifiÃÂ© sÃÂ©parÃÂ©ment
  } else if (hope === fear && hope !== 1) {
    // CRITIQUE ! (ÃÂ©galitÃÂ© sauf 1-1)
    player.hope_tokens++;
    critical = true;
    notifyPlayer("Ã¢ÅâÃ¢Åâ CRITIQUE ! RÃÂ©ussite exceptionnelle !");
  } else if (hope === 1 && fear === 1) {
    // CATASTROPHE (1-1) : gÃÂ©nÃÂ¨re Peur
    system.fear_tokens++; // CACHÃâ°
    critical = "catastrophe";
    notifyPlayer("Ã¢ÅâÃ¢Åâ CATASTROPHE !");
  }
  
  return {
    success: result >= difficulty,
    result: result,
    hope: hope,
    fear: fear,
    critical: critical
  };
}

// VÃÂ©rification dÃÂ©clenchement Peur (aprÃÂ¨s action significative ou fin tour)
function checkFearTrigger(player) {
  // FiabilitÃÂ© selon ÃÂ©tat vaisseau
  const reliability_die = player.ship.reliability_die; // ex: 60
  const roll = random(1, reliability_die);
  const fear_capital = system.fear_tokens;
  
  if (roll < fear_capital) {
    // Ãâ°VÃâ°NEMENT DÃâ°CLENCHÃâ° !
    
    // 1. Chercher ÃÂ©vÃÂ©nements existants proches
    const nearby = findNearbyFearEvents(player);
    
    // 2. Utiliser ÃÂ©vÃÂ©nement existant ou crÃÂ©er nouveau
    const event = nearby.length > 0 
      ? selectCompatibleEvent(nearby, fear_capital)
      : generateNewFearEvent(player, fear_capital);
    
    // 3. Si ÃÂ©vÃÂ©nement persistant, stocker en BDD
    if (event.persistent) {
      storeFearEvent(event, player);
    }
    
    // 4. DÃÂ©clencher l'ÃÂ©vÃÂ©nement
    triggerFearEvent(player, event);
    
    // 5. Consommer jetons Peur
    system.fear_tokens -= event.fear_cost;
    
    return true;
  }
  
  return false;
}

// Recherche ÃÂ©vÃÂ©nements Peur proches
function findNearbyFearEvents(player) {
  return db.query(`
    SELECT * FROM fear_events 
    WHERE position_system_id = ? 
    AND status = 'active'
    AND expires_turn > ?
    ORDER BY created_turn ASC
  `, [player.system_id, current_turn]);
}

// GÃÂ©nÃÂ©ration nouvel ÃÂ©vÃÂ©nement selon capital Peur
function generateNewFearEvent(player, fear_capital) {
  let event_type, fear_cost, duration, persistent;
  
  if (fear_capital >= 20) {
    // Ãâ°vÃÂ©nement majeur
    event_type = selectRandom(['fleet', 'disaster', 'faction_intervention']);
    fear_cost = random(20, 30);
    duration = random(30, 100);
    persistent = true;
  } else if (fear_capital >= 11) {
    // Ãâ°vÃÂ©nement critique
    event_type = selectRandom(['ambush_coordinated', 'catastrophe', 'elite_enemy']);
    fear_cost = random(11, 20);
    duration = random(20, 50);
    persistent = true;
  } else if (fear_capital >= 6) {
    // Complication majeure
    event_type = selectRandom(['pirate_ambush', 'anomaly', 'system_failure']);
    fear_cost = random(6, 10);
    duration = random(10, 30);
    persistent = (event_type === 'pirate_ambush');
  } else {
    // Complication mineure
    event_type = selectRandom(['minor_failure', 'deviation', 'false_alert']);
    fear_cost = random(2, 5);
    duration = 0;
    persistent = false;
  }
  
  return {
    type: event_type,
    fear_cost: fear_cost,
    duration: duration,
    persistent: persistent,
    data: generateEventData(event_type, player)
  };
}

// Nettoyage ÃÂ©vÃÂ©nements expirÃÂ©s (chaque tour)
function cleanupExpiredFearEvents() {
  db.query(`
    UPDATE fear_events 
    SET status = 'expired' 
    WHERE expires_turn < ? AND status = 'active'
  `, [current_turn]);
}
```

### RÃÂ©fÃÂ©rences
- **Lunastars** : https://v2.lunastars.net
- **Empire Galactique (JDR)** : https://jeuderole.empiregalactique.site
- **Star Citizen** : https://robertsspaceindustries.com
- **GAIA Archive** : https://gea.esac.esa.int/archive/
- **NASA Exoplanet Archive** : https://exoplanetarchive.ipac.caltech.edu/

---

## ÄÅ¸ââ CHANGELOG & Ãâ°VOLUTIONS

### Version 0.1 (Document initial)
- Concepts core dÃÂ©finis
- Architecture modulaire multi-univers
- SystÃÂ¨me 2D12 Daggerheart proposÃÂ©
- Luna Industries nommÃÂ©e
- GÃÂ©nÃÂ©ration procÃÂ©durale complÃÂ¨te

### Ãâ¬ valider/modifier
- [ ] Balance ÃÂ©conomique (prix vaisseaux, ressources)
- [ ] SystÃÂ¨me de dÃÂ©s final (2D12 vs D20)
- [ ] Noms factions/guildes Archiluminique
- [ ] Contenu aliens (Phase future)
- [ ] RÃÂ¨gles PvP dÃÂ©taillÃÂ©es

---

**Document vivant - DerniÃÂ¨re mise ÃÂ  jour : 2025-10-30**

