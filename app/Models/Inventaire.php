<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventaire extends Model
{
    protected $fillable = [
        'conteneur_type',
        'conteneur_id',
        'ressource_id',
        'quantite',
    ];

    protected $casts = [
        'quantite' => 'integer',
    ];

    /**
     * Conteneur (Vaisseau, Base, Personnage)
     */
    public function conteneur(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Ressource stockée
     */
    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class);
    }

    /**
     * Calculer poids total
     */
    public function getPoidsTotalAttribute(): float
    {
        return $this->quantite * $this->ressource->poids_unitaire;
    }

    /**
     * Calculer valeur totale
     */
    public function getValeurTotaleAttribute(): float
    {
        return $this->quantite * $this->ressource->prix_base;
    }
}
