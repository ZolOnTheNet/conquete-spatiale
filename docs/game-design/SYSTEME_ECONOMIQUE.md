# Système êconomique - Marchés des Stations

## Vue d'ensemble

Le système économique de Conquête Spatiale est basé sur l'offre et la demande dynamique. Chaque station possède son propre marché avec des prix qui varient en fonction des stocks, de la production locale et de la consommation.

## Types de Produits

### Matières Premières (`matiere_premiere`)
Ressources naturelles brutes extraites des astéroØdes et planètes :
- **Minerai de Fer** (FER) : 50œÇ° base
- **Minerai de Cuivre** (CUIVRE) : 80œÇ° base
- **Silicium** (SI) : 120œÇ° base

### Matières Raffinées (`matiere_raffinee`)
Matières premières transformées :
- **Acier** (ACIER) : 150œÇ° base - Niveau tech 2
- **Aluminium** (ALU) : 180œÇ° base - Niveau tech 2

### Carburants (`carburant`)
Combustibles pour la propulsion :
- **Hydrogène** (H2) : 200œÇ° base
- **Deutérium** (D2) : 500œÇ° base - Niveau tech 3

### Composants (`composant`)
Pièces électroniques et mécaniques :
- **Circuit électronique** (CIRCUIT) : 300œÇ° base - Niveau tech 3
- **Processeur Quantique** (QPROC) : 2000œÇ° base - Niveau tech 5

### Consommables (`consommable`)
Nourriture, eau, médicaments :
- **Eau** (H2O) : 10œÇ° base
- **Rations Alimentaires** (FOOD) : 15œÇ° base
- **Médicaments** (MED) : 100œÇ° base - Niveau tech 2

### Manufacturés (`manufacture`)
Objets fabriqués complexes :
- **Drone Minier** (DRONE) : 5000œÇ° base - Niveau tech 4
- **Pièces Détachées** (PARTS) : 250œÇ° base - Niveau tech 2

### Luxe (`luxe`)
Articles de luxe et rares :
- **Vin Terrien** (VIN) : 500œÇ° base

## Système de Prix Dynamiques

### Formule de Base

Les prix sont calculés dynamiquement selon plusieurs facteurs :

```
Prix Final = Prix Base ó Modificateur Type êconomique ó Ajustement Stock
```

### Types êconomiques

Chaque produit dans chaque station a un type économique déterminé automatiquement :

#### 1. **PRODUCTEUR** (Production > Consommation ó 1.5)
- **Modificateur Vente** : 0.7 (-30%)
- **Modificateur Achat** : 0.4 (-60%)
- **Logique** : La station produit beaucoup, donc vend pas cher mais achète très peu cher
- **Indicateur** : œ¨á PROD

**Exemple** : Jupiter-spatiogare pour l'Hydrogène (H2)
- Production : 1000/jour
- Consommation : 50/jour
- œÜí Prix vente : 140œÇ° au lieu de 200œÇ°
- œÜí Prix achat : 80œÇ° au lieu de 200œÇ°

#### 2. **CONSOMMATEUR** (Consommation > Production ó 1.5)
- **Modificateur Vente** : 1.8 (+80%)
- **Modificateur Achat** : 1.3 (+30%)
- **Logique** : La station consomme beaucoup, donc vend cher et achète cher
- **Indicateur** : œ¨Ü CONSO

**Exemple** : Terra-Maxi-Hub pour les Rations (FOOD)
- Production : 500/jour
- Consommation : 800/jour
- œÜí Prix vente : 360œÇ° au lieu de 200œÇ°
- œÜí Prix achat : 260œÇ° au lieu de 200œÇ°

#### 3. **êQUILIBRE** (Production œâà Consommation)
- **Modificateur Vente** : 1.1 (+10%)
- **Modificateur Achat** : 0.8 (-20%)
- **Logique** : Production et consommation équilibrées, prix moyens
- **Indicateur** : œÜí êQUIL

#### 4. **TRANSIT** (Ni production ni consommation)
- **Modificateur Vente** : 1.2 (+20%)
- **Modificateur Achat** : 0.7 (-30%)
- **Logique** : Simple point de commerce, marge standard
- **Indicateur** : TRANSIT

### Ajustement selon Stock

Le stock influence aussi les prix :

```
Ratio Stock = Stock Actuel / Stock Maximum

Ajustement = 1.0 + (0.5 - Ratio Stock) ó 0.8
```

