<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Compte;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class BackendController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {}

    /**
     * Dashboard administratif principal
     */
    public function dashboard(): View
    {
        $stats = [
            'comptes_total' => Compte::count(),
            'personnages_total' => Personnage::count(),
            'systemes_total' => SystemeStellaire::count(),
            'systemes_gaia' => SystemeStellaire::where('source_gaia', true)->count(),
            'personnages_actifs' => Personnage::whereNotNull('systeme_actuel_id')->count(),
        ];

        $derniersPersonnages = Personnage::with(['compte', 'vaisseau', 'systemeActuel'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('backend.dashboard', compact('stats', 'derniersPersonnages'));
    }

    /**
     * Carte stellaire 3D interactive
     */
    public function carte(): View
    {
        return view('backend.carte');
    }

    /**
     * API: Récupérer tous les systèmes stellaires pour la carte
     */
    public function apiSystemes(): JsonResponse
    {
        $systemes = SystemeStellaire::select([
            'id',
            'nom',
            'secteur_x',
            'secteur_y',
            'secteur_z',
            'position_x',
            'position_y',
            'position_z',
            'type_etoile',
            'couleur',
            'nb_planetes',
            'source_gaia',
        ])->get();

        return response()->json([
            'success' => true,
            'count' => $systemes->count(),
            'systemes' => $systemes,
        ]);
    }

    /**
     * API: Récupérer positions des joueurs
     */
    public function apiJoueurs(): JsonResponse
    {
        $personnages = Personnage::with(['compte', 'vaisseau', 'systemeActuel'])
            ->whereNotNull('systeme_actuel_id')
            ->get()
            ->map(function ($personnage) {
                $systeme = $personnage->systemeActuel;

                return [
                    'id' => $personnage->id,
                    'nom' => $personnage->nom,
                    'compte' => $personnage->compte->nom_login,
                    'vaisseau' => $personnage->vaisseau?->nom_modele ?? 'Aucun',
                    'systeme' => [
                        'id' => $systeme->id,
                        'nom' => $systeme->nom,
                        'x' => $systeme->secteur_x + $systeme->position_x,
                        'y' => $systeme->secteur_y + $systeme->position_y,
                        'z' => $systeme->secteur_z + $systeme->position_z,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $personnages->count(),
            'personnages' => $personnages,
        ]);
    }

    /**
     * API: Téléporter un personnage vers un système
     */
    public function apiTeleport(Request $request, Personnage $personnage): JsonResponse
    {
        $validated = $request->validate([
            'systeme_id' => 'required|exists:systemes_stellaires,id',
        ]);

        $systeme = SystemeStellaire::findOrFail($validated['systeme_id']);

        $personnage->update([
            'systeme_actuel_id' => $systeme->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Personnage {$personnage->nom} téléporté vers {$systeme->nom}",
            'personnage' => [
                'id' => $personnage->id,
                'nom' => $personnage->nom,
                'systeme' => $systeme->nom,
            ],
        ]);
    }

    /**
     * Interface de gestion des sauvegardes
     */
    public function backupIndex(): View
    {
        $backups = $this->backupService->listBackups();

        return view('backend.backup.index', compact('backups'));
    }

    /**
     * Créer une nouvelle sauvegarde
     */
    public function backupCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $backup = $this->backupService->createBackup($validated['description'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde créée avec succès',
                'backup' => $backup,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la sauvegarde',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Télécharger une sauvegarde
     */
    public function backupDownload(string $filename)
    {
        return $this->backupService->downloadBackup($filename);
    }

    /**
     * Restaurer une sauvegarde
     */
    public function backupRestore(Request $request, string $filename): JsonResponse
    {
        try {
            $this->backupService->restoreBackup($filename);

            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde restaurée avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer une sauvegarde
     */
    public function backupDelete(string $filename): JsonResponse
    {
        try {
            $this->backupService->deleteBackup($filename);

            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde supprimée avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les sauvegardes (API)
     */
    public function backupList(): JsonResponse
    {
        $backups = $this->backupService->listBackups();

        return response()->json([
            'success' => true,
            'count' => count($backups),
            'backups' => $backups,
        ]);
    }
}
