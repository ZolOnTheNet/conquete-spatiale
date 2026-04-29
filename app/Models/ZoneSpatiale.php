<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\Detectable;

/**
 * Modèle ZoneSpatiale
 *
 * Représente une zone étendue dans l'espace (ceinture d'astéroïdes, nuage de débris, etc.)
 * Structure géométrique : anneau toroïdal défini par rayon_min/max et azimut_debut/fin
 *
 * Hiérarchie :
 * - SystemeStellaire → ZoneSpatiale → ObjetSpatial (astéroïdes notables)
 *
 * @property int $id
 * @property string $type Type de zone (ceinture_asteroides, nuage_debris, etc.)
 * @property string|null $nom Nom de la zone
 * @property int $systeme_stellaire_id FK vers systeme
 * @property int $rayon_min Rayon intérieur en cUA
 * @property int $rayon_max Rayon extérieur en cUA
 * @property float $azimut_debut Angle début en radians (0 à 2π)
 * @property float $azimut_fin Angle fin en radians
 * @property int $densite Nombre virtuel d'objets
 * @property float $vitesse_traversee_modif Multiplicateur vitesse
 * @property float $risque_collision Probabilité collision
 * @property float $detectabilite_base Score détection
 * @property bool $poi_connu Zone découverte
 * @property string|null $parent_type Type parent polymorphique
 * @property int|null $parent_id ID parent polymorphique
 * @property string|null $description
 * @property array|null $proprietes
 *
 * @see docs/game-design/GDD_Asteroides.md
 */
class ZoneSpatiale extends Model
{
    use Detectable;

    protected $table = 'zones_spatiales';

    protected $fillable = [
        'type',
        'nom',
        'systeme_stellaire_id',
        'rayon_min',
        'rayon_max',
        'azimut_debut',
        'azimut_fin',
        'densite',
        'vitesse_traversee_modif',
        'risque_collision',
        'detectabilite_base',
        'poi_connu',
        'parent_type',
        'parent_id',
        'description',
        'proprietes',
    ];

    protected $casts = [
        'rayon_min' => 'integer',
        'rayon_max' => 'integer',
        'azimut_debut' => 'decimal:8', // Radians (ex: 3.14159265)
        'azimut_fin' => 'decimal:8',
        'densite' => 'integer',
        'vitesse_traversee_modif' => 'decimal:2',
        'risque_collision' => 'decimal:2',
        'detectabilite_base' => 'decimal:2',
        'poi_connu' => 'boolean',
        'proprietes' => 'array',
    ];

    // ========== RELATIONS ==========

    /**
     * Système stellaire auquel appartient cette zone
     */
    public function systeme(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_stellaire_id');
    }

    /**
     * Objets spatiaux notables dans cette zone
     * Ex: astéroïdes remarquables comme Cérès, Vesta, Pallas
     */
    public function objetNotables(): MorphMany
    {
        return $this->morphMany(ObjetSpatial::class, 'parent');
    }

    /**
     * Astéroïdes notables spécifiquement (filtre sur type)
     */
    public function asteroideNotables()
    {
        return $this->objetNotables()->whereIn('type', ['asteroide', 'asteroide_notable']);
    }

    /**
     * Parent polymorphique de cette zone (optionnel)
     * Ex: zone imbriquée dans une plus grande zone
     */
    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Sous-zones enfants (zones imbriquées)
     */
    public function sousZones(): MorphMany
    {
        return $this->morphMany(ZoneSpatiale::class, 'parent');
    }

    // ========== MÉTHODES GÉOMÉTRIQUES ==========

    /**
     * Vérifie si une position (x,y) est dans la zone
     *
     * @param int $posX Position X en cUA
     * @param int $posY Position Y en cUA
     * @return bool True si position dans la zone
     */
    public function contientPosition(int $posX, int $posY): bool
    {
        // Distance à l'étoile (centre = 0,0)
        $distance = sqrt($posX ** 2 + $posY ** 2);

        // Vérifier rayon
        if ($distance < $this->rayon_min || $distance > $this->rayon_max) {
            return false;
        }

        // Calculer angle de la position (azimut)
        $angle = atan2($posY, $posX); // Retourne -π à π

        // Normaliser angle entre 0 et 2π
        if ($angle < 0) {
            $angle += 2 * M_PI;
        }

        // Vérifier si dans l'arc de cercle
        // IMPORTANT: Gérer le cas où l'arc traverse 0° (ex: 350° à 10°)
        if ($this->azimut_debut <= $this->azimut_fin) {
            // Cas normal: arc ne traverse pas 0°
            return $angle >= $this->azimut_debut && $angle <= $this->azimut_fin;
        } else {
            // Cas spécial: arc traverse 0° (ex: 5.5 rad à 0.5 rad)
            return $angle >= $this->azimut_debut || $angle <= $this->azimut_fin;
        }
    }

    /**
     * Calcule la distance minimale d'une position à la bordure de la zone
     *
     * @param int $posX Position X en cUA
     * @param int $posY Position Y en cUA
     * @return int Distance en cUA
     */
    public function distanceABordure(int $posX, int $posY): int
    {
        $distance = sqrt($posX ** 2 + $posY ** 2);

        // Distance à la bordure la plus proche
        $distanceBordureMin = abs($distance - $this->rayon_min);
        $distanceBordureMax = abs($distance - $this->rayon_max);

        return (int) min($distanceBordureMin, $distanceBordureMax);
    }

