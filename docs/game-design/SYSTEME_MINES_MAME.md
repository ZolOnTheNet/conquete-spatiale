# ⛏️ SYSTÈME DE MINES - MAME

## Vue d'ensemble

Les **MAME (Model Autonome de Mine d'Exploitation)** sont des **Points d'Intérêt (PoI)** abordables qui permettent l'extraction autonome de ressources depuis les gisements planétaires.

---

## 📋 Caractéristiques

### Définition

**MAME** = Plus petite unité de production d'une ressource (extraction)

- **Type** : PoI abordable (comme une station)
- **Fonction** : Extraction autonome de ressources
- **Propriété** : Possédée par un joueur, peut être vendue
- **Accès** : Contrôlé par le propriétaire (public, privé, faction)

### Identification

Chaque mine a un **ID unique** (bigint, auto-incrémenté) dans la table `mines`.

**Exemple de nommage** :
- MAME-Fer-Mars-Alpha
- MAME-Titanium-Jupiter-Station-01
- Mine Personnalisée du Joueur

---

## 🏗️ Installation

### Prérequis

1. Gisement de ressource découvert sur une planète
2. Ressources nécessaires à l'installation :
   - Modules de construction
   - Énergie pour l'installation
   - Licence d'exploitation (si applicable)

### Emplacements possibles

| Emplacement | Description | Avantages | Inconvénients |
|-------------|-------------|-----------|---------------|
| **Surface** | Mine installée à la surface de la planète | Accès direct au gisement, stable | Nécessite planète accessible |
| **Orbite** | Mine orbitale (pour extraction depuis l'espace) | Pas besoin d'atterrir | Coût plus élevé, moins efficace |

---

## ⚙️ Fonctionnement

### Production autonome

La mine **extrait automatiquement** des ressources en fonction du temps écoulé dans le jeu.

**Formule** :
```
quantite_extraite = taux_extraction × temps_passe × facteur_usure
```

**Où** :
- `taux_extraction` : Unités/jour (temps de jeu) - défaut 100 unités/jour
- `temps_passe` : Jours de jeu écoulés depuis dernière extraction
- `facteur_usure` : 1.0 - (niveau_usure / 200) → Max -50% à 100% d'usure

### Consommation de ressources

Pour fonctionner, la mine consomme :

| Ressource | Consommation | Fréquence |
|-----------|--------------|-----------|
| **Énergie** | 10 unités | Par jour |
| **Pièces de rechange** | 1 unité | Par mois |
| **Pièces d'usure** | 5 unités | Par mois |

⚠️ **Si la mine manque de ressources, elle s'arrête !**

### Stockage

- **Capacité de stockage** : 10 000 unités (configurable par modèle)
- **Stock actuel** : Quantité de ressource actuellement stockée
- **Stock plein** : La mine s'arrête d'extraire si le stock est plein

---

## 🔒 Système d'accès

### Modes d'accès

Le propriétaire peut configurer l'accès à la mine :

1. **Privé** (défaut) : Seul le propriétaire peut accéder
2. **Public** : Tout le monde peut récupérer des ressources
3. **Faction** : Membres de la faction du propriétaire
4. **Liste d'autorisés** : Liste spécifique de personnages autorisés

### Actions possibles

| Action | Propriétaire | Autorisé | Public (si activé) |
|--------|--------------|----------|-------------------|
| Récupérer ressources | ✅ | ✅ | ✅ |
| Ravitailler (énergie, pièces) | ✅ | ✅ | ❌ |
| Effectuer maintenance | ✅ | ✅ | ❌ |
| Modifier accès | ✅ | ❌ | ❌ |
| Vendre la mine | ✅ | ❌ | ❌ |

---

## 🛠️ Maintenance

### Usure

- **Niveau d'usure** : 0-100%
- **Augmentation** : +0.1% par jour d'activité
- **Effet** : Réduit le taux d'extraction jusqu'à -50%

### États opérationnels

| Statut | Description | Production |
|--------|-------------|------------|
| **Active** | Fonctionne normalement | ✅ 100% |
| **Inactive** | Arrêtée manuellement | ❌ 0% |
| **Maintenance** | Usure ≥ 100%, nécessite réparation | ❌ 0% |
| **Endommagée** | Défaillance technique | ❌ 0% |

### Effectuer la maintenance

**Coût** :
- 1 pièce de rechange
- 5 pièces d'usure

**Effet** :
- Réinitialise l'usure à 0%
- Remet la mine en statut "Active"

---

## 💰 Économie

### Prix et valeur

- **Valeur estimée** : 50 000 crédits (défaut)
- **Prix d'achat** : Prix payé lors de l'achat (si seconde main)
- **Vendable** : Oui, entre joueurs

### Rentabilité

**Exemple** : Mine de Fer
- **Production** : 100 unités/jour
- **Consommation énergie** : 10 unités/jour (coût : ~10 crédits)
- **Valeur du fer** : 5 crédits/unité
- **Revenu brut** : 500 crédits/jour
- **Revenu net** : ~490 crédits/jour

**Amortissement** : ~102 jours (si achat à 50 000 crédits)

---

## 🔍 Détection

### PoI connu

- **Champ** : `poi_connu` (boolean)
- **Détectabilité de base** : 30.0 (plus facile à détecter qu'une petite station)
- **Visible sur la carte** : Si découverte

Les mines peuvent être détectées via la commande `scan`.

---

## 🎮 Commandes joueur

### Aborder une mine

```
arrimer mine <nom_mine>
```

### Récupérer des ressources

```
recuperer <quantite> <ressource> depuis mine
```

### Ravitailler

```
ravitailler mine <nom_mine> energie <quantite>
ravitailler mine <nom_mine> pieces <quantite>
```

### Effectuer la maintenance

```
maintenance mine <nom_mine>
```

### Gérer les accès

```
mine acces <nom_mine> public
mine acces <nom_mine> prive
mine acces <nom_mine> faction
mine acces <nom_mine> autoriser <nom_joueur>
mine acces <nom_mine> revoquer <nom_joueur>
```

### Vendre

```
vendre mine <nom_mine> a <nom_joueur> pour <prix>
```

---

## 🎨 Interface Admin

### Gestion des mines

Route : `/admin/mines`

**Fonctionnalités** :
- ✅ Lister toutes les mines
- ✅ Créer une mine (pour test)
- ✅ Modifier caractéristiques (taux extraction, capacité, etc.)
- ✅ Supprimer une mine
- ✅ Forcer maintenance
- ✅ Ravitailler instantanément
- ✅ Changer propriétaire

**Affichage dans planete-detail** :
- Liste des mines sur la planète
- Bouton "Créer une mine" sur un gisement

---

## 💾 Structure technique

### Table `mines`

```sql
id, nom, planete_id, gisement_id, emplacement,
installateur_id, proprietaire_id, modele,
capacite_stockage, stock_actuel, taux_extraction,
statut, niveau_usure, derniere_extraction,
energie_consommee, stock_energie,
pieces_rechange_consommees, stock_pieces_rechange,
pieces_usure_consommees, stock_pieces_usure,
acces_public, autorises_ids, acces_faction, faction_id,
base_id, connectee_base, valeur_estimee,
poi_connu, detectabilite_base
```

### Modèle `Mine`

**Relations** :
- `planete()` : Planète où se trouve la mine
- `gisement()` : Gisement exploité
- `proprietaire()` : Propriétaire actuel
- `installateur()` : Qui l'a installée
- `faction()` : Faction associée (si applicable)
- `base()` : Base connectée (optionnel)

**Méthodes principales** :
- `peutAcceder(Personnage)` : Vérifier les droits d'accès
- `peutFonctionner()` : Vérifier si peut produire
- `extraire(tempsPasse)` : Extraction automatique
- `recupererRessources(quantite, Personnage)` : Retrait manuel
- `effectuerMaintenance()` : Réparation
- `ravitailler()` : Ajouter consommables
- `vendre(nouveauProprietaire, prix)` : Transaction

---

## 🚀 Future : Interface avec base

### Connexion à une base

Une mine peut être **connectée à une base** :

**Avantages** :
- ✅ Transfert automatique des ressources vers la base
- ✅ Ravitaillement automatique depuis la base
- ✅ Maintenance automatisée
- ✅ Surveillance centralisée

**Configuration** :
- `base_id` : ID de la base
- `connectee_base` : true/false

⚠️ **Non implémenté dans la Phase 1**

---

## 📊 Statistiques et monitoring

### Tableau de bord propriétaire

**Affichage** :
- Nom et localisation
- Ressource exploitée
- Production actuelle vs théorique
- Stock disponible
- Niveau d'usure
- Consommables restants (jours d'autonomie)
- Revenu généré (total)

---

## ✅ Checklist d'implémentation

- [x] Migration `create_mines_table`
- [x] Modèle `Mine` avec relations et méthodes
- [x] Documentation complète
- [ ] Relations inverses (Planete, Gisement, Personnage)
- [ ] Interface admin mines
- [ ] Commandes joueur (arrimer, récupérer, ravitailler)
- [ ] Système d'extraction automatique (cron/temporel)
- [ ] Tests unitaires

---

---

## 🎯 Accès et Interface Contextuelle

### Accès depuis le vaisseau

Les mines sont accessibles depuis différents contextes selon la localisation du personnage :

**Dans un vaisseau** :
- ✅ Voir les mines via **COM (Communications)** - Bases de données à distance
- ✅ Scanner les mines comme PoI
- ❌ Pas d'accès direct au marché (mais données des marchés via COM)
- ❌ Pas d'accès direct aux descriptions d'armes/combat

**Dans une station/ville** :
- ✅ Accès complet au marché
- ✅ Accès aux informations détaillées
- ✅ Possibilité d'achat/vente

**Menu Vaisseau** :
- **Timonerie** : Position, Carte, Scanner
- **Ingénierie** : État du vaisseau, réparations
- **Soute** : Inventaire, cargaison
- **Armement** : Armes embarquées
- **COM** : Communications
  - Bases de données stations/villes à proximité
  - Prix d'achat/vente des ressources
  - Demande des stations et villes
  - Messages sur sous-réseaux (achetables)

### Position du personnage

Le système affiche toujours :
- **Type de localisation** : Vaisseau, Station, Navette, Planète, etc.
- **Position spatiale** : Coordonnées (secteur + position)
- **Objet spatial** : Nom du vaisseau/station
- **État** : En déplacement, amarré, en orbite, etc.

---

**Dernière mise à jour** : 2025-11-23
**Statut** : En développement
