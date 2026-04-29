<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Vérifier si un index existe
     */
    protected function indexExists(string $table, string $index): bool
    {
        $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=?", [$table]);
        foreach ($indexes as $idx) {
            if ($idx->name === $index) {
                return true;
            }
        }
        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            // Métadonnées NASA Exoplanet Archive
            if (!Schema::hasColumn('planetes', 'source_nasa_exoplanet')) {
                $table->boolean('source_nasa_exoplanet')->default(false)->after('donnees_supplementaires')
                    ->comment('Planète issue du catalogue NASA Exoplanet Archive');
            }

            if (!Schema::hasColumn('planetes', 'nasa_exo_id')) {
                $table->string('nasa_exo_id')->nullable()->after('source_nasa_exoplanet')
                    ->comment('Identifiant dans le catalogue NASA (pl_name)');
            }

            if (!Schema::hasColumn('planetes', 'nasa_discovery_method')) {
                $table->string('nasa_discovery_method', 50)->nullable()->after('nasa_exo_id')
                    ->comment('Méthode de découverte (transit, radial velocity, imaging, etc.)');
            }

            if (!Schema::hasColumn('planetes', 'nasa_discovery_year')) {
                $table->integer('nasa_discovery_year')->nullable()->after('nasa_discovery_method')
                    ->comment('Année de découverte');
            }

            // Données orbitales avancées
            if (!Schema::hasColumn('planetes', 'excentricite_orbitale')) {
                $table->decimal('excentricite_orbitale', 8, 6)->default(0.0)->after('periode_orbitale')
                    ->comment('Excentricité de l\'orbite (0 = circulaire, <1 = elliptique)');
            }

            // Note: poi_connu et detectabilite_base existent déjà (migration 2025_11_20_000007)
            // Pas besoin de les ajouter à nouveau
        });

        // Ajouter les index seulement s'ils n'existent pas
        Schema::table('planetes', function (Blueprint $table) {
            if (!$this->indexExists('planetes', 'planetes_source_nasa_exoplanet_index')) {
                $table->index('source_nasa_exoplanet');
            }
            if (!$this->indexExists('planetes', 'planetes_nasa_exo_id_index')) {
                $table->index('nasa_exo_id');
            }
            // Index poi_connu existe déjà
        });

        // Ajouter les nouveaux types de planètes si nécessaire
        // Note: On ne peut pas modifier un ENUM directement en SQLite,
        // donc on utilise une approche conditionnelle pour MySQL/MariaDB
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE planetes MODIFY COLUMN type ENUM(
                'terrestre',
                'gazeuse',
                'naine',
                'oceanique',
                'glacee',
                'volcanique',
                'desert',
                'super-terrestre',
                'neptunienne'
            ) DEFAULT 'terrestre'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            // Supprimer uniquement les colonnes ajoutées par cette migration
            $columnsToRemove = [];

            if (Schema::hasColumn('planetes', 'source_nasa_exoplanet')) {
                $columnsToRemove[] = 'source_nasa_exoplanet';
            }
            if (Schema::hasColumn('planetes', 'nasa_exo_id')) {
                $columnsToRemove[] = 'nasa_exo_id';
            }
            if (Schema::hasColumn('planetes', 'nasa_discovery_method')) {
                $columnsToRemove[] = 'nasa_discovery_method';
            }
            if (Schema::hasColumn('planetes', 'nasa_discovery_year')) {
                $columnsToRemove[] = 'nasa_discovery_year';
            }
            if (Schema::hasColumn('planetes', 'excentricite_orbitale')) {
                $columnsToRemove[] = 'excentricite_orbitale';
            }

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
