# Génération et Enrichissement du CSV GAIA

Ce document explique comment créer et enrichir le fichier CSV d'étoiles GAIA pour le jeu.

## Vue d'ensemble

Le système utilise un fichier CSV (`database/data/gaia_nearby_stars.csv`) contenant des étoiles proches du Système Solaire. Quatre commandes Artisan permettent de générer et enrichir ce fichier :

1. **`gaia:import-real`** - üåü **IMPORT RêEL** : Télécharge les vraies données du catalogue GAIA DR3 (ESA)
2. **`gaia:generate`** - Génère des étoiles procédurales aléatoires
3. **`gaia:add`** - Ajoute une étoile spécifique avec coordonnées cartésiennes
4. **`gaia:add-radius`** - Génère des étoiles dans une zone sphérique définie

## Format du CSV

Le fichier CSV contient les colonnes suivantes :

| Colonne | Description | Exemple |
|---------|-------------|---------|
| `source_id` | Identifiant unique | `PROC-000000000123` |
| `name` | Nom de l'étoile | `Proxima Centauri` |
| `ra` | Ascension droite (degrés) | `219.90205833` |
| `dec` | Déclinaison (degrés) | `-60.83399269` |
| `distance` | Distance en années-lumière | `4.2465` |
| `spectral_type` | Type spectral | `M5.5Ve` |
| `magnitude` | Magnitude apparente | `11.13` |

## Système de coordonnées

Le jeu utilise des **coordonnées cartésiennes** avec la Terre  l'origine (0, 0, 0) :

