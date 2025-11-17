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
        Schema::create('vaisseaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objet_spatial_id')->constrained('objets_spatiaux')->onDelete('cascade');
            $table->string('modele')->default('A-0'); // A-0, A-1, M-series, etc.

            // === PROPULSION ===
            $table->integer('type_propulsion')->default(1);
            $table->string('mode')->default('energetique'); // 'combustible' | 'energetique'
            $table->decimal('reserve', 10, 2)->default(0); // UE stockable
            $table->decimal('energie_actuelle', 10, 2)->default(0);

            // Vitesses
            $table->decimal('vitesse_conventionnelle', 8, 2)->default(1.0);
            $table->decimal('vitesse_saut', 8, 2)->default(10.0);

            // Pannes
            $table->integer('part_panne')->default(0);

            // Combustible (si applicable)
            $table->decimal('combustible', 10, 2)->nullable();
            $table->decimal('efficacite', 6, 2)->nullable();
            $table->string('type_combustible')->nullable();
            $table->decimal('recuperation', 6, 2)->nullable();

            // Coefficients (selon GDD_Vaisseaux_Complet.md)
            $table->decimal('init_conventionnel', 8, 2)->default(0);
            $table->decimal('init_hyperespace', 8, 2)->default(200);
            $table->decimal('coef_conventionnel', 8, 2)->default(1.0);
            $table->decimal('coef_hyperespace', 8, 2)->default(1.0);
            $table->decimal('coef_pa_mn', 8, 2)->default(1.0);
            $table->decimal('coef_pa_he', 8, 2)->default(0.2);

            // === SOUTE ===
            $table->integer('max_soutes')->default(0);
            $table->integer('place_soute')->default(0);
            $table->decimal('masse_variable', 10, 2)->default(0);
            $table->json('soutes')->nullable(); // Tableau de cargos

            // === ARMEMENT ===
            $table->json('emplacements_armes')->nullable();
            $table->integer('nb_armes')->default(0);

            // === MAINTENANCE ===
            $table->integer('vetuste')->default(0);
            $table->integer('complexite_fct')->default(1);
            $table->integer('score_panne')->default(0);
            $table->integer('score_entretien')->default(0);
            $table->json('pannes_actuelles')->nullable();

            // === INFORMATIQUE ===
            $table->integer('system_informatique')->default(0);
            $table->json('programmes')->nullable(); // Programmes et niveaux

            // === EMPLACEMENTS (12 selon GDD) ===
            $table->json('emplacements')->nullable(); // Structure des 12 emplacements

            // Logs
            $table->json('date_logs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaisseaux');
    }
};
