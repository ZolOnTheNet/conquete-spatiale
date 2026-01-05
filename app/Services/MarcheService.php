<?php

namespace App\Services;

use App\Models\Station;
use App\Models\Produit;
use App\Models\MarcheStation;

/**
 * Service de gestion du marché des stations
 */
class MarcheService
{
    /**
     * Calculer le prix dynamique selon l'offre/demande
     *
     * @param MarcheStation $marche
     * @param string $type 'achat' ou 'vente'
     * @return float Prix calculé
     */
    public function calculerPrix(MarcheStation $marche, string $type = 'achat'): float
    {
        $produit = $marche->produit;
        $prixBase = $produit->prix_base;

        // Ratio stock actuel / stock max
        $ratio = $marche->stock_max > 0
            ? $marche->stock_actuel / $marche->stock_max
            : 0.5;

        // Modificateur selon type économique
        $modType = match ($marche->type_economique) {
            'producteur' => -0.1,    // Vend moins cher
            'consommateur' => 0.1,   // Achète plus cher
            default => 0,
        };

        if ($type === 'achat') {
            // Prix d'achat joueur (ce que le joueur paye)
            // Plus le stock est bas, plus c'est cher
            $modificateur = 1 + (1 - $ratio) * 0.5 + $modType;
        } else {
            // Prix de vente joueur (ce que le joueur reçoit)
            // Plus le stock est haut, moins on paye
            $modificateur = 1 - $ratio * 0.3 - $modType;
        }

        return max(1, round($prixBase * $modificateur, 2));
    }

    /**
     * Mettre à jour tous les prix d'une station
     *
     * @param Station $station
     * @return int Nombre de produits mis à jour
     */
    public function mettreAJourPrixStation(Station $station): int
    {
        $count = 0;

        foreach ($station->marches as $marche) {
            $marche->prix_achat_joueur = $this->calculerPrix($marche, 'achat');
            $marche->prix_vente_joueur = $this->calculerPrix($marche, 'vente');
            $marche->derniere_mise_a_jour_prix = now();
            $marche->save();
            $count++;
        }

        return $count;
    }

    /**
     * Simuler la production/consommation journalière
     *
     * @param Station $station
     * @return array Résumé des changements
     */
    public function simulerEconomieJournaliere(Station $station): array
    {
        $changements = [];

        foreach ($station->marches as $marche) {
            $avant = $marche->stock_actuel;

            // Production
            if ($marche->production_par_jour > 0) {
                $marche->stock_actuel = min(
                    $marche->stock_max,
                    $marche->stock_actuel + $marche->production_par_jour
                );
            }

            // Consommation
            if ($marche->consommation_par_jour > 0) {
                $marche->stock_actuel = max(
                    0,
                    $marche->stock_actuel - $marche->consommation_par_jour
                );
            }

            if ($marche->stock_actuel !== $avant) {
                $marche->save();
                $changements[] = [
                    'produit' => $marche->produit->nom ?? 'Unknown',
                    'avant' => $avant,
                    'apres' => $marche->stock_actuel,
                    'delta' => $marche->stock_actuel - $avant,
                ];
            }
        }

        // Recalculer les prix après changement de stock
        $this->mettreAJourPrixStation($station);

        return $changements;
    }

    /**
     * Ajouter un produit au marché d'une station
     *
     * @param Station $station
     * @param Produit $produit
     * @param array $options Configuration du marché
     * @return MarcheStation
     */
    public function ajouterProduit(Station $station, Produit $produit, array $options = []): MarcheStation
    {
        $defaults = [
            'stock_actuel' => 100,
            'stock_min' => 0,
            'stock_max' => 1000,
            'production_par_jour' => 0,
            'consommation_par_jour' => 0,
            'type_economique' => 'equilibre',
            'disponible_achat' => true,
            'disponible_vente' => true,
        ];

        $data = array_merge($defaults, $options, [
            'station_id' => $station->id,
            'produit_id' => $produit->id,
        ]);

        $marche = MarcheStation::create($data);

        // Calculer les prix initiaux
        $marche->prix_achat_joueur = $this->calculerPrix($marche, 'achat');
        $marche->prix_vente_joueur = $this->calculerPrix($marche, 'vente');
        $marche->derniere_mise_a_jour_prix = now();
        $marche->save();

        return $marche;
    }

    /**
     * Obtenir les meilleurs prix pour un produit
     *
     * @param int $produitId
     * @param string $type 'achat' ou 'vente'
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getMeilleursPrix(int $produitId, string $type = 'achat', int $limit = 10)
    {
        $query = MarcheStation::where('produit_id', $produitId)
            ->where($type === 'achat' ? 'disponible_achat' : 'disponible_vente', true)
            ->with(['station.systemeStellaire']);

        if ($type === 'achat') {
            // Pour achat, on veut les prix les plus bas
            $query->where('stock_actuel', '>', 0)
                ->orderBy('prix_achat_joueur', 'asc');
        } else {
            // Pour vente, on veut les prix les plus hauts
            $query->orderBy('prix_vente_joueur', 'desc');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Vérifier si un achat est possible
     *
     * @param MarcheStation $marche
     * @param int $quantite
     * @return array ['possible' => bool, 'raison' => string|null]
     */
    public function verifierAchat(MarcheStation $marche, int $quantite): array
    {
        if (!$marche->disponible_achat) {
            return ['possible' => false, 'raison' => 'Produit non disponible à l\'achat'];
        }

        if ($marche->stock_actuel < $quantite) {
            return ['possible' => false, 'raison' => 'Stock insuffisant'];
        }

        return ['possible' => true, 'raison' => null];
    }

    /**
     * Vérifier si une vente est possible
     *
     * @param MarcheStation $marche
     * @param int $quantite
     * @return array ['possible' => bool, 'raison' => string|null]
     */
    public function verifierVente(MarcheStation $marche, int $quantite): array
    {
        if (!$marche->disponible_vente) {
            return ['possible' => false, 'raison' => 'La station n\'achète pas ce produit'];
        }

        $espaceRestant = $marche->stock_max - $marche->stock_actuel;
        if ($espaceRestant < $quantite) {
            return ['possible' => false, 'raison' => 'La station n\'a plus de place en stock'];
        }

        return ['possible' => true, 'raison' => null];
    }
}
