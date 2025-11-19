# Commandes de jeu - Conquete Spatiale

Liste complete des commandes disponibles dans le jeu.

## Commandes Generales

| Commande | Alias | Description |
|----------|-------|-------------|
| `help` | `aide` | Affiche la liste des commandes disponibles |
| `status` | `statut` | Affiche le statut du personnage (PA, niveau, XP) |

## Navigation et Exploration

| Commande | Alias | Description |
|----------|-------|-------------|
| `position` | `pos` | Affiche la position actuelle du vaisseau |
| `vaisseau` | `ship` | Affiche les details du vaisseau actif |
| `carte` | `map` | Affiche la carte des systemes decouverts |
| `deplacer <x> <y> <z>` | `move` | Deplace le vaisseau en sous-lumiere |
| `saut <systeme>` | `jump` | Effectue un saut hyperespace vers un systeme |
| `scan` | `scanner` | Scanne la zone pour decouvrir des systemes |

## Economie et Ressources

| Commande | Alias | Description |
|----------|-------|-------------|
| `scan-planete` | `scanp` | Scanne une planete pour ses gisements |
| `extraire <ressource>` | `mine` | Extrait une ressource d'un gisement |
| `inventaire` | `inv` | Affiche l'inventaire du vaisseau |
| `marche` | `market` | Affiche le marche de la station actuelle |
| `acheter <ressource> <quantite>` | `buy` | Achete une ressource au marche |
| `vendre <ressource> <quantite>` | `sell` | Vend une ressource au marche |
| `prix` | `prices` | Affiche les prix du marche local |

## Fabrication

| Commande | Alias | Description |
|----------|-------|-------------|
| `recettes` | `recipes` | Liste les recettes de fabrication disponibles |
| `fabriquer <recette>` | `craft` | Fabrique un objet selon une recette |

## Combat

| Commande | Alias | Description |
|----------|-------|-------------|
| `armes` | `weapons` | Liste les armes disponibles |
| `boucliers` | `shields` | Liste les boucliers disponibles |
| `equiper <type> <id>` | `equip` | Equipe une arme ou un bouclier |
| `etat-combat` | `combat` | Affiche l'etat de combat du vaisseau |
| `reparer` | `repair` | Repare les degats du vaisseau |
| `scanner-ennemis` | `scane` | Scanne la zone pour des ennemis |
| `ennemis` | `enemies` | Liste les types d'ennemis connus |
| `attaquer <ennemi_id>` | `attack` | Attaque un ennemi detecte |
| `fuir` | `flee` | Tente de fuir un combat |

## Missions et Factions

| Commande | Alias | Description |
|----------|-------|-------------|
| `missions` | `quests` | Liste les missions disponibles et en cours |
| `missions disponibles` | - | Filtre: missions disponibles uniquement |
| `missions en-cours` | - | Filtre: missions en cours uniquement |
| `mission-accepter <id>` | `accept` | Accepte une mission |
| `mission-rendre <id>` | `complete` | Rend une mission completee |
| `mission-abandonner <id>` | `abandon` | Abandonne une mission (penalite reputation) |
| `factions` | - | Liste les factions connues |
| `reputation` | `rep` | Affiche vos reputations avec les factions |

## Systeme Daggerheart

| Commande | Alias | Description |
|----------|-------|-------------|
| `lancer [competence]` | `roll` | Lance les des (2d12 Hope/Fear) |

## Points d'Action (PA)

Chaque action consomme des PA. La recuperation est automatique:
- 1 PA recupere par heure (configurable)
- Maximum: 36 PA
- Depart: 24 PA

Le chrono de recuperation demarre a la premiere depense de PA.

## Notes

- Les commandes sont insensibles a la casse
- Les arguments entre `<>` sont obligatoires
- Les arguments entre `[]` sont optionnels
- Plusieurs alias peuvent etre utilises pour une meme commande
