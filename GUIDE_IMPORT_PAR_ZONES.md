# Guide : Import GAIA par zones concentriques

Ce guide explique comment agrandir progressivement votre univers zone par zone depuis un point central.

## Concept

Au lieu d'importer toutes les étoiles d'un coup, vous pouvez :
1. Commencer avec une petite zone autour du Soleil
2. Agrandir progressivement en cercles concentriques
3. Créer des "bulles" d'exploration autour de systèmes spécifiques

## Méthode 1 : Zones concentriques depuis le Soleil

### Zone 1 : Voisinage immédiat (0-50 AL)

```bash
php artisan gaia:import-real --radius=50 --limit=1000
php artisan migrate:fresh --seed
```

### Zone 2 : Expansion (50-100 AL)

```bash
php artisan gaia:import-real --merge --min-distance=50 --radius=100 --limit=2000
php artisan migrate:fresh --seed
```

### Zone 3 : Exploration lointaine (100-200 AL)

```bash
php artisan gaia:import-real --merge --min-distance=100 --radius=200 --limit=3000
php artisan migrate:fresh --seed
```

## Méthode 2 : Expansion autour d'un système spécifique

### Étape 1 : Identifier le système

Allez dans **Admin** → **Univers** et notez l'ID du système qui vous intéresse (par exemple, Alpha Centauri = ID 42).

### Étape 2 : Importer autour de ce système

```bash
# Importer 500 étoiles dans un rayon de 50 AL autour du système ID 42
php artisan gaia:import-real --system-id=42 --radius=50 --limit=500 --merge
php artisan migrate:fresh --seed
```

## Méthode 3 : Coordonnées personnalisées

Si vous connaissez les coordonnées célestes (RA/Dec) d'une zone :

```bash
# Zone autour d'Orion (RA=83.8, Dec=-5.4, Distance~1500 AL)
php artisan gaia:import-real \
  --center-ra=83.8 \
  --center-dec=-5.4 \
  --center-distance=1500 \
  --radius=100 \
  --limit=1000 \
  --merge

php artisan migrate:fresh --seed
```

## Options disponibles

| Option | Description | Exemple |
|--------|-------------|---------|
| `--radius=AL` | Rayon de la zone en années-lumière | `--radius=100` |
| `--min-distance=AL` | Distance minimale (pour zones concentriques) | `--min-distance=50` |
| `--system-id=ID` | Utiliser un système comme centre | `--system-id=42` |
| `--center-ra=DEG` | Ascension droite du centre | `--center-ra=83.8` |
| `--center-dec=DEG` | Déclinaison du centre | `--center-dec=-5.4` |
| `--center-distance=AL` | Distance du centre depuis le Soleil | `--center-distance=1500` |
| `--merge` | Fusionner avec données existantes | `--merge` |
| `--limit=N` | Nombre max d'étoiles | `--limit=2000` |

## Scénarios d'utilisation

### Scénario 1 : Exploration progressive

**Jour 1 :** Zone locale
```bash
php artisan gaia:import-real --radius=25 --limit=200
php artisan migrate:fresh --seed
```

**Jour 7 :** Première expansion
```bash
php artisan backup:create
php artisan gaia:import-real --merge --min-distance=25 --radius=75 --limit=800
php artisan migrate:fresh --seed
```

**Jour 30 :** Grande expansion
```bash
php artisan backup:create
php artisan gaia:import-real --merge --min-distance=75 --radius=150 --limit=2000
php artisan migrate:fresh --seed
```

### Scénario 2 : Création de "bulles" d'exploration

```bash
# 1. Zone autour du Soleil
php artisan gaia:import-real --radius=50 --limit=500
php artisan migrate:fresh --seed

# 2. Trouver l'ID d'Alpha Centauri dans l'admin (exemple: ID 5)

# 3. Créer une bulle autour d'Alpha Centauri
php artisan backup:create
php artisan gaia:import-real --merge --system-id=5 --radius=30 --limit=300
php artisan migrate:fresh --seed

# 4. Trouver l'ID de Sirius (exemple: ID 12)

# 5. Créer une bulle autour de Sirius
php artisan backup:create
php artisan gaia:import-real --merge --system-id=12 --radius=40 --limit=400
php artisan migrate:fresh --seed
```

### Scénario 3 : Zones non-concentriques

