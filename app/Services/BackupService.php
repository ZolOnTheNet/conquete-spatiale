<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class BackupService
{
    protected string $backupDisk = 'local';
    protected string $backupPath = 'backups';

    /**
     * Créer une nouvelle sauvegarde
     */
    public function createBackup(?string $description = null): array
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$timestamp}";
        $zipFilename = "{$filename}.zip";

        // Créer dossier temporaire
        $tempDir = storage_path("app/temp/{$filename}");
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            // Récupérer toutes les tables
            $tables = $this->getAllTables();

            // Créer metadata
            $metadata = [
                'created_at' => now()->toIso8601String(),
                'description' => $description,
                'db_version' => $this->getDatabaseVersion(),
                'laravel_version' => app()->version(),
                'tables' => [],
            ];

            // Exporter chaque table en JSON
            foreach ($tables as $table) {
                $data = DB::table($table)->get()->toArray();
                $tableFile = "{$table}.json";

                file_put_contents(
                    "{$tempDir}/{$tableFile}",
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                $metadata['tables'][$table] = [
                    'file' => $tableFile,
                    'rows' => count($data),
                    'columns' => $this->getTableColumns($table),
                ];
            }

            // Sauvegarder metadata
            file_put_contents(
                "{$tempDir}/metadata.json",
                json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // Créer archive ZIP
            $zipPath = storage_path("app/{$this->backupPath}/{$zipFilename}");
            $this->ensureBackupDirectoryExists();

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception("Impossible de créer l'archive ZIP");
            }

            // Ajouter tous les fichiers au ZIP
            $files = scandir($tempDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $zip->addFile("{$tempDir}/{$file}", $file);
                }
            }

            $zip->close();

            // Nettoyer dossier temporaire
            $this->deleteDirectory($tempDir);

            return [
                'filename' => $zipFilename,
                'path' => $zipPath,
                'size' => filesize($zipPath),
                'created_at' => $metadata['created_at'],
                'description' => $description,
                'tables_count' => count($tables),
            ];
        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            throw $e;
        }
    }

    /**
     * Lister toutes les sauvegardes disponibles
     */
    public function listBackups(): array
    {
        $this->ensureBackupDirectoryExists();

        $files = Storage::disk($this->backupDisk)->files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.zip')) {
                $fullPath = Storage::disk($this->backupDisk)->path($file);
                $filename = basename($file);

                // Extraire metadata du ZIP
                $metadata = $this->extractMetadataFromZip($fullPath);

                $backups[] = [
                    'filename' => $filename,
                    'path' => $fullPath,
                    'size' => filesize($fullPath),
                    'created_at' => $metadata['created_at'] ?? date('Y-m-d H:i:s', filemtime($fullPath)),
                    'description' => $metadata['description'] ?? null,
                    'db_version' => $metadata['db_version'] ?? null,
                    'tables_count' => count($metadata['tables'] ?? []),
                ];
            }
        }

        // Trier par date décroissante
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Restaurer une sauvegarde
     */
    public function restoreBackup(string $filename): void
    {
        $zipPath = Storage::disk($this->backupDisk)->path("{$this->backupPath}/{$filename}");

        if (!file_exists($zipPath)) {
            throw new \Exception("Sauvegarde introuvable: {$filename}");
        }

        $tempDir = storage_path("app/temp/restore_" . time());
        mkdir($tempDir, 0755, true);

        try {
            // Extraire le ZIP
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \Exception("Impossible d'ouvrir l'archive ZIP");
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Lire metadata
            $metadataPath = "{$tempDir}/metadata.json";
            if (!file_exists($metadataPath)) {
                throw new \Exception("Fichier metadata.json introuvable dans la sauvegarde");
            }

            $metadata = json_decode(file_get_contents($metadataPath), true);

            // Désactiver les contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Restaurer chaque table
            foreach ($metadata['tables'] as $tableName => $tableInfo) {
                $dataFile = "{$tempDir}/{$tableInfo['file']}";

                if (!file_exists($dataFile)) {
                    throw new \Exception("Fichier de données introuvable: {$tableInfo['file']}");
                }

                $data = json_decode(file_get_contents($dataFile), true);

                // Vider la table
                DB::table($tableName)->truncate();

                // Insérer les données par lots
                if (!empty($data)) {
                    $chunks = array_chunk($data, 100);
                    foreach ($chunks as $chunk) {
                        // Convertir stdClass en array si nécessaire
                        $chunk = array_map(function ($row) {
                            return (array) $row;
                        }, $chunk);

                        DB::table($tableName)->insert($chunk);
                    }
                }
            }

            // Réactiver les contraintes
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Nettoyer dossier temporaire
            $this->deleteDirectory($tempDir);
        } catch (\Exception $e) {
            // Réactiver les contraintes en cas d'erreur
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Nettoyer
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }

            throw $e;
        }
    }

    /**
     * Supprimer une sauvegarde
     */
    public function deleteBackup(string $filename): void
    {
        $filePath = "{$this->backupPath}/{$filename}";

        if (!Storage::disk($this->backupDisk)->exists($filePath)) {
            throw new \Exception("Sauvegarde introuvable: {$filename}");
        }

        Storage::disk($this->backupDisk)->delete($filePath);
    }

    /**
     * Télécharger une sauvegarde
     */
    public function downloadBackup(string $filename): StreamedResponse
    {
        $filePath = "{$this->backupPath}/{$filename}";

        if (!Storage::disk($this->backupDisk)->exists($filePath)) {
            throw new \Exception("Sauvegarde introuvable: {$filename}");
        }

        return Storage::disk($this->backupDisk)->download($filePath);
    }

    /**
     * Récupérer toutes les tables de la base
     */
    protected function getAllTables(): array
    {
        $tables = [];
        $databaseName = DB::getDatabaseName();

        // SQLite
        if (config('database.default') === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($results as $result) {
                $tables[] = $result->name;
            }
        }
        // MySQL/MariaDB
        else {
            $results = DB::select("SHOW TABLES");
            $key = "Tables_in_{$databaseName}";
            foreach ($results as $result) {
                $tables[] = $result->$key;
            }
        }

        // Exclure les tables de migrations si souhaité
        return array_filter($tables, function ($table) {
            return $table !== 'migrations';
        });
    }

    /**
     * Récupérer les colonnes d'une table
     */
    protected function getTableColumns(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    /**
     * Récupérer la version de la base de données
     */
    protected function getDatabaseVersion(): string
    {
        try {
            // Récupérer le dernier batch de migrations
            $lastBatch = DB::table('migrations')->max('batch');
            $migrationsCount = DB::table('migrations')->where('batch', $lastBatch)->count();

            return "batch_{$lastBatch}_migrations_{$migrationsCount}";
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Extraire metadata d'un fichier ZIP
     */
    protected function extractMetadataFromZip(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return [];
        }

        $metadataContent = $zip->getFromName('metadata.json');
        $zip->close();

        if ($metadataContent === false) {
            return [];
        }

        return json_decode($metadataContent, true) ?? [];
    }

    /**
     * S'assurer que le dossier de backup existe
     */
    protected function ensureBackupDirectoryExists(): void
    {
        $path = storage_path("app/{$this->backupPath}");
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Supprimer un dossier récursivement
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
