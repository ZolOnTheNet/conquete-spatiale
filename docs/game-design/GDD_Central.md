# GAME DESIGN DOCUMENT - INDEX CENTRAL
## Jeu de Conquête Galactique - Console Web

---

## ⚠️📋 NOTES IMPORTANTES

### Disclaimer sur les Valeurs Numériques

**TOUS LES CHIFFRES, COÛTS, DURÉES ET VALEURS NUMÉRIQUES PRÉSENTS DANS CE GDD SONT INDICATIFS.**

- Les valeurs proposées sont des **suggestions** pour aider à la conception
- Aucune valeur n'est définitive ou validée
- **Tous les paramètres devront être estimés, testés et équilibrés** lors de l'implémentation
- Les formules de calcul sont des **exemples** à adapter selon les besoins réels du gameplay

**Ces propositions ne constituent pas des choix définitifs du porteur de projet.**

Le game design final sera déterminé par :
- Les tests de gameplay
- L'équilibrage progressif
- Les retours des joueurs
- Les contraintes techniques

---

## 🔧📝 CORRECTIONS IMPORTANTES

### 📄⚠️ Document de Référence : [CORRECTIONS_IMPORTANTES.md](./CORRECTIONS_IMPORTANTES.md)

**Modifications majeures apportées le 2025-11-01 :**

1. **🎯 Principe PJ (Personnage Joueur)**
   - ✗ ANCIEN : Un joueur = Un vaisseau actif
   - ✅ NOUVEAU : Un joueur = Un PJ qui pilote un vaisseau
   - Possibilité de PJ secondaires pour jouer avec des amis

2. **✓ Module MicroHE**
   - Nouveau module pour petits sauts intra-système
   - Portée 0.1-2 UA (à étudier)
   - Alternative au conventionnel lent

3. **✓ Système Coordonnées**
   - Secteur = coordonnées entières (zone)
   - Position = coordonnées décimales (précision)
   - Exemple : Secteur (0,0,0) + Position (0.12, 0.14, 0.1)

4. **✓ Tâches de Traitement**
   - Système asynchrone moteur/joueur
   - ? détailler (implémentation)

5. **✓ Système Découverte**
   - Algorithme détaillé (formules complètes)
   - Plus on cherche ✓ plus on trouve

**Consulter [CORRECTIONS_IMPORTANTES.md](./CORRECTIONS_IMPORTANTES.md) pour détails complets.**

---

## œš STRUCTURE DOCUMENTAIRE

Ce Game Design Document est divisé en plusieurs parties thématiques pour faciliter la navigation et les mises à jour.

### œ? Document Complet de Référence

#### [GDD_Conquete_Galactique.md](./GDD_Conquete_Galactique.md) - Document Exhaustif
**Contient TOUTES les sections détaillées** (2250+ lignes, 60+ pages)
- Système de jeu complet
- Navigation et combat
- ?conomie et génération procédurale
- Architecture technique complète

**Utilisation :** Référence exhaustive, recherche de contenu spécifique

---

### Documents Thématiques

#### 1. ? [Système de Jeu Core](./GDD_Systeme_Jeu.md)
**Mécanique centrale du jeu**
- Système de dés Daggerheart (2D12)
- Les 6 Traits et 16 Compétences
- Système d'expérience "Learning by Doing"
- Jetons d'Espoir et de Peur
- Exemples d'application

**?tat :** En développement
**Dernière mise à jour :** 2025-10-31

---

#### 2. ? [Navigation et Déplacements](./GDD_Navigation.md)
**Systèmes de voyage et exploration**
- Hyper-espace (inter-stellaire)
- Déplacement conventionnel (intra-système)
- Phase d'orientation post-saut
- Satellites de communication
- Coûts en Points d'Action (PA)

**?tat :** En développement
**Dernière mise à jour :** 2025-10-31

---

#### 3. [Combat et Abordages](./GDD_Combat_Detaille.md) **â?¨ NOUVEAU**
**Systèmes de combat détaillés**
- Gestion des 4 cas de présence (0, 1, 2 joueurs, PNJ)
- Séquences de combat (tirs rapides)
- Saut d'urgence (3 niveaux)
- Comportements prédéfinis (joueur absent)
- Console d'ordres et notifications
- Résolution automatique

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-10-31

---

#### 4. œ¸ [Vaisseaux et ?quipements](./GDD_Vaisseaux_Complet.md) MIS ? JOUR**
**Spécifications complètes depuis wiki**
- 12 Emplacements (Pilotage, Moteur, Moteur HE, Boucliers, etc.)
- Système soute (3 niveaux transport personnel)
- 2 types propulsion (Combustible vs Extraction énergétique)
- Formules calcul (Conventionnel et HE)
- Modèles A-0, A-1, séries M, E, F
- Programmes informatiques

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-11-01

