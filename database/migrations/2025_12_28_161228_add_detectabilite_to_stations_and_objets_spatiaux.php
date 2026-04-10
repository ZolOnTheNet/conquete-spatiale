<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter detectabilite_base et poi_connu à stations
        Schema::table('stations', function (Blueprint $table) {
            $table->decimal('detectabilite_base', 8, 2)->default(0)->after('description')
                ->comment('Score de détectabilité (0 = calculé automatiquement)');
            $table->boolean('poi_connu')->default(false)->after('detectabilite_base')
                ->comment('Visible sur la carte (découvert)');
        });

        // Ajouter detectabilite_base et poi_connu à objets_spatiaux
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->decimal('detectabilite_base', 8, 2)->default(0)->after('type')
                ->comment('Score de détectabilité (0 = calculé automatiquement)');
            $table->boolean('poi_connu')->default(false)->after('detectabilite_base')
                ->comment('Visible sur la carte (découvert)');
        });

        // Ajouter detectabilite_base à systemes_stellaires si pas déjà présent
        if (!Schema::hasColumn('systemes_stellaires', 'detectabilite_base')) {
            Schema::table('systemes_stellaires', function (Blueprint $table) {
                $table->decimal('detectabilite_base', 8, 2)->default(0)->after('puissance_solaire')
                    ->comment('Score de détectabilité (0 = calculé automatiquement)');
            });
        }

        // Ajouter detectabilite_base à planetes si pas déjà présent
        if (!Schema::hasColumn('planetes', 'detectabilite_base')) {
            Schema::table('planetes', function (Blueprint $table) {
                $table->decimal('detectabilite_base', 8, 2)->default(0)->after('rayon')
                    ->comment('Score de détectabilité (0 = calculé automatiquement)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn(['detectabilite_base', 'poi_connu']);
        });

        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->dropColumn(['detectabilite_base', 'poi_connu']);
        });

        // Ne pas supprimer de systemes_stellaires et planetes car peut-être déjà présent
    }
};
