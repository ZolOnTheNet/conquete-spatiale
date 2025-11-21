<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Combat;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Dashboard admin
     */
    public function index()
    {
        $stats = [
            'comptes' => Compte::count(),
            'personnages' => Personnage::count(),
            'systemes' => SystemeStellaire::count(),
            'planetes' => Planete::count(),
            'combats_actifs' => Combat::where('statut', 'en_cours')->count(),
        ];

        return view('admin.index', compact('stats'));
    }

    /**
     * Gestion des comptes
     */
    public function comptes()
    {
        $comptes = Compte::with('personnages')->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.comptes', compact('comptes'));
    }

    /**
     * Gestion de l'univers
     */
    public function univers(Request $request)
    {
        // Récupérer les paramètres de filtre
        $coordX = $request->input('coord_x', 0);
        $coordY = $request->input('coord_y', 0);
        $coordZ = $request->input('coord_z', 0);
        $maxDistance = $request->input('max_distance', 0); // 0 = illimité
        $perPage = $request->input('per_page', 25);
        $sortBy = $request->input('sort_by', 'nom');
        $sortDirection = $request->input('sort_direction', 'asc');

        // Valider per_page
        if (!in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 25;
        }

        // Construire la requête
        $query = SystemeStellaire::withCount('planetes');

        // Calculer la distance au carré par rapport aux coordonnées saisies
        // Distance² = (x2-x1)² + (y2-y1)² + (z2-z1)²
        // Note: On calcule le carré en SQL (compatible SQLite) et la racine en PHP
        $query->selectRaw('systemes_stellaires.*');
        $query->selectRaw(
            '(
                ((secteur_x * 10 + position_x) - ?) * ((secteur_x * 10 + position_x) - ?) +
                ((secteur_y * 10 + position_y) - ?) * ((secteur_y * 10 + position_y) - ?) +
                ((secteur_z * 10 + position_z) - ?) * ((secteur_z * 10 + position_z) - ?)
            ) as distance_squared',
            [$coordX, $coordX, $coordY, $coordY, $coordZ, $coordZ]
        );

        // Filtrer par distance max si spécifié (utiliser whereRaw au lieu de havingRaw pour compatibilité SQLite avec pagination)
        if ($maxDistance > 0) {
            $maxDistanceSquared = $maxDistance * $maxDistance;
            $query->whereRaw(
                '(
                    ((secteur_x * 10 + position_x) - ?) * ((secteur_x * 10 + position_x) - ?) +
                    ((secteur_y * 10 + position_y) - ?) * ((secteur_y * 10 + position_y) - ?) +
                    ((secteur_z * 10 + position_z) - ?) * ((secteur_z * 10 + position_z) - ?)
                ) <= ?',
                [$coordX, $coordX, $coordY, $coordY, $coordZ, $coordZ, $maxDistanceSquared]
            );
        }

        // Tri
        $validSortColumns = ['nom', 'type_etoile', 'puissance', 'detectabilite_base',
                             'planetes_count', 'distance_squared', 'poi_connu'];
        if (in_array($sortBy, $validSortColumns)) {
            if ($sortBy === 'planetes_count') {
                // Tri spécial pour le count
                $query->orderBy('planetes_count', $sortDirection);
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }
        }

        $systemes = $query->paginate($perPage)
            ->appends([
                'coord_x' => $coordX,
                'coord_y' => $coordY,
                'coord_z' => $coordZ,
                'max_distance' => $maxDistance,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ]);

        return view('admin.univers', compact('systemes', 'coordX', 'coordY', 'coordZ',
                                              'maxDistance', 'perPage', 'sortBy', 'sortDirection'));
    }

    /**
     * Gestion des planètes
     */
    public function planetes()
    {
        $planetes = Planete::with('systemeStellaire')
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.planetes', compact('planetes'));
    }

    /**
     * Gestion des backups
     */
    public function backup()
    {
        return view('admin.backup');
    }
}
