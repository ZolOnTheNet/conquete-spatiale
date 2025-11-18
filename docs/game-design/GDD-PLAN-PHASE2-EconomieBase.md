# 💰 PLAN PHASE 2 : ÉCONOMIE DE BASE
## Conquête Spatiale

**Version :** 1.0
**Date création :** 2025-11-18
**Durée estimée :** 3-4 jours
**Prérequis :** Phase 1 MVP complète + Pré-Phase 2 Infrastructure

---

## 🎯 OBJECTIFS PRINCIPAUX

Implémenter le système économique de base permettant :

1. **Extraction de ressources** (21 matières premières)
2. **Transformation industrielle** (3 niveaux)
3. **Commerce basique** (achat/vente)
4. **Gestion inventaire** (vaisseaux et bases)
5. **Production automatique** en arrière-plan

---

## 📋 JOUR 1 : RESSOURCES ET GISEMENTS (8h)

### Objectif

Créer le système de ressources minières et gisements.

### 1.1 Base de Données Ressources

#### Migration : `create_ressources_table`

```php
Schema::create('ressources', function (Blueprint $table) {
    $table->id();
    $table->string('code', 20)->unique(); // FER, GRAPHITE, etc.
    $table->string('nom', 100);
    $table->string('categorie', 50); // metaux, gaz, chimie, exotique
    $table->text('description')->nullable();
    $table->decimal('poids_unitaire', 8, 3)->default(1.0); // tonnes/unité
    $table->decimal('prix_base', 12, 2)->default(100); // crédits/unité
    $table->integer('rarete')->default(50); // 1-100 (1=très rare, 100=très commun)
    $table->timestamps();
});
```

#### Seeder : `RessourceSeeder`

**21 matières premières selon GDD :**

| Code | Nom | Catégorie | Poids | Prix Base | Rareté |
|------|-----|-----------|-------|-----------|--------|
| GRAPHITE | Graphite | metaux | 1.0 | 50 | 70 |
| URANIUM | Uranium | metaux | 1.5 | 500 | 10 |
| HYDROGENE | Hydrogène | gaz | 0.1 | 20 | 90 |
| BAUXITE | Bauxite | metaux | 1.2 | 40 | 75 |
| PLATINE | Platine | metaux | 2.0 | 800 | 15 |
| ALUMINIUM | Aluminium | metaux | 0.8 | 60 | 80 |
| ZINC | Zinc | metaux | 1.1 | 55 | 70 |
| NICKEL | Nickel | metaux | 1.3 | 70 | 65 |
| TUNGSTENE | Tungstène | metaux | 1.8 | 150 | 50 |
| FER | Fer | metaux | 1.0 | 30 | 95 |
| NIOBIUM | Niobium | metaux | 1.4 | 200 | 40 |
| ARGILES | Argiles | elementaire | 0.8 | 10 | 90 |
| SABLES | Sables | elementaire | 0.9 | 10 | 95 |
| OXYGENE | Oxygène | gaz | 0.1 | 15 | 95 |
| GLACES | Glaces | elementaire | 0.5 | 20 | 85 |
| BITUMES | Bitumes | elementaire | 1.0 | 40 | 60 |
| NACRETOILE | Nacrétoile | exotique | 0.5 | 1500 | 5 |
| PLAZETOILE | Plazétoile | exotique | 0.4 | 2000 | 3 |
| ARGETOILE | Argétoile | exotique | 0.3 | 2500 | 2 |
| TYRETOILE | Tyrétoile | exotique | 0.6 | 1800 | 4 |
| ELEMENTS_CHIMIQUES | Éléments Chimiques | chimie | 0.7 | 80 | 60 |

### 1.2 Gisements Miniers

#### Migration : `create_gisements_table`

```php
Schema::create('gisements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('planete_id')->constrained()->onDelete('cascade');
    $table->foreignId('ressource_id')->constrained()->onDelete('cascade');

    // Position sur la planète (optionnel, pour plusieurs gisements/planète)
    $table->decimal('latitude', 8, 5)->nullable();
    $table->decimal('longitude', 9, 5)->nullable();

    // Caractéristiques du gisement
    $table->integer('richesse')->default(100); // 0-100 (% rendement)
    $table->bigInteger('quantite_totale')->default(1000000); // Unités totales
    $table->bigInteger('quantite_restante')->default(1000000);

    // État
    $table->boolean('decouvert')->default(false);
    $table->timestamp('decouvert_le')->nullable();
    $table->foreignId('decouvert_par')->nullable()->constrained('personnages');

    // Exploitation
    $table->boolean('en_exploitation')->default(false);
    $table->foreignId('exploite_par')->nullable()->constrained('personnages');

    $table->timestamps();

    // Index composites
    $table->index(['planete_id', 'ressource_id']);
});
```

#### Model : `Gisement`

```php
class Gisement extends Model
{
    protected $fillable = [
        'planete_id', 'ressource_id', 'latitude', 'longitude',
        'richesse', 'quantite_totale', 'quantite_restante',
        'decouvert', 'decouvert_le', 'decouvert_par',
        'en_exploitation', 'exploite_par',
    ];

    protected $casts = [
        'decouvert' => 'boolean',
        'en_exploitation' => 'boolean',
        'decouvert_le' => 'datetime',
    ];

    public function planete()
    {
        return $this->belongsTo(Planete::class);
    }

    public function ressource()
    {
        return $this->belongsTo(Ressource::class);
    }

    public function decouvreur()
    {
        return $this->belongsTo(Personnage::class, 'decouvert_par');
    }

    public function exploitant()
    {
        return $this->belongsTo(Personnage::class, 'exploite_par');
    }

    /**
     * Calculer rendement effectif d'extraction
     */
    public function getRendementEffectif(): int
    {
        // Base = richesse du gisement
        $rendement = $this->richesse;

        // TODO: Facteurs additionnels
        // - Équipement extracteur
        // - Compétences personnage
        // - Technologie

        return max(10, min(100, $rendement)); // Entre 10 et 100%
    }

    /**
     * Extraire une quantité
     */
    public function extraire(int $quantite): int
    {
        $quantite_extraite = min($quantite, $this->quantite_restante);

        $this->quantite_restante -= $quantite_extraite;
        $this->save();

        return $quantite_extraite;
    }

    /**
     * Vérifier si épuisé
     */
    public function isEpuise(): bool
    {
        return $this->quantite_restante <= 0;
    }
}
```

