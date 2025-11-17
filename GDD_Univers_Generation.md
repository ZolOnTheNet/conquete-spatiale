# ðŸŒŒ UNIVERS ET GÃ‰NÃ‰RATION PROCÃ‰DURALE
## Jeu de ConquÃªte Galactique

---

## âš ï¸ DISCLAIMER
DonnÃ©es issues du wiki - Algorithmes Ã  implÃ©menter et tester.

---

## ðŸŽ¯ Moteur GÃ©nÃ©rique Multi-Univers

### Objectif

CrÃ©er un **moteur assez complexe** pour supporter diffÃ©rents univers de science-fiction.

### Univers EnvisagÃ©s

**1. Monde de KÂ² (Zaib)**
- Zones se dÃ©placent
- SystÃ¨me magique

**2. Style Lunastars**
- Case par case
- DÃ©placement suivant un axe Ã  la fois

**3. Style Solar Empire**
- Passage d'un lieu Ã  un autre
- Vecteurs d'hyperespace (routes)

---

## ðŸ“ Principes GÃ©nÃ©raux

### ReprÃ©sentation de l'Univers

**SystÃ¨me 3D :**
- CoordonnÃ©es (x, y, z) en **rÃ©els simples**
- Partie fractionnelle **pas forcÃ©ment accessible** au joueur

**Point de dÃ©part :**
- Terre OU ville "Initiale"

**CoordonnÃ©es entiÃ¨res (x, y, z) :**
- ReprÃ©sentent une **zone**
- Peuvent contenir diffÃ©rents objets :
  - Bases
  - Filons (gisements)
  - Vaisseaux
  - Points of Value (PoV)

---

### Contenus

**Objets et Zones :**
- Un objet peut **pointer sur une zone**
- CrÃ©e d'autres espaces (sous-terrain, donjons, etc.)
- Objets relatifs au style de jeu
- CoordonnÃ©es plus prÃ©cises possibles pour un objet

---

### GÃ©nÃ©rateur

**GÃ©nÃ©ration :**
- **Au fur et Ã  mesure**
- Exception : coordonnÃ©es dÃ©cidÃ©es par MJ

**MJ peut :**
- DÃ©finir **grandes zones modÃ¨les**
- DÃ©terminent variations d'une zone
- Cubes de 10, 20, 30 zones de cÃ´tÃ©
- Univers peut devenir trÃ¨s grand rapidement

**DÃ©clenchement gÃ©nÃ©ration :**
- ArrivÃ©e dans coordonnÃ©e **frontiÃ¨re**
- OU arrivÃ©e dans coordonnÃ©e du "nouveau cube" modÃ¨le

---

## ðŸŒŸ Classification des Ã‰toiles

### Types Spectraux

| Classe | TempÃ©rature | Couleur | Puissance |
|--------|-------------|---------|-----------|
| **O** | > 25 000 K | Bleue | 150 - 200 |
| **B** | 10 000 - 25 000 K | Bleue-blanche | 100 - 140 |
| **A** | 7 500 - 10 000 K | Blanche | 80 - 100 |
| **F** | 6 000 - 7 500 K | Jaune-blanche | 60 - 80 |
| **G** | 5 000 - 6 000 K | Jaune | 40 - 60 |
| **K** | 3 500 - 5 000 K | Jaune-orange | 30 - 40 |
| **M** | < 3 500 K | Rouge | 20 - 30 |

### Courbe de Gauss (Distribution)

**Sur 20 Ã©lÃ©ments :**
```
Type    O   B   A   F   G   K   M
Indice  7   6   5   4   3   2   1
Nombre  1   1   3   4   6   3   2
```

**Utilisation :**
- Distribution naturelle des types d'Ã©toiles
- G (type solaire) = le plus frÃ©quent
- O/B (gÃ©antes bleues) = trÃ¨s rares
- M (naines rouges) = assez rares dans ce modÃ¨le

---

## â›ï¸ Gisements (AstÃ©roÃ¯des)

### Principe

**Par gisement :**
- **1 seule mine autonome** installable
- Exception : mines pirates possibles

### Rendement

**Facteurs :**
- Augmente progressivement vers **extÃ©rieur de l'univers** (10 Ã  50%)
- Plus facile de produire dÃ¨s **prÃ©sence humaine** (bases)

**Exploitation par MAME :**
- Ne produit **pas systÃ©matiquement** un cargo
- Tirage alÃ©atoire vis-Ã -vis du rendement

**Exemple :**
```
Rendement gisement : 30%
Roll 1D100 :
- â‰¤ 30 : Production de 1 cargo
- > 30 : Pas de production ce tour
```

---

## ðŸ”§ GÃ‰NÃ‰RATEUR SIMPLE D'UNIVERS

### Principe

Produire une zone de **N Ã— N Ã— N** avec un modÃ¨le en paramÃ¨tre.

