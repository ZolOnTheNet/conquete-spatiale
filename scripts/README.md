# Scripts d'Installation et Configuration

Ce répertoire contient tous les scripts utilitaires pour installer et configurer le projet "Conquête Spatiale" sur différentes plateformes.

## Scripts Disponibles

### Installation Windows

- **setup-windows.bat** - Script d'installation automatique (Batch)
  ```bash
  scripts\setup-windows.bat
  ```

- **setup-windows.ps1** - Script d'installation automatique (PowerShell)
  ```powershell
  .\scripts\setup-windows.ps1
  ```

Ces scripts effectuent :
- Installation des dépendances Composer
- Configuration du fichier .env
- Génération de la clé d'application
- Création de la base de données SQLite
- Exécution des migrations
- Installation des dépendances npm
- Compilation des assets
- Vérification de la configuration PHP

### Démarrage du Serveur

- **start-dev.bat** - Démarre l'environnement de développement complet
  ```bash
  scripts\start-dev.bat
  ```

Lance automatiquement :
- Serveur Laravel (http://localhost:8000)
- Serveur Vite (hot reload)
- Worker de queue
- Logs en temps réel (Laravel Pail)

### Utilitaires

- **check-php-version.php** - Vérifie la version PHP et les extensions
  ```bash
  php scripts\check-php-version.php
  ```

Vérifie :
- Version PHP installée (8.2+ minimum, 8.3+ recommandé)
- Extensions PHP requises
- Compatibilité avec Laravel 12

## Usage

### Première Installation

```bash
# Cloner le projet
git clone https://github.com/ZolOnTheNet/conquete-spatiale.git
cd conquete-spatiale

# Lancer l'installation
scripts\setup-windows.bat
```

### Démarrage Quotidien

```bash
# Lancer l'environnement de développement
scripts\start-dev.bat
```

### Vérification de l'Environnement

```bash
# Vérifier PHP et extensions
php scripts\check-php-version.php
```

## Documentation

Pour plus d'informations :
- [Installation Windows](../docs/INSTALLATION_WINDOWS.md)
- [Installation PHP 8.3](../docs/INSTALLATION_PHP_WINDOWS.md)
- [Quick Start Windows](../docs/QUICK_START_WINDOWS.md)

## Notes Techniques

### Fins de Ligne

Les scripts Windows (.bat, .ps1) utilisent des fins de ligne CRLF configurées via `.gitattributes` pour garantir le bon fonctionnement sur Windows.

### Chemins

Les scripts sont conçus pour être exécutés depuis la **racine du projet**. Ils utilisent des chemins relatifs appropriés.

### Compatibilité

- **setup-windows.bat** : Compatible tous Windows (XP+)
- **setup-windows.ps1** : Nécessite PowerShell 5.0+ (Windows 10+)
- **start-dev.bat** : Compatible tous Windows
- **check-php-version.php** : PHP 7.0+ (fonctionne avec toutes versions)
