# GDD — Vaisseaux : Modules, Systèmes & Constructeurs
## Conquête Spatiale — Document de référence

> Document vivant. Complémentaire à `GDD_Vaisseaux_Complet.md`.
> Dernière mise à jour : 2026-05-15

---

## 1. FABRICANTS ET PHILOSOPHIE

Le marché est dominé par quelques grands groupes industriels. **Un même fabricant peut couvrir plusieurs catégories de composants**, avec des lignes de produits distinctes ciblant des marchés différents. Chaque ligne dispose de versions numérotées (Mk I → Mk III) qui représentent des montées en efficacité.

Le mixage de marques est libre — aucun bonus ou malus n'est lié à la cohérence de marque.

### 1.1 Constructeurs de coques (châssis complets)

| Constructeur                           | Siège                | Série principale   | Réputation                          |
| -------------------------------------- | -------------------- | ------------------ | ----------------------------------- |
| **Helix Dynamics**                     | Angeles              | A (polyvalent)     | Fiable, accessible, le plus vendu   |
| **Meridian Cargo Group** *(MCG)*       | Espérance            | M (marchand)       | Solide, lent, très grande capacité  |
| **Aether Exploration Systems** *(AES)* | New-San Francisco    | E (explorateur)    | Haute technologie, prix élevé       |
| **Vortex Defense Industries** *(VDI)*  | Inconnue (militaire) | F (forces)         | Militaire, exportation restreinte   |
| **Hullworks Cooperative**              | Mobile (flottille)   | Occasion / hybride | Remis à neuf, bricolé mais pas cher |

### 1.2 Fabricants de composants — vue d'ensemble

| Fabricant                  | Philosophie                                          | Catégories couvertes                                         |
| -------------------------- | ---------------------------------------------------- | ------------------------------------------------------------ |
| **Helix Dynamics**         | Fiabilité à prix raisonnable, pensé pour la Série A  | MN léger, Type B budget, Armement léger                      |
| **MCG**                    | Constructeur Série M, quelques optimisations cargo   | Modules soute                                                |
| **AES**                    | Technologie d'avant-garde, coûte cher                | HE haute perf, Type B dense, Boucliers exploration           |
| **VDI**                    | Militaire pur, hautes performances, export restreint | MN militaire, HE militaire, Boucliers combat, Armement lourd |
| **Hullworks Coop**         | Recyclé, économique, variable                        | Réservoirs budget, Boucliers occasion                        |
| **Kronos Engines**         | Généraliste accessible, présent partout              | MN standard, HE standard, Réservoirs légers                  |
| **Ashvelt & Partners**     | Propulsion à combustible, robustesse commerciale     | MN lourd, HE commercial, Réservoirs tous carburants          |
| **Graviton Power Systems** | Spécialiste énergie Type B, extracteurs              | Type B panneaux, voiles, matière noire                       |
| **Stratos Drive Corp**     | HE longue portée, précision astrogation              | HE exploration                                               |
| **Titan Propulsion Works** | Motorisation lourde, gros vaisseaux                  | MN lourd, HE lourd, Type B extracteurs                       |
| **OmniShield**             | Défense et boucliers (spécialiste absolu)            | Boucliers toutes gammes                                      |
| **Nexus Arms**             | Armement de précision (spécialiste)                  | Lasers, missiles                                             |
| **StellarTech**            | Informatique, démons, intégration logicielle         | SI, modules logiciels, boucliers connectés                   |
| **DataCore Systems**       | Informatique avancée, bases de données               | SI avancé, démons navigation/combat                          |
| **BioSurvival Corp**       | Survie équipage, médical                             | Modules vie, démons survie                                   |
| **VoidCraft Mods**         | Modifications illégales, sans garantie               | Armement modifié, démons illégaux                            |

---

## 2. MOTEURS CONVENTIONNELS (MN)

Le moteur conventionnel gère les déplacements intra-système : approche, arrimage, manœuvres.

**Formule de consommation :**
```
Consommation MN = Init_MN + (Masse × Distance / Vitesse_MN)
PA MN = Consommation / Vitesse × Coef_PAMN / 100
```
> `Coef PA` > 100 = moins efficace (plus de PA par unité de distance).

---

### 2.1 Kronos Engines — généraliste budget/standard

*Ligne de référence de l'espace civil. On les trouve partout, ils ne tombent jamais vraiment en panne.*

#### Ligne "Compact" — A-0, petits vaisseaux

| Version           | V. MN | Coef PA | Masse | Prix     |
| ----------------- | ----- | ------- | ----- | -------- |
| **Compact Mk I**  | 0.5   | 125     | 200 t | 2 000 cr |
| **Compact Mk II** | 0.8   | 120     | 210 t | 3 500 cr |

#### Ligne "Standard" — A-1, M-1 entrée

| Version             | V. MN | Coef PA | Masse | Prix      |
| ---------------------| -------| ---------| -------| -----------|
| **Standard Mk I**   | 1.0   | 110     | 500 t | 5 000 cr  |
| **Standard Mk II**  | 1.5   | 105     | 520 t | 9 000 cr  |
| **Standard Mk III** | 2.0   | 100     | 560 t | 15 000 cr |

