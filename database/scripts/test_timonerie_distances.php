<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Helpers\GameTimeHelper;
use App\Helpers\CoordinatesHelper;

echo "=== TEST DES DISTANCES DANS LA TIMONERIE ===" . PHP_EOL . PHP_EOL;

$personnage = Personnage::first();
if (!$personnage) {
    echo "❌ Aucun personnage trouvé" . PHP_EOL;
    exit(1);
}

// Placer le vaisseau dans le système Sol à une position proche de l'étoile
$vaisseau = $personnage->vaisseauActif;
if (!$vaisseau || !$vaisseau->objetSpatial) {
    echo "❌ Pas de vaisseau actif" . PHP_EOL;
    exit(1);
}

$objetSpatial = $vaisseau->objetSpatial;

// Placer dans le système Sol (0,0,0) à une position de 0.1 UA de l'étoile
$objetSpatial->secteur_x = 0;
$objetSpatial->secteur_y = 0;
$objetSpatial->secteur_z = 0;
$objetSpatial->position_x = CoordinatesHelper::uaToCua(0.1); // 0.1 UA = 10 cUA
$objetSpatial->position_y = 0;
$objetSpatial->position_z = 0;
$objetSpatial->save();

echo "Vaisseau placé dans le système Sol à 0.1 UA de l'étoile" . PHP_EOL;
echo "Position: secteur (0,0,0), position (" . ($objetSpatial->position_x/100) . " UA, 0, 0)" . PHP_EOL;
echo PHP_EOL;

// Date du jeu
$dateJeu = GameTimeHelper::getDateActuelleJeu($personnage);
$timestampJours = GameTimeHelper::dateToJours($dateJeu);

echo "Date du jeu: " . GameTimeHelper::formatDateJeu($dateJeu) . PHP_EOL;
echo "Timestamp: $timestampJours jours depuis 3000-01-01" . PHP_EOL;
echo PHP_EOL;

// Récupérer le système Sol
$sol = SystemeStellaire::where('nom', 'Sol')->orWhere('nom', 'like', '%Sol%')->first();
if (!$sol) {
    echo "❌ Système Sol non trouvé" . PHP_EOL;
    exit(1);
}

echo "Système: {$sol->nom}" . PHP_EOL;
echo "Puissance: {$sol->puissance}" . PHP_EOL;
echo "Puissance solaire: {$sol->puissance_solaire}" . PHP_EOL;
echo PHP_EOL;

// Récupérer les planètes
$planetes = $sol->planetes()->where('poi_connu', true)->get();

echo "=== DISTANCES DES PLANÈTES ===" . PHP_EOL;
foreach ($planetes as $planete) {
    $distance = $planete->getDistanceDepuisVaisseau($vaisseau, $timestampJours);
    $position = $planete->getPositionAbsolue($timestampJours);

    // Calculer PA requis
    $paRequis = max(1, (int)ceil($distance / 2));

    echo "🌍 {$planete->nom}" . PHP_EOL;
    echo "   Distance étoile: {$planete->distance_etoile} UA" . PHP_EOL;
    echo "   Position orbitale: X=" . round($position['x']/100, 2) . " UA, ";
    echo "Y=" . round($position['y']/100, 2) . " UA" . PHP_EOL;
    echo "   Distance vaisseau: " . round($distance, 2) . " UA" . PHP_EOL;
    echo "   PA requis: $paRequis" . PHP_EOL;

    // Vérification
    if ($distance > 1000) {
        echo "   ❌ ERREUR: Distance trop grande !" . PHP_EOL;
    } else if ($distance >= 0.1 && $distance <= 10) {
        echo "   ✅ Distance cohérente" . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "=== TEST TERMINÉ ===" . PHP_EOL;
