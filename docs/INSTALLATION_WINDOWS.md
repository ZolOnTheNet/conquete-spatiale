# Guide d'Installation - Windows 11

Ce guide vous permettra de configurer rapidement votre environnement de développement sur Windows 11 pour travailler sur le projet "Conquête Spatiale".

## Prérequis

### 1. Installer PHP 8.2+

> **Note importante** : Ce projet utilise **SQLite par défaut sur Windows** pour simplifier l'installation. Aucune configuration de base de données MySQL/MariaDB n'est nécessaire !

**Option A : Via XAMPP (Recommandé pour débutants - Simple et Complet)**
1. Télécharger XAMPP : https://www.apachefriends.org/fr/download.html
2. **Installer XAMPP avec PHP 8.2.12** (dernière version Windows disponible)
3. Ajouter PHP au PATH :
   - Ouvrir les "Variables d'environnement système"
   - Modifier la variable `Path`
   - Ajouter : `C:\xampp\php`

> ✅ **XAMPP 8.2.12 est parfaitement adapté pour Laravel 12 !**

**Option B : Via PHP 8.3 standalone (Pour avoir la toute dernière version)**

📖 **Guide complet** : [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)

**Lien direct - PHP 8.3.15 pour Windows (Non Thread Safe - recommandé pour Laravel)** :
```
https://windows.php.net/downloads/releases/php-8.3.15-nts-Win32-vs16-x64.zip
```

Installation rapide :
1. Télécharger le ZIP ci-dessus
2. Extraire dans `C:\php`
3. Copier `php.ini-development` vers `php.ini`
4. Activer les extensions nécessaires dans `php.ini` :
   ```ini
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=openssl
   extension=pdo_sqlite
   extension=sqlite3
   extension=zip
   ```
5. Ajouter `C:\php` au PATH système

> 📝 Pour les détails complets, consultez [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)

**Vérification :**
```bash
php -v
```
Vous devriez voir PHP 8.2.x ou supérieur.

### 2. Installer Composer

1. Télécharger : https://getcomposer.org/Composer-Setup.exe
2. Exécuter l'installeur (il détectera automatiquement PHP)
3. Redémarrer le terminal

**Vérification :**
```bash
composer --version
```

### 3. Installer Node.js

1. Télécharger la version LTS : https://nodejs.org/
2. Installer avec les options par défaut
3. Redémarrer le terminal

**Vérification :**
```bash
node -v
npm -v
```

### 4. Installer Git (si pas déjà installé)

1. Télécharger : https://git-scm.com/download/win
2. Installer avec les options par défaut
3. Configurer Git :
   ```bash
   git config --global user.name "Votre Nom"
   git config --global user.email "votre@email.com"
   ```

### 5. Installer VSCodium (Éditeur recommandé)

**VSCodium** est une version open-source de VS Code sans télémétrie Microsoft.

1. Télécharger : https://vscodium.com/
2. Installer avec les options par défaut

**Extensions recommandées :**
- PHP Intelephense
- Laravel Extra Intellisense
- Tailwind CSS IntelliSense
- EditorConfig for VS Code
- GitLens

## Installation du Projet

### 1. Cloner le Projet

```bash
# Naviguer vers votre dossier de projets
cd C:\Users\VotreNom\Documents\devlog\php

# Cloner le repository
git clone https://github.com/ZolOnTheNet/conquete-spatiale.git
cd conquete-spatiale

# Se placer sur la branche de développement
git checkout dev
```

### 2. Installation Manuelle (Étape par Étape)

**Note importante** : Sur Windows avec Git Bash (recommandé), utilisez les commandes Linux-style.

```bash
# 1. Installer les dépendances PHP
composer install

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Générer la clé d'application
php artisan key:generate

# 4. Créer la base de données SQLite
touch database/database.sqlite

# 5. Exécuter les migrations avec le seeder de jeu
php artisan migrate:fresh --seed --seeder=GameSeeder

# 6. Installer les dépendances Node.js
npm install

# 7. Compiler les assets
npm run build
```

**Si vous utilisez PowerShell ou CMD** (au lieu de Git Bash), remplacez :
- `cp .env.example .env` par `copy .env.example .env`
- `touch database/database.sqlite` par `type nul > database\database.sqlite`

