<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Base extends Model
{
    protected $table = 'bases';

    protected $fillable = [
        'objet_spatial_id',
        'type_base',
        'gestionnaire_id',
        'modules',
        'production_energie',
        'production_ressources',
        'population_max',
        'population_actuelle',
        'defense',
        'est_operationnelle',
        'date_logs',
    ];

    protected $casts = [
        'modules' => 'array',
        'production_ressources' => 'array',
        'est_operationnelle' => 'boolean',
        'date_logs' => 'array',
    ];

    // Relations
    public function objetSpatial(): BelongsTo
    {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
    }

    public function gestionnaire(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'gestionnaire_id');
    }

    // Méthodes de base (selon GDD)
    public function ajouterModule(array $module): bool
    {
        $modules = $this->modules ?? [];

        // Vérifier capacité max (selon type base)
        if ($this->type_base === 'arche' && count($modules) >= 5) {
            return false;
        }

        $modules[] = $module;
        $this->modules = $modules;
        return true;
    }

    public function produire(): array
    {
        if (!$this->est_operationnelle) {
            return [
                'success' => false,
                'erreur' => 'Base non opérationnelle',
            ];
        }

        $production = [
            'energie' => $this->production_energie,
            'ressources' => $this->production_ressources ?? [],
        ];

        return [
            'success' => true,
            'production' => $production,
        ];
    }

    public function changerGestionnaire(Personnage $nouveauGestionnaire): void
    {
        $this->gestionnaire_id = $nouveauGestionnaire->id;
    }
}
