<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Vaisseau;
use App\Models\ScanProgress;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Station;
use App\Models\Mine;
use App\Models\ObjetSpatial;
use App\Models\Decouverte;
use App\Helpers\CoordinatesHelper;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    /**
     * Scan simple (sans jet de compétence)
     * Utilise uniquement les dés du scanner
     */
    public function scanSimple(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau pour scanner.'
            ], 400);
        }

        // Lancer les dés du scanner
        $scanResult = $vaisseau->lancerDesScan();

        // Lancer le dé de bonus SI le personnage en a un (persiste entre les scans)
        $bonusResult = $personnage->lancerBonusScan();
        $bonusLabel = $personnage->getScanBonusDiceLabel();

        // Total du scan
        $totalScan = $scanResult['total'] + $bonusResult;

        // Trouver les objets détectables dans la portée
        $objetsScannes = $this->trouverObjetsDetectables($vaisseau);

        // Mettre à jour les progrès de scan (avec bonus existant si présent)
        $resultats = $this->mettreAJourScans(
            $personnage,
            $vaisseau,
            $objetsScannes,
            $totalScan,
            $bonusResult
        );

        // Structure de retour selon si bonus existe ou non
        $bonusData = null;
        if ($bonusResult !== 0) {
            $bonusData = [
                'resultat' => $bonusResult,
                'formule' => $bonusLabel,
            ];
        }

        return response()->json([
            'success' => true,
            'scan' => $scanResult,
            'bonus' => $bonusData,
            'total' => $totalScan,
            'objets_detectes' => $resultats['nouveaux_detectes'],
            'progres' => $resultats['progres'],
            'message' => $bonusResult !== 0
                ? "Scan effectué : {$scanResult['formula']} + {$bonusLabel} = {$totalScan}"
                : "Scan effectué : {$scanResult['formula']} = {$scanResult['total']}"
        ]);
    }

    /**
     * Scan avec jet de Réglage (finesse)
     * Utilise les dés du scanner + bonus de finesse
     */
    public function scanAvecReglage(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau pour scanner.'
            ], 400);
        }

        // Difficulté du jet (peut être paramétré)
        $difficulte = $request->input('difficulte', config('game.scan.difficulte_reglage', 12));

        // Jet de compétence finesse
        $jetCompetence = $personnage->lancerJetScan('finesse', $difficulte);

        // Lancer les dés du scanner
        $scanResult = $vaisseau->lancerDesScan();

        // Lancer le dé de bonus (basé sur le jet de compétence)
        $bonusResult = $personnage->lancerBonusScan();
        $bonusLabel = $personnage->getScanBonusDiceLabel();

        // Total du scan
        $totalScan = $scanResult['total'] + $bonusResult;

        // Trouver les objets détectables
        $objetsScannes = $this->trouverObjetsDetectables($vaisseau);

        // Mettre à jour les progrès de scan
        $resultats = $this->mettreAJourScans(
            $personnage,
            $vaisseau,
            $objetsScannes,
            $totalScan,
            $bonusResult
        );

        return response()->json([
            'success' => true,
            'jet_competence' => $jetCompetence,
            'scan' => $scanResult,
            'bonus' => [
                'resultat' => $bonusResult,
                'formule' => $bonusLabel,
            ],
            'total' => $totalScan,
            'objets_detectes' => $resultats['nouveaux_detectes'],
            'progres' => $resultats['progres'],
            'message' => "Scan avec réglage : {$scanResult['formula']} + {$bonusLabel} = {$totalScan}"
        ]);
    }

    /**
     * Scan avec jet d'Astronomie (savoir)
     * Utilise les dés du scanner + bonus de savoir
     */
    public function scanAvecAstro(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau pour scanner.'
            ], 400);
        }

        // Difficulté du jet (peut être paramétré)
        $difficulte = $request->input('difficulte', config('game.scan.difficulte_astro', 12));

        // Jet de compétence savoir
        $jetCompetence = $personnage->lancerJetScan('savoir', $difficulte);

        // Lancer les dés du scanner
        $scanResult = $vaisseau->lancerDesScan();

        // Lancer le dé de bonus (basé sur le jet de compétence)
        $bonusResult = $personnage->lancerBonusScan();
        $bonusLabel = $personnage->getScanBonusDiceLabel();

        // Total du scan
        $totalScan = $scanResult['total'] + $bonusResult;

        // Trouver les objets détectables
        $objetsScannes = $this->trouverObjetsDetectables($vaisseau);

        // Mettre à jour les progrès de scan
        $resultats = $this->mettreAJourScans(
            $personnage,
            $vaisseau,
            $objetsScannes,
            $totalScan,
            $bonusResult
        );

        return response()->json([
            'success' => true,
            'jet_competence' => $jetCompetence,
            'scan' => $scanResult,
            'bonus' => [
                'resultat' => $bonusResult,
                'formule' => $bonusLabel,
            ],
            'total' => $totalScan,
            'objets_detectes' => $resultats['nouveaux_detectes'],
            'progres' => $resultats['progres'],
            'message' => "Scan avec astronomie : {$scanResult['formula']} + {$bonusLabel} = {$totalScan}"
        ]);
    }

    /**
     * Trouve tous les objets détectables dans la portée du scanner
     *
     * DEUX RÈGLES DISTINCTES:
     * 1. INTER-SECTEUR (Systèmes): Distance en AL uniquement avec secteurs
     * 2. INTRA-SECTEUR (Planètes/Stations/Mines): Même secteur requis, distance en cUA avec positions
     */
    private function trouverObjetsDetectables(Vaisseau $vaisseau): array
    {
        $objetSpatial = $vaisseau->objetSpatial;
        $portee = $vaisseau->portee_scan;

        // Coordonnées du vaisseau (GARDER SÉPARÉES)
        $secteurX = $objetSpatial->secteur_x;
        $secteurY = $objetSpatial->secteur_y;
        $secteurZ = $objetSpatial->secteur_z;
        $positionX = $objetSpatial->position_x;
        $positionY = $objetSpatial->position_y;
        $positionZ = $objetSpatial->position_z;

        $objets = [];

        // ============================================================
        // RÈGLE INTER-SECTEUR: Systèmes stellaires
        // Distance calculée UNIQUEMENT avec secteurs (AL)
        // ============================================================
        $systemes = SystemeStellaire::all();
        foreach ($systemes as $systeme) {
            // Distance UNIQUEMENT avec secteurs (AL) - positions ignorées
            $dx = $systeme->secteur_x - $secteurX;
            $dy = $systeme->secteur_y - $secteurY;
            $dz = $systeme->secteur_z - $secteurZ;

            $distanceAL = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

            if ($distanceAL <= $portee) {
                $objets[] = [
                    'type' => SystemeStellaire::class,
                    'id' => $systeme->id,
                    'objet' => $systeme,
                    'distance' => $distanceAL
                ];
            }
        }

        // ============================================================
        // RÈGLE INTRA-SECTEUR: Planètes (POI locaux)
        // Détectables UNIQUEMENT dans le même secteur
        // Distance calculée dans getScoreDetection() avec positions (cUA)
        // ============================================================
        $planetes = Planete::all();
        foreach ($planetes as $planete) {
            if (!$planete->systemeStellaire) continue;

            $systeme = $planete->systemeStellaire;

            // Vérifier si même secteur (RÈGLE INTRA-SECTEUR)
            if ($secteurX != $systeme->secteur_x ||
                $secteurY != $systeme->secteur_y ||
                $secteurZ != $systeme->secteur_z) {
                continue;
            }

            // Dans le même secteur → ajouter à la liste des objets scannables
            // La distance réelle sera calculée dans getScoreDetection()
            $objets[] = [
                'type' => Planete::class,
                'id' => $planete->id,
                'objet' => $planete,
                'distance' => 0, // Non utilisé pour POI locaux
            ];
        }

        // ============================================================
        // RÈGLE INTRA-SECTEUR: Stations (POI locaux)
        // ============================================================
        $stations = Station::all();
        foreach ($stations as $station) {
            if (!$station->systemeStellaire) continue;

            $systeme = $station->systemeStellaire;

            // Vérifier si même secteur
            if ($secteurX != $systeme->secteur_x ||
                $secteurY != $systeme->secteur_y ||
                $secteurZ != $systeme->secteur_z) {
                continue;
            }

            $objets[] = [
                'type' => Station::class,
                'id' => $station->id,
                'objet' => $station,
                'distance' => 0,
            ];
        }

        // ============================================================
        // RÈGLE INTRA-SECTEUR: Mines (POI locaux)
        // ============================================================
        $mines = Mine::all();
        foreach ($mines as $mine) {
            if (!$mine->planete || !$mine->planete->systemeStellaire) continue;

            $systeme = $mine->planete->systemeStellaire;

            // Vérifier si même secteur
            if ($secteurX != $systeme->secteur_x ||
                $secteurY != $systeme->secteur_y ||
                $secteurZ != $systeme->secteur_z) {
                continue;
            }

            $objets[] = [
                'type' => Mine::class,
                'id' => $mine->id,
                'objet' => $mine,
                'distance' => 0,
            ];
        }

        // ============================================================
        // Autres objets spatiaux (vaisseaux, etc.)
        // Utiliser RÈGLE INTRA-SECTEUR (même secteur uniquement)
        // ============================================================
        $autresObjets = ObjetSpatial::where('id', '!=', $objetSpatial->id)->get();
        foreach ($autresObjets as $autre) {
            // Vérifier si même secteur
            if ($secteurX != $autre->secteur_x ||
                $secteurY != $autre->secteur_y ||
                $secteurZ != $autre->secteur_z) {
                continue;
            }

            $objets[] = [
                'type' => ObjetSpatial::class,
                'id' => $autre->id,
                'objet' => $autre,
                'distance' => 0,
            ];
        }

        return $objets;
    }

    /**
     * Met à jour les progrès de scan pour tous les objets
     * IMPORTANT: Le CUMUL est UNIQUE et sur le vaisseau (scan_niveau_actuel)
     */
    private function mettreAJourScans(Personnage $personnage, Vaisseau $vaisseau, array $objets, int $totalScan, int $bonus): array
    {
        $nouveauxDetectes = [];
        $progres = [];

        $objetSpatial = $vaisseau->objetSpatial;

        // Coordonnées du vaisseau (GARDER SÉPARÉES pour les 2 règles)
        $secteurX = $objetSpatial->secteur_x;
        $secteurY = $objetSpatial->secteur_y;
        $secteurZ = $objetSpatial->secteur_z;
        $positionX = $objetSpatial->position_x;
        $positionY = $objetSpatial->position_y;
        $positionZ = $objetSpatial->position_z;

        // CUMUL UNIQUE sur le vaisseau
        $ancienCumul = $vaisseau->scan_niveau_actuel;
        $nouveauCumul = $ancienCumul + $totalScan;
        $vaisseau->scan_niveau_actuel = $nouveauCumul;
        $vaisseau->save();

        foreach ($objets as $objetInfo) {
            $objet = $objetInfo['objet'];
            $type = $objetInfo['type'];
            $id = $objetInfo['id'];

            // Calculer le score de détection requis
            // IMPORTANT: Passer secteur et position séparément (2 règles distinctes)
            $scoreDetection = $objet->getScoreDetection(
                $secteurX, $secteurY, $secteurZ,
                $positionX, $positionY, $positionZ
            );

            // Trouver ou créer le progrès de scan (SANS cumul individuel)
            $scanProgress = ScanProgress::firstOrCreate(
                [
                    'personnage_id' => $personnage->id,
                    'detectable_type' => $type,
                    'detectable_id' => $id,
                ],
                [
                    'score_detection' => $scoreDetection,
                    'cumul_scans' => 0, // Non utilisé, on garde pour compatibilité
                    'nb_scans_effectues' => 0,
                    'bonus_dice_type' => 0,
                    'detecte' => false,
                ]
            );

            $scanProgress->nb_scans_effectues += 1;
            $scanProgress->bonus_dice_type = $personnage->scan_bonus_dice_type;

            // Vérifier si l'objet est détecté (avec le CUMUL UNIQUE du vaisseau)
            $etaitDetecte = $scanProgress->detecte;
            if (!$etaitDetecte && $nouveauCumul >= $scoreDetection) {
                $scanProgress->detecte = true;

                // Marquer l'objet comme connu
                if (property_exists($objet, 'poi_connu')) {
                    $objet->poi_connu = true;
                    $objet->save();
                }

                // Ajouter à la spatiocarte du personnage
                $this->ajouterASpatiocarte($personnage, $vaisseau, $objet, $type, $scoreDetection);

                $nouveauxDetectes[] = [
                    'type' => class_basename($type),
                    'nom' => $objet->nom ?? "Objet #$id",
                    'score_requis' => $scoreDetection,
                    'cumul_final' => $nouveauCumul,
                ];
            }

            $scanProgress->save();

            // Calculer le pourcentage avec le CUMUL UNIQUE
            $pourcentage = $scoreDetection > 0 ? round(($nouveauCumul / $scoreDetection) * 100, 1) : 100;

            // Ajouter au rapport de progrès
            $progres[] = [
                'type' => class_basename($type),
                'nom' => $objet->nom ?? "Objet #$id",
                'score_requis' => round($scoreDetection, 2),
                'cumul_avant' => round($ancienCumul, 2),
                'apport' => $totalScan,
                'cumul_apres' => round($nouveauCumul, 2),
                'pourcentage' => min($pourcentage, 100),
                'detecte' => $scanProgress->detecte,
                'nouveau_detecte' => !$etaitDetecte && $scanProgress->detecte,
            ];
        }

        return [
            'nouveaux_detectes' => $nouveauxDetectes,
            'progres' => $progres,
        ];
    }

    /**
     * Calcule la distance 3D entre deux points en cUA
     *
     * @param int $x1 Position X1 en cUA
     * @param int $y1 Position Y1 en cUA
     * @param int $z1 Position Z1 en cUA
     * @param int $x2 Position X2 en cUA
     * @param int $y2 Position Y2 en cUA
     * @param int $z2 Position Z2 en cUA
     * @return int Distance en cUA
     */
    private function calculerDistance(int $x1, int $y1, int $z1, int $x2, int $y2, int $z2): int
    {
        return CoordinatesHelper::distance3D($x1, $y1, $z1, $x2, $y2, $z2);
    }

    /**
     * Réinitialise le bonus de scan du personnage
     */
    public function reinitialiserBonus(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->reinitialiserBonusScan();

        return response()->json([
            'success' => true,
            'message' => 'Bonus de scan réinitialisé.'
        ]);
    }

    /**
     * Réinitialise tous les progrès de scan du personnage
     */
    public function reinitialiserTousLesScans(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        // Supprimer tous les scan_progress du personnage
        $count = ScanProgress::where('personnage_id', $personnage->id)->delete();

        // Réinitialiser aussi le bonus
        $personnage->reinitialiserBonusScan();

        return response()->json([
            'success' => true,
            'message' => "Tous les scans réinitialisés ({$count} objet(s))."
        ]);
    }

    /**
     * Affiche la liste des scans en cours
     */
    public function listeScans(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        $scans = ScanProgress::where('personnage_id', $personnage->id)
            ->with('detectable')
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'type' => class_basename($scan->detectable_type),
                    'nom' => $scan->detectable->nom ?? "Objet #" . $scan->detectable_id,
                    'score_requis' => round($scan->score_detection, 2),
                    'cumul' => round($scan->cumul_scans, 2),
                    'pourcentage' => $scan->getPourcentageProgression(),
                    'nb_scans' => $scan->nb_scans_effectues,
                    'detecte' => $scan->detecte,
                ];
            });

        return response()->json([
            'success' => true,
            'scans' => $scans
        ]);
    }

    /**
     * Ajoute un objet détecté à la spatiocarte du personnage
     */
    private function ajouterASpatiocarte(Personnage $personnage, Vaisseau $vaisseau, $objet, string $type, float $scoreDetection): void
    {
        // Pour les systèmes stellaires : créer une Decouverte
        if ($type === SystemeStellaire::class) {
            $systeme = $objet;

            // Calculer la distance de découverte (RÈGLE INTER-SECTEUR: AL uniquement)
            $objetSpatial = $vaisseau->objetSpatial;

            $dx = $systeme->secteur_x - $objetSpatial->secteur_x;
            $dy = $systeme->secteur_y - $objetSpatial->secteur_y;
            $dz = $systeme->secteur_z - $objetSpatial->secteur_z;

            $distance = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

            // Créer ou mettre à jour la découverte
            Decouverte::updateOrCreate(
                [
                    'personnage_id' => $personnage->id,
                    'systeme_stellaire_id' => $systeme->id,
                ],
                [
                    'resultat_scan' => $scoreDetection,
                    'seuil_detection' => $scoreDetection,
                    'distance_decouverte' => round($distance, 2),
                    'decouvert_a' => now(),
                    'coordonnees_connues' => true,
                    'type_etoile_connu' => true,  // Seulement la puissance de l'étoile
                    'nb_planetes_connu' => false, // PAS le nombre de planètes (non visité)
                    'visite' => false,            // PAS visité
                ]
            );
        }

        // Pour les autres objets (Planete, Station, Mine, ObjetSpatial)
        // Le flag poi_connu = true est déjà défini via marquerDecouvert()
        // Ces objets sont visibles dans la spatiocarte locale quand on est dans le système
    }
}
