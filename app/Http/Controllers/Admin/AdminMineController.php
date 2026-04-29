<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mine;
use App\Models\Planete;
use App\Models\Gisement;
use App\Models\Ressource;
use App\Models\Personnage;
use Illuminate\Http\Request;

class AdminMineController extends Controller
{
    public function index(Request $request)
    {
        $query = Mine::with(['planete.systemeStellaire', 'gisement.ressource', 'proprietaire']);

        // Recherche par nom
        if ($request->filled('nom')) {
            $query->where('nom', 'LIKE', '%' . $request->nom . '%');
        }

        // Filtre par système (recherche textuelle)
        if ($request->filled('systeme')) {
            $query->whereHas('planete.systemeStellaire', function($q) use ($request) {
                $q->where('nom', 'LIKE', '%' . $request->systeme . '%');
            });
        }

        // Filtre par planète (recherche textuelle)
        if ($request->filled('planete')) {
            $query->whereHas('planete', function($q) use ($request) {
                $q->where('nom', 'LIKE', '%' . $request->planete . '%');
            });
        }

        // Filtre par ressource
        if ($request->filled('ressource_id')) {
            $query->whereHas('gisement', function($q) use ($request) {
                $q->where('ressource_id', $request->ressource_id);
            });
        }

        // Filtre par propriétaire
        if ($request->filled('proprietaire')) {
            $query->whereHas('proprietaire', function($q) use ($request) {
                $q->where('nom', 'LIKE', '%' . $request->proprietaire . '%');
            });
        }

        // Filtrage par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage === 'all') {
            $mines = $query->orderBy('nom')->get();
            $mines = new \Illuminate\Pagination\LengthAwarePaginator(
                $mines,
                $mines->count(),
                $mines->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            if (!in_array($perPage, [20, 50, 100, 200])) {
                $perPage = 20;
            }
            $mines = $query->orderBy('nom')->paginate($perPage)->withQueryString();
        }

        // Charger les données pour les sélecteurs
        $ressources = Ressource::orderBy('nom')->get();

        $filters = [
            'nom' => $request->nom,
            'systeme' => $request->systeme,
            'planete' => $request->planete,
            'ressource_id' => $request->ressource_id,
            'proprietaire' => $request->proprietaire,
            'statut' => $request->statut,
            'per_page' => $perPage,
        ];

        return view('admin.mines.index', compact('mines', 'ressources', 'filters'));
    }

    public function create()
    {
        $planetes = Planete::with('systemeStellaire')->orderBy('nom')->get();
        $ressources = Ressource::orderBy('nom')->get();
        $personnages = Personnage::orderBy('nom')->get();

        return view('admin.mines.create', compact('planetes', 'ressources', 'personnages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'planete_id' => 'required|exists:planetes,id',
            'gisement_id' => 'nullable|exists:gisements,id',
            'proprietaire_id' => 'nullable|exists:personnages,id',
            'modele' => 'nullable|string|max:100',
            'capacite_stockage' => 'required|integer|min:1',
            'taux_extraction' => 'required|numeric|min:0',
            'statut' => 'required|in:active,inactive,maintenance,endommagee',
            'emplacement' => 'required|in:surface,orbite',
        ]);

        $mine = Mine::create($validated);

        return redirect()->route('admin.mines.index')
            ->with('success', "Mine {$mine->nom} créée avec succès.");
    }

    public function edit(Mine $mine)
    {
        $planetes = Planete::with('systemeStellaire')->orderBy('nom')->get();
        $gisements = Gisement::where('planete_id', $mine->planete_id)->get();
        $personnages = Personnage::orderBy('nom')->get();

        return view('admin.mines.edit', compact('mine', 'planetes', 'gisements', 'personnages'));
    }

    public function update(Request $request, Mine $mine)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'planete_id' => 'required|exists:planetes,id',
            'gisement_id' => 'nullable|exists:gisements,id',
            'proprietaire_id' => 'nullable|exists:personnages,id',
            'modele' => 'nullable|string|max:100',
            'capacite_stockage' => 'required|integer|min:1',
            'taux_extraction' => 'required|numeric|min:0',
            'statut' => 'required|in:active,inactive,maintenance,endommagee',
            'emplacement' => 'required|in:surface,orbite',
        ]);

        $mine->update($validated);

        return redirect()->route('admin.mines.index')
            ->with('success', "Mine {$mine->nom} mise à jour.");
    }

    public function destroy(Mine $mine)
    {
        $nom = $mine->nom;
        $mine->delete();

        return redirect()->route('admin.mines.index')
            ->with('success', "Mine {$nom} supprimée.");
    }

    /**
     * Ravitailler une mine en énergie
     */
    public function ravitailler(Request $request, Mine $mine)
    {
        $request->validate([
            'quantite_energie' => 'required|integer|min:1',
        ]);

        $mine->stock_energie = ($mine->stock_energie ?? 0) + $request->quantite_energie;
        $mine->save();

        return back()->with('success', "Mine {$mine->nom} ravitaillée.");
    }

    /**
     * Effectuer la maintenance d'une mine
     */
    public function maintenance(Mine $mine)
    {
        $mine->statut = 'active';
        $mine->niveau_usure = 0;
        $mine->save();

        return back()->with('success', "Maintenance de {$mine->nom} effectuée.");
    }
}
