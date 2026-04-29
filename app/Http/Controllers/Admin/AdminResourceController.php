<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ressource;
use App\Models\Gisement;
use App\Models\Planete;
use Illuminate\Http\Request;

class AdminResourceController extends Controller
{
    /**
     * Liste des ressources
     */
    public function index(Request $request)
    {
        $query = Ressource::withCount('gisements');

        // Recherche par nom
        if ($request->filled('nom')) {
            $query->where('nom', 'LIKE', '%' . $request->nom . '%');
        }

        // Recherche par code
        if ($request->filled('code')) {
            $query->where('code', 'LIKE', '%' . $request->code . '%');
        }

        // Filtre par catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage === 'all') {
            $ressources = $query->orderBy('nom')->get();
            $ressources = new \Illuminate\Pagination\LengthAwarePaginator(
                $ressources,
                $ressources->count(),
                $ressources->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            if (!in_array($perPage, [20, 50, 100, 200])) {
                $perPage = 20;
            }
            $ressources = $query->orderBy('nom')->paginate($perPage)->withQueryString();
        }

        $filters = [
            'nom' => $request->nom,
            'code' => $request->code,
            'categorie' => $request->categorie,
            'per_page' => $perPage,
        ];

        return view('admin.ressources.index', compact('ressources', 'filters'));
    }

    /**
     * Créer une ressource
     */
    public function create()
    {
        return view('admin.ressources.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:ressources',
            'code' => 'required|string|max:10|unique:ressources',
            'categorie' => 'required|in:metaux,gaz,elementaire,chimie,exotique',
            'rarete' => 'required|integer|min:1|max:100',
            'prix_base' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $ressource = Ressource::create($validated);

        return redirect()->route('admin.ressources.index')
            ->with('success', "Ressource {$ressource->nom} créée.");
    }

    public function edit(Ressource $ressource)
    {
        return view('admin.ressources.edit', compact('ressource'));
    }

    public function update(Request $request, Ressource $ressource)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:ressources,nom,' . $ressource->id,
            'code' => 'required|string|max:10|unique:ressources,code,' . $ressource->id,
            'categorie' => 'required|in:metaux,gaz,elementaire,chimie,exotique',
            'rarete' => 'required|integer|min:1|max:100',
            'prix_base' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $ressource->update($validated);

        return redirect()->route('admin.ressources.index')
            ->with('success', "Ressource {$ressource->nom} mise à jour.");
    }

    /**
     * Liste des gisements
     */
    public function gisements(Request $request)
    {
        $query = Gisement::with(['planete.systemeStellaire', 'ressource']);

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
            $query->where('ressource_id', $request->ressource_id);
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage === 'all') {
            $gisements = $query->orderBy('quantite_restante', 'desc')->get();
            $gisements = new \Illuminate\Pagination\LengthAwarePaginator(
                $gisements,
                $gisements->count(),
                $gisements->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            if (!in_array($perPage, [20, 50, 100, 200])) {
                $perPage = 20;
            }
            $gisements = $query->orderBy('quantite_restante', 'desc')->paginate($perPage)->withQueryString();
        }

        $ressources = Ressource::orderBy('nom')->get();

        $filters = [
            'systeme' => $request->systeme,
            'planete' => $request->planete,
            'ressource_id' => $request->ressource_id,
            'per_page' => $perPage,
        ];

        return view('admin.gisements.index', compact('gisements', 'ressources', 'filters'));
    }

    /**
     * Créer un gisement
     */
    public function createGisement()
    {
        $planetes = Planete::with('systemeStellaire')->orderBy('nom')->get();
        $ressources = Ressource::orderBy('nom')->get();

        return view('admin.gisements.create', compact('planetes', 'ressources'));
    }

    public function storeGisement(Request $request)
    {
        $validated = $request->validate([
            'planete_id' => 'required|exists:planetes,id',
            'ressource_id' => 'required|exists:ressources,id',
            'quantite_totale' => 'required|integer|min:1',
            'richesse' => 'required|integer|min:1|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $validated['quantite_restante'] = $validated['quantite_totale'];

        $gisement = Gisement::create($validated);

        return redirect()->route('admin.gisements.index')
            ->with('success', "Gisement créé sur {$gisement->planete->nom}.");
    }
}
