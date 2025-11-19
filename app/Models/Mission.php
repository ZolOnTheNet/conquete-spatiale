<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'titre',
        'description',
        'faction_id',
        'type',
        'difficulte',
        'niveau_requis',
        'reputation_requise',
        'objectifs',
        'recompense_credits',
        'recompense_xp',
        'recompense_reputation',
        'recompense_objets',
        'penalite_reputation',
        'duree_limite',
        'repetable',
        'cooldown',
        'systeme_depart_id',
        'systeme_arrivee_id',
        'actif',
    ];

    protected $casts = [
        'objectifs' => 'array',
        'recompense_objets' => 'array',
        'recompense_credits' => 'integer',
        'recompense_xp' => 'integer',
        'recompense_reputation' => 'integer',
        'penalite_reputation' => 'integer',
        'niveau_requis' => 'integer',
        'reputation_requise' => 'integer',
        'duree_limite' => 'integer',
        'cooldown' => 'integer',
        'repetable' => 'boolean',
        'actif' => 'boolean',
    ];

    /**
     * Faction qui donne la mission
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * Systeme de depart
     */
    public function systemeDepart(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_depart_id');
    }

    /**
     * Systeme d'arrivee
     */
    public function systemeArrivee(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_arrivee_id');
    }

    /**
     * Personnages ayant cette mission
     */
    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'mission_personnage')
            ->withPivot(['statut', 'progression', 'acceptee_le', 'completee_le', 'expire_le', 'fois_completee'])
            ->withTimestamps();
    }

    /**
     * Verifier si un personnage peut accepter cette mission
     */
    public function peutEtreAcceptee(Personnage $personnage): array
    {
        $raisons = [];

        // Niveau requis
        if ($personnage->niveau < $this->niveau_requis) {
            $raisons[] = "Niveau {$this->niveau_requis} requis (vous etes niveau {$personnage->niveau})";
        }

        // Reputation requise
        if ($this->faction_id && $this->reputation_requise > 0) {
            $rep = Reputation::getOuCreer($personnage->id, $this->faction_id);
            if ($rep->valeur < $this->reputation_requise) {
                $raisons[] = "Reputation {$this->reputation_requise} requise avec {$this->faction->nom} (vous avez {$rep->valeur})";
            }
        }

        // Deja en cours
        $existante = $this->personnages()
            ->where('personnage_id', $personnage->id)
            ->whereIn('mission_personnage.statut', ['en_cours'])
            ->first();

        if ($existante) {
            $raisons[] = "Mission deja en cours";
        }

        // Cooldown si repetable
        if ($this->repetable && $this->cooldown) {
            $derniere = $this->personnages()
                ->where('personnage_id', $personnage->id)
                ->where('mission_personnage.statut', 'rendue')
                ->orderBy('mission_personnage.completee_le', 'desc')
                ->first();

            if ($derniere && $derniere->pivot->completee_le) {
                $cooldown_fin = $derniere->pivot->completee_le->addMinutes($this->cooldown);
                if (now()->lt($cooldown_fin)) {
                    $minutes = now()->diffInMinutes($cooldown_fin);
                    $raisons[] = "Cooldown: {$minutes} minutes restantes";
                }
            }
        }

        // Non repetable et deja completee
        if (!$this->repetable) {
            $completee = $this->personnages()
                ->where('personnage_id', $personnage->id)
                ->whereIn('mission_personnage.statut', ['rendue', 'completee'])
                ->exists();

            if ($completee) {
                $raisons[] = "Mission deja completee (non repetable)";
            }
        }

        return [
            'peut_accepter' => empty($raisons),
            'raisons' => $raisons,
        ];
    }

    /**
     * Accepter la mission pour un personnage
     */
    public function accepter(Personnage $personnage): array
    {
        $check = $this->peutEtreAcceptee($personnage);
        if (!$check['peut_accepter']) {
            return [
                'success' => false,
                'message' => implode('. ', $check['raisons']),
            ];
        }

        // Initialiser la progression
        $progression = [];
        foreach ($this->objectifs as $index => $objectif) {
            $progression[] = [
                'index' => $index,
                'actuel' => 0,
                'requis' => $objectif['quantite'] ?? 1,
            ];
        }

        // Calculer expiration
        $expire_le = null;
        if ($this->duree_limite) {
            $expire_le = now()->addMinutes($this->duree_limite);
        }

        // Attacher au personnage
        $this->personnages()->attach($personnage->id, [
            'statut' => 'en_cours',
            'progression' => json_encode($progression),
            'acceptee_le' => now(),
            'expire_le' => $expire_le,
        ]);

        return [
            'success' => true,
            'message' => "Mission '{$this->titre}' acceptee!",
        ];
    }

    /**
     * Mettre a jour la progression d'un objectif
     */
    public function mettreAJourProgression(Personnage $personnage, int $objectif_index, int $quantite): bool
    {
        $pivot = $this->personnages()
            ->where('personnage_id', $personnage->id)
            ->where('mission_personnage.statut', 'en_cours')
            ->first();

        if (!$pivot) {
            return false;
        }

        $progression = json_decode($pivot->pivot->progression, true);

        foreach ($progression as &$obj) {
            if ($obj['index'] == $objectif_index) {
                $obj['actuel'] = min($obj['actuel'] + $quantite, $obj['requis']);
                break;
            }
        }

        // Mettre a jour
        $this->personnages()->updateExistingPivot($personnage->id, [
            'progression' => json_encode($progression),
        ]);

        // Verifier si completee
        $completee = true;
        foreach ($progression as $obj) {
            if ($obj['actuel'] < $obj['requis']) {
                $completee = false;
                break;
            }
        }

        if ($completee) {
            $this->personnages()->updateExistingPivot($personnage->id, [
                'statut' => 'completee',
                'completee_le' => now(),
            ]);
        }

        return $completee;
    }

    /**
     * Rendre la mission et recevoir les recompenses
     */
    public function rendre(Personnage $personnage): array
    {
        $pivot = $this->personnages()
            ->where('personnage_id', $personnage->id)
            ->where('mission_personnage.statut', 'completee')
            ->first();

        if (!$pivot) {
            return [
                'success' => false,
                'message' => "Mission non completee ou deja rendue",
            ];
        }

        // Donner les recompenses
        $personnage->credits += $this->recompense_credits;
        $personnage->ajouterExperience($this->recompense_xp);
        $personnage->save();

        // Reputation
        if ($this->faction_id) {
            $rep = Reputation::getOuCreer($personnage->id, $this->faction_id);
            $rep->modifier($this->recompense_reputation);
            $rep->missions_completees++;
            $rep->save();
        }

        // Objets recompenses
        $objets_donnes = [];
        if ($this->recompense_objets && $personnage->vaisseauActif) {
            foreach ($this->recompense_objets as $item) {
                $personnage->vaisseauActif->ajouterRessource($item['ressource_id'], $item['quantite']);
                $objets_donnes[] = $item;
            }
        }

        // Mettre a jour le statut
        $fois = $pivot->pivot->fois_completee + 1;
        $this->personnages()->updateExistingPivot($personnage->id, [
            'statut' => 'rendue',
            'fois_completee' => $fois,
        ]);

        return [
            'success' => true,
            'message' => "Mission rendue!",
            'recompenses' => [
                'credits' => $this->recompense_credits,
                'xp' => $this->recompense_xp,
                'reputation' => $this->recompense_reputation,
                'objets' => $objets_donnes,
            ],
        ];
    }

    /**
     * Abandonner la mission
     */
    public function abandonner(Personnage $personnage): array
    {
        $pivot = $this->personnages()
            ->where('personnage_id', $personnage->id)
            ->where('mission_personnage.statut', 'en_cours')
            ->first();

        if (!$pivot) {
            return [
                'success' => false,
                'message' => "Aucune mission en cours a abandonner",
            ];
        }

        // Penalite reputation
        if ($this->faction_id) {
            $rep = Reputation::getOuCreer($personnage->id, $this->faction_id);
            $rep->modifier(-$this->penalite_reputation);
            $rep->missions_echouees++;
            $rep->save();
        }

        // Mettre a jour
        $this->personnages()->updateExistingPivot($personnage->id, [
            'statut' => 'abandonnee',
        ]);

        return [
            'success' => true,
            'message' => "Mission abandonnee. Penalite: -{$this->penalite_reputation} reputation",
        ];
    }

    /**
     * Obtenir les missions disponibles pour un personnage
     */
    public static function getDisponibles(Personnage $personnage): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('actif', true)
            ->where('niveau_requis', '<=', $personnage->niveau)
            ->with('faction')
            ->get()
            ->filter(function ($mission) use ($personnage) {
                return $mission->peutEtreAcceptee($personnage)['peut_accepter'];
            });
    }
}