---

#### 5. œ° [?conomie et Ressources](./GDD_Economie_Complete.md) **â?¨ MIS ? JOUR**
**Système économique complet depuis wiki**
- Nœuds économiques (Hommes + Machines ✓ Production)
- 21 matières premières minières
- Chaîne transformation industrielle complète
- 3 niveaux médicaments
- Système personnel et productivité
- Opportunités économiques

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-11-01

---

#### 5.5. œ?ï¸? [Bases Spatiales](./GDD_Bases_Spatiales.md) **â?¨ NOUVEAU**
**Système de bases spatiales depuis wiki**
- L'Arche (module maître, 5 modules + production énergie)
- Extension par arches (gestionnaire, accord)
- 13 types de modules (Antenne, Bar, Mine, Habitation, etc.)
- Système gestionnaire (changement possible)
- Idées attachées (investissement, extension infinie, humanisation)

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-11-01

---

#### 6. œ? [Univers et Génération Procédurale](./GDD_Univers_Generation.md) **â?¨ MIS ? JOUR**
**Création dynamique de l'univers depuis wiki**
- Moteur générique multi-univers (Zaib, Lunastars, Solar Empire)
- Classification étoiles (O à M) + Courbe de Gauss
- Générateur simple (NœNœN, courbe Gauss)
- Générateur à chemins (routes entre systèmes)
- Gisements et rendement
- Références univers (Zaib, Lunastars, Solar Empire)

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-11-01

---

#### 7. œ­ [Détection et Exploration](./GDD_Detection.md)
**Systèmes de découverte**
- Fond d'étoiles dynamique
- Système de détection par accumulation
- Piège des galaxies lointaines
- Capacités évolutives des vaisseaux
- Marché de l'information

**?tat :** En développement
**Dernière mise à jour :** 2025-10-31

---

#### 7.5. œ­ [Système de Découverte](./GDD_Systeme_Decouverte.md) **â?¨ NOUVEAU**
**Algorithme de découverte des systèmes stellaires**
- Basé sur puissance solaire (min 10)
- Formule seuil : 500 + (Distance œ 100)
- Points tâche cumulatifs
- Lancé : (SysExpl) D (2 œ PSol)
- Plus on cherche, plus on trouve (petits/distants/cachés)

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-11-01

---

#### 8. œ?ï¸? [Réputation et Factions](./GDD_Reputation.md)
**Système social et politique**
- Système de réputation (0-25000 pts)
- Guildes impériales et joueurs
- Actions influençant la réputation
- Diplomatie

**?tat :** En développement
**Dernière mise à jour :** 2025-10-31

---

#### 9. œ? [Univers : Conquête Spatiale](./GDD_Univers_Conquete_Spatiale.md) **â?¨ NOUVEAU**
**Historique et Lore**
- Début de la conquête spatiale (moteur de saut)
- Grandes cités spatiales (Angeles, Espérance, New-SanFrancisco)
- Système des guildes (nations et compagnies)
- Corsaires vs Pirates
- Archétypes de joueurs (Explorateur, Marchand, Transporteur, etc.)
- Zones de l'espace

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-10-31

---

#### 10. œ» [Structure de l'Interface](./GDD_Interface.md) **â?¨ NOUVEAU**
**Interface utilisateur et UX**
- Layout général (4 zones)
- Chapitres et menus (Personnage, Vaisseau, Base, Jeu)
- Console d'ordres
- Architecture MVC
- Design modulaire

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-10-31

---

#### 11. œ» [Architecture Technique et Classes](./GDD_Architecture_Technique.md) **â?¨ MIS ? JOUR**
**Implémentation technique depuis wiki**
- Pattern MVC (Modèle-Vue-Contrôleur)
- Classe Compte (joueur)
- Classe ObjetSpatial (parent)
- Classe Vaisseau (hérite ObjetSpatial)
- Classe Base (hérite ObjetSpatial)
- Classes auxiliaires (Cargo, Module, Programme, Panne)
- Tables base de données

**?tat :** ✓ Intégré depuis Wiki
**Dernière mise à jour :** 2025-11-01

---

## œ§ ARCHITECTURE MULTI-UNIVERS

### Objectif
Le moteur de jeu est conçu pour supporter plusieurs univers de science-fiction sans modification majeure du code core.

### Univers Supportés (prévus)
1. **Archiluminique** - Univers original du jeu
2. **Conquête Spatiale** - Proche de la réalité, vaisseaux avec hyper-espace
3. **Star Wars** - Guerre Civile Galactique
4. **Warhammer 40K** - Imperium, Chaos, Xenos
5. **Star Citizen** - UEE, systèmes Stanton

