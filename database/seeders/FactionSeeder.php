<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faction;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating factions...');

        $factions = [
            [
                'code' => 'FEDERATION',
                'nom' => 'Federation Terrienne',
                'description' => 'Le gouvernement central de la Terre et ses colonies. Maintient l\'ordre et la loi dans les systemes civilises.',
                'type' => 'gouvernement',
                'alignement' => 'legal',
                'couleur' => '#3B82F6',
                'relations' => [
                    'CARTEL' => -2,
                    'GUILDE_MARCHANDS' => 1,
                ],
            ],
            [
                'code' => 'GUILDE_MARCHANDS',
                'nom' => 'Guilde des Marchands',
                'description' => 'Puissante organisation commerciale controlant les routes marchandes principales.',
                'type' => 'guilde',
                'alignement' => 'neutre',
                'couleur' => '#F59E0B',
                'relations' => [
                    'FEDERATION' => 1,
                    'CONSORTIUM' => 1,
                ],
            ],
            [
                'code' => 'CONSORTIUM',
                'nom' => 'Consortium Minier',
                'description' => 'Corporation specialisee dans l\'extraction de ressources sur les mondes inhospitaliers.',
                'type' => 'corporation',
                'alignement' => 'neutre',
                'couleur' => '#8B5CF6',
                'relations' => [
                    'GUILDE_MARCHANDS' => 1,
                ],
            ],
            [
                'code' => 'CARTEL',
                'nom' => 'Cartel des Ombres',
                'description' => 'Organisation criminelle operant dans les zones frontieres. Contrebande, piraterie et marche noir.',
                'type' => 'pirate',
                'alignement' => 'criminel',
                'couleur' => '#EF4444',
                'relations' => [
                    'FEDERATION' => -2,
                    'MERCENAIRES' => 1,
                ],
            ],
            [
                'code' => 'MERCENAIRES',
                'nom' => 'Compagnie des Mercenaires',
                'description' => 'Soldats de fortune vendant leurs services au plus offrant. Efficaces mais sans scrupules.',
                'type' => 'militaire',
                'alignement' => 'neutre',
                'couleur' => '#10B981',
                'relations' => [
                    'CARTEL' => 1,
                ],
            ],
            [
                'code' => 'ACADEMIE',
                'nom' => 'Academie Stellaire',
                'description' => 'Institution scientifique dediee a l\'exploration et la recherche. Finance des expeditions.',
                'type' => 'scientifique',
                'alignement' => 'legal',
                'couleur' => '#06B6D4',
                'relations' => [
                    'FEDERATION' => 1,
                ],
            ],
        ];

        foreach ($factions as $data) {
            Faction::create($data);
        }

        $this->command->info(count($factions) . ' factions created');
    }
}
