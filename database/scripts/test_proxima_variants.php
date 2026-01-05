<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 TEST - Recherche Proxima Centauri par variantes\n";
echo str_repeat("=", 70) . "\n\n";

$url = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

$variants = [
    'Proxima Centauri',
    'Proxima Cen',
    'Proxima',
    'Prox Cen',
    'GJ 551',
    'HIP 70890',
    'V645 Cen',
    'Alpha Centauri C',
];

foreach ($variants as $variant) {
    $query = "SELECT pl_name, hostname FROM ps WHERE hostname='{$variant}'";

    try {
        $response = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->get($url, [
                'query' => $query,
                'format' => 'json',
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data) && is_array($data)) {
                echo "✅ '{$variant}': " . count($data) . " planète(s) trouvée(s)\n";
                foreach ($data as $planet) {
                    if (isset($planet['pl_name'])) {
                        echo "   • {$planet['pl_name']}\n";
                    }
                }
            } else {
                echo "⚠️  '{$variant}': aucune planète\n";
            }
        } else {
            echo "❌ '{$variant}': erreur " . $response->status() . "\n";
        }

    } catch (\Exception $e) {
        echo "❌ '{$variant}': " . $e->getMessage() . "\n";
    }
}
