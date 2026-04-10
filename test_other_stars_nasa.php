<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 Recherche des noms NASA pour les étoiles célèbres\n";
echo str_repeat("=", 70) . "\n\n";

$url = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

$stars = [
    'Tau Ceti' => ['Tau Ceti', 'Tau Cet', 'tau Cet'],
    'Epsilon Eridani' => ['Epsilon Eridani', 'Epsilon Eri', 'eps Eri', 'Ran'],
    'Sirius' => ['Sirius', 'Alpha CMa', 'alpha Canis Majoris'],
    'Barnard\'s Star' => ['Barnard\'s Star', 'Barnard', 'GJ 699'],
    'Wolf 359' => ['Wolf 359', 'CN Leo', 'CN Leonis'],
    '61 Cygni A' => ['61 Cyg A', '61 Cygni A', '61 Cygni'],
];

foreach ($stars as $starName => $variants) {
    echo "🌟 {$starName}:\n";

    $found = false;
    foreach ($variants as $variant) {
        $query = "SELECT pl_name FROM ps WHERE hostname='{$variant}'";

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
                    echo "   ✅ Trouvé avec '{$variant}': " . count($data) . " planète(s)\n";
                    foreach (array_slice($data, 0, 3) as $planet) {
                        if (isset($planet['pl_name'])) {
                            echo "      • {$planet['pl_name']}\n";
                        }
                    }
                    $found = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            // Ignorer
        }
    }

    if (!$found) {
        echo "   ⚠️  Aucune exoplanète trouvée\n";
    }

    echo "\n";
}
