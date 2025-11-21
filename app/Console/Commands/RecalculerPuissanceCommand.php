<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemeStellaire;

class RecalculerPuissanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'systemes:recalculer-puissance
                            {--dry-run : Afficher les changements sans les appliquer}
                            {--filter= : Filtrer par type spectral (ex: G, M, K)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule la puissance des systèmes stellaires basé sur leur type spectral';

    /**
     * Mapping des types spectraux vers plages de puissance
     * Formule: min - 1 + 1d(max - min + 1)
     */
    protected $puissances = [
        'O' => [150, 200],
        'B' => [100, 140],
        'A' => [80, 100],
        'F' => [60, 80],
        'G' => [40, 60],
        'K' => [30, 40],
        'M' => [20, 30],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $filter = $this->option('filter');

        $this->info('🔄 Recalcul de la puissance des systèmes stellaires...');

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Aucune modification ne sera effectuée');
        }

        // Construire la requête
        $query = SystemeStellaire::query();

        if ($filter) {
            $filter = strtoupper($filter);
            $this->info("🔍 Filtre actif : Type spectral {$filter}");
            $query->where('type_etoile', 'LIKE', $filter . '%');
        }

        $systemes = $query->get();
        $total = $systemes->count();

        if ($total === 0) {
            $this->warn('Aucun système trouvé.');
            return 0;
        }

        $this->info("📦 {$total} systèmes à traiter");

        // Créer la barre de progression
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Démarrage...');
        $bar->start();

        $updated = 0;
        $unchanged = 0;
        $errors = 0;

        foreach ($systemes as $systeme) {
            $bar->setMessage("Traitement: {$systeme->nom}");

            try {
                // Extraire la classe spectrale (première lettre)
                $typeClass = strtoupper(substr($systeme->type_etoile, 0, 1));

                if (!isset($this->puissances[$typeClass])) {
                    $typeClass = 'G'; // Défaut : type solaire
                }

                [$min, $max] = $this->puissances[$typeClass];

                // Formule : min - 1 + 1d(max - min + 1)
                // Équivalent à : rand(min, max)
                $nouvellePuissance = $this->rollDice($min, $max);

                if ($systeme->puissance !== $nouvellePuissance) {
                    if (!$dryRun) {
                        $systeme->puissance = $nouvellePuissance;
                        $systeme->save();
                    }
                    $updated++;
                } else {
                    $unchanged++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nErreur pour {$systeme->nom}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->setMessage('Terminé!');
        $bar->finish();
        $this->newLine(2);

        // Statistiques
        $this->info('✅ Traitement terminé');
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Systèmes traités', $total],
                ['Mis à jour', $updated],
                ['Inchangés', $unchanged],
                ['Erreurs', $errors],
            ]
        );

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Aucune modification n\'a été effectuée');
            $this->info('💡 Exécutez sans --dry-run pour appliquer les changements');
        }

        return 0;
    }

    /**
     * Simule un lancer de dé selon la formule min - 1 + 1d(max - min + 1)
     *
     * @param int $min Valeur minimale
     * @param int $max Valeur maximale
     * @return int Résultat du lancer
     */
    protected function rollDice(int $min, int $max): int
    {
        // Formule : min - 1 + 1d(max - min + 1)
        // Équivalent à : rand(min, max)
        $dice = $max - $min + 1;
        $roll = rand(1, $dice);

        return ($min - 1) + $roll;
    }
}
