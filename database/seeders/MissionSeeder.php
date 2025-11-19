<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mission;
use App\Models\Faction;
use App\Models\Ressource;

class MissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating missions...');

        // Recuperer les factions
        $federation = Faction::where('code', 'FEDERATION')->first();
        $guilde = Faction::where('code', 'GUILDE_MARCHANDS')->first();
        $consortium = Faction::where('code', 'CONSORTIUM')->first();
        $cartel = Faction::where('code', 'CARTEL')->first();
        $academie = Faction::where('code', 'ACADEMIE')->first();

        // Recuperer quelques ressources
        $fer = Ressource::where('code', 'FER')->first();
        $hydrogene = Ressource::where('code', 'HYDROGENE')->first();

        $missions = [
            // FEDERATION - Missions legales
            [
                'code' => 'FED_LIVRAISON_01',
                'titre' => 'Livraison de Fournitures',
                'description' => 'La colonie de Mars a besoin de fer pour ses constructions. Transportez 500 unites de fer jusqu\'a destination.',
                'faction_id' => $federation?->id,
                'type' => 'livraison',
                'difficulte' => 'facile',
                'niveau_requis' => 1,
                'reputation_requise' => 0,
                'objectifs' => [
                    ['type' => 'livrer', 'ressource' => 'FER', 'quantite' => 500],
                ],
                'recompense_credits' => 1000,
                'recompense_xp' => 50,
                'recompense_reputation' => 30,
                'penalite_reputation' => 15,
                'repetable' => true,
                'cooldown' => 60,
            ],
            [
                'code' => 'FED_PATROUILLE_01',
                'titre' => 'Patrouille de Secteur',
                'description' => 'Eliminez 3 pirates dans le secteur pour securiser les routes commerciales.',
                'faction_id' => $federation?->id,
                'type' => 'combat',
                'difficulte' => 'normal',
                'niveau_requis' => 2,
                'reputation_requise' => 50,
                'objectifs' => [
                    ['type' => 'eliminer', 'cible' => 'pirate', 'quantite' => 3],
                ],
                'recompense_credits' => 2500,
                'recompense_xp' => 150,
                'recompense_reputation' => 75,
                'penalite_reputation' => 30,
                'repetable' => true,
                'cooldown' => 120,
            ],

            // GUILDE MARCHANDS - Commerce
            [
                'code' => 'GUILDE_COMMERCE_01',
                'titre' => 'Opportunite Commerciale',
                'description' => 'Realisez 3 transactions commerciales (achat ou vente) pour prouver vos talents de negociant.',
                'faction_id' => $guilde?->id,
                'type' => 'commerce',
                'difficulte' => 'facile',
                'niveau_requis' => 1,
                'reputation_requise' => 0,
                'objectifs' => [
                    ['type' => 'transactions', 'quantite' => 3],
                ],
                'recompense_credits' => 800,
                'recompense_xp' => 40,
                'recompense_reputation' => 25,
                'penalite_reputation' => 10,
                'repetable' => true,
                'cooldown' => 30,
            ],
            [
                'code' => 'GUILDE_PROFIT_01',
                'titre' => 'Benefice Substantiel',
                'description' => 'Generez un profit de 5000 credits grace au commerce.',
                'faction_id' => $guilde?->id,
                'type' => 'commerce',
                'difficulte' => 'normal',
                'niveau_requis' => 3,
                'reputation_requise' => 100,
                'objectifs' => [
                    ['type' => 'profit', 'quantite' => 5000],
                ],
                'recompense_credits' => 2000,
                'recompense_xp' => 100,
                'recompense_reputation' => 60,
                'penalite_reputation' => 25,
                'repetable' => true,
                'cooldown' => 180,
            ],

            // CONSORTIUM - Extraction
            [
                'code' => 'CONS_EXTRACTION_01',
                'titre' => 'Quota d\'Extraction',
                'description' => 'Extrayez 1000 unites de ressources pour le Consortium.',
                'faction_id' => $consortium?->id,
                'type' => 'collecte',
                'difficulte' => 'facile',
                'niveau_requis' => 1,
                'reputation_requise' => 0,
                'objectifs' => [
                    ['type' => 'extraire', 'quantite' => 1000],
                ],
                'recompense_credits' => 1200,
                'recompense_xp' => 60,
                'recompense_reputation' => 35,
                'penalite_reputation' => 15,
                'repetable' => true,
                'cooldown' => 45,
            ],
            [
                'code' => 'CONS_RARE_01',
                'titre' => 'Ressources Rares',
                'description' => 'Collectez 100 unites de Platine pour un projet special.',
                'faction_id' => $consortium?->id,
                'type' => 'collecte',
                'difficulte' => 'difficile',
                'niveau_requis' => 4,
                'reputation_requise' => 200,
                'objectifs' => [
                    ['type' => 'collecter', 'ressource' => 'PLATINE', 'quantite' => 100],
                ],
                'recompense_credits' => 8000,
                'recompense_xp' => 300,
                'recompense_reputation' => 100,
                'penalite_reputation' => 50,
                'repetable' => true,
                'cooldown' => 360,
            ],

            // ACADEMIE - Exploration
            [
                'code' => 'ACAD_EXPLORATION_01',
                'titre' => 'Cartographie Stellaire',
                'description' => 'Decouvrez 3 nouveaux systemes stellaires pour l\'Academie.',
                'faction_id' => $academie?->id,
                'type' => 'exploration',
                'difficulte' => 'normal',
                'niveau_requis' => 2,
                'reputation_requise' => 0,
                'objectifs' => [
                    ['type' => 'decouvrir', 'quantite' => 3],
                ],
                'recompense_credits' => 1500,
                'recompense_xp' => 200,
                'recompense_reputation' => 50,
                'penalite_reputation' => 20,
                'repetable' => true,
                'cooldown' => 90,
            ],
            [
                'code' => 'ACAD_SCAN_01',
                'titre' => 'Analyse Planetaire',
                'description' => 'Scannez 5 planetes pour collecter des donnees scientifiques.',
                'faction_id' => $academie?->id,
                'type' => 'exploration',
                'difficulte' => 'facile',
                'niveau_requis' => 1,
                'reputation_requise' => 0,
                'objectifs' => [
                    ['type' => 'scanner_planetes', 'quantite' => 5],
                ],
                'recompense_credits' => 600,
                'recompense_xp' => 80,
                'recompense_reputation' => 40,
                'penalite_reputation' => 15,
                'repetable' => true,
                'cooldown' => 60,
            ],

            // CARTEL - Missions illegales
            [
                'code' => 'CART_CONTREBANDE_01',
                'titre' => 'Livraison Discrete',
                'description' => 'Transportez des marchandises... sans poser de questions. Livrez 200 unites sans vous faire reperer.',
                'faction_id' => $cartel?->id,
                'type' => 'livraison',
                'difficulte' => 'normal',
                'niveau_requis' => 2,
                'reputation_requise' => 0,
                'objectifs' => [
                    ['type' => 'livrer_secret', 'quantite' => 200],
                ],
                'recompense_credits' => 3000,
                'recompense_xp' => 100,
                'recompense_reputation' => 50,
                'penalite_reputation' => 40,
                'repetable' => true,
                'cooldown' => 120,
            ],
            [
                'code' => 'CART_SABOTAGE_01',
                'titre' => 'Elimination de Concurrence',
                'description' => 'Detruisez 2 vaisseaux de la Federation pour affirmer notre presence.',
                'faction_id' => $cartel?->id,
                'type' => 'combat',
                'difficulte' => 'difficile',
                'niveau_requis' => 4,
                'reputation_requise' => 150,
                'objectifs' => [
                    ['type' => 'eliminer', 'cible' => 'militaire', 'quantite' => 2],
                ],
                'recompense_credits' => 6000,
                'recompense_xp' => 250,
                'recompense_reputation' => 80,
                'penalite_reputation' => 60,
                'repetable' => true,
                'cooldown' => 240,
            ],
        ];

        foreach ($missions as $data) {
            Mission::create($data);
        }

        $this->command->info(count($missions) . ' missions created');
    }
}