### 1.3 Génération Gisements

#### Ajout à `PlaneteSeeder` / `UniverseSeeder`

```php
protected function genererGisements(Planete $planete): void
{
    // Nombre de gisements selon type de planète
    $nb_gisements = match($planete->type_planete) {
        'Tellurique' => rand(3, 8),
        'Gazeuse' => rand(1, 3), // Gaz uniquement
        'Glacée' => rand(2, 5),
        default => rand(1, 4),
    };

    $ressources_disponibles = $this->getRessourcesSelonType($planete->type_planete);

    for ($i = 0; $i < $nb_gisements; $i++) {
        $ressource = $ressources_disponibles->random();

        Gisement::create([
            'planete_id' => $planete->id,
            'ressource_id' => $ressource->id,
            'richesse' => rand(20, 100),
            'quantite_totale' => rand(100000, 10000000),
            'quantite_restante' => function($attributes) {
                return $attributes['quantite_totale'];
            },
            'decouvert' => false,
        ]);
    }
}

protected function getRessourcesSelonType(string $type): Collection
{
    $ressources = Ressource::all();

    return match($type) {
        'Tellurique' => $ressources->whereIn('categorie', ['metaux', 'elementaire', 'chimie']),
        'Gazeuse' => $ressources->where('categorie', 'gaz'),
        'Glacée' => $ressources->whereIn('code', ['GLACES', 'OXYGENE', 'HYDROGENE']),
        default => $ressources,
    };
}
```

### 1.4 Commandes de Base

#### `scan-planete` - Scanner planète pour gisements

```php
// Dans GameController
private function scanPlanete(Personnage $personnage, array $parts): array
{
    // Récupérer planète cible
    if (count($parts) < 2) {
        return ['success' => false, 'message' => 'Usage: scan-planete <nom_planete>'];
    }

    $nom_planete = $parts[1];

    // Trouver planète dans système actuel
    $systeme = $personnage->getSystemeActuel();

    if (!$systeme) {
        return ['success' => false, 'message' => 'Vous devez être dans un système stellaire.'];
    }

    $planete = $systeme->planetes()->where('nom', 'like', "%{$nom_planete}%")->first();

    if (!$planete) {
        return ['success' => false, 'message' => "Planète '{$nom_planete}' non trouvée."];
    }

    // Scanner gisements (utilise scanner vaisseau)
    $resultat = $personnage->scannerGisements($planete);

    if (!$resultat['succes']) {
        return ['success' => false, 'message' => $resultat['message']];
    }

    // Afficher résultats
    $message = "\n=== SCAN GÉOLOGIQUE : {$planete->nom} ===\n";
    $message .= "Type: {$planete->type_planete}\n";
    $message .= "Puissance scan: {$resultat['puissance_scan']}\n\n";

    if (count($resultat['gisements_detectes']) > 0) {
        $message .= "--- GISEMENTS DÉTECTÉS ---\n";
        foreach ($resultat['gisements_detectes'] as $detection) {
            $message .= "\n• {$detection['ressource']}\n";
            $message .= "  Richesse: {$detection['richesse']}%\n";
            $message .= "  Quantité estimée: " . number_format($detection['quantite']) . " unités\n";
        }
    } else {
        $message .= "Aucun gisement détecté.\n";
        $message .= "💡 Améliorez votre scanner géologique pour mieux détecter.\n";
    }

    return ['success' => true, 'message' => $message];
}
```

#### Méthode `Personnage::scannerGisements()`

```php
public function scannerGisements(Planete $planete): array
{
    if (!$this->vaisseauActif) {
        return ['succes' => false, 'message' => 'Vous devez être à bord d\'un vaisseau.'];
    }

    $vaisseau = $this->vaisseauActif;
    $puissance_scan = $vaisseau->getPuissanceScanEffective();

    // Trouver gisements non découverts
    $gisements = $planete->gisements()->where('decouvert', false)->get();

    $detections = [];

    foreach ($gisements as $gisement) {
        // Formule détection: 2d12 + Puissance_Scan vs Seuil
        $jet = $this->lancerDes();
        $resultat_des = $jet['total'];
        $resultat_total = $resultat_des + ($puissance_scan / 10);

        // Seuil basé sur rareté de la ressource
        $rarete = $gisement->ressource->rarete;
        $seuil = 150 - $rarete; // Plus rare = plus difficile

        $detecte = $resultat_total >= $seuil;

        if ($detecte) {
            // Marquer comme découvert
            $gisement->update([
                'decouvert' => true,
                'decouvert_le' => now(),
                'decouvert_par' => $this->id,
            ]);

            $detections[] = [
                'ressource' => $gisement->ressource->nom,
                'code' => $gisement->ressource->code,
                'richesse' => $gisement->richesse,
                'quantite' => $gisement->quantite_totale,
                'gisement_id' => $gisement->id,
            ];
        }
    }

    return [
        'succes' => true,
        'puissance_scan' => $puissance_scan,
        'gisements_detectes' => $detections,
    ];
}
```

---

## 📋 JOUR 2 : INVENTAIRE ET EXTRACTION (8h)

### Objectif

Système d'inventaire pour vaisseaux et extraction minière.

### 2.1 Inventaire

#### Migration : `create_inventaires_table`

