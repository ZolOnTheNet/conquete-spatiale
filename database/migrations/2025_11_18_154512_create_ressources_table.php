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
        Schema::create('ressources', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // FER, GRAPHITE, etc.
            $table->string('nom', 100);
            $table->string('categorie', 50); // metaux, gaz, chimie, exotique, elementaire
            $table->text('description')->nullable();
            $table->decimal('poids_unitaire', 8, 3)->default(1.0); // tonnes/unité
            $table->decimal('prix_base', 12, 2)->default(100); // crédits/unité
            $table->integer('rarete')->default(50); // 1-100 (1=très rare, 100=très commun)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ressources');
    }
};
