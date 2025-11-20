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
        Schema::create('armes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');

            // Type d'arme
            $table->enum('type', [
                'laser',        // Rapide, precis, degats moyens
                'canon',        // Lent, degats eleves
                'missile',      // Tres lent, tres gros degats
                'plasma',       // Degats sur duree
                'emp',          // Desactive systemes
            ])->default('laser');

            // Caracteristiques combat
            $table->integer('degats_min')->default(10);
            $table->integer('degats_max')->default(20);
            $table->integer('portee')->default(100); // En unites
            $table->integer('cadence')->default(1); // Tirs par tour
            $table->integer('precision')->default(70); // % de chance de toucher

            // Cout
            $table->integer('energie_tir')->default(5); // Energie par tir
            $table->integer('niveau_requis')->default(1);
            $table->integer('prix')->default(1000);

            // Emplacement
            $table->enum('taille', ['petit', 'moyen', 'grand'])->default('petit');

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
        Schema::dropIfExists('armes');
    }
};
