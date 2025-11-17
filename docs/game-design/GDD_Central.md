# GAME DESIGN DOCUMENT - INDEX CENTRAL
## Jeu de ConquÃªte Galactique - Console Web

---

## âš ï¸ NOTES IMPORTANTES

### Disclaimer sur les Valeurs NumÃ©riques

**TOUS LES CHIFFRES, COÃ›TS, DURÃ‰ES ET VALEURS NUMÃ‰RIQUES PRÃ‰SENTS DANS CE GDD SONT INDICATIFS.**

- Les valeurs proposÃ©es sont des **suggestions** pour aider Ã  la conception
- Aucune valeur n'est dÃ©finitive ou validÃ©e
- **Tous les paramÃ¨tres devront Ãªtre estimÃ©s, testÃ©s et Ã©quilibrÃ©s** lors de l'implÃ©mentation
- Les formules de calcul sont des **exemples** Ã  adapter selon les besoins rÃ©els du gameplay

**Ces propositions ne constituent pas des choix dÃ©finitifs du porteur de projet.**

Le game design final sera dÃ©terminÃ© par :
- Les tests de gameplay
- L'Ã©quilibrage progressif
- Les retours des joueurs
- Les contraintes techniques

---

## ðŸ”§ CORRECTIONS IMPORTANTES

### âš ï¸ Document de RÃ©fÃ©rence : [CORRECTIONS_IMPORTANTES.md](./CORRECTIONS_IMPORTANTES.md)

**Modifications majeures apportÃ©es le 2025-11-01 :**

1. **âœ… Principe PJ (Personnage Joueur)**
   - âŒ ANCIEN : Un joueur = Un vaisseau actif
   - âœ“ NOUVEAU : Un joueur = Un PJ qui pilote un vaisseau
   - PossibilitÃ© de PJ secondaires pour jouer avec des amis

2. **âœ… Module MicroHE**
   - Nouveau module pour petits sauts intra-systÃ¨me
   - PortÃ©e 0.1-2 UA (Ã  Ã©tudier)
   - Alternative au conventionnel lent

3. **âœ… SystÃ¨me CoordonnÃ©es**
   - Secteur = coordonnÃ©es entiÃ¨res (zone)
   - Position = coordonnÃ©es dÃ©cimales (prÃ©cision)
   - Exemple : Secteur (0,0,0) + Position (0.12, 0.14, 0.1)

4. **âœ… TÃ¢ches de Traitement**
   - SystÃ¨me asynchrone moteur/joueur
   - Ã€ dÃ©tailler (implÃ©mentation)

5. **âœ… SystÃ¨me DÃ©couverte**
   - Algorithme dÃ©taillÃ© (formules complÃ¨tes)
   - Plus on cherche â†’ plus on trouve

**Consulter [CORRECTIONS_IMPORTANTES.md](./CORRECTIONS_IMPORTANTES.md) pour dÃ©tails complets.**

---

## ðŸ“š STRUCTURE DOCUMENTAIRE

Ce Game Design Document est divisÃ© en plusieurs parties thÃ©matiques pour faciliter la navigation et les mises Ã  jour.

### ðŸ“– Document Complet de RÃ©fÃ©rence

#### [GDD_Conquete_Galactique.md](./GDD_Conquete_Galactique.md) - Document Exhaustif
**Contient TOUTES les sections dÃ©taillÃ©es** (2250+ lignes, 60+ pages)
- SystÃ¨me de jeu complet
- Navigation et combat
- Ã‰conomie et gÃ©nÃ©ration procÃ©durale
- Architecture technique complÃ¨te

**Utilisation :** RÃ©fÃ©rence exhaustive, recherche de contenu spÃ©cifique

---

### Documents ThÃ©matiques

#### 1. ðŸŽ² [SystÃ¨me de Jeu Core](./GDD_Systeme_Jeu.md)
**MÃ©canique centrale du jeu**
- SystÃ¨me de dÃ©s Daggerheart (2D12)
- Les 6 Traits et 16 CompÃ©tences
- SystÃ¨me d'expÃ©rience "Learning by Doing"
- Jetons d'Espoir et de Peur
- Exemples d'application