```php
Schema::create('inventaires', function (Blueprint $table) {
    $table->id();

    // Propriétaire (polymorphic)
    $table->morphs('conteneur'); // conteneur_type, conteneur_id
    // Exemples: Vaisseau, Base, Personnage

    $table->foreignId('ressource_id')->constrained()->onDelete('cascade');
    $table->bigInteger('quantite')->default(0);

    $table->timestamps();

    // Unicité: 1 ligne par ressource par conteneur
    $table->unique(['conteneur_type', 'conteneur_id', 'ressource_id']);
});
```

#### Trait : `HasInventaire`

```php
trait HasInventaire
{
    public function inventaires()
    {
        return $this->morphMany(Inventaire::class, 'conteneur');
    }

    /**
     * Obtenir quantité d'une ressource
     */
    public function getQuantiteRessource(int|string $ressource_id): int
    {
        if (is_string($ressource_id)) {
            $ressource = Ressource::where('code', $ressource_id)->first();
            $ressource_id = $ressource?->id ?? 0;
        }

        $inventaire = $this->inventaires()
            ->where('ressource_id', $ressource_id)
            ->first();

        return $inventaire?->quantite ?? 0;
    }

    /**
     * Ajouter ressource
     */
    public function ajouterRessource(int $ressource_id, int $quantite): bool
    {
        // Vérifier capacité si applicable
        if (method_exists($this, 'getCapaciteRestante')) {
            $ressource = Ressource::find($ressource_id);
            $poids_total = $ressource->poids_unitaire * $quantite;

            if ($this->getCapaciteRestante() < $poids_total) {
                return false; // Pas assez de place
            }
        }

        $inventaire = $this->inventaires()->firstOrCreate(
            ['ressource_id' => $ressource_id],
            ['quantite' => 0]
        );

        $inventaire->increment('quantite', $quantite);

        return true;
    }

    /**
     * Retirer ressource
     */
    public function retirerRessource(int $ressource_id, int $quantite): bool
    {
        $inventaire = $this->inventaires()
            ->where('ressource_id', $ressource_id)
            ->first();

        if (!$inventaire || $inventaire->quantite < $quantite) {
            return false; // Pas assez
        }

        $inventaire->decrement('quantite', $quantite);

        // Supprimer si quantité = 0
        if ($inventaire->quantite <= 0) {
            $inventaire->delete();
        }

        return true;
    }

    /**
     * Obtenir inventaire complet
     */
    public function getInventaireComplet(): Collection
    {
        return $this->inventaires()
            ->with('ressource')
            ->get()
            ->map(function ($inv) {
                return [
                    'ressource' => $inv->ressource->nom,
                    'code' => $inv->ressource->code,
                    'quantite' => $inv->quantite,
                    'poids_unitaire' => $inv->ressource->poids_unitaire,
                    'poids_total' => $inv->quantite * $inv->ressource->poids_unitaire,
                    'prix_unitaire' => $inv->ressource->prix_base,
                    'valeur_totale' => $inv->quantite * $inv->ressource->prix_base,
                ];
            });
    }
}
```

#### Ajouter à `Vaisseau`, `Base`

```php
class Vaisseau extends Model
{
    use HasInventaire;

    // ...

    public function getCapaciteRestante(): float
    {
        $capacite_max = $this->capacite_soute; // Tonnes
        $poids_actuel = $this->getPoidsCargaison();

        return max(0, $capacite_max - $poids_actuel);
    }

    public function getPoidsCargaison(): float
    {
        return $this->inventaires()
            ->with('ressource')
            ->get()
            ->sum(function ($inv) {
                return $inv->quantite * $inv->ressource->poids_unitaire;
            });
    }
}
```

#### Migration : Ajouter `capacite_soute` à `vaisseaux`

```php
$table->decimal('capacite_soute', 10, 2)->default(100.0)->after('reserve'); // Tonnes
```

### 2.2 Extraction Minière

#### Commande : `extraire <ressource> [quantite]`

```php
private function extraire(Personnage $personnage, array $parts): array
{
    if (count($parts) < 2) {
        return ['success' => false, 'message' => 'Usage: extraire <code_ressource> [quantite]'];
    }

    $code_ressource = strtoupper($parts[1]);
    $quantite_demandee = isset($parts[2]) ? (int)$parts[2] : 100;

    // Vérifier que le personnage est sur une planète (ou en orbite proche)
    $planete = $personnage->getPlaneteActuelle();

    if (!$planete) {
        return ['success' => false, 'message' => 'Vous devez être sur une planète ou en orbite proche.'];
    }

    // Trouver gisement exploitable
    $gisement = $planete->gisements()
        ->whereHas('ressource', function ($q) use ($code_ressource) {
            $q->where('code', $code_ressource);
        })
        ->where('decouvert', true)
        ->where('quantite_restante', '>', 0)
        ->first();

    if (!$gisement) {
        return ['success' => false, 'message' => "Aucun gisement de {$code_ressource} découvert ou disponible."];
    }

    // Consommer PA
    $cout_pa = 2; // 2 PA par extraction
    if ($personnage->points_action < $cout_pa) {
        return ['success' => false, 'message' => "Pas assez de PA (besoin: {$cout_pa})."];
    }

    $personnage->consommerPA($cout_pa);

    // Calculer quantité extraite (avec rendement)
    $rendement = $gisement->getRendementEffectif() / 100;
    $quantite_brute = min($quantite_demandee, $gisement->quantite_restante);
    $quantite_extraite = (int)($quantite_brute * $rendement);

    // Extraire du gisement
    $gisement->extraire($quantite_brute);

    // Ajouter au vaisseau
    $vaisseau = $personnage->vaisseauActif;

    if (!$vaisseau) {
        return ['success' => false, 'message' => 'Vous devez avoir un vaisseau actif.'];
    }

    $ajoute = $vaisseau->ajouterRessource($gisement->ressource_id, $quantite_extraite);

    if (!$ajoute) {
        return ['success' => false, 'message' => 'Soute pleine ! Impossible d\'ajouter la ressource.'];
    }

    // Message de succès
    $ressource = $gisement->ressource;
    $message = "\n=== EXTRACTION MINIÈRE ===\n";
    $message .= "Ressource: {$ressource->nom}\n";
    $message .= "Quantité extraite: {$quantite_extraite} unités\n";
    $message .= "Rendement: " . ($rendement * 100) . "%\n";
    $message .= "Gisement restant: " . number_format($gisement->quantite_restante) . " unités\n";
    $message .= "\nSoute: " . round($vaisseau->getPoidsCargaison(), 2) . " / {$vaisseau->capacite_soute} tonnes\n";

    // XP Mining (TODO: Learning by doing)

    return ['success' => true, 'message' => $message];
}
```

