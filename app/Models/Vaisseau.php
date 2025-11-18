<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaisseau extends Model
{
    protected $table = 'vaisseaux';

    protected $fillable = [
        'objet_spatial_id',
        'modele',
        'type_propulsion',
        'mode',
        'reserve',
        'energie_actuelle',
        'vitesse_conventionnelle',
        'vitesse_saut',
        'part_panne',
        'combustible',
        'efficacite',
        'type_combustible',
        'recuperation',
        'init_conventionnel',
        'init_hyperespace',
        'coef_conventionnel',
        'coef_hyperespace',
        'coef_pa_mn',
        'coef_pa_he',
        'max_soutes',
        'place_soute',
        'masse_variable',
        'soutes',
        'emplacements_armes',
        'nb_armes',
        'vetuste',
        'complexite_fct',
        'score_panne',
        'score_entretien',
        'pannes_actuelles',
        'system_informatique',
        'programmes',
        'emplacements',
        'date_logs',
        // Système de scan
        'portee_scan',
        'puissance_scan',
        'bonus_scan',
        'scan_niveau_actuel',
        'scan_secteur_x',
        'scan_secteur_y',
        'scan_secteur_z',
        'scan_position_x',
        'scan_position_y',
        'scan_position_z',
    ];

    protected $casts = [
        'soutes' => 'array',
        'emplacements_armes' => 'array',
        'pannes_actuelles' => 'array',
        'programmes' => 'array',
        'emplacements' => 'array',
        'date_logs' => 'array',
    ];

    // Relations
    public function objetSpatial(): BelongsTo
    {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
    }

    // Méthodes de propulsion (selon GDD)
    private function getMasseTotal(): float
    {
        // Masse totale = masse fixe (objet spatial) + masse variable (cargo)
        return $this->objetSpatial->masse + $this->masse_variable;
    }

    public function calculerConsommationConventionnelle(float $distance): float
    {
        // Formule GDD: Init_Conventionnel + (Masse × Distance / Vitesse)
        $masse = $this->getMasseTotal();
        $init = $this->init_conventionnel ?? config('game.deplacement.conventionnel.init', 0);
        return $init + ($masse * $distance / $this->vitesse_conventionnelle);
    }

    public function calculerConsommationHE(float $distance): float
    {
        // Formule GDD: Init_HE + (Coef_HE/100) × (Masse/Vitesse) × Distance
        $masse = $this->getMasseTotal();
        $init = $this->init_hyperespace ?? config('game.deplacement.hyperespace.init', 200);
        $coef = $this->coef_hyperespace ?? config('game.deplacement.hyperespace.coef', 0.5);
        return $init + ($coef * ($masse / $this->vitesse_saut) * $distance);
    }

    public function calculerNbPA(float $distance, float $consommation, string $mode = 'conventionnel'): int
    {
        if ($mode === 'hyperespace' || $mode === 'HE') {
            // Formule GDD: PA = pa_base + Coef_PAHE × Distance
            $pa_base = config('game.deplacement.hyperespace.pa_base', 1);
            $coef = $this->coef_pa_he ?? config('game.deplacement.hyperespace.coef_pa', 0.2);
            return (int)ceil($pa_base + ($coef * $distance));
        } else {
            // Formule GDD: PA = Consommation / Vitesse × Coef_PAMN
            $coef = $this->coef_pa_mn ?? config('game.deplacement.conventionnel.coef_pa', 1.0);
            return (int)ceil(($consommation / $this->vitesse_conventionnelle) * $coef);
        }
    }

    public function rechargerEnergie(float $quantite): void
    {
        $this->energie_actuelle = min(
            $this->reserve,
            $this->energie_actuelle + $quantite
        );
    }

    public function consommerEnergie(float $quantite): bool
    {
        if ($this->energie_actuelle >= $quantite) {
            $this->energie_actuelle -= $quantite;
            return true;
        }
        return false;
    }

    public function deplacer(ObjetSpatial $destination, string $mode = 'conventionnel'): array
    {
        $distance = $this->objetSpatial->calculerDistance($destination);
        $consommation = $mode === 'hyperespace'
            ? $this->calculerConsommationHE($distance)
            : $this->calculerConsommationConventionnelle($distance);
        $pa = $this->calculerNbPA($distance, $consommation, $mode);

        if ($this->consommerEnergie($consommation)) {
            // Mettre à jour position
            $this->objetSpatial->setPosition(
                $destination->secteur_x,
                $destination->secteur_y,
                $destination->secteur_z,
                $destination->position_x,
                $destination->position_y,
                $destination->position_z
            );
            $this->objetSpatial->save();

            // Réinitialiser scan (vaisseau a bougé)
            $this->reinitialiserScan();

            return [
                'success' => true,
                'consommation' => $consommation,
                'pa' => $pa,
                'energie_restante' => $this->energie_actuelle,
            ];
        }

        return [
            'success' => false,
            'erreur' => 'Énergie insuffisante',
            'manquant' => $consommation - $this->energie_actuelle,
        ];
    }

    /**
     * Déplacer avec coordonnées directes (mode conventionnel)
     */
    public function deplacerVers(float $secteur_x, float $secteur_y, float $secteur_z, float $position_x = 0, float $position_y = 0, float $position_z = 0, string $mode = 'conventionnel'): array
    {
        $os_current = $this->objetSpatial;

        // Calculer distance (formule euclidienne 3D)
        $dx = ($secteur_x + $position_x) - ($os_current->secteur_x + $os_current->position_x);
        $dy = ($secteur_y + $position_y) - ($os_current->secteur_y + $os_current->position_y);
        $dz = ($secteur_z + $position_z) - ($os_current->secteur_z + $os_current->position_z);
        $distance = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        $consommation = $mode === 'hyperespace'
            ? $this->calculerConsommationHE($distance)
            : $this->calculerConsommationConventionnelle($distance);
        $pa = $this->calculerNbPA($distance, $consommation, $mode);

        if (!$this->consommerEnergie($consommation)) {
            return [
                'success' => false,
                'erreur' => 'Énergie insuffisante',
                'manquant' => round($consommation - $this->energie_actuelle, 2),
                'requis' => round($consommation, 2),
            ];
        }

        // Mettre à jour position
        $os_current->secteur_x = (int)$secteur_x;
        $os_current->secteur_y = (int)$secteur_y;
        $os_current->secteur_z = (int)$secteur_z;
        $os_current->position_x = $position_x;
        $os_current->position_y = $position_y;
        $os_current->position_z = $position_z;
        $os_current->save();

        // Réinitialiser scan (vaisseau a bougé)
        $this->reinitialiserScan();

        $this->save();

        return [
            'success' => true,
            'distance' => round($distance, 2),
            'consommation' => round($consommation, 2),
            'pa' => $pa,
            'energie_restante' => round($this->energie_actuelle, 2),
        ];
    }

    // === SYSTÈME DE SCAN PROGRESSIF ===

    /**
     * Vérifie si le vaisseau a bougé depuis le dernier scan
     * Si oui, réinitialise le scan en cours
     */
    public function verifierDeplacementScan(): bool
    {
        // Pas de scan en cours
        if ($this->scan_niveau_actuel === 0 || $this->scan_secteur_x === null) {
            return false;
        }

        $os = $this->objetSpatial;

        // Vérifier si position a changé
        $a_bouge = (
            $os->secteur_x !== $this->scan_secteur_x ||
            $os->secteur_y !== $this->scan_secteur_y ||
            $os->secteur_z !== $this->scan_secteur_z ||
            abs($os->position_x - $this->scan_position_x) > 0.01 ||
            abs($os->position_y - $this->scan_position_y) > 0.01 ||
            abs($os->position_z - $this->scan_position_z) > 0.01
        );

        if ($a_bouge) {
            $this->reinitialiserScan();
            return true;
        }

        return false;
    }

    /**
     * Réinitialise le scan en cours
     */
    public function reinitialiserScan(): void
    {
        $this->scan_niveau_actuel = 0;
        $this->scan_secteur_x = null;
        $this->scan_secteur_y = null;
        $this->scan_secteur_z = null;
        $this->scan_position_x = null;
        $this->scan_position_y = null;
        $this->scan_position_z = null;
        $this->save();
    }

    /**
     * Démarre ou continue un scan progressif
     * @param int $niveau_apporte Niveau apporté par ce scan (défaut: puissance_scan / 10)
     * @return array Résultat du scan
     */
    public function scannerZone(int $niveau_apporte = null): array
    {
        $os = $this->objetSpatial;

        // Si pas de scan en cours ou position différente, démarrer nouveau scan
        if ($this->scan_niveau_actuel === 0 || $this->verifierDeplacementScan()) {
            $this->scan_secteur_x = $os->secteur_x;
            $this->scan_secteur_y = $os->secteur_y;
            $this->scan_secteur_z = $os->secteur_z;
            $this->scan_position_x = $os->position_x;
            $this->scan_position_y = $os->position_y;
            $this->scan_position_z = $os->position_z;
            $this->scan_niveau_actuel = 0;
        }

        // Calculer niveau apporté par ce scan
        if ($niveau_apporte === null) {
            $puissance_totale = $this->puissance_scan + $this->bonus_scan;
            $niveau_apporte = max(10, (int)($puissance_totale / 10)); // Minimum 10
        }

        // Cumuler avec scan précédent
        $ancien_niveau = $this->scan_niveau_actuel;
        $this->scan_niveau_actuel += $niveau_apporte;
        $this->save();

        return [
            'ancien_niveau' => $ancien_niveau,
            'niveau_apporte' => $niveau_apporte,
            'nouveau_niveau' => $this->scan_niveau_actuel,
            'portee' => $this->portee_scan,
            'puissance_totale' => $this->puissance_scan + $this->bonus_scan,
        ];
    }

    /**
     * Obtient la puissance de scan effective (base + bonus + niveau cumulé)
     */
    public function getPuissanceScanEffective(): int
    {
        return $this->puissance_scan + $this->bonus_scan + $this->scan_niveau_actuel;
    }
}
