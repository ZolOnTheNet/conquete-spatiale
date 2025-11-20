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
        Schema::create('gisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planete_id')->constrained()->onDelete('cascade');
            $table->foreignId('ressource_id')->constrained()->onDelete('cascade');

            // Position sur la planète (optionnel, pour plusieurs gisements/planète)
            $table->decimal('latitude', 8, 5)->nullable();
            $table->decimal('longitude', 9, 5)->nullable();

            // Caractéristiques du gisement
            $table->integer('richesse')->default(100); // 0-100 (% rendement)
            $table->bigInteger('quantite_totale')->default(1000000); // Unités totales
            $table->bigInteger('quantite_restante')->default(1000000);

            // État
            $table->boolean('decouvert')->default(false);
            $table->timestamp('decouvert_le')->nullable();
            $table->foreignId('decouvert_par')->nullable()->constrained('personnages');

            // Exploitation
            $table->boolean('en_exploitation')->default(false);
            $table->foreignId('exploite_par')->nullable()->constrained('personnages');

            $table->timestamps();

            // Index composites
            $table->index(['planete_id', 'ressource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gisements');
    }
};
