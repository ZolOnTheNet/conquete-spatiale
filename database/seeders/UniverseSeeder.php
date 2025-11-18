<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\UniverseGeneratorService;

class UniverseSeeder extends Seeder
{
    /**
     * Initialise l'univers de jeu avec le Système Solaire et quelques systèmes voisins
     */
    public function run(): void
    {
        $generator = new UniverseGeneratorService();

        $this->command->info('🌌 Génération de l\'univers de départ...');

        // Générer le Système Solaire (point de départ)
        $this->command->info('☀️  Génération du Système Solaire...');
        $soleil = $generator->genererSystemeSolaire();
        $this->command->info("✅ Système Solaire créé: {$soleil->nom} ({$soleil->nb_planetes} planètes)");

        // Générer systèmes voisins
        $nb_voisins = config('game.univers.systemes_initiaux', 10);
        $rayon = config('game.univers.rayon_initial', 10.0);

        $this->command->info("🌟 Génération de {$nb_voisins} systèmes voisins (rayon {$rayon} années-lumière)...");
        $systemes = $generator->genererSystemesVoisins($nb_voisins, $rayon);

        foreach ($systemes as $systeme) {
            $habitable = $systeme->planetes()->where('habitable', true)->count();
            $marker = $habitable > 0 ? '🌍' : '⭐';
            $this->command->info(
                "{$marker} {$systeme->nom} (Type {$systeme->type_etoile}, {$systeme->nb_planetes} planètes, {$habitable} habitables)"
            );
        }

        $total_systemes = 1 + count($systemes);
        $total_planetes = $soleil->nb_planetes + collect($systemes)->sum('nb_planetes');
        $total_habitables = collect($systemes)->sum(function ($s) {
            return $s->planetes()->where('habitable', true)->count();
        }) + 1; // +1 pour la Terre

        $this->command->info('');
        $this->command->info("✨ Univers initialisé:");
        $this->command->info("   - {$total_systemes} systèmes stellaires");
        $this->command->info("   - {$total_planetes} planètes");
        $this->command->info("   - {$total_habitables} planètes habitables");
    }
}
