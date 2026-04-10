<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour lier Station à ObjetSpatial
 *
 * CONTEXTE :
 * Une station EST un objet spatial et doit avoir une position 3D complète.
 * Cette migration ajoute la FK vers objets_spatiaux.
 *
 * AVANT : Station {planete_id, orbite_rayon_ua, orbite_angle}
 *         → Pas de position propre
 *
 * APRÈS : Station {objet_spatial_id → ObjetSpatial {secteur_x/y/z, position_x/y/z}}
 *         → Position complète héritée d'ObjetSpatial
 *
 * ATTENTION : Exécuter scripts/migrate_stations_to_objets_spatiaux.php
 *             AVANT cette migration pour créer les ObjetSpatial correspondants
 *
 * @see scripts/migrate_stations_to_objets_spatiaux.php
 * @see docs/TODO_ZONES_ASTEROIDES.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            // Lien vers objet spatial (position 3D)
            $table->foreignId('objet_spatial_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('objets_spatiaux')
                  ->onDelete('cascade');

            $table->index('objet_spatial_id');
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropForeign(['objet_spatial_id']);
            $table->dropIndex(['objet_spatial_id']);
            $table->dropColumn('objet_spatial_id');
        });
    }
};
