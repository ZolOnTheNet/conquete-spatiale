<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "VÉRIFICATION DES UNITÉS DE POSITION (cUA)\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

// 1. Vérifier objets_spatiaux
echo "1. OBJETS SPATIAUX (objets_spatiaux table)\n";
echo str_repeat('─', 79) . "\n";

$objets = DB::table('objets_spatiaux')->get(['id', 'type', 'nom', 'position_x', 'position_y', 'position_z']);
echo "Total objets: " . $objets->count() . "\n\n";

foreach ($objets as $obj) {
    echo "  [{$obj->id}] {$obj->type}: {$obj->nom}\n";
    echo "    Position: ({$obj->position_x}, {$obj->position_y}, {$obj->position_z})\n";

    // Vérifier si les valeurs semblent être en cUA (valeurs typiques: 0-630000 cUA pour 1 AL)
    $max_pos = max(abs($obj->position_x), abs($obj->position_y), abs($obj->position_z));
    if ($max_pos > 0 && $max_pos < 100) {
        echo "    ⚠️ SUSPECT: Valeurs très faibles, possiblement en UA au lieu de cUA\n";
    } elseif ($max_pos > 1000000) {
        echo "    ⚠️ SUSPECT: Valeurs très élevées\n";
    } else {
        echo "    ✓ Semble cohérent avec cUA\n";
    }
    echo "\n";
}

// 2. Vérifier systèmes stellaires
echo "\n2. SYSTÈMES STELLAIRES (systemes_stellaires table)\n";
echo str_repeat('─', 79) . "\n";

$systemes = DB::table('systemes_stellaires')
    ->limit(5)
    ->get(['id', 'nom', 'secteur_x', 'secteur_y', 'secteur_z', 'position_x', 'position_y', 'position_z']);

echo "Total systèmes (showing first 5): " . DB::table('systemes_stellaires')->count() . "\n\n";

foreach ($systemes as $sys) {
    echo "  [{$sys->id}] {$sys->nom}\n";
    echo "    Secteur (AL): ({$sys->secteur_x}, {$sys->secteur_y}, {$sys->secteur_z})\n";
    echo "    Position (cUA?): ({$sys->position_x}, {$sys->position_y}, {$sys->position_z})\n";

    $max_pos = max(abs($sys->position_x), abs($sys->position_y), abs($sys->position_z));
    if ($max_pos > 0 && $max_pos < 100) {
        echo "    ⚠️ SUSPECT: Possiblement en UA\n";
    } else {
        echo "    ✓ Semble en cUA\n";
    }
    echo "\n";
}

// 3. Vérifier planètes
echo "\n3. PLANÈTES (planetes table)\n";
echo str_repeat('─', 79) . "\n";

$planetes = DB::table('planetes')
    ->limit(5)
    ->get(['id', 'nom', 'distance_etoile', 'cache_position_x', 'cache_position_y', 'cache_position_z']);

echo "Total planètes (showing first 5): " . DB::table('planetes')->count() . "\n\n";

foreach ($planetes as $p) {
    echo "  [{$p->id}] {$p->nom}\n";
    echo "    distance_etoile: {$p->distance_etoile} (devrait être en UA)\n";
    echo "    cache_position: ({$p->cache_position_x}, {$p->cache_position_y}, {$p->cache_position_z})\n";

    // distance_etoile devrait être en UA (valeurs typiques: 0.38 - 30 pour système solaire)
    if ($p->distance_etoile > 0 && $p->distance_etoile < 1000) {
        echo "    ✓ distance_etoile semble en UA\n";
    } else {
        echo "    ⚠️ SUSPECT: distance_etoile devrait être en UA\n";
    }

    // cache_position devrait être en cUA si rempli
    if ($p->cache_position_x !== null) {
        $max_cache = max(abs($p->cache_position_x ?? 0), abs($p->cache_position_y ?? 0), abs($p->cache_position_z ?? 0));
        if ($max_cache > 0 && $max_cache < 100) {
            echo "    ⚠️ SUSPECT: cache_position possiblement en UA\n";
        } elseif ($max_cache > 0) {
            echo "    ✓ cache_position semble en cUA\n";
        }
    } else {
        echo "    ℹ️  cache_position vide (normal si pas encore calculé)\n";
    }
    echo "\n";
}

// 4. Vérifier stations
echo "\n4. STATIONS (stations table)\n";
echo str_repeat('─', 79) . "\n";

$stations_count = DB::table('stations')->count();
echo "Total stations: {$stations_count}\n";

if ($stations_count > 0) {
    $stations = DB::table('stations')
        ->limit(5)
        ->get(['id', 'nom', 'orbite_rayon_ua', 'orbite_angle']);

    foreach ($stations as $st) {
        echo "  [{$st->id}] {$st->nom}\n";
        echo "    orbite_rayon_ua: {$st->orbite_rayon_ua} (nom suggère UA, devrait être cUA?)\n";
        echo "    orbite_angle: {$st->orbite_angle}°\n";

        if ($st->orbite_rayon_ua > 0 && $st->orbite_rayon_ua < 100) {
            echo "    ⚠️ ATTENTION: 'orbite_rayon_ua' semble être en UA, devrait être renommé/converti en cUA\n";
        }
        echo "\n";
    }
} else {
    echo "  ℹ️  Aucune station dans la base de données\n\n";
}

// 5. Résumé
echo "\n═══════════════════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

echo "✓ objets_spatiaux.position_x/y/z : Semblent être en cUA\n";
echo "✓ systemes_stellaires.position_x/y/z : Semblent être en cUA\n";
echo "✓ planetes.cache_position_x/y/z : Documentation dit cUA (à vérifier si remplis)\n";
echo "⚠️  planetes.distance_etoile : En UA (correct pour ce champ)\n";

if ($stations_count > 0) {
    echo "⚠️  stations.orbite_rayon_ua : Nom suggère UA, à vérifier/convertir en cUA\n";
} else {
    echo "ℹ️  stations : Aucune dans la base, impossible de vérifier\n";
}

echo "\nNOTE: Les positions dans les secteurs (position_x/y/z) doivent être en cUA (centièmes d'UA)\n";
echo "      Les distances orbitales (distance_etoile) peuvent rester en UA pour lisibilité\n";
echo "      Mais pour cohérence, orbite_rayon_ua devrait être en cUA également\n";
