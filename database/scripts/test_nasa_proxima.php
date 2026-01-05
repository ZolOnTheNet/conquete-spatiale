<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 TEST NASA API - Proxima Centauri\n";
echo str_repeat("=", 70) . "\n\n";

$url = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

// Test 1: Recherche exacte par nom
$query = "SELECT pl_name, hostname, sy_dist, pl_orbsmax, pl_rade, pl_bmasse, "
       . "pl_orbper, pl_eqt, pl_orbeccen, st_teff, discoverymethod, disc_year "
       . "FROM ps "
       . "WHERE hostname = 'Proxima Centauri' "
       . "ORDER BY pl_orbsmax ASC";

echo "Query:\n---\n{$query}\n---\n\n";

try {
    echo "📡 Requête vers NASA Exoplanet Archive...\n";
    $response = Http::timeout(120)
        ->withOptions(['verify' => false]) // Désactiver vérification SSL pour test
        ->get($url, [
            'query' => $query,
            'format' => 'json',
        ]);

    echo "Statut: " . $response->status() . "\n";

    if ($response->successful()) {
        $data = $response->json();

        echo "\nRéponse JSON:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        if (isset($data['data']) && count($data['data']) > 0) {
            echo "✅ " . count($data['data']) . " exoplanète(s) trouvée(s):\n";
            foreach ($data['data'] as $row) {
                echo "   • {$row[0]} (distance orbitale: {$row[3]} AU)\n";
            }
        } else {
            echo "⚠️  Aucune exoplanète trouvée\n";
        }
    } else {
        echo "❌ Erreur HTTP: " . $response->body() . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Test 2: Recherche variante "Proxima"
echo "\n" . str_repeat("=", 70) . "\n";
echo "📡 Test variante: Proxima\n\n";

$query2 = "SELECT pl_name, hostname FROM ps WHERE hostname LIKE '%Proxima%'";

try {
    $response2 = Http::timeout(120)
        ->withOptions(['verify' => false])
        ->get($url, [
            'query' => $query2,
            'format' => 'json',
        ]);

    if ($response2->successful()) {
        $data2 = $response2->json();

        if (isset($data2['data']) && count($data2['data']) > 0) {
            echo "✅ Trouvé avec LIKE:\n";
            foreach ($data2['data'] as $row) {
                echo "   • {$row[0]} autour de '{$row[1]}'\n";
            }
        } else {
            echo "⚠️  Aucun résultat avec LIKE\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
