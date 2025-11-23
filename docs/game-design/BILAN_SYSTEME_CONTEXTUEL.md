# 📊 BILAN - SYSTÈME DE MENU CONTEXTUEL ET LOCALISATION

**Date**: 2025-11-23  
**Version**: Phase 1 - Fondations  
**Statut**: ✅ Architecture complète, 🚧 Vues en développement

---

## 🎯 Vue d'ensemble

Le système de menu contextuel et de localisation permet une interface utilisateur qui s'adapte automatiquement selon la position du personnage dans l'univers du jeu. Ce système est au cœur de l'expérience de jeu et définit quelles actions sont disponibles selon le contexte.

---

## ✅ RÉALISATIONS - PHASE 1 (FONDATIONS)

### 1. Architecture de base

#### Helper PersonnageLocation (`app/Helpers/PersonnageLocation.php`)
**Statut**: ✅ **COMPLET**

Fonctionnalités implémentées:
- ✅ Détection automatique de la localisation du personnage
- ✅ Support des types: vaisseau, station, navette, planète
- ✅ Support des états: amarré, en orbite, en déplacement, à la surface
- ✅ Méthodes de contrôle d'accès:
  - `estDansVaisseau()`
  - `estDansStation()`
  - `peutAccederMarchePhysique()`
  - `peutAccederDonneesMarche()`
  - `peutAccederCombat()`
- ✅ Génération dynamique des sections de menu selon localisation
- ✅ Formatage des coordonnées (secteur + position absolue)
- ✅ Description textuelle de la localisation

#### View Composer (`app/View/Composers/PersonnageLocationComposer.php`)
**Statut**: ✅ **COMPLET**

- ✅ Injection automatique des données de localisation dans toutes les vues
- ✅ Variables injectées: `$personnageLocation`, `$menuSections`
- ✅ Enregistré pour `layouts.app` et `game.*`

#### Middleware RequiresLocation (`app/Http/Middleware/RequiresLocation.php`)
**Statut**: ✅ **COMPLET**

- ✅ Restriction d'accès basée sur la localisation
- ✅ Support des modes: `vaisseau`, `station`, `marche-physique`, `combat`
- ✅ Réponses JSON pour requêtes AJAX
- ✅ Page d'erreur dédiée pour requêtes standard
- ✅ Enregistré comme alias `requires.location`

### 2. Interface utilisateur

#### Dashboard contextuel (`resources/views/game/dashboard.blade.php`)
**Statut**: ✅ **COMPLET**

- ✅ Menu de gauche dynamique basé sur la localisation
- ✅ Affichage de la position actuelle (type + coordonnées)
- ✅ Fonction `loadView()` pour chargement AJAX dans panneau principal
- ✅ Support des paramètres de requête pour la carte
- ✅ Gestion des scripts pour vues dynamiques

#### Composant menu contextuel (`resources/views/components/contextual-menu.blade.php`)
**Statut**: ✅ **CRÉÉ** (non encore utilisé dans le dashboard actuel)

- ✅ Composant réutilisable
- ✅ Affichage des informations de localisation
- ✅ Génération automatique des sections de menu
- ⏳ À intégrer dans le dashboard pour remplacer l'implémentation directe

#### Intégration de la carte
**Statut**: ✅ **COMPLET**

- ✅ Carte chargée dans le panneau principal du dashboard
- ✅ Vue partielle `carte-content.blade.php` pour AJAX
- ✅ Détection AJAX dans `GameController::carte()`
- ✅ Navigation dans la carte via AJAX (zoom, changement de plan)
- ✅ Tous les scripts exécutés correctement après chargement AJAX

### 3. Contrôleurs et routes

#### VaisseauController (`app/Http/Controllers/VaisseauController.php`)
**Statut**: ✅ **COMPLET**

