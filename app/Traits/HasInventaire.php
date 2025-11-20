<?php

namespace App\Traits;

use App\Models\Inventaire;
use App\Models\Ressource;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasInventaire
{
    /**
     * Relation vers inventaires
     */
    public function inventaires(): MorphMany
    {
        return $this->morphMany(Inventaire::class, 'conteneur');
    }

    /**
     * Obtenir quantité d'une ressource
     */
    public function getQuantiteRessource(int|string $ressource_id): int
    {
        if (is_string($ressource_id)) {
            $ressource = Ressource::where('code', $ressource_id)->first();
            $ressource_id = $ressource?->id ?? 0;
        }

        $inventaire = $this->inventaires()
            ->where('ressource_id', $ressource_id)
            ->first();

        return $inventaire?->quantite ?? 0;
    }

    /**
     * Ajouter ressource à l'inventaire
     */
    public function ajouterRessource(int $ressource_id, int $quantite): bool
    {
        // Vérifier capacité si applicable
        if (method_exists($this, 'getCapaciteRestante')) {
            $ressource = Ressource::find($ressource_id);
            if (!$ressource) return false;

            $poids_total = $ressource->poids_unitaire * $quantite;

            if ($this->getCapaciteRestante() < $poids_total) {
                return false; // Pas assez de place
            }
        }

        $inventaire = $this->inventaires()->firstOrCreate(
            ['ressource_id' => $ressource_id],
            ['quantite' => 0]
        );

        $inventaire->increment('quantite', $quantite);

        return true;
    }

    /**
     * Retirer ressource de l'inventaire
     */
    public function retirerRessource(int $ressource_id, int $quantite): bool
    {
        $inventaire = $this->inventaires()
            ->where('ressource_id', $ressource_id)
            ->first();

        if (!$inventaire || $inventaire->quantite < $quantite) {
            return false; // Pas assez
        }

        $inventaire->decrement('quantite', $quantite);

        // Supprimer si quantité = 0
        if ($inventaire->quantite <= 0) {
            $inventaire->delete();
        }

        return true;
    }

    /**
     * Obtenir poids total de l'inventaire
     */
    public function getPoidsInventaire(): float
    {
        return $this->inventaires()
            ->with('ressource')
            ->get()
            ->sum(function ($inv) {
                return $inv->quantite * $inv->ressource->poids_unitaire;
            });
    }

    /**
     * Obtenir valeur totale de l'inventaire
     */
    public function getValeurInventaire(): float
    {
        return $this->inventaires()
            ->with('ressource')
            ->get()
            ->sum(function ($inv) {
                return $inv->quantite * $inv->ressource->prix_base;
            });
    }

    /**
     * Transférer ressource vers un autre conteneur
     */
    public function transfererRessource(int $ressource_id, int $quantite, $destination): bool
    {
        // Vérifier qu'on a assez
        if ($this->getQuantiteRessource($ressource_id) < $quantite) {
            return false;
        }

        // Vérifier que destination peut recevoir
        if (!method_exists($destination, 'ajouterRessource')) {
            return false;
        }

        // Retirer de source et ajouter à destination
        if ($this->retirerRessource($ressource_id, $quantite)) {
            if ($destination->ajouterRessource($ressource_id, $quantite)) {
                return true;
            }
            // Rollback si échec
            $this->ajouterRessource($ressource_id, $quantite);
        }

        return false;
    }

    /**
     * Lister inventaire complet
     */
    public function listerInventaire(): array
    {
        return $this->inventaires()
            ->with('ressource')
            ->get()
            ->map(function ($inv) {
                return [
                    'ressource_id' => $inv->ressource_id,
                    'code' => $inv->ressource->code,
                    'nom' => $inv->ressource->nom,
                    'quantite' => $inv->quantite,
                    'poids' => $inv->quantite * $inv->ressource->poids_unitaire,
                    'valeur' => $inv->quantite * $inv->ressource->prix_base,
                ];
            })
            ->toArray();
    }
}
