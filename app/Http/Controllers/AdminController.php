<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Combat;
use App\Models\Gisement;
use App\Models\Ressource;
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
     * Détails d'un système stellaire
     */
    public function showSysteme($id)
    {
        $systeme = SystemeStellaire::with(['planetes.gisements.ressource', 'planetes.stations'])
            ->findOrFail($id);

        return view('admin.univers-detail', compact('systeme'));
    }

    /**
     * Mettre à jour la puissance d'un système manuellement
     */
    public function updatePuissance(Request $request, $id)
    {
        $systeme = SystemeStellaire::findOrFail($id);

        $request->validate([
            'puissance' => 'required|integer|min:1|max:200'
        ]);

        $systeme->puissance = $request->puissance;
        $systeme->save();

        return redirect()->route('admin.univers.show', $id)
            ->with('success', "Puissance mise à jour: {$request->puissance}");
    }

    /**
     * Recalculer la puissance selon le type spectral
     */
    public function recalculerPuissance($id)
    {
        $systeme = SystemeStellaire::findOrFail($id);

        // Mapping des types spectraux vers plages de puissance
        $puissances = [
            'O' => [150, 200],
            'B' => [100, 140],
            'A' => [80, 100],
            'F' => [60, 80],
            'G' => [40, 60],
            'K' => [30, 40],
            'M' => [20, 30],
        ];

        // Extraire la classe spectrale (première lettre)
        $typeClass = strtoupper(substr($systeme->type_etoile, 0, 1));

        if (!isset($puissances[$typeClass])) {
            $typeClass = 'G'; // Défaut : type solaire
        }

        [$min, $max] = $puissances[$typeClass];

        // Formule : min - 1 + 1d(max - min + 1)
        $dice = $max - $min + 1;
        $roll = rand(1, $dice);
        $nouvellePuissance = ($min - 1) + $roll;

        $anciennePuissance = $systeme->puissance;
        $systeme->puissance = $nouvellePuissance;
        $systeme->save();

        return redirect()->route('admin.univers.show', $id)
            ->with('success', "Puissance recalculée: {$anciennePuissance} → {$nouvellePuissance} (type {$typeClass})");
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

    /**
     * Gestion des productions (gisements)
     */
    public function production(Request $request)
    {
        // Récupérer tous les systèmes pour le sélecteur
        $systemes = SystemeStellaire::orderBy('nom')->get();

        // Si un système est sélectionné, charger ses planètes et gisements
        $planetes = collect();
        $systeme_actuel = null;

        if ($request->has('system_id') && $request->system_id) {
            $systeme_actuel = SystemeStellaire::find($request->system_id);

            if ($systeme_actuel) {
                $planetes = Planete::where('systeme_stellaire_id', $systeme_actuel->id)
                    ->with(['gisements.ressource'])
                    ->orderBy('nom')
                    ->get();
            }
        }

        // Récupérer toutes les ressources pour les sélecteurs
        $ressources = Ressource::orderBy('nom')->get();

        return view('admin.production', compact('systemes', 'planetes', 'systeme_actuel', 'ressources'));
    }

    /**
     * Mise à jour d'un gisement
     */
    public function updateGisement(Request $request, $id)
    {
        try {
            $gisement = Gisement::findOrFail($id);

            $validated = $request->validate([
                'ressource_id' => 'required|exists:ressources,id',
                'richesse' => 'required|integer|min:1|max:100',
                'quantite_totale' => 'required|integer|min:0',
                'quantite_restante' => 'required|integer|min:0',
            ]);

            $gisement->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Gisement mis à jour avec succès',
                'gisement' => $gisement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
