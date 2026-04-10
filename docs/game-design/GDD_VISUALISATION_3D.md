# GDD - Visualisation 3D Spatiale

**Date:** 31 décembre 2025
**Statut:** Étude de faisabilité
**Version:** 1.0

---

## 📊 Vue d'Ensemble

Ce document étudie la faisabilité et les options techniques pour ajouter une vue 3D interactive au système de visualisation spatiale du jeu. L'objectif est d'améliorer l'immersion et la compréhension des positions et trajectoires dans l'espace tridimensionnel.

---

## 1. État Actuel du Système

### 1.1 Visualisations Existantes

**Canvas 2D - Carte Univers** (`resources/views/game/carte.blade.php`)
- Grille secteurs : 21 AL (joueur) ou 100 AL (admin)
- Fond étoilé procédural déterministe (seed basé sur coordonnées)
- Affichage : systèmes découverts, grille de référence, position joueur
- Taille : 600×600 px (joueur), 1000×1000 px (admin)

**SVG 2D - Détail Secteur** (`resources/views/game/carte-secteur.blade.php`)
- Vue intra-secteur (10 AL³)
- Orbites planétaires avec zoom interactif (0.5x à 10x)
- Animation : pulsation étoiles, positions dynamiques

**JavaScript - Calculs Orbitaux** (`public/js/orbital-calculator.js`)
- Classe statique pour calculs temps réel côté client
- Formules : positions orbitales, distances, animations
- Cache de positions pour optimisation

### 1.2 Architecture Spatiale

```
Univers
├── Secteurs (AL entiers) → secteur_x/y/z
│   └── Systèmes stellaires (grille 3D)
└── Intra-secteur (10 AL³)
    ├── Positions (cUA) → position_x/y/z
    └── Orbites → distance, angle, vitesse_angulaire
```

**Conversions :**
- 1 UA = 100 cUA (centi-Unités Astronomiques)
- 1 AL = 63,241 UA = 6,324,100 cUA

---

## 2. Richesse des Données Disponibles ✅

Le système dispose de **toutes les données nécessaires** pour une visualisation 3D :

| Données | Disponibilité | Utilisation 3D |
|---------|--------------|----------------|
| Positions 3D systèmes | ✅ `secteur_x/y/z` | Placement étoiles |
| Positions 3D planètes | ✅ `cache_position_x/y/z` | Placement planètes |
| Paramètres orbitaux | ✅ `distance_etoile`, `angle_orbital_initial`, `vitesse_angulaire` | Trajectoires orbitales |
| Données stellaires | ✅ `type_etoile`, `temperature`, `rayon_solaire` | Rendu étoiles (couleur, taille) |
| Données planétaires | ✅ `rayon_km`, `masse` | Rendu planètes (échelle) |
| Positions vaisseaux | ✅ `position_x/y/z` + orbite optionnelle | Objets mobiles |
| Orientation | ✅ `azimut` (0-360°) | Rotation objets |

---

## 3. Options Techniques

### 3.1 Spacekit.js ⭐ RECOMMANDÉ

**Description :** Bibliothèque JavaScript spécialisée pour visualisations spatiales et astronomiques.

**Avantages :**
- Support natif des **orbites kepleriennes**
- Gestion automatique des **échelles** (UA, AL)
- Rendu optimisé pour l'**astronomie**
- Exemples de systèmes solaires clé-en-main
- Code minimal pour résultats professionnels

**Inconvénients :**
- Moins flexible pour UI personnalisée
- Communauté plus petite que Three.js

**Code exemple :**
```javascript
const viz = new Spacekit.Simulation(document.getElementById('canvas'), {
  basePath: 'https://typpo.github.io/spacekit/src'
});

// Ajouter un système stellaire
viz.createObject('Sol', Spacekit.SpaceObjectPresets.SUN);

// Ajouter une planète avec orbite
viz.createSphere('Proxima b', {
  textureUrl: '/textures/planet.jpg',
  radius: 1.07, // Rayons terrestres
  orbit: {
    semiMajorAxis: 0.0485, // UA
    period: 11.2, // jours
    inclination: 0,
    longitudeOfAscendingNode: 0
  }
});
```

**Liens :**
- GitHub: https://github.com/typpo/spacekit
- Documentation: https://typpo.github.io/spacekit/

