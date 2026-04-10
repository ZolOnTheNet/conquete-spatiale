<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter l'azimut (orientation horizontale 0-360°) aux objets spatiaux
     *
     * L'azimut représente la direction "avant" du vaisseau dans le plan horizontal (XY)
     * - 0° = Nord (Y+)
     * - 90° = Est (X+)
     * - 180° = Sud (Y-)
     * - 270° = Ouest (X-)
     */
    public function up(): void
    {
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->decimal('azimut', 5, 2)->default(0.00)->after('position_z')
                ->comment('Orientation horizontale en degrés (0-360°), 0° = Y+');
        });

        echo "✓ Colonne azimut ajoutée à objets_spatiaux\n";
        echo "  - Valeur par défaut : 0.00° (direction Y+)\n";
        echo "  - Mise à jour automatique lors des déplacements\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->dropColumn('azimut');
        });
    }
};
