# Structure des Objets

Ce document décrit la structure des objets principaux de la base de données pour le jeu Conquête Galactique.

## Vaisseau

### Table: `vaisseaux`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du vaisseau | Auto-incrément |
| objet_spatial_id | bigint(20) unsigned | Identifiant de l'objet spatial associé | - |
| modele | varchar(255) | Modèle du vaisseau | 'A-0' |
| type_propulsion | int(11) | Type de propulsion | 1 |
| mode | varchar(255) | Mode de propulsion | 'energetique' |
| reserve | decimal(10,2) | Réserve d'énergie | 0.00 |
| coque_max | int(11) | Coque maximale | 100 |
| coque_actuelle | int(11) | Coque actuelle | 100 |
| energie_actuelle | decimal(10,2) | Énergie actuelle | 0.00 |
| vitesse_conventionnelle | decimal(8,2) | Vitesse conventionnelle | 1.00 |
| vitesse_saut | decimal(8,2) | Vitesse de saut | 10.00 |
| part_panne | int(11) | Part de panne | 0 |
| init_conventionnel | decimal(8,2) | Initialisation conventionnelle | 0.00 |
| init_hyperespace | decimal(8,2) | Initialisation hyperespace | 200.00 |
| coef_conventionnel | decimal(8,2) | Coefficient conventionnel | 1.00 |
| coef_hyperespace | decimal(8,2) | Coefficient hyperespace | 1.00 |
| coef_pa_mn | decimal(8,2) | Coefficient PA MN | 1.00 |
| coef_pa_he | decimal(8,2) | Coefficient PA HE | 0.20 |
| max_soutes | int(11) | Nombre maximal de soutes | 0 |
| place_soute | int(11) | Place dans les soutes | 0 |
| masse_variable | decimal(10,2) | Masse variable | 0.00 |
| nb_armes | int(11) | Nombre d'armes | 0 |
| vetuste | int(11) | Niveau de vétusté | 0 |
| complexite_fct | int(11) | Complexité fonctionnelle | 1 |
| score_panne | int(11) | Score de panne | 0 |
| score_entretien | int(11) | Score d'entretien | 0 |
| system_informatique | int(11) | Système informatique | 0 |
| portee_scan | decimal(6,2) | Portée du scan | 5.00 |
| puissance_scan | int(11) | Puissance du scan | 100 |
| bonus_scan | int(11) | Bonus de scan | 0 |
| scan_niveau_actuel | int(11) | Niveau de scan actuel | 0 |
| scan_secteur_x | int(11) | Secteur X du scan | NULL |
| scan_secteur_y | int(11) | Secteur Y du scan | NULL |
| scan_secteur_z | int(11) | Secteur Z du scan | NULL |
| scan_position_x | decimal(10,3) | Position X du scan | NULL |
| scan_position_y | decimal(10,3) | Position Y du scan | NULL |
| scan_position_z | decimal(10,3) | Position Z du scan | NULL |
| bouclier_actuel | int(11) | Bouclier actuel | 0 |
| esquive | int(11) | Esquive | 10 |
| bonus_precision | int(11) | Bonus de précision | 0 |
| en_combat | tinyint(1) | En combat | 0 |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `energie_actuelle` est utilisé pour stocker l'énergie actuelle du vaisseau.
- Le champ `reserve` est utilisé comme énergie maximale pour le rechargement.
- Le champ `energie_max` n'existe pas dans la table `vaisseaux`.

## Système Stellaire

