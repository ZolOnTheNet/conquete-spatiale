<?php

namespace App\Http\Controllers;

use App\Models\ZoneSpatiale;
use App\Models\SystemeStellaire;
use App\Models\ObjetSpatial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur pour gérer les zones spatiales
 *
 * Fonctionnalités :
 * - Création de zones (ceintures d'astéroïdes, nuages, etc.)
 * - Gestion des astéroïdes notables dans une zone
 * - Détection des zones via scan
 * - Vérification si vaisseau est dans une zone
 *
 * @see App\Models\ZoneSpatiale
 * @see docs/game-design/GDD_Asteroides.md
 */
class ZoneSpatialController extends Controller
{
    /**
     * Liste toutes les zones d'un système stellaire
     */
    public function index(Request $request, int $systemeId)
    {
        $systeme = SystemeStellaire::findOrFail($systemeId);

        $zones = ZoneSpatiale::with(['asteroideNotables'])
            ->where('systeme_stellaire_id', $systemeId)
            ->get();

        return response()->json([
            'systeme' => $systeme->nom,
            'zones' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'nom' => $zone->getNomComplet(),
                    'type' => $zone->type,
                    'icone' => $zone->getIcone(),
                    'rayons_ua' => $zone->getRayonsUA(),
                    'azimuts_deg' => $zone->getAzimutsDegres(),
                    'cercle_complet' => $zone->estCercleComplet(),
                    'densite' => $zone->densite,
                    'poi_connu' => $zone->poi_connu,
                    'nb_notables' => $zone->asteroideNotables->count(),
                    'nb_notables_connus' => $zone->asteroideNotables->where('poi_connu', true)->count(),
                ];
            }),
        ]);
    }

    /**
     * Crée une nouvelle zone spatiale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'systeme_stellaire_id' => 'required|exists:systemes_stellaires,id',
            'type' => 'required|string|in:ceinture_asteroides,nuage_debris,nebuleuse',
            'nom' => 'nullable|string|max:255',
            'rayon_min' => 'required|integer|min:1',
            'rayon_max' => 'required|integer|gt:rayon_min',
            'azimut_debut' => 'nullable|numeric|min:0|max:6.283185',
            'azimut_fin' => 'nullable|numeric|min:0|max:6.283185',
            'densite' => 'nullable|integer|min:1|max:1000',
            'vitesse_traversee_modif' => 'nullable|numeric|min:0.1|max:1',
            'risque_collision' => 'nullable|numeric|min:0|max:1',
            'detectabilite_base' => 'nullable|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $zone = ZoneSpatiale::create($validated);

        Log::info("Zone spatiale créée", [
            'zone_id' => $zone->id,
            'systeme_id' => $zone->systeme_stellaire_id,
            'type' => $zone->type,
        ]);

        return response()->json([
            'message' => 'Zone spatiale créée avec succès',
            'zone' => $zone,
        ], 201);
    }

    /**
     * Ajoute un astéroïde notable à une zone
     */
    public function ajouterNotable(Request $request, int $zoneId)
    {
        $zone = ZoneSpatiale::findOrFail($zoneId);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'masse' => 'required|integer|min:1',
            'distance_etoile' => 'required|integer|min:1', // cUA
            'angle_orbital_initial' => 'required|numeric|min:0|max:6.283185', // radians
            'vitesse_angulaire' => 'required|numeric',
            'periode_orbitale' => 'nullable|integer',
            'detectabilite_base' => 'nullable|numeric',
        ]);

        // Créer l'objet spatial
        $objet = ObjetSpatial::create([
            'nom' => $validated['nom'],
            'type' => 'asteroide_notable',
            'secteur_x' => $zone->systeme->secteur_x ?? 0,
            'secteur_y' => $zone->systeme->secteur_y ?? 0,
            'secteur_z' => $zone->systeme->secteur_z ?? 0,
            'position_x' => 0, // Sera calculé par orbite
            'position_y' => 0,
            'position_z' => 0,
            'masse' => $validated['masse'],
            'detectabilite_base' => $validated['detectabilite_base'] ?? 60,
            'poi_connu' => false,
            'parent_type' => ZoneSpatiale::class,
            'parent_id' => $zone->id,
        ]);

        // Créer entrée planète pour gérer l'orbite
        // (Astéroïdes utilisent la même mécanique orbitale que planètes)
        $planete = \App\Models\Planete::create([
            'systeme_stellaire_id' => $zone->systeme_stellaire_id,
            'nom' => $validated['nom'],
            'type' => 'asteroide',
            'rayon' => 0.001, // Petit rayon pour astéroïde
            'distance_etoile' => $validated['distance_etoile'],
            'angle_orbital_initial' => $validated['angle_orbital_initial'],
            'vitesse_angulaire' => $validated['vitesse_angulaire'],
            'periode_orbitale' => $validated['periode_orbitale'] ?? 365,
            'detectabilite_base' => $validated['detectabilite_base'] ?? 60,
            'poi_connu' => false,
        ]);

        Log::info("Astéroïde notable ajouté à zone", [
            'zone_id' => $zone->id,
            'objet_id' => $objet->id,
            'planete_id' => $planete->id,
            'nom' => $validated['nom'],
        ]);

        return response()->json([
            'message' => 'Astéroïde notable ajouté avec succès',
            'objet' => $objet,
            'planete' => $planete,
        ], 201);
    }

    /**
     * Vérifie si un vaisseau est dans une zone
     */
    public function verifierPresenceVaisseau(Request $request, int $zoneId)
    {
        $zone = ZoneSpatiale::findOrFail($zoneId);

        $validated = $request->validate([
            'position_x' => 'required|integer',
            'position_y' => 'required|integer',
        ]);

        $dansZone = $zone->contientPosition(
            $validated['position_x'],
            $validated['position_y']
        );

        $distanceBordure = $zone->distanceABordure(
            $validated['position_x'],
            $validated['position_y']
        );

        return response()->json([
            'dans_zone' => $dansZone,
            'distance_bordure_cua' => $distanceBordure,
            'distance_bordure_ua' => $distanceBordure / 100,
            'modificateur_vitesse' => $dansZone ? $zone->getModificateurVitesse() : 1.0,
            'risque_collision' => $dansZone ? $zone->risque_collision : 0,
        ]);
    }

    /**
     * Génère des astéroïdes procéduraux autour d'une position
     */
    public function genererAsteroidesProceduraux(Request $request, int $zoneId)
    {
        $zone = ZoneSpatiale::findOrFail($zoneId);

        $validated = $request->validate([
            'position_x' => 'required|integer',
            'position_y' => 'required|integer',
            'rayon_recherche' => 'nullable|integer|min:100|max:50000',
            'nombre_max' => 'nullable|integer|min:1|max:100',
        ]);

        $asteroides = $zone->genererAsteroidesProceduraux(
            $validated['position_x'],
            $validated['position_y'],
            $validated['rayon_recherche'] ?? 5000,
            $validated['nombre_max'] ?? 20
        );

        return response()->json([
            'zone' => $zone->getNomComplet(),
            'nombre_generes' => count($asteroides),
            'asteroides' => $asteroides,
        ]);
    }

    /**
     * Affiche les détails d'une zone
     */
    public function show(int $zoneId)
    {
        $zone = ZoneSpatiale::with(['systeme', 'asteroideNotables'])
            ->findOrFail($zoneId);

        return response()->json([
            'zone' => [
                'id' => $zone->id,
                'nom' => $zone->getNomComplet(),
                'type' => $zone->type,
                'icone' => $zone->getIcone(),
                'systeme' => $zone->systeme->nom,
                'rayons' => $zone->getRayonsUA(),
                'azimuts' => $zone->getAzimutsDegres(),
                'cercle_complet' => $zone->estCercleComplet(),
                'densite' => $zone->densite,
                'vitesse_modif' => $zone->vitesse_traversee_modif,
                'risque_collision' => $zone->risque_collision,
                'detectabilite' => $zone->detectabilite_base,
                'poi_connu' => $zone->poi_connu,
                'description' => $zone->description,
                'notables' => $zone->asteroideNotables->map(function ($ast) {
                    return [
                        'id' => $ast->id,
                        'nom' => $ast->nom,
                        'masse' => $ast->masse,
                        'poi_connu' => $ast->poi_connu,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Met à jour une zone
     */
    public function update(Request $request, int $zoneId)
    {
        $zone = ZoneSpatiale::findOrFail($zoneId);

        $validated = $request->validate([
            'nom' => 'nullable|string|max:255',
            'rayon_min' => 'nullable|integer|min:1',
            'rayon_max' => 'nullable|integer|gt:rayon_min',
            'azimut_debut' => 'nullable|numeric|min:0|max:6.283185',
            'azimut_fin' => 'nullable|numeric|min:0|max:6.283185',
            'densite' => 'nullable|integer|min:1|max:1000',
            'vitesse_traversee_modif' => 'nullable|numeric|min:0.1|max:1',
            'risque_collision' => 'nullable|numeric|min:0|max:1',
            'detectabilite_base' => 'nullable|numeric|min:1',
            'poi_connu' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $zone->update($validated);

        return response()->json([
            'message' => 'Zone mise à jour avec succès',
            'zone' => $zone,
        ]);
    }

    /**
     * Supprime une zone
     */
    public function destroy(int $zoneId)
    {
        $zone = ZoneSpatiale::findOrFail($zoneId);

        // Supprimer aussi les astéroïdes notables liés
        $zone->asteroideNotables()->delete();

        $zone->delete();

        return response()->json([
            'message' => 'Zone supprimée avec succès',
        ]);
    }

    /**
     * Marque une zone comme découverte
     */
    public function marquerDecouvert(int $zoneId)
    {
        $zone = ZoneSpatiale::findOrFail($zoneId);
        $zone->marquerDecouvert();

        return response()->json([
            'message' => 'Zone marquée comme découverte',
            'zone' => $zone,
        ]);
    }
}
