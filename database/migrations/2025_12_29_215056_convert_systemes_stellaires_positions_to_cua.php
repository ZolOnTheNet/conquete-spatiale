<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convertir les positions des systèmes stellaires de AL (fractions) vers cUA (entiers)
     *
     * PROBLÈME IDENTIFIÉ: Les positions étaient stockées comme fractions d'AL (0.45, 0.81, etc.)
     * au lieu de cUA (centièmes d'UA).
     *
     * CONVERSION: 1 AL = 63,241 UA = 6,324,100 cUA
     *
     * Exemple: position_x = 0.45134385566649 AL → 2,854,513 cUA
     */
    public function up(): void
    {
        // Conversion factor: 1 AL = 6,324,100 cUA
        $conversionFactor = 6324100;

        echo "\n[MIGRATION] Conversion des positions des systèmes stellaires vers cUA...\n";

        // Compter les systèmes à convertir
        $count = DB::table('systemes_stellaires')->count();
        echo "[INFO] Nombre de systèmes stellaires à convertir: {$count}\n";

        // Exemple avant conversion
        $sample = DB::table('systemes_stellaires')->first();
        if ($sample) {
            echo "[AVANT] Exemple - {$sample->nom}:\n";
            echo "  position_x: {$sample->position_x} AL\n";
            echo "  position_y: {$sample->position_y} AL\n";
            echo "  position_z: {$sample->position_z} AL\n";
        }

        // Conversion des positions: multiplier par 6,324,100 et arrondir à l'entier
        DB::statement("
            UPDATE systemes_stellaires
            SET
                position_x = ROUND(position_x * {$conversionFactor}),
                position_y = ROUND(position_y * {$conversionFactor}),
                position_z = ROUND(position_z * {$conversionFactor})
        ");

        // Exemple après conversion
        $sampleAfter = DB::table('systemes_stellaires')->where('id', $sample->id ?? 1)->first();
        if ($sampleAfter) {
            echo "[APRÈS] Exemple - {$sampleAfter->nom}:\n";
            echo "  position_x: {$sampleAfter->position_x} cUA\n";
            echo "  position_y: {$sampleAfter->position_y} cUA\n";
            echo "  position_z: {$sampleAfter->position_z} cUA\n";
        }

        echo "[SUCCESS] {$count} systèmes stellaires convertis de AL vers cUA ✓\n\n";
    }

    /**
     * Reverse the migrations.
     * ATTENTION: Cette conversion inverse entraînera une perte de précision
     */
    public function down(): void
    {
        // Conversion inverse: diviser par 6,324,100
        $conversionFactor = 6324100;

        echo "\n[ROLLBACK] Reconversion des positions des systèmes stellaires vers AL...\n";
        echo "[WARNING] Cette opération entraînera une perte de précision.\n";

        DB::statement("
            UPDATE systemes_stellaires
            SET
                position_x = position_x / {$conversionFactor},
                position_y = position_y / {$conversionFactor},
                position_z = position_z / {$conversionFactor}
        ");

        $count = DB::table('systemes_stellaires')->count();
        echo "[DONE] {$count} systèmes stellaires reconvertis vers AL\n\n";
    }
};
