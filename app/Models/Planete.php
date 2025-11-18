<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planete extends Model
{
    protected $table = 'planetes';

    protected $fillable = [
        'systeme_stellaire_id',
        'nom',
        'type',
        'rayon',
        'masse',
        'gravite',
        'distance_etoile',
        'periode_orbitale',
        'habitable',
        'habitee',
        'population',
        'a_atmosphere',
        'composition_atmosphere',
        'pression_atmospherique',
        'gisements',
        'rendement_base',
        'temperature_moyenne',
        'temperature_min',
        'temperature_max',
        'description',
        'donnees_supplementaires',
    ];

    protected $casts = [
        'habitable' => 'boolean',
        'habitee' => 'boolean',
        'a_atmosphere' => 'boolean',
        'gisements' => 'array',
        'donnees_supplementaires' => 'array',
    ];

    // Relations
    public function systemeStellaire(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_stellaire_id');
    }

    /**
     * Génère un type de planète selon la distance à l'étoile
     * Zone habitable (zone Goldilocks): 0.95 - 1.37 UA pour type G
     */
    public static function genererType(float $distanceUA, string $typeEtoile): string
    {
        // Ajuster zone habitable selon type étoile
        $zoneHabitable = self::getZoneHabitable($typeEtoile);

        if ($distanceUA < $zoneHabitable['min'] * 0.3) {
            // Très proche: volcanique ou désertique
            return rand(0, 1) ? 'volcanique' : 'desert';
        } elseif ($distanceUA < $zoneHabitable['min']) {
            // Proche mais hors zone: terrestre chaude ou désertique
            return rand(0, 2) ? 'desert' : 'terrestre';
        } elseif ($distanceUA >= $zoneHabitable['min'] && $distanceUA <= $zoneHabitable['max']) {
            // Zone habitable: terrestre ou océanique
            $rand = rand(1, 10);
            if ($rand <= 6) return 'terrestre';
            if ($rand <= 9) return 'oceanique';
            return 'desert';
        } elseif ($distanceUA < $zoneHabitable['max'] * 2) {
            // Après zone habitable: terrestre froide ou glacée
            return rand(0, 1) ? 'terrestre' : 'glacee';
        } elseif ($distanceUA < $zoneHabitable['max'] * 5) {
            // Zone externe: gazeuse ou glacée
            $rand = rand(1, 10);
            if ($rand <= 7) return 'gazeuse';
            return 'glacee';
        } else {
            // Très loin: glacée ou naine
            $rand = rand(1, 10);
            if ($rand <= 7) return 'glacee';
            if ($rand <= 9) return 'naine';
            return 'gazeuse';
        }
    }

    /**
     * Retourne la zone habitable selon le type d'étoile
     */
    public static function getZoneHabitable(string $typeEtoile): array
    {
        return match ($typeEtoile) {
            'O' => ['min' => 10.0, 'max' => 50.0],  // Très lumineux
            'B' => ['min' => 5.0, 'max' => 20.0],
            'A' => ['min' => 2.0, 'max' => 8.0],
            'F' => ['min' => 1.3, 'max' => 2.5],
            'G' => ['min' => 0.95, 'max' => 1.37],  // Type solaire (Terre = 1 UA)
            'K' => ['min' => 0.5, 'max' => 0.9],
            'M' => ['min' => 0.1, 'max' => 0.4],    // Naines rouges
            default => ['min' => 0.95, 'max' => 1.37],
        };
    }

    /**
     * Génère les propriétés physiques selon le type
     */
    public function genererProprietes(): void
    {
        $props = match ($this->type) {
            'terrestre' => [
                'rayon' => rand(50, 200) / 100,     // 0.5 - 2.0 rayons terrestres
                'masse' => rand(30, 300) / 100,      // 0.3 - 3.0 masses terrestres
                'atmosphere' => rand(0, 100) > 30,   // 70% ont atmosphère
                'composition' => 'N2, O2, CO2',
            ],
            'gazeuse' => [
                'rayon' => rand(400, 1200) / 100,    // 4 - 12 rayons terrestres
                'masse' => rand(5000, 30000) / 100,  // 50 - 300 masses terrestres
                'atmosphere' => true,
                'composition' => 'H2, He, CH4',
            ],
            'oceanique' => [
                'rayon' => rand(80, 150) / 100,
                'masse' => rand(50, 200) / 100,
                'atmosphere' => true,
                'composition' => 'N2, O2, H2O',
            ],
            'glacee' => [
                'rayon' => rand(40, 150) / 100,
                'masse' => rand(20, 150) / 100,
                'atmosphere' => rand(0, 100) > 50,
                'composition' => 'CO2, N2, CH4',
            ],
            'volcanique' => [
                'rayon' => rand(60, 150) / 100,
                'masse' => rand(40, 200) / 100,
                'atmosphere' => true,
                'composition' => 'SO2, CO2, H2S',
            ],
            'desert' => [
                'rayon' => rand(50, 180) / 100,
                'masse' => rand(30, 250) / 100,
                'atmosphere' => rand(0, 100) > 60,
                'composition' => 'CO2, N2',
            ],
            'naine' => [
                'rayon' => rand(10, 50) / 100,
                'masse' => rand(1, 30) / 100,
                'atmosphere' => false,
                'composition' => null,
            ],
            default => [
                'rayon' => 1.0,
                'masse' => 1.0,
                'atmosphere' => false,
                'composition' => null,
            ],
        };

        $this->rayon = $props['rayon'];
        $this->masse = $props['masse'];
        $this->gravite = round($props['masse'] / ($props['rayon'] ** 2), 2);
        $this->a_atmosphere = $props['atmosphere'];
        $this->composition_atmosphere = $props['composition'];

        if ($this->a_atmosphere) {
            // Pression atmosphérique (Earth = 1 bar)
            $this->pression_atmospherique = match ($this->type) {
                'gazeuse' => rand(100, 1000) / 10,
                'volcanique' => rand(5, 50) / 10,
                'oceanique' => rand(8, 15) / 10,
                'terrestre' => rand(5, 20) / 10,
                default => rand(1, 10) / 10,
            };
        }
    }

    /**
     * Calcule si la planète est habitable
     */
    public function calculerHabitabilite(): bool
    {
        // Critères d'habitabilité
        $criteres = [
            'type_valide' => in_array($this->type, ['terrestre', 'oceanique']),
            'zone_habitable' => $this->estDansZoneHabitable(),
            'a_atmosphere' => $this->a_atmosphere === true,
            'gravite_acceptable' => $this->gravite >= 0.4 && $this->gravite <= 2.5,
            'temperature_acceptable' => $this->temperature_moyenne >= -50 && $this->temperature_moyenne <= 50,
        ];

        // Une planète est habitable si elle remplit au moins 4 critères sur 5
        $score = count(array_filter($criteres));
        $this->habitable = $score >= 4;

        return $this->habitable;
    }

    /**
     * Vérifie si la planète est dans la zone habitable
     */
    public function estDansZoneHabitable(): bool
    {
        if (!$this->systemeStellaire) {
            return false;
        }

        $zone = self::getZoneHabitable($this->systemeStellaire->type_etoile);
        return $this->distance_etoile >= $zone['min'] && $this->distance_etoile <= $zone['max'];
    }

    /**
     * Génère les gisements de ressources selon le type de planète
     */
    public function genererGisements(): array
    {
        $gisements = [];

        // Ressources de base selon GDD_Economie
        $ressources_base = ['fer', 'cuivre', 'aluminium', 'titane'];
        $ressources_rares = ['or', 'platine', 'uranium', 'tyberium'];

        // Distribution selon type de planète
        $chances = match ($this->type) {
            'terrestre' => ['base' => 0.8, 'rare' => 0.3],
            'volcanique' => ['base' => 0.9, 'rare' => 0.5],  // Riche en minéraux
            'desert' => ['base' => 0.7, 'rare' => 0.4],
            'glacee' => ['base' => 0.5, 'rare' => 0.2],
            'oceanique' => ['base' => 0.4, 'rare' => 0.1],
            'gazeuse' => ['base' => 0.0, 'rare' => 0.0],     // Pas de minage
            'naine' => ['base' => 0.6, 'rare' => 0.3],
            default => ['base' => 0.5, 'rare' => 0.2],
        };

        // Générer ressources de base
        foreach ($ressources_base as $ressource) {
            if (rand(1, 100) / 100 <= $chances['base']) {
                $gisements[$ressource] = rand(10, 100); // Quantité 10-100
            }
        }

        // Générer ressources rares
        foreach ($ressources_rares as $ressource) {
            if (rand(1, 100) / 100 <= $chances['rare']) {
                $gisements[$ressource] = rand(5, 50); // Quantité 5-50
            }
        }

        $this->gisements = $gisements;
        return $gisements;
    }

    /**
     * Calcule la température moyenne selon distance à l'étoile
     */
    public function calculerTemperature(float $puissanceSolaire): void
    {
        // Formule simplifiée: T proportionnel à sqrt(puissance/distance²)
        // Terre = 15°C à 1 UA avec puissance solaire 50
        $facteur = sqrt($puissanceSolaire / ($this->distance_etoile ** 2)) / sqrt(50);

        $temp_base = 15 * $facteur;

        // Ajuster selon type
        $ajustement = match ($this->type) {
            'volcanique' => rand(100, 300),
            'glacee' => rand(-200, -100),
            'desert' => rand(20, 50),
            'oceanique' => rand(-10, 10),
            default => 0,
        };

        $this->temperature_moyenne = (int)round($temp_base + $ajustement);

        // Variation thermique
        $variation = match ($this->type) {
            'desert' => rand(40, 80),
            'terrestre' => rand(20, 40),
            'volcanique' => rand(50, 100),
            default => rand(10, 30),
        };

        $this->temperature_min = $this->temperature_moyenne - $variation;
        $this->temperature_max = $this->temperature_moyenne + $variation;
    }

    /**
     * Génère un nom de planète procéduralement
     */
    public static function genererNom(string $nomSysteme, int $numero): string
    {
        // Format: Nom du système + numéro romain ou lettre
        $suffixes = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

        if ($numero <= count($suffixes)) {
            return $nomSysteme . ' ' . $suffixes[$numero - 1];
        }

        // Sinon utiliser des lettres
        return $nomSysteme . ' ' . chr(64 + $numero); // A, B, C...
    }
}