Routes implémentées:
- ✅ `/vaisseau/position` - Affichage position détaillée
- ✅ `/vaisseau/scanner` - Scanner (placeholder)
- ✅ `/vaisseau/etat` - État du vaisseau (placeholder)
- ✅ `/vaisseau/reparations` - Réparations (placeholder)
- ✅ `/vaisseau/cargaison` - Cargaison (placeholder)
- ✅ `/vaisseau/armes` - Armes embarquées (placeholder)
- ✅ `/inventaire` - Inventaire personnel (placeholder)

Toutes les méthodes:
- ✅ Supportent les requêtes AJAX
- ✅ Vérifient la présence du personnage
- ✅ Retournent des vues partielles pour AJAX

#### ComController (`app/Http/Controllers/ComController.php`)
**Statut**: ✅ **COMPLET**

Routes implémentées:
- ✅ `/com/databases` - Bases de données (placeholder)
- ✅ `/com/prix` - Prix des marchés (placeholder)
- ✅ `/com/demandes` - Demandes stations (placeholder)
- ✅ `/com/messages` - Messages sous-réseaux (placeholder)

Toutes les méthodes:
- ✅ Supportent les requêtes AJAX
- ✅ Vérifient la présence du personnage
- ✅ Retournent des vues partielles pour AJAX

### 4. Vues

#### Vues Vaisseau - Timonerie
- ✅ `position.blade.php` - **FONCTIONNELLE** avec données réelles
- 🚧 `scanner.blade.php` - **PLACEHOLDER**

#### Vues Vaisseau - Ingénierie
- 🚧 `etat.blade.php` - **PLACEHOLDER**
- 🚧 `reparations.blade.php` - **PLACEHOLDER**

#### Vues Vaisseau - Soute
- 🚧 `inventaire.blade.php` - **PLACEHOLDER**
- 🚧 `cargaison.blade.php` - **PLACEHOLDER**

#### Vues Vaisseau - Armement
- 🚧 `armes.blade.php` - **PLACEHOLDER**

#### Vues COM
- 🚧 `databases.blade.php` - **PLACEHOLDER**
- 🚧 `prix.blade.php` - **PLACEHOLDER**
- 🚧 `demandes.blade.php` - **PLACEHOLDER**
- 🚧 `messages.blade.php` - **PLACEHOLDER**

---

## 🚧 PHASE 2 - IMPLÉMENTATION DES VUES

### Priorité HAUTE - Timonerie

#### Scanner
**Fichier**: `resources/views/game/vaisseau/partials/scanner.blade.php`

**Objectifs**:
- [ ] Afficher les objets spatiaux à proximité
- [ ] Détection des stations
- [ ] Détection des vaisseaux
- [ ] Détection des planètes
- [ ] Détection des anomalies/PoI
- [ ] Calcul des distances
- [ ] Filtres par type d'objet
- [ ] Tri par distance

**Logique métier à implémenter**:
- [ ] Requête pour récupérer objets dans un rayon donné
- [ ] Calcul de distance 3D entre objets spatiaux
- [ ] Vérification si objets découverts (via `decouvertes`)
- [ ] Niveau de détail selon distance et puissance du scanner

**Modèles concernés**:
- `ObjetSpatial` (position des objets)
- `SystemeStellaire` (systèmes)
- `Planete` (planètes)
- `Station` (stations)
- `Vaisseau` (autres vaisseaux si multiplayer)

---

### Priorité HAUTE - Soute

#### Inventaire Personnel
**Fichier**: `resources/views/game/vaisseau/partials/inventaire.blade.php`

**Objectifs**:
- [ ] Lister les objets du personnage
- [ ] Catégoriser par type (armes, équipement, consommables, objets)
- [ ] Afficher poids total / capacité
- [ ] Actions: Utiliser, Équiper, Jeter, Transférer
- [ ] Détails au survol (tooltips)

**Logique métier à implémenter**:
- [ ] Relation `Personnage → items` (table pivot `personnage_items`)
- [ ] Gestion du poids et de la capacité de transport
- [ ] Actions d'utilisation d'objets
- [ ] Système d'équipement

