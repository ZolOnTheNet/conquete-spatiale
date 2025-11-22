<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemeStellaire extends Model
{
    protected $table = 'systemes_stellaires';

    protected $fillable = [
        'nom',
        'type_etoile',
        'couleur',
        'temperature',
        'puissance',
        'puissance_solaire',
        'detectabilite_base',
        'masse_solaire',
        'rayon_solaire',
        'secteur_x',
        'secteur_y',
        'secteur_z',
        'position_x',
        'position_y',
        'position_z',
        'nb_planetes',
        'explore',
        'habite',
        'poi_connu',
        'description',
        'donnees_supplementaires',
    ];

    protected $casts = [
        'explore' => 'boolean',
        'habite' => 'boolean',
        'poi_connu' => 'boolean',
        'donnees_supplementaires' => 'array',
    ];

    // Relations
    public function planetes(): HasMany
    {
        return $this->hasMany(Planete::class, 'systeme_stellaire_id');
    }

    // Méthodes utilitaires
    public function calculerDistance(SystemeStellaire $autre): float
    {
        $dx = ($this->secteur_x + $this->position_x) - ($autre->secteur_x + $autre->position_x);
        $dy = ($this->secteur_y + $this->position_y) - ($autre->secteur_y + $autre->position_y);
        $dz = ($this->secteur_z + $this->position_z) - ($autre->secteur_z + $autre->position_z);

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Retourne les propriétés de l'étoile selon sa classification
     */
    public static function getProprietesEtoile(string $type): array
    {
        return match ($type) {
            'O' => [
                'couleur' => 'Bleue',
                'temperature_min' => 25000,
                'temperature_max' => 50000,
                'puissance_min' => 150,
                'puissance_max' => 200,
                'masse_min' => 16,
                'masse_max' => 90,
            ],
            'B' => [
                'couleur' => 'Bleue-blanche',
                'temperature_min' => 10000,
                'temperature_max' => 25000,
                'puissance_min' => 100,
                'puissance_max' => 140,
                'masse_min' => 2.5,
                'masse_max' => 16,
            ],
            'A' => [
                'couleur' => 'Blanche',
                'temperature_min' => 7500,
                'temperature_max' => 10000,
                'puissance_min' => 80,
                'puissance_max' => 100,
                'masse_min' => 1.7,
                'masse_max' => 2.5,
            ],
            'F' => [
                'couleur' => 'Jaune-blanche',
                'temperature_min' => 6000,
                'temperature_max' => 7500,
                'puissance_min' => 60,
                'puissance_max' => 80,
                'masse_min' => 1.2,
                'masse_max' => 1.7,
            ],
            'G' => [
                'couleur' => 'Jaune',
                'temperature_min' => 5000,
                'temperature_max' => 6000,
                'puissance_min' => 40,
                'puissance_max' => 60,
                'masse_min' => 0.9,
                'masse_max' => 1.2,
            ],
            'K' => [
                'couleur' => 'Jaune-orange',
                'temperature_min' => 3500,
                'temperature_max' => 5000,
                'puissance_min' => 30,
                'puissance_max' => 40,
                'masse_min' => 0.6,
                'masse_max' => 0.9,
            ],
            'M' => [
                'couleur' => 'Rouge',
                'temperature_min' => 2000,
                'temperature_max' => 3500,
                'puissance_min' => 20,
                'puissance_max' => 30,
                'masse_min' => 0.1,
                'masse_max' => 0.6,
            ],
            default => [
                'couleur' => 'Jaune',
                'temperature_min' => 5000,
                'temperature_max' => 6000,
                'puissance_min' => 40,
                'puissance_max' => 60,
                'masse_min' => 0.9,
                'masse_max' => 1.2,
            ],
        };
    }

    /**
     * Distribution de Gauss des types d'étoiles (selon GDD)
     * Sur 20 étoiles: O:1, B:1, A:3, F:4, G:6, K:3, M:2
     */
    public static function genererTypeAleatoire(): string
    {
        $distribution = [
            'O' => 1,
            'B' => 1,
            'A' => 3,
            'F' => 4,
            'G' => 6,  // Le plus fréquent (type solaire)
            'K' => 3,
            'M' => 2,
        ];

        $pool = [];
        foreach ($distribution as $type => $count) {
            $pool = array_merge($pool, array_fill(0, $count, $type));
        }

        return $pool[array_rand($pool)];
    }
}
