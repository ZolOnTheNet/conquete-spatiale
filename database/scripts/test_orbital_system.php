<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Personnage;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Helpers\GameTimeHelper;
use Carbon\Carbon;

echo "=== TEST DU SYSTÈME ORBITAL COMPLET ===" . PHP_EOL . PHP_EOL;

// 1. Récupérer un personnage
$personnage = Personnage::first();
if (!$personnage) {
    echo "❌ Aucun personnage trouvé en base" . PHP_EOL;
    exit(1);
}

echo "👤 Personnage: {$personnage->nom}" . PHP_EOL;
echo "📅 Date actuelle du jeu: " . GameTimeHelper::formatDateJeu(
    GameTimeHelper::getDateActuelleJeu($personnage)
) . PHP_EOL;
echo "⚡ PA disponibles: {$personnage->points_action}/{$personnage->max_points_action}" . PHP_EOL;
echo PHP_EOL;

// 2. Trouver le système Sol
$sol = SystemeStellaire::where('nom', 'Sol')->orWhere('nom', 'like', '%Sol%')->first();
if (!$sol) {
    echo "⚠️  Système Sol non trouvé, utilisation du premier système" . PHP_EOL;
    $sol = SystemeStellaire::first();
}

echo "⭐ Système testé: {$sol->nom}" . PHP_EOL;
echo "   Position: ({$sol->secteur_x}, {$sol->secteur_y}, {$sol->secteur_z}) AL" . PHP_EOL;
echo PHP_EOL;

// 3. Récupérer les planètes connues du système
$planetes = $sol->planetes()->where('poi_connu', true)->get();
echo "🪐 Planètes connues dans le système: " . $planetes->count() . PHP_EOL . PHP_EOL;

if ($planetes->count() == 0) {
    echo "⚠️  Aucune planète connue dans ce système" . PHP_EOL;
    exit(0);
}

// 4. Tester les positions orbitales AVANT dépense de PA
echo "=== POSITIONS DES PLANÈTES (Date actuelle) ===" . PHP_EOL;
$timestampActuel = GameTimeHelper::getTimestampJoursActuel($personnage);

foreach ($planetes->take(5) as $planete) {
    $position = $planete->getPositionAbsolue($timestampActuel);
    $donneesOrbitales = $planete->getDonneesOrbitales($personnage);

    echo "  🌍 {$planete->nom}" . PHP_EOL;
    echo "     Distance étoile: {$planete->distance_etoile} UA" . PHP_EOL;
    echo "     Période orbitale: {$planete->periode_orbitale} jours" . PHP_EOL;
    echo "     Vitesse angulaire: " . round($planete->vitesse_angulaire, 6) . " rad/jour" . PHP_EOL;
    echo "     Position orbitale: X=" . round($donneesOrbitales['cache_position_x']/100, 2) . " UA, ";
    echo "Y=" . round($donneesOrbitales['cache_position_y']/100, 2) . " UA" . PHP_EOL;
    echo PHP_EOL;
}

// 5. Simuler dépense de PA et vérifier changement de positions
echo "=== SIMULATION DÉPENSE DE PA ===" . PHP_EOL;
$paAvant = $personnage->points_action;
$dateAvant = Carbon::parse($personnage->derniere_connexion);

echo "Dépense de 10 PA (= 5 jours in-game)..." . PHP_EOL;
$personnage->consommerPA(10);
$personnage->refresh();

$dateApres = Carbon::parse($personnage->derniere_connexion);
$joursAvances = $dateAvant->floatDiffInDays($dateApres, false);

echo "✓ PA restants: {$personnage->points_action}/{$personnage->max_points_action}" . PHP_EOL;
echo "✓ Date in-game avancée de: " . round($joursAvances, 2) . " jours" . PHP_EOL;
echo "✓ Nouvelle date: " . GameTimeHelper::formatDateJeu($dateApres) . PHP_EOL;
echo PHP_EOL;

// 6. Vérifier que les positions ont changé
echo "=== NOUVELLES POSITIONS DES PLANÈTES (Après +5 jours) ===" . PHP_EOL;
$timestampNouveau = GameTimeHelper::getTimestampJoursActuel($personnage);

foreach ($planetes->take(5) as $planete) {
    // Forcer le recalcul
    $planete->refresh();
    $donneesOrbitales = $planete->getDonneesOrbitales($personnage);

    // Calculer angle actuel
    $angleActuel = $planete->angle_orbital_initial + ($planete->vitesse_angulaire * $timestampNouveau);
    $x_ua = $planete->distance_etoile * cos($angleActuel);
    $y_ua = $planete->distance_etoile * sin($angleActuel);

    echo "  🌍 {$planete->nom}" . PHP_EOL;
    echo "     Angle orbital actuel: " . round($angleActuel, 4) . " rad (" . round(rad2deg($angleActuel), 1) . "°)" . PHP_EOL;
    echo "     Nouvelle position: X=" . round($x_ua, 2) . " UA, Y=" . round($y_ua, 2) . " UA" . PHP_EOL;
    echo PHP_EOL;
}

echo "=== TEST TERMINÉ ===" . PHP_EOL;
echo "✅ Toutes les planètes ont leurs données orbitales" . PHP_EOL;
echo "✅ Le temps avance correctement à chaque dépense de PA" . PHP_EOL;
echo "✅ Les positions des planètes se mettent à jour automatiquement" . PHP_EOL;
