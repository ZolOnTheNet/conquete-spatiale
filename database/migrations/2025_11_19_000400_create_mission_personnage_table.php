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
        Schema::create('mission_personnage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');

            // Statut de la mission
            $table->enum('statut', [
                'disponible',   // Peut etre acceptee
                'en_cours',     // Acceptee, en progression
                'completee',    // Objectifs atteints
                'rendue',       // Recompenses recues
                'echouee',      // Echec (temps, mort, etc)
                'abandonnee',   // Abandonnee par joueur
            ])->default('disponible');

            // Progression des objectifs
            $table->json('progression')->nullable(); // [{objectif_index, actuel, requis}]

            // Dates importantes
            $table->timestamp('acceptee_le')->nullable();
            $table->timestamp('completee_le')->nullable();
            $table->timestamp('expire_le')->nullable();

            // Nombre de fois completee (si repetable)
            $table->integer('fois_completee')->default(0);
            $table->timestamp('dernier_cooldown')->nullable();

            $table->timestamps();

            // Index
            $table->index(['personnage_id', 'statut']);
            $table->index(['mission_id', 'personnage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_personnage');
    }
};
