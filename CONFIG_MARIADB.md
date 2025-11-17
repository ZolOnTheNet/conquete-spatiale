# Configuration MariaDB Externe

Ce guide explique comment configurer l'application pour utiliser une base MariaDB sur une machine externe en réseau local.

## 📋 Prérequis

- MariaDB installé sur la machine distante
- Connexion réseau entre les deux machines
- Droits d'administration sur le serveur MariaDB

---

## 🖥️ Configuration du Serveur MariaDB (Machine Externe)

### 1. Installation de MariaDB (si nécessaire)

**Debian/Ubuntu:**
```bash
sudo apt update
sudo apt install mariadb-server mariadb-client
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

**Configuration sécurisée:**
```bash
sudo mysql_secure_installation
```

### 2. Configurer MariaDB pour accepter les connexions externes

**Éditer le fichier de configuration:**
```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

**Modifier la ligne bind-address:**
```ini
# Avant (n'écoute que localhost)
bind-address = 127.0.0.1

# Après (écoute sur toutes les interfaces)
bind-address = 0.0.0.0

# OU spécifier l'IP locale spécifique
bind-address = 192.168.1.100  # Remplacer par l'IP réelle du serveur
```

**Redémarrer MariaDB:**
```bash
sudo systemctl restart mariadb
```

### 3. Créer la base de données et l'utilisateur

**Connexion à MariaDB:**
```bash
sudo mysql -u root -p
```

**Créer la base de données:**
```sql
CREATE DATABASE conquete_spatiale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Créer un utilisateur pour l'accès distant:**
```sql
-- Remplacer '192.168.1.%' par votre plage réseau
-- Ou '%' pour autoriser toutes les IPs (moins sécurisé)
CREATE USER 'conquete_user'@'192.168.1.%' IDENTIFIED BY 'VotreMotDePasseSecurise123!';

-- Accorder tous les privilèges sur la base
GRANT ALL PRIVILEGES ON conquete_spatiale.* TO 'conquete_user'@'192.168.1.%';

-- Appliquer les changements
FLUSH PRIVILEGES;

-- Vérifier les utilisateurs créés
SELECT User, Host FROM mysql.user WHERE User = 'conquete_user';
```

**Pour un accès depuis n'importe quelle IP (développement uniquement):**
```sql
CREATE USER 'conquete_user'@'%' IDENTIFIED BY 'VotreMotDePasseSecurise123!';
GRANT ALL PRIVILEGES ON conquete_spatiale.* TO 'conquete_user'@'%';
FLUSH PRIVILEGES;
```

**Quitter MariaDB:**
```sql
EXIT;
```

### 4. Configurer le pare-feu

**Ubuntu/Debian avec UFW:**
```bash
# Autoriser le port MariaDB (3306) depuis votre réseau local
sudo ufw allow from 192.168.1.0/24 to any port 3306

# OU autoriser depuis une IP spécifique
sudo ufw allow from 192.168.1.50 to any port 3306

# Vérifier les règles
sudo ufw status
```

**Firewalld (CentOS/RHEL):**
```bash
sudo firewall-cmd --permanent --add-port=3306/tcp
sudo firewall-cmd --reload
```

### 5. Vérifier que MariaDB écoute bien

```bash
# Vérifier les ports en écoute
sudo netstat -tlnp | grep 3306

# OU avec ss
sudo ss -tlnp | grep 3306

# Résultat attendu :
# tcp  0  0 0.0.0.0:3306  0.0.0.0:*  LISTEN  1234/mariadbd
```

---

## 💻 Configuration de l'Application Laravel (Machine de Développement)

### 1. Tester la connexion au serveur MariaDB

**Depuis votre machine de développement:**
```bash
# Installer le client MySQL/MariaDB si nécessaire
sudo apt install mysql-client  # Ubuntu/Debian
# ou
brew install mysql-client      # macOS

# Tester la connexion
mysql -h 192.168.1.100 -u conquete_user -p
# Entrer le mot de passe

# Si connexion réussie, vous verrez:
# MariaDB [(none)]>
```

### 2. Configurer le fichier .env

**Éditer le fichier `.env` du projet Laravel:**
```env
DB_CONNECTION=mysql
DB_HOST=192.168.1.100          # IP de votre serveur MariaDB
DB_PORT=3306
DB_DATABASE=conquete_spatiale
DB_USERNAME=conquete_user
DB_PASSWORD=VotreMotDePasseSecurise123!
```

### 3. Exécuter les migrations

```bash
# Vider le cache de configuration
php artisan config:clear

