<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mettre à jour les vaisseaux existants avec des valeurs par défaut pour le scan
        DB::table('vaisseaux')
            ->whereNull('portee_scan')
            ->orWhereNull('puissance_scan')
            ->orWhereNull('bonus_scan')
            ->orWhereNull('scan_niveau_actuel')
            ->update([
                'portee_scan' => DB::raw('COALESCE(portee_scan, 5.0)'),
                'puissance_scan' => DB::raw('COALESCE(puissance_scan, 20)'),
                'bonus_scan' => DB::raw('COALESCE(bonus_scan, 0)'),
                'scan_niveau_actuel' => DB::raw('COALESCE(scan_niveau_actuel, 0)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback nécessaire
    }
};
