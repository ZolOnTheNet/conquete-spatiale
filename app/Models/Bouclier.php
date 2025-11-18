<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bouclier extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'type',
        'points_max',
        'regeneration',
        'resistance',
        'vs_laser',
        'vs_canon',
        'vs_missile',
        'vs_plasma',
        'vs_emp',
        'energie_maintien',
        'niveau_requis',
        'prix',
        'taille',
        'description',
        'actif',
    ];

    protected $casts = [
        'points_max' => 'integer',
        'regeneration' => 'integer',
        'resistance' => 'integer',
        'vs_laser' => 'integer',
        'vs_canon' => 'integer',
        'vs_missile' => 'integer',
        'vs_plasma' => 'integer',
        'vs_emp' => 'integer',
        'energie_maintien' => 'integer',
        'niveau_requis' => 'integer',
        'prix' => 'integer',
        'actif' => 'boolean',
    ];

    /**
     * Calculer les degats reduits par le bouclier
     */
    public function absorberDegats(int $degats, string $type_arme, int $points_actuels): array
    {
        // Resistance de base
        $reduction_base = $this->resistance;

        // Bonus contre type d'arme
        $bonus_type = match($type_arme) {
            'laser' => $this->vs_laser,
            'canon' => $this->vs_canon,
            'missile' => $this->vs_missile,
            'plasma' => $this->vs_plasma,
            'emp' => $this->vs_emp,
            default => 0,
        };

        // Calculer reduction totale
        $reduction_pourcent = min(80, $reduction_base + $bonus_type); // Max 80%
        $degats_reduits = (int)floor($degats * (1 - $reduction_pourcent / 100));

        // Absorber avec les points de bouclier
        if ($points_actuels >= $degats_reduits) {
            // Bouclier absorbe tout
            return [
                'degats_bouclier' => $degats_reduits,
                'degats_coque' => 0,
                'bouclier_restant' => $points_actuels - $degats_reduits,
            ];
        } else {
            // Bouclier perce
            $degats_coque = $degats_reduits - $points_actuels;
            return [
                'degats_bouclier' => $points_actuels,
                'degats_coque' => $degats_coque,
                'bouclier_restant' => 0,
            ];
        }
    }

    /**
     * Regenerer le bouclier
     */
    public function regenerer(int $points_actuels): int
    {
        return min($this->points_max, $points_actuels + $this->regeneration);
    }

    /**
     * Obtenir l'efficacite globale du bouclier
     */
    public function getEfficaciteGlobale(): float
    {
        // Moyenne des resistances specifiques + resistance de base
        $moyenne_vs = ($this->vs_laser + $this->vs_canon + $this->vs_missile + $this->vs_plasma + $this->vs_emp) / 5;
        return $this->resistance + $moyenne_vs;
    }

    /**
     * Temps pour regeneration complete
     */
    public function getTempsRegeneration(): int
    {
        if ($this->regeneration <= 0) return PHP_INT_MAX;
        return (int)ceil($this->points_max / $this->regeneration);
    }
}
