<?php

namespace App\Http\Controllers;

use App\Models\SystemeStellaire;
use App\Models\Station;
use App\Services\NavigationService;
use App\Services\UniverseGeneratorService;
use App\Helpers\GameTimeHelper;
use App\Helpers\CoordinatesHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TimonerieController extends Controller
{
    protected NavigationService $navigationService;
    protected UniverseGeneratorService $universeGenerator;

    public function __construct(
        NavigationService $navigationService,
        UniverseGeneratorService $universeGenerator
    ) {
        $this->navigationService = $navigationService;
        $this->universeGenerator = $universeGenerator;
    }

    /**
     * Afficher la timonerie
     */
    public function index(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau || !$vaisseau->objetSpatial) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucun vaisseau actif');
        }

        $objetSpatial = $vaisseau->objetSpatial;

        // Récupérer les sauts hyperspatiaux disponibles
        $sautsDisponibles = $this->getSautsDisponibles($personnage, $vaisseau);

        // Récupérer les POI du secteur pour déplacements conventionnels
        $poisSecteur = $this->getPoISecteur($vaisseau, $personnage);

        // Système actuel
        $systemeActuel = SystemeStellaire::where('secteur_x', $objetSpatial->secteur_x)
            ->where('secteur_y', $objetSpatial->secteur_y)
            ->where('secteur_z', $objetSpatial->secteur_z)
            ->with('planetes')
            ->first();

        // Date actuelle du jeu pour calculs orbitaux JavaScript
        $dateJeuActuelle = GameTimeHelper::getDateActuelleJeu($personnage);

        return view('game.navire.timonerie', [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'objetSpatial' => $objetSpatial,
            'systemeActuel' => $systemeActuel,
            'sautsDisponibles' => $sautsDisponibles,
            'poisSecteur' => $poisSecteur,
            'dateJeuActuelle' => $dateJeuActuelle,
        ]);
    }

    /**
     * Calculer un saut hyperespace (sans l'effectuer)
     */
    public function calculerSaut(Request $request): JsonResponse
    {
        $destinationId = $request->input('destination_id');
        $poiId = $request->input('poi_id', 'systeme');
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        $destination = SystemeStellaire::find($destinationId);
        if (!$destination) {
            return response()->json(['error' => 'Destination introuvable'], 404);
        }

        // Calculer le coût du saut
        $distance = $this->navigationService->calculerDistance(
            $vaisseau->objetSpatial,
            $destination
        );

        $energieRequise = $this->navigationService->calculerCoutEnergie($distance);
        $paRequis = $this->navigationService->calculerCoutPA($distance);

        // Calculer le jet de navigation
        $jetResult = $this->calculerJetNavigation($personnage, $vaisseau);
        $jetNavigation = $jetResult['jet'];
        $scoreErreur = 50 - $jetNavigation;
        $deltaResult = $this->calculerDelta($scoreErreur, $distance);
        $deltaCalcule = $deltaResult['x']; // Utiliser delta X pour la représentation

        // Position cible (avant application du delta) — coordonnées secteur (années-lumière entières)
        $positionCible = [
            'secteur_x' => $destination->secteur_x,
            'secteur_y' => $destination->secteur_y,
            'secteur_z' => $destination->secteur_z,
        ];

        // Déterminer le nom du POI cible
        $poiNom = 'système';
        if ($poiId !== 'systeme') {
            // TODO: Charger le POI réel depuis la base de données
            // Pour l'instant, utiliser un nom par défaut
            $poiNom = "POI #{$poiId}";
        }

        // Stocker le calcul en session
        $this->storeCalculSaut($request, $destination, $poiId, $poiNom, [
            'distance' => $distance,
            'energieRequise' => $energieRequise,
            'paRequis' => $paRequis,
            'jetNavigation' => $jetNavigation,
            'scoreErreur' => $scoreErreur,
            'deltaDetails' => $deltaResult,
            'positionCible' => $positionCible,
            'jetDetails' => $jetResult,
        ]);

        // Vérifier la disponibilité
        $accessible = $vaisseau->energie_actuelle >= $energieRequise
            && $personnage->points_action >= $paRequis;

        return response()->json([
            'destination' => $destination->nom,
            'poiCible' => $poiNom,
            'distance' => round($distance, 2),
            'energieRequise' => $energieRequise,
            'paRequis' => $paRequis,
            'accessible' => $accessible,
            'energieDisponible' => $vaisseau->energie_actuelle,
            'paDisponibles' => $personnage->points_action,
            'jetNavigation' => $jetNavigation,
            'scoreErreur' => $scoreErreur,
            'precision' => number_format(100 - ($scoreErreur * 0.5), 1),
            'calculValide' => true,
            'calculValideJusquau' => now()->addMinutes(30)->format('H:i:s'),
        ]);
    }

    /**
     * Effectuer un saut hyperespace
     */
    public function effectuerSaut(Request $request): JsonResponse
    {
        $destinationId = $request->input('destination_id');
        $poiId = $request->input('poi_id', 'systeme');
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        // Vérifier si un calcul valide existe
        $calcul = $this->getCalculSautValide($request, $destinationId, $poiId);

        if (!$calcul) {
            return response()->json([
                'error' => 'Aucun calcul de saut valide. Utilisez d\'abord "Calculer".',
                'action' => 'calculer_requis'
            ], 400);
        }

        $destination = SystemeStellaire::find($destinationId);
        if (!$destination) {
            return response()->json(['error' => 'Destination introuvable'], 404);
        }

        // Vérifier les ressources (déjà vérifié dans calculerSaut, mais on reverifie)
        if ($vaisseau->energie_actuelle < $calcul['energie_requise']) {
            return response()->json(['error' => 'Énergie insuffisante'], 400);
        }

        if ($personnage->points_action < $calcul['pa_requis']) {
            return response()->json(['error' => 'Points d\'action insuffisants'], 400);
        }

        // Calculer la position d'arrivée avec le delta
        $distanceLocale = $poiId === 'systeme' ? 0.5 : $calcul['distance'];
        $deltaResult = $this->calculerDelta($calcul['score_erreur'], $distanceLocale, $poiId === 'systeme');
        $arrivee = $this->calculerArriveeAvecDelta(
            $vaisseau,
            $calcul['position_cible'],
            $deltaResult,
            $distanceLocale
        );

        // 🚀 GÉNÉRATION DYNAMIQUE: Étendre l'univers autour de la destination
        $this->expandUniverseAroundDestination($arrivee['secteur_x'], $arrivee['secteur_y'], $arrivee['secteur_z']);

        // Placer le vaisseau près de l'étoile du système destination (position en cUA)
        // position_x/y/z du système est le décalage de l'étoile dans son secteur (en cUA)
        $systemeDest = SystemeStellaire::where('secteur_x', $arrivee['secteur_x'])
            ->where('secteur_y', $arrivee['secteur_y'])
            ->where('secteur_z', $arrivee['secteur_z'])
            ->first();

        $arrivee['position_x'] = ($systemeDest->position_x ?? 0) + rand(-150, 150);
        $arrivee['position_y'] = ($systemeDest->position_y ?? 0) + rand(-150, 150);
        $arrivee['position_z'] = ($systemeDest->position_z ?? 0) + rand(-150, 150);

        // Mettre à jour la position du vaisseau
        $vaisseau->objetSpatial->update([
            'secteur_x' => $arrivee['secteur_x'],
            'secteur_y' => $arrivee['secteur_y'],
            'secteur_z' => $arrivee['secteur_z'],
            'position_x' => $arrivee['position_x'],
            'position_y' => $arrivee['position_y'],
            'position_z' => $arrivee['position_z'],
        ]);

        // IMPORTANT: Le vaisseau sort d'orbite lors d'un saut hyperespace
        if ($vaisseau->estEnOrbite()) {
            $vaisseau->update([
                'orbite_planete_id' => null,
                'orbite_rayon_ua' => null,
                'orbite_angle_initial' => null,
                'orbite_debut' => null,
            ]);
        }

        // IMPORTANT: Réinitialiser le scan après un saut
        $vaisseau->reinitialiserScan();

        // Consommer les ressources
        $vaisseau->energie_actuelle -= $calcul['energie_requise'];
        $vaisseau->save();

        $personnage->points_action -= $calcul['pa_requis'];
        $personnage->save();

        // Invalider le calcul après utilisation
        $request->session()->forget('dernier_calcul_saut');

        return response()->json([
            'success' => true,
            'message' => "Saut effectué vers {$destination->nom}",
            'poiCible' => $calcul['poi_nom'],
            'arrivee' => $arrivee,
            'jetNavigation' => $calcul['jet_navigation'],
            'scoreErreur' => $calcul['score_erreur'],
            'precision' => number_format(100 - ($calcul['score_erreur'] * 0.5), 1),
            'energieRestante' => $vaisseau->energie_actuelle,
            'paRestants' => $personnage->points_action,
        ]);
    }

    /**
     * S'approcher d'un POI
     */
    public function sApprocher(Request $request): JsonResponse
    {
        $poiId = $request->input('poi_id');
        $poiType = $request->input('poi_type');
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        $objetSpatial = $vaisseau->objetSpatial;
        $poiTypeNormalized = strtolower($poiType);

        // Position vaisseau en cUA
        $vaisseauPosCua = [
            'x' => CoordinatesHelper::alToCua($objetSpatial->secteur_x) + $objetSpatial->position_x,
            'y' => CoordinatesHelper::alToCua($objetSpatial->secteur_y) + $objetSpatial->position_y,
            'z' => CoordinatesHelper::alToCua($objetSpatial->secteur_z) + $objetSpatial->position_z,
        ];

        // Récupérer le POI selon son type
        $nomPoi = '';
        $distanceCua = 0;
        $ciblePosCua = ['x' => 0, 'y' => 0, 'z' => 0];

        switch ($poiTypeNormalized) {
            case 'systemestellaire':
            case 'systeme':
                $systeme = \App\Models\SystemeStellaire::find($poiId);
                if (!$systeme) {
                    return response()->json(['error' => 'Système introuvable'], 404);
                }

                $nomPoi = $systeme->nom;

                // Vérifier qu'on est dans le même secteur
                if ($systeme->secteur_x != $objetSpatial->secteur_x ||
                    $systeme->secteur_y != $objetSpatial->secteur_y ||
                    $systeme->secteur_z != $objetSpatial->secteur_z) {
                    return response()->json(['error' => 'Le système n\'est pas dans ce secteur'], 400);
                }

                // Position système en cUA (centre du système)
                $ciblePosCua = [
                    'x' => CoordinatesHelper::alToCua($systeme->secteur_x) + ($systeme->position_x ?? 0),
                    'y' => CoordinatesHelper::alToCua($systeme->secteur_y) + ($systeme->position_y ?? 0),
                    'z' => CoordinatesHelper::alToCua($systeme->secteur_z) + ($systeme->position_z ?? 0),
                ];
                break;

            case 'planete':
                $planete = \App\Models\Planete::find($poiId);
                if (!$planete || !$planete->systemeStellaire) {
                    return response()->json(['error' => 'Planète introuvable'], 404);
                }

                $systeme = $planete->systemeStellaire;
                $nomPoi = $planete->nom;

                // Vérifier qu'on est dans le même secteur que le système
                if ($systeme->secteur_x != $objetSpatial->secteur_x ||
                    $systeme->secteur_y != $objetSpatial->secteur_y ||
                    $systeme->secteur_z != $objetSpatial->secteur_z) {
                    return response()->json(['error' => 'La planète n\'est pas dans ce secteur'], 400);
                }

                // IMPORTANT: Utiliser le timestamp du jeu pour cohérence
                $timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);

                // Position absolue de la planète en cUA (avec timestamp du jeu)
                $ciblePosCua = $planete->getPositionAbsolue($timestampJours);
                break;

            case 'station':
                $station = \App\Models\Station::find($poiId);
                if (!$station || !$station->systemeStellaire) {
                    return response()->json(['error' => 'Station introuvable'], 404);
                }

                $systeme = $station->systemeStellaire;
                $nomPoi = $station->nom;

                // Vérifier qu'on est dans le même secteur
                if ($systeme->secteur_x != $objetSpatial->secteur_x ||
                    $systeme->secteur_y != $objetSpatial->secteur_y ||
                    $systeme->secteur_z != $objetSpatial->secteur_z) {
                    return response()->json(['error' => 'La station n\'est pas dans ce secteur'], 400);
                }

                // IMPORTANT: Utiliser le timestamp du jeu pour cohérence
                $timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);

                // Position station (autour d'une planète ou de l'étoile)
                if ($station->planete_id) {
                    $planete = $station->planete;
                    $ciblePosCua = $planete->getPositionAbsolue($timestampJours);
                    // TODO: ajouter offset pour orbite station
                } else {
                    // Station autour de l'étoile
                    $ciblePosCua = [
                        'x' => CoordinatesHelper::alToCua($systeme->secteur_x) + ($systeme->position_x ?? 0),
                        'y' => CoordinatesHelper::alToCua($systeme->secteur_y) + ($systeme->position_y ?? 0),
                        'z' => CoordinatesHelper::alToCua($systeme->secteur_z) + ($systeme->position_z ?? 0),
                    ];
                }
                break;

            default:
                return response()->json(['error' => 'Type de POI invalide: ' . $poiType], 400);
        }

        // Calculer distance en cUA
        $distanceCua = CoordinatesHelper::distance3D(
            $vaisseauPosCua['x'], $vaisseauPosCua['y'], $vaisseauPosCua['z'],
            $ciblePosCua['x'], $ciblePosCua['y'], $ciblePosCua['z']
        );

        // Convertir en UA pour affichage
        $distanceUa = CoordinatesHelper::cuaToUa($distanceCua);

        // Vérifier la proximité pour amarrage (< 0.01 UA = 1 cUA)
        $seuilAmarrageUa = 0.01;
        if ($distanceUa < $seuilAmarrageUa) {
            return response()->json([
                'success' => true,
                'message' => 'Vous êtes à proximité immédiate de ' . $nomPoi . '. Vous pouvez vous amarrer.',
                'distance' => round($distanceUa, 4),
                'peutAmarrer' => true,
                'distanceParcourue' => 0,
                'distanceRestante' => round($distanceUa, 4),
                'energieConsommee' => 0,
                'paConsommes' => 0,
                'energieRestante' => $vaisseau->energie_actuelle,
                'paRestants' => $personnage->points_action,
                'nouvellePosition' => [
                    'x' => CoordinatesHelper::cuaToUa($vaisseauPosCua['x']),
                    'y' => CoordinatesHelper::cuaToUa($vaisseauPosCua['y']),
                    'z' => CoordinatesHelper::cuaToUa($vaisseauPosCua['z']),
                ],
            ]);
        }

        // Calculer le coût du déplacement (énergie: 20 par UA, PA: 1 par 2 UA)
        $energieRequise = max(10, (int)($distanceUa * 20));
        $paRequis = max(1, (int)ceil($distanceUa / 2));

        // Vérifier les ressources disponibles
        $energieDisponible = $vaisseau->energie_actuelle;
        $paDisponibles = $personnage->points_action;

        // Calculer le pourcentage du trajet réalisable
        $pourcentageEnergie = $energieDisponible / $energieRequise;
        $pourcentagePA = $paDisponibles / $paRequis;
        $pourcentageTrajet = min(1.0, $pourcentageEnergie, $pourcentagePA);

        if ($pourcentageTrajet <= 0) {
            return response()->json(['error' => 'Ressources insuffisantes pour se déplacer'], 400);
        }

        // Calculer le déplacement en cUA
        $dx_cua = $ciblePosCua['x'] - $vaisseauPosCua['x'];
        $dy_cua = $ciblePosCua['y'] - $vaisseauPosCua['y'];
        $dz_cua = $ciblePosCua['z'] - $vaisseauPosCua['z'];

        // Nouvelle position en cUA
        $nouvellePosCua = [
            'x' => $vaisseauPosCua['x'] + (int)round($dx_cua * $pourcentageTrajet),
            'y' => $vaisseauPosCua['y'] + (int)round($dy_cua * $pourcentageTrajet),
            'z' => $vaisseauPosCua['z'] + (int)round($dz_cua * $pourcentageTrajet),
        ];

        // Décomposer en secteur (AL) + position intra (cUA)
        // Le secteur ne change pas pour un déplacement intra-système
        $nouveauSecteurX = $objetSpatial->secteur_x;
        $nouveauSecteurY = $objetSpatial->secteur_y;
        $nouveauSecteurZ = $objetSpatial->secteur_z;

        $nouvellePosIntraX = $nouvellePosCua['x'] - CoordinatesHelper::alToCua($nouveauSecteurX);
        $nouvellePosIntraY = $nouvellePosCua['y'] - CoordinatesHelper::alToCua($nouveauSecteurY);
        $nouvellePosIntraZ = $nouvellePosCua['z'] - CoordinatesHelper::alToCua($nouveauSecteurZ);

        // Mettre à jour la position du vaisseau
        $objetSpatial->update([
            'position_x' => $nouvellePosIntraX,
            'position_y' => $nouvellePosIntraY,
            'position_z' => $nouvellePosIntraZ,
        ]);

        // IMPORTANT: Le vaisseau sort d'orbite quand il utilise la propulsion conventionnelle
        if ($vaisseau->estEnOrbite()) {
            $vaisseau->update([
                'orbite_planete_id' => null,
                'orbite_rayon_ua' => null,
                'orbite_angle_initial' => null,
                'orbite_debut' => null,
            ]);
        }

        // IMPORTANT: Réinitialiser le scan car le vaisseau a bougé
        $vaisseau->reinitialiserScan();

        // Consommer les ressources (proportionnelles au trajet effectué)
        $energieConsommee = (int)ceil($energieRequise * $pourcentageTrajet);
        $paConsommes = (int)ceil($paRequis * $pourcentageTrajet);

        $vaisseau->energie_actuelle -= $energieConsommee;
        $vaisseau->save();

        $personnage->points_action -= $paConsommes;
        $personnage->save();

        // Calculer la distance restante (proportionnelle au trajet non effectué)
        $distanceRestante = $distanceUa * (1 - $pourcentageTrajet);

        if ($pourcentageTrajet >= 1.0) {
            return response()->json([
                'success' => true,
                'message' => "Déplacement effectué vers {$nomPoi}",
                'distanceParcourue' => round($distanceUa, 2),
                'distanceRestante' => round($distanceRestante, 4),
                'energieConsommee' => $energieConsommee,
                'paConsommes' => $paConsommes,
                'energieRestante' => $vaisseau->energie_actuelle,
                'paRestants' => $personnage->points_action,
                'nouvellePosition' => [
                    'x' => CoordinatesHelper::cuaToUa($nouvellePosCua['x']),
                    'y' => CoordinatesHelper::cuaToUa($nouvellePosCua['y']),
                    'z' => CoordinatesHelper::cuaToUa($nouvellePosCua['z']),
                ],
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Déplacement partiel effectué (ressources insuffisantes)",
                'pourcentageTrajet' => round($pourcentageTrajet * 100, 1),
                'distanceParcourue' => round($distanceUa * $pourcentageTrajet, 2),
                'distanceRestante' => round($distanceRestante, 4),
                'energieConsommee' => $energieConsommee,
                'paConsommes' => $paConsommes,
                'energieRestante' => $vaisseau->energie_actuelle,
                'paRestants' => $personnage->points_action,
                'nouvellePosition' => [
                    'x' => CoordinatesHelper::cuaToUa($nouvellePosCua['x']),
                    'y' => CoordinatesHelper::cuaToUa($nouvellePosCua['y']),
                    'z' => CoordinatesHelper::cuaToUa($nouvellePosCua['z']),
                ],
            ]);
        }
    }

    /**
     * S'amarrer à une station
     */
    public function sAmarrer(Request $request): JsonResponse
    {
        $stationId = $request->input('station_id');
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        // Chercher la vraie station (pas le système stellaire)
        $station = Station::find($stationId);
        if (!$station) {
            return response()->json(['error' => 'Station introuvable'], 404);
        }

        // LOGIQUE SIMPLE selon ARCHITECTURE_OBJETS_SPATIAUX.md:
        // Si le bouton "Amarrer" est affiché, c'est qu'on PEUT amarrer.
        // Les tests de proximité sont faits côté serveur lors de l'affichage du bouton.
        // Ici, on fait juste l'amarrage.

        $objetSpatial = $vaisseau->objetSpatial;
        if (!$objetSpatial) {
            return response()->json(['error' => 'Vaisseau sans objet spatial'], 500);
        }

        // Obtenir la position effective de la station (avec orbital si nécessaire)
        $stationObjetSpatial = $station->objetSpatial;
        if (!$stationObjetSpatial) {
            return response()->json(['error' => 'Station sans objet spatial'], 500);
        }

        $timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);
        $stationPos = $stationObjetSpatial->getPositionEffective($timestampJours);

        // === AMARRAGE ===
        // 1. Marquer l'amarrage dans la table vaisseaux
        $vaisseau->arrime_a_station_id = $stationId;

        // 2. Sortir d'orbite si nécessaire
        if ($vaisseau->estEnOrbite()) {
            $vaisseau->orbite_planete_id = null;
            $vaisseau->orbite_rayon_ua = null;
            $vaisseau->orbite_angle_initial = null;
            $vaisseau->orbite_debut = null;
        }

        $vaisseau->save();

        // 3. Définir le parent dans objets_spatiaux (HÉRITAGE DE POSITION)
        $objetSpatial->parent_type = Station::class;
        $objetSpatial->parent_id = $stationId;

        // 4. Copier la position de la station (le vaisseau hérite la position de son parent)
        $objetSpatial->secteur_x = $stationPos['secteur_x'];
        $objetSpatial->secteur_y = $stationPos['secteur_y'];
        $objetSpatial->secteur_z = $stationPos['secteur_z'];
        $objetSpatial->position_x = $stationPos['position_x'];
        $objetSpatial->position_y = $stationPos['position_y'];
        $objetSpatial->position_z = $stationPos['position_z'];

        $objetSpatial->save();

        // NOTE: Le personnage reste à bord de son vaisseau.
        // personnage->dans_station_id sera défini lors du transbordement (StationController::transborder)

        // Consommer un minimum de ressources pour l'amarrage (1 PA, 10 énergie)
        if ($personnage->points_action > 0) {
            $personnage->points_action -= 1;
            $personnage->save();
        }

        if ($vaisseau->energie_actuelle > 10) {
            $vaisseau->energie_actuelle -= 10;
            $vaisseau->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Amarrage réussi à {$station->nom}",
            'station' => [
                'id' => $station->id,
                'nom' => $station->nom,
            ],
            'energieRestante' => $vaisseau->energie_actuelle,
            'paRestants' => $personnage->points_action,
            'contextMenu' => 'station',
        ]);
    }

    /**
     * S'orbiter autour d'une planète
     */
    public function sOrbiter(Request $request): JsonResponse
    {
        $planeteId = $request->input('planete_id');
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        $planete = \App\Models\Planete::find($planeteId);
        if (!$planete || !$planete->systemeStellaire) {
            return response()->json(['error' => 'Planète introuvable'], 404);
        }

        $objetSpatial = $vaisseau->objetSpatial;
        $systeme = $planete->systemeStellaire;

        // Vérifier qu'on est dans le même secteur
        if ($systeme->secteur_x != $objetSpatial->secteur_x ||
            $systeme->secteur_y != $objetSpatial->secteur_y ||
            $systeme->secteur_z != $objetSpatial->secteur_z) {
            return response()->json(['error' => 'La planète n\'est pas dans ce secteur'], 400);
        }

        // IMPORTANT: Utiliser le timestamp du jeu pour cohérence
        $timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);

        // Calculer la distance à la planète en UA (avec timestamp du jeu)
        $distance = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);

        // Vérifier que la distance est inférieure à 0.1 UA (plus strict que pour les stations)
        if ($distance >= 0.1) {
            return response()->json([
                'error' => 'Trop éloigné pour s\'orbiter',
                'distance' => round($distance, 4),
                'distanceMaximale' => 0.1,
                'message' => 'Utilisez "S\'approcher" pour vous rapprocher d\'abord',
            ], 400);
        }

        // Mettre le vaisseau en orbite de la planète
        // Calculer la position relative du vaisseau par rapport à la planète
        $planetePosAbs = $planete->getPositionAbsolue($timestampJours); // En cUA

        $vaisseau_x_cua = CoordinatesHelper::alToCua($objetSpatial->secteur_x) + $objetSpatial->position_x;
        $vaisseau_y_cua = CoordinatesHelper::alToCua($objetSpatial->secteur_y) + $objetSpatial->position_y;
        $vaisseau_z_cua = CoordinatesHelper::alToCua($objetSpatial->secteur_z) + $objetSpatial->position_z;

        // Vecteur relatif (vaisseau - planète) en cUA
        $dx = $vaisseau_x_cua - $planetePosAbs['x'];
        $dy = $vaisseau_y_cua - $planetePosAbs['y'];
        $dz = $vaisseau_z_cua - $planetePosAbs['z'];

        // Angle initial dans le plan XY (en radians)
        $angleInitial = atan2($dy, $dx);

        // Rayon orbital en UA
        $rayonOrbitalUa = $distance; // Distance déjà calculée en UA

        // Enregistrer l'état orbital dans le vaisseau
        $vaisseau->orbite_planete_id = $planete->id;
        $vaisseau->orbite_rayon_ua = $rayonOrbitalUa;
        $vaisseau->orbite_angle_initial = $angleInitial;
        $vaisseau->orbite_debut = GameTimeHelper::joursToDate($timestampJours);
        $vaisseau->arrime_a_station_id = null; // Désarrimer si amarré
        $vaisseau->save();

        // Mettre à jour le personnage pour indiquer qu'il est en orbite d'une planète
        $personnage->dans_station_id = null;
        $personnage->save();

        // Consommer un minimum de ressources pour la mise en orbite (1 PA, 5 énergie)
        if ($personnage->points_action > 0) {
            $personnage->points_action -= 1;
            $personnage->save();
        }

        if ($vaisseau->energie_actuelle > 5) {
            $vaisseau->energie_actuelle -= 5;
            $vaisseau->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Mise en orbite réussie autour de {$planete->nom}\nOrbite stable à " . round($rayonOrbitalUa, 4) . " UA\nVotre vaisseau se déplace maintenant avec la planète",
            'planete' => [
                'id' => $planete->id,
                'nom' => $planete->nom,
                'type' => $planete->type,
            ],
            'orbite' => [
                'rayon_ua' => round($rayonOrbitalUa, 4),
                'angle_initial_rad' => round($angleInitial, 6),
                'angle_initial_deg' => round(rad2deg($angleInitial), 2),
            ],
            'distance' => round($distance, 4),
            'energieRestante' => $vaisseau->energie_actuelle,
            'paRestants' => $personnage->points_action,
        ]);
    }

    /**
     * Atterrir sur une planète (depuis l'orbite ou depuis une approche directe)
     *
     * Prérequis : vaisseau en orbite (orbite_planete_id = planeteId)
     *            OU distance < 0.1 UA de la planète
     */
    public function atterrir(Request $request): JsonResponse
    {
        $planeteId = $request->input('planete_id');
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        $planete = \App\Models\Planete::find($planeteId);
        if (!$planete || !$planete->systemeStellaire) {
            return response()->json(['error' => 'Planète introuvable'], 404);
        }

        $objetSpatial = $vaisseau->objetSpatial;
        $systeme = $planete->systemeStellaire;

        // Vérifier qu'on est dans le même secteur
        if ($systeme->secteur_x != $objetSpatial->secteur_x ||
            $systeme->secteur_y != $objetSpatial->secteur_y ||
            $systeme->secteur_z != $objetSpatial->secteur_z) {
            return response()->json(['error' => 'La planète n\'est pas dans ce secteur'], 400);
        }

        $timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);

        // Vérifier que la planète est accessible (orbite valide ou distance < 0.1 UA)
        $enOrbite = $vaisseau->orbite_planete_id == $planete->id;
        $distance = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);

        if (!$enOrbite && $distance >= 0.1) {
            return response()->json([
                'error' => 'Trop éloigné pour atterrir. Mettez-vous en orbite d\'abord.',
                'distance' => round($distance, 4),
            ], 400);
        }

        // Marquer l'atterrissage : sortir d'orbite, poser le vaisseau
        $vaisseau->orbite_planete_id   = null;
        $vaisseau->orbite_rayon_ua     = null;
        $vaisseau->orbite_angle_initial = null;
        $vaisseau->orbite_debut        = null;
        $vaisseau->arrime_a_station_id = null;
        $vaisseau->save();

        // Consommer 1 PA + 5 énergie pour l'atterrissage
        if ($personnage->points_action > 0) {
            $personnage->points_action -= 1;
            $personnage->save();
        }
        if ($vaisseau->energie_actuelle > 5) {
            $vaisseau->energie_actuelle -= 5;
            $vaisseau->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Atterrissage réussi sur {$planete->nom}\nVaisseau posé sur la surface — systèmes en veille",
            'planete' => ['id' => $planete->id, 'nom' => $planete->nom, 'type' => $planete->type],
            'energieRestante' => $vaisseau->energie_actuelle,
            'paRestants'      => $personnage->points_action,
        ]);
    }

    /**
     * Tourner le vaisseau sur lui-même (rotation azimut)
     * Ne déplace pas le vaisseau, donc ne réinitialise pas le scan
     *
     * @param Request $request {direction: 'gauche'|'droite', angle: 15}
     */
    public function tourner(Request $request): JsonResponse
    {
        $direction = $request->input('direction'); // 'gauche' ou 'droite'
        $angle = $request->input('angle', 15); // Par défaut 15°
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return response()->json(['error' => 'Aucun vaisseau actif'], 400);
        }

        $objetSpatial = $vaisseau->objetSpatial;

        // Calculer le nouvel azimut
        $azimutActuel = $objetSpatial->azimut ?? 0;
        $deltaAzimut = ($direction === 'gauche') ? -$angle : $angle;
        $nouvelAzimut = fmod($azimutActuel + $deltaAzimut + 360, 360);

        // Mettre à jour l'azimut sans déplacer le vaisseau
        $objetSpatial->azimut = round($nouvelAzimut, 2);
        $objetSpatial->save();

        // Consommer un minimum de ressources (0.1 PA, 1 énergie)
        if ($personnage->points_action >= 0.1) {
            $personnage->points_action -= 0.1;
            $personnage->save();
        }

        if ($vaisseau->energie_actuelle > 1) {
            $vaisseau->energie_actuelle -= 1;
            $vaisseau->save();
        }

        return response()->json([
            'success' => true,
            'message' => sprintf(
                'Rotation de %d° vers la %s effectuée',
                $angle,
                $direction === 'gauche' ? 'gauche' : 'droite'
            ),
            'azimutPrecedent' => round($azimutActuel, 2),
            'nouvelAzimut' => round($nouvelAzimut, 2),
            'direction' => $direction,
            'angle' => $angle,
            'energieRestante' => $vaisseau->energie_actuelle,
            'paRestants' => round($personnage->points_action, 2),
        ]);
    }

    /**
     * Récupérer les systèmes accessibles pour les sauts
     */
    protected function getSautsDisponibles($personnage, $vaisseau): array
    {
        // Seulement les systèmes dont les coordonnées sont connues (dans l'atlas)
        $decouvertes = $personnage->decouvertes()
            ->where('coordonnees_connues', true)
            ->with('systemeStellaire')
            ->get();

        $objetSpatial = $vaisseau->objetSpatial;
        $sauts = [];

        foreach ($decouvertes as $decouverte) {
            $systeme = $decouverte->systemeStellaire;
            if (!$systeme) continue;

            // Exclure le secteur actuel (pas de saut vers soi-même)
            if ($systeme->secteur_x == $objetSpatial->secteur_x
                && $systeme->secteur_y == $objetSpatial->secteur_y
                && $systeme->secteur_z == $objetSpatial->secteur_z) {
                continue;
            }

            // Calculer la distance
            $distance = $this->navigationService->calculerDistance(
                $objetSpatial,
                $systeme
            );

            // Calculer azimut et élévation du système par rapport au vaisseau
            $vaisseauAbsX = CoordinatesHelper::alToCua($objetSpatial->secteur_x) + $objetSpatial->position_x;
            $vaisseauAbsY = CoordinatesHelper::alToCua($objetSpatial->secteur_y) + $objetSpatial->position_y;
            $vaisseauAbsZ = CoordinatesHelper::alToCua($objetSpatial->secteur_z) + $objetSpatial->position_z;

            $systemeAbsX = CoordinatesHelper::alToCua($systeme->secteur_x) + ($systeme->position_x ?? 0);
            $systemeAbsY = CoordinatesHelper::alToCua($systeme->secteur_y) + ($systeme->position_y ?? 0);
            $systemeAbsZ = CoordinatesHelper::alToCua($systeme->secteur_z) + ($systeme->position_z ?? 0);

            // Vecteur vaisseau → système
            $dx = $systemeAbsX - $vaisseauAbsX;
            $dy = $systemeAbsY - $vaisseauAbsY;
            $dz = $systemeAbsZ - $vaisseauAbsZ;

            // Azimut absolu (0° = Y+, sens horaire)
            $azimutAbsolu = rad2deg(atan2($dx, $dy));
            if ($azimutAbsolu < 0) $azimutAbsolu += 360;

            // Azimut relatif (par rapport à l'orientation du vaisseau)
            $azimutRelatif = fmod($azimutAbsolu - $objetSpatial->azimut + 360, 360);

            // Élévation
            $distance3D_cua = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            $elevation = ($distance3D_cua > 0) ? rad2deg(asin($dz / $distance3D_cua)) : 0;

            // Calculer les coûts
            $energieRequise = $this->navigationService->calculerCoutEnergie($distance);
            $paRequis = $this->navigationService->calculerCoutPA($distance);

            // Vérifier l'accessibilité
            $accessible = $vaisseau->energie_actuelle >= $energieRequise
                && $personnage->points_action >= $paRequis;

            $systeme->distance = $distance;
            $systeme->azimut_relatif = round($azimutRelatif, 2);
            $systeme->azimut_absolu = round($azimutAbsolu, 2);
            $systeme->elevation = round($elevation, 2);
            $systeme->energieRequise = $energieRequise;
            $systeme->paRequis = $paRequis;
            $systeme->accessible = $accessible;
            $systeme->visite = (bool)$decouverte->visite;

            $sauts[] = $systeme;
        }

        // Trier par distance
        usort($sauts, fn($a, $b) => $a->distance <=> $b->distance);

        return $sauts;
    }

    /**
     * Récupérer les POI du secteur actuel (PLANÈTES + STATIONS)
     */
    protected function getPoISecteur($vaisseau, $personnage): array
    {
        $objetSpatial = $vaisseau->objetSpatial;

        // Trouver le système stellaire du secteur actuel
        $systeme = SystemeStellaire::where('secteur_x', $objetSpatial->secteur_x)
            ->where('secteur_y', $objetSpatial->secteur_y)
            ->where('secteur_z', $objetSpatial->secteur_z)
            ->first();

        if (!$systeme) {
            // Espace profond, pas de POI
            return [];
        }

        $pois = [];

        // Timestamp en jours depuis 3000-01-01 (pour calculs orbitaux)
        $timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);

        // 1. Récupérer les PLANÈTES du système (POI connus)
        // TODO: Implémenter système de découverte de planètes (actuellement seulement poi_connu)
        $planetes = $systeme->planetes()
            ->where('poi_connu', true)
            ->get();

        foreach ($planetes as $planete) {
            // Calculer distance vaisseau <-> planète (en UA) avec le timestamp du jeu
            $distance = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);

            // Calculer azimut et élévation de la planète par rapport au vaisseau
            $planetePos = $planete->getPositionAbsolue($timestampJours); // Position en cUA

            // IMPORTANT: Utiliser position dynamique (prend en compte orbite si le vaisseau est en orbite)
            $vaisseauPosAbs = $objetSpatial->getPositionAbsolueCua($timestampJours);
            $vaisseauAbsX = $vaisseauPosAbs['x'];
            $vaisseauAbsY = $vaisseauPosAbs['y'];
            $vaisseauAbsZ = $vaisseauPosAbs['z'];

            // Vecteur vaisseau → planète
            $dx = $planetePos['x'] - $vaisseauAbsX;
            $dy = $planetePos['y'] - $vaisseauAbsY;
            $dz = $planetePos['z'] - $vaisseauAbsZ;

            // Azimut absolu (0° = Y+, sens horaire)
            $azimutAbsolu = rad2deg(atan2($dx, $dy));
            if ($azimutAbsolu < 0) $azimutAbsolu += 360;

            // Azimut relatif (par rapport à l'orientation du vaisseau)
            $azimutRelatif = fmod($azimutAbsolu - $objetSpatial->azimut + 360, 360);

            // Élévation
            $distance3D = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            $elevation = ($distance3D > 0) ? rad2deg(asin($dz / $distance3D)) : 0;

            // Icône selon type de planète
            $icone = match($planete->type) {
                'terrestre' => '🌍',
                'gazeuse' => '🪐',
                'oceanique' => '🌊',
                'glacee' => '❄️',
                'volcanique' => '🌋',
                'desert' => '🏜️',
                'naine' => '🌑',
                default => '🪨',
            };

            // Couleur selon type (pour le radar SVG)
            $couleur = match($planete->type) {
                'terrestre' => '#8B4513',
                'gazeuse' => '#FFA500',
                'oceanique' => '#4169E1',
                'glacee' => '#87CEEB',
                'volcanique' => '#FF4500',
                'desert' => '#DEB887',
                'naine' => '#696969',
                default => '#808080',
            };

            // Créer un objet POI avec attributs pour affichage
            // Formules identiques à sApprocher() : énergie = 20/UA, PA = 1/2UA
            $poi = (object)[
                'id' => $planete->id,
                'nom' => $planete->nom,
                'type' => $planete->type,
                'categorie' => $planete->categorie ?? 'planete',
                'icone' => $icone,
                'couleur' => $couleur,
                'distance' => $distance,
                'azimut_absolu' => round($azimutAbsolu, 2),
                'azimut_relatif' => round($azimutRelatif, 2),
                'elevation' => round($elevation, 2),
                'type_poi' => 'planete',
                'energieRequise' => max(10, (int)($distance * 20)),
                'paRequis' => max(1, (int)ceil($distance / 2)),
                'donneesOrbitales' => $planete->getDonneesOrbitales($personnage),
            ];

            $pois[] = $poi;
        }

        // 2. Récupérer les STATIONS du système
        $stations = \App\Models\Station::where('systeme_stellaire_id', $systeme->id)
            ->where('accessible', true)
            ->get();

        foreach ($stations as $station) {
            // IMPORTANT: Utiliser position dynamique (prend en compte orbite si le vaisseau est en orbite)
            $vaisseauPosAbs = $objetSpatial->getPositionAbsolueCua($timestampJours);
            $vaisseauAbsX = $vaisseauPosAbs['x'];
            $vaisseauAbsY = $vaisseauPosAbs['y'];
            $vaisseauAbsZ = $vaisseauPosAbs['z'];

            // Distance station <-> vaisseau (en UA)
            if ($station->planete_id) {
                // Station en orbite d'une planète
                $planete = $station->planete;
                $distancePlanete = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);
                $distance = $distancePlanete + ($station->orbite_rayon_ua ?? 0);

                // Position de la station = position de la planète (approximation)
                $stationPos = $planete->getPositionAbsolue($timestampJours);
            } else {
                // Station autour de l'étoile - utiliser le système cUA
                $systemePosCua = [
                    'x' => CoordinatesHelper::alToCua($systeme->secteur_x) + ($systeme->position_x ?? 0),
                    'y' => CoordinatesHelper::alToCua($systeme->secteur_y) + ($systeme->position_y ?? 0),
                    'z' => CoordinatesHelper::alToCua($systeme->secteur_z) + ($systeme->position_z ?? 0),
                ];

                $distanceCua = CoordinatesHelper::distance3D(
                    $vaisseauAbsX, $vaisseauAbsY, $vaisseauAbsZ,
                    $systemePosCua['x'], $systemePosCua['y'], $systemePosCua['z']
                );

                $distance = CoordinatesHelper::cuaToUa($distanceCua);
                $stationPos = $systemePosCua;
            }

            // Vecteur vaisseau → station
            $dx = $stationPos['x'] - $vaisseauAbsX;
            $dy = $stationPos['y'] - $vaisseauAbsY;
            $dz = $stationPos['z'] - $vaisseauAbsZ;

            // Azimut absolu (0° = Y+, sens horaire)
            $azimutAbsolu = rad2deg(atan2($dx, $dy));
            if ($azimutAbsolu < 0) $azimutAbsolu += 360;

            // Azimut relatif (par rapport à l'orientation du vaisseau)
            $azimutRelatif = fmod($azimutAbsolu - $objetSpatial->azimut + 360, 360);

            // Élévation
            $distance3D = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            $elevation = ($distance3D > 0) ? rad2deg(asin($dz / $distance3D)) : 0;

            // Créer un objet POI pour affichage
            // Formules identiques à sApprocher() : énergie = 20/UA, PA = 1/2UA
            $poi = (object)[
                'id' => $station->id,
                'nom' => $station->nom,
                'icone' => '🛰️',
                'couleur' => '#C0C0C0', // Gris argenté pour les stations
                'distance' => $distance,
                'azimut_absolu' => round($azimutAbsolu, 2),
                'azimut_relatif' => round($azimutRelatif, 2),
                'elevation' => round($elevation, 2),
                'type_poi' => 'station',
                'energieRequise' => max(10, (int)($distance * 20)),
                'paRequis' => max(1, (int)ceil($distance / 2)),
                'est_amarre' => $vaisseau->arrime_a_station_id == $station->id,
            ];

            $pois[] = $poi;
        }

        // Trier par distance
        usort($pois, fn($a, $b) => $a->distance <=> $b->distance);

        return $pois;
    }

    /**
     * Stocker un calcul de saut en session
     */
    protected function storeCalculSaut(Request $request, $destination, $poiId, $poiNom, $calculData)
    {
        $jetDetails = $calculData['jetDetails'] ?? [];

        $deltaDetails = $calculData['deltaDetails'] ?? [];

        $request->session()->put('dernier_calcul_saut', [
            'destination_id' => $destination->id,
            'destination_nom' => $destination->nom,
            'poi_cible' => $poiId,
            'poi_nom' => $poiNom,
            'distance' => $calculData['distance'],
            'energie_requise' => $calculData['energieRequise'],
            'pa_requis' => $calculData['paRequis'],
            'jet_navigation' => $calculData['jetNavigation'],
            'score_erreur' => $calculData['scoreErreur'],
            'position_cible' => $calculData['positionCible'],
            'valid_until' => now()->addMinutes(30)->timestamp,
            'est_critique' => $jetDetails['estCritique'] ?? false,
            'est_espoir' => $jetDetails['estEspoir'] ?? false,
            'est_peur' => $jetDetails['estPeur'] ?? false,
            'hope_gain' => $jetDetails['hopeGain'] ?? 0,
            'fear_gain' => $jetDetails['fearGain'] ?? 0,
            'de1' => $jetDetails['de1'] ?? 0,
            'de2' => $jetDetails['de2'] ?? 0,
            'delta_d10' => $deltaDetails['d10'] ?? [0, 0, 0],
            'delta_d2' => $deltaDetails['d2'] ?? 1,
            'delta_somme_d10' => $deltaDetails['sommeD10'] ?? 0,
            'delta_d2_signe' => $deltaDetails['d2Signé'] ?? 0,
            'delta_multiplicateur' => $deltaDetails['multiplicateur'] ?? 0,
        ]);
    }

    /**
     * Récupérer un calcul de saut valide depuis la session
     */
    protected function getCalculSautValide(Request $request, $destinationId, $poiId = 'systeme')
    {
        $calcul = $request->session()->get('dernier_calcul_saut');

        // Vérifier si le calcul existe et est valide
        if (!$calcul ||
            $calcul['destination_id'] != $destinationId ||
            $calcul['poi_cible'] != $poiId ||
            $calcul['valid_until'] < now()->timestamp) {
            return null;
        }

        return $calcul;
    }

    /**
     * Calculer le jet de navigation
     */
    protected function calculerJetNavigation($personnage, $vaisseau): array
    {
        // Lancer les 2d12
        $de1 = rand(1, 12);
        $de2 = rand(1, 12);

        $savoir = $personnage->savoir ?? 0;
        $competences = is_array($personnage->competences) ? $personnage->competences : [];
        $navigation = $competences['navigation'] ?? 0;
        $ordinateur = (int)($vaisseau->system_informatique ?? 0);
        $module = 0; // Bonus modules navigation (non implémenté)

        // Calculer le jet de base
        $jetBase = $de1 + $de2 + $savoir + $navigation + $ordinateur + $module;

        // Vérifier si c'est un critique (dés égaux)
        $estCritique = $de1 === $de2;
        $estEspoir = false;
        $estPeur = false;
        $hopeGain = 0;
        $fearGain = 0;

        if ($estCritique) {
            // Critique: résultat substitué à 35
            $jetFinal = 35;
            $hopeGain = 1; // Réussite avec espoir
        } else {
            // Vérifier espoir/peur
            if ($de1 > $de2) {
                $estEspoir = true;
                $hopeGain = 1;
            } elseif ($de1 < $de2) {
                $estPeur = true;
                $fearGain = 1;
            }

            $jetFinal = $jetBase;
        }

        // Plafonner à 49
        $jetFinal = min(49, $jetFinal);

        return [
            'jet' => $jetFinal,
            'de1' => $de1,
            'de2' => $de2,
            'estCritique' => $estCritique,
            'estEspoir' => $estEspoir,
            'estPeur' => $estPeur,
            'hopeGain' => $hopeGain,
            'fearGain' => $fearGain,
            'details' => [
                'savoir' => $savoir,
                'navigation' => $navigation,
                'ordinateur' => $ordinateur,
                'module' => $module,
            ]
        ];
    }

    /**
     * Calculer la position d'arrivée avec delta
     */
    protected function calculerArriveeAvecDelta($vaisseau, $positionCible, $deltaResult, $distanceLocale): array
    {
        // Le delta est en années-lumière → s'applique aux coordonnées de secteur
        $secteurX = (int) round(($positionCible['secteur_x'] ?? 0) + $deltaResult['x']);
        $secteurY = (int) round(($positionCible['secteur_y'] ?? 0) + $deltaResult['y']);
        $secteurZ = (int) round(($positionCible['secteur_z'] ?? 0) + $deltaResult['z']);

        return [
            'secteur_x' => $secteurX,
            'secteur_y' => $secteurY,
            'secteur_z' => $secteurZ,
            'position_x' => 0.5,
            'position_y' => 0.5,
            'position_z' => 0.5,
        ];
    }

    /**
     * Annuler le calcul de saut en cours
     */
    public function annulerCalculSaut(Request $request): JsonResponse
    {
        $request->session()->forget('dernier_calcul_saut');
        return response()->json(['success' => true]);
    }

    /**
     * Améliorer le calcul de saut (coûte 1 PA)
     */
    public function ameliorerCalculSaut(Request $request): JsonResponse
    {
        $personnage = $request->attributes->get('personnage');
        $calcul = $request->session()->get('dernier_calcul_saut');

        if (!$calcul) {
            return response()->json(['error' => 'Aucun calcul en cours'], 400);
        }

        if ($personnage->points_action < 1) {
            return response()->json(['error' => '1 PA requis pour améliorer le calcul'], 400);
        }

        // Réduire le score d'erreur de 5 à 15 (aléatoire)
        $amelioration = rand(5, 15);
        $nouveauScore = max(5, $calcul['score_erreur'] - $amelioration);

        // Mettre à jour le calcul
        $calcul['score_erreur'] = $nouveauScore;
        $calcul['delta_calcule'] = $this->calculerDelta($nouveauScore, $calcul['distance']);
        $request->session()->put('dernier_calcul_saut', $calcul);

        // Consommer 1 PA
        $personnage->points_action -= 1;
        $personnage->save();

        return response()->json([
            'success' => true,
            'nouveau_score' => $nouveauScore,
            'precision' => number_format(100 - $nouveauScore * 0.5, 1),
            'message' => 'Calcul amélioré! Précision augmentée.'
        ]);
    }

    /**
     * Calculer le delta basé sur le score d'erreur
     * Formule finale: 1d2_signé × (3d10-15 + Score d'Erreur) / 100 × Distance
     * Le signe s'applique à toute l'expression, pas juste ajouté
     * Calcul séparé pour X, Y, Z avec des valeurs aléatoires différentes
     */
    protected function calculerDelta($scoreErreur, $distanceReference, $pourSystème = false)
    {
        $deltas = [];
        $allDetails = [];

        // Calculer 3 deltas séparés (X, Y, Z) avec des jets différents
        for ($i = 0; $i < 3; $i++) {
            // Lancer 3d10-15 pour chaque axe
            $d10_1 = rand(1, 10);
            $d10_2 = rand(1, 10);
            $d10_3 = rand(1, 10);
            $sommeD10 = $d10_1 + $d10_2 + $d10_3 - 15;

            // Lancer 1d2 signé pour chaque axe
            $d2 = rand(1, 2);
            $d2Signé = $d2 == 1 ? -1 : 1;

            // Calculer le multiplicateur: 1d2 × (3d10-15 + Score) / 100
            $multiplicateur = $d2Signé * ($sommeD10 + $scoreErreur) / 100;

            $deltas[$i] = $multiplicateur * $distanceReference;

            $allDetails[$i] = [
                'd10' => [$d10_1, $d10_2, $d10_3],
                'd2' => $d2,
                'sommeD10' => $sommeD10,
                'd2Signé' => $d2Signé,
                'multiplicateur' => $multiplicateur,
            ];
        }

        // Pour Z, diviser par 2 (moins précis en altitude)
        return [
            'x' => $deltas[0],
            'y' => $deltas[1],
            'z' => $deltas[2] / 2,
            'details' => [
                'x' => $allDetails[0],
                'y' => $allDetails[1],
                'z' => array_merge($allDetails[2], ['divisé_par_2' => true]),
            ]
        ];
    }

    /**
     * Étendre l'univers autour d'une destination (génération dynamique)
     */
    protected function expandUniverseAroundDestination(
        int $secteurX,
        int $secteurY,
        int $secteurZ
    ): void {
        if (!config('universe.dynamic_generation_enabled', true)) {
            return;
        }

        try {
            $radius = config('universe.dynamic_generation_radius', 3);

            Log::info("Génération dynamique déclenchée : secteur [{$secteurX}, {$secteurY}, {$secteurZ}]");

            $this->universeGenerator->expandUniverseAroundPosition(
                $secteurX,
                $secteurY,
                $secteurZ,
                $radius
            );

            Log::info("Génération dynamique terminée");

        } catch (\Exception $e) {
            Log::error("Erreur génération dynamique: " . $e->getMessage());
        }
    }
}
