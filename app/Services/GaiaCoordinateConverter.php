<?php

namespace App\Services;

class GaiaCoordinateConverter
{
    /**
     * Convertir coordonnées galactiques (RA, DEC, Distance) vers coordonnées du jeu
     *
     * @param float $ra Right Ascension (degrés)
     * @param float $dec Declination (degrés)
     * @param float $distanceLy Distance (années-lumière)
     * @return array ['secteur_x', 'secteur_y', 'secteur_z', 'position_x', 'position_y', 'position_z']
     */
    public static function galacticToGame(float $ra, float $dec, float $distanceLy): array
    {
        // Convert to radians
        $raRad = deg2rad($ra);
        $decRad = deg2rad($dec);

        // Spherical to Cartesian coordinates
        // X = distance * cos(DEC) * cos(RA)
        // Y = distance * cos(DEC) * sin(RA)
        // Z = distance * sin(DEC)
        $x = $distanceLy * cos($decRad) * cos($raRad);
        $y = $distanceLy * cos($decRad) * sin($raRad);
        $z = $distanceLy * sin($decRad);

        // Sol (notre Soleil) est au centre (0, 0, 0)
        // Séparer partie entière (secteur) et décimale (position)
        return [
            'secteur_x' => (int)floor($x),
            'secteur_y' => (int)floor($y),
            'secteur_z' => (int)floor($z),
            'position_x' => $x - floor($x),
            'position_y' => $y - floor($y),
            'position_z' => $z - floor($z),
        ];
    }

    /**
     * Convertir coordonnées du jeu vers coordonnées galactiques
     *
     * @param int $secteurX
     * @param int $secteurY
     * @param int $secteurZ
     * @param float $positionX
     * @param float $positionY
     * @param float $positionZ
     * @return array ['ra', 'dec', 'distance_ly']
     */
    public static function gameToGalactic(
        int $secteurX,
        int $secteurY,
        int $secteurZ,
        float $positionX,
        float $positionY,
        float $positionZ
    ): array {
        // Reconstruire coordonnées cartésiennes complètes
        $x = $secteurX + $positionX;
        $y = $secteurY + $positionY;
        $z = $secteurZ + $positionZ;

        // Distance euclidienne
        $distance = sqrt($x * $x + $y * $y + $z * $z);

        // Cartesian to Spherical
        // DEC = arcsin(Z / distance)
        // RA = arctan2(Y, X)
        $dec = rad2deg(asin($z / max($distance, 0.0001))); // Éviter division par zéro
        $ra = rad2deg(atan2($y, $x));

        // Normaliser RA entre 0 et 360
        if ($ra < 0) {
            $ra += 360;
        }

        return [
            'ra' => $ra,
            'dec' => $dec,
            'distance_ly' => $distance,
        ];
    }

    /**
     * Calculer distance entre deux points GAIA (RA, DEC, Distance)
     *
     * @param array $point1 ['ra', 'dec', 'distance_ly']
     * @param array $point2 ['ra', 'dec', 'distance_ly']
     * @return float Distance en années-lumière
     */
    public static function calculateDistance(array $point1, array $point2): float
    {
        // Convertir en cartésien
        $coords1 = self::galacticToGame($point1['ra'], $point1['dec'], $point1['distance_ly']);
        $coords2 = self::galacticToGame($point2['ra'], $point2['dec'], $point2['distance_ly']);

        $x1 = $coords1['secteur_x'] + $coords1['position_x'];
        $y1 = $coords1['secteur_y'] + $coords1['position_y'];
        $z1 = $coords1['secteur_z'] + $coords1['position_z'];

        $x2 = $coords2['secteur_x'] + $coords2['position_x'];
        $y2 = $coords2['secteur_y'] + $coords2['position_y'];
        $z2 = $coords2['secteur_z'] + $coords2['position_z'];

        // Distance euclidienne
        return sqrt(
            pow($x2 - $x1, 2) +
            pow($y2 - $y1, 2) +
            pow($z2 - $z1, 2)
        );
    }

    /**
     * Mapper type spectral GAIA vers type de jeu
     *
     * @param string $gaiaType Type spectral GAIA (ex: G2V, M3, K5III)
     * @return string Type simplifié (O, B, A, F, G, K, M)
     */
    public static function mapSpectralType(?string $gaiaType): string
    {
        if (empty($gaiaType)) {
            return 'G'; // Défaut
        }

        // Extraire première lettre (classe principale)
        $firstChar = strtoupper(substr($gaiaType, 0, 1));

        // Vérifier que c'est un type valide
        if (in_array($firstChar, ['O', 'B', 'A', 'F', 'G', 'K', 'M'])) {
            return $firstChar;
        }

        // Défaut si type inconnu
        return 'G';
    }

    /**
     * Obtenir couleur d'une étoile selon son type spectral
     *
     * @param string $spectralType Type spectral (O, B, A, F, G, K, M)
     * @return string Couleur
     */
    public static function getColorFromType(string $spectralType): string
    {
        return match($spectralType) {
            'O' => 'Bleue',
            'B' => 'Bleue-blanche',
            'A' => 'Blanche',
            'F' => 'Jaune-blanche',
            'G' => 'Jaune',
            'K' => 'Orange',
            'M' => 'Rouge',
            default => 'Jaune',
        };
    }
}