**Ã‰tat :** En dÃ©veloppement
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 2. ðŸš€ [Navigation et DÃ©placements](./GDD_Navigation.md)
**SystÃ¨mes de voyage et exploration**
- Hyper-espace (inter-stellaire)
- DÃ©placement conventionnel (intra-systÃ¨me)
- Phase d'orientation post-saut
- Satellites de communication
- CoÃ»ts en Points d'Action (PA)

**Ã‰tat :** En dÃ©veloppement
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 3. âš”ï¸ [Combat et Abordages](./GDD_Combat_Detaille.md) **âœ¨ NOUVEAU**
**SystÃ¨mes de combat dÃ©taillÃ©s**
- Gestion des 4 cas de prÃ©sence (0, 1, 2 joueurs, PNJ)
- SÃ©quences de combat (tirs rapides)
- Saut d'urgence (3 niveaux)
- Comportements prÃ©dÃ©finis (joueur absent)
- Console d'ordres et notifications
- RÃ©solution automatique

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 4. ðŸ›¸ [Vaisseaux et Ã‰quipements](./GDD_Vaisseaux_Complet.md) **âœ¨ MIS Ã€ JOUR**
**SpÃ©cifications complÃ¨tes depuis wiki**
- 12 Emplacements (Pilotage, Moteur, Moteur HE, Boucliers, etc.)
- SystÃ¨me soute (3 niveaux transport personnel)
- 2 types propulsion (Combustible vs Extraction Ã©nergÃ©tique)
- Formules calcul (Conventionnel et HE)
- ModÃ¨les A-0, A-1, sÃ©ries M, E, F
- Programmes informatiques

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-11-01

---

#### 5. ðŸ’° [Ã‰conomie et Ressources](./GDD_Economie_Complete.md) **âœ¨ MIS Ã€ JOUR**
**SystÃ¨me Ã©conomique complet depuis wiki**
- NÅ“uds Ã©conomiques (Hommes + Machines â†’ Production)
- 21 matiÃ¨res premiÃ¨res miniÃ¨res
- ChaÃ®ne transformation industrielle complÃ¨te
- 3 niveaux mÃ©dicaments
- SystÃ¨me personnel et productivitÃ©
- OpportunitÃ©s Ã©conomiques

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-11-01

---

#### 5.5. ðŸ—ï¸ [Bases Spatiales](./GDD_Bases_Spatiales.md) **âœ¨ NOUVEAU**
**SystÃ¨me de bases spatiales depuis wiki**
- L'Arche (module maÃ®tre, 5 modules + production Ã©nergie)
- Extension par arches (gestionnaire, accord)
- 13 types de modules (Antenne, Bar, Mine, Habitation, etc.)
- SystÃ¨me gestionnaire (changement possible)
- IdÃ©es attachÃ©es (investissement, extension infinie, humanisation)

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-11-01

---

#### 6. ðŸŒŒ [Univers et GÃ©nÃ©ration ProcÃ©durale](./GDD_Univers_Generation.md) **âœ¨ MIS Ã€ JOUR**
**CrÃ©ation dynamique de l'univers depuis wiki**
- Moteur gÃ©nÃ©rique multi-univers (Zaib, Lunastars, Solar Empire)
- Classification Ã©toiles (O Ã  M) + Courbe de Gauss
- GÃ©nÃ©rateur simple (NÃ—NÃ—N, courbe Gauss)
- GÃ©nÃ©rateur Ã  chemins (routes entre systÃ¨mes)
- Gisements et rendement
- RÃ©fÃ©rences univers (Zaib, Lunastars, Solar Empire)

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-11-01

---

#### 7. ðŸ”­ [DÃ©tection et Exploration](./GDD_Detection.md)
**SystÃ¨mes de dÃ©couverte**
- Fond d'Ã©toiles dynamique
- SystÃ¨me de dÃ©tection par accumulation
- PiÃ¨ge des galaxies lointaines
- CapacitÃ©s Ã©volutives des vaisseaux
- MarchÃ© de l'information

**Ã‰tat :** En dÃ©veloppement
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 7.5. ðŸ”­ [SystÃ¨me de DÃ©couverte](./GDD_Systeme_Decouverte.md) **âœ¨ NOUVEAU**
**Algorithme de dÃ©couverte des systÃ¨mes stellaires**
- BasÃ© sur puissance solaire (min 10)
- Formule seuil : 500 + (Distance Ã— 100)
- Points tÃ¢che cumulatifs
- LancÃ© : (SysExpl) D (2 Ã— PSol)
- Plus on cherche, plus on trouve (petits/distants/cachÃ©s)

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-11-01