---

### 2.2 Ashvelt & Partners — propulsion commerciale haute performance

*Spécialiste du fret lourd. Leurs moteurs tiennent à la distance, pas à la vitesse.*

#### Ligne "PowerDrive" — M-1 standard

| Version              | V. MN | Coef PA | Masse | Prix      |
| -------------------- | ----- | ------- | ----- | --------- |
| **PowerDrive Mk I**  | 2.5   | 100     | 800 t | 12 000 cr |
| **PowerDrive Mk II** | 3.5   | 95      | 900 t | 22 000 cr |

#### Ligne "HeavyFreight" — M-1 lourd et cargo

| Version                | V. MN | Coef PA | Masse   | Prix      |
| ---------------------- | ----- | ------- | ------- | --------- |
| **HeavyFreight Mk I**  | 3.0   | 98      | 1 200 t | 20 000 cr |
| **HeavyFreight Mk II** | 4.0   | 93      | 1 400 t | 35 000 cr |

---

### 2.3 Helix Dynamics — propulsion légère Série A

*Conçus en interne pour équiper leurs propres coques. Légers, efficaces dans la gamme A, peu adaptés aux gros vaisseaux.*

#### Ligne "Starter" — A-0, entrée de gamme

| Version          | V. MN | Coef PA | Masse | Prix     |
| ---------------- | ----- | ------- | ----- | -------- |
| **Starter Mk I** | 0.7   | 118     | 250 t | 2 800 cr |

#### Ligne "Runner" — A-1 standard et performance

| Version          | V. MN | Coef PA | Masse | Prix      |
| ---------------- | ----- | ------- | ----- | --------- |
| **Runner Mk I**  | 2.0   | 105     | 700 t | 10 000 cr |
| **Runner Mk II** | 3.0   | 98      | 780 t | 18 000 cr |

#### Ligne "Sprint" — A-1 vitesse maximale

| Version          | V. MN | Coef PA | Masse   | Prix      |
| ---------------- | ----- | ------- | ------- | --------- |
| **Sprint Mk I**  | 5.0   | 88      | 1 200 t | 28 000 cr |
| **Sprint Mk II** | 6.0   | 84      | 1 300 t | 45 000 cr |

---

### 2.4 VDI — propulsion militaire

*Usage réglementé. Pas vendus dans les stations civiles. Performances brutes incomparables.*

#### Ligne "TactDrive" — F-series standard

| Version             | V. MN | Coef PA | Masse   | Prix      |
| ------------------- | ----- | ------- | ------- | --------- |
| **TactDrive Mk I**  | 5.5   | 86      | 1 400 t | 55 000 cr |
| **TactDrive Mk II** | 7.5   | 80      | 1 600 t | 90 000 cr |

#### Ligne "StrikePulse" — F-series élite

| Version              | V. MN | Coef PA | Masse   | Prix       |
| -------------------- | ----- | ------- | ------- | ---------- |
| **StrikePulse Mk I** | 9.0   | 75      | 2 000 t | 130 000 cr |

---

### 2.5 Titan Propulsion Works — motorisation lourde

*Frégates, destroyers, grands cargos. Conçu pour des masses de plusieurs milliers de tonnes.*

#### Ligne "FreightMover" — M-1 lourd et très lourd

| Version                | V. MN | Coef PA | Masse   | Prix      |
| ---------------------- | ----- | ------- | ------- | --------- |
| **FreightMover Mk I**  | 1.5   | 112     | 2 000 t | 18 000 cr |
| **FreightMover Mk II** | 2.5   | 106     | 2 200 t | 38 000 cr |

#### Ligne "Capital" — frégates et destroyers

| Version          | V. MN | Coef PA | Masse   | Prix       |
| ---------------- | ----- | ------- | ------- | ---------- |
| **Capital Mk I** | 4.0   | 90      | 5 000 t | 200 000 cr |

---

## 3. MOTEURS HYPER-ESPACE (HE)

Le moteur HE propulse le vaisseau en hyper-espace. Difficile à arrêter une fois lancé. Très énergivore.

**Formule de consommation :**
```
Consommation HE = Init_HE + (Coef_HE/100) × (Masse / Vitesse_HE) × Distance
PA HE = 1 + Coef_PAHE × Distance
```

---

### 3.1 Kronos Engines — HE budget/standard

*Premier moteur HE pour la plupart des pilotes. Fiable, pas rapide.*

#### Ligne "HE-Micro" — A-0, drones, vaisseaux parasites

| Version            | V. HE | Init HE | Coef HE | Coef PAHE | Prix     |
| ------------------ | ----- | ------- | ------- | --------- | -------- |
| **HE-Micro Mk I**  | 0.5   | 260     | 68      | 26        | 4 000 cr |
| **HE-Micro Mk II** | 0.7   | 248     | 64      | 24        | 6 500 cr |

#### Ligne "HE-Standard" — A-1 courant

