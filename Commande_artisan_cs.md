# Commandes Artisan - Conquête Spatiale

Ce document regroupe l'ensemble des commandes artisan personnalisées du projet Conquête Spatiale.

## Table des matières
- [Import de données GAIA](#import-de-données-gaia)
- [Recalcul des valeurs](#recalcul-des-valeurs)
- [Gestion de la base de données](#gestion-de-la-base-de-données)
- [Génération de contenu](#génération-de-contenu)
- [Maintenance](#maintenance)

---

## Import de données GAIA

Le catalogue GAIA DR3 de l'Agence Spatiale Européenne (ESA) contient des données réelles sur plus d'un milliard d'étoiles. Ces commandes permettent d'importer ces données dans votre jeu.

### `gaia:import-real`

Importe les vraies données du catalogue GAIA DR3 depuis l'API TAP de l'ESA.

#### Synopsis
```bash
php artisan gaia:import-real [options]
```

#### Description
Cette commande télécharge automatiquement les données réelles d'étoiles depuis le catalogue GAIA DR3 de l'ESA via leur API TAP (Table Access Protocol). Les données sont converties au format du jeu et sauvegardées dans un fichier CSV.

#### Options

| Option | Description | Valeur par défaut |
|--------|-------------|-------------------|
| `--radius=AL` | Rayon maximum en années-lumière depuis le Soleil | 100 |
| `--limit=N` | Nombre maximum d'étoiles à importer | 2000 |
| `--min-magnitude=N` | Magnitude apparente maximale (plus bas = plus lumineux) | 15 |
| `--csv=PATH` | Fichier de sortie CSV | `database/data/gaia_nearby_stars.csv` |
| `--merge` | Fusionner avec les étoiles existantes au lieu de remplacer | - |
| `--insecure` | Désactiver la vérification SSL (si erreur certificat) | - |

#### Exemples d'utilisation

```bash
# Importer 2000 étoiles dans un rayon de 100 AL (défaut)
php artisan gaia:import-real

# Importer 5000 étoiles dans un rayon de 200 AL
php artisan gaia:import-real --radius=200 --limit=5000

# Importer seulement les étoiles très lumineuses (magnitude < 10)
php artisan gaia:import-real --min-magnitude=10 --limit=1000

# Fusionner avec des données existantes
php artisan gaia:import-real --merge --radius=150

# Si erreur de certificat SSL
php artisan gaia:import-real --insecure
```

#### Processus complet d'import GAIA

Pour ajouter de nouveaux systèmes stellaires depuis GAIA :

1. **Étape 1 : Télécharger les données GAIA**
   ```bash
   php artisan gaia:import-real --radius=200 --limit=3000
   ```
   Cette commande crée le fichier `database/data/gaia_nearby_stars.csv`

2. **Étape 2 : Sauvegarder votre base actuelle (optionnel mais recommandé)**
   ```bash
   php artisan backup:create
   ```

3. **Étape 3 : Importer les données en base de données**
   ```bash
   php artisan migrate:fresh --seed
   ```
   Cette commande réinitialise la base et importe toutes les étoiles du CSV

**Note importante :** La commande `migrate:fresh --seed` supprime toutes les données existantes. Si vous voulez conserver vos données, utilisez plutôt l'option `--merge` à l'étape 1.

#### Données importées

Pour chaque étoile, les données suivantes sont importées :
- **source_id** : Identifiant GAIA DR3
- **nom** : Désignation de l'étoile
- **ra** : Ascension droite (coordonnées célestes)
- **dec** : Déclinaison (coordonnées célestes)
- **distance** : Distance en années-lumière
- **spectral_type** : Type spectral estimé (O, B, A, F, G, K, M)
- **magnitude** : Magnitude apparente

Ces données sont ensuite converties en coordonnées de jeu (secteur X/Y/Z et position intra-secteur).

---

### `gaia:generate`

Génère des étoiles procédurales au format GAIA (alternative si l'API n'est pas accessible).

#### Synopsis
```bash
php artisan gaia:generate [options]
```

#### Description
Génère des étoiles procédurales avec une distribution réaliste des types spectraux et les sauvegarde dans un fichier CSV compatible avec le format GAIA.

#### Options

| Option | Description | Valeur par défaut |
|--------|-------------|-------------------|
| `--count=N` | Nombre d'étoiles à générer | 100 |
| `--radius=AL` | Rayon maximum en années-lumière | 100 |
| `--output=PATH` | Fichier de sortie | `database/data/gaia_nearby_stars.csv` |

#### Exemples

```bash
# Générer 500 étoiles dans un rayon de 150 AL
php artisan gaia:generate --count=500 --radius=150

# Générer dans un fichier personnalisé
php artisan gaia:generate --count=1000 --output=custom_stars.csv
```

#### Distribution des types spectraux

Les étoiles générées suivent une distribution réaliste :
- Type M (naines rouges) : 76.45% - Les plus communes
- Type K (orange) : 12.1%
- Type G (jaune, comme le Soleil) : 7.6%
- Type F (jaune-blanc) : 3.0%
- Type A (blanc) : 0.6%
- Type B (bleu-blanc) : 0.13%
- Type O (bleu) : 0.00003% - Les plus rares

---

## Recalcul des valeurs

### `cs:recalculer-valeurs`

Recalcule les valeurs des systèmes stellaires, planètes et POI (Points d'Intérêt).

#### Synopsis
```bash
php artisan cs:recalculer-valeurs [options]
```

#### Description
Cette commande permet de recalculer automatiquement les valeurs physiques et statistiques des systèmes stellaires et des planètes du jeu. Elle est utile après des modifications de formules ou pour corriger des données incohérentes.

#### Options

| Option | Description | Valeurs possibles |
|--------|-------------|-------------------|
| `--type=TYPE` | Type de recalcul à effectuer | `systemes`, `planetes`, `all` (défaut: `all`) |
| `--dry-run` | Affiche les changements sans les appliquer | - |
| `--system-id=ID` | Recalcule uniquement le système avec cet ID | Numéro d'ID |
| `--planet-id=ID` | Recalcule uniquement la planète avec cet ID | Numéro d'ID |

#### Exemples d'utilisation

```bash
# Recalculer tous les systèmes et planètes
php artisan cs:recalculer-valeurs

# Recalculer uniquement les systèmes stellaires
php artisan cs:recalculer-valeurs --type=systemes

# Recalculer uniquement les planètes
php artisan cs:recalculer-valeurs --type=planetes

# Simuler le recalcul sans appliquer les changements (dry-run)
php artisan cs:recalculer-valeurs --dry-run

# Recalculer un système spécifique (ID 42)
php artisan cs:recalculer-valeurs --system-id=42

# Recalculer une planète spécifique (ID 123)
php artisan cs:recalculer-valeurs --planet-id=123
```

#### Détails des calculs

##### Pour les systèmes stellaires
- **Puissance** : Recalculée selon le type spectral (O, B, A, F, G, K, M)
  - Type O : 150-200
  - Type B : 100-140
  - Type A : 80-100
  - Type F : 60-80
  - Type G : 40-60 (type solaire)
  - Type K : 30-40
  - Type M : 20-30
  - Exception : Sol = 50
- **Détectabilité** : `(200 - puissance) / 3`
- **Puissance solaire** : Basée sur les propriétés du type d'étoile

##### Pour les planètes
- **Propriétés physiques** : Rayon, masse, gravité (selon le type de planète)
- **Atmosphère** : Présence et composition recalculées
- **Température** : Calculée selon la distance à l'étoile et la puissance solaire
- **Habitabilité** : Recalculée selon 5 critères :
  - Type de planète (terrestre ou océanique)
  - Zone habitable
  - Présence d'atmosphère
  - Gravité acceptable (0.4 à 2.5)
  - Température acceptable (-50°C à 50°C)

#### Statistiques affichées

La commande affiche un tableau récapitulatif :
- Systèmes traités
- Systèmes mis à jour
- Planètes traitées
- Planètes mises à jour
- Erreurs rencontrées

---

### `systemes:recalculer-puissance`

Recalcule uniquement la puissance des systèmes stellaires.

#### Synopsis
```bash
php artisan systemes:recalculer-puissance [options]
```

#### Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Affiche les changements sans les appliquer |
| `--filter=TYPE` | Filtre par type spectral (ex: G, M, K) |

#### Exemples

```bash
# Recalculer la puissance de tous les systèmes
php artisan systemes:recalculer-puissance

# Recalculer uniquement les étoiles de type G
php artisan systemes:recalculer-puissance --filter=G

# Simuler le recalcul
php artisan systemes:recalculer-puissance --dry-run
```

---

## Gestion de la base de données

### `db:reset-game`

Réinitialise la base de données du jeu.

#### Synopsis
```bash
php artisan db:reset-game
```

---

## Génération de contenu

### `generate:gaia`

Génère le système stellaire Gaia.

#### Synopsis
```bash
php artisan generate:gaia
```

### `add:gaia-star`

Ajoute une étoile au système Gaia.

#### Synopsis
```bash
php artisan add:gaia-star
```

### `add:gaia-radius`

Ajoute un rayon au système Gaia.

#### Synopsis
```bash
php artisan add:gaia-radius
```

### `import:real-gaia`

Importe les données réelles de Gaia.

#### Synopsis
```bash
php artisan import:real-gaia
```

---

## Maintenance

### `fix:solar-system-discoveries`

Corrige les découvertes du système solaire.

#### Synopsis
```bash
php artisan fix:solar-system-discoveries
```

---

## Gestion des sauvegardes

### `backup:create`

Crée une sauvegarde de la base de données.

#### Synopsis
```bash
php artisan backup:create
```

### `backup:list`

Liste toutes les sauvegardes disponibles.

#### Synopsis
```bash
php artisan backup:list
```

### `backup:restore`

Restaure une sauvegarde de la base de données.

#### Synopsis
```bash
php artisan backup:restore
```

---

## Notes techniques

### Types de planètes
- **Terrestre** : Planète rocheuse, potentiellement habitable
- **Gazeuse** : Géante gazeuse (hydrogène, hélium)
- **Océanique** : Planète majoritairement recouverte d'eau
- **Glacée** : Planète froide avec glace d'eau ou autres composés
- **Volcanique** : Planète avec activité volcanique intense
- **Désert** : Planète aride, peu ou pas d'eau
- **Naine** : Petite planète, souvent sans atmosphère

### Zone habitable
La zone habitable varie selon le type d'étoile :
- Type O : 10.0 - 50.0 UA
- Type B : 5.0 - 20.0 UA
- Type A : 2.0 - 8.0 UA
- Type F : 1.3 - 2.5 UA
- Type G : 0.95 - 1.37 UA (type solaire, Terre = 1 UA)
- Type K : 0.5 - 0.9 UA
- Type M : 0.1 - 0.4 UA (naines rouges)

---

## Bonnes pratiques

1. **Toujours utiliser `--dry-run` d'abord** : Avant d'appliquer des changements massifs, vérifiez ce qui sera modifié.

2. **Sauvegardes régulières** : Créez une sauvegarde avant d'exécuter des commandes de recalcul :
   ```bash
   php artisan backup:create
   php artisan cs:recalculer-valeurs
   ```

3. **Tests ciblés** : Testez d'abord sur un système ou une planète spécifique :
   ```bash
   php artisan cs:recalculer-valeurs --system-id=1 --dry-run
   ```

4. **Surveillance des erreurs** : Consultez le tableau des statistiques pour vérifier le nombre d'erreurs.

---

*Document créé le : 2025-11-28*
*Dernière mise à jour : 2025-11-28*
