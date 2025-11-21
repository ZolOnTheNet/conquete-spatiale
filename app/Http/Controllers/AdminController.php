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
    public function univers()
    {
        $systemes = SystemeStellaire::withCount('planetes')
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.univers', compact('systemes'));
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
