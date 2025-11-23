<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Decouverte;

class FixSolarSystemDiscoveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:fix-solar-discoveries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajoute les découvertes du Système Solaire (PoI connus) aux personnages existants qui ne les ont pas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction des découvertes du Système Solaire...');

        // Récupérer tous les systèmes avec poi_connu = true
        $systemesConnus = SystemeStellaire::where('poi_connu', true)->get();

        if ($systemesConnus->isEmpty()) {
            $this->warn('⚠️  Aucun système avec poi_connu = true trouvé.');
            $this->info('💡 Assurez-vous que le GaiaSeeder a été exécuté pour créer le Système Solaire.');
            return 1;
        }

        $this->info("📍 {$systemesConnus->count()} systèmes PoI connus trouvés:");
        foreach ($systemesConnus as $systeme) {
            $this->line("   - {$systeme->nom}");
        }

        // Récupérer tous les personnages
        $personnages = Personnage::all();

        if ($personnages->isEmpty()) {
            $this->warn('⚠️  Aucun personnage trouvé.');
            return 0;
        }

        $this->info("\n👥 {$personnages->count()} personnages trouvés.");

        $bar = $this->output->createProgressBar($personnages->count());
        $bar->start();

        $totalAjoute = 0;
        $totalDeja = 0;

        foreach ($personnages as $personnage) {
            $ajoutesPourCePerso = 0;

            foreach ($systemesConnus as $systeme) {
                // Vérifier si la découverte existe déjà
                $existe = Decouverte::where('personnage_id', $personnage->id)
                    ->where('systeme_stellaire_id', $systeme->id)
                    ->exists();

                if (!$existe) {
                    // Créer la découverte
                    Decouverte::create([
                        'personnage_id' => $personnage->id,
                        'systeme_stellaire_id' => $systeme->id,
                        'resultat_scan' => 9999,
                        'seuil_detection' => 0,
                        'distance_decouverte' => 0.0,
                        'decouvert_a' => now(),
                        'coordonnees_connues' => true,
                        'type_etoile_connu' => true,
                        'nb_planetes_connu' => true,
                        'visite' => false,
                    ]);

                    $ajoutesPourCePerso++;
                    $totalAjoute++;
                } else {
                    $totalDeja++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Correction terminée!");
        $this->info("   📝 {$totalAjoute} découvertes ajoutées");
        $this->info("   ✓ {$totalDeja} découvertes déjà existantes");

        return 0;
    }
}
