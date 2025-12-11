# 🪐 SYSTÈME ORBITAL DES PLANÈTES

## Vue d'ensemble

Les planètes tournent autour de leur étoile selon une orbite circulaire simplifiée. Le calcul de position est optimisé pour être effectué **côté client (JavaScript)** afin d'alléger le serveur et permettre un affichage temps réel.

---

## 📊 Données stockées en base de données

### Table `planetes` - Colonnes orbitales

| Colonne | Type | Description |
|---------|------|-------------|
| `distance_etoile` | DECIMAL | Distance à l'étoile en UA |
| `periode_orbitale` | INT | Période orbitale en jours terrestres (Loi de Kepler) |
| `angle_orbital_initial` | DOUBLE | Angle de départ en **radians** (0 à 2π) |
| `vitesse_angulaire` | DOUBLE | Vitesse angulaire en **rad/jour** (précalculé = 2π/période) |
| `cache_position_x` | DECIMAL | Position X en UA (cache serveur) |
| `cache_position_y` | DECIMAL | Position Y en UA (cache serveur) |
| `cache_position_z` | DECIMAL | Position Z en UA (cache serveur) |
| `cache_timestamp_jours` | BIGINT | Timestamp en jours depuis 3000-01-01 (cache) |
| `cache_validite_jours` | INT | Durée de validité du cache en jours |

### ⚠️ Important : Stockage en radians

**Les angles sont stockés en RADIANS, pas en degrés** pour éviter les conversions répétées lors des calculs.

---

## 🧮 Formules astronomiques

### 1. Période orbitale (Loi de Kepler - 3ème loi)

```
T = 365.25 × sqrt(d³)
```

Où :
- `T` = période orbitale en jours terrestres
- `d` = distance à l'étoile en UA

**Exemples réels :**
- Terre (1 UA) : 365.25 jours
- Mars (1.52 UA) : 684 jours
- Jupiter (5.2 UA) : 4333 jours

### 2. Vitesse angulaire

```
ω = 2π / T
```

Où :
- `ω` = vitesse angulaire en rad/jour
- `T` = période orbitale en jours

**Pré-calculée et stockée** dans `vitesse_angulaire` pour optimiser les performances.

### 3. Position orbitale à un instant t

```
θ(t) = θ₀ + (ω × temps_écoulé)

x = d × cos(θ(t))
y = d × sin(θ(t))
z = 0  // Orbite plane simplifiée
```

Où :
- `θ₀` = angle initial (stocké dans `angle_orbital_initial`)
- `ω` = vitesse angulaire (stockée dans `vitesse_angulaire`)
- `temps_écoulé` = nombre de jours depuis la référence (3000-01-01)
- `d` = distance à l'étoile en UA

---

## 💻 Utilisation côté serveur (PHP)

### Obtenir les données orbitales d'une planète

```php
use App\Models\Planete;
use App\Helpers\GameTimeHelper;

$planete = Planete::find(1);
$personnage = auth()->user()->personnage;

// Obtenir les données optimisées pour JavaScript
$donneesOrbitales = $planete->getDonneesOrbitales($personnage);

// Retourne :
// [
//     'distance_etoile' => 1.0,
//     'periode_orbitale' => 365,
//     'angle_orbital_initial' => 0.1234,  // radians
//     'vitesse_angulaire' => 0.0172,      // rad/jour
//     'cache_position_x' => 0.9876,
//     'cache_position_y' => 0.1543,
//     'cache_position_z' => 0.0,
//     'cache_timestamp_jours' => 18,
//     'timestamp_actuel_jours' => 20,
//     'cache_validite_jours' => 10,
// ]
```

### Calculer la distance vaisseau ↔ planète

```php
$vaisseau = $personnage->vaisseauActif;
$distance_ua = $planete->getDistanceDepuisVaisseau($vaisseau);

echo "Distance : " . number_format($distance_ua, 2) . " UA";
```

---

## 🌐 Utilisation côté client (JavaScript)

### 1. Inclusion du module

```html
<script src="{{ asset('js/orbital-calculator.js') }}"></script>
```

### 2. Calculer la position d'une planète

```javascript
// Données depuis le serveur
const planete = {
    distance_etoile: 1.0,
    angle_orbital_initial: 0.1604,  // radians
    vitesse_angulaire: 0.01721,     // rad/jour
    cache_position_x: 0.9876,
    cache_position_y: 0.1543,
    cache_position_z: 0.0,
    cache_timestamp_jours: 18,
    cache_validite_jours: 10,
};

const systeme = {
    position_x: 0.0,  // AL
    position_y: 0.0,  // AL
    position_z: 0.0,  // AL
};

// Date actuelle du jeu (depuis serveur)
const dateJeu = new Date('3000-01-20T12:00:00Z');
const timestampJours = OrbitalCalculator.dateToJours(dateJeu);

// Calculer position orbitale (en UA par rapport à l'étoile)
const positionOrbitale = OrbitalCalculator.calculerPositionOrbitale(planete, timestampJours);
// Résultat : {x: 0.987, y: 0.154, z: 0, angle: 0.5432}

// Calculer position absolue (en AL dans l'espace)
const positionAbsolue = OrbitalCalculator.calculerPositionAbsolue(planete, systeme, timestampJours);
// Résultat : {x: 0.0000156, y: 0.0000024, z: 0, angle: 0.5432}
```

