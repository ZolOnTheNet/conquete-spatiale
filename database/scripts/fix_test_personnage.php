<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Personnage;
use App\Helpers\GameTimeHelper;
use Carbon\Carbon;

echo "=== Réinitialisation du personnage de test ===" . PHP_EOL;

$personnage = Personnage::first();

if (!$personnage) {
    echo "❌ Aucun personnage trouvé" . PHP_EOL;
    exit(1);
}

echo "Personnage: {$personnage->nom}" . PHP_EOL;
echo "PA avant: {$personnage->points_action}" . PHP_EOL;
echo "Date avant: " . ($personnage->derniere_connexion ?? 'NULL') . PHP_EOL;

// Réinitialiser
$personnage->points_action = 36; // Max PA
$personnage->derniere_connexion = GameTimeHelper::DATE_REFERENCE; // 3000-01-01
$personnage->save();

echo PHP_EOL;
echo "✓ PA après: {$personnage->points_action}" . PHP_EOL;
echo "✓ Date après: {$personnage->derniere_connexion}" . PHP_EOL;
echo PHP_EOL;
echo "Le personnage est prêt pour le test !" . PHP_EOL;
