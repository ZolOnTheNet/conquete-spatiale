<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 TEST NASA API - Exoplanètes proches (<5 AL)\n";
echo str_repeat("=", 70) . "\n\n";

$url = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

// Chercher toutes les exoplanètes dans un rayon de 5 AL
$radiusParsecs = 5 / 3.26156; // ~1.53 parsecs

$query = "SELECT pl_name, hostname, sy_dist "
       . "FROM ps "
       . "WHERE sy_dist < {$radiusParsecs} "
       . "AND pl_name IS NOT NULL "
       . "ORDER BY sy_dist ASC "
       . "LIMIT 20";

echo "Query:\n---\n{$query}\n---\n\n";

try {
    echo "📡 Requête vers NASA Exoplanet Archive...\n";
    $response = Http::timeout(120)
        ->withOptions(['verify' => false])
        ->get($url, [
            'query' => $query,
            'format' => 'json',
        ]);

    echo "Statut: " . $response->status() . "\n\n";

    if ($response->successful()) {
        $data = $response->json();

        if (isset($data['data']) && count($data['data']) > 0) {
            echo "✅ " . count($data['data']) . " exoplanète(s) trouvée(s) à moins de 5 AL:\n\n";

            $byHost = [];
            foreach ($data['data'] as $row) {
                $hostname = $row[1];
                if (!isset($byHost[$hostname])) {
                    $byHost[$hostname] = [];
                }
                $byHost[$hostname][] = $row[0]; // planet name
            }

            foreach ($byHost as $hostname => $planets) {
                echo "🌟 {$hostname}:\n";
                foreach ($planets as $planet) {
                    echo "   • {$planet}\n";
                }
                echo "\n";
            }
        } else {
            echo "⚠️  Aucune exoplanète trouvée dans ce rayon\n";
            echo "Réponse brute: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Erreur HTTP: " . $response->body() . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Test avec une planète connue (51 Pegasi b - première exoplanète découverte autour d'une étoile de type solaire)
echo "\n" . str_repeat("=", 70) . "\n";
echo "📡 Test de contrôle: 51 Pegasi\n\n";

$query2 = "SELECT pl_name, hostname, sy_dist FROM ps WHERE hostname = '51 Peg' LIMIT 5";

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
            echo "✅ API fonctionne! Exoplanètes autour de 51 Peg:\n";
            foreach ($data2['data'] as $row) {
                echo "   • {$row[0]} (distance: {$row[2]} pc)\n";
            }
        } else {
            echo "Réponse: " . json_encode($data2, JSON_PRETTY_PRINT) . "\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