### 2.3 Commande Inventaire

```php
private function inventaire(Personnage $personnage, array $parts): array
{
    $vaisseau = $personnage->vaisseauActif;

    if (!$vaisseau) {
        return ['success' => false, 'message' => 'Vous devez avoir un vaisseau actif.'];
    }

    $inventaire = $vaisseau->getInventaireComplet();

    $message = "\n=== INVENTAIRE VAISSEAU ===\n";
    $message .= "Modèle: {$vaisseau->modele}\n";
    $message .= "Capacité: " . round($vaisseau->getPoidsCargaison(), 2) . " / {$vaisseau->capacite_soute} tonnes\n\n";

    if ($inventaire->isEmpty()) {
        $message .= "Soute vide.\n";
    } else {
        $message .= "--- CARGAISON ---\n";

        $valeur_totale = 0;

        foreach ($inventaire as $item) {
            $message .= "\n• {$item['ressource']} ({$item['code']})\n";
            $message .= "  Quantité: " . number_format($item['quantite']) . " unités\n";
            $message .= "  Poids: " . round($item['poids_total'], 2) . " tonnes\n";
            $message .= "  Valeur: " . number_format($item['valeur_totale']) . " crédits\n";

            $valeur_totale += $item['valeur_totale'];
        }

        $message .= "\n--- TOTAL ---\n";
        $message .= "Valeur totale: " . number_format($valeur_totale) . " crédits\n";
    }

    return ['success' => true, 'message' => $message];
}
```

---

## 📋 JOUR 3 : COMMERCE DE BASE (8h)

### Objectif

Système de commerce : marchés, achat/vente.

### 3.1 Marchés

#### Migration : `create_marches_table`

```php
Schema::create('marches', function (Blueprint $table) {
    $table->id();
    $table->string('nom', 100);

    // Localisation (polymorphic)
    $table->morphs('localisation'); // Base, Station, Planète

    $table->string('type_marche', 50)->default('standard');
    // Types: standard, bourse, antiquites, noir

    $table->decimal('taxe_achat', 5, 2)->default(5.0); // %
    $table->decimal('taxe_vente', 5, 2)->default(5.0); // %

    $table->boolean('actif')->default(true);

    $table->timestamps();
});
```

#### Migration : `create_offres_marche_table`

```php
Schema::create('offres_marche', function (Blueprint $table) {
    $table->id();
    $table->foreignId('marche_id')->constrained()->onDelete('cascade');
    $table->foreignId('ressource_id')->constrained()->onDelete('cascade');

    $table->enum('type', ['achat', 'vente']); // Le marché achète ou vend

    $table->decimal('prix_unitaire', 12, 2);
    $table->integer('quantite_disponible')->default(0);
    $table->integer('quantite_max')->default(1000000); // Stock max

    // Offre PNJ ou joueur
    $table->foreignId('joueur_id')->nullable()->constrained('personnages')->onDelete('cascade');

    $table->boolean('active')->default(true);

    $table->timestamps();

    $table->index(['marche_id', 'ressource_id', 'type']);
});
```

#### Seeder : Créer marché de départ

```php
// Dans DatabaseSeeder ou UniverseSeeder
protected function creerMarchePrincipal(): void
{
    // Créer base Terra (point de départ)
    $base_terra = Base::create([
        'nom' => 'Base Terra',
        'objet_spatial_id' => ObjetSpatial::create([
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'position_x' => 0.0,
            'position_y' => 0.0,
            'position_z' => 0.0,
            'type' => 'base',
        ])->id,
        // ... autres champs
    ]);

    // Créer marché
    $marche = Marche::create([
        'nom' => 'Marché Central Terra',
        'localisation_type' => Base::class,
        'localisation_id' => $base_terra->id,
        'type_marche' => 'standard',
        'taxe_achat' => 5.0,
        'taxe_vente' => 5.0,
    ]);

    // Ajouter offres pour toutes les ressources
    $ressources = Ressource::all();

    foreach ($ressources as $ressource) {
        // Le marché ACHÈTE aux joueurs
        OffreMarche::create([
            'marche_id' => $marche->id,
            'ressource_id' => $ressource->id,
            'type' => 'achat',
            'prix_unitaire' => $ressource->prix_base * 0.8, // Achète 80% du prix base
            'quantite_disponible' => 999999,
            'quantite_max' => 999999,
        ]);

        // Le marché VEND aux joueurs
        OffreMarche::create([
            'marche_id' => $marche->id,
            'ressource_id' => $ressource->id,
            'type' => 'vente',
            'prix_unitaire' => $ressource->prix_base * 1.2, // Vend 120% du prix base
            'quantite_disponible' => rand(100, 10000),
            'quantite_max' => 50000,
        ]);
    }
}
```

### 3.2 Crédits Personnage

#### Migration : Ajouter `credits` à `personnages`

```php
$table->decimal('credits', 15, 2)->default(10000)->after('experience');
```

### 3.3 Commandes Commerce

#### `marche` - Afficher offres du marché proche

