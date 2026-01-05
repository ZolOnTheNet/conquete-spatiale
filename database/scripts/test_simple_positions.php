<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Helpers\GameTimeHelper;

echo "=== TEST SIMPLE DES POSITIONS ===" . PHP_EOL . PHP_EOL;

// Recharger Sol depuis la base (fresh query)
$sol = SystemeStellaire::where('nom', 'Sol')->first();

echo "Système Sol (rechargé):" . PHP_EOL;
echo "  Secteur: ({$sol->secteur_x}, {$sol->secteur_y}, {$sol->secteur_z}) AL" . PHP_EOL;
echo "  Position: ({$sol->position_x}, {$sol->position_y}, {$sol->position_z}) cUA" . PHP_EOL;
echo PHP_EOL;

// Recharger Terre depuis la base
$terre = $sol->planetes()->where('nom', 'Terre')->first();

echo "Planète Terre:" . PHP_EOL;
echo "  distance_etoile: {$terre->distance_etoile} UA" . PHP_EOL;
echo PHP_EOL;

// Timestamp: 7.5 jours après 3000-01-01
$timestampJours = 7.5;

echo "Calcul de la position avec timestamp = $timestampJours jours:" . PHP_EOL;

// Étape 1: Calculer angle actuel
$angleActuel = $terre->angle_orbital_initial + ($terre->vitesse_angulaire * $timestampJours);
echo "  Angle actuel: " . round($angleActuel, 4) . " rad" . PHP_EOL;

// Étape 2: Position orbitale en UA
$x_ua = $terre->distance_etoile * cos($angleActuel);
$y_ua = $terre->distance_etoile * sin($angleActuel);
echo "  Position orbitale (UA): X=" . round($x_ua, 4) . ", Y=" . round($y_ua, 4) . PHP_EOL;

// Étape 3: Conversion UA → cUA
$x_cua = (int)round($x_ua * 100);
$y_cua = (int)round($y_ua * 100);
echo "  Position orbitale (cUA): X=$x_cua, Y=$y_cua" . PHP_EOL;

// Étape 4: Position du système en cUA
$systeme_x_cua = (int)round($sol->secteur_x * 6324100) + ($sol->position_x ?? 0);
$systeme_y_cua = (int)round($sol->secteur_y * 6324100) + ($sol->position_y ?? 0);
echo "  Position système (cUA): X=$systeme_x_cua, Y=$systeme_y_cua" . PHP_EOL;

// Étape 5: Position absolue
$abs_x_cua = $systeme_x_cua + $x_cua;
$abs_y_cua = $systeme_y_cua + $y_cua;
echo "  Position absolue (cUA): X=$abs_x_cua, Y=$abs_y_cua" . PHP_EOL;
echo "  Position absolue (UA): X=" . ($abs_x_cua/100) . ", Y=" . ($abs_y_cua/100) . PHP_EOL;

echo PHP_EOL;
echo "=== Appel de getPositionAbsolue() ===" . PHP_EOL;
$position = $terre->getPositionAbsolue($timestampJours);
echo "  X: {$position['x']} cUA = " . ($position['x']/100) . " UA" . PHP_EOL;
echo "  Y: {$position['y']} cUA = " . ($position['y']/100) . " UA" . PHP_EOL;
