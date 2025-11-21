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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code')->unique(); // Code court pour commandes
            $table->enum('type', [
                'matiere_premiere',  // Minerais bruts, ressources naturelles
                'matiere_raffinee',  // Minerais raffinés, métaux purs
                'composant',         // Composants électroniques, mécaniques
                'manufacture',       // Objets manufacturés, équipements
                'consommable',       // Nourriture, eau, médicaments
                'carburant',         // Hydrogène, deutérium, antimatière
                'luxe',             // Articles de luxe
            ]);
            $table->text('description')->nullable();

            // Caractéristiques physiques
            $table->decimal('volume_unite', 10, 3)->default(1.0); // m³ par unité
            $table->decimal('masse_unite', 10, 3)->default(1.0);  // tonnes par unité

            // Économie
            $table->decimal('prix_base', 12, 2)->default(100.0); // Prix de référence
            $table->boolean('illegal')->default(false);
            $table->integer('niveau_technologique')->default(1); // 1-10

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
