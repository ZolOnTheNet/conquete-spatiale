<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Station;
use App\Models\Vaisseau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour le ravitaillement des vaisseaux
 *
 * Gère :
 * - Ravitaillement complet (automatique)
 * - Ravitaillement sélectif (carburant, eau, oxygène)
 * - Calcul des coûts
 *
 * @see docs/game-design/SYSTEME_STATIONS.md
 */
class RavitaillementController extends Controller
{
    /**
     * Afficher l'interface de ravitaillement
     */
    public function index(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Aucun personnage actif sélectionné.');
        }

        // Vérifier que le personnage est dans une station
        if (!$personnage->dans_station_id) {
            return redirect()->route('game.navire.timonerie')
                ->with('error', 'Vous devez être dans une station pour ravitailler.');
        }

        $station = $personnage->dansStation;

        // Vérifier que la station a un service de ravitaillement
        if (!$station->ravitaillement) {
            return redirect()->route('station.menu')
                ->with('error', 'Cette station ne dispose pas de service de ravitaillement.');
        }

        $vaisseau = $personnage->vaisseauActif;

        // Calculer les besoins
        $besoins = $this->calculerBesoins($vaisseau);

        return view('game.ravitaillement.index', compact('personnage', 'station', 'vaisseau', 'besoins'));
    }

    /**
     * Ravitailler complètement le vaisseau
     */
    public function ravitaillerComplet(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->dans_station_id) {
            return redirect()->route('game.navire.timonerie')
                ->with('error', 'Vous devez être dans une station pour ravitailler.');
        }

        $station = $personnage->dansStation;

        if (!$station->ravitaillement) {
            return redirect()->route('station.menu')
                ->with('error', 'Cette station ne dispose pas de service de ravitaillement.');
        }

        $vaisseau = $personnage->vaisseauActif;

        // Calculer besoins
        $besoins = $this->calculerBesoins($vaisseau);
        $coutTotal = $besoins['cout_total'];

        // Vérifier crédits (TODO selon votre système)
        // if ($personnage->credits < $coutTotal) {
        //     return redirect()->back()->with('error', 'Crédits insuffisants.');
        // }

        // Ravitailler
        $vaisseau->energie_actuelle = $vaisseau->reserve;
        // TODO: Ajouter eau, oxygène selon votre modèle
        $vaisseau->save();

        // Déduire crédits (TODO)
        // $personnage->credits -= $coutTotal;
        // $personnage->save();

        return redirect()->back()
            ->with('success', "Ravitaillement complet effectué pour {$coutTotal} crédits.");
    }

    /**
     * Ravitailler uniquement le carburant
     */
    public function ravitaillerCarburant(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->dans_station_id) {
            return redirect()->route('game.navire.timonerie')
                ->with('error', 'Vous devez être dans une station pour ravitailler.');
        }

        $station = $personnage->dansStation;

        if (!$station->ravitaillement) {
            return redirect()->route('station.menu')
                ->with('error', 'Cette station ne dispose pas de service de ravitaillement.');
        }

        $vaisseau = $personnage->vaisseauActif;

        // Calculer besoins carburant
        $energieManquante = $vaisseau->reserve - $vaisseau->energie_actuelle;
        $coutCarburant = $energieManquante * config('game.prix.carburant_par_unite', 1);

        // Vérifier crédits (TODO)
        // if ($personnage->credits < $coutCarburant) {
        //     return redirect()->back()->with('error', 'Crédits insuffisants.');
        // }

        // Ravitailler
        $vaisseau->energie_actuelle = $vaisseau->reserve;
        $vaisseau->save();

        // Déduire crédits (TODO)
        // $personnage->credits -= $coutCarburant;
        // $personnage->save();

        return redirect()->back()
            ->with('success', "Carburant ravitaillé pour {$coutCarburant} crédits.");
    }

    /**
     * Calculer les besoins de ravitaillement
     */
    private function calculerBesoins(Vaisseau $vaisseau): array
    {
        $energieManquante = $vaisseau->reserve - $vaisseau->energie_actuelle;

        // Prix de base (à configurer dans config/game.php)
        $prixCarburantParUnite = config('game.prix.carburant_par_unite', 1);
        $prixEauParUnite = config('game.prix.eau_par_unite', 0.5);
        $prixOxygeneParUnite = config('game.prix.oxygene_par_unite', 0.3);

        // Calculs
        $coutCarburant = $energieManquante * $prixCarburantParUnite;

        // TODO: Ajouter eau, oxygène si le modèle Vaisseau les gère
        $coutEau = 0;
        $coutOxygene = 0;

        $coutTotal = $coutCarburant + $coutEau + $coutOxygene;

        return [
            'energie_manquante' => $energieManquante,
            'energie_max' => $vaisseau->reserve,
            'energie_actuelle' => $vaisseau->energie_actuelle,
            'cout_carburant' => $coutCarburant,
            'cout_eau' => $coutEau,
            'cout_oxygene' => $coutOxygene,
            'cout_total' => $coutTotal,
        ];
    }
}
