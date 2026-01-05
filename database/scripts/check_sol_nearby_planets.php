<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "POSITIONS DES PLANÈTES DANS LES 10 SYSTÈMES LES PLUS PROCHES DE SOL\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

// Trouver Sol (devrait être à 0,0,0)
$sol = SystemeStellaire::where('secteur_x', 0)
    ->where('secteur_y', 0)
    ->where('secteur_z', 0)
    ->first();

if (!$sol) {
    echo "❌ Sol introuvable à (0,0,0)!\n";
    echo "Recherche de systèmes proches de (0,0,0)...\n\n";
}

// Récupérer tous les systèmes et calculer leur distance à Sol
$systemes = SystemeStellaire::with('planetes')->get();

$systemesAvecDistance = [];
foreach ($systemes as $systeme) {
    // Distance en AL (seulement secteurs)
    $distance = sqrt(
        $systeme->secteur_x * $systeme->secteur_x +
        $systeme->secteur_y * $systeme->secteur_y +
        $systeme->secteur_z * $systeme->secteur_z
    );

    $systemesAvecDistance[] = [
        'systeme' => $systeme,
        'distance' => $distance,
    ];
}

// Trier par distance
usort($systemesAvecDistance, fn($a, $b) => $a['distance'] <=> $b['distance']);

// Prendre les 10 premiers
$top10 = array_slice($systemesAvecDistance, 0, 10);

foreach ($top10 as $index => $data) {
    $systeme = $data['systeme'];
    $distance = $data['distance'];

    echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ #" . ($index + 1) . " - " . str_pad($systeme->nom, 68) . "│\n";
    echo "├─────────────────────────────────────────────────────────────────────────┤\n";
    echo "│ Distance à Sol: " . str_pad(number_format($distance, 2, '.', '') . " AL", 56) . "│\n";
    echo "│ Secteur: (" . str_pad($systeme->secteur_x . ", " . $systeme->secteur_y . ", " . $systeme->secteur_z . ")", 60) . "│\n";
    echo "│ Position secteur: (" . str_pad(
        number_format($systeme->position_x) . ", " .
        number_format($systeme->position_y) . ", " .
        number_format($systeme->position_z), 56) . ") cUA │\n";
    echo "│ Type étoile: " . str_pad($systeme->type_etoile ?? 'N/A', 59) . "│\n";
    echo "│ Puissance: " . str_pad($systeme->puissance ?? $systeme->puissance_solaire ?? 'N/A', 61) . "│\n";
    echo "├─────────────────────────────────────────────────────────────────────────┤\n";

    $planetes = $systeme->planetes;

    if ($planetes->count() > 0) {
        echo "│ PLANÈTES (" . $planetes->count() . "):                                                           │\n";
        echo "│                                                                         │\n";

        foreach ($planetes as $p) {
            $nom = str_pad(substr($p->nom, 0, 30), 32);
            $distUA = str_pad(number_format($p->distance_etoile, 2, '.', '') . " UA", 12);
            $type = str_pad(substr($p->type ?? 'inconnu', 0, 15), 17);

            echo "│   • " . $nom . " | " . $distUA . " | " . $type . " │\n";

            // Position cache (si remplie)
            if ($p->cache_position_x !== null) {
                $cachePos = "      cache_pos: (" .
                    number_format($p->cache_position_x) . ", " .
                    number_format($p->cache_position_y) . ", " .
                    number_format($p->cache_position_z) . ") cUA";
                echo "│   " . str_pad($cachePos, 70) . "│\n";
            }
        }
    } else {
        echo "│ Aucune planète                                                          │\n";
    }

    echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";
}

// Statistiques globales
echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "STATISTIQUES\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

$totalPlanetes = 0;
foreach ($top10 as $data) {
    $totalPlanetes += $data['systeme']->planetes->count();
}

echo "Systèmes analysés: 10\n";
echo "Total planètes: {$totalPlanetes}\n";
echo "Moyenne planètes/système: " . number_format($totalPlanetes / 10, 1) . "\n\n";

// Vérifier si les positions sont en cUA ou UA
echo "VÉRIFICATION DES UNITÉS:\n";
echo "─────────────────────────\n";
$samplePlanete = DB::table('planetes')->where('distance_etoile', '>', 0)->first();
if ($samplePlanete) {
    echo "✓ distance_etoile: {$samplePlanete->distance_etoile} (en UA, normal)\n";
    if ($samplePlanete->cache_position_x !== null) {
        echo "✓ cache_position_x: {$samplePlanete->cache_position_x} (en cUA, normal)\n";
    } else {
        echo "ℹ️  cache_position: vide (sera calculé lors du mouvement orbital)\n";
    }
}