**Exemples** :
- **Stock plein (100%)** : Ratio = 1.0 œÜí Ajustement = 0.6 (prix bas)
- **Stock moyen (50%)** : Ratio = 0.5 œÜí Ajustement = 1.0 (prix normal)
- **Stock vide (0%)** : Ratio = 0.0 œÜí Ajustement = 1.4 (prix élevé)

### Exemple Complet

**Terra-Maxi-Hub - Minerai de Fer**
- Type économique : CONSOMMATEUR (prod=50, conso=500)
- Prix base : 50œÇ°
- Stock : 3000 / 10000 (30%)

**Calcul** :
1. Modificateur consommateur vente = 1.8
2. Ratio stock = 0.3
3. Ajustement stock = 1.0 + (0.5 - 0.3) ó 0.8 = 1.16
4. **Prix vente** = 50 ó 1.8 ó 1.16 = **104œÇ°**
5. Modificateur consommateur achat = 1.3
6. **Prix achat** = 50 ó 1.3 ó 1.16 = **75œÇ°**

## Marchés par Station

### Terra-Maxi-Hub (Terre)
**Profil** : Hub commercial majeur, consommateur de ressources, producteur de biens manufacturés

| Type | Production | Consommation | R¥le |
|------|------------|--------------|------|
| Matières premières | Faible | Très élevée | CONSOMMATEUR œ¨Ü |
| Matières raffinées | Moyenne | Moyenne | êQUILIBRE œÜí |
| Composants | élevée | Faible | PRODUCTEUR œ¨á |
| Manufacturés | Très élevée | Faible | PRODUCTEUR œ¨á |
| Consommables | élevée | Très élevée | CONSOMMATEUR œ¨Ü |
| Carburants | Faible | élevée | CONSOMMATEUR œ¨Ü |
| Luxe | Faible | élevée | CONSOMMATEUR œ¨Ü |

**Stratégie pour les joueurs** :
- œúÖ **Vendre** : Matières premières, consommables (prix élevés)
- œúÖ **Acheter** : Composants, manufacturés (prix bas)

### Mars-spatiogare (Mars)
**Profil** : Colonie minière, gros producteur de minerais

| Type | Production | Consommation | R¥le |
|------|------------|--------------|------|
| Matières premières | Très élevée | Faible | PRODUCTEUR œ¨á |
| Matières raffinées | élevée | Faible | PRODUCTEUR œ¨á |
| Consommables | Faible | Moyenne | CONSOMMATEUR œ¨Ü |
| Carburants | Faible | Moyenne | êQUILIBRE œÜí |

**Stratégie** :
- œúÖ **Acheter** : Minerais (prix très bas)
- œúÖ **Vendre** : Consommables, carburants

### Jupiter-spatiogare (Jupiter)
**Profil** : Station d'extraction de gaz, producteur majeur de carburants

| Type | Production | Consommation | R¥le |
|------|------------|--------------|------|
| Carburants | Très élevée | Faible | PRODUCTEUR œ¨á |
| Matières premières | Moyenne | Faible | PRODUCTEUR œ¨á |
| Consommables | Faible | Moyenne | CONSOMMATEUR œ¨Ü |

**Stratégie** :
- œúÖ **Acheter** : Hydrogène, Deutérium (prix très bas)
- œúÖ **Vendre** : Nourriture, eau

### Neptune-spatiogare (Neptune)
**Profil** : Station industrielle, raffinage et manufacture

| Type | Production | Consommation | R¥le |
|------|------------|--------------|------|
| Matières raffinées | Très élevée | Faible | PRODUCTEUR œ¨á |
| Carburants | élevée | Faible | PRODUCTEUR œ¨á |
| Manufacturés | Moyenne | Faible | PRODUCTEUR œ¨á |

**Stratégie** :
- œúÖ **Acheter** : Acier, aluminium, drones (prix bas)
- œúÖ **Vendre** : Matières premières brutes

### Lunastar-station (Lune)
**Profil** : Station de départ, équilibrée pour débutants

| Type | Production | Consommation | R¥le |
|------|------------|--------------|------|
| Tous produits | Moyenne | Moyenne | êQUILIBRE œÜí |

**Stratégie** :
- Bons prix pour apprendre le commerce
- Pas d'extrêmes de prix

## Routes Commerciales Rentables

### Route 1 : Mars œÜí Terre
1. **Acheter  Mars** : Minerai de Fer (35œÇ° producteur)
2. **Vendre  Terre** : Minerai de Fer (104œÇ° consommateur)
3. **Profit** : +69œÇ° par unité (+197%)

### Route 2 : Jupiter œÜí Terre
1. **Acheter  Jupiter** : Hydrogène (140œÇ° producteur)
2. **Vendre  Terre** : Hydrogène (432œÇ° consommateur)
3. **Profit** : +292œÇ° par unité (+209%)

