# Système Temporel du Jeu

## Date de Référence

**Date de début de la conquête spatiale : 1er janvier 3000 à 00:00**

Toutes les dates du jeu sont exprimées à partir de cette référence.

---

## Points d'Action (PA)

### Concept de base
- **1 PA = 0.5 jour de jeu** (12 heures in-game)
- Les PA permettent au joueur d'effectuer des actions (déplacements, constructions, combats, etc.)

### Régénération des PA

#### Mode Production
- **1 PA se régénère toutes les 1 heure réelle**
- Maximum de PA par joueur : configurable (par défaut 24 PA)

#### Mode Test/Développement
- **1 PA se régénère toutes les 3 heures réelles**
- **1 PA = 0.5 heure de jeu** (au lieu de 0.5 jour)
- Permet de tester plus rapidement les mécaniques temporelles

---

## Mécanique Temporelle

### Principe : Le temps ne s'écoule QUE si le joueur a saturé ses PA

Le temps du jeu n'avance **pas en temps réel**. Il n'avance que lorsque le joueur a **consommé tous ses PA disponibles**.

### Exemple Détaillé

**Situation initiale :**
- Joueur connecté le : **18 janvier 3000 à 12:00** (date in-game)
- PA disponibles : **24 PA**
- PA dépensés : **3 PA**
- PA restants : **21 PA**
- Heure réelle de connexion : **14:00**

**Scénario 1 : Retour avant saturation des PA**
- Le joueur revient à **16:00** (2h réelles écoulées)
- PA régénérés : **2 PA** (1 PA/heure)
- PA totaux : 21 + 2 = **23 PA**
- **Date in-game : toujours 18 janvier 3000 à 12:00** ❌ Temps figé
- Le joueur reprend là où il en était

**Scénario 2 : Retour avant saturation complète (17h réelles)**
- Le joueur revient à **07:00 le lendemain** (17h réelles écoulées)
- PA régénérés : **17 PA**
- PA totaux : 21 + 17 = **24 PA** (max atteint, 14 PA perdus)
- **Date in-game : toujours 18 janvier 3000 à 12:00** ❌ Temps figé
- Le joueur n'a pas consommé tous ses PA avant de partir

**Scénario 3 : Retour après saturation complète**
- Le joueur dépense **tous ses 24 PA** avant de partir
- PA restants : **0 PA**
- Il revient **25 heures réelles plus tard**
- PA régénérés : **24 PA** (max)
- Temps réel écoulé : 25h - 24h (pour remplir les PA) = **1h supplémentaire**
- **Date in-game avance de : 1h × 0.5 jour = 0.5 jour = 12h**
- **Nouvelle date : 19 janvier 3000 à 00:00** ✅ Temps avancé

### Formule de Calcul

```
SI pa_restants > 0 ALORS
    // Le temps n'avance pas
    date_retour = date_derniere_connexion
SINON
    // Le joueur a tout dépensé
    heures_reelles_ecoulees = (timestamp_actuel - timestamp_derniere_connexion) / 3600
    heures_pour_remplir_pa = pa_max

    SI heures_reelles_ecoulees > heures_pour_remplir_pa ALORS
        heures_surplus = heures_reelles_ecoulees - heures_pour_remplir_pa
        jours_jeu_ecoules = heures_surplus × 0.5 jours
        date_retour = date_derniere_connexion + jours_jeu_ecoules
    SINON
        date_retour = date_derniere_connexion
    FIN SI
FIN SI
```

---

## Événements Temporels

### Trace des Actions

Tous les événements du jeu doivent être tracés avec un **timestamp in-game** :

- **Déplacements** : timestamp de départ et d'arrivée
- **Messages COM générale** : timestamp d'envoi
- **Rencontres** : timestamp de la rencontre
- **Constructions** : timestamp de début et de fin
- **Combats** : timestamp du combat
- **Transactions commerciales** : timestamp de la transaction

### Affichage des Événements

Les événements doivent apparaître **progressivement** en fonction du temps écoulé :

