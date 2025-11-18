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
        Schema::table('personnages', function (Blueprint $table) {
            // Modifier les valeurs par défaut de PA (10 -> 36)
            $table->integer('points_action')->default(36)->change();
            $table->integer('max_points_action')->default(36)->change();

            // Ajouter timestamp de dernière récupération
            $table->timestamp('derniere_recuperation_pa')->nullable()->after('max_points_action');
        });

        // Initialiser le timestamp pour les personnages existants
        DB::table('personnages')
            ->whereNull('derniere_recuperation_pa')
            ->update(['derniere_recuperation_pa' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            // Remettre les valeurs par défaut à 10
            $table->integer('points_action')->default(10)->change();
            $table->integer('max_points_action')->default(10)->change();

            // Supprimer le timestamp
            $table->dropColumn('derniere_recuperation_pa');
        });
    }
};
