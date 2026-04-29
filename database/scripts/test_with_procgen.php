<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UniverseGeneratorService;
use App\Services\ExoplanetService;
use App\Models\SystemeStellaire;
use App\Models\Planete;

echo "🧪 TEST COMPLET: Génération procédurale + Import NASA\n";
echo str_repeat("=", 70) . "\n\n";

$universeGen = app(UniverseGeneratorService::class);
$exoplanetService = app(ExoplanetService::class);

// ÉTAPE 1: Forcer génération procédurale
echo "📍 ÉTAPE 1: Génération procédurale forcée [100, 100, 50]\n";
echo str_repeat("-", 70) . "\n";

$systeme = $universeGen->ensureSectorExists(100, 100, 50, true); // forceGenerate = true

if ($systeme) {
    echo "✅ Système généré: {$systeme->nom}\n";
    echo "   Type étoile: {$systeme->type_etoile}\n";
    echo "   Position: [{$systeme->secteur_x}, {$systeme->secteur_y}, {$systeme->secteur_z}]\n";
    echo "   Planètes: {$systeme->nb_planetes}\n";
    echo "   Source GAIA: " . ($systeme->source_gaia ? 'Oui ✨' : 'Non (procédural) 🎲') . "\n";
} else {
    echo "❌ Échec génération\n";
}

echo "\n";

// ÉTAPE 2: Trouver une étoile GAIA et tester import NASA
echo "📍 ÉTAPE 2: Test import NASA pour étoile GAIA existante\n";
echo str_repeat("-", 70) . "\n";

$gaiaSystem = SystemeStellaire::where('source_gaia', true)
    ->whereNotNull('gaia_distance_ly')
    ->orderBy('gaia_distance_ly')
    ->first();

if ($gaiaSystem) {
    echo "✅ Étoile GAIA trouvée: {$gaiaSystem->nom}\n";
    echo "   Distance: " . number_format($gaiaSystem->gaia_distance_ly, 2) . " AL\n";
    echo "   Planètes actuelles: {$gaiaSystem->nb_planetes}\n\n";

    echo "🔍 Interrogation API NASA Exoplanet Archive...\n";
    $exoplanets = $exoplanetService->getExoplanetsForGaiaSystem($gaiaSystem);

    if (!empty($exoplanets)) {
        echo "✅ {" . count($exoplanets) . "} exoplanète(s) trouvée(s) dans le catalogue NASA !\n\n";

        foreach ($exoplanets as $exo) {
            echo "   🪐 {$exo['pl_name']}\n";
            echo "      - Rayon: " . number_format($exo['pl_rade'], 2) . " rayons terrestres\n";
            echo "      - Masse: " . number_format($exo['pl_bmasse'], 2) . " masses terrestres\n";
            echo "      - Distance: " . number_format($exo['pl_orbsmax'], 3) . " AU\n";
            if ($exo['disc_year']) {
                echo "      - Découverte: {$exo['disc_year']}\n";
            }
            echo "\n";
        }

        echo "💾 Import en base de données...\n";
        $imported = $exoplanetService->importExoplanetsForSystem($gaiaSystem);
        echo "✅ {$imported} exoplanète(s) importée(s)\n\n";

        // Vérifier
        $gaiaSystem->refresh();
        $nasaPlanets = $gaiaSystem->planetes()->where('source_nasa_exoplanet', true)->get();

        if ($nasaPlanets->count() > 0) {
            echo "🎉 SUCCÈS ! Exoplanètes NASA en base:\n";
            foreach ($nasaPlanets as $planet) {
                echo "   ✨ {$planet->nom} (NASA ID: {$planet->nasa_exo_id})\n";
            }
        }
    } else {
        echo "ℹ️  Aucune exoplanète trouvée pour cette étoile dans le catalogue NASA\n";
        echo "   (Normal: seulement ~6000 exoplanètes connues sur des milliards d'étoiles)\n";
    }
} else {
    echo "❌ Aucune étoile GAIA trouvée\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 STATISTIQUES FINALES:\n";
echo "   Total systèmes: " . SystemeStellaire::count() . "\n";
echo "   Dont GAIA: " . SystemeStellaire::where('source_gaia', true)->count() . "\n";
echo "   Total planètes: " . Planete::count() . "\n";
echo "   Dont exoplanètes NASA: " . Planete::where('source_nasa_exoplanet', true)->count() . " ⭐\n";