| Version                | V. HE | Init HE | Coef HE | Coef PAHE | Prix      |
| ---------------------- | ----- | ------- | ------- | --------- | --------- |
| **HE-Standard Mk I**   | 1.0   | 210     | 52      | 21        | 8 000 cr  |
| **HE-Standard Mk II**  | 1.3   | 202     | 50      | 20        | 13 000 cr |
| **HE-Standard Mk III** | 1.6   | 195     | 48      | 19        | 20 000 cr |

---

### 3.2 Stratos Drive Corp — HE longue portée, exploration

*Optimisés pour minimiser l'erreur de saut et réduire la consommation sur longue distance. La référence pour les explorateurs.*

#### Ligne "DeepReach" — E-series entrée

| Version             | V. HE | Init HE | Coef HE | Coef PAHE | Prix      |
| ------------------- | ----- | ------- | ------- | --------- | --------- |
| **DeepReach Mk I**  | 2.0   | 170     | 46      | 19        | 28 000 cr |
| **DeepReach Mk II** | 2.5   | 162     | 43      | 18        | 40 000 cr |

#### Ligne "Voyager" — E-series avancé

| Version           | V. HE | Init HE | Coef HE | Coef PAHE | Prix      |
| ----------------- | ----- | ------- | ------- | --------- | --------- |
| **Voyager Mk I**  | 3.0   | 155     | 41      | 17        | 58 000 cr |
| **Voyager Mk II** | 3.5   | 148     | 39      | 16        | 80 000 cr |

---

### 3.3 Ashvelt & Partners — HE commercial fiable

*Moteurs pensés pour la rentabilité sur les routes de fret régulières. Pas spectaculaires, mais jamais en panne.*

#### Ligne "HyperFuel" — M-1, commerce interstellaire

| Version              | V. HE | Init HE | Coef HE | Coef PAHE | Prix      |
| -------------------- | ----- | ------- | ------- | --------- | --------- |
| **HyperFuel Mk I**   | 1.5   | 195     | 50      | 22        | 16 000 cr |
| **HyperFuel Mk II**  | 2.0   | 186     | 47      | 20        | 28 000 cr |
| **HyperFuel Mk III** | 2.5   | 178     | 44      | 19        | 45 000 cr |

---

### 3.4 AES (Aether Exploration Systems) — haute technologie

*L'excellence en HE pour les explorateurs de haut vol. Coût d'entrée élevé, performances sans égal dans la gamme civile.*

#### Ligne "Horizon" — E-series

| Version           | V. HE | Init HE | Coef HE | Coef PAHE | Prix      |
| ----------------- | ----- | ------- | ------- | --------- | --------- |
| **Horizon Mk I**  | 3.0   | 156     | 42      | 18        | 55 000 cr |
| **Horizon Mk II** | 3.5   | 150     | 40      | 17        | 75 000 cr |

#### Ligne "Apex" — E-series élite

| Version       | V. HE | Init HE | Coef HE | Coef PAHE | Prix       |
| ------------- | ----- | ------- | ------- | --------- | ---------- |
| **Apex Mk I** | 4.0   | 145     | 38      | 16        | 110 000 cr |

---

### 3.5 VDI — HE militaire

*Classifiés. Disponibles uniquement dans les stations militaires ou sur le marché gris.*

#### Ligne "WarJump" — F-series standard

| Version           | V. HE | Init HE | Coef HE | Coef PAHE | Prix       |
| ----------------- | ----- | ------- | ------- | --------- | ---------- |
| **WarJump Mk I**  | 4.5   | 140     | 40      | 16        | 85 000 cr  |
| **WarJump Mk II** | 5.5   | 130     | 37      | 14        | 130 000 cr |

#### Ligne "Phantom" — F-series élite, rare

| Version          | V. HE | Init HE | Coef HE | Coef PAHE | Prix       |
| ---------------- | ----- | ------- | ------- | --------- | ---------- |
| **Phantom Mk I** | 7.5   | 120     | 32      | 12        | 200 000 cr |

---

## 4. SYSTÈMES TYPE B — RECHARGEMENT ÉNERGÉTIQUE

Pour les vaisseaux à énergie extraite. Remplace le réservoir de combustible.

> **État d'implémentation :**
> - ✅ **Micro-panneaux solaires** — implémenté
> - 🔲 **Voiles solaires** — prévu
> - 🔲 **Extracteurs de matière noire** — prévu

---

### 4.1 Micro-panneaux solaires ✅

Le vaisseau doit être **immobile** pendant la recharge. Puissance extraite proportionnelle à `Puissance_étoile`.

#### Helix Dynamics — "SunLeaf" — budget Série A

| Version           | Puissance/PA   | Masse | Prix     | Cible                |
| ----------------- | -------------- | ----- | -------- | -------------------- |
| **SunLeaf Mk I**  | P_étoile × 0.8 | 150 t | 2 500 cr | A-0, entrée de gamme |
| **SunLeaf Mk II** | P_étoile × 1.0 | 180 t | 4 200 cr | A-1                  |

#### Graviton Power Systems — "GVT-Solar" — standard à haute perf

