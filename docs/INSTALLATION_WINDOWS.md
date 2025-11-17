# Guide d'Installation - Windows 11

Ce guide vous permettra de configurer rapidement votre environnement de développement sur Windows 11 pour travailler sur le projet "Conquête Spatiale".

## Prérequis

### 1. Installer PHP 8.2+

**Option A : Via XAMPP (Recommandé pour débutants)**
1. Télécharger XAMPP : https://www.apachefriends.org/fr/download.html
2. Installer XAMPP (version PHP 8.2 minimum)
3. Ajouter PHP au PATH :
   - Ouvrir les "Variables d'environnement système"
   - Modifier la variable `Path`
   - Ajouter : `C:\xampp\php`

**Option B : Via PHP standalone (Recommandé pour développeurs)**
1. Télécharger PHP 8.2+ : https://windows.php.net/download/
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
.\setup-windows.ps1
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
.\start-dev.bat
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

## Support

Pour plus d'informations sur le jeu, consultez :
- `/docs/game-design/GDD_Central.md` - Documentation principale
- `/docs/game-design/GUIDE_DEMARRAGE.md` - Guide de démarrage du projet
