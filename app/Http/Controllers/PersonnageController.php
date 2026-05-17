<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnageController extends Controller
{
    /**
     * Afficher le dossier du personnage
     */
    public function dossier(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->load(['vaisseauActif.objetSpatial', 'compte']);

        // Déterminer le contexte
        $contextService = app(\App\Services\GameContextService::class);
        $context = $contextService->getMenuContext($personnage);

        // Récupérer le système actuel si en vaisseau
        $systeme = null;
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
            $systeme = \App\Models\SystemeStellaire::where('secteur_x', $personnage->vaisseauActif->objetSpatial->secteur_x)
                ->where('secteur_y', $personnage->vaisseauActif->objetSpatial->secteur_y)
                ->where('secteur_z', $personnage->vaisseauActif->objetSpatial->secteur_z)
                ->with('planetes')
                ->first();
        }

        return view('game.personnage.dossier', [
            'personnage' => $personnage,
            'systeme' => $systeme,
            'context' => $context,
            'isAdmin' => $request->user()->is_admin ?? false,
        ]);
    }

    /**
     * Afficher la spatiocarte (carte des systèmes découverts avec onglets Carte/Atlas)
     */
    public function spatiocarte(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $onglet = $request->get('onglet', 'carte'); // 'carte' ou 'atlas'

        // Si onglet carte, rediriger vers la vue carte complète
        if ($onglet === 'carte') {
            return redirect()->route('carte');
        }

        // Onglet Atlas : récupérer les découvertes
        $decouvertes = $personnage->decouvertes()
            ->with(['systemeStellaire.planetes.gisements.ressource'])
            ->get()
            ->map(function($decouverte) use ($personnage) {
                $systeme = $decouverte->systemeStellaire;
                if (!$systeme) return null;

                // Calculer la distance depuis la position du personnage
                $distance = 0;
                if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
                    $os = $personnage->vaisseauActif->objetSpatial;
                    $dx = ($systeme->secteur_x + $systeme->position_x) - ($os->secteur_x + $os->position_x);
                    $dy = ($systeme->secteur_y + $systeme->position_y) - ($os->secteur_y + $os->position_y);
                    $dz = ($systeme->secteur_z + $systeme->position_z) - ($os->secteur_z + $os->position_z);
                    $distance = sqrt($dx*$dx + $dy*$dy + $dz*$dz);
                }

                return [
                    'systeme' => $systeme,
                    'decouverte' => $decouverte,
                    'distance' => $distance,
                    'coords_secteur' => "({$systeme->secteur_x}, {$systeme->secteur_y}, {$systeme->secteur_z})",
                    'coords_position' => "({$systeme->position_x}, {$systeme->position_y}, {$systeme->position_z})",
                ];
            })
            ->filter()
            ->sortBy('distance');

        return view('game.spatiocarte-atlas', [
            'personnage' => $personnage,
            'decouvertes' => $decouvertes,
            'vaisseau' => $personnage->vaisseauActif,
            'compte' => $request->user(),
        ]);
    }

    /**
     * Carte galactique 3D (tous les systèmes découverts, vue Three.js)
     */
    public function carte3d(Request $request)
    {
        $personnage   = $request->attributes->get('personnage');
        $vaisseau     = $personnage->vaisseauActif;
        $objetSpatial = $vaisseau?->objetSpatial;

        // Position d'origine = secteur uniquement (position_x intra-système peut être en cUA, sans rapport avec l'échelle galactique)
        $originX = $objetSpatial ? $objetSpatial->secteur_x : 0;
        $originY = $objetSpatial ? $objetSpatial->secteur_y : 0;
        $originZ = $objetSpatial ? $objetSpatial->secteur_z : 0;

        $systemeActuel = null;
        if ($objetSpatial) {
            $systemeActuel = \App\Models\SystemeStellaire::where('secteur_x', $objetSpatial->secteur_x)
                ->where('secteur_y', $objetSpatial->secteur_y)
                ->where('secteur_z', $objetSpatial->secteur_z)
                ->first();
        }

        $energieActuelle = $vaisseau?->energie_actuelle ?? 0;
        $paActuel        = $personnage->points_action ?? 0;

        $decouvertes = $personnage->decouvertes()->with('systemeStellaire')->get();

        // Préchargement des comptes en 3 requêtes groupées
        $sysIds = $decouvertes->pluck('systeme_stellaire_id')->filter()->unique();

        $planetCounts = \DB::table('planetes')
            ->whereIn('systeme_stellaire_id', $sysIds)
            ->groupBy('systeme_stellaire_id')
            ->selectRaw('systeme_stellaire_id, COUNT(*) as nb')
            ->pluck('nb', 'systeme_stellaire_id');

        $mineCounts = \DB::table('gisements')
            ->join('planetes', 'gisements.planete_id', '=', 'planetes.id')
            ->whereIn('planetes.systeme_stellaire_id', $sysIds)
            ->groupBy('planetes.systeme_stellaire_id')
            ->selectRaw('planetes.systeme_stellaire_id, COUNT(*) as nb')
            ->pluck('nb', 'planetes.systeme_stellaire_id');

        $stationCounts = \DB::table('stations')
            ->whereIn('systeme_stellaire_id', $sysIds)
            ->groupBy('systeme_stellaire_id')
            ->selectRaw('systeme_stellaire_id, COUNT(*) as nb')
            ->pluck('nb', 'systeme_stellaire_id');

        $galacticData = [];

        // Position actuelle au centre
        $galacticData[] = [
            'name'            => $systemeActuel->nom ?? 'Position actuelle',
            'x'               => 0.0,
            'y'               => 0.0,
            'z'               => 0.0,
            'colorHex'        => '#ff8a3d',
            'size'            => 2.0,
            'id'              => $systemeActuel->id ?? 0,
            'isCurrent'       => true,
            'coords'          => $objetSpatial
                ? "({$objetSpatial->secteur_x}, {$objetSpatial->secteur_y}, {$objetSpatial->secteur_z})"
                : '(0, 0, 0)',
            'distance'        => 0,
            'type_etoile'     => $systemeActuel->type_etoile ?? '—',
            'pa_requis'       => 0,
            'energie_requise' => 0,
            'saut_possible'   => false,
            'nb_planetes'     => $systemeActuel ? (int)($planetCounts[$systemeActuel->id] ?? 0) : 0,
            'nb_mines'        => $systemeActuel ? (int)($mineCounts[$systemeActuel->id] ?? 0) : 0,
            'nb_stations'     => $systemeActuel ? (int)($stationCounts[$systemeActuel->id] ?? 0) : 0,
        ];

        foreach ($decouvertes as $decouverte) {
            $sys = $decouverte->systemeStellaire;
            if (!$sys) continue;
            if ($systemeActuel && $sys->id === $systemeActuel->id) continue;

            $dx   = $sys->secteur_x - $originX;
            $dy   = $sys->secteur_y - $originY;
            $dz   = $sys->secteur_z - $originZ;
            $dist = round(sqrt($dx * $dx + $dy * $dy + $dz * $dz), 2);

            $paRequis       = max(1, (int)ceil($dist / 2));
            $energieRequise = (int)(100 + ($dist * 50));

            $galacticData[] = [
                'name'            => $sys->nom,
                'x'               => round($dx * 9, 2),
                'y'               => round($dz * 1.8, 2),
                'z'               => round($dy * 9, 2),
                'colorHex'        => '#7fd4ff',
                'size'            => 1.3,
                'id'              => $sys->id,
                'isCurrent'       => false,
                'coords'          => "({$sys->secteur_x}, {$sys->secteur_y}, {$sys->secteur_z})",
                'distance'        => $dist,
                'type_etoile'     => $sys->type_etoile ?? '—',
                'pa_requis'       => $paRequis,
                'energie_requise' => $energieRequise,
                'saut_possible'   => $energieActuelle >= $energieRequise && $paActuel >= $paRequis,
                'nb_planetes'     => (int)($planetCounts[$sys->id] ?? 0),
                'nb_mines'        => (int)($mineCounts[$sys->id] ?? 0),
                'nb_stations'     => (int)($stationCounts[$sys->id] ?? 0),
            ];
        }

        return view('game.carte3d', [
            'personnage'   => $personnage,
            'vaisseau'     => $vaisseau,
            'galacticData' => $galacticData,
            'nSystems'     => count($galacticData),
            'compte'       => $request->user(),
        ]);
    }

    /**
     * API JSON : POIs d'un système pour la carte 3D
     */
    public function carte3dSysteme(Request $request, int $id)
    {
        $personnage = $request->attributes->get('personnage');

        $systeme = \App\Models\SystemeStellaire::with(['planetes.gisements'])->find($id);
        if (!$systeme) return response()->json(['error' => 'not found'], 404);

        $stations = \App\Models\Station::where('systeme_stellaire_id', $id)->get();

        $pois = [];

        // Corps primaires (planètes sans parent) triés par distance à l'étoile
        $primaires = $systeme->planetes->whereNull('planete_parente_id')->sortBy('distance_etoile')->values();
        $primaires->each(function ($p, $i) use (&$pois) {
            $angle  = ($i / max(1, count($pois) + 1)) * M_PI * 2;
            $r      = max(8, ($p->distance_etoile ?? ($i + 1) * 8));
            $pois[] = [
                'id'                => $p->id,
                'name'              => $p->nom,
                'type'              => 'planete',
                'categorie'         => $p->categorie ?? $p->type ?? '—',
                'colorHex'          => $this->planeteColor($p->categorie ?? $p->type),
                'size'              => 1.2,
                'orbitRadius'       => (float)$r,
                'angle'             => $angle,
                'x'                 => round(cos($angle) * $r, 2),
                'y'                 => 0.0,
                'z'                 => round(sin($angle) * $r, 2),
                'planete_parente_id'=> null,
                'nb_gisements'      => $p->gisements?->count() ?? 0,
            ];
        });

        // Satellites (planètes avec parent)
        $satellites = $systeme->planetes->whereNotNull('planete_parente_id');
        $satellites->each(function ($s) use (&$pois) {
            $parent = collect($pois)->firstWhere('id', $s->planete_parente_id);
            $r      = max(2, ($s->distance_planete ?? 3));
            $angle  = lcg_value() * M_PI * 2;
            $px     = $parent ? $parent['x'] : 0;
            $pz     = $parent ? $parent['z'] : 0;
            $pois[] = [
                'id'                => $s->id,
                'name'              => $s->nom,
                'type'              => 'satellite',
                'categorie'         => $s->categorie ?? $s->type ?? '—',
                'colorHex'          => '#8899aa',
                'size'              => 0.7,
                'orbitRadius'       => (float)$r,
                'angle'             => $angle,
                'x'                 => round($px + cos($angle) * $r, 2),
                'y'                 => 0.0,
                'z'                 => round($pz + sin($angle) * $r, 2),
                'planete_parente_id'=> $s->planete_parente_id,
                'nb_gisements'      => $s->gisements?->count() ?? 0,
            ];
        });

        // Stations
        $stCount = $stations->count();
        $stations->each(function ($st, $i) use (&$pois, $stCount) {
            $parent = collect($pois)->firstWhere('id', $st->planete_id);
            $r      = $st->orbite_rayon_ua ?? 2;
            $angle  = $st->orbite_angle   ?? ($i / max(1, $stCount) * M_PI * 2);
            $px     = $parent ? $parent['x'] : 30;
            $pz     = $parent ? $parent['z'] : 0;
            $pois[] = [
                'id'                => 'st-' . $st->id,
                'name'              => $st->nom,
                'type'              => 'station',
                'categorie'         => $st->type ?? '—',
                'colorHex'          => '#ffd700',
                'size'              => 0.9,
                'orbitRadius'       => (float)$r,
                'angle'             => (float)$angle,
                'x'                 => round($px + cos($angle) * $r, 2),
                'y'                 => 0.5,
                'z'                 => round($pz + sin($angle) * $r, 2),
                'planete_parente_id'=> $st->planete_id,
                'nb_gisements'      => 0,
            ];
        });

        return response()->json([
            'systeme' => ['nom' => $systeme->nom, 'type_etoile' => $systeme->type_etoile],
            'pois'    => $pois,
        ]);
    }

    private function planeteColor(?string $cat): string
    {
        return match (true) {
            str_contains($cat ?? '', 'tellu')  => '#a0c8a0',
            str_contains($cat ?? '', 'gaz')    => '#d4a070',
            str_contains($cat ?? '', 'glac')   => '#a0c8e0',
            str_contains($cat ?? '', 'roch')   => '#909090',
            default                            => '#8899aa',
        };
    }

    /**
     * Afficher la gestion des biens du personnage
     */
    public function gestion(Request $request): View
    {
        $personnage = $request->attributes->get('personnage');
        $personnage->load(['vaisseauActif']);

        // Déterminer le contexte
        $contextService = app(\App\Services\GameContextService::class);
        $context = $contextService->getMenuContext($personnage);

        // Récupérer le système actuel si en vaisseau
        $systeme = null;
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
            $systeme = \App\Models\SystemeStellaire::where('secteur_x', $personnage->vaisseauActif->objetSpatial->secteur_x)
                ->where('secteur_y', $personnage->vaisseauActif->objetSpatial->secteur_y)
                ->where('secteur_z', $personnage->vaisseauActif->objetSpatial->secteur_z)
                ->with('planetes')
                ->first();
        }

        return view('game.personnage.gestion', [
            'personnage' => $personnage,
            'systeme' => $systeme,
            'context' => $context,
            'isAdmin' => $request->user()->is_admin ?? false,
        ]);
    }
}
