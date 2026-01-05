<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

echo "=== VÉRIFICATION DES DISTANCES DES PLANÈTES ===" . PHP_EOL . PHP_EOL;

$sol = SystemeStellaire::where('nom', 'Sol')->first();

if (!$sol) {
    echo "❌ Sol non trouvé" . PHP_EOL;
    exit(1);
}

echo "Système Sol - Distances des planètes:" . PHP_EOL . PHP_EOL;

$planetes = $sol->planetes()->orderBy('distance_etoile')->get();

foreach ($planetes as $planete) {
    echo sprintf(
        "%-15s : %10.4f UA (ID: %d)",
        $planete->nom,
        $planete->distance_etoile,
        $planete->id
    ) . PHP_EOL;
}

echo PHP_EOL;
echo "=== ANALYSE ===" . PHP_EOL;
echo "Si les distances sont ~100× trop grandes (ex: Terre à 100 AU au lieu de 1 UA)," . PHP_EOL;
echo "c'est que distance_etoile est stocké en cUA au lieu de UA." . PHP_EOL;
