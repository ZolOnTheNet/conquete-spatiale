<?php

namespace App\Services;

use App\Models\Personnage;

class GameContextService
{
    /**
     * Détermine le contexte du menu (navire ou station)
     *
     * @param Personnage $personnage
     * @return string 'navire' ou 'station'
     */
    public function getMenuContext(Personnage $personnage): string
    {
        // Si le personnage est amarré à une station
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->arrime_a_station_id) {
            return 'station';
        }

        // Si le personnage est dans une station (sans vaisseau)
        if ($personnage->dans_station_id) {
            return 'station';
        }

        // Par défaut, mode navire
        return 'navire';
    }

    /**
     * Vérifie si le personnage est dans un vaisseau
     *
     * @param Personnage $personnage
     * @return bool
     */
    public function isInShip(Personnage $personnage): bool
    {
        return $personnage->vaisseauActif !== null
            && $personnage->vaisseauActif->arrime_a_station_id === null;
    }

    /**
     * Vérifie si le personnage est dans une station
     *
     * @param Personnage $personnage
     * @return bool
     */
    public function isInStation(Personnage $personnage): bool
    {
        return $personnage->dans_station_id !== null
            || ($personnage->vaisseauActif && $personnage->vaisseauActif->arrime_a_station_id !== null);
    }

    /**
     * Récupère la station actuelle du personnage (si applicable)
     *
     * @param Personnage $personnage
     * @return \App\Models\Station|null
     */
    public function getCurrentStation(Personnage $personnage)
    {
        // Station via vaisseau amarré
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->arrime_a_station_id) {
            return $personnage->vaisseauActif->stationArrimee;
        }

        // Station directe
        if ($personnage->dans_station_id) {
            return $personnage->stationActuelle;
        }

        return null;
    }
}
