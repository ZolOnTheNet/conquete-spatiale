<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'derniere_connexion',
        'vaisseau_actif_id',
        'dans_station_id',
        'date_logs',
    ];

    protected $casts = [
        'competences' => 'array',
        'date_logs' => 'array',
        'derniere_recuperation_pa' => 'datetime',
        'derniere_connexion' => 'datetime',
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

    public function dansStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'dans_station_id');
    }

    public function objetsSpatiauxPossedes(): HasMany
    {
        return $this->hasMany(ObjetSpatial::class, 'proprietaire_id');
    }

    public function decouvertes(): HasMany
    {
        return $this->hasMany(Decouverte::class);
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class, 'mission_personnage')
            ->withPivot([
                'statut',
                'progression',
                'acceptee_le',
                'completee_le',
                'expire_le',
                'fois_completee',
                'dernier_cooldown',
            ])
            ->withTimestamps();
    }

    public function reputations(): HasMany
    {
        return $this->hasMany(Reputation::class);
    }

    /**
     * Mines possédées (propriétaire actuel)
     */
    public function mines(): HasMany
    {
        return $this->hasMany(Mine::class, 'proprietaire_id');
    }

    /**
     * Mines installées (installateur initial)
     */
    public function minesInstalles(): HasMany
    {
        return $this->hasMany(Mine::class, 'installateur_id');
    }

    /**
     * Obtient la réputation avec une faction spécifique
     */
    public function getReputation(Faction $faction): Reputation
    {
        return Reputation::getOuCreer($this->id, $faction->id);
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
            $this->points_action -= $pa;

            // Démarrer le chrono de récupération dès la première dépense de PA
            if (!$this->derniere_recuperation_pa) {
                $this->derniere_recuperation_pa = now();
            }

            // Faire avancer le temps du jeu : 1 PA = 0.5 jour in-game
            $this->avancerTempsJeu($pa);

            // Régénération passive du bouclier : 1 tick par PA dépensé
            if ($this->vaisseau_actif_id) {
                $vaisseau = $this->relationLoaded('vaisseauActif')
                    ? $this->vaisseauActif
                    : $this->vaisseauActif()->first();
                if ($vaisseau) $vaisseau->regenererBouclier($pa);
            }

            return true;
        }
        return false;
    }

    /**
     * Faire avancer le temps in-game selon les PA dépensés
     * Règle : 1 PA = 0.5 jour in-game
     *
     * @param int $pa Nombre de PA dépensés
     * @return void
     */
    public function avancerTempsJeu(int $pa): void
    {
        $joursAvances = $pa * 0.5; // 1 PA = 0.5 jour

        // Initialiser derniere_connexion si null (premier lancement)
        if (!$this->derniere_connexion) {
            $this->derniere_connexion = \App\Helpers\GameTimeHelper::DATE_REFERENCE;
        }

        // Avancer la date in-game
        $dateActuelle = \Carbon\Carbon::parse($this->derniere_connexion);
        $nouvelleDateJeu = $dateActuelle->copy()->addDays($joursAvances);
        $this->derniere_connexion = $nouvelleDateJeu;

        // Sauvegarder immédiatement (important pour les calculs orbitaux suivants)
        $this->save();
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
        // Si pas de timestamp et pas au max, initialiser maintenant pour les personnages existants
        if (!$this->derniere_recuperation_pa && $this->points_action < $this->max_points_action) {
            $this->derniere_recuperation_pa = now();
            $this->save();
            return [
                'pa_recuperes' => 0,
                'heures_ecoulees' => 0,
            ];
        }

        // Si pas de timestamp et au max, rien à faire
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

        $minutes_ecoulees = $derniere_recup->diffInMinutes($maintenant);
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
     * Scanne les systèmes stellaires (scan progressif lié au vaisseau)
     * Utilise la formule: 2d12 + Puissance_Scan_Effective vs Seuil (500 + Distance × 100)
     * Chaque scan coûte 1 PA et améliore la détection
     */
    public function scannerSystemes(): array
    {
        // Vérifier présence d'un vaisseau
        if (!$this->vaisseauActif) {
            return [
                'succes' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau pour scanner.',
            ];
        }

        $vaisseau = $this->vaisseauActif;
        $positionActuelle = $this->getPositionActuelle();

        if (!$positionActuelle) {
            return [
                'succes' => false,
                'message' => 'Position du vaisseau introuvable.',
            ];
        }

        // Lancer scan progressif du vaisseau
        $scan_info = $vaisseau->scannerZone();
        $rayon = $vaisseau->portee_scan;

        // Trouver systèmes dans le rayon (non encore découverts)
        $systemes = SystemeStellaire::all()->filter(function ($systeme) use ($positionActuelle, $rayon) {
            // Vérifier si déjà découvert
            $deja_decouvert = $this->decouvertes()
                ->where('systeme_stellaire_id', $systeme->id)
                ->exists();

            if ($deja_decouvert) {
                return false;
            }

            // Vérifier distance
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
        $puissance_scan = $vaisseau->getPuissanceScanEffective();

        foreach ($systemes as $systeme) {
            // Calculer distance
            $distance = $this->calculerDistance($positionActuelle, [
                'secteur_x' => $systeme->secteur_x,
                'secteur_y' => $systeme->secteur_y,
                'secteur_z' => $systeme->secteur_z,
                'position_x' => $systeme->position_x,
                'position_y' => $systeme->position_y,
                'position_z' => $systeme->position_z,
            ]);

            // Formule de détection: 2d12 + Puissance_Scan vs (500 + Distance × 100)
            $seuil_base = config('game.decouverte.seuil_base', 500);
            $mult_distance = config('game.decouverte.seuil_par_distance', 100);
            $seuil = $seuil_base + ($distance * $mult_distance);

            // Lancer 2d12
            $jet = $this->lancerDes(0);
            $resultat_des = $jet['total'];

            // Ajouter puissance du scan
            $resultat_total = $resultat_des + $puissance_scan;

            // Ajuster seuil selon puissance (étoiles brillantes plus faciles)
            // Utiliser le champ 'puissance' s'il est disponible, sinon fallback sur 'puissance_solaire'
            $puissance = $systeme->puissance ?? $systeme->puissance_solaire;
            $ajustement_puissance = ($puissance - 50) * 2; // ±2 par tranche de 1
            $seuil_final = max(1, $seuil - $ajustement_puissance);

            $detecte = $resultat_total >= $seuil_final;

            // Ne créer découverte QUE si détecté
            if ($detecte) {
                $decouverte = Decouverte::create([
                    'personnage_id' => $this->id,
                    'systeme_stellaire_id' => $systeme->id,
                    'resultat_scan' => $resultat_total,
                    'seuil_detection' => $seuil_final,
                    'distance_decouverte' => $distance,
                    'decouvert_a' => now(),
                    'coordonnees_connues' => true,
                    'type_etoile_connu' => true,
                    'nb_planetes_connu' => true,
                    'visite' => false,
                ]);

                $decouvertes[] = [
                    'systeme' => $systeme->nom,
                    'distance' => round($distance, 2),
                    'resultat_des' => $resultat_des,
                    'puissance_scan' => $puissance_scan,
                    'resultat_total' => $resultat_total,
                    'seuil' => $seuil_final,
                    'details' => [
                        'type_etoile' => $systeme->type_etoile,
                        'couleur' => $systeme->couleur,
                        'nb_planetes' => $systeme->nb_planetes,
                        'puissance' => $systeme->puissance,
                        'detectabilite_base' => $systeme->detectabilite_base,
                    ],
                ];
            }
        }

        return [
            'succes' => true,
            'scan_info' => $scan_info,
            'rayon' => $rayon,
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

    // === SYSTÈME DE SCAN V2.0 (GDD) ===

    /**
     * Lance un jet de compétence pour le scan (Daggerheart)
     *
     * @param string $competence 'finesse' ou 'savoir'
     * @param int $difficulte Difficulté du jet (12-25)
     * @return array ['succes' => bool, 'espoir' => int, 'peur' => int, 'total' => int, 'marge' => int, 'dice_type' => int, 'complication' => bool]
     */
    public function lancerJetScan(string $competence, int $difficulte): array
    {
        $modificateur = $this->{$competence} ?? 0;

        // Lancer 2d12 (Espoir et Peur)
        $deEspoir = rand(1, 12);
        $dePeur = rand(1, 12);
        $total = $deEspoir + $dePeur + $modificateur;

        $succes = $total >= $difficulte;
        $marge = $total - $difficulte;
        $complication = false;

        // Déterminer le TYPE de dé de bonus/malus selon la marge
        if ($succes) {
            // Réussite - déterminer le dé selon la marge
            if ($marge <= 2) {
                $this->scan_bonus_dice_type = 4;  // 1d4
            } elseif ($marge <= 5) {
                $this->scan_bonus_dice_type = 6;  // 1d6
            } elseif ($marge <= 8) {
                $this->scan_bonus_dice_type = 8;  // 1d8
            } elseif ($marge <= 11) {
                $this->scan_bonus_dice_type = 10; // 1d10
            } else {
                $this->scan_bonus_dice_type = 12; // 1d12
            }

            // Complication mineure si Peur > Espoir
            if ($dePeur > $deEspoir) {
                $complication = true;
            }
        } else {
            // Échec - malus de 1d6
            $this->scan_bonus_dice_type = -6;
        }

        $this->save();

        return [
            'succes' => $succes,
            'espoir' => $deEspoir,
            'peur' => $dePeur,
            'total' => $total,
            'modificateur' => $modificateur,
            'difficulte' => $difficulte,
            'marge' => $marge,
            'dice_type' => $this->scan_bonus_dice_type,
            'dice_label' => $this->getScanBonusDiceLabel(),
            'complication' => $complication,
        ];
    }

    /**
     * Retourne le label du dé de bonus/malus
     */
    public function getScanBonusDiceLabel(): string
    {
        if ($this->scan_bonus_dice_type == 0) {
            return 'Aucun';
        } elseif ($this->scan_bonus_dice_type < 0) {
            return '-1d' . abs($this->scan_bonus_dice_type);
        } else {
            return '+1d' . $this->scan_bonus_dice_type;
        }
    }

    /**
     * Lance le dé de bonus/malus (appelé à chaque scan)
     */
    public function lancerBonusScan(): int
    {
        if ($this->scan_bonus_dice_type == 0) {
            return 0;
        } elseif ($this->scan_bonus_dice_type < 0) {
            // Malus
            return -rand(1, abs($this->scan_bonus_dice_type));
        } else {
            // Bonus
            return rand(1, $this->scan_bonus_dice_type);
        }
    }

    /**
     * Réinitialise le dé de bonus de scan (quand le vaisseau change de position ou refait un jet)
     */
    public function reinitialiserBonusScan(): void
    {
        $this->scan_bonus_dice_type = 0;
        $this->save();
    }
}
