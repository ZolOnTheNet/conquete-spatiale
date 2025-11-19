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
        Schema::table('vaisseaux', function (Blueprint $table) {
            // Points de structure
            $table->integer('coque_max')->default(100)->after('reserve');
            $table->integer('coque_actuelle')->default(100)->after('coque_max');

            // Equipement armes (jusqu'a 3 emplacements)
            $table->foreignId('arme_1_id')->nullable()->constrained('armes')->onDelete('set null');
            $table->foreignId('arme_2_id')->nullable()->constrained('armes')->onDelete('set null');
            $table->foreignId('arme_3_id')->nullable()->constrained('armes')->onDelete('set null');

            // Equipement bouclier
            $table->foreignId('bouclier_id')->nullable()->constrained('boucliers')->onDelete('set null');
            $table->integer('bouclier_actuel')->default(0); // Points actuels

            // Statistiques combat
            $table->integer('esquive')->default(10); // % d'esquive
            $table->integer('bonus_precision')->default(0);

            // Etat
            $table->boolean('en_combat')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->dropForeign(['arme_1_id']);
            $table->dropForeign(['arme_2_id']);
            $table->dropForeign(['arme_3_id']);
            $table->dropForeign(['bouclier_id']);

            $table->dropColumn([
                'coque_max',
                'coque_actuelle',
                'arme_1_id',
                'arme_2_id',
                'arme_3_id',
                'bouclier_id',
                'bouclier_actuel',
                'esquive',
                'bonus_precision',
                'en_combat',
            ]);
        });
    }
};