```bash
# Zone A : Autour du Soleil
php artisan gaia:import-real --radius=50 --limit=500
php artisan migrate:fresh --seed

# Zone B : Région d'Orion (loin du Soleil)
php artisan backup:create
php artisan gaia:import-real --merge \
  --center-ra=83.8 \
  --center-dec=-5.4 \
  --center-distance=1500 \
  --radius=100 \
  --limit=500
php artisan migrate:fresh --seed
```

## Visualisation des zones

Après chaque import, vérifiez dans **Admin** → **Carte** :
- Les nouvelles étoiles apparaissent
- Les zones se remplissent progressivement
- Aucun doublon n'est créé (l'option `--merge` évite les doublons)

## Bonnes pratiques

### 1. Toujours sauvegarder avant d'agrandir

```bash
php artisan backup:create
```

### 2. Utiliser --merge pour éviter de tout recréer

```bash
# ❌ MAUVAIS : Écrase tout
php artisan gaia:import-real --radius=100

# ✅ BON : Ajoute aux données existantes
php artisan gaia:import-real --merge --min-distance=50 --radius=100
```

### 3. Augmenter progressivement le rayon

Ne passez pas de 50 AL à 500 AL d'un coup. Augmentez par paliers :
- 25 → 50 → 100 → 150 → 200 → 300 → 500

### 4. Vérifier le nombre d'étoiles

Commencez avec `--limit` petit, puis augmentez :
```bash
# Test
php artisan gaia:import-real --radius=100 --limit=100 --dry-run

# Si OK, augmenter
php artisan gaia:import-real --radius=100 --limit=2000
```

## Calcul des zones concentriques

Pour créer des anneaux parfaits :

**Anneau 1 :** 0-50 AL
```bash
php artisan gaia:import-real --radius=50
```

**Anneau 2 :** 50-100 AL
```bash
php artisan gaia:import-real --merge --min-distance=50 --radius=100
```

**Anneau 3 :** 100-150 AL
```bash
php artisan gaia:import-real --merge --min-distance=100 --radius=150
```

## Comprendre les coordonnées célestes

### Ascension Droite (RA)
- Équivalent de la longitude céleste
- Mesurée en degrés : 0° à 360°
- Ou en heures : 0h à 24h (1h = 15°)

### Déclinaison (Dec)
- Équivalent de la latitude céleste
- Mesurée en degrés : -90° (pôle sud) à +90° (pôle nord)

### Exemples de coordonnées célestes

| Objet | RA (°) | Dec (°) | Distance (AL) |
|-------|--------|---------|---------------|
| Proxima Centauri | 217.4 | -62.7 | 4.2 |
| Sirius | 101.3 | -16.7 | 8.6 |
| Betelgeuse (Orion) | 88.8 | 7.4 | 548 |
| Vega | 279.2 | 38.8 | 25 |

## Dépannage

### "Aucune étoile trouvée"

1. Vérifiez que le centre existe
2. Augmentez `--radius`
3. Augmentez `--min-magnitude`
4. Vérifiez les coordonnées (RA/Dec)

### "Trop de doublons"

Utilisez `--merge` :
```bash
php artisan gaia:import-real --merge ...
```

### "Import très lent"

1. Réduisez `--limit`
2. Réduisez `--radius`
3. Augmentez `--min-magnitude`

### "Système non trouvé" (--system-id)

Vérifiez l'ID dans **Admin** → **Univers**

---

## Exemple complet : Univers progressif

```bash
# Semaine 1 : Voisinage
php artisan gaia:import-real --radius=30 --limit=300
php artisan migrate:fresh --seed

# Semaine 2 : Expansion locale
php artisan backup:create
php artisan gaia:import-real --merge --min-distance=30 --radius=75 --limit=700
php artisan migrate:fresh --seed

# Semaine 3 : Zone étendue
php artisan backup:create
php artisan gaia:import-real --merge --min-distance=75 --radius=125 --limit=1200
php artisan migrate:fresh --seed

# Semaine 4 : Bulle Alpha Centauri
php artisan backup:create
php artisan gaia:import-real --merge --system-id=5 --radius=40 --limit=400
php artisan migrate:fresh --seed

# Mois 2 : Grande expansion
php artisan backup:create
php artisan gaia:import-real --merge --min-distance=125 --radius=200 --limit=2500
php artisan migrate:fresh --seed
```

Vous avez maintenant un univers riche qui s'étend progressivement autour de zones d'intérêt !

---

*Document créé le : 2025-11-28*
*Dernière mise à jour : 2025-11-28*
