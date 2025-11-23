<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marche;
use App\Models\PrixMarche;
use App\Models\Ressource;
use App\Models\Planete;
use App\Models\SystemeStellaire;

class MarcheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating markets...');

        // Créer des marchés sur les planètes habitables ou importantes
        $this->creerMarchePrincipal();
        $this->creerMarchesSecondaires();

        $this->command->info('Markets created successfully');
    }

    /**
     * Créer le marché principal (Sol - Terre)
     */
    protected function creerMarchePrincipal(): void
    {
        // Trouver le système Sol
        $sol = SystemeStellaire::where('nom', 'Sol')->first();

        if (!$sol) {
            $this->command->warn('System Sol not found, skipping main market');
            return;
        }

        // Trouver ou créer une planète habitable
        $terre = $sol->planetes()->where('nom', 'like', '%3%')->first()
            ?? $sol->planetes()->first();

        if (!$terre) {
            $this->command->warn('No planet found in Sol system');
            return;
        }

        // Créer le marché central
        $marche = Marche::create([
            'nom' => 'Bourse Terrienne Centrale',
            'type' => 'commercial',
            'localisation_type' => Planete::class,
            'localisation_id' => $terre->id,
            'multiplicateur_achat' => 1.0,
            'multiplicateur_vente' => 0.85,
            'taxe' => 0.03,
            'actif' => true,
            'description' => 'Le plus grand marché de la galaxie connue.',
        ]);

        // Ajouter toutes les ressources avec stock initial
        $this->ajouterRessourcesMarche($marche, 'commercial');

        $this->command->info("Main market created: {$marche->nom}");
    }

    /**
     * Créer des marchés secondaires
     */
    protected function creerMarchesSecondaires(): void
    {
        // Alpha Centauri - marché minier
        $alphaCentauri = SystemeStellaire::where('nom', 'like', 'Alpha Centauri%')->first();
        if ($alphaCentauri && $alphaCentauri->planetes()->exists()) {
            $planete = $alphaCentauri->planetes()->first();
            $marche = Marche::create([
                'nom' => 'Comptoir Minier Alpha',
                'type' => 'minier',
                'localisation_type' => Planete::class,
                'localisation_id' => $planete->id,
                'multiplicateur_achat' => 1.1,
                'multiplicateur_vente' => 0.9,
                'taxe' => 0.02,
                'actif' => true,
                'description' => 'Spécialisé dans les minerais bruts.',
            ]);
            $this->ajouterRessourcesMarche($marche, 'minier');
        }

        // Proxima Centauri - marché contrebande
        $proxima = SystemeStellaire::where('nom', 'Proxima Centauri')->first();
        if ($proxima && $proxima->planetes()->exists()) {
            $planete = $proxima->planetes()->first();
            $marche = Marche::create([
                'nom' => 'Le Trou Noir',
                'type' => 'contrebande',
                'localisation_type' => Planete::class,
                'localisation_id' => $planete->id,
                'multiplicateur_achat' => 0.8,
                'multiplicateur_vente' => 1.0,
                'taxe' => 0.0,
                'actif' => true,
                'description' => 'Un marché discret pour marchandises... spéciales.',
            ]);
            $this->ajouterRessourcesMarche($marche, 'contrebande');
        }

        // Sirius - marché industriel
        $sirius = SystemeStellaire::where('nom', 'Sirius')->first();
        if ($sirius && $sirius->planetes()->exists()) {
            $planete = $sirius->planetes()->first();
            $marche = Marche::create([
                'nom' => 'Hub Industriel Sirius',
                'type' => 'industriel',
                'localisation_type' => Planete::class,
                'localisation_id' => $planete->id,
                'multiplicateur_achat' => 1.05,
                'multiplicateur_vente' => 0.88,
                'taxe' => 0.04,
                'actif' => true,
                'description' => 'Centre de transformation et production.',
            ]);
            $this->ajouterRessourcesMarche($marche, 'industriel');
        }

        // Tau Ceti - marché commercial secondaire
        $tauCeti = SystemeStellaire::where('nom', 'Tau Ceti')->first();
        if ($tauCeti && $tauCeti->planetes()->exists()) {
            $planete = $tauCeti->planetes()->first();
            $marche = Marche::create([
                'nom' => 'Marché Colonial Tau',
                'type' => 'commercial',
                'localisation_type' => Planete::class,
                'localisation_id' => $planete->id,
                'multiplicateur_achat' => 1.15,
                'multiplicateur_vente' => 0.8,
                'taxe' => 0.05,
                'actif' => true,
                'description' => 'Marché frontalier avec prix variables.',
            ]);
            $this->ajouterRessourcesMarche($marche, 'commercial');
        }
    }

    /**
     * Ajouter des ressources à un marché selon son type
     */
    protected function ajouterRessourcesMarche(Marche $marche, string $typeMarche): void
    {
        $ressources = Ressource::all();

        foreach ($ressources as $ressource) {
            // Déterminer le stock initial selon le type de marché
            $stockInitial = $this->getStockInitial($ressource, $typeMarche);
            $stockMax = $this->getStockMax($ressource, $typeMarche);

            // Prix de base avec variation
            $variation = rand(90, 110) / 100;
            $prixAchat = $ressource->prix_base * $variation;
            $prixVente = $prixAchat * 0.85;

            PrixMarche::create([
                'marche_id' => $marche->id,
                'ressource_id' => $ressource->id,
                'prix_achat' => $prixAchat,
                'prix_vente' => $prixVente,
                'stock' => $stockInitial,
                'stock_max' => $stockMax,
                'demande' => rand(80, 120) / 100,
            ]);
        }
    }

    /**
     * Obtenir le stock initial selon le type de ressource et marché
     */
    protected function getStockInitial(Ressource $ressource, string $typeMarche): int
    {
        $base = match($ressource->rarete) {
            'commun' => 5000,
            'peu_commun' => 2000,
            'rare' => 500,
            'tres_rare' => 100,
            'exotique' => 10,
            default => 1000,
        };

        // Modifier selon type de marché
        return match($typeMarche) {
            'minier' => $ressource->categorie === 'metal' ? $base * 2 : $base / 2,
            'industriel' => $ressource->categorie === 'chimie' ? $base * 2 : $base,
            'contrebande' => $ressource->rarete === 'exotique' ? $base * 3 : $base / 3,
            default => $base,
        };
    }

    /**
     * Obtenir le stock maximum selon le type de ressource et marché
     */
    protected function getStockMax(Ressource $ressource, string $typeMarche): int
    {
        $base = match($ressource->rarete) {
            'commun' => 50000,
            'peu_commun' => 20000,
            'rare' => 5000,
            'tres_rare' => 1000,
            'exotique' => 100,
            default => 10000,
        };

        return match($typeMarche) {
            'minier' => $ressource->categorie === 'metal' ? $base * 2 : $base,
            'industriel' => $base * 1.5,
            'contrebande' => $base / 2,
            default => $base,
        };
    }
}
