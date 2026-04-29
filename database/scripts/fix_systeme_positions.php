<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

echo "=== CORRECTION DES POSITIONS DES SYSTÈMES STELLAIRES ===" . PHP_EOL . PHP_EOL;

// Pour les systèmes stellaires, la position fine (position_x/y/z) devrait être (0,0,0)
// car la position principale est déjà définie par secteur_x/y/z (en AL)

$systemes = SystemeStellaire::all();
$corrected = 0;

foreach ($systemes as $systeme) {
    // Si la position est non-nulle, la corriger
    if ($systeme->position_x != 0 || $systeme->position_y != 0 || $systeme->position_z != 0) {
        echo "Correction de {$systeme->nom}:" . PHP_EOL;
        echo "  Avant: position ({$systeme->position_x}, {$systeme->position_y}, {$systeme->position_z}) cUA" . PHP_EOL;

        $systeme->position_x = 0;
        $systeme->position_y = 0;
        $systeme->position_z = 0;
        $systeme->save();

        echo "  Après: position (0, 0, 0) cUA" . PHP_EOL;
        echo PHP_EOL;
        $corrected++;
    }
}

echo "=== CORRECTION TERMINÉE ===" . PHP_EOL;
echo "Systèmes corrigés : $corrected sur " . $systemes->count() . PHP_EOL;
