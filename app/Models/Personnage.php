<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personnage extends Model
{
    protected $table = 'personnages';

    protected $fillable = [
        'compte_id',
        'nom',
        'prenom',
        'agilite',
        'force',
        'finesse',
        'instinct',
        'presence',
        'savoir',
        'competences',
        'experience',
        'niveau',
        'jetons_hope',
        'jetons_fear',
        'points_action',
        'max_points_action',
        'derniere_recuperation_pa',
        'vaisseau_actif_id',
        'date_logs',
    ];

    protected $casts = [
        'competences' => 'array',
        'date_logs' => 'array',
        'derniere_recuperation_pa' => 'datetime',
    ];

    // Relations
    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }

    public function vaisseauActif(): BelongsTo
    {
        return $this->belongsTo(Vaisseau::class, 'vaisseau_actif_id');
    }

    public function objetsSpatiauxPossedes(): HasMany
    {
        return $this->hasMany(ObjetSpatial::class, 'proprietaire_id');
    }

    public function decouvertes(): HasMany
    {
        return $this->hasMany(Decouverte::class);
    }

    // Méthodes Daggerheart
    public function lancerDes(int $competenceNiveau = 0): array
    {
        $hope = rand(1, config('game.daggerheart.des_hope', 12));
        $fear = rand(1, config('game.daggerheart.des_fear', 12));
        $total = $hope + $fear + $competenceNiveau;

        $resultat = [
            'hope' => $hope,
            'fear' => $fear,
            'total' => $total,
            'critique' => $hope === $fear,
        ];

        // Gestion jetons
        if ($hope > $fear) {
            $this->jetons_hope++;
        } elseif ($fear > $hope) {
            $this->jetons_fear++;
        }

        return $resultat;
    }

    public function gagnerExperience(int $xp): void
    {
        $this->experience += $xp;
        // Logique de niveau à implémenter selon GDD
    }

    // Gestion des Points d'Action (PA)
    public function consommerPA(int $pa): bool
    {
        if ($this->points_action >= $pa) {
            // Si on était au max et qu'on dépense pour la première fois, démarrer le timestamp
            $etait_au_max = ($this->points_action >= $this->max_points_action);

            $this->points_action -= $pa;

            // Démarrer le chrono de récupération si on passe du max à moins que max
            if ($etait_au_max && !$this->derniere_recuperation_pa) {
                $this->derniere_recuperation_pa = now();
            }

            return true;
        }
        return false;
    }

    public function restaurerPA(int $pa = null): void
    {
        if ($pa === null) {
            // Restauration complète (nouveau tour)
            $this->points_action = $this->max_points_action;
        } else {
            $this->points_action = min(
                $this->max_points_action,
                $this->points_action + $pa
            );
        }
    }

    public function ajusterMaxPA(int $nouveau_max): void
    {
        $this->max_points_action = $nouveau_max;
        // Ajuster les PA actuels si nécessaire
        if ($this->points_action > $nouveau_max) {
            $this->points_action = $nouveau_max;
        }
    }

    /**
     * Récupération automatique de PA: 1 PA par heure écoulée
     * Appelé au début de chaque action du joueur
     * Timestamp démarre uniquement à la première dépense (max → max-1)
     */
    public function recupererPAAutomatique(): array
    {
        // Si pas de timestamp = jamais dépensé de PA, aucune récupération
        if (!$this->derniere_recuperation_pa) {
            return [
                'pa_recuperes' => 0,
                'heures_ecoulees' => 0,
            ];
        }

        // Déjà au maximum, arrêter le chrono et réinitialiser timestamp
        if ($this->points_action >= $this->max_points_action) {
            $this->derniere_recuperation_pa = null;
            $this->save();
            return [
                'pa_recuperes' => 0,
                'heures_ecoulees' => 0,
            ];
        }

        // Calculer périodes écoulées depuis dernière récupération
        $maintenant = now();
        $derniere_recup = $this->derniere_recuperation_pa;
        $delai_minutes = config('game.pa.recuperation_delai', 60);
        $pa_par_periode = config('game.pa.recuperation_montant', 1);

        $minutes_ecoulees = $maintenant->diffInMinutes($derniere_recup);
        $periodes_ecoulees = (int)floor($minutes_ecoulees / $delai_minutes);

        // Aucune période complète écoulée
        if ($periodes_ecoulees < 1) {
            return [
                'pa_recuperes' => 0,
                'heures_ecoulees' => 0,
                'prochaine_recuperation_dans' => $delai_minutes - ($minutes_ecoulees % $delai_minutes),
            ];
        }

        // Calculer PA à récupérer (pa_par_periode × périodes, sans dépasser max)
        $pa_manquants = $this->max_points_action - $this->points_action;
        $pa_a_recuperer = min($periodes_ecoulees * $pa_par_periode, $pa_manquants);

        // Appliquer récupération
        $this->points_action += $pa_a_recuperer;

        // Si on atteint le max, arrêter le chrono
        if ($this->points_action >= $this->max_points_action) {
            $this->derniere_recuperation_pa = null;
        } else {
            // Mettre à jour timestamp (ajouter les périodes récupérées pour ne pas perdre de fraction)
            $periodes_utilisees = (int)ceil($pa_a_recuperer / $pa_par_periode);
            $this->derniere_recuperation_pa = $derniere_recup->addMinutes($periodes_utilisees * $delai_minutes);
        }

        $this->save();

        return [
            'pa_recuperes' => $pa_a_recuperer,
            'heures_ecoulees' => round($periodes_ecoulees * $delai_minutes / 60, 1),
            'pa_actuels' => $this->points_action,
            'prochaine_recuperation_dans' => $this->points_action >= $this->max_points_action
                ? null
                : $delai_minutes - ($maintenant->diffInMinutes($this->derniere_recuperation_pa) % $delai_minutes),
        ];
    }

    // Système de découverte
    /**
     * Scanne les systèmes stellaires dans un rayon donné
     * Utilise la formule: 2d12 vs Seuil (500 + Distance × 100)
     */
    public function scannerSystemes(float $rayon = 5.0): array
    {
        // Obtenir position actuelle (via vaisseau ou objet spatial)
        $positionActuelle = $this->getPositionActuelle();

        if (!$positionActuelle) {
            return [
                'succes' => false,
                'message' => 'Position actuelle introuvable. Vous devez être à bord d\'un vaisseau.',
            ];
        }

        // Trouver systèmes dans le rayon
        $systemes = SystemeStellaire::all()->filter(function ($systeme) use ($positionActuelle, $rayon) {
            $distance = $this->calculerDistance($positionActuelle, [
                'secteur_x' => $systeme->secteur_x,
                'secteur_y' => $systeme->secteur_y,
                'secteur_z' => $systeme->secteur_z,
                'position_x' => $systeme->position_x,
                'position_y' => $systeme->position_y,
                'position_z' => $systeme->position_z,
            ]);

            return $distance <= $rayon;
        });

        $decouvertes = [];
        $deja_connus = 0;

        foreach ($systemes as $systeme) {
            // Vérifier si déjà découvert
            $decouverte_existante = $this->decouvertes()
                ->where('systeme_stellaire_id', $systeme->id)
                ->first();

            if ($decouverte_existante) {
                $deja_connus++;
                continue;
            }

            // Calculer distance
            $distance = $this->calculerDistance($positionActuelle, [
                'secteur_x' => $systeme->secteur_x,
                'secteur_y' => $systeme->secteur_y,
                'secteur_z' => $systeme->secteur_z,
                'position_x' => $systeme->position_x,
                'position_y' => $systeme->position_y,
                'position_z' => $systeme->position_z,
            ]);

            // Formule de détection: 2d12 vs (500 + Distance × 100)
            $seuil_base = config('game.decouverte.seuil_base', 500);
            $mult_distance = config('game.decouverte.seuil_par_distance', 100);
            $seuil = $seuil_base + ($distance * $mult_distance);

            // Lancer 2d12
            $jet = $this->lancerDes(0);
            $resultat = $jet['total'];

            // Ajuster seuil selon puissance solaire (étoiles brillantes plus faciles à détecter)
            $ajustement_puissance = ($systeme->puissance_solaire - 50) * 2; // ±2 par tranche de 1
            $seuil_final = max(1, $seuil - $ajustement_puissance);

            $detecte = $resultat >= $seuil_final;

            // Créer découverte
            $decouverte = Decouverte::create([
                'personnage_id' => $this->id,
                'systeme_stellaire_id' => $systeme->id,
                'resultat_scan' => $resultat,
                'seuil_detection' => $seuil_final,
                'distance_decouverte' => $distance,
                'decouvert_a' => now(),
                'coordonnees_connues' => true,
                'type_etoile_connu' => $detecte,
                'nb_planetes_connu' => $detecte,
                'visite' => false,
            ]);

            $decouvertes[] = [
                'systeme' => $systeme->nom,
                'distance' => round($distance, 2),
                'resultat_jet' => $resultat,
                'seuil' => $seuil_final,
                'detecte' => $detecte,
                'details' => $detecte ? [
                    'type_etoile' => $systeme->type_etoile,
                    'couleur' => $systeme->couleur,
                    'nb_planetes' => $systeme->nb_planetes,
                ] : null,
            ];
        }

        return [
            'succes' => true,
            'rayon' => $rayon,
            'systemes_trouves' => count($decouvertes),
            'deja_connus' => $deja_connus,
            'decouvertes' => $decouvertes,
        ];
    }

    /**
     * Récupère la position actuelle du personnage
     */
    public function getPositionActuelle(): ?array
    {
        // Si dans un vaisseau, utiliser position du vaisseau
        if ($this->vaisseauActif) {
            $objet = $this->vaisseauActif->objetSpatial;
            if ($objet) {
                return [
                    'secteur_x' => $objet->secteur_x,
                    'secteur_y' => $objet->secteur_y,
                    'secteur_z' => $objet->secteur_z,
                    'position_x' => $objet->position_x,
                    'position_y' => $objet->position_y,
                    'position_z' => $objet->position_z,
                ];
            }
        }

        // Sinon chercher dans objets possédés
        $objet = $this->objetsSpatiauxPossedes()->first();
        if ($objet) {
            return [
                'secteur_x' => $objet->secteur_x,
                'secteur_y' => $objet->secteur_y,
                'secteur_z' => $objet->secteur_z,
                'position_x' => $objet->position_x,
                'position_y' => $objet->position_y,
                'position_z' => $objet->position_z,
            ];
        }

        return null;
    }

    /**
     * Calcule la distance 3D entre deux positions
     */
    public function calculerDistance(array $pos1, array $pos2): float
    {
        $dx = ($pos1['secteur_x'] + $pos1['position_x']) - ($pos2['secteur_x'] + $pos2['position_x']);
        $dy = ($pos1['secteur_y'] + $pos1['position_y']) - ($pos2['secteur_y'] + $pos2['position_y']);
        $dz = ($pos1['secteur_z'] + $pos1['position_z']) - ($pos2['secteur_z'] + $pos2['position_z']);

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Obtient tous les systèmes découverts
     */
    public function getSystemesDecouverts(): array
    {
        return $this->decouvertes()
            ->with('systemeStellaire')
            ->get()
            ->map(function ($decouverte) {
                return $decouverte->getInformationsRevelees();
            })
            ->toArray();
    }
}
