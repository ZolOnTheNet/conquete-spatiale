<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Station;
use App\Models\Vaisseau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour le garage/réparations
 *
 * Gère :
 * - Affichage de l'état du vaisseau
 * - Réparation de la coque
 * - Réparation des systèmes (énergie, moteurs)
 * - Réparation de pannes spécifiques
 *
 * @see docs/game-design/SYSTEME_STATIONS.md
 */
class GarageController extends Controller
{
    /**
     * Afficher l'interface du garage
     */
    public function index(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif ?? null;

        // Simple : si amarré, récupérer la station
        if (!$vaisseau || !$vaisseau->arrime_a_station_id) {
            return redirect()->route('navire.timonerie')
                ->with('error', 'Vous devez être amarré pour accéder au garage.');
        }

        $station = Station::find($vaisseau->arrime_a_station_id);
        $vaisseau->load(['arme1', 'arme2', 'arme3', 'bouclier', 'objetSpatial']);
        $diagnostics = $this->calculerDiagnostics($vaisseau);

        return view('game.garage.index', compact('personnage', 'station', 'vaisseau', 'diagnostics'));
    }

    /**
     * Réparer la coque du vaisseau
     */
    public function reparerCoque(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau || !$vaisseau->arrime_a_station_id) {
            return redirect()->route('navire.timonerie')->with('error', 'Vous devez être amarré.');
        }

        $station = Station::find($vaisseau->arrime_a_station_id);

        // Calculer dommages coque
        $dommagesCoque = $vaisseau->coque_max - $vaisseau->coque_actuelle;

        if ($dommagesCoque <= 0) {
            return redirect()->back()
                ->with('info', 'La coque est déjà en parfait état.');
        }

        // Calculer coût (1 crédit par point de coque à réparer)
        $coutReparation = $dommagesCoque * config('game.prix.reparation_coque_par_point', 10);

        // Vérifier crédits (TODO)
        // if ($personnage->credits < $coutReparation) {
        //     return redirect()->back()->with('error', 'Crédits insuffisants.');
        // }

        // Réparer
        $vaisseau->coque_actuelle = $vaisseau->coque_max;
        $vaisseau->save();

        // Déduire crédits (TODO)
        // $personnage->credits -= $coutReparation;
        // $personnage->save();

        return redirect()->back()
            ->with('success', "Coque réparée ({$dommagesCoque} points) pour {$coutReparation} crédits.");
    }

    /**
     * Réparer une panne spécifique
     */
    public function reparerPanne(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        $request->validate([
            'panne_id' => 'required|string',
        ]);

        $vaisseau = $personnage->vaisseauActif;
        $panneId = $request->panne_id;

        // Vérifier que la panne existe
        $pannes = $vaisseau->pannes_actuelles ?? [];

        if (!isset($pannes[$panneId])) {
            return redirect()->back()
                ->with('error', 'Cette panne n\'existe pas.');
        }

        $panne = $pannes[$panneId];

        // Calculer coût (selon complexité de la panne)
        $coutReparation = $panne['complexite'] * config('game.prix.reparation_panne_base', 50);

        // Vérifier crédits (TODO)
        // if ($personnage->credits < $coutReparation) {
        //     return redirect()->back()->with('error', 'Crédits insuffisants.');
        // }

        // Réparer (retirer la panne)
        unset($pannes[$panneId]);
        $vaisseau->pannes_actuelles = $pannes;
        $vaisseau->save();

        // Déduire crédits (TODO)
        // $personnage->credits -= $coutReparation;
        // $personnage->save();

        return redirect()->back()
            ->with('success', "Panne '{$panne['nom']}' réparée pour {$coutReparation} crédits.");
    }

    /**
     * Réparer tout le vaisseau (complet)
     */
    public function reparerTout(Request $request)
    {
        $personnage = $request->attributes->get('personnage');
        $vaisseau = $personnage->vaisseauActif;

        $diagnostics = $this->calculerDiagnostics($vaisseau);
        $coutTotal = $diagnostics['cout_total'];

        if ($coutTotal <= 0) {
            return redirect()->back()
                ->with('info', 'Le vaisseau est déjà en parfait état.');
        }

        // Vérifier crédits (TODO)
        // if ($personnage->credits < $coutTotal) {
        //     return redirect()->back()->with('error', 'Crédits insuffisants.');
        // }

        // Réparer tout
        $vaisseau->coque_actuelle = $vaisseau->coque_max;
        $vaisseau->pannes_actuelles = [];
        $vaisseau->save();

        // Déduire crédits (TODO)
        // $personnage->credits -= $coutTotal;
        // $personnage->save();

        return redirect()->back()
            ->with('success', "Réparations complètes effectuées pour {$coutTotal} crédits.");
    }

    /**
     * Calculer les diagnostics et coûts de réparation
     */
    private function calculerDiagnostics(Vaisseau $vaisseau): array
    {
        // Dommages coque
        $dommagesCoque = $vaisseau->coque_max - $vaisseau->coque_actuelle;
        $pourcentageCoque = ($vaisseau->coque_actuelle / $vaisseau->coque_max) * 100;
        $coutCoque = $dommagesCoque * config('game.prix.reparation_coque_par_point', 10);

        // Pannes
        $pannes = $vaisseau->pannes_actuelles ?? [];
        $nbPannes = count($pannes);
        $coutPannes = 0;

        foreach ($pannes as $panne) {
            $coutPannes += ($panne['complexite'] ?? 1) * config('game.prix.reparation_panne_base', 50);
        }

        // Total
        $coutTotal = $coutCoque + $coutPannes;

        return [
            'coque_actuelle' => $vaisseau->coque_actuelle,
            'coque_max' => $vaisseau->coque_max,
            'dommages_coque' => $dommagesCoque,
            'pourcentage_coque' => $pourcentageCoque,
            'cout_coque' => $coutCoque,
            'pannes' => $pannes,
            'nb_pannes' => $nbPannes,
            'cout_pannes' => $coutPannes,
            'cout_total' => $coutTotal,
        ];
    }
}
