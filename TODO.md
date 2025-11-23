# 📋 TODO - Conquête Spatiale

**Date**: 2025-11-23
**Version**: 2.0 - Plan Global Consolidé
**Références**:
- Détails techniques: `docs/BILAN_TECHNIQUE.md`
- Système contextuel: `docs/game-design/BILAN_SYSTEME_CONTEXTUEL.md`
- Plan initial: `docs/TODO_PROJET.md`

---

## ✅ RÉALISATIONS MAJEURES

### Infrastructure & Architecture (100% ✅)
- ✅ Laravel 12 + MariaDB + Eloquent ORM
- ✅ 26 tables de base de données avec relations
- ✅ Système d'authentification Sanctum
- ✅ Middleware de localisation contextuelle
- ✅ View Composer pour injection automatique
- ✅ Architecture MVC modulaire

### Interface & Navigation (90% ✅)
- ✅ Dashboard 3 panneaux avec chargement AJAX
- ✅ Menu contextuel dynamique (selon localisation)
- ✅ Carte interactive secteur avec zoom SVG
- ✅ Carte joueur (systèmes découverts uniquement)
- ✅ Système de commandes texte (35+ commandes)
- ✅ Page sélection/création personnage améliorée

### Systèmes de Jeu (80% ✅)
- ✅ **Univers**: Génération procédurale + GAIA (hybrid mode)
- ✅ **Navigation**: Déplacement, scanner systèmes, découvertes
- ✅ **Économie**: 21 ressources, 11 recettes, marchés, gisements
- ✅ **Extraction**: Système mines MAME avec admin interface
- ✅ **Commerce**: Achat/vente, prix dynamiques, inventaires
- ✅ **Combat PvE**: 10 types ennemis, armes, boucliers, tactiques IA
- ✅ **Temps**: Système temporel (voir `SYSTEME_TEMPOREL.md`)
- ✅ **Points d'Action**: Récupération automatique, coûts actions

### Données & Seeders (100% ✅)
- ✅ RessourceSeeder (21 ressources)
- ✅ RecetteSeeder (11 recettes fabrication)
- ✅ EquipementSeeder (11 armes, 7 boucliers)
- ✅ EnnemiSeeder (10 types avec IA)
- ✅ UniverseSeeder (Sol + systèmes voisins)
- ✅ GaiaSeeder (données ESA GAIA réelles)

---

## 🔥 PRIORITÉ IMMÉDIATE - Sprint Vues & Logique

### Vues Vaisseau (Timonerie)
- [ ] **Scanner** - Détecter objets spatiaux à proximité
  - Liste stations, planètes, vaisseaux, anomalies
  - Calcul distances 3D (formule euclidienne)
  - Filtres par type, tri par distance
  - Rayon de détection (5-20 AL selon équipement)
  - Backend: Query objets dans rayon + relation `decouvertes`

- [ ] **Position détaillée** - AMÉLIORER vue existante
  - ✅ Vue de base créée (`vaisseau/partials/position.blade.php`)
  - [ ] Ajouter navigation vers objets proches
  - [ ] Ajouter bouton "Mettre le cap"
  - [ ] Afficher historique déplacements récents

### Vues Vaisseau (Soute)
- [ ] **Cargaison** - Gestion ressources transportées
  - Liste ressources avec quantité/capacité max
  - Indicateur surcharge (pénalités vitesse)
  - Actions: Charger, Décharger, Jeter
  - Migration: `vaisseau_cargaison` (vaisseau_id, ressource_id, quantite)
  - Capacité selon modèle vaisseau (100-10000 unités)

- [ ] **Inventaire Personnel** - Gestion items
  - Liste items par catégorie (armes, équipement, consommables)
  - Affichage poids total / capacité (50 kg max)
  - Actions: Utiliser, Équiper, Jeter, Transférer
  - Migrations: `items`, `personnage_items` (pivot)
  - Système équipement (armes/armures personnelles)

### Vues Vaisseau (Ingénierie)
- [ ] **État Vaisseau** - Monitoring systèmes
  - Intégrité coque (%) avec barre progression
  - État boucliers (%) avec type équipé
  - Énergie disponible / max
  - État sous-systèmes (propulsion, armes, vie, senseurs)
  - Alertes si systèmes critiques (<25%)
  - Migration: Ajouter colonnes `coque_actuelle`, `boucliers_actuels`, `energie_actuelle` à `vaisseaux`

