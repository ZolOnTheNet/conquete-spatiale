# 🔧 Documentation Commandes Admin

**Réservé aux administrateurs** (`compte.is_admin = true`)

---

## 🎯 Accès

Toutes les commandes admin commencent par le préfixe **`/adm`**

Les non-admins reçoivent un message d'erreur si tentative d'utilisation.

---

## 📋 Liste des Commandes

### `/adm scan`

**Description** : Scanner avec informations de détection avancées

**Usage** :
```
/adm scan
```

**Fonctionnalités** :
- Exécute le scan normal
- Ajoute des informations de détection détaillées :
  - Position actuelle du vaisseau
  - Liste des systèmes proches non découverts (distance < 50 AL)
  - Seuil de détection calculé pour chaque système
  - Indication si système proche de la détection

**Exemple de sortie** :
```
=== SCAN ZONE ===
[... scan normal ...]

[ADMIN] INFORMATIONS DÉTECTION:
Position actuelle: (0, 0, 0)
Systèmes proches de la détection:

  Alpha Centauri (4.37 AL) - Seuil: 8 - PROCHE DÉTECTION!
  Sirius (8.60 AL) - Seuil: 17 - PROCHE DÉTECTION!
  Epsilon Eridani (10.50 AL) - Seuil: 21 - PROCHE DÉTECTION!
```

---

### `/adm mv` (move)

**Description** : Déplacer personnages et vaisseaux

**Usage** :

#### Déplacer un personnage dans un vaisseau
```
/adm mv perso <id_personnage> vaisseau <id_vaisseau>
/adm mv personnage <id_personnage> ship <id_vaisseau>
```

**Exemple** :
```
/adm mv perso 1 vaisseau 5
→ [ADMIN] Jean Dupont placé dans Shuttle Standard.
```

#### Déplacer un personnage dans une station
```
/adm mv perso <id_personnage> station <id_station>
```

**Exemple** :
```
/adm mv perso 1 station 3
→ [ADMIN] Jean Dupont placé dans Lunastar Station.
```

#### Amarrer un vaisseau à une station
```
/adm mv vaisseau <id_vaisseau> station <id_station>
```

**Exemple** :
```
/adm mv vaisseau 2 station 3
→ [ADMIN] Shuttle de Jean amarré à Lunastar Station.
```

**Note** : Met aussi à jour les coordonnées de l'objet spatial.

#### Téléporter un vaisseau vers un secteur
```
/adm mv vaisseau <id_vaisseau> <secteur_x> <secteur_y> <secteur_z>
```

**Exemple** :
```
/adm mv vaisseau 2 10 20 5
→ [ADMIN] Shuttle de Jean téléporté en secteur (10, 20, 5).
```

**Note** : Désarrime automatiquement le vaisseau.

---

### `/adm tp` (teleport)

**Description** : Téléporter le personnage actuel (vaisseau)

**Usage** :
```
/adm tp <secteur_x> <secteur_y> <secteur_z>
/adm teleport <secteur_x> <secteur_y> <secteur_z>
```

**Exemple** :
```
/adm tp 50 30 10
→ [ADMIN] Téléportation vers secteur (50, 30, 10).
   Pas de coût énergétique.
```

**Fonctionnalités** :
- Téléporte instantanément le vaisseau actif
- Aucun coût en PA ou énergie
- Désarrime automatiquement si amarré
- Met à jour l'objet spatial

---

### `/adm give`

**Description** : Donner des ressources au personnage

**Usage** :

#### Donner des PA
```
/adm give pa <quantité>
```

**Exemple** :
```
/adm give pa 10
→ [ADMIN] +10 PA donnés. Total: 34/36
```

**Note** : Ne dépasse pas le maximum (max_points_action).

#### Donner des crédits
```
/adm give credits <quantité>
/adm give argent <quantité>
```

**Exemple** :
```
/adm give credits 1000
→ [ADMIN] Système de crédits pas encore implémenté.
```

**Note** : À implémenter quand le système de crédits sera prêt.

---

### `/adm info`

**Description** : Afficher des informations détaillées sur un objet

**Usage** :

#### Informations sur un personnage
```
/adm info perso <id>
/adm info personnage <id>
```

**Exemple** :
```
/adm info perso 1

[ADMIN] PERSONNAGE #1
Nom: Jean Dupont
Compte: admin@example.com (ID: 1)
Niveau: 1 | XP: 0
PA: 24/36
Vaisseau actif: Shuttle de Jean (ID: 1)
Dans station: 1
Dans vaisseau: Non
```

#### Informations sur un vaisseau
```
/adm info vaisseau <id>
/adm info ship <id>
```

**Exemple** :
```
/adm info vaisseau 1

[ADMIN] VAISSEAU #1
Nom: Shuttle de Jean
Modèle: Shuttle Standard
Propriétaire: Jean Dupont (ID: 1)
Position: Secteur (0, 0, 0)
          Absolue (0.0, 1.0, 0.0)
Amarré: Station #1
```

