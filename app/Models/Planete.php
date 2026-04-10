<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\GameTimeHelper;
use App\Helpers\CoordinatesHelper;
use Carbon\Carbon;

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
        'excentricite_orbitale',
        'angle_orbital_initial',
        'vitesse_angulaire',
        'cache_position_x',
        'cache_position_y',
        'cache_position_z',
        'cache_timestamp_jours',
        'cache_validite_jours',
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
        'detectabilite_base',
        'poi_connu',
        // Champs NASA Exoplanet Archive
        'source_nasa_exoplanet',
        'nasa_exo_id',
        'nasa_discovery_method',
        'nasa_discovery_year',
    ];

    protected $casts = [
        'habitable' => 'boolean',
        'habitee' => 'boolean',
        'a_atmosphere' => 'boolean',
        'poi_connu' => 'boolean',
        'source_nasa_exoplanet' => 'boolean',
        'gisements' => 'array',
        'donnees_supplementaires' => 'array',
        'angle_orbital_initial' => 'float',
        'vitesse_angulaire' => 'float',
        'cache_position_x' => 'integer', // cUA (centi-UA)
        'cache_position_y' => 'integer', // cUA
        'cache_position_z' => 'integer', // cUA
        'cache_timestamp_jours' => 'integer',
        'cache_validite_jours' => 'integer',
        'nasa_discovery_year' => 'integer',
        'excentricite_orbitale' => 'float',
    ];

    // Relations
    public function systemeStellaire(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_stellaire_id');
    }

    /**
     * Gisements de ressources sur cette planète
     */
    public function gisements(): HasMany
    {
        return $this->hasMany(Gisement::class);
    }

    /**
     * Stations orbitales autour de cette planète
     */
    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    /**
     * Mines d'exploitation sur cette planète
     */
    public function mines(): HasMany
    {
        return $this->hasMany(Mine::class);
    }

    /**
     * Découvertes de cette planète par les personnages
     * Note : Pour l'instant, on utilise les découvertes du système stellaire
     * TODO: Créer une table decouvertes_planetes séparée si besoin
     */
    /**
     * TODO: Relation pour système de découverte de planètes (à implémenter)
     * Nécessite ajout colonne 'planete_id' dans table 'decouvertes'
     * ou création d'une table 'decouvertes_planetes' séparée
     */
    // public function decouvertes(): HasMany
    // {
    //     return $this->hasMany(\App\Models\Decouverte::class, 'planete_id');
    // }

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

        // Nombre de gisements selon type de planète
        $nb_gisements = match($this->type) {
            'terrestre', 'Tellurique' => rand(3, 8),
            'volcanique' => rand(4, 10), // Riche en minéraux
            'gazeuse', 'Gazeuse' => rand(1, 3), // Gaz uniquement
            'glacee', 'Glacée' => rand(2, 5),
            'naine' => rand(2, 4),
            default => rand(1, 4),
        };

        // Obtenir ressources disponibles selon type
        $ressources_disponibles = $this->getRessourcesSelonType();

        if ($ressources_disponibles->isEmpty()) {
            return $gisements;
        }

        for ($i = 0; $i < $nb_gisements; $i++) {
            $ressource = $ressources_disponibles->random();

            // Calculer quantité selon rareté
            $quantite_base = match(true) {
                $ressource->rarete >= 80 => rand(5000000, 20000000), // Très commun
                $ressource->rarete >= 50 => rand(1000000, 5000000),  // Commun
                $ressource->rarete >= 30 => rand(500000, 2000000),   // Peu commun
                $ressource->rarete >= 10 => rand(100000, 500000),    // Rare
                default => rand(10000, 100000),                       // Très rare
            };

            $gisement = Gisement::create([
                'planete_id' => $this->id,
                'ressource_id' => $ressource->id,
                'latitude' => rand(-9000, 9000) / 100,  // -90.00 à 90.00
                'longitude' => rand(-18000, 18000) / 100, // -180.00 à 180.00
                'richesse' => rand(20, 100),
                'quantite_totale' => $quantite_base,
                'quantite_restante' => $quantite_base,
                'decouvert' => false,
            ]);

            $gisements[] = $gisement;
        }

        return $gisements;
    }

    /**
     * Obtenir ressources disponibles selon type de planète
     */
    protected function getRessourcesSelonType()
    {
        $ressources = Ressource::all();

        return match($this->type) {
            'terrestre', 'Tellurique' => $ressources->whereIn('categorie', ['metaux', 'elementaire', 'chimie']),
            'volcanique' => $ressources->whereIn('categorie', ['metaux', 'elementaire']),
            'gazeuse', 'Gazeuse' => $ressources->where('categorie', 'gaz'),
            'glacee', 'Glacée' => $ressources->whereIn('code', ['GLACES', 'OXYGENE', 'HYDROGENE', 'FER']),
            default => $ressources->where('categorie', '!=', 'exotique'),
        };
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

    /**
     * Obtenir les données orbitales pour le calcul JavaScript côté client
     * Retourne les données optimisées pour le calcul en temps réel
     *
     * @param \App\Models\Personnage|null $personnage Pour obtenir la date actuelle du jeu
     * @return array Données orbitales + cache
     */
    public function getDonneesOrbitales($personnage = null): array
    {
        // Date actuelle du jeu
        $timestampJoursActuel = $personnage
            ? GameTimeHelper::getTimestampJoursActuel($personnage)
            : GameTimeHelper::dateToJours(Carbon::now());

        // Vérifier si le cache est valide
        $cacheValide = false;
        if ($this->cache_timestamp_jours !== null) {
            $joursDepuisCache = abs($timestampJoursActuel - $this->cache_timestamp_jours);
            $cacheValide = $joursDepuisCache < $this->cache_validite_jours;
        }

        // Si cache invalide, recalculer
        if (!$cacheValide) {
            $this->recalculerPositionCache($timestampJoursActuel);
        }

        return [
            // Identifiant
            'id' => $this->id,
            'nom' => $this->nom,

            // Paramètres orbitaux (constants)
            'distance_etoile' => $this->distance_etoile, // UA
            'periode_orbitale' => $this->periode_orbitale, // jours
            'angle_orbital_initial' => $this->angle_orbital_initial, // radians
            'vitesse_angulaire' => $this->vitesse_angulaire, // rad/jour

            // Cache de position (pour éviter recalculs)
            'cache_position_x' => $this->cache_position_x, // UA
            'cache_position_y' => $this->cache_position_y, // UA
            'cache_position_z' => $this->cache_position_z, // UA
            'cache_timestamp_jours' => $this->cache_timestamp_jours,

            // Métadonnées
            'timestamp_actuel_jours' => $timestampJoursActuel,
            'cache_validite_jours' => $this->cache_validite_jours,
        ];
    }

    /**
     * Recalculer la position de la planète et mettre à jour le cache
     *
     * @param float $timestampJours Timestamp en jours depuis 3000-01-01
     * @return void
     */
    public function recalculerPositionCache(float $timestampJours): void
    {
        if (!$this->vitesse_angulaire || !$this->distance_etoile) {
            return; // Pas de données orbitales
        }

        // Calculer l'angle actuel
        $angleActuel = $this->angle_orbital_initial + ($this->vitesse_angulaire * $timestampJours);

        // Position orbitale (orbite circulaire dans plan XY)
        // IMPORTANT: distance_etoile est en cUA depuis migration 2025_12_30_160337
        $x_cua = (int)round($this->distance_etoile * cos($angleActuel));
        $y_cua = (int)round($this->distance_etoile * sin($angleActuel));
        $z_cua = 0; // Plan orbital simplifié (peut ajouter inclinaison plus tard)

        // Mettre à jour le cache (déjà en cUA)
        $this->update([
            'cache_position_x' => $x_cua,
            'cache_position_y' => $y_cua,
            'cache_position_z' => $z_cua,
            'cache_timestamp_jours' => (int)$timestampJours,
        ]);
    }

    /**
     * Calculer la position absolue de la planète dans l'espace (en cUA)
     *
     * @param float|null $timestampJours Timestamp en jours (null = actuel)
     * @return array ['x' => int, 'y' => int, 'z' => int] Position en cUA
     */
    public function getPositionAbsolue(?float $timestampJours = null): array
    {
        $timestampJours = $timestampJours ?? GameTimeHelper::dateToJours(Carbon::now());

        // Calculer position orbitale (orbite circulaire dans plan XY)
        // IMPORTANT: distance_etoile est en cUA depuis migration 2025_12_30_160337
        $angleActuel = $this->angle_orbital_initial + ($this->vitesse_angulaire * $timestampJours);
        $x_cua = (int)round($this->distance_etoile * cos($angleActuel));
        $y_cua = (int)round($this->distance_etoile * sin($angleActuel));
        $z_cua = 0;

        // Position de l'étoile (système stellaire) en cUA
        $systeme = $this->systemeStellaire;
        $systeme_x_cua = CoordinatesHelper::alToCua($systeme->secteur_x) + ($systeme->position_x ?? 0);
        $systeme_y_cua = CoordinatesHelper::alToCua($systeme->secteur_y) + ($systeme->position_y ?? 0);
        $systeme_z_cua = CoordinatesHelper::alToCua($systeme->secteur_z) + ($systeme->position_z ?? 0);

        return [
            'x' => $systeme_x_cua + $x_cua,
            'y' => $systeme_y_cua + $y_cua,
            'z' => $systeme_z_cua + $z_cua,
        ];
    }

    /**
     * Calculer la distance entre la planète et un vaisseau (en UA)
     *
     * @param \App\Models\Vaisseau $vaisseau
     * @param float|null $timestampJours
     * @return float Distance en UA
     */
    public function getDistanceDepuisVaisseau($vaisseau, ?float $timestampJours = null): float
    {
        $positionPlanete = $this->getPositionAbsolue($timestampJours); // En cUA
        $objetSpatialVaisseau = $vaisseau->objetSpatial;

        // IMPORTANT: Utiliser position dynamique (prend en compte orbite si le vaisseau est en orbite)
        $vaisseauPosAbs = $objetSpatialVaisseau->getPositionAbsolueCua($timestampJours);
        $vaisseau_x_cua = $vaisseauPosAbs['x'];
        $vaisseau_y_cua = $vaisseauPosAbs['y'];
        $vaisseau_z_cua = $vaisseauPosAbs['z'];

        // Distance en cUA
        $distance_cua = CoordinatesHelper::distance3D(
            $positionPlanete['x'], $positionPlanete['y'], $positionPlanete['z'],
            $vaisseau_x_cua, $vaisseau_y_cua, $vaisseau_z_cua
        );

        // Conversion cUA → UA pour l'affichage gameplay
        return CoordinatesHelper::cuaToUa($distance_cua);
    }

    /**
     * Calcule le score de détection pour une planète (RÈGLE INTRA-SECTEUR)
     *
     * IMPORTANT:
     * - Les planètes sont des POI locaux, détectables UNIQUEMENT dans le même secteur
     * - Distance calculée UNIQUEMENT avec positions (cUA), secteurs ignorés
     * - Formule: (distance_cUA / 1000) × detectabilite_base
     *
     * @param int $fromSecteurX Secteur X du scanner (AL)
     * @param int $fromSecteurY Secteur Y du scanner (AL)
     * @param int $fromSecteurZ Secteur Z du scanner (AL)
     * @param int $fromPositionX Position X du scanner (cUA)
     * @param int $fromPositionY Position Y du scanner (cUA)
     * @param int $fromPositionZ Position Z du scanner (cUA)
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
        // Si déjà connue, seuil de détection = 0 (apparaît automatiquement)
        if ($this->poi_connu) {
            return 0;
        }

        $systeme = $this->systemeStellaire;
        if (!$systeme) {
            return 999999; // Planète orpheline, impossible à détecter
        }

        // RÈGLE INTRA-SECTEUR: Vérifier si même secteur
        if ($systeme->secteur_x !== $fromSecteurX ||
            $systeme->secteur_y !== $fromSecteurY ||
            $systeme->secteur_z !== $fromSecteurZ) {
            // Pas dans le même secteur → non détectable en local
            return 999999;
        }

        // IMPORTANT: Utiliser cache_position si disponible, sinon position du système
        $planeteX = $this->cache_position_x ?? $systeme->position_x;
        $planeteY = $this->cache_position_y ?? $systeme->position_y;
        $planeteZ = $this->cache_position_z ?? $systeme->position_z;

        // Distance calculée UNIQUEMENT avec positions (cUA) - secteurs ignorés
        $dx = $planeteX - $fromPositionX;
        $dy = $planeteY - $fromPositionY;
        $dz = $planeteZ - $fromPositionZ;

        $distance_cUA = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        // Si detectabilite_base n'est pas initialisé (0 ou -1), calculer
        $detectabilite = $this->detectabilite_base;
        if ($detectabilite <= 0) {
            $detectabilite = $this->calculerDetectabilite();
        }

        // Formule INTRA-SECTEUR: (distance_cUA / 1000) × detectabilite_base
        return ($distance_cUA / 1000) * $detectabilite;
    }

    /**
     * Calcule la détectabilité de base si non initialisée
     * Formule: floor((30 - taille_planete) / 2)
     */
    public function calculerDetectabilite(): float
    {
        // Utiliser le rayon comme approximation de la taille
        // rayon est en rayons terrestres (0.5-12.0)
        $taille = $this->rayon ?? 1.0;
        return floor((30 - $taille) / 2);
    }

    /**
     * Marque la planète comme découverte (poi_connu = true)
     */
    public function marquerDecouvert(): void
    {
        $this->poi_connu = true;
        $this->save();
    }
}
