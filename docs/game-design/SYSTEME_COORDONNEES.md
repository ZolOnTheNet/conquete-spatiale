# 📍 SYSTÈME DE COORDONNÉES
## Jeu de Conquête Galactique

---

## ⚠️ RÈGLE CRITIQUE

**LES COORDONNÉES NE SONT PAS MULTIPLIÉES PAR 10 !**

---

## 🎯 Structure des Coordonnées

### Dans la Base de Données

Chaque objet spatial (système stellaire, vaisseau, mine, etc.) a **6 champs de coordonnées** :

```sql
-- Coordonnées AL ENTIÈRES (Années-Lumière)
secteur_x INT NOT NULL,
secteur_y INT NOT NULL,
secteur_z INT NOT NULL,

-- Position DÉCIMALE dans le secteur (0.0 à 1.0)
position_x DECIMAL(10,3) NOT NULL,
position_y DECIMAL(10,3) NOT NULL,
position_z DECIMAL(10,3) NOT NULL
```

### Signification

**`secteur_x/y/z`** : Coordonnées AL entières
- Exemple : `secteur_x = 5` → Le système est à 5 AL sur l'axe X
- **C'EST LA COORDONNÉE AFFICHÉE SUR LA CARTE**
- **PAS de calcul, PAS de multiplication**

**`position_x/y/z`** : Position précise DANS le secteur
- Valeurs entre `0.0` et `1.0`
- Exemple : `position_x = 0.5` → Le système est au milieu du secteur
- **Utilisé uniquement pour navigation précise et sous-cartes**
- **PAS affiché sur la carte principale**

---

## 📊 Exemples

### Sol (Système Solaire)
```
secteur_x = 0
secteur_y = 0
secteur_z = 0
position_x = 0.000
position_y = 0.000
position_z = 0.000
```
**Coordonnées carte : (0, 0, 0) AL**

### Vega Aurigae
```
secteur_x = 0
secteur_y = 0
secteur_z = 4
position_x = 0.962
position_y = 0.779
position_z = 0.530
```
**Coordonnées carte : (0, 0, 4) AL**
Position précise : 4.53 AL sur l'axe Z

---

## ❌ ERREURS À NE PAS FAIRE

### ❌ FAUX - Multiplication par 10
```php
// ❌ ERREUR !
$abs_x = $systeme->secteur_x * 10 + $systeme->position_x;
// Résultat : 0 * 10 + 0.962 = 0.962 (FAUX !)
```

```javascript
// ❌ ERREUR !
const sysAbsX = parseInt(system.secteur_x * 10 + system.position_x);
// Résultat : 0 * 10 + 0.962 = 0 (FAUX !)
```

### ❌ FAUX - Division par 10 (CHERCHER dans la grille)
```javascript
// ❌ ERREUR !
const secteurX = Math.floor(absX / 10);
// Si absX = 4, alors secteurX = 0 (FAUX !)
// La grille est indexée par secteur_x/y/z qui SONT les AL entières
// Vega Aurigae est dans grille[0][0][4], pas grille[0][0][0] !
```

```php
// ❌ ERREUR !
$secteurX = floor($absX / 10);
$hasSystem = isset($grille[$secteurX][$secteurY][$secteurZ]);
// Si absZ = 4, cherche dans grille[0][0][0] au lieu de grille[0][0][4] !
```

### ✅ CORRECT - Utilisation directe
```php
// ✅ CORRECT
$abs_x = $systeme->secteur_x;
$abs_y = $systeme->secteur_y;
$abs_z = $systeme->secteur_z;
```

```javascript
// ✅ CORRECT
const sysAbsX = parseInt(system.secteur_x);
const sysAbsY = parseInt(system.secteur_y);
const sysAbsZ = parseInt(system.secteur_z);
```

---

## 🗺️ Affichage sur la Carte

### Carte Principale (100 AL × 100 AL)

```php
// Pour afficher sur la carte
foreach ($systemes as $systeme) {
    // Coordonnées AL entières = directement les secteurs
    $x_carte = $systeme->secteur_x;
    $y_carte = $systeme->secteur_y;
    $z_carte = $systeme->secteur_z;

    // Les positions décimales ne sont PAS utilisées ici
}
```

### Indexation de la Grille

**IMPORTANT** : La grille PHP est indexée par secteur_x/y/z qui **SONT** les coordonnées AL entières :

