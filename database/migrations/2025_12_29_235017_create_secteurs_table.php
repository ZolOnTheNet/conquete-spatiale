<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table secteurs pour stocker les métadonnées spatiales
     *
     * PRINCIPE:
     * - Un secteur = cube de 1 AL³ dans l'espace
     * - Coordonnées (secteur_x, secteur_y, secteur_z) en AL
     * - Création à la demande (lazy loading) lors du premier scan ou génération
     * - Cache de difficulté de scan pour performance
     * - Propagation de puissance : 30% de la puissance des secteurs adjacents
     */
    public function up(): void
    {
        Schema::create('secteurs', function (Blueprint $table) {
            $table->id();

            // ========== COORDONNÉES SPATIALES ==========
            $table->integer('secteur_x')->comment('Coordonnée X en AL (années-lumière)');
            $table->integer('secteur_y')->comment('Coordonnée Y en AL');
            $table->integer('secteur_z')->comment('Coordonnée Z en AL');

            // Index unique pour garantir un seul secteur par coordonnées
            $table->unique(['secteur_x', 'secteur_y', 'secteur_z'], 'unique_secteur_coords');

            // ========== CACHE DE SCAN ==========
            $table->integer('difficulte_scan')->default(5)
                ->comment('Difficulté de scan (5-25) calculée selon nb POI');
            $table->integer('nb_poi_locaux')->default(0)
                ->comment('Nombre de POI locaux (planètes, stations, mines) dans le secteur');
            $table->integer('nb_poi_lointains')->default(0)
                ->comment('Nombre de systèmes stellaires dans rayon 10 AL');
            $table->timestamp('derniere_maj_difficulte')->nullable()
                ->comment('Date dernière mise à jour de la difficulté');

            // ========== MÉTADONNÉES SPATIALES ==========
            $table->decimal('puissance_lieu', 10, 2)->default(0)
                ->comment('Puissance énergétique du secteur (avec propagation 30% des adjacents)');
            $table->decimal('densite_matiere', 10, 4)->default(0)
                ->comment('Densité de matière (pour minage/détection)');
            $table->integer('niveau_danger')->default(0)
                ->comment('Niveau de danger (0=sûr, 10=très dangereux)');
            $table->decimal('radiation_ambiante', 10, 2)->default(1)
                ->comment('Niveau de radiation (1=fond cosmique, 100+=mortel)');

            // ========== EXPLORATION ==========
            $table->boolean('explore')->default(false)
                ->comment('Le secteur a-t-il été visité par un joueur?');
            $table->integer('nb_visites')->default(0)
                ->comment('Nombre de fois où le secteur a été visité');
            $table->timestamp('premiere_visite')->nullable()
                ->comment('Date de première visite');
            $table->timestamp('derniere_visite')->nullable()
                ->comment('Date de dernière visite');

            // ========== NOMMAGE ET DESCRIPTION ==========
            $table->string('nom_region', 100)->nullable()
                ->comment('Nom de la région (ex: "Bordure de Sol", "Nébuleuse Obscure")');
            $table->text('description')->nullable()
                ->comment('Description du secteur');

            // ========== GÉNÉRATION PROCÉDURALE ==========
            $table->boolean('genere_proceduralement')->default(false)
                ->comment('Le secteur a-t-il été généré par l\'algorithme?');
            $table->bigInteger('seed_generation')->nullable()
                ->comment('Seed utilisé pour la génération procédurale');

            // Index pour recherches fréquentes
            $table->index('explore', 'idx_secteur_explore');
            $table->index('nom_region', 'idx_secteur_region');
            $table->index(['secteur_x', 'secteur_y'], 'idx_secteur_xy');

            $table->timestamps();
        });

        // Créer le secteur Sol (0,0,0) par défaut
        DB::table('secteurs')->insert([
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'difficulte_scan' => 5,
            'nb_poi_locaux' => 0,
            'nb_poi_lointains' => 0,
            'puissance_lieu' => 50, // Puissance du Soleil
            'explore' => true,
            'nom_region' => 'Système Sol',
            'description' => 'Le système solaire, berceau de l\'humanité',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "\n[INFO] Table 'secteurs' créée avec succès.\n";
        echo "[INFO] Secteur Sol (0,0,0) initialisé.\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secteurs');
        echo "\n[INFO] Table 'secteurs' supprimée.\n\n";
    }
};