**Tables à créer**:
- [ ] `items` (définition des objets)
- [ ] `personnage_items` (inventaire)

#### Cargaison du Vaisseau
**Fichier**: `resources/views/game/vaisseau/partials/cargaison.blade.php`

**Objectifs**:
- [ ] Lister les ressources transportées
- [ ] Afficher quantité / capacité max
- [ ] Grouper par type de ressource
- [ ] Actions: Jeter, Transférer vers inventaire personnel
- [ ] Indicateur de surcharge

**Logique métier à implémenter**:
- [ ] Relation `Vaisseau → ressources` (table pivot `vaisseau_cargaison`)
- [ ] Calcul de l'espace utilisé vs capacité
- [ ] Transfert ressources ↔ inventaire personnel
- [ ] Largage de ressources

**Tables à créer**:
- [ ] `vaisseau_cargaison` (ressources transportées)

---

### Priorité MOYENNE - Ingénierie

#### État du Vaisseau
**Fichier**: `resources/views/game/vaisseau/partials/etat.blade.php`

**Objectifs**:
- [ ] Afficher intégrité coque (%)
- [ ] Afficher état boucliers (%)
- [ ] Afficher énergie disponible
- [ ] État des systèmes (propulsion, armes, vie, senseurs)
- [ ] Indicateurs visuels (barres de progression, alertes)
- [ ] Alertes si systèmes critiques

**Logique métier à implémenter**:
- [ ] Attributs `Vaisseau`: `coque_actuelle`, `coque_max`, `boucliers_actuels`, `boucliers_max`
- [ ] Attributs `Vaisseau`: `energie_actuelle`, `energie_max`
- [ ] Table `vaisseau_systemes` pour état détaillé des sous-systèmes
- [ ] Calcul des dégâts et réparations

**Migration à créer**:
```php
Schema::table('vaisseaux', function (Blueprint $table) {
    $table->integer('coque_actuelle')->default(100);
    $table->integer('coque_max')->default(100);
    $table->integer('boucliers_actuels')->default(0);
    $table->integer('boucliers_max')->default(0);
    $table->integer('energie_actuelle')->default(100);
    $table->integer('energie_max')->default(100);
});
```

#### Réparations
**Fichier**: `resources/views/game/vaisseau/partials/reparations.blade.php`

**Objectifs**:
- [ ] Lister composants endommagés
- [ ] Afficher coût réparation (ressources + temps)
- [ ] Bouton "Réparer" (si ressources disponibles)
- [ ] Progression réparation en cours
- [ ] Réparation automatique dans station (optionnel)

**Logique métier à implémenter**:
- [ ] Système de dommages par composant
- [ ] Calcul coût réparation (matériaux + temps)
- [ ] File d'attente de réparations
- [ ] Commande `reparer <composant>`

---

### Priorité MOYENNE - Armement

#### Armes Embarquées
**Fichier**: `resources/views/game/vaisseau/partials/armes.blade.php`

**Objectifs**:
- [ ] Lister armes installées sur le vaisseau
- [ ] Afficher munitions restantes par arme
- [ ] Afficher état de l'arme (opérationnelle, endommagée)
- [ ] Bouton "Tester" (tir à blanc)
- [ ] Installation/désinstallation d'armes

**Logique métier à implémenter**:
- [ ] Relation `Vaisseau → armes` (table pivot `vaisseau_armes`)
- [ ] Modèle `Arme` avec types (laser, missile, torpille, etc.)
- [ ] Gestion munitions par arme
- [ ] Slots d'armes limités par modèle de vaisseau

**Tables à créer**:
- [ ] `armes` (définition des armes)
- [ ] `vaisseau_armes` (armes installées)

---

### Priorité HAUTE - COM (Communications)

#### Bases de Données
**Fichier**: `resources/views/game/com/partials/databases.blade.php`

