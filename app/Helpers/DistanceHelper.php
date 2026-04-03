<?php

namespace App\Helpers;

class DistanceHelper
{
    /**
     * Convert AU to Gm/G km format
     * 1 AU = 149.6 Gm (gigameters)
     * 1 Gm = 1,000 km
     * 
     * @param float $distanceAU Distance in Astronomical Units
     * @return string Formatted distance string
     */
    public static function formatDistance(float $distanceAU): string
    {
        $distanceGm = $distanceAU * 149.6;
        
        if ($distanceGm >= 1000) {
            // Convert to G km (gigakilometers)
            $distanceGkm = $distanceGm / 1000;
            return number_format($distanceGkm, 2) . ' G km';
        } else {
            // Keep in Gm
            return number_format($distanceGm, 2) . ' Gm';
        }
    }
    
    /**
     * Convert AU to Gm (for calculations)
     * 
     * @param float $distanceAU Distance in Astronomical Units
     * @return float Distance in Gigameters
     */
    public static function auToGm(float $distanceAU): float
    {
        return $distanceAU * 149.6;
    }
    
    /**
     * Convert Gm to AU (for calculations)
     * 
     * @param float $distanceGm Distance in Gigameters
     * @return float Distance in Astronomical Units
     */
    public static function gmToAu(float $distanceGm): float
    {
        return $distanceGm / 149.6;
    }
}