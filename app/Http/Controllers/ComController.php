<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Contrôleur pour le système COM (Communications)
 * Gère l'accès aux bases de données, prix des marchés, demandes, et messages
 */
class ComController extends Controller
{
    /**
     * Afficher les bases de données accessibles
     */
    public function databases(Request $request)
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
            return view('game.com.partials.databases', $data);
        }

        return view('game.com.databases', $data);
    }

    /**
     * Afficher les prix des marchés
     */
    public function prix(Request $request)
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
            return view('game.com.partials.prix', $data);
        }

        return view('game.com.prix', $data);
    }

    /**
     * Afficher les demandes des stations
     */
    public function demandes(Request $request)
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
            return view('game.com.partials.demandes', $data);
        }

        return view('game.com.demandes', $data);
    }

    /**
     * Afficher les messages sur les sous-réseaux
     */
    public function messages(Request $request)
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
            return view('game.com.partials.messages', $data);
        }

        return view('game.com.messages', $data);
    }
}
