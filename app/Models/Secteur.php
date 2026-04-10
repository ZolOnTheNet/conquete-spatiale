<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Secteur
 *
 * Représente un cube de 1 AL³ dans l'espace avec métadonnées de scan et exploration.
 * Lazy loading: les secteurs sont créés à la demande lors du premier scan ou génération.
 *
 * DIFFICULTÉ DE SCAN:
 * - Utilisée pour les jets de pilote (scan & repérage, scan & astrogation)
 * - Échelle de 5 (presque vide) à 25 (très perturbé)
 * - Formule: min(25, 5 + (POI_Locaux × 0.2) + (POI_Lointains × 0.5))
 * - Devient fixe après première utilisation, recalculée si nouveaux POI ajoutés
 *
 * PUISSANCE DE LIEU:
 * - Propagation: 30% de la puissance des secteurs adjacents (26 secteurs autour)
 * - Calculée depuis les systèmes stellaires présents dans le secteur
 */
class Secteur extends Model
{
    protected $table = 'secteurs';

    protected $fillable = [
        'secteur_x',
        'secteur_y',
        'secteur_z',
        'difficulte_scan',
        'nb_poi_locaux',
        'nb_poi_lointains',
        'derniere_maj_difficulte',
        'puissance_lieu',
        'densite_matiere',
        'niveau_danger',
        'radiation_ambiante',
        'explore',
        'nb_visites',
        'premiere_visite',
        'derniere_visite',
        'nom_region',
        'description',
        'genere_proceduralement',
        'seed_generation',
    ];

    protected $casts = [
        'difficulte_scan' => 'integer',
        'nb_poi_locaux' => 'integer',
        'nb_poi_lointains' => 'integer',
        'derniere_maj_difficulte' => 'datetime',
        'puissance_lieu' => 'decimal:2',
        'densite_matiere' => 'decimal:4',
        'niveau_danger' => 'integer',
        'radiation_ambiante' => 'decimal:2',
        'explore' => 'boolean',
        'nb_visites' => 'integer',
        'premiere_visite' => 'datetime',
        'derniere_visite' => 'datetime',
        'genere_proceduralement' => 'boolean',
        'seed_generation' => 'integer',
    ];

    // ========================================================================
    // RELATIONS
    // ========================================================================

    /**
     * Systèmes stellaires dans ce secteur
     */
    public function systemes()
    {
        return $this->hasMany(SystemeStellaire::class, 'secteur_x', 'secteur_x')
            ->where('secteur_y', $this->secteur_y)
            ->where('secteur_z', $this->secteur_z);
    }

    // ========================================================================
    // MÉTHODES STATIQUES - OBTENTION/CRÉATION
    // ========================================================================

    /**
     * Obtient un secteur existant ou en crée un nouveau (lazy loading)
     *
     * @param int $x Coordonnée X en AL
     * @param int $y Coordonnée Y en AL
     * @param int $z Coordonnée Z en AL
     * @return Secteur
     */
    public static function obtenirOuCreer(int $x, int $y, int $z): Secteur
    {
        $secteur = static::where('secteur_x', $x)
            ->where('secteur_y', $y)
            ->where('secteur_z', $z)
            ->first();

        if (!$secteur) {
            // Créer un nouveau secteur avec valeurs par défaut
            $secteur = static::create([
                'secteur_x' => $x,
                'secteur_y' => $y,
                'secteur_z' => $z,
                'difficulte_scan' => 5, // Minimum par défaut
                'nb_poi_locaux' => 0,
                'nb_poi_lointains' => 0,
                'puissance_lieu' => 0,
                'densite_matiere' => 0,
                'niveau_danger' => 0,
                'radiation_ambiante' => 1, // Fond cosmique
                'explore' => false,
                'nb_visites' => 0,
                'genere_proceduralement' => false,
            ]);

            // Calculer la difficulté et la puissance initiales
            $secteur->recalculerDifficulte();
            $secteur->calculerPuissanceLieu();
            $secteur->save();
        }

        return $secteur;
    }

    // ========================================================================
    // CALCUL DE DIFFICULTÉ DE SCAN
    // ========================================================================

