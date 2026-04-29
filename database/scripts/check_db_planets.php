<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;
use Illuminate\Support\Facades\DB;

echo "🔍 Vérification directe de la base de données\n";
echo str_repeat("=", 70) . "\n\n";

$systeme = SystemeStellaire::where('nom_commun', 'Proxima Centauri')->first();

if (!$systeme) {
    echo "❌ Système non trouvé\n";
    exit(1);
}

echo "Système ID: {$systeme->id}\n\n";

$planetes = DB::table('planetes')
    ->where('systeme_stellaire_id', $systeme->id)
    ->select('nom', 'type', 'rayon', 'distance_etoile', 'source_nasa_exoplanet', 'nasa_discovery_method', 'nasa_discovery_year', 'nasa_exo_id')
    ->get();

echo "Nombre de planètes: {$planetes->count()}\n";
echo str_repeat("-", 70) . "\n\n";

foreach ($planetes as $planete) {
    echo "Nom: {$planete->nom}\n";
    echo "Type: {$planete->type}\n";
    echo "Rayon: {$planete->rayon} RT\n";
    echo "Distance: {$planete->distance_etoile} UA\n";
    echo "Source NASA: " . (int)$planete->source_nasa_exoplanet . "\n";
    echo "NASA ID: " . ($planete->nasa_exo_id ?? 'NULL') . "\n";
    echo "Méthode: " . ($planete->nasa_discovery_method ?? 'NULL') . "\n";
    echo "Année: " . ($planete->nasa_discovery_year ?? 'NULL') . "\n";
    echo "\n";
}
