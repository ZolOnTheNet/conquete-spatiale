<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour ajouter la relation parent polymorphique aux objets spatiaux
 *
 * Permet de créer une hiérarchie d'objets :
 * - Astéroïde notable → parent = ZoneSpatiale
 * - Station orbitale → parent = Planete (via ObjetSpatial)
 * - Débris → parent = Station détruite
 * - Vaisseau amarré → parent = Station/Planete
 *
 * La relation polymorphique permet de référencer n'importe quel type de parent :
 * - parent_type: 'App\Models\ZoneSpatiale'
 * - parent_id: ID de la zone
 *
 * @see docs/game-design/GDD_Asteroides.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            // Relation polymorphique parent
            $table->string('parent_type')->nullable()->after('azimut');
            $table->unsignedBigInteger('parent_id')->nullable()->after('parent_type');

            // Index composite pour performance des requêtes
            $table->index(['parent_type', 'parent_id'], 'idx_objets_parent');

            // Note: Pas de FK car polymorphique (peut pointer vers différentes tables)
        });
    }

    public function down(): void
    {
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->dropIndex('idx_objets_parent');
            $table->dropColumn(['parent_type', 'parent_id']);
        });
    }
};