**Objectifs**:
- [ ] Lister stations à proximité (rayon COM)
- [ ] Afficher informations: nom, faction, services
- [ ] Afficher ressources disponibles à la vente
- [ ] Distance et direction
- [ ] Bouton "Mettre le cap"

**Logique métier à implémenter**:
- [ ] Calcul rayon COM selon équipement vaisseau
- [ ] Requête stations dans rayon
- [ ] Accès aux données publiques des stations
- [ ] Interface avec système de marché

#### Prix des Marchés
**Fichier**: `resources/views/game/com/partials/prix.blade.php`

**Objectifs**:
- [ ] Tableau des ressources avec prix dans stations proches
- [ ] Colonnes: Ressource | Station | Prix Achat | Prix Vente | Distance
- [ ] Tri par prix, distance, ressource
- [ ] Mise en évidence des opportunités (écarts importants)
- [ ] Rafraîchissement périodique

**Logique métier à implémenter**:
- [ ] Table `marche_prix` (prix dynamiques par station/ressource)
- [ ] Calcul des opportunités commerciales
- [ ] Actualisation des prix (système économique)

**Tables à créer**:
- [ ] `marche_prix` (station_id, ressource_id, prix_achat, prix_vente, stock, timestamp)

#### Demandes des Stations
**Fichier**: `resources/views/game/com/partials/demandes.blade.php`

**Objectifs**:
- [ ] Lister demandes actives des stations
- [ ] Afficher: Ressource demandée | Quantité | Prix offert | Station
- [ ] Filtrer par type de demande (urgent, standard)
- [ ] Bouton "Accepter contrat" (si ressources en cargaison)

**Logique métier à implémenter**:
- [ ] Table `station_demandes` (besoins des stations)
- [ ] Système de contrats/missions
- [ ] Calcul récompenses selon distance/urgence

**Tables à créer**:
- [ ] `station_demandes` (station_id, ressource_id, quantite, prix_offert, urgence, expiration)
- [ ] `contrats` (personnage_id, demande_id, statut, date_acceptation)

#### Messages et Sous-Réseaux
**Fichier**: `resources/views/game/com/partials/messages.blade.php`

**Objectifs**:
- [ ] Boîte de réception des messages
- [ ] Liste des sous-réseaux disponibles (publics/payants)
- [ ] Bouton "S'abonner" pour sous-réseaux payants
- [ ] Envoyer un message
- [ ] Messages de faction/guilde
- [ ] Annonces commerciales

**Logique métier à implémenter**:
- [ ] Table `messages` (messagerie inter-joueurs)
- [ ] Table `sous_reseaux` (canaux de communication)
- [ ] Table `abonnements` (accès aux sous-réseaux payants)
- [ ] Système de modération

**Tables à créer**:
- [ ] `messages` (expediteur_id, destinataire_id, sujet, contenu, lu, date)
- [ ] `sous_reseaux` (nom, description, prix_abonnement, public)
- [ ] `abonnements` (personnage_id, sous_reseau_id, date_debut, date_fin)

---

## 🔮 PHASE 3 - FONCTIONNALITÉS AVANCÉES

### Système de Combat
**Statut**: ❌ **NON COMMENCÉ**

**Contrôleur à créer**: `CombatController.php`

**Vues à créer**:
- [ ] `game/combat/armes.blade.php` - Catalogue d'armes personnelles
- [ ] `game/combat/equipement.blade.php` - Armures et équipement

**Logique**:
- [ ] Combat au sol (personnel)
- [ ] Combat spatial (vaisseau)
- [ ] Système de statistiques (vie, armure, esquive)
- [ ] Équipement d'armes/armures
- [ ] Combats contre IA ou autres joueurs

**Tables à créer**:
- [ ] `armes_personnelles`
- [ ] `armures`
- [ ] `personnage_equipement`
- [ ] `combats` (historique)

---

### Système de Marché (Station)
**Statut**: ❌ **NON COMMENCÉ**

**Contrôleur à créer**: `MarcheController.php`

