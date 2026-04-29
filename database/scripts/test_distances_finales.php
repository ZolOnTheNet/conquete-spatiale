<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Helpers\GameTimeHelper;
use App\Helpers\CoordinatesHelper;

echo "=== TEST FINAL DES DISTANCES (TOUT EN cUA) ===" . PHP_EOL . PHP_EOL;

$personnage = Personnage::first();
if (!$personnage) {
    echo "❌ Aucun personnage trouvé" . PHP_EOL;
    exit(1);
}

$vaisseau = $personnage->vaisseauActif;
if (!$vaisseau) {
    echo "❌ Pas de vaisseau actif" . PHP_EOL;
    exit(1);
}

$sol = SystemeStellaire::where('nom', 'Sol')->first();
if (!$sol) {
    echo "❌ Système Sol non trouvé" . PHP_EOL;
    exit(1);
}

$timestampJours = GameTimeHelper::getTimestampJoursActuel($personnage);

echo "Date du jeu: " . GameTimeHelper::formatDateJeu(GameTimeHelper::getDateActuelleJeu($personnage)) . PHP_EOL;
echo "Timestamp: $timestampJours jours" . PHP_EOL . PHP_EOL;

$planetes = $sol->planetes()->where('poi_connu', true)->orderBy('distance_etoile')->get();

echo "=== DISTANCES DES PLANÈTES (depuis PHP) ===" . PHP_EOL;
foreach ($planetes as $planete) {
    // Distance stockée en cUA
    $distance_cua = $planete->distance_etoile;
    $distance_ua = CoordinatesHelper::cuaToUa($distance_cua);

    // Distance calculée
    $distance_calculee = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);

    echo sprintf(
        "%-10s : %6.0f cUA = %5.2f UA | Distance vaisseau: %5.2f UA",
        $planete->nom,
        $distance_cua,
        $distance_ua,
        $distance_calculee
    ) . PHP_EOL;

    // Vérification
    if ($distance_calculee > 100) {
        echo "  ❌ ERREUR : Distance trop grande !" . PHP_EOL;
    } else {
        echo "  ✅ OK" . PHP_EOL;
    }
}

echo PHP_EOL . "=== RÉSULTAT ===" . PHP_EOL;
echo "Si toutes les distances vaisseau sont < 100 UA, c'est correct !" . PHP_EOL;
