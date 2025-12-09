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
                ->with('planetes')
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
     * Afficher la spatiocarte (carte des systèmes découverts avec onglets Carte/Atlas)
     */
    public function spatiocarte(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $onglet = $request->get('onglet', 'carte'); // 'carte' ou 'atlas'

        // Si onglet carte, rediriger vers la vue carte complète
        if ($onglet === 'carte') {
            return redirect()->route('carte');
        }

        // Onglet Atlas : récupérer les découvertes
        $decouvertes = $personnage->decouvertes()
            ->with(['systemeStellaire.planetes.gisements.ressource'])
            ->get()
            ->map(function($decouverte) use ($personnage) {
                $systeme = $decouverte->systemeStellaire;
                if (!$systeme) return null;

                // Calculer la distance depuis la position du personnage
                $distance = 0;
                if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
                    $os = $personnage->vaisseauActif->objetSpatial;
                    $dx = ($systeme->secteur_x + $systeme->position_x) - ($os->secteur_x + $os->position_x);
                    $dy = ($systeme->secteur_y + $systeme->position_y) - ($os->secteur_y + $os->position_y);
                    $dz = ($systeme->secteur_z + $systeme->position_z) - ($os->secteur_z + $os->position_z);
                    $distance = sqrt($dx*$dx + $dy*$dy + $dz*$dz);
                }

                return [
                    'systeme' => $systeme,
                    'decouverte' => $decouverte,
                    'distance' => $distance,
                    'coords_secteur' => "({$systeme->secteur_x}, {$systeme->secteur_y}, {$systeme->secteur_z})",
                    'coords_position' => "({$systeme->position_x}, {$systeme->position_y}, {$systeme->position_z})",
                ];
            })
            ->filter()
            ->sortBy('distance');

        return view('game.spatiocarte-atlas', [
            'personnage' => $personnage,
            'decouvertes' => $decouvertes,
            'vaisseau' => $personnage->vaisseauActif,
            'compte' => $request->user(),
        ]);
    }

    /**
     * Afficher la gestion des biens du personnage
     */
    public function gestion(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->load(['vaisseauActif']);

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

        return view('game.personnage.gestion', [
            'personnage' => $personnage,
            'systeme' => $systeme,
            'context' => $context,
            'isAdmin' => $request->user()->is_admin ?? false,
        ]);
    }
}