- [ ] **Réparations** - Interface maintenance
  - Liste composants endommagés avec %
  - Coût réparation (ressources + temps)
  - Bouton "Réparer" (si ressources dispo)
  - File d'attente réparations
  - Réparation auto dans station (optionnel)

### Vues COM (Communications)
- [ ] **Prix des Marchés** - Tableau comparatif
  - Colonnes: Ressource | Station | Prix Achat | Prix Vente | Distance
  - Tri multi-critères (prix, distance, ressource)
  - Mise en évidence opportunités (écarts >50%)
  - Rayon COM: 10-50 AL selon équipement
  - Migration: `marche_prix` (station_id, ressource_id, prix_achat, prix_vente, stock, updated_at)

- [ ] **Bases de Données** - Stations à proximité
  - Liste stations dans rayon COM
  - Info: Nom, faction, services, distance
  - Ressources disponibles à la vente
  - Bouton "Mettre le cap"
  - Backend: Query stations + relation `marches`

- [ ] **Demandes Stations** - Contrats transport
  - Liste demandes actives (ressource, quantité, prix, urgence)
  - Filtres: urgent, standard, expirées
  - Bouton "Accepter contrat" (vérif cargaison)
  - Calcul récompense selon distance/urgence
  - Migration: `station_demandes` (station_id, ressource_id, quantite, prix_offert, urgence, expiration)

- [ ] **Messages** - Messagerie inter-joueurs
  - Boîte de réception + envoi
  - Sous-réseaux (publics/payants)
  - Bouton "S'abonner" (payant)
  - Messages faction/guilde
  - Migrations: `messages`, `sous_reseaux`, `abonnements`

---

## ⚡ COURT TERME - Fonctionnalités Core

### Infrastructure Base de Données
- [ ] Migration: Attributs vaisseau (coque, boucliers, énergie)
- [ ] Table: `vaisseau_cargaison` (ressources transport)
- [ ] Tables: `items`, `personnage_items` (inventaire)
- [ ] Table: `marche_prix` (prix dynamiques)
- [ ] Table: `station_demandes` (contrats)
- [ ] Tables: `messages`, `sous_reseaux`, `abonnements`
- [ ] Table: `vaisseau_armes` (armes embarquées)

### Commandes de Jeu
- [ ] `scanner` - Alias vers vue scanner (AJAX)
- [ ] `charger <ressource> <qte>` - Charger cargaison
- [ ] `decharger <ressource> <qte>` - Décharger
- [ ] `jeter <ressource> <qte>` - Larguer ressources
- [ ] `inventaire-perso` - Alias inventaire personnel
- [ ] `utiliser <item>` - Utiliser consommable
- [ ] `equiper <item>` - Équiper arme/armure
- [ ] `desequiper <slot>` - Retirer équipement
- [ ] `cap <x> <y> <z>` - Définir destination
- [ ] `deplacer` - Lancer déplacement vers cap

### Système Économique
- [ ] Fluctuation prix dynamique (cron job Laravel)
- [ ] Système offre/demande (stock influence prix)
- [ ] Marché physique stations (interface graphique)
- [ ] Historique transactions (audit)
- [ ] Routes commerciales (analyse opportunités)

### Interface Station
- [ ] Menu contextuel pour localisation "station"
- [ ] Vue Marché physique (achat/vente GUI)
- [ ] Vue Missions disponibles
- [ ] Action "Embarquer vaisseau" (quitter station)
- [ ] Services station (réparation, ravitaillement)
- [ ] Réputation avec faction station

---

## 📅 MOYEN TERME - Expansion Gameplay

### Déplacement Avancé
- [ ] Déplacement dans secteur (trajectoires)
- [ ] Calcul consommation carburant/énergie
- [ ] Temps de trajet (vitesse vaisseau)
- [ ] Interception par autres joueurs (PvP)
- [ ] Zones dangereuses (pirates, anomalies)
- [ ] Arrêt d'urgence (`arreter` command)
- [ ] Dommages en cas de collision

### Combat Étendu
- [ ] Armes personnelles (pistolets, fusils)
- [ ] Armures personnelles (légère, lourde)
- [ ] Combat au sol (PvE, PvP)
- [ ] Armes embarquées vaisseau (installation)
- [ ] Combat spatial (extension système actuel)
- [ ] Munitions et rechargement
- [ ] Système de visée et précision

