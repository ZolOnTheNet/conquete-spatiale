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
        Schema::create('boucliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');

            // Type de bouclier
            $table->enum('type', [
                'energie',      // Bouclier energetique standard
                'coque',        // Blindage renforce
                'regeneratif',  // Se regenere rapidement
                'adaptatif',    // S'adapte aux degats
            ])->default('energie');

            // Caracteristiques defense
            $table->integer('points_max')->default(100); // Points de bouclier
            $table->integer('regeneration')->default(5); // Points regeneres par tour
            $table->integer('resistance')->default(0); // % reduction degats

            // Efficacite contre types d'armes (% reduction bonus)
            $table->integer('vs_laser')->default(0);
            $table->integer('vs_canon')->default(0);
            $table->integer('vs_missile')->default(0);
            $table->integer('vs_plasma')->default(0);
            $table->integer('vs_emp')->default(0);

            // Cout
            $table->integer('energie_maintien')->default(10); // Energie par tour
            $table->integer('niveau_requis')->default(1);
            $table->integer('prix')->default(1500);

            // Emplacement
            $table->enum('taille', ['petit', 'moyen', 'grand'])->default('moyen');

            // Meta
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);

            $table->timestamps();

            // Index
            $table->index('type');
            $table->index('taille');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boucliers');
    }
};
