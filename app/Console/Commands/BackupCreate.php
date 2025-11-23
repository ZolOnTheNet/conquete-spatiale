<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create
                            {--description= : Description de la sauvegarde}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer une sauvegarde complète de la base de données';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $description = $this->option('description');

        $this->info('🔄 Création de la sauvegarde en cours...');
        $this->newLine();

        try {
            $backup = $backupService->createBackup($description);

            $this->info('✅ Sauvegarde créée avec succès !');
            $this->newLine();

            $this->table(
                ['Propriété', 'Valeur'],
                [
                    ['Fichier', $backup['filename']],
                    ['Taille', $this->formatBytes($backup['size'])],
                    ['Date', $backup['created_at']],
                    ['Description', $backup['description'] ?? 'Aucune'],
                    ['Tables sauvegardées', $backup['tables_count']],
                ]
            );

            $this->newLine();
            $this->info("📁 Emplacement: {$backup['path']}");
            $this->newLine();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création de la sauvegarde:');
            $this->error($e->getMessage());
            $this->newLine();

            return 1;
        }
    }

    /**
     * Formater la taille en octets
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
