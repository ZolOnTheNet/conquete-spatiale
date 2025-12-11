<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            // Données orbitales (optimisées pour calcul)
            $table->double('angle_orbital_initial')->default(0)->after('periode_orbitale')
                ->comment('Angle initial en radians (0 à 2π)');

            $table->double('vitesse_angulaire')->nullable()->after('angle_orbital_initial')
                ->comment('Vitesse angulaire en rad/jour (2π/période)');

            // Cache de position (pour optimisation)
            $table->decimal('cache_position_x', 12, 6)->nullable()->after('vitesse_angulaire')
                ->comment('Position X en UA (cache)');

            $table->decimal('cache_position_y', 12, 6)->nullable()->after('cache_position_x')
                ->comment('Position Y en UA (cache)');

            $table->decimal('cache_position_z', 12, 6)->nullable()->after('cache_position_y')
                ->comment('Position Z en UA (cache)');

            $table->bigInteger('cache_timestamp_jours')->nullable()->after('cache_position_z')
                ->comment('Timestamp en jours depuis 3000-01-01 (cache)');

            $table->integer('cache_validite_jours')->default(10)->after('cache_timestamp_jours')
                ->comment('Nombre de jours avant recalcul');
        });

        // Initialiser les données orbitales pour les planètes existantes
        $this->initializeOrbitalData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            $table->dropColumn([
                'angle_orbital_initial',
                'vitesse_angulaire',
                'cache_position_x',
                'cache_position_y',
                'cache_position_z',
                'cache_timestamp_jours',
                'cache_validite_jours',
            ]);
        });
    }

    /**
     * Initialiser les données orbitales pour toutes les planètes
     */
    protected function initializeOrbitalData(): void
    {
        $planetes = DB::table('planetes')->get();

        foreach ($planetes as $planete) {
            // Si pas de période orbitale, la calculer avec Loi de Kepler
            $periode = $planete->periode_orbitale;

            if (!$periode && $planete->distance_etoile) {
                // T = 365.25 × sqrt(d³)
                $periode = round(365.25 * pow($planete->distance_etoile, 1.5));
            }

            // Vitesse angulaire = 2π / période (en rad/jour)
            $vitesseAngulaire = $periode > 0 ? (2 * M_PI) / $periode : 0;

            // Angle initial aléatoire (0 à 2π radians)
            $angleInitial = lcg_value() * 2 * M_PI;

            // Validité du cache : plus la planète est lente, plus le cache est valide
            // Formule : max(10, période/50) jours
            $cacheValidite = $periode > 0 ? max(10, (int)($periode / 50)) : 10;

            DB::table('planetes')
                ->where('id', $planete->id)
                ->update([
                    'periode_orbitale' => $periode,
                    'angle_orbital_initial' => $angleInitial,
                    'vitesse_angulaire' => $vitesseAngulaire,
                    'cache_validite_jours' => $cacheValidite,
                    // Cache initialisé à null, sera calculé au premier accès
                    'cache_position_x' => null,
                    'cache_position_y' => null,
                    'cache_position_z' => null,
                    'cache_timestamp_jours' => null,
                ]);
        }

        echo "✓ Données orbitales initialisées pour {$planetes->count()} planètes\n";
    }
};
