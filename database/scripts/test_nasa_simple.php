<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 TEST NASA API - Requêtes simples\n";
echo str_repeat("=", 70) . "\n\n";

$url = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

// Test 1: Lister quelques systèmes célèbres
echo "📡 Test 1: Systèmes célèbres\n\n";

$systems = ['51 Peg', 'TRAPPIST-1', 'Kepler-186', 'Tau Cet'];

foreach ($systems as $system) {
    $query = "SELECT pl_name, hostname FROM ps WHERE hostname='{$system}'";

    try {
        $response = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->get($url, [
                'query' => $query,
                'format' => 'json',
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data)) {
                echo "✅ {$system}: " . count($data) . " planète(s)\n";
                if (count($data) > 0 && isset($data[0])) {
                    echo "   Exemple: " . json_encode($data[0]) . "\n";
                }
            } else {
                echo "⚠️  {$system}: aucune donnée\n";
            }
        } else {
            echo "❌ {$system}: erreur " . $response->status() . "\n";
        }

    } catch (\Exception $e) {
        echo "❌ {$system}: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

// Test 2: Liste des 10 premières planètes
echo str_repeat("=", 70) . "\n";
echo "📡 Test 2: Liste des 10 premières planètes du catalogue\n\n";

$query = "SELECT pl_name, hostname, sy_dist, disc_year FROM ps LIMIT 10";

try {
    $response = Http::timeout(30)
        ->withOptions(['verify' => false])
        ->get($url, [
            'query' => $query,
            'format' => 'json',
        ]);

    if ($response->successful()) {
        $data = $response->json();

        echo "Réponse brute:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        if (!empty($data)) {
            echo "✅ " . count($data) . " planètes récupérées\n";
        }
    } else {
        echo "❌ Erreur: " . $response->body() . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
