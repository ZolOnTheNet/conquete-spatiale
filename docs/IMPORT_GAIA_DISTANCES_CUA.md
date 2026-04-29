# Import Gaia - Conversion des Distances en cUA

## 📌 IMPORTANT : Convention des Unités

Depuis la migration `2025_12_30_160337_convert_distance_etoile_to_cua`, **toutes** les coordonnées et distances sont stockées en **cUA (centi-unités astronomiques)** pour garantir la précision des calculs avec des **entiers**.

### Pourquoi des cUA ?

- **Précision** : Évite les erreurs d'arrondi des nombres flottants
- **Cohérence** : Toutes les positions sont en entiers (cUA)
- **Performance** : Calculs plus rapides avec des entiers

### Unités Utilisées

| Champ | Unité | Exemple (Terre) |
|-------|-------|-----------------|
| `secteur_x/y/z` | AL (entiers) | 0, 0, 0 |
| `position_x/y/z` | cUA (entiers) | Variable selon position orbitale |
| `distance_etoile` | **cUA (entiers)** | **100** (= 1.00 UA) |
| `cache_position_x/y/z` | cUA (entiers) | ±100 autour de 0 |

### Conversions

```
1 UA = 100 cUA
1 AL = 63 241 UA = 6 324 100 cUA
```

## 🚀 Import depuis Gaia

Lors de l'import de données depuis le catalogue Gaia, **TOUJOURS** convertir les distances en cUA :

### Exemple en PHP

```php
use App\Helpers\CoordinatesHelper;

// Distance reçue de Gaia (en UA)
$distance_ua = 5.2; // Jupiter

// Conversion UA → cUA pour stockage
$distance_cua = CoordinatesHelper::uaToCua($distance_ua); // 520

// Créer la planète
Planete::create([
    'nom' => 'Jupiter',
    'distance_etoile' => $distance_cua, // 520 cUA, PAS 5.2 UA !
    'systeme_stellaire_id' => $systeme->id,
    // ...
]);
```

### Exemple pour un Seeder

```php
public function run()
{
    $planetes = [
        ['nom' => 'Mercure', 'distance_ua' => 0.39],
        ['nom' => 'Vénus', 'distance_ua' => 0.72],
        ['nom' => 'Terre', 'distance_ua' => 1.00],
        ['nom' => 'Mars', 'distance_ua' => 1.52],
    ];

    foreach ($planetes as $data) {
        Planete::create([
            'nom' => $data['nom'],
            // IMPORTANT: Convertir UA → cUA
            'distance_etoile' => CoordinatesHelper::uaToCua($data['distance_ua']),
            // ...
        ]);
    }
}
```

## ⚠️ Pièges à Éviter

### ❌ MAUVAIS - Stocker en UA

```php
Planete::create([
    'distance_etoile' => 5.2, // ❌ ERREUR : sera traité comme 5.2 cUA = 0.052 UA !
]);
```

### ✅ CORRECT - Stocker en cUA

```php
Planete::create([
    'distance_etoile' => 520, // ✅ Correct : 520 cUA = 5.2 UA
]);
```

Ou mieux, utiliser le helper :

```php
Planete::create([
    'distance_etoile' => CoordinatesHelper::uaToCua(5.2), // ✅ Correct et explicite
]);
```

## 🔄 Vérification après Import

Après tout import, vérifiez les distances :

```bash
php artisan tinker
```

```php
// Vérifier une planète
$terre = Planete::where('nom', 'Terre')->first();
echo $terre->distance_etoile; // Devrait afficher 100 (cUA)

// Conversion pour affichage
echo \App\Helpers\CoordinatesHelper::cuaToUa($terre->distance_etoile); // Affiche 1.0 (UA)
```

## 📝 Checklist Import Gaia

- [ ] Récupérer `distance` depuis Gaia (en UA ou parsecs)
- [ ] Convertir en UA si nécessaire (1 parsec = 206265 UA)
- [ ] **Convertir UA → cUA** avec `CoordinatesHelper::uaToCua()`
- [ ] Stocker dans `distance_etoile`
- [ ] Vérifier quelques planètes connues (Terre = 100, Jupiter = 520, etc.)

## 🐛 Dépannage

### Les distances sont 100× trop grandes

➡️ Vous avez stocké des UA au lieu de cUA. Corriger avec :

```php
DB::statement('UPDATE planetes SET distance_etoile = distance_etoile * 100');
```

### Les distances sont 100× trop petites

➡️ Vous avez converti deux fois. Corriger avec :

```php
DB::statement('UPDATE planetes SET distance_etoile = distance_etoile / 100');
```

---

**Auteur** : Système de gestion des coordonnées spatiales
**Date** : 30 décembre 2025
**Migration associée** : `2025_12_30_160337_convert_distance_etoile_to_cua`