**Vues à créer**:
- [ ] `game/station/marche.blade.php` - Interface marché physique

**Logique**:
- [ ] Achat/vente ressources dans station
- [ ] Prix dynamiques selon offre/demande
- [ ] Stock limité par station
- [ ] Transactions sécurisées
- [ ] Historique des transactions

**Middleware à appliquer**:
```php
Route::get('/marche', [MarcheController::class, 'index'])
    ->middleware('requires.location:station');
```

---

### Système de Déplacement
**Statut**: ❌ **NON COMMENCÉ**

**Objectifs**:
- [ ] Déplacement vaisseau dans secteur
- [ ] Déplacement inter-secteurs (saut)
- [ ] Calcul consommation carburant
- [ ] Trajectoires optimales
- [ ] Interception possible par autres joueurs
- [ ] Zones dangereuses (pirates, anomalies)

**Commandes à implémter**:
- [ ] `cap <x> <y> <z>` - Définir destination
- [ ] `deplacer` - Lancer le déplacement
- [ ] `arreter` - Arrêter déplacement
- [ ] `saut <secteur_x> <secteur_y> <secteur_z>` - Saut FTL

---

### Système de Stations (Menu Station)
**Statut**: ❌ **NON COMMENCÉ**

**Contrôleur à créer**: `StationController.php`

**Vues à créer**:
- [ ] `game/station/dashboard.blade.php` - Tableau de bord station
- [ ] `game/station/quitter.blade.php` - Embarquer dans vaisseau
- [ ] `game/station/missions.blade.php` - Missions disponibles

**Logique**:
- [ ] Embarquer/débarquer du vaisseau
- [ ] Accès aux services de la station
- [ ] Missions et contrats locaux
- [ ] Réputation avec faction de la station

---

### Système de Missions
**Statut**: ❌ **NON COMMENCÉ**

**Contrôleur à créer**: `MissionController.php`

**Objectifs**:
- [ ] Génération de missions procédurales
- [ ] Types: Transport, Exploration, Élimination, Collecte
- [ ] Récompenses: Crédits, Réputation, Objets
- [ ] Chaînes de missions
- [ ] Missions de faction

**Tables à créer**:
- [ ] `missions` (définition)
- [ ] `personnage_missions` (missions actives/complétées)
- [ ] `objectifs_mission` (étapes mission)

---

## 📋 TODO LIST CONSOLIDÉE

### TODO - IMMÉDIAT (Phase 2)

#### Architecture
- [ ] Créer migrations pour attributs vaisseau (coque, boucliers, énergie)
- [ ] Créer table `vaisseau_cargaison`
- [ ] Créer table `items` et `personnage_items`
- [ ] Créer table `marche_prix`
- [ ] Créer table `station_demandes`

#### Vues Vaisseau
- [ ] Implémenter vue Scanner avec détection objets à proximité
- [ ] Implémenter vue État du Vaisseau avec données réelles
- [ ] Implémenter vue Cargaison avec gestion ressources
- [ ] Implémenter vue Inventaire avec gestion items personnage

#### Vues COM
- [ ] Implémenter vue Bases de Données avec stations à proximité
- [ ] Implémenter vue Prix des Marchés avec tableau comparatif
- [ ] Implémenter vue Demandes avec contrats disponibles

#### Commandes
- [ ] Ajouter commandes de gestion inventaire (`utiliser`, `equiper`, `jeter`)
- [ ] Ajouter commandes de gestion cargaison (`charger`, `decharger`, `jeter`)
- [ ] Ajouter commande `scanner` (alias vers vue scanner)

---

### TODO - COURT TERME

#### Système Économique
- [ ] Créer modèle `MarchePrix` avec relations
- [ ] Implémenter fluctuation des prix (cron job)
- [ ] Créer système d'offre/demande dynamique
- [ ] Implémenter marché physique dans stations