### Couches d'Abstraction

```
✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓
✓   CONTENU UNIVERS (modules)         ✓
✓   - Factions                         ✓
✓   - Vaisseaux                        ✓
✓   - Technologies                     ✓
✓   - Lore / ?vénements                ✓
✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓
              ✓
✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓
✓   RœGLES UNIVERS                     ✓
✓   - Vitesses FTL                     ✓
✓   - Types d'armes                    ✓
✓   - Ressources spécifiques           ✓
✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓
              ✓
✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓
✓   MOTEUR CORE (universel)            ✓
✓   - Navigation                       ✓
✓   - Détection                        ✓
✓   - Combat (système de dés)          ✓
✓   - ?conomie                         ✓
✓   - Réputation                       ✓
✓   - Génération procédurale           ✓
✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓
```

---

## ?Ž¯ FORMAT DU JEU

### Caractéristiques Principales
- **Interface** : Console web (commandes texte + visualisation optionnelle)
- **Rythme** : Tour par tour (1 tour = 1 jour in-game)
- **Multijoueur** : **ASYNCHRONE** - Persistant
  - Les joueurs ne jouent **PAS en même temps**
  - Mécanique PvP asymétrique avec règles de comportement automatiques
  - Seuils de fuite automatique
- **Progression** : Du vaisseau starter à l'empire multi-systèmes

### Piliers de Gameplay
1. **Exploration** - Découvrir des systèmes stellaires inconnus
2. **?conomie** - Chaîne de production complexe
3. **Combat** - Affrontements tactiques tour par tour
4. **Diplomatie** - Système de réputation avec guildes
5. **Conquête** - Expansion territoriale

---

## œ? PHASES DE D?VELOPPEMENT

### Phase 1 : MVP (Minimum Viable Product)
1. ✓ Système navigation tour par tour
2. ✓ Détection basique
3. ✓ Génération procédurale simple (étoiles GAIA)
4. ✓ Combat PvE basique
5. ✓ ?conomie simplifiée
6. ✓ Interface console fonctionnelle

### Phase 2 : Expansion
- Système réputation complet
- Guildes joueurs
- Génération planètes avancée
- Marché dynamique
- PvP asynchrone

### Phase 3 : Endgame
- Stations mobiles
- Construction avancée
- Diplomatie complexe
- ?vénements galactiques
- Multi-univers (Star Wars, etc.)

---

## œ? CHANGELOG GLOBAL

### Version 0.3 (2025-10-31)
- ✓ Restructuration documentaire modulaire
- ✓ Ajout disclaimer sur valeurs numériques
- ✓ Système d'XP "Learning by Doing"
- ✓ Compétence "Corps à corps" ajoutée
- ✓ Section Abordages complète
- ✓ PvP Asynchrone défini
- ✓ Correction encodage UTF-8

### Version 0.2 (2025-10-30)
- ✓ Système Daggerheart 2D12 intégré
- ✓ 16 Compétences définies avec Traits
- ✓ Gameplay asynchrone précisé
- ✓ Nom univers "Archiluminique"

### Version 0.1 (2025-10-30)
- ✓ Concepts core définis
- ✓ Architecture modulaire multi-univers
- ✓ Luna Industries nommée
- ✓ Génération procédurale complète

---

## ✓ ? VALIDER / MODIFIER

- [ ] Balance économique (prix vaisseaux, ressources)
- [ ] Valeurs XP et coûts de progression
- [ ] Noms factions/guildes Archiluminique
- [ ] Règles PvP détaillées
- [ ] Contenu aliens (Phase future)
- [ ] Calibrage système de détection
- [ ] Tests de gameplay et équilibrage

---

## œš R?F?RENCES

### Inspirations
- **Lunastars** : https://v2.lunastars.net
- **Empire Galactique (JDR)** : https://jeuderole.empiregalactique.site
- **Star Citizen** : https://robertsspaceindustries.com
- **Daggerheart** : https://darringtonpress.com/daggerheart

### Sources de Données
- **GAIA Archive** : https://gea.esac.esa.int/archive/
- **NASA Exoplanet Archive** : https://exoplanetarchive.ipac.caltech.edu/
- **JPL Horizons** : https://ssd.jpl.nasa.gov/horizons/
- **JPL Small-Body Database** : https://ssd.jpl.nasa.gov/tools/sbdb_lookup.html

---

**Document vivant - Dernière mise à jour : 2025-10-31**

**Contact Projet :** [? compléter]
**Version GDD :** 0.3-alpha
