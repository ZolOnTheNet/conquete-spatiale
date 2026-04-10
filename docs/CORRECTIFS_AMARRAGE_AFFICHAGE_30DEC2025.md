# Correctifs Amarrage et Affichage - 30 Décembre 2025

## 🎯 Problèmes Identifiés et Corrigés

### 1. ✅ Logique d'Amarrage Incohérente

**Problème** : À < 0.01 UA d'un objet, le vaisseau affichait "Vous êtes déjà à proximité immédiate" mais ne permettait pas l'amarrage.

**Cause** :
- Fonction `sApprocher()` bloquait à < 0.01 UA sans proposer d'action
- Fonction `sAmarrer()` exigeait < 1.0 UA au lieu de < 0.01 UA

**Solution** :
- `TimonerieController::sApprocher()` ligne 305-314 : Retourne maintenant un message indiquant qu'on peut s'amarrer
- `TimonerieController::sAmarrer()` ligne 463-471 : Seuil réduit de 1.0 UA à 0.01 UA (1 cUA)

```php
// AVANT (incorrect)
if ($distanceUa < $seuilProximiteUa) {
    return response()->json([
        'message' => 'Vous êtes déjà à proximité immédiate...',
    ]);
}
// Amarrage : $distance >= 1.0

// APRÈS (correct)
if ($distanceUa < 0.01) {
    return response()->json([
        'success' => true,
        'message' => 'Vous êtes à proximité immédiate... Vous pouvez vous amarrer.',
        'peutAmarrer' => true,
    ]);
}
// Amarrage : $distance >= 0.01
```

---

### 2. ✅ Échelle Vue de Dessus Incorrecte

**Problème** : La vue de dessus affichait jusqu'à 100 UA, au lieu de max 20 UA autour du vaisseau.

**Solution** :
- `timonerie.blade.php` ligne 533 : `radarMaxDistance` changé de 100 à 20 UA
- `timonerie.blade.php` ligne 593 : Cercles concentriques adaptés : [0.1, 0.5, 1, 5, 10, 20] au lieu de [1, 5, 10, 20, 50, 100]

```javascript
// AVANT
const radarMaxDistance = 100;
const circles = [1, 5, 10, 20, 50, 100];

// APRÈS
const radarMaxDistance = 20;
const circles = [0.1, 0.5, 1, 5, 10, 20];
```

---

### 3. ✅ Affichage Distances en cUA au lieu de UA

**Problème CRITIQUE** : Tous les blades affichaient `distance_etoile` directement, mais cette valeur est stockée en **cUA** (entiers) depuis la migration. L'affichage montrait donc des valeurs 100× trop grandes (ex: Terre à 100 UA au lieu de 1.0 UA).

**Rappel important** :
- **Stockage** : toujours en cUA (entiers) pour éviter les erreurs d'arrondi
- **Affichage** : toujours en UA (décimaux) pour l'utilisateur
- **Conversion** : 1 UA = 100 cUA

**Fichiers Corrigés** :

#### 3.1. `admin/carte-secteur.blade.php` (3 occurrences)

```php
// Ligne 58 : Calcul d'échelle
// AVANT
$scaleUA = ($planetesPlusEloignee->distance_etoile / $radiusMaxPx) * 60;

// APRÈS
$scaleUA = (($planetesPlusEloignee->distance_etoile / 100) / $radiusMaxPx) * 60;
```

```blade
<!-- Ligne 109 : Étiquette sur carte SVG -->
<!-- AVANT -->
{{ number_format($planete->distance_etoile, 1) }} UA

<!-- APRÈS -->
{{ number_format($planete->distance_etoile / 100, 2) }} UA
```

```blade
<!-- Ligne 260 : Tooltip planète -->
<!-- AVANT -->
{{ number_format($planete->distance_etoile, 2) }} UA

<!-- APRÈS -->
{{ number_format($planete->distance_etoile / 100, 2) }} UA
```

#### 3.2. `game/carte-secteur.blade.php` (3 occurrences)

Même corrections que pour `admin/carte-secteur.blade.php` :
- Ligne 55 : Calcul d'échelle
- Ligne 107 : Étiquette SVG
- Ligne 254 : Tooltip planète

#### 3.3. `admin/partials/systeme-visualisation.blade.php` (2 occurrences)

- Ligne 34 : Calcul d'échelle
- Ligne 85 : Étiquette SVG

#### 3.4. `admin/planete-detail.blade.php` (1 occurrence + contrôleur)

```blade
<!-- Ligne 105 : Formulaire d'édition -->
<!-- AVANT -->
<input type="number" name="distance_etoile" value="{{ $planete->distance_etoile }}">

<!-- APRÈS -->
<input type="number" name="distance_etoile" value="{{ $planete->distance_etoile / 100 }}">
```

**Important** : Comme l'utilisateur entre une valeur en UA, le contrôleur doit multiplier par 100 avant de sauvegarder.

---

### 4. ✅ Contrôleur Admin - Conversions Manquantes

**Problème** : Le formulaire d'édition de planète acceptait des UA mais les stockait directement sans conversion.

