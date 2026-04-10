<?php

/**
 * Script de migration : Stations → ObjetSpatial
 *
 * Crée un ObjetSpatial pour chaque Station existante
 * et lie la station à cet objet spatial.
 *
 * USAGE :
 *   php artisan tinker
 *   >>> include 'scripts/migrate_stations_to_objets_spatiaux.php';
 *
 * PRÉREQUIS :
 *   - Migration zones_spatiales exécutée
 *   - Migration parent polymorphique exécutée
 *   - Migration Station.objet_spatial_id PAS ENCORE exécutée (nullable)
 *
 * @see database/migrations/2025_12_31_140000_add_objet_spatial_id_to_stations.php
 */

use App\Models\Station;
use App\Models\ObjetSpatial;
use App\Models\Planete;
use App\Models\SystemeStellaire;

echo "=== Migration Stations → ObjetSpatial ===\n\n";

$stations = Station::all();
$total = $stations->count();
$success = 0;
$errors = 0;

echo "Stations à migrer : {$total}\n\n";

foreach ($stations as $station) {
    try {
        // Vérifier si déjà migré
        if ($station->objet_spatial_id) {
            echo "⚠️  Station #{$station->id} ({$station->nom}) déjà migrée\n";
            continue;
        }

        // Calculer position depuis orbite planète ou système
        $position = [
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'position_x' => 0,
            'position_y' => 0,
            'position_z' => 0,
        ];

        $parentType = null;
        $parentId = null;

        // Cas 1 : Station en orbite autour d'une planète
        if ($station->planete_id) {
            $planete = Planete::find($station->planete_id);

            if ($planete) {
                // Position de la planète
                $posPlanete = $planete->getPositionOrbitale();

                // Offset orbital de la station
                $rayonCua = ($station->orbite_rayon_ua ?? 0.05) * 100;
                $angle = $station->orbite_angle ?? 0;

                $position = [
                    'secteur_x' => $planete->systemeStellaire->secteur_x ?? 0,
                    'secteur_y' => $planete->systemeStellaire->secteur_y ?? 0,
                    'secteur_z' => $planete->systemeStellaire->secteur_z ?? 0,
                    'position_x' => (int)($posPlanete['x'] + ($rayonCua * cos($angle))),
                    'position_y' => (int)($posPlanete['y'] + ($rayonCua * sin($angle))),
                    'position_z' => (int)$posPlanete['z'],
                ];

                $parentType = Planete::class;
                $parentId = $planete->id;

                echo "  → Orbite planète {$planete->nom}\n";
            }
        }
        // Cas 2 : Station dans un système stellaire (position fixe)
        elseif ($station->systeme_stellaire_id) {
            $systeme = SystemeStellaire::find($station->systeme_stellaire_id);

            if ($systeme) {
                $position = [
                    'secteur_x' => $systeme->secteur_x ?? 0,
                    'secteur_y' => $systeme->secteur_y ?? 0,
                    'secteur_z' => $systeme->secteur_z ?? 0,
                    'position_x' => $systeme->position_x ?? 0,
                    'position_y' => $systeme->position_y ?? 0,
                    'position_z' => $systeme->position_z ?? 0,
                ];

                $parentType = SystemeStellaire::class;
                $parentId = $systeme->id;

                echo "  → Dans système {$systeme->nom}\n";
            }
        }

        // Créer ObjetSpatial
        $objet = ObjetSpatial::create([
            'nom' => $station->nom,
            'type' => 'station',
            'secteur_x' => $position['secteur_x'],
            'secteur_y' => $position['secteur_y'],
            'secteur_z' => $position['secteur_z'],
            'position_x' => $position['position_x'],
            'position_y' => $position['position_y'],
            'position_z' => $position['position_z'],
            'parent_type' => $parentType,
            'parent_id' => $parentId,
            'detectabilite_base' => $station->detectabilite_base ?? 40,
            'poi_connu' => $station->poi_connu ?? true,
            'masse' => 100000, // Station = 100 tonnes par défaut
            'volume' => 50000,
        ]);

        // Lier station à objet spatial
        $station->objet_spatial_id = $objet->id;
        $station->save();

        echo "✅ Station #{$station->id} ({$station->nom}) → ObjetSpatial #{$objet->id}\n";
        echo "   Position : ({$position['secteur_x']}, {$position['secteur_y']}, {$position['secteur_z']}) ";
        echo "/ ({$position['position_x']}, {$position['position_y']}, {$position['position_z']}) cUA\n\n";

        $success++;

    } catch (Exception $e) {
        echo "❌ Erreur Station #{$station->id} : {$e->getMessage()}\n\n";
        $errors++;
    }
}

echo "\n=== Résumé ===\n";
echo "Total : {$total}\n";
echo "Succès : {$success}\n";
echo "Erreurs : {$errors}\n";
echo "Déjà migrées : " . ($total - $success - $errors) . "\n\n";

echo "PROCHAINE ÉTAPE : Exécuter migration add_objet_spatial_id_to_stations\n";
echo "  php artisan migrate --path=database/migrations/2025_12_31_140000_add_objet_spatial_id_to_stations.php\n";