### Vaisseaux & Modules
- [ ] 12 emplacements modules vaisseau
- [ ] Module MicroHE (sauts intra-système)
- [ ] Modules scanner améliorés (portée +)
- [ ] Modules COM améliorés (portée +)
- [ ] Modules cargo (capacité +)
- [ ] Installation/désinstallation modules
- [ ] Compatibilité modules par modèle vaisseau
- [ ] Commandes: `installer <module>`, `desinstaller <slot>`

### Progression Personnage
- [ ] 16 compétences selon GDD
- [ ] Learning by doing (XP par action)
- [ ] Niveau et progression par compétence
- [ ] Arbres de compétences (déblocages)
- [ ] Jetons Hope (utilisation + gestion)
- [ ] Événements Fear (narratif caché)
- [ ] Système de achievements

---

## 🔮 LONG TERME - Avancé & Multiplayer

### Missions & Contrats
- [ ] Génération missions procédurales
- [ ] Types: Transport, Exploration, Élimination, Collecte
- [ ] Chaînes de missions (storylines)
- [ ] Missions de faction (influence réputation)
- [ ] Système de récompenses (crédits, XP, objets)
- [ ] Objectifs multiples par mission
- [ ] Échecs et conséquences

### Factions & Réputation
- [ ] Système de factions (impériales, guildes, pirates)
- [ ] Réputation par faction (-100 à +100)
- [ ] Influence sur prix, accès, missions
- [ ] Zones d'influence territoriales
- [ ] Guerres de factions (événements)
- [ ] Alliances joueurs-factions

