<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

$stars = SystemeStellaire::where('source_gaia', true)
    ->orderBy('gaia_distance_ly')
    ->limit(10)
    ->get(['nom', 'type_etoile', 'gaia_distance_ly', 'nb_planetes']);

echo "🌟 Étoiles GAIA les plus proches:\n\n";
foreach ($stars as $star) {
    $dist = number_format($star->gaia_distance_ly, 2);
    echo "  - {$star->nom} ({$star->type_etoile}) - {$dist} AL - {$star->nb_planetes} planètes\n";
}

echo "\n📊 Total systèmes GAIA: " . SystemeStellaire::where('source_gaia', true)->count() . "\n";
