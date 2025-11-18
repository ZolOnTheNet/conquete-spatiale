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
        Schema::create('systemes_stellaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();

            // Classification stellaire (O, B, A, F, G, K, M selon GDD)
            $table->enum('type_etoile', ['O', 'B', 'A', 'F', 'G', 'K', 'M'])->default('G');
            $table->string('couleur')->nullable(); // Ex: Bleue, Jaune, Rouge
            $table->integer('temperature')->nullable(); // En Kelvin
            $table->decimal('puissance_solaire', 8, 2)->default(50); // 20-200 selon GDD
            $table->decimal('masse_solaire', 10, 2)->default(1.0); // En masses solaires
            $table->decimal('rayon_solaire', 10, 2)->default(1.0); // En rayons solaires

            // Position 3D (Secteur + Position décimale)
            $table->integer('secteur_x')->default(0);
            $table->integer('secteur_y')->default(0);
            $table->integer('secteur_z')->default(0);
            $table->decimal('position_x', 10, 3)->default(0);
            $table->decimal('position_y', 10, 3)->default(0);
            $table->decimal('position_z', 10, 3)->default(0);

            // Index pour recherche rapide par secteur
            $table->index(['secteur_x', 'secteur_y', 'secteur_z'], 'idx_secteur_systeme');

            // Propriétés du système
            $table->integer('nb_planetes')->default(0);
            $table->boolean('explore')->default(false); // Découvert par au moins un joueur
            $table->boolean('habite')->default(false); // Contient des habitants

            // Métadonnées
            $table->text('description')->nullable();
            $table->json('donnees_supplementaires')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systemes_stellaires');
    }
};