---

### 3.2 Three.js + Cannon.js

**Description :** Solution généraliste ultra-flexible pour graphismes 3D + physique.

**Avantages :**
- **Très flexible** et personnalisable
- Grande communauté, documentation riche
- Performance excellente
- Intégration facile avec le JS existant
- Contrôle total sur le rendu

**Inconvénients :**
- Plus de code à écrire pour les orbites
- Nécessite calculs manuels (déjà disponibles dans `orbital-calculator.js` ✅)

**Code exemple :**
```javascript
// Scene de base
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight);
const renderer = new THREE.WebGLRenderer();

// Étoile
const starGeometry = new THREE.SphereGeometry(10, 32, 32);
const starMaterial = new THREE.MeshBasicMaterial({ color: 0xffff00 });
const star = new THREE.Mesh(starGeometry, starMaterial);
scene.add(star);

// Planète avec orbite
const planetGeometry = new THREE.SphereGeometry(1, 32, 32);
const planet = new THREE.Mesh(planetGeometry, planetMaterial);

// Animation orbite (utilise orbital-calculator.js existant)
function animate() {
  const pos = OrbitalCalculator.calculerPositionOrbitale(planeteData, timestampJours);
  planet.position.set(pos.x / 100, pos.y / 100, pos.z / 100); // cUA → UA
  renderer.render(scene, camera);
  requestAnimationFrame(animate);
}
```

---

### 3.3 CesiumJS

**Description :** Plateforme avancée pour visualisations géospatiales et telemetry streaming.

**Avantages :**
- Visualisations **ultra-réalistes**
- Support natif de telemetry streaming
- Parfait pour satellites et trajectoires temps réel
- Outils professionnels intégrés

**Inconvénients :**
- **Très lourd** (plusieurs MB)
- Courbe d'apprentissage raide
- Peut-être excessif pour le besoin actuel
- Complexité de configuration

**Lien :** https://cesium.com/platform/cesiumjs/

---

## 4. Scénarios d'Usage

### 4.1 Vue Secteur 3D (10 AL³)

```
Caméra libre (WASD + souris)
├── Étoile au centre (sphere jaune pulsante)
├── Planètes en orbite (trajectoires animées)
├── Vaisseaux (modèles 3D ou icônes)
└── Grille de référence 3D (plans XY, XZ, YZ)

Échelle : 1 unité 3D = 1 UA
```

**Fonctionnalités :**
- Rotation caméra libre (orbite autour du système)
- Zoom continu
- Orbites visibles (cercles/ellipses transparents)
- Animation temps accéléré (×10, ×100, ×1000)
- Labels planètes (sprites 2D billboard)

---

### 4.2 Vue Univers 3D (21 AL³ ou 100 AL³)

```
Caméra orbitale
├── Systèmes stellaires (étoiles colorées selon type)
├── Secteurs visibles (cubes wireframe)
├── Trajectoire vaisseau (trail de particules)
└── Minimap 2D en overlay

Échelle : 1 unité 3D = 1 AL
```

**Fonctionnalités :**
- Vue galaxie avec milliers d'étoiles
- Filtrage par type spectral (O, B, A, F, G, K, M)
- Sélection système → zoom progressif vers vue secteur
- Trajectoires hyperspatiales (splines 3D)

---

### 4.3 Vue Orbite Planète (zoom rapproché)

```
Caméra first-person (cockpit vaisseau)
├── Planète (texture, nuages, atmosphère)
├── Lunes (orbites)
├── Stations (modèles 3D)
└── Autres vaisseaux

Échelle : 1 unité 3D = 100 km
```

**Fonctionnalités :**
- Atmosphère planétaire (shader glow)
- Rotation planète (période de rotation)
- Lens flare étoile
- Anneaux planétaires (si applicable)

---

## 5. Intégration avec l'Existant

### 5.1 Approche Incrémentale (Recommandée)

**Phase 1 : Cohabitation 2D/3D**

```html
<!-- Blade template (carte.blade.php) -->
<div class="row">
  <!-- Colonne gauche : Canvas 2D actuel -->
  <div class="col-md-6">
    <canvas id="carte2d"></canvas>
    <button id="toggle3d" class="btn btn-primary">
      <i class="fas fa-cube"></i> Passer en 3D
    </button>
  </div>

  <!-- Colonne droite : Canvas 3D (nouveau) -->
  <div class="col-md-6">
    <div id="carte3d" style="display:none; height:600px;"></div>
  </div>
</div>
```

