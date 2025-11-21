<?php
/**
 * Script de vérification des types de colonnes dans systemes_stellaires
 *
 * Exécution : php verify-column-types.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n=== VÉRIFICATION DES TYPES DE COLONNES ===\n\n";

// 1. Interroger le schéma SQLite
echo "1. SCHÉMA SQLITE (PRAGMA table_info) :\n";
echo str_repeat("-", 80) . "\n";

$tableInfo = DB::select("PRAGMA table_info(systemes_stellaires)");

foreach ($tableInfo as $column) {
    if (in_array($column->name, ['secteur_x', 'secteur_y', 'secteur_z', 'position_x', 'position_y', 'position_z'])) {
        printf("%-15s | Type: %-15s | Nullable: %s | Default: %s\n",
            $column->name,
            $column->type,
            $column->notnull ? 'NO' : 'YES',
            $column->dflt_value ?? 'NULL'
        );
    }
}

// 2. Vérifier avec un exemple de données
echo "\n2. EXEMPLE DE DONNÉES (Premier système) :\n";
echo str_repeat("-", 80) . "\n";

$systeme = DB::table('systemes_stellaires')->first();

if ($systeme) {
    echo "Système : {$systeme->nom}\n\n";

    echo "secteur_x     : " . var_export($systeme->secteur_x, true) . " (type PHP: " . gettype($systeme->secteur_x) . ")\n";
    echo "secteur_y     : " . var_export($systeme->secteur_y, true) . " (type PHP: " . gettype($systeme->secteur_y) . ")\n";
    echo "secteur_z     : " . var_export($systeme->secteur_z, true) . " (type PHP: " . gettype($systeme->secteur_z) . ")\n\n";

    echo "position_x    : " . var_export($systeme->position_x, true) . " (type PHP: " . gettype($systeme->position_x) . ")\n";
    echo "position_y    : " . var_export($systeme->position_y, true) . " (type PHP: " . gettype($systeme->position_y) . ")\n";
    echo "position_z    : " . var_export($systeme->position_z, true) . " (type PHP: " . gettype($systeme->position_z) . ")\n";

    echo "\nVérification stricte des types :\n";
    echo "secteur_x est un integer ? " . (is_int($systeme->secteur_x) ? '✓ OUI' : '✗ NON') . "\n";
    echo "position_x est un string ?  " . (is_string($systeme->position_x) ? '✓ OUI (DECIMAL en SQLite)' : '✗ NON') . "\n";
}

// 3. Vérifier le casting du modèle
echo "\n3. CONFIGURATION DU MODÈLE SystemeStellaire :\n";
echo str_repeat("-", 80) . "\n";

$model = new \App\Models\SystemeStellaire();
$casts = $model->getCasts();

echo "Casts définis :\n";
foreach (['secteur_x', 'secteur_y', 'secteur_z', 'position_x', 'position_y', 'position_z'] as $col) {
    echo "  {$col}: " . ($casts[$col] ?? 'non défini (utilise le type DB)') . "\n";
}

// 4. Test d'une requête WHERE sur secteur
echo "\n4. TEST PERFORMANCE : Recherche par secteur\n";
echo str_repeat("-", 80) . "\n";

$start = microtime(true);
$count = DB::table('systemes_stellaires')
    ->where('secteur_x', 0)
    ->where('secteur_y', 0)
    ->where('secteur_z', 0)
    ->count();
$elapsed = (microtime(true) - $start) * 1000;

echo "Systèmes au secteur (0,0,0): {$count}\n";
echo "Temps d'exécution: " . number_format($elapsed, 3) . " ms\n";
echo "Index utilisé: idx_secteur_systeme\n";

// 5. Test d'une requête DECIMAL
echo "\n5. TEST PERFORMANCE : Recherche par position (DECIMAL)\n";
echo str_repeat("-", 80) . "\n";

$start = microtime(true);
$count = DB::table('systemes_stellaires')
    ->where('position_x', '>', 0.5)
    ->count();
$elapsed = (microtime(true) - $start) * 1000;

echo "Systèmes avec position_x > 0.5: {$count}\n";
echo "Temps d'exécution: " . number_format($elapsed, 3) . " ms\n";
echo "⚠️  Pas d'index sur position_x (normal, on filtre par secteur d'abord)\n";

// 6. DIAGNOSTIC FINAL
echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNOSTIC FINAL\n";
echo str_repeat("=", 80) . "\n\n";

$tableInfo = DB::select("PRAGMA table_info(systemes_stellaires)");
$secteurTypes = [];
$positionTypes = [];

foreach ($tableInfo as $col) {
    if (in_array($col->name, ['secteur_x', 'secteur_y', 'secteur_z'])) {
        $secteurTypes[] = $col->type;
    }
    if (in_array($col->name, ['position_x', 'position_y', 'position_z'])) {
        $positionTypes[] = $col->type;
    }
}

$secteurCorrect = count(array_unique($secteurTypes)) === 1 && strtoupper($secteurTypes[0]) === 'INTEGER';
$positionCorrect = count(array_unique($positionTypes)) === 1 && strpos(strtoupper($positionTypes[0]), 'DECIMAL') !== false;

echo "✓ Secteurs (secteur_x/y/z) : " . implode(', ', array_unique($secteurTypes)) . "\n";
echo "  → " . ($secteurCorrect ? '✓ CORRECT (INTEGER)' : '✗ PROBLÈME ! Devrait être INTEGER') . "\n\n";

echo "✓ Positions (position_x/y/z) : " . implode(', ', array_unique($positionTypes)) . "\n";
echo "  → " . ($positionCorrect ? '✓ CORRECT (DECIMAL)' : '✗ PROBLÈME ! Devrait être DECIMAL') . "\n\n";

if ($secteurCorrect && $positionCorrect) {
    echo "✅ CONCLUSION : Le système est CORRECT !\n";
    echo "   Secteurs = INTEGER (recherche rapide avec index)\n";
    echo "   Positions = DECIMAL (précision 0.001)\n";
} else {
    echo "❌ CONCLUSION : Le système a un PROBLÈME !\n";
    echo "   Vérifiez les migrations et réexécutez-les si nécessaire.\n";
}

echo "\n";