### Table: `systemes_stellaires`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du système stellaire | Auto-incrément |
| nom | varchar(255) | Nom du système stellaire | - |
| type_etoile | varchar(255) | Type d'étoile | - |
| couleur | varchar(255) | Couleur de l'étoile | - |
| temperature | decimal(10,2) | Température de l'étoile | - |
| puissance | decimal(10,2) | Puissance de l'étoile | - |
| puissance_solaire | decimal(10,2) | Puissance solaire | - |
| detectabilite_base | decimal(10,2) | Détectabilité de base | - |
| masse_solaire | decimal(10,2) | Masse solaire | - |
| rayon_solaire | decimal(10,2) | Rayon solaire | - |
| secteur_x | int(11) | Secteur X | - |
| secteur_y | int(11) | Secteur Y | - |
| secteur_z | int(11) | Secteur Z | - |
| position_x | decimal(10,3) | Position X | - |
| position_y | decimal(10,3) | Position Y | - |
| position_z | decimal(10,3) | Position Z | - |
| nb_planetes | int(11) | Nombre de planètes | - |
| explore | tinyint(1) | Exploré | - |
| habite | tinyint(1) | Habité | - |
| poi_connu | tinyint(1) | POI connu | - |
| description | longtext | Description | - |
| donnees_supplementaires | longtext | Données supplémentaires | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `puissance_solaire` est utilisé pour calculer la puissance solaire locale.

## Personnage

### Table: `personnages`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du personnage | Auto-incrément |
| compte_id | bigint(20) unsigned | Identifiant du compte | - |
| nom | varchar(255) | Nom du personnage | - |
| prenom | varchar(255) | Prénom du personnage | NULL |
| agilite | int(11) | Agilité | - |
| force | int(11) | Force | - |
| finesse | int(11) | Finesse | - |
| instinct | int(11) | Instinct | - |
| presence | int(11) | Présence | - |
| savoir | int(11) | Savoir | - |
| competences | longtext | Compétences | - |
| experience | int(11) | Expérience | - |
| niveau | int(11) | Niveau | - |
| jetons_hope | int(11) | Jetons d'espoir | - |
| jetons_fear | int(11) | Jetons de peur | - |
| points_action | int(11) | Points d'action | - |
| max_points_action | int(11) | Points d'action maximaux | - |
| derniere_recuperation_pa | timestamp | Dernière récupération de PA | NULL |
| dans_station_id | bigint(20) unsigned | Identifiant de la station | NULL |
| dans_vaisseau_id | bigint(20) unsigned | Identifiant du vaisseau | NULL |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `points_action` est utilisé pour stocker les points d'action actuels du personnage.
- Le champ `max_points_action` est utilisé pour stocker les points d'action maximaux du personnage.

## Station

### Table: `stations`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la station | Auto-incrément |
| nom | varchar(255) | Nom de la station | - |
| systeme_stellaire_id | bigint(20) unsigned | Identifiant du système stellaire | - |
| accessible | tinyint(1) | Accessible | - |
| ravitaillement | tinyint(1) | Ravitaillement disponible | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `ravitaillement` indique si la station offre des services de ravitaillement.

## Objet Spatial

### Table: `objets_spatiaux`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de l'objet spatial | Auto-incrément |
| secteur_x | int(11) | Secteur X | - |
| secteur_y | int(11) | Secteur Y | - |
| secteur_z | int(11) | Secteur Z | - |
| position_x | decimal(10,3) | Position X | - |
| position_y | decimal(10,3) | Position Y | - |
| position_z | decimal(10,3) | Position Z | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Les champs `secteur_x`, `secteur_y`, et `secteur_z` sont utilisés pour stocker les coordonnées du secteur.
- Les champs `position_x`, `position_y`, et `position_z` sont utilisés pour stocker les coordonnées de la position.

## Ressource

### Table: `ressources`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la ressource | Auto-incrément |
| code | varchar(255) | Code de la ressource | - |
| nom | varchar(255) | Nom de la ressource | - |
| categorie | varchar(255) | Catégorie de la ressource | - |
| unite | varchar(255) | Unité de la ressource | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `code` est utilisé pour identifier la ressource.
- Le champ `nom` est utilisé pour afficher le nom de la ressource.
- Le champ `categorie` est utilisé pour regrouper les ressources par catégorie.

## Gisement

