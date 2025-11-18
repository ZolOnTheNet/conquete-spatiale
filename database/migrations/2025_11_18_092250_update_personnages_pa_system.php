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
            // Modifier les valeurs par défaut de PA
            // points_action: 24 (1 journée de départ)
            // max_points_action: 36 (capital max = 1,5 jours)
            $table->integer('points_action')->default(24)->change();
            $table->integer('max_points_action')->default(36)->change();

            // Ajouter timestamp de dernière récupération
            // null par défaut = chrono démarre à la première dépense
            $table->timestamp('derniere_recuperation_pa')->nullable()->after('max_points_action');
        });

        // NE PAS initialiser le timestamp pour les personnages existants
        // Le timestamp démarre uniquement à la première dépense de PA
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
