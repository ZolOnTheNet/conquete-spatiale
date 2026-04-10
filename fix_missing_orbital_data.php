<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Planete;

echo "=== Correction des données orbitales manquantes ===" . PHP_EOL . PHP_EOL;

$planetes = Planete::all();

$corrected = 0;

foreach ($planetes as $planete) {
    $needsUpdate = false;

    // Si la planète a une période orbitale mais pas de vitesse angulaire, la calculer
    if ($planete->periode_orbitale > 0 && is_null($planete->vitesse_angulaire)) {
        $planete->vitesse_angulaire = (2 * M_PI) / $planete->periode_orbitale;
        $needsUpdate = true;
        echo "✓ Calcul vitesse_angulaire pour {$planete->nom}: " . round($planete->vitesse_angulaire, 6) . " rad/jour" . PHP_EOL;
    }

    // Si la planète a une distance mais pas de période orbitale, la calculer (Loi de Kepler)
    if ($planete->distance_etoile > 0 && is_null($planete->periode_orbitale)) {
        $planete->periode_orbitale = (int)(365.25 * sqrt(pow($planete->distance_etoile, 3)));
        $planete->vitesse_angulaire = (2 * M_PI) / $planete->periode_orbitale;
        $needsUpdate = true;
        echo "✓ Calcul periode_orbitale pour {$planete->nom}: {$planete->periode_orbitale} jours" . PHP_EOL;
    }

    // Si la planète n'a pas d'angle orbital initial, en générer un aléatoire
    if (is_null($planete->angle_orbital_initial) && $planete->distance_etoile > 0) {
        $planete->angle_orbital_initial = mt_rand(0, 62831) / 10000; // 0 à 2π
        $needsUpdate = true;
        echo "✓ Génération angle_orbital_initial pour {$planete->nom}: " . round($planete->angle_orbital_initial, 4) . " rad" . PHP_EOL;
    }

    // Calculer la validité du cache si nécessaire
    if ($planete->periode_orbitale > 0 && is_null($planete->cache_validite_jours)) {
        $planete->cache_validite_jours = max(10, (int)($planete->periode_orbitale / 50));
        $needsUpdate = true;
    }

    if ($needsUpdate) {
        $planete->save();
        $corrected++;
    }
}

echo PHP_EOL . "=== Correction terminée ===" . PHP_EOL;
echo "Planètes corrigées : $corrected" . PHP_EOL . PHP_EOL;

// Vérification finale
$sansOrbitale = Planete::whereNull('vitesse_angulaire')
    ->orWhereNull('periode_orbitale')
    ->orWhereNull('angle_orbital_initial')
    ->where('distance_etoile', '>', 0)
    ->count();

echo "Planètes restant sans données orbitales : $sansOrbitale" . PHP_EOL;

if ($sansOrbitale == 0) {
    echo "✅ Toutes les planètes ont maintenant leurs données orbitales !" . PHP_EOL;
}