### Table: `gisements`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du gisement | Auto-incrément |
| planete_id | bigint(20) unsigned | Identifiant de la planète | - |
| ressource_id | bigint(20) unsigned | Identifiant de la ressource | - |
| quantite | decimal(15,2) | Quantité disponible | - |
| taux_extraction | decimal(6,2) | Taux d'extraction | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `quantite` est utilisé pour stocker la quantité disponible de la ressource.
- Le champ `taux_extraction` est utilisé pour calculer le taux d'extraction de la ressource.

## Planète

### Table: `planetes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la planète | Auto-incrément |
| systeme_stellaire_id | bigint(20) unsigned | Identifiant du système stellaire | - |
| nom | varchar(255) | Nom de la planète | - |
| type | varchar(255) | Type de planète | - |
| masse | decimal(10,2) | Masse de la planète | - |
| rayon | decimal(10,2) | Rayon de la planète | - |
| distance_etoile | decimal(10,2) | Distance à l'étoile | - |
| periode_orbite | decimal(10,2) | Période d'orbite | - |
| temperature | decimal(10,2) | Température de la planète | - |
| atmosphere | varchar(255) | Atmosphère de la planète | - |
| gravite | decimal(10,2) | Gravité de la planète | - |
| jour_solaire | decimal(10,2) | Jour solaire | - |
| annee_sidérale | decimal(10,2) | Année sidérale | - |
| inclinaison_axiale | decimal(10,2) | Inclinaison axiale | - |
| excentricite | decimal(10,2) | Excentricité | - |
| pression_atmospherique | decimal(10,2) | Pression atmosphérique | - |
| composition_atmosphere | longtext | Composition de l'atmosphère | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `distance_etoile` est utilisé pour calculer la distance de la planète à son étoile.
- Le champ `type` est utilisé pour classifier la planète (par exemple, tellurique, gazeuse, etc.).

## Combat

### Table: `combats`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du combat | Auto-incrément |
| attaquant_id | bigint(20) unsigned | Identifiant de l'attaquant | - |
| defenseur_id | bigint(20) unsigned | Identifiant du défenseur | - |
| en_cours | tinyint(1) | Combat en cours | - |
| tour_actuel | int(11) | Tour actuel | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `en_cours` indique si le combat est en cours.
- Le champ `tour_actuel` est utilisé pour suivre le tour actuel du combat.

## Mission

### Table: `missions`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la mission | Auto-incrément |
| titre | varchar(255) | Titre de la mission | - |
| description | longtext | Description de la mission | - |
| type | varchar(255) | Type de mission | - |
| difficulté | int(11) | Difficulté de la mission | - |
| recompense_credits | int(11) | Récompense en crédits | - |
| recompense_xp | int(11) | Récompense en XP | - |
| duree_limite | int(11) | Durée limite de la mission | - |
| cooldown | int(11) | Cooldown de la mission | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `recompense_credits` est utilisé pour stocker la récompense en crédits.
- Le champ `recompense_xp` est utilisé pour stocker la récompense en XP.

## Réputation

### Table: `reputations`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la réputation | Auto-incrément |
| personnage_id | bigint(20) unsigned | Identifiant du personnage | - |
| faction_id | bigint(20) unsigned | Identifiant de la faction | - |
| valeur | int(11) | Valeur de la réputation | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `valeur` est utilisé pour stocker la valeur de la réputation.

## Faction

### Table: `factions`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la faction | Auto-incrément |
| nom | varchar(255) | Nom de la faction | - |
| description | longtext | Description de la faction | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `nom` est utilisé pour afficher le nom de la faction.
- Le champ `description` est utilisé pour décrire la faction.

## Marché

### Table: `marches`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du marché | Auto-incrément |
| station_id | bigint(20) unsigned | Identifiant de la station | - |
| ressource_id | bigint(20) unsigned | Identifiant de la ressource | - |
| prix_achat | decimal(10,2) | Prix d'achat | - |
| prix_vente | decimal(10,2) | Prix de vente | - |
| quantite | decimal(15,2) | Quantité disponible | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `prix_achat` est utilisé pour stocker le prix d'achat de la ressource.
- Le champ `prix_vente` est utilisé pour stocker le prix de vente de la ressource.
- Le champ `quantite` est utilisé pour stocker la quantité disponible de la ressource.

