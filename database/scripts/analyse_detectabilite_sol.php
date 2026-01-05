<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Station;
use App\Models\Mine;
use App\Models\Vaisseau;

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "ANALYSE DE DÉTECTABILITÉ DEPUIS SOL\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

// Trouver le vaisseau du joueur
$vaisseau = Vaisseau::with('objetSpatial')->first();

if (!$vaisseau || !$vaisseau->objetSpatial) {
    echo "❌ Aucun vaisseau trouvé!\n";
    exit(1);
}

$os = $vaisseau->objetSpatial;
$portee = $vaisseau->portee_scan ?? 10; // Portée par défaut 10 AL

echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ POSITION DU VAISSEAU                                                    │\n";
echo "├─────────────────────────────────────────────────────────────────────────┤\n";
echo "│ Nom: " . str_pad($os->nom, 67) . "│\n";
echo "│ Secteur: (" . str_pad($os->secteur_x . ", " . $os->secteur_y . ", " . $os->secteur_z . ")", 61) . ") AL │\n";
echo "│ Position: (" . str_pad($os->position_x . ", " . $os->position_y . ", " . $os->position_z, 60) . ") cUA │\n";
echo "│ Portée scan: " . str_pad($portee . " AL", 58) . "│\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

// ============================================================================
// RÈGLE 1: SYSTÈMES STELLAIRES (Inter-secteur)
// Distance calculée UNIQUEMENT avec les secteurs (AL)
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "RÈGLE 1: SYSTÈMES STELLAIRES (Inter-secteur, distance en AL)\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

$systemes = SystemeStellaire::all();
$systemesAPortee = [];

foreach ($systemes as $systeme) {
    // Distance UNIQUEMENT avec les secteurs (AL) - positions ignorées
    $dx = $systeme->secteur_x - $os->secteur_x;
    $dy = $systeme->secteur_y - $os->secteur_y;
    $dz = $systeme->secteur_z - $os->secteur_z;

    $distanceAL = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

    if ($distanceAL <= $portee) {
        $systemesAPortee[] = [
            'systeme' => $systeme,
            'distance_AL' => $distanceAL,
        ];
    }
}

// Trier par distance
usort($systemesAPortee, fn($a, $b) => $a['distance_AL'] <=> $b['distance_AL']);

echo "Systèmes stellaires à portée (≤ {$portee} AL) : " . count($systemesAPortee) . "\n\n";

// Afficher les 10 premiers
$top10Systems = array_slice($systemesAPortee, 0, 10);
echo "Top 10 plus proches:\n";
echo "──────────────────────────────────────────────────────────────────────────\n";

foreach ($top10Systems as $index => $data) {
    $systeme = $data['systeme'];
    $distance = $data['distance_AL'];

    echo sprintf(
        "%2d. %-40s | %6.2f AL | Secteur (%2d,%2d,%2d)\n",
        $index + 1,
        substr($systeme->nom, 0, 40),
        $distance,
        $systeme->secteur_x,
        $systeme->secteur_y,
        $systeme->secteur_z
    );
}

echo "\n";

// ============================================================================
// RÈGLE 2: POI LOCAUX (Intra-secteur)
// Détectables UNIQUEMENT dans le même secteur, distance en cUA
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "RÈGLE 2: POI LOCAUX (Intra-secteur, même secteur requis, distance en cUA)\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

// Planètes dans le même secteur
$planetes = Planete::with('systemeStellaire')->get();
$planetesMemeSecteur = [];

foreach ($planetes as $planete) {
    if (!$planete->systemeStellaire) continue;

    $sys = $planete->systemeStellaire;

    // Vérifier si même secteur
    if ($sys->secteur_x == $os->secteur_x &&
        $sys->secteur_y == $os->secteur_y &&
        $sys->secteur_z == $os->secteur_z) {

        // Distance calculée UNIQUEMENT avec les positions (cUA) - secteurs ignorés
        // IMPORTANT: Utiliser cache_position de la planète si disponible, sinon position du système
        $planeteX = $planete->cache_position_x ?? $sys->position_x;
        $planeteY = $planete->cache_position_y ?? $sys->position_y;
        $planeteZ = $planete->cache_position_z ?? $sys->position_z;

        $dx = $planeteX - $os->position_x;
        $dy = $planeteY - $os->position_y;
        $dz = $planeteZ - $os->position_z;

        $distanceCUA = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
        $distanceUA = $distanceCUA / 100;

        $planetesMemeSecteur[] = [
            'planete' => $planete,
            'systeme' => $sys,
            'distance_cUA' => $distanceCUA,
            'distance_UA' => $distanceUA,
        ];
    }
}

