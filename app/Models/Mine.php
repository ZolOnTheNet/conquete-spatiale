<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Detectable;

class Mine extends Model
{
    use Detectable;
    protected $fillable = [
        'objet_spatial_id',
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

    /**
     * Stations associées à cette mine (à proximité)
     */
    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'mine_station')
            ->withPivot('distance_km')
            ->withTimestamps();
    }

    /**
     * Objet spatial associé (position 3D complète)
     *
     * IMPORTANT : Depuis migration 2025_12_31_140001, chaque Mine
     * hérite d'un ObjetSpatial pour gérer sa position 3D
     *
     * @see ObjetSpatial
     */
    public function objetSpatial(): BelongsTo
    {
        return $this->belongsTo(ObjetSpatial::class, 'objet_spatial_id');
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

    /**
     * Obtenir la position complète de la mine
     *
     * PRIORITÉ : Si objet_spatial_id existe, utiliser la position de l'ObjetSpatial
     * SINON : Fallback sur l'ancienne méthode (legacy)
     */
    public function getPosition(): array
    {
        // Nouvelle méthode : Utiliser ObjetSpatial
        if ($this->objet_spatial_id && $this->objetSpatial) {
            return $this->objetSpatial->getPosition();
        }

        // Fallback legacy (pour mines non migrées)
        $planete = $this->planete;
        if (!$planete) {
            return [
                'secteur_x' => 0,
                'secteur_y' => 0,
                'secteur_z' => 0,
                'position_x' => 0,
                'position_y' => 0,
                'position_z' => 0,
            ];
        }

        $systeme = $planete->systemeStellaire;
        if (!$systeme) {
            return [
                'secteur_x' => 0,
                'secteur_y' => 0,
                'secteur_z' => 0,
                'position_x' => 0,
                'position_y' => 0,
                'position_z' => 0,
            ];
        }

        // Position de la planète
        $posPlanete = $planete->getPositionOrbitale();

        return [
            'secteur_x' => $systeme->secteur_x,
            'secteur_y' => $systeme->secteur_y,
            'secteur_z' => $systeme->secteur_z,
            'position_x' => (int)$posPlanete['x'],
            'position_y' => (int)$posPlanete['y'],
            'position_z' => (int)$posPlanete['z'],
        ];
    }

    /**
     * Obtenir la position orbitale précise de la mine
     * Utilisé par ObjetSpatial.getPositionEffective()
     *
     * Pour les mines, la position est généralement celle de la planète
     * (posées au sol) ou celle de la base (attachées)
     *
     * @param float|null $timestampJours Timestamp du jeu en jours
     * @return array Position absolue [secteur_x/y/z, position_x/y/z]
     */
    public function getPositionOrbitale(?float $timestampJours = null): array
    {
        // Cas 1 : Mine attachée à une base
        if ($this->base_id) {
            $base = $this->base;
            if ($base && $base->objetSpatial) {
                $posBase = $base->objetSpatial->getPositionEffective($timestampJours);
                return [
                    'secteur_x' => $posBase['secteur_x'] ?? 0,
                    'secteur_y' => $posBase['secteur_y'] ?? 0,
                    'secteur_z' => $posBase['secteur_z'] ?? 0,
                    'position_x' => $posBase['position_x'] ?? 0,
                    'position_y' => $posBase['position_y'] ?? 0,
                    'position_z' => $posBase['position_z'] ?? 0,
                ];
            }
        }

        // Cas 2 : Mine sur planète (standard)
        if ($this->planete_id) {
            $planete = $this->planete;
            if ($planete) {
                $posPlanete = $planete->getPositionOrbitale($timestampJours);
                $systeme = $planete->systemeStellaire;

                return [
                    'secteur_x' => $systeme->secteur_x ?? 0,
                    'secteur_y' => $systeme->secteur_y ?? 0,
                    'secteur_z' => $systeme->secteur_z ?? 0,
                    'position_x' => (int)$posPlanete['x'],
                    'position_y' => (int)$posPlanete['y'],
                    'position_z' => (int)$posPlanete['z'],
                ];
            }
        }

        // Fallback : position nulle
        return [
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'position_x' => 0,
            'position_y' => 0,
            'position_z' => 0,
        ];
    }

    // === SYSTÈME DE DÉTECTION ===

    /**
     * Calcule le score de détection de la mine (RÈGLE INTRA-SECTEUR)
     *
     * PRIORITÉ : Si objet_spatial_id existe, déléguer à ObjetSpatial
     * SINON : Fallback sur l'ancienne méthode (legacy)
     *
     * @param int $fromSecteurX Secteur X du scanner (AL)
     * @param int $fromSecteurY Secteur Y du scanner (AL)
     * @param int $fromSecteurZ Secteur Z du scanner (AL)
     * @param int $fromPositionX Position X du scanner (cUA)
     * @param int $fromPositionY Position Y du scanner (cUA)
     * @param int $fromPositionZ Position Z du scanner (cUA)
     * @return float Score de détection requis
     */
    public function getScoreDetection(
        int $fromSecteurX,
        int $fromSecteurY,
        int $fromSecteurZ,
        int $fromPositionX = 0,
        int $fromPositionY = 0,
        int $fromPositionZ = 0
    ): float {
        // Nouvelle méthode : Déléguer à ObjetSpatial
        if ($this->objet_spatial_id && $this->objetSpatial) {
            return $this->objetSpatial->getScoreDetection(
                $fromSecteurX,
                $fromSecteurY,
                $fromSecteurZ,
                $fromPositionX,
                $fromPositionY,
                $fromPositionZ
            );
        }

        // Fallback legacy (pour mines non migrées)
        // Si déjà connue, seuil de détection = 0 (apparaît automatiquement)
        if ($this->poi_connu) {
            return 0;
        }

        // Les mines sont en orbite d'une planète, donc on utilise la position de la planète
        $planete = $this->planete;
        if (!$planete) {
            return 999999; // Mine orpheline, impossible à détecter
        }

        $systeme = $planete->systemeStellaire;
        if (!$systeme) {
            return 999999; // Planète orpheline, impossible à détecter
        }

        // RÈGLE INTRA-SECTEUR: Vérifier si même secteur
        if ($systeme->secteur_x !== $fromSecteurX ||
            $systeme->secteur_y !== $fromSecteurY ||
            $systeme->secteur_z !== $fromSecteurZ) {
            // Pas dans le même secteur → non détectable en local
            return 999999;
        }

        // IMPORTANT: Utiliser cache_position de la planète si disponible, sinon position du système
        $planeteX = $planete->cache_position_x ?? $systeme->position_x;
        $planeteY = $planete->cache_position_y ?? $systeme->position_y;
        $planeteZ = $planete->cache_position_z ?? $systeme->position_z;

        // Distance calculée UNIQUEMENT avec positions (cUA) - secteurs ignorés
        $dx = $planeteX - $fromPositionX;
        $dy = $planeteY - $fromPositionY;
        $dz = $planeteZ - $fromPositionZ;

        $distance_cUA = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        // Si detectabilite_base n'est pas initialisé (0 ou -1), calculer
        $detectabilite = $this->detectabilite_base;
        if ($detectabilite <= 0) {
            $detectabilite = $this->calculerDetectabilite();
        }

        // Formule INTRA-SECTEUR: (distance_cUA / 1000) × detectabilite_base
        return ($distance_cUA / 1000) * $detectabilite;
    }

    /**
     * Calcule la détectabilité de base si non initialisée
     * Formule: 100 - (taux_extraction × 10) - (capacite_stockage / 100)
     * Les mines actives et grandes sont plus faciles à détecter
     */
    public function calculerDetectabilite(): float
    {
        $taux = $this->taux_extraction ?? 1.0;
        $capacite = $this->capacite_stockage ?? 100;

        // Base de 100, réduit par l'activité et la taille
        $detectabilite = 100 - ($taux * 10) - ($capacite / 100);

        // Les mines inactives sont plus difficiles à détecter
        if ($this->statut !== 'active') {
            $detectabilite += 20;
        }

        return max(10, $detectabilite); // Minimum 10
    }

    /**
     * Marque la mine comme découverte (poi_connu = true)
     */
    public function marquerDecouvert(): void
    {
        $this->poi_connu = true;
        $this->save();
    }
}
