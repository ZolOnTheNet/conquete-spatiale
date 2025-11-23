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
        Schema::create('mines', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Ex: "MAME-Fer-Mars-Alpha"

            // Localisation
            $table->foreignId('planete_id')->constrained('planetes')->onDelete('cascade');
            $table->foreignId('gisement_id')->constrained('gisements')->onDelete('cascade');

            // Position orbitale (si mine orbitale) ou surface
            $table->enum('emplacement', ['surface', 'orbite'])->default('surface');
            $table->decimal('orbite_rayon_ua', 10, 6)->nullable(); // Si orbite
            $table->decimal('orbite_angle', 10, 4)->nullable(); // Angle orbital (0-360°)

            // Propriété
            $table->foreignId('installateur_id')->nullable()->constrained('personnages')->onDelete('set null'); // Qui l'a installé
            $table->foreignId('proprietaire_id')->nullable()->constrained('personnages')->onDelete('set null'); // Propriétaire actuel
            $table->timestamp('installe_a')->nullable(); // Date d'installation

            // Caractéristiques techniques
            $table->string('modele')->default('MAME-Standard'); // Type de MAME
            $table->integer('capacite_stockage')->default(10000); // Unités de ressource stockables
            $table->integer('stock_actuel')->default(0); // Quantité actuellement stockée
            $table->decimal('taux_extraction', 8, 2)->default(100.0); // Unités/jour (temps de jeu)

            // État opérationnel
            $table->enum('statut', ['active', 'inactive', 'maintenance', 'endommagee'])->default('active');
            $table->integer('niveau_usure')->default(0); // 0-100% (100 = nécessite maintenance)
            $table->timestamp('derniere_maintenance')->nullable();
            $table->timestamp('derniere_extraction')->nullable();

            // Consommation de ressources (pour fonctionner)
            $table->integer('energie_consommee')->default(10); // Unités énergie/jour
            $table->integer('pieces_rechange_consommees')->default(1); // Pièces/mois
            $table->integer('pieces_usure_consommees')->default(5); // Pièces/mois

            // Stock de consommables (pour autonomie)
            $table->integer('stock_energie')->default(0);
            $table->integer('stock_pieces_rechange')->default(0);
            $table->integer('stock_pieces_usure')->default(0);

            // Accès et sécurité
            $table->boolean('acces_public')->default(false); // Autoriser accès à tous
            $table->text('autorises_ids')->nullable(); // JSON array de personnage_ids autorisés
            $table->boolean('acces_faction')->default(false); // Autoriser membres de la faction propriétaire
            $table->foreignId('faction_id')->nullable()->constrained('factions')->onDelete('set null');

            // Interface avec base (optionnel - pour plus tard)
            $table->foreignId('base_id')->nullable()->constrained('bases')->onDelete('set null');
            $table->boolean('connectee_base')->default(false); // Mine connectée à une base

            // Économie
            $table->integer('prix_achat')->nullable(); // Prix d'achat initial (si vendue)
            $table->integer('valeur_estimee')->default(50000); // Valeur marchande

            // Détectabilité
            $table->boolean('poi_connu')->default(false); // Visible sur la carte
            $table->decimal('detectabilite_base', 8, 2)->default(30.0); // Score de détection

            $table->text('description')->nullable();
            $table->timestamps();

            // Index pour recherches fréquentes
            $table->index('proprietaire_id');
            $table->index('planete_id');
            $table->index('gisement_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mines');
    }
};
