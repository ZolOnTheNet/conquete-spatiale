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
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();

            // Propriétaire (polymorphic)
            $table->morphs('conteneur'); // conteneur_type, conteneur_id
            // Exemples: Vaisseau, Base, Personnage

            $table->foreignId('ressource_id')->constrained()->onDelete('cascade');
            $table->bigInteger('quantite')->default(0);

            $table->timestamps();

            // Unicité: 1 ligne par ressource par conteneur
            $table->unique(['conteneur_type', 'conteneur_id', 'ressource_id'], 'inventaire_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaires');
    }
};
