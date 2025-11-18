<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DbResetGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-game
                            {--mode=hybrid : Mode de génération (basic/gaia/hybrid)}
                            {--systems=20 : Nombre de systèmes procéduraux à générer}
                            {--force : Forcer sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialiser et peupler la base de données du jeu';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mode = $this->option('mode');
        $force = $this->option('force');

        // Valider le mode
        if (!in_array($mode, ['basic', 'gaia', 'hybrid', 'procedural'])) {
            $this->error("Mode invalide: {$mode}");
            $this->info("Modes disponibles: basic, gaia, hybrid, procedural");
            return 1;
        }

        // Afficher warning
        $this->newLine();
        $this->warn('⚠️  ATTENTION : Cette commande va SUPPRIMER toutes les données existantes !');
        $this->newLine();

        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Mode de génération', $mode],
                ['Systèmes procéduraux', $this->option('systems')],
            ]
        );

        $this->newLine();

        // Demander confirmation
        if (!$force && !$this->confirm('Voulez-vous continuer ?', false)) {
            $this->info('Opération annulée.');
            return 0;
        }

        // Reset des migrations
        $this->info('🗄️  Réinitialisation de la base de données...');
        Artisan::call('migrate:fresh', ['--force' => true], $this->output);

        $this->newLine();

        // Seed selon le mode
        $this->info("🌱 Peuplement de la base (mode: {$mode})...");
        Artisan::call('db:seed', [
            '--force' => true,
            '--class' => 'Database\\Seeders\\DatabaseSeeder',
            '--option' => [
                'mode' => $mode,
            ],
        ], $this->output);

        $this->newLine();
        $this->info('✅ Base de données réinitialisée avec succès !');
        $this->newLine();

        // Afficher info de connexion
        $this->info('📝 Compte de test:');
        $this->info('   Login: test');
        $this->info('   Password: password');
        $this->newLine();

        return 0;
    }
}
