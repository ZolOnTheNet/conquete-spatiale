<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('planetes');
$nasaColumns = array_filter($columns, fn($c) =>
    str_contains($c, 'nasa') ||
    str_contains($c, 'excentricite') ||
    str_contains($c, 'source_nasa')
);

echo "✅ Colonnes NASA ajoutées:\n";
foreach ($nasaColumns as $col) {
    echo "   - {$col}\n";
}

echo "\n✅ Colonnes existantes (poi_connu, detectabilite_base):\n";
if (in_array('poi_connu', $columns)) echo "   - poi_connu\n";
if (in_array('detectabilite_base', $columns)) echo "   - detectabilite_base\n";

echo "\n✅ Total colonnes planetes: " . count($columns) . "\n";
