<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;
use App\Models\Station;

echo "=== STATIONS DANS SOL ===\n\n";

$sol = SystemeStellaire::where('nom', 'Sol')->first();

if ($sol) {
    $stations = Station::where('systeme_stellaire_id', $sol->id)->get();
    echo "Nombre de stations: " . $stations->count() . "\n\n";

    if ($stations->count() > 0) {
        foreach ($stations as $station) {
            echo "  - {$station->nom}\n";
            echo "    Type: {$station->type}\n";
            echo "    Population: {$station->population}\n\n";
        }
    } else {
        echo "❌ Aucune station dans le système Sol !\n";
        echo "Les planètes majeures (Terre, Mars) devraient avoir des stations.\n";
    }
} else {
    echo "❌ Système Sol non trouvé !\n";
}

echo "\n=== TOTAL DES STATIONS ===\n";
$totalStations = Station::count();
echo "Nombre total de stations dans la base: $totalStations\n";

if ($totalStations > 0) {
    echo "\nQuelques exemples:\n";
    Station::limit(5)->get()->each(function($s) {
        echo "  - {$s->nom} (système: {$s->systeme_stellaire_id})\n";
    });
}
