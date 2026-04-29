<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convertir distance_etoile de UA vers cUA (multiplier par 100)
     *
     * IMPORTANT: Toutes les positions sont en cUA (entiers) pour éviter les erreurs d'arrondi.
     * Cette migration uniformise distance_etoile pour qu'elle soit aussi en cUA.
     */
    public function up(): void
    {
        // Multiplier toutes les distances par 100 (UA → cUA)
        DB::statement('UPDATE planetes SET distance_etoile = distance_etoile * 100');

        echo "✓ Toutes les distances distance_etoile converties de UA vers cUA (×100)\n";
    }

    /**
     * Reverse: convertir cUA vers UA (diviser par 100)
     */
    public function down(): void
    {
        // Diviser toutes les distances par 100 (cUA → UA)
        DB::statement('UPDATE planetes SET distance_etoile = distance_etoile / 100');

        echo "✓ Toutes les distances distance_etoile reconverties de cUA vers UA (÷100)\n";
    }
};