| Version              | Puissance/PA   | Masse | Prix      | Cible        |
| -------------------- | -------------- | ----- | --------- | ------------ |
| **GVT-Solar Mk I**   | P_étoile × 1.0 | 200 t | 4 000 cr  | A-1 standard |
| **GVT-Solar Mk II**  | P_étoile × 1.5 | 300 t | 9 000 cr  | M-1          |
| **GVT-Solar Mk III** | P_étoile × 2.0 | 400 t | 18 000 cr | E-series     |

#### StellarTech — "SmartPanel" — panneaux à gestion informatique

*Bonus : déploiement automatisé via démon Propulsion & Pilotage, optimisation de l'angle en temps réel.*

| Version              | Puissance/PA   | Masse | Prix      | Cible                 |
| -------------------- | -------------- | ----- | --------- | --------------------- |
| **SmartPanel Mk I**  | P_étoile × 1.2 | 220 t | 6 000 cr  | A-1 avec SI standard  |
| **SmartPanel Mk II** | P_étoile × 1.6 | 260 t | 11 000 cr | A-1+, M-1 avec SI pro |

#### AES — "SolarDense" — haute densité

| Version              | Puissance/PA   | Masse | Prix      | Cible           |
| -------------------- | -------------- | ----- | --------- | --------------- |
| **SolarDense Mk I**  | P_étoile × 2.0 | 350 t | 16 000 cr | E-series        |
| **SolarDense Mk II** | P_étoile × 2.5 | 450 t | 24 000 cr | E-series avancé |

---

### 4.2 Voiles solaires 🔲 *(prévu)*

Déplacement possible pendant la recharge. Masse importante, mâts déployables.

#### Graviton Power Systems — "GVT-Voile"

| Version             | Puissance/PA   | Masse   | Prix      | Note   |
| ------------------- | -------------- | ------- | --------- | ------ |
| **GVT-Voile Mk I**  | P_étoile × 1.2 | 800 t   | 18 000 cr | 2 mâts |
| **GVT-Voile Mk II** | P_étoile × 2.0 | 1 400 t | 42 000 cr | 4 mâts |

#### AES — "ArgèteSail" — voile en argète-stellaire

*Spectre élargi, efficace même autour des naines rouges.*

| Version             | Puissance/PA   | Masse   | Prix      | Note                        |
| ------------------- | -------------- | ------- | --------- | --------------------------- |
| **ArgèteSail Mk I** | P_étoile × 3.0 | 2 000 t | 90 000 cr | Capteurs spectraux intégrés |

---

### 4.3 Extracteurs de matière noire 🔲 *(prévu)*

Indépendants de la distance à l'étoile. Réservés aux gros vaisseaux.

#### Graviton Power Systems — "GVT-DarkMatter"

| Version         | Puissance extraite           | Masse   | Équipage min. | Prix       |
| --------------- | ---------------------------- | ------- | ------------- | ---------- |
| **GVT-DM Mk I** | P_étoile × 2.0 (indépendant) | 3 000 t | 3             | 150 000 cr |

#### Titan Propulsion Works — "Void-Anchor"

| Version              | Puissance extraite           | Masse   | Équipage min. | Prix       |
| -------------------- | ---------------------------- | ------- | ------------- | ---------- |
| **Void-Anchor Mk I** | P_étoile × 3.5 (indépendant) | 8 000 t | 6             | 400 000 cr |

---

## 5. RÉSERVOIRS TYPE A — COMBUSTIBLE

> ⚠️ **Non implémenté.** Le mode Type A (combustible) n'est pas encore disponible en jeu. Cette section est purement prévisionnelle.

L'efficacité (`eff`) multiplie la quantité de carburant utilisée : 0.80 = 20% de moins consommé qu'un réservoir brut.

---

### Ashvelt & Partners — "AVP" — tous carburants, gamme pro

*La référence du marché. Fiches techniques précises, garantie 5 ans.*

#### Ligne "AVP-U" — uranium

| Version         | Capacité | Efficacité | Masse | Prix      |
| --------------- | -------- | ---------- | ----- | --------- |
| **AVP-U Mk I**  | 200 u    | 0.75       | 400 t | 6 000 cr  |
| **AVP-U Mk II** | 500 u    | 0.78       | 900 t | 14 000 cr |

#### Ligne "AVP-P" — plasma (H₂)

| Version         | Capacité | Efficacité | Masse | Prix      |
| --------------- | -------- | ---------- | ----- | --------- |
| **AVP-P Mk I**  | 300 u    | 0.80       | 300 t | 9 000 cr  |
| **AVP-P Mk II** | 600 u    | 0.82       | 550 t | 18 000 cr |

#### Ligne "AVP-T" — tyberium *(rare)*

*Le tyberium est le carburant le plus efficace. Rare — disponible dans les systèmes à anneaux planétaires.*

| Version         | Capacité | Efficacité | Masse | Prix      |
| --------------- | -------- | ---------- | ----- | --------- |
| **AVP-T Mk I**  | 250 u    | 0.90       | 250 t | 15 000 cr |
| **AVP-T Mk II** | 500 u    | 0.90       | 450 t | 28 000 cr |

---

### Kronos Engines — "KronosTank" — uranium/plasma budget

