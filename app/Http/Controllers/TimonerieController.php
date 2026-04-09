<?php

namespace App\Http\Controllers;

use App\Models\SystemeStellaire;
use App\Services\NavigationService;
use App\Helpers\GameTimeHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class TimonerieController extends Controller
{
    protected NavigationService $navigationService;

    public function __construct(NavigationService $navigationService)
    {
        $this->navigationService = $navigationService;
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
        $jetNavigation = $this->calculerJetNavigation($personnage, $vaisseau);
        $scoreErreur = 50 - $jetNavigation;
        $deltaCalcule = $this->calculerDelta($scoreErreur, $distance);

        // Position cible (avant application du delta)
        $positionCible = [
            'x' => $destination->position_x,
            'y' => $destination->position_y,
            'z' => $destination->position_z,
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
            'deltaCalcule' => $deltaCalcule,
            'positionCible' => $positionCible,
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
        if ($vaisseau->energie_actuelle < $calcul['energieRequise']) {
            return response()->json(['error' => 'Énergie insuffisante'], 400);
        }

        if ($personnage->points_action < $calcul['paRequis']) {
            return response()->json(['error' => 'Points d\'action insuffisants'], 400);
        }

        // Calculer la position d'arrivée avec le delta
        $distanceLocale = $poiId === 'systeme' ? 0.5 : $calcul['distance'];
        $arrivee = $this->calculerArriveeAvecDelta(
            $vaisseau,
            $calcul['positionCible'],
            $calcul['deltaCalcule'],
            $distanceLocale
        );

        // Mettre à jour la position du vaisseau
        $vaisseau->objetSpatial->update([
            'secteur_x' => $arrivee['secteur_x'],
            'secteur_y' => $arrivee['secteur_y'],
            'secteur_z' => $arrivee['secteur_z'],
            'position_x' => $arrivee['position_x'],
            'position_y' => $arrivee['position_y'],
            'position_z' => $arrivee['position_z'],
        ]);

        // Consommer les ressources
        $vaisseau->energie_actuelle -= $calcul['energieRequise'];
        $vaisseau->save();

        $personnage->points_action -= $calcul['paRequis'];
        $personnage->save();

        // Invalider le calcul après utilisation
        $request->session()->forget('dernier_calcul_saut');

        return response()->json([
            'success' => true,
            'message' => "Saut effectué vers {$destination->nom}",
            'poiCible' => $calcul['poiNom'],
            'arrivee' => $arrivee,
            'jetNavigation' => $calcul['jetNavigation'],
            'scoreErreur' => $calcul['scoreErreur'],
            'precision' => number_format(100 - ($calcul['scoreErreur'] * 0.5), 1),
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

        // Récupérer le POI (pour l'instant, seulement les systèmes stellaires)
        $poi = SystemeStellaire::find($poiId);
        if (!$poi) {
            return response()->json(['error' => 'POI introuvable'], 404);
        }

        $objetSpatial = $vaisseau->objetSpatial;

        // Vérifier qu'on est dans le même secteur
        if ($poi->secteur_x != $objetSpatial->secteur_x ||
            $poi->secteur_y != $objetSpatial->secteur_y ||
            $poi->secteur_z != $objetSpatial->secteur_z) {
            return response()->json(['error' => 'Le POI n\'est pas dans ce secteur'], 400);
        }

        // Calculer la distance actuelle en UA
        $dx = $poi->position_x - $objetSpatial->position_x;
        $dy = $poi->position_y - $objetSpatial->position_y;
        $dz = $poi->position_z - $objetSpatial->position_z;
        $distanceActuelle = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        if ($distanceActuelle < 0.1) {
            return response()->json([
                'message' => 'Vous êtes déjà à proximité immédiate de ' . $poi->nom,
                'distance' => round($distanceActuelle, 2),
            ]);
        }

        // Calculer le coût du déplacement (énergie: 20 par UA, PA: 1 par 2 UA)
        $energieRequise = max(10, (int)($distanceActuelle * 20));
        $paRequis = max(1, (int)ceil($distanceActuelle / 2));

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

        // Calculer la nouvelle position
        $nouveauX = $objetSpatial->position_x + ($dx * $pourcentageTrajet);
        $nouveauY = $objetSpatial->position_y + ($dy * $pourcentageTrajet);
        $nouveauZ = $objetSpatial->position_z + ($dz * $pourcentageTrajet);

        // Mettre à jour la position du vaisseau
        $objetSpatial->update([
            'position_x' => round($nouveauX, 2),
            'position_y' => round($nouveauY, 2),
            'position_z' => round($nouveauZ, 2),
        ]);

        // Consommer les ressources (proportionnelles au trajet effectué)
        $energieConsommee = (int)ceil($energieRequise * $pourcentageTrajet);
        $paConsommes = (int)ceil($paRequis * $pourcentageTrajet);

        $vaisseau->energie_actuelle -= $energieConsommee;
        $vaisseau->save();

        $personnage->points_action -= $paConsommes;
        $personnage->save();

        // Calculer la distance restante
        $dxRestant = $poi->position_x - $nouveauX;
        $dyRestant = $poi->position_y - $nouveauY;
        $dzRestant = $poi->position_z - $nouveauZ;
        $distanceRestante = sqrt($dxRestant * $dxRestant + $dyRestant * $dyRestant + $dzRestant * $dzRestant);

        if ($pourcentageTrajet >= 1.0) {
            return response()->json([
                'success' => true,
                'message' => "Déplacement effectué vers {$poi->nom}",
                'distanceParcourue' => round($distanceActuelle, 2),
                'distanceRestante' => round($distanceRestante, 2),
                'energieConsommee' => $energieConsommee,
                'paConsommes' => $paConsommes,
                'energieRestante' => $vaisseau->energie_actuelle,
                'paRestants' => $personnage->points_action,
                'nouvellePosition' => [
                    'x' => round($nouveauX, 2),
                    'y' => round($nouveauY, 2),
                    'z' => round($nouveauZ, 2),
                ],
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Déplacement partiel effectué (ressources insuffisantes)",
                'pourcentageTrajet' => round($pourcentageTrajet * 100, 1),
                'distanceParcourue' => round($distanceActuelle * $pourcentageTrajet, 2),
                'distanceRestante' => round($distanceRestante, 2),
                'energieConsommee' => $energieConsommee,
                'paConsommes' => $paConsommes,
                'energieRestante' => $vaisseau->energie_actuelle,
                'paRestants' => $personnage->points_action,
                'nouvellePosition' => [
                    'x' => round($nouveauX, 2),
                    'y' => round($nouveauY, 2),
                    'z' => round($nouveauZ, 2),
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

        // Pour l'instant, on considère les systèmes stellaires comme points d'amarrage
        // TODO: Plus tard, implémenter des stations orbitales spécifiques
        $station = SystemeStellaire::find($stationId);
        if (!$station) {
            return response()->json(['error' => 'Station introuvable'], 404);
        }

        $objetSpatial = $vaisseau->objetSpatial;

        // Vérifier qu'on est dans le même secteur
        if ($station->secteur_x != $objetSpatial->secteur_x ||
            $station->secteur_y != $objetSpatial->secteur_y ||
            $station->secteur_z != $objetSpatial->secteur_z) {
            return response()->json(['error' => 'La station n\'est pas dans ce secteur'], 400);
        }

        // Calculer la distance en UA
        $dx = $station->position_x - $objetSpatial->position_x;
        $dy = $station->position_y - $objetSpatial->position_y;
        $dz = $station->position_z - $objetSpatial->position_z;
        $distance = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        // Vérifier que la distance est inférieure à 1 UA
        if ($distance >= 1.0) {
            return response()->json([
                'error' => 'Trop éloigné pour s\'amarrer',
                'distance' => round($distance, 2),
                'distanceMaximale' => 1.0,
                'message' => 'Utilisez "S\'approcher" pour vous rapprocher d\'abord',
            ], 400);
        }

        // Amarrer le vaisseau (pour l'instant, on utilise l'ID du système comme station)
        // TODO: Créer une table stations et utiliser l'ID de station réel
        $vaisseau->arrime_a_station_id = $stationId;
        $vaisseau->save();

        // Optionnel: mettre à jour le personnage pour qu'il soit considéré "dans la station"
        $personnage->dans_station_id = $stationId;
        $personnage->save();

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
            'distance' => round($distance, 2),
            'energieRestante' => $vaisseau->energie_actuelle,
            'paRestants' => $personnage->points_action,
            'contextMenu' => 'station',
        ]);
    }

    /**
     * Récupérer les systèmes accessibles pour les sauts
     */
    protected function getSautsDisponibles($personnage, $vaisseau): array
    {
        // Récupérer les systèmes découverts
        $decouvertes = $personnage->decouvertes()->with('systemeStellaire')->get();

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

            // Calculer les coûts
            $energieRequise = $this->navigationService->calculerCoutEnergie($distance);
            $paRequis = $this->navigationService->calculerCoutPA($distance);

            // Vérifier l'accessibilité
            $accessible = $vaisseau->energie_actuelle >= $energieRequise
                && $personnage->points_action >= $paRequis;

            $systeme->distance = $distance;
            $systeme->energieRequise = $energieRequise;
            $systeme->paRequis = $paRequis;
            $systeme->accessible = $accessible;

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

        // 1. Récupérer les PLANÈTES du système (POI connus)
        // TODO: Implémenter système de découverte de planètes (actuellement seulement poi_connu)
        $planetes = $systeme->planetes()
            ->where('poi_connu', true)
            ->get();

        foreach ($planetes as $planete) {
            // Calculer distance vaisseau <-> planète (en UA)
            $distance = $planete->getDistanceDepuisVaisseau($vaisseau);

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

            // Créer un objet POI avec attributs pour affichage
            // (évite de polluer le modèle Eloquent avec des attributs temporaires)
            $poi = (object)[
                'id' => $planete->id,
                'nom' => $planete->nom,
                'type' => $planete->type,
                'icone' => $icone,
                'distance' => $distance,
                'type_poi' => 'planete',
                'energieRequise' => max(10, (int)($distance * 2)),
                'paRequis' => max(1, (int)($distance / 10)),
                'donneesOrbitales' => $planete->getDonneesOrbitales($personnage),
            ];

            $pois[] = $poi;
        }

        // 2. Récupérer les STATIONS du système
        $stations = \App\Models\Station::where('systeme_stellaire_id', $systeme->id)
            ->where('accessible', true)
            ->get();

        foreach ($stations as $station) {
            // Distance station <-> vaisseau
            // Les stations sont en orbite des planètes, donc proche de la planète
            if ($station->planete_id) {
                $planete = $station->planete;
                $distancePlanete = $planete->getDistanceDepuisVaisseau($vaisseau);
                $distance = $distancePlanete + ($station->orbite_rayon_ua ?? 0);
            } else {
                // Station autour de l'étoile
                $dx = $systeme->position_x - $objetSpatial->position_x;
                $dy = $systeme->position_y - $objetSpatial->position_y;
                $dz = $systeme->position_z - $objetSpatial->position_z;
                $distance_al = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
                $distance = $distance_al * 63241; // AL → UA
            }

            // Créer un objet POI pour affichage (même approche que les planètes)
            $poi = (object)[
                'id' => $station->id,
                'nom' => $station->nom,
                'icone' => '🛰️',
                'distance' => $distance,
                'type_poi' => 'station',
                'energieRequise' => max(5, (int)($distance * 2)),
                'paRequis' => max(1, (int)($distance / 10)),
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
            'delta_calcule' => $calculData['deltaCalcule'],
            'position_cible' => $calculData['positionCible'],
            'valid_until' => now()->addMinutes(30)->timestamp,
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
    protected function calculerJetNavigation($personnage, $vaisseau): int
    {
        // Base: 2d12 + Intelligence + Navigation + Ordinateur + Module
        $de1 = rand(1, 12);
        $de2 = rand(1, 12);

        $intelligence = $personnage->intelligence ?? 0;
        $navigation = $personnage->competences()->where('type', 'navigation')->first()->niveau ?? 0;
        $ordinateur = $vaisseau->ordinateur->bonus_navigation ?? 0;
        $module = $vaisseau->modules()->where('type', 'navigation')->sum('bonus');

        $jet = $de1 + $de2 + $intelligence + $navigation + $ordinateur + $module;

        // Vérifier si c'est un critique (dés égaux)
        $estCritique = $de1 === $de2;
        $bonusCritique = $estCritique ? 35 : 0;

        return $jet + $bonusCritique;
    }

    /**
     * Calculer le delta basé sur le score d'erreur
     */
    protected function calculerDelta($scoreErreur, $distance): float
    {
        // Formule: D(2 x ScoreErreur)/ScoreErreur-1
        // Où D(n) est un nombre aléatoire entre -n et +n
        if ($scoreErreur <= 1) {
            return 0; // Éviter la division par zéro
        }
        
        $d = rand(-$scoreErreur * 2, $scoreErreur * 2);
        return $d / ($scoreErreur - 1);
    }

    /**
     * Calculer la position d'arrivée avec delta
     */
    protected function calculerArriveeAvecDelta($vaisseau, $positionCible, $delta, $distanceLocale): array
    {
        // Appliquer le delta aux coordonnées
        $arrivee = [
            'secteur_x' => $positionCible['x'],
            'secteur_y' => $positionCible['y'],
            'secteur_z' => $positionCible['z'],
            'position_x' => $positionCible['x'] + ($delta * $distanceLocale),
            'position_y' => $positionCible['y'] + ($delta * $distanceLocale),
            'position_z' => $positionCible['z'] + ($delta * $distanceLocale / 2), // Moins précis en Z
        ];

        // S'assurer que les valeurs restent dans des limites raisonnables
        $arrivee['position_x'] = max(-100, min(100, $arrivee['position_x']));
        $arrivee['position_y'] = max(-100, min(100, $arrivee['position_y']));
        $arrivee['position_z'] = max(-100, min(100, $arrivee['position_z']));

        return $arrivee;
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
}
