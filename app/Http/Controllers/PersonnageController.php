<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnageController extends Controller
{
    /**
     * Afficher le dossier du personnage
     */
    public function dossier(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->load(['vaisseauActif.objetSpatial', 'compte']);

        // Déterminer le contexte
        $contextService = app(\App\Services\GameContextService::class);
        $context = $contextService->getMenuContext($personnage);

        // Récupérer le système actuel si en vaisseau
        $systeme = null;
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
            $systeme = \App\Models\SystemeStellaire::where('secteur_x', $personnage->vaisseauActif->objetSpatial->secteur_x)
                ->where('secteur_y', $personnage->vaisseauActif->objetSpatial->secteur_y)
                ->where('secteur_z', $personnage->vaisseauActif->objetSpatial->secteur_z)
                ->first();
        }

        return view('game.personnage.dossier', [
            'personnage' => $personnage,
            'systeme' => $systeme,
            'context' => $context,
            'isAdmin' => $request->user()->is_admin ?? false,
        ]);
    }

    /**
     * Afficher la spatiocarte (carte des systèmes découverts)
     */
    public function spatiocarte(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');

        // Redirection vers la carte principale
        return redirect()->route('carte');
    }

    /**
     * Afficher la gestion des biens du personnage
     */
    public function gestion(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->load(['vaisseaux', 'inventaires']);

        // Déterminer le contexte
        $contextService = app(\App\Services\GameContextService::class);
        $context = $contextService->getMenuContext($personnage);

        // Récupérer le système actuel si en vaisseau
        $systeme = null;
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
            $systeme = \App\Models\SystemeStellaire::where('secteur_x', $personnage->vaisseauActif->objetSpatial->secteur_x)
                ->where('secteur_y', $personnage->vaisseauActif->objetSpatial->secteur_y)
                ->where('secteur_z', $personnage->vaisseauActif->objetSpatial->secteur_z)
                ->first();
        }

        return view('game.personnage.gestion', [
            'personnage' => $personnage,
            'systeme' => $systeme,
            'context' => $context,
            'isAdmin' => $request->user()->is_admin ?? false,
        ]);
    }
}
