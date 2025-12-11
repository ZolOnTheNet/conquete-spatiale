# Ã¢âºï¿½Ã¯Â¸ï¿½ SYSTÃËME DE MINES - MAME

## Vue d'ensemble

Les **MAME (Model Autonome de Mine d'Exploitation)** sont des **Points d'IntÃÂ©rÃÂªt (PoI)** abordables qui permettent l'extraction autonome de ressources depuis les gisements planÃÂ©taires.

---

## ÄÅ¸ââ¹ CaractÃÂ©ristiques

### DÃÂ©finition

**MAME** = Plus petite unitÃÂ© de production d'une ressource (extraction)

- **Type** : PoI abordable (comme une station)
- **Fonction** : Extraction autonome de ressources
- **PropriÃÂ©tÃÂ©** : PossÃÂ©dÃÂ©e par un joueur, peut ÃÂªtre vendue
- **AccÃÂ¨s** : ContrÃÂ´lÃÂ© par le propriÃÂ©taire (public, privÃÂ©, faction)

### Identification

Chaque mine a un **ID unique** (bigint, auto-incrÃÂ©mentÃÂ©) dans la table `mines`.

**Exemple de nommage** :
- MAME-Fer-Mars-Alpha
- MAME-Titanium-Jupiter-Station-01
- Mine PersonnalisÃÂ©e du Joueur

---

## ÄÅ¸ï¿½âÃ¯Â¸ï¿½ Installation

### PrÃÂ©requis

1. Gisement de ressource dÃÂ©couvert sur une planÃÂ¨te
2. Ressources nÃÂ©cessaires ÃÂ  l'installation :
   - Modules de construction
   - Ãâ°nergie pour l'installation
   - Licence d'exploitation (si applicable)

### Emplacements possibles

| Emplacement | Description | Avantages | InconvÃÂ©nients |
|-------------|-------------|-----------|---------------|
| **Surface** | Mine installÃÂ©e ÃÂ  la surface de la planÃÂ¨te | AccÃÂ¨s direct au gisement, stable | NÃÂ©cessite planÃÂ¨te accessible |
| **Orbite** | Mine orbitale (pour extraction depuis l'espace) | Pas besoin d'atterrir | CoÃÂ»t plus ÃÂ©levÃÂ©, moins efficace |

---

## Ã¢Å¡â¢Ã¯Â¸ï¿½ Fonctionnement

### Production autonome

La mine **extrait automatiquement** des ressources en fonction du temps ÃÂ©coulÃÂ© dans le jeu.

**Formule** :
```
quantite_extraite = taux_extraction Ãâ temps_passe Ãâ facteur_usure
```

**OÃÂ¹** :
- `taux_extraction` : UnitÃÂ©s/jour (temps de jeu) - dÃÂ©faut 100 unitÃÂ©s/jour
- `temps_passe` : Jours de jeu ÃÂ©coulÃÂ©s depuis derniÃÂ¨re extraction
- `facteur_usure` : 1.0 - (niveau_usure / 200) Ã¢â â Max -50% ÃÂ  100% d'usure

### Consommation de ressources

Pour fonctionner, la mine consomme :

| Ressource | Consommation | FrÃÂ©quence |
|-----------|--------------|-----------|
| **Ãâ°nergie** | 10 unitÃÂ©s | Par jour |
| **PiÃÂ¨ces de rechange** | 1 unitÃÂ© | Par mois |
| **PiÃÂ¨ces d'usure** | 5 unitÃÂ©s | Par mois |

Ã¢Å¡Â Ã¯Â¸ï¿½ **Si la mine manque de ressources, elle s'arrÃÂªte !**

### Stockage

- **CapacitÃÂ© de stockage** : 10 000 unitÃÂ©s (configurable par modÃÂ¨le)
- **Stock actuel** : QuantitÃÂ© de ressource actuellement stockÃÂ©e
- **Stock plein** : La mine s'arrÃÂªte d'extraire si le stock est plein

---

## ÄÅ¸ââ SystÃÂ¨me d'accÃÂ¨s

### Modes d'accÃÂ¨s

Le propriÃÂ©taire peut configurer l'accÃÂ¨s ÃÂ  la mine :

1. **PrivÃÂ©** (dÃÂ©faut) : Seul le propriÃÂ©taire peut accÃÂ©der
2. **Public** : Tout le monde peut rÃÂ©cupÃÂ©rer des ressources
3. **Faction** : Membres de la faction du propriÃÂ©taire
4. **Liste d'autorisÃÂ©s** : Liste spÃÂ©cifique de personnages autorisÃÂ©s

### Actions possibles

| Action | PropriÃÂ©taire | AutorisÃÂ© | Public (si activÃÂ©) |
|--------|--------------|----------|-------------------|
| RÃÂ©cupÃÂ©rer ressources | Ã¢Åâ¦ | Ã¢Åâ¦ | Ã¢Åâ¦ |
| Ravitailler (ÃÂ©nergie, piÃÂ¨ces) | Ã¢Åâ¦ | Ã¢Åâ¦ | Ã¢ï¿½Å |
| Effectuer maintenance | Ã¢Åâ¦ | Ã¢Åâ¦ | Ã¢ï¿½Å |
| Modifier accÃÂ¨s | Ã¢Åâ¦ | Ã¢ï¿½Å | Ã¢ï¿½Å |
| Vendre la mine | Ã¢Åâ¦ | Ã¢ï¿½Å | Ã¢ï¿½Å |

---

## ÄÅ¸âºÂ Ã¯Â¸ï¿½ Maintenance

### Usure

- **Niveau d'usure** : 0-100%
- **Augmentation** : +0.1% par jour d'activitÃÂ©
- **Effet** : RÃÂ©duit le taux d'extraction jusqu'ÃÂ  -50%

### Ãâ°tats opÃÂ©rationnels

| Statut | Description | Production |
|--------|-------------|------------|
| **Active** | Fonctionne normalement | Ã¢Åâ¦ 100% |
| **Inactive** | ArrÃÂªtÃÂ©e manuellement | Ã¢ï¿½Å 0% |
| **Maintenance** | Usure Ã¢â°Â¥ 100%, nÃÂ©cessite rÃÂ©paration | Ã¢ï¿½Å 0% |
| **EndommagÃÂ©e** | DÃÂ©faillance technique | Ã¢ï¿½Å 0% |

### Effectuer la maintenance

**CoÃÂ»t** :
- 1 piÃÂ¨ce de rechange
- 5 piÃÂ¨ces d'usure

**Effet** :
- RÃÂ©initialise l'usure ÃÂ  0%
- Remet la mine en statut "Active"

---

## ÄÅ¸âÂ° Ãâ°conomie

### Prix et valeur

- **Valeur estimÃÂ©e** : 50 000 crÃÂ©dits (dÃÂ©faut)
- **Prix d'achat** : Prix payÃÂ© lors de l'achat (si seconde main)
- **Vendable** : Oui, entre joueurs

### RentabilitÃÂ©

**Exemple** : Mine de Fer
- **Production** : 100 unitÃÂ©s/jour
- **Consommation ÃÂ©nergie** : 10 unitÃÂ©s/jour (coÃÂ»t : ~10 crÃÂ©dits)
- **Valeur du fer** : 5 crÃÂ©dits/unitÃÂ©
- **Revenu brut** : 500 crÃÂ©dits/jour
- **Revenu net** : ~490 crÃÂ©dits/jour

**Amortissement** : ~102 jours (si achat ÃÂ  50 000 crÃÂ©dits)

---

## ÄÅ¸âï¿½ DÃÂ©tection

### PoI connu

- **Champ** : `poi_connu` (boolean)
- **DÃÂ©tectabilitÃÂ© de base** : 30.0 (plus facile ÃÂ  dÃÂ©tecter qu'une petite station)
- **Visible sur la carte** : Si dÃÂ©couverte

Les mines peuvent ÃÂªtre dÃÂ©tectÃÂ©es via la commande `scan`.

---

## ÄÅ¸ï¿½Â® Commandes joueur

### Aborder une mine

```
arrimer mine <nom_mine>
```

### RÃÂ©cupÃÂ©rer des ressources

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

### GÃÂ©rer les accÃÂ¨s

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

## ÄÅ¸ï¿½Â¨ Interface Admin

### Gestion des mines

Route : `/admin/mines`

**FonctionnalitÃÂ©s** :
- Ã¢Åâ¦ Lister toutes les mines
- Ã¢Åâ¦ CrÃÂ©er une mine (pour test)
- Ã¢Åâ¦ Modifier caractÃÂ©ristiques (taux extraction, capacitÃÂ©, etc.)
- Ã¢Åâ¦ Supprimer une mine
- Ã¢Åâ¦ Forcer maintenance
- Ã¢Åâ¦ Ravitailler instantanÃÂ©ment
- Ã¢Åâ¦ Changer propriÃÂ©taire

**Affichage dans planete-detail** :
- Liste des mines sur la planÃÂ¨te
- Bouton "CrÃÂ©er une mine" sur un gisement

---

## ÄÅ¸âÂ¾ Structure technique

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

### ModÃÂ¨le `Mine`

**Relations** :
- `planete()` : PlanÃÂ¨te oÃÂ¹ se trouve la mine
- `gisement()` : Gisement exploitÃÂ©
- `proprietaire()` : PropriÃÂ©taire actuel
- `installateur()` : Qui l'a installÃÂ©e
- `faction()` : Faction associÃÂ©e (si applicable)
- `base()` : Base connectÃÂ©e (optionnel)

**MÃÂ©thodes principales** :
- `peutAcceder(Personnage)` : VÃÂ©rifier les droits d'accÃÂ¨s
- `peutFonctionner()` : VÃÂ©rifier si peut produire
- `extraire(tempsPasse)` : Extraction automatique
- `recupererRessources(quantite, Personnage)` : Retrait manuel
- `effectuerMaintenance()` : RÃÂ©paration
- `ravitailler()` : Ajouter consommables
- `vendre(nouveauProprietaire, prix)` : Transaction

---

## ÄÅ¸Å¡â¬ Future : Interface avec base

### Connexion ÃÂ  une base

Une mine peut ÃÂªtre **connectÃÂ©e ÃÂ  une base** :

**Avantages** :
- Ã¢Åâ¦ Transfert automatique des ressources vers la base
- Ã¢Åâ¦ Ravitaillement automatique depuis la base
- Ã¢Åâ¦ Maintenance automatisÃÂ©e
- Ã¢Åâ¦ Surveillance centralisÃÂ©e

**Configuration** :
- `base_id` : ID de la base
- `connectee_base` : true/false

Ã¢Å¡Â Ã¯Â¸ï¿½ **Non implÃÂ©mentÃÂ© dans la Phase 1**

---

## ÄÅ¸âÅ  Statistiques et monitoring

### Tableau de bord propriÃÂ©taire

**Affichage** :
- Nom et localisation
- Ressource exploitÃÂ©e
- Production actuelle vs thÃÂ©orique
- Stock disponible
- Niveau d'usure
- Consommables restants (jours d'autonomie)
- Revenu gÃÂ©nÃÂ©rÃÂ© (total)

---

## Ã¢Åâ¦ Checklist d'implÃÂ©mentation

- [x] Migration `create_mines_table`
- [x] ModÃÂ¨le `Mine` avec relations et mÃÂ©thodes
- [x] Documentation complÃÂ¨te
- [ ] Relations inverses (Planete, Gisement, Personnage)
- [ ] Interface admin mines
- [ ] Commandes joueur (arrimer, rÃÂ©cupÃÂ©rer, ravitailler)
- [ ] SystÃÂ¨me d'extraction automatique (cron/temporel)
- [ ] Tests unitaires

---

---

## ÄÅ¸ï¿½Â¯ AccÃÂ¨s et Interface Contextuelle

### AccÃÂ¨s depuis le vaisseau

Les mines sont accessibles depuis diffÃÂ©rents contextes selon la localisation du personnage :

**Dans un vaisseau** :
- Ã¢Åâ¦ Voir les mines via **COM (Communications)** - Bases de donnÃÂ©es ÃÂ  distance
- Ã¢Åâ¦ Scanner les mines comme PoI
- Ã¢ï¿½Å Pas d'accÃÂ¨s direct au marchÃÂ© (mais donnÃÂ©es des marchÃÂ©s via COM)
- Ã¢ï¿½Å Pas d'accÃÂ¨s direct aux descriptions d'armes/combat

**Dans une station/ville** :
- Ã¢Åâ¦ AccÃÂ¨s complet au marchÃÂ©
- Ã¢Åâ¦ AccÃÂ¨s aux informations dÃÂ©taillÃÂ©es
- Ã¢Åâ¦ PossibilitÃÂ© d'achat/vente

**Menu Vaisseau** :
- **Timonerie** : Position, Carte, Scanner
- **IngÃÂ©nierie** : Ãâ°tat du vaisseau, rÃÂ©parations
- **Soute** : Inventaire, cargaison
- **Armement** : Armes embarquÃÂ©es
- **COM** : Communications
  - Bases de donnÃÂ©es stations/villes ÃÂ  proximitÃÂ©
  - Prix d'achat/vente des ressources
  - Demande des stations et villes
  - Messages sur sous-rÃÂ©seaux (achetables)

### Position du personnage

Le systÃÂ¨me affiche toujours :
- **Type de localisation** : Vaisseau, Station, Navette, PlanÃÂ¨te, etc.
- **Position spatiale** : CoordonnÃÂ©es (secteur + position)
- **Objet spatial** : Nom du vaisseau/station
- **Ãâ°tat** : En dÃÂ©placement, amarrÃÂ©, en orbite, etc.

---

**DerniÃÂ¨re mise ÃÂ  jour** : 2025-11-23
**Statut** : En dÃÂ©veloppement
