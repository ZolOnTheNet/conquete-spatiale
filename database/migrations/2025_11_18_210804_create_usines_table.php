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
        Schema::create('usines', function (Blueprint $table) {
            $table->id();
            $table->string('nom');

            // Proprietaire
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');

            // Localisation polymorphique (Planete ou Station)
            $table->morphs('localisation');

            // Type d'usine
            $table->enum('type', [
                'atelier',      // Petit, polyvalent
                'raffinerie',   // Specialise raffinage
                'forge',        // Specialise alliages
                'laboratoire',  // Specialise avance
            ])->default('atelier');

            // Caracteristiques
            $table->integer('niveau')->default(1);
            $table->integer('energie_max')->default(100);
            $table->integer('energie_actuelle')->default(100);
            $table->decimal('efficacite', 5, 2)->default(1.0); // Multiplicateur vitesse

            // Production en cours
            $table->foreignId('recette_en_cours_id')->nullable()->constrained('recettes')->onDelete('set null');
            $table->integer('quantite_en_cours')->default(0);
            $table->timestamp('production_debut')->nullable();
            $table->timestamp('production_fin')->nullable();

            // Etat
            $table->boolean('actif')->default(true);

            $table->timestamps();

            // Index
            $table->index('personnage_id');
            $table->index(['localisation_type', 'localisation_id']);
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usines');
    }
};