### 3. Calculer distance avec vaisseau

```javascript
const vaisseau = {
    position_x: 0.001,  // AL
    position_y: 0.002,  // AL
    position_z: 0.0,    // AL
};

const distance = OrbitalCalculator.calculerDistance(planete, systeme, vaisseau, timestampJours);
console.log(`Distance: ${distance.toFixed(2)} UA`);
```

### 4. Mettre à jour plusieurs planètes

```javascript
const planetes = [...]; // Liste de planètes depuis serveur
const planetesAvecPositions = OrbitalCalculator.mettreAJourPositions(
    planetes,
    systeme,
    vaisseau,
    dateJeu
);

// Afficher dans l'interface
planetesAvecPositions.forEach(p => {
    console.log(`${p.nom}: ${p.distance_ua.toFixed(2)} UA`);
});
```

---

## 🎨 Exemple : Affichage dans la timonerie

```javascript
// Dans resources/views/game/navire/timonerie.blade.php

const planetes = @json($poisSecteur);  // Depuis PHP
const systeme = @json($systemeActuel);
const vaisseau = {
    position_x: {{ $objetSpatial->position_x }},
    position_y: {{ $objetSpatial->position_y }},
    position_z: {{ $objetSpatial->position_z }},
};

// Date actuelle du jeu (depuis personnage)
const dateJeu = new Date('{{ $personnage->derniere_connexion }}');

// Calculer positions et distances
const planetesActualisees = OrbitalCalculator.mettreAJourPositions(
    planetes.filter(p => p.type_poi === 'planete'),
    systeme,
    vaisseau,
    dateJeu
);

// Afficher dans l'UI
planetesActualisees.forEach(planete => {
    const element = document.getElementById(`planete-${planete.id}`);
    if (element) {
        element.querySelector('.distance').textContent =
            `${planete.distance_ua.toFixed(2)} UA`;
    }
});
```

---

## 🔄 Système de cache intelligent

### Logique du cache

1. **Serveur** : Recalcule la position si cache expiré (`joursDepuisCache >= cache_validite_jours`)
2. **Client** : Utilise le cache si encore valide, sinon recalcule

### Durée de validité du cache

```php
// Formule : max(10, période/50) jours
$cache_validite_jours = max(10, (int)($periode_orbitale / 50));
```

**Exemples :**
- Terre (365j) : cache valide **7 jours**
- Jupiter (4333j) : cache valide **86 jours**
- Neptune (60317j) : cache valide **1206 jours**

Les planètes lentes ne nécessitent pas de recalculs fréquents !

---

## 🚀 Performance

### Optimisations implémentées

1. ✅ **Angles en radians** (pas de conversion deg↔rad)
2. ✅ **Vitesse angulaire précalculée** (pas de division répétée)
3. ✅ **Cache serveur intelligent** (évite recalculs inutiles)
4. ✅ **Calcul côté client** (décharge le serveur)
5. ✅ **Cache validité adaptative** (planètes lentes = cache long)

### Temps de calcul estimé

- **Calcul 1 planète** : ~0.01 ms (négligeable)
- **Calcul 100 planètes** : ~1 ms
- **Animation temps réel** : 60 FPS possible

---

## 📌 Points importants

### Découverte des planètes

**Les planètes doivent être découvertes !**

- Seules les planètes `poi_connu = true` sont visibles par défaut
- Pour le système Sol, les 5 planètes principales sont `poi_connu = true`
- Les autres planètes doivent être découvertes par scan (TODO: implémenter)

### Système temporel

Le temps du jeu est basé sur :
- **Date de référence** : 1er janvier 3000 à 00:00
- **1 PA** = 0.5 jour in-game
- Le temps n'avance que si PA épuisés (voir `SYSTEME_TEMPOREL.md`)

### Conversion d'unités

```
1 UA = 0.0000158 AL
1 AL = 63241 UA
```

---

## 🔧 TODO / Améliorations futures

- [ ] Ajouter inclinaison orbitale (angle par rapport au plan XY)
- [ ] Ajouter excentricité (orbites elliptiques)
- [ ] Implémenter découverte progressive des planètes par scan
- [ ] Animation visuelle des orbites sur la carte
- [ ] Précession des orbites (variation lente de l'angle)
- [ ] Influence gravitationnelle mutuelle (très complexe)

---

**Document vivant - Dernière mise à jour : 2025-12-11**
