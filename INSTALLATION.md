# Installation et Démarrage - Conquête Galactique

## Prérequis

- PHP 8.2+
- Composer
- MySQL 8.0+ ou MariaDB 10.3+
- Node.js et npm (optionnel pour assets)

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/ZolOnTheNet/conquete-spatiale.git
cd conquete-spatiale
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configuration de l'environnement

Le fichier `.env` est déjà configuré. Vérifiez les paramètres de base de données :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=conquete_spatiale
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Créer la base de données

```bash
mysql -u root -p
CREATE DATABASE conquete_spatiale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 5. Exécuter les migrations et seeders

```bash
php artisan migrate:fresh --seed --seeder=GameSeeder
```

Cela va créer :
- Toutes les tables nécessaires
- Un compte de test : `test` / `password`
- Un personnage : John Stark
- Un vaisseau : Explorer-01 (modèle A-0)

### 6. Lancer le serveur de développement

```bash
php artisan serve
```

Le jeu sera accessible à : http://localhost:8000

## Utilisation

### Interface Console

L'interface console est accessible directement sur la page d'accueil.

### Commandes disponibles

- `help` ou `aide` - Afficher l'aide
- `status` ou `statut` - Afficher les stats du personnage
- `position` ou `pos` - Afficher la position actuelle
- `vaisseau` ou `ship` - Afficher les infos du vaisseau
- `lancer [competence]` - Lancer les dés (système Daggerheart 2D12)

### Système Daggerheart

Le jeu utilise le système Daggerheart avec :
- 2d12 (Hope + Fear)
- Jetons Hope/Fear
- 6 Traits : Agilité, Force, Finesse, Instinct, Présence, Savoir
- 16 Compétences

## Architecture

### Models

- **Compte** : Gestion des utilisateurs
- **Personnage** : PJ avec traits et compétences
- **ObjetSpatial** : Classe parente pour tous les objets spatiaux
- **Vaisseau** : Vaisseaux avec propulsion et systèmes
- **Base** : Stations spatiales avec modules

### Système de coordonnées

- **Secteur** : Coordonnées entières (x, y, z)
- **Position** : Coordonnées décimales pour précision

### Système de propulsion

Les vaisseaux utilisent des formules selon le GDD :
- Mode conventionnel : `InitConv + (Distance × CoefConv)`
- Hyperespace : `InitHE + (Distance × CoefHE)`
- PA : Calculés selon le mode de déplacement

## Développement à venir (2 jours)

### Priorités Jour 1
- [ ] Système d'authentification
- [ ] Commandes de déplacement fonctionnelles
- [ ] Génération procédurale basique (systèmes stellaires)
- [ ] Détection et exploration

### Priorités Jour 2
- [ ] Système économique basique
- [ ] Commerce simple
- [ ] Amélioration interface (3 panneaux)
- [ ] Tests et debug

## Documentation

Tous les documents de game design sont dans `docs/game-design/` :
- `CONTEXT.md` - Vue d'ensemble et règles de développement
- `GDD_Central.md` - Index principal
- `GDD_Vaisseaux_Complet.md` - Système vaisseaux
- `GDD_Architecture_Technique.md` - Architecture technique
- Et bien d'autres...

## Support

Pour toute question, consultez les GDD dans `docs/game-design/` ou créez une issue sur GitHub.

---

**Version** : Alpha 0.1
**Laravel** : 12
**Système de jeu** : Daggerheart 2D12