# Tester la connexion
php artisan db:show

# Exécuter les migrations
php artisan migrate:fresh --seed --seeder=GameSeeder
```

---

## 🔒 Sécurité - Recommandations

### 1. Connexion SSL/TLS (Production)

**Sur le serveur MariaDB:**
```sql
-- Forcer SSL pour l'utilisateur
ALTER USER 'conquete_user'@'192.168.1.%' REQUIRE SSL;
FLUSH PRIVILEGES;
```

**Dans Laravel (.env):**
```env
DB_SSLMODE=require
MYSQL_ATTR_SSL_CA=/chemin/vers/ca-cert.pem
```

### 2. Restrictions réseau

```bash
# Ne jamais utiliser '%' en production
# Toujours limiter aux IPs nécessaires
CREATE USER 'conquete_user'@'192.168.1.50' IDENTIFIED BY 'MotDePasse';
```

### 3. Mot de passe fort

```bash
# Générer un mot de passe fort
openssl rand -base64 32
```

### 4. Logs de connexion

**Activer les logs de connexion:**
```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

```ini
[mysqld]
general_log = 1
general_log_file = /var/log/mysql/mysql.log
```

---

## 🧪 Tests de Connexion

### Script de test rapide

**Créer un fichier `test-db-connection.php`:**
```php
<?php
$host = '192.168.1.100';
$db   = 'conquete_spatiale';
$user = 'conquete_user';
$pass = 'VotreMotDePasseSecurise123!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion à MariaDB réussie !\n";

    // Tester une requête
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "✓ Version MariaDB: $version\n";

} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
}
```

**Exécuter:**
```bash
php test-db-connection.php
```

---

## 🐛 Dépannage

### Problème: "Connection refused"

**Causes possibles:**
- MariaDB n'écoute pas sur 0.0.0.0
- Pare-feu bloque le port 3306
- IP incorrecte

**Solutions:**
```bash
# Vérifier bind-address
sudo grep bind-address /etc/mysql/mariadb.conf.d/50-server.cnf

# Vérifier le port
sudo netstat -tlnp | grep 3306

# Tester avec telnet
telnet 192.168.1.100 3306
```

### Problème: "Access denied"

**Causes possibles:**
- Mauvais utilisateur/mot de passe
- Host non autorisé dans MariaDB

**Solutions:**
```sql
-- Vérifier les utilisateurs autorisés
SELECT User, Host FROM mysql.user;

-- Recréer l'utilisateur si nécessaire
DROP USER 'conquete_user'@'ancien_host';
CREATE USER 'conquete_user'@'nouveau_host' IDENTIFIED BY 'MotDePasse';
GRANT ALL PRIVILEGES ON conquete_spatiale.* TO 'conquete_user'@'nouveau_host';
FLUSH PRIVILEGES;
```

### Problème: "Too many connections"

**Solution:**
```sql
-- Augmenter le nombre de connexions max
SET GLOBAL max_connections = 200;

-- Rendre permanent
-- Dans /etc/mysql/mariadb.conf.d/50-server.cnf
[mysqld]
max_connections = 200
```

---

## 📊 Monitoring

### Vérifier les connexions actives

```sql
-- Voir les connexions actives
SHOW PROCESSLIST;

-- Voir les connexions par utilisateur
SELECT user, host, COUNT(*)
FROM information_schema.processlist
GROUP BY user, host;
```

### Logs en temps réel

```bash
# Suivre les logs MariaDB
sudo tail -f /var/log/mysql/error.log
```

---

## 🔄 Configuration Exemple Complète

### Serveur MariaDB (192.168.1.100)

```bash
# /etc/mysql/mariadb.conf.d/50-server.cnf
[mysqld]
bind-address = 0.0.0.0
port = 3306
max_connections = 150
```

```sql
-- Base et utilisateur
CREATE DATABASE conquete_spatiale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'conquete_user'@'192.168.1.%' IDENTIFIED BY 'MotDePasseSecurise123!';
GRANT ALL PRIVILEGES ON conquete_spatiale.* TO 'conquete_user'@'192.168.1.%';
FLUSH PRIVILEGES;
```

### Application Laravel (.env)

```env
DB_CONNECTION=mysql
DB_HOST=192.168.1.100
DB_PORT=3306
DB_DATABASE=conquete_spatiale
DB_USERNAME=conquete_user
DB_PASSWORD=MotDePasseSecurise123!
```

---

**Version:** 1.0
**Date:** 2025-11-17