### 3. Vérifier la Configuration

Le fichier `.env` devrait contenir :
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
```

> ✅ **SQLite est configuré par défaut**, aucune configuration de base de données supplémentaire n'est nécessaire !

## Configuration VSCodium - Encodage UTF-8

**IMPORTANT** : Ce projet utilise l'encodage UTF-8 et les fins de lignes LF (Linux) sur tous les systèmes, y compris Windows.

### Fichiers de configuration déjà présents

Le projet contient déjà :
1. **`.editorconfig`** - Configuration universelle
2. **`.vscode/settings.json`** - Configuration VSCodium/VS Code
3. **`.gitattributes`** - Normalisation Git

### Vérifier l'encodage dans VSCodium

**Barre de statut (en bas à droite)** devrait afficher :
```
UTF-8    LF    Spaces: 4
```

**Si vous voyez `CRLF` au lieu de `LF` :**
1. Cliquer sur `CRLF` dans la barre de statut
2. Sélectionner `LF`
3. Sauvegarder le fichier

**Si vous voyez un autre encodage que `UTF-8` :**
1. Cliquer sur l'encodage affiché
2. Choisir "Reopen with Encoding"
3. Sélectionner "UTF-8"
4. Sauvegarder

### Scripts de correction d'encodage

Si vous rencontrez des problèmes d'encodage UTF-8 dans les fichiers markdown :

```bash
# Corriger les doubles encodages (caractères bizarres)
python fix-double-encoding.py

# Convertir les fins de lignes Windows (CRLF) vers Linux (LF)
python text2linux.py
```

📖 **Guide complet** : Voir [GUIDE_VSCODIUM.md](../GUIDE_VSCODIUM.md) à la racine du projet.

## Lancement du Projet

### Option 1 : Lancement manuel (Recommandé pour débuter)

**Terminal 1 - Serveur Laravel :**
```bash
php artisan serve
```
Le serveur démarre sur http://localhost:8000

**Terminal 2 - Vite (hot reload des assets) :**
```bash
npm run dev
```

**Terminal 3 - Queue worker (optionnel) :**
```bash
php artisan queue:listen
```

### Option 2 : Lancement via Composer (Tous les services)

```bash
composer dev
```

Cette commande lance automatiquement :
- Serveur Laravel (port 8000)
- Worker de queue
- Logs en temps réel
- Vite (hot reload)

## Commandes Utiles

### Développement

```bash
# Lancer le serveur de développement
php artisan serve

# Lancer Vite pour le hot-reload
npm run dev

# Compiler les assets pour production
npm run build

# Exécuter les tests
php artisan test
```

### Base de données

```bash
# Exécuter les migrations
php artisan migrate

# Réinitialiser la base de données
php artisan migrate:fresh

# Réinitialiser avec le seeder de jeu
php artisan migrate:fresh --seed --seeder=GameSeeder

# Voir la structure de la base SQLite
php artisan db:show
php artisan db:table stars
```

### Cache

```bash
# Vider tous les caches
php artisan optimize:clear

# Ou individuellement
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Git

```bash
# Récupérer les dernières modifications
git pull origin dev

# Voir l'état des fichiers
git status

# Commit et push
git add .
git commit -m "Description des modifications"
git push origin dev
```

## Synchronisation entre Ubuntu et Windows

Pour travailler de manière fluide entre les deux systèmes :

### 1. Toujours Pull avant de travailler
```bash
git pull
```

### 2. Commit régulièrement
```bash
git add .
git commit -m "Description claire"
git push
```

### 3. Fichiers à ne pas commiter

Le fichier `.gitignore` gère déjà cela, mais vérifiez que ces fichiers ne sont jamais commités :
- `.env` (configuration locale)
- `node_modules/`
- `vendor/`
- `database/database.sqlite`

### 4. Après un Pull sur Windows

Si vous pullez des modifications depuis Ubuntu :
```bash
# Mettre à jour les dépendances si composer.json a changé
composer install

# Mettre à jour les dépendances Node si package.json a changé
npm install

# Exécuter les nouvelles migrations si nécessaire
php artisan migrate
```

### 5. Attention aux fins de lignes