| Version              | Capacité | Carburant         | Efficacité | Masse | Prix     |
| -------------------- | -------- | ----------------- | ---------- | ----- | -------- |
| **KronosTank Mk I**  | 150 u    | Uranium ou Plasma | 0.70       | 350 t | 4 500 cr |
| **KronosTank Mk II** | 300 u    | Uranium ou Plasma | 0.72       | 600 t | 8 000 cr |

---

### Hullworks Cooperative — "RenFuel Flex" — recyclé, multi-carburant

*Fiabilité variable. Ce qu'on trouve dans une épave, remis en état.*

| Version               | Capacité | Carburant         | Efficacité | Masse | Prix     |
| --------------------- | -------- | ----------------- | ---------- | ----- | -------- |
| **RenFuel Flex Mk I** | 200 u    | Plasma ou Uranium | 0.72       | 350 t | 5 000 cr |

---

### MCG — "MegaTank" — grande capacité marchande

| Version            | Capacité | Carburant | Efficacité | Masse   | Prix      |
| ------------------ | -------- | --------- | ---------- | ------- | --------- |
| **MegaTank Mk I**  | 800 u    | Uranium   | 0.74       | 1 500 t | 20 000 cr |
| **MegaTank Mk II** | 1 200 u  | Uranium   | 0.76       | 2 000 t | 35 000 cr |

---

### Titan Propulsion Works — "Fortress Tank" — blindé haute capacité

| Version                | Capacité | Carburant | Efficacité | Masse | Prix      |
| ---------------------- | -------- | --------- | ---------- | ----- | --------- |
| **Fortress Tank Mk I** | 400 u    | Tyberium  | 0.88       | 600 t | 35 000 cr |

---

## 6. BOUCLIERS

Chaque bouclier appartient à un type : **énergie** (régénère, faible vs EMP), **coque** (blindage physique, pas de regen, très résistant EMP), **régénératif** (regen élevée, points faibles), **adaptatif** (toutes résistances, très cher).

Champs de stats : `points_max` / `regeneration` / `resistance` globale / `vs_laser/canon/missile/plasma/emp` (bonus ou malus %) / `energie_maintien`.

---

### OmniShield — spécialiste boucliers, toutes gammes

#### Ligne "Standard" — énergie, généraliste

| Produit             | Code DB        | Type    | pts max | Regen | Res. | Maintien | Prix     |
| ------------------- | -------------- | ------- | ------- | ----- | ---- | -------- | -------- |
| **Standard Mk I**   | `BOUCLIER_MK1` | énergie | 50      | 3     | 5    | 5 UE     | 800 cr   |
| **Standard Mk II**  | `BOUCLIER_MK2` | énergie | 100     | 5     | 10   | 10 UE    | 2 000 cr |
| **Standard Mk III** | `BOUCLIER_MK3` | énergie | 200     | 8     | 15   | 20 UE    | 6 000 cr |

#### Ligne "Régén" — régénératif, récupération rapide

| Produit        | Code DB     | Type        | pts max | Regen | Res. | Maintien | Prix     |
| -------------- | ----------- | ----------- | ------- | ----- | ---- | -------- | -------- |
| **Régén Mk I** | `REGEN_MK1` | régénératif | 60      | 10    | 5    | 15 UE    | 4 000 cr |

#### Ligne "Adapt" — adaptatif haut de gamme

| Produit        | Code DB     | Type      | pts max | Regen | Res. | Maintien | Prix      |
| -------------- | ----------- | --------- | ------- | ----- | ---- | -------- | --------- |
| **Adapt Mk I** | `ADAPT_MK1` | adaptatif | 120     | 5     | 10   | 25 UE    | 15 000 cr |

---

### VDI — blindages de combat

*Non vendus au civil dans la plupart des stations. Marché militaire ou gris.*. Le blindage est dépéndant de la masse du vaisseau, plus un vaisseau est gros, plus le cout en blindage est important, par contre il ne prend qu'1 SCU de cargo.

#### Ligne "CombatArmor" — blindage physique

| Produit               | Code DB        | Type  | pts max | Regen | Res. | Maintien | Prix     |
| --------------------- | -------------- | ----- | ------- | ----- | ---- | -------- | -------- |
| **CombatArmor Mk I**  | `BLINDAGE_MK1` | coque | 80      | 0     | 15   | 0        | 1 500 cr |
| **CombatArmor Mk II** | `BLINDAGE_MK2` | coque | 150     | 0     | 25   | 0        | 5 000 cr |

*Nota : les blindages de coque n'utilisent pas d'énergie mais ne régénèrent pas.*

---

### AES — boucliers exploration

*Compromis légèreté/protection pensé pour les vaisseaux qui sortent souvent des routes connues.*

> Prévus. Produits à ajouter en seeder (lignes `ExploShield Mk I/II`).

---

### StellarTech — boucliers connectés à l'informatique

*Le démon "Gestion Bouclier Avancée" booste ces boucliers : régénération accélérée et résistances sélectives selon le type d'arme adverse.*

> Prévus. Produits à ajouter en seeder (lignes `SmartShield Mk I/II`).

---

### Hullworks Cooperative — boucliers d'occasion

