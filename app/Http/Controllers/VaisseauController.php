<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Contrôleur pour les sections du vaisseau
 * Gère toutes les fonctionnalités accessibles depuis le vaisseau
 */
class VaisseauController extends Controller
{
    /**
     * Afficher la position actuelle du vaisseau
     */
    public function position(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response('Aucun vaisseau actif', 404);
        }

        $vaisseau = $personnage->vaisseauActif;
        $objetSpatial = $vaisseau->objetSpatial;

        $data = [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'objetSpatial' => $objetSpatial,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.position', $data);
        }

        return view('game.vaisseau.position', $data);
    }

    /**
     * Afficher le scanner du vaisseau
     */
    public function scanner(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response('Aucun vaisseau actif', 404);
        }

        $vaisseau = $personnage->vaisseauActif;
        $objetSpatial = $vaisseau->objetSpatial;

        $data = [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'objetSpatial' => $objetSpatial,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.scanner', $data);
        }

        return view('game.vaisseau.scanner', $data);
    }

    /**
     * Afficher l'état du vaisseau (Ingénierie)
     */
    public function etat(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response('Aucun vaisseau actif', 404);
        }

        $vaisseau = $personnage->vaisseauActif;

        $data = [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.etat', $data);
        }

        return view('game.vaisseau.etat', $data);
    }

    /**
     * Afficher les réparations du vaisseau (Ingénierie)
     */
    public function reparations(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response('Aucun vaisseau actif', 404);
        }

        $vaisseau = $personnage->vaisseauActif;

        $data = [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.reparations', $data);
        }

        return view('game.vaisseau.reparations', $data);
    }

    /**
     * Afficher l'inventaire (Soute)
     */
    public function inventaire(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response('Personnage introuvable', 404);
        }

        $data = [
            'personnage' => $personnage,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.inventaire', $data);
        }

        return view('game.vaisseau.inventaire', $data);
    }

    /**
     * Afficher la cargaison du vaisseau (Soute)
     */
    public function cargaison(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response('Aucun vaisseau actif', 404);
        }

        $vaisseau = $personnage->vaisseauActif;

        $data = [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.cargaison', $data);
        }

        return view('game.vaisseau.cargaison', $data);
    }

    /**
     * Afficher les armes embarquées (Armement)
     */
    public function armes(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response('Aucun vaisseau actif', 404);
        }

        $vaisseau = $personnage->vaisseauActif;

        $data = [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
        ];

        // Si requête AJAX, retourner seulement le contenu
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.vaisseau.partials.armes', $data);
        }

        return view('game.vaisseau.armes', $data);
    }
}
