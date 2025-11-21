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
        // Ajouter champs d'arrimage aux vaisseaux
        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->foreignId('arrime_a_station_id')->nullable()->after('objet_spatial_id')->constrained('stations')->onDelete('set null');
            $table->timestamp('arrime_le')->nullable()->after('arrime_a_station_id');
            $table->json('dernier_jet_pilotage')->nullable()->after('arrime_le');
        });

        // Ajouter champ pour savoir si personnage est dans une station
        Schema::table('personnages', function (Blueprint $table) {
            $table->foreignId('dans_station_id')->nullable()->after('vaisseau_actif_id')->constrained('stations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->dropForeign(['arrime_a_station_id']);
            $table->dropColumn(['arrime_a_station_id', 'arrime_le', 'dernier_jet_pilotage']);
        });

        Schema::table('personnages', function (Blueprint $table) {
            $table->dropForeign(['dans_station_id']);
            $table->dropColumn('dans_station_id');
        });
    }
};