Pour 10 â†’ 1000 cubes : suivre **courbe de Gauss** autour d'un soleil de puissance donnÃ©e.

---

### EntrÃ©e (ParamÃ¨tres du ModÃ¨le)

**DensitÃ©s :**
- **DensitÃ© de soleils** : % donnant idÃ©e du nombre de soleils
- **DensitÃ© d'astÃ©roÃ¯des**
- **DensitÃ© de mines**
- **DensitÃ© d'Ã©toiles miniÃ¨res**

**Puissances moyennes :**
- **Puissance moyenne des soleils** : Ã©chelle 20 Ã  140 (dÃ©faut 50)
- **Puissance moyenne des astÃ©roÃ¯des** : Ã©chelle 50 Ã  150 (dÃ©faut 70)

**Position :**
- **Position X, Y, Z la plus petite** du cube

**Autres (pas utilisÃ©s pour l'instant) :**
- Nombre de vents/courants
- Leurs longueurs moyennes

---

### MÃ©thode

#### 1. Calcul Nombre de Soleils

**Courbe de Gauss :**
- Option A : Utiliser tableau prÃ©-calculÃ© selon types
- Option B : Calculer tableau
  - Puissance moyenne = centre
  - Ã‰cart-type = 30 (exemple)

**RÃ©sultat :** Tableau (puissance de soleil Ã— nombre)

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

**Somme du tableau** = nombre total de soleils Ã  distribuer

---

#### 2. Calcul Pas de Progression

```
Pas = Nombre de secteurs / Nombre de soleils
```

**Pas AlÃ©atoire** (Ã©vite coÃ¯ncidences) :
```
Pas alÃ©atoire = Pas/2 + 1D(Pas)
```

---

#### 3. Parcours des Secteurs

**Principe :** MÃ©thode des "petits chevaux" (le dernier avance)

**Ã‰tapes :**
1. Tous commencent en **position 0**
2. Prendre premier type d'Ã©lÃ©ment en position minimum
3. Ajouter **Pas/2 + 1D(Pas)** Ã  sa position
4. Pour cette position : transformer secteur en secteur de type Ã©lÃ©ment
5. Mettre Ã  zÃ©ro champ de tri si nÃ©gatif
6. **RÃ©duire de 1** le nombre d'Ã©lÃ©ments du type choisi

**Choix du type (soleil) :**
- **Option A (alÃ©atoire)** : 1D(nombre de types)
- **Option B (mÃ©thodique)** : Plus grande â†’ plus petite puissance

---

#### 4. MÃªme Principe pour AstÃ©roÃ¯des

**CrÃ©er tableau :**
- Danger min = 50
- ReprÃ©sente nombre Ã— danger
- ConnaÃ®tre nombre total d'astÃ©roÃ¯des
- DÃ©finir pas â†’ "pas alÃ©atoire"
- Parcourir et distribuer

---

### Initialisation

**Premier parcours avec crÃ©ation :**
- Si secteur n'existe pas : **gÃ©nÃ©rer N Ã— N Ã— N secteurs**
- Utiliser un champ (ex: puissance solaire)
- Nombre alÃ©atoire **nÃ©gatif** : -1 Ã  -Nâ´
  - Formule : **-1D(Nâ´)**

**But :** Ordre de tirage alÃ©atoire pour parcours

---

### AprÃ¨s Traitement (Post-production)

**Traitements optionnels :**

1. **CrÃ©ation de vents** (gÃ©nÃ©ration peut Ãªtre longue)
2. **CrÃ©ation liens entre soleils** (mÃ©thode primaire)
3. **CrÃ©ation zones de forte puissance** (Ã©nergies)
4. **CrÃ©ation secteurs spÃ©ciaux**

**Nettoyage :**
- Secteurs avec puissance **nÃ©gative** = secteurs vides
- Peuvent Ãªtre supprimÃ©s selon style univers

**NumÃ©rotation :**
```
Exemple : s001x02n00003

001   = numÃ©ro de gÃ©nÃ©ration
02    = modÃ¨le type choisi
00003 = numÃ©ro dans parcours
```

Peut Ãªtre faite dans parcours principal si on ne numÃ©rotise pas espaces vides.

---

## ðŸ›¤ï¸ GÃ‰NÃ‰RATEUR Ã€ CHEMINS D'UNIVERS

### Principe

**CoordonnÃ©es secteur** pas d'une grande importance.

Entre objets notables â†’ il existe un **chemin**.

**CaractÃ©ristiques :**
- Chaque objet a un chemin
- Chemin empruntable **des deux cÃ´tÃ©s** (bidirectionnel)

### MÃ©thode

**Pour un secteur :**
1. SÃ©lectionner tous soleils **en dessous d'une distance**
2. CrÃ©er les arcs (chemins) entre eux

**RÃ©sultat :**
- Graphe de connexions
- Navigation par chemins prÃ©dÃ©finis
- Style "Solar Empire"

**Avantages :**
- Navigation simplifiÃ©e
- ContrÃ´le routes stratÃ©giques
- Goulots d'Ã©tranglement tactiques

**InconvÃ©nients :**
- Moins de libertÃ© exploration
- PrÃ©dictibilitÃ©

---

## ðŸŒ RÃ©fÃ©rences d'Univers

### ZAIB (Monde KÂ²)

**Contexte :**
- "Bulle des vents" = petit univers
- **Portiques** : permettent passage bulle Ã  bulle

**Moyens de transport :**
- Navires
- Vaisseaux
- **Insekts** : insectes trÃ¨s grands
  - Transport marchandises + hommes
  - Type maritime (vivent dans l'air)
  - Type aÃ©rien

**Nature :** Monde **magique**

**Application possible :**
- Zones mobiles
- Portails entre zones
- Magie comme systÃ¨me Ã©nergÃ©tique

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

**Objets dÃ©ployables :**
- MAMEs (Mines Automatiques)
- Antennes Hyper-relais
- Bars

#### Points Forts

- Missions
- Aide Ã©nergie/uniteks vaisseau Ã  vaisseau
- SystÃ¨me gestion d'Ã©nergie
- SystÃ¨me maladie
- RÃ©putation, XP, compÃ©tences amÃ©liorables

**Interaction PvE :**
- AstÃ©roÃ¯de Ã©vÃ©nementiel (tirage alÃ©atoire)
- TempÃªtes et vents
- CrÃ©ation mine
- CrÃ©ation station Ã©lÃ©mentaire (bar)

#### Points Faibles

**ProblÃ¨mes de conception :**

1. **OrientÃ© Environnement > Joueur**
   - Aide entre joueurs limitÃ©e
   - Uniquement dons uniteks/Ã©nergie

2. **SystÃ¨me missions favorise entropie**
   - Lien Terre â†” base Vulcania
   - Toutes ressources autour Vulcania
   - Autres exploitations non compÃ©titives

**LeÃ§ons pour notre jeu :**
- âœ… Favoriser interaction joueurs
- âœ… Ã‰conomie dÃ©centralisÃ©e
- âœ… Plusieurs hubs importants

---

### SINS OF A SOLAR EMPIRE

**Type :** Jeu temps rÃ©el de stratÃ©gie

**Principe :**
- 3 races
- ConquÃªte progressive univers Ã  dÃ©couvrir
- Espaces de jeu = systÃ¨mes solaires
- **ReliÃ©s par vecteur d'hyperespace**
- Vecteur = route entre deux points

**Application :**
- GÃ©nÃ©rateur Ã  chemins
- Routes stratÃ©giques
- ContrÃ´le territorial

---

## ðŸŽ² Variantes de GÃ©nÃ©ration

### GÃ©nÃ©ration Hybride

**Combiner les deux gÃ©nÃ©rateurs :**
1. GÃ©nÃ©rateur simple pour crÃ©er zones
2. GÃ©nÃ©rateur Ã  chemins pour crÃ©er routes principales
3. Navigation libre OU par chemins selon contexte

**Avantages :**
- FlexibilitÃ©
- Routes connues ET exploration libre
- StratÃ©gie + dÃ©couverte

---

### Zones SpÃ©ciales

**Types possibles :**
- NÃ©buleuses (visibilitÃ© rÃ©duite)
- Champs astÃ©roÃ¯des denses
- Anomalies gravitationnelles
- Trous noirs
- Zones de tempÃªte
- Ruines anciennes
- Portails mystÃ©rieux

**GÃ©nÃ©ration :**
- Par MJ (placÃ©es manuellement)
- OU procÃ©dural (% de chance)

---

## ðŸ—ºï¸ Secteurs et CoordonnÃ©es

### SystÃ¨me de CoordonnÃ©es

**Format :** (x, y, z)
- Entiers = zones principales
- DÃ©cimales = positions prÃ©cises dans zone

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

**Base de donnÃ©es :**
- Stocker uniquement zones **gÃ©nÃ©rÃ©es/visitÃ©es**
- Zones vides supprimÃ©es (optimisation)
- GÃ©nÃ©ration Ã  la demande

**Tables :**
```sql
secteurs (
    id,
    coord_x, coord_y, coord_z,
    type (vide/soleil/astÃ©roÃ¯de/etc),
    puissance,
    danger,
    dÃ©couvert_par,
    date_gÃ©nÃ©ration
)
```

---

## ðŸ’¡ IdÃ©es Futures

**Ã‰volutions possibles :**
- Univers dynamique (Ã©toiles vieillissent)
- Ã‰vÃ©nements cosmiques (supernovae)
- Migration civilisations
- Expansion/contraction zones contrÃ´lÃ©es
- PhÃ©nomÃ¨nes temporels
- Dimensions parallÃ¨les

---

**Document vivant - DerniÃ¨re mise Ã  jour : 2025-11-01**