    /**
     * Recalcule la difficulté de scan basée sur le nombre de POI
     *
     * FORMULE:
     * - Base: 5 (secteur presque vide)
     * - POI Locaux (même secteur): +0.2 par objet (planètes, stations, mines)
     * - POI Lointains (≤10 AL): +0.5 par système stellaire
     * - Maximum: 25 (très perturbé)
     *
     * USAGE: Pour jets de pilote (scan & repérage, scan & astrogation)
     *
     * @return int Difficulté calculée (5-25)
     */
    public function recalculerDifficulte(): int
    {
        // Compter les POI locaux dans ce secteur exact
        $nbPoiLocaux = $this->compterPoiLocaux();

        // Compter les systèmes stellaires dans un rayon de 10 AL
        $nbPoiLointains = $this->compterPoiLointains();

        // Appliquer la formule
        $difficulte = 5 + ($nbPoiLocaux * 0.2) + ($nbPoiLointains * 0.5);

        // Plafonner à 25
        $difficulte = min(25, $difficulte);

        // Arrondir à l'entier
        $difficulte = (int) round($difficulte);

        // Mettre à jour le modèle
        $this->difficulte_scan = $difficulte;
        $this->nb_poi_locaux = $nbPoiLocaux;
        $this->nb_poi_lointains = $nbPoiLointains;
        $this->derniere_maj_difficulte = now();

        return $difficulte;
    }

    /**
     * Compte les POI locaux dans ce secteur exact (même secteur)
     *
     * POI Locaux:
     * - Planètes (via systèmes stellaires du secteur)
     * - Stations dans le secteur
     * - Mines dans le secteur
     *
     * @return int Nombre de POI locaux
     */
    private function compterPoiLocaux(): int
    {
        $count = 0;

        // 1. Planètes (via systèmes stellaires du secteur)
        $systemes = SystemeStellaire::where('secteur_x', $this->secteur_x)
            ->where('secteur_y', $this->secteur_y)
            ->where('secteur_z', $this->secteur_z)
            ->get();

        foreach ($systemes as $systeme) {
            $count += $systeme->planetes()->count();
        }

        // 2. Stations dans le secteur
        $stations = Station::whereHas('systemeStellaire', function ($query) {
            $query->where('secteur_x', $this->secteur_x)
                ->where('secteur_y', $this->secteur_y)
                ->where('secteur_z', $this->secteur_z);
        })->count();

        $count += $stations;

        // 3. Mines dans le secteur (via planètes)
        $mines = Mine::whereHas('planete.systemeStellaire', function ($query) {
            $query->where('secteur_x', $this->secteur_x)
                ->where('secteur_y', $this->secteur_y)
                ->where('secteur_z', $this->secteur_z);
        })->count();

        $count += $mines;

        return $count;
    }

    /**
     * Compte les systèmes stellaires dans un rayon de 10 AL (POI lointains)
     *
     * @return int Nombre de systèmes à portée
     */
    private function compterPoiLointains(): int
    {
        $portee = 10; // AL

        $systemes = SystemeStellaire::all();
        $count = 0;

        foreach ($systemes as $systeme) {
            $dx = $systeme->secteur_x - $this->secteur_x;
            $dy = $systeme->secteur_y - $this->secteur_y;
            $dz = $systeme->secteur_z - $this->secteur_z;

            $distance = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

            if ($distance <= $portee) {
                $count++;
            }
        }

        return $count;
    }

    // ========================================================================
    // CALCUL DE PUISSANCE DE LIEU
    // ========================================================================

    /**
     * Calcule la puissance énergétique du secteur avec propagation
     *
     * PRINCIPE:
     * 1. Puissance locale: somme des systèmes stellaires dans ce secteur
     * 2. Propagation: 30% de la puissance des 26 secteurs adjacents
     *
     * @return float Puissance calculée
     */
    public function calculerPuissanceLieu(): float
    {
        // 1. Puissance locale (systèmes stellaires dans ce secteur)
        $puissanceLocale = $this->calculerPuissanceLocale();

        // 2. Propagation depuis les secteurs adjacents (30%)
        $puissancePropagee = $this->calculerPuissancePropagee();

        // Total
        $puissanceTotal = $puissanceLocale + $puissancePropagee;

        // Mettre à jour le modèle
        $this->puissance_lieu = $puissanceTotal;

        return $puissanceTotal;
    }

