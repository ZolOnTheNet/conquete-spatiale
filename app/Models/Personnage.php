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
}
