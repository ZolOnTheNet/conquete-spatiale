<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Decouverte extends Model
{
    protected $table = 'decouvertes';

    protected $fillable = [
        'personnage_id',
        'systeme_stellaire_id',
        'resultat_scan',
        'seuil_detection',
        'distance_decouverte',
        'decouvert_a',
        'coordonnees_connues',
        'type_etoile_connu',
        'nb_planetes_connu',
        'visite',
        'notes',
    ];

    protected $casts = [
        'coordonnees_connues' => 'boolean',
        'type_etoile_connu' => 'boolean',
        'nb_planetes_connu' => 'boolean',
        'visite' => 'boolean',
        'decouvert_a' => 'datetime',
    ];

    // Relations
    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function systemeStellaire(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_stellaire_id');
    }

    /**
     * Retourne les informations révélées selon le niveau de connaissance
     */
    public function getInformationsRevelees(): array
    {
        $systeme = $this->systemeStellaire;

        $info = [
            'id' => $systeme->id,
            'nom' => $systeme->nom,
        ];

        // Coordonnées toujours révélées
        if ($this->coordonnees_connues) {
            $info['secteur_x'] = $systeme->secteur_x;
            $info['secteur_y'] = $systeme->secteur_y;
            $info['secteur_z'] = $systeme->secteur_z;
            $info['position_x'] = $systeme->position_x;
            $info['position_y'] = $systeme->position_y;
            $info['position_z'] = $systeme->position_z;
            $info['distance_decouverte'] = $this->distance_decouverte;
        }

        // Type d'étoile révélé après scan réussi
        if ($this->type_etoile_connu) {
            $info['type_etoile'] = $systeme->type_etoile;
            $info['couleur'] = $systeme->couleur;
            $info['temperature'] = $systeme->temperature;
            $info['puissance_solaire'] = $systeme->puissance_solaire;
            $info['puissance'] = $systeme->puissance;
            $info['detectabilite_base'] = $systeme->detectabilite_base;
        }

        // Nombre de planètes révélé après scan réussi
        if ($this->nb_planetes_connu) {
            $info['nb_planetes'] = $systeme->nb_planetes;
            $info['habite'] = $systeme->habite;
        }

        // Visité : toutes informations disponibles
        if ($this->visite) {
            $info['visite'] = true;
            $info['planetes'] = $systeme->planetes()->get()->map(function ($planete) {
                return [
                    'id' => $planete->id,
                    'nom' => $planete->nom,
                    'type' => $planete->type,
                    'habitable' => $planete->habitable,
                    'habitee' => $planete->habitee,
                    'distance_etoile' => $planete->distance_etoile,
                ];
            });
        }

        if ($this->notes) {
            $info['notes'] = $this->notes;
        }

        return $info;
    }

    /**
     * Marque le système comme visité (révèle toutes les infos)
     */
    public function marquerVisite(): void
    {
        $this->update([
            'visite' => true,
            'type_etoile_connu' => true,
            'nb_planetes_connu' => true,
            'coordonnees_connues' => true,
        ]);
    }
}
