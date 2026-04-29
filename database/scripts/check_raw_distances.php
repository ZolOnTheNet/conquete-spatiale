<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VALEURS BRUTES DE distance_etoile ===" . PHP_EOL . PHP_EOL;

$planetes = DB::table('planetes')
    ->whereIn('nom', ['Mercure', 'Terre', 'Mars', 'Uranus'])
    ->orderBy('distance_etoile')
    ->get();

foreach ($planetes as $planete) {
    echo sprintf("%-10s : distance_etoile = %s", $planete->nom, $planete->distance_etoile) . PHP_EOL;
}

echo PHP_EOL;
echo "Si Terre = 1, alors c'est en UA" . PHP_EOL;
echo "Si Terre = 100, alors c'est en cUA" . PHP_EOL;