### Route 3 : Terre œÜí Mars
1. **Acheter  Terre** : Rations Alimentaires (21œÇ° producteur manufacturé)
2. **Vendre  Mars** : Rations Alimentaires (39œÇ° consommateur)
3. **Profit** : +18œÇ° par unité (+86%)

### Route Triangulaire Optimale
1. **Mars œÜí Terre** : Minerais (+197%)
2. **Terre œÜí Neptune** : Circuits électroniques (+120%)
3. **Neptune œÜí Mars** : Pièces détachées (+80%)

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
- Joueur **achète** 1000 unités œÜí Stock station baisse œÜí Prix monte
- Joueur **vend** 1000 unités œÜí Stock station monte œÜí Prix baisse

## Commandes

### `marche`
Affiche le marché de la station actuelle

**Prérequis** :
- ätre dans une station (`transborder`)
- Station doit avoir `commerciale = true`

**Affichage** :
```
=== MARCHê DE Terra-Maxi-Hub ===

Code       | Produit              | Type        | Achat    | Vente    | Stock    | êco
-----------|----------------------|-------------|----------|----------|----------|-------------
FER        | Minerai de Fer       | matiere_... | 75œÇ°      | 104œÇ°     | 3,000    | CONSO œ¨Ü
H2         | Hydrogène            | carburant   | 260œÇ°     | 432œÇ°     | 1,200    | CONSO œ¨Ü
CIRCUIT    | Circuit électronique | composant   | 180œÇ°     | 252œÇ°     | 8,500    | PROD œ¨á

üí∞ Achat = Station achète AU joueur | Vente = Station vend AU joueur
œ¨á PROD = Prix bas | œ¨Ü CONSO = Prix élevé | œÜí êQUIL = Prix moyen
```

### `acheter <code> <quantité>`
Acheter un produit  la station (station VEND au joueur)

**Exemple** :
```
> acheter FER 500

=== ACHAT EFFECTUê ===
Station: Terra-Maxi-Hub
Produit: Minerai de Fer (FER)
Quantité: 500 unités
Prix unitaire: 104.00œÇ°
Prix total: 52,000.00œÇ°
Nouveau stock station: 2,500
Type économique: consommateur

üí° Le prix a été ajusté selon l'offre et la demande.
```

**Vérifications** :
- Stock station suffisant
- Crédits joueur suffisants (TODO)
- Capacité cargo vaisseau (TODO)

### `vendre <code> <quantité>`
Vendre un produit  la station (station ACHTE au joueur)

**Exemple** :
```
> vendre FER 500

=== VENTE EFFECTUêE ===
Station: Terra-Maxi-Hub
Produit: Minerai de Fer (FER)
Quantité: 500 unités
Prix unitaire: 75.00œÇ°
Prix total: 37,500.00œÇ°
Nouveau stock station: 3,500
Type économique: consommateur

üí° Le prix a été ajusté selon l'offre et la demande.
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
- **Prix majorés** : +50%  +200%
- **Disponibilité limitée** : Certaines stations seulement
- **Risques** : Contr¥les douaniers, confiscation

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

### Détermination Type êconomique
```php
if (production == 0 && consommation == 0) œÜí TRANSIT
else if (production > consommation ó 1.5) œÜí PRODUCTEUR
else if (consommation > production ó 1.5) œÜí CONSOMMATEUR
else œÜí êQUILIBRE
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
ajust_stock = 1.0 + (0.5 - ratio_stock) ó 0.8;

prix_vente = prix_base ó modif ó ajust_stock;
```

### Calcul Prix Achat
```php
modif = match(type_economique) {
    PRODUCTEUR => 0.4,
    CONSOMMATEUR => 1.3,
    EQUILIBRE => 0.8,
    TRANSIT => 0.7,
};

prix_achat = prix_base ó modif ó ajust_stock;

// S'assurer que station fait une marge
if (prix_achat >= prix_vente) {
    prix_achat = prix_vente ó 0.7;
}
```

## êquilibrage

### Prix Base Recommandés

- **Matières premières** : 50-150œÇ°
- **Matières raffinées** : 150-300œÇ°
- **Carburants** : 200-500œÇ°
- **Composants** : 300-2000œÇ°
- **Consommables** : 10-100œÇ°
- **Manufacturés** : 250-5000œÇ°
- **Luxe** : 500-2000œÇ°

### Production/Consommation

- **Faible** : 50-100/jour
- **Moyenne** : 100-300/jour
- **élevée** : 300-600/jour
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
