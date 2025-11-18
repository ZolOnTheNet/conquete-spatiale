<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ennemi extends Model
{
    use HasFactory;

    protected $table = 'ennemis';

    protected $fillable = [
        'code',
        'nom',
        'description',
        'type',
        'faction',
        'niveau',
        'difficulte',
        'coque_max',
        'bouclier_max',
        'bouclier_regen',
        'esquive',
        'degats_min',
        'degats_max',
        'precision',
        'cadence',
        'type_arme',
        'resistance_laser',
        'resistance_canon',
        'resistance_missile',
        'resistance_plasma',
        'resistance_emp',
        'tactique',
        'seuil_fuite',
        'credits_min',
        'credits_max',
        'xp_recompense',
        'zone_niveau_min',
        'zone_niveau_max',
        'chance_spawn',
    ];

    protected $casts = [
        'niveau' => 'integer',
        'coque_max' => 'integer',
        'bouclier_max' => 'integer',
        'bouclier_regen' => 'integer',
        'esquive' => 'integer',
        'degats_min' => 'integer',
        'degats_max' => 'integer',
        'precision' => 'integer',
        'cadence' => 'integer',
        'resistance_laser' => 'integer',
        'resistance_canon' => 'integer',
        'resistance_missile' => 'integer',
        'resistance_plasma' => 'integer',
        'resistance_emp' => 'integer',
        'seuil_fuite' => 'integer',
        'credits_min' => 'integer',
        'credits_max' => 'integer',
        'xp_recompense' => 'integer',
        'zone_niveau_min' => 'integer',
        'zone_niveau_max' => 'integer',
        'chance_spawn' => 'integer',
    ];

    /**
     * Combats impliquant cet ennemi
     */
    public function combats(): HasMany
    {
        return $this->hasMany(Combat::class);
    }

    /**
     * Calculer les degats d'une attaque
     */
    public function calculerDegats(): int
    {
        return rand($this->degats_min, $this->degats_max);
    }

    /**
     * Tenter de toucher une cible
     */
    public function tenterToucher(int $esquive_cible = 0): bool
    {
        $chance = max(5, min(95, $this->precision - $esquive_cible));
        return rand(1, 100) <= $chance;
    }

    /**
     * Executer une attaque
     */
    public function attaquer(int $esquive_cible = 0): array
    {
        $resultats = [];

        for ($i = 0; $i < $this->cadence; $i++) {
            $touche = $this->tenterToucher($esquive_cible);
            $degats = $touche ? $this->calculerDegats() : 0;

            $resultats[] = [
                'touche' => $touche,
                'degats' => $degats,
            ];
        }

        return $resultats;
    }

    /**
     * Calculer les degats recus apres resistances
     */
    public function calculerDegatsRecus(int $degats, string $type_arme, int $bouclier_actuel): array
    {
        // Resistance selon type d'arme
        $resistance = match($type_arme) {
            'laser' => $this->resistance_laser,
            'canon' => $this->resistance_canon,
            'missile' => $this->resistance_missile,
            'plasma' => $this->resistance_plasma,
            'emp' => $this->resistance_emp,
            default => 0,
        };

        // Reduction des degats
        $degats_reduits = (int)floor($degats * (1 - $resistance / 100));
        $degats_reduits = max(1, $degats_reduits);

        // Absorption par bouclier
        $degats_bouclier = min($bouclier_actuel, $degats_reduits);
        $nouveau_bouclier = $bouclier_actuel - $degats_bouclier;

        // Degats restants sur coque
        $degats_coque = $degats_reduits - $degats_bouclier;

        return [
            'degats_initiaux' => $degats,
            'resistance' => $resistance,
            'degats_reduits' => $degats_reduits,
            'degats_bouclier' => $degats_bouclier,
            'degats_coque' => $degats_coque,
            'nouveau_bouclier' => $nouveau_bouclier,
        ];
    }

    /**
     * Decider de l'action selon la tactique
     */
    public function deciderAction(int $coque_actuelle, int $bouclier_actuel, int $coque_joueur): string
    {
        $pourcent_coque = ($coque_actuelle / $this->coque_max) * 100;

        // Verifier si doit fuir
        if ($pourcent_coque <= $this->seuil_fuite && $this->tactique !== 'agressif') {
            return 'fuir';
        }

        // Actions selon tactique
        return match($this->tactique) {
            'agressif' => 'attaquer',
            'defensif' => $bouclier_actuel < $this->bouclier_max * 0.3 ? 'regenerer' : 'attaquer',
            'fuite' => $pourcent_coque < 50 ? 'fuir' : 'attaquer',
            default => 'attaquer', // equilibre
        };
    }

    /**
     * Generer les recompenses
     */
    public function genererRecompenses(): array
    {
        $credits = rand($this->credits_min, $this->credits_max);

        // Bonus selon difficulte
        $multiplicateur = match($this->difficulte) {
            'facile' => 1.0,
            'moyen' => 1.5,
            'difficile' => 2.5,
            'boss' => 5.0,
            default => 1.0,
        };

        return [
            'credits' => (int)($credits * $multiplicateur),
            'xp' => (int)($this->xp_recompense * $multiplicateur),
        ];
    }

    /**
     * DPS moyen de l'ennemi
     */
    public function getDPS(): float
    {
        $degats_moyens = ($this->degats_min + $this->degats_max) / 2;
        $chance_toucher = $this->precision / 100;
        return $degats_moyens * $this->cadence * $chance_toucher;
    }

    /**
     * Trouver un ennemi aleatoire pour une zone
     */
    public static function spawnPourZone(int $niveau_zone): ?self
    {
        $ennemis = self::where('zone_niveau_min', '<=', $niveau_zone)
            ->where('zone_niveau_max', '>=', $niveau_zone)
            ->get();

        if ($ennemis->isEmpty()) {
            return null;
        }

        // Selection ponderee par chance_spawn
        $total_chance = $ennemis->sum('chance_spawn');
        $roll = rand(1, $total_chance);

        $cumul = 0;
        foreach ($ennemis as $ennemi) {
            $cumul += $ennemi->chance_spawn;
            if ($roll <= $cumul) {
                return $ennemi;
            }
        }

        return $ennemis->first();
    }

    /**
     * Verifier si un spawn a lieu
     */
    public static function checkSpawn(int $niveau_zone): bool
    {
        // Chance de base: 15% + 2% par niveau de zone
        $chance = 15 + ($niveau_zone * 2);
        return rand(1, 100) <= min($chance, 50);
    }
}
