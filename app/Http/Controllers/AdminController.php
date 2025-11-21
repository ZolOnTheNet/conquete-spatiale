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

        // Calculer la distance par rapport aux coordonnées saisies
        // Distance = sqrt((x2-x1)^2 + (y2-y1)^2 + (z2-z1)^2)
        $query->selectRaw('systemes_stellaires.*');
        $query->selectRaw(
            'SQRT(
                POW((secteur_x * 10 + position_x) - ?, 2) +
                POW((secteur_y * 10 + position_y) - ?, 2) +
                POW((secteur_z * 10 + position_z) - ?, 2)
            ) as distance_from_point',
            [$coordX, $coordY, $coordZ]
        );

        // Filtrer par distance max si spécifié
        if ($maxDistance > 0) {
            $query->havingRaw('distance_from_point <= ?', [$maxDistance]);
        }

        // Tri
        $validSortColumns = ['nom', 'type_etoile', 'puissance', 'detectabilite_base',
                             'planetes_count', 'distance_from_point', 'poi_connu'];
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
