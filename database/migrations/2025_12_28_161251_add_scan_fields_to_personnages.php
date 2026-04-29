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
        Schema::table('personnages', function (Blueprint $table) {
            // Ajouter seulement les champs manquants (finesse et savoir existent déjà)
            if (!Schema::hasColumn('personnages', 'maitrise')) {
                $table->integer('maitrise')->default(0)->after('savoir')
                    ->comment('Compétence Maîtrise (expertise générale)');
            }

            if (!Schema::hasColumn('personnages', 'scan_bonus_dice_type')) {
                $table->integer('scan_bonus_dice_type')->default(0)->after('niveau')
                    ->comment('Type de dé de bonus scan: 0=aucun, 4/6/8/10/12=bonus, -6=malus');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            // Supprimer seulement les champs que nous avons ajoutés
            if (Schema::hasColumn('personnages', 'maitrise')) {
                $table->dropColumn('maitrise');
            }
            if (Schema::hasColumn('personnages', 'scan_bonus_dice_type')) {
                $table->dropColumn('scan_bonus_dice_type');
            }
        });
    }
};
