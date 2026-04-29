# Système des Stations Spatiales

## Vue d'ensemble

Les stations spatiales sont les hubs centraux de l'activité des joueurs dans Conquête Spatiale. Elles permettent l'amarrage, le commerce, les réparations, et bien plus encore.

## Accessibilité

### Planètes : INACCESSIBLES
Toutes les planètes sont **inaccessibles** aux vaisseaux des joueurs pour les raisons suivantes :
- **Terre, Lune, Mars** : Surpopulation et transport (vaisseaux trop gros pour atterrir)
- **Jupiter, Neptune** : Planètes gazeuses (impossible d'atterrir)

*Note technique* : Ceci simplifie le gameplay en évitant de gérer les mondes planétaires dans cette phase du jeu.

### Stations : ACCESSIBLES
**TOUTES** les stations sont accessibles aux joueurs. Les vaisseaux peuvent :
- S'arrimer (avec jet de pilotage)
- Se transborder dans la station
- Utiliser les services disponibles
- Désamarrer (avec jet de pilotage)

## Arrimage et Navigation

### Arrimage (`arrimer`)
Lorsqu'un vaisseau souhaite s'arrimer à une station :
1. Le pilote doit être dans le même secteur que la station
2. Le pilote lance un **jet de pilotage Daggerheart** (Hope + Fear + Compétence)
3. Le résultat détermine la qualité de l'amarrage :
   - **Critique (Hope = Fear)** : Amarrage parfait ou incident dramatique
   - **Succès (≥12)** : Amarrage réussi sans problème
   - **Succès partiel (9-11)** : Amarrage avec complications mineures
   - **Échec (<9)** : Collision, dommages, ou échec d'amarrage
4. Une fois arrimé, le vaisseau est en sécurité et consomme moins d'énergie

### Désamarrage (`desarrimer`)
Pour quitter la station :
1. Le pilote doit être à bord du vaisseau (pas dans la station)
2. Le pilote lance un **jet de pilotage Daggerheart**
3. Le résultat détermine la qualité du départ
4. Le vaisseau quitte l'amarrage et peut naviguer librement

## Transbordement

### Entrer dans la station (`transborder`)
- Le vaisseau doit être arrimé
- Le personnage quitte physiquement son vaisseau
- Le personnage entre dans la station et peut accéder aux services
- Le vaisseau reste arrimé et sécurisé

### Retourner au vaisseau (`embarquer`)
- Le personnage doit être dans une station
- Le personnage retourne à bord de son vaisseau
- Le vaisseau est toujours arrimé
- Le personnage peut maintenant désamarrer

## Services de Station

Les stations offrent différents services selon leur configuration. Tous les services ne sont pas disponibles dans toutes les stations.

### 1. Marché (`marche`)
**Disponible si** : `commerciale = true`

Le marché permet :
- **Acheter** des marchandises (minerais, composants, carburant, nourriture)
- **Vendre** des marchandises depuis la cargaison du vaisseau
- Les prix varient selon l'offre/demande locale
- Certaines marchandises rares nécessitent une réputation

Commandes :
- `marche` - Afficher les marchandises disponibles
- `acheter <marchandise> <quantité>` - Acheter
- `vendre <marchandise> <quantité>` - Vendre

### 2. Garage (`garage`)
**Disponible si** : `reparations = true`

Le garage permet :
- **Réparer** le vaisseau (coque, moteurs, systèmes)
- **Stocker** des véhicules additionnels
- **Améliorer** les équipements existants
- **Installer** de nouveaux modules

Commandes :
- `garage` - Afficher l'état du vaisseau et services
- `reparer <système>` - Réparer un système endommagé
- `ameliorer <équipement>` - Améliorer un équipement
- `stocker <véhicule>` - Stocker un véhicule au garage

### 3. Quartier des Comptoirs (`comptoirs`)
**Toujours disponible**

Le quartier des comptoirs est le cœur social et administratif :
- **Missions** : Obtenir de nouvelles missions des guildes
- **Guildes** : Dialoguer avec les représentants des guildes
- **Plaintes** : Déposer des plaintes officielles
- **Commerce spécialisé** : Vendeurs de matériel rare, armes, équipements
- **Bar** : Interactions sociales, rumeurs, informations
- **Recrutement** : Recruter des membres d'équipage

Commandes :
- `comptoirs` - Accéder au quartier des comptoirs
- `missions` - Consulter les missions disponibles
- `accepter <mission_id>` - Accepter une mission
- `guildes` - Voir les guildes présentes
- `bar` - Aller au bar (rumeurs, informations)

### 4. Hôpital (`hopital`)
**Disponible si** : `medical = true`

L'hôpital permet :
- **Soigner** les blessures du personnage
- **Régénérer** les points de vie
- **Traiter** les maladies ou empoisonnements
- **Cybernétique** : Installer des améliorations (avancé)

Commandes :
- `hopital` - Accéder à l'hôpital
- `soigner` - Soigner toutes les blessures (coût variable)
- `traiter <condition>` - Traiter une condition spécifique

### 5. Quartier Industriel (`industrie`)
**Disponible si** : `industrielle = true`

Le quartier industriel permet :
- **Raffiner** les minerais bruts en matériaux utilisables
- **Transformer** les matériaux en composants
- **Fabriquer** des équipements (si plans disponibles)
- **Recycler** des équipements obsolètes

Commandes :
- `industrie` - Accéder au quartier industriel
- `raffiner <minerai> <quantité>` - Raffiner un minerai
- `fabriquer <plan>` - Fabriquer un équipement
- `recycler <équipement>` - Recycler un équipement

### 6. Ravitaillement (`ravitailler`)
**Disponible si** : `ravitaillement = true`

Le ravitaillement permet :
- **Carburant** : Remplir les réservoirs
- **Eau** : Recharger l'eau potable
- **Oxygène** : Recharger les réserves d'O2
- **Nourriture** : Acheter des rations

Commandes :
- `ravitailler` - Ravitailler complètement (automatique)
- `ravitailler carburant` - Ravitailler uniquement le carburant

## Types de Stations

### Spatiogare
Station standard avec services de base :
- Amarrage : 50-200 vaisseaux
- Services : Marché, garage, comptoirs, ravitaillement
- Accessible à tous

### Hub Commercial
Grande station commerciale :
- Amarrage : 500-1000 vaisseaux
- Services complets incluant industrie et médical
- Prix compétitifs, grande variété de marchandises
- Exemple : **Terra-Maxi-Hub**

### Station Militaire
Station avec présence militaire :
- Amarrage limité selon réputation
- Services militaires et réparations avancées
- Missions militaires disponibles
- Accès restreint aux équipements militaires

### Station de Départ
Station pour nouveaux joueurs :
- Services gratuits ou à prix réduit pour débutants
- Missions tutoriel
- Exemple : **Lunastar-station**

## Stations du Système Solaire

| Station | Planète | Type | Services | Capacité |
|---------|---------|------|----------|----------|
| **Terra-Maxi-Hub** | Terre | Hub Commercial | Tous | 1000 |
| **Lunastar-station** | Lune | Départ | Tous sauf industrie | 200 |
| **Mars-spatiogare** | Mars | Spatiogare | Tous | 150 |
| **Jupiter-spatiogare** | Jupiter | Spatiogare | Tous sauf médical | 100 |
| **Neptune-spatiogare** | Neptune | Spatiogare Militaire | Tous sauf médical | 80 |

## Flux de Jeu Typique

1. **Timonerie** : Le joueur navigue vers une station
2. **Arrimage** : `arrimer` - Jet de pilotage
3. **Transbordement** : `transborder` - Entre dans la station
4. **Services** : Utilise les services (`marche`, `garage`, `comptoirs`, etc.)
5. **Retour** : `embarquer` - Retourne au vaisseau
6. **Départ** : `desarrimer` - Jet de pilotage, quitte la station

## Considérations Techniques

### État du Personnage
Le personnage peut être dans 3 états :
- **À bord du vaisseau, non arrimé** : Peut naviguer librement
- **À bord du vaisseau, arrimé** : Peut transborder ou désamarrer
- **Dans une station** : Peut utiliser services ou embarquer

### Vérifications
Avant chaque action, vérifier :
1. Le personnage est-il dans le bon état ?
2. Le vaisseau est-il arrimé (pour transborder) ?
3. La station offre-t-elle ce service ?
4. Le personnage a-t-il les crédits/réputation nécessaires ?


### Liste des modules de station :

**Dernière mise à jour : 31 décembre 2025**

Les modules de station déterminent quels services sont disponibles dans une station. Si un module n'est pas installé dans une station, le service correspondant n'apparaît pas dans le menu.

#### Modules Implémentés ✅

| Module | Champ DB | Route | Contrôleur | Vue | Statut |
|--------|----------|-------|------------|-----|--------|
| **Amarrage** |  (toujours) | `capacite_amarrage` | - | TimonerieController | - | ✅ Fonctionnel |
| **Hall d'accueil** | (toujours) | `station.hall` | StationController@hall | `game/station/hall.blade.php` | ✅ Créé |
| **Hangar** | (toujours) | `station.hangar` | StationController@hangar | `game/station/hangar.blade.php` | ✅ Fonctionnel |
| **Comptoirs** | (toujours) | `station.missions` | StationController@missions | `game/station/missions.blade.php` | ✅ Créé |
| **Cantina** | (toujours) | `station.cantina` | StationController@cantina | `game/station/cantina.blade.php` | ✅ Créé |
| **Marché** | `commerciale` | `marche.index` | MarcheController@index | `game/marche/index.blade.php` | ✅ Fonctionnel |
| **Garage** | `reparations` | `garage.index` | GarageController@index | `game/garage/index.blade.php` | ✅ Fonctionnel |
| **Ravitaillement** | `ravitaillement` | `ravitaillement.index` | RavitaillementController@index | `game/ravitaillement/index.blade.php` | ✅ Fonctionnel |

#### Modules En Construction 🚧

| Module | Champ DB | Route | Contrôleur | Vue | Statut |
|--------|----------|-------|------------|-----|--------|
| **Hôpital** | `medical` | `station.hopital` | StationController@hopital | `game/station/en-construction.blade.php` | 🚧 En construction |
| **Zone Industrielle** | `industrielle` | `station.industrie` | StationController@industrie | `game/station/en-construction.blade.php` | 🚧 En construction |

#### Modules Prévus 📋

| Module | Champ DB (prévu) | Description | Priorité |
|--------|------------------|-------------|----------|
| **Académie de Formation** | `academie` | Formation et amélioration des compétences de l'équipage | Moyenne |
| **Bureau de Recrutement** | `recrutement` | Embauche de nouveaux membres d'équipage | Moyenne |
| **Bourse d'échange** | `bourse` | Trading avancé, contrats à terme, spéculation | Basse |
| **Archives** | `archives` | Consultation de l'historique, encyclopédie galactique | Basse |
| **Laboratoire de Recherche** | `laboratoire` | Recherche technologique, amélioration d'équipements | Haute |
| **Poste de Commandement Militaire** | `militaire` | Missions militaires, recrutement factions, guerre | Moyenne |
| **Banque Galactique** | `banque` | Prêts, investissements, coffres sécurisés | Moyenne |
| **Atelier de Fabrication** | `fabrication` | Création d'objets personnalisés, artisanat | Haute |
| **Station de Transit** | `transit` | Transport rapide vers autres stations (navette) | Basse |
| **Casino** | `casino` | Jeux de hasard, divertissement | Basse |
| **Auberge** | `auberge` | Repos, récupération, moral de l'équipage | Moyenne |
| **Bibliothèque de Plans** | `plans` | Achat de plans technologiques rares | Moyenne |
| **Centre de Décontamination** | `decontamination` | Nettoyage de matériaux dangereux | Basse |
| **Entrepôt Personnel** | `entrepot` | Stockage longue durée de marchandises | Haute |

#### Règles d'affichage dans le menu

```php
// Exemple de logique dans station/menu.blade.php
@if($station->commerciale)
    <a href="{{ route('marche.index') }}">🛒 Marché</a>
@endif

@if($station->reparations)
    <a href="{{ route('garage.index') }}">🔧 Garage</a>
@endif

@if($station->medical)
    <a href="{{ route('station.hopital') }}">🏥 Hôpital</a>
@endif
```

#### Compatibilité par type de station

| Type de Station | Modules inclus par défaut |
|----------------|---------------------------|
| **Spatiogare** | Amarrage, Hall, Hangar, Comptoirs, Cantina, Marché, Garage, Ravitaillement |
| **Hub Commercial** | Tous les modules Spatiogare + Hôpital, Zone Industrielle, Bourse, Entrepôt |
| **Station Militaire** | Amarrage, Hall, Hangar, Garage, Ravitaillement, Poste Militaire, Académie |
| **Station de Départ** | Amarrage, Hall, Hangar, Comptoirs, Marché (prix réduits), Académie (gratuit) |
| **Station Minière** | Amarrage, Hall, Hangar, Garage, Zone Industrielle, Ravitaillement |
| **Station de Recherche** | Amarrage, Hall, Hangar, Laboratoire, Archives, Bibliothèque Plans |

#### Notes techniques

- Les modules marqués `(toujours)` sont disponibles dans **toutes** les stations
- Les autres modules dépendent du champ booléen correspondant dans la table `stations`
- Si le module n'existe pas en DB (`NULL` ou `false`), le lien ne s'affiche pas dans le menu
- Les modules "En construction" affichent une page dédiée avec description de la fonctionnalité prévue

---

### Liste des modules de vaisseaux :

**Dernière mise à jour : 31 décembre 2025**

Les modules de vaisseaux sont des équipements installables via les services des stations (principalement **Garage** et **Zone Industrielle**). Chaque vaisseau a un nombre limité d'emplacements selon sa taille.

#### 1. Modules de Propulsion
- **Moteur conventionnel** (Niveaux 1-5)
  - Améliore la vitesse de déplacement dans le système
  - Consommation énergétique variable
  - Installation : Garage

- **Générateur hyperspatial** (Niveaux 1-5)
  - Permet les sauts hyperspatiaux
  - Distance de saut augmentée par niveau
  - Précision améliorée aux niveaux supérieurs
  - Installation : Garage

- **Propulseurs de manœuvre** (Niveaux 1-3)
  - Améliore l'agilité et la vitesse de rotation
  - Bonus au jet de pilotage pour amarrage/désamarrage
  - Installation : Garage

#### 2. Modules de Défense
- **Générateur de bouclier** (Niveaux 1-5)
  - Points de bouclier : 50/100/200/400/800
  - Temps de recharge variable
  - Installation : Garage

- **Plaques de blindage** (Léger/Moyen/Lourd)
  - Augmente les points de coque
  - Pénalité de vitesse selon le type
  - Installation : Garage ou Zone Industrielle

- **Système anti-missiles** (Niveaux 1-3)
  - Interception automatique des projectiles
  - Chance d'interception augmentée par niveau
  - Installation : Garage

#### 3. Modules d'Armement
- **Canons laser** (Niveaux 1-5)
  - Dégâts : 10/20/40/80/160
  - Précision élevée, portée moyenne
  - Installation : Garage (Niveau 1-3), Zone Industrielle (Niveau 4-5)

- **Lanceurs de missiles** (Niveaux 1-3)
  - Dégâts massifs, munitions limitées
  - Nécessite ravitaillement en missiles
  - Installation : Garage

- **Tourelles automatiques** (Niveaux 1-2)
  - Défense automatique contre petits vaisseaux
  - Nécessite système de ciblage
  - Installation : Garage

#### 4. Modules de Détection
- **Scanner courte portée** (Niveaux 1-3)
  - Détection dans le secteur actuel
  - Niveau 1 : Objets > 1000 tonnes
  - Niveau 2 : Objets > 100 tonnes
  - Niveau 3 : Objets > 10 tonnes
  - Installation : Garage

- **Scanner longue portée** (Niveaux 1-3)
  - Détection multi-secteurs
  - Portée : 1/3/5 secteurs
  - Installation : Garage

- **Scanner d'analyse** (Niveaux 1-2)
  - Analyse détaillée des objets détectés
  - Composition, ressources, dangerosité
  - Installation : Garage ou Zone Industrielle

#### 5. Modules de Support
- **Baie de cargaison étendue** (Niveaux 1-3)
  - Capacité : +50/+100/+200 tonnes
  - Installation permanente
  - Installation : Zone Industrielle

- **Système de recyclage** (Niveaux 1-2)
  - Récupération d'eau et d'oxygène
  - Réduit les besoins en ravitaillement
  - Installation : Zone Industrielle

- **Générateur énergétique** (Niveaux 1-5)
  - Production : 100/200/400/800/1600 unités/jour
  - Alimentation des systèmes du vaisseau
  - Installation : Garage

- **Raffinerie embarquée** (Niveau 1)
  - Permet le raffinage de minerais en vol
  - Consomme beaucoup d'énergie
  - Réduit la capacité de cargaison (-50 tonnes)
  - Installation : Zone Industrielle

#### 6. Modules de Vie et Confort
- **Système de survie amélioré** (Niveaux 1-3)
  - Augmente l'autonomie en O2 et eau
  - Réduit la consommation par membre d'équipage
  - Installation : Garage

- **Quartiers d'équipage** (Capacité 2/5/10/20)
  - Augmente le nombre de membres d'équipage possible
  - Améliore le moral de l'équipage
  - Installation : Zone Industrielle

- **Infirmerie** (Niveaux 1-2)
  - Soins médicaux de base à bord
  - Traitement des blessures légères/moyennes
  - Installation : Hôpital ou Zone Industrielle

#### 7. Modules de Navigation
- **Ordinateur de bord** (Niveaux 1-5)
  - Calcul de trajectoires optimisées
  - Bonus au jet de navigation
  - Cartographie automatique
  - Installation : Garage

- **Système de ciblage** (Niveaux 1-3)
  - Améliore la précision des armes
  - Niveau 3 : Ciblage automatique
  - Installation : Garage

- **Balise de détresse** (Niveau 1)
  - Signal d'urgence longue portée
  - Utilisable en cas de panne critique
  - Installation : Garage

#### 8. Modules Spéciaux
- **Module de camouflage** (Niveaux 1-3)
  - Réduit la détectabilité du vaisseau
  - Consommation énergétique élevée
  - Installation : Zone Industrielle (plans requis)

- **Système de minage** (Niveaux 1-3)
  - Extraction de minerais depuis astéroïdes
  - Niveau 3 : Minage planétaire orbital
  - Installation : Zone Industrielle

- **Module de recherche** (Niveau 1)
  - Laboratoire scientifique embarqué
  - Analyse d'échantillons et découvertes
  - Installation : Station de Recherche uniquement

- **Générateur de champ de stase** (Niveau 1)
  - Conservation de marchandises périssables
  - Transport de personnes en stase
  - Installation : Zone Industrielle (plans requis)

#### 9. Modules de Communication
- **Système COM standard** (Niveau 1)
  - Communication intra-système
  - Inclus de base sur tous les vaisseaux
  - Installation : Non applicable (standard)

- **Relais hyperspatial** (Niveaux 1-2)
  - Communication inter-systèmes
  - Niveau 2 : Communication instantanée
  - Installation : Zone Industrielle

- **Brouilleur** (Niveaux 1-2)
  - Brouille les communications ennemies
  - Perturbe les systèmes de ciblage
  - Installation : Station Militaire ou Zone Industrielle

#### Installation et Compatibilité
- Chaque vaisseau a un nombre limité d'**emplacements de modules** selon sa taille :
  - Petit vaisseau (Navette) : 3 emplacements
  - Vaisseau moyen (Cargo) : 6 emplacements
  - Grand vaisseau (Croiseur) : 12 emplacements
- Certains modules sont incompatibles entre eux
- L'installation nécessite :
  - Accès au service approprié (Garage ou Zone Industrielle)
  - Crédits suffisants
  - Temps d'installation (varie selon le module : 1h à 48h)
  - Parfois : Réputation minimale, plans techniques, ou autorisation spéciale

#### Coûts indicatifs (en crédits)
- Modules Niveau 1 : 1 000 - 5 000 cr
- Modules Niveau 2 : 5 000 - 15 000 cr
- Modules Niveau 3 : 15 000 - 50 000 cr
- Modules Niveau 4 : 50 000 - 150 000 cr
- Modules Niveau 5 : 150 000 - 500 000 cr
- Modules Spéciaux : 10 000 - 1 000 000 cr

---

### Base de Données
Champs importants :
- **Vaisseau** : `arrime_a_station_id`, `derniere_manoeuvre`
- **Personnage** : `dans_station_id`, `vaisseau_actif_id`
- **Station** : `accessible`, `commerciale`, `industrielle`, `militaire`, `reparations`, `ravitaillement`, `medical`