- **X** : Direction vers le Soleil (positif = vers l'extérieur)
- **Y** : Direction perpendiculaire (positif = vers la gauche)
- **Z** : Direction vers le p¥le nord galactique (positif = vers le haut)

**Conversion automatique** : Les commandes convertissent automatiquement les coordonnées cartésiennes (x, y, z) en coordonnées sphériques (RA/Dec) pour le CSV.

## Commandes disponibles

### 1. Importer les vraies données GAIA DR3 (ESA) üåü

```bash
php artisan gaia:import-real [--radius=100] [--limit=2000] [--min-magnitude=15] [--csv=] [--merge] [--insecure]
```

**œ≠ê RECOMMANDê** : Cette commande interroge la **vraie base de données GAIA DR3** de l'Agence Spatiale Européenne !

**Options :**
- `--radius=N` : Rayon maximum en années-lumière depuis le Soleil (défaut: 100)
- `--limit=N` : Nombre maximum d'étoiles  importer (défaut: 2000)
- `--min-magnitude=N` : Magnitude apparente maximale - plus bas = plus lumineux (défaut: 15)
- `--csv=path` : Fichier de sortie (défaut: `database/data/gaia_nearby_stars.csv`)
- `--merge` : Fusionner avec étoiles existantes au lieu de remplacer
- `--insecure` : Désactiver la vérification SSL (utile si erreur certificat auto-signé)

**Exemples :**

```bash
# Importer 2000 étoiles réelles dans un rayon de 100 AL
php artisan gaia:import-real

# Importer étoiles très proches (50 AL) et brillantes (mag < 10)
php artisan gaia:import-real --radius=50 --min-magnitude=10 --limit=1000

# Enrichir un fichier existant avec vraies données GAIA
php artisan gaia:import-real --radius=150 --merge

# Import massif : 5000 étoiles jusqu' 200 AL
php artisan gaia:import-real --radius=200 --limit=5000
```

**Fonctionnalités :**
- œúÖ Interroge l'API TAP officielle de GAIA DR3 (https://gea.esac.esa.int)
- œúÖ Données astronomiques réelles : positions précises, parallaxes, magnitudes
- œúÖ Filtrage par qualité (parallax_over_error > 5)
- œúÖ Estimation du type spectral depuis température effective et indice de couleur BP-RP
- œúÖ Conversion automatique parallaxe œÜí distance en années-lumière
- œúÖ Détection des doublons automatique
- œúÖ Mode fusion (--merge) pour combiner avec étoiles procédurales

**Données sources :**
- **GAIA DR3** : 3e édition du catalogue GAIA (juin 2022)
- **~2 milliards d'étoiles** recensées dans la Voie Lactée
- **Précision astrométrique** : ~0.01  0.5 mas (milli-arcseconde)
- **Source officielle** : European Space Agency (ESA)

**Requis :**
- Connexion internet active
- Timeout : 120 secondes (pour grandes requêtes)

---

### 2. Générer des étoiles aléatoires

```bash
php artisan gaia:generate [--count=100] [--radius=100] [--output=chemin/vers/fichier.csv]
```

**Options :**
- `--count=N` : Nombre d'étoiles  générer (défaut: 100)
- `--radius=N` : Rayon maximum en années-lumière (défaut: 100)
- `--output=path` : Fichier de sortie (défaut: `database/data/gaia_nearby_stars.csv`)

**Exemples :**

```bash
# Générer 100 étoiles dans un rayon de 100 AL
php artisan gaia:generate

# Générer 500 étoiles dans un rayon de 200 AL
php artisan gaia:generate --count=500 --radius=200

# Générer dans un fichier personnalisé
php artisan gaia:generate --output=storage/custom_stars.csv
```

**Fonctionnalités :**
- œúÖ Distribution réaliste des types spectraux (76% de naines rouges M)
- œúÖ Génération uniforme dans une sphère
- œúÖ Calcul automatique de la magnitude apparente
- œúÖ Détection des doublons (< 0.01 AL)
- œúÖ Choix : Remplacer / Enrichir / Annuler si fichier existant

### 2. Ajouter une étoile spécifique

```bash
php artisan gaia:add {name} {x} {y} {z} [--spectral-type=G2V] [--magnitude=] [--csv=]
```

**Arguments :**
- `name` : Nom de l'étoile (ex: "Alpha Centauri C")
- `x` : Coordonnée X en années-lumière
- `y` : Coordonnée Y en années-lumière
- `z` : Coordonnée Z en années-lumière

**Options :**
- `--spectral-type=TYPE` : Type spectral (défaut: G2V)
- `--magnitude=N` : Magnitude apparente (calculée automatiquement si omise)
- `--csv=path` : Fichier CSV (défaut: `database/data/gaia_nearby_stars.csv`)

**Exemples :**

```bash
# Ajouter une étoile de type G2V  4 AL de la Terre
php artisan gaia:add "Wolf 359" 7.78 0.0 0.0 --spectral-type=M6V

# Ajouter une géante rouge  10 AL
php artisan gaia:add "Aldebaran Simulée" 0 10 0 --spectral-type=K5III

# Ajouter avec magnitude spécifique
php artisan gaia:add "Mon êtoile" 5 5 5 --spectral-type=F5V --magnitude=6.5
```

**Fonctionnalités :**
- œúÖ Conversion automatique cartésien œÜí sphérique
- œúÖ Calcul automatique de la magnitude si non fournie
- œúÖ Détection des doublons par nom
- œúÖ Détection des doublons par proximité (< 0.01 AL)
- œúÖ Source ID unique basé sur hash

### 3. Générer des étoiles dans une zone sphérique

```bash
php artisan gaia:add-radius {x} {y} {z} {radius} [--count=50] [--csv=]
```

**Arguments :**
- `x` : Centre X en années-lumière
- `y` : Centre Y en années-lumière
- `z` : Centre Z en années-lumière
- `radius` : Rayon de la sphère en années-lumière

**Options :**
- `--count=N` : Nombre d'étoiles  générer (défaut: 50)
- `--csv=path` : Fichier CSV (défaut: `database/data/gaia_nearby_stars.csv`)

**Exemples :**

```bash
# Peupler une zone  50 AL de la Terre
php artisan gaia:add-radius 50 0 0 20 --count=100

# Créer un amas stellaire  100 AL
php artisan gaia:add-radius 100 100 0 10 --count=200

# Zone dense autour d'Alpha Centauri (environ 4.37 AL de la Terre)
php artisan gaia:add-radius 4 0 0 2 --count=30
```

**Fonctionnalités :**
- œúÖ Génération dans une sphère décalée
- œúÖ Distribution réaliste des types spectraux
- œúÖ Détection des doublons automatique
- œúÖ Fusion avec étoiles existantes

## élimination des doublons

Toutes les commandes implémentent une **détection automatique des doublons** :

### Par nom
Si une étoile porte déj le même nom, l'ajout est refusé.

### Par proximité spatiale
Si une étoile existe  **moins de 0.01 AL** (environ 630 UA) d'une nouvelle position :
- L'ajout est ignoré (en mode génération)
- L'ajout est refusé avec erreur (en mode ajout manuel)

**Seuil :** 0.01 AL = 630 UA (environ 15ó la distance Soleil-Neptune)

## Workflow recommandé

### Option A : Avec vraies données GAIA (œ≠ê Recommandé)

```bash
# 1. Importer les vraies étoiles GAIA dans un rayon de 100 AL
php artisan gaia:import-real --radius=100 --limit=2000

# 2. Enrichir avec zones spécifiques pour le gameplay
php artisan gaia:add-radius 50 50 0 20 --count=100

# 3. Ajouter étoiles personnalisées pour le scénario
php artisan gaia:add "Colonie Alpha" 75 25 10 --spectral-type=G5V --merge

# 4. Importer en base de données
php artisan migrate:fresh --seed
```

### Option B : Génération procédurale (si pas d'internet)

```bash
# 1. Générer 200 étoiles dans un rayon de 100 AL
php artisan gaia:generate --count=200 --radius=100

# 2. Enrichir avec étoiles réelles connues
php artisan gaia:add "Alpha Centauri A" 3.09 -3.09 0 --spectral-type=G2V
php artisan gaia:add "Sirius" -8.6 0 0 --spectral-type=A1V

# 3. Peupler des zones spécifiques
php artisan gaia:add-radius 10 10 5 5 --count=50
php artisan gaia:add-radius 150 0 0 30 --count=100

# 4. Vérifier et migrer
cat database/data/gaia_nearby_stars.csv | wc -l
php artisan migrate:fresh --seed
```

## Types spectraux supportés

Distribution réaliste basée sur les statistiques de la Voie Lactée :

| Type | Description | Température | Fréquence |
|------|-------------|-------------|-----------|
| O | Bleue supergéante | 30000-50000 K | 0.00003% |
| B | Bleue | 10000-30000 K | 0.13% |
| A | Blanche | 7500-10000 K | 0.6% |
| F | Blanc-jaune | 6000-7500 K | 3% |
| G | Jaune (comme le Soleil) | 5200-6000 K | 7.6% |
| K | Orange | 3700-5200 K | 12.1% |
| M | Rouge (naines rouges) | 2400-3700 K | 76.45% |

**Sous-classes :** 0-9 (ex: G2, M5)
**Classes de luminosité :** V (naine), IV (sous-géante), III (géante)

## Exemple complet : Créer l'environnement de jeu

### Scénario 1 : Univers réaliste avec GAIA DR3 (œ≠ê Recommandé)

```bash
# 1. Importer vraies étoiles GAIA (base réaliste)
php artisan gaia:import-real --radius=150 --limit=3000

# 2. Ajouter systèmes importants pour le scénario (en mode merge)
php artisan gaia:add "Nouvelle Terre" 25 10 5 --spectral-type=G5V
php artisan gaia:add "Avant-poste Colonial" 30 15 -5 --spectral-type=K0V

# 3. Enrichir zone de jeu avec plus d'étoiles
php artisan gaia:add-radius 0 0 0 15 --count=50

# 4. Créer amas lointain (objectif de mission)
php artisan gaia:add-radius 80 40 20 10 --count=80

# 5. Importer en base de données
php artisan migrate:fresh --seed
```

### Scénario 2 : Univers entièrement procédural

```bash
# 1. Générer base d'étoiles
php artisan gaia:generate --count=300 --radius=150

# 2. Ajouter systèmes importants pour le scénario
php artisan gaia:add "Nouvelle Terre" 25 10 5 --spectral-type=G5V
php artisan gaia:add "Avant-poste Colonial" 30 15 -5 --spectral-type=K0V

# 3. Peupler zone de départ (autour du Système Solaire)
php artisan gaia:add-radius 0 0 0 15 --count=50

# 4. Créer amas lointain (objectif de mission)
php artisan gaia:add-radius 80 40 20 10 --count=80

# 5. Importer en base de données
php artisan migrate:fresh --seed
```

## Dépannage

### Le fichier existe déj

La commande `gaia:generate` propose 3 options :
- **Remplacer** : êcrase complètement le fichier
- **Enrichir** : Ajoute les nouvelles étoiles aux existantes
- **Annuler** : Annule l'opération

### Doublon détecté

```
œùå Une étoile existe déj  moins de 0.01 AL de cette position !
```

**Solution :** Déplacer légèrement les coordonnées ou utiliser une position plus éloignée.

### Magnitude négative

C'est normal ! Les étoiles très brillantes ont des magnitudes négatives (ex: Sirius = -1.46).

### Conversion RA/Dec incorrecte

Vérifiez que les coordonnées X, Y, Z sont bien en années-lumière et correspondent au système de référence décrit ci-dessus.

### Erreur SSL / Certificat auto-signé

```
œùå Erreur lors de l'import GAIA:
cURL error 60: SSL certificate problem: self-signed certificate in certificate chain
```

**Cause :** Environnement avec proxy d'entreprise, certificats SSL locaux, ou configuration cURL stricte.

**Solution :** Utiliser l'option `--insecure` pour désactiver la vérification SSL :

```bash
php artisan gaia:import-real --insecure
```

⚠️📋 **Note de sécurité :** Cette option désactive la vérification SSL. Ä utiliser uniquement si vous faites confiance  votre réseau et  la source (GAIA ESA est une source officielle fiable).

### Erreur lors de l'import GAIA réel

```
œùå Erreur lors de l'import GAIA: Connection timeout
```

**Solutions :**
1. Vérifier votre connexion internet
2. Réduire le `--limit` (essayer 1000 au lieu de 2000)
3. Réduire le `--radius` (essayer 50 AL au lieu de 100)
4. Réessayer dans quelques minutes (le serveur GAIA peut être temporairement surchargé)
5. Si derrière un proxy/firewall, essayer `--insecure`

### Aucune donnée reßue de GAIA

```
œùå Aucune donnée reßue de GAIA. Vérifiez votre connexion internet.
```

**Causes possibles :**
- Pas de connexion internet
- Pare-feu bloquant les requêtes HTTPS vers gea.esac.esa.int
- Serveur GAIA temporairement hors ligne

**Solution de contournement :**
Utiliser la génération procédurale  la place :
```bash
php artisan gaia:generate --count=2000 --radius=100
```

### Format de réponse GAIA inattendu

L'API GAIA peut changer. Si vous rencontrez cette erreur, ouvrez une issue GitHub ou utilisez la génération procédurale.

## Intégration dans le jeu

Une fois le CSV créé, le `GaiaSeeder` l'importe automatiquement lors du `php artisan migrate:fresh --seed`.

**Ordre d'import :**
1. Si CSV existe œÜí Import CSV GAIA
2. Sinon œÜí Import étoiles connues hardcodées

**Calculs automatiques :**
- Conversion RA/Dec œÜí Secteur 3D du jeu
- Calcul de la puissance stellaire selon type
- Calcul de la détectabilité : `D_base = (200 - Puissance) / 3`
- Génération procédurale de planètes si configuré

## Notes techniques

### Précision des calculs

- Distance : 4 décimales (0.0001 AL œâà 0.63 UA)
- RA/Dec : 8 décimales (précision millisecondes d'arc)
- Magnitude : 2 décimales

### Performance

- Génération de 1000 étoiles : ~2-5 secondes
- Détection de doublons : O(n) par étoile
- Pour > 10000 étoiles, considérer une indexation spatiale

### Limites

- Maximum théorique : ~1 million d'étoiles (limite CSV)
- Recommandé : 100-5000 étoiles pour un gameplay fluide
- Rayon maximum testé : 1000 AL

## Références

- Distribution des types spectraux : [GAIA DR3](https://www.cosmos.esa.int/web/gaia/dr3)
- Système de coordonnées : Coordonnées équatoriales J2000
- Conversion cartésien œÜî sphérique : Formules astronomiques standard
