<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarcheStation extends Model
{
    protected $table = 'marche_stations';

    protected $fillable = [
        'station_id',
        'produit_id',
        'stock_actuel',
        'stock_min',
        'stock_max',
        'production_par_jour',
        'consommation_par_jour',
        'type_economique',
        'prix_achat_joueur',
        'prix_vente_joueur',
        'derniere_mise_a_jour_prix',
        'disponible_achat',
        'disponible_vente',
    ];

    protected $casts = [
        'stock_actuel' => 'integer',
        'stock_min' => 'integer',
        'stock_max' => 'integer',
        'production_par_jour' => 'float',
        'consommation_par_jour' => 'float',
        'prix_achat_joueur' => 'float',
        'prix_vente_joueur' => 'float',
        'derniere_mise_a_jour_prix' => 'datetime',
        'disponible_achat' => 'boolean',
        'disponible_vente' => 'boolean',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Calculer et mettre à jour les prix selon l'offre et la demande
     *
     * Logique :
     * - PRODUCTEUR (prod > conso) : Prix vente BAS, prix achat TRÈS BAS
     * - CONSOMMATEUR (conso > prod) : Prix vente TRÈS ÉLEVÉ, prix achat ÉLEVÉ
     * - Stock élevé : Prix baisse
     * - Stock faible : Prix monte
     */
    public function calculerPrix(): void
    {
        $produit = $this->produit;
        $prixBase = $produit->prix_base;

        // Déterminer le type économique
        $this->determinerTypeEconomique();

        // Ratio de stock (0.0 = vide, 1.0 = plein)
        $ratioStock = $this->stock_max > 0
            ? $this->stock_actuel / $this->stock_max
            : 0.5;

        // Ratio production/consommation
        $totalFlux = $this->production_par_jour + $this->consommation_par_jour;
        $ratioProduction = $totalFlux > 0
            ? $this->production_par_jour / $totalFlux
            : 0.5;

        // Modificateurs selon type économique
        switch ($this->type_economique) {
            case 'producteur':
                // Produit beaucoup, vend pas cher, achète très peu cher
                $modifVente = 0.7;  // -30% sur prix vente
                $modifAchat = 0.4;  // -60% sur prix achat
                break;

            case 'consommateur':
                // Consomme beaucoup, vend très cher, achète cher
                $modifVente = 1.8;  // +80% sur prix vente
                $modifAchat = 1.3;  // +30% sur prix achat
                break;

            case 'equilibre':
                // Production ≈ Consommation, prix moyens
                $modifVente = 1.1;
                $modifAchat = 0.8;
                break;

            case 'transit':
            default:
                // Ni production ni consommation, prix standard
                $modifVente = 1.2;
                $modifAchat = 0.7;
                break;
        }

        // Ajustement selon le stock
        // Stock bas = prix monte, stock haut = prix descend
        $ajustementStock = 1.0 + (0.5 - $ratioStock) * 0.8; // Entre 0.6 et 1.4

        // Calcul des prix finaux
        $this->prix_vente_joueur = round($prixBase * $modifVente * $ajustementStock, 2);
        $this->prix_achat_joueur = round($prixBase * $modifAchat * $ajustementStock, 2);

        // S'assurer que prix achat < prix vente (la station fait une marge)
        if ($this->prix_achat_joueur >= $this->prix_vente_joueur) {
            $this->prix_achat_joueur = round($this->prix_vente_joueur * 0.7, 2);
        }

        $this->derniere_mise_a_jour_prix = now();
    }

    /**
     * Déterminer le type économique basé sur production/consommation
     */
    protected function determinerTypeEconomique(): void
    {
        $prod = $this->production_par_jour;
        $conso = $this->consommation_par_jour;

        if ($prod == 0 && $conso == 0) {
            $this->type_economique = 'transit';
        } elseif ($prod > $conso * 1.5) {
            $this->type_economique = 'producteur';
        } elseif ($conso > $prod * 1.5) {
            $this->type_economique = 'consommateur';
        } else {
            $this->type_economique = 'equilibre';
        }
    }

    /**
     * Simuler l'écoulement du temps (production/consommation)
     */
    public function simulerJour(): void
    {
        // Production
        $this->stock_actuel += (int)$this->production_par_jour;

        // Consommation
        $this->stock_actuel -= (int)$this->consommation_par_jour;

        // Limites
        if ($this->stock_actuel < 0) {
            $this->stock_actuel = 0;
        }
        if ($this->stock_actuel > $this->stock_max) {
            $this->stock_actuel = $this->stock_max;
        }

        // Recalculer les prix
        $this->calculerPrix();
    }

    /**
     * Acheter au joueur (joueur vend à la station)
     */
    public function acheterAuJoueur(int $quantite): array
    {
        if (!$this->disponible_achat) {
            return [
                'success' => false,
                'message' => 'La station n\'achète pas ce produit.',
            ];
        }

        $espaceDisponible = $this->stock_max - $this->stock_actuel;

        if ($quantite > $espaceDisponible) {
            return [
                'success' => false,
                'message' => "La station n'a pas assez d'espace (max: {$espaceDisponible} unités).",
            ];
        }

        $coutTotal = $this->prix_achat_joueur * $quantite;

        $this->stock_actuel += $quantite;
        $this->calculerPrix();
        $this->save();

        return [
            'success' => true,
            'quantite' => $quantite,
            'prix_unitaire' => $this->prix_achat_joueur,
            'total' => $coutTotal,
        ];
    }

    /**
     * Vendre au joueur (joueur achète à la station)
     */
    public function vendreAuJoueur(int $quantite): array
    {
        if (!$this->disponible_vente) {
            return [
                'success' => false,
                'message' => 'La station ne vend pas ce produit.',
            ];
        }

        if ($quantite > $this->stock_actuel) {
            return [
                'success' => false,
                'message' => "Stock insuffisant (disponible: {$this->stock_actuel} unités).",
            ];
        }

        $coutTotal = $this->prix_vente_joueur * $quantite;

        $this->stock_actuel -= $quantite;
        $this->calculerPrix();
        $this->save();

        return [
            'success' => true,
            'quantite' => $quantite,
            'prix_unitaire' => $this->prix_vente_joueur,
            'total' => $coutTotal,
        ];
    }
}
