<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore
                            {filename : Nom du fichier de sauvegarde à restaurer}
                            {--force : Forcer sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaurer une sauvegarde de la base de données';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $filename = $this->argument('filename');
        $force = $this->option('force');

        // Vérifier que le fichier existe
        $backups = $backupService->listBackups();
        $backup = collect($backups)->firstWhere('filename', $filename);

        if (!$backup) {
            $this->error("❌ Sauvegarde introuvable: {$filename}");
            $this->newLine();
            $this->info('💡 Utilisez la commande backup:list pour voir les sauvegardes disponibles');
            return 1;
        }

        // Afficher informations
        $this->newLine();
        $this->warn('⚠️  ATTENTION : Cette opération va REMPLACER toutes les données actuelles !');
        $this->newLine();

        $this->table(
            ['Propriété', 'Valeur'],
            [
                ['Fichier', $backup['filename']],
                ['Date création', $backup['created_at']],
                ['Description', $backup['description'] ?? 'Aucune'],
                ['Taille', $this->formatBytes($backup['size'])],
                ['Tables', $backup['tables_count']],
                ['Version DB', $backup['db_version'] ?? 'N/A'],
            ]
        );

        $this->newLine();

        // Demander confirmation
        if (!$force && !$this->confirm('Voulez-vous vraiment restaurer cette sauvegarde ?', false)) {
            $this->info('Opération annulée.');
            return 0;
        }

        $this->info('🔄 Restauration en cours...');
        $this->newLine();

        try {
            $backupService->restoreBackup($filename);

            $this->info('✅ Sauvegarde restaurée avec succès !');
            $this->newLine();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la restauration:');
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
