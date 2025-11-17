# 🔄 Scripts de Synchronisation

Scripts pour synchroniser automatiquement la branche Claude vers votre branche `dev`.

## 📁 Fichiers

- `sync-to-dev.sh` - Script Bash (Linux/macOS)
- `sync-to-dev.ps1` - Script PowerShell (Windows)
- `CONFIG_MARIADB.md` - Configuration MariaDB externe

---

## 🐧 Linux / macOS - Bash

### Utilisation

```bash
# Rendre le script exécutable (première fois)
chmod +x sync-to-dev.sh

# Lancer la synchronisation
./sync-to-dev.sh
```

### Ce que fait le script

1. ✅ Vérifie que vous êtes dans un repo Git
2. ✅ Sauvegarde (stash) vos modifications non commitées
3. ✅ Récupère les dernières modifications distantes
4. ✅ Bascule sur la branche `dev`
5. ✅ Fusionne votre branche Claude dans `dev`
6. ✅ Push vers `origin/dev`
7. ✅ Retourne sur votre branche d'origine
8. ✅ Restaure vos modifications si nécessaire

### Gestion automatique

- 🔒 Détecte les modifications non commitées
- 💾 Propose de les stasher automatiquement
- ⚠️ Détecte les conflits de fusion
- 🔄 Propose de retourner sur la branche d'origine
- 📦 Propose de restaurer le stash

---

## 🪟 Windows - PowerShell

### Utilisation

```powershell
# Lancer la synchronisation (mode interactif)
.\sync-to-dev.ps1

# Lancer la synchronisation (mode automatique)
.\sync-to-dev.ps1 -Force
```

### Mode Force

Le paramètre `-Force` répond automatiquement "Oui" à toutes les questions :
- Stash automatique des modifications
- Création automatique de la branche dev
- Retour automatique sur la branche d'origine
- Restauration automatique du stash

### Politique d'exécution

Si vous avez une erreur `execution_policy`, exécutez :

```powershell
# Autoriser pour la session actuelle
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process

# Puis lancer le script
.\sync-to-dev.ps1
```

---

## 🗄️ Configuration MariaDB Externe

Voir le fichier `CONFIG_MARIADB.md` pour un guide complet.

### Résumé rapide

**Sur le serveur MariaDB:**
```sql
CREATE DATABASE conquete_spatiale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'conquete_user'@'192.168.1.%' IDENTIFIED BY 'VotreMotDePasse';
GRANT ALL PRIVILEGES ON conquete_spatiale.* TO 'conquete_user'@'192.168.1.%';
FLUSH PRIVILEGES;
```

**Configuration MariaDB:**
```ini
# /etc/mysql/mariadb.conf.d/50-server.cnf
bind-address = 0.0.0.0
```

**Pare-feu:**
```bash
sudo ufw allow from 192.168.1.0/24 to any port 3306
```

**Dans Laravel (.env):**
```env
DB_CONNECTION=mysql
DB_HOST=192.168.1.100
DB_PORT=3306
DB_DATABASE=conquete_spatiale
DB_USERNAME=conquete_user
DB_PASSWORD=VotreMotDePasse
```

---

## 🚨 Dépannage

### Script Bash : Permission denied

```bash
chmod +x sync-to-dev.sh
```

### PowerShell : Execution Policy

```powershell
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
```

### Git : Conflits de fusion

Si le script détecte des conflits :

```bash
# Résoudre manuellement les conflits
git add .
git commit
git push origin dev
```

### MariaDB : Connection refused

Vérifiez :
1. MariaDB écoute sur 0.0.0.0 (bind-address)
2. Le pare-feu autorise le port 3306
3. L'IP est correcte

```bash
# Vérifier le port
sudo netstat -tlnp | grep 3306

# Tester la connexion
telnet 192.168.1.100 3306
```

---

## 📝 Workflow Recommandé

### Développement quotidien

```bash
# 1. Travailler sur votre branche Claude
git checkout claude/init-conquete-spatiale-01VxY9SzWwKRZJBY64swHVuf

# 2. Faire vos commits
git add .
git commit -m "feat: nouvelle fonctionnalité"
git push

# 3. Synchroniser vers dev
./sync-to-dev.sh

# 4. Continuer le développement
git checkout claude/init-conquete-spatiale-01VxY9SzWwKRZJBY64swHVuf
```

### Avant une démo/présentation

```bash
# Synchroniser tout vers dev
./sync-to-dev.sh

# Vérifier que dev est à jour
git checkout dev
git log --oneline -5

# Tester l'application depuis dev
php artisan serve
```

---

## 🔐 Sécurité

### Branches protégées

Pour protéger la branche `dev` sur GitHub :

1. Aller dans Settings → Branches
2. Ajouter une règle pour `dev`
3. Cocher :
   - ✅ Require pull request reviews
   - ✅ Require status checks to pass
   - ✅ Include administrators

### Pas de secrets dans Git

```bash
# Vérifier qu'aucun secret n'est tracké
git ls-files | xargs grep -l "password\|secret\|key"

# Le .env ne doit JAMAIS être commité
cat .gitignore | grep .env
```

---

## 📚 Ressources

- [Documentation Laravel](https://laravel.com/docs)
- [MariaDB Documentation](https://mariadb.org/documentation/)
- [Git Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows)

---

**Créé le :** 2025-11-17
**Projet :** Conquête Galactique
