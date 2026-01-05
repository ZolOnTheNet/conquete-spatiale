<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UniverseGeneratorService;
use App\Models\SystemeStellaire;
use App\Models\Planete;

echo "🚀 TEST GÉNÉRATION DYNAMIQUE À LA VOLÉE\n";
echo str_repeat("=", 60) . "\n\n";

$universeGen = app(UniverseGeneratorService::class);

// Test 1: Secteur vide éloigné (génération procédurale possible)
echo "📍 Test 1: Génération secteur [50, 50, 25] (loin du Soleil)\n";
echo str_repeat("-", 60) . "\n";

$systemesBefore = SystemeStellaire::count();
$planetesBefore = Planete::count();

$systeme1 = $universeGen->ensureSectorExists(50, 50, 25);

$systemesAfter = SystemeStellaire::count();
$planetesAfter = Planete::count();

if ($systeme1) {
    echo "✅ Système généré: {$systeme1->nom}\n";
    echo "   Type: {$systeme1->type_etoile}\n";
    echo "   Planètes: {$systeme1->nb_planetes}\n";
    echo "   Source GAIA: " . ($systeme1->source_gaia ? 'Oui' : 'Non') . "\n";

    // Vérifier si des exoplanètes NASA ont été importées
    $nasaPlanets = $systeme1->planetes()->where('source_nasa_exoplanet', true)->count();
    if ($nasaPlanets > 0) {
        echo "   🪐 Exoplanètes NASA: {$nasaPlanets}\n";
    }
} else {
    echo "ℹ️  Aucun système généré (secteur vide selon config)\n";
}

echo "   Nouveaux systèmes: " . ($systemesAfter - $systemesBefore) . "\n";
echo "   Nouvelles planètes: " . ($planetesAfter - $planetesBefore) . "\n\n";

// Test 2: Expansion autour d'une position (rayon 2)
echo "📍 Test 2: Expansion autour de [10, 5, 3] avec rayon 2\n";
echo str_repeat("-", 60) . "\n";

$systemesBefore = SystemeStellaire::count();
$planetesBefore = Planete::count();

$systemes = $universeGen->expandUniverseAroundPosition(10, 5, 3, 2);

$systemesAfter = SystemeStellaire::count();
$planetesAfter = Planete::count();

echo "✅ Systèmes dans le rayon: " . count($systemes) . "\n";
echo "   Nouveaux systèmes générés: " . ($systemesAfter - $systemesBefore) . "\n";
echo "   Nouvelles planètes générées: " . ($planetesAfter - $planetesBefore) . "\n\n";

// Test 3: Chercher une étoile GAIA proche
echo "📍 Test 3: Recherche étoile GAIA proche de [0, 0, 0] (Soleil)\n";
echo str_repeat("-", 60) . "\n";

$reflection = new ReflectionClass($universeGen);
$method = $reflection->getMethod('findGaiaStarNearSector');
$method->setAccessible(true);

$gaiaSysteme = $method->invoke($universeGen, 0, 0, 0);

if ($gaiaSysteme) {
    echo "✅ Étoile GAIA trouvée: {$gaiaSysteme->nom}\n";
    echo "   Position: [{$gaiaSysteme->secteur_x}, {$gaiaSysteme->secteur_y}, {$gaiaSysteme->secteur_z}]\n";
    echo "   Distance: " . number_format($gaiaSysteme->gaia_distance_ly ?? 0, 2) . " AL\n";
    echo "   Planètes: {$gaiaSysteme->nb_planetes}\n";
} else {
    echo "ℹ️  Aucune étoile GAIA proche du Soleil\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Tests terminés !\n\n";

echo "📊 STATISTIQUES FINALES:\n";
echo "   Total systèmes: " . SystemeStellaire::count() . "\n";
echo "   Total planètes: " . Planete::count() . "\n";
echo "   Systèmes GAIA: " . SystemeStellaire::where('source_gaia', true)->count() . "\n";
echo "   Exoplanètes NASA: " . Planete::where('source_nasa_exoplanet', true)->count() . "\n";
