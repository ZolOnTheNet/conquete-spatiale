<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

echo "🌟 Vérification des planètes de Proxima Centauri\n";
echo str_repeat("=", 70) . "\n\n";

$systeme = SystemeStellaire::where('nom_commun', 'Proxima Centauri')
    ->with('planetes')
    ->first();

if (!$systeme) {
    echo "❌ Système non trouvé\n";
    exit(1);
}

echo "Système: {$systeme->nom}\n";
echo "Nom commun: {$systeme->nom_commun}\n";
echo "Aliases: {$systeme->noms_alternatifs}\n";
echo "Type: {$systeme->type_etoile}\n";
echo "Distance: " . number_format($systeme->gaia_distance_ly, 2) . " AL\n\n";

$planetes = $systeme->planetes;

echo "Nombre de planètes: {$planetes->count()}\n";
echo str_repeat("-", 70) . "\n\n";

foreach ($planetes as $planete) {
    echo "🪐 {$planete->nom}\n";
    echo "   Type: {$planete->type}\n";
    echo "   Rayon: {$planete->rayon} RT (rayons terrestres)\n";
    echo "   Masse: {$planete->masse} MT (masses terrestres)\n";
    echo "   Distance orbitale: {$planete->distance_etoile} UA\n";
    echo "   Période orbitale: {$planete->periode_orbitale} jours\n";
    echo "   Température: " . ($planete->temperature_moyenne ?? 'N/A') . " °C\n";
    echo "   Habitable: " . ($planete->habitable ? 'Oui' : 'Non') . "\n";
    echo "   Source NASA: " . ($planete->source_nasa_exoplanet ? 'Oui' : 'Non') . "\n";
    echo "   Méthode découverte: {$planete->nasa_discovery_method}\n";
    echo "   Année découverte: {$planete->nasa_discovery_year}\n";
    echo "\n";
}
