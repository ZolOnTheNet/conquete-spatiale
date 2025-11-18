<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ressource extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'description',
        'poids_unitaire',
        'prix_base',
        'rarete',
    ];

    protected $casts = [
        'poids_unitaire' => 'decimal:3',
        'prix_base' => 'decimal:2',
        'rarete' => 'integer',
    ];

    /**
     * Gisements contenant cette ressource
     */
    public function gisements(): HasMany
    {
        return $this->hasMany(Gisement::class);
    }

    /**
     * Vérifier si ressource est rare
     */
    public function isRare(): bool
    {
        return $this->rarete < 30;
    }

    /**
     * Vérifier si ressource est exotique
     */
    public function isExotique(): bool
    {
        return $this->categorie === 'exotique';
    }

    /**
     * Calculer prix selon rareté
     */
    public function getPrixDynamique(float $multiplicateur = 1.0): float
    {
        // Prix de base inversement proportionnel à la rareté
        $facteur_rarete = (100 - $this->rarete) / 100;
        return round($this->prix_base * (1 + $facteur_rarete) * $multiplicateur, 2);
    }
}
