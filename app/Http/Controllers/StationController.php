<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Station;
use App\Models\Vaisseau;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des stations spatiales
 *
 * Gère :
 * - Transbordement (vaisseau → station)
 * - Embarquement (station → vaisseau)
 * - Menu principal et services de la station
 *
 * @see docs/game-design/SYSTEME_STATIONS.md
 */
class StationController extends Controller
{
    /**
     * Préparer les données communes pour toutes les vues de station
     */
    protected function getStationData(Request $request): array
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->load(['vaisseauActif.objetSpatial']);

        $contextService = app(\App\Services\GameContextService::class);
        $station = $contextService->getCurrentStation($personnage);

        // Récupérer le système stellaire si disponible
        $systeme = null;
        if ($station) {
            $station->load('planetes'); // Charger les planètes
            $systeme = $station; // La station est pour l'instant un SystemeStellaire
        }

        return [
            'personnage' => $personnage,
            'station' => $station,
            'systeme' => $systeme,
            'isAdmin' => $request->user()->is_admin ?? false,
        ];
    }

    /**
     * Hall principal de la station
     */
    public function hall(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau   = $personnage?->vaisseauActif;

        // Auto-transborder : vaisseau amarré mais personnage pas encore entré dans la station
        if ($vaisseau && $vaisseau->arrime_a_station_id && !$personnage->dans_station_id) {
            $personnage->dans_station_id = $vaisseau->arrime_a_station_id;
            $personnage->save();
        }

        return view('game.station.hall', $this->getStationData($request));
    }

    /**
     * Hangar de la station
     */
    public function hangar(Request $request): View
    {
        return view('game.station.hangar', $this->getStationData($request));
    }

    /**
     * Marché de la station
     */
    public function marche(Request $request): View
    {
        return view('game.station.marche', $this->getStationData($request));
    }

    /**
     * Bureau des missions
     */
    public function missions(Request $request): View
    {
        return view('game.station.missions', $this->getStationData($request));
    }

    /**
     * Cantina de la station
     */
    public function cantina(Request $request): View
    {
        return view('game.station.cantina', $this->getStationData($request));
    }

    /**
     * Afficher le menu principal de la station
     *
     * Le personnage doit être dans une station (dans_station_id renseigné)
     */
    public function menu(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Aucun personnage actif sélectionné.');
        }

        // Vérifier que le personnage est bien dans une station
        if (!$personnage->dans_station_id) {
            return redirect()->route('navire.timonerie')
                ->with('error', 'Vous devez d\'abord transborder dans une station.');
        }

        $station = $personnage->dansStation;

        if (!$station) {
            // Incohérence données : personnage a dans_station_id mais station n'existe pas
            $personnage->dans_station_id = null;
            $personnage->save();

            return redirect()->route('navire.timonerie')
                ->with('error', 'Erreur : station introuvable.');
        }

        // Vérifier l'accessibilité (au cas où réputation a changé)
        if (!$station->estAccessiblePour($personnage)) {
            return redirect()->route('navire.timonerie')
                ->with('error', "Accès refusé à {$station->nom} : {$station->raison_inaccessible}");
        }

        return view('game.station.menu', compact('personnage', 'station'));
    }

    /**
     * Transborder : Quitter le vaisseau et entrer dans la station
     *
     * Conditions :
     * - Le personnage doit être à bord d'un vaisseau (dans_station_id = NULL)
     * - Le vaisseau doit être arrimé à une station (arrime_a_station_id renseigné)
     * - La station doit être accessible (réputation, etc.)
     */
    public function transborder(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Aucun personnage actif sélectionné.');
        }

        // Vérifier que le personnage est à bord d'un vaisseau (pas déjà dans une station)
        if ($personnage->dans_station_id) {
            return redirect()->route('station.menu')
                ->with('error', 'Vous êtes déjà dans une station.');
        }

        // Vérifier que le personnage a un vaisseau actif
        if (!$personnage->vaisseau_actif_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucun vaisseau actif.');
        }

        $vaisseau = $personnage->vaisseauActif;

        // Vérifier que le vaisseau est arrimé
        if (!$vaisseau->arrime_a_station_id) {
            return redirect()->route('navire.timonerie')
                ->with('error', 'Le vaisseau doit être arrimé à une station pour transborder.');
        }

        $station = Station::find($vaisseau->arrime_a_station_id);

        if (!$station) {
            // Incohérence : vaisseau arrimé mais station n'existe pas
            $vaisseau->arrime_a_station_id = null;
            $vaisseau->arrime_le = null;
            $vaisseau->save();

            return redirect()->route('navire.timonerie')
                ->with('error', 'Erreur : station introuvable.');
        }

        // Vérifier l'accessibilité de la station
        if (!$station->estAccessiblePour($personnage)) {
            return redirect()->route('navire.timonerie')
                ->with('error', "Accès refusé à {$station->nom} : {$station->raison_inaccessible}");
        }

        // Transbordement réussi
        $personnage->dans_station_id = $station->id;
        $personnage->save();

        return redirect()->route('station.menu')
            ->with('success', "Vous avez transbordé dans {$station->nom}. Bienvenue à bord !");
    }

    /**
     * Embarquer : Quitter la station et retourner au vaisseau
     *
     * Conditions :
     * - Le personnage doit être dans une station (dans_station_id renseigné)
     * - Le vaisseau du personnage doit toujours être arrimé
     */
    public function embarquer(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Aucun personnage actif sélectionné.');
        }

        // Vérifier que le personnage est dans une station
        if (!$personnage->dans_station_id) {
            return redirect()->route('navire.timonerie')
                ->with('error', 'Vous êtes déjà à bord de votre vaisseau.');
        }

        $station = $personnage->dansStation;

        // Vérifier que le personnage a un vaisseau actif
        if (!$personnage->vaisseau_actif_id) {
            return redirect()->route('station.menu')
                ->with('error', 'Aucun vaisseau actif. Vous devez en acquérir un pour quitter la station.');
        }

        $vaisseau = $personnage->vaisseauActif;

        // Vérifier que le vaisseau est toujours arrimé
        if (!$vaisseau->arrime_a_station_id || $vaisseau->arrime_a_station_id != $personnage->dans_station_id) {
            return redirect()->route('station.menu')
                ->with('error', 'Votre vaisseau n\'est plus arrimé à cette station.');
        }

        // Embarquement réussi
        $personnage->dans_station_id = null;
        $personnage->save();

        return redirect()->route('navire.timonerie')
            ->with('success', "Vous êtes de retour à bord de votre vaisseau. Vous pouvez désamarrer quand vous le souhaitez.");
    }

    /**
     * Afficher les informations d'une station (sans y être)
     *
     * Utile pour la carte ou la timonerie
     */
    public function show(Request $request, Station $station)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Aucun personnage actif sélectionné.');
        }

        // Vérifier si la station est connue (découverte)
        if (!$station->poi_connu) {
            return redirect()->back()
                ->with('error', 'Cette station n\'a pas encore été découverte.');
        }

        return view('game.station.info', compact('personnage', 'station'));
    }

    /**
     * Hôpital de la station (en construction)
     */
    public function hopital(Request $request): View
    {
        $data = $this->getStationData($request);

        return view('game.station.en-construction', array_merge($data, [
            'titre' => '🏥 Hôpital',
            'description' => 'Soins médicaux, traitements des blessures et maladies, amélioration des capacités de l\'équipage.',
        ]));
    }

    /**
     * Zone industrielle de la station (en construction)
     */
    public function industrie(Request $request): View
    {
        $data = $this->getStationData($request);

        return view('game.station.en-construction', array_merge($data, [
            'titre' => '🏭 Zone Industrielle',
            'description' => 'Raffinage de minerais, fabrication de composants, amélioration d\'équipements.',
        ]));
    }
}