    // ========== SYSTÈME DE DÉTECTION ==========

    /**
     * Calcule le score de détection de la zone
     *
     * RÈGLE: Zones sont détectées par distance à la BORDURE (pas au centre)
     * Plus la zone est grande, plus elle est facile à détecter
     *
     * @param int $fromPosX Position observateur X en cUA
     * @param int $fromPosY Position observateur Y en cUA
     * @return float Score de détection (plus bas = plus facile)
     */
    public function getScoreDetection(int $fromPosX, int $fromPosY): float
    {
        // Si déjà connue, seuil = 0 (apparaît automatiquement)
        if ($this->poi_connu) {
            return 0;
        }

        // Distance à la bordure la plus proche
        $distanceBordure = $this->distanceABordure($fromPosX, $fromPosY);

        // Formule: (distance_bordure / 1000) × detectabilite_base
        // Note: detectabilite_base des zones est plus BAS (ex: 30 vs 60 pour astéroïdes)
        // donc zones sont détectées AVANT les objets dedans
        return ($distanceBordure / 1000) * $this->detectabilite_base;
    }

    /**
     * Marque la zone comme découverte
     */
    public function marquerDecouvert(): void
    {
        $this->poi_connu = true;
        $this->save();
    }

    // ========== GAMEPLAY ==========

    /**
     * Calcule le risque de collision pour une distance parcourue
     *
     * @param float $distanceUA Distance parcourue en UA
     * @return float Probabilité de collision (0 à 1)
     */
    public function calculerRisqueCollision(float $distanceUA): float
    {
        // Formule: risque augmente avec distance parcourue dans zone
        return min(1.0, $this->risque_collision * $distanceUA);
    }

    /**
     * Retourne le multiplicateur de vitesse dans cette zone
     *
     * @return float Multiplicateur (ex: 0.5 = vitesse divisée par 2)
     */
    public function getModificateurVitesse(): float
    {
        return $this->vitesse_traversee_modif;
    }

    /**
     * Génère des astéroïdes procéduraux autour d'une position
     * (pour affichage visuel, non persistés en BD)
     *
     * @param int $posX Position centrale X en cUA
     * @param int $posY Position centrale Y en cUA
     * @param int $rayonRecherche Rayon de recherche en cUA (ex: 5000 = 50 UA)
     * @param int $nombreMax Nombre max d'astéroïdes à générer
     * @return array Liste d'astéroïdes procéduraux
     */
    public function genererAsteroidesProceduraux(int $posX, int $posY, int $rayonRecherche = 5000, int $nombreMax = 20): array
    {
        // Seed déterministe basé sur position et ID de zone
        $seed = $this->id * 1000 + (int)($posX / 100) * 10 + (int)($posY / 100);
        mt_srand($seed);

        $asteroides = [];
        $densite = min($this->densite, $nombreMax);

        for ($i = 0; $i < $densite; $i++) {
            // Position aléatoire autour du point central
            $angle = mt_rand() / mt_getrandmax() * 2 * M_PI;
            $distance = mt_rand() / mt_getrandmax() * $rayonRecherche;

            $astX = $posX + (int)($distance * cos($angle));
            $astY = $posY + (int)($distance * sin($angle));

            // Vérifier que c'est bien dans la zone
            if (!$this->contientPosition($astX, $astY)) {
                continue;
            }

            $asteroides[] = [
                'nom' => "Astéroïde #" . ($this->id * 1000 + $i),
                'position_x' => $astX,
                'position_y' => $astY,
                'position_z' => 0,
                'masse' => mt_rand(100, 5000),
                'type' => 'asteroide_procedural',
                'procedural' => true, // Flag pour identifier
            ];
        }

        // Reset seed
        mt_srand();

        return $asteroides;
    }

    // ========== HELPERS D'AFFICHAGE ==========

    /**
     * Retourne le nom formaté de la zone
     */
    public function getNomComplet(): string
    {
        if ($this->nom) {
            return $this->nom;
        }

        return match($this->type) {
            'ceinture_asteroides' => 'Ceinture d\'astéroïdes',
            'nuage_debris' => 'Nuage de débris',
            'nebuleuse' => 'Nébuleuse',
            default => 'Zone spatiale',
        };
    }

    /**
     * Retourne l'icône pour affichage
     */
    public function getIcone(): string
    {
        return match($this->type) {
            'ceinture_asteroides' => '🌌',
            'nuage_debris' => '💫',
            'nebuleuse' => '☁️',
            default => '⭕',
        };
    }

    /**
     * Retourne les rayons en UA pour affichage
     */
    public function getRayonsUA(): array
    {
        return [
            'min' => $this->rayon_min / 100,
            'max' => $this->rayon_max / 100,
        ];
    }

    /**
     * Retourne les azimuts en degrés pour affichage
     */
    public function getAzimutsDegres(): array
    {
        return [
            'debut' => round($this->azimut_debut * 180 / M_PI, 2),
            'fin' => round($this->azimut_fin * 180 / M_PI, 2),
        ];
    }

    /**
     * Vérifie si la zone forme un cercle complet
     */
    public function estCercleComplet(): bool
    {
        // Si azimut_fin - azimut_debut ≈ 2π, c'est un cercle complet
        $arc = abs($this->azimut_fin - $this->azimut_debut);
        return abs($arc - 2 * M_PI) < 0.01; // Tolérance de 0.01 rad
    }
}
