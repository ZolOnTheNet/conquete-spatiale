<?php

namespace App\Console\Commands;

use App\Models\SystemeStellaire;
use App\Services\ExoplanetService;
use Illuminate\Console\Command;

class ImportRealExoplanetsCommand extends Command
{
    protected $signature = 'exoplanet:import-real
                            {--radius=100 : Rayon maximum en années-lumière}
                            {--limit=1000 : Nombre maximum d\'exoplanètes à importer}
                            {--star= : Importer pour une étoile spécifique}
                            {--gaia-only : Importer uniquement pour les systèmes GAIA existants}
                            {--clear-cache : Vider le cache avant l\'import}';

    protected $description = 'Import les exoplanètes réelles depuis le catalogue NASA Exoplanet Archive';

    protected ExoplanetService $exoplanetService;

    public function __construct(ExoplanetService $exoplanetService)
    {
        parent::__construct();
        $this->exoplanetService = $exoplanetService;
    }

    public function handle(): int
    {
        $this->info('🪐 Import des exoplanètes NASA Exoplanet Archive');
        $this->newLine();

        // Vider le cache si demandé
        if ($this->option('clear-cache')) {
            $this->exoplanetService->clearCache();
            $this->info('✓ Cache vidé');
        }

        $radius = (float)$this->option('radius');
        $limit = (int)$this->option('limit');
        $starName = $this->option('star');
        $gaiaOnly = $this->option('gaia-only');

        // Mode 1: Import pour une étoile spécifique
        if ($starName) {
            return $this->importForSpecificStar($starName);
        }

        // Mode 2: Import pour tous les systèmes GAIA existants
        if ($gaiaOnly) {
            return $this->importForGaiaSystems();
        }

        // Mode 3: Import dans un rayon (associer aux systèmes GAIA si possible)
        return $this->importInRadius($radius, $limit);
    }

    /**
     * Importer les exoplanètes pour une étoile spécifique
     */
    protected function importForSpecificStar(string $starName): int
    {
        $this->info("🔍 Recherche du système stellaire: {$starName}");

        // Chercher par nom, nom_commun ou dans les noms alternatifs
        $systeme = SystemeStellaire::where('nom', $starName)
            ->orWhere('nom_commun', $starName)
            ->orWhereJsonContains('noms_alternatifs', $starName)
            ->first();

        if (!$systeme) {
            $this->error("✗ Système stellaire non trouvé: {$starName}");
            $this->comment("💡 Assurez-vous que l'étoile existe dans la base de données (import GAIA)");
            return 1;
        }

        $this->info("✓ Système trouvé: {$systeme->nom} ({$systeme->type_etoile})");
        $this->newLine();

        $count = $this->exoplanetService->importExoplanetsForSystem($systeme);

        if ($count > 0) {
            $this->info("✅ {$count} exoplanète(s) importée(s) pour {$systeme->nom}");
            return 0;
        } else {
            $this->comment("ℹ️  Aucune exoplanète trouvée pour {$systeme->nom} dans le catalogue NASA");
            return 0;
        }
    }

    /**
     * Importer les exoplanètes pour tous les systèmes GAIA existants
     */
    protected function importForGaiaSystems(): int
    {
        $this->info('🌟 Import pour tous les systèmes GAIA existants');
        $this->newLine();

        $systemes = SystemeStellaire::where('source_gaia', true)->get();

        if ($systemes->isEmpty()) {
            $this->error('✗ Aucun système GAIA trouvé dans la base de données');
            $this->comment('💡 Lancez d\'abord: php artisan gaia:import-real');
            return 1;
        }

        $this->info("📦 {$systemes->count()} systèmes GAIA trouvés");
        $this->newLine();

        $bar = $this->output->createProgressBar($systemes->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Démarrage...');
        $bar->start();

        $totalImported = 0;
        $systemesWithPlanets = 0;

        foreach ($systemes as $systeme) {
            $bar->setMessage("Import pour {$systeme->nom}...");

            $count = $this->exoplanetService->importExoplanetsForSystem($systeme);

            if ($count > 0) {
                $totalImported += $count;
                $systemesWithPlanets++;
            }

            $bar->advance();
        }

        $bar->setMessage('Import terminé!');
        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Import terminé:");
        $this->info("   • {$totalImported} exoplanètes importées");
        $this->info("   • {$systemesWithPlanets} systèmes avec des planètes");
        $this->info("   • " . ($systemes->count() - $systemesWithPlanets) . " systèmes sans exoplanètes connues");

        return 0;
    }

    /**
     * Importer les exoplanètes dans un rayon et les associer aux systèmes GAIA
     */
    protected function importInRadius(float $radius, int $limit): int
    {
        $this->info("🌌 Import des exoplanètes dans un rayon de {$radius} AL");
        $this->info("   Limite: {$limit} exoplanètes maximum");
        $this->newLine();

        $this->info('📡 Interrogation de l\'API NASA Exoplanet Archive...');
        $exoplanets = $this->exoplanetService->getExoplanetsInRadius($radius, $limit);

        if (empty($exoplanets)) {
            $this->error('✗ Aucune exoplanète trouvée dans ce rayon');
            return 1;
        }

        $this->info("✓ {" . count($exoplanets) . "} exoplanètes trouvées");
        $this->newLine();

        // Grouper par étoile
        $byHost = [];
        foreach ($exoplanets as $exo) {
            $hostname = $exo['hostname'];
            if (!isset($byHost[$hostname])) {
                $byHost[$hostname] = [];
            }
            $byHost[$hostname][] = $exo;
        }

        $this->info("🌟 " . count($byHost) . " systèmes stellaires différents");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($byHost));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->start();

        $totalImported = 0;
        $systemesFound = 0;
        $systemesNotFound = 0;

        foreach ($byHost as $hostname => $planets) {
            $bar->setMessage("Recherche {$hostname}...");

            // Chercher le système stellaire correspondant
            $systeme = SystemeStellaire::where('nom', $hostname)
                ->orWhere('nom_commun', $hostname)
                ->orWhereJsonContains('noms_alternatifs', $hostname)
                ->orWhere('gaia_source_id', 'like', "%{$hostname}%")
                ->first();

            if ($systeme) {
                $systemesFound++;

                // Importer les planètes
                foreach ($planets as $exoData) {
                    $planete = $this->exoplanetService->createPlanetFromExoplanetData($systeme, $exoData);
                    if ($planete) {
                        $totalImported++;
                    }
                }

                // Mettre à jour le nombre de planètes
                $systeme->nb_planetes = $systeme->planetes()->count();
                $systeme->save();
            } else {
                $systemesNotFound++;
            }

            $bar->advance();
        }

        $bar->setMessage('Import terminé!');
        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Import terminé:");
        $this->info("   • {$totalImported} exoplanètes importées");
        $this->info("   • {$systemesFound} systèmes trouvés dans la base");
        $this->info("   • {$systemesNotFound} systèmes non trouvés (étoiles pas encore importées)");
        $this->newLine();

        if ($systemesNotFound > 0) {
            $this->comment("💡 Pour importer les étoiles manquantes:");
            $this->comment("   php artisan gaia:import-real --radius={$radius}");
        }

        return 0;
    }
}
