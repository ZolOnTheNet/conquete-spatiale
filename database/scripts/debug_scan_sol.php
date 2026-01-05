<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vaisseau;
use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Station;

echo "=== DEBUG SCAN SECTEUR SOL ===\n\n";

// Trouver le vaisseau du joueur
$personnage = Personnage::first();
if (!$personnage) {
    echo "Aucun personnage trouvé\n";
    exit;
}

$vaisseau = $personnage->vaisseauActif;
if (!$vaisseau) {
    echo "Aucun vaisseau actif\n";
    exit;
}

$objetSpatial = $vaisseau->objetSpatial;

echo "Vaisseau: {$vaisseau->nom}\n";
echo "Position: Secteur ({$objetSpatial->secteur_x}, {$objetSpatial->secteur_y}, {$objetSpatial->secteur_z})\n";
echo "          Position ({$objetSpatial->position_x}, {$objetSpatial->position_y}, {$objetSpatial->position_z})\n";
echo "Portée scan: {$vaisseau->portee_scan} AL\n";
echo "Puissance scan: {$vaisseau->puissance_scan}\n\n";

// Position absolue du vaisseau
$posX = $objetSpatial->secteur_x + $objetSpatial->position_x;
$posY = $objetSpatial->secteur_y + $objetSpatial->position_y;
$posZ = $objetSpatial->secteur_z + $objetSpatial->position_z;

echo "Position absolue: ($posX, $posY, $posZ)\n\n";

// Chercher le système Sol
$sol = SystemeStellaire::where('nom', 'like', '%Sol%')
    ->orWhere('nom', 'like', '%Sun%')
    ->orWhere('id', 1)
    ->first();

if ($sol) {
    echo "=== SYSTÈME SOL ===\n";
    echo "Nom: {$sol->nom}\n";
    echo "Position: Secteur ({$sol->secteur_x}, {$sol->secteur_y}, {$sol->secteur_z})\n";
    echo "          Position ({$sol->position_x}, {$sol->position_y}, {$sol->position_z})\n";

    $solPosX = $sol->secteur_x + $sol->position_x;
    $solPosY = $sol->secteur_y + $sol->position_y;
    $solPosZ = $sol->secteur_z + $sol->position_z;
    echo "Position absolue: ($solPosX, $solPosY, $solPosZ)\n";

    $distance = sqrt(
        pow($solPosX - $posX, 2) +
        pow($solPosY - $posY, 2) +
        pow($solPosZ - $posZ, 2)
    );
    echo "Distance au vaisseau: $distance AL\n";
    echo "Dans portée ? " . ($distance <= $vaisseau->portee_scan ? "OUI" : "NON") . "\n\n";

    // Planètes de Sol
    $planetes = Planete::where('systeme_stellaire_id', $sol->id)->get();
    echo "=== PLANÈTES DU SYSTÈME ===\n";
    echo "Nombre de planètes: " . $planetes->count() . "\n\n";

    foreach ($planetes as $planete) {
        echo "- {$planete->nom}\n";
        echo "  Distance système: $distance AL\n";
        echo "  Portée requise: " . ($vaisseau->portee_scan / 10) . " AL\n";
        echo "  Détectable ? " . ($distance <= ($vaisseau->portee_scan / 10) ? "OUI" : "NON") . "\n";

        if (method_exists($planete, 'getScoreDetection')) {
            $score = $planete->getScoreDetection($posX, $posY, $posZ);
            echo "  Score détection requis: $score\n";
        }
        echo "\n";
    }

    // Stations de Sol
    $stations = Station::where('systeme_stellaire_id', $sol->id)->get();
    echo "=== STATIONS DU SYSTÈME ===\n";
    echo "Nombre de stations: " . $stations->count() . "\n\n";

    foreach ($stations as $station) {
        echo "- {$station->nom}\n";
        echo "  Distance système: $distance AL\n";
        echo "  Portée requise: " . ($vaisseau->portee_scan / 5) . " AL\n";
        echo "  Détectable ? " . ($distance <= ($vaisseau->portee_scan / 5) ? "OUI" : "NON") . "\n";

        if (method_exists($station, 'getScoreDetection')) {
            $score = $station->getScoreDetection($posX, $posY, $posZ);
            echo "  Score détection requis: $score\n";
        }
        echo "\n";
    }
} else {
    echo "Système Sol non trouvé!\n";
}

// Chercher TOUS les systèmes
echo "\n=== TOUS LES SYSTÈMES ===\n";
$systemes = SystemeStellaire::all();
echo "Total systèmes: " . $systemes->count() . "\n\n";

foreach ($systemes->take(5) as $sys) {
    $sysX = $sys->secteur_x + $sys->position_x;
    $sysY = $sys->secteur_y + $sys->position_y;
    $sysZ = $sys->secteur_z + $sys->position_z;

    $dist = sqrt(
        pow($sysX - $posX, 2) +
        pow($sysY - $posY, 2) +
        pow($sysZ - $posZ, 2)
    );

    echo "- {$sys->nom}: Distance $dist AL - " . ($dist <= $vaisseau->portee_scan ? "PORTÉE" : "trop loin") . "\n";
}
