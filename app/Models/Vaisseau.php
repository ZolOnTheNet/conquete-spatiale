<?php

namespace App\Models;

use App\Traits\HasInventaire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaisseau extends Model
{
    use HasInventaire;
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
        // Combat
        'coque_max',
        'coque_actuelle',
        'arme_1_id',
        'arme_2_id',
        'arme_3_id',
        'bouclier_id',
        'bouclier_actuel',
        'esquive',
        'bonus_precision',
        'en_combat',
        // Stations et arrimage
        'arrime_a_station_id',
        'arrime_le',
        'dernier_jet_pilotage',
        // Système orbital
        'orbite_planete_id',
        'orbite_rayon_ua',
        'orbite_angle_initial',
        'orbite_debut',
    ];

    protected $casts = [
        'soutes' => 'array',
        'emplacements_armes' => 'array',
        'pannes_actuelles' => 'array',
        'programmes' => 'array',
        'emplacements' => 'array',
        'date_logs' => 'array',
        'coque_max' => 'integer',
        'coque_actuelle' => 'integer',
        'bouclier_actuel' => 'integer',
        'esquive' => 'integer',
        'bonus_precision' => 'integer',
        'en_combat' => 'boolean',
        'dernier_jet_pilotage' => 'array',
        'arrime_le' => 'datetime',
        'orbite_debut' => 'datetime',
    ];

    // Relations
    public function objetSpatial(): BelongsTo
    {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
    }

    public function orbitePlanete(): BelongsTo
    {
        return $this->belongsTo(Planete::class, 'orbite_planete_id');
    }

    /**
     * Calcule la position actuelle du vaisseau en orbite (en cUA)
     * Retourne null si le vaisseau n'est pas en orbite
     *
     * @param float|null $timestampJours Timestamp en jours (null = actuel)
     * @return array|null ['x' => int, 'y' => int, 'z' => int] Position en cUA, ou null si pas en orbite
     */
    public function getPositionOrbitale(?float $timestampJours = null): ?array
    {
        // Vérifier si le vaisseau est en orbite
        if (!$this->orbite_planete_id || !$this->orbite_rayon_ua || $this->orbite_angle_initial === null) {
            return null;
        }

        $timestampJours = $timestampJours ?? \App\Helpers\GameTimeHelper::getTimestampJoursActuel(
            \App\Models\Personnage::where('vaisseau_actif_id', $this->id)->first()
        );

        // Charger la planète orbitée
        $planete = $this->orbitePlanete;
        if (!$planete) {
            return null;
        }

        // Position absolue de la planète (en cUA)
        $planetePosAbs = $planete->getPositionAbsolue($timestampJours);

        // Calculer vitesse angulaire orbitale (rad/jour)
        // Formule simplifiée: plus l'orbite est proche, plus elle est rapide
        // Pour une orbite à 0.01 UA (~1.5M km), période ~1 jour
        // vitesse_angulaire = 2π / période
        $rayon_ua = $this->orbite_rayon_ua;
        $periode_jours = sqrt($rayon_ua) * 10; // Simplifié (Kepler serait: T² ∝ R³)
        $vitesse_angulaire = (2 * M_PI) / max(0.1, $periode_jours); // rad/jour

        // Temps écoulé depuis début orbite
        $tempsEcoule = $timestampJours - \App\Helpers\GameTimeHelper::dateToJours($this->orbite_debut);

        // Angle actuel du vaisseau
        $angleActuel = $this->orbite_angle_initial + ($vitesse_angulaire * $tempsEcoule);

        // Position relative du vaisseau par rapport à la planète (orbite circulaire dans plan XY)
        $rayon_cua = \App\Helpers\CoordinatesHelper::uaToCua($rayon_ua);
        $x_relatif = (int)round($rayon_cua * cos($angleActuel));
        $y_relatif = (int)round($rayon_cua * sin($angleActuel));
        $z_relatif = 0; // Plan orbital simplifié

        // Position absolue du vaisseau = position planète + offset orbital
        return [
            'x' => $planetePosAbs['x'] + $x_relatif,
            'y' => $planetePosAbs['y'] + $y_relatif,
            'z' => $planetePosAbs['z'] + $z_relatif,
        ];
    }

    /**
     * Vérifie si le vaisseau est actuellement en orbite
     *
     * @return bool
     */
    public function estEnOrbite(): bool
    {
        return $this->orbite_planete_id !== null;
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

    /**
     * Recharge l'énergie depuis une étoile (Type B uniquement)
     * @param int $nb_pa Nombre de PA à dépenser pour recharger
     * @param bool $admin_override Mode admin qui bypass les restrictions
     * @return array Résultat du rechargement
     */
    public function rechargerDepuisEtoile(int $nb_pa, bool $admin_override = false): array
    {
        // Vérifier que le vaisseau est de Type B (extraction énergie) sauf en mode admin
        if (!$admin_override && $this->mode !== 'energetique') {
            return [
                'success' => false,
                'message' => "[ERREUR] Rechargement impossible. Votre vaisseau utilise une propulsion à combustible (Type A).\nVous devez vous ravitailler à une station avec la commande 'ravitailler'.",
            ];
        }

        // Vérifier que l'énergie n'est pas déjà au maximum
        if ($this->energie_actuelle >= $this->reserve) {
            return [
                'success' => false,
                'message' => "[INFO] Réserve d'énergie déjà pleine ({$this->energie_actuelle}/{$this->reserve} UE).",
            ];
        }

        // Trouver le système stellaire actuel
        $os = $this->objetSpatial;
        $systeme = \App\Models\SystemeStellaire::where('secteur_x', $os->secteur_x)
            ->where('secteur_y', $os->secteur_y)
            ->where('secteur_z', $os->secteur_z)
            ->first();

        if (!$systeme) {
            return [
                'success' => false,
                'message' => "[ERREUR] Aucun système stellaire détecté à cette position. Impossible de recharger en espace profond.",
            ];
        }

        // Calculer la puissance de l'étoile
        $puissance_etoile = $systeme->puissance ?? $systeme->puissance_solaire ?? 50;

        // Calculer l'énergie rechargée (puissance étoile par PA)
        $energie_rechargee = $puissance_etoile * $nb_pa;

        // Appliquer le rechargement
        $energie_avant = $this->energie_actuelle;
        $this->rechargerEnergie($energie_rechargee);
        $energie_apres = $this->energie_actuelle;
        $energie_reellement_ajoutee = $energie_apres - $energie_avant;

        $this->save();

        // Message différent selon le type de propulsion
        $type_propulsion_msg = match($this->type_propulsion ?? 1) {
            1 => 'micro-panneaux',
            2 => 'voile solaire',
            3 => 'matière noire',
            default => 'extraction d\'énergie',
        };

        $message = "[RECHARGE] Extraction d'énergie stellaire réussie!\n";
        $message .= "─────────────────────────────\n";
        $message .= "Système: {$systeme->nom}\n";
        $message .= "Type d'étoile: {$systeme->type_etoile} ({$systeme->couleur})\n";
        $message .= "Puissance stellaire: {$puissance_etoile}\n";
        $message .= "Type de propulsion: {$type_propulsion_msg}\n";
        $message .= "─────────────────────────────\n";
        $message .= "PA dépensés: {$nb_pa}\n";
        $message .= "Énergie extraite: +{$energie_reellement_ajoutee} UE\n";
        $message .= "Réserve: {$energie_apres}/{$this->reserve} UE\n";

        // Info supplémentaire pour voile solaire
        if ($this->type_propulsion == 2) {
            $message .= "\n[INFO] Votre voile solaire vous permet de vous déplacer pendant le rechargement.";
        }

        return [
            'success' => true,
            'message' => $message,
            'energie_avant' => $energie_avant,
            'energie_apres' => $energie_apres,
            'energie_ajoutee' => $energie_reellement_ajoutee,
            'pa_depenses' => $nb_pa,
            'puissance_etoile' => $puissance_etoile,
        ];
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
     * IMPORTANT: Supprime TOUS les progrès de scan du personnage et réinitialise le bonus
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

        // Trouver le personnage qui possède ce vaisseau
        $personnage = \App\Models\Personnage::where('vaisseau_actif_id', $this->id)->first();

        if ($personnage) {
            // Supprimer tous les ScanProgress du personnage
            \App\Models\ScanProgress::where('personnage_id', $personnage->id)->delete();

            // Réinitialiser le bonus de scan
            $personnage->reinitialiserBonusScan();
        }
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
     * Obtient la puissance de scan (base + bonus équipement)
     * NOTE: scan_niveau_actuel N'AFFECTE PAS la formule des dés
     */
    public function getPuissanceScanEffective(): int
    {
        $base = $this->puissance_scan ?? 20;  // Valeur par défaut si NULL
        $bonus = $this->bonus_scan ?? 0;

        return $base + $bonus;
    }

    /**
     * Retourne la formule de dés pour le scanner selon la puissance effective
     * Système à 21 paliers (de 1d4 à 5d24+2d20)
     * @return array ['formula' => string, 'dice' => array]
     */
    public function getDiceFormula(): array
    {
        $puissance = $this->getPuissanceScanEffective();

        $tiers = [
            ['min' => 1, 'max' => 4, 'dice' => [[1, 4]]],
            ['min' => 5, 'max' => 9, 'dice' => [[2, 4]]],
            ['min' => 10, 'max' => 14, 'dice' => [[3, 4]]],
            ['min' => 15, 'max' => 19, 'dice' => [[1, 6]]],
            ['min' => 20, 'max' => 29, 'dice' => [[2, 6]]],
            ['min' => 30, 'max' => 39, 'dice' => [[3, 6]]],
            ['min' => 40, 'max' => 49, 'dice' => [[4, 6]]],
            ['min' => 50, 'max' => 59, 'dice' => [[1, 8]]],
            ['min' => 60, 'max' => 69, 'dice' => [[2, 8]]],
            ['min' => 70, 'max' => 79, 'dice' => [[3, 8]]],
            ['min' => 80, 'max' => 89, 'dice' => [[1, 10]]],
            ['min' => 90, 'max' => 99, 'dice' => [[2, 10]]],
            ['min' => 100, 'max' => 109, 'dice' => [[3, 10]]],
            ['min' => 110, 'max' => 119, 'dice' => [[1, 12]]],
            ['min' => 120, 'max' => 124, 'dice' => [[2, 12]]],
            ['min' => 125, 'max' => 129, 'dice' => [[3, 12]]],
            ['min' => 130, 'max' => 134, 'dice' => [[4, 12]]],
            ['min' => 135, 'max' => 139, 'dice' => [[5, 12]]],
            ['min' => 140, 'max' => 144, 'dice' => [[1, 20]]],
            ['min' => 145, 'max' => 149, 'dice' => [[1, 24]]],
            ['min' => 150, 'max' => 9999, 'dice' => [[5, 24], [2, 20]]],
        ];

        foreach ($tiers as $tier) {
            if ($puissance >= $tier['min'] && $puissance <= $tier['max']) {
                $formula = [];
                $diceArray = [];
                foreach ($tier['dice'] as [$nb, $faces]) {
                    $formula[] = "{$nb}d{$faces}";
                    $diceArray[] = ['nb' => $nb, 'faces' => $faces];
                }
                return [
                    'formula' => implode(' + ', $formula),
                    'dice' => $diceArray,
                    'puissance' => $puissance
                ];
            }
        }

        // Fallback (ne devrait jamais arriver)
        return [
            'formula' => '1d4',
            'dice' => [['nb' => 1, 'faces' => 4]],
            'puissance' => $puissance
        ];
    }

    /**
     * Lance les dés du scanner et retourne les résultats détaillés
     * @return array Résultats du jet de scan
     */
    public function lancerDesScan(): array
    {
        $formula = $this->getDiceFormula();
        $total = 0;
        $details = [];
        $allRolls = [];

        foreach ($formula['dice'] as $die) {
            $rolls = [];
            for ($i = 0; $i < $die['nb']; $i++) {
                $roll = rand(1, $die['faces']);
                $rolls[] = $roll;
                $total += $roll;
            }
            $allRolls[] = [
                'dice' => "{$die['nb']}d{$die['faces']}",
                'rolls' => $rolls,
                'sum' => array_sum($rolls)
            ];
            $details[] = "{$die['nb']}d{$die['faces']}: [" . implode(', ', $rolls) . "] = " . array_sum($rolls);
        }

        return [
            'total' => $total,
            'formula' => $formula['formula'],
            'puissance' => $formula['puissance'],
            'details' => implode(' + ', $details),
            'rolls' => $allRolls
        ];
    }

    /**
     * Calcule la capacité de soute restante
     */
    public function getCapaciteRestante(): float
    {
        $capacite_totale = $this->place_soute ?? 1000; // tonnes
        $poids_actuel = $this->getPoidsInventaire();

        return max(0, $capacite_totale - $poids_actuel);
    }

    /**
     * Vérifie si le vaisseau peut charger une quantité de ressource
     */
    public function peutCharger(int $ressource_id, int $quantite): bool
    {
        $ressource = Ressource::find($ressource_id);
        if (!$ressource) return false;

        $poids_ajoute = $ressource->poids_unitaire * $quantite;
        return $this->getCapaciteRestante() >= $poids_ajoute;
    }

    // === SYSTÈME DE COMBAT ===

    /**
     * Relations armes
     */
    public function arme1(): BelongsTo
    {
        return $this->belongsTo(Arme::class, 'arme_1_id');
    }

    public function arme2(): BelongsTo
    {
        return $this->belongsTo(Arme::class, 'arme_2_id');
    }

    public function arme3(): BelongsTo
    {
        return $this->belongsTo(Arme::class, 'arme_3_id');
    }

    /**
     * Relation bouclier
     */
    public function bouclier(): BelongsTo
    {
        return $this->belongsTo(Bouclier::class, 'bouclier_id');
    }

    /**
     * Obtenir toutes les armes equipees
     */
    public function getArmesEquipees(): array
    {
        $armes = [];
        if ($this->arme1) $armes[] = $this->arme1;
        if ($this->arme2) $armes[] = $this->arme2;
        if ($this->arme3) $armes[] = $this->arme3;
        return $armes;
    }

    /**
     * Equiper une arme dans un slot
     */
    public function equiperArme(int $arme_id, int $slot = 1): bool
    {
        $arme = Arme::find($arme_id);
        if (!$arme) return false;

        match($slot) {
            1 => $this->arme_1_id = $arme_id,
            2 => $this->arme_2_id = $arme_id,
            3 => $this->arme_3_id = $arme_id,
            default => null,
        };

        $this->save();
        return true;
    }

    /**
     * Equiper un bouclier
     */
    public function equiperBouclier(int $bouclier_id): bool
    {
        $bouclier = Bouclier::find($bouclier_id);
        if (!$bouclier) return false;

        $this->bouclier_id = $bouclier_id;
        $this->bouclier_actuel = $bouclier->points_max;
        $this->save();
        return true;
    }

    /**
     * Calculer les degats totaux d'une salve
     */
    public function tirerSalve(int $esquive_cible = 0): array
    {
        $resultats = [];
        $energie_totale = 0;

        foreach ($this->getArmesEquipees() as $arme) {
            $cout = $arme->getCoutEnergieSalve();

            if ($this->energie_actuelle >= $cout) {
                $this->energie_actuelle -= $cout;
                $energie_totale += $cout;

                $resultat = $arme->attaquer($this->bonus_precision, $esquive_cible);
                $resultats[] = [
                    'arme' => $arme->nom,
                    'type' => $arme->type,
                    'tirs' => $resultat,
                    'energie' => $cout,
                ];
            }
        }

        $this->save();

        return [
            'resultats' => $resultats,
            'energie_utilisee' => $energie_totale,
        ];
    }

    /**
     * Recevoir des degats
     */
    public function recevoirDegats(int $degats, string $type_arme = 'laser'): array
    {
        $degats_bouclier = 0;
        $degats_coque = 0;

        // D'abord le bouclier absorbe
        if ($this->bouclier && $this->bouclier_actuel > 0) {
            $absorption = $this->bouclier->absorberDegats($degats, $type_arme, $this->bouclier_actuel);
            $this->bouclier_actuel = $absorption['bouclier_restant'];
            $degats_bouclier = $absorption['degats_bouclier'];
            $degats_coque = $absorption['degats_coque'];
        } else {
            $degats_coque = $degats;
        }

        // Puis la coque
        $this->coque_actuelle = max(0, $this->coque_actuelle - $degats_coque);
        $this->save();

        return [
            'degats_totaux' => $degats,
            'degats_bouclier' => $degats_bouclier,
            'degats_coque' => $degats_coque,
            'bouclier_restant' => $this->bouclier_actuel,
            'coque_restante' => $this->coque_actuelle,
            'detruit' => $this->coque_actuelle <= 0,
        ];
    }

    /**
     * Regenerer le bouclier
     */
    public function regenererBouclier(): int
    {
        if (!$this->bouclier) return 0;

        $ancien = $this->bouclier_actuel;
        $this->bouclier_actuel = $this->bouclier->regenerer($this->bouclier_actuel);
        $this->save();

        return $this->bouclier_actuel - $ancien;
    }

    /**
     * Reparer la coque
     */
    public function reparerCoque(int $quantite): int
    {
        $ancien = $this->coque_actuelle;
        $this->coque_actuelle = min($this->coque_max, $this->coque_actuelle + $quantite);
        $this->save();

        return $this->coque_actuelle - $ancien;
    }

    /**
     * Est-ce que le vaisseau est detruit?
     */
    public function isDetruit(): bool
    {
        return $this->coque_actuelle <= 0;
    }

    /**
     * Obtenir le pourcentage de coque
     */
    public function getPourcentageCoque(): float
    {
        if ($this->coque_max <= 0) return 0;
        return round(($this->coque_actuelle / $this->coque_max) * 100, 1);
    }

    /**
     * Obtenir le pourcentage de bouclier
     */
    public function getPourcentageBouclier(): float
    {
        if (!$this->bouclier) return 0;
        return round(($this->bouclier_actuel / $this->bouclier->points_max) * 100, 1);
    }
}