```php
private function marche(Personnage $personnage, array $parts): array
{
    // Trouver marché le plus proche
    $marche = $this->getMarchePlusProche($personnage);

    if (!$marche) {
        return ['success' => false, 'message' => 'Aucun marché accessible dans votre position actuelle.'];
    }

    // Filtre par ressource si spécifié
    $code_ressource = $parts[1] ?? null;

    $query = $marche->offres()->with('ressource')->where('active', true);

    if ($code_ressource) {
        $query->whereHas('ressource', function ($q) use ($code_ressource) {
            $q->where('code', strtoupper($code_ressource));
        });
    }

    $offres = $query->get();

    $message = "\n=== MARCHÉ : {$marche->nom} ===\n";
    $message .= "Type: {$marche->type_marche}\n";
    $message .= "Taxes: Achat {$marche->taxe_achat}% | Vente {$marche->taxe_vente}%\n\n";

    if ($offres->isEmpty()) {
        $message .= "Aucune offre disponible.\n";
    } else {
        $message .= "--- OFFRES ---\n";

        $achats = $offres->where('type', 'achat');
        $ventes = $offres->where('type', 'vente');

        if ($ventes->isNotEmpty()) {
            $message .= "\n🛒 VENTES (vous pouvez acheter) :\n";
            foreach ($ventes as $offre) {
                $prix_ttc = $offre->prix_unitaire * (1 + $marche->taxe_achat / 100);
                $message .= "• {$offre->ressource->nom} ({$offre->ressource->code})\n";
                $message .= "  Prix: " . number_format($prix_ttc, 2) . " crédits/unité\n";
                $message .= "  Stock: " . number_format($offre->quantite_disponible) . " unités\n";
            }
        }

        if ($achats->isNotEmpty()) {
            $message .= "\n💰 ACHATS (marché achète) :\n";
            foreach ($achats as $offre) {
                $prix_net = $offre->prix_unitaire * (1 - $marche->taxe_vente / 100);
                $message .= "• {$offre->ressource->nom} ({$offre->ressource->code})\n";
                $message .= "  Prix: " . number_format($prix_net, 2) . " crédits/unité\n";
            }
        }
    }

    $message .= "\n💵 Vos crédits: " . number_format($personnage->credits, 2) . " ¢\n";
    $message .= "\nCommandes: acheter <ressource> <quantite> | vendre <ressource> <quantite>\n";

    return ['success' => true, 'message' => $message];
}

private function getMarchePlusProche(Personnage $personnage): ?Marche
{
    // Simplification: marché accessible si dans même secteur ou secteur adjacent
    $position = $personnage->getPositionActuelle();

    return Marche::whereHasMorph('localisation', [Base::class], function ($q) use ($position) {
        $q->whereHas('objetSpatial', function ($sq) use ($position) {
            $sq->whereBetween('secteur_x', [$position['secteur_x'] - 1, $position['secteur_x'] + 1])
               ->whereBetween('secteur_y', [$position['secteur_y'] - 1, $position['secteur_y'] + 1])
               ->whereBetween('secteur_z', [$position['secteur_z'] - 1, $position['secteur_z'] + 1]);
        });
    })
    ->where('actif', true)
    ->first();
}
```

#### `acheter <ressource> <quantite>`

```php
private function acheter(Personnage $personnage, array $parts): array
{
    if (count($parts) < 3) {
        return ['success' => false, 'message' => 'Usage: acheter <code_ressource> <quantite>'];
    }

    $code_ressource = strtoupper($parts[1]);
    $quantite = (int)$parts[2];

    if ($quantite <= 0) {
        return ['success' => false, 'message' => 'Quantité invalide.'];
    }

    // Trouver marché
    $marche = $this->getMarchePlusProche($personnage);

    if (!$marche) {
        return ['success' => false, 'message' => 'Aucun marché accessible.'];
    }

    // Trouver offre de vente
    $offre = $marche->offres()
        ->where('type', 'vente')
        ->whereHas('ressource', function ($q) use ($code_ressource) {
            $q->where('code', $code_ressource);
        })
        ->where('active', true)
        ->first();

    if (!$offre) {
        return ['success' => false, 'message' => "Ressource {$code_ressource} non disponible."];
    }

    // Vérifier stock
    if ($offre->quantite_disponible < $quantite) {
        return ['success' => false, 'message' => "Stock insuffisant (disponible: {$offre->quantite_disponible})."];
    }

    // Calculer prix TTC
    $prix_unitaire_ttc = $offre->prix_unitaire * (1 + $marche->taxe_achat / 100);
    $prix_total = $prix_unitaire_ttc * $quantite;

    // Vérifier crédits
    if ($personnage->credits < $prix_total) {
        return ['success' => false, 'message' => "Crédits insuffisants (besoin: " . number_format($prix_total, 2) . " ¢)."];
    }

    // Vérifier capacité soute
    $vaisseau = $personnage->vaisseauActif;
    if (!$vaisseau) {
        return ['success' => false, 'message' => 'Vous devez avoir un vaisseau actif.'];
    }

    $ressource = $offre->ressource;
    $poids_total = $ressource->poids_unitaire * $quantite;

    if ($vaisseau->getCapaciteRestante() < $poids_total) {
        return ['success' => false, 'message' => 'Soute pleine ! Libérez de l\'espace.'];
    }

    // Transaction
    DB::transaction(function () use ($personnage, $vaisseau, $offre, $quantite, $prix_total) {
        // Déduire crédits
        $personnage->decrement('credits', $prix_total);

        // Déduire stock marché
        $offre->decrement('quantite_disponible', $quantite);

        // Ajouter au vaisseau
        $vaisseau->ajouterRessource($offre->ressource_id, $quantite);
    });

    $message = "\n=== TRANSACTION RÉUSSIE ===\n";
    $message .= "Acheté: {$quantite} x {$ressource->nom}\n";
    $message .= "Prix total: " . number_format($prix_total, 2) . " crédits\n";
    $message .= "Crédits restants: " . number_format($personnage->fresh()->credits, 2) . " ¢\n";

    return ['success' => true, 'message' => $message];
}
```

