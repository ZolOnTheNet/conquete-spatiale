<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marche extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'localisation_type',
        'localisation_id',
        'multiplicateur_achat',
        'multiplicateur_vente',
        'taxe',
        'actif',
        'description',
    ];

    protected $casts = [
        'multiplicateur_achat' => 'float',
        'multiplicateur_vente' => 'float',
        'taxe' => 'float',
        'actif' => 'boolean',
    ];

    /**
     * Localisation du marché (Planete ou Station)
     */
    public function localisation(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Prix des ressources sur ce marché
     */
    public function prix(): HasMany
    {
        return $this->hasMany(PrixMarche::class);
    }

    /**
     * Obtenir le prix d'achat d'une ressource (joueur achète)
     */
    public function getPrixAchat(int $ressource_id): ?float
    {
        $prixMarche = $this->prix()->where('ressource_id', $ressource_id)->first();

        if (!$prixMarche) {
            // Utiliser le prix de base de la ressource
            $ressource = Ressource::find($ressource_id);
            if (!$ressource) return null;

            return $ressource->prix_base * $this->multiplicateur_achat * (1 + $this->taxe);
        }

        return $prixMarche->prix_achat * $this->multiplicateur_achat * (1 + $this->taxe);
    }

    /**
     * Obtenir le prix de vente d'une ressource (joueur vend)
     */
    public function getPrixVente(int $ressource_id): ?float
    {
        $prixMarche = $this->prix()->where('ressource_id', $ressource_id)->first();

        if (!$prixMarche) {
            // Utiliser le prix de base de la ressource
            $ressource = Ressource::find($ressource_id);
            if (!$ressource) return null;

            return $ressource->prix_base * $this->multiplicateur_vente * (1 - $this->taxe);
        }

        return $prixMarche->prix_vente * $this->multiplicateur_vente * (1 - $this->taxe);
    }

    /**
     * Obtenir le stock disponible d'une ressource
     */
    public function getStock(int $ressource_id): int
    {
        $prixMarche = $this->prix()->where('ressource_id', $ressource_id)->first();
        return $prixMarche ? $prixMarche->stock : 0;
    }

    /**
     * Vérifier si le marché peut vendre une quantité
     */
    public function peutVendre(int $ressource_id, int $quantite): bool
    {
        return $this->getStock($ressource_id) >= $quantite;
    }

    /**
     * Acheter des ressources (joueur achète au marché)
     */
    public function acheter(int $ressource_id, int $quantite): array
    {
        if (!$this->actif) {
            return ['success' => false, 'message' => 'Ce marché est fermé.'];
        }

        $prixMarche = $this->prix()->where('ressource_id', $ressource_id)->first();

        if (!$prixMarche || $prixMarche->stock < $quantite) {
            return ['success' => false, 'message' => 'Stock insuffisant.'];
        }

        $prixTotal = $this->getPrixAchat($ressource_id) * $quantite;

        // Réduire le stock
        $prixMarche->stock -= $quantite;

        // Augmenter légèrement le prix (offre/demande)
        $prixMarche->prix_achat *= (1 + 0.001 * $quantite);
        $prixMarche->prix_vente *= (1 + 0.001 * $quantite);
        $prixMarche->save();

        return [
            'success' => true,
            'prix_total' => round($prixTotal, 2),
            'quantite' => $quantite,
        ];
    }

    /**
     * Vendre des ressources (joueur vend au marché)
     */
    public function vendre(int $ressource_id, int $quantite): array
    {
        if (!$this->actif) {
            return ['success' => false, 'message' => 'Ce marché est fermé.'];
        }

        $prixMarche = $this->prix()->where('ressource_id', $ressource_id)->first();

        if (!$prixMarche) {
            // Créer une entrée si elle n'existe pas
            $ressource = Ressource::find($ressource_id);
            if (!$ressource) {
                return ['success' => false, 'message' => 'Ressource inconnue.'];
            }

            $prixMarche = $this->prix()->create([
                'ressource_id' => $ressource_id,
                'prix_achat' => $ressource->prix_base,
                'prix_vente' => $ressource->prix_base * 0.9,
                'stock' => 0,
                'stock_max' => 10000,
            ]);
        }

        // Vérifier capacité de stockage
        if ($prixMarche->stock + $quantite > $prixMarche->stock_max) {
            return ['success' => false, 'message' => 'Le marché ne peut pas stocker autant.'];
        }

        $prixTotal = $this->getPrixVente($ressource_id) * $quantite;

        // Augmenter le stock
        $prixMarche->stock += $quantite;

        // Diminuer légèrement le prix (offre/demande)
        $prixMarche->prix_achat *= (1 - 0.001 * $quantite);
        $prixMarche->prix_vente *= (1 - 0.001 * $quantite);

        // Plancher minimum
        $ressource = Ressource::find($ressource_id);
        $prixMarche->prix_achat = max($prixMarche->prix_achat, $ressource->prix_base * 0.5);
        $prixMarche->prix_vente = max($prixMarche->prix_vente, $ressource->prix_base * 0.4);

        $prixMarche->save();

        return [
            'success' => true,
            'prix_total' => round($prixTotal, 2),
            'quantite' => $quantite,
        ];
    }

    /**
     * Lister les ressources disponibles
     */
    public function listerRessources(): array
    {
        $liste = [];

        foreach ($this->prix()->with('ressource')->get() as $prixMarche) {
            $liste[] = [
                'ressource_id' => $prixMarche->ressource_id,
                'code' => $prixMarche->ressource->code,
                'nom' => $prixMarche->ressource->nom,
                'prix_achat' => round($this->getPrixAchat($prixMarche->ressource_id), 2),
                'prix_vente' => round($this->getPrixVente($prixMarche->ressource_id), 2),
                'stock' => $prixMarche->stock,
            ];
        }

        return $liste;
    }

    /**
     * Est-ce un marché noir ?
     */
    public function isContrebande(): bool
    {
        return $this->type === 'contrebande';
    }
}
