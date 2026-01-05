<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Detectable;

class Station extends Model
{
    use HasFactory, Detectable;

    protected $fillable = [
        'objet_spatial_id',
        'nom',
        'type',
        'planete_id',
        'systeme_stellaire_id',
        'orbite_rayon_ua',
        'orbite_angle',
        'description',
        'capacite_amarrage',
        'population',
        'commerciale',
        'industrielle',
        'militaire',
        'reparations',
        'ravitaillement',
        'medical',
        'faction_id',
        'reputation_requise',
        'accessible',
        'gere_admin',
        'raison_inaccessible',
        'detectabilite_base',
        'poi_connu',
        'nb_modules',
        'nb_mines_associees',
    ];

    protected $casts = [
        'commerciale' => 'boolean',
        'industrielle' => 'boolean',
        'militaire' => 'boolean',
        'reparations' => 'boolean',
        'ravitaillement' => 'boolean',
        'medical' => 'boolean',
        'accessible' => 'boolean',
        'gere_admin' => 'boolean',
        'poi_connu' => 'boolean',
        'orbite_rayon_ua' => 'decimal:6',
        'orbite_angle' => 'decimal:4',
        'detectabilite_base' => 'decimal:2',
    ];

    // Relations

    /**
     * Objet spatial associé (position 3D complète)
     *
     * IMPORTANT : Depuis migration 2025_12_31_140000, chaque Station
     * hérite d'un ObjetSpatial pour gérer sa position 3D
     *
     * @see ObjetSpatial
     */
    public function objetSpatial(): BelongsTo
    {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
    }

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
     * Mines associées à cette station (à proximité ou sur la même planète)
     */
    public function mines(): BelongsToMany
    {
        return $this->belongsToMany(Mine::class, 'mine_station')
            ->withPivot('distance_km')
            ->withTimestamps();
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
     *
     * PRIORITÉ : Si objet_spatial_id existe, utiliser la position de l'ObjetSpatial
     * SINON : Fallback sur l'ancienne méthode (legacy)
     */
    public function getPosition(): array
    {
        // Nouvelle méthode : Utiliser ObjetSpatial
        if ($this->objet_spatial_id && $this->objetSpatial) {
            return $this->objetSpatial->getPosition();
        }

        // Fallback legacy (pour stations non migrées)
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

    /**
     * Obtenir la position orbitale précise de la station
     * Utilisé par ObjetSpatial.getPositionEffective()
     *
     * @param float|null $timestampJours Timestamp du jeu en jours
     * @return array Position absolue [secteur_x/y/z, position_x/y/z]
     */
    public function getPositionOrbitale(?float $timestampJours = null): array
    {
        // Si pas en orbite, retourner position système
        if (!$this->planete_id) {
            $systeme = $this->systemeStellaire;
            return [
                'secteur_x' => $systeme->secteur_x ?? 0,
                'secteur_y' => $systeme->secteur_y ?? 0,
                'secteur_z' => $systeme->secteur_z ?? 0,
                'position_x' => $systeme->position_x ?? 0,
                'position_y' => $systeme->position_y ?? 0,
                'position_z' => $systeme->position_z ?? 0,
            ];
        }

        $planete = $this->planete;
        if (!$planete) {
            return [
                'secteur_x' => 0,
                'secteur_y' => 0,
                'secteur_z' => 0,
                'position_x' => 0,
                'position_y' => 0,
                'position_z' => 0,
            ];
        }

        // Position planète
        $posPlanete = $planete->getPositionOrbitale($timestampJours);

        // Offset orbital station
        $rayonCua = ($this->orbite_rayon_ua ?? 0.05) * 100;
        $angle = $this->orbite_angle ?? 0;

        return [
            'secteur_x' => $planete->systemeStellaire->secteur_x ?? 0,
            'secteur_y' => $planete->systemeStellaire->secteur_y ?? 0,
            'secteur_z' => $planete->systemeStellaire->secteur_z ?? 0,
            'position_x' => (int)($posPlanete['x'] + ($rayonCua * cos($angle))),
            'position_y' => (int)($posPlanete['y'] + ($rayonCua * sin($angle))),
            'position_z' => (int)$posPlanete['z'],
        ];
    }

    // === SYSTÈME DE DÉTECTION ===

    /**
     * Calcule le score de détection de la station (RÈGLE INTRA-SECTEUR)
     *
     * PRIORITÉ : Si objet_spatial_id existe, déléguer à ObjetSpatial
     * SINON : Fallback sur l'ancienne méthode (legacy)
     *
     * @param int $fromSecteurX Secteur X du scanner (AL)
     * @param int $fromSecteurY Secteur Y du scanner (AL)
     * @param int $fromSecteurZ Secteur Z du scanner (AL)
     * @param int $fromPositionX Position X du scanner (cUA)
     * @param int $fromPositionY Position Y du scanner (cUA)
     * @param int $fromPositionZ Position Z du scanner (cUA)
     * @return float Score de détection requis
     */
    public function getScoreDetection(
        int $fromSecteurX,
        int $fromSecteurY,
        int $fromSecteurZ,
        int $fromPositionX = 0,
        int $fromPositionY = 0,
        int $fromPositionZ = 0
    ): float {
        // Nouvelle méthode : Déléguer à ObjetSpatial
        if ($this->objet_spatial_id && $this->objetSpatial) {
            return $this->objetSpatial->getScoreDetection(
                $fromSecteurX,
                $fromSecteurY,
                $fromSecteurZ,
                $fromPositionX,
                $fromPositionY,
                $fromPositionZ
            );
        }

        // Fallback legacy (pour stations non migrées)
        // Si déjà connue, seuil de détection = 0 (apparaît automatiquement)
        if ($this->poi_connu) {
            return 0;
        }

        $systeme = $this->systemeStellaire;
        if (!$systeme) {
            return 999999; // Station orpheline, impossible à détecter
        }

        // RÈGLE INTRA-SECTEUR: Vérifier si même secteur
        if ($systeme->secteur_x !== $fromSecteurX ||
            $systeme->secteur_y !== $fromSecteurY ||
            $systeme->secteur_z !== $fromSecteurZ) {
            // Pas dans le même secteur → non détectable en local
            return 999999;
        }

        // Distance calculée UNIQUEMENT avec positions (cUA) - secteurs ignorés
        $dx = $systeme->position_x - $fromPositionX;
        $dy = $systeme->position_y - $fromPositionY;
        $dz = $systeme->position_z - $fromPositionZ;

        $distance_cUA = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        // Si detectabilite_base n'est pas initialisé (0 ou -1), calculer
        $detectabilite = $this->detectabilite_base;
        if ($detectabilite <= 0) {
            $detectabilite = $this->calculerDetectabilite();
        }

        // Formule INTRA-SECTEUR: (distance_cUA / 1000) × detectabilite_base
        return ($distance_cUA / 1000) * $detectabilite;
    }

    /**
     * Calcule la détectabilité de base si non initialisée
     * Formule: 150 - modules - (10 × nb_mines) - (population / 1000)
     * Plus la station est grande et active, plus elle est facile à détecter
     */
    public function calculerDetectabilite(): float
    {
        $modules = $this->nb_modules ?? 5;
        $mines = $this->nb_mines_associees ?? 0;
        $population = $this->population ?? 100;

        return max(1, 150 - $modules - (10 * $mines) - ($population / 1000));
    }

    /**
     * Marque la station comme découverte (poi_connu = true)
     */
    public function marquerDecouvert(): void
    {
        $this->poi_connu = true;
        $this->save();
    }
}
