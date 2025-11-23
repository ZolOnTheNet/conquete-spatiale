# Système Économique - Marchés des Stations

## Vue d'ensemble

Le système économique de Conquête Spatiale est basé sur l'offre et la demande dynamique. Chaque station possède son propre marché avec des prix qui varient en fonction des stocks, de la production locale et de la consommation.

## Types de Produits

### Matières Premières (`matiere_premiere`)
Ressources naturelles brutes extraites des astéroïdes et planètes :
- **Minerai de Fer** (FER) : 50₡ base
- **Minerai de Cuivre** (CUIVRE) : 80₡ base
- **Silicium** (SI) : 120₡ base

### Matières Raffinées (`matiere_raffinee`)
Matières premières transformées :
- **Acier** (ACIER) : 150₡ base - Niveau tech 2
- **Aluminium** (ALU) : 180₡ base - Niveau tech 2

### Carburants (`carburant`)
Combustibles pour la propulsion :
- **Hydrogène** (H2) : 200₡ base
- **Deutérium** (D2) : 500₡ base - Niveau tech 3

### Composants (`composant`)
Pièces électroniques et mécaniques :
- **Circuit Électronique** (CIRCUIT) : 300₡ base - Niveau tech 3
- **Processeur Quantique** (QPROC) : 2000₡ base - Niveau tech 5

### Consommables (`consommable`)
Nourriture, eau, médicaments :
- **Eau** (H2O) : 10₡ base
- **Rations Alimentaires** (FOOD) : 15₡ base
- **Médicaments** (MED) : 100₡ base - Niveau tech 2

### Manufacturés (`manufacture`)
Objets fabriqués complexes :
- **Drone Minier** (DRONE) : 5000₡ base - Niveau tech 4
- **Pièces Détachées** (PARTS) : 250₡ base - Niveau tech 2

### Luxe (`luxe`)
Articles de luxe et rares :
- **Vin Terrien** (VIN) : 500₡ base

## Système de Prix Dynamiques

### Formule de Base

Les prix sont calculés dynamiquement selon plusieurs facteurs :

```
Prix Final = Prix Base × Modificateur Type Économique × Ajustement Stock
```

### Types Économiques

Chaque produit dans chaque station a un type économique déterminé automatiquement :

#### 1. **PRODUCTEUR** (Production > Consommation × 1.5)
- **Modificateur Vente** : 0.7 (-30%)
- **Modificateur Achat** : 0.4 (-60%)
- **Logique** : La station produit beaucoup, donc vend pas cher mais achète très peu cher
- **Indicateur** : ⬇ PROD

**Exemple** : Jupiter-spatiogare pour l'Hydrogène (H2)
- Production : 1000/jour
- Consommation : 50/jour
- → Prix vente : 140₡ au lieu de 200₡
- → Prix achat : 80₡ au lieu de 200₡

#### 2. **CONSOMMATEUR** (Consommation > Production × 1.5)
- **Modificateur Vente** : 1.8 (+80%)
- **Modificateur Achat** : 1.3 (+30%)
- **Logique** : La station consomme beaucoup, donc vend cher et achète cher
- **Indicateur** : ⬆ CONSO

**Exemple** : Terra-Maxi-Hub pour les Rations (FOOD)
- Production : 500/jour
- Consommation : 800/jour
- → Prix vente : 360₡ au lieu de 200₡
- → Prix achat : 260₡ au lieu de 200₡

#### 3. **ÉQUILIBRE** (Production ≈ Consommation)
- **Modificateur Vente** : 1.1 (+10%)
- **Modificateur Achat** : 0.8 (-20%)
- **Logique** : Production et consommation équilibrées, prix moyens
- **Indicateur** : → ÉQUIL

#### 4. **TRANSIT** (Ni production ni consommation)
- **Modificateur Vente** : 1.2 (+20%)
- **Modificateur Achat** : 0.7 (-30%)
- **Logique** : Simple point de commerce, marge standard
- **Indicateur** : TRANSIT