#### `vendre <ressource> <quantite>`

```php
private function vendre(Personnage $personnage, array $parts): array
{
    if (count($parts) < 3) {
        return ['success' => false, 'message' => 'Usage: vendre <code_ressource> <quantite>'];
    }

    $code_ressource = strtoupper($parts[1]);
    $quantite = (int)$parts[2];

    if ($quantite <= 0) {
        return ['success' => false, 'message' => 'Quantité invalide.'];
    }

    // Trouver marché
    $marche = $this->getMarchePlusProche($personnage);

    if (!$marche) {
        return ['success' => false, 'message' => 'Aucun marché accessible.'];
    }

    // Trouver offre d'achat
    $offre = $marche->offres()
        ->where('type', 'achat')
        ->whereHas('ressource', function ($q) use ($code_ressource) {
            $q->where('code', $code_ressource);
        })
        ->where('active', true)
        ->first();

    if (!$offre) {
        return ['success' => false, 'message' => "Le marché n'achète pas {$code_ressource}."];
    }

    $ressource = $offre->ressource;

    // Vérifier inventaire
    $vaisseau = $personnage->vaisseauActif;
    if (!$vaisseau) {
        return ['success' => false, 'message' => 'Vous devez avoir un vaisseau actif.'];
    }

    $quantite_possedee = $vaisseau->getQuantiteRessource($ressource->id);

    if ($quantite_possedee < $quantite) {
        return ['success' => false, 'message' => "Vous n'avez que {$quantite_possedee} unités."];
    }

    // Calculer prix net (après taxes)
    $prix_unitaire_net = $offre->prix_unitaire * (1 - $marche->taxe_vente / 100);
    $prix_total = $prix_unitaire_net * $quantite;

    // Transaction
    DB::transaction(function () use ($personnage, $vaisseau, $offre, $ressource, $quantite, $prix_total) {
        // Ajouter crédits
        $personnage->increment('credits', $prix_total);

        // Augmenter stock marché
        $offre->increment('quantite_disponible', $quantite);

        // Retirer du vaisseau
        $vaisseau->retirerRessource($ressource->id, $quantite);
    });

    $message = "\n=== VENTE RÉUSSIE ===\n";
    $message .= "Vendu: {$quantite} x {$ressource->nom}\n";
    $message .= "Prix total: " . number_format($prix_total, 2) . " crédits\n";
    $message .= "Crédits totaux: " . number_format($personnage->fresh()->credits, 2) . " ¢\n";

    return ['success' => true, 'message' => $message];
}
```

---

## 📋 JOUR 4 : TRANSFORMATION INDUSTRIELLE (8h)

### Objectif

Système de transformation de ressources (3 niveaux).

### 4.1 Recettes de Transformation

#### Migration : `create_recettes_table`

```php
Schema::create('recettes', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique(); // ACIER, PLASTACIER, etc.
    $table->string('nom', 100);
    $table->foreignId('produit_id')->constrained('ressources'); // Ressource produite
    $table->integer('quantite_produite')->default(1);

    $table->integer('niveau_transformation')->default(1); // 1=base, 2=intermédiaire, 3=avancé
    $table->integer('duree_production')->default(60); // Minutes
    $table->decimal('cout_energetique', 10, 2)->default(10.0); // Énergie consommée

    $table->text('description')->nullable();

    $table->timestamps();
});
```

#### Migration : `create_ingredients_recette_table`

```php
Schema::create('ingredients_recette', function (Blueprint $table) {
    $table->id();
    $table->foreignId('recette_id')->constrained()->onDelete('cascade');
    $table->foreignId('ressource_id')->constrained()->onDelete('cascade');
    $table->integer('quantite_requise');

    $table->timestamps();
});
```

#### Seeder : `RecetteSeeder`

**Exemples selon GDD :**

