<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaisseau extends Model
{
    protected $table = 'vaisseaux';

    protected $fillable = [
        'objet_spatial_id',
        'modele',
        'type_propulsion',
        'mode',
        'reserve',
        'energie_actuelle',
        'vitesse_conventionnelle',
        'vitesse_saut',
        'part_panne',
        'combustible',
        'efficacite',
        'type_combustible',
        'recuperation',
        'init_conventionnel',
        'init_hyperespace',
        'coef_conventionnel',
        'coef_hyperespace',
        'coef_pa_mn',
        'coef_pa_he',
        'max_soutes',
        'place_soute',
        'masse_variable',
        'soutes',
        'emplacements_armes',
        'nb_armes',
        'vetuste',
        'complexite_fct',
        'score_panne',
        'score_entretien',
        'pannes_actuelles',
        'system_informatique',
        'programmes',
        'emplacements',
        'date_logs',
    ];

    protected $casts = [
        'soutes' => 'array',
        'emplacements_armes' => 'array',
        'pannes_actuelles' => 'array',
        'programmes' => 'array',
        'emplacements' => 'array',
        'date_logs' => 'array',
    ];

    // Relations
    public function objetSpatial(): BelongsTo
    {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
    }

    // Méthodes de propulsion (selon GDD)
    public function calculerConsommationConventionnelle(float $distance): float
    {
        // Formule: InitConv + (Distance × CoefConv)
        return $this->init_conventionnel + ($distance * $this->coef_conventionnel);
    }

    public function calculerConsommationHE(float $distance): float
    {
        // Formule: InitHE + (Distance × CoefHE)
        return $this->init_hyperespace + ($distance * $this->coef_hyperespace);
    }

    public function calculerNbPA(float $distance, string $mode = 'conventionnel'): int
    {
        if ($mode === 'hyperespace' || $mode === 'HE') {
            // PA = Distance × CoefPAHE
            return (int)ceil($distance * $this->coef_pa_he);
        } else {
            // PA = Distance × CoefPAMN
            return (int)ceil($distance * $this->coef_pa_mn);
        }
    }

    public function rechargerEnergie(float $quantite): void
    {
        $this->energie_actuelle = min(
            $this->reserve,
            $this->energie_actuelle + $quantite
        );
    }

    public function consommerEnergie(float $quantite): bool
    {
        if ($this->energie_actuelle >= $quantite) {
            $this->energie_actuelle -= $quantite;
            return true;
        }
        return false;
    }

    public function deplacer(ObjetSpatial $destination, string $mode = 'conventionnel'): array
    {
        $distance = $this->objetSpatial->calculerDistance($destination);
        $consommation = $mode === 'hyperespace'
            ? $this->calculerConsommationHE($distance)
            : $this->calculerConsommationConventionnelle($distance);
        $pa = $this->calculerNbPA($distance, $mode);

        if ($this->consommerEnergie($consommation)) {
            // Mettre à jour position
            $this->objetSpatial->setPosition(
                $destination->secteur_x,
                $destination->secteur_y,
                $destination->secteur_z,
                $destination->position_x,
                $destination->position_y,
                $destination->position_z
            );
            $this->objetSpatial->save();

            return [
                'success' => true,
                'consommation' => $consommation,
                'pa' => $pa,
                'energie_restante' => $this->energie_actuelle,
            ];
        }

        return [
            'success' => false,
            'erreur' => 'Énergie insuffisante',
            'manquant' => $consommation - $this->energie_actuelle,
        ];
    }
}
