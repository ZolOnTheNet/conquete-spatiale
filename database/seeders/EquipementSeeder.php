<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arme;
use App\Models\Bouclier;

class EquipementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating weapons and shields...');

        $this->creerArmes();
        $this->creerBoucliers();

        $this->command->info('Weapons and shields created successfully');
    }

    /**
     * Creer les armes de base
     */
    protected function creerArmes(): void
    {
        $armes = [
            // LASERS - Rapides, precis
            [
                'code' => 'LASER_MK1',
                'nom' => 'Laser Mk1',
                'type' => 'laser',
                'degats_min' => 5,
                'degats_max' => 10,
                'portee' => 150,
                'cadence' => 3,
                'precision' => 85,
                'energie_tir' => 3,
                'niveau_requis' => 1,
                'prix' => 500,
                'taille' => 'petit',
                'description' => 'Laser leger standard, ideal pour debutants.',
            ],
            [
                'code' => 'LASER_MK2',
                'nom' => 'Laser Mk2',
                'type' => 'laser',
                'degats_min' => 8,
                'degats_max' => 15,
                'portee' => 180,
                'cadence' => 3,
                'precision' => 80,
                'energie_tir' => 5,
                'niveau_requis' => 2,
                'prix' => 1200,
                'taille' => 'petit',
                'description' => 'Version amelioree du laser standard.',
            ],
            [
                'code' => 'LASER_LOURD',
                'nom' => 'Laser Lourd',
                'type' => 'laser',
                'degats_min' => 15,
                'degats_max' => 25,
                'portee' => 200,
                'cadence' => 2,
                'precision' => 75,
                'energie_tir' => 8,
                'niveau_requis' => 3,
                'prix' => 3000,
                'taille' => 'moyen',
                'description' => 'Laser haute puissance pour combats intensifs.',
            ],

            // CANONS - Lents mais puissants
            [
                'code' => 'CANON_MK1',
                'nom' => 'Canon Mk1',
                'type' => 'canon',
                'degats_min' => 15,
                'degats_max' => 25,
                'portee' => 120,
                'cadence' => 1,
                'precision' => 70,
                'energie_tir' => 8,
                'niveau_requis' => 2,
                'prix' => 1500,
                'taille' => 'moyen',
                'description' => 'Canon a projectiles standard.',
            ],
            [
                'code' => 'CANON_MK2',
                'nom' => 'Canon Mk2',
                'type' => 'canon',
                'degats_min' => 25,
                'degats_max' => 40,
                'portee' => 140,
                'cadence' => 1,
                'precision' => 65,
                'energie_tir' => 12,
                'niveau_requis' => 3,
                'prix' => 4000,
                'taille' => 'moyen',
                'description' => 'Canon ameliore a haute velocite.',
            ],
            [
                'code' => 'CANON_SIEGE',
                'nom' => 'Canon de Siege',
                'type' => 'canon',
                'degats_min' => 50,
                'degats_max' => 80,
                'portee' => 100,
                'cadence' => 1,
                'precision' => 55,
                'energie_tir' => 25,
                'niveau_requis' => 5,
                'prix' => 12000,
                'taille' => 'grand',
                'description' => 'Enorme canon pour detruire les stations.',
            ],

            // MISSILES - Tres puissants, tres lents
            [
                'code' => 'MISSILE_MK1',
                'nom' => 'Lance-Missiles Mk1',
                'type' => 'missile',
                'degats_min' => 30,
                'degats_max' => 50,
                'portee' => 250,
                'cadence' => 1,
                'precision' => 60,
                'energie_tir' => 15,
                'niveau_requis' => 3,
                'prix' => 5000,
                'taille' => 'moyen',
                'description' => 'Lanceur de missiles guides.',
            ],
            [
                'code' => 'MISSILE_LOURD',
                'nom' => 'Torpilles Lourdes',
                'type' => 'missile',
                'degats_min' => 60,
                'degats_max' => 100,
                'portee' => 300,
                'cadence' => 1,
                'precision' => 50,
                'energie_tir' => 30,
                'niveau_requis' => 4,
                'prix' => 10000,
                'taille' => 'grand',
                'description' => 'Torpilles devastatrices a longue portee.',
            ],

            // PLASMA - Degats sur duree
            [
                'code' => 'PLASMA_MK1',
                'nom' => 'Projecteur Plasma',
                'type' => 'plasma',
                'degats_min' => 10,
                'degats_max' => 20,
                'portee' => 80,
                'cadence' => 2,
                'precision' => 75,
                'energie_tir' => 10,
                'niveau_requis' => 3,
                'prix' => 4500,
                'taille' => 'moyen',
                'description' => 'Projette du plasma brulant.',
            ],

            // EMP - Desactive systemes
            [
                'code' => 'EMP_MK1',
                'nom' => 'Canon EMP',
                'type' => 'emp',
                'degats_min' => 5,
                'degats_max' => 10,
                'portee' => 100,
                'cadence' => 1,
                'precision' => 90,
                'energie_tir' => 20,
                'niveau_requis' => 4,
                'prix' => 8000,
                'taille' => 'moyen',
                'description' => 'Desactive temporairement les systemes ennemis.',
            ],
        ];

        foreach ($armes as $data) {
            Arme::create($data);
        }

        $this->command->info('11 weapons created');
    }

    /**
     * Creer les boucliers de base
     */
    protected function creerBoucliers(): void
    {
        $boucliers = [
            // ENERGIE - Standard
            [
                'code' => 'BOUCLIER_MK1',
                'nom' => 'Bouclier Mk1',
                'type' => 'energie',
                'points_max' => 50,
                'regeneration' => 3,
                'resistance' => 5,
                'vs_laser' => 10,
                'vs_canon' => 0,
                'vs_missile' => 0,
                'vs_plasma' => 5,
                'vs_emp' => -20,
                'energie_maintien' => 5,
                'niveau_requis' => 1,
                'prix' => 800,
                'taille' => 'petit',
                'description' => 'Bouclier energetique de base.',
            ],
            [
                'code' => 'BOUCLIER_MK2',
                'nom' => 'Bouclier Mk2',
                'type' => 'energie',
                'points_max' => 100,
                'regeneration' => 5,
                'resistance' => 10,
                'vs_laser' => 15,
                'vs_canon' => 5,
                'vs_missile' => 5,
                'vs_plasma' => 10,
                'vs_emp' => -15,
                'energie_maintien' => 10,
                'niveau_requis' => 2,
                'prix' => 2000,
                'taille' => 'moyen',
                'description' => 'Bouclier energetique ameliore.',
            ],
            [
                'code' => 'BOUCLIER_MK3',
                'nom' => 'Bouclier Mk3',
                'type' => 'energie',
                'points_max' => 200,
                'regeneration' => 8,
                'resistance' => 15,
                'vs_laser' => 20,
                'vs_canon' => 10,
                'vs_missile' => 10,
                'vs_plasma' => 15,
                'vs_emp' => -10,
                'energie_maintien' => 20,
                'niveau_requis' => 4,
                'prix' => 6000,
                'taille' => 'grand',
                'description' => 'Bouclier energetique haute capacite.',
            ],

            // COQUE - Blindage
            [
                'code' => 'BLINDAGE_MK1',
                'nom' => 'Blindage Mk1',
                'type' => 'coque',
                'points_max' => 80,
                'regeneration' => 0,
                'resistance' => 15,
                'vs_laser' => -10,
                'vs_canon' => 20,
                'vs_missile' => 15,
                'vs_plasma' => -5,
                'vs_emp' => 30,
                'energie_maintien' => 0,
                'niveau_requis' => 2,
                'prix' => 1500,
                'taille' => 'moyen',
                'description' => 'Plaques de blindage renforcees.',
            ],
            [
                'code' => 'BLINDAGE_MK2',
                'nom' => 'Blindage Mk2',
                'type' => 'coque',
                'points_max' => 150,
                'regeneration' => 0,
                'resistance' => 25,
                'vs_laser' => -5,
                'vs_canon' => 30,
                'vs_missile' => 25,
                'vs_plasma' => 0,
                'vs_emp' => 40,
                'energie_maintien' => 0,
                'niveau_requis' => 4,
                'prix' => 5000,
                'taille' => 'grand',
                'description' => 'Blindage composite haute resistance.',
            ],

            // REGENERATIF
            [
                'code' => 'REGEN_MK1',
                'nom' => 'Bouclier Regeneratif',
                'type' => 'regeneratif',
                'points_max' => 60,
                'regeneration' => 10,
                'resistance' => 5,
                'vs_laser' => 5,
                'vs_canon' => 5,
                'vs_missile' => 5,
                'vs_plasma' => 5,
                'vs_emp' => -25,
                'energie_maintien' => 15,
                'niveau_requis' => 3,
                'prix' => 4000,
                'taille' => 'moyen',
                'description' => 'Bouclier a regeneration rapide.',
            ],

            // ADAPTATIF
            [
                'code' => 'ADAPT_MK1',
                'nom' => 'Bouclier Adaptatif',
                'type' => 'adaptatif',
                'points_max' => 120,
                'regeneration' => 5,
                'resistance' => 10,
                'vs_laser' => 15,
                'vs_canon' => 15,
                'vs_missile' => 15,
                'vs_plasma' => 15,
                'vs_emp' => 15,
                'energie_maintien' => 25,
                'niveau_requis' => 5,
                'prix' => 15000,
                'taille' => 'grand',
                'description' => 'Bouclier qui s\'adapte a tous les types d\'armes.',
            ],
        ];

        foreach ($boucliers as $data) {
            Bouclier::create($data);
        }

        $this->command->info('7 shields created');
    }
}
