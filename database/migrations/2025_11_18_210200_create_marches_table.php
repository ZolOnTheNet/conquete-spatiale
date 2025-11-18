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
        Schema::create('marches', function (Blueprint $table) {
            $table->id();
            $table->string('nom');

            // Type de marché
            $table->enum('type', [
                'commercial',    // Marché standard
                'minier',        // Spécialisé ressources brutes
                'industriel',    // Spécialisé produits transformés
                'contrebande',   // Marché noir
            ])->default('commercial');

            // Localisation polymorphique (Planete ou Station)
            $table->morphs('localisation');

            // Multiplicateurs de prix
            $table->decimal('multiplicateur_achat', 5, 2)->default(1.0);  // Prix d'achat pour le joueur
            $table->decimal('multiplicateur_vente', 5, 2)->default(0.8); // Prix de vente pour le joueur
            $table->decimal('taxe', 5, 2)->default(0.05); // 5% taxe par défaut

            // État
            $table->boolean('actif')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();

            // Index
            $table->index(['localisation_type', 'localisation_id']);
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marches');
    }
};
