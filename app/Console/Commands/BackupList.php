<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lister toutes les sauvegardes disponibles';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $this->info('📋 Liste des sauvegardes disponibles');
        $this->newLine();

        try {
            $backups = $backupService->listBackups();

            if (empty($backups)) {
                $this->warn('Aucune sauvegarde trouvée.');
                $this->newLine();
                $this->info('💡 Utilisez la commande backup:create pour créer une sauvegarde');
                return 0;
            }

            $rows = array_map(function ($backup) {
                return [
                    $backup['filename'],
                    $backup['created_at'],
                    $backup['description'] ?? '-',
                    $this->formatBytes($backup['size']),
                    $backup['tables_count'],
                    $backup['db_version'] ?? 'N/A',
                ];
            }, $backups);

            $this->table(
                ['Fichier', 'Date', 'Description', 'Taille', 'Tables', 'Version DB'],
                $rows
            );

            $this->newLine();
            $this->info("Total: " . count($backups) . " sauvegarde(s)");
            $this->newLine();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la récupération des sauvegardes:');
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
