<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recette;
use App\Models\Ressource;

class RecetteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating recipes...');

        // Recuperer les IDs des ressources par code
        $ressources = Ressource::pluck('id', 'code')->toArray();

        $recettes = [
            // RAFFINAGE - Transformation basique
            [
                'code' => 'RAFF_BAUXITE',
                'nom' => 'Raffinage Bauxite',
                'categorie' => 'raffinage',
                'temps_fabrication' => 30,
                'niveau_requis' => 1,
                'energie_requise' => 5,
                'ingredients' => [
                    ['ressource_id' => $ressources['BAUXITE'], 'quantite' => 100],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['ALUMINIUM'], 'quantite' => 50],
                ],
                'description' => 'Extraction d\'aluminium a partir de bauxite brute.',
            ],
            [
                'code' => 'ELECTRO_GLACES',
                'nom' => 'Electrolyse Glaces',
                'categorie' => 'raffinage',
                'temps_fabrication' => 45,
                'niveau_requis' => 1,
                'energie_requise' => 10,
                'ingredients' => [
                    ['ressource_id' => $ressources['GLACES'], 'quantite' => 100],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['HYDROGENE'], 'quantite' => 80],
                    ['ressource_id' => $ressources['OXYGENE'], 'quantite' => 40],
                ],
                'description' => 'Separation de la glace en hydrogene et oxygene.',
            ],
            [
                'code' => 'RAFF_BITUMES',
                'nom' => 'Raffinage Bitumes',
                'categorie' => 'raffinage',
                'temps_fabrication' => 60,
                'niveau_requis' => 2,
                'energie_requise' => 15,
                'ingredients' => [
                    ['ressource_id' => $ressources['BITUMES'], 'quantite' => 100],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['ELEMENTS_CHIMIQUES'], 'quantite' => 60],
                ],
                'description' => 'Extraction de composes chimiques des bitumes.',
            ],
            [
                'code' => 'FUSION_SABLES',
                'nom' => 'Fusion Sables',
                'categorie' => 'raffinage',
                'temps_fabrication' => 40,
                'niveau_requis' => 1,
                'energie_requise' => 8,
                'ingredients' => [
                    ['ressource_id' => $ressources['SABLES'], 'quantite' => 200],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['GRAPHITE'], 'quantite' => 50],
                ],
                'description' => 'Extraction de graphite a partir de sables riches en carbone.',
            ],

            // ALLIAGES - Combinaisons de metaux
            [
                'code' => 'ALLIAGE_ACIER',
                'nom' => 'Alliage Acier Renforce',
                'categorie' => 'alliage',
                'temps_fabrication' => 90,
                'niveau_requis' => 2,
                'energie_requise' => 20,
                'ingredients' => [
                    ['ressource_id' => $ressources['FER'], 'quantite' => 100],
                    ['ressource_id' => $ressources['NICKEL'], 'quantite' => 20],
                    ['ressource_id' => $ressources['GRAPHITE'], 'quantite' => 10],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['TUNGSTENE'], 'quantite' => 40],
                ],
                'description' => 'Alliage haute resistance pour blindages.',
            ],
            [
                'code' => 'ALLIAGE_CONDUCTEUR',
                'nom' => 'Alliage Conducteur',
                'categorie' => 'alliage',
                'temps_fabrication' => 120,
                'niveau_requis' => 3,
                'energie_requise' => 30,
                'ingredients' => [
                    ['ressource_id' => $ressources['ALUMINIUM'], 'quantite' => 50],
                    ['ressource_id' => $ressources['ZINC'], 'quantite' => 30],
                    ['ressource_id' => $ressources['NICKEL'], 'quantite' => 20],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['NIOBIUM'], 'quantite' => 25],
                ],
                'description' => 'Supraconducteur pour systemes avances.',
            ],

            // COMPOSANTS - Pieces detachees
            [
                'code' => 'COMP_CARBURANT',
                'nom' => 'Synthese Carburant',
                'categorie' => 'composant',
                'temps_fabrication' => 60,
                'niveau_requis' => 1,
                'energie_requise' => 12,
                'ingredients' => [
                    ['ressource_id' => $ressources['HYDROGENE'], 'quantite' => 100],
                    ['ressource_id' => $ressources['OXYGENE'], 'quantite' => 50],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['ELEMENTS_CHIMIQUES'], 'quantite' => 80],
                ],
                'description' => 'Carburant synthetique pour vaisseaux.',
            ],
            [
                'code' => 'COMP_ELECTRONIQUE',
                'nom' => 'Composants Electroniques',
                'categorie' => 'composant',
                'temps_fabrication' => 180,
                'niveau_requis' => 3,
                'energie_requise' => 40,
                'ingredients' => [
                    ['ressource_id' => $ressources['GRAPHITE'], 'quantite' => 30],
                    ['ressource_id' => $ressources['NIOBIUM'], 'quantite' => 10],
                    ['ressource_id' => $ressources['ELEMENTS_CHIMIQUES'], 'quantite' => 20],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['PLATINE'], 'quantite' => 5],
                ],
                'description' => 'Circuits integres pour systemes de bord.',
            ],

            // AVANCE - High-tech et exotique
            [
                'code' => 'ADV_REACTEUR',
                'nom' => 'Enrichissement Uranium',
                'categorie' => 'avance',
                'temps_fabrication' => 300,
                'niveau_requis' => 4,
                'energie_requise' => 100,
                'ingredients' => [
                    ['ressource_id' => $ressources['FER'], 'quantite' => 200],
                    ['ressource_id' => $ressources['TUNGSTENE'], 'quantite' => 50],
                    ['ressource_id' => $ressources['ELEMENTS_CHIMIQUES'], 'quantite' => 100],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['URANIUM'], 'quantite' => 10],
                ],
                'description' => 'Traitement et enrichissement de l\'uranium.',
            ],
            [
                'code' => 'ADV_CRISTAL',
                'nom' => 'Cristallisation Energetique',
                'categorie' => 'avance',
                'temps_fabrication' => 600,
                'niveau_requis' => 5,
                'energie_requise' => 200,
                'ingredients' => [
                    ['ressource_id' => $ressources['PLATINE'], 'quantite' => 20],
                    ['ressource_id' => $ressources['URANIUM'], 'quantite' => 5],
                    ['ressource_id' => $ressources['NIOBIUM'], 'quantite' => 30],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['PLAZETOILE'], 'quantite' => 1],
                ],
                'description' => 'Creation de cristaux energetiques synthetiques.',
            ],
            [
                'code' => 'ADV_STELLAIRE',
                'nom' => 'Fusion Stellaire',
                'categorie' => 'avance',
                'temps_fabrication' => 900,
                'niveau_requis' => 5,
                'energie_requise' => 300,
                'ingredients' => [
                    ['ressource_id' => $ressources['PLAZETOILE'], 'quantite' => 2],
                    ['ressource_id' => $ressources['URANIUM'], 'quantite' => 10],
                    ['ressource_id' => $ressources['TUNGSTENE'], 'quantite' => 100],
                ],
                'produits' => [
                    ['ressource_id' => $ressources['ARGETOILE'], 'quantite' => 1],
                ],
                'description' => 'Transmutation au niveau stellaire.',
            ],
        ];

        foreach ($recettes as $data) {
            Recette::create($data);
        }

        $this->command->info('11 recipes created successfully');
    }
}
