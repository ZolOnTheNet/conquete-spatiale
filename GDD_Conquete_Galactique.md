# GAME DESIGN DOCUMENT
## Jeu d'Exploration Galactique - Interface Web

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture modulaire multi-univers](#architecture-modulaire)
3. [Système de jeu core](#système-de-jeu-core)
4. [Interface utilisateur](#interface-utilisateur)
5. [Navigation et déplacements](#navigation-et-déplacements)
6. [Détection et exploration](#détection-et-exploration)
7. [Vaisseaux et équipements](#vaisseaux-et-équipements)
8. [Économie et ressources](#économie-et-ressources)
9. [Combat et zones de contrôle](#combat-et-zones-de-contrôle)
10. [Réputation et factions](#réputation-et-factions)
11. [Génération procédurale](#génération-procédurale)
12. [Points d'intérêt (PoV)](#points-dintérêt-pov)
13. [Architecture technique](#architecture-technique)

---

## 🌌 VUE D'ENSEMBLE

### Concept
Jeu web d'exploration galactique au tour par tour, avec interface graphique et système de commandes. Les joueurs explorent la Voie Lactée en utilisant des données réelles (GAIA, NASA) combinées à de la génération procédurale.

### Piliers de gameplay
- **Exploration** : Découvrir des systèmes stellaires inconnus avec risques et récompenses
- **Économie** : Chaîne de production complexe (extraction → raffinage → production)
- **Commerce** : Empire commercial, routes, négoce de ressources et informations
- **Combat** : Affrontements tactiques tour par tour avec gestion de ressources
- **Diplomatie** : Système de réputation avec guildes et factions

**Note importante :** Le jeu ne se concentre pas sur la conquête territoriale classique et la construction d'empire militaire. L'accent est mis sur l'exploration, le commerce, et la construction d'un réseau d'influence économique. Les mécaniques de contrôle territorial sont prévues pour développement ultérieur, mais pas prioritaires au début.

### Format
- **Interface** : Web avec visualisation graphique + console de commandes
- **Rythme** : Tour par tour (1 tour = 1 jour in-game)
- **Mode de jeu** : **ASYNCHRONE** - Les joueurs ne jouent pas en même temps
- **Multijoueur** : Persistant, joueurs humains + IA
- **Progression** : Du vaisseau starter à un réseau commercial étendu

**Implication du mode asynchrone :**
- Les joueurs jouent à leur propre rythme
- Combat PvP nécessite une **mécanique asymétrique automatique**
- Règles de comportement de défense/attaque prédéfinies
- Seuils de fuite automatique
- Système gère les combats entre joueurs absents

---

## 🔧 ARCHITECTURE MODULAIRE MULTI-UNIVERS

### Objectif
Le moteur de jeu doit supporter plusieurs univers de science-fiction sans modification majeure du code core.

### Univers supportés (prévus)
1. **Archiluminique** (univers original du jeu)
2. **Conquête Spatiale** (proche de la réalité, vaisseaux avec hyper-espace)
3. **Star Wars** (Guerre Civile Galactique, etc.)
4. **Warhammer 40K** (Imperium, Chaos, Xenos)
5. **Star Citizen** (UEE, systèmes Stanton, etc.)

### Couches d'abstraction

```
┌─────────────────────────────────────┐
│   CONTENU UNIVERS (modules)         │
│   - Factions                         │
│   - Vaisseaux                        │
│   - Technologies                     │
│   - Lore / Événements                │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   RÈGLES UNIVERS                     │
│   - Vitesses FTL                     │
│   - Types d'armes                    │
│   - Ressources spécifiques           │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   MOTEUR CORE (universel)            │
│   - Navigation                       │
│   - Détection                        │
│   - Combat (système de dés)          │
│   - Économie                         │
│   - Réputation                       │
│   - Génération procédurale           │
└─────────────────────────────────────┘
```

### Configuration par univers
Fichiers de configuration JSON/YAML définissant :
- Noms des factions
- Stats des vaisseaux
- Arbres technologiques
- Paramètres de balance (vitesses, coûts, etc.)

---

## 🎲 SYSTÈME DE JEU CORE

### Système de dés : Daggerheart (2D12)

**Mécanisme central :**

Le système utilise des **Dés de Dualité** :
- **2d12** de couleurs différentes : un dé d'**Espoir** (Hope) et un dé de **Peur** (Fear)
- Formule : `Somme 2d12 + Trait + Modificateurs` vs Difficulté

**Résolution d'action :**

1. **Lancer les 2d12** (Hope + Fear)
2. **ADDITIONNER les deux dés** + Trait + Modificateurs
   - **Trait** : valeur du trait approprié (Agilité, Force, Finesse, Instinct, Présence, Connaissance)
   - **Modificateurs** : bonus d'équipement, circonstances, etc.
   - **Note** : Les compétences seront détaillées ultérieurement
3. **Comparer au seuil de difficulté** :
   - ≥ Seuil : **Succès**
   - < Seuil : **Échec**

4. **Génération de jetons** (indépendant du succès) :
   - Si **dé d'Espoir > dé de Peur** → génère des **jetons d'Espoir** pour les joueurs
   - Si **dé de Peur > dé d'Espoir** → génère des **jetons de Peur** pour le système (caché)
   - Si **égalité (sauf 1-1)** → **CRITIQUE !** Réussite exceptionnelle + génère **1 jeton d'Espoir**
   - Si **1-1** → **CATASTROPHE !** Génère **1 jeton de Peur** (système)

**Points clés :**
- Le résultat = **Somme des 2 dés** + modificateurs (pas le meilleur)
- On peut **réussir avec Peur** (somme élevée mais Fear > Hope → jeton système)
- On peut **échouer avec Espoir** (somme faible mais Hope > Fear → jeton joueur)
- **Égalité (sauf 1-1)** = **CRITIQUE** → Réussite exceptionnelle + jeton Espoir
- **Double 1 (1-1)** = **CATASTROPHE** → Génère jeton de Peur (complications assurées)
- Les jetons sont une ressource narrative pour influencer l'histoire

---

### Les 6 Traits

Chaque personnage/vaisseau possède 6 traits (valeur numérique) :

**Traits physiques :**
- **Agilité** - Mouvement, esquive, dextérité
- **Force** - Combat physique, puissance
- **Finesse** - Précision, discrétion, manipulation

**Traits mentaux :**
- **Instinct** - Intuition, survie, perception
- **Présence** - Charisme, leadership, intimidation
- **Connaissance** - Érudition, analyse, technologie

**Note importante :**
- Les **Traits** représentent les **valeurs minimales** des compétences
- Les **Compétences** sont utilisées pour les jets de dés spécifiques
- Toutes les compétences n'ajouteront pas nécessairement au jet de dés

---

### Les Compétences

Chaque compétence est associée à un **Trait de base**. Lors d'un jet, on utilise :
- La valeur de la **compétence** (si applicable)
- Le **trait associé** comme valeur minimale

**Liste des compétences :**

**Compétences de Navigation & Technique :**
- **Astrogation** (Connaissance) - Calculs hyperespace, navigation stellaire
- **Pilotage** (Agilité) - Manœuvres vaisseau, combat spatial
- **Informatique** (Connaissance) - Systèmes informatiques, piratage
- **Mécanique** (Finesse) - Réparations, maintenance, bricolage

**Compétences Sociales :**
- **Charme** (Présence) - Séduction, manipulation douce
- **Coercition** (Force) - Intimidation, menaces
- **Commandement** (Instinct) - Leadership, tactique d'équipe
- **Négociation** (Présence) - Commerce, diplomatie

**Compétences de Survie & Perception :**
- **Perception** (Instinct) - Détection, vigilance, observation
- **Survie** (Instinct) - Environnements hostiles, débrouillardise
- **Médecine** (Connaissance) - Soins, chirurgie, biologie

**Compétences de Combat :**
- **Artillerie** (Finesse) - Canons, tourelles, armes énergétiques
- **Arme Lourde** (Finesse) - Railguns, torpilles, armes lourdes
- **Missile** (Instinct) - Guidage missiles, tactique de tir

**Compétences Spéciales :**
- **Marché Noir** (Connaissance) - Contacts illégaux, contrebande, ressources rares

---

### Système asymétrique : Joueurs vs Système

**Joueurs :**
- Utilisent **2d12** (Dés de Dualité)
- Génèrent des jetons d'Espoir (visibles, ressource du joueur)
- Génèrent des jetons de Peur (invisibles, capital du système)

**Système/IA (adversaires, environnement) :**
- Utilise **1d20** pour contrôler les ennemis
- Accumule les jetons de Peur (CACHÉS du joueur)
- Utilise automatiquement les jetons pour créer :
  - Complications narratives
  - Événements imprévus
  - Dangers et embuscades
  - Renforcer ennemis
  - Rendre l'environnement hostile

**Capital de Peur (invisible) :**
Le joueur ne voit PAS combien de jetons de Peur le système possède. Cela crée de la tension et de l'incertitude.

**Système de déclenchement :**

```
À chaque action significative (ou fin de tour) :
1. Jet de dés : 1d60 (ou lié à fiabilité vaisseau)
2. Si résultat < Capital Peur accumulé → ÉVÉNEMENT
3. Événement consomme X jetons de Peur selon importance
4. Si pas d'événement → Capital Peur continue d'augmenter
```

**Formule de déclenchement :**
```
Jet : 1d60 (ou 1dX selon fiabilité vaisseau)
Capital Peur accumulé : N jetons

Si Jet < N → Événement déclenché
```

**Exemple :**
```
Capital Peur : 15 jetons
Fiabilité vaisseau : Standard (1d60)
Jet : 1d60 = 12
12 < 15 → ÉVÉNEMENT !

→ Système déclenche "Embuscade pirate" (coût 8 jetons)
→ Capital Peur restant : 15 - 8 = 7 jetons
```

**Événements et coûts en Peur :**

```
COMPLICATIONS MINEURES (coût 2-5 jetons)
├─ Panne mineure (coût 1-2 PA réparation)
├─ Déviation navigation (+0.1-0.3 AL)
├─ Contact radio parasite / fausse alerte
└─ Micro-météorite (-5 HP)

COMPLICATIONS MAJEURES (coût 6-10 jetons)
├─ Embuscade pirate (1-3 ennemis)
├─ Anomalie spatiale (obstacle navigation)
├─ Panne système critique (arme/bouclier/moteur)
└─ Rencontre hostile imprévue

ÉVÉNEMENTS CRITIQUES (coût 11-20 jetons)
├─ Embuscade coordonnée (5+ ennemis)
├─ Catastrophe environnementale (tempête, radiation)
├─ Trahison/sabotage interne
└─ Apparition élite/boss ennemi

ÉVÉNEMENTS MAJEURS (coût 20+ jetons)
├─ Flotte ennemie (10+ vaisseaux)
├─ Désastre système (supernova, trou noir)
├─ Intervention faction majeure
└─ Arc narratif déclenché
```

**Fiabilité du vaisseau (modificateur) :**

```
Vaisseau neuf/bien entretenu : 1d60 (standard)
Vaisseau usé : 1d50 (événements plus fréquents)
Vaisseau délabré : 1d40 (très instable)
Vaisseau militaire : 1d80 (très fiable)
Vaisseau prototype : 1d100 (extrêmement fiable)
```

**⚠️ COHÉRENCE NARRATIVE - RÈGLES IMPORTANTES :**

**1. Persistance des événements générés :**

```
Événement créé → Stocké en base de données
├─ Position exacte (système, coordonnées)
├─ Type (flotte pirate, anomalie, etc.)
├─ Durée de vie / Persistance
└─ État (actif, en mouvement, disparu)

Exemple : Flotte pirate générée
├─ Créée à : Système Alpha, secteur B-4
├─ Reste là : 10-30 tours minimum
├─ Peut se déplacer : Selon IA/patrouille
├─ Disparaît si : Détruite OU événement timer écoulé
```

**2. Événements localisés :**

Les événements sont **liés à un lieu spécifique** :

```
TYPE 1 : Événements fixes (persistent longtemps)
├─ Flotte pirate → Reste dans secteur 20-50 tours
├─ Champ d'astéroïdes → Permanent (jusqu'à exploitation)
├─ Anomalie spatiale → Reste 50-100 tours
└─ Base ennemie → Permanente (jusqu'à destruction)

TYPE 2 : Événements temporaires (disparaissent)
├─ Tempête solaire → 5-10 tours
├─ Nuage ionisé → 10-20 tours
├─ Passage flotte commerciale → 2-5 tours
└─ Signal de détresse → 5-15 tours

TYPE 3 : Événements vaisseau (suivent le joueur)
├─ Panne système → Jusqu'à réparation
├─ Trahison équipage → Événement narratif unique
├─ Malus temporaire → Durée définie
└─ Poursuite ennemie → Jusqu'à combat/fuite
```

**3. Vérification de cohérence avant génération :**

```
Avant de déclencher un événement :
1. Vérifier la position du joueur
2. Vérifier les événements déjà actifs dans la zone
3. Choisir un événement compatible avec le contexte
4. Si flotte générée → Créer entité persistante en BDD
5. Marquer l'événement avec timestamp et durée

Exemple :
- Joueur dans système paisible (zone Empire)
  → Pas de grosse flotte pirate (incohérent)
  → Plutôt : panne, petite patrouille pirate isolée
  
- Joueur dans espace sauvage
  → Flotte pirate cohérente
  → Stockée en BDD avec position et patrouille
```

**4. Recyclage d'événements existants :**

```
Si événement Peur doit se déclencher :
1. Chercher événements actifs près de la position joueur
2. Si événement compatible existe → L'utiliser (rencontre)
3. Sinon → Créer nouvel événement

Exemple :
- Flotte pirate générée tour 15 à Alpha-B4
- Joueur arrive Alpha-B3 au tour 20
- Capital Peur déclenche événement
→ Au lieu de créer nouvelle flotte
→ Utiliser la flotte existante (elle patrouille)
→ "Vous êtes détecté par la flotte pirate !"
```

**Implémentation technique :**

```javascript
// Table base de données
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

// Fonction déclenchement
function checkFearEvent(player) {
  // Jet de fiabilité
  const reliability_die = player.ship.reliability_die; // ex: 60
  const roll = random(1, reliability_die);
  const fear_capital = system.fear_tokens;
  
  if (roll < fear_capital) {
    // Événement déclenché !
    
    // 1. Chercher événements existants proches
    const nearby_events = db.query(`
      SELECT * FROM fear_events 
      WHERE position_system_id = ? 
      AND status = 'active'
      AND expires_turn > ?
    `, [player.system_id, current_turn]);
    
    // 2. Si événement compatible existe, l'utiliser
    if (nearby_events.length > 0) {
      const event = selectCompatibleEvent(nearby_events);
      triggerExistingEvent(player, event);
    } else {
      // 3. Sinon, créer nouvel événement
      const event = generateNewEvent(player, fear_capital);
      
      // 4. Si événement persistant, stocker en BDD
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

// Nettoyage périodique
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
- Dépensés volontairement par le joueur pour :
  - **Relancer les dés** (2d12)
  - **Activer un talent** (à définir ultérieurement)
  - **Obtenir certains effets environnementaux** (à définir)
  - Survivre à la mort (mécanique à définir)

**Commandes :**
```
> check_hope
Jetons d'Espoir disponibles : 3

> use_hope reroll
Jeton d'Espoir dépensé (Reste : 2)
Relance des dés autorisée
```

**Jetons de Peur (ressource système - CACHÉ) :**
- Accumulés automatiquement quand Fear > Hope
- Dépensés automatiquement par le système pour :
  - Déclencher embuscades
  - Activer capacités ennemies
  - Introduire complications (pannes, anomalies)
  - Faire intervenir renforts ennemis
  - Créer événements narratifs
- Le joueur ne voit que les **effets** (pas le compteur)

---

### Exemples d'application

#### Détection d'embuscade
```
Action : Détecter une embuscade pirate à l'approche d'un système
Compétence : Perception (Instinct)
Difficulté : 14 (furtivité ennemie)

Jet : 2d12 + Perception + Bonus détecteurs
- Dé Hope : 7
- Dé Fear : 9
- Perception : +2
- Instinct (trait minimum) : +3
- Bonus détecteurs : +2
→ Résultat : (7 + 9) + 2 + 2 = 20 ≥ 14 = SUCCÈS
(Note : On utilise Perception +2, pas Instinct +3, car Perception > trait minimum)

Mais Fear > Hope (9 > 7) → +1 jeton de Peur (CACHÉ)
→ Embuscade détectée à temps !
→ Mais le système accumule de la Peur...

Affichage console :
> approach asteroid_belt

🎲 Hope: 7  |  Fear: 9
Résultat: (7 + 9) + 2 + 2 = 20
✓ Détection réussie !

⚠️ ALERTE : 3 vaisseaux pirates détectés en embuscade !
Position : 2.1 UA, secteur Gamma
Option : Éviter [2 PA] | Engager combat | Fuir

[Système : +1 Peur stocké]
```

#### Combat spatial
```
Action : Attaquer un vaisseau ennemi
Compétence : Artillerie (Finesse)
Difficulté : Seuil d'Évasion ennemi (10 + Agilité + Armure)

Exemple contre Corvette pirate :
- Seuil d'Évasion : 10 + 2 (Agilité) + 2 (Armure) = 14

Jet : 2d12 + Artillerie + Bonus arme
- Dé Hope : 11
- Dé Fear : 5
- Artillerie : +3
- Finesse (trait minimum) : +4
- Bonus canons : +1
→ Résultat : (11 + 5) + 4 + 1 = 21 ≥ 14 = SUCCÈS
(Note : On utilise Finesse +4, pas Artillerie +3, car trait > compétence)

Hope > Fear (11 > 5) → +1 jeton d'Espoir pour le joueur
→ Touché ! Et le joueur gagne une ressource narrative

Affichage console :
> attack pirate_corvette laser_cannons

🎲 Hope: 11  |  Fear: 5
Résultat: (11 + 5) + 4 + 1 = 21
Défense cible: 14
✓ TOUCHÉ - 32 dégâts infligés

✓ Jeton d'Espoir gagné ! (Total : 3)
Corvette pirate : 68/100 HP
```

#### Saut hyper-espace
```
Action : Saut FTL vers système inconnu
Compétence : Astrogation (Connaissance)
Difficulté : 13 (selon distance/conditions)

Jet : 2d12 + Astrogation + Qualité Drive
- Dé Hope : 6
- Dé Fear : 9
- Astrogation : +4
- Connaissance (trait minimum) : +2
- Drive : +3
→ Résultat : (6 + 9) + 4 + 3 = 22 ≥ 13 = SUCCÈS
(Note : On utilise Astrogation +4, pas Connaissance +2)

Mais Fear > Hope (9 > 6) → +1 jeton de Peur
→ Arrivée réussie, mais complication possible :
  - Déviation mineure de trajectoire
  - Rencontre imprévue
  - Système endommagé par le saut
```

#### Négociation avec guilde
```
Action : Obtenir meilleur prix pour données
Compétence : Négociation (Présence)
Difficulté : 15

Jet : 2d12 + Négociation
- Dé Hope : 10
- Dé Fear : 10
- Négociation : +3
- Présence (trait minimum) : +3
→ Résultat : (10 + 10) + 3 = 23 ≥ 15 = SUCCÈS
(Note : On utilise Négociation +3, égal au trait)

Hope = Fear (10-10) ET ≠ 1-1 → **CRITIQUE !**
→ +1 jeton d'Espoir
→ Négociation exceptionnelle ! Bonus supplémentaire :
  - Prix +25% au lieu de +10%
  - Accès données premium offert
  - Réputation guilde +20 (au lieu de +10)

Affichage console :
> negotiate data_sale cartographers_guild

🎲 Hope: 10  |  Fear: 10
Résultat: (10 + 10) + 3 = 23
✓✓ CRITIQUE ! Réussite exceptionnelle !

✓ Jeton d'Espoir gagné ! (Total : 3)

Prix obtenu : 15 000 cr (+25%)
Bonus : Accès cartes premium débloqué
```

#### Catastrophe (1-1)
```
Action : Réparer système endommagé sous le feu
Compétence : Mécanique (Finesse)
Difficulté : 14

Jet : 2d12 + Mécanique
- Dé Hope : 1
- Dé Fear : 1
- Mécanique : +2
- Finesse (trait minimum) : +4
→ Résultat : (1 + 1) + 4 = 6 < 14 = ÉCHEC
(Note : On utilise Finesse +4, pas Mécanique +2)

Hope = Fear = 1-1 → CATASTROPHE !
→ +1 jeton de Peur (système, caché)
→ Réparation échoue catastrophiquement :
  - Système totalement HS (au lieu de juste endommagé)
  - Surcharge → Dégâts supplémentaires (-15 HP)
  - Temps perdu (2 PA perdus)

Affichage console :
> repair shields

🎲 Hope: 1  |  Fear: 1
Résultat: (1 + 1) + 4 = 6
✗✗ CATASTROPHE !

[Système : +1 Peur stocké]

⚠️ Surcharge critique !
Boucliers : Hors service total
Dégâts : -15 HP coque
PA perdus : 2
```

---

### Adaptation pour le jeu vidéo

**Calculs automatiques :**
- Le serveur lance les 2d12
- Affiche les résultats (Hope: X, Fear: Y)
- Indique succès/échec
- Génère jetons automatiquement

**Affichage console :**
```
> scan_for_threats

Scan de menaces (Instinct +3, Détecteurs +2)...
🎲 Hope: 8  |  Fear: 10
Résultat: (8 + 10) + 3 + 2 = 23
✓ SUCCÈS - Aucune menace immédiate détectée

[Système : +1 Peur accumulé]
(Le joueur ne voit pas cette ligne - Peur caché)

> check_hope
Jetons d'Espoir disponibles : 2

> use_hope navigation_bonus
Jeton d'Espoir dépensé (+2 au prochain jet de navigation)
Jetons restants : 1
```

**Utilisation automatique Peur par le système :**
```
[Joueur fait plusieurs actions avec Fear > Hope]
[Système accumule 5 jetons de Peur]

> jump_hyperspace target_system_gamma

Calcul de saut...
🎲 Hope: 10  |  Fear: 6
✓ Saut réussi

[Système dépense 3 jetons Peur]

⚠️ ÉVÉNEMENT : Sortie d'hyperespace perturbée !
Champ d'astéroïdes non répertorié détecté
Micro-dégâts : -5 HP coque
Position : +0.3 AL de déviation

(Le joueur ne sait pas que c'était causé par les jetons Peur)
```

---

## 🖥️ INTERFACE UTILISATEUR

### Vue d'ensemble

L'interface est composée de **trois zones principales** :
- **Panneau de navigation à gauche** : Système d'onglets thématiques
- **Zone d'affichage centrale** : Informations contextuelles, visualisations
- **Console à droite** : Messages + saisie commandes + boutons raccourcis

**Schéma de layout :**
```
┌──────────────────────────────────────────────────────────────────┐
│  [LOGO/TITRE DU JEU]                          [USER INFO] [PA:10]│
├────────────┬─────────────────────────────┬───────────────────────┤
│            │                             │                       │
│  ONGLETS   │    ZONE D'AFFICHAGE        │  ZONE MESSAGES        │
│  (MENUS)   │    CENTRALE                 │  (Dialogue IA)        │
│            │                             │  ┌─────────────────┐  │
│ ├─ Lieu    │  ┌───────────────────────┐ │  │ > Bienvenue     │  │
│ ├─ Service │  │                       │ │  │ > Scout LI-200  │  │
│ ├─Personnel│  │   COCKPIT / CARTE     │ │  │ > Sol, Terre    │  │
│ └─ Jeu     │  │   RADAR / DONNÉES     │ │  │ > 10 PA dispos  │  │
│            │  │                       │ │  │ ...             │  │
│ Sous-menus:│  │   (Contextuel selon   │ │  │ [Historique]    │  │
│ • Pont     │  │    onglet sélectionné)│ │  └─────────────────┘  │
│ • Soute    │  │                       │ │                       │
│ • Machines │  └───────────────────────┘ │  ZONE SAISIE          │
│ • ...      │                             │  ┌─────────────────┐  │
│            │                             │  │ > _             │  │
│            │                             │  └─────────────────┘  │
│            │                             │  [BOUTONS RACCOURCIS] │
│            │                             │  [Scan][Jump][Attack] │
│            │                             │  [Dock][Trade][Help]  │
└────────────┴─────────────────────────────┴───────────────────────┘
```

---

### 1. Panneau de navigation (Gauche)

**4 Grands thèmes avec sous-menus :**

#### 📍 LIEU
Interaction avec le lieu actuel (Station ou Vaisseau)

```
Lieu
├─ Vaisseau (si dans vaisseau)
│   ├─ Pont
│   ├─ Soute
│   ├─ Quartiers équipage
│   ├─ Salle des machines
│   └─ Systèmes (armement, boucliers, etc.)
│
└─ Station (si amarré/à quai)
    ├─ Hangar / Docks
    ├─ Marché / Commerce
    ├─ Chantier naval (réparations, upgrades)
    ├─ Quartier administratif (missions, guildes)
    ├─ Cantina / Espaces sociaux
    └─ Zones spéciales (selon station)
```

#### 🛠️ SERVICE
Compétences communes et gestion

```
Service
├─ Spatio-carte
│   ├─ Carte galactique
│   ├─ Système actuel (vue détaillée)
│   ├─ Routes connues
│   └─ Points d'intérêt découverts
│
├─ Gestion vaisseaux
│   ├─ Flotte personnelle
│   ├─ Statut / Réparations
│   ├─ Équipements / Upgrades
│   └─ Assignations équipage
│
├─ Gestion bases/mines
│   ├─ Liste installations
│   ├─ Production / Ressources
│   ├─ Personnel assigné
│   └─ Défenses
│
├─ Communication
│   ├─ Messages / Guildes
│   ├─ Marché (offres/demandes)
│   └─ Intel / Rapports
│
└─ Économie
    ├─ Inventaire global
    ├─ Finances
    ├─ Routes commerciales
    └─ Contrats actifs
```

#### 👥 PERSONNEL
Gestion du personnel du vaisseau

```
Personnel
├─ PJ Principal
│   ├─ Fiche personnage
│   ├─ Traits / Compétences
│   ├─ Équipement personnel
│   └─ Historique / Réputation
│
├─ PJ Secondaires (équipage nommé)
│   ├─ Liste équipage
│   ├─ Spécialisations
│   ├─ Affectations postes
│   └─ Moral / État
│
└─ Personnel non-joueur
    ├─ Effectifs (nombre par rôle)
    ├─ Recrutement
    ├─ Formation
    └─ Besoins (alimentation, confort, etc.)
```

#### 🎮 JEU
Méta-jeu et paramètres

```
Jeu
├─ Options
├─ Paramètres
├─ Tutoriel / Aide
├─ Statistiques
├─ Succès / Objectifs
└─ Sauvegarde / Quitter
```

---

### 2. Zone d'affichage centrale

**Affichage contextuel selon l'onglet/sous-menu sélectionné à gauche.**

#### Exemples d'affichages :

**LIEU > Vaisseau > Pont :**
```
┌─────────────────────────────────────┐
│   VUE COCKPIT DU VAISSEAU           │
│                                     │
│   [Schéma 3D ou vue cockpit]        │
│   • Indicateurs HP coque : 85/100   │
│   • Boucliers : 100%                │
│   • Énergie réacteur : 75%          │
│   • Carburant : 450/500             │
│                                     │
│   RADAR LOCAL                       │
│   [Carte 2D locale 360°]            │
│   • Objets détectés dans 5 UA       │
│                                     │
└─────────────────────────────────────┘
```

**SERVICE > Spatio-carte > Système actuel :**
```
┌─────────────────────────────────────┐
│   SYSTÈME SOL                       │
│                                     │
│   [Carte 2D/3D du système]          │
│   ☉ Soleil (centre)                 │
│   • Mercure (0.39 UA)               │
│   • Vénus (0.72 UA)                 │
│   • Terre (1.0 UA) ← VOUS ÊTES ICI  │
│   • Mars (1.52 UA)                  │
│   • Ceinture astéroïdes (2.7 UA)    │
│   • Jupiter (5.2 UA)                │
│   ...                               │
│                                     │
│   Boutons :                         │
│   [Zoom +/-] [Vue 3D] [Routes]      │
└─────────────────────────────────────┘
```

**SERVICE > Gestion vaisseaux :**
```
┌─────────────────────────────────────┐
│   SCOUT LI-200 "Explorateur"        │
│                                     │
│   [Schéma vaisseau avec modules]    │
│                                     │
│   MODULES INSTALLÉS :               │
│   ├─ Détecteurs Mk II (Slot 1)      │
│   ├─ Canons Laser (Slot 2)          │
│   ├─ Boucliers Standard (Slot 3)    │
│   └─ Drive Hyper-espace Mk I        │
│                                     │
│   STATISTIQUES :                    │
│   • HP : 85/100                     │
│   • Cargo : 15/50 unités            │
│   • Équipage : 3/4                  │
│   • Entretien : Bon état            │
│                                     │
│   [Réparer] [Upgrade] [Vendre]      │
└─────────────────────────────────────┘
```

**PERSONNEL > PJ Principal :**
```
┌─────────────────────────────────────┐
│   CAPITAINE JEAN MERCIER            │
│   [Portrait/Avatar]                 │
│                                     │
│   TRAITS :                          │
│   • Agilité : 3                     │
│   • Force : 2                       │
│   • Finesse : 4                     │
│   • Instinct : 5                    │
│   • Présence : 3                    │
│   • Connaissance : 4                │
│                                     │
│   COMPÉTENCES : (à définir)         │
│   • Navigation : 6                  │
│   • Combat : 4                      │
│   • Négociation : 5                 │
│   ...                               │
│                                     │
│   RÉPUTATION :                      │
│   • Empire Terrien : +250 (Ami)     │
│   • Cartographes : +180 (Connu)     │
│   • Pirates : -50 (Méfiant)         │
└─────────────────────────────────────┘
```

**LIEU > Station > Marché :**
```
┌─────────────────────────────────────┐
│   MARCHÉ - STATION ALPHA CENTAURI   │
│                                     │
│   ACHETER :                         │
│   Item              Prix    Stock   │
│   ─────────────────────────────────│
│   Carburant         50cr    1000u   │
│   Pièces détachées  200cr   50u     │
│   Nourriture (std)  10cr    500u    │
│   Munitions laser   150cr   100u    │
│   ...                               │
│                                     │
│   VENDRE :                          │
│   Votre Item        Prix    Qté     │
│   ─────────────────────────────────│
│   Minerai fer       80cr    25u     │
│   Données cartes    500cr   3u      │
│   ...                               │
│                                     │
│   Crédits : 12 450 cr               │
└─────────────────────────────────────┘
```

---

### 3. Console (Droite)

#### Zone Messages (Haut)
- **Dialogue avec l'IA du système**
- Affichage des actions, résultats, événements
- Historique scrollable
- Codes couleur :
  - Blanc : Informations neutres
  - Vert : Succès, gains
  - Jaune : Avertissements
  - Rouge : Dangers, échecs, dégâts
  - Bleu : Communications, messages guildes
  - Violet : Événements spéciaux, critiques

#### Zone Saisie (Milieu)
- **Champ texte pour commandes**
- Commandes introduites par **mot-clé au début**
- Auto-complétion suggérée
- Historique commandes (flèches haut/bas)

#### Boutons Raccourcis (Bas)
**Actions évidentes et standards (à définir précisément ultérieurement)**

Exemples de boutons contextuels :

**En navigation :**
```
[Scan] [Jump] [Dock] [Auto-pilot]
```

**En combat :**
```
[Attack] [Evade] [Target] [Flee]
```

**À la station :**
```
[Trade] [Repair] [Upgrade] [Missions]
```

**Généraux (toujours visibles) :**
```
[Help] [Status] [End Turn]
```

**Exemples de commandes textuelles :**

```
NAVIGATION
> jump [système]          : Saut hyper-espace
> travel [planète]        : Déplacement conventionnel
> scan                    : Scanner zone actuelle
> dock [station]          : Amarrage

COMBAT
> attack [cible] [arme]   : Attaquer
> evade                   : Manœuvre évasive
> target [système]        : Cibler système spécifique
> flee                    : Fuir le combat

COMMERCE
> buy [item] [quantité]   : Acheter
> sell [item] [quantité]  : Vendre
> trade [destination]     : Établir route commerciale
> market                  : Afficher marché local

GESTION
> repair [système]        : Réparer
> upgrade [système]       : Améliorer
> assign [personnel] [poste] : Assigner équipage
> status                  : État vaisseau/équipage

SOCIAL
> contact [faction]       : Contacter
> negotiate               : Négocier
> accept_mission [id]     : Accepter mission
> reputation              : Voir réputations

SYSTÈME
> help [commande]         : Aide
> history                 : Historique actions
> check_hope              : Voir jetons Espoir
> end_turn                : Finir le tour
```

---

### Intégration des 3 zones

**Flux d'interaction :**

1. **Sélection onglet/menu (Gauche)** → Change affichage central
2. **Visualisation/Clic élément (Centre)** → Pré-remplit commande (Droite)
3. **Validation commande (Droite)** → Résultat affiché dans messages (Droite) + mise à jour affichage (Centre)

**Exemple de flux complet :**
```
1. Joueur clique "Service > Spatio-carte > Système actuel" (Gauche)
   → Centre affiche carte du système Sol

2. Joueur clique sur "Mars" dans la carte (Centre)
   → Console pré-remplit : "> travel Mars_"

3. Joueur valide ou modifie et appuie Entrée
   → Message console : "🎲 Navigation réussie, arrivée Mars dans 3 tours"
   → Carte centrale met à jour position
```

**Raccourcis clavier :**
- `Tab` : Focus sur zone saisie
- `Ctrl+H` : Afficher/masquer panneau gauche
- `Ctrl+M` : Afficher/masquer console droite
- `F1` : Aide contextuelle
- `Espace` : End turn

**Responsive :**
- Petits écrans : Panneau gauche se rétracte (icônes)
- Console droite peut se minimiser
- Zone centrale reste prioritaire

---

## 🚀 NAVIGATION ET DÉPLACEMENTS

### Tour par tour
- **1 tour = 1 jour in-game**
- **Points d'Action (PA)** par tour : 10-15 (selon vaisseau/équipage)

### Types de déplacement

#### 1. Hyper-espace (inter-stellaire)
**Caractéristiques :**
- Portée : 3-6 années-lumière par saut
- Direction : Choisie par le joueur (vecteur 3D)
- Puissance : Paramétrable (distance = f(puissance))
- **Imprécision** : Scatter aléatoire autour de la cible
  - Précision = f(qualité drive, calculs navigation)
  
**Arrêts forcés :**
Le saut s'interrompt si obstacle non détecté :
- Champ d'astéroïdes dense
- Gravité planétaire/stellaire
- Anomalie spatiale
- **Pas à la demande du joueur** (sauf abandon saut)

**Coût :**
- Minimum : 3-5 PA pour calculs + saut
- Carburant hyperespace consommé
- Temps de recharge entre sauts

#### 2. Conventionnel (intra-système)
**Caractéristiques :**
- Entre objets d'un même système
- Plus lent mais précis
- Coût : 1-5 PA selon distance

**Vitesses :**
- Subluminique : 0.01-0.3c (fraction vitesse lumière)
- Transit planète → planète : quelques heures à jours

### Phase d'orientation (post-saut)

**Après un saut hyper-espace :**
1. **Scan obligatoire (≥1 PA)** pour déterminer position
2. Calcul fond d'étoiles visible
3. Reconnaissance patterns connus (étoiles brillantes)
4. Triangulation → Position estimée

**Coût variable :**
- 1 PA : Orientation basique (±2 AL d'erreur)
- 2-3 PA : Scan approfondi (±0.5 AL)
- +X PA si zone complexe (nébuleuse, champ dense)

---

## 🔭 DÉTECTION ET EXPLORATION

### Fond d'étoiles dynamique

**Concept :**
Le vaisseau "voit" ce que ses capteurs détectent depuis sa position actuelle.

**Calcul :**
Pour chaque étoile GAIA dans un rayon de X AL :
1. Distance au vaisseau
2. Magnitude apparente depuis cette position
3. Position angulaire relative (RA/Dec)
4. Identifiable si assez brillante

**Rendu visuel :**
- Canvas/WebGL avec points d'étoiles
- Densité réaliste (milliers de points)
- Objets détectés en surbrillance colorée

### Objets détectables

| Type | Couleur UI | Difficulté détection |
|------|------------|---------------------|
| Étoiles identifiées | 🟡 Jaune/Orange | Facile |
| Planètes connues | 🔵 Bleu | Moyen |
| Bases/Stations | 🟢 Vert | Moyen |
| Zones extraction | 🟠 Orange pulsant | Difficile |
| Champs astéroïdes | 🔴 Rouge diffus | Variable |
| **Galaxies lointaines** | 🟣 Violet | **PIÈGE** |

### Piège des galaxies
- Visuellement similaires à étoiles brillantes
- Saut vers galaxie → Échec catastrophique
- Position résultante aléatoire (perdu)
- Amélioration capteurs = distinguer étoiles/galaxies

### Capacités évolutives vaisseau

| Niveau | Détection | Précision navigation |
|--------|-----------|---------------------|
| Base | Étoiles >mag 3 | ±2 AL |
| Amélioré | +Planètes géantes | ±1 AL |
| Avancé | +Astéroïdes, bases | ±0.5 AL |
| Expert | Distingue galaxies | ±0.1 AL |

### Satellites de communication

**Niveaux technologiques :**

```
Niveau 1 : Portée système (0.5 AL)
├─ Couvre 1 système solaire
├─ Cartographie locale
└─ Coût faible, déploiement rapide

Niveau 2 : Portée étendue (1.5 AL)  
├─ Couvre systèmes voisins proches
├─ Crée "corridors sûrs"
└─ Coût moyen

Niveau 3 : Portée longue (3 AL max)
├─ Réseau inter-stellaire
├─ Relais communications
└─ Coût élevé, tech avancée
```

**Bénéfices zone couverte :**
- Pas de scan requis (0 PA économisés)
- Navigation précise (pas d'erreur)
- Alertes temps réel (flottes, événements)
- Calculs hyper-espace optimisés

**Modèle économique satellites :**
```
Propriétaire : Joueur/Guilde
├─ Politique d'accès
│   ├─ Gratuit pour membres guilde
│   ├─ Abonnement pour alliés (X cr/tour)
│   ├─ Paiement à l'usage pour neutres
│   └─ Bloqué pour ennemis
├─ Revenus passifs
└─ Contrôle stratégique (cible militaire)
```

---

## 🛸 VAISSEAUX ET ÉQUIPEMENTS

### Fabricant starter : **Luna Industries**
*Hommage à Lunastars (https://v2.lunastars.net)*

**Slogan :** *"L'espace pour tous"*

**Gamme produits :**
```
Luna Industries
├─ LI-100 "Sparrow" : Shuttle 800 cr
├─ LI-200 "Scout" : Explorateur 2000 cr
├─ LI-250 "Hauler" : Cargo 2500 cr
└─ LI-300 "Interceptor" : Combat léger 3500 cr
```

### Classes de vaisseaux
*Inspiré Star Citizen (robertspaceindustries.com)*

#### 1. EXPLORATION 🔭
```
Taille S (solo) : 1-2 sièges
├─ Détection : 2D8/PA
├─ Vitesse hyper-espace : Standard
├─ Cargo : Minimal
└─ Ex : Scout léger

Taille M (équipage) : 2-6 sièges
├─ Détection : 3D10/PA
├─ Portée : Excellente
├─ Labo analyse : Oui
└─ Ex : Explorateur longue-portée

Taille L (grand équipage) : 6-20 sièges
├─ Détection : 4D12+5/PA
├─ Cartographie avancée
├─ Drones déployables
└─ Ex : Vaisseau reconnaissance
```

#### 2. COMBAT ⚔️
```
Taille S : Chasseur
├─ Détection : 1D6/PA (faible)
├─ Armement : Fort
├─ Manœuvrabilité : Excellente
└─ Rôle : Interception

Taille M : Corvette
├─ Détection : 2D6/PA
├─ Blindage : Bon
├─ Équipage : 4-8
└─ Rôle : Patrouille

Taille L : Frégate/Destroyer
├─ Détection : 2D8/PA
├─ Armement lourd
├─ Hangar : petits vaisseaux
└─ Rôle : Contrôle spatial
```

#### 3. COMMERCE & TRANSPORT 📦
```
Taille M : Cargo léger
├─ Détection : 1D4/PA (minimal)
├─ Soutes : 100-500 unités
├─ Défense : Faible
└─ Économique

Taille L : Cargo lourd
├─ Détection : 1D6/PA
├─ Soutes : 1000-5000 unités
├─ Équipage : 10-30
└─ Rentable longues distances

Taille XL : Transport masse
├─ Détection : 1D4/PA
├─ Soutes : 10 000+ unités
├─ Escorte nécessaire
└─ Lignes commerciales
```

#### 4. RECHERCHE 🔬
```
Taille M : Laboratoire mobile
├─ Détection : 3D8/PA (senseurs scientifiques)
├─ Analyse : Spectrométrie, échantillons
├─ Bonus identification PoV
└─ Rôle : Études planétaires

Taille L : Station recherche mobile
├─ Détection : 4D10/PA
├─ Labs multiples
├─ Stationnement longue durée
└─ Rôle : Recherche approfondie
```

#### 5. CONSTRUCTION 🏗️
```
Taille L : Navire-usine
├─ Détection : 1D6/PA
├─ Fabrique satellites, drones
├─ Extraction ressources basique
└─ Lent mais polyvalent

Taille XL : Constructor capital
├─ Détection : 2D6/PA
├─ Construit stations, bases
├─ Équipage : 50-100
└─ Infrastructure majeure
```

#### 6. DONNÉES & COMMUNICATION 📡
```
Taille S : Relais mobile
├─ Détection : 1D8/PA
├─ Portée com' : 5 AL
├─ Furtivité : Bonne
└─ Espionnage/Intel

Taille M : Vaisseau SIGINT
├─ Détection : 4D6/PA (passif)
├─ Interception communications
├─ Guerre électronique
└─ Militaire spécialisé

Taille L : Hub communication
├─ Détection : 2D8/PA
├─ Portée : 10 AL
├─ Stockage données massif
└─ Nœud réseau guilde
```

#### 7. STATIONS MOBILES 🛰️
```
Taille XL : Station mobile
├─ Détection : 3D8/PA
├─ Hangar : 5-10 petits vaisseaux
├─ Autonomie : mois/années
├─ Équipage : 100-500
└─ Rôle : Base avancée

Taille Capital : Cité-vaisseau
├─ Détection : 4D10+10/PA
├─ Population : 1000-5000
├─ Autosuffisante
├─ Flotte intégrée
└─ Siège guilde/faction
```

### Système d'équipements

**Slots selon taille :**
```
Taille vaisseau = Nombre slots
├─ S : 1-2 slots détection
├─ M : 2-4 slots détection
├─ L : 4-8 slots détection
└─ XL/Capital : 8-16 slots détection
```

**Types détecteurs :**
```
├─ Passif (1 slot) : 1D6, furtif
├─ Actif Standard (1 slot) : 1D8
├─ Longue portée (2 slots) : 1D10
├─ Militaire (2 slots) : 1D12
└─ Scientifique (3 slots) : 2D8 + bonus identification
```

**Synergie :** 
- 3 détecteurs actifs sur M = 3D8/PA cumulés

### Économie vaisseaux

**Prix indicatifs (Archiluminique) :**
```
DÉPART (accessibles tour 1)
├─ Shuttle léger : 500-1000 cr
├─ Scout basique : 1500-2500 cr
├─ Cargo starter : 2000-3000 cr
└─ Chasseur occasion : 3000-4000 cr

PROGRESSION
├─ Vaisseaux M : 10K-50K cr
├─ Vaisseaux L : 100K-500K cr
└─ Capitaux XL : 5M-50M+ cr
```

**Gestion flotte :**
- Achat, vente (40-60% valeur neuve)
- Stockage hangar (10K cr/mois/vaisseau)
- Assurance (5-10% valeur/an, rembourse 80%)
- Amélioration modulaire (slots)

---

## 💰 ÉCONOMIE ET RESSOURCES

### Chaîne de production (3 niveaux)

```
EXTRACTION (sites naturels)
├─ Minerais bruts
├─ Glace/Eau
├─ Gaz
├─ Matière organique
└─ Matière exotique (rare)
    ↓
RAFFINAGE (stations/vaisseaux-usine)
├─ Métaux communs (fer, aluminium)
├─ Métaux rares (titane, platine)
├─ Polymères
├─ Carburants
├─ Composés chimiques
├─ Bio-nutriments
└─ Matériaux exotiques
    ↓
PRODUCTION (usines spécialisées)
├─ Produits SIMPLES (1 composant)
├─ Produits INTERMÉDIAIRES (2-4 composants)
└─ Produits COMPLEXES (5+ composants + intermédiaires)
```

### Domaines économiques

#### 1. ALIMENTATION 🍽️
```
Basique (Simple)
├─ Croquettes nutritives
└─ Coût : 1 cr/unité | Moral : Affecté

Standard (Intermédiaire)
├─ Repas spatiaux
└─ Coût : 5 cr/unité | Moral : Satisfait

Qualité (Complexe)
├─ Unités synthétiseur gourmet
└─ Coût : 20 cr/unité | Moral : Heureux

Luxe (Complexe+)
├─ Repas réels (agriculture spatiale)
└─ Coût : 100+ cr/unité | Moral : Loyal
```

#### 2. ÉNERGIE ⚡
```
├─ Cellules standard (10 jours autonomie)
├─ Batteries haute capacité (30 jours)
└─ Réacteurs fusion (100+ jours)
```

#### 3. ARMEMENT 🔫
```
Simple : Armes légères, munitions
Intermédiaire : Tourelles, missiles, boucliers
Complexe : Lasers militaires, torpilles plasma
```

#### 4. CONFORT 🛏️
```
Minimal : Couchettes | Moral : -10%
Standard : Cabines | Moral : neutre
Luxe : Suites, gravité artificielle | Moral : +20%
```

#### 5. ÉLECTRONIQUE 💻
```
Simple : Circuits, capteurs
Intermédiaire : Ordinateurs, navigation
Complexe : IA tactiques, serveurs
```

#### 6. MÉCANIQUE 🔧
```
Simple : Pièces détachées, outils
Intermédiaire : Moteurs subluminiques
Complexe : Drives hyper-espace
```

#### 7. SANTÉ 🏥
```
Basique : Trousses premiers soins
Standard : Medkits avancés, scanners
Avancé : Régénérateurs tissulaires, nano-médecine
```

#### 8. DIVERTISSEMENT 🎮
```
Minimal : Holovids | Moral : +5%
Standard : Bibliothèques, VR | Moral : +15%
Premium : Holodeck immersif | Moral : +30%
```

#### 9. ÉDUCATION 📚
```
Basique : Manuels | +1% XP équipage
Standard : Simulateurs, IA tuteurs | +3% XP
Avancé : Labs recherche | +5% XP + découvertes
```

#### 10. CONSTRUCTION 🏗️
```
Simple : Structures basiques
Intermédiaire : Stations modulaires
Complexe : Installations capitales
```

#### 11. DONNÉES/INTEL 📊
```
Simple : Cartes basiques
Intermédiaire : Données marché, routes
Complexe : Intel militaire, brevets tech
```

#### 12. LUXE 💎
```
Simple : Souvenirs, art mineur
Intermédiaire : Œuvres d'art, vins
Complexe : Antiquités, artefacts
```

### Système de personnel

**Besoins par production :**
```
├─ Produits Simples : 1-2 ouvriers/usine
├─ Intermédiaires : 5-10 techniciens/usine
└─ Complexes : 20-50 spécialistes/usine
```

**Spécialisations :**
- Mineurs (extraction)
- Ingénieurs (raffinage)
- Techniciens (production simple/intermédiaire)
- Scientifiques (production complexe)
- Médecins (santé)
- Soldats (armement/sécurité)
- Administrateurs (gestion)

**Formule productivité :**
```
Productivité = f(Moral, Éducation, Équipement)
Moral = f(Alimentation, Confort, Santé, Divertissement, Salaire)
```

### Marché de l'information

**PoV découverts = vendables :**

```
Valeur selon :
├─ Rareté (nouveau vs connu)
├─ Type (planète habitable > astéroïde)
├─ Ressources potentielles
├─ Position stratégique
└─ Âge info (fraîche = cher)

Acheteurs :
├─ Guilde Cartographes
│   ├─ Prix standard
│   ├─ Diffusion publique (délai quelques tours)
│   └─ Crédibilité = meilleur prix futur
│
├─ Guildes spécialisées
│   ├─ Mineurs → astéroïdes riches
│   ├─ Militaires → bases ennemies
│   ├─ Scientifiques → anomalies
│   └─ Prix premium, diffusion restreinte
│
└─ Joueurs/Corporations privées
    ├─ Négociation libre
    ├─ Exclusivité totale possible
    └─ Espionnage économique
```

---

## ⚔️ COMBAT ET ZONES DE CONTRÔLE

### Zones de contrôle

#### ZONE 1 : ESPACE EMPIRE (0-100 AL Soleil)
```
Sécurité : HAUTE
├─ Couverture satellite : 100%
├─ Patrouilles militaires : fréquentes
└─ Temps réponse : 1-3 tours

Règles d'engagement :
✓ Attaque pirates (marqués rouge)
✓ Attaque ennemis déclarés Empire
✓ Légitime défense (après 1er tir reçu)
✗ Tir en premier sur civil/neutre
✗ Attaque autorités/police

Infractions → Conséquences :
├─ Tir illégal : Amende + réputation -50
├─ Meurtre civil : Prison + réputation -200 + bounty
└─ Attaque autorité : Wanted permanent + flotte
```

#### ZONE 2 : ESPACE COLONISÉ
```
Sécurité : MOYENNE
├─ Couverture satellite : 60-80%
├─ Patrouilles : occasionnelles
└─ Temps réponse : 5-10 tours

Règles : Similaires Empire, application variable
```

#### ZONE 3 : FRONTIÈRE
```
Sécurité : FAIBLE
├─ Couverture satellite : 20-40%
├─ Patrouilles : rares
└─ Temps réponse : 20+ tours

Règles : Loi du plus fort
Détection : Peu probable
```

#### ZONE 4 : ESPACE SAUVAGE
```
Sécurité : NULLE
├─ Couverture satellite : 0%
├─ Patrouilles : inexistantes
└─ Pas d'autorité

Règles :
├─ AUCUNE loi
├─ PvP libre
├─ Ressources riches
└─ Détection : Impossible
```

### Système de combat (tour par tour)

**Initiative : Le Projecteur (Spotlight)**

Daggerheart n'utilise **PAS d'initiative traditionnelle.**

```
Système du Projecteur (adapté pour jeu solo/multi) :

1. TOUR JOUEUR(S)
   ├─ Joueur actif (a le Projecteur)
   │   ├─ Effectue ses actions (dépense PA)
   │   └─ Si multi-joueurs : passe Projecteur à allié
   │
   └─ Fin tour joueur(s)

2. TOUR SYSTÈME/IA
   ├─ Active les adversaires (1d20 pour leurs actions)
   ├─ Utilise automatiquement jetons de Peur (cachés) :
   │   ├─ Activer ennemi supplémentaire (1 jeton)
   │   ├─ Capacité spéciale ennemie (1-3 jetons)
   │   ├─ Renforcer attaque (1 jeton = +1d6 dégâts)
   │   └─ Introduire complication (2 jetons)
   │
   └─ Projecteur retourne au(x) joueur(s)

Cycle continu jusqu'à fin combat (fuite/reddition/destruction)
```

**Adaptation selon mode de jeu :**
- **Solo/PvE** : Alternance automatique joueur → système
- **Multi/Coop** : Joueurs se passent Projecteur → système
- **PvP Asynchrone** : Système gère le combat avec comportements prédéfinis

---

### Combat PvP Asynchrone

**Problématique :**
Les joueurs ne sont pas connectés en même temps. Quand un joueur A attaque un joueur B absent, le système doit gérer automatiquement la défense du joueur B.

**Solution : Comportements de combat prédéfinis**

Chaque joueur définit des **règles de comportement** pour son vaisseau/flotte :

```
COMPORTEMENT DE DÉFENSE (à développer)
├─ Stratégie : Offensive / Défensive / Fuite
├─ Seuil de fuite : % HP restants (ex: fuir si <30% HP)
├─ Priorités de cible : Plus proche / Plus faible / Plus dangereux
├─ Utilisation capacités : Conservateur / Agressif
└─ Gestion PA : Attaque prioritaire / Défense prioritaire

COMPORTEMENT D'ATTAQUE (à développer)
├─ Approche : Frontal / Flanc / Distance
├─ Sélection armes : Selon distance / Selon cible
├─ Utilisation jetons Espoir : Jamais / Si critique / Toujours
└─ Condition de désengagement : Jamais / Si dégâts lourds

SEUILS DE FUITE AUTOMATIQUE
├─ HP < X% : Fuite immédiate
├─ Adversaires > Y : Fuite si en infériorité numérique
├─ Systèmes critiques HS : Fuite si moteurs/armes détruites
└─ Objectif atteint : Se retirer après mission accomplie
```

**Résolution d'un combat asynchrone :**

1. Joueur A initie l'attaque contre joueur B (absent)
2. Le système charge les comportements prédéfinis de B
3. Combat simulé tour par tour selon les règles :
   - Jet de dés pour chaque action (2d12 + compétence)
   - Application des comportements de défense de B
   - Vérification seuils de fuite
4. Résultat enregistré (victoire/défaite/fuite)
5. Joueur B reçoit rapport à sa prochaine connexion

**Note :** Les détails mécaniques du combat asymétrique sont à développer ultérieurement.

**Phases d'un tour (joueur avec Projecteur) :**

```
├─ Phase 1 : MOUVEMENT (coût PA)
│   ├─ Rapprocher/Éloigner (1-3 PA)
│   ├─ Manœuvre évasive (2 PA, +défense)
│   └─ Interception (3 PA, bloque fuite)
│
├─ Phase 2 : ACTIONS (PA restants)
│   ├─ Attaque arme (coût variable)
│   ├─ Scan ennemi (1 PA)
│   ├─ Contremesures (2 PA)
│   ├─ Réparations urgentes (3 PA)
│   ├─ Utiliser jeton Espoir (bonus/capacité)
│   └─ Charger armes lourdes (variable)
│
└─ Phase 3 : RÉSOLUTION
    ├─ Calcul dégâts
    ├─ Check systèmes endommagés
    ├─ Génération jetons (Hope/Fear)
    └─ Moral équipage
```

**Mécanique attaque/défense :**

```
Attaque (système 2D12 Daggerheart) :
├─ Jet : 2d12 + Trait (Force/Finesse) + Bonus arme
├─ SOMME des deux dés + modificateurs
├─ Comparer au Seuil d'Évasion cible
│   └─ Seuil Évasion = 10 + Agilité cible + Armure cible
├─ ≥ Seuil : Touché
├─ < Seuil : Raté
│
└─ Génération jetons (dés individuels) :
    ├─ Hope > Fear : +1 jeton Espoir (visible, joueur)
    └─ Fear > Hope : +1 jeton Peur (caché, système)

Exemple d'attaque :
> attack pirate_corvette cannon_laser

Cible : Corvette pirate
- Seuil d'Évasion : 10 + 2 (Agilité) + 2 (Armure) = 14

🎲 Hope: 9  |  Fear: 11
Finesse +4, Canons +2
Résultat: (9 + 11) + 4 + 2 = 26
Défense cible: 14
✓ TOUCHÉ - 25 dégâts

[Système : +1 Peur stocké - Fear > Hope]
→ Le système accumule de la Peur...
   Peut déclencher : renfort, manœuvre risquée, etc.

Types d'armes :
├─ Canons laser (2 PA, moyenne portée, précis)
├─ Missiles (3 PA, longue portée, contrable)
├─ Railgun (4 PA, énorme dégâts, lent)
├─ Torpilles (5 PA, dégâts zone, anti-capital)
└─ EMP (3 PA, désactive systèmes)

Défense :
├─ Seuil Évasion = 10 + Agilité + Armure
├─ Blindage (absorbe X dégâts/tour)
├─ Boucliers (PA rechargeable)
└─ Manœuvres actives (coût PA, +bonus temporaire)
```

**Dommages & Systèmes :**

```
Points de Coque (HP) :
├─ S : 50-100 HP
├─ M : 150-300 HP
├─ L : 400-800 HP
├─ XL : 1000-2000 HP
└─ Capital : 5000+ HP

Dégâts critiques (% HP restant) :
├─ 75% : Système -25% efficacité
├─ 50% : Fuite, -1 PA/tour
├─ 25% : Système majeur HS
├─ 0% : Destruction OU reddition

Systèmes ciblables (si scan réussi) :
├─ Moteurs (immobilise)
├─ Armes (désarme)
├─ Senseurs (aveugle)
├─ Générateur (coupe boucliers)
└─ Pont (moral équipage)
```

### Types ennemis PNJ

```
Pirates solitaires (S-M)
├─ IA : Opportuniste
├─ Fuit si <40% HP
└─ Butin : Moyen

Gangs pirates (3-6 vaisseaux)
├─ IA : Coordonnée
├─ Fuit si leader détruit
└─ Butin : Bon

Cartels (10+ vaisseaux + base)
├─ IA : Tactique
├─ Renforts si base attaquée
└─ Butin : Excellent

Renégats/Mercenaires
├─ IA : Variable selon contrat
├─ Équipement militaire
└─ Ne fuit jamais si payé

Autorités (Police/Militaire)
├─ IA : Légaliste
├─ Scan d'abord
└─ Appel renforts si perdant
```

---

## 🏛️ RÉPUTATION ET FACTIONS

### Système de réputation

**Par guilde/faction indépendante :**

```
Paliers réputation (0-25000+ pts) :
├─ Étranger (0) : Accès basique
├─ Connu (500) : -5% prix, missions Rang 1
├─ Ami (2000) : -10% prix, missions Rang 2, accès données
├─ Respecté (5000) : -15% prix, missions Rang 3, équipements spéciaux
├─ Honoré (10000) : -20% prix, missions Rang 4, blueprints rares
└─ Légende (25000) : -25% prix, missions uniques, siège guilde ?

Réputation négative possible (ennemi)
```

**Guildes principales :**

```
GUILDES IMPÉRIALES (IA au début)
├─ Empire Terrien (militaire/admin)
├─ Guilde des Cartographes
├─ Guilde des Marchands
├─ Académie Scientifique
└─ Autres selon univers

GUILDES JOUEURS (créables)
├─ Gérées par joueurs
├─ Peuvent définir propres règles
├─ Commerce, exploration, militaire, etc.
└─ Territoires contrôlés
```

**Actions affectant réputation :**

```
├─ Tuer pirate : Empire +10, Pirates -20
├─ Tuer civil neutre : Toutes factions -100
├─ Mission guilde réussie : +50-200 pts
├─ Trahison contrat : -200-500 pts
├─ Partage données : Cartographes +5-50
└─ Commerce régulier : +1-5 pts/transaction
```

---

## 🌌 GÉNÉRATION PROCÉDURALE

### Sources de données réelles

**Bases exploitables :**

1. **ESA GAIA** (Étoiles)
   - 1.8+ milliards d'étoiles cartographiées
   - Positions 3D, distances, mouvements
   - Type spectral, magnitude, température
   - API TAP accessible

2. **NASA Exoplanet Archive** (Planètes connues)
   - 29 000+ exoplanètes confirmées
   - Masse, rayon, période orbitale
   - Distance étoile hôte
   - API REST/TAP

3. **JPL Small-Body Database** (Astéroïdes/Comètes)
   - Tous astéroïdes/comètes système solaire
   - Paramètres orbitaux
   - Composition physique
   - API JSON

4. **JPL Horizons** (Éphémérides)
   - Positions précises temps réel
   - Planètes, lunes, astéroïdes
   - Calculs orbitaux
   - API REST

### Algorithme de génération systèmes

**Principe :**
```
Seed = GAIA source_id de l'étoile
→ Génération reproductible identique pour tous
```

**Budget de masse :**
```
Budget = Masse_étoile (M☉) × 50 unités

Exemple :
- 1 M☉ (type Soleil) = 50 unités
- 0.5 M☉ (naine rouge) = 25 unités
- 2 M☉ (type A) = 100 unités
```

### Profils stellaires

#### TYPE O/B (Géantes bleues) - 15-60 M☉
```
Budget : 80-200 unités
Zone habitable : 50-100 UA (trop lointaine)
Génération :
├─ 30% : Géante gazeuse massive (50-80 u)
├─ 40% : Ceintures astéroïdes épaisses (10-20 u)
├─ 20% : Planètes rocheuses irradiées (5-15 u)
└─ 10% : Vide
Intérêt : Minéral riche, dangereux
```

#### TYPE A (Blanches) - 1.4-2.1 M☉
```
Budget : 40-80 unités
Zone habitable : 4-10 UA
Génération :
├─ 40% : Géante gazeuse (30-50 u)
├─ 30% : Telluriques intérieures (10-20 u)
├─ 20% : Ceintures (5-15 u)
└─ 10% : Mini-système
Intérêt : Commerce, bases militaires
```

#### TYPE F (Jaune-blanc) - 1.04-1.4 M☉
```
Budget : 35-60 unités
Zone habitable : 1.5-3 UA
Génération équilibrée :
├─ 35% : 1-2 gazeuses (20-40 u)
├─ 40% : 2-4 telluriques (15-30 u)
├─ 15% : Ceinture (5-10 u)
└─ 10% : Lunes multiples
Intérêt : Colonies potentielles
```

#### TYPE G (Solaire) - 0.8-1.04 M☉
```
Budget : 30-50 unités
Zone habitable : 0.9-1.5 UA ⭐ OPTIMAL
Génération type Système Solaire :
├─ 25% : 1-2 gazeuses extérieures (15-30 u)
├─ 45% : 3-5 telluriques (12-25 u)
├─ 20% : Ceinture astéroïdes (5-10 u)
└─ 10% : Système riche lunes
Intérêt : COLONISATION PRIORITAIRE
```

#### TYPE K (Orange) - 0.45-0.8 M☉
```
Budget : 20-40 unités
Zone habitable : 0.3-0.9 UA (proche)
Génération compacte :
├─ 30% : 1 gazeuse moyenne (10-20 u)
├─ 50% : 2-4 telluriques rapprochées (10-25 u)
├─ 15% : Ceinture fine (3-8 u)
└─ 5% : Système pauvre
Intérêt : Stable, longue durée vie
```

#### TYPE M (Naine rouge) - 0.08-0.45 M☉
```
Budget : 10-30 unités
Zone habitable : 0.05-0.3 UA (très proche)
Génération minimaliste :
├─ 20% : 1 petite gazeuse (5-15 u)
├─ 60% : 1-3 telluriques verrouillées (5-20 u)
├─ 15% : Astéroïdes épars (2-5 u)
└─ 5% : Vide
Intérêt : Nombreuses, verrouillage gravitationnel
```

#### NAINES BRUNES - 0.01-0.08 M☉
```
Budget : 5-15 unités
Pas de zone habitable
Génération rare :
├─ 40% : 1-2 planètes errantes capturées
└─ 60% : Vide
Intérêt : Cachettes, bases clandestines
```

### Coûts budgétaires par objet

```
GÉANTES GAZEUSES
├─ Super-Jupiter (>10 MJ) : 40-60 u + 2D6 lunes
├─ Jupiter (1-10 MJ) : 25-40 u + 2D4 lunes
└─ Neptune (0.1-1 MJ) : 15-25 u + 1D4 lunes

PLANÈTES TELLURIQUES
├─ Super-Terre (>2 MT) : 12-20 u + 1D3 lunes
├─ Terrestre (0.5-2 MT) : 8-15 u + 1D2 lunes
└─ Mars-like (<0.5 MT) : 5-10 u + 0-2 lunes

LUNES
├─ Majeure (Titan/Ganymède) : 3-8 u
├─ Standard (Lune) : 2-5 u
└─ Petite : 1-3 u

CEINTURES ASTÉROÏDES
├─ Dense : 10-20 u (2D6 sites extraction)
├─ Moyenne : 5-10 u (1D6 sites)
└─ Éparse : 2-5 u (1D3 sites)

OBJETS SPÉCIAUX
├─ Planète océan : 10-18 u
├─ Planète désertique : 8-14 u
├─ Monde glacé : 6-12 u
├─ Planète volcanique : 7-13 u
└─ Planète morte : 4-8 u
```

### Implantations humaines

**Zones spatiales (<200 AL Soleil) :**

```
ZONE 1 : CŒUR EMPIRE (0-100 AL)
├─ Densité : Forte (80% systèmes G/K colonisés)
├─ Contrôle : Empire Terrien centralisé
├─ Population : ~50 milliards
├─ Sécurité : Haute
└─ Systèmes majeurs :
    ├─ Sol (Terre) : 10 milliards
    ├─ Alpha Centauri : 5 milliards
    └─ 50-100 autres colonies

ZONE 2 : FRONTIÈRE COLONIALE (100-150 AL)
├─ Densité : Moyenne (40% habités)
├─ Contrôle : Mixte (Gouverneurs + Guildes)
├─ Population : ~10 milliards
├─ Sécurité : Moyenne à faible
└─ Colonies indépendantes

ZONE 3 : ESPACE PIONNIER (150-200 AL)
├─ Densité : Faible (10% habités)
├─ Contrôle : Factions, Guildes, Cartels
├─ Population : ~1 milliard
├─ Sécurité : Nulle
└─ Avant-postes isolés

AU-DELÀ 200 AL : TERRA INCOGNITA
├─ Quelques éclaireurs
├─ Bases secrètes (rumeurs)
└─ Futur contenu (aliens ?)
```

**Génération colonies :**

```
Pour planète habitable :
├─ Roll 1D100 selon distance Terre :
│   ├─ 0-50 AL : 80% colonisée
│   ├─ 50-100 AL : 60% colonisée
│   ├─ 100-150 AL : 30% colonisée
│   ├─ 150-200 AL : 10% colonisée
│   └─ >200 AL : 2% colonisée
│
└─ Si colonisée, taille (1D100) :
    ├─ 1-20 : Avant-poste (100-1000)
    ├─ 21-50 : Petite colonie (1K-50K)
    ├─ 51-80 : Colonie établie (50K-1M)
    ├─ 81-95 : Monde mineur (1M-100M)
    └─ 96-100 : Monde majeur (100M-5B)
```

---

## 📍 POINTS D'INTÉRÊT (PoV)

### Définition
**Point of Value (PoV)** : Tout objet/entité que la base de données doit stocker car il a une valeur (stratégique, économique, scientifique).

### Types de PoV

#### PoV HYPERSPATIAUX (>1 AL, entre systèmes)
```
Naturels :
├─ Étoiles (GAIA source principale)
├─ Naines brunes
├─ Nuages interstellaires
└─ Trous noirs vagabonds (très rare)

Artificiels :
├─ Stations relais lointaines
├─ Vaisseaux en transit
├─ Balises navigation
└─ Champs de mines (pièges)
```

#### PoV LOCAUX (dans système, <1 AL)
```
Naturels :
├─ Planètes (telluriques, gazeuses)
├─ Lunes
├─ Ceintures astéroïdes
├─ Comètes
└─ Anomalies (nuages, champs magnétiques)

Artificiels :
├─ Bases (actives, abandonnées, ruines)
├─ Satellites/Stations
├─ Mines/Extraction
├─ Épaves vaisseaux
└─ Balises/Relais
```

### Système de détection PoV

**Mécanique en 2 phases :**

#### PHASE 1 : ACCUMULATION
```
Chaque tour en mode détection : +XDY (selon équipement)
Somme cumulée : S
Condition : S ≥ Valeur_Recherche du PoV
Si atteint → Passage Phase 2
```

#### PHASE 2 : RÉSOLUTION
```
1 jet de dés (1D% ou 2D12)
Seuil selon type objet :
├─ Passif (planète, épave) : 90%+ succès
├─ Actif furtif (base camouflée) : 50%
└─ Contremesures actives : 20%

Succès → PoV détecté et révélé au joueur
```

### Valeurs de recherche (exemples)

| Objet | Valeur Recherche | Notes |
|-------|------------------|-------|
| Étoile | 5-15 | Selon magnitude |
| Planète géante | 20-30 | Proche = facile |
| Planète tellurique | 40-60 | Petite, sombre |
| Lune | 60-80 | Très petite |
| Ceinture astéroïdes | 30-50 | Zone étendue |
| Base active | 50-100 | Émissions + contremesures |
| Base fantôme | 100-200 | Contremesures militaires |
| Épave | 80-120 | Passive, petite |
| Station relais | 30-60 | Émissions fortes |

### Équipement détection

```
Niveau Base : 1D6/PA
Niveau Amélioré : 2D6/PA
Niveau Avancé : 3D6/PA
Niveau Militaire : 4D6+bonus/PA

Modificateurs :
├─ +bonus si zone cartographiée
├─ -malus si brouillage actif
└─ +bonus si intel préalable
```

### Marché de l'information

**PoV découverts = vendables :**

```
Acheteurs :
├─ Guilde Cartographes
│   └─ Diffusion publique (quelques tours délai)
│
├─ Guildes spécialisées
│   └─ Diffusion restreinte (membres)
│
└─ Joueurs/Corporations privées
    └─ Exclusivité totale possible
```

**Valeur dépend de :**
- Rareté (nouveau système vs déjà connu)
- Type (planète habitable > astéroïde banal)
- Ressources potentielles
- Position stratégique
- Fraîcheur de l'information

---

## 💻 ARCHITECTURE TECHNIQUE

### Stack technique

```
FRONTEND
├─ HTML/CSS/JavaScript
├─ Interface console (commandes texte)
├─ Canvas/WebGL (visualisation optionnelle)
└─ Framework : Vanilla JS ou React/Vue léger

BACKEND
├─ Node.js + Express (API REST)
├─ Python (optionnel, pour scripts GAIA)
└─ WebSocket (temps réel si nécessaire)

BASE DE DONNÉES
├─ MySQL/PostgreSQL (principal)
├─ IndexedDB (cache client-side)
└─ Redis (cache serveur, sessions)

APIs EXTERNES
├─ ESA GAIA TAP (étoiles)
├─ NASA Exoplanet Archive (planètes)
├─ JPL Horizons (éphémérides)
└─ JPL Small-Body DB (astéroïdes)
```

### Tables base de données (schéma indicatif)

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

-- SYSTÈMES STELLAIRES
systems (
    id, gaia_source_id,
    ra, dec, distance_ly,
    spectral_type, mass_solar,
    explored, discovered_by_player_id,
    discovery_turn,
    control_faction_id
)

-- PLANÈTES/OBJETS
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

-- POINTS D'INTÉRÊT
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

-- ÉVÉNEMENTS PEUR (persistance et cohérence narrative)
fear_events (
    id, 
    type (pirate_fleet/anomaly/disaster/etc),
    position_system_id,
    position_x, position_y, position_z,
    created_turn,
    expires_turn,
    status (active/expired/destroyed),
    data_json (détails spécifiques événement),
    fear_cost_consumed
)

-- RÉPUTATION
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

-- MARCHÉ
market_listings (
    id, seller_id, item_type,
    quantity, price_per_unit,
    location_system_id
)
```

### Workflow requêtes GAIA

```
1. Joueur entre en zone inexplorée
2. Backend vérifie si système en DB
3. Si non :
   a. Requête GAIA TAP (étoile + voisines)
   b. Parse données (type, masse, position)
   c. Génération procédurale système (seed = source_id)
   d. Stockage DB
4. Si oui : Chargement depuis DB
5. Retour données au client
```

**Exemple requête GAIA (ADQL) :**

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
- Systèmes explorés stockés DB
- Fond d'étoiles pré-calculé par position
- Éphémérides planètes calculées à la demande

**Calculs distribués :**
- Génération procédurale côté serveur
- Rendu visuel côté client
- WebWorkers pour calculs lourds

**Pagination données :**
- Chargement zone par zone
- Pas de chargement galaxie entière
- Secteurs de 10-50 AL

---

## 📝 NOTES DE DÉVELOPPEMENT

### Priorités Phase 1 (MVP)
1. ✅ Système navigation tour par tour
2. ✅ Détection basique (1D6)
3. ✅ Génération procédurale simple (étoiles GAIA)
4. ✅ Combat PvE basique
5. ✅ Économie simplifiée (3-5 ressources)
6. ✅ Interface console fonctionnelle

### Phase 2 (Expansion)
- Système réputation complet
- Guildes joueurs
- Génération planètes avancée
- Marché dynamique
- PvP

### Phase 3 (Endgame)
- Stations mobiles
- Construction
- Diplomatie complexe
- Événements galactiques
- Multi-univers (Star Wars, etc.)

### Système de dés à finaliser
**Système retenu : Daggerheart (2D12)**

**Caractéristiques finales :**
- Somme des 2d12 + modificateurs vs Difficulté
- Jetons d'Espoir (visibles, ressource joueur)
- Jetons de Peur (CACHÉS, capital système)
- Système utilise automatiquement Peur pour générer aventure

**Implémentation technique :**
```javascript
// Jet de dés
function rollDaggerheart(trait, bonus, difficulty) {
  const hope = random(1, 12);
  const fear = random(1, 12);
  const result = hope + fear + trait + bonus;
  
  let critical = false;
  
  // Génération jetons
  if (hope > fear) {
    player.hope_tokens++;
    notifyPlayer("✓ Jeton d'Espoir gagné !");
  } else if (fear > hope) {
    system.fear_tokens++; // CACHÉ
    // Note : Déclenchement vérifié séparément
  } else if (hope === fear && hope !== 1) {
    // CRITIQUE ! (égalité sauf 1-1)
    player.hope_tokens++;
    critical = true;
    notifyPlayer("✓✓ CRITIQUE ! Réussite exceptionnelle !");
  } else if (hope === 1 && fear === 1) {
    // CATASTROPHE (1-1) : génère Peur
    system.fear_tokens++; // CACHÉ
    critical = "catastrophe";
    notifyPlayer("✗✗ CATASTROPHE !");
  }
  
  return {
    success: result >= difficulty,
    result: result,
    hope: hope,
    fear: fear,
    critical: critical
  };
}

// Vérification déclenchement Peur (après action significative ou fin tour)
function checkFearTrigger(player) {
  // Fiabilité selon état vaisseau
  const reliability_die = player.ship.reliability_die; // ex: 60
  const roll = random(1, reliability_die);
  const fear_capital = system.fear_tokens;
  
  if (roll < fear_capital) {
    // ÉVÉNEMENT DÉCLENCHÉ !
    
    // 1. Chercher événements existants proches
    const nearby = findNearbyFearEvents(player);
    
    // 2. Utiliser événement existant ou créer nouveau
    const event = nearby.length > 0 
      ? selectCompatibleEvent(nearby, fear_capital)
      : generateNewFearEvent(player, fear_capital);
    
    // 3. Si événement persistant, stocker en BDD
    if (event.persistent) {
      storeFearEvent(event, player);
    }
    
    // 4. Déclencher l'événement
    triggerFearEvent(player, event);
    
    // 5. Consommer jetons Peur
    system.fear_tokens -= event.fear_cost;
    
    return true;
  }
  
  return false;
}

// Recherche événements Peur proches
function findNearbyFearEvents(player) {
  return db.query(`
    SELECT * FROM fear_events 
    WHERE position_system_id = ? 
    AND status = 'active'
    AND expires_turn > ?
    ORDER BY created_turn ASC
  `, [player.system_id, current_turn]);
}

// Génération nouvel événement selon capital Peur
function generateNewFearEvent(player, fear_capital) {
  let event_type, fear_cost, duration, persistent;
  
  if (fear_capital >= 20) {
    // Événement majeur
    event_type = selectRandom(['fleet', 'disaster', 'faction_intervention']);
    fear_cost = random(20, 30);
    duration = random(30, 100);
    persistent = true;
  } else if (fear_capital >= 11) {
    // Événement critique
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

// Nettoyage événements expirés (chaque tour)
function cleanupExpiredFearEvents() {
  db.query(`
    UPDATE fear_events 
    SET status = 'expired' 
    WHERE expires_turn < ? AND status = 'active'
  `, [current_turn]);
}
```

### Références
- **Lunastars** : https://v2.lunastars.net
- **Empire Galactique (JDR)** : https://jeuderole.empiregalactique.site
- **Star Citizen** : https://robertsspaceindustries.com
- **GAIA Archive** : https://gea.esac.esa.int/archive/
- **NASA Exoplanet Archive** : https://exoplanetarchive.ipac.caltech.edu/

---

## 🔄 CHANGELOG & ÉVOLUTIONS

### Version 0.1 (Document initial)
- Concepts core définis
- Architecture modulaire multi-univers
- Système 2D12 Daggerheart proposé
- Luna Industries nommée
- Génération procédurale complète

### À valider/modifier
- [ ] Balance économique (prix vaisseaux, ressources)
- [ ] Système de dés final (2D12 vs D20)
- [ ] Noms factions/guildes Archiluminique
- [ ] Contenu aliens (Phase future)
- [ ] Règles PvP détaillées

---

**Document vivant - Dernière mise à jour : 2025-10-30**

