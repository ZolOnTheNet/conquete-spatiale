# Quick Start - Windows 11

Guide rapide pour démarrer sur Windows 11 en 5 minutes.

## Installation Express

### 1. Prérequis (à installer une seule fois)

Installez dans l'ordre :
1. **PHP 8.2+** : https://windows.php.net/download/ ou XAMPP https://www.apachefriends.org/
2. **Composer** : https://getcomposer.org/Composer-Setup.exe
3. **Node.js LTS** : https://nodejs.org/
4. **Git** : https://git-scm.com/download/win

### 2. Installation du Projet

```bash
# Cloner le projet
git clone https://github.com/ZolOnTheNet/conquete-spatiale.git
cd conquete-spatiale

# Lancer l'installation automatique
scripts\setup-windows.bat
```

### 3. Démarrer le Projet

```bash
# Lancer l'environnement de développement
scripts\start-dev.bat
```

Ouvrir dans le navigateur : http://localhost:8000

## Commandes Quotidiennes

### Synchroniser avec le repository

```bash
# Avant de commencer à travailler
git pull

# Après vos modifications
git add .
git commit -m "Description de vos modifications"
git push
```

### Démarrer le serveur

```bash
# Option 1 : Script automatique (recommandé)
scripts\start-dev.bat

# Option 2 : Serveur seul
php artisan serve
# puis dans un autre terminal :
npm run dev
```

### Arrêter le serveur

Appuyer sur `Ctrl+C` dans le terminal.

## Problèmes Courants

### "php n'est pas reconnu..."
→ Ajouter PHP au PATH système (voir docs/INSTALLATION_WINDOWS.md)

### "composer n'est pas reconnu..."
→ Réinstaller Composer et redémarrer le terminal

### Port 8000 occupé
```bash
php artisan serve --port=8001
```

### Erreur de permissions
→ Lancer le terminal en tant qu'Administrateur

## Scripts Disponibles

- `scripts\setup-windows.bat` ou `scripts\setup-windows.ps1` : Installation initiale
- `scripts\start-dev.bat` : Démarrer l'environnement de développement
- `composer dev` : Alternative pour démarrer tous les services

## Documentation Complète

Pour plus de détails, consultez :
- **Installation complète** : `docs/INSTALLATION_WINDOWS.md`
- **Guide du projet** : `docs/game-design/GUIDE_DEMARRAGE.md`
- **Architecture technique** : `docs/game-design/GDD_Architecture_Technique.md`

## Support

En cas de problème, vérifiez :
1. Que tous les prérequis sont installés
2. Que vous êtes dans le bon dossier du projet
3. Que le fichier `.env` existe
4. La documentation complète dans `docs/INSTALLATION_WINDOWS.md`