    /**
     * Calcule la puissance locale (systèmes stellaires dans ce secteur)
     *
     * @return float Puissance locale
     */
    private function calculerPuissanceLocale(): float
    {
        $systemes = SystemeStellaire::where('secteur_x', $this->secteur_x)
            ->where('secteur_y', $this->secteur_y)
            ->where('secteur_z', $this->secteur_z)
            ->get();

        $puissance = 0;

        foreach ($systemes as $systeme) {
            // Utiliser puissance ou puissance_solaire selon ce qui est disponible
            $puissance += $systeme->puissance ?? $systeme->puissance_solaire ?? 0;
        }

        return $puissance;
    }

    /**
     * Calcule la puissance propagée depuis les 26 secteurs adjacents (30% chacun)
     *
     * IMPORTANT: Pour éviter une récursion infinie, on calcule UNIQUEMENT
     * la puissance locale des secteurs adjacents, pas leur puissance propagée.
     *
     * @return float Puissance propagée
     */
    private function calculerPuissancePropagee(): float
    {
        $puissanceTotale = 0;
        $coefficientPropagation = 0.30; // 30%

        // Les 26 secteurs adjacents (3×3×3 - 1)
        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                for ($dz = -1; $dz <= 1; $dz++) {
                    // Ignorer le secteur central (nous-mêmes)
                    if ($dx === 0 && $dy === 0 && $dz === 0) {
                        continue;
                    }

                    $adjX = $this->secteur_x + $dx;
                    $adjY = $this->secteur_y + $dy;
                    $adjZ = $this->secteur_z + $dz;

                    // Calculer la puissance locale du secteur adjacent
                    // (sans récursion - on ne récupère que les systèmes, pas la propagation)
                    $systemes = SystemeStellaire::where('secteur_x', $adjX)
                        ->where('secteur_y', $adjY)
                        ->where('secteur_z', $adjZ)
                        ->get();

                    $puissanceAdj = 0;
                    foreach ($systemes as $systeme) {
                        $puissanceAdj += $systeme->puissance ?? $systeme->puissance_solaire ?? 0;
                    }

                    // Ajouter 30% de cette puissance
                    $puissanceTotale += $puissanceAdj * $coefficientPropagation;
                }
            }
        }

        return $puissanceTotale;
    }

    // ========================================================================
    // EXPLORATION
    // ========================================================================

    /**
     * Marque le secteur comme exploré
     *
     * @param bool $increment Incrémenter le nombre de visites
     */
    public function marquerExplore(bool $increment = true): void
    {
        if (!$this->explore) {
            $this->explore = true;
            $this->premiere_visite = now();
        }

        if ($increment) {
            $this->nb_visites++;
        }

        $this->derniere_visite = now();
        $this->save();
    }

    // ========================================================================
    // UTILITAIRES
    // ========================================================================

    /**
     * Retourne les coordonnées du secteur sous forme de string
     *
     * @return string Format: "(X, Y, Z) AL"
     */
    public function getCoordonneesAttribute(): string
    {
        return "({$this->secteur_x}, {$this->secteur_y}, {$this->secteur_z}) AL";
    }

    /**
     * Retourne une description du niveau de difficulté
     *
     * @return string Description lisible
     */
    public function getDescriptionDifficulteAttribute(): string
    {
        $diff = $this->difficulte_scan;

        if ($diff <= 7) {
            return 'Très facile';
        } elseif ($diff <= 12) {
            return 'Facile';
        } elseif ($diff <= 17) {
            return 'Moyen';
        } elseif ($diff <= 22) {
            return 'Difficile';
        } else {
            return 'Très difficile';
        }
    }

    /**
     * Retourne une description du niveau de puissance
     *
     * @return string Description lisible
     */
    public function getDescriptionPuissanceAttribute(): string
    {
        $puis = $this->puissance_lieu;

        if ($puis === 0) {
            return 'Espace vide';
        } elseif ($puis < 10) {
            return 'Très faible';
        } elseif ($puis < 30) {
            return 'Faible';
        } elseif ($puis < 60) {
            return 'Moyenne';
        } elseif ($puis < 100) {
            return 'Forte';
        } else {
            return 'Très forte';
        }
    }
}
