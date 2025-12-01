<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Combat;
use App\Models\Gisement;
use App\Models\Ressource;
use App\Models\Mine;
use App\Services\UniverseGeneratorService;
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
        // Note: secteur_x/y/z SONT déjà les coordonnées AL entières (pas de *10 !)
        // Coordonnées complètes = secteur + position
        $query->selectRaw('systemes_stellaires.*');
        $query->selectRaw(
            '(
                ((secteur_x + position_x) - ?) * ((secteur_x + position_x) - ?) +
                ((secteur_y + position_y) - ?) * ((secteur_y + position_y) - ?) +
                ((secteur_z + position_z) - ?) * ((secteur_z + position_z) - ?)
            ) as distance_squared',
            [$coordX, $coordX, $coordY, $coordY, $coordZ, $coordZ]
        );

        // Filtrer par distance max si spécifié
        if ($maxDistance > 0) {
            $maxDistanceSquared = $maxDistance * $maxDistance;
            $query->whereRaw(
                '(
                    ((secteur_x + position_x) - ?) * ((secteur_x + position_x) - ?) +
                    ((secteur_y + position_y) - ?) * ((secteur_y + position_y) - ?) +
                    ((secteur_z + position_z) - ?) * ((secteur_z + position_z) - ?)
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

        // Recalculer la détectabilité : (200 - puissance) / 3
        $systeme->detectabilite_base = round((200 - $request->puissance) / 3, 2);

        $systeme->save();

        return redirect()->route('admin.univers.show', $id)
            ->with('success', "Puissance mise à jour: {$request->puissance} | Détectabilité: {$systeme->detectabilite_base}");
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

        // Recalculer la détectabilité : (200 - puissance) / 3
        $systeme->detectabilite_base = round((200 - $nouvellePuissance) / 3, 2);

        $systeme->save();

        return redirect()->route('admin.univers.show', $id)
            ->with('success', "Puissance recalculée: {$anciennePuissance} → {$nouvellePuissance} (type {$typeClass}) | Détectabilité: {$systeme->detectabilite_base}");
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
     * Afficher le détail d'une planète (avec possibilité d'édition)
     */
    public function showPlanete($id)
    {
        $planete = Planete::with([
            'systemeStellaire',
            'gisements.ressource',
            'gisements.mines',
            'stations',
            'mines.gisement.ressource'
        ])->findOrFail($id);

        // Charger toutes les ressources pour le sélecteur de nouveau gisement
        $ressources = Ressource::orderBy('nom')->get();

        return view('admin.planete-detail', compact('planete', 'ressources'));
    }

    /**
     * Mettre à jour une planète
     */
    public function updatePlanete(Request $request, $id)
    {
        $planete = Planete::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string',
            'rayon' => 'nullable|numeric',
            'masse' => 'nullable|numeric',
            'gravite' => 'nullable|numeric',
            'distance_etoile' => 'nullable|numeric',
            'periode_orbitale' => 'nullable|integer',
            'temperature_moyenne' => 'nullable|integer',
            'a_atmosphere' => 'boolean',
            'composition_atmosphere' => 'nullable|string',
            'habitable' => 'boolean',
            'habitee' => 'boolean',
            'population' => 'nullable|integer',
            'accessible' => 'boolean',
        ]);

        $planete->update($validated);

        return redirect()->route('admin.planetes.show', $id)
            ->with('success', 'Planète mise à jour avec succès');
    }

    /**
     * Carte de l'univers - Niveau 1 (vue des secteurs)
     */
    public function carte(Request $request)
    {
        // Coordonnées centrales (par défaut 0,0,0)
        $centerX = $request->input('x', 0);
        $centerY = $request->input('y', 0);
        $centerZ = $request->input('z', 0);

        // Plan d'affichage (par défaut Z - affiche plan XY)
        $plan = $request->input('plan', 'Z');

        // Taille de la carte (100 AL × 100 AL)
        $size = 100;
        $halfSize = 50;

        // Charger tous les systèmes stellaires
        // Les coordonnées AL entières sont directement secteur_x/y/z
        // (position_x/y/z sont des décimales entre 0 et 1 pour la position précise dans le secteur)
        $systemes = SystemeStellaire::all()->map(function($systeme) {
            $systeme->abs_x = $systeme->secteur_x;
            $systeme->abs_y = $systeme->secteur_y;
            $systeme->abs_z = $systeme->secteur_z;
            return $systeme;
        });

        // Créer un index par coordonnées de secteur pour accès rapide
        $grille = [];
        foreach ($systemes as $systeme) {
            $secteurX = $systeme->secteur_x;
            $secteurY = $systeme->secteur_y;
            $secteurZ = $systeme->secteur_z;

            if (!isset($grille[$secteurX])) {
                $grille[$secteurX] = [];
            }
            if (!isset($grille[$secteurX][$secteurY])) {
                $grille[$secteurX][$secteurY] = [];
            }
            $grille[$secteurX][$secteurY][$secteurZ] = $systeme;
        }

        // Récupérer la position actuelle du personnage principal du compte admin (si existe)
        $positionActuelle = null;
        $compte = $request->user();
        if ($compte && $compte->personnagePrincipal) {
            $personnage = $compte->personnagePrincipal;
            if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
                $objet = $personnage->vaisseauActif->objetSpatial;
                // Coordonnées AL entières = directement secteur_x/y/z
                $positionActuelle = [
                    'x' => intval($objet->secteur_x),
                    'y' => intval($objet->secteur_y),
                    'z' => intval($objet->secteur_z),
                ];
            }
        }

        return view('admin.carte', compact('centerX', 'centerY', 'centerZ', 'plan', 'size', 'grille', 'positionActuelle'));
    }

    /**
     * Carte de l'univers - Niveau 2 (vue intra-secteur)
     */
    public function carteSecteur($x, $y, $z)
    {
        // Récupérer tous les systèmes dans ce secteur avec toutes les relations
        $systemes = SystemeStellaire::where('secteur_x', $x)
            ->where('secteur_y', $y)
            ->where('secteur_z', $z)
            ->with([
                'planetes.gisements.ressource',
                'planetes.stations'
            ])
            ->get();

        return view('admin.carte-secteur', compact('x', 'y', 'z', 'systemes'));
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

    /**
     * Liste de toutes les mines (MAME)
     */
    public function mines()
    {
        $mines = Mine::with(['planete.systemeStellaire', 'gisement.ressource', 'proprietaire', 'installateur'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.mines', compact('mines'));
    }

    /**
     * Créer une mine sur un gisement
     */
    public function storeMine(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'planete_id' => 'required|exists:planetes,id',
            'gisement_id' => 'required|exists:gisements,id',
            'emplacement' => 'required|in:surface,orbite',
            'capacite_stockage' => 'nullable|integer|min:100',
            'taux_extraction' => 'nullable|numeric|min:1',
        ]);

        // Valeurs par défaut
        $validated['capacite_stockage'] = $validated['capacite_stockage'] ?? 10000;
        $validated['taux_extraction'] = $validated['taux_extraction'] ?? 100.0;
        $validated['statut'] = 'active';
        $validated['stock_actuel'] = 0;
        $validated['niveau_usure'] = 0;
        $validated['derniere_extraction'] = now();

        $mine = Mine::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mine créée avec succès',
                'mine' => $mine
            ]);
        }

        return redirect()->back()->with('success', 'Mine créée avec succès');
    }

    /**
     * Mettre à jour une mine
     */
    public function updateMine(Request $request, $id)
    {
        $mine = Mine::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'nullable|string|max:255',
            'taux_extraction' => 'nullable|numeric|min:1',
            'capacite_stockage' => 'nullable|integer|min:100',
            'statut' => 'nullable|in:active,inactive,maintenance,endommagee',
            'stock_energie' => 'nullable|integer|min:0',
            'stock_pieces_rechange' => 'nullable|integer|min:0',
            'stock_pieces_usure' => 'nullable|integer|min:0',
            'niveau_usure' => 'nullable|integer|min:0|max:100',
        ]);

        $mine->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mine mise à jour avec succès',
                'mine' => $mine
            ]);
        }

        return redirect()->back()->with('success', 'Mine mise à jour avec succès');
    }

    /**
     * Supprimer une mine
     */
    public function destroyMine($id)
    {
        $mine = Mine::findOrFail($id);
        $nom = $mine->nom;
        $mine->delete();

        return redirect()->route('admin.mines')
            ->with('success', "Mine \"{$nom}\" supprimée avec succès");
    }

    /**
     * Ravitailler une mine (admin)
     */
    public function ravitaillerMine(Request $request, $id)
    {
        $mine = Mine::findOrFail($id);

        $validated = $request->validate([
            'energie' => 'nullable|integer|min:0',
            'pieces_rechange' => 'nullable|integer|min:0',
            'pieces_usure' => 'nullable|integer|min:0',
        ]);

        $mine->ravitailler(
            $validated['energie'] ?? 0,
            $validated['pieces_rechange'] ?? 0,
            $validated['pieces_usure'] ?? 0
        );

        return response()->json([
            'success' => true,
            'message' => 'Mine ravitaillée avec succès',
            'mine' => $mine->fresh()
        ]);
    }

    /**
     * Forcer la maintenance d'une mine (admin)
     */
    public function maintenanceMine($id)
    {
        $mine = Mine::findOrFail($id);

        $result = $mine->effectuerMaintenance();

        if (!$result['succes']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'mine' => $mine->fresh()
        ]);
    }

    /**
     * Créer un gisement sur une planète
     */
    public function storeGisement(Request $request)
    {
        $validated = $request->validate([
            'planete_id' => 'required|exists:planetes,id',
            'ressource_id' => 'required|exists:ressources,id',
            'latitude' => 'nullable|numeric|min:-90|max:90',
            'longitude' => 'nullable|numeric|min:-180|max:180',
            'richesse' => 'nullable|integer|min:1|max:100',
            'quantite_totale' => 'nullable|integer|min:1',
        ]);

        // Valeurs par défaut
        $validated['latitude'] = $validated['latitude'] ?? rand(-9000, 9000) / 100;
        $validated['longitude'] = $validated['longitude'] ?? rand(-18000, 18000) / 100;
        $validated['richesse'] = $validated['richesse'] ?? rand(20, 100);
        $validated['quantite_totale'] = $validated['quantite_totale'] ?? rand(1000000, 10000000);
        $validated['quantite_restante'] = $validated['quantite_totale'];
        $validated['decouvert'] = true;

        $gisement = Gisement::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gisement créé avec succès',
                'gisement' => $gisement->load('ressource')
            ]);
        }

        return redirect()->back()->with('success', 'Gisement créé avec succès');
    }

    /**
     * Générer les planètes pour un système stellaire
     */
    public function genererPlanetes($id)
    {
        $systeme = SystemeStellaire::findOrFail($id);

        // Vérifier si le système a déjà des planètes
        if ($systeme->planetes()->count() > 0) {
            return redirect()->back()->with('error', "Le système {$systeme->nom} a déjà des planètes générées.");
        }

        // Vérifier que le système est censé avoir des planètes
        if ($systeme->nb_planetes === 0) {
            return redirect()->back()->with('error', "Le système {$systeme->nom} n'est pas censé avoir de planètes (nb_planetes = 0).");
        }

        // Générer les planètes
        for ($i = 1; $i <= $systeme->nb_planetes; $i++) {
            // Types correspondant à l'enum de la migration
            $types = ['terrestre', 'gazeuse', 'glacee', 'naine'];
            $type = $types[array_rand($types)];

            // Rayon en rayons terrestres (Terre = 1.0)
            $rayon = match($type) {
                'terrestre' => rand(5, 25) / 10, // 0.5 à 2.5 rayons terrestres
                'gazeuse' => rand(40, 140) / 10, // 4 à 14 rayons terrestres
                'glacee' => rand(3, 13) / 10, // 0.3 à 1.3 rayons terrestres
                'naine' => rand(1, 5) / 10, // 0.1 à 0.5 rayons terrestres
            };

            $planete = Planete::create([
                'systeme_stellaire_id' => $systeme->id,
                'nom' => "{$systeme->nom} {$i}",
                'distance_etoile' => $i * 0.5 + rand(0, 10) / 10,
                'rayon' => $rayon,
                'masse' => match($type) {
                    'terrestre' => rand(5, 30) / 10, // 0.5 à 3 masses terrestres
                    'gazeuse' => rand(50, 3000) / 10, // 5 à 300 masses terrestres
                    'glacee' => rand(1, 15) / 10, // 0.1 à 1.5 masses terrestres
                    'naine' => rand(1, 3) / 100, // 0.01 à 0.03 masses terrestres
                },
                'type' => $type,
                'a_atmosphere' => in_array($type, ['terrestre', 'gazeuse']) ? rand(0, 1) === 1 : false,
                'population' => 0,
                'detectabilite_base' => $this->calculatePlanetDetectability($rayon),
                'poi_connu' => false,
            ]);

            // Générer gisements pour cette planète
            $planete->genererGisements();
        }

        return redirect()->back()->with('success', "{$systeme->nb_planetes} planètes générées avec succès pour le système {$systeme->nom}");
    }

    /**
     * Créer un nouveau système solaire
     */
    public function creerSystemeSolaire(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'coord_x' => 'required|numeric',
            'coord_y' => 'required|numeric',
            'coord_z' => 'required|numeric',
            'type_etoile' => 'required|string|in:O,B,A,F,G,K,M',
            'nb_planetes' => 'nullable|integer|min:0|max:20',
        ]);

        // Convertir les coordonnées absolues en secteur + position
        $absX = $validated['coord_x'];
        $absY = $validated['coord_y'];
        $absZ = $validated['coord_z'];

        $secteurX = (int)floor($absX / 10);
        $secteurY = (int)floor($absY / 10);
        $secteurZ = (int)floor($absZ / 10);

        $positionX = $absX - ($secteurX * 10);
        $positionY = $absY - ($secteurY * 10);
        $positionZ = $absZ - ($secteurZ * 10);

        // Calculer puissance selon le type spectral
        $puissances = [
            'O' => [150, 200],
            'B' => [100, 140],
            'A' => [80, 100],
            'F' => [60, 80],
            'G' => [40, 60],
            'K' => [30, 40],
            'M' => [20, 30],
        ];

        [$min, $max] = $puissances[$validated['type_etoile']];
        $puissance = rand($min, $max);
        $detectabilite = round((200 - $puissance) / 3, 2);

        // Couleur selon le type
        $couleurs = [
            'O' => '#9BB0FF',
            'B' => '#AABFFF',
            'A' => '#CAD7FF',
            'F' => '#F8F7FF',
            'G' => '#FFF4EA',
            'K' => '#FFD2A1',
            'M' => '#FFCC6F',
        ];

        // Créer le système
        $systeme = SystemeStellaire::create([
            'nom' => $validated['nom'],
            'secteur_x' => $secteurX,
            'secteur_y' => $secteurY,
            'secteur_z' => $secteurZ,
            'position_x' => $positionX,
            'position_y' => $positionY,
            'position_z' => $positionZ,
            'type_etoile' => $validated['type_etoile'],
            'couleur' => $couleurs[$validated['type_etoile']],
            'puissance' => $puissance,
            'detectabilite_base' => $detectabilite,
            'nb_planetes' => $validated['nb_planetes'] ?? rand(0, 12),
            'poi_connu' => false,
            'source_gaia' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Système {$systeme->nom} créé avec succès",
                'systeme' => $systeme
            ]);
        }

        return redirect()->route('admin.carte', [
            'x' => $secteurX,
            'y' => $secteurY,
            'z' => $secteurZ,
        ])->with('success', "Système {$systeme->nom} créé avec succès aux coordonnées ({$absX}, {$absY}, {$absZ})");
    }

    /**
     * Calculer la détectabilité d'une planète
     */
    protected function calculatePlanetDetectability(float $rayon): float
    {
        // Plus une planète est grande, plus elle est facile à détecter
        // Terre (rayon = 1.0) = détectabilité 50
        // Jupiter (rayon = 11) = détectabilité 10
        if ($rayon <= 0) return 100.0;

        $detectabilite = 100 - ($rayon * 8);
        return max(10.0, min(100.0, $detectabilite));
    }
}
