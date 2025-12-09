<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

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
}