**Solution** : `AdminController::updatePlanete()` ligne 358-361

```php
// Convertir distance_etoile de UA vers cUA (l'utilisateur entre en UA, on stocke en cUA)
if (isset($validated['distance_etoile'])) {
    $validated['distance_etoile'] = $validated['distance_etoile'] * 100;
}
```

**Bonus** : Correction de la génération procédurale (ligne 725)

```php
// AVANT
'distance_etoile' => $i * 0.5 + rand(0, 10) / 10,

// APRÈS
'distance_etoile' => ($i * 0.5 + rand(0, 10) / 10) * 100, // Convertir UA → cUA
```

---

## 📊 Résumé des Modifications

### Fichiers Modifiés

| Fichier | Lignes | Type de modification |
|---------|--------|---------------------|
| `app/Http/Controllers/TimonerieController.php` | 305-314, 463-471 | Logique amarrage |
| `resources/views/game/navire/timonerie.blade.php` | 533, 593 | Échelle vue dessus |
| `resources/views/admin/carte-secteur.blade.php` | 58, 109, 260 | Affichage distances |
| `resources/views/game/carte-secteur.blade.php` | 55, 107, 254 | Affichage distances |
| `resources/views/admin/partials/systeme-visualisation.blade.php` | 34, 85 | Affichage distances |
| `resources/views/admin/planete-detail.blade.php` | 105 | Formulaire édition |
| `app/Http/Controllers/AdminController.php` | 358-361, 725 | Conversion UA → cUA |

### Statistiques

- **7 fichiers** modifiés
- **14 lignes** corrigées pour affichage distances
- **3 conversions** UA → cUA ajoutées
- **2 seuils** d'amarrage corrigés
- **1 échelle** radar ajustée

---

## 🧪 Tests de Vérification

### Test 1 : Amarrage

```bash
# 1. S'approcher d'une planète jusqu'à < 0.01 UA
# 2. Vérifier message : "Vous pouvez vous amarrer"
# 3. Tester commande amarrage
# Résultat attendu : Amarrage réussi
```

### Test 2 : Vue de Dessus

```bash
# 1. Ouvrir timonerie, onglet "Vue Dessus"
# 2. Vérifier échelle max = 20 UA
# 3. Vérifier cercles : 0.1, 0.5, 1, 5, 10, 20 UA
# Résultat attendu : Systèmes distants en périphérie, échelle correcte
```

### Test 3 : Affichage Distances

```bash
# 1. Ouvrir carte secteur (admin ou game)
# 2. Vérifier distances planètes :
#    - Terre : ~1.0 UA (pas 100)
#    - Mars : ~1.5 UA (pas 152)
#    - Jupiter : ~5.2 UA (pas 520)
# Résultat attendu : Toutes les distances en UA
```

### Test 4 : Formulaire Édition

```bash
# 1. Admin > Planètes > Modifier
# 2. Vérifier champ "Distance à l'étoile" affiche en UA (ex: 1.52 pour Mars)
# 3. Modifier valeur (ex: 2.0 UA)
# 4. Sauvegarder
# 5. Vérifier en base : distance_etoile = 200 (cUA)
# Résultat attendu : Conversion UA → cUA appliquée
```

---

## 📝 Règles à Respecter

### Règle d'Or : cUA en Interne, UA en Affichage

```
┌─────────────────────────────────────────────────────┐
│  STOCKAGE (Base de données)                         │
│  → Toujours en cUA (entiers)                        │
│  → Exemple : distance_etoile = 152 (Mars)           │
└─────────────────────────────────────────────────────┘
                        ↓
                   Conversion
                  (diviser par 100)
                        ↓
┌─────────────────────────────────────────────────────┐
│  AFFICHAGE (Blades, API)                            │
│  → Toujours en UA (décimaux)                        │
│  → Exemple : {{ $planete->distance_etoile / 100 }}  │
│  → Affichage : 1.52 UA                              │
└─────────────────────────────────────────────────────┘
```

### Checklist pour Futurs Changements

Avant d'afficher `distance_etoile` :
- [ ] Vérifier si c'est pour un affichage utilisateur
- [ ] Si oui, diviser par 100 : `$planete->distance_etoile / 100`
- [ ] Formater avec `number_format(..., 2)` pour 2 décimales
- [ ] Ajouter " UA" après la valeur

Avant de sauvegarder `distance_etoile` :
- [ ] Vérifier si la valeur vient d'un formulaire utilisateur
- [ ] Si oui, multiplier par 100 : `$validated['distance_etoile'] * 100`
- [ ] Ou utiliser `CoordinatesHelper::uaToCua($valeur)`

---

## 🔮 Prochaines Vérifications

1. **Tester l'interface** après rafraîchissement navigateur
2. **Vérifier logs** si erreurs JavaScript
3. **Tester amarrage** sur différents objets (planètes, stations, systèmes)
4. **Vérifier formulaires** de création/édition de planètes

---

**Auteur** : Claude Sonnet 4.5
**Date** : 30 décembre 2025
**Session** : Correctifs amarrage, échelle radar, et affichages distances