### Ajustement selon Stock

Le stock influence aussi les prix :

```
Ratio Stock = Stock Actuel / Stock Maximum

Ajustement = 1.0 + (0.5 - Ratio Stock) × 0.8
```

**Exemples** :
- **Stock plein (100%)** : Ratio = 1.0 → Ajustement = 0.6 (prix bas)
- **Stock moyen (50%)** : Ratio = 0.5 → Ajustement = 1.0 (prix normal)
- **Stock vide (0%)** : Ratio = 0.0 → Ajustement = 1.4 (prix élevé)

### Exemple Complet

**Terra-Maxi-Hub - Minerai de Fer**
- Type économique : CONSOMMATEUR (prod=50, conso=500)
- Prix base : 50₡
- Stock : 3000 / 10000 (30%)

**Calcul** :
1. Modificateur consommateur vente = 1.8
2. Ratio stock = 0.3
3. Ajustement stock = 1.0 + (0.5 - 0.3) × 0.8 = 1.16
4. **Prix vente** = 50 × 1.8 × 1.16 = **104₡**
5. Modificateur consommateur achat = 1.3
6. **Prix achat** = 50 × 1.3 × 1.16 = **75₡**

## Marchés par Station

### Terra-Maxi-Hub (Terre)
**Profil** : Hub commercial majeur, consommateur de ressources, producteur de biens manufacturés

| Type | Production | Consommation | Rôle |
|------|------------|--------------|------|
| Matières premières | Faible | Très élevée | CONSOMMATEUR ⬆ |
| Matières raffinées | Moyenne | Moyenne | ÉQUILIBRE → |
| Composants | Élevée | Faible | PRODUCTEUR ⬇ |
| Manufacturés | Très élevée | Faible | PRODUCTEUR ⬇ |
| Consommables | Élevée | Très élevée | CONSOMMATEUR ⬆ |
| Carburants | Faible | Élevée | CONSOMMATEUR ⬆ |
| Luxe | Faible | Élevée | CONSOMMATEUR ⬆ |

**Stratégie pour les joueurs** :
- ✅ **Vendre** : Matières premières, consommables (prix élevés)
- ✅ **Acheter** : Composants, manufacturés (prix bas)

### Mars-spatiogare (Mars)
**Profil** : Colonie minière, gros producteur de minerais

| Type | Production | Consommation | Rôle |
|------|------------|--------------|------|
| Matières premières | Très élevée | Faible | PRODUCTEUR ⬇ |
| Matières raffinées | Élevée | Faible | PRODUCTEUR ⬇ |
| Consommables | Faible | Moyenne | CONSOMMATEUR ⬆ |
| Carburants | Faible | Moyenne | ÉQUILIBRE → |

**Stratégie** :
- ✅ **Acheter** : Minerais (prix très bas)
- ✅ **Vendre** : Consommables, carburants

### Jupiter-spatiogare (Jupiter)
**Profil** : Station d'extraction de gaz, producteur majeur de carburants

| Type | Production | Consommation | Rôle |
|------|------------|--------------|------|
| Carburants | Très élevée | Faible | PRODUCTEUR ⬇ |
| Matières premières | Moyenne | Faible | PRODUCTEUR ⬇ |
| Consommables | Faible | Moyenne | CONSOMMATEUR ⬆ |

**Stratégie** :
- ✅ **Acheter** : Hydrogène, Deutérium (prix très bas)
- ✅ **Vendre** : Nourriture, eau

### Neptune-spatiogare (Neptune)
**Profil** : Station industrielle, raffinage et manufacture

| Type | Production | Consommation | Rôle |
|------|------------|--------------|------|
| Matières raffinées | Très élevée | Faible | PRODUCTEUR ⬇ |
| Carburants | Élevée | Faible | PRODUCTEUR ⬇ |
| Manufacturés | Moyenne | Faible | PRODUCTEUR ⬇ |

