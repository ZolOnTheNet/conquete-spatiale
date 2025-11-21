<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'planete_id',
        'systeme_stellaire_id',
        'orbite_rayon_ua',
        'orbite_angle',
        'description',
        'capacite_amarrage',
        'commerciale',
        'industrielle',
        'militaire',
        'reparations',
        'ravitaillement',
        'medical',
        'faction_id',
        'reputation_requise',
        'accessible',
        'raison_inaccessible',
    ];

    protected $casts = [
        'commerciale' => 'boolean',
        'industrielle' => 'boolean',
        'militaire' => 'boolean',
        'reparations' => 'boolean',
        'ravitaillement' => 'boolean',
        'medical' => 'boolean',
        'accessible' => 'boolean',
        'orbite_rayon_ua' => 'decimal:6',
        'orbite_angle' => 'decimal:4',
    ];

    // Relations
    public function planete(): BelongsTo
    {
        return $this->belongsTo(Planete::class);
    }

    public function systemeStellaire(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * Produits disponibles sur le marché
     */
    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'marche_stations')
            ->withPivot([
                'stock_actuel',
                'stock_min',
                'stock_max',
                'production_par_jour',
                'consommation_par_jour',
                'type_economique',
                'prix_achat_joueur',
                'prix_vente_joueur',
                'derniere_mise_a_jour_prix',
                'disponible_achat',
                'disponible_vente',
            ])
            ->withTimestamps();
    }

    /**
     * Marché de la station
     */
    public function marches(): HasMany
    {
        return $this->hasMany(MarcheStation::class);
    }

    /**
     * Vérifie si la station est accessible pour un personnage
     */
    public function estAccessiblePour(Personnage $personnage): bool
    {
        if (!$this->accessible) {
            return false;
        }

        // Vérifier réputation si nécessaire
        if ($this->faction_id && $this->reputation_requise > 0) {
            $reputation = Reputation::where('personnage_id', $personnage->id)
                ->where('faction_id', $this->faction_id)
                ->first();

            return $reputation && $reputation->niveau >= $this->reputation_requise;
        }

        return true;
    }

    /**
     * Obtenir la position complète de la station
     */
    public function getPosition(): array
    {
        $systeme = $this->systemeStellaire;

        $position = [
            'secteur_x' => $systeme->secteur_x,
            'secteur_y' => $systeme->secteur_y,
            'secteur_z' => $systeme->secteur_z,
            'position_x' => $systeme->position_x,
            'position_y' => $systeme->position_y,
            'position_z' => $systeme->position_z,
        ];

        // Si en orbite d'une planète, ajuster position
        if ($this->planete_id && $this->orbite_rayon_ua) {
            // TODO: Calculer position orbitale précise
            $position['orbite'] = [
                'rayon_ua' => $this->orbite_rayon_ua,
                'angle' => $this->orbite_angle,
            ];
        }

        return $position;
    }
}