```php
class RecetteSeeder extends Seeder
{
    public function run()
    {
        // Niveau 1 : Matériaux de Base
        $this->creerRecette('ACIER', 'Acier', 1, 30, [
            'FER' => 1,
            'GRAPHITE' => 1,
        ]);

        $this->creerRecette('DIAMANT', 'Diamant', 1, 120, [
            'GRAPHITE' => 10,
        ]);

        // Niveau 2 : Matériaux Intermédiaires
        $this->creerRecette('PLASTACIER', 'Plastacier', 2, 60, [
            'ACIER' => 1,
            'PLAZETOILE' => 1,
        ]);

        $this->creerRecette('ELECTRONIQUE', 'Électronique', 2, 45, [
            'SABLES' => 1,
            'PLAZETOILE' => 1,
            'PLATINE' => 1,
        ]);

        $this->creerRecette('VERRERIE', 'Verrerie', 2, 30, [
            'SABLES' => 1,
            'PLAZETOILE' => 1,
        ]);

        $this->creerRecette('MECANIQUE', 'Mécanique', 2, 40, [
            'ACIER' => 1,
            'BITUMES' => 1,
        ]);

        // Niveau 3 : Composants Avancés
        $this->creerRecette('MOTEUR', 'Moteur', 3, 120, [
            'MECANIQUE' => 2,
            'ELECTRONIQUE' => 2,
            'ARGETOILE' => 1,
            'NIOBIUM' => 1,
        ]);

        $this->creerRecette('BATTERIE', 'Batterie', 3, 90, [
            'NIOBIUM' => 1,
            'ELECTRONIQUE' => 2,
            'NACRETOILE' => 1,
        ]);

        // Agricole
        $this->creerRecette('TERREAUX', 'Terreaux', 1, 20, [
            'ARGILES' => 1,
            'SABLES' => 1,
        ]);

        $this->creerRecette('ENGRAIS', 'Engrais', 1, 30, [
            'HYDROGENE' => 1,
            'ELEMENTS_CHIMIQUES' => 1, // Azote
        ]);

        // TODO: Plus de recettes selon GDD
    }

    protected function creerRecette(
        string $code,
        string $nom,
        int $niveau,
        int $duree,
        array $ingredients
    ): void {
        // Créer ressource produit si n'existe pas
        $produit = Ressource::firstOrCreate(
            ['code' => $code],
            [
                'nom' => $nom,
                'categorie' => match($niveau) {
                    1 => 'materiau_base',
                    2 => 'materiau_intermediaire',
                    3 => 'composant_avance',
                },
                'poids_unitaire' => 1.0,
                'prix_base' => $this->calculerPrixRecette($ingredients) * 2, // Valeur ajoutée
                'rarete' => 50,
            ]
        );

        // Créer recette
        $recette = Recette::create([
            'code' => $code,
            'nom' => $nom,
            'produit_id' => $produit->id,
            'quantite_produite' => 1,
            'niveau_transformation' => $niveau,
            'duree_production' => $duree,
            'cout_energetique' => $duree * 0.5,
        ]);

        // Ajouter ingrédients
        foreach ($ingredients as $code_ressource => $quantite) {
            $ressource = Ressource::where('code', $code_ressource)->first();

            if ($ressource) {
                $recette->ingredients()->create([
                    'ressource_id' => $ressource->id,
                    'quantite_requise' => $quantite,
                ]);
            }
        }
    }

    protected function calculerPrixRecette(array $ingredients): float
    {
        $total = 0;
        foreach ($ingredients as $code => $quantite) {
            $ressource = Ressource::where('code', $code)->first();
            if ($ressource) {
                $total += $ressource->prix_base * $quantite;
            }
        }
        return $total;
    }
}
```

### 4.2 Module de Production

#### Migration : `create_modules_production_table`

```php
Schema::create('modules_production', function (Blueprint $table) {
    $table->id();
    $table->string('nom', 100);

    // Localisation (sur une base)
    $table->foreignId('base_id')->constrained()->onDelete('cascade');

    $table->foreignId('recette_id')->nullable()->constrained()->onDelete('set null');
    // Recette actuellement en production

    $table->integer('niveau_module')->default(1); // 1-3, détermine recettes possibles
    $table->decimal('efficacite', 5, 2)->default(100.0); // % (peut être amélioré)

    $table->boolean('actif')->default(true);
    $table->timestamp('production_debut')->nullable();
    $table->timestamp('production_fin')->nullable();

    $table->timestamps();
});
```

**Note :** Pour l'instant, les modules de production seront créés manuellement ou via commande admin. La construction de bases complètes viendra dans une phase ultérieure.

### 4.3 Commande de Production

#### `produire <code_recette> [quantite]`

```php
private function produire(Personnage $personnage, array $parts): array
{
    if (count($parts) < 2) {
        return ['success' => false, 'message' => 'Usage: produire <code_recette> [quantite]'];
    }

    $code_recette = strtoupper($parts[1]);
    $quantite = isset($parts[2]) ? (int)$parts[2] : 1;

    // Trouver recette
    $recette = Recette::where('code', $code_recette)->first();

    if (!$recette) {
        return ['success' => false, 'message' => "Recette '{$code_recette}' introuvable."];
    }

    // Vérifier que le personnage est dans une base avec module de production
    // Simplification pour MVP : production possible depuis vaisseau si a les ressources
    $vaisseau = $personnage->vaisseauActif;

    if (!$vaisseau) {
        return ['success' => false, 'message' => 'Vous devez avoir un vaisseau actif.'];
    }

    // Vérifier ingrédients
    $ingredients = $recette->ingredients;
    foreach ($ingredients as $ingredient) {
        $quantite_requise = $ingredient->quantite_requise * $quantite;
        $quantite_possedee = $vaisseau->getQuantiteRessource($ingredient->ressource_id);

        if ($quantite_possedee < $quantite_requise) {
            $ressource = $ingredient->ressource;
            return [
                'success' => false,
                'message' => "Ingrédient manquant: {$ressource->nom} (besoin: {$quantite_requise}, vous avez: {$quantite_possedee})"
            ];
        }
    }

    // Vérifier énergie
    $cout_energie = $recette->cout_energetique * $quantite;
    if ($vaisseau->energie_actuelle < $cout_energie) {
        return ['success' => false, 'message' => "Énergie insuffisante (besoin: {$cout_energie})."];
    }

    // Consommer PA
    $cout_pa = 3; // 3 PA par production
    if ($personnage->points_action < $cout_pa) {
        return ['success' => false, 'message' => "Pas assez de PA (besoin: {$cout_pa})."];
    }

    $personnage->consommerPA($cout_pa);

    // Production (instantanée pour MVP, async plus tard)
    DB::transaction(function () use ($recette, $vaisseau, $quantite, $cout_energie, $ingredients) {
        // Consommer ingrédients
        foreach ($ingredients as $ingredient) {
            $quantite_a_retirer = $ingredient->quantite_requise * $quantite;
            $vaisseau->retirerRessource($ingredient->ressource_id, $quantite_a_retirer);
        }

        // Consommer énergie
        $vaisseau->decrement('energie_actuelle', $cout_energie);

        // Produire
        $quantite_produite = $recette->quantite_produite * $quantite;
        $vaisseau->ajouterRessource($recette->produit_id, $quantite_produite);
    });

    $produit = $recette->produit;

    $message = "\n=== PRODUCTION RÉUSSIE ===\n";
    $message .= "Recette: {$recette->nom}\n";
    $message .= "Produit: {$quantite} x {$produit->nom}\n";
    $message .= "Énergie consommée: {$cout_energie}\n";
    $message .= "\n💡 Dans une version future, la production sera asynchrone.\n";

    return ['success' => true, 'message' => $message];
}
```

