<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Helpers\CoordinatesHelper;

class ObjetSpatial extends Model
{
    protected $table = 'objets_spatiaux';

    protected $fillable = [
        'nom',
        'type',
        'secteur_x',
        'secteur_y',
        'secteur_z',
        'position_x',
        'position_y',
        'position_z',
        'azimut',
        'contenu_dans',
        'secteur_id',
        'proprietaire_id',
        'remorque_par',
        'volume',
        'masse',
        'resistance',
        'coef_dommages',
        'date_logs',
        'detectabilite_base',
        'poi_connu',
        'parent_type',
        'parent_id',
    ];

    protected $casts = [
        'secteur_x' => 'integer',
        'secteur_y' => 'integer',
        'secteur_z' => 'integer',
        'position_x' => 'integer', // cUA (centi-UA)
        'position_y' => 'integer', // cUA
        'position_z' => 'integer', // cUA
        'azimut' => 'decimal:2', // Orientation 0-360°
        'date_logs' => 'array',
        'detectabilite_base' => 'decimal:2',
        'poi_connu' => 'boolean',
    ];

    // Relations
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'proprietaire_id');
    }

    public function vaisseau(): HasOne
    {
        return $this->hasOne(Vaisseau::class, 'objet_spatial_id');
    }

    public function base(): HasOne
    {
        return $this->hasOne(Base::class, 'objet_spatial_id');
    }

    public function station(): HasOne
    {
        return $this->hasOne(Station::class, 'objet_spatial_id');
    }

    public function mine(): HasOne
    {
        return $this->hasOne(Mine::class, 'objet_spatial_id');
    }

    /**
     * Relation polymorphique parent
     * Permet de lier cet objet à un parent de type variable :
     * - ZoneSpatiale (ex: astéroïde dans ceinture)
     * - ObjetSpatial (ex: station orbitale autour d'une planète)
     * - SystemeStellaire (ex: objet lié directement au système)
     */
    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Objets enfants de cet objet spatial
     * Ex: stations orbitant autour d'une planète, débris autour d'une épave
     */
    public function enfants(): MorphMany
    {
        return $this->morphMany(ObjetSpatial::class, 'parent');
    }

    /**
     * Vérifie si cet objet est dans une zone spatiale
     */
    public function estDansZone(): bool
    {
        return $this->parent_type === ZoneSpatiale::class;
    }

    /**
     * Récupère la zone parente si elle existe
     */
    public function getZoneParente(): ?ZoneSpatiale
    {
        if ($this->parent_type === ZoneSpatiale::class) {
            return $this->parent;
        }
        return null;
    }

    /**
     * Vérifie si cet objet a un parent
     */
    public function aUnParent(): bool
    {
        return $this->parent_type !== null && $this->parent_id !== null;
    }

    // Méthodes spatiales (selon GDD)
    public function getPosition(): array
    {
        return [
            'secteur' => [
                'x' => $this->secteur_x,
                'y' => $this->secteur_y,
                'z' => $this->secteur_z,
            ],
            'position' => [
                'x' => $this->position_x,
                'y' => $this->position_y,
                'z' => $this->position_z,
            ],
        ];
    }

    /**
     * Obtenir la position absolue en cUA (avec support orbital pour vaisseaux)
     * IMPORTANT: Si le vaisseau est en orbite, retourne la position dynamique
     *
     * @param float|null $timestampJours Timestamp du jeu en jours (null = pas de calcul orbital)
     * @return array ['x' => int, 'y' => int, 'z' => int] Position absolue en cUA
     */
    public function getPositionAbsolueCua(?float $timestampJours = null): array
    {
        // Vérifier si cet objet est un vaisseau en orbite
        if ($this->vaisseau && $timestampJours !== null) {
            $positionOrbitale = $this->vaisseau->getPositionOrbitale($timestampJours);
            if ($positionOrbitale !== null) {
                // Le vaisseau est en orbite, utiliser sa position orbitale dynamique
                return $positionOrbitale;
            }
        }

        // Position statique standard (conversion secteur AL → cUA + position intra)
        $x_cua = CoordinatesHelper::alToCua($this->secteur_x) + $this->position_x;
        $y_cua = CoordinatesHelper::alToCua($this->secteur_y) + $this->position_y;
        $z_cua = CoordinatesHelper::alToCua($this->secteur_z) + $this->position_z;

        return [
            'x' => $x_cua,
            'y' => $y_cua,
            'z' => $z_cua,
        ];
    }

    /**
     * Obtenir la position effective de l'objet spatial
     * Prend en compte les héritages de position (parent, orbite, etc.)
     *
     * PRIORITÉ :
     * 1. Si objet en orbite (vaisseau) → calculer position orbitale
     * 2. Si parent existe → hériter/calculer depuis parent
     * 3. Sinon → position propre stockée
     *
     * @param float|null $timestampJours Timestamp du jeu en jours
     * @return array ['secteur_x' => int, 'secteur_y' => int, 'secteur_z' => int, 'position_x' => int, 'position_y' => int, 'position_z' => int]
     */
    public function getPositionEffective(?float $timestampJours = null): array
    {
        // Cas 1 : Objet en orbite (vaisseau)
        if ($this->vaisseau && $this->vaisseau->orbite_planete_id) {
            return $this->vaisseau->getPositionOrbitale($timestampJours);
        }

        // Cas 2 : Parent existe, vérifier héritage position
        if ($this->parent_type && $this->parent_id) {
            $parent = $this->parent;

            // Parent = Planète → calculer position orbitale
            if ($parent instanceof Planete) {
                $posPlanete = $parent->getPositionOrbitale($timestampJours);

                // Si station en orbite planète, ajouter offset
                if ($this->type === 'station' && $this->station) {
                    $rayonCua = ($this->station->orbite_rayon_ua ?? 0.05) * 100;
                    $angle = $this->station->orbite_angle ?? 0;

                    return [
                        'secteur_x' => $parent->systemeStellaire->secteur_x ?? 0,
                        'secteur_y' => $parent->systemeStellaire->secteur_y ?? 0,
                        'secteur_z' => $parent->systemeStellaire->secteur_z ?? 0,
                        'position_x' => (int)($posPlanete['x'] + ($rayonCua * cos($angle))),
                        'position_y' => (int)($posPlanete['y'] + ($rayonCua * sin($angle))),
                        'position_z' => (int)$posPlanete['z'],
                    ];
                }

                // Sinon, position planète directe (mine sur planète)
                return [
                    'secteur_x' => $parent->systemeStellaire->secteur_x ?? 0,
                    'secteur_y' => $parent->systemeStellaire->secteur_y ?? 0,
                    'secteur_z' => $parent->systemeStellaire->secteur_z ?? 0,
                    'position_x' => (int)$posPlanete['x'],
                    'position_y' => (int)$posPlanete['y'],
                    'position_z' => (int)$posPlanete['z'],
                ];
            }

            // Parent = ZoneSpatiale → position propre (astéroïde dans zone)
            if ($parent instanceof ZoneSpatiale) {
                return [
                    'secteur_x' => $this->secteur_x,
                    'secteur_y' => $this->secteur_y,
                    'secteur_z' => $this->secteur_z,
                    'position_x' => $this->position_x,
                    'position_y' => $this->position_y,
                    'position_z' => $this->position_z,
                ];
            }

            // Parent = ObjetSpatial → hériter position parent (récursif)
            if ($parent instanceof ObjetSpatial) {
                return $parent->getPositionEffective($timestampJours);
            }

            // Parent = Station → via objet_spatial_id
            if ($parent instanceof Station && $parent->objetSpatial) {
                return $parent->objetSpatial->getPositionEffective($timestampJours);
            }

            // Parent = SystemeStellaire → position système
            if ($parent instanceof SystemeStellaire) {
                return [
                    'secteur_x' => $parent->secteur_x ?? 0,
                    'secteur_y' => $parent->secteur_y ?? 0,
                    'secteur_z' => $parent->secteur_z ?? 0,
                    'position_x' => $parent->position_x ?? 0,
                    'position_y' => $parent->position_y ?? 0,
                    'position_z' => $parent->position_z ?? 0,
                ];
            }
        }

        // Cas 3 : Position propre stockée
        return [
            'secteur_x' => $this->secteur_x,
            'secteur_y' => $this->secteur_y,
            'secteur_z' => $this->secteur_z,
            'position_x' => $this->position_x,
            'position_y' => $this->position_y,
            'position_z' => $this->position_z,
        ];
    }

    /**
     * Définir la position de l'objet spatial
     *
     * @param int $secteurX Secteur X en AL
     * @param int $secteurY Secteur Y en AL
     * @param int $secteurZ Secteur Z en AL
     * @param int $posX Position X en cUA
     * @param int $posY Position Y en cUA
     * @param int $posZ Position Z en cUA
     */
    public function setPosition(int $secteurX, int $secteurY, int $secteurZ, int $posX, int $posY, int $posZ): void
    {
        $this->secteur_x = $secteurX;
        $this->secteur_y = $secteurY;
        $this->secteur_z = $secteurZ;
        $this->position_x = $posX;
        $this->position_y = $posY;
        $this->position_z = $posZ;
    }

    /**
     * Calculer la distance avec un autre objet spatial
     *
     * @param ObjetSpatial $autre
     * @return int Distance en cUA
     */
    public function calculerDistance(ObjetSpatial $autre): int
    {
        // Si dans des secteurs différents, distance en AL converti en cUA
        if (!$this->memeSecteur($autre)) {
            // Position absolue = secteur (AL) + position intra (cUA)
            $thisAbsX = CoordinatesHelper::alToCua($this->secteur_x) + $this->position_x;
            $thisAbsY = CoordinatesHelper::alToCua($this->secteur_y) + $this->position_y;
            $thisAbsZ = CoordinatesHelper::alToCua($this->secteur_z) + $this->position_z;

            $autreAbsX = CoordinatesHelper::alToCua($autre->secteur_x) + $autre->position_x;
            $autreAbsY = CoordinatesHelper::alToCua($autre->secteur_y) + $autre->position_y;
            $autreAbsZ = CoordinatesHelper::alToCua($autre->secteur_z) + $autre->position_z;

            return CoordinatesHelper::distance3D($thisAbsX, $thisAbsY, $thisAbsZ, $autreAbsX, $autreAbsY, $autreAbsZ);
        }

        // Même secteur : distance intra-système directe en cUA
        return CoordinatesHelper::distance3D(
            $this->position_x, $this->position_y, $this->position_z,
            $autre->position_x, $autre->position_y, $autre->position_z
        );
    }

    public function memeSecteur(ObjetSpatial $autre): bool
    {
        return $this->secteur_x === $autre->secteur_x
            && $this->secteur_y === $autre->secteur_y
            && $this->secteur_z === $autre->secteur_z;
    }

    public function subirDommages(int $dommages): void
    {
        $this->resistance = max(0, $this->resistance - $dommages);
    }

    public function reparer(int $montant): void
    {
        $this->resistance = min(100, $this->resistance + $montant);
    }

    // === SYSTÈME DE DÉTECTION ===

    /**
     * Calcule le score de détection de l'objet spatial (RÈGLE INTRA-SECTEUR)
     *
     * IMPORTANT:
     * - Les objets spatiaux (vaisseaux, bases) sont des POI locaux
     * - Détectables UNIQUEMENT dans le même secteur
     * - Distance calculée UNIQUEMENT avec positions (cUA), secteurs ignorés
     * - Formule: (distance_cUA / 1000) × detectabilite_base
     *
     * @param int $fromSecteurX Secteur X en AL
     * @param int $fromSecteurY Secteur Y en AL
     * @param int $fromSecteurZ Secteur Z en AL
     * @param int $fromPosX Position X en cUA
     * @param int $fromPosY Position Y en cUA
     * @param int $fromPosZ Position Z en cUA
     * @return float Score de détection
     */
    public function getScoreDetection(int $fromSecteurX, int $fromSecteurY, int $fromSecteurZ, int $fromPosX, int $fromPosY, int $fromPosZ): float
    {
        // Si déjà connu, seuil de détection = 0 (apparaît automatiquement)
        if ($this->poi_connu) {
            return 0;
        }

        // RÈGLE PARENT ZONE: Si objet dans une zone non découverte → invisible
        if ($this->estDansZone()) {
            $zone = $this->getZoneParente();
            if ($zone && !$zone->poi_connu) {
                return 999999; // Zone parent pas découverte = objet invisible
            }
        }

        // RÈGLE INTRA-SECTEUR: Vérifier si même secteur
        if ($this->secteur_x !== $fromSecteurX ||
            $this->secteur_y !== $fromSecteurY ||
            $this->secteur_z !== $fromSecteurZ) {
            // Pas dans le même secteur → non détectable en local
            return 999999;
        }

        // Distance calculée UNIQUEMENT avec positions (cUA) - secteurs ignorés
        $dx = $this->position_x - $fromPosX;
        $dy = $this->position_y - $fromPosY;
        $dz = $this->position_z - $fromPosZ;

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
     * Dépend du type d'objet spatial
     * - vaisseau/base: basé sur la masse (gros objets plus faciles à détecter)
     * - épave: moyenne détectabilité
     * - trace de vol: très difficile à détecter
     * - débris: difficile à détecter
     */
    public function calculerDetectabilite(): float
    {
        // Détectabilité basée sur le type et la masse
        $masse = $this->masse ?? 100;

        $detectabilite = match($this->type) {
            'vaisseau' => max(50, 150 - ($masse / 10)),  // Gros vaisseaux plus faciles
            'base' => max(30, 120 - ($masse / 20)),      // Bases assez détectables
            'epave' => max(60, 100 - ($masse / 15)),     // Épaves moyennement détectables
            'trace_vol' => 150,                          // Traces très difficiles
            'debris' => 120,                             // Débris difficiles
            'asteroide' => max(40, 80 - ($masse / 50)),  // Astéroïdes selon taille
            default => 100,                              // Par défaut
        };

        return max(1, $detectabilite);
    }

    /**
     * Marque l'objet spatial comme découvert (poi_connu = true)
     */
    public function marquerDecouvert(): void
    {
        $this->poi_connu = true;
        $this->save();
    }

    // === SYSTÈME DE NAVIGATION - AZIMUT & ÉLÉVATION ===

    /**
     * Calcule l'azimut (angle horizontal) d'un autre objet par rapport à cet objet
     *
     * @param ObjetSpatial $cible L'objet cible
     * @return float Azimut en degrés (0-360°), où 0° = Nord (Y+), 90° = Est (X+)
     */
    public function calculerAzimutVers(ObjetSpatial $cible): float
    {
        // Positions absolues en cUA
        $myAbsX = CoordinatesHelper::alToCua($this->secteur_x) + $this->position_x;
        $myAbsY = CoordinatesHelper::alToCua($this->secteur_y) + $this->position_y;

        $cibleAbsX = CoordinatesHelper::alToCua($cible->secteur_x) + $cible->position_x;
        $cibleAbsY = CoordinatesHelper::alToCua($cible->secteur_y) + $cible->position_y;

        // Vecteur this → cible
        $dx = $cibleAbsX - $myAbsX;
        $dy = $cibleAbsY - $myAbsY;

        // Azimut absolu (0° = Y+, sens horaire)
        $azimut = rad2deg(atan2($dx, $dy));

        // Normaliser entre 0-360°
        if ($azimut < 0) {
            $azimut += 360;
        }

        return round($azimut, 2);
    }

    /**
     * Calcule l'azimut relatif d'un autre objet par rapport à l'orientation de cet objet
     *
     * @param ObjetSpatial $cible L'objet cible
     * @return float Azimut relatif en degrés (0-360°), où 0° = devant, 180° = derrière
     */
    public function calculerAzimutRelatifVers(ObjetSpatial $cible): float
    {
        $azimutAbsolu = $this->calculerAzimutVers($cible);
        $azimutRelatif = $azimutAbsolu - $this->azimut;

        // Normaliser entre 0-360°
        $azimutRelatif = fmod($azimutRelatif + 360, 360);

        return round($azimutRelatif, 2);
    }

    /**
     * Calcule l'élévation (angle vertical) d'un autre objet par rapport à cet objet
     *
     * @param ObjetSpatial $cible L'objet cible
     * @return float Élévation en degrés (-90° à +90°), où 0° = même plan, +90° = au-dessus, -90° = en-dessous
     */
    public function calculerElevationVers(ObjetSpatial $cible): float
    {
        // Positions absolues en cUA
        $myAbsX = CoordinatesHelper::alToCua($this->secteur_x) + $this->position_x;
        $myAbsY = CoordinatesHelper::alToCua($this->secteur_y) + $this->position_y;
        $myAbsZ = CoordinatesHelper::alToCua($this->secteur_z) + $this->position_z;

        $cibleAbsX = CoordinatesHelper::alToCua($cible->secteur_x) + $cible->position_x;
        $cibleAbsY = CoordinatesHelper::alToCua($cible->secteur_y) + $cible->position_y;
        $cibleAbsZ = CoordinatesHelper::alToCua($cible->secteur_z) + $cible->position_z;

        // Vecteur this → cible
        $dx = $cibleAbsX - $myAbsX;
        $dy = $cibleAbsY - $myAbsY;
        $dz = $cibleAbsZ - $myAbsZ;

        // Distance 3D
        $distance3D = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        if ($distance3D == 0) {
            return 0;
        }

        // Élévation = arcsin(dz / distance)
        $elevation = rad2deg(asin($dz / $distance3D));

        return round($elevation, 2);
    }

    /**
     * Met à jour l'azimut de cet objet en fonction d'un déplacement
     *
     * @param int $nouveauSecteurX Nouveau secteur X
     * @param int $nouveauSecteurY Nouveau secteur Y
     * @param int $nouveauSecteurZ Nouveau secteur Z
     * @param int $nouvellePosX Nouvelle position X (cUA)
     * @param int $nouvellePosY Nouvelle position Y (cUA)
     * @param int $nouvellePosZ Nouvelle position Z (cUA)
     */
    public function updateAzimutFromMovement(int $nouveauSecteurX, int $nouveauSecteurY, int $nouveauSecteurZ,
                                            int $nouvellePosX, int $nouvellePosY, int $nouvellePosZ): void
    {
        // Positions actuelles en cUA
        $oldAbsX = CoordinatesHelper::alToCua($this->secteur_x) + $this->position_x;
        $oldAbsY = CoordinatesHelper::alToCua($this->secteur_y) + $this->position_y;

        // Nouvelles positions en cUA
        $newAbsX = CoordinatesHelper::alToCua($nouveauSecteurX) + $nouvellePosX;
        $newAbsY = CoordinatesHelper::alToCua($nouveauSecteurY) + $nouvellePosY;

        // Vecteur de déplacement
        $dx = $newAbsX - $oldAbsX;
        $dy = $newAbsY - $oldAbsY;

        // Si déplacement significatif (> 1 cUA), calculer le nouvel azimut
        if (sqrt($dx * $dx + $dy * $dy) > 1) {
            $nouvelAzimut = rad2deg(atan2($dx, $dy));

            // Normaliser entre 0-360°
            if ($nouvelAzimut < 0) {
                $nouvelAzimut += 360;
            }

            $this->azimut = round($nouvelAzimut, 2);
        }
    }
}
