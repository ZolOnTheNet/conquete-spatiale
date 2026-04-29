<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScanProgress extends Model
{
    protected $table = 'scan_progress';

    protected $fillable = [
        'personnage_id',
        'detectable_type',
        'detectable_id',
        'score_detection',
        'cumul_scans',
        'nb_scans_effectues',
        'bonus_dice_type',
        'detecte',
    ];

    protected $casts = [
        'score_detection' => 'decimal:2',
        'cumul_scans' => 'decimal:2',
        'nb_scans_effectues' => 'integer',
        'bonus_dice_type' => 'integer',
        'detecte' => 'boolean',
    ];

    /**
     * Relation avec le personnage qui scanne
     */
    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    /**
     * Relation polymorphique vers l'objet scanné
     * Peut être: SystemeStellaire, Planete, Station, Mine, ObjetSpatial, etc.
     */
    public function detectable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Vérifie si l'objet a été détecté
     */
    public function estDetecte(): bool
    {
        return $this->cumul_scans >= $this->score_detection;
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getPourcentageProgression(): float
    {
        if ($this->score_detection <= 0) {
            return 100;
        }

        return min(100, round(($this->cumul_scans / $this->score_detection) * 100, 1));
    }

    /**
     * Marquer comme détecté
     */
    public function marquerDetecte(): void
    {
        $this->detecte = true;
        $this->save();

        // Marquer l'objet comme découvert (poi_connu = true)
        if ($this->detectable && method_exists($this->detectable, 'marquerDecouvert')) {
            $this->detectable->marquerDecouvert();
        }
    }
}
