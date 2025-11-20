<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'description',
        'type',
        'alignement',
        'relations',
        'couleur',
        'actif',
    ];

    protected $casts = [
        'relations' => 'array',
        'actif' => 'boolean',
    ];

    /**
     * Missions de cette faction
     */
    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    /**
     * Reputations avec cette faction
     */
    public function reputations(): HasMany
    {
        return $this->hasMany(Reputation::class);
    }

    /**
     * Obtenir le rang pour une valeur de reputation
     */
    public static function getRangPourReputation(int $valeur): string
    {
        return match(true) {
            $valeur <= -500 => 'hostile',
            $valeur <= -100 => 'inamical',
            $valeur < 100 => 'neutre',
            $valeur < 500 => 'amical',
            $valeur < 800 => 'apprecie',
            $valeur < 1000 => 'honore',
            default => 'venere',
        };
    }

    /**
     * Obtenir le modificateur de prix selon la reputation
     */
    public static function getModificateurPrix(string $rang): float
    {
        return match($rang) {
            'hostile' => 1.5,
            'inamical' => 1.2,
            'neutre' => 1.0,
            'amical' => 0.95,
            'apprecie' => 0.9,
            'honore' => 0.85,
            'venere' => 0.8,
            default => 1.0,
        };
    }
}
