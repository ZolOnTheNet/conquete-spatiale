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
        Schema::create('recettes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');

            // Type de recette
            $table->enum('categorie', [
                'raffinage',    // Transformation brute (minerai -> metal)
                'alliage',      // Combinaison metaux
                'composant',    // Pieces detachees
                'avance',       // High-tech, exotique
            ])->default('raffinage');

            // Temps et difficulte
            $table->integer('temps_fabrication')->default(60); // En secondes
            $table->integer('niveau_requis')->default(1);
            $table->integer('energie_requise')->default(10); // Energie usine

            // Ingredients (JSON: [{"ressource_id": 1, "quantite": 100}, ...])
            $table->json('ingredients');

            // Produits (JSON: [{"ressource_id": 2, "quantite": 50}, ...])
            $table->json('produits');

            // Meta
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);

            $table->timestamps();

            // Index
            $table->index('categorie');
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recettes');
    }
};
