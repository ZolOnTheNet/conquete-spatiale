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
            // === SYSTÈME DE SCAN ===
            // Caractéristiques du scanner (améliorables)
            $table->decimal('portee_scan', 6, 2)->default(5.0)->after('system_informatique');  // Portée en années-lumière
            $table->integer('puissance_scan')->default(100)->after('portee_scan');              // Puissance de base (100 = standard)
            $table->integer('bonus_scan')->default(0)->after('puissance_scan');                 // Bonus d'équipements

            // État du scan en cours (scan progressif)
            $table->integer('scan_niveau_actuel')->default(0)->after('bonus_scan');             // Niveau cumulé (0 = pas de scan en cours)
            $table->integer('scan_secteur_x')->nullable()->after('scan_niveau_actuel');         // Position du scan en cours
            $table->integer('scan_secteur_y')->nullable()->after('scan_secteur_x');
            $table->integer('scan_secteur_z')->nullable()->after('scan_secteur_y');
            $table->decimal('scan_position_x', 10, 3)->nullable()->after('scan_secteur_z');
            $table->decimal('scan_position_y', 10, 3)->nullable()->after('scan_position_x');
            $table->decimal('scan_position_z', 10, 3)->nullable()->after('scan_position_y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->dropColumn([
                'portee_scan',
                'puissance_scan',
                'bonus_scan',
                'scan_niveau_actuel',
                'scan_secteur_x',
                'scan_secteur_y',
                'scan_secteur_z',
                'scan_position_x',
                'scan_position_y',
                'scan_position_z',
            ]);
        });
    }
};