**Phase 2 : Toggle avec préférence utilisateur**

```javascript
// Sauvegarde préférence dans localStorage
const mode = localStorage.getItem('carteMode') || '2d';

// Initialisation selon préférence
if (mode === '3d') {
  initVue3D(planetes, systeme);
  document.getElementById('carte3d').style.display = 'block';
  document.getElementById('carte2d').style.display = 'none';
}

// Toggle
document.getElementById('toggle3d').addEventListener('click', () => {
  const newMode = mode === '2d' ? '3d' : '2d';
  localStorage.setItem('carteMode', newMode);
  location.reload(); // Ou basculement dynamique
});
```

**Phase 3 : Vue 3D par défaut**

Une fois stabilisée, la vue 3D devient le mode principal avec option de retour 2D pour compatibilité.

---

### 5.2 Réutilisation de orbital-calculator.js

**Le code de calcul orbital est déjà prêt !**

```javascript
// Dans la vue 3D, utiliser les fonctions existantes
const planetes = @json($systeme->planetes);
const systeme = @json($systeme);
const vaisseau = @json($personnage->vaisseau);

// Calculer positions pour rendu 3D
planetes.forEach(planete => {
  const pos = OrbitalCalculator.calculerPositionOrbitale(
    planete,
    timestampJours
  );

  // Positionner l'objet 3D
  planetMesh.position.set(
    pos.x / 100,  // cUA → UA
    pos.y / 100,
    pos.z / 100
  );
});

// Animation temps réel
function animate() {
  const dateJeu = new Date(dateJeuActuelle);
  const timestampJours = OrbitalCalculator.dateToJours(dateJeu);

  // Mettre à jour toutes les positions
  OrbitalCalculator.mettreAJourPositions(planetes, systeme, vaisseau, dateJeu);

  renderer.render(scene, camera);
  requestAnimationFrame(animate);
}
```

---

## 6. Fonctionnalités 3D Envisageables

### 6.1 Niveau 1 - MVP (Minimum Viable Product)

**Essentielles :**
- ✅ Rotation caméra (orbite autour du système)
- ✅ Zoom libre (molette souris)
- ✅ Orbites visibles (cercles/ellipses)
- ✅ Animation temps accéléré (contrôles ×1, ×10, ×100, ×1000)
- ✅ Labels planètes (sprites 2D toujours face caméra)
- ✅ Sphères colorées pour planètes (couleur selon type)
- ✅ Étoile au centre (sphere jaune avec emission)

**Estimation :** 11-15 heures avec Spacekit.js

---

### 6.2 Niveau 2 - Enrichissement

**Visuelles :**
- ✅ Textures planètes (procédurales ou images)
- ✅ Grille de référence 3D (plans XY, XZ, YZ avec opacité)
- ✅ Skybox étoilé (cube map avec fond étoilé)
- ✅ Éclairage réaliste (ombres planètes)
- ✅ Lens flare étoiles (effet optique)

**Gameplay :**
- ✅ Vaisseaux 3D (modèles simples ou icônes)
- ✅ Sphères de détection (wireframe radius selon `detectabilite_base`)
- ✅ Trajectoires vaisseaux (splines/courbes)
- ✅ Stations spatiales (cubes ou modèles)

**Estimation :** +8-10 heures

---

### 6.3 Niveau 3 - Avancé

**Effets :**
- ✅ Anneaux planétaires (géométrie anneau + texture)
- ✅ Atmosphère (shader glow autour planètes)
- ✅ Trails de particules (moteurs vaisseaux)
- ✅ Explosions/Effets combat (particules)

**Technique :**
- ✅ LOD (Level of Detail) : Planètes lointaines = spheres simples
- ✅ Frustum culling : Ne rendre que ce qui est visible
- ✅ Instancing : Réutiliser géométries pour étoiles similaires
- ✅ Web Workers : Calculs orbitaux en arrière-plan

**Immersion :**
- ✅ VR/XR support (WebXR API)
- ✅ Audio spatial (sons moteurs selon distance)
- ✅ Cockpit view (first-person depuis vaisseau)

