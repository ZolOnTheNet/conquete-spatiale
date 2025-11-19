<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrixMarche extends Model
{
    protected $table = 'prix_marches';

    protected $fillable = [
        'marche_id',
        'ressource_id',
        'prix_achat',
        'prix_vente',
        'stock',
        'stock_max',
        'demande',
    ];

    protected $casts = [
        'prix_achat' => 'float',
        'prix_vente' => 'float',
        'stock' => 'integer',
        'stock_max' => 'integer',
        'demande' => 'float',
    ];

    /**
     * Le marché associé
     */
    public function marche(): BelongsTo
    {
        return $this->belongsTo(Marche::class);
    }

    /**
     * La ressource associée
     */
    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class);
    }

    /**
     * Est-ce que le stock est épuisé ?
     */
    public function isEpuise(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Est-ce que le stock est plein ?
     */
    public function isPlein(): bool
    {
        return $this->stock >= $this->stock_max;
    }

    /**
     * Pourcentage du stock
     */
    public function getPourcentageStock(): float
    {
        if ($this->stock_max <= 0) return 0;
        return round(($this->stock / $this->stock_max) * 100, 1);
    }

    /**
     * Ajuster les prix selon l'offre et la demande
     */
    public function ajusterPrix(): void
    {
        $ressource = $this->ressource;
        $prixBase = $ressource->prix_base;

        // Ratio stock/stock_max influence le prix
        $ratioStock = $this->stock_max > 0 ? $this->stock / $this->stock_max : 0;

        // Prix monte si stock bas, descend si stock haut
        $facteurStock = 1 + (0.5 - $ratioStock) * 0.5;

        // Appliquer la demande
        $facteurDemande = $this->demande;

        // Calculer nouveaux prix
        $this->prix_achat = max($prixBase * 0.5, $prixBase * $facteurStock * $facteurDemande);
        $this->prix_vente = max($prixBase * 0.4, $this->prix_achat * 0.85);

        $this->save();
    }
}
