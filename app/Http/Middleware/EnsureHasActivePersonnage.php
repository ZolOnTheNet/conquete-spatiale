<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasActivePersonnage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $compte = $request->user();

        // Verifier si la requete attend du JSON (AJAX)
        $wantsJson = $request->expectsJson() || $request->ajax();

        if (!$compte) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être connecté.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté.');
        }

        // Vérifier si le compte a un personnage principal
        if (!$compte->perso_principal) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez sélectionner ou créer un personnage.',
                    'redirect' => route('personnage.selection'),
                ], 403);
            }
            return redirect()->route('personnage.selection')
                ->with('info', 'Veuillez sélectionner ou créer un personnage.');
        }

        // Charger le personnage actif dans la session
        $personnage = $compte->personnagePrincipal;

        if (!$personnage) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Personnage principal introuvable.',
                    'redirect' => route('personnage.selection'),
                ], 404);
            }
            return redirect()->route('personnage.selection')
                ->with('error', 'Personnage principal introuvable.');
        }

        // Stocker le personnage dans la requête pour accès facile
        $request->attributes->set('personnage', $personnage);

        return $next($request);
    }
}