Git est configuré pour normaliser automatiquement les fins de lignes :
- **Dans le repository** : Toujours LF (Linux)
- **Sur Windows** : Git peut convertir en CRLF au checkout (mais pas obligatoire)
- **VSCodium** : Force LF pour la cohérence

Si vous avez des problèmes :
```bash
# Reconvertir tous les fichiers vers LF
python text2linux.py
```

## Résolution des Problèmes Courants

### PHP n'est pas reconnu
- Vérifier que PHP est bien dans le PATH système
- Redémarrer le terminal/PowerShell/Git Bash
- Tester avec `php -v`

### Extension PHP manquante
- Ouvrir `php.ini` (dans `C:\xampp\php\` ou `C:\php\`)
- Décommenter (retirer le `;`) devant l'extension nécessaire
- Exemple : `;extension=pdo_sqlite` → `extension=pdo_sqlite`
- Redémarrer le serveur

**Extensions requises pour ce projet :**
```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

### Port 8000 déjà utilisé
```bash
# Utiliser un autre port
php artisan serve --port=8001
```

### SQLite ne fonctionne pas
- Vérifier que le fichier `database/database.sqlite` existe
- Si non : `touch database/database.sqlite` (Git Bash) ou `type nul > database\database.sqlite` (CMD)
- Vérifier que l'extension SQLite est activée dans `php.ini`
- Vérifier les permissions du dossier `database/`

### Erreurs lors de `php artisan migrate:fresh --seed --seeder=GameSeeder`

**Erreur "APPLICATION IN PRODUCTION"** :
- Vérifier que `.env` contient `APP_ENV=local`
- Vérifier que `.env` contient `APP_DEBUG=true`

**Erreur de connexion à la base de données** :
- Vérifier que `database/database.sqlite` existe
- Vérifier que `.env` contient `DB_CONNECTION=sqlite`

### npm install échoue
```bash
# Nettoyer le cache npm
npm cache clean --force
npm install
```

### Caractères bizarres dans les fichiers (é → Ã©, etc.)

C'est un problème de double encodage UTF-8 :
```bash
# Corriger automatiquement
python fix-double-encoding.py
```

## Différences Ubuntu vs Windows

### Chemins de fichiers
- Ubuntu : `/` (slash)
- Windows : `\` (backslash)
- Laravel gère cela automatiquement via les helpers
- **Recommandation** : Utiliser Git Bash qui accepte les `/` comme sur Linux

### Permissions
- Sur Windows, généralement pas de problèmes de permissions
- Sur Ubuntu, parfois besoin de `chmod 755` ou `chmod 644`

### Variables d'environnement
- Les deux systèmes utilisent le même `.env`
- Faire attention aux chemins absolus si vous en définissez

### Base de données
- **Windows** : SQLite (simplifié, aucune configuration)
- **Ubuntu/Production** : MySQL ou PostgreSQL (performances)

## Travailler avec différentes versions de PHP

### Question : Puis-je avoir PHP 8.2.12 (XAMPP) sur Windows et 8.3 sur Ubuntu ?

**Réponse courte** : Oui, et **c'est parfaitement OK pour Laravel 12** !

### Contexte

XAMPP pour Windows s'arrête actuellement à **PHP 8.2.12**. C'est la version la plus simple à installer.

**Bonne nouvelle** : Laravel 12 fonctionne parfaitement avec PHP 8.2+ et 8.3. Les différences entre les deux versions sont minimes.

### Deux Options

**Option 1 : XAMPP 8.2.12 (Recommandé si vous débutez)**
- ✅ Simple à installer
- ✅ Inclut Apache, MySQL, phpMyAdmin
- ✅ Parfaitement compatible avec Laravel 12
- ✅ Pas de configuration complexe

**Option 2 : PHP 8.3 Standalone (Pour les développeurs expérimentés)**
- ✅ Dernière version de PHP
- ✅ Plus léger (pas d'Apache, MySQL)
- ⚠️ Configuration manuelle requise
- 📖 Voir : [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)

### Si vous utilisez des versions différentes (8.2 vs 8.3)

**Bonnes pratiques** :

1. **Développez avec la version la plus basse (8.2.12)** :
   - Codez sur Windows avec XAMPP 8.2.12
   - Testez sur Ubuntu avec 8.3
   - Jamais l'inverse

2. **Évitez les fonctionnalités PHP 8.3-only** :
   - Ne pas utiliser `json_validate()` (nouveau en 8.3)
   - Ne pas utiliser les nouvelles fonctionnalités Random
   - S'en tenir aux fonctionnalités PHP 8.2

3. **Committez toujours `composer.lock`** :
   - Les dépendances seront identiques sur les deux systèmes
   - Garantit la même version de Laravel et packages

### En Pratique

Pour votre projet "Conquête Spatiale" :

- ✅ **Windows avec XAMPP 8.2.12** : Parfait
- ✅ **Ubuntu avec PHP 8.3** : Parfait
- ✅ **Laravel 12** : Supporte les deux

**Vous ne rencontrerez aucun problème** tant que vous :
- Utilisez les fonctionnalités standard de Laravel
- Ne codez pas avec des fonctions spécifiques à PHP 8.3
- Testez régulièrement sur les deux environnements

### Différences Mineures PHP 8.2 vs 8.3

**Nouvelles en 8.3 (à éviter si vous restez en 8.2)** :
- `json_validate()` → Utiliser `json_decode()` à la place
- Typed class constants → Ne pas typer les constantes
- `Random\Randomizer::getBytesFromString()` → Utiliser les fonctions random classiques

**Pour 99% du code Laravel** : Aucune différence notable

## Structure du Projet

```
conquete-spatiale/
├── app/                  # Code Laravel (Modèles, Contrôleurs, etc.)
├── database/
│   ├── migrations/      # Migrations de la base de données
│   ├── seeders/         # Seeders (GameSeeder pour données initiales)
│   └── database.sqlite  # Base de données SQLite (créée lors de l'installation)
├── docs/                # Documentation du projet
│   ├── game-design/     # Game Design Document
│   ├── INSTALLATION_WINDOWS.md  # Ce fichier
│   └── ...
├── resources/
│   ├── views/           # Templates Blade
│   └── js/              # JavaScript frontend
├── routes/              # Routes web et API
├── .env                 # Configuration locale (non versionné)
├── .editorconfig        # Configuration éditeur
└── .vscode/             # Configuration VSCodium
```

## Support et Documentation

### Documentation du jeu

- **GDD Central** : [docs/game-design/GDD_Central.md](game-design/GDD_Central.md)
- **Guide de démarrage** : [docs/game-design/GUIDE_DEMARRAGE.md](game-design/GUIDE_DEMARRAGE.md)

### Documentation technique

- **Installation PHP détaillée** : [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)
- **Guide VSCodium** : [../GUIDE_VSCODIUM.md](../GUIDE_VSCODIUM.md)
- **Commandes admin** : [COMMANDES_ADMIN.md](COMMANDES_ADMIN.md)

### Aide Laravel

- Documentation officielle : https://laravel.com/docs
- Laracasts (tutoriels vidéo) : https://laracasts.com

## Checklist d'Installation

- [ ] PHP 8.2+ installé et dans le PATH
- [ ] Composer installé
- [ ] Node.js et npm installés
- [ ] Git installé et configuré
- [ ] VSCodium installé avec extensions
- [ ] Repository cloné
- [ ] `composer install` exécuté
- [ ] Fichier `.env` créé (copié depuis `.env.example`)
- [ ] `php artisan key:generate` exécuté
- [ ] Base de données SQLite créée (`database/database.sqlite`)
- [ ] `php artisan migrate:fresh --seed --seeder=GameSeeder` exécuté
- [ ] `npm install` exécuté
- [ ] `npm run build` exécuté
- [ ] Encodage UTF-8 et LF vérifiés dans VSCodium

## Prochaines Étapes

Une fois l'installation terminée :

1. **Lancer le serveur** : `php artisan serve`
2. **Ouvrir le navigateur** : http://localhost:8000
3. **Créer un compte** et commencer à jouer !
4. **Consulter la documentation** : `docs/game-design/`
5. **Rejoindre le développement** : Voir `TODO.md` pour les tâches en cours

---

**Dernière mise à jour** : 2025-11-26
**Version** : 1.1
