<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Station;
use App\Models\Personnage;

$personnage = Personnage::first();
$vaisseau = $personnage->vaisseauActif;
$objetSpatial = $vaisseau->objetSpatial;

echo "=== VAISSEAU ===\n";
echo "Position absolue: ({$objetSpatial->secteur_x},{$objetSpatial->secteur_y},{$objetSpatial->secteur_z}) + ({$objetSpatial->position_x},{$objetSpatial->position_y},{$objetSpatial->position_z})\n";
$vx = $objetSpatial->secteur_x + $objetSpatial->position_x;
$vy = $objetSpatial->secteur_y + $objetSpatial->position_y;
$vz = $objetSpatial->secteur_z + $objetSpatial->position_z;
echo "Coordonnées: ($vx, $vy, $vz)\n";
echo "Portée scan: {$vaisseau->portee_scan} AL\n\n";

$sol = SystemeStellaire::where('nom', 'Sol')->first();

if ($sol) {
    echo "=== SYSTÈME SOL ===\n";
    echo "Position: ({$sol->secteur_x},{$sol->secteur_y},{$sol->secteur_z}) + ({$sol->position_x},{$sol->position_y},{$sol->position_z})\n";
    $sx = $sol->secteur_x + $sol->position_x;
    $sy = $sol->secteur_y + $sol->position_y;
    $sz = $sol->secteur_z + $sol->position_z;
    echo "Coordonnées: ($sx, $sy, $sz)\n";

    $distance = sqrt(pow($sx - $vx, 2) + pow($sy - $vy, 2) + pow($sz - $vz, 2));
    echo "Distance: $distance AL\n";
    echo "Système détectable ? " . ($distance <= $vaisseau->portee_scan ? "✓ OUI" : "✗ NON") . "\n\n";

    $planetes = Planete::where('systeme_stellaire_id', $sol->id)->get();
    echo "=== PLANÈTES DE SOL ===\n";
    echo "Nombre: {$planetes->count()}\n";
    if ($planetes->count() > 0) {
        echo "Portée requise pour planètes: " . ($vaisseau->portee_scan / 10) . " AL\n";
        echo "Planètes détectables ? " . ($distance <= ($vaisseau->portee_scan / 10) ? "✓ OUI" : "✗ NON ($distance > " . ($vaisseau->portee_scan / 10) . ")") . "\n\n";

        foreach ($planetes as $p) {
            echo "  - {$p->nom}\n";
        }
    }

    $stations = Station::where('systeme_stellaire_id', $sol->id)->get();
    echo "\n=== STATIONS DE SOL ===\n";
    echo "Nombre: {$stations->count()}\n";
    if ($stations->count() > 0) {
        echo "Portée requise pour stations: " . ($vaisseau->portee_scan / 5) . " AL\n";
        echo "Stations détectables ? " . ($distance <= ($vaisseau->portee_scan / 5) ? "✓ OUI" : "✗ NON ($distance > " . ($vaisseau->portee_scan / 5) . ")") . "\n\n";

        foreach ($stations as $s) {
            echo "  - {$s->nom}\n";
        }
    }
} else {
    echo "❌ Sol non trouvé!\n";
}
