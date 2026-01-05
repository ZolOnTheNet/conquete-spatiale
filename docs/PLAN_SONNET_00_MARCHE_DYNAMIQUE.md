# Plan Sonnet #00 : Système de Marché Dynamique

**PRIORITÉ : HAUTE - À exécuter en premier**
**Objectif :** Créer un système économique réaliste où les prix fluctuent selon l'offre et la demande

---

## Concept du système

### Principes fondamentaux

1. **Production** : Les modules industriels produisent des ressources
2. **Consommation** : Les modules/populations consomment des ressources
3. **Stock** : Différence entre production et consommation
4. **Prix dynamiques** : Basés sur le niveau de stock
   - Stock élevé → Prix bas (surplus)
   - Stock faible → Prix élevé (pénurie)
   - Stock nul → Non disponible à l'achat

### Ce que voit le joueur vs le MJ

| Joueur | MJ |
|--------|-----|
| Liste des ressources disponibles | Taux de production |
| Prix d'achat/vente | Taux de consommation |
| Quantité en stock (approximative) | Stock réel |
| - | Paramètres de régulation |
| - | Historique des transactions |

---

## Étape 1 : Créer la migration

**Commande :** `php artisan make:migration create_marche_economique_tables`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des stocks de marché (par station/planète)
        Schema::create('marche_stocks', function (Blueprint $table) {
            $table->id();

            // Localisation (station OU planète, pas les deux)
            $table->foreignId('station_id')->nullable()->constrained('stations')->onDelete('cascade');
            $table->foreignId('planete_id')->nullable()->constrained('planetes')->onDelete('cascade');

            // Ressource
            $table->foreignId('ressource_id')->constrained('ressources')->onDelete('cascade');

            // Stock actuel
            $table->decimal('stock_actuel', 15, 2)->default(0);
            $table->decimal('stock_max', 15, 2)->default(10000);

            // Production/Consommation par cycle (par heure par défaut)
            $table->decimal('production_par_cycle', 12, 2)->default(0); // Ce qui est produit
            $table->decimal('consommation_par_cycle', 12, 2)->default(0); // Ce qui est consommé

            // Prix de base (peut être différent du prix global de la ressource)
            $table->decimal('prix_base', 10, 2)->default(100);

            // Multiplicateurs de prix (configurables par MJ)
            $table->decimal('mult_prix_min', 5, 2)->default(0.5);  // Prix min = base * 0.5
            $table->decimal('mult_prix_max', 5, 2)->default(3.0);  // Prix max = base * 3.0

            // Seuils de stock (en %)
            $table->integer('seuil_surplus')->default(80);   // Au-dessus = surplus, prix bas
            $table->integer('seuil_penurie')->default(20);   // En-dessous = pénurie, prix haut
            $table->integer('seuil_indispo')->default(5);    // En-dessous = non achetable

            // Flags
            $table->boolean('achat_autorise')->default(true);   // Joueur peut acheter
            $table->boolean('vente_autorise')->default(true);   // Joueur peut vendre
            $table->boolean('visible')->default(true);          // Visible au joueur

            // Dernière mise à jour du cycle économique
            $table->timestamp('dernier_cycle_at')->nullable();

            $table->timestamps();

            // Contraintes
            $table->unique(['station_id', 'ressource_id']);
            $table->unique(['planete_id', 'ressource_id']);
            $table->index('ressource_id');
        });

        // Table des transactions (historique)
        Schema::create('marche_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('marche_stock_id')->constrained('marche_stocks')->onDelete('cascade');
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');

            $table->enum('type', ['achat', 'vente']);
            $table->decimal('quantite', 12, 2);
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('prix_total', 15, 2);

            // Stock avant/après pour audit
            $table->decimal('stock_avant', 15, 2);
            $table->decimal('stock_apres', 15, 2);

            $table->timestamps();

            $table->index(['marche_stock_id', 'created_at']);
            $table->index(['personnage_id', 'created_at']);
        });

        // Table de configuration globale du marché (pour MJ)
        Schema::create('marche_config', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insérer les configurations par défaut
        DB::table('marche_config')->insert([
            ['cle' => 'cycle_duree_minutes', 'valeur' => '60', 'description' => 'Durée d\'un cycle économique en minutes', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'volatilite_prix', 'valeur' => '0.1', 'description' => 'Facteur de volatilité des prix (0-1)', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'taxe_transaction', 'valeur' => '0.05', 'description' => 'Taxe sur les transactions (5%)', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'affichage_stock_precis', 'valeur' => 'false', 'description' => 'Afficher le stock précis aux joueurs', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('marche_config');
        Schema::dropIfExists('marche_transactions');
        Schema::dropIfExists('marche_stocks');
    }
};
```

---

## Étape 2 : Créer le modèle MarcheStock

**Fichier :** `app/Models/MarcheStock.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarcheStock extends Model
{
    use HasFactory;

    protected $table = 'marche_stocks';

    protected $fillable = [
        'station_id',
        'planete_id',
        'ressource_id',
        'stock_actuel',
        'stock_max',
        'production_par_cycle',
        'consommation_par_cycle',
        'prix_base',
        'mult_prix_min',
        'mult_prix_max',
        'seuil_surplus',
        'seuil_penurie',
        'seuil_indispo',
        'achat_autorise',
        'vente_autorise',
        'visible',
        'dernier_cycle_at',
    ];

    protected $casts = [
        'stock_actuel' => 'decimal:2',
        'stock_max' => 'decimal:2',
        'production_par_cycle' => 'decimal:2',
        'consommation_par_cycle' => 'decimal:2',
        'prix_base' => 'decimal:2',
        'mult_prix_min' => 'decimal:2',
        'mult_prix_max' => 'decimal:2',
        'achat_autorise' => 'boolean',
        'vente_autorise' => 'boolean',
        'visible' => 'boolean',
        'dernier_cycle_at' => 'datetime',
    ];

    // ===== RELATIONS =====

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function planete(): BelongsTo
    {
        return $this->belongsTo(Planete::class);
    }

    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MarcheTransaction::class);
    }

    // ===== ACCESSEURS =====

    /**
     * Obtenir le lieu (station ou planète)
     */
    public function getLieuAttribute(): ?Model
    {
        return $this->station ?? $this->planete;
    }

    /**
     * Nom du lieu pour affichage
     */
    public function getNomLieuAttribute(): string
    {
        return $this->lieu?->nom ?? 'Inconnu';
    }

    /**
     * Pourcentage de remplissage du stock
     */
    public function getPourcentageStockAttribute(): float
    {
        if ($this->stock_max <= 0) return 0;
        return round(($this->stock_actuel / $this->stock_max) * 100, 1);
    }

    /**
     * Balance production - consommation
     */
    public function getBalanceAttribute(): float
    {
        return $this->production_par_cycle - $this->consommation_par_cycle;
    }

    /**
     * Statut textuel du stock
     */
    public function getStatutStockAttribute(): string
    {
        $pct = $this->pourcentage_stock;

        if ($pct <= $this->seuil_indispo) return 'indisponible';
        if ($pct <= $this->seuil_penurie) return 'penurie';
        if ($pct >= $this->seuil_surplus) return 'surplus';
        return 'normal';
    }

    // ===== CALCUL DES PRIX =====

    /**
     * Calculer le prix d'achat actuel (joueur achète au marché)
     */
    public function getPrixAchatAttribute(): float
    {
        $pct = $this->pourcentage_stock;

        // Si sous le seuil d'indisponibilité, pas d'achat possible
        if ($pct <= $this->seuil_indispo) {
            return 0; // Signal: non disponible
        }

        // Calcul du multiplicateur basé sur le stock
        // Stock bas = prix élevé, Stock haut = prix bas
        $multiplicateur = $this->calculerMultiplicateurPrix($pct);

        return round($this->prix_base * $multiplicateur, 2);
    }

    /**
     * Calculer le prix de vente actuel (joueur vend au marché)
     */
    public function getPrixVenteAttribute(): float
    {
        $pct = $this->pourcentage_stock;

        // Si stock plein, le marché n'achète plus (ou très peu)
        if ($pct >= 100) {
            return round($this->prix_base * $this->mult_prix_min * 0.5, 2);
        }

        // Prix de vente = 70-80% du prix d'achat avec ajustement inverse
        // Quand le stock est bas, le marché paie mieux pour attirer la marchandise
        $multiplicateur = $this->calculerMultiplicateurPrixVente($pct);

        return round($this->prix_base * $multiplicateur * 0.75, 2);
    }

    /**
     * Calculer le multiplicateur de prix basé sur le pourcentage de stock
     */
    private function calculerMultiplicateurPrix(float $pctStock): float
    {
        // Interpolation linéaire entre les seuils
        // seuil_penurie (20%) -> mult_prix_max (3.0)
        // seuil_surplus (80%) -> mult_prix_min (0.5)

        if ($pctStock <= $this->seuil_penurie) {
            // Pénurie: prix maximum
            return $this->mult_prix_max;
        }

        if ($pctStock >= $this->seuil_surplus) {
            // Surplus: prix minimum
            return $this->mult_prix_min;
        }

        // Zone normale: interpolation
        $plage = $this->seuil_surplus - $this->seuil_penurie;
        $position = ($pctStock - $this->seuil_penurie) / $plage;

        return $this->mult_prix_max - ($position * ($this->mult_prix_max - $this->mult_prix_min));
    }

    /**
     * Multiplicateur pour prix de vente (inverse)
     */
    private function calculerMultiplicateurPrixVente(float $pctStock): float
    {
        // Quand stock bas, on paie mieux le joueur pour l'inciter à vendre
        if ($pctStock <= $this->seuil_penurie) {
            return $this->mult_prix_max * 0.9; // 90% du max
        }

        if ($pctStock >= $this->seuil_surplus) {
            return $this->mult_prix_min * 0.8; // On achète moins cher en surplus
        }

        // Zone normale
        $plage = $this->seuil_surplus - $this->seuil_penurie;
        $position = ($pctStock - $this->seuil_penurie) / $plage;

        return ($this->mult_prix_max * 0.9) - ($position * ($this->mult_prix_max * 0.9 - $this->mult_prix_min * 0.8));
    }

    // ===== VÉRIFICATIONS =====

    /**
     * Vérifie si le joueur peut acheter cette ressource
     */
    public function peutAcheter(float $quantite = 1): bool
    {
        if (!$this->achat_autorise) return false;
        if ($this->pourcentage_stock <= $this->seuil_indispo) return false;
        if ($this->stock_actuel < $quantite) return false;

        return true;
    }

    /**
     * Vérifie si le joueur peut vendre cette ressource
     */
    public function peutVendre(float $quantite = 1): bool
    {
        if (!$this->vente_autorise) return false;
        if ($this->stock_actuel + $quantite > $this->stock_max) return false;

        return true;
    }

    /**
     * Quantité maximale achetable
     */
    public function getQuantiteAchetableMaxAttribute(): float
    {
        if (!$this->achat_autorise) return 0;

        $seuilMin = ($this->seuil_indispo / 100) * $this->stock_max;
        return max(0, $this->stock_actuel - $seuilMin);
    }

    /**
     * Quantité maximale vendable (espace restant)
     */
    public function getQuantiteVendableMaxAttribute(): float
    {
        if (!$this->vente_autorise) return 0;

        return max(0, $this->stock_max - $this->stock_actuel);
    }

    // ===== DONNÉES POUR AFFICHAGE =====

    /**
     * Données pour le joueur (informations limitées)
     */
    public function pourJoueur(): array
    {
        $config = MarcheConfig::get('affichage_stock_precis', 'false');
        $stockPrecis = $config === 'true';

        return [
            'ressource_id' => $this->ressource_id,
            'ressource_nom' => $this->ressource->nom,
            'ressource_code' => $this->ressource->code ?? null,
            'stock_indicateur' => $this->getIndicateurStockJoueur(),
            'stock_quantite' => $stockPrecis ? $this->stock_actuel : null,
            'prix_achat' => $this->peutAcheter() ? $this->prix_achat : null,
            'prix_vente' => $this->peutVendre() ? $this->prix_vente : null,
            'peut_acheter' => $this->peutAcheter(),
            'peut_vendre' => $this->peutVendre(),
            'quantite_max_achat' => $this->quantite_achetable_max,
        ];
    }

    /**
     * Indicateur de stock pour le joueur (pas le chiffre exact)
     */
    private function getIndicateurStockJoueur(): string
    {
        $pct = $this->pourcentage_stock;

        if ($pct <= $this->seuil_indispo) return 'Épuisé';
        if ($pct <= $this->seuil_penurie) return 'Rare';
        if ($pct <= 40) return 'Limité';
        if ($pct <= 60) return 'Disponible';
        if ($pct <= $this->seuil_surplus) return 'Abondant';
        return 'Surplus';
    }

    /**
     * Données complètes pour le MJ
     */
    public function pourMJ(): array
    {
        return [
            'id' => $this->id,
            'lieu' => $this->nom_lieu,
            'lieu_type' => $this->station_id ? 'station' : 'planete',
            'ressource_id' => $this->ressource_id,
            'ressource_nom' => $this->ressource->nom,
            'stock_actuel' => $this->stock_actuel,
            'stock_max' => $this->stock_max,
            'pourcentage' => $this->pourcentage_stock,
            'statut' => $this->statut_stock,
            'production' => $this->production_par_cycle,
            'consommation' => $this->consommation_par_cycle,
            'balance' => $this->balance,
            'prix_base' => $this->prix_base,
            'prix_achat' => $this->prix_achat,
            'prix_vente' => $this->prix_vente,
            'mult_min' => $this->mult_prix_min,
            'mult_max' => $this->mult_prix_max,
            'achat_autorise' => $this->achat_autorise,
            'vente_autorise' => $this->vente_autorise,
            'dernier_cycle' => $this->dernier_cycle_at,
        ];
    }
}
```

---

## Étape 3 : Créer le modèle MarcheTransaction

**Fichier :** `app/Models/MarcheTransaction.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarcheTransaction extends Model
{
    protected $table = 'marche_transactions';

    protected $fillable = [
        'marche_stock_id',
        'personnage_id',
        'type',
        'quantite',
        'prix_unitaire',
        'prix_total',
        'stock_avant',
        'stock_apres',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'prix_total' => 'decimal:2',
        'stock_avant' => 'decimal:2',
        'stock_apres' => 'decimal:2',
    ];

    public function marcheStock(): BelongsTo
    {
        return $this->belongsTo(MarcheStock::class);
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function estAchat(): bool
    {
        return $this->type === 'achat';
    }

    public function estVente(): bool
    {
        return $this->type === 'vente';
    }
}
```

---

## Étape 4 : Créer le modèle MarcheConfig

**Fichier :** `app/Models/MarcheConfig.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarcheConfig extends Model
{
    protected $table = 'marche_config';

    protected $fillable = ['cle', 'valeur', 'description'];

    /**
     * Obtenir une valeur de configuration
     */
    public static function get(string $cle, $default = null)
    {
        return Cache::remember("marche_config_{$cle}", 3600, function () use ($cle, $default) {
            $config = static::where('cle', $cle)->first();
            return $config ? $config->valeur : $default;
        });
    }

    /**
     * Définir une valeur de configuration
     */
    public static function set(string $cle, $valeur, ?string $description = null): void
    {
        static::updateOrCreate(
            ['cle' => $cle],
            ['valeur' => (string) $valeur, 'description' => $description]
        );

        Cache::forget("marche_config_{$cle}");
    }

    /**
     * Obtenir toutes les configurations
     */
    public static function all(): array
    {
        return static::pluck('valeur', 'cle')->toArray();
    }
}
```

---

## Étape 5 : Créer le service MarcheEconomiqueService

**Fichier :** `app/Services/MarcheEconomiqueService.php`

```php
<?php

namespace App\Services;

use App\Models\MarcheStock;
use App\Models\MarcheTransaction;
use App\Models\MarcheConfig;
use App\Models\Personnage;
use App\Models\Station;
use App\Models\Planete;
use App\Models\Ressource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarcheEconomiqueService
{
    /**
     * Obtenir le marché d'une station
     */
    public function getMarcheStation(Station $station, bool $pourMJ = false): array
    {
        $stocks = MarcheStock::where('station_id', $station->id)
            ->where('visible', true)
            ->with('ressource')
            ->get();

        return $stocks->map(fn($s) => $pourMJ ? $s->pourMJ() : $s->pourJoueur())->toArray();
    }

    /**
     * Obtenir le marché d'une planète
     */
    public function getMarchePlanete(Planete $planete, bool $pourMJ = false): array
    {
        $stocks = MarcheStock::where('planete_id', $planete->id)
            ->where('visible', true)
            ->with('ressource')
            ->get();

        return $stocks->map(fn($s) => $pourMJ ? $s->pourMJ() : $s->pourJoueur())->toArray();
    }

    /**
     * Acheter une ressource (joueur achète au marché)
     */
    public function acheter(
        Personnage $personnage,
        MarcheStock $stock,
        float $quantite
    ): array {
        // Vérifications
        if (!$stock->peutAcheter($quantite)) {
            return [
                'success' => false,
                'message' => 'Achat impossible : stock insuffisant ou achat non autorisé.',
            ];
        }

        $prixUnitaire = $stock->prix_achat;
        $taxe = (float) MarcheConfig::get('taxe_transaction', '0.05');
        $prixTotal = round($prixUnitaire * $quantite * (1 + $taxe), 2);

        // Vérifier crédits
        if ($personnage->credits < $prixTotal) {
            return [
                'success' => false,
                'message' => "Crédits insuffisants. Besoin de " . number_format($prixTotal, 2) . " Cr.",
            ];
        }

        // Vérifier capacité de soute
        $vaisseau = $personnage->vaisseauActif;
        if ($vaisseau) {
            $poidsTotal = $quantite * ($stock->ressource->masse_unitaire ?? 1);
            $capaciteRestante = $vaisseau->capacite_soute - ($vaisseau->chargement_actuel ?? 0);

            if ($poidsTotal > $capaciteRestante) {
                return [
                    'success' => false,
                    'message' => 'Capacité de soute insuffisante.',
                ];
            }
        }

        return DB::transaction(function () use ($personnage, $stock, $quantite, $prixUnitaire, $prixTotal, $vaisseau) {
            $stockAvant = $stock->stock_actuel;

            // Déduire du stock
            $stock->stock_actuel -= $quantite;
            $stock->save();

            // Déduire les crédits
            $personnage->credits -= $prixTotal;
            $personnage->save();

            // Ajouter à la soute du vaisseau
            if ($vaisseau) {
                $this->ajouterAuVaisseau($vaisseau, $stock->ressource_id, $quantite);
            }

            // Enregistrer la transaction
            MarcheTransaction::create([
                'marche_stock_id' => $stock->id,
                'personnage_id' => $personnage->id,
                'type' => 'achat',
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'prix_total' => $prixTotal,
                'stock_avant' => $stockAvant,
                'stock_apres' => $stock->stock_actuel,
            ]);

            Log::info("Marché: Achat", [
                'personnage' => $personnage->id,
                'ressource' => $stock->ressource->nom,
                'quantite' => $quantite,
                'prix' => $prixTotal,
            ]);

            return [
                'success' => true,
                'message' => "Achat de {$quantite} {$stock->ressource->nom} pour " . number_format($prixTotal, 2) . " Cr.",
                'transaction' => [
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'prix_total' => $prixTotal,
                    'nouveau_solde' => $personnage->credits,
                ],
            ];
        });
    }

    /**
     * Vendre une ressource (joueur vend au marché)
     */
    public function vendre(
        Personnage $personnage,
        MarcheStock $stock,
        float $quantite
    ): array {
        // Vérifications
        if (!$stock->peutVendre($quantite)) {
            return [
                'success' => false,
                'message' => 'Vente impossible : stock marché plein ou vente non autorisée.',
            ];
        }

        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return [
                'success' => false,
                'message' => 'Aucun vaisseau actif.',
            ];
        }

        // Vérifier que le joueur a la ressource
        $quantiteEnSoute = $this->getQuantiteEnSoute($vaisseau, $stock->ressource_id);
        if ($quantiteEnSoute < $quantite) {
            return [
                'success' => false,
                'message' => "Vous n'avez que {$quantiteEnSoute} unités de {$stock->ressource->nom}.",
            ];
        }

        $prixUnitaire = $stock->prix_vente;
        $taxe = (float) MarcheConfig::get('taxe_transaction', '0.05');
        $prixTotal = round($prixUnitaire * $quantite * (1 - $taxe), 2); // Taxe déduite

        return DB::transaction(function () use ($personnage, $stock, $quantite, $prixUnitaire, $prixTotal, $vaisseau) {
            $stockAvant = $stock->stock_actuel;

            // Ajouter au stock
            $stock->stock_actuel += $quantite;
            $stock->save();

            // Ajouter les crédits
            $personnage->credits += $prixTotal;
            $personnage->save();

            // Retirer de la soute
            $this->retirerDuVaisseau($vaisseau, $stock->ressource_id, $quantite);

            // Enregistrer la transaction
            MarcheTransaction::create([
                'marche_stock_id' => $stock->id,
                'personnage_id' => $personnage->id,
                'type' => 'vente',
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'prix_total' => $prixTotal,
                'stock_avant' => $stockAvant,
                'stock_apres' => $stock->stock_actuel,
            ]);

            Log::info("Marché: Vente", [
                'personnage' => $personnage->id,
                'ressource' => $stock->ressource->nom,
                'quantite' => $quantite,
                'prix' => $prixTotal,
            ]);

            return [
                'success' => true,
                'message' => "Vente de {$quantite} {$stock->ressource->nom} pour " . number_format($prixTotal, 2) . " Cr.",
                'transaction' => [
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'prix_total' => $prixTotal,
                    'nouveau_solde' => $personnage->credits,
                ],
            ];
        });
    }

    /**
     * Exécuter un cycle économique (production/consommation)
     * À appeler via une commande artisan programmée
     */
    public function executerCycleEconomique(): array
    {
        $stocks = MarcheStock::all();
        $resultats = [];

        foreach ($stocks as $stock) {
            $ancienStock = $stock->stock_actuel;

            // Appliquer production
            $stock->stock_actuel += $stock->production_par_cycle;

            // Appliquer consommation
            $stock->stock_actuel -= $stock->consommation_par_cycle;

            // Borner entre 0 et max
            $stock->stock_actuel = max(0, min($stock->stock_max, $stock->stock_actuel));

            $stock->dernier_cycle_at = now();
            $stock->save();

            $resultats[] = [
                'lieu' => $stock->nom_lieu,
                'ressource' => $stock->ressource->nom,
                'avant' => $ancienStock,
                'apres' => $stock->stock_actuel,
                'variation' => $stock->stock_actuel - $ancienStock,
            ];
        }

        Log::info("Cycle économique exécuté", ['stocks_maj' => count($resultats)]);

        return $resultats;
    }

    /**
     * Initialiser le marché pour une station avec toutes les ressources
     */
    public function initialiserMarcheStation(Station $station, array $options = []): void
    {
        $ressources = Ressource::all();

        foreach ($ressources as $ressource) {
            MarcheStock::firstOrCreate(
                [
                    'station_id' => $station->id,
                    'ressource_id' => $ressource->id,
                ],
                [
                    'stock_actuel' => $options['stock_initial'] ?? 500,
                    'stock_max' => $options['stock_max'] ?? 10000,
                    'production_par_cycle' => $options['production'] ?? 10,
                    'consommation_par_cycle' => $options['consommation'] ?? 8,
                    'prix_base' => $ressource->prix_base ?? 100,
                    'visible' => true,
                ]
            );
        }
    }

    /**
     * Ajouter ressource au vaisseau (à adapter selon votre modèle Vaisseau)
     */
    private function ajouterAuVaisseau($vaisseau, int $ressourceId, float $quantite): void
    {
        // TODO: Adapter selon votre système de cargaison
        // Exemple avec une table pivot ou JSON
        $cargaison = $vaisseau->cargaison ?? [];
        $cargaison[$ressourceId] = ($cargaison[$ressourceId] ?? 0) + $quantite;
        $vaisseau->cargaison = $cargaison;
        $vaisseau->save();
    }

    /**
     * Retirer ressource du vaisseau
     */
    private function retirerDuVaisseau($vaisseau, int $ressourceId, float $quantite): void
    {
        $cargaison = $vaisseau->cargaison ?? [];
        $cargaison[$ressourceId] = max(0, ($cargaison[$ressourceId] ?? 0) - $quantite);
        if ($cargaison[$ressourceId] <= 0) {
            unset($cargaison[$ressourceId]);
        }
        $vaisseau->cargaison = $cargaison;
        $vaisseau->save();
    }

    /**
     * Obtenir quantité en soute
     */
    private function getQuantiteEnSoute($vaisseau, int $ressourceId): float
    {
        $cargaison = $vaisseau->cargaison ?? [];
        return $cargaison[$ressourceId] ?? 0;
    }

    // ===== MÉTHODES MJ =====

    /**
     * Ajuster manuellement le stock (MJ)
     */
    public function ajusterStock(MarcheStock $stock, float $quantite, string $raison = ''): void
    {
        $stock->stock_actuel = max(0, min($stock->stock_max, $quantite));
        $stock->save();

        Log::info("MJ: Ajustement stock", [
            'stock_id' => $stock->id,
            'nouvelle_valeur' => $quantite,
            'raison' => $raison,
        ]);
    }

    /**
     * Ajuster les paramètres de prix (MJ)
     */
    public function ajusterPrix(MarcheStock $stock, array $params): void
    {
        $stock->update([
            'prix_base' => $params['prix_base'] ?? $stock->prix_base,
            'mult_prix_min' => $params['mult_min'] ?? $stock->mult_prix_min,
            'mult_prix_max' => $params['mult_max'] ?? $stock->mult_prix_max,
        ]);
    }

    /**
     * Ajuster production/consommation (MJ)
     */
    public function ajusterFlux(MarcheStock $stock, float $production, float $consommation): void
    {
        $stock->update([
            'production_par_cycle' => $production,
            'consommation_par_cycle' => $consommation,
        ]);
    }

    /**
     * Rapport économique pour MJ
     */
    public function genererRapportEconomique(): array
    {
        $stocks = MarcheStock::with(['ressource', 'station', 'planete'])->get();

        $rapport = [
            'resume' => [
                'total_stocks' => $stocks->count(),
                'en_penurie' => $stocks->where('statut_stock', 'penurie')->count(),
                'en_surplus' => $stocks->where('statut_stock', 'surplus')->count(),
                'indisponibles' => $stocks->where('statut_stock', 'indisponible')->count(),
            ],
            'alertes' => [],
            'par_ressource' => [],
        ];

        foreach ($stocks->groupBy('ressource_id') as $ressourceId => $stocksRessource) {
            $ressource = $stocksRessource->first()->ressource;
            $stockTotal = $stocksRessource->sum('stock_actuel');
            $capaciteTotal = $stocksRessource->sum('stock_max');
            $balanceTotal = $stocksRessource->sum('balance');

            $rapport['par_ressource'][$ressource->nom] = [
                'stock_total' => $stockTotal,
                'capacite_totale' => $capaciteTotal,
                'pourcentage' => $capaciteTotal > 0 ? round(($stockTotal / $capaciteTotal) * 100, 1) : 0,
                'balance_globale' => $balanceTotal,
                'tendance' => $balanceTotal > 0 ? 'croissant' : ($balanceTotal < 0 ? 'decroissant' : 'stable'),
            ];

            // Alertes
            foreach ($stocksRessource as $stock) {
                if ($stock->statut_stock === 'penurie') {
                    $rapport['alertes'][] = "⚠️ Pénurie de {$ressource->nom} à {$stock->nom_lieu}";
                }
                if ($stock->balance < -50) {
                    $rapport['alertes'][] = "📉 {$ressource->nom} à {$stock->nom_lieu}: consommation excessive";
                }
            }
        }

        return $rapport;
    }
}
```

---

## Étape 6 : Créer la commande Artisan pour le cycle économique

**Fichier :** `app/Console/Commands/MarcheCycleCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Services\MarcheEconomiqueService;
use Illuminate\Console\Command;