*Récupérés, remis en état. Stats inférieures, prix imbattable. Fiabilité variable.*

> Prévus. Produit à ajouter en seeder (ligne `HullShield Mk I`).

---

## 7. ARMEMENT EMBARQUÉ

Trois emplacements d'arme maximum par vaisseau (slots `arme_1_id`, `arme_2_id`, `arme_3_id`).

**Types en jeu :** laser (rapide, précis), canon (lent, puissant), missile (portée max, guidé), plasma (courte portée, dégâts feu), EMP (désactivation systèmes).

---

### Nexus Arms — armement de précision

*Référence civile pour les armes laser et les lanceurs de missiles.*

| Produit             | Code DB       | Type    | Dégâts | Portée | Précision | Énergie/tir | Prix     |
| ------------------- | ------------- | ------- | ------ | ------ | --------- | ----------- | -------- |
| **NX-Laser Mk I**   | `LASER_MK1`   | laser   | 5–10   | 150    | 85%       | 3 UE        | 500 cr   |
| **NX-Laser Mk II**  | `LASER_MK2`   | laser   | 8–15   | 180    | 80%       | 5 UE        | 1 200 cr |
| **NX-Missile Mk I** | `MISSILE_MK1` | missile | 30–50  | 250    | 60%       | 15 UE       | 5 000 cr |

---

### VDI — armement lourd militaire

*Canons cinétiques, torpilles, EMP tactique. Export réglementé.*

| Produit                 | Code DB         | Type    | Dégâts | Portée | Précision | Énergie/tir | Prix      |
| ----------------------- | --------------- | ------- | ------ | ------ | --------- | ----------- | --------- |
| **VDI-Canon Mk I**      | `CANON_MK1`     | canon   | 15–25  | 120    | 70%       | 8 UE        | 1 500 cr  |
| **VDI-Canon Mk II**     | `CANON_MK2`     | canon   | 25–40  | 140    | 65%       | 12 UE       | 4 000 cr  |
| **VDI-Canon Siège**     | `CANON_SIEGE`   | canon   | 50–80  | 100    | 55%       | 25 UE       | 12 000 cr |
| **VDI-Torpille Lourde** | `MISSILE_LOURD` | missile | 60–100 | 300    | 50%       | 30 UE       | 10 000 cr |
| **VDI-EMP Mk I**        | `EMP_MK1`       | EMP     | 5–10   | 100    | 90%       | 20 UE       | 8 000 cr  |

---

### AES — armement scientifique

*Projecteur plasma haute densité, développé pour neutraliser les systèmes hostiles en zone d'exploration.*

| Produit             | Code DB      | Type   | Dégâts | Portée | Précision | Énergie/tir | Prix     |
| ------------------- | ------------ | ------ | ------ | ------ | --------- | ----------- | -------- |
| **AES-Plasma Mk I** | `PLASMA_MK1` | plasma | 10–20  | 80     | 75%       | 10 UE       | 4 500 cr |

---

### Helix Dynamics — armement léger Série A

*Intégré d'origine sur certaines coques A. Peu puissant, économique en énergie.*

| Produit               | Code DB       | Type  | Dégâts | Portée | Précision | Énergie/tir | Prix     |
| --------------------- | ------------- | ----- | ------ | ------ | --------- | ----------- | -------- |
| **Helix-Laser Lourd** | `LASER_LOURD` | laser | 15–25  | 200    | 75%       | 8 UE        | 3 000 cr |

---

### VoidCraft Mods — armement modifié *(marché noir)*

*Pas de garantie. Vendus uniquement dans certaines cantinas. Résultats imprévisibles — peut exploser.*

> Prévus. Produits à ajouter en seeder (variantes modifiées des types existants).

---

## 8. MODULES EN SOUTE (Emplacements convertis)

Un vaisseau peut sacrifier des baies de cargaison pour installer des modules. Chaque module occupe **1 ou plusieurs soutes**. Aucun module ne peut occuper les 12 emplacements fonctionnels (qui sont fixes par la coque).

### 8.1 Transport de personnel

| Module                       | Soutes | Capacité             | Durabilité               | Prix     |
| ---------------------------- | ------ | -------------------- | ------------------------ | -------- |
| **Kit Passager Succinct**    | 1      | 10 personnes / soute | 1-2 utilisations         | 500 cr   |
| **Module Passager Standard** | 1      | 5 personnes / soute  | Permanent                | 3 000 cr |
| **Cabines 1ère Classe**      | 1      | 3 personnes / soute  | Permanent                | 9 000 cr |
| **Transports de Troupe**     | 1      | 20 personnes / soute | Permanent, inconfortable | 2 000 cr |

### 8.2 Science & Exploration

| Module                    | Soutes | Effet                                            | Prix      |
| ------------------------- | ------ | ------------------------------------------------ | --------- |
| **Laboratoire d'Analyse** | 1      | Bonus puissance scan +20, analyse ressources     | 18 000 cr |
| **Module Sondage**        | 1      | Permet sondage planétaire, +portée scan 1 AL     | 25 000 cr |
| **Télescope Long-Portée** | 2      | +portée scan 3 AL, nécessite déploiement (arrêt) | 40 000 cr |