**Stratégie** :
- ✅ **Acheter** : Acier, aluminium, drones (prix bas)
- ✅ **Vendre** : Matières premières brutes

### Lunastar-station (Lune)
**Profil** : Station de départ, équilibrée pour débutants

| Type | Production | Consommation | Rôle |
|------|------------|--------------|------|
| Tous produits | Moyenne | Moyenne | ÉQUILIBRE → |

**Stratégie** :
- Bons prix pour apprendre le commerce
- Pas d'extrêmes de prix

## Routes Commerciales Rentables

### Route 1 : Mars → Terre
1. **Acheter à Mars** : Minerai de Fer (35₡ producteur)
2. **Vendre à Terre** : Minerai de Fer (104₡ consommateur)
3. **Profit** : +69₡ par unité (+197%)

### Route 2 : Jupiter → Terre
1. **Acheter à Jupiter** : Hydrogène (140₡ producteur)
2. **Vendre à Terre** : Hydrogène (432₡ consommateur)
3. **Profit** : +292₡ par unité (+209%)

### Route 3 : Terre → Mars
1. **Acheter à Terre** : Rations Alimentaires (21₡ producteur manufacturé)
2. **Vendre à Mars** : Rations Alimentaires (39₡ consommateur)
3. **Profit** : +18₡ par unité (+86%)

### Route Triangulaire Optimale
1. **Mars → Terre** : Minerais (+197%)
2. **Terre → Neptune** : Circuits électroniques (+120%)
3. **Neptune → Mars** : Pièces détachées (+80%)

## Simulation Temporelle

Le marché évolue dans le temps :

### Production/Consommation Quotidienne

Chaque jour simulé :
```php
Stock Actuel += Production par jour
Stock Actuel -= Consommation par jour
```

**Limites** :
- Stock ne peut pas descendre sous 0
- Stock ne peut pas dépasser Stock Maximum

### Recalcul Automatique des Prix

Après chaque changement de stock (production/consommation ou transaction joueur), les prix sont automatiquement recalculés.

**Impact des transactions joueurs** :
- Joueur **achète** 1000 unités → Stock station baisse → Prix monte
- Joueur **vend** 1000 unités → Stock station monte → Prix baisse

## Commandes

### `marche`
Affiche le marché de la station actuelle

**Prérequis** :
- Être dans une station (`transborder`)
- Station doit avoir `commerciale = true`

**Affichage** :
```
=== MARCHÉ DE Terra-Maxi-Hub ===

Code       | Produit              | Type        | Achat    | Vente    | Stock    | Éco
-----------|----------------------|-------------|----------|----------|----------|-------------
FER        | Minerai de Fer       | matiere_... | 75₡      | 104₡     | 3,000    | CONSO ⬆
H2         | Hydrogène            | carburant   | 260₡     | 432₡     | 1,200    | CONSO ⬆
CIRCUIT    | Circuit Électronique | composant   | 180₡     | 252₡     | 8,500    | PROD ⬇

💰 Achat = Station achète AU joueur | Vente = Station vend AU joueur
⬇ PROD = Prix bas | ⬆ CONSO = Prix élevé | → ÉQUIL = Prix moyen
```

### `acheter <code> <quantité>`
Acheter un produit à la station (station VEND au joueur)

**Exemple** :
```
> acheter FER 500

=== ACHAT EFFECTUÉ ===
Station: Terra-Maxi-Hub
Produit: Minerai de Fer (FER)
Quantité: 500 unités
Prix unitaire: 104.00₡
Prix total: 52,000.00₡
Nouveau stock station: 2,500
Type économique: consommateur

💡 Le prix a été ajusté selon l'offre et la demande.
```

**Vérifications** :
- Stock station suffisant
- Crédits joueur suffisants (TODO)
- Capacité cargo vaisseau (TODO)

### `vendre <code> <quantité>`
Vendre un produit à la station (station ACHÈTE au joueur)