**Estimation :** +12-20 heures

---

## 7. Performance et Optimisation

### 7.1 Données à Transférer

**Pour 1 système avec 10 planètes :**

```javascript
// JSON minimal côté client
{
  systeme: {
    position_x: 0,
    position_y: 0,
    position_z: 0,
    rayon_solaire: 1.0,
    type_etoile: "G",
    temperature: 5778
  },
  planetes: [
    {
      nom: "Proxima b",
      distance_etoile: 485,        // cUA
      angle_orbital_initial: 0.5,  // radians
      vitesse_angulaire: 0.001,    // rad/jour
      cache_position_x: 100,       // cUA
      cache_position_y: 50,        // cUA
      cache_position_z: 0,         // cUA
      rayon_km: 7160,
      type: "tellurique"
    }
    // ... × 10
  ]
}

// Total : ~2 KB JSON
```

**Pour vue univers (1000 systèmes visibles) :**
- Positions uniquement (secteur_x/y/z) : ~30 KB
- Acceptable pour chargement initial

---

### 7.2 Techniques d'Optimisation

**Level of Detail (LOD) :**
```javascript
// Distance caméra → planète
const distance = camera.position.distanceTo(planet.position);

if (distance < 50) {
  planet.geometry = highDetailSphere; // 64 segments
} else if (distance < 200) {
  planet.geometry = mediumDetailSphere; // 32 segments
} else {
  planet.geometry = lowDetailSphere; // 8 segments
}
```

**Frustum Culling (automatique Three.js) :**
- Ne rend que les objets dans le champ de vision
- Gain énorme pour vue univers (1000+ étoiles)

**Instancing (pour étoiles similaires) :**
```javascript
// Réutiliser une seule géométrie pour toutes les étoiles de type G
const starInstancedMesh = new THREE.InstancedMesh(
  starGeometry,
  starMaterial,
  countStarsTypeG
);

// Position de chaque instance
for (let i = 0; i < countStarsTypeG; i++) {
  matrix.setPosition(positions[i].x, positions[i].y, positions[i].z);
  starInstancedMesh.setMatrixAt(i, matrix);
}
```

**Web Workers (calculs lourds) :**
```javascript
// Dans worker.js
self.onmessage = function(e) {
  const positions = calculateAllOrbitalPositions(e.data.planetes, e.data.timestamp);
  self.postMessage({ positions });
};

// Dans main.js
worker.postMessage({ planetes, timestamp });
worker.onmessage = function(e) {
  updatePlanetPositions(e.data.positions);
};
```

---

## 8. Complexité d'Implémentation

| Fonctionnalité | Spacekit.js | Three.js | CesiumJS |
|----------------|-------------|----------|----------|
| Setup initial | 🟢 2h | 🟡 4h | 🔴 8h |
| Orbites basiques | 🟢 1h | 🟡 4h | 🟢 2h |
| UI/Controls | 🟡 3h | 🟢 2h | 🔴 6h |
| Animation temps | 🟢 1h | 🟡 3h | 🟢 2h |
| Textures/Shaders | 🟡 4h | 🟢 3h | 🟢 2h |
| **Total MVP** | **~11h** | **~16h** | **~20h** |

**Légende :**
- 🟢 Facile/Rapide
- 🟡 Moyen
- 🔴 Difficile/Long

---

## 9. Recommandations Finales

### 9.1 Approche Progressive Recommandée

**Phase 1 : MVP - Vue Secteur 3D** (11-15h)
```
✅ Spacekit.js pour vue secteur 3D
├── Système stellaire au centre
├── Planètes en orbite (sphères colorées)
├── Trajectoires orbitales (cercles)
├── Animation temps accéléré
├── Labels informatifs
└── Toggle 2D/3D
```

**Phase 2 : Enrichissement** (+8-10h)
```
✅ Three.js pour customisation avancée
├── Textures planètes procédurales
├── Vaisseaux 3D (modèles ou icônes)
├── Grille de référence 3D
├── Sphères de détection
├── Skybox étoilé
└── Éclairage réaliste
```

**Phase 3 : Vue Univers** (+12-15h)
```
✅ Vue galaxie (100 AL³)
├── Systèmes stellaires (points lumineux)
├── Secteurs (cubes wireframe)
├── Trajectoire hyperspatiale
├── Minimap intégrée
└── Filtres par type spectral
```