#### Système de Messagerie
- [ ] Créer tables `messages`, `sous_reseaux`, `abonnements`
- [ ] Créer `MessageController`
- [ ] Implémenter envoi/réception messages
- [ ] Implémenter système d'abonnement sous-réseaux

#### Interface Station
- [ ] Créer menu contextuel pour localisation "station"
- [ ] Implémenter vue Marché (achat/vente physique)
- [ ] Implémenter vue Missions
- [ ] Implémenter action "Quitter station" (embarquer vaisseau)

---

### TODO - MOYEN TERME (Phase 3)

#### Combat
- [ ] Créer système de combat personnel
- [ ] Créer système de combat spatial
- [ ] Implémenter armes personnelles et armures
- [ ] Implémenter armes embarquées sur vaisseau

#### Déplacement
- [ ] Implémenter déplacement dans secteur
- [ ] Implémenter sauts FTL inter-secteurs
- [ ] Calculer consommation carburant
- [ ] Gérer collisions et interceptions

#### Stations & Bases
- [ ] Permettre création de bases personnelles
- [ ] Connecter mines MAME aux bases
- [ ] Gérer transfert automatique ressources mine → base
- [ ] Implémenter défense des bases

---

### TODO - LONG TERME (Phase 4+)

#### Multiplayer
- [ ] Détection autres joueurs dans secteur
- [ ] Combat PvP
- [ ] Commerce entre joueurs
- [ ] Alliances et factions

#### Économie Avancée
- [ ] Marché galactique (agrégation prix)
- [ ] Économie joueur (production → vente)
- [ ] Routes commerciales automatiques

#### Exploration
- [ ] Génération procédurale systèmes
- [ ] Anomalies spatiales (événements aléatoires)
- [ ] Artefacts et technologies anciennes
- [ ] Terraformation planètes

---

## 🔧 CONCEPTS NON FINALISÉS

### 1. Système de Temps Réel vs Tour par Tour
**Statut**: ⚠️ **À DÉFINIR**

**Questions**:
- Le déplacement est-il en temps réel ou au tour?
- Les réparations prennent-elles du temps réel?
- Les combats sont-ils instantanés ou progressifs?

**Impact**: Affecte toute la conception des mécaniques de jeu.

---

### 2. Rayon d'Action du Scanner et COM
**Statut**: ⚠️ **À DÉFINIR**

**Paramètres à définir**:
- Rayon de détection du scanner (en AL)
- Rayon du réseau COM (en AL)
- Dégradation des informations selon distance
- Amélioration possible avec équipement

**Proposition**:
- Scanner: 5 AL par défaut, jusqu'à 20 AL avec équipement
- COM: 10 AL par défaut, jusqu'à 50 AL avec équipement

---

### 3. Capacité de Transport
**Statut**: ⚠️ **À DÉFINIR**

**Questions**:
- Poids vs Volume (ou les deux)?
- Limitation inventaire personnel? (actuellement illimité)
- Pénalités si surcharge?

**Proposition**:
- Inventaire personnel: 50 kg max
- Cargaison vaisseau: Variable selon modèle (100-10000 unités)
- Surcharge: Réduction vitesse déplacement

---

### 4. Système de Carburant/Énergie
**Statut**: ⚠️ **À IMPLÉMENTER**

**Actuellement**:
- Vaisseau a attribut `energie_actuelle` prévu mais non utilisé
- Pas de consommation pour déplacement
- Pas de ravitaillement nécessaire

**À implémenter**:
- [ ] Consommation énergie pour déplacement
- [ ] Consommation énergie pour scanner/COM
- [ ] Consommation munitions/énergie pour armes
- [ ] Stations de ravitaillement
- [ ] Panne sèche = immobilisation

---

### 5. Système de Faction/Réputation
**Statut**: ⚠️ **NON COMMENCÉ**

**Concept**:
- Réputation par faction (hostile, neutre, amical, allié)
- Influence accès stations, prix, missions
- Gain/perte réputation selon actions

