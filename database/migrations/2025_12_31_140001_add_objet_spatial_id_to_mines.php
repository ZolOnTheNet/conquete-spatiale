<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour lier Mine à ObjetSpatial
 *
 * CONTEXTE :
 * Une mine (MAME ou évoluée) EST un objet spatial et doit avoir une position 3D.
 *
 * Types de mines :
 * - MAME (Mine Autonome) : Petite, attachée à ressource (planète/astéroïde)
 * - Évoluée : Devient station avec MCS (Module Création Station)
 *
 * Cette migration ajoute la FK vers objets_spatiaux.
 *
 * AVANT : Mine {planete_id, base_id, orbite_rayon_ua}
 *         → Pas de position propre
 *
 * APRÈS : Mine {objet_spatial_id → ObjetSpatial {secteur_x/y/z, position_x/y/z}}
 *         → Position complète héritée d'ObjetSpatial
 *
 * ATTENTION : Exécuter scripts/migrate_mines_to_objets_spatiaux.php
 *             AVANT cette migration pour créer les ObjetSpatial correspondants
 *
 * @see scripts/migrate_mines_to_objets_spatiaux.php
 * @see docs/TODO_ZONES_ASTEROIDES.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mines', function (Blueprint $table) {
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
        Schema::table('mines', function (Blueprint $table) {
            $table->dropForeign(['objet_spatial_id']);
            $table->dropIndex(['objet_spatial_id']);
            $table->dropColumn('objet_spatial_id');
        });
    }
};
