<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mine extends Model
{
    protected $fillable = [
        'nom',
        'planete_id',
        'gisement_id',
        'emplacement',
        'orbite_rayon_ua',
        'orbite_angle',
        'installateur_id',
        'proprietaire_id',
        'installe_a',
        'modele',
        'capacite_stockage',
        'stock_actuel',
        'taux_extraction',
        'statut',
        'niveau_usure',
        'derniere_maintenance',
        'derniere_extraction',
        'energie_consommee',
        'pieces_rechange_consommees',
        'pieces_usure_consommees',
        'stock_energie',
        'stock_pieces_rechange',
        'stock_pieces_usure',
        'acces_public',
        'autorises_ids',
        'acces_faction',
        'faction_id',
        'base_id',
        'connectee_base',
        'prix_achat',
        'valeur_estimee',
        'poi_connu',
        'detectabilite_base',
        'description',
    ];

    protected $casts = [
        'orbite_rayon_ua' => 'decimal:6',
        'orbite_angle' => 'decimal:4',
        'taux_extraction' => 'decimal:2',
        'detectabilite_base' => 'decimal:2',
        'acces_public' => 'boolean',
        'acces_faction' => 'boolean',
        'connectee_base' => 'boolean',
        'poi_connu' => 'boolean',
        'autorises_ids' => 'array',
        'installe_a' => 'datetime',
        'derniere_maintenance' => 'datetime',
        'derniere_extraction' => 'datetime',
    ];

    // ========== RELATIONS ==========

    /**
     * Planète où se trouve la mine
     */
    public function planete(): BelongsTo
    {
        return $this->belongsTo(Planete::class);
    }

    /**
     * Gisement exploité par la mine
     */
    public function gisement(): BelongsTo
    {
        return $this->belongsTo(Gisement::class);
    }

    /**
     * Personnage qui a installé la mine
     */
    public function installateur(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'installateur_id');
    }

    /**
     * Propriétaire actuel de la mine
     */
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'proprietaire_id');
    }

    /**
     * Faction associée (si applicable)
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * Base connectée (optionnel)
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    // ========== MÉTHODES MÉTIER ==========

    /**
     * Vérifier si un personnage peut accéder à la mine
     */
    public function peutAcceder(Personnage $personnage): bool
    {
        // Le propriétaire a toujours accès
        if ($this->proprietaire_id === $personnage->id) {
            return true;
        }

        // Accès public
        if ($this->acces_public) {
            return true;
        }

        // Accès faction
        if ($this->acces_faction && $this->faction_id && $personnage->faction_id === $this->faction_id) {
            return true;
        }

        // Liste des autorisés
        if ($this->autorises_ids && in_array($personnage->id, $this->autorises_ids)) {
            return true;
        }

        return false;
    }

    /**
     * Autoriser un personnage à accéder à la mine
     */
    public function autoriserAcces(Personnage $personnage): void
    {
        $autorises = $this->autorises_ids ?? [];
        if (!in_array($personnage->id, $autorises)) {
            $autorises[] = $personnage->id;
            $this->autorises_ids = $autorises;
            $this->save();
        }
    }

    /**
     * Révoquer l'accès d'un personnage
     */
    public function revoquerAcces(Personnage $personnage): void
    {
        $autorises = $this->autorises_ids ?? [];
        $this->autorises_ids = array_values(array_diff($autorises, [$personnage->id]));
        $this->save();
    }

    /**
     * Vérifier si la mine peut fonctionner (a les ressources nécessaires)
     */
    public function peutFonctionner(): bool
    {
        if ($this->statut !== 'active') {
            return false;
        }

        // Vérifier si la mine a assez d'énergie pour aujourd'hui
        if ($this->stock_energie < $this->energie_consommee) {
            return false;
        }

        // La mine peut fonctionner
        return true;
    }

    /**
     * Calculer la production actuelle (ajustée par l'usure)
     */
    public function getProductionActuelle(): float
    {
        if (!$this->peutFonctionner()) {
            return 0.0;
        }

        // La production diminue avec l'usure
        $facteurUsure = 1.0 - ($this->niveau_usure / 200); // Max -50% à 100% d'usure
        return $this->taux_extraction * max(0.5, $facteurUsure);
    }

    /**
     * Extraire des ressources (appelé par le système temporel)
     */
    public function extraire(float $tempsPasse): array
    {
        if (!$this->peutFonctionner()) {
            return [
                'success' => false,
                'message' => "La mine {$this->nom} ne peut pas fonctionner.",
                'raison' => $this->statut !== 'active' ? 'inactive' : 'manque_energie',
            ];
        }

        // Calculer la quantité extraite
        $quantiteExtraite = $this->getProductionActuelle() * $tempsPasse;

        // Vérifier si le gisement a assez de ressources
        if ($this->gisement->quantite_restante < $quantiteExtraite) {
            $quantiteExtraite = $this->gisement->quantite_restante;
        }

        // Vérifier la capacité de stockage
        $espaceDispo = $this->capacite_stockage - $this->stock_actuel;
        if ($quantiteExtraite > $espaceDispo) {
            $quantiteExtraite = $espaceDispo;
        }

        if ($quantiteExtraite <= 0) {
            return [
                'success' => false,
                'message' => "La mine {$this->nom} est pleine ou le gisement est épuisé.",
            ];
        }

        // Effectuer l'extraction
        $this->gisement->quantite_restante -= $quantiteExtraite;
        $this->gisement->save();

        $this->stock_actuel += $quantiteExtraite;
        $this->derniere_extraction = now();

        // Consommer de l'énergie
        $this->stock_energie -= $this->energie_consommee * $tempsPasse;

        // Augmenter l'usure
        $this->niveau_usure += 0.1 * $tempsPasse; // 0.1% par jour
        if ($this->niveau_usure >= 100) {
            $this->statut = 'maintenance';
        }

        $this->save();

        return [
            'success' => true,
            'quantite' => $quantiteExtraite,
            'ressource' => $this->gisement->ressource->nom,
            'stock_actuel' => $this->stock_actuel,
        ];
    }

    /**
     * Récupérer des ressources depuis la mine
     */
    public function recupererRessources(int $quantite, Personnage $personnage): array
    {
        if (!$this->peutAcceder($personnage)) {
            return [
                'success' => false,
                'message' => "Vous n'avez pas l'autorisation d'accéder à cette mine.",
            ];
        }

        if ($quantite > $this->stock_actuel) {
            return [
                'success' => false,
                'message' => "La mine ne contient que {$this->stock_actuel} unités.",
            ];
        }

        $this->stock_actuel -= $quantite;
        $this->save();

        return [
            'success' => true,
            'quantite' => $quantite,
            'ressource' => $this->gisement->ressource->nom,
            'stock_restant' => $this->stock_actuel,
        ];
    }

    /**
     * Effectuer la maintenance de la mine
     */
    public function effectuerMaintenance(): array
    {
        if ($this->stock_pieces_rechange < 1 || $this->stock_pieces_usure < 5) {
            return [
                'success' => false,
                'message' => "Pièces insuffisantes pour la maintenance (besoin: 1 pièce de rechange, 5 pièces d'usure).",
            ];
        }

        // Consommer les pièces
        $this->stock_pieces_rechange -= 1;
        $this->stock_pieces_usure -= 5;

        // Réinitialiser l'usure
        $this->niveau_usure = 0;
        $this->statut = 'active';
        $this->derniere_maintenance = now();
        $this->save();

        return [
            'success' => true,
            'message' => "Maintenance effectuée avec succès sur {$this->nom}.",
        ];
    }

    /**
     * Ravitailler la mine en consommables
     */
    public function ravitailler(int $energie = 0, int $piecesRechange = 0, int $piecesUsure = 0): void
    {
        $this->stock_energie += $energie;
        $this->stock_pieces_rechange += $piecesRechange;
        $this->stock_pieces_usure += $piecesUsure;
        $this->save();
    }

    /**
     * Vendre la mine à un autre personnage
     */
    public function vendre(Personnage $nouveauProprietaire, int $prix): array
    {
        $this->proprietaire_id = $nouveauProprietaire->id;
        $this->prix_achat = $prix;
        $this->save();

        return [
            'success' => true,
            'message' => "Mine {$this->nom} vendue à {$nouveauProprietaire->nom} pour {$prix} crédits.",
        ];
    }

    /**
     * Obtenir le nom complet avec ressource
     */
    public function getNomCompletAttribute(): string
    {
        $ressource = $this->gisement->ressource->nom ?? 'Inconnu';
        return "{$this->nom} ({$ressource})";
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatutFormate(): string
    {
        $statuts = [
            'active' => '🟢 Active',
            'inactive' => '⚫ Inactive',
            'maintenance' => '🟡 Maintenance requise',
            'endommagee' => '🔴 Endommagée',
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }
}
