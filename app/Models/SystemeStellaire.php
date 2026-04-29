<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemeStellaire extends Model
{
    protected $table = 'systemes_stellaires';

    protected $fillable = [
        'nom',
        'nom_commun',
        'noms_alternatifs',
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
        'source_gaia',
        'gaia_source_id',
        'gaia_ra',
        'gaia_dec',
        'gaia_distance_ly',
    ];

    protected $casts = [
        'explore' => 'boolean',
        'habite' => 'boolean',
        'poi_connu' => 'boolean',
        'source_gaia' => 'boolean',
        'donnees_supplementaires' => 'array',
        'noms_alternatifs' => 'array',
    ];

    // Relations
    public function planetes(): HasMany
    {
        return $this->hasMany(Planete::class, 'systeme_stellaire_id');
    }

    /** Planètes primaires uniquement (exclut les lunes) */
    public function planetesPrimaires(): HasMany
    {
        return $this->hasMany(Planete::class, 'systeme_stellaire_id')
            ->where('categorie', 'planete');
    }

    // Méthodes utilitaires
    public function calculerDistance(SystemeStellaire $autre): float
    {
        // RÈGLE INTER-SECTEUR: Pour les systèmes stellaires, on utilise SEULEMENT les secteurs (AL)
        // Les positions dans le secteur sont ignorées pour la détection inter-système
        $dx = $this->secteur_x - $autre->secteur_x;
        $dy = $this->secteur_y - $autre->secteur_y;
        $dz = $this->secteur_z - $autre->secteur_z;

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Calcule le score de détection depuis une position donnée
     * RÈGLE INTER-SECTEUR: Utilise SEULEMENT les secteurs (AL), ignore les positions
     * Formule: (distance_AL / 10) × detectabilite_base
     *
     * @param int $fromSecteurX Secteur X du vaisseau (en AL)
     * @param int $fromSecteurY Secteur Y du vaisseau (en AL)
     * @param int $fromSecteurZ Secteur Z du vaisseau (en AL)
     * @param int $fromPositionX Position X du vaisseau (en cUA) - NON UTILISÉ pour systèmes
     * @param int $fromPositionY Position Y du vaisseau (en cUA) - NON UTILISÉ pour systèmes
     * @param int $fromPositionZ Position Z du vaisseau (en cUA) - NON UTILISÉ pour systèmes
     * @return float Score de détection requis
     */
    public function getScoreDetection(
        int $fromSecteurX,
        int $fromSecteurY,
        int $fromSecteurZ,
        int $fromPositionX = 0,
        int $fromPositionY = 0,
        int $fromPositionZ = 0
    ): float {
        // Si déjà connu, seuil de détection = 0 (apparaît automatiquement)
        if ($this->poi_connu) {
            return 0;
        }

        // RÈGLE INTER-SECTEUR: Distance calculée UNIQUEMENT avec les secteurs (AL)
        // Les positions sont ignorées car la détection inter-système est à l'échelle AL
        $dx = $this->secteur_x - $fromSecteurX;
        $dy = $this->secteur_y - $fromSecteurY;
        $dz = $this->secteur_z - $fromSecteurZ;

        $distanceAL = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        // Si detectabilite_base n'est pas initialisé (0 ou -1), calculer
        $detectabilite = $this->detectabilite_base;
        if ($detectabilite <= 0) {
            $detectabilite = $this->calculerDetectabilite();
        }

        // Formule: (distance_AL / 10) × detectabilite_base
        return ($distanceAL / 10) * $detectabilite;
    }

    /**
     * Calcule la détectabilité de base si non initialisée
     * Formule: (200 - puissance) / 3
     */
    public function calculerDetectabilite(): float
    {
        $puissance = $this->puissance ?? $this->puissance_solaire ?? 50;
        return (200 - $puissance) / 3;
    }

    /**
     * Marque le système comme découvert (poi_connu = true)
     */
    public function marquerDecouvert(): void
    {
        $this->poi_connu = true;
        $this->save();
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