**Tables à créer**:
- [ ] `factions` (nom, description, zone_influence)
- [ ] `personnage_reputations` (personnage_id, faction_id, niveau)

---

### 6. Systèmes de Compétences
**Statut**: ⚠️ **NON COMMENCÉ**

**Concept**:
- Compétences de pilotage (vitesse, manœuvrabilité)
- Compétences techniques (réparation, scanner)
- Compétences commerciales (négociation prix)
- Compétences combat (précision, dégâts)

**Progression**:
- XP par action
- Niveaux par compétence
- Arbres de compétences

---

## 📈 MÉTRIQUES DE PROGRESSION

### Phase 1 (Fondations) - TERMINÉE ✅
- Architecture: **100%** ✅
- Routes & Contrôleurs: **100%** ✅
- Vues (structure): **100%** ✅
- Vues (contenu): **15%** 🚧 (1/7 fonctionnelle)

### Phase 2 (Vues & Logique Métier) - EN COURS 🚧
- Migrations: **0%** ❌
- Modèles: **30%** 🚧 (Vaisseau, ObjetSpatial existants)
- Vues Vaisseau: **14%** 🚧 (1/7)
- Vues COM: **0%** ❌
- Système Économique: **0%** ❌

### Phase 3 (Fonctionnalités Avancées) - NON COMMENCÉE ❌
- Combat: **0%**
- Déplacement: **0%**
- Stations: **0%**
- Missions: **0%**

### Phase 4+ (Multiplayer & Avancé) - NON COMMENCÉE ❌
- Multiplayer: **0%**
- Économie avancée: **0%**
- Exploration procédurale: **0%**

---

## 🎯 PRIORITÉS RECOMMANDÉES

### Sprint 1 (Immédiat)
1. ✅ Scanner fonctionnel
2. ✅ Cargaison fonctionnelle
3. ✅ Inventaire fonctionnel
4. ✅ Prix des marchés (COM) fonctionnel

**Objectif**: Interface complète pour vaisseau, permettant navigation et gestion basique.

### Sprint 2
1. ✅ État du vaisseau
2. ✅ Système de réparation
3. ✅ Marché physique (station)
4. ✅ Embarquer/débarquer vaisseau

**Objectif**: Interaction complète avec stations, gestion maintenance vaisseau.

### Sprint 3
1. ✅ Déplacement basique (dans secteur)
2. ✅ Consommation énergie
3. ✅ Système de temps
4. ✅ Demandes stations (COM)

**Objectif**: Mobilité et premier cycle économique (transport ressources).

### Sprint 4
1. ✅ Système de missions simple
2. ✅ Messages/communication
3. ✅ Armes embarquées
4. ✅ Combat spatial basique

**Objectif**: Boucle de gameplay complète (exploration → missions → combat → récompenses).

---

## 📝 NOTES IMPORTANTES

### Architecture Actuelle - Points Forts
- ✅ Séparation claire des responsabilités (Helper, Controller, View)
- ✅ Support AJAX intégré dès le départ
- ✅ Middleware réutilisable pour restrictions de localisation
- ✅ View Composer pour injection automatique des données
- ✅ Structure évolutive et modulaire

### Architecture Actuelle - Points d'Attention
- ⚠️ Beaucoup de vues en placeholder (risque d'oubli)
- ⚠️ Pas encore de tests unitaires
- ⚠️ Certaines mécaniques de jeu non définies
- ⚠️ Pas de documentation API pour développeurs externes

### Recommandations
1. **Tests**: Créer tests unitaires pour `PersonnageLocation` et middleware
2. **Documentation**: Documenter API des contrôleurs pour futures extensions
3. **Validation**: Définir les mécaniques de jeu avant d'implémenter
4. **Refactoring**: Utiliser le composant `contextual-menu.blade.php` dans le dashboard

---

**Dernière mise à jour**: 2025-11-23  
**Auteur**: Claude (Assistant IA)  
**Statut du document**: 📘 Actif - À mettre à jour après chaque sprint
