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
            $table->decimal('detectabilite_base', 10, 2)->nullable()->after('rayon');
            $table->boolean('poi_connu')->default(false)->after('detectabilite_base');
        });

        // Calculer la détectabilité pour les planètes existantes
        $this->calculatePlanetDetectability();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            $table->dropColumn(['detectabilite_base', 'poi_connu']);
        });
    }

    /**
     * Calculer la détectabilité basée sur la taille (rayon)
     * Formule : D_base = 150 - (Taille × 10)
     */
    protected function calculatePlanetDetectability(): void
    {
        $planetes = DB::table('planetes')->get();

        foreach ($planetes as $planete) {
            // Rayon en rayons terrestres
            $rayon = $planete->rayon ?? 1.0;

            // Formule : D_base = 150 - (Taille × 10)
            $detectabilite = 150 - ($rayon * 10);

            // Limiter entre un minimum et maximum raisonnable
            $detectabilite = max(1, min(150, $detectabilite));

            // Planètes importantes du système solaire = PoI connus
            $poiConnu = in_array($planete->nom, ['Terre', 'Lune', 'Mars', 'Jupiter', 'Neptune']);

            DB::table('planetes')
                ->where('id', $planete->id)
                ->update([
                    'detectabilite_base' => round($detectabilite, 2),
                    'poi_connu' => $poiConnu,
                ]);
        }
    }
};
