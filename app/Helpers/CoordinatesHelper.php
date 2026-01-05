<?php

namespace App\Helpers;

/**
 * Helper de conversion de coordonnées spatiales
 *
 * Système de coordonnées :
 * - Secteurs : entiers en AL (années-lumière)
 * - Positions intra-système : entiers en cUA (centi-UA)
 *
 * Conversions :
 * - 1 UA = 100 cUA
 * - 1 AL = 63 241 UA = 6 324 100 cUA
 */
class CoordinatesHelper
{
    // Facteurs de conversion
    const CUA_PER_UA = 100;
    const UA_PER_AL = 63241;
    const CUA_PER_AL = 6324100; // 63241 * 100

    /**
     * Convertir UA → cUA
     */
    public static function uaToCua(float $ua): int
    {
        return (int)round($ua * self::CUA_PER_UA);
    }

    /**
     * Convertir cUA → UA
     */
    public static function cuaToUa(int $cua): float
    {
        return $cua / self::CUA_PER_UA;
    }

    /**
     * Convertir AL → cUA
     */
    public static function alToCua(float $al): int
    {
        return (int)round($al * self::CUA_PER_AL);
    }

    /**
     * Convertir cUA → AL
     */
    public static function cuaToAl(int $cua): float
    {
        return $cua / self::CUA_PER_AL;
    }

    /**
     * Convertir AL → UA
     */
    public static function alToUa(float $al): float
    {
        return $al * self::UA_PER_AL;
    }

    /**
     * Convertir UA → AL
     */
    public static function uaToAl(float $ua): float
    {
        return $ua / self::UA_PER_AL;
    }

    /**
     * Formater une distance en cUA pour affichage
     *
     * @param int $cua Distance en cUA
     * @param string $unit Unité de sortie ('auto', 'UA', 'AL', 'cUA')
     * @param int $decimals Nombre de décimales
     * @return string Distance formatée avec unité
     */
    public static function formatDistance(int $cua, string $unit = 'auto', int $decimals = 2): string
    {
        if ($unit === 'cUA') {
            return number_format($cua, 0) . ' cUA';
        }

        if ($unit === 'UA' || ($unit === 'auto' && abs($cua) < 100000)) {
            // Afficher en UA pour distances < 1000 UA (100000 cUA)
            $ua = self::cuaToUa($cua);
            return number_format($ua, $decimals) . ' UA';
        }

        if ($unit === 'AL' || $unit === 'auto') {
            // Afficher en AL pour grandes distances
            $al = self::cuaToAl($cua);
            return number_format($al, $decimals) . ' AL';
        }

        return number_format($cua, 0) . ' cUA';
    }

    /**
     * Calculer distance 3D en cUA
     *
     * @param int $x1 Position X1 en cUA
     * @param int $y1 Position Y1 en cUA
     * @param int $z1 Position Z1 en cUA
     * @param int $x2 Position X2 en cUA
     * @param int $y2 Position Y2 en cUA
     * @param int $z2 Position Z2 en cUA
     * @return int Distance en cUA
     */
    public static function distance3D(int $x1, int $y1, int $z1, int $x2, int $y2, int $z2): int
    {
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        $dz = $z2 - $z1;

        return (int)round(sqrt($dx * $dx + $dy * $dy + $dz * $dz));
    }

    /**
     * Calcule la distance entre deux tableaux de position
     *
     * @param array $pos1 Position 1 avec x/y/z ou position_x/y/z
     * @param array $pos2 Position 2 avec x/y/z ou position_x/y/z
     * @return float Distance en cUA
     */
    public static function distanceEntrePositions(array $pos1, array $pos2): float
    {
        return self::distance3D(
            $pos1['x'] ?? $pos1['position_x'] ?? 0,
            $pos1['y'] ?? $pos1['position_y'] ?? 0,
            $pos1['z'] ?? $pos1['position_z'] ?? 0,
            $pos2['x'] ?? $pos2['position_x'] ?? 0,
            $pos2['y'] ?? $pos2['position_y'] ?? 0,
            $pos2['z'] ?? $pos2['position_z'] ?? 0
        );
    }

    /**
     * Calcule la distance totale incluant les secteurs
     *
     * @param array $pos1 Position 1 avec secteur_x/y/z et position_x/y/z
     * @param array $pos2 Position 2 avec secteur_x/y/z et position_x/y/z
     * @return float Distance totale en cUA
     */
    public static function distanceTotale(array $pos1, array $pos2): float
    {
        $x1 = ($pos1['secteur_x'] ?? 0) * self::CUA_PER_AL + ($pos1['position_x'] ?? 0);
        $y1 = ($pos1['secteur_y'] ?? 0) * self::CUA_PER_AL + ($pos1['position_y'] ?? 0);
        $z1 = ($pos1['secteur_z'] ?? 0) * self::CUA_PER_AL + ($pos1['position_z'] ?? 0);

        $x2 = ($pos2['secteur_x'] ?? 0) * self::CUA_PER_AL + ($pos2['position_x'] ?? 0);
        $y2 = ($pos2['secteur_y'] ?? 0) * self::CUA_PER_AL + ($pos2['position_y'] ?? 0);
        $z2 = ($pos2['secteur_z'] ?? 0) * self::CUA_PER_AL + ($pos2['position_z'] ?? 0);

        return sqrt(
            pow($x2 - $x1, 2) +
            pow($y2 - $y1, 2) +
            pow($z2 - $z1, 2)
        );
    }

    /**
     * Convertir une position de tableau associatif
     *
     * @param array $position ['x' => float, 'y' => float, 'z' => float] en AL
     * @return array ['x' => int, 'y' => int, 'z' => int] en cUA
     */
    public static function positionAlToCua(array $position): array
    {
        return [
            'x' => self::alToCua($position['x'] ?? 0),
            'y' => self::alToCua($position['y'] ?? 0),
            'z' => self::alToCua($position['z'] ?? 0),
        ];
    }

    /**
     * Convertir une position de tableau associatif
     *
     * @param array $position ['x' => int, 'y' => int, 'z' => int] en cUA
     * @return array ['x' => float, 'y' => float, 'z' => float] en AL
     */
    public static function positionCuaToAl(array $position): array
    {
        return [
            'x' => self::cuaToAl($position['x'] ?? 0),
            'y' => self::cuaToAl($position['y'] ?? 0),
            'z' => self::cuaToAl($position['z'] ?? 0),
        ];
    }
}