class MarcheCycleCommand extends Command
{
    protected $signature = 'marche:cycle';
    protected $description = 'Exécute un cycle économique (production/consommation)';

    public function handle(MarcheEconomiqueService $service): int
    {
        $this->info('Exécution du cycle économique...');

        $resultats = $service->executerCycleEconomique();

        $this->info(count($resultats) . ' stocks mis à jour.');

        // Afficher les variations significatives
        foreach ($resultats as $r) {
            if (abs($r['variation']) > 10) {
                $signe = $r['variation'] > 0 ? '+' : '';
                $this->line("  {$r['lieu']} - {$r['ressource']}: {$signe}{$r['variation']}");
            }
        }

        return Command::SUCCESS;
    }
}
```

**Planifier dans `app/Console/Kernel.php` ou `routes/console.php` (Laravel 11) :**

```php
// routes/console.php (Laravel 11)
use Illuminate\Support\Facades\Schedule;

Schedule::command('marche:cycle')->hourly();
```

---

## Étape 7 : Contrôleur Admin pour le MJ

**Fichier :** `app/Http/Controllers/Admin/AdminMarcheController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarcheStock;
use App\Models\MarcheConfig;
use App\Models\Station;
use App\Models\Ressource;
use App\Services\MarcheEconomiqueService;
use Illuminate\Http\Request;

