<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class JeuController extends Controller
{
    /**
     * Afficher le profil du joueur
     */
    public function profil(Request $request): View
    {
        $compte = $request->user();
        $personnage = $request->attributes->get('personnage');

        $compte->load('personnages');
        $personnage->load(['vaisseauActif.objetSpatial', 'decouvertes']);

        // Déterminer le contexte
        $contextService = app(\App\Services\GameContextService::class);
        $context = $contextService->getMenuContext($personnage);

        // Récupérer le système actuel si en vaisseau
        $systeme = null;
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
            $systeme = \App\Models\SystemeStellaire::where('secteur_x', $personnage->vaisseauActif->objetSpatial->secteur_x)
                ->where('secteur_y', $personnage->vaisseauActif->objetSpatial->secteur_y)
                ->where('secteur_z', $personnage->vaisseauActif->objetSpatial->secteur_z)
                ->with('planetes')
                ->first();
        }

        return view('game.jeu.profil', [
            'compte' => $compte,
            'personnage' => $personnage,
            'systeme' => $systeme,
            'context' => $context,
            'isAdmin' => $compte->is_admin ?? false,
        ]);
    }
}
