<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Station;
use App\Models\Produit;
use App\Models\MarcheStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur pour le marché des stations
 *
 * Gère :
 * - Affichage des produits disponibles
 * - Achat de marchandises
 * - Vente de marchandises
 * - Prix dynamiques selon l'offre/demande
 *
 * @see docs/game-design/SYSTEME_STATIONS.md
 */
class MarcheController extends Controller
{
    /**
     * Afficher le marché de la station
     */
    public function index()
    {
        $personnage = Auth::user()->personnageActif;

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Aucun personnage actif sélectionné.');
        }

        // Vérifier que le personnage est dans une station
        if (!$personnage->dans_station_id) {
            return redirect()->route('game.navire.timonerie')
                ->with('error', 'Vous devez être dans une station pour accéder au marché.');
        }

        $station = $personnage->dansStation;

        // Vérifier que la station a un marché
        if (!$station->commerciale) {
            return redirect()->route('station.menu')
                ->with('error', 'Cette station ne dispose pas de marché.');
        }

        // Récupérer les produits disponibles
        $produits = $station->produits()
            ->with('ressource')
            ->get();

        // Récupérer le vaisseau pour voir la cargo
        $vaisseau = $personnage->vaisseauActif;

        return view('game.marche.index', compact('personnage', 'station', 'produits', 'vaisseau'));
    }

    /**
     * Acheter une marchandise
     */
    public function acheter(Request $request)
    {
        $personnage = Auth::user()->personnageActif;

        if (!$personnage || !$personnage->dans_station_id) {
            return redirect()->route('game.navire.timonerie')
                ->with('error', 'Vous devez être dans une station pour acheter.');
        }

        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1',
        ]);

        $station = $personnage->dansStation;
        $produit = Produit::findOrFail($request->produit_id);
        $quantite = $request->quantite;

        // Vérifier que le produit est disponible dans cette station
        $marcheStation = MarcheStation::where('station_id', $station->id)
            ->where('produit_id', $produit->id)
            ->first();

        if (!$marcheStation) {
            return redirect()->back()
                ->with('error', 'Ce produit n\'est pas disponible dans cette station.');
        }

        // Vérifier disponibilité achat
        if (!$marcheStation->disponible_achat) {
            return redirect()->back()
                ->with('error', 'Ce produit n\'est pas disponible à l\'achat actuellement.');
        }

        // Vérifier le stock
        if ($marcheStation->stock_actuel < $quantite) {
            return redirect()->back()
                ->with('error', "Stock insuffisant. Stock disponible : {$marcheStation->stock_actuel}");
        }

        $vaisseau = $personnage->vaisseauActif;

        // Vérifier l'espace cargo
        if ($vaisseau->place_soute + ($quantite * $produit->volume) > $vaisseau->max_soutes) {
            $espaceDispo = $vaisseau->max_soutes - $vaisseau->place_soute;
            return redirect()->back()
                ->with('error', "Espace cargo insuffisant. Espace disponible : {$espaceDispo}");
        }

        // Calculer le prix total
        $prixUnitaire = $marcheStation->prix_achat_joueur;
        $prixTotal = $prixUnitaire * $quantite;

        // Vérifier les crédits (à implémenter selon votre système de crédits)
        // TODO: Ajouter vérification crédits personnage
        // if ($personnage->credits < $prixTotal) {
        //     return redirect()->back()->with('error', 'Crédits insuffisants.');
        // }

        DB::transaction(function () use ($marcheStation, $quantite, $vaisseau, $produit, $prixTotal, $personnage) {
            // Déduire du stock station
            $marcheStation->stock_actuel -= $quantite;
            $marcheStation->save();

            // Ajouter à la cargo du vaisseau
            $vaisseau->ajouterCargo($produit->id, $quantite);

            // Déduire crédits (TODO selon votre système)
            // $personnage->credits -= $prixTotal;
            // $personnage->save();
        });

        return redirect()->back()
            ->with('success', "Achat réussi : {$quantite}x {$produit->nom} pour {$prixTotal} crédits.");
    }

    /**
     * Vendre une marchandise
     */
    public function vendre(Request $request)
    {
        $personnage = Auth::user()->personnageActif;

        if (!$personnage || !$personnage->dans_station_id) {
            return redirect()->route('game.navire.timonerie')
                ->with('error', 'Vous devez être dans une station pour vendre.');
        }

        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1',
        ]);

        $station = $personnage->dansStation;
        $produit = Produit::findOrFail($request->produit_id);
        $quantite = $request->quantite;

        // Vérifier que le produit est accepté par cette station
        $marcheStation = MarcheStation::where('station_id', $station->id)
            ->where('produit_id', $produit->id)
            ->first();

        if (!$marcheStation) {
            return redirect()->back()
                ->with('error', 'Cette station n\'achète pas ce produit.');
        }

        // Vérifier disponibilité vente
        if (!$marcheStation->disponible_vente) {
            return redirect()->back()
                ->with('error', 'Cette station n\'achète pas ce produit actuellement.');
        }

        $vaisseau = $personnage->vaisseauActif;

        // Vérifier que le vaisseau a cette marchandise
        if (!$vaisseau->aCargo($produit->id, $quantite)) {
            return redirect()->back()
                ->with('error', 'Vous n\'avez pas assez de cette marchandise dans votre cargo.');
        }

        // Calculer le prix total
        $prixUnitaire = $marcheStation->prix_vente_joueur;
        $prixTotal = $prixUnitaire * $quantite;

        DB::transaction(function () use ($marcheStation, $quantite, $vaisseau, $produit, $prixTotal, $personnage) {
            // Retirer de la cargo du vaisseau
            $vaisseau->retirerCargo($produit->id, $quantite);

            // Ajouter au stock station
            $marcheStation->stock_actuel += $quantite;
            $marcheStation->save();

            // Ajouter crédits (TODO selon votre système)
            // $personnage->credits += $prixTotal;
            // $personnage->save();
        });

        return redirect()->back()
            ->with('success', "Vente réussie : {$quantite}x {$produit->nom} pour {$prixTotal} crédits.");
    }
}