usort($planetesMemeSecteur, fn($a, $b) => $a['distance_cUA'] <=> $b['distance_cUA']);

echo "Planètes dans le même secteur : " . count($planetesMemeSecteur) . "\n";
if (count($planetesMemeSecteur) > 0) {
    echo "──────────────────────────────────────────────────────────────────────────\n";
    foreach ($planetesMemeSecteur as $index => $data) {
        $planete = $data['planete'];
        $systeme = $data['systeme'];
        $distUA = $data['distance_UA'];

        echo sprintf(
            "%2d. %-35s | %10.2f UA | Système: %s\n",
            $index + 1,
            substr($planete->nom, 0, 35),
            $distUA,
            substr($systeme->nom, 0, 25)
        );
    }
}
echo "\n";

// Stations dans le même secteur
$stations = Station::with('systemeStellaire')->get();
$stationsMemeSecteur = [];

foreach ($stations as $station) {
    if (!$station->systemeStellaire) continue;

    $sys = $station->systemeStellaire;

    if ($sys->secteur_x == $os->secteur_x &&
        $sys->secteur_y == $os->secteur_y &&
        $sys->secteur_z == $os->secteur_z) {

        $dx = $sys->position_x - $os->position_x;
        $dy = $sys->position_y - $os->position_y;
        $dz = $sys->position_z - $os->position_z;

        $distanceCUA = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

        $stationsMemeSecteur[] = [
            'station' => $station,
            'distance_cUA' => $distanceCUA,
        ];
    }
}

echo "Stations dans le même secteur : " . count($stationsMemeSecteur) . "\n\n";

// Mines dans le même secteur
$mines = Mine::with('planete.systemeStellaire')->get();
$minesMemeSecteur = [];

foreach ($mines as $mine) {
    if (!$mine->planete || !$mine->planete->systemeStellaire) continue;

    $sys = $mine->planete->systemeStellaire;

    if ($sys->secteur_x == $os->secteur_x &&
        $sys->secteur_y == $os->secteur_y &&
        $sys->secteur_z == $os->secteur_z) {

        $minesMemeSecteur[] = $mine;
    }
}

echo "Mines dans le même secteur : " . count($minesMemeSecteur) . "\n\n";

// ============================================================================
// RÉSUMÉ
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ DÉTECTABILITÉ\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

echo "RÈGLE INTER-SECTEUR (Systèmes stellaires, distance AL):\n";
echo "  • Systèmes à portée (≤ {$portee} AL) : " . count($systemesAPortee) . "\n";
echo "  • Distance calculée : UNIQUEMENT secteurs (position ignorée)\n";
echo "  • Formule détection : (distance_AL / 10) × detectabilite_base\n\n";

echo "RÈGLE INTRA-SECTEUR (POI locaux, même secteur requis, distance cUA):\n";
echo "  • Planètes dans secteur ({$os->secteur_x},{$os->secteur_y},{$os->secteur_z}) : " . count($planetesMemeSecteur) . "\n";
echo "  • Stations dans secteur : " . count($stationsMemeSecteur) . "\n";
echo "  • Mines dans secteur : " . count($minesMemeSecteur) . "\n";
echo "  • Distance calculée : UNIQUEMENT positions (secteur ignoré)\n";
echo "  • Formule détection : (distance_cUA / 1000) × detectabilite_base\n\n";

$totalPOI = count($planetesMemeSecteur) + count($stationsMemeSecteur) + count($minesMemeSecteur);
echo "TOTAL POI LOCAUX (même secteur) : {$totalPOI}\n";
echo "TOTAL SYSTÈMES (≤ {$portee} AL) : " . count($systemesAPortee) . "\n\n";

// Statistiques par distance
echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "RÉPARTITION PAR DISTANCE\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

$ranges = [
    [0, 1, "0-1 AL"],
    [1, 3, "1-3 AL"],
    [3, 5, "3-5 AL"],
    [5, 7, "5-7 AL"],
    [7, 10, "7-10 AL"],
];

foreach ($ranges as [$min, $max, $label]) {
    $count = count(array_filter($systemesAPortee, fn($s) => $s['distance_AL'] >= $min && $s['distance_AL'] < $max));
    $bar = str_repeat('█', min($count, 50));
    echo sprintf("  %-10s : %3d %s\n", $label, $count, $bar);
}
