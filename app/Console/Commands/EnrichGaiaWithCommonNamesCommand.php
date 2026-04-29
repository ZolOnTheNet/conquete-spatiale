<?php

namespace App\Console\Commands;

use App\Models\SystemeStellaire;
use App\Services\StarNameMatcher;
use Illuminate\Console\Command;

class EnrichGaiaWithCommonNamesCommand extends Command
{
    protected $signature = 'gaia:enrich-common-names
                            {--force : Forcer même si déjà enrichi}';

    protected $description = 'Enrichit les étoiles GAIA existantes avec leurs noms communs';

    protected StarNameMatcher $starMatcher;

    public function __construct(StarNameMatcher $starMatcher)
    {
        parent::__construct();
        $this->starMatcher = $starMatcher;
    }

    public function handle(): int
    {
        $this->info('🔗 Enrichissement des étoiles GAIA avec noms communs');
        $this->newLine();

        $force = $this->option('force');

        // Récupérer les systèmes GAIA à enrichir
        $query = SystemeStellaire::where('source_gaia', true)
            ->whereNotNull('gaia_ra')
            ->whereNotNull('gaia_dec')
            ->whereNotNull('gaia_distance_ly');

        if (!$force) {
            // Par défaut, enrichir seulement ceux qui n'ont pas encore de nom commun
            $query->whereNull('nom_commun');
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

        foreach ($systemes as $systeme) {
            $bar->setMessage("Traitement: {$systeme->nom}");

            // Chercher nom commun via coordonnées
            $match = $this->starMatcher->findCommonName(
                $systeme->gaia_ra,
                $systeme->gaia_dec,
                $systeme->gaia_distance_ly
            );

            if ($match) {
                $oldName = $systeme->nom;

                $systeme->nom_commun = $match['nom_commun'];
                $systeme->noms_alternatifs = json_encode($match['aliases']);
                $systeme->save();

                $enriched++;
                $this->newLine();
                $this->line("  ✓ {$oldName} → <fg=green>{$match['nom_commun']}</>");
                $bar->setMessage("Enrichi: {$match['nom_commun']}");
            } else {
                $notFound++;
            }

            $bar->advance();
        }

        $bar->setMessage('Enrichissement terminé!');
        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Enrichissement terminé:');
        $this->info("   • {$enriched} systèmes enrichis avec noms communs");
        $this->info("   • {$notFound} systèmes sans nom commun trouvé");

        $this->newLine();

        // Afficher quelques exemples
        $examples = SystemeStellaire::where('source_gaia', true)
            ->whereNotNull('nom_commun')
            ->limit(5)
            ->get(['nom', 'nom_commun', 'gaia_distance_ly']);

        if ($examples->isNotEmpty()) {
            $this->info('🌟 Exemples de noms enrichis:');
            foreach ($examples as $ex) {
                $dist = number_format($ex->gaia_distance_ly, 2);
                $this->line("   • {$ex->nom} → {$ex->nom_commun} ({$dist} AL)");
            }
        }

        return 0;
    }
}