---

### 9.2 Choix Technique Final

**Recommandation : Spacekit.js**

**Raisons :**
1. ✅ **Spécialisé pour l'astronomie** → gains de temps énormes
2. ✅ **Données déjà parfaites** → positions 3D + paramètres orbitaux complets
3. ✅ **Rendu réaliste out-of-the-box** → qualité professionnelle immédiate
4. ✅ **Maintenance simple** → moins de code custom à maintenir
5. ✅ **Courbe d'apprentissage douce** → documentation claire avec exemples

**Alternative : Three.js** si besoin de :
- Plus de contrôle sur UI/UX custom
- Intégration poussée avec le design actuel
- Effets graphiques très spécifiques
- Apprentissage long terme (réutilisable sur d'autres projets)

---

## 10. Conclusion

### 10.1 Faisabilité

**FAISABILITÉ : 100% ✅**

Le projet dispose de :
- ✅ Toutes les données nécessaires (positions 3D, orbites)
- ✅ Architecture propre et structurée
- ✅ Calculs orbitaux fonctionnels (`orbital-calculator.js`)
- ✅ Conversion d'unités maîtrisée (CoordinatesHelper)
- ✅ Système de détection prêt (scores de détectabilité)

---

### 10.2 Bénéfices Attendus

**Gameplay :**
- 🎯 Meilleure immersion spatiale
- 🎯 Compréhension intuitive des positions 3D
- 🎯 Visualisation des trajectoires complexes
- 🎯 Anticipation des rencontres orbitales

**Différenciation :**
- 🎯 Innovation forte vs jeux textuels classiques
- 🎯 Modernité du jeu (technologies 3D)
- 🎯 Accessibilité pour nouveaux joueurs

---

### 10.3 Risques et Mitigation

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Performance faible | 🟡 Faible | 🔴 Élevé | LOD + Frustum culling + Instancing |
| Compatibilité navigateur | 🟢 Très faible | 🟡 Moyen | WebGL support 95%+ (fallback 2D) |
| Complexité maintenance | 🟡 Faible | 🟡 Moyen | Spacekit.js (code minimal) |
| Courbe apprentissage | 🟢 Très faible | 🟢 Faible | Documentation riche + exemples |

**Stratégie de mitigation globale :**
1. Commencer par Spacekit.js (MVP rapide)
2. Garder vue 2D comme fallback
3. Tests performance sur machines moyennes
4. Optimisations progressives selon besoins réels

---

### 10.4 Prochaines Étapes

**Si décision de lancer le développement :**

1. **Spike technique** (4h)
   - Installer Spacekit.js
   - Créer prototype minimal (1 système, 3 planètes)
   - Tester performance
   - Valider intégration avec données existantes

2. **MVP** (11-15h)
   - Implémenter vue secteur 3D complète
   - Toggle 2D/3D fonctionnel
   - Animation temps accéléré
   - Tests utilisateurs alpha

3. **Itérations** (selon feedback)
   - Enrichissements visuels
   - Vue univers
   - Optimisations

---

## Références

**Bibliothèques 3D :**
- [Spacekit - GitHub](https://github.com/typpo/spacekit)
- [Spacekit.js - Documentation](https://typpo.github.io/spacekit/)
- [Three.js - Documentation](https://threejs.org/)
- [CesiumJS - Platform](https://cesium.com/platform/cesiumjs/)

**Articles et Ressources :**
- [Top 5 JavaScript 3D Libraries in 2025](https://25scripts.com/tutorial/top-5-javascript-3d-libraries-in-2025/)
- [12 Best JavaScript Animation Libraries 2025](https://www.devkit.best/blog/mdx/javascript-animation-libraries-physics-engines-2025)
- [Orbital Mechanics - GitHub Topics](https://github.com/topics/orbital-mechanics?l=javascript)

**Code Interne :**
- `public/js/orbital-calculator.js` - Calculs orbitaux existants
- `app/Helpers/CoordinatesHelper.php` - Conversions d'unités
- `resources/views/game/carte.blade.php` - Vue 2D actuelle
- `resources/views/game/carte-secteur.blade.php` - Vue SVG secteur

---

**Document maintenu par :** Claude Code
**Dernière mise à jour :** 2025-12-31
