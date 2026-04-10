<?php

namespace App\Services;

use App\Models\SystemeStellaire;
use App\Models\Planete;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExoplanetService
{
    /**
     * URL de l'API NASA Exoplanet Archive (TAP Service)
     */
    protected string $apiUrl = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

    /**
     * Durée du cache en secondes (24 heures par défaut)
     */
    protected int $cacheDuration = 86400;

    /**
     * Timeout des requêtes HTTP en secondes
     */
    protected int $timeout = 120;

    /**
     * Récupérer les exoplanètes autour d'une étoile spécifique
     *
     * @param string $starName Nom de l'étoile (ex: "Proxima Centauri")
     * @return array Liste des exoplanètes
     */
    public function getExoplanetsForStar(string $starName): array
    {
        $cacheKey = "exoplanet_star_{$starName}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($starName) {
            // Query SQL pour l'API TAP
            $query = "SELECT pl_name, hostname, sy_dist, pl_orbsmax, pl_rade, pl_bmasse, "
                   . "pl_orbper, pl_eqt, pl_orbeccen, st_teff, discoverymethod, disc_year "
                   . "FROM ps "
                   . "WHERE hostname = '{$starName}' "
                   . "ORDER BY pl_orbsmax ASC";

            return $this->executeQuery($query);
        });
    }

    /**
     * Récupérer toutes les exoplanètes dans un rayon donné (en AL)
     *
     * @param float $radiusLY Rayon en années-lumière
     * @param int $limit Limite de résultats (défaut: 1000)
     * @return array Liste des exoplanètes
     */
    public function getExoplanetsInRadius(float $radiusLY, int $limit = 1000): array
    {
        $radiusParsecs = $radiusLY / 3.26156; // Conversion AL -> parsecs
        $cacheKey = "exoplanet_radius_{$radiusLY}_limit_{$limit}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($radiusParsecs, $limit) {
            // Query SQL pour l'API TAP
            $query = "SELECT pl_name, hostname, sy_dist, pl_orbsmax, pl_rade, pl_bmasse, "
                   . "pl_orbper, pl_eqt, pl_orbeccen, st_teff, discoverymethod, disc_year "
                   . "FROM ps "
                   . "WHERE sy_dist < {$radiusParsecs} "
                   . "AND pl_name IS NOT NULL "
                   . "ORDER BY sy_dist ASC "
                   . "LIMIT {$limit}";

            return $this->executeQuery($query);
        });
    }

    /**
     * Récupérer les exoplanètes pour un système stellaire GAIA
     *
     * @param SystemeStellaire $systeme Système stellaire
     * @return array Liste des exoplanètes trouvées
     */
    public function getExoplanetsForGaiaSystem(SystemeStellaire $systeme): array
    {
        // Essayer d'abord avec le nom commun (ex: "Proxima Centauri")
        if ($systeme->nom_commun) {
            $exoplanets = $this->getExoplanetsForStar($systeme->nom_commun);
            if (!empty($exoplanets)) {
                Log::info("Exoplanètes trouvées pour {$systeme->nom_commun}");
                return $exoplanets;
            }
        }

        // Essayer avec les noms alternatifs
        if ($systeme->noms_alternatifs) {
            $aliases = is_string($systeme->noms_alternatifs)
                ? json_decode($systeme->noms_alternatifs, true)
                : $systeme->noms_alternatifs;

            if (is_array($aliases)) {
                foreach ($aliases as $alias) {
                    $exoplanets = $this->getExoplanetsForStar($alias);
                    if (!empty($exoplanets)) {
                        Log::info("Exoplanètes trouvées pour alias {$alias}");
                        return $exoplanets;
                    }
                }
            }
        }

        // Essayer avec le nom GAIA original
        if ($systeme->nom) {
            $exoplanets = $this->getExoplanetsForStar($systeme->nom);
            if (!empty($exoplanets)) {
                return $exoplanets;
            }
        }

        // Essayer avec des variantes du nom
        $nameVariants = $this->getStarNameVariants($systeme->nom);
        foreach ($nameVariants as $variant) {
            $exoplanets = $this->getExoplanetsForStar($variant);
            if (!empty($exoplanets)) {
                return $exoplanets;
            }
        }

        return [];
    }

    /**
     * Importer les exoplanètes pour un système stellaire et les créer en base
     *
     * @param SystemeStellaire $systeme Système stellaire
     * @return int Nombre de planètes créées
     */
    public function importExoplanetsForSystem(SystemeStellaire $systeme): int
    {
        $exoplanets = $this->getExoplanetsForGaiaSystem($systeme);

        if (empty($exoplanets)) {
            return 0;
        }

        $count = 0;

        foreach ($exoplanets as $exoData) {
            // Vérifier si la planète n'existe pas déjà
            $exists = Planete::where('systeme_stellaire_id', $systeme->id)
                ->where('nom', $exoData['pl_name'])
                ->exists();

            if ($exists) {
                continue;
            }

            // Créer la planète avec les données NASA
            $planete = $this->createPlanetFromExoplanetData($systeme, $exoData);

            if ($planete) {
                $count++;
            }
        }

        // Mettre à jour le nombre de planètes du système
        $systeme->nb_planetes = $systeme->planetes()->count();
        $systeme->save();

        return $count;
    }

    /**
     * Créer une planète depuis les données NASA Exoplanet Archive
     *
     * @param SystemeStellaire $systeme Système stellaire parent
     * @param array $exoData Données de l'exoplanète
     * @return Planete|null Planète créée ou null si erreur
     */
    protected function createPlanetFromExoplanetData(SystemeStellaire $systeme, array $exoData): ?Planete
    {
        try {
            // Déterminer le type de planète selon le rayon
            $rayon = $exoData['pl_rade'] ?? 1.0;
            $type = $this->determinePlanetType($rayon);

            // Calculer la masse (convertir masses terrestres)
            $masse = $exoData['pl_bmasse'] ?? 1.0;

            // Distance orbitale en UA
            $distance = $exoData['pl_orbsmax'] ?? 1.0;

            // Période orbitale en jours
            $periodeOrbitale = $exoData['pl_orbper'] ?? 365.25;

            // Température d'équilibre
            $temperature = $exoData['pl_eqt'] ?? null;

            // Vérifier habitabilité (zone habitable basique)
            $habitable = $this->isInHabitableZone($distance, $systeme->type_etoile, $temperature);

            $planete = Planete::create([
                'systeme_stellaire_id' => $systeme->id,
                'nom' => $exoData['pl_name'],
                'distance_etoile' => $distance,
                'rayon' => $rayon,
                'masse' => $masse,
                'type' => $type,
                'periode_orbitale' => (int)round($periodeOrbitale),
                'temperature_moyenne' => $temperature ? (int)round($temperature - 273.15) : null, // Kelvin -> Celsius
                'a_atmosphere' => $rayon > 0.5, // Approximation
                'habitable' => $habitable,
                'excentricite_orbitale' => $exoData['pl_orbeccen'] ?? 0.0,

                // Métadonnées NASA
                'source_nasa_exoplanet' => true,
                'nasa_exo_id' => $exoData['pl_name'],
                'nasa_discovery_method' => $exoData['discoverymethod'] ?? null,
                'nasa_discovery_year' => $exoData['disc_year'] ?? null,

                'detectabilite_base' => $this->calculatePlanetDetectability($rayon),
                'poi_connu' => false, // Les exoplanètes doivent être découvertes
            ]);

            // Générer gisements si applicable
            if (in_array($type, ['terrestre', 'naine'])) {
                $planete->genererGisements();
            }

            return $planete;

        } catch (\Exception $e) {
            Log::error("Erreur création exoplanète {$exoData['pl_name']}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Déterminer le type de planète selon son rayon
     *
     * @param float $rayon Rayon en rayons terrestres
     * @return string Type de planète
     */
    protected function determinePlanetType(float $rayon): string
    {
        if ($rayon < 0.5) {
            return 'naine';
        } elseif ($rayon < 1.5) {
            return 'terrestre';
        } elseif ($rayon < 2.5) {
            return 'super-terrestre';
        } elseif ($rayon < 6.0) {
            return 'neptunienne';
        } else {
            return 'gazeuse';
        }
    }

    /**
     * Vérifier si une planète est dans la zone habitable
     *
     * @param float $distance Distance en UA
     * @param string $typeEtoile Type spectral de l'étoile
     * @param float|null $temperature Température d'équilibre (K)
     * @return bool
     */
    protected function isInHabitableZone(float $distance, string $typeEtoile, ?float $temperature): bool
    {
        // Si on a la température, vérifier qu'elle est dans la zone habitable (273-373 K)
        if ($temperature !== null) {
            return $temperature >= 273 && $temperature <= 373;
        }

        // Sinon, utiliser la distance orbitale selon le type d'étoile
        $zoneHabitable = Planete::getZoneHabitable($typeEtoile);
        return $distance >= $zoneHabitable['min'] && $distance <= $zoneHabitable['max'];
    }

    /**
     * Calculer la détectabilité d'une planète
     *
     * @param float $rayon Rayon en rayons terrestres
     * @return float Détectabilité
     */
    protected function calculatePlanetDetectability(float $rayon): float
    {
        // Formule : D_base = 150 - (Taille × 10)
        $detectabilite = 150 - ($rayon * 10);
        return max(1, min(150, round($detectabilite, 2)));
    }

    /**
     * Obtenir les variantes de nom d'une étoile
     *
     * @param string $nom Nom de l'étoile
     * @return array Liste de variantes
     */
    protected function getStarNameVariants(string $nom): array
    {
        $variants = [];

        // Retirer suffixes communs
        $nom = preg_replace('/ (A|B|C|D)$/', '', $nom);

        // Ajouter variantes avec/sans espaces
        $variants[] = $nom;
        $variants[] = str_replace(' ', '', $nom);
        $variants[] = str_replace('-', ' ', $nom);

        // Variantes grecques
        $greekLetters = [
            'Alpha' => 'α', 'Beta' => 'β', 'Gamma' => 'γ', 'Delta' => 'δ',
            'Epsilon' => 'ε', 'Zeta' => 'ζ', 'Eta' => 'η', 'Theta' => 'θ',
        ];

        foreach ($greekLetters as $word => $letter) {
            if (strpos($nom, $word) !== false) {
                $variants[] = str_replace($word, $letter, $nom);
            }
        }

        return array_unique($variants);
    }

    /**
     * Exécuter une query SQL sur l'API TAP NASA
     *
     * @param string $query Query SQL
     * @return array Résultats parsés
     */
    protected function executeQuery(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => false]) // Désactiver vérification SSL pour développement
                ->get($this->apiUrl, [
                    'query' => $query,
                    'format' => 'json',
                ]);

            if (!$response->successful()) {
                Log::warning("Erreur API NASA Exoplanet Archive: " . $response->status());
                return [];
            }

            $data = $response->json();

            // L'API retourne directement un tableau d'objets
            if (!is_array($data) || empty($data)) {
                return [];
            }

            // Parser les résultats
            $results = [];
            foreach ($data as $row) {
                // Le format est déjà un tableau associatif, pas besoin de parser
                $results[] = is_array($row) ? $this->parseExoplanetRow($row) : $row;
            }

            return $results;

        } catch (\Exception $e) {
            Log::error("Erreur requête NASA Exoplanet Archive: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parser une ligne de résultat de l'API
     *
     * @param array $row Ligne de données (format indexé ou associatif)
     * @return array Données parsées
     */
    protected function parseExoplanetRow(array $row): array
    {
        // Si le row est déjà un tableau associatif, le retourner tel quel
        if (isset($row['pl_name'])) {
            return $row;
        }

        // Sinon, parser le format indexé (ancien format)
        return [
            'pl_name' => $row[0] ?? null,
            'hostname' => $row[1] ?? null,
            'sy_dist' => (float)($row[2] ?? 0),
            'pl_orbsmax' => (float)($row[3] ?? 1),
            'pl_rade' => (float)($row[4] ?? 1),
            'pl_bmasse' => (float)($row[5] ?? 1),
            'pl_orbper' => (float)($row[6] ?? 365),
            'pl_eqt' => (float)($row[7] ?? null),
            'pl_orbeccen' => (float)($row[8] ?? 0),
            'st_teff' => (float)($row[9] ?? null),
            'discoverymethod' => $row[10] ?? null,
            'disc_year' => (int)($row[11] ?? null),
        ];
    }

    /**
     * Vider le cache des exoplanètes
     */
    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Configurer le timeout des requêtes
     *
     * @param int $seconds Timeout en secondes
     */
    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    /**
     * Configurer la durée du cache
     *
     * @param int $seconds Durée en secondes
     */
    public function setCacheDuration(int $seconds): void
    {
        $this->cacheDuration = $seconds;
    }
}
