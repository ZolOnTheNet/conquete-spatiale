<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check objets_spatiaux columns
$columns = DB::select("PRAGMA table_info(objets_spatiaux)");

echo "ObjetSpatial columns:\n";
echo str_repeat('=', 80) . "\n";

foreach ($columns as $col) {
    echo sprintf("%-30s | %-15s | %s\n", $col->name, $col->type, $col->notnull ? 'NOT NULL' : '');
}

// Check types of objects
echo "\n\nTypes of objets_spatiaux:\n";
echo str_repeat('=', 80) . "\n";

$types = DB::table('objets_spatiaux')
    ->select('type', DB::raw('COUNT(*) as count'))
    ->groupBy('type')
    ->get();

foreach ($types as $t) {
    echo "Type: {$t->type} - Count: {$t->count}\n";
}

// Check sample positions
echo "\n\nSample ObjetSpatial positions (first 10):\n";
echo str_repeat('=', 80) . "\n";

$objets = DB::table('objets_spatiaux')
    ->limit(10)
    ->get(['id', 'type', 'nom', 'secteur_x', 'secteur_y', 'secteur_z', 'position_x', 'position_y', 'position_z']);

foreach ($objets as $o) {
    echo "ID: {$o->id} | Type: {$o->type} | Nom: {$o->nom}\n";
    echo "  Secteur: ({$o->secteur_x}, {$o->secteur_y}, {$o->secteur_z}) AL\n";
    echo "  Position: ({$o->position_x}, {$o->position_y}, {$o->position_z}) cUA\n\n";
}
