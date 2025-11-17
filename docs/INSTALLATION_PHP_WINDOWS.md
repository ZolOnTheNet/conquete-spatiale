# Installation de PHP 8.3 sur Windows 11

Guide détaillé pour installer PHP 8.3 en standalone sur Windows 11.

## Téléchargement

### Lien Direct PHP 8.3.15 (Dernière version stable)

**Version recommandée pour Laravel (Non Thread Safe - NTS)** :
- **64-bit** : https://windows.php.net/downloads/releases/php-8.3.15-nts-Win32-vs16-x64.zip

**Alternative (Thread Safe - TS)** :
- **64-bit** : https://windows.php.net/downloads/releases/php-8.3.15-Win32-vs16-x64.zip

> **Quelle version choisir ?**
> - **Non Thread Safe (NTS)** : Pour utilisation en ligne de commande et serveur Laravel Artisan (RECOMMANDÉ)
> - **Thread Safe (TS)** : Si vous utilisez Apache avec mod_php

Pour toujours avoir la dernière version : https://windows.php.net/download/

## Installation Étape par Étape

### 1. Télécharger PHP

Téléchargez la version **Non Thread Safe x64** (lien ci-dessus).

### 2. Extraire l'archive

```
1. Créer le dossier : C:\php
2. Extraire le contenu du ZIP dans C:\php
3. Vous devriez avoir : C:\php\php.exe
```

### 3. Configurer php.ini

```bash
# Dans C:\php
1. Copier php.ini-development
2. Renommer la copie en php.ini
```

### 4. Activer les extensions requises

Ouvrir `C:\php\php.ini` dans un éditeur de texte et décommenter (retirer le `;`) ces lignes :

```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

Pour décommenter, transformer :
```ini
;extension=curl    (désactivé)
```
en :
```ini
extension=curl     (activé)
```

### 5. Ajouter PHP au PATH Windows

**Méthode graphique** :

1. Clic droit sur "Ce PC" → Propriétés
2. Paramètres système avancés
3. Variables d'environnement
4. Dans "Variables système", sélectionner "Path"
5. Cliquer sur "Modifier"
6. Cliquer sur "Nouveau"
7. Ajouter : `C:\php`
8. Cliquer sur "OK" partout

**Méthode PowerShell (Administrateur)** :

```powershell
# Exécuter PowerShell en tant qu'Administrateur
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\php", "Machine")
```

### 6. Vérifier l'installation

**Fermer et rouvrir** votre terminal (important !), puis :

```bash
php -v
```

Vous devriez voir :
```
PHP 8.3.15 (cli) (built: xxx) ( NTS Visual C++ 2019 x64 )
```

### 7. Vérifier les extensions

```bash
php -m
```

Vérifiez que ces extensions apparaissent :
- curl
- fileinfo
- mbstring
- openssl
- pdo_sqlite
- sqlite3
- zip

## Installation de Composer

Une fois PHP installé :

1. Télécharger : https://getcomposer.org/Composer-Setup.exe
2. L'installeur détectera automatiquement votre PHP dans `C:\php`
3. Suivre l'installation
4. Redémarrer le terminal

Vérifier :
```bash
composer --version
```

## Utilisation avec XAMPP (Optionnel)

Si vous avez déjà XAMPP et voulez utiliser PHP 8.3 en ligne de commande :

### Option 1 : Deux installations séparées

- XAMPP 8.2.12 : Pour Apache/MySQL (`C:\xampp\php\php.exe`)
- PHP 8.3 standalone : Pour CLI/Laravel (`C:\php\php.exe`)

Le PATH utilisera `C:\php` en priorité si vous le placez avant `C:\xampp\php`.

### Option 2 : Remplacer le PHP de XAMPP

**⚠️ Avancé - Faire une sauvegarde avant !**

1. Sauvegarder `C:\xampp\php` en `C:\xampp\php_old`
2. Extraire PHP 8.3 dans `C:\xampp\php`
3. Copier les fichiers de configuration depuis `php_old` si nécessaire
4. Redémarrer Apache depuis le panneau XAMPP

## Différence entre PHP 8.2 et 8.3

### Nouvelles fonctionnalités PHP 8.3 :

- `json_validate()` - Validation JSON sans parsing
- Typage des constantes de classe
- Améliorations `Random\Randomizer`
- Override attribute
- Meilleures performances (légère amélioration)

### Pour Laravel 12 :

Les deux versions fonctionnent parfaitement. PHP 8.2.12 est suffisant pour tout le projet.

## Recommandation Finale

### Si vous avez déjà XAMPP 8.2.12 :

**C'est parfait !** Gardez-le. La différence avec 8.3 est minime pour votre usage.

### Si vous partez de zéro :

Installez PHP 8.3 standalone (ce guide) - c'est plus léger et à jour.

## Vérification Automatique

Après installation, utilisez notre script :

```bash
cd C:\chemin\vers\conquete-spatiale
php check-php-version.php
```

## Dépannage

### "php n'est pas reconnu..."

1. Vérifiez que `C:\php\php.exe` existe
2. Vérifiez le PATH (voir étape 5)
3. **Redémarrez le terminal** (crucial!)
4. Essayez `C:\php\php.exe -v` directement

### Extensions manquantes

1. Ouvrir `C:\php\php.ini`
2. Chercher l'extension (ex: `extension=curl`)
3. Retirer le `;` devant
4. Sauvegarder
5. Redémarrer le terminal
6. Vérifier avec `php -m`

### Erreur "VCRUNTIME140.dll manquant"

Installer Visual C++ Redistributable :
https://aka.ms/vs/17/release/vc_redist.x64.exe

## Liens Utiles

- **Page officielle** : https://windows.php.net/download/
- **Documentation PHP** : https://www.php.net/manual/en/install.windows.php
- **Composer** : https://getcomposer.org/
- **Extensions PECL** : https://windows.php.net/downloads/pecl/releases/

## Support

En cas de problème, vérifiez :
1. Que `php.exe` est bien dans `C:\php`
2. Que le PATH contient `C:\php`
3. Que vous avez redémarré le terminal
4. Que `php.ini` existe (pas `php.ini-development`)
