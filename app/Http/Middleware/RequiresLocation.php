<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PersonnageLocation;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour restreindre l'accès à certaines fonctionnalités selon la localisation
 */
class RequiresLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $requiredLocation  Type de localisation requis (vaisseau, station, etc.)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $requiredLocation): Response
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response('Personnage introuvable', 404);
        }

        $location = new PersonnageLocation($personnage);

        // Vérifier si le personnage est dans le bon type de localisation
        $isAllowed = match ($requiredLocation) {
            'vaisseau' => $location->estDansVaisseau(),
            'station' => $location->estDansStation(),
            'marche-physique' => $location->peutAccederMarchePhysique(),
            'combat' => $location->peutAccederCombat(),
            default => false,
        };

        if (!$isAllowed) {
            $errorMessages = [
                'vaisseau' => 'Vous devez être dans un vaisseau pour accéder à cette fonctionnalité.',
                'station' => 'Vous devez être dans une station pour accéder à cette fonctionnalité.',
                'marche-physique' => 'Vous devez être dans une station pour accéder au marché physique.',
                'combat' => 'Vous devez être dans une station pour accéder aux fonctionnalités de combat.',
            ];

            $message = $errorMessages[$requiredLocation] ?? 'Accès refusé depuis votre localisation actuelle.';

            // Si requête AJAX, retourner une réponse JSON
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'error' => $message,
                    'required_location' => $requiredLocation,
                    'current_location' => $location->getType(),
                ], 403);
            }

            // Sinon, retourner une page d'erreur
            return response()->view('errors.location-restricted', [
                'message' => $message,
                'requiredLocation' => $requiredLocation,
                'currentLocation' => $location->getType(),
            ], 403);
        }

        return $next($request);
    }
}
