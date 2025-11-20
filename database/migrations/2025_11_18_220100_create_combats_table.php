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
        Schema::create('combats', function (Blueprint $table) {
            $table->id();

            // Participants
            $table->foreignId('vaisseau_id')->constrained('vaisseaux')->onDelete('cascade');
            $table->foreignId('ennemi_id')->constrained('ennemis')->onDelete('cascade');

            // Etat du combat
            $table->enum('statut', ['en_cours', 'victoire', 'defaite', 'fuite'])->default('en_cours');
            $table->integer('tour')->default(1);

            // Etat de l'ennemi dans ce combat
            $table->integer('ennemi_coque')->default(100);
            $table->integer('ennemi_bouclier')->default(50);

            // Historique des actions
            $table->json('log')->nullable();

            // Recompenses accordees
            $table->integer('credits_gagnes')->default(0);
            $table->integer('xp_gagne')->default(0);
            $table->json('butin')->nullable();

            // Position du combat
            $table->integer('coord_x')->default(0);
            $table->integer('coord_y')->default(0);
            $table->integer('coord_z')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combats');
    }
};
