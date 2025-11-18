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

        // Toujours créer les comptes et personnages de test
        $this->call(GameSeeder::class);

        // Créer les ressources (nécessaires pour génération gisements)
        $this->call(RessourceSeeder::class);

        // Créer les recettes (après les ressources)
        $this->call(RecetteSeeder::class);

        // Créer les équipements (armes et boucliers)
        $this->call(EquipementSeeder::class);

        // Créer les ennemis
        $this->call(EnnemiSeeder::class);

        // Générer l'univers selon le mode
        match($mode) {
            'basic', 'procedural' => $this->call(UniverseSeeder::class),
            'gaia' => $this->call(GaiaSeeder::class),
            'hybrid' => $this->call([
                GaiaSeeder::class,
                UniverseSeeder::class,
            ]),
            default => $this->call(UniverseSeeder::class),
        };

        // Créer les marchés (après la génération des planètes)
        $this->call(MarcheSeeder::class);

        $this->command->info('');
        $this->command->info('Base de donnees initialisee avec succes !');
    }
}
