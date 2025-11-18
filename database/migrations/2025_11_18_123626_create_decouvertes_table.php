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
        Schema::create('decouvertes', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');
            $table->foreignId('systeme_stellaire_id')->constrained('systemes_stellaires')->onDelete('cascade');

            // Données de découverte
            $table->integer('resultat_scan')->nullable(); // Résultat du jet 2d12
            $table->integer('seuil_detection')->nullable(); // Seuil à atteindre au moment du scan
            $table->float('distance_decouverte')->nullable(); // Distance au moment de la découverte
            $table->timestamp('decouvert_a'); // Date de découverte

            // Informations révélées
            $table->boolean('coordonnees_connues')->default(true); // Coordonnées toujours révélées
            $table->boolean('type_etoile_connu')->default(false); // Type révélé si scan réussi
            $table->boolean('nb_planetes_connu')->default(false); // Nombre de planètes révélé
            $table->boolean('visite')->default(false); // Le personnage s'y est rendu

            $table->text('notes')->nullable(); // Notes personnelles du joueur

            $table->timestamps();

            // Index pour recherches fréquentes
            $table->unique(['personnage_id', 'systeme_stellaire_id'], 'idx_unique_decouverte');
            $table->index('personnage_id', 'idx_personnage_decouvertes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decouvertes');
    }
};
