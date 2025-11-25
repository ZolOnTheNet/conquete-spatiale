# Scripts d'Installation et Configuration

Ce répertoire contient tous les scripts utilitaires pour installer, configurer et maintenir le projet "Conquête Spatiale" sur différentes plateformes (Windows et Ubuntu/Linux).

---

## Table des Matières

1. [Scripts d'Installation](#scripts-dinstallation)
2. [Scripts de Maintenance](#scripts-de-maintenance)
3. [Scripts de Correction d'Encodage](#scripts-de-correction-dencodage)
4. [Scripts d'Analyse](#scripts-danalyse)
5. [Usage Multi-Plateforme](#usage-multi-plateforme)

---

## Scripts d'Installation

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
  php scripts/check-php-version.php
  ```

Vérifie :
- Version PHP installée (8.2+ minimum, 8.3+ recommandé)
- Extensions PHP requises
- Compatibilité avec Laravel 12

---

## Scripts de Correction d'Encodage

Ces scripts corrigent les problèmes d'encodage UTF-8 dans les fichiers du projet. Utiles lorsque vous voyez des caractères comme `Ã©` au lieu de `é`, `âš ` au lieu de `⚠️`, etc.

### fix_utf8.py ⭐ **(RECOMMANDÉ)**

Script simple et efficace pour corriger les caractères mal encodés.

**Ubuntu/Linux :**
```bash
python3 scripts/fix_utf8.py docs/
```

**Windows :**
```powershell
python scripts\fix_utf8.py docs\
```

**Caractéristiques :**
- ✅ Aucune dépendance externe
- ✅ Rapide et efficace
- ✅ Corrige les caractères français et emojis
- ✅ Fonctionne sur Windows et Linux

**Usage :**
```bash
# Corriger un fichier spécifique
python3 scripts/fix_utf8.py docs/game-design/GDD_Central.md

# Corriger un dossier entier
python3 scripts/fix_utf8.py docs/

# Corriger tout le projet
python3 scripts/fix_utf8.py .
```

### fix_encoding_simple.py

Version détaillée sans dépendances externes avec mode dry-run.

**Ubuntu/Linux :**
```bash
# Mode dry-run (test sans modification)
python3 scripts/fix_encoding_simple.py docs/ --dry-run

# Correction réelle
python3 scripts/fix_encoding_simple.py docs/
```

**Windows :**
```powershell
python scripts\fix_encoding_simple.py docs\ --dry-run
python scripts\fix_encoding_simple.py docs\
```

**Caractéristiques :**
- ✅ Détection automatique de l'encodage (UTF-8, ISO-8859-1, Windows-1252)
- ✅ Mode dry-run pour prévisualiser les changements
- ✅ Rapport détaillé des fichiers traités

### fix_encoding.py

Version complète avec détection avancée (nécessite `chardet`).

**Installation de la dépendance :**

**Ubuntu/Linux :**
```bash
sudo apt install python3-chardet
# OU
pip3 install --user chardet
```

**Windows :**
```powershell
pip install chardet
```

**Usage :**
```bash
python3 scripts/fix_encoding.py docs/ --dry-run
python3 scripts/fix_encoding.py docs/
```

**Caractéristiques :**
- ✅ Détection précise avec niveau de confiance
- ✅ Gère de multiples encodages
- ✅ Mode dry-run
- ⚠️ Nécessite le module `chardet`

### fix_double_encoding.py

Spécialisé pour les cas de **double encodage** (UTF-8 → ISO-8859-1 → UTF-8).

**Usage :**
```bash
python3 scripts/fix_double_encoding.py docs/
```

**Caractéristiques :**
- ✅ Corrige les doubles encodages complexes
- ✅ Mode dry-run disponible
- ⚠️ Cas d'usage spécifique

### fix_encoding_robust.py

Approche multi-stratégies avec support optionnel de `ftfy`.

**Ubuntu/Linux :**
```bash
# Sans ftfy (mode manuel)
python3 scripts/fix_encoding_robust.py docs/ --manual

# Avec ftfy (plus puissant)
pip3 install --user ftfy
python3 scripts/fix_encoding_robust.py docs/
```

**Windows :**
```powershell
python scripts\fix_encoding_robust.py docs\ --manual
```

**Caractéristiques :**
- ✅ Plusieurs stratégies de correction
- ✅ Support optionnel de ftfy pour cas complexes
- ✅ Fallback automatique si ftfy absent

### fix_encoding_direct.py

Correction par remplacement direct de caractères (en développement).

---

## Scripts d'Analyse

### analyze_universe_sectors.py

Script d'analyse des secteurs de l'univers du jeu.

**Usage :**
```bash
python3 scripts/analyze_universe_sectors.py
```

---

## Usage Multi-Plateforme

### Configuration Git pour Windows et Ubuntu

Le projet est conçu pour fonctionner de manière transparente entre Windows et Ubuntu.

#### Sur Ubuntu/Linux

```bash
# Configuration déjà appliquée
git config --global core.autocrlf input
git config --global core.eol lf
```

#### Sur Windows

```powershell
# À exécuter lors de la première installation
git config --global core.autocrlf true
git config --global core.eol lf
```

### Encodage des Fichiers

Le projet utilise **UTF-8** partout. Les fichiers de configuration garantissent cet encodage :

- **`.editorconfig`** : Force UTF-8 pour tous les éditeurs compatibles
- **`.gitattributes`** : Force LF (Unix) pour les fichiers texte
- **`.vscode/settings.json`** : Force UTF-8 dans VSCode/VSCodium

### Problèmes d'Encodage Entre Plateformes

Si vous voyez des caractères bizarres (`Ã©`, `âš `, etc.) :

1. **Utilisez `fix_utf8.py`** (recommandé) :
   ```bash
   python3 scripts/fix_utf8.py .
   ```

2. **Vérifiez votre éditeur** :
   - VSCode/VSCodium : Vérifier l'encodage en bas à droite (doit être "UTF-8")
   - Recharger la fenêtre après avoir modifié `.vscode/settings.json`

3. **Si problème persiste** :
   ```bash
   # Restaurer depuis Git
   git checkout HEAD -- fichier_problematique.md

   # Puis corriger avec fix_utf8.py
   python3 scripts/fix_utf8.py fichier_problematique.md
   ```

### Chemins de Fichiers

Les scripts Python utilisent `pathlib` qui gère automatiquement les différences Windows/Linux.

**Exemples équivalents :**
```bash
# Linux
python3 scripts/fix_utf8.py docs/

# Windows
python scripts\fix_utf8.py docs\
```

---

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

---

## Tableau Récapitulatif des Scripts

| Script | Plateforme | Dépendances | Usage Principal |
|--------|------------|-------------|-----------------|
| `setup-windows.bat` | Windows | aucune | Installation initiale |
| `setup-windows.ps1` | Windows (PS 5.0+) | aucune | Installation initiale |
| `start-dev.bat` | Windows | aucune | Démarrage serveur dev |
| `check-php-version.php` | Windows/Linux | PHP | Vérification config |
| **`fix_utf8.py`** ⭐ | Windows/Linux | aucune | **Correction encodage (RECOMMANDÉ)** |
| `fix_encoding_simple.py` | Windows/Linux | aucune | Correction encodage détaillée |
| `fix_encoding.py` | Windows/Linux | chardet | Correction encodage avancée |
| `fix_double_encoding.py` | Windows/Linux | aucune | Double encodage |
| `fix_encoding_robust.py` | Windows/Linux | ftfy (opt.) | Multi-stratégies |
| `fix_encoding_direct.py` | Windows/Linux | aucune | En développement |
| `analyze_universe_sectors.py` | Windows/Linux | aucune | Analyse univers |

---

## Quick Start Guide

### Sur Ubuntu/Linux

```bash
# Installation
scripts/setup-windows.bat  # Adapter pour Linux si nécessaire

# Corriger l'encodage
python3 scripts/fix_utf8.py docs/

# Vérifier PHP
php scripts/check-php-version.php

# Analyser l'univers
python3 scripts/analyze_universe_sectors.py
```

### Sur Windows

```powershell
# Installation
scripts\setup-windows.bat

# Corriger l'encodage
python scripts\fix_utf8.py docs\

# Démarrer le serveur
scripts\start-dev.bat

# Vérifier PHP
php scripts\check-php-version.php
```

---

## Documentation

Pour plus d'informations :
- [Installation Windows](../docs/INSTALLATION_WINDOWS.md)
- [Installation PHP 8.3](../docs/INSTALLATION_PHP_WINDOWS.md)
- [Quick Start Windows](../docs/QUICK_START_WINDOWS.md)
- [Configuration MariaDB](../docs/CONFIG_MARIADB.md)
- [Commandes Utiles](../docs/COMMANDES.md)

---

## Notes Techniques

### Fins de Ligne

Les scripts Windows (.bat, .ps1) utilisent des fins de ligne **CRLF** configurées via `.gitattributes` pour garantir le bon fonctionnement sur Windows. Les scripts Python et PHP utilisent **LF** (Unix).

### Chemins

**Tous les scripts doivent être exécutés depuis la racine du projet.**

```bash
# ✅ Correct
python3 scripts/fix_utf8.py docs/

# ❌ Incorrect
cd scripts
python3 fix_utf8.py ../docs/
```

### Compatibilité

| Script | Windows | Linux | Prérequis |
|--------|---------|-------|-----------|
| `setup-windows.bat` | ✅ XP+ | ❌ | Batch |
| `setup-windows.ps1` | ✅ 10+ | ❌ | PowerShell 5.0+ |
| `start-dev.bat` | ✅ Tous | ❌ | Batch |
| `check-php-version.php` | ✅ | ✅ | PHP 7.0+ |
| Scripts Python (`.py`) | ✅ | ✅ | Python 3.6+ |

### Partage Samba/Réseau

Si vous travaillez sur un partage réseau entre Windows et Linux :

1. **Toujours utiliser UTF-8** dans votre éditeur
2. **Configurer Git** sur les deux systèmes comme indiqué
3. **Lancer `fix_utf8.py`** après un `git pull` si problèmes d'affichage
4. **Ne pas éditer** les mêmes fichiers simultanément sur les deux systèmes

---

## Support

En cas de problème :

1. **Vérifier la configuration** :
   ```bash
   git config --get core.autocrlf
   git config --get core.eol
   ```

2. **Vérifier l'encodage des fichiers** :
   ```bash
   file -bi docs/game-design/GDD_Central.md
   ```

3. **Corriger l'encodage** :
   ```bash
   python3 scripts/fix_utf8.py .
   ```

4. **Consulter les logs Git** :
   ```bash
   git status
   git diff
   ```
