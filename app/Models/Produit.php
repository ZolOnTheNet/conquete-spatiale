<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Produit extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'type',
        'description',
        'volume_unite',
        'masse_unite',
        'prix_base',
        'illegal',
        'niveau_technologique',
    ];

    protected $casts = [
        'volume_unite' => 'float',
        'masse_unite' => 'float',
        'prix_base' => 'float',
        'illegal' => 'boolean',
        'niveau_technologique' => 'integer',
    ];

    /**
     * Stations vendant ce produit
     */
    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'marche_stations')
            ->withPivot([
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
            ])
            ->withTimestamps();
    }
}
