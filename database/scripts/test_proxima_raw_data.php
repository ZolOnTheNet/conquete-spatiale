<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ExoplanetService;
use Illuminate\Support\Facades\Log;

echo "🔍 Données brutes NASA pour Proxima Cen\n";
echo str_repeat("=", 70) . "\n\n";

$service = app(ExoplanetService::class);

// Récupérer les données
$exoplanets = $service->getExoplanetsForStar('Proxima Cen');

echo "Nombre d'exoplanètes trouvées: " . count($exoplanets) . "\n\n";

foreach ($exoplanets as $index => $exo) {
    echo "Exoplanète #{$index}:\n";
    echo json_encode($exo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    echo str_repeat("-", 70) . "\n\n";
}
