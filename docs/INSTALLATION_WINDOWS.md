# Guide d'Installation - Windows 11

Ce guide vous permettra de configurer rapidement votre environnement de développement sur Windows 11 pour travailler sur le projet "Conquête Spatiale".

## Prérequis

### 1. Installer PHP 8.2+

> **Note importante** : XAMPP pour Windows s'arrête actuellement à **PHP 8.2.12** - ce qui est **parfait pour Laravel 12** ! Si vous voulez absolument PHP 8.3, utilisez l'installation standalone (Option B).

**Option A : Via XAMPP (Recommandé pour débutants - Simple et Complet)**
1. Télécharger XAMPP : https://www.apachefriends.org/fr/download.html
2. **Installer XAMPP avec PHP 8.2.12** (dernière version Windows disponible)
3. Ajouter PHP au PATH :
   - Ouvrir les "Variables d'environnement système"
   - Modifier la variable `Path`
   - Ajouter : `C:\xampp\php`

> ✅ **XAMPP 8.2.12 est parfaitement adapté pour Laravel 12 !**

**Option B : Via PHP 8.3 standalone (Pour avoir la toute dernière version)**

📘 **Guide complet** : [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)

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

> 📖 Pour les détails complets, consultez [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)

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

## Installation du Projet

### 1. Cloner le Projet

```bash
# Naviguer vers votre dossier de projets
cd C:\Users\VotreNom\Documents\Projets

# Cloner le repository
git clone https://github.com/ZolOnTheNet/conquete-spatiale.git
cd conquete-spatiale

# Se placer sur la branche de développement
git checkout claude/setup-windows-dev-01ALQ5gscjmMzXQXmaL42LNo
```

### 2. Installation Automatique

**Option facile - Utiliser le script PowerShell :**

```powershell
.\scripts\setup-windows.ps1
```

**OU Installation manuelle :**

```bash
# Installer les dépendances PHP
composer install

# Copier le fichier d'environnement
copy .env.example .env

# Générer la clé d'application
php artisan key:generate

# Créer la base de données SQLite
type nul > database\database.sqlite

# Exécuter les migrations
php artisan migrate

# Installer les dépendances Node.js
npm install

# Compiler les assets
npm run build
```

## Lancement du Projet

### Option 1 : Lancement automatique (Recommandé)

Utiliser le script batch pour démarrer tous les services :

```bash
.\scripts\start-dev.bat
```

Ce script lance :
- Le serveur Laravel (http://localhost:8000)
- Le worker de queue
- Les logs en temps réel
- Vite pour le hot-reload des assets

### Option 2 : Lancement manuel

**Terminal 1 - Serveur Laravel :**
```bash
php artisan serve
```

**Terminal 2 - Vite (dev assets) :**
```bash
npm run dev
```

**Terminal 3 - Queue worker (optionnel) :**
```bash
php artisan queue:listen
```

### Option 3 : Lancement via Composer

```bash
composer dev
```

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
composer test
# ou
php artisan test
```

### Base de données
```bash
# Exécuter les migrations
php artisan migrate

# Réinitialiser la base de données
php artisan migrate:fresh

# Avec seeders
php artisan migrate:fresh --seed
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
git pull origin claude/setup-windows-dev-01ALQ5gscjmMzXQXmaL42LNo

# Voir l'état des fichiers
git status

# Commit et push
git add .
git commit -m "Description des modifications"
git push -u origin claude/setup-windows-dev-01ALQ5gscjmMzXQXmaL42LNo
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

## Résolution des Problèmes Courants

### PHP n'est pas reconnu
- Vérifier que PHP est bien dans le PATH système
- Redémarrer le terminal/PowerShell

### Extension PHP manquante
- Ouvrir `php.ini`
- Décommenter (retirer le `;`) devant l'extension nécessaire
- Exemple : `;extension=pdo_sqlite` → `extension=pdo_sqlite`
- Redémarrer le serveur

### Port 8000 déjà utilisé
```bash
# Utiliser un autre port
php artisan serve --port=8001
```

### Problèmes de permissions
Sous Windows, exécuter PowerShell/CMD en tant qu'Administrateur si nécessaire.

### SQLite ne fonctionne pas
- Vérifier que le fichier `database/database.sqlite` existe
- Si non : `type nul > database\database.sqlite`
- Vérifier que l'extension SQLite est activée dans `php.ini`

### npm install échoue
```bash
# Nettoyer le cache npm
npm cache clean --force
npm install
```

## Scripts PowerShell

### Politique d'exécution

Si les scripts PowerShell ne s'exécutent pas :
```powershell
# Exécuter en tant qu'Administrateur
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

## Différences Ubuntu vs Windows

### Chemins de fichiers
- Ubuntu : `/` (slash)
- Windows : `\` (backslash)
- Laravel gère cela automatiquement via les helpers

### Permissions
- Sur Windows, généralement pas de problèmes de permissions
- Sur Ubuntu, parfois besoin de `chmod`

### Variables d'environnement
- Les deux systèmes utilisent le même `.env`
- Faire attention aux chemins absolus si vous en définissez

## Éditeurs Recommandés

- **VS Code** : https://code.visualstudio.com/
  - Extensions utiles :
    - PHP Intelephense
    - Laravel Extra Intellisense
    - Tailwind CSS IntelliSense
    - ESLint
    - GitLens

- **PhpStorm** : https://www.jetbrains.com/phpstorm/
  - Support Laravel intégré

## Travailler avec différentes versions de PHP

### Question : Puis-je avoir PHP 8.2.12 (XAMPP) sur Windows et 8.3 sur Ubuntu ?

**Réponse courte** : Oui, et **c'est parfaitement OK pour Laravel 12** !

### Contexte

XAMPP pour Windows s'arrête actuellement à **PHP 8.2.12**. C'est la seule version facile disponible avec XAMPP.

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
- 📘 Voir : [INSTALLATION_PHP_WINDOWS.md](INSTALLATION_PHP_WINDOWS.md)

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

3. **Vérifiez la compatibilité** :
   ```bash
   php scripts\check-php-version.php
   ```

4. **Committez toujours `composer.lock`** :
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

## Support

Pour plus d'informations sur le jeu, consultez :
- `/docs/game-design/GDD_Central.md` - Documentation principale
- `/docs/game-design/GUIDE_DEMARRAGE.md` - Guide de démarrage du projet
