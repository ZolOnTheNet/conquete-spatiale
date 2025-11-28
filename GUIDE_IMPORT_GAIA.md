# Guide rapide : Import de données GAIA

Ce guide vous explique comment ajouter de nouveaux systèmes stellaires depuis le catalogue GAIA DR3.

## Méthode 1 : Import de vraies données GAIA (Recommandé)

### Prérequis
- Connexion Internet active
- Accès à l'API GAIA de l'ESA

### Étapes

#### 1. Télécharger les données depuis GAIA

```bash
php artisan gaia:import-real --radius=200 --limit=3000
```

**Paramètres :**
- `--radius=200` : Télécharge les étoiles dans un rayon de 200 années-lumière autour du centre (par défaut : Soleil)
- `--limit=3000` : Limite à 3000 étoiles maximum

**Résultat :** Crée le fichier `database/data/gaia_nearby_stars.csv`

### Import par zones concentriques

Vous pouvez agrandir progressivement votre univers zone par zone :

```bash
# Zone 1 : Voisinage immédiat (0-50 AL)
php artisan gaia:import-real --radius=50 --limit=1000
php artisan migrate:fresh --seed

# Zone 2 : Expansion (50-100 AL)
php artisan gaia:import-real --merge --min-distance=50 --radius=100 --limit=2000
php artisan migrate:fresh --seed

# Zone 3 : Exploration lointaine (100-200 AL)
php artisan gaia:import-real --merge --min-distance=100 --radius=200 --limit=3000
php artisan migrate:fresh --seed
```

**Voir :** [GUIDE_IMPORT_PAR_ZONES.md](GUIDE_IMPORT_PAR_ZONES.md) pour plus de détails

#### 2. Sauvegarder votre base de données actuelle (Optionnel)

```bash
php artisan backup:create
```

Cette étape est recommandée si vous avez des données importantes.

#### 3. Importer les données en base

```bash
php artisan migrate:fresh --seed
```

**Attention :** Cette commande supprime toutes les données existantes et réimporte tout depuis le CSV.

### Options avancées

#### Importer plus d'étoiles

```bash
# 5000 étoiles dans un rayon de 300 AL
php artisan gaia:import-real --radius=300 --limit=5000
```

#### Importer seulement les étoiles lumineuses

```bash
# Magnitude < 10 (plus visible)
php artisan gaia:import-real --min-magnitude=10 --limit=2000
```

#### Fusionner avec des données existantes

```bash
# Ajoute de nouvelles étoiles sans écraser les anciennes
php artisan gaia:import-real --merge --radius=250
```

#### Importer autour d'un système spécifique

```bash
# Importer 500 étoiles dans un rayon de 50 AL autour du système ID 42
php artisan gaia:import-real --system-id=42 --radius=50 --limit=500 --merge
```

#### Importer avec coordonnées personnalisées

```bash
# Zone autour d'Orion (RA=83.8, Dec=-5.4)
php artisan gaia:import-real \
  --center-ra=83.8 \
  --center-dec=-5.4 \
  --center-distance=1500 \
  --radius=100 \
  --limit=1000 \
  --merge
```

#### En cas d'erreur SSL

```bash
php artisan gaia:import-real --insecure
```

---

## Méthode 2 : Générer des étoiles procédurales

Si l'API GAIA n'est pas accessible, vous pouvez générer des étoiles procédurales.

### Étapes

#### 1. Générer le fichier CSV

```bash
php artisan gaia:generate --count=1000 --radius=150
```

**Paramètres :**
- `--count=1000` : Génère 1000 étoiles
- `--radius=150` : Dans un rayon de 150 AL

#### 2. Importer en base

```bash
php artisan migrate:fresh --seed
```

---

## Vérification après import

### Compter les systèmes importés

Connectez-vous à l'interface d'administration et vérifiez :
- Menu **Admin** → **Univers**
- Le nombre total de systèmes devrait correspondre à votre import

### Visualiser sur la carte

- Menu **Admin** → **Carte**
- Vous devriez voir tous les systèmes stellaires

---

## Après l'import : Générer les planètes

Par défaut, les systèmes importés n'ont **pas encore de planètes**. Elles sont générées à la demande.

### Option 1 : Générer via l'interface admin

1. Allez dans **Admin** → **Univers**
2. Cliquez sur un système
3. Cliquez sur le bouton **"Générer Planètes"**

### Option 2 : Générer par commande (à venir)

Une commande pour générer toutes les planètes en masse sera ajoutée prochainement.

---

## Recalculer les valeurs (optionnel)

Si vous souhaitez recalculer la puissance et la détectabilité des systèmes :

```bash
# Simuler (sans appliquer)
php artisan cs:recalculer-valeurs --dry-run

# Appliquer le recalcul
php artisan cs:recalculer-valeurs
```

---

## Restaurer une sauvegarde

Si quelque chose se passe mal :

```bash
# Lister les sauvegardes
php artisan backup:list

# Restaurer une sauvegarde
php artisan backup:restore
```

---

## Foire aux questions

### Combien d'étoiles puis-je importer ?

**Recommandations :**
- **Test / Développement** : 100-500 étoiles (rapide)
- **Petit univers** : 1000-2000 étoiles
- **Univers moyen** : 3000-5000 étoiles
- **Grand univers** : 10000+ étoiles (peut être lent)

### Quelle est la différence entre `--radius` et `--limit` ?

- **`--radius`** : Zone géographique (en années-lumière)
- **`--limit`** : Nombre maximum d'étoiles à télécharger

Par exemple, `--radius=100 --limit=5000` téléchargera jusqu'à 5000 étoiles dans un rayon de 100 AL. Si GAIA a seulement 1500 étoiles dans ce rayon, vous n'en obtiendrez que 1500.

### Puis-je ajouter des étoiles sans tout réimporter ?

Oui, utilisez l'option `--merge` :

```bash
# Première import
php artisan gaia:import-real --radius=100 --limit=1000

# Plus tard, ajouter d'autres étoiles (radius différent)
php artisan gaia:import-real --merge --radius=200 --limit=2000

# Réimporter en base
php artisan migrate:fresh --seed
```

### L'import est très lent, que faire ?

L'API GAIA peut être lente selon la charge. Solutions :
1. Réduire `--limit`
2. Réduire `--radius`
3. Augmenter `--min-magnitude` (moins d'étoiles faibles)
4. Utiliser `gaia:generate` pour des étoiles procédurales (instantané)

### Quelle est la magnitude ?

La magnitude mesure la luminosité apparente :
- **-1 à 5** : Très visible à l'œil nu (ex: Sirius = -1.46)
- **6 à 10** : Visible avec jumelles
- **11+** : Visible seulement avec télescope

Plus le nombre est **petit**, plus l'étoile est **brillante**.

---

## Exemple complet : Premier import

```bash
# 1. Créer une sauvegarde de sécurité
php artisan backup:create

# 2. Télécharger 2000 étoiles dans 150 AL
php artisan gaia:import-real --radius=150 --limit=2000

# 3. Vérifier le CSV créé
# Le fichier est dans : database/data/gaia_nearby_stars.csv

# 4. Importer en base de données
php artisan migrate:fresh --seed

# 5. Vérifier dans l'admin
# Aller sur : http://localhost/admin/univers
```

C'est tout ! Vous avez maintenant un univers avec de vraies données GAIA.

---

*Document créé le : 2025-11-28*
*Dernière mise à jour : 2025-11-28*
