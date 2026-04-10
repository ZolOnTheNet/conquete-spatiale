<?php

namespace App\Console\Commands;

use App\Models\SystemeStellaire;
use App\Services\SimbadService;
use Illuminate\Console\Command;

class EnrichGaiaWithSimbadCommand extends Command
{
    protected $signature = 'gaia:enrich-simbad
                            {--limit=50 : Nombre de systèmes à enrichir (0 = tous)}
                            {--force : Forcer même si déjà enrichi}
                            {--clear-cache : Vider le cache SIMBAD avant}';

    protected $description = 'Enrichit les noms GAIA avec leurs noms communs depuis SIMBAD';

    protected SimbadService $simbadService;

    public function __construct(SimbadService $simbadService)
    {
        parent::__construct();
        $this->simbadService = $simbadService;
    }

    public function handle(): int
    {
        $this->info('🔗 Enrichissement GAIA → Noms communs (via SIMBAD)');
        $this->newLine();

        if ($this->option('clear-cache')) {
            $this->simbadService->clearCache();
            $this->info('✓ Cache SIMBAD vidé');
            $this->newLine();
        }

        $limit = (int)$this->option('limit');
        $force = $this->option('force');

        // Récupérer les systèmes GAIA à enrichir
        $query = SystemeStellaire::where('source_gaia', true)
            ->whereNotNull('gaia_source_id');

        if (!$force) {
            // Par défaut, enrichir seulement ceux qui ont un nom GAIA DR3
            $query->where('nom', 'like', 'GAIA DR3%');
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $systemes = $query->get();

        if ($systemes->isEmpty()) {
            $this->comment('ℹ️  Aucun système à enrichir');
            return 0;
        }

        $this->info("📦 {$systemes->count()} systèmes à traiter");
        $this->newLine();

        $bar = $this->output->createProgressBar($systemes->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Démarrage...');
        $bar->start();

        $enriched = 0;
        $notFound = 0;
        $errors = 0;

        foreach ($systemes as $systeme) {
            $bar->setMessage("Traitement: {$systeme->nom}");

            try {
                // Obtenir le nom principal depuis SIMBAD
                $mainName = $this->simbadService->getMainNameFromGaiaId($systeme->gaia_source_id);

                if ($mainName) {
                    // Vérifier si c'est un nom commun
                    if ($this->isCommonName($mainName)) {
                        $oldName = $systeme->nom;
                        $systeme->nom = $mainName;
                        $systeme->save();

                        $enriched++;
                        $this->newLine();
                        $this->line("  ✓ {$oldName} → <fg=green>{$mainName}</>");
                        $bar->setMessage("Enrichi: {$mainName}");
                    } else {
                        // Nom technique trouvé, mais pas plus utile que GAIA DR3
                        $notFound++;
                    }
                } else {
                    $notFound++;
                }

            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("  ✗ Erreur pour {$systeme->nom}: " . $e->getMessage());
            }

            $bar->advance();

            // Petit délai pour ne pas surcharger SIMBAD
            usleep(100000); // 0.1 seconde
        }

        $bar->setMessage('Enrichissement terminé!');
        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Enrichissement terminé:');
        $this->info("   • {$enriched} systèmes enrichis avec noms communs");
        $this->info("   • {$notFound} systèmes sans nom commun trouvé");
        if ($errors > 0) {
            $this->warn("   • {$errors} erreurs");
        }

        $this->newLine();

        // Afficher quelques exemples
        $examples = SystemeStellaire::where('source_gaia', true)
            ->whereNotNull('gaia_source_id')
            ->whereNotLike('nom', 'GAIA DR3%')
            ->limit(5)
            ->get(['nom', 'gaia_distance_ly']);

        if ($examples->isNotEmpty()) {
            $this->info('🌟 Exemples de noms enrichis:');
            foreach ($examples as $ex) {
                $dist = number_format($ex->gaia_distance_ly, 2);
                $this->line("   • {$ex->nom} ({$dist} AL)");
            }
        }

        return 0;
    }

    /**
     * Vérifier si un nom est un nom commun
     */
    protected function isCommonName(string $name): bool
    {
        // Liste de préfixes de noms communs
        $commonPrefixes = [
            'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta',
            'Proxima', 'Sirius', 'Vega', 'Altair', 'Rigel', 'Betelgeuse',
            'Tau', 'Sigma', 'Omega', 'Mu', 'Nu', 'Xi', 'Omicron', 'Pi',
        ];

        // Catalogues techniques à exclure
        $technicalPrefixes = [
            'GAIA DR3', 'HD ', 'HR ', 'HIP ', 'TYC ', '2MASS', 'WISE',
        ];

        // Exclure noms techniques
        foreach ($technicalPrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return false;
            }
        }

        // Vérifier noms communs
        foreach ($commonPrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
