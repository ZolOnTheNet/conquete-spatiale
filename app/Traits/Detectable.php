<?php

namespace App\Traits;

use App\Helpers\CoordinatesHelper;
use App\Models\Personnage;

/**
 * Trait pour les objets détectables par scan
 *
 * Requiert que le modèle ait les attributs :
 * - detectabilite_base (float)
 * - poi_connu (bool, optionnel)
 * - masse (float, optionnel)
 */
trait Detectable
{
    /**
     * Calcule le score de détection de cet objet
     *
     * @param Personnage $personnage Le personnage qui scanne
     * @param float $distanceCua Distance en cUA
     * @param int $puissanceScan Puissance du scanner
     * @return float Score de détection (0-100+)
     */
    public function getScoreDetection(Personnage $personnage, float $distanceCua, int $puissanceScan): float
    {
        $detectabiliteBase = $this->detectabilite_base ?? 0;

        // Facteur de distance (plus c'est loin, plus c'est dur)
        $facteurDistance = 1;
        if ($distanceCua > 0) {
            $distanceUa = CoordinatesHelper::cuaToUa($distanceCua);
            $facteurDistance = max(0.1, 1 - ($distanceUa / 1000));
        }

        $score = ($detectabiliteBase * $puissanceScan * $facteurDistance) / 100;

        return round($score, 2);
    }

    /**
     * Calcule la détectabilité effective avec modificateurs
     *
     * @return float Détectabilité effective
     */
    public function calculerDetectabilite(): float
    {
        $base = $this->detectabilite_base ?? 0;

        // Modificateur de masse (plus gros = plus détectable)
        $masse = $this->masse ?? 0;
        $modMasse = $masse > 0 ? log10($masse + 1) * 5 : 0;

        // Modificateur d'activité
        $modActivite = 0;
        if (property_exists($this, 'est_actif') && $this->est_actif) {
            $modActivite = 10;
        }

        return max(0, $base + $modMasse + $modActivite);
    }

    /**
     * Marque l'objet comme découvert (POI connu)
     */
    public function marquerDecouvert(): void
    {
        if (property_exists($this, 'poi_connu') || isset($this->poi_connu)) {
            $this->poi_connu = true;
            $this->save();
        }
    }

    /**
     * Vérifie si l'objet est découvert
     */
    public function estDecouvert(): bool
    {
        return $this->poi_connu ?? false;
    }

    /**
     * Vérifie si l'objet est détectable (détectabilité > 0)
     */
    public function estDetectable(): bool
    {
        return ($this->detectabilite_base ?? 0) > 0;
    }
}
