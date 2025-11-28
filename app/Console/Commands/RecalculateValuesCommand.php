<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use Illuminate\Support\Facades\DB;

class RecalculateValuesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cs:recalculer-valeurs
                            {--type= : Type de recalcul (systemes, planetes, all) [défaut: all]}
                            {--dry-run : Afficher les changements sans les appliquer}
                            {--system-id= : ID d\'un système spécifique à recalculer}
                            {--planet-id= : ID d\'une planète spécifique à recalculer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule les valeurs des systèmes stellaires, planètes et POI';

    /**
     * Mapping des types spectraux vers plages de puissance
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
        $type = $this->option('type') ?? 'all';
        $dryRun = $this->option('dry-run');
        $systemId = $this->option('system-id');
        $planetId = $this->option('planet-id');

        $this->info('🔄 Recalcul des valeurs du jeu Conquête Spatiale');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Aucune modification ne sera effectuée');
            $this->newLine();
        }

        $stats = [
            'systemes_traites' => 0,
            'systemes_mis_a_jour' => 0,
            'planetes_traitees' => 0,
            'planetes_mises_a_jour' => 0,
            'erreurs' => 0,
        ];

        // Recalcul des systèmes stellaires
        if (in_array($type, ['all', 'systemes'])) {
            $this->info('━━━ SYSTÈMES STELLAIRES ━━━');
            $statsSystemes = $this->recalculerSystemes($dryRun, $systemId);
            $stats['systemes_traites'] = $statsSystemes['traites'];
            $stats['systemes_mis_a_jour'] = $statsSystemes['mis_a_jour'];
            $stats['erreurs'] += $statsSystemes['erreurs'];
            $this->newLine();
        }

        // Recalcul des planètes
        if (in_array($type, ['all', 'planetes'])) {
            $this->info('━━━ PLANÈTES ━━━');
            $statsPlanetes = $this->recalculerPlanetes($dryRun, $systemId, $planetId);
            $stats['planetes_traitees'] = $statsPlanetes['traites'];
            $stats['planetes_mises_a_jour'] = $statsPlanetes['mis_a_jour'];
            $stats['erreurs'] += $statsPlanetes['erreurs'];
            $this->newLine();
        }

        // Affichage des statistiques finales
        $this->info('✅ Traitement terminé');
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Systèmes traités', $stats['systemes_traites']],
                ['Systèmes mis à jour', $stats['systemes_mis_a_jour']],
                ['Planètes traitées', $stats['planetes_traitees']],
                ['Planètes mises à jour', $stats['planetes_mises_a_jour']],
                ['Erreurs', $stats['erreurs']],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  Mode DRY-RUN : Aucune modification n\'a été effectuée');
            $this->info('💡 Exécutez sans --dry-run pour appliquer les changements');
        }

        return 0;
    }

    /**
     * Recalcule les valeurs des systèmes stellaires
     */
    protected function recalculerSystemes(bool $dryRun, ?int $systemId = null): array
    {
        $query = SystemeStellaire::query();

        if ($systemId) {
            $query->where('id', $systemId);
        }

        $systemes = $query->get();
        $total = $systemes->count();

        if ($total === 0) {
            $this->warn('Aucun système trouvé.');
            return ['traites' => 0, 'mis_a_jour' => 0, 'erreurs' => 0];
        }

        $this->info("📦 {$total} système(s) à traiter");

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Démarrage...');
        $bar->start();

        $updated = 0;
        $errors = 0;

        foreach ($systemes as $systeme) {
            $bar->setMessage("Traitement: {$systeme->nom}");

            try {
                $modified = false;

                // Extraire la classe spectrale (première lettre)
                $typeClass = strtoupper(substr($systeme->type_etoile, 0, 1));

                if (!isset($this->puissances[$typeClass])) {
                    $typeClass = 'G'; // Défaut : type solaire
                }

                [$min, $max] = $this->puissances[$typeClass];

                // Exception pour Sol
                if ($systeme->nom === 'Sol') {
                    $nouvellePuissance = 50;
                } else {
                    // Recalculer puissance
                    $nouvellePuissance = rand($min, $max);
                }

                if ($systeme->puissance !== $nouvellePuissance) {
                    if (!$dryRun) {
                        $systeme->puissance = $nouvellePuissance;
                    }
                    $modified = true;
                }

                // Recalculer détectabilité : (200 - puissance) / 3
                $nouvelleDetectabilite = round((200 - $nouvellePuissance) / 3, 2);

                if ($systeme->detectabilite_base !== $nouvelleDetectabilite) {
                    if (!$dryRun) {
                        $systeme->detectabilite_base = $nouvelleDetectabilite;
                    }
                    $modified = true;
                }

                // Recalculer puissance_solaire (basé sur les propriétés du type d'étoile)
                $props = SystemeStellaire::getProprietesEtoile($typeClass);
                $nouvellePuissanceSolaire = rand($props['puissance_min'], $props['puissance_max']);

                if ($systeme->puissance_solaire !== $nouvellePuissanceSolaire) {
                    if (!$dryRun) {
                        $systeme->puissance_solaire = $nouvellePuissanceSolaire;
                    }
                    $modified = true;
                }

                if ($modified) {
                    if (!$dryRun) {
                        $systeme->save();
                    }
                    $updated++;
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("\nErreur pour {$systeme->nom}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->setMessage('Terminé!');
        $bar->finish();
        $this->newLine();

        return [
            'traites' => $total,
            'mis_a_jour' => $updated,
            'erreurs' => $errors,
        ];
    }

    /**
     * Recalcule les valeurs des planètes
     */
    protected function recalculerPlanetes(bool $dryRun, ?int $systemId = null, ?int $planetId = null): array
    {
        $query = Planete::with('systemeStellaire');

        if ($planetId) {
            $query->where('id', $planetId);
        } elseif ($systemId) {
            $query->where('systeme_stellaire_id', $systemId);
        }

        $planetes = $query->get();
        $total = $planetes->count();

        if ($total === 0) {
            $this->warn('Aucune planète trouvée.');
            return ['traites' => 0, 'mis_a_jour' => 0, 'erreurs' => 0];
        }

        $this->info("🪐 {$total} planète(s) à traiter");

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Démarrage...');
        $bar->start();

        $updated = 0;
        $errors = 0;

        foreach ($planetes as $planete) {
            $bar->setMessage("Traitement: {$planete->nom}");

            try {
                $modified = false;
                $anciennesValeurs = [
                    'rayon' => $planete->rayon,
                    'masse' => $planete->masse,
                    'gravite' => $planete->gravite,
                    'habitable' => $planete->habitable,
                ];

                // Recalculer les propriétés physiques
                $planete->genererProprietes();

                // Recalculer la température si le système stellaire existe
                if ($planete->systemeStellaire && $planete->systemeStellaire->puissance_solaire) {
                    $planete->calculerTemperature($planete->systemeStellaire->puissance_solaire);
                }

                // Recalculer l'habitabilité
                $planete->calculerHabitabilite();

                // Vérifier si des modifications ont été faites
                if ($anciennesValeurs['rayon'] !== $planete->rayon ||
                    $anciennesValeurs['masse'] !== $planete->masse ||
                    $anciennesValeurs['gravite'] !== $planete->gravite ||
                    $anciennesValeurs['habitable'] !== $planete->habitable) {
                    $modified = true;
                }

                if ($modified) {
                    if (!$dryRun) {
                        $planete->save();
                    }
                    $updated++;
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("\nErreur pour {$planete->nom}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->setMessage('Terminé!');
        $bar->finish();
        $this->newLine();

        return [
            'traites' => $total,
            'mis_a_jour' => $updated,
            'erreurs' => $errors,
        ];
    }
}
