<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectPersonnage
{
    /**
     * Injecte le personnage actif dans la requête de manière standardisée
     */
    public function handle(Request $request, Closure $next): Response
    {
        $personnage = null;

        if (Auth::check()) {
            $user = Auth::user();
            $personnage = $user->personnageActif;
        }

        // Méthode standard : via attributes
        $request->attributes->set('personnage', $personnage);

        return $next($request);
    }
}
