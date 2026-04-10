<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = DB::select("PRAGMA table_info(planetes)");

echo "Planete columns containing 'position' or 'distance':\n";
echo str_repeat('=', 60) . "\n";

foreach ($columns as $col) {
    if (stripos($col->name, 'position') !== false || stripos($col->name, 'distance') !== false) {
        echo sprintf("%-30s | %s\n", $col->name, $col->type);
    }
}

// Check sample planet data
echo "\n\nSample planet position data (first 5 planets):\n";
echo str_repeat('=', 60) . "\n";

$planets = DB::table('planetes')->limit(5)->get(['nom', 'cache_position_x', 'cache_position_y', 'cache_position_z', 'distance_etoile']);

foreach ($planets as $p) {
    echo "Planet: {$p->nom}\n";
    echo "  cache_position: ({$p->cache_position_x}, {$p->cache_position_y}, {$p->cache_position_z})\n";
    echo "  distance_etoile: {$p->distance_etoile}\n\n";
}
