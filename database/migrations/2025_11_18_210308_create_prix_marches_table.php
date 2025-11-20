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
        Schema::create('prix_marches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marche_id')->constrained('marches')->onDelete('cascade');
            $table->foreignId('ressource_id')->constrained('ressources')->onDelete('cascade');

            // Prix dynamiques
            $table->decimal('prix_achat', 12, 2);  // Prix pour acheter (joueur achète)
            $table->decimal('prix_vente', 12, 2);  // Prix pour vendre (joueur vend)

            // Stock
            $table->integer('stock')->default(0);
            $table->integer('stock_max')->default(10000);

            // Dynamique du marché
            $table->decimal('demande', 5, 2)->default(1.0); // Coefficient de demande

            $table->timestamps();

            // Index
            $table->unique(['marche_id', 'ressource_id']);
            $table->index('ressource_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prix_marches');
    }
};