---

### `/adm list`

**Description** : Lister tous les objets d'un type

**Usage** :

#### Lister tous les personnages
```
/adm list persos
/adm list personnages
```

**Exemple** :
```
/adm list persos

[ADMIN] LISTE DES PERSONNAGES (3):

ID 1: Jean Dupont (Compte: admin@example.com) - Niveau 1
ID 2: Alice Martin (Compte: alice@example.com) - Niveau 1
ID 3: Bob Smith (Compte: bob@example.com) - Niveau 2
```

#### Lister tous les vaisseaux
```
/adm list vaisseaux
/adm list ships
```

**Exemple** :
```
/adm list vaisseaux

[ADMIN] LISTE DES VAISSEAUX (3):

ID 1: Shuttle de Jean (Shuttle Standard) - Propriétaire: Jean Dupont
ID 2: Shuttle d'Alice (Shuttle Standard) - Propriétaire: Alice Martin
ID 3: Corvette de Bob (Corvette) - Propriétaire: Bob Smith
```

---

### `/adm print` (dump)

**Description** : Interroger et déboguer des objets Laravel (Eloquent)

**Usage** :

#### Lister les objets disponibles
```
/adm print OBJECTS
/adm print HELP
```

**Affiche** :
- Liste des alias rapides (PJ, SHIP, COMPTE)
- Liste des modèles interrogeables
- Exemples d'utilisation

#### Déboguer le personnage actuel
```
/adm print PJ
/adm print PERSO
/adm print PERSONNAGE
```

**Exemple de sortie** :
```
[ADMIN] DEBUG OBJET: Personnage #1 (actuel)
============================================================

Classe: App\Models\Personnage

ATTRIBUTS:
  id:                       1
  compte_id:                1
  nom:                      Jean
  prenom:                   Dupont
  niveau:                   1
  experience:               0
  points_action:            24
  max_points_action:        36
  vaisseau_actif_id:        1
  dans_station_id:          1
  dans_vaisseau_id:         null
  [... autres attributs ...]

RELATIONS CHARGEES:
  compte:                   Compte #1
  vaisseauActif:            Vaisseau #1

TIMESTAMPS:
  created_at:               2025-11-23 14:23:45
  updated_at:               2025-11-23 15:12:30
```

#### Déboguer le vaisseau actuel
```
/adm print SHIP
/adm print VAISSEAU
```

#### Déboguer le compte actuel
```
/adm print COMPTE
```

#### Déboguer un objet par modèle et ID
```
/adm print <Modèle> <id>
```

**Exemples** :
```
/adm print Personnage 2
/adm print Vaisseau 5
/adm print Station 1
/adm print SystemeStellaire 10
/adm print Planete 15
/adm print ObjetSpatial 3
/adm print Ressource 1
/adm print Arme 2
/adm print Bouclier 1
/adm print Combat 1
/adm print Ennemi 5
/adm print Gisement 10
/adm print Marche 1
/adm print Recette 3
```

**Fonctionnalités** :
- Affiche la classe de l'objet
- Liste tous les attributs (colonnes DB)
- Liste les relations Eloquent chargées
- Affiche les timestamps (created_at, updated_at)
- Format lisible et aligné

**Cas d'usage** :
- Déboguer un personnage qui semble bloqué
- Vérifier les relations Eloquent
- Inspecter les valeurs exactes des attributs
- Comprendre l'état d'un objet complexe

---

### `/adm list comptes` (listing accounts)

**Description** : Lister et rechercher des comptes

**Usage** :

#### Lister tous les comptes
```
/adm list comptes
/adm list accounts
```

**Exemple** :
```
/adm list comptes

[ADMIN] LISTE DES COMPTES (5):

ID 1: admin@example.com [ADMIN] - Perso: Jean Dupont
ID 2: alice@mail.com - Perso: Alice Martin
ID 3: bob@test.com
ID 4: test@example.com - Perso: Test User
ID 5: newplayer@game.com
```

#### Rechercher un compte
```
/adm list comptes <recherche>
```

**Exemples** :
```
/adm list comptes alice
→ [ADMIN] LISTE DES COMPTES (recherche: 'alice') (1):
  ID 2: alice@mail.com - Perso: Alice Martin

/adm list comptes @example
→ Trouve tous les comptes avec "@example" dans l'email
```

**Fonctionnalités** :
- Recherche dans adresse_mail, nom, et prenom
- Affiche le badge [ADMIN] pour les administrateurs
- Affiche le personnage principal si défini
- Utile pour trouver un compte avant substitution

---

### `/adm su` (substitute user)

**Description** : Se substituer à un autre compte pour tester, tout en conservant les privilèges admin

**Usage** :

#### Se substituer à un compte
```
/adm su <compte_id>
/adm switch <compte_id>
```

**Exemple** :
```
/adm su 3

[ADMIN] Substitution vers compte #3: bob@test.com - Personnage: Bob Smith
Vous gardez vos privilèges admin.
Tapez '/adm su back' pour revenir à votre compte.
```

