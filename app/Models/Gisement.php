<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gisement extends Model
{
    protected $fillable = [
        'planete_id',
        'ressource_id',
        'latitude',
        'longitude',
        'richesse',
        'quantite_totale',
        'quantite_restante',
        'decouvert',
        'decouvert_le',
        'decouvert_par',
        'en_exploitation',
        'exploite_par',
    ];

    protected $casts = [
        'decouvert' => 'boolean',
        'en_exploitation' => 'boolean',
        'decouvert_le' => 'datetime',
        'richesse' => 'integer',
        'quantite_totale' => 'integer',
        'quantite_restante' => 'integer',
    ];

    /**
     * Planète contenant ce gisement
     */
    public function planete(): BelongsTo
    {
        return $this->belongsTo(Planete::class);
    }

    /**
     * Ressource du gisement
     */
    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class);
    }

    /**
     * Personnage ayant découvert le gisement
     */
    public function decouvreur(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'decouvert_par');
    }

    /**
     * Personnage exploitant le gisement
     */
    public function exploitant(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'exploite_par');
    }

    /**
     * Mines d'exploitation sur ce gisement (MAME)
     */
    public function mines(): HasMany
    {
        return $this->hasMany(Mine::class);
    }

    /**
     * Calculer rendement effectif d'extraction
     */
    public function getRendementEffectif(): int
    {
        // Base = richesse du gisement
        $rendement = $this->richesse;

        // TODO: Facteurs additionnels
        // - Équipement extracteur
        // - Compétences personnage
        // - Technologie

        return max(10, min(100, $rendement)); // Entre 10 et 100%
    }

    /**
     * Extraire une quantité
     */
    public function extraire(int $quantite): int
    {
        $quantite_extraite = min($quantite, $this->quantite_restante);

        $this->quantite_restante -= $quantite_extraite;
        $this->save();

        return $quantite_extraite;
    }

    /**
     * Vérifier si épuisé
     */
    public function isEpuise(): bool
    {
        return $this->quantite_restante <= 0;
    }

    /**
     * Pourcentage restant
     */
    public function getPourcentageRestant(): float
    {
        if ($this->quantite_totale == 0) {
            return 0;
        }

        return round(($this->quantite_restante / $this->quantite_totale) * 100, 2);
    }

    /**
     * Découvrir le gisement
     */
    public function decouvrir(Personnage $personnage): void
    {
        $this->decouvert = true;
        $this->decouvert_le = now();
        $this->decouvert_par = $personnage->id;
        $this->save();
    }

    /**
     * Démarrer exploitation
     */
    public function demarrerExploitation(Personnage $personnage): void
    {
        $this->en_exploitation = true;
        $this->exploite_par = $personnage->id;
        $this->save();
    }

    /**
     * Arrêter exploitation
     */
    public function arreterExploitation(): void
    {
        $this->en_exploitation = false;
        $this->exploite_par = null;
        $this->save();
    }
}
