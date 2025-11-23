<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\HasInventaire;

class Usine extends Model
{
    use HasInventaire;

    protected $fillable = [
        'nom',
        'personnage_id',
        'localisation_type',
        'localisation_id',
        'type',
        'niveau',
        'energie_max',
        'energie_actuelle',
        'efficacite',
        'recette_en_cours_id',
        'quantite_en_cours',
        'production_debut',
        'production_fin',
        'actif',
    ];

    protected $casts = [
        'niveau' => 'integer',
        'energie_max' => 'integer',
        'energie_actuelle' => 'integer',
        'efficacite' => 'float',
        'quantite_en_cours' => 'integer',
        'production_debut' => 'datetime',
        'production_fin' => 'datetime',
        'actif' => 'boolean',
    ];

    /**
     * Proprietaire de l'usine
     */
    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    /**
     * Localisation (Planete ou Station)
     */
    public function localisation(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Recette en cours de production
     */
    public function recetteEnCours(): BelongsTo
    {
        return $this->belongsTo(Recette::class, 'recette_en_cours_id');
    }

    /**
     * Verifier si l'usine est en production
     */
    public function isEnProduction(): bool
    {
        return $this->recette_en_cours_id !== null && $this->production_fin !== null;
    }

    /**
     * Verifier si la production est terminee
     */
    public function isProductionTerminee(): bool
    {
        if (!$this->isEnProduction()) {
            return false;
        }

        return now()->gte($this->production_fin);
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getPourcentageProgression(): float
    {
        if (!$this->isEnProduction()) {
            return 0;
        }

        $debut = $this->production_debut;
        $fin = $this->production_fin;
        $maintenant = now();

        if ($maintenant->gte($fin)) {
            return 100;
        }

        $total = $fin->diffInSeconds($debut);
        $ecoule = $maintenant->diffInSeconds($debut);

        return round(($ecoule / $total) * 100, 1);
    }

    /**
     * Demarrer une production
     */
    public function demarrerProduction(Recette $recette, int $quantite = 1): array
    {
        if ($this->isEnProduction() && !$this->isProductionTerminee()) {
            return ['success' => false, 'message' => 'Production deja en cours'];
        }

        // Verifier energie
        $energie_requise = $recette->energie_requise * $quantite;
        if ($this->energie_actuelle < $energie_requise) {
            return [
                'success' => false,
                'message' => "Energie insuffisante. Requis: {$energie_requise} | Disponible: {$this->energie_actuelle}",
            ];
        }

        // Verifier niveau
        if ($this->niveau < $recette->niveau_requis) {
            return [
                'success' => false,
                'message' => "Niveau usine insuffisant. Requis: {$recette->niveau_requis} | Actuel: {$this->niveau}",
            ];
        }

        // Verifier ingredients dans l'inventaire de l'usine
        if (!$recette->peutFabriquer($this, $quantite)) {
            $manquants = $recette->getIngredientsManquants($this, $quantite);
            return [
                'success' => false,
                'message' => 'Ingredients insuffisants',
                'manquants' => $manquants,
            ];
        }

        // Consommer ingredients
        foreach ($recette->ingredients as $ingredient) {
            $qte = $ingredient['quantite'] * $quantite;
            $this->retirerRessource($ingredient['ressource_id'], $qte);
        }

        // Consommer energie
        $this->energie_actuelle -= $energie_requise;

        // Calculer temps avec efficacite
        $temps_total = ($recette->temps_fabrication * $quantite) / $this->efficacite;

        // Demarrer production
        $this->recette_en_cours_id = $recette->id;
        $this->quantite_en_cours = $quantite;
        $this->production_debut = now();
        $this->production_fin = now()->addSeconds($temps_total);
        $this->save();

        return [
            'success' => true,
            'message' => 'Production demarree',
            'fin_prevue' => $this->production_fin->format('H:i:s'),
            'duree' => $temps_total,
        ];
    }

    /**
     * Recuperer les produits finis
     */
    public function recupererProduction(): array
    {
        if (!$this->isProductionTerminee()) {
            if ($this->isEnProduction()) {
                $restant = now()->diffInSeconds($this->production_fin);
                return [
                    'success' => false,
                    'message' => "Production en cours. Termine dans {$restant}s",
                ];
            }
            return ['success' => false, 'message' => 'Aucune production en cours'];
        }

        $recette = $this->recetteEnCours;
        if (!$recette) {
            $this->resetProduction();
            return ['success' => false, 'message' => 'Recette introuvable'];
        }

        // Ajouter les produits a l'inventaire de l'usine
        $produits_ajoutes = [];
        foreach ($recette->produits as $produit) {
            $qte = $produit['quantite'] * $this->quantite_en_cours;
            $this->ajouterRessource($produit['ressource_id'], $qte);

            $ressource = Ressource::find($produit['ressource_id']);
            $produits_ajoutes[] = [
                'nom' => $ressource ? $ressource->nom : 'Inconnu',
                'code' => $ressource ? $ressource->code : '???',
                'quantite' => $qte,
            ];
        }

        $this->resetProduction();

        return [
            'success' => true,
            'message' => 'Production recuperee',
            'produits' => $produits_ajoutes,
        ];
    }

    /**
     * Reset l'etat de production
     */
    protected function resetProduction(): void
    {
        $this->recette_en_cours_id = null;
        $this->quantite_en_cours = 0;
        $this->production_debut = null;
        $this->production_fin = null;
        $this->save();
    }

    /**
     * Recharger l'energie (par exemple via ressources)
     */
    public function rechargerEnergie(int $quantite): void
    {
        $this->energie_actuelle = min($this->energie_max, $this->energie_actuelle + $quantite);
        $this->save();
    }

    /**
     * Ameliorer le niveau de l'usine
     */
    public function ameliorer(): array
    {
        // Cout d'amelioration (exemple simple)
        $cout = $this->niveau * 10000;

        $this->niveau++;
        $this->energie_max += 50;
        $this->efficacite += 0.1;
        $this->save();

        return [
            'success' => true,
            'nouveau_niveau' => $this->niveau,
            'energie_max' => $this->energie_max,
            'efficacite' => $this->efficacite,
        ];
    }
}
