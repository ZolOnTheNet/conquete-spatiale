<?php

namespace Database\Seeders;

use App\Models\Ressource;
use Illuminate\Database\Seeder;

class RessourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ressources = [
            // MÉTAUX (10)
            [
                'code' => 'FER',
                'nom' => 'Fer',
                'categorie' => 'metaux',
                'description' => 'Métal de base, très commun dans les astéroïdes et planètes telluriques.',
                'poids_unitaire' => 1.0,
                'prix_base' => 30,
                'rarete' => 95,
            ],
            [
                'code' => 'ALUMINIUM',
                'nom' => 'Aluminium',
                'categorie' => 'metaux',
                'description' => 'Métal léger utilisé dans la construction de vaisseaux.',
                'poids_unitaire' => 0.8,
                'prix_base' => 60,
                'rarete' => 80,
            ],
            [
                'code' => 'BAUXITE',
                'nom' => 'Bauxite',
                'categorie' => 'metaux',
                'description' => 'Minerai d\'aluminium brut.',
                'poids_unitaire' => 1.2,
                'prix_base' => 40,
                'rarete' => 75,
            ],
            [
                'code' => 'GRAPHITE',
                'nom' => 'Graphite',
                'categorie' => 'metaux',
                'description' => 'Utilisé dans les systèmes de refroidissement et batteries.',
                'poids_unitaire' => 1.0,
                'prix_base' => 50,
                'rarete' => 70,
            ],
            [
                'code' => 'ZINC',
                'nom' => 'Zinc',
                'categorie' => 'metaux',
                'description' => 'Métal utilisé pour les alliages et protections.',
                'poids_unitaire' => 1.1,
                'prix_base' => 55,
                'rarete' => 70,
            ],
            [
                'code' => 'NICKEL',
                'nom' => 'Nickel',
                'categorie' => 'metaux',
                'description' => 'Métal résistant à la corrosion.',
                'poids_unitaire' => 1.3,
                'prix_base' => 70,
                'rarete' => 65,
            ],
            [
                'code' => 'NIOBIUM',
                'nom' => 'Niobium',
                'categorie' => 'metaux',
                'description' => 'Métal rare utilisé dans les supraconducteurs.',
                'poids_unitaire' => 1.4,
                'prix_base' => 200,
                'rarete' => 40,
            ],
            [
                'code' => 'TUNGSTENE',
                'nom' => 'Tungstène',
                'categorie' => 'metaux',
                'description' => 'Métal très dense, utilisé dans les blindages.',
                'poids_unitaire' => 1.8,
                'prix_base' => 150,
                'rarete' => 50,
            ],
            [
                'code' => 'PLATINE',
                'nom' => 'Platine',
                'categorie' => 'metaux',
                'description' => 'Métal précieux utilisé en électronique avancée.',
                'poids_unitaire' => 2.0,
                'prix_base' => 800,
                'rarete' => 15,
            ],
            [
                'code' => 'URANIUM',
                'nom' => 'Uranium',
                'categorie' => 'metaux',
                'description' => 'Matière fissile pour réacteurs nucléaires.',
                'poids_unitaire' => 1.5,
                'prix_base' => 500,
                'rarete' => 10,
            ],

            // GAZ (2)
            [
                'code' => 'HYDROGENE',
                'nom' => 'Hydrogène',
                'categorie' => 'gaz',
                'description' => 'Gaz léger utilisé comme carburant.',
                'poids_unitaire' => 0.1,
                'prix_base' => 20,
                'rarete' => 90,
            ],
            [
                'code' => 'OXYGENE',
                'nom' => 'Oxygène',
                'categorie' => 'gaz',
                'description' => 'Gaz vital, extrait des glaces ou atmosphères.',
                'poids_unitaire' => 0.1,
                'prix_base' => 15,
                'rarete' => 95,
            ],

            // ÉLÉMENTAIRES (4)
            [
                'code' => 'SABLES',
                'nom' => 'Sables',
                'categorie' => 'elementaire',
                'description' => 'Matière première pour verre et silicium.',
                'poids_unitaire' => 0.9,
                'prix_base' => 10,
                'rarete' => 95,
            ],
            [
                'code' => 'ARGILES',
                'nom' => 'Argiles',
                'categorie' => 'elementaire',
                'description' => 'Utilisé en construction basique.',
                'poids_unitaire' => 0.8,
                'prix_base' => 10,
                'rarete' => 90,
            ],
            [
                'code' => 'GLACES',
                'nom' => 'Glaces',
                'categorie' => 'elementaire',
                'description' => 'Eau gelée, source d\'eau et oxygène.',
                'poids_unitaire' => 0.5,
                'prix_base' => 20,
                'rarete' => 85,
            ],
            [
                'code' => 'BITUMES',
                'nom' => 'Bitumes',
                'categorie' => 'elementaire',
                'description' => 'Hydrocarbures utilisés en industrie chimique.',
                'poids_unitaire' => 1.0,
                'prix_base' => 40,
                'rarete' => 60,
            ],

            // CHIMIE (1)
            [
                'code' => 'ELEMENTS_CHIMIQUES',
                'nom' => 'Éléments Chimiques',
                'categorie' => 'chimie',
                'description' => 'Composés chimiques variés pour industrie avancée.',
                'poids_unitaire' => 0.7,
                'prix_base' => 80,
                'rarete' => 60,
            ],

            // EXOTIQUES (4)
            [
                'code' => 'NACRETOILE',
                'nom' => 'Nacrétoile',
                'categorie' => 'exotique',
                'description' => 'Matériau organique spatial rare, propriétés uniques.',
                'poids_unitaire' => 0.5,
                'prix_base' => 1500,
                'rarete' => 5,
            ],
            [
                'code' => 'ARGETOILE',
                'nom' => 'Argétoile',
                'categorie' => 'exotique',
                'description' => 'Métal stellaire extrêmement rare, conductivité exceptionnelle.',
                'poids_unitaire' => 0.3,
                'prix_base' => 2500,
                'rarete' => 2,
            ],
            [
                'code' => 'PLAZETOILE',
                'nom' => 'Plazétoile',
                'categorie' => 'exotique',
                'description' => 'Cristaux énergétiques formés dans les nébuleuses.',
                'poids_unitaire' => 0.4,
                'prix_base' => 2000,
                'rarete' => 3,
            ],
            [
                'code' => 'TYRETOILE',
                'nom' => 'Tyrétoile',
                'categorie' => 'exotique',
                'description' => 'Alliage naturel spatial aux propriétés gravitationnelles.',
                'poids_unitaire' => 0.6,
                'prix_base' => 1800,
                'rarete' => 4,
            ],
        ];

        foreach ($ressources as $data) {
            Ressource::create($data);
        }

        $this->command->info('✅ 21 ressources créées avec succès');
    }
}
