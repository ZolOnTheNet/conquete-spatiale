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
        Schema::table('vaisseaux', function (Blueprint $table) {
            // Champs pour le système orbital
            $table->unsignedBigInteger('orbite_planete_id')->nullable()->after('arrime_a_station_id');
            $table->decimal('orbite_rayon_ua', 10, 4)->nullable()->after('orbite_planete_id')->comment('Rayon orbital en UA');
            $table->decimal('orbite_angle_initial', 10, 6)->nullable()->after('orbite_rayon_ua')->comment('Angle initial en radians');
            $table->timestamp('orbite_debut')->nullable()->after('orbite_angle_initial')->comment('Date début orbite (timestamp du jeu)');

            // Clé étrangère
            $table->foreign('orbite_planete_id')->references('id')->on('planetes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->dropForeign(['orbite_planete_id']);
            $table->dropColumn(['orbite_planete_id', 'orbite_rayon_ua', 'orbite_angle_initial', 'orbite_debut']);
        });
    }
};
