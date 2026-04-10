# Système de Navigation - Conquête Spatiale

## Table des Matières
1. [Jet de Navigation](#jet-de-navigation)
2. [Calcul du Score d'Erreur](#calcul-du-score-derreur)
3. [Calcul du Delta de Position](#calcul-du-delta-de-position)
4. [Application du Delta](#application-du-delta)
5. [Exemples Complets](#exemples-complets)

---

## Jet de Navigation

### Formule de Base
```
Jet = 2d12 + Intelligence + Navigation + Ordinateur + Module
```

### Règles Spéciales
- **Critique** : Si les 2 dés sont égaux → Résultat substitué à **35** (pas 24)
- **Espoir** : Si premier dé > second dé → **+1 Hope**
- **Peur** : Si premier dé < second dé → **+1 Fear**
- **Plafond** : Maximum **49** (même avec un critique)

### Calcul Détaillé
1. Lancer 2d12 → `dé1` et `dé2`
2. Calculer le jet de base : `dé1 + dé2 + Intelligence + Navigation + Ordinateur + Module`
3. Vérifier les conditions spéciales :
   - Si `dé1 == dé2` → **Critique** (jet = 35)
   - Sinon si `dé1 > dé2` → **Espoir** (+1 Hope)
   - Sinon si `dé1 < dé2` → **Peur** (+1 Fear)
4. Appliquer le plafond : `min(49, jet)`

---

## Calcul du Score d'Erreur

### Formule
```
Score d'Erreur = 50 - Jet de Navigation
```

### Interprétation
- **Score 0** : Précision parfaite (100%)
- **Score 25** : Précision moyenne (87.5%)
- **Score 48** : Précision minimale (2%)

### Précision
```
Précision = 100 - (Score d'Erreur × 0.5)%
```

---

## Calcul du Delta de Position

### Nouvelle Formule (Corrigée)

Le score d'erreur représente le **pourcentage de décalage moyen** par rapport à la position cible.

#### Pour les Planètes/POI
```
Delta X = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 100) × Distance Planète-Étoile
Delta Y = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 100) × Distance Planète-Étoile
Delta Z = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 200) × Distance Planète-Étoile
```

#### Pour les Systèmes (cible <système>)
```
Distance de référence = 100 Gm (point sûr: 100,100,0)
Delta X = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 100) × 100
Delta Y = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 100) × 100
Delta Z = (3d10 - 15 + 1d2_signé) × (Score d'Erreur / 200) × 100
```

### Décomposition
1. **3d10 - 15** : Donne un résultat entre -15 et +15 (moyenne 0)
2. **1d2_signé** : 
   - 1 → -1
   - 2 → +1
3. **Ajouter le score d'erreur** directement
4. **Diviser par 100** pour obtenir un multiplicateur
5. **Application** à la distance de référence

### Plage de Valeurs
- **Minimum** : (-15-1+0)/100 × distance = -16% × distance
- **Maximum** : (15+1+48)/100 × distance = 64% × distance
- **Pour score=48** : (-15-1+48)/100 = 32% à (15+1+48)/100 = 64% de la distance de référence

---

## Application du Delta

### Position Finale
```
Position Finale X = Position Cible X + Delta X
Position Finale Y = Position Cible Y + Delta Y
Position Finale Z = Position Cible Z + Delta Z
```

### Limites
- Les coordonnées sont limitées à ±100 pour éviter les valeurs extrêmes
- La précision en Z est divisée par 2 (moins précise en altitude)

---

## Exemples Complets

### Exemple 1: Critique vers Terre
- **Jet** : 7+7 → Critique = 35
- **Score d'Erreur** : 50 - 35 = 15
- **Précision** : 100 - (15 × 0.5) = 92.5%
- **Distance Planète-Étoile** : 149.6 Gm
- **Delta X** : ((3d10-15) + 1d2 + 15) / 100 × 149.6
  - Supposons 3d10=15, 1d2=1 → (0 + (-1) + 15) / 100 × 149.6 = 14/100 × 149.6 = +20.94 Gm
- **Position Finale** : Position Terre ± (+20.94, deltaY, deltaZ/2)

### Exemple 2: Espoir vers Mars
- **Jet** : 10+5+5+3+2+1 = 26 (Espoir car 10>5)
- **Score d'Erreur** : 50 - 26 = 24
- **Précision** : 100 - (24 × 0.5) = 88%
- **Distance Planète-Étoile** : 227.9 Gm
- **Delta X** : ((3d10-15) + 1d2 + 24) / 100 × 227.9
  - Supposons 3d10=18, 1d2=2 → (3 + 1 + 24) / 100 × 227.9 = 28/100 × 227.9 = +63.81 Gm
- **Position Finale** : Position Mars ± (+63.81, deltaY, deltaZ/2)

### Exemple 3: Peur vers Soleil (<système>)
- **Jet** : 3+9+5+3+2+1 = 23 (Peur car 3<9)
- **Score d'Erreur** : 50 - 23 = 27
- **Précision** : 100 - (27 × 0.5) = 86.5%
- **Distance de référence** : 100 Gm
- **Delta X** : ((3d10-15) + 1d2 + 27) / 100 × 100
  - Supposons 3d10=6, 1d2=1 → (-9 + (-1) + 27) / 100 × 100 = 17 Gm
- **Position Finale** : (100,100,0) ± (+17, deltaY, deltaZ/2)

---

## Implémentation Technique

### PHP (Laravel)
```php
// Calcul du delta
protected function calculerDelta($scoreErreur, $distanceReference, $pourSystème = false) {
    // Lancer 3d10-15
    $d10_1 = rand(1, 10);
    $d10_2 = rand(1, 10);
    $d10_3 = rand(1, 10);
    $sommeD10 = $d10_1 + $d10_2 + $d10_3 - 15;
    
    // Lancer 1d2 signé
    $d2 = rand(1, 2);
    $d2Signé = $d2 == 1 ? -1 : 1;
    
    // Calculer le multiplicateur
    $multiplicateur = ($sommeD10 + $d2Signé) / 100;
    
    // Pour Z, diviser par 2
    $multiplicateurZ = $multiplicateur / 2;
    
    return [
        'x' => $multiplicateur * $scoreErreur * $distanceReference,
        'y' => $multiplicateur * $scoreErreur * $distanceReference,
        'z' => $multiplicateurZ * $scoreErreur * $distanceReference,
        'details' => [
            'd10' => [$d10_1, $d10_2, $d10_3],
            'd2' => $d2,
            'sommeD10' => $sommeD10,
            'd2Signé' => $d2Signé,
            'multiplicateur' => $multiplicateur,
        ]
    ];
}
```

### JavaScript (Affichage)
```javascript
// Affichage des détails du delta
function afficherDetailsDelta(deltaDetails) {
    console.log(`Delta calculé:`);
    console.log(`- 3d10: [${deltaDetails.d10.join('+')}]=${deltaDetails.sommeD10+15}`);
    console.log(`- 1d2: ${deltaDetails.d2} (${deltaDetails.d2Signé > 0 ? '+' : ''}${deltaDetails.d2Signé})`);
    console.log(`- Multiplicateur: ${deltaDetails.multiplicateur}`);
    console.log(`- Delta X/Y: ${deltaDetails.x.toFixed(2)} Gm`);
    console.log(`- Delta Z: ${deltaDetails.z.toFixed(2)} Gm`);
}
```

---

## Notes de Design

1. **Réalisme** : Plus le score d'erreur est élevé, plus l'imprécision est grande
2. **Stratégie** : Les joueurs peuvent améliorer leur jet pour réduire l'imprécision
3. **Aléatoire** : Même avec un bon jet, il reste une part de hasard
4. **Équilibrage** : Le système permet des arrivées précises ou des erreurs spectaculaires

---

## Historique des Modifications

- **v1.0** : Système initial avec jet simple
- **v1.1** : Ajout des critiques (35 au lieu de 24)
- **v1.2** : Ajout de l'espoir/peur
- **v1.3** : Correction du calcul du delta (pourcentage basé)
- **v1.4** : Ajout des détails complets des dés

---

## TODO / Améliorations Futures

- [ ] Implémenter la gestion complète des points Hope/Fear
- [ ] Ajouter des modificateurs basés sur la distance
- [ ] Implémenter des événements spéciaux (tempêtes ioniques, etc.)
- [ ] Ajouter des compétences de navigation avancées
- [ ] Implémenter un système de cartographie progressive