**Exemple :**
- Joueur part en mission à **18 jan 3000 12:00**
- Mission prend **2 jours in-game** (4 PA)
- Joueur dépense les 4 PA
- **Date d'arrivée calculée : 20 jan 3000 12:00**

Si le joueur se reconnecte :
- **Avant que le temps n'ait avancé** → Mission en cours, afficher ETA
- **Après que le temps ait avancé au-delà de la date d'arrivée** → Mission terminée, afficher résultat

---

## Tables Nécessaires

### `personnages`
- `derniere_connexion` : timestamp in-game
- `pa_actuels` : nombre de PA disponibles
- `pa_max` : maximum de PA (par défaut 24)
- `timestamp_derniere_action` : timestamp réel de la dernière action

### `evenements`
- `personnage_id`
- `type` : déplacement, message, rencontre, combat, etc.
- `timestamp_jeu` : date in-game de l'événement
- `timestamp_reel` : date réelle de création
- `donnees` : JSON avec les détails de l'événement

### `deplacements`
- `personnage_id`
- `vaisseau_id`
- `depart_timestamp` : date in-game de départ
- `arrivee_timestamp` : date in-game d'arrivée prévue
- `origine_x`, `origine_y`, `origine_z`
- `destination_x`, `destination_y`, `destination_z`
- `statut` : en_cours, arrive, annule

---

## Configuration

### Fichier `config/game.php`

```php
return [
    'temps' => [
        // Date de début de la conquête spatiale
        'date_debut' => '3000-01-01 00:00:00',

        // Mode de jeu
        'mode' => env('GAME_MODE', 'production'), // production ou test

        // Configuration PA
        'pa' => [
            'max_defaut' => 24,

            // Production
            'regeneration_heures_production' => 1, // 1 PA toutes les 1h réelles
            'duree_jeu_par_pa_production' => 0.5,  // 1 PA = 0.5 jour in-game

            // Test
            'regeneration_heures_test' => 3,        // 1 PA toutes les 3h réelles
            'duree_jeu_par_pa_test' => 0.5 / 24,    // 1 PA = 0.5h in-game (0.5/24 jour)
        ],
    ],
];
```

---

## Notes d'Implémentation

### À faire plus tard
- [ ] Système de traçage des événements avec timestamps
- [ ] Calcul automatique de l'avancement du temps in-game
- [ ] Affichage progressif des événements selon le temps écoulé
- [ ] Gestion des rencontres/combats avec timestamps
- [ ] Système de messagerie COM avec horodatage
- [ ] Notifications des événements survenus pendant l'absence du joueur

### Priorités
1. Implémenter le système de PA et régénération
2. Calculer l'avancement du temps selon les PA dépensés
3. Tracer les déplacements avec timestamps
4. Afficher les événements en fonction du temps écoulé

---

## Exemples de Gameplay

### Joueur Actif (reste connecté)
- Se connecte avec 24 PA
- Dépense 10 PA en 2h réelles
- Régénère 2 PA
- Total : 16 PA disponibles
- **Temps in-game : figé** (pas encore saturé ses PA initiaux)

### Joueur Occasionnel
- Se connecte avec 24 PA
- Dépense 24 PA en 1h réelle
- Part, revient 48h plus tard
- PA régénérés : 24 PA (max)
- Temps surplus : 48h - 24h = 24h
- **Temps in-game avancé de : 24h × 0.5j = 12 jours**

### Joueur Hardcore (saturé en permanence)
- Utilise toujours tous ses PA
- Revient exactement toutes les 24h
- **Temps in-game avance exactement de : 0 jour** (pas de surplus)
- Maximise son efficacité

---

## Avantages de ce Système

✅ **Équitable** : Joueurs actifs et occasionnels peuvent progresser
✅ **Anti-farming** : Impossible de laisser tourner en AFK
✅ **Stratégique** : Le joueur doit gérer ses PA intelligemment
✅ **Immersif** : Le temps du jeu a du sens
✅ **Scalable** : Fonctionne quel que soit le nombre de joueurs
