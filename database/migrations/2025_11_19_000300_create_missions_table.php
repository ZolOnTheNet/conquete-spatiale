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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('titre', 150);
            $table->text('description');

            // Faction qui donne la mission
            $table->foreignId('faction_id')->nullable()->constrained('factions')->onDelete('set null');

            // Type de mission
            $table->enum('type', [
                'livraison',        // Transporter des ressources
                'exploration',      // Decouvrir des systemes
                'combat',           // Eliminer des ennemis
                'collecte',         // Collecter des ressources
                'escorte',          // Proteger un convoi
                'espionnage',       // Recuperer des informations
                'commerce',         // Realiser des transactions
            ])->default('livraison');

            // Difficulte
            $table->enum('difficulte', ['facile', 'normal', 'difficile', 'expert'])->default('normal');
            $table->integer('niveau_requis')->default(1);
            $table->integer('reputation_requise')->default(0); // Reputation min avec faction

            // Objectifs (JSON)
            $table->json('objectifs'); // [{type, cible, quantite, progression}]

            // Recompenses
            $table->integer('recompense_credits')->default(0);
            $table->integer('recompense_xp')->default(0);
            $table->integer('recompense_reputation')->default(50);
            $table->json('recompense_objets')->nullable(); // [{ressource_id, quantite}]

            // Penalites en cas d'echec/abandon
            $table->integer('penalite_reputation')->default(25);

            // Limites
            $table->integer('duree_limite')->nullable(); // En tours/heures
            $table->boolean('repetable')->default(false);
            $table->integer('cooldown')->nullable(); // Temps avant de reprendre (minutes)

            // Localisation
            $table->foreignId('systeme_depart_id')->nullable()->constrained('systemes_stellaires')->onDelete('set null');
            $table->foreignId('systeme_arrivee_id')->nullable()->constrained('systemes_stellaires')->onDelete('set null');

            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