### 8.3 Combat & Défense

| Module                  | Soutes | Effet                                                | Prix      |
| ----------------------- | ------ | ---------------------------------------------------- | --------- |
| **Tourelle Auxiliaire** | 1      | +1 emplacement d'arme (fixe, limité à gauche/droite) | 22 000 cr |
| **Bouclier Secondaire** | 1      | Bouclier d'appoint 100 pts, non régénérant           | 12 000 cr |
| **Module Leurres**      | 1      | Peut déployer 3 leurres par combat                   | 8 000 cr  |
| **EMP Tactique**        | 1      | Désactive systèmes ennemis, usage unique / escale    | 15 000 cr |
| **Module Furtivité**    | 2      | Réduit détectabilité -50% au scan adverse            | 80 000 cr |

### 8.4 Logistique & Maintenance

| Module                        | Soutes | Effet                                          | Prix                   |
| ----------------------------- | ------ | ---------------------------------------------- | ---------------------- |
| **Atelier de Bord**           | 1      | +score entretien en vol, répare pannes faibles | 14 000 cr              |
| **Module Médical**            | 1      | Soins équipage, +récup PA personnage (+1 PA/h) | 10 000 cr              |
| **Mini-Forge**                | 1      | Transformation matières 1er niveau en vol      | 30 000 cr              |
| **Réservoir Carburant Extra** | 1      | +200 u du combustible actuel                   | 3 000 cr (+ cargaison) |
| **Hangar Parasite**           | 2      | Peut embarquer et transporter un vaisseau A-0  | 45 000 cr              |

### 8.5 Commerce & Administration

| Module                       | Soutes | Effet                                                    | Prix      |
| ---------------------------- | ------ | -------------------------------------------------------- | --------- |
| **Salle de Marché**          | 1      | Permet transactions marchandes à distance (-5% prix)     | 18 000 cr |
| **Module de Représentation** | 1      | Bonus influence diplomatique, accès à certaines stations | 25 000 cr |

---

## 9. SYSTÈME INFORMATIQUE (SI)

Le système informatique est le cerveau du vaisseau. Il détermine le nombre de **places de service** disponibles pour les démons logiciels.

### 9.1 Modules informatiques (emplacement n°5 des 12 slots)

| Modèle              | Marque      | Places de service | Prix      | Note                                        |
| ------------------- | ----------- | ----------------- | --------- | ------------------------------------------- |
| **SI-3 Nano**       | StellarTech | 3                 | 2 000 cr  | A-0, drones, vaisseaux parasites            |
| **SI-5 Standard**   | StellarTech | 5                 | 6 000 cr  | Défaut A-1, la plupart des petits vaisseaux |
| **SI-7 Pro**        | StellarTech | 7                 | 16 000 cr | M-1, E-series entrée                        |
| **SI-10 Master**    | StellarTech | 10                | 38 000 cr | E-series avancé, F-series                   |
| **SI-10 Tactical**  | DataCore    | 10                | 55 000 cr | Optimisé combat, bonus visée                |
| **SI-8 Deep Space** | AES         | 8                 | 28 000 cr | Optimisé exploration, bonus atlas           |

> Un module informatique détérioré peut perdre des places de service (panne système).
> On ne peut pas avoir plus de 10 places de service quelle que soit l'installation.

---

## 10. LES DÉMONS (Programmes de service)

Un **démon** est un programme logiciel s'exécutant en tâche de fond sur le système informatique du vaisseau. Il occupe un certain nombre de **places de service**. Certains sont obligatoires dès la mise en service.

Un démon peut être mis en veille (libère les places de service mais perd sa fonction), remplacé ou mis à jour en station.

### 10.1 Démons préinstallés (obligatoires / sur tout vaisseau opérationnel)

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

### 10.2 Démons optionnels — Combat & Tactique

| Démon                         | Places | Marque         | Fonctions                                                            | Prix      |
| ----------------------------- | ------ | -------------- | -------------------------------------------------------------------- | --------- |
| **Système de Visée Multiple** | 2      | Nexus Arms     | Vise plusieurs cibles simultanément, bonus précision +10             | 12 000 cr |
| **Reconnaissance Tactique**   | 3      | VDI / DataCore | Analyse adversaire, prédit tactique ennemie, donne son type AI       | 25 000 cr |
| **Protocole Sécurité**        | 1      | OmniShield     | Détection intrusions, verrouillage accès, alerte sabotage            | 8 000 cr  |
| **Gestion Bouclier Avancée**  | 1      | OmniShield     | Optimise regénération bouclier, résistance sélective par type d'arme | 15 000 cr |
| **Brouilleur Tactique**       | 2      | VoidCraft      | Perturbe scanners adverses, réduit précision ennemie -15%            | 30 000 cr |

### 10.3 Démons optionnels — Navigation & Exploration