class AdminMarcheController extends Controller
{
    public function __construct(
        private MarcheEconomiqueService $marcheService
    ) {}

    /**
     * Vue principale - rapport économique
     */
    public function index()
    {
        $rapport = $this->marcheService->genererRapportEconomique();
        $configs = MarcheConfig::all();

        return view('admin.marche.index', compact('rapport', 'configs'));
    }

    /**
     * Liste des stocks d'une station
     */
    public function station(Station $station)
    {
        $stocks = MarcheStock::where('station_id', $station->id)
            ->with('ressource')
            ->orderBy('ressource_id')
            ->get()
            ->map(fn($s) => $s->pourMJ());

        return view('admin.marche.station', compact('station', 'stocks'));
    }

    /**
     * Modifier un stock
     */
    public function updateStock(Request $request, MarcheStock $stock)
    {
        $validated = $request->validate([
            'stock_actuel' => 'required|numeric|min:0',
            'stock_max' => 'required|numeric|min:1',
            'production_par_cycle' => 'required|numeric|min:0',
            'consommation_par_cycle' => 'required|numeric|min:0',
            'prix_base' => 'required|numeric|min:0.01',
            'mult_prix_min' => 'required|numeric|min:0.1|max:1',
            'mult_prix_max' => 'required|numeric|min:1|max:10',
            'seuil_surplus' => 'required|integer|min:50|max:100',
            'seuil_penurie' => 'required|integer|min:5|max:49',
            'seuil_indispo' => 'required|integer|min:0|max:20',
            'achat_autorise' => 'boolean',
            'vente_autorise' => 'boolean',
            'visible' => 'boolean',
        ]);

        $validated['achat_autorise'] = $request->has('achat_autorise');
        $validated['vente_autorise'] = $request->has('vente_autorise');
        $validated['visible'] = $request->has('visible');

        $stock->update($validated);

        return back()->with('success', 'Stock mis à jour.');
    }

