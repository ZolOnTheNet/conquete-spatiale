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
        $hope = rand(1, 12);
        $fear = rand(1, 12);
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

        // Calculer heures écoulées depuis dernière récupération
        $maintenant = now();
        $derniere_recup = $this->derniere_recuperation_pa;
        $heures_ecoulees = (int)floor($maintenant->diffInHours($derniere_recup));

        // Aucune heure complète écoulée
        if ($heures_ecoulees < 1) {
            return [
                'pa_recuperes' => 0,
                'heures_ecoulees' => 0,
                'prochaine_recuperation_dans' => 60 - $maintenant->diffInMinutes($derniere_recup) % 60,
            ];
        }

        // Calculer PA à récupérer (1 PA par heure, sans dépasser max)
        $pa_manquants = $this->max_points_action - $this->points_action;
        $pa_a_recuperer = min($heures_ecoulees, $pa_manquants);

        // Appliquer récupération
        $this->points_action += $pa_a_recuperer;

        // Si on atteint le max, arrêter le chrono
        if ($this->points_action >= $this->max_points_action) {
            $this->derniere_recuperation_pa = null;
        } else {
            // Mettre à jour timestamp (ajouter les heures récupérées pour ne pas perdre de fraction)
            $this->derniere_recuperation_pa = $derniere_recup->addHours($pa_a_recuperer);
        }

        $this->save();

        return [
            'pa_recuperes' => $pa_a_recuperer,
            'heures_ecoulees' => $heures_ecoulees,
            'pa_actuels' => $this->points_action,
            'prochaine_recuperation_dans' => $this->points_action >= $this->max_points_action
                ? null
                : 60 - $maintenant->diffInMinutes($this->derniere_recuperation_pa) % 60,
        ];
    }
}
