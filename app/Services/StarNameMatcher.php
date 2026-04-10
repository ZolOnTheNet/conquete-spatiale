<?php

namespace App\Services;

/**
 * Service pour matcher les coordonnées GAIA avec les noms d'étoiles célèbres
 * Basé sur un catalogue de correspondance (RA, Dec, Distance)
 */
class StarNameMatcher
{
    /**
     * Catalogue des étoiles célèbres avec coordonnées
     * Format: [nom_commun => [ra, dec, distance_ly, noms_alternatifs]]
     *
     * Sources: SIMBAD, Wikipedia, catalogues astronomiques
     */
    protected array $knownStars = [
        // Système Alpha Centauri
        'Proxima Centauri' => [
            'ra' => 217.42897500,  // deg
            'dec' => -62.67978611,  // deg
            'distance' => 4.24,     // AL
            'aliases' => ['Proxima Cen', 'Proxima', 'Alpha Centauri C', 'V645 Centauri', 'HIP 70890', 'GJ 551'],
        ],
        'Alpha Centauri A' => [
            'ra' => 219.90205833,
            'dec' => -60.83399269,
            'distance' => 4.37,
            'aliases' => ['Rigil Kentaurus', 'Rigil Kent', 'HIP 71683'],
        ],
        'Alpha Centauri B' => [
            'ra' => 219.90205833,
            'dec' => -60.83399269,
            'distance' => 4.37,
            'aliases' => ['Toliman', 'HIP 71681'],
        ],

        // Étoiles brillantes célèbres
        'Sirius' => [
            'ra' => 101.28715533,
            'dec' => -16.71611586,
            'distance' => 8.6,
            'aliases' => ['Alpha Canis Majoris', 'Dog Star', 'HIP 32349', 'HD 48915'],
        ],
        'Epsilon Eridani' => [
            'ra' => 53.23267083,
            'dec' => -9.45832778,
            'distance' => 10.5,
            'aliases' => ['eps Eri', 'Epsilon Eri', 'Ran', 'HIP 16537', 'HD 22049'],
        ],
        'Tau Ceti' => [
            'ra' => 26.01709944,
            'dec' => -15.93745722,
            'distance' => 11.9,
            'aliases' => ['tau Cet', 'Tau Cet', 'HIP 8102', 'HD 10700'],
        ],
        'Wolf 359' => [
            'ra' => 164.12007083,
            'dec' => 7.00494306,
            'distance' => 7.9,
            'aliases' => ['CN Leonis', 'HIP 54035'],
        ],
        'Lalande 21185' => [
            'ra' => 165.93897917,
            'dec' => 35.95637639,
            'distance' => 8.3,
            'aliases' => ['BD+36 2147', 'HIP 54035'],
        ],
        'Luyten 726-8 A' => [
            'ra' => 25.88805556,
            'dec' => -17.95027778,
            'distance' => 8.7,
            'aliases' => ['BL Ceti', 'UV Ceti'],
        ],

        // Étoiles de type solaire
        '61 Cygni A' => [
            'ra' => 316.86294444,
            'dec' => 38.73722222,
            'distance' => 11.4,
            'aliases' => ['HIP 104214', 'HD 201091'],
        ],
        'Procyon' => [
            'ra' => 114.82552778,
            'dec' => 5.22499444,
            'distance' => 11.5,
            'aliases' => ['Alpha Canis Minoris', 'HIP 37279', 'HD 61421'],
        ],

        // Étoiles avec exoplanètes connues
        '51 Pegasi' => [
            'ra' => 344.36658333,
            'dec' => 20.76891667,
            'distance' => 50.9,
            'aliases' => ['HIP 113357', 'HD 217014', 'Helvetios'],
        ],
        'HD 209458' => [
            'ra' => 330.795,
            'dec' => 18.884,
            'distance' => 159.0,
            'aliases' => ['HIP 108859'],
        ],

        // TRAPPIST-1 (7 exoplanètes)
        'TRAPPIST-1' => [
            'ra' => 346.62203611,
            'dec' => -5.04130556,
            'distance' => 40.66,
            'aliases' => ['2MASS J23062928-0502285'],
        ],

        // Autres étoiles célèbres
        'Barnard\'s Star' => [
            'ra' => 269.45402305,
            'dec' => 4.69339306,
            'distance' => 5.96,
            'aliases' => ['HIP 87937', 'V2500 Ophiuchi'],
        ],
    ];

    /**
     * Trouver le nom commun d'une étoile à partir de ses coordonnées
     *
     * @param float $ra Ascension droite (degrés)
     * @param float $dec Déclinaison (degrés)
     * @param float $distance Distance (années-lumière)
     * @param float $tolerance Tolérance de matching (degrés pour RA/Dec, % pour distance)
     * @return array|null ['nom_commun' => string, 'aliases' => array] ou null
     */
    public function findCommonName(
        float $ra,
        float $dec,
        float $distance,
        float $tolerance = 0.5
    ): ?array {
        $bestMatch = null;
        $bestScore = PHP_FLOAT_MAX;

        foreach ($this->knownStars as $commonName => $data) {
            // Calculer distance angulaire (simplifiée)
            $deltaRA = abs($data['ra'] - $ra);
            $deltaDec = abs($data['dec'] - $dec);

            // Gérer le wrap-around pour RA (0-360°)
            if ($deltaRA > 180) {
                $deltaRA = 360 - $deltaRA;
            }

            // Distance euclidienne sur la sphère (approximation)
            $angularDist = sqrt($deltaRA * $deltaRA + $deltaDec * $deltaDec);

            // Distance en AL (pourcentage de différence)
            $distPercent = abs($data['distance'] - $distance) / $data['distance'] * 100;

            // Score combiné (plus bas = meilleur)
            $score = $angularDist + ($distPercent / 10); // Pondération: angle prioritaire

            // Vérifier si dans la tolérance
            if ($angularDist <= $tolerance && $distPercent <= 10 && $score < $bestScore) {
                $bestMatch = [
                    'nom_commun' => $commonName,
                    'aliases' => $data['aliases'],
                    'score' => $score,
                    'delta_ra' => $deltaRA,
                    'delta_dec' => $deltaDec,
                    'delta_dist' => abs($data['distance'] - $distance),
                ];
                $bestScore = $score;
            }
        }

        return $bestMatch;
    }

    /**
     * Obtenir toutes les étoiles du catalogue
     *
     * @return array
     */
    public function getAllKnownStars(): array
    {
        return $this->knownStars;
    }

    /**
     * Ajouter une étoile au catalogue (pour extension future)
     *
     * @param string $commonName
     * @param float $ra
     * @param float $dec
     * @param float $distance
     * @param array $aliases
     */
    public function addKnownStar(
        string $commonName,
        float $ra,
        float $dec,
        float $distance,
        array $aliases = []
    ): void {
        $this->knownStars[$commonName] = [
            'ra' => $ra,
            'dec' => $dec,
            'distance' => $distance,
            'aliases' => $aliases,
        ];
    }

    /**
     * Obtenir les statistiques du catalogue
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $totalAliases = 0;
        foreach ($this->knownStars as $data) {
            $totalAliases += count($data['aliases']);
        }

        return [
            'total_stars' => count($this->knownStars),
            'total_aliases' => $totalAliases,
            'avg_aliases_per_star' => round($totalAliases / count($this->knownStars), 2),
        ];
    }
}
