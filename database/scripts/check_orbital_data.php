<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Planete;

$planetes = Planete::all();
echo "Total planètes: " . $planetes->count() . PHP_EOL;

$sansOrbitale = $planetes->filter(function($p) {
    return is_null($p->angle_orbital_initial) ||
           is_null($p->vitesse_angulaire) ||
           is_null($p->periode_orbitale) ||
           is_null($p->distance_etoile);
});

echo "Planètes SANS données orbitales complètes: " . $sansOrbitale->count() . PHP_EOL;

if ($sansOrbitale->count() > 0) {
    echo "\nExemples de planètes sans données orbitales:" . PHP_EOL;
    foreach ($sansOrbitale->take(10) as $p) {
        echo "  - " . $p->nom . " (système: " . $p->systemeStellaire->nom . ")" . PHP_EOL;
        echo "    distance_etoile: " . ($p->distance_etoile ?? 'NULL') . PHP_EOL;
        echo "    periode_orbitale: " . ($p->periode_orbitale ?? 'NULL') . PHP_EOL;
        echo "    angle_orbital_initial: " . ($p->angle_orbital_initial ?? 'NULL') . PHP_EOL;
        echo "    vitesse_angulaire: " . ($p->vitesse_angulaire ?? 'NULL') . PHP_EOL;
    }
}

$avecOrbitale = $planetes->filter(function($p) {
    return !is_null($p->angle_orbital_initial) &&
           !is_null($p->vitesse_angulaire) &&
           !is_null($p->periode_orbitale) &&
           !is_null($p->distance_etoile);
});

echo "\nPlanètes AVEC données orbitales complètes: " . $avecOrbitale->count() . PHP_EOL;

if ($avecOrbitale->count() > 0) {
    echo "\nExemples de planètes avec données orbitales:" . PHP_EOL;
    foreach ($avecOrbitale->take(5) as $p) {
        echo "  - " . $p->nom . " (système: " . $p->systemeStellaire->nom . ")" . PHP_EOL;
        echo "    distance_etoile: " . $p->distance_etoile . " UA" . PHP_EOL;
        echo "    periode_orbitale: " . $p->periode_orbitale . " jours" . PHP_EOL;
        echo "    angle_orbital_initial: " . round($p->angle_orbital_initial, 4) . " rad" . PHP_EOL;
        echo "    vitesse_angulaire: " . round($p->vitesse_angulaire, 6) . " rad/jour" . PHP_EOL;
    }
}
