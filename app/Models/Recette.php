<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recette extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'temps_fabrication',
        'niveau_requis',
        'energie_requise',
        'ingredients',
        'produits',
        'description',
        'actif',
    ];

    protected $casts = [
        'ingredients' => 'array',
        'produits' => 'array',
        'temps_fabrication' => 'integer',
        'niveau_requis' => 'integer',
        'energie_requise' => 'integer',
        'actif' => 'boolean',
    ];

    /**
     * Obtenir les ingredients avec details ressources
     */
    public function getIngredientsDetails(): array
    {
        $details = [];

        foreach ($this->ingredients as $ingredient) {
            $ressource = Ressource::find($ingredient['ressource_id']);
            if ($ressource) {
                $details[] = [
                    'ressource_id' => $ressource->id,
                    'code' => $ressource->code,
                    'nom' => $ressource->nom,
                    'quantite' => $ingredient['quantite'],
                ];
            }
        }

        return $details;
    }

    /**
     * Obtenir les produits avec details ressources
     */
    public function getProduitsDetails(): array
    {
        $details = [];

        foreach ($this->produits as $produit) {
            $ressource = Ressource::find($produit['ressource_id']);
            if ($ressource) {
                $details[] = [
                    'ressource_id' => $ressource->id,
                    'code' => $ressource->code,
                    'nom' => $ressource->nom,
                    'quantite' => $produit['quantite'],
                ];
            }
        }

        return $details;
    }

    /**
     * Verifier si un inventaire a les ingredients necessaires
     */
    public function peutFabriquer($inventoryOwner, int $multiplicateur = 1): bool
    {
        foreach ($this->ingredients as $ingredient) {
            $quantite_requise = $ingredient['quantite'] * $multiplicateur;
            $quantite_dispo = $inventoryOwner->getQuantiteRessource($ingredient['ressource_id']);

            if ($quantite_dispo < $quantite_requise) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtenir les ingredients manquants
     */
    public function getIngredientsManquants($inventoryOwner, int $multiplicateur = 1): array
    {
        $manquants = [];

        foreach ($this->ingredients as $ingredient) {
            $quantite_requise = $ingredient['quantite'] * $multiplicateur;
            $quantite_dispo = $inventoryOwner->getQuantiteRessource($ingredient['ressource_id']);

            if ($quantite_dispo < $quantite_requise) {
                $ressource = Ressource::find($ingredient['ressource_id']);
                $manquants[] = [
                    'ressource_id' => $ingredient['ressource_id'],
                    'code' => $ressource ? $ressource->code : '???',
                    'nom' => $ressource ? $ressource->nom : 'Inconnu',
                    'requis' => $quantite_requise,
                    'disponible' => $quantite_dispo,
                    'manquant' => $quantite_requise - $quantite_dispo,
                ];
            }
        }

        return $manquants;
    }

    /**
     * Fabriquer la recette (consommer ingredients, produire resultats)
     */
    public function fabriquer($inventoryOwner, int $multiplicateur = 1): array
    {
        if (!$this->peutFabriquer($inventoryOwner, $multiplicateur)) {
            $manquants = $this->getIngredientsManquants($inventoryOwner, $multiplicateur);
            return [
                'success' => false,
                'message' => 'Ingredients insuffisants',
                'manquants' => $manquants,
            ];
        }

        // Retirer les ingredients
        foreach ($this->ingredients as $ingredient) {
            $quantite = $ingredient['quantite'] * $multiplicateur;
            $inventoryOwner->retirerRessource($ingredient['ressource_id'], $quantite);
        }

        // Ajouter les produits
        $produits_ajoutes = [];
        foreach ($this->produits as $produit) {
            $quantite = $produit['quantite'] * $multiplicateur;
            $inventoryOwner->ajouterRessource($produit['ressource_id'], $quantite);

            $ressource = Ressource::find($produit['ressource_id']);
            $produits_ajoutes[] = [
                'nom' => $ressource ? $ressource->nom : 'Inconnu',
                'code' => $ressource ? $ressource->code : '???',
                'quantite' => $quantite,
            ];
        }

        return [
            'success' => true,
            'produits' => $produits_ajoutes,
            'temps' => $this->temps_fabrication * $multiplicateur,
        ];
    }

    /**
     * Calculer le poids total des ingredients
     */
    public function getPoidsIngredients(): float
    {
        $poids = 0;

        foreach ($this->ingredients as $ingredient) {
            $ressource = Ressource::find($ingredient['ressource_id']);
            if ($ressource) {
                $poids += $ressource->poids_unitaire * $ingredient['quantite'];
            }
        }

        return $poids;
    }

    /**
     * Calculer le poids total des produits
     */
    public function getPoidsProduits(): float
    {
        $poids = 0;

        foreach ($this->produits as $produit) {
            $ressource = Ressource::find($produit['ressource_id']);
            if ($ressource) {
                $poids += $ressource->poids_unitaire * $produit['quantite'];
            }
        }

        return $poids;
    }
}
