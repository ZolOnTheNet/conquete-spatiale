<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Récupérer mode depuis option (db:reset-game) ou config par défaut
        try {
            $mode = $this->command->option('mode') ?? config('universe.generation_mode', 'hybrid');
        } catch (\Exception $e) {
            $mode = config('universe.generation_mode', 'hybrid');
        }

        $this->command->info("🎮 Mode de génération: {$mode}");

        // GameSeeder orchestre tout : ressources, factions, univers GAIA, compte test
        // (appelle en interne : RessourceSeeder, FactionSeeder, GaiaSeeder)
        $this->call(GameSeeder::class);

        // Contenu additionnel (après GameSeeder pour éviter les doublons)
        $this->call(RecetteSeeder::class);
        $this->call(EquipementSeeder::class);
        $this->call(EnnemiSeeder::class);
        $this->call(MarcheSeeder::class);
        $this->call(MissionSeeder::class);

        $this->command->info('');
        $this->command->info('Base de donnees initialisee avec succes !');
    }
}
