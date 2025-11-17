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
        Schema::create('bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objet_spatial_id')->constrained('objets_spatiaux')->onDelete('cascade');
            $table->string('type_base')->default('arche'); // 'arche', 'station', etc.

            // Gestionnaire
            $table->foreignId('gestionnaire_id')->nullable()->constrained('personnages')->onDelete('set null');

            // Modules (selon GDD_Bases_Spatiales.md)
            // L'Arche = module maître + 5 modules + production énergie
            $table->json('modules')->nullable(); // Liste des modules installés

            // Production
            $table->decimal('production_energie', 10, 2)->default(0);
            $table->json('production_ressources')->nullable();

            // Capacités
            $table->integer('population_max')->default(0);
            $table->integer('population_actuelle')->default(0);
            $table->integer('defense')->default(0);

            // État
            $table->boolean('est_operationnelle')->default(true);
            $table->json('date_logs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bases');
    }
};
