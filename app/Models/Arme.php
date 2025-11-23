<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arme extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'type',
        'degats_min',
        'degats_max',
        'portee',
        'cadence',
        'precision',
        'energie_tir',
        'niveau_requis',
        'prix',
        'taille',
        'description',
        'actif',
    ];

    protected $casts = [
        'degats_min' => 'integer',
        'degats_max' => 'integer',
        'portee' => 'integer',
        'cadence' => 'integer',
        'precision' => 'integer',
        'energie_tir' => 'integer',
        'niveau_requis' => 'integer',
        'prix' => 'integer',
        'actif' => 'boolean',
    ];

    /**
     * Calculer les degats d'un tir
     */
    public function calculerDegats(): int
    {
        return rand($this->degats_min, $this->degats_max);
    }

    /**
     * Verifier si le tir touche
     */
    public function tenterToucher(int $bonus_precision = 0, int $esquive_cible = 0): bool
    {
        $chance = $this->precision + $bonus_precision - $esquive_cible;
        $chance = max(5, min(95, $chance)); // Entre 5% et 95%
        return rand(1, 100) <= $chance;
    }

    /**
     * Effectuer une attaque complete
     */
    public function attaquer(int $bonus_precision = 0, int $esquive_cible = 0): array
    {
        $resultats = [];

        for ($i = 0; $i < $this->cadence; $i++) {
            $touche = $this->tenterToucher($bonus_precision, $esquive_cible);
            $degats = $touche ? $this->calculerDegats() : 0;

            $resultats[] = [
                'touche' => $touche,
                'degats' => $degats,
            ];
        }

        return $resultats;
    }

    /**
     * Obtenir les degats moyens
     */
    public function getDegatsMoyens(): float
    {
        return ($this->degats_min + $this->degats_max) / 2;
    }

    /**
     * Obtenir le DPS theorique
     */
    public function getDPS(): float
    {
        $degats_moyens = $this->getDegatsMoyens();
        $chance_toucher = $this->precision / 100;
        return $degats_moyens * $this->cadence * $chance_toucher;
    }

    /**
     * Cout en energie pour une salve complete
     */
    public function getCoutEnergieSalve(): int
    {
        return $this->energie_tir * $this->cadence;
    }
}
