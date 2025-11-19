<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reputation extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnage_id',
        'faction_id',
        'valeur',
        'rang',
        'missions_completees',
        'missions_echouees',
    ];

    protected $casts = [
        'valeur' => 'integer',
        'missions_completees' => 'integer',
        'missions_echouees' => 'integer',
    ];

    /**
     * Personnage
     */
    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    /**
     * Faction
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * Modifier la reputation
     */
    public function modifier(int $montant): void
    {
        $this->valeur = max(-1000, min(1000, $this->valeur + $montant));
        $this->rang = Faction::getRangPourReputation($this->valeur);
        $this->save();
    }

    /**
     * Obtenir ou creer la reputation pour un personnage/faction
     */
    public static function getOuCreer(int $personnage_id, int $faction_id): self
    {
        return self::firstOrCreate(
            [
                'personnage_id' => $personnage_id,
                'faction_id' => $faction_id,
            ],
            [
                'valeur' => 0,
                'rang' => 'neutre',
            ]
        );
    }

    /**
     * Verifier si peut accepter une mission selon reputation
     */
    public function peutAccepterMission(int $reputation_requise): bool
    {
        return $this->valeur >= $reputation_requise;
    }

    /**
     * Obtenir le pourcentage vers le prochain rang
     */
    public function getPourcentageVersProchainRang(): int
    {
        $seuils = [
            'hostile' => [-1000, -500],
            'inamical' => [-500, -100],
            'neutre' => [-100, 100],
            'amical' => [100, 500],
            'apprecie' => [500, 800],
            'honore' => [800, 1000],
            'venere' => [1000, 1000],
        ];

        $bornes = $seuils[$this->rang];
        $min = $bornes[0];
        $max = $bornes[1];

        if ($max == $min) return 100;

        return (int)((($this->valeur - $min) / ($max - $min)) * 100);
    }
}
