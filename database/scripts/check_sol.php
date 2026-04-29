<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemeStellaire;

$sol = SystemeStellaire::where('nom', 'Sol')
    ->orWhere('nom', 'like', '%Sun%')
    ->orWhere('nom', 'like', 'Solar%')
    ->get();

if ($sol->isEmpty()) {
    echo "❌ AUCUN système 'Sol' trouvé dans la base de données!\n\n";

    // Chercher le système à (0,0,0)
    $systemeZero = SystemeStellaire::where('secteur_x', 0)
        ->where('secteur_y', 0)
        ->where('secteur_z', 0)
        ->where('position_x', 0)
        ->where('position_y', 0)
        ->where('position_z', 0)
        ->first();

    if ($systemeZero) {
        echo "✓ Système trouvé à (0,0,0): {$systemeZero->nom}\n";
    } else {
        echo "❌ AUCUN système à la position (0,0,0) !\n";
    }
} else {
    echo "✓ Système Sol trouvé:\n";
    foreach ($sol as $s) {
        echo "  - {$s->nom} at secteur ({$s->secteur_x},{$s->secteur_y},{$s->secteur_z}) + position ({$s->position_x},{$s->position_y},{$s->position_z})\n";
    }
}