### Bases Personnelles
- [ ] Construction bases spatiales (L'Arche)
- [ ] 13 types modules base selon GDD
- [ ] Gestionnaire de base (NPC ou joueur)
- [ ] Production énergie + ressources
- [ ] Connexion mines MAME → bases
- [ ] Transfert automatique ressources
- [ ] Défense bases (tourelles, boucliers)
- [ ] Système de population

### Multiplayer
- [ ] Détection autres joueurs dans secteur
- [ ] Combat PvP (vaisseau + au sol)
- [ ] Commerce P2P (échange direct)
- [ ] Guildes de joueurs
- [ ] Chat temps réel (WebSockets)
- [ ] Alliances et territoires
- [ ] Classements (richesse, combat, exploration)

### Interface Avancée
- [ ] Auto-complétion commandes (JavaScript)
- [ ] Notifications temps réel (WebSockets)
- [ ] Chat en jeu (canaux publics/privés)
- [ ] Système d'aide contextuelle
- [ ] Historique commandes (flèches haut/bas)
- [ ] Raccourcis clavier
- [ ] Thèmes UI (clair/sombre)

### Exploration & Univers
- [ ] Génération procédurale étendue
- [ ] Anomalies spatiales (événements aléatoires)
- [ ] Artefacts et technologies anciennes
- [ ] Terraformation planètes
- [ ] Colonisation planètes
- [ ] Contrôle territoires
- [ ] Météo spatiale (tempêtes, radiations)

### Multi-Univers
- [ ] Support univers alternatifs (Star Wars, W40K)
- [ ] Sélecteur univers (création compte)
- [ ] Données spécifiques par univers
- [ ] Lore et événements uniques
- [ ] Technologies par univers
- [ ] Factions spécifiques

---

## ⚠️ DÉCISIONS DESIGN À FINALISER

### Mécaniques Fondamentales
- [ ] **Temps réel vs Tour par tour**: Déplacement/Combat?
  - Proposition: Tour par tour avec ticks toutes les heures
- [ ] **Rayon Scanner/COM**: Défaut et maximum?
  - Proposition: Scanner 5-20 AL, COM 10-50 AL
- [ ] **Capacité transport**: Poids/Volume/Hybride?
  - Proposition: Volume uniquement (simplification)
- [ ] **Système carburant**: Consommation par action?
  - Proposition: Énergie pour tout, ravitaillement stations
- [ ] **Pénalités surcharge**: Vitesse réduite?
  - Proposition: -10% vitesse par 10% surcharge

### Équilibrage Économique
- [ ] Vitesse déplacement (AL/heure, AL/jour?)
- [ ] Coûts réparations (% prix vaisseau?)
- [ ] Prix ressources baseline (revoir équilibre)
- [ ] Taux fluctuation prix (±5% par jour?)
- [ ] Inflation monétaire (prévention)

### Équilibrage Combat
- [ ] Difficulté ennemis par zone
- [ ] Récompenses combat (butin + XP)
- [ ] Pénalités mort (perte items? respawn?)
- [ ] Balance armes/armures
- [ ] Temps recharge boucliers

---

## 📊 PROGRESSION GLOBALE

### Phase 0 - Infrastructure (100% ✅)
- ✅ Base de données (26 tables)
- ✅ Models Eloquent + relations
- ✅ Seeders données de jeu
- ✅ Architecture MVC

### Phase 1 - MVP Fondations (95% ✅)
- ✅ Authentification
- ✅ Génération univers (procedural + GAIA)
- ✅ Système de commandes (35+)
- ✅ Interface 3 panneaux AJAX
- ✅ Carte interactive
- ✅ Navigation basique
- 🚧 Tutoriel interactif (manquant)

### Phase 2 - Vues & Logique (20% 🚧)
- ✅ Architecture menu contextuel (100%)
- ✅ Controllers + routes (100%)
- 🚧 Vues Vaisseau (14% - 1/7 fonctionnelle)
- ❌ Vues COM (0%)
- ❌ Migrations manquantes (0%)
- ❌ Système économique avancé (0%)

### Phase 3 - Fonctionnalités Core (60% 🚧)
- ✅ Économie base (ressources, commerce)
- ✅ Combat PvE (10 ennemis, IA)
- ✅ Extraction (mines MAME)
- ❌ Déplacement avancé (0%)
- ❌ Missions (0%)
- ❌ Factions/Réputation (0%)

### Phase 4 - Avancé (0% ❌)
- ❌ Multiplayer
- ❌ Bases personnelles
- ❌ Progression avancée
- ❌ Multi-univers

### Phase 5 - Qualité & Production (0% ❌)
- ❌ Tests unitaires
- ❌ Documentation complète
- ❌ Optimisation performance
- ❌ Déploiement production

---

## 🎯 ROADMAP SPRINTS

### Sprint Actuel - Vues Essentielles (5-7 jours)
**Objectif**: Interface complète vaisseau + COM

**Livrables**:
1. Scanner fonctionnel (détection objets)
2. Cargaison fonctionnelle (gestion ressources)
3. Inventaire personnel (items + équipement)
4. Prix marchés (COM - tableau comparatif)
5. Bases de données (COM - stations proches)

**Migrations requises**:
- `vaisseau_cargaison`
- `items`, `personnage_items`
- `marche_prix`

### Sprint 2 - Maintenance & Station (4-5 jours)
1. État vaisseau (coque, boucliers, énergie)
2. Système réparations
3. Marché physique station (GUI)
4. Embarquer/débarquer vaisseau
5. Demandes stations (contrats)

### Sprint 3 - Mobilité & Temps (4-5 jours)
1. Déplacement dans secteur
2. Consommation énergie/carburant
3. Système de temps amélioré
4. Commandes navigation (`cap`, `deplacer`)
5. Trajectoires et interceptions

### Sprint 4 - Gameplay Loop (5-7 jours)
1. Système missions simple
2. Messages/communication
3. Armes embarquées
4. Combat spatial amélioré
5. Boucle complète: Explorer → Mission → Combat → Récompense

---

## 📈 MÉTRIQUES

**Total tâches identifiées**: ~150
**Terminées**: ~65 (43%)
**En cours**: ~12 (8%)
**À faire**: ~73 (49%)

**Estimation temps restant**:
- Court terme (Sprint 1-2): 2-3 semaines
- Moyen terme (Sprint 3-4): 1-2 mois
- Long terme (Phase 4-5): 3-6 mois

---

## 📝 NOTES IMPORTANTES

### Points Forts Actuels
- ✅ Architecture solide et modulaire
- ✅ Séparation claire des responsabilités
- ✅ Support AJAX intégré
- ✅ Système de commandes extensible
- ✅ Beaucoup de systèmes de jeu déjà fonctionnels

### Points d'Attention
- ⚠️ Beaucoup de vues placeholder (risque d'oubli)
- ⚠️ Pas de tests unitaires (dette technique)
- ⚠️ Mécaniques de jeu à finaliser (décisions design)
- ⚠️ Documentation API manquante
- ⚠️ Performance non optimisée (OK pour dev, à améliorer)

### Recommandations
1. **Priorité 1**: Finir les vues essentielles (Sprint 1)
2. **Priorité 2**: Créer tests unitaires (au moins helpers + middleware)
3. **Priorité 3**: Définir mécaniques de jeu (document design)
4. **Priorité 4**: Documentation API (pour contributeurs)
5. **Continu**: Refactoring léger (utiliser composants Blade)

---

**Dernière mise à jour**: 2025-11-23
**Version**: 2.0
**Statut**: 📘 Document de référence principal