| Démon                         | Places | Marque        | Fonctions                                                                   | Prix      |
| ----------------------------- | ------ | ------------- | --------------------------------------------------------------------------- | --------- |
| **Calcul Trajectoire Avancé** | 1      | Stratos / AES | Réduit l'erreur de saut HE, bonus au jet d'astrogation                      | 10 000 cr |
| **Navigation Autonome**       | 2      | StellarTech   | Pilotage automatique sur route pré-calculée (manœuvres simples)             | 20 000 cr |
| **Analyse Spectrométrique**   | 1      | AES           | Identifie composition étoiles et planètes depuis l'espace, bonus découverte | 14 000 cr |
| **Cartographie Active**       | 2      | DataCore      | Enrichit l'atlas automatiquement pendant le vol, partage avec guilde        | 18 000 cr |
| **Détecteur Anomalies**       | 1      | AES           | Signale épaves, champs de déchets, objets inhabituels dans la portée scan   | 9 000 cr  |

### 10.4 Démons optionnels — Logistique & Commerce

| Démon                    | Places | Marque      | Fonctions                                                                    | Prix      |
| ------------------------ | ------ | ----------- | ---------------------------------------------------------------------------- | --------- |
| **IA Commerce**          | 1      | DataCore    | Analyse les prix marchés connus, recommande les routes commerciales          | 7 000 cr  |
| **Démon de Maintenance** | 2      | StellarTech | Monitoring pannes, alerte précoce, réduit probabilité panne +score entretien | 16 000 cr |
| **Gestion Équipage**     | 1      | BioSurvival | Plannings, moral, besoins de l'équipage, alerte mutinerie                    | 6 000 cr  |
| **Optimiseur de Soute**  | 1      | MCG         | Réduit masse variable effective -5% (meilleur arrimage)                      | 5 000 cr  |

### 10.5 Démons illégaux / de contrebande *(vendus uniquement dans certaines cantinas)*

| Démon                       | Places | Fonctions                                                    | Note                                 |
| --------------------------- | ------ | ------------------------------------------------------------ | ------------------------------------ |
| **Fantôme de Transpondeur** | 1      | Masque ou falsifie l'identifiant IFF du vaisseau             | Illégal dans la plupart des systèmes |
| **Override Douanier**       | 2      | Bypass les demandes d'inspection automatisées des stations   | Illégal, expulsion si découvert      |
| **Démon de Piratage**       | 3      | Tente de prendre le contrôle de systèmes vaisseaux adjacents | Interdit partout                     |

---

## 11. RÉCAPITULATIF : PLACES DE SERVICE PAR CONFIGURATION

### Exemple — Vaisseau A-1 de base (SI-5, 5 places)

| #   | Démon                   | Places | Statut      |
| --- | ----------------------- | ------ | ----------- |
| 1   | Gestion Capteurs        | 1      | Obligatoire |
| 2   | Propulsion & Pilotage   | 1      | Obligatoire |
| 3   | Atlas & Base de Données | 1      | Obligatoire |
| 4   | *Libre*                 | 1      | Disponible  |
| 5   | *Libre*                 | 1      | Disponible  |

**Places restantes : 2** pour des démons optionnels.

---

### Exemple — E-series explorateur (SI-8 Deep Space, 8 places)

| #   | Démon                   | Places | Statut      |
| --- | ----------------------- | ------ | ----------- |
| 1   | Gestion Capteurs        | 1      | Obligatoire |
| 2   | Propulsion & Pilotage   | 1      | Obligatoire |
| 3   | Atlas & Base de Données | 1      | Obligatoire |
| 4   | Analyse Spectrométrique | 1      | Installé    |
| 5-6 | Cartographie Active     | 2      | Installé    |
| 7   | Détecteur Anomalies     | 1      | Installé    |
| 8   | *Libre*                 | 1      | Disponible  |

**Places restantes : 1**

---

### Exemple — F-series militaire (SI-10 Tactical, 10 places)

| #   | Démon                     | Places | Statut      |
| --- | ------------------------- | ------ | ----------- |
| 1   | Gestion Capteurs          | 1      | Obligatoire |
| 2   | Propulsion & Pilotage     | 1      | Obligatoire |
| 3   | Atlas & Base de Données   | 1      | Obligatoire |
| 4-5 | Système de Visée Multiple | 2      | Installé    |
| 6-8 | Reconnaissance Tactique   | 3      | Installé    |
| 9   | Gestion Bouclier Avancée  | 1      | Installé    |
| 10  | Protocole Sécurité        | 1      | Installé    |

**Places restantes : 0** — configuration saturée.

---

## 12. INTÉGRATION EN JEU (notes de développement)

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

### État d'implémentation des systèmes de propulsion

| Système                                             | État　　　　　　　|
| -----------------------------------------------------| -------------------|
| Mode énergétique Type B — micro-panneaux solaires   | ✅ Implémenté　　　|
| Mode énergétique Type B — voiles solaires           | 🔲 Prévu　　　　　|
| Mode énergétique Type B — extracteurs matière noire | 🔲 Prévu　　　　　|
| Mode combustible Type A                             | 🔲 Non implémenté |
| Achat/remplacement de composants en station         | 🔲 Non implémenté |

---

*Document créé le 2026-05-15 — à compléter lors de l'implémentation des modules achetables en station.*
