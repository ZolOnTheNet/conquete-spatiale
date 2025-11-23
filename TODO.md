# 📋 TODO - Conquête Spatiale

**Date**: 2025-11-23  
**Voir détails complets**: `docs/game-design/BILAN_SYSTEME_CONTEXTUEL.md`

---

## 🔥 PRIORITÉ IMMÉDIATE

### Vues Vaisseau
- [ ] **Scanner** - Détecter objets spatiaux à proximité
  - Stations, planètes, vaisseaux, anomalies
  - Calcul distances 3D
  - Filtres et tri
  
- [ ] **Cargaison** - Gestion ressources transportées
  - Liste ressources avec quantité/capacité
  - Actions: charger, décharger, jeter
  - Migration: `vaisseau_cargaison`

- [ ] **Inventaire** - Gestion items personnels
  - Liste items par catégorie
  - Actions: utiliser, équiper, jeter, transférer
  - Migrations: `items`, `personnage_items`

### Vues COM
- [ ] **Prix des Marchés** - Tableau comparatif prix
  - Prix achat/vente par station
  - Tri et filtres
  - Migration: `marche_prix`

- [ ] **Bases de Données** - Infos stations à proximité
  - Liste stations dans rayon COM
  - Services et ressources disponibles
  - Distance et direction

---

## ⚡ COURT TERME

### Infrastructure
- [ ] Migrations attributs vaisseau (coque, boucliers, énergie)
- [ ] Table `station_demandes` (besoins stations)
- [ ] Table `messages` + `sous_reseaux` + `abonnements`

### Fonctionnalités
- [ ] **État Vaisseau** - Affichage coque/boucliers/systèmes
- [ ] **Réparations** - Interface réparation composants
- [ ] **Demandes Stations** - Contrats de transport
- [ ] **Messages COM** - Messagerie inter-joueurs

### Commandes
- [ ] `scanner` - Alias vers vue scanner
- [ ] `charger <ressource> <quantite>` - Charger cargaison
- [ ] `decharger <ressource> <quantite>` - Décharger cargaison
- [ ] `utiliser <item>` - Utiliser objet inventaire
- [ ] `equiper <item>` - Équiper arme/armure

---

## 📅 MOYEN TERME

### Système Économique
- [ ] Fluctuation prix dynamique (cron job)
- [ ] Marché physique stations (achat/vente)
- [ ] Système offre/demande

### Interface Station
- [ ] Menu contextuel station complété
- [ ] Vue Marché physique
- [ ] Vue Missions disponibles
- [ ] Action "Embarquer vaisseau"

### Déplacement
- [ ] Commande `cap <x> <y> <z>` - Définir destination
- [ ] Commande `deplacer` - Lancer déplacement
- [ ] Calcul consommation carburant
- [ ] Trajectoires et temps de trajet

---

## 🔮 LONG TERME

### Combat
- [ ] Armes personnelles + armures
- [ ] Combat au sol
- [ ] Armes embarquées vaisseau
- [ ] Combat spatial

### Missions
- [ ] Génération missions procédurales
- [ ] Types: Transport, Exploration, Élimination, Collecte
- [ ] Système de récompenses
- [ ] Missions de faction

### Avancé
- [ ] Factions et réputation
- [ ] Système de compétences (pilotage, combat, commerce)
- [ ] Bases personnelles
- [ ] Multiplayer (PvP, commerce)

---

## ⚠️ DÉCISIONS À PRENDRE

### Mécanique de Jeu
- [ ] **Temps réel vs Tour par tour** pour déplacement/combat?
- [ ] **Rayon Scanner/COM** par défaut et maximum?
- [ ] **Capacité transport** - Poids ou Volume ou les deux?
- [ ] **Système carburant** - Consommation par action?
- [ ] **Pénalités surcharge** - Vitesse réduite?

### Équilibrage
- [ ] Vitesse de déplacement (AL/jour?)
- [ ] Coûts réparations
- [ ] Prix ressources (baseline)
- [ ] Taux de fluctuation prix
- [ ] Difficulté combat

---

## 📊 PROGRESSION

- ✅ **Phase 1 - Fondations**: Architecture complète
- 🚧 **Phase 2 - Vues & Logique**: 15% (1/7 vues fonctionnelles)
- ❌ **Phase 3 - Fonctionnalités**: Non commencée
- ❌ **Phase 4 - Multiplayer**: Non commencée

---

## 🎯 SPRINT ACTUEL: Vues Vaisseau

**Objectif**: Interface complète pour navigation et gestion basique depuis vaisseau

**Livrables**:
1. Scanner fonctionnel
2. Cargaison fonctionnelle
3. Inventaire fonctionnel
4. Prix marchés (COM) fonctionnel

**Estimé**: ~3-5 jours de développement

---

**Dernière mise à jour**: 2025-11-23