---

#### 8. ðŸ›ï¸ [RÃ©putation et Factions](./GDD_Reputation.md)
**SystÃ¨me social et politique**
- SystÃ¨me de rÃ©putation (0-25000 pts)
- Guildes impÃ©riales et joueurs
- Actions influenÃ§ant la rÃ©putation
- Diplomatie

**Ã‰tat :** En dÃ©veloppement
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 9. ðŸŒŒ [Univers : ConquÃªte Spatiale](./GDD_Univers_Conquete_Spatiale.md) **âœ¨ NOUVEAU**
**Historique et Lore**
- DÃ©but de la conquÃªte spatiale (moteur de saut)
- Grandes citÃ©s spatiales (Angeles, EspÃ©rance, New-SanFrancisco)
- SystÃ¨me des guildes (nations et compagnies)
- Corsaires vs Pirates
- ArchÃ©types de joueurs (Explorateur, Marchand, Transporteur, etc.)
- Zones de l'espace

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 10. ðŸ’» [Structure de l'Interface](./GDD_Interface.md) **âœ¨ NOUVEAU**
**Interface utilisateur et UX**
- Layout gÃ©nÃ©ral (4 zones)
- Chapitres et menus (Personnage, Vaisseau, Base, Jeu)
- Console d'ordres
- Architecture MVC
- Design modulaire

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-10-31

---

#### 11. ðŸ’» [Architecture Technique et Classes](./GDD_Architecture_Technique.md) **âœ¨ MIS Ã€ JOUR**
**ImplÃ©mentation technique depuis wiki**
- Pattern MVC (ModÃ¨le-Vue-ContrÃ´leur)
- Classe Compte (joueur)
- Classe ObjetSpatial (parent)
- Classe Vaisseau (hÃ©rite ObjetSpatial)
- Classe Base (hÃ©rite ObjetSpatial)
- Classes auxiliaires (Cargo, Module, Programme, Panne)
- Tables base de donnÃ©es

**Ã‰tat :** âœ… IntÃ©grÃ© depuis Wiki
**DerniÃ¨re mise Ã  jour :** 2025-11-01

---

## ðŸ”§ ARCHITECTURE MULTI-UNIVERS

### Objectif
Le moteur de jeu est conÃ§u pour supporter plusieurs univers de science-fiction sans modification majeure du code core.

### Univers SupportÃ©s (prÃ©vus)
1. **Archiluminique** - Univers original du jeu
2. **ConquÃªte Spatiale** - Proche de la rÃ©alitÃ©, vaisseaux avec hyper-espace
3. **Star Wars** - Guerre Civile Galactique
4. **Warhammer 40K** - Imperium, Chaos, Xenos
5. **Star Citizen** - UEE, systÃ¨mes Stanton

### Couches d'Abstraction

```
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚   CONTENU UNIVERS (modules)         â”‚
â”‚   - Factions                         â”‚
â”‚   - Vaisseaux                        â”‚
â”‚   - Technologies                     â”‚
â”‚   - Lore / Ã‰vÃ©nements                â”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
              â†“
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚   RÃˆGLES UNIVERS                     â”‚
â”‚   - Vitesses FTL                     â”‚
â”‚   - Types d'armes                    â”‚
â”‚   - Ressources spÃ©cifiques           â”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
              â†“
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚   MOTEUR CORE (universel)            â”‚
â”‚   - Navigation                       â”‚
â”‚   - DÃ©tection                        â”‚
â”‚   - Combat (systÃ¨me de dÃ©s)          â”‚
â”‚   - Ã‰conomie                         â”‚
â”‚   - RÃ©putation                       â”‚
â”‚   - GÃ©nÃ©ration procÃ©durale           â”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
```

---

## ðŸŽ¯ FORMAT DU JEU

### CaractÃ©ristiques Principales
- **Interface** : Console web (commandes texte + visualisation optionnelle)
- **Rythme** : Tour par tour (1 tour = 1 jour in-game)
- **Multijoueur** : **ASYNCHRONE** - Persistant
  - Les joueurs ne jouent **PAS en mÃªme temps**
  - MÃ©canique PvP asymÃ©trique avec rÃ¨gles de comportement automatiques
  - Seuils de fuite automatique
- **Progression** : Du vaisseau starter Ã  l'empire multi-systÃ¨mes

### Piliers de Gameplay
1. **Exploration** - DÃ©couvrir des systÃ¨mes stellaires inconnus
2. **Ã‰conomie** - ChaÃ®ne de production complexe
3. **Combat** - Affrontements tactiques tour par tour
4. **Diplomatie** - SystÃ¨me de rÃ©putation avec guildes
5. **ConquÃªte** - Expansion territoriale

---

## ðŸ“… PHASES DE DÃ‰VELOPPEMENT

### Phase 1 : MVP (Minimum Viable Product)
1. âœ… SystÃ¨me navigation tour par tour
2. âœ… DÃ©tection basique
3. âœ… GÃ©nÃ©ration procÃ©durale simple (Ã©toiles GAIA)
4. âœ… Combat PvE basique
5. âœ… Ã‰conomie simplifiÃ©e
6. âœ… Interface console fonctionnelle

### Phase 2 : Expansion
- SystÃ¨me rÃ©putation complet
- Guildes joueurs
- GÃ©nÃ©ration planÃ¨tes avancÃ©e
- MarchÃ© dynamique
- PvP asynchrone

### Phase 3 : Endgame
- Stations mobiles
- Construction avancÃ©e
- Diplomatie complexe
- Ã‰vÃ©nements galactiques
- Multi-univers (Star Wars, etc.)

---

## ðŸ”„ CHANGELOG GLOBAL

### Version 0.3 (2025-10-31)
- âœ… Restructuration documentaire modulaire
- âœ… Ajout disclaimer sur valeurs numÃ©riques
- âœ… SystÃ¨me d'XP "Learning by Doing"
- âœ… CompÃ©tence "Corps Ã  corps" ajoutÃ©e
- âœ… Section Abordages complÃ¨te
- âœ… PvP Asynchrone dÃ©fini
- âœ… Correction encodage UTF-8

### Version 0.2 (2025-10-30)
- âœ… SystÃ¨me Daggerheart 2D12 intÃ©grÃ©
- âœ… 16 CompÃ©tences dÃ©finies avec Traits
- âœ… Gameplay asynchrone prÃ©cisÃ©
- âœ… Nom univers "Archiluminique"

### Version 0.1 (2025-10-30)
- âœ… Concepts core dÃ©finis
- âœ… Architecture modulaire multi-univers
- âœ… Luna Industries nommÃ©e
- âœ… GÃ©nÃ©ration procÃ©durale complÃ¨te

---

## âœ… Ã€ VALIDER / MODIFIER

- [ ] Balance Ã©conomique (prix vaisseaux, ressources)
- [ ] Valeurs XP et coÃ»ts de progression
- [ ] Noms factions/guildes Archiluminique
- [ ] RÃ¨gles PvP dÃ©taillÃ©es
- [ ] Contenu aliens (Phase future)
- [ ] Calibrage systÃ¨me de dÃ©tection
- [ ] Tests de gameplay et Ã©quilibrage

---

## ðŸ“š RÃ‰FÃ‰RENCES

### Inspirations
- **Lunastars** : https://v2.lunastars.net
- **Empire Galactique (JDR)** : https://jeuderole.empiregalactique.site
- **Star Citizen** : https://robertsspaceindustries.com
- **Daggerheart** : https://darringtonpress.com/daggerheart

### Sources de DonnÃ©es
- **GAIA Archive** : https://gea.esac.esa.int/archive/
- **NASA Exoplanet Archive** : https://exoplanetarchive.ipac.caltech.edu/
- **JPL Horizons** : https://ssd.jpl.nasa.gov/horizons/
- **JPL Small-Body Database** : https://ssd.jpl.nasa.gov/tools/sbdb_lookup.html

---

**Document vivant - DerniÃ¨re mise Ã  jour : 2025-10-31**

**Contact Projet :** [Ã€ complÃ©ter]
**Version GDD :** 0.3-alpha