```php
// Construction de la grille (AdminController)
$grille = [];
foreach ($systemes as $systeme) {
    $grille[$systeme->secteur_x][$systeme->secteur_y][$systeme->secteur_z] = $systeme;
}

// Vega Aurigae : secteur (0, 0, 4)
// Elle est dans : $grille[0][0][4]

// ✅ CORRECT - Chercher un système
$absZ = 4;  // Coordonnée carte en AL
$secteurZ = $absZ;  // PAS de division !
$systeme = $grille[0][0][$secteurZ] ?? null;  // Trouve Vega Aurigae ✓

// ❌ FAUX - Division par 10
$absZ = 4;
$secteurZ = floor($absZ / 10);  // = 0 !
$systeme = $grille[0][0][$secteurZ] ?? null;  // Cherche dans [0][0][0], ne trouve pas Vega Aurigae ✗
```

### Sous-Carte (Niveau Secteur)

```php
// Pour la navigation précise dans un secteur
$x_precise = $systeme->secteur_x + $systeme->position_x;
// Exemple : 4 + 0.530 = 4.530 AL

// Pour changer de secteur si position >= 1.0
while ($systeme->position_x >= 1.0) {
    $systeme->secteur_x += 1;
    $systeme->position_x -= 1.0;
}
```

---

## 🔄 Conversion et Navigation

### Déplacement d'un Vaisseau

```php
// Déplacer de 0.5 AL sur l'axe X
$vaisseau->position_x += 0.5;

// Normaliser si position >= 1.0
if ($vaisseau->position_x >= 1.0) {
    $vaisseau->secteur_x += floor($vaisseau->position_x);
    $vaisseau->position_x = fmod($vaisseau->position_x, 1.0);
}

// Normaliser si position < 0.0
if ($vaisseau->position_x < 0.0) {
    $vaisseau->secteur_x += floor($vaisseau->position_x);
    $vaisseau->position_x = 1.0 + fmod($vaisseau->position_x, 1.0);
}
```

### Distance entre deux Objets

```php
// Distance 3D en AL
function distance($obj1, $obj2) {
    $dx = ($obj1->secteur_x + $obj1->position_x) -
          ($obj2->secteur_x + $obj2->position_x);
    $dy = ($obj1->secteur_y + $obj1->position_y) -
          ($obj2->secteur_y + $obj2->position_y);
    $dz = ($obj1->secteur_z + $obj1->position_z) -
          ($obj2->secteur_z + $obj2->position_z);

    return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
}
```

### Même Secteur ?

```php
function meme_secteur($obj1, $obj2) {
    return $obj1->secteur_x === $obj2->secteur_x &&
           $obj1->secteur_y === $obj2->secteur_y &&
           $obj1->secteur_z === $obj2->secteur_z;
}
```

---

## 📝 Checklist de Validation

Avant de commiter du code manipulant des coordonnées :

- [ ] Je n'ai PAS multiplié `secteur_x/y/z` par 10
- [ ] Je n'ai PAS divisé les coordonnées par 10
- [ ] J'utilise `secteur_x/y/z` directement pour l'affichage carte
- [ ] J'utilise `position_x/y/z` uniquement pour navigation précise
- [ ] Mes calculs de distance utilisent TOUTES les coordonnées (secteur + position)
- [ ] J'ai testé avec des systèmes ayant des positions décimales (ex: Vega Aurigae)

---

## 🐛 Debug

### Vérifier les Coordonnées d'un Système

```bash
php artisan tinker

$vega = App\Models\SystemeStellaire::where('nom', 'LIKE', '%Vega%')->first();
echo "Secteur: ({$vega->secteur_x}, {$vega->secteur_y}, {$vega->secteur_z})\n";
echo "Position: ({$vega->position_x}, {$vega->position_y}, {$vega->position_z})\n";
echo "Carte AL: ({$vega->secteur_x}, {$vega->secteur_y}, {$vega->secteur_z})\n";
```

### Système de Test

Utilisez **Vega Aurigae** pour tester :
- Secteur : (0, 0, 4)
- Position : (0.962, 0.779, 0.530)
- **Doit apparaître en (0, 0, 4) sur la carte**
- **PAS en (0, 0, 40) !**

---

**Document vivant - Dernière mise à jour : 2025-11-28**
