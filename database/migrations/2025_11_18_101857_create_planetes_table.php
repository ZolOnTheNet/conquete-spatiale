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
        Schema::create('planetes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('systeme_stellaire_id')->constrained('systemes_stellaires')->onDelete('cascade');
            $table->string('nom');

            // Type de planète
            $table->enum('type', [
                'terrestre',      // Rocheuse comme la Terre
                'gazeuse',        // Géante gazeuse comme Jupiter
                'naine',          // Planète naine comme Pluton
                'oceanique',      // Monde océan
                'glacee',         // Monde de glace
                'volcanique',     // Monde volcanique
                'desert',         // Monde désertique
            ])->default('terrestre');

            // Caractéristiques physiques
            $table->decimal('rayon', 10, 2)->nullable(); // En rayons terrestres
            $table->decimal('masse', 10, 2)->nullable(); // En masses terrestres
            $table->decimal('gravite', 6, 2)->default(1.0); // En G (1 = gravité terrestre)
            $table->decimal('distance_etoile', 10, 4)->nullable(); // En UA
            $table->integer('periode_orbitale')->nullable(); // En jours terrestres

            // Habitabilité
            $table->boolean('habitable')->default(false);
            $table->boolean('habitee')->default(false); // Présence de population
            $table->bigInteger('population')->default(0);

            // Atmosphère
            $table->boolean('a_atmosphere')->default(false);
            $table->string('composition_atmosphere')->nullable(); // Ex: N2, O2, CO2, etc.
            $table->decimal('pression_atmospherique', 8, 2)->nullable(); // En bars

            // Ressources minières (selon GDD_Economie)
            $table->json('gisements')->nullable(); // {"fer": 30, "or": 10, "tyberium": 5}
            $table->integer('rendement_base')->default(30); // % de rendement

            // Température
            $table->integer('temperature_moyenne')->nullable(); // En °C
            $table->integer('temperature_min')->nullable();
            $table->integer('temperature_max')->nullable();

            // Métadonnées
            $table->text('description')->nullable();
            $table->json('donnees_supplementaires')->nullable();
            $table->timestamps();

            // Index
            $table->index('systeme_stellaire_id');
            $table->index('habitable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planetes');
    }
};
