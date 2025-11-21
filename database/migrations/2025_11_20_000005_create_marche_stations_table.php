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
        Schema::create('marche_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');

            // Stocks
            $table->integer('stock_actuel')->default(0);
            $table->integer('stock_min')->default(0);      // Seuil d'alerte
            $table->integer('stock_max')->default(10000);  // Capacité maximale

            // Production/Consommation (par jour)
            $table->decimal('production_par_jour', 10, 2)->default(0);
            $table->decimal('consommation_par_jour', 10, 2)->default(0);

            // Type économique
            $table->enum('type_economique', [
                'producteur',     // Production > Consommation (prix vente bas)
                'consommateur',   // Consommation > Production (prix vente élevé)
                'equilibre',      // Production ≈ Consommation (prix moyen)
                'transit',        // Ni production ni consommation (prix standard)
            ])->default('transit');

            // Prix (calculés dynamiquement)
            $table->decimal('prix_achat_joueur', 12, 2); // Prix auquel la station ACHÈTE au joueur
            $table->decimal('prix_vente_joueur', 12, 2); // Prix auquel la station VEND au joueur
            $table->timestamp('derniere_mise_a_jour_prix')->nullable();

            // Disponibilité
            $table->boolean('disponible_achat')->default(true);  // Station achète ce produit
            $table->boolean('disponible_vente')->default(true);  // Station vend ce produit

            $table->timestamps();

            // Index
            $table->unique(['station_id', 'produit_id']);
            $table->index('type_economique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marche_stations');
    }
};
