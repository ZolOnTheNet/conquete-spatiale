<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\SystemeStellaire;

echo "🔍 TEST SIMBAD API\n";
echo str_repeat("=", 70) . "\n\n";

// Récupérer une étoile GAIA
$star = SystemeStellaire::where('source_gaia', true)
    ->whereNotNull('gaia_source_id')
    ->first();

if (!$star) {
    echo "❌ Aucune étoile GAIA trouvée\n";
    exit(1);
}

echo "🌟 Étoile testée: {$star->nom}\n";
echo "   GAIA ID: {$star->gaia_source_id}\n";
echo "   Distance: " . number_format($star->gaia_distance_ly, 2) . " AL\n\n";

// Nettoyer l'ID
$cleanId = str_replace(['GAIA DR3 ', 'Gaia DR3 ', 'gaia dr3 '], '', $star->gaia_source_id);

echo "📡 Requête SIMBAD TAP...\n";
echo "   Clean ID: {$cleanId}\n\n";

// Test 1: Requête simple par ID
$url = 'http://simbad.u-strasbg.fr/simbad/sim-tap/sync';

$query = "SELECT main_id, otype_txt " .
         "FROM basic " .
         "WHERE oid IN (" .
         "  SELECT oidref FROM ids WHERE id = 'Gaia DR3 {$cleanId}'" .
         ")";

echo "Query ADQL:\n";
echo "---\n{$query}\n---\n\n";

try {
    $response = Http::timeout(30)->get($url, [
        'request' => 'doQuery',
        'lang' => 'ADQL',
        'format' => 'json',
        'query' => $query,
    ]);

    echo "Statut: " . $response->status() . "\n";

    if ($response->successful()) {
        $data = $response->json();

        echo "Réponse JSON:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

        if (isset($data['data']) && count($data['data']) > 0) {
            echo "✅ Nom principal trouvé: " . $data['data'][0][0] . "\n";
            echo "   Type: " . $data['data'][0][1] . "\n";
        } else {
            echo "⚠️  Aucune donnée retournée\n";
        }
    } else {
        echo "❌ Erreur HTTP: " . $response->body() . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Test 2: Recherche inversée (nom commun → GAIA)
echo "\n" . str_repeat("=", 70) . "\n";
echo "📡 Test inversé: Proxima Centauri → GAIA ID\n\n";

$query2 = "SELECT id " .
          "FROM ids " .
          "WHERE oidref IN (" .
          "  SELECT oid FROM basic WHERE main_id = 'Proxima Centauri'" .
          ") AND id LIKE 'Gaia DR3%'";

echo "Query:\n---\n{$query2}\n---\n\n";

try {
    $response2 = Http::timeout(30)->get($url, [
        'request' => 'doQuery',
        'lang' => 'ADQL',
        'format' => 'json',
        'query' => $query2,
    ]);

    if ($response2->successful()) {
        $data2 = $response2->json();

        echo "Réponse:\n";
        echo json_encode($data2, JSON_PRETTY_PRINT) . "\n\n";

        if (isset($data2['data']) && count($data2['data']) > 0) {
            echo "✅ GAIA ID trouvé: " . $data2['data'][0][0] . "\n";
        } else {
            echo "⚠️  Proxima Centauri non trouvé dans SIMBAD\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