## Recette

### Table: `recettes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la recette | Auto-incrément |
| code | varchar(255) | Code de la recette | - |
| nom | varchar(255) | Nom de la recette | - |
| categorie | varchar(255) | Catégorie de la recette | - |
| ingredients | longtext | Ingrédients de la recette | - |
| resultats | longtext | Résultats de la recette | - |
| temps_fabrication | int(11) | Temps de fabrication | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `ingredients` est utilisé pour stocker les ingrédients de la recette.
- Le champ `resultats` est utilisé pour stocker les résultats de la recette.
- Le champ `temps_fabrication` est utilisé pour stocker le temps de fabrication de la recette.

## Arme

### Table: `armes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de l'arme | Auto-incrément |
| code | varchar(255) | Code de l'arme | - |
| nom | varchar(255) | Nom de l'arme | - |
| type | varchar(255) | Type de l'arme | - |
| degats | int(11) | Dégâts de l'arme | - |
| portee | int(11) | Portée de l'arme | - |
| precision | int(11) | Précision de l'arme | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `degats` est utilisé pour stocker les dégâts de l'arme.
- Le champ `portee` est utilisé pour stocker la portée de l'arme.
- Le champ `precision` est utilisé pour stocker la précision de l'arme.

## Bouclier

### Table: `boucliers`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du bouclier | Auto-incrément |
| code | varchar(255) | Code du bouclier | - |
| nom | varchar(255) | Nom du bouclier | - |
| type | varchar(255) | Type du bouclier | - |
| puissance | int(11) | Puissance du bouclier | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `puissance` est utilisé pour stocker la puissance du bouclier.

## Programme

### Table: `programmes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du programme | Auto-incrément |
| code | varchar(255) | Code du programme | - |
| nom | varchar(255) | Nom du programme | - |
| type | varchar(255) | Type du programme | - |
| effet | longtext | Effet du programme | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `effet` est utilisé pour décrire l'effet du programme.

## Panne

### Table: `pannes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la panne | Auto-incrément |
| code | varchar(255) | Code de la panne | - |
| nom | varchar(255) | Nom de la panne | - |
| type | varchar(255) | Type de la panne | - |
| effet | longtext | Effet de la panne | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `effet` est utilisé pour décrire l'effet de la panne.

## Découverte

### Table: `decouvertes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique de la découverte | Auto-incrément |
| personnage_id | bigint(20) unsigned | Identifiant du personnage | - |
| systeme_stellaire_id | bigint(20) unsigned | Identifiant du système stellaire | - |
| date_decouverte | timestamp | Date de la découverte | - |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `date_decouverte` est utilisé pour stocker la date de la découverte.

## Compte

### Table: `comptes`

| Champ | Type | Description | Valeur par défaut |
|-------|------|-------------|------------------|
| id | bigint(20) unsigned | Identifiant unique du compte | Auto-incrément |
| name | varchar(255) | Nom du compte | - |
| email | varchar(255) | Email du compte | - |
| email_verified_at | timestamp | Date de vérification de l'email | NULL |
| password | varchar(255) | Mot de passe du compte | - |
| is_admin | tinyint(1) | Est administrateur | - |
| credits | int(11) | Crédits du compte | - |
| remember_token | varchar(100) | Token de rappel | NULL |
| created_at | timestamp | Date de création | NULL |
| updated_at | timestamp | Date de mise à jour | NULL |

### Notes
- Le champ `is_admin` est utilisé pour indiquer si le compte est un administrateur.
- Le champ `credits` est utilisé pour stocker les crédits du compte.

## Conclusion

Ce document décrit la structure des objets principaux de la base de données pour le jeu Conquête Galactique. Il est important de se référer à ce document pour éviter les erreurs de champ inexistant ou de type incorrect.
