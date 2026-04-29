<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Faction;
use App\Models\Mine;
use Illuminate\Http\Request;

class AdminStationController extends Controller
{
    public function index(Request $request)
    {
        $query = Station::with(['systemeStellaire', 'planete', 'faction']);

        // Recherche par nom
        if ($request->filled('nom')) {
            $query->where('nom', 'LIKE', '%' . $request->nom . '%');
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par système
        if ($request->filled('systeme_id')) {
            $query->where('systeme_stellaire_id', $request->systeme_id);
        }

        // Filtre par planète
        if ($request->filled('planete_id')) {
            $query->where('planete_id', $request->planete_id);
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage === 'all') {
            $stations = $query->orderBy('nom')->get();
            $stations = new \Illuminate\Pagination\LengthAwarePaginator(
                $stations,
                $stations->count(),
                $stations->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            if (!in_array($perPage, [20, 50, 100, 200])) {
                $perPage = 20;
            }
            $stations = $query->orderBy('nom')->paginate($perPage)->withQueryString();
        }

        // Charger les systèmes et planètes pour les sélecteurs
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $planetes = collect();
        if ($request->filled('systeme_id')) {
            $planetes = Planete::where('systeme_stellaire_id', $request->systeme_id)
                ->orderBy('nom')
                ->get();
        }

        $filters = [
            'nom' => $request->nom,
            'type' => $request->type,
            'systeme_id' => $request->systeme_id,
            'planete_id' => $request->planete_id,
            'per_page' => $perPage,
        ];

        return view('admin.stations.index', compact('stations', 'systemes', 'planetes', 'filters'));
    }

    public function create()
    {
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $planetes = Planete::orderBy('nom')->get();
        $factions = Faction::orderBy('nom')->get();
        $mines = Mine::with(['planete.systemeStellaire', 'gisement.ressource'])
            ->orderBy('nom')
            ->get();

        return view('admin.stations.create', compact('systemes', 'planetes', 'factions', 'mines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string',
            'systeme_stellaire_id' => 'required|exists:systemes_stellaires,id',
            'planete_id' => 'nullable|exists:planetes,id',
            'faction_id' => 'nullable|exists:factions,id',
            'capacite_amarrage' => 'required|integer|min:1',
            'population' => 'nullable|integer|min:0',
            'accessible' => 'boolean',
            'gere_admin' => 'boolean',
            'commerciale' => 'boolean',
            'industrielle' => 'boolean',
            'militaire' => 'boolean',
            'reparations' => 'boolean',
            'ravitaillement' => 'boolean',
            'medical' => 'boolean',
            'description' => 'nullable|string',
            'mines' => 'nullable|array',
            'mines.*' => 'exists:mines,id',
        ]);

        $station = Station::create($validated);

        // Synchroniser les mines associées
        if ($request->has('mines')) {
            $station->mines()->sync($request->mines);
        }

        return redirect()->route('admin.stations.index')
            ->with('success', "Station {$station->nom} créée avec succès.");
    }

    public function edit(Station $station)
    {
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $planetes = Planete::where('systeme_stellaire_id', $station->systeme_stellaire_id)
            ->orderBy('nom')->get();
        $factions = Faction::orderBy('nom')->get();

        // Charger les mines du même système ou de la même planète
        $minesQuery = Mine::with(['planete.systemeStellaire', 'gisement.ressource']);

        if ($station->planete_id) {
            // Si la station est sur une planète, afficher les mines de cette planète
            $minesQuery->where('planete_id', $station->planete_id);
        } else {
            // Sinon, afficher les mines du même système
            $minesQuery->whereHas('planete', function($q) use ($station) {
                $q->where('systeme_stellaire_id', $station->systeme_stellaire_id);
            });
        }

        $mines = $minesQuery->orderBy('nom')->get();

        return view('admin.stations.edit', compact('station', 'systemes', 'planetes', 'factions', 'mines'));
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string',
            'systeme_stellaire_id' => 'required|exists:systemes_stellaires,id',
            'planete_id' => 'nullable|exists:planetes,id',
            'faction_id' => 'nullable|exists:factions,id',
            'capacite_amarrage' => 'required|integer|min:1',
            'population' => 'nullable|integer|min:0',
            'accessible' => 'boolean',
            'gere_admin' => 'boolean',
            'commerciale' => 'boolean',
            'industrielle' => 'boolean',
            'militaire' => 'boolean',
            'reparations' => 'boolean',
            'ravitaillement' => 'boolean',
            'medical' => 'boolean',
            'description' => 'nullable|string',
            'mines' => 'nullable|array',
            'mines.*' => 'exists:mines,id',
        ]);

        $station->update($validated);

        // Synchroniser les mines associées
        if ($request->has('mines')) {
            $station->mines()->sync($request->mines);
        } else {
            // Si aucune mine n'est cochée, on détache toutes les mines
            $station->mines()->sync([]);
        }

        return redirect()->route('admin.stations.index')
            ->with('success', "Station {$station->nom} mise à jour.");
    }

    public function destroy(Station $station)
    {
        $nom = $station->nom;
        $station->delete();

        return redirect()->route('admin.stations.index')
            ->with('success', "Station {$nom} supprimée.");
    }
}