#### `recettes` - Lister recettes disponibles

```php
private function recettes(Personnage $personnage, array $parts): array
{
    $recettes = Recette::with(['produit', 'ingredients.ressource'])
        ->orderBy('niveau_transformation')
        ->orderBy('nom')
        ->get();

    $message = "\n=== RECETTES DISPONIBLES ===\n";

    $par_niveau = $recettes->groupBy('niveau_transformation');

    foreach ($par_niveau as $niveau => $recettes_niveau) {
        $nom_niveau = match($niveau) {
            1 => 'MATÉRIAUX DE BASE',
            2 => 'MATÉRIAUX INTERMÉDIAIRES',
            3 => 'COMPOSANTS AVANCÉS',
            default => "NIVEAU {$niveau}",
        };

        $message .= "\n--- {$nom_niveau} ---\n";

        foreach ($recettes_niveau as $recette) {
            $message .= "\n• {$recette->nom} ({$recette->code})\n";
            $message .= "  Produit: {$recette->quantite_produite} x {$recette->produit->nom}\n";
            $message .= "  Durée: {$recette->duree_production} min | Énergie: {$recette->cout_energetique}\n";
            $message .= "  Ingrédients:\n";

            foreach ($recette->ingredients as $ingredient) {
                $message .= "    - {$ingredient->quantite_requise} x {$ingredient->ressource->nom}\n";
            }
        }
    }

    $message .= "\nCommande: produire <code_recette> [quantite]\n";

    return ['success' => true, 'message' => $message];
}
```

---

## 📊 RÉCAPITULATIF PHASE 2

### Fonctionnalités Livrées

1. ✅ **21 ressources minières** avec catégories et rareté
2. ✅ **Gisements sur planètes** avec richesse et quantité
3. ✅ **Scan géologique** pour découvrir gisements
4. ✅ **Extraction minière** avec rendement et consommation PA
5. ✅ **Système d'inventaire** polymorphique (vaisseaux, bases)
6. ✅ **Capacité de soute** et gestion du poids
7. ✅ **Marchés** (standard, bourse, antiquités, noir)
8. ✅ **Commerce** : achat/vente avec taxes
9. ✅ **Crédits** pour personnages
10. ✅ **Recettes de transformation** (3 niveaux)
11. ✅ **Production industrielle** instantanée (MVP)

### Commandes Ajoutées

| Commande | Description |
|----------|-------------|
| `scan-planete <nom>` | Scanner gisements d'une planète |
| `extraire <ressource> [qte]` | Extraire ressource d'un gisement |
| `inventaire` | Afficher inventaire vaisseau |
| `marche [ressource]` | Afficher offres du marché proche |
| `acheter <ressource> <qte>` | Acheter ressource au marché |
| `vendre <ressource> <qte>` | Vendre ressource au marché |
| `recettes` | Lister recettes de transformation |
| `produire <recette> [qte]` | Produire à partir d'une recette |

### Structure Fichiers

```
app/
├── Models/
│   ├── Ressource.php
│   ├── Gisement.php
│   ├── Inventaire.php
│   ├── Marche.php
│   ├── OffreMarche.php
│   ├── Recette.php
│   ├── IngredientRecette.php
│   └── Traits/
│       └── HasInventaire.php
database/
├── migrations/
│   ├── xxxx_create_ressources_table.php
│   ├── xxxx_create_gisements_table.php
│   ├── xxxx_create_inventaires_table.php
│   ├── xxxx_create_marches_table.php
│   ├── xxxx_create_offres_marche_table.php
│   ├── xxxx_create_recettes_table.php
│   ├── xxxx_create_ingredients_recette_table.php
│   ├── xxxx_add_credits_to_personnages.php
│   └── xxxx_add_capacite_soute_to_vaisseaux.php
└── seeders/
    ├── RessourceSeeder.php
    ├── RecetteSeeder.php
    └── MarcheSeeder.php (dans UniverseSeeder)
```

### Cycles Économiques Fonctionnels

**Cycle 1 : Extraction → Vente**
```
1. Scanner planète → Découvrir gisement
2. Extraire ressource → Ajouter à inventaire
3. Voyager vers marché
4. Vendre ressource → Obtenir crédits
```

**Cycle 2 : Achat → Transformation → Vente**
```
1. Acheter matières premières au marché
2. Produire matériaux intermédiaires/avancés
3. Vendre produits finis (valeur ajoutée)
4. Profit!
```

**Cycle 3 : Extraction → Transformation → Vente**
```
1. Extraire matières premières
2. Transformer en produits finis
3. Vendre pour maximiser profit
```

---

## 🎯 APRÈS PHASE 2

### Améliorations Futures

**Court terme (Phase 2.5) :**
- Production asynchrone (tâches en arrière-plan)
- Modules de production sur bases
- Dynamique offre/demande (prix fluctuants)
- Qualité des ressources

**Moyen terme (Phase 3) :**
- Commerce entre joueurs
- Contrats et missions économiques
- Guildes marchandes
- Marché noir et contrebande

**Long terme (Phase 4+) :**
- Économie planétaire
- Monnaies multiples
- Système bancaire (prêts, intérêts)
- Spéculation et bourse

---

## 📈 MÉTRIQUES DE SUCCÈS

**Phase 2 sera considérée réussie si :**

1. ✅ Un joueur peut scanner et découvrir des gisements
2. ✅ Un joueur peut extraire des ressources
3. ✅ Un joueur peut acheter/vendre au marché
4. ✅ Un joueur peut transformer des ressources
5. ✅ Le cycle économique complet fonctionne
6. ✅ Les prix et taxes s'appliquent correctement
7. ✅ L'inventaire et les crédits se gèrent correctement

---

**Document vivant - Dernière mise à jour : 2025-11-18**