#### Revenir au compte admin original
```
/adm su back
/adm su retour
```

**Exemple** :
```
/adm su back

[ADMIN] Retour au compte: admin@example.com
Vous avez retrouvé votre compte administrateur.
```

**Fonctionnalités** :
- Bascule vers le compte cible
- Active le personnage principal du compte cible
- Conserve le flag `is_admin` en session
- Mémorise le compte admin original pour le retour
- Avertit si le compte cible n'a pas de personnage principal
- Permet de tester le jeu du point de vue d'un joueur

**Workflow typique** :
```bash
# 1. Trouver le compte à tester
/adm list comptes alice

# 2. Se substituer au compte
/adm su 2

# 3. Tester le jeu comme ce joueur (avec commandes admin)
/adm print PJ
position
scan

# 4. Revenir au compte admin
/adm su back
```

**Notes** :
- La substitution est temporaire (durée de la session)
- Vous pouvez utiliser toutes les commandes admin pendant la substitution
- Utile pour déboguer les problèmes spécifiques à un compte
- ATTENTION : Si le compte n'a pas de personnage principal, certaines actions peuvent échouer

---

## 🔍 Cas d'Usage

### Déboguer un personnage bloqué

```bash
# 1. Voir l'état détaillé du personnage
/adm print PJ

# 2. Vérifier les informations du vaisseau
/adm print SHIP

# 3. Si problème, voir les attributs précis
/adm info perso 1

# 4. Le téléporter dans une station
/adm mv perso 1 station 1

# 5. Lui donner des PA
/adm give pa 20
```

### Inspecter les relations d'un objet

```bash
# Voir un vaisseau avec toutes ses relations
/adm print Vaisseau 5

# Vérifier qu'un personnage a bien son vaisseau actif
/adm print PJ

# Inspecter une station et ses relations
/adm print Station 1
```

### Téléporter un vaisseau pour tester

```bash
# Téléporter vers une zone de test
/adm tp 100 100 100

# Vérifier la position
position

# Scanner la zone
/adm scan
```

### Réorganiser les personnages/vaisseaux

```bash
# Lister tous les vaisseaux
/adm list vaisseaux

# Amarrer un vaisseau à une station
/adm mv vaisseau 5 station 2

# Placer un personnage dans un vaisseau
/adm mv perso 3 vaisseau 5
```

### Tester le jeu avec un autre compte

```bash
# 1. Rechercher le compte à tester
/adm list comptes newplayer

# 2. Se substituer au compte
/adm su 5

# 3. Vérifier l'état du personnage
/adm print PJ

# 4. Si le compte n'a pas de personnage ou de vaisseau, en créer
/adm mv perso 5 vaisseau 10
/adm tp 0 0 0

# 5. Tester le jeu normalement
position
scan
help

# 6. Déboguer si nécessaire
/adm info perso 5
/adm give pa 20

# 7. Revenir au compte admin
/adm su back
```

---

## ⚠️ Précautions

### Incohérences possibles

**Attention** : Les commandes admin contournent les validations normales du jeu.

**Risques** :
- Personnage sans vaisseau actif
- Vaisseau avec coordonnées incohérentes
- État de jeu invalide

**Recommandation** : Toujours vérifier l'état après manipulation avec `/adm info`.

### Commandes destructives

Certaines commandes modifient l'état du jeu de façon irréversible :
- `/adm mv` change la position
- `/adm give` modifie les ressources

**Recommandation** : Faire une sauvegarde de la base de données avant tests intensifs.

---

## 📝 Notes Techniques

### Vérification Admin

Toutes les commandes vérifient automatiquement :
```php
if (!$personnage->compte->is_admin) {
    return 'Accès refusé';
}
```

### Alias Supportés

Plusieurs alias sont acceptés pour faciliter l'usage :

| Commande | Alias |
|----------|-------|
| `mv` | `move` |
| `tp` | `teleport` |
| `su` | `switch` |
| `perso` | `personnage` |
| `vaisseau` | `ship` |
| `vaisseaux` | `ships` |
| `persos` | `personnages` |
| `comptes` | `accounts` |
| `credits` | `argent` |
| `back` | `retour` (pour `/adm su back`) |

---

## 🚀 Commandes Futures

### Prévues

- `/adm spawn vaisseau <modele>` - Créer un vaisseau
- `/adm spawn station <nom>` - Créer une station
- `/adm delete <type> <id>` - Supprimer un objet
- `/adm set <objet> <attribut> <valeur>` - Modifier attribut directement
- `/adm god` - Mode invincible (PA/énergie infinis)
- `/adm reveal` - Révéler tous les systèmes stellaires
- `/adm reload` - Recharger un modèle avec toutes ses relations
- `/adm exec <code>` - Exécuter du code PHP arbitraire (dangereux)

---

**Dernière mise à jour** : 2025-11-23
**Version** : 1.2 (ajout `/adm list comptes` et `/adm su`)