    /**
     * Exécuter un cycle manuellement
     */
    public function executerCycle()
    {
        $resultats = $this->marcheService->executerCycleEconomique();

        return back()->with('success', count($resultats) . ' stocks mis à jour.');
    }

    /**
     * Initialiser le marché d'une station
     */
    public function initialiser(Request $request, Station $station)
    {
        $this->marcheService->initialiserMarcheStation($station, [
            'stock_initial' => $request->input('stock_initial', 500),
            'stock_max' => $request->input('stock_max', 10000),
        ]);

        return back()->with('success', "Marché de {$station->nom} initialisé.");
    }

    /**
     * Modifier la configuration globale
     */
    public function updateConfig(Request $request)
    {
        foreach ($request->input('config', []) as $cle => $valeur) {
            MarcheConfig::set($cle, $valeur);
        }

        return back()->with('success', 'Configuration mise à jour.');
    }
}
```

---

## Étape 8 : Routes admin

**Ajouter à `routes/web.php` dans le groupe admin :**

```php
use App\Http\Controllers\Admin\AdminMarcheController;

// Dans le groupe admin
Route::prefix('marche')->name('marche.')->group(function () {
    Route::get('/', [AdminMarcheController::class, 'index'])->name('index');
    Route::get('/station/{station}', [AdminMarcheController::class, 'station'])->name('station');
    Route::put('/stock/{stock}', [AdminMarcheController::class, 'updateStock'])->name('stock.update');
    Route::post('/cycle', [AdminMarcheController::class, 'executerCycle'])->name('cycle');
    Route::post('/initialiser/{station}', [AdminMarcheController::class, 'initialiser'])->name('initialiser');
    Route::post('/config', [AdminMarcheController::class, 'updateConfig'])->name('config');
});
```

---

## Étape 9 : Vue admin principale

**Fichier :** `resources/views/admin/marche/index.blade.php`

```blade
@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Économie & Marchés</h1>
        <form action="{{ route('admin.marche.cycle') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">
                Exécuter un cycle économique
            </button>
        </form>
    </div>

    <!-- Alertes -->
    @if(!empty($rapport['alertes']))
    <div class="bg-yellow-900 border border-yellow-600 rounded-lg p-4 mb-6">
        <h3 class="font-bold text-yellow-400 mb-2">Alertes économiques</h3>
        <ul class="text-sm">
            @foreach($rapport['alertes'] as $alerte)
            <li>{{ $alerte }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Résumé -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-800 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-blue-400">{{ $rapport['resume']['total_stocks'] }}</div>
            <div class="text-sm text-gray-400">Stocks totaux</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-green-400">{{ $rapport['resume']['en_surplus'] }}</div>
            <div class="text-sm text-gray-400">En surplus</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-yellow-400">{{ $rapport['resume']['en_penurie'] }}</div>
            <div class="text-sm text-gray-400">En pénurie</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-red-400">{{ $rapport['resume']['indisponibles'] }}</div>
            <div class="text-sm text-gray-400">Indisponibles</div>
        </div>
    </div>

    <!-- Par ressource -->
    <div class="bg-gray-800 rounded-lg overflow-hidden mb-6">
        <h3 class="bg-gray-700 px-4 py-3 font-bold">État par ressource</h3>
        <table class="w-full">
            <thead class="bg-gray-750">
                <tr>
                    <th class="px-4 py-2 text-left">Ressource</th>
                    <th class="px-4 py-2 text-left">Stock global</th>
                    <th class="px-4 py-2 text-left">Remplissage</th>
                    <th class="px-4 py-2 text-left">Balance</th>
                    <th class="px-4 py-2 text-left">Tendance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rapport['par_ressource'] as $nom => $data)
                <tr class="border-b border-gray-700">
                    <td class="px-4 py-2 font-medium">{{ $nom }}</td>
                    <td class="px-4 py-2">{{ number_format($data['stock_total']) }} / {{ number_format($data['capacite_totale']) }}</td>
                    <td class="px-4 py-2">
                        <div class="w-full bg-gray-600 rounded h-2">
                            <div class="h-2 rounded {{ $data['pourcentage'] < 20 ? 'bg-red-500' : ($data['pourcentage'] > 80 ? 'bg-green-500' : 'bg-blue-500') }}"
                                 style="width: {{ $data['pourcentage'] }}%"></div>
                        </div>
                        <span class="text-xs">{{ $data['pourcentage'] }}%</span>
                    </td>
                    <td class="px-4 py-2 {{ $data['balance_globale'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $data['balance_globale'] >= 0 ? '+' : '' }}{{ $data['balance_globale'] }}/cycle
                    </td>
                    <td class="px-4 py-2">
                        @if($data['tendance'] === 'croissant')
                            <span class="text-green-400">↗ Croissant</span>
                        @elseif($data['tendance'] === 'decroissant')
                            <span class="text-red-400">↘ Décroissant</span>
                        @else
                            <span class="text-gray-400">→ Stable</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Configuration -->
    <div class="bg-gray-800 rounded-lg p-4">
        <h3 class="font-bold mb-4">Configuration globale</h3>
        <form action="{{ route('admin.marche.config') }}" method="POST" class="grid grid-cols-2 gap-4">
            @csrf
            @foreach($configs as $config)
            <div>
                <label class="block text-sm text-gray-400 mb-1">{{ $config->description ?? $config->cle }}</label>
                <input type="text" name="config[{{ $config->cle }}]" value="{{ $config->valeur }}"
                    class="w-full bg-gray-700 rounded px-3 py-2">
            </div>
            @endforeach
            <div class="col-span-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">
                    Sauvegarder
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

## Vérification

```bash
php artisan make:migration create_marche_economique_tables
php artisan migrate

php -l app/Models/MarcheStock.php
php -l app/Models/MarcheTransaction.php
php -l app/Models/MarcheConfig.php
php -l app/Services/MarcheEconomiqueService.php
php -l app/Http/Controllers/Admin/AdminMarcheController.php
php -l app/Console/Commands/MarcheCycleCommand.php

php artisan route:list --path=admin/marche
```

---

## Critères de succès

- [ ] Tables créées : `marche_stocks`, `marche_transactions`, `marche_config`
- [ ] Prix dynamiques calculés correctement
- [ ] Cycle économique fonctionnel
- [ ] Interface MJ avec rapport
- [ ] Joueur voit les indicateurs (pas les chiffres exacts)

---

*Plan créé le 3 janvier 2026 pour Claude Code Sonnet*
