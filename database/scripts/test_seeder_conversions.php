<?php

/**
 * Test des conversions cUA du GaiaSeeder
 * Vérifie que les distances seront correctement stockées
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Helpers\CoordinatesHelper;

echo "=== TEST DES CONVERSIONS SEEDER (cUA) ===" . PHP_EOL . PHP_EOL;

$planetes = [
    'Terre' => 1.0,
    'Lune' => 1.00257,
    'Mars' => 1.52,
    'Jupiter' => 5.2,
    'Neptune' => 30.1,
];

echo "Vérification des conversions UA → cUA :" . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;

foreach ($planetes as $nom => $distance_ua) {
    $distance_cua = CoordinatesHelper::uaToCua($distance_ua);
    $verification_ua = CoordinatesHelper::cuaToUa($distance_cua);

    $status = (abs($verification_ua - $distance_ua) < 0.01) ? '✅' : '❌';

    echo sprintf(
        "%-10s : %6.2f UA → %6d cUA → %6.2f UA %s",
        $nom,
        $distance_ua,
        $distance_cua,
        $verification_ua,
        $status
    ) . PHP_EOL;
}

echo PHP_EOL . "=== TEST PLANÈTES PROCÉDURALES ===" . PHP_EOL . PHP_EOL;

// Tester quelques distances procédurales
for ($i = 1; $i <= 5; $i++) {
    $distance_ua = $i * 0.5 + rand(0, 10) / 10;
    $distance_cua = CoordinatesHelper::uaToCua($distance_ua);

    echo sprintf(
        "Planète %d : %5.2f UA → %5d cUA",
        $i,
        $distance_ua,
        $distance_cua
    ) . PHP_EOL;
}

echo PHP_EOL . "=== RÉSULTAT ===" . PHP_EOL;
echo "Si toutes les conversions affichent ✅, le seeder est prêt !" . PHP_EOL;
