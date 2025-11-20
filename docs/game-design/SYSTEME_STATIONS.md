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

1. **Navigation** : Le joueur navigue vers une station
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

### Base de Données
Champs importants :
- **Vaisseau** : `arrime_a_station_id`, `derniere_manoeuvre`
- **Personnage** : `dans_station_id`, `vaisseau_actif_id`
- **Station** : `accessible`, `commerciale`, `industrielle`, `militaire`, `reparations`, `ravitaillement`, `medical`
