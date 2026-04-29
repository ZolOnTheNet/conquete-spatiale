<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;
use Illuminate\Support\Facades\DB;

echo "📊 État des systèmes stellaires en base\n";
echo str_repeat("=", 70) . "\n\n";

// Statistiques globales
$totalSystems = SystemeStellaire::count();
$gaiaSystemsCount = SystemeStellaire::where('source_gaia', true)->count();
$withCommonName = SystemeStellaire::whereNotNull('nom_commun')->count();
$withoutCommonName = SystemeStellaire::whereNull('nom_commun')->count();

echo "Total systèmes: {$totalSystems}\n";
echo "Systèmes GAIA: {$gaiaSystemsCount}\n";
echo "Avec nom commun: {$withCommonName}\n";
echo "Sans nom commun: {$withoutCommonName}\n\n";

// Systèmes avec noms communs
echo str_repeat("-", 70) . "\n";
echo "🌟 Systèmes enrichis avec noms communs:\n\n";

$enriched = SystemeStellaire::whereNotNull('nom_commun')
    ->select('nom', 'nom_commun', 'gaia_distance_ly')
    ->orderBy('gaia_distance_ly')
    ->get();

foreach ($enriched as $sys) {
    $dist = number_format($sys->gaia_distance_ly, 2);
    echo "  • {$sys->nom_commun} ({$dist} AL)\n";
}

echo "\n" . str_repeat("-", 70) . "\n";
echo "📦 Statistiques des planètes:\n\n";

$totalPlanets = DB::table('planetes')->count();
$nasaPlanets = DB::table('planetes')->where('source_nasa_exoplanet', true)->count();
$proceduralPlanets = $totalPlanets - $nasaPlanets;

echo "Total planètes: {$totalPlanets}\n";
echo "Planètes NASA: {$nasaPlanets}\n";
echo "Planètes procédurales: {$proceduralPlanets}\n\n";

// Systèmes avec planètes NASA
$systemsWithNasaPlanets = DB::table('systemes_stellaires')
    ->join('planetes', 'systemes_stellaires.id', '=', 'planetes.systeme_stellaire_id')
    ->where('planetes.source_nasa_exoplanet', true)
    ->select('systemes_stellaires.nom', 'systemes_stellaires.nom_commun')
    ->distinct()
    ->get();

if ($systemsWithNasaPlanets->count() > 0) {
    echo "Systèmes avec exoplanètes NASA importées:\n";
    foreach ($systemsWithNasaPlanets as $sys) {
        $count = DB::table('planetes')
            ->join('systemes_stellaires', 'planetes.systeme_stellaire_id', '=', 'systemes_stellaires.id')
            ->where('systemes_stellaires.nom', $sys->nom)
            ->where('planetes.source_nasa_exoplanet', true)
            ->count();

        echo "  • " . ($sys->nom_commun ?? $sys->nom) . " : {$count} planète(s)\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "💡 Prochaines étapes:\n\n";

if ($withoutCommonName > 0) {
    echo "  1. Ajouter plus d'étoiles au catalogue StarNameMatcher\n";
    echo "     (actuellement ~20 étoiles célèbres)\n\n";
}

if ($withCommonName > $systemsWithNasaPlanets->count()) {
    $canImport = $withCommonName - $systemsWithNasaPlanets->count();
    echo "  2. Importer les exoplanètes pour les {$canImport} systèmes restants:\n";
    echo "     php artisan exoplanet:import-real --gaia-only\n\n";
}
