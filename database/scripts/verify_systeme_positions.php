<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

echo "=== VÉRIFICATION DES POSITIONS DES SYSTÈMES STELLAIRES ===" . PHP_EOL . PHP_EOL;

// Compter tous les systèmes
$totalSystemes = SystemeStellaire::count();
echo "Total de systèmes stellaires: $totalSystemes" . PHP_EOL;

// Trouver les systèmes avec positions non-nulles
$systemesNonNuls = SystemeStellaire::where(function($query) {
    $query->where('position_x', '!=', 0)
          ->orWhere('position_y', '!=', 0)
          ->orWhere('position_z', '!=', 0);
})->get();

echo "Systèmes avec positions non-nulles: " . $systemesNonNuls->count() . PHP_EOL . PHP_EOL;

if ($systemesNonNuls->count() > 0) {
    echo "⚠️ Systèmes à corriger:" . PHP_EOL;
    foreach ($systemesNonNuls->take(10) as $systeme) {
        echo "  - {$systeme->nom}: position ({$systeme->position_x}, {$systeme->position_y}, {$systeme->position_z}) cUA" . PHP_EOL;
    }
    if ($systemesNonNuls->count() > 10) {
        echo "  ... et " . ($systemesNonNuls->count() - 10) . " autres" . PHP_EOL;
    }
} else {
    echo "✅ Tous les systèmes ont des positions nulles (0, 0, 0) cUA" . PHP_EOL;
}

echo PHP_EOL;

// Vérifier spécifiquement Sol
$sol = SystemeStellaire::where('nom', 'Sol')->first();
if ($sol) {
    echo "Vérification de Sol:" . PHP_EOL;
    echo "  Secteur: ({$sol->secteur_x}, {$sol->secteur_y}, {$sol->secteur_z}) AL" . PHP_EOL;
    echo "  Position: ({$sol->position_x}, {$sol->position_y}, {$sol->position_z}) cUA" . PHP_EOL;

    if ($sol->position_x == 0 && $sol->position_y == 0 && $sol->position_z == 0) {
        echo "  ✅ Sol est correctement positionné" . PHP_EOL;
    } else {
        echo "  ❌ Sol a une position non-nulle!" . PHP_EOL;
    }
}

echo PHP_EOL . "=== VÉRIFICATION TERMINÉE ===" . PHP_EOL;
