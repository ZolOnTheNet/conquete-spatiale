<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table zones_spatiales
 *
 * Cette table permet de définir des zones étendues dans l'espace comme :
 * - Ceintures d'astéroïdes (ex: ceinture principale, ceinture de Kuiper)
 * - Nuages de débris
 * - Champs de gaz/poussière
 *
 * Une zone est définie par :
 * - Un système stellaire parent
 * - Deux rayons (min/max) formant un anneau toroïdal
 * - Deux angles (azimut_debut/fin) permettant un arc de cercle partiel
 * - Une densité virtuelle d'objets
 *
 * @see docs/game-design/GDD_Asteroides.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones_spatiales', function (Blueprint $table) {
            $table->id();

            // Type de zone
            $table->string('type', 50); // 'ceinture_asteroides', 'nuage_debris', 'nebuleuse', etc.
            $table->string('nom')->nullable(); // Ex: "Ceinture Principale", "Ceinture de Kuiper"

            // Relation au système stellaire (une zone appartient à un système)
            $table->foreignId('systeme_stellaire_id')
                  ->constrained('systemes_stellaires')
                  ->onDelete('cascade');

            // Géométrie de la zone (anneau toroïdal)
            // IMPORTANT: Rayons en cUA (centi-UA) pour cohérence avec positions
            $table->integer('rayon_min')->comment('Rayon intérieur en cUA (ex: 220 cUA = 2.2 UA)');
            $table->integer('rayon_max')->comment('Rayon extérieur en cUA (ex: 320 cUA = 3.2 UA)');

            // Arc de cercle (angle en RADIANS pour calculs optimaux)
            // 0 rad = direction Y+ (nord théorique)
            // 2π rad = tour complet (cercle fermé)
            $table->decimal('azimut_debut', 10, 8)->default(0)->comment('Angle début en radians (0 à 2π)');
            $table->decimal('azimut_fin', 10, 8)->default(6.28318531)->comment('Angle fin en radians (2π = tour complet)');

            // Densité et gameplay
            $table->integer('densite')->default(50)->comment('Nombre virtuel d\'objets dans la zone');
            $table->decimal('vitesse_traversee_modif', 5, 2)->default(0.5)->comment('Multiplicateur vitesse (0.5 = -50%)');
            $table->decimal('risque_collision', 5, 2)->default(0.01)->comment('Probabilité collision par UA traversée');

            // Système de détection (compatible avec POI existants)
            $table->decimal('detectabilite_base', 10, 2)->default(30)->comment('Zones plus faciles à détecter (score plus bas)');
            $table->boolean('poi_connu')->default(false)->comment('True si zone déjà découverte');

            // Parent polymorphique optionnel (pour zones imbriquées)
            // Ex: sous-zone dans une grande zone
            $table->string('parent_type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->index(['parent_type', 'parent_id'], 'idx_zones_parent');

            // Métadonnées
            $table->text('description')->nullable();
            $table->json('proprietes')->nullable()->comment('Propriétés custom (minerais, dangers, etc.)');

            $table->timestamps();

            // Index pour performance
            $table->index('systeme_stellaire_id');
            $table->index('type');
            $table->index('poi_connu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones_spatiales');
    }
};