**Exemple** :
```
> vendre FER 500

=== VENTE EFFECTUÉE ===
Station: Terra-Maxi-Hub
Produit: Minerai de Fer (FER)
Quantité: 500 unités
Prix unitaire: 75.00₡
Prix total: 37,500.00₡
Nouveau stock station: 3,500
Type économique: consommateur

💡 Le prix a été ajusté selon l'offre et la demande.
```

**Vérifications** :
- Inventaire joueur suffisant (TODO)
- Capacité stockage station suffisante

## Piraterie et Transport (Futur)

### Transport de Marchandises NPC

Le système simule des transports NPC entre stations pour équilibrer les marchés :

- **Convois commerciaux** : Transportent marchandises entre stations
- **Fréquence** : Basée sur les déséquilibres offre/demande
- **Routes** : Automatiquement calculées pour maximiser profits

### Opportunités de Piraterie

- **Intercepter convois** : Voler marchandises en transit
- **Risques** : Réputation, forces de sécurité
- **Récompenses** : Marchandises revendables au marché noir

### Marché Noir

- **Produits illégaux** : `illegal = true`
- **Prix majorés** : +50% à +200%
- **Disponibilité limitée** : Certaines stations seulement
- **Risques** : Contrôles douaniers, confiscation

## Base de Données

### Table `produits`
```sql
id, nom, code, type, description, volume_unite, masse_unite,
prix_base, illegal, niveau_technologique
```

### Table `marche_stations`
```sql
id, station_id, produit_id,
stock_actuel, stock_min, stock_max,
production_par_jour, consommation_par_jour,
type_economique,
prix_achat_joueur, prix_vente_joueur,
derniere_mise_a_jour_prix,
disponible_achat, disponible_vente
```

## Formules Clés

### Détermination Type Économique
```php
if (production == 0 && consommation == 0) → TRANSIT
else if (production > consommation × 1.5) → PRODUCTEUR
else if (consommation > production × 1.5) → CONSOMMATEUR
else → ÉQUILIBRE
```

### Calcul Prix Vente
```php
modif = match(type_economique) {
    PRODUCTEUR => 0.7,
    CONSOMMATEUR => 1.8,
    EQUILIBRE => 1.1,
    TRANSIT => 1.2,
};

ratio_stock = stock_actuel / stock_max;
ajust_stock = 1.0 + (0.5 - ratio_stock) × 0.8;

prix_vente = prix_base × modif × ajust_stock;
```

### Calcul Prix Achat
```php
modif = match(type_economique) {
    PRODUCTEUR => 0.4,
    CONSOMMATEUR => 1.3,
    EQUILIBRE => 0.8,
    TRANSIT => 0.7,
};

prix_achat = prix_base × modif × ajust_stock;

// S'assurer que station fait une marge
if (prix_achat >= prix_vente) {
    prix_achat = prix_vente × 0.7;
}
```

## Équilibrage

### Prix Base Recommandés

- **Matières premières** : 50-150₡
- **Matières raffinées** : 150-300₡
- **Carburants** : 200-500₡
- **Composants** : 300-2000₡
- **Consommables** : 10-100₡
- **Manufacturés** : 250-5000₡
- **Luxe** : 500-2000₡

### Production/Consommation

- **Faible** : 50-100/jour
- **Moyenne** : 100-300/jour
- **Élevée** : 300-600/jour
- **Très élevée** : 600-1000/jour

### Stocks Recommandés

- **Stock minimum** : 10% du stock max
- **Stock maximum** : 5000-20000 selon importance
- **Stock initial** : 30-90% du stock max

## TODO Technique

- [ ] Implémenter système de crédits pour personnages
- [ ] Implémenter cargo/inventaire pour vaisseaux
- [ ] Simulation temporelle automatique (cron/jobs)
- [ ] Transport NPC entre stations
- [ ] Marché noir et produits illégaux
- [ ] Interface graphique des marchés
- [ ] Graphiques d'évolution des prix
- [ ] Alertes de prix (notifications)
- [ ] Contrats de transport
- [ ] Système de réputation marchand
