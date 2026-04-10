<?php

/**
 * Script de migration : Mines → ObjetSpatial
 *
 * Crée un ObjetSpatial pour chaque Mine existante
 * et lie la mine à cet objet spatial.
 *
 * Types de mines :
 * - MAME (autonome) : attachée à planète/astéroïde
 * - Évoluée : attachée à station
 *
 * USAGE :
 *   php artisan tinker
 *   >>> include 'scripts/migrate_mines_to_objets_spatiaux.php';
 *
 * PRÉREQUIS :
 *   - Migration zones_spatiales exécutée
 *   - Migration parent polymorphique exécutée
 *   - Migration stations_to_objets_spatiaux exécutée
 *   - Migration Mine.objet_spatial_id PAS ENCORE exécutée (nullable)
 *
 * @see database/migrations/2025_12_31_140001_add_objet_spatial_id_to_mines.php
 */

use App\Models\Mine;
use App\Models\ObjetSpatial;
use App\Models\Planete;
use App\Models\Station;
use App\Models\Base;

echo "=== Migration Mines → ObjetSpatial ===\n\n";

$mines = Mine::all();
$total = $mines->count();
$success = 0;
$errors = 0;

echo "Mines à migrer : {$total}\n\n";

foreach ($mines as $mine) {
    try {
        // Vérifier si déjà migré
        if ($mine->objet_spatial_id) {
            echo "⚠️  Mine #{$mine->id} ({$mine->nom}) déjà migrée\n";
            continue;
        }

        // Calculer position
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

        // Cas 1 : Mine sur planète (MAME)
        if ($mine->planete_id) {
            $planete = Planete::find($mine->planete_id);

            if ($planete) {
                $posPlanete = $planete->getPositionOrbitale();

                $position = [
                    'secteur_x' => $planete->systemeStellaire->secteur_x ?? 0,
                    'secteur_y' => $planete->systemeStellaire->secteur_y ?? 0,
                    'secteur_z' => $planete->systemeStellaire->secteur_z ?? 0,
                    'position_x' => (int)$posPlanete['x'],
                    'position_y' => (int)$posPlanete['y'],
                    'position_z' => (int)$posPlanete['z'],
                ];

                $parentType = Planete::class;
                $parentId = $planete->id;

                echo "  → Sur planète {$planete->nom}\n";
            }
        }
        // Cas 2 : Mine attachée à station (évoluée)
        elseif ($mine->base_id) {
            $base = Base::find($mine->base_id);

            if ($base && $base->objetSpatial) {
                $posBase = $base->objetSpatial->getPosition();

                $position = [
                    'secteur_x' => $posBase['secteur']['x'],
                    'secteur_y' => $posBase['secteur']['y'],
                    'secteur_z' => $posBase['secteur']['z'],
                    'position_x' => $posBase['position']['x'],
                    'position_y' => $posBase['position']['y'],
                    'position_z' => $posBase['position']['z'],
                ];

                // Parent = la base (qui elle-même a un objet_spatial_id)
                $parentType = ObjetSpatial::class;
                $parentId = $base->objet_spatial_id;

                echo "  → Attachée à base {$base->nom}\n";
            }
        }

        // Créer ObjetSpatial
        $objet = ObjetSpatial::create([
            'nom' => $mine->nom,
            'type' => 'mine',
            'secteur_x' => $position['secteur_x'],
            'secteur_y' => $position['secteur_y'],
            'secteur_z' => $position['secteur_z'],
            'position_x' => $position['position_x'],
            'position_y' => $position['position_y'],
            'position_z' => $position['position_z'],
            'parent_type' => $parentType,
            'parent_id' => $parentId,
            'detectabilite_base' => $mine->detectabilite_base ?? 50,
            'poi_connu' => $mine->poi_connu ?? false,
            'masse' => 5000, // Mine MAME = 5 tonnes
            'volume' => 1000,
        ]);

        // Lier mine à objet spatial
        $mine->objet_spatial_id = $objet->id;
        $mine->save();

        echo "✅ Mine #{$mine->id} ({$mine->nom}) → ObjetSpatial #{$objet->id}\n";
        echo "   Position : ({$position['secteur_x']}, {$position['secteur_y']}, {$position['secteur_z']}) ";
        echo "/ ({$position['position_x']}, {$position['position_y']}, {$position['position_z']}) cUA\n\n";

        $success++;

    } catch (Exception $e) {
        echo "❌ Erreur Mine #{$mine->id} : {$e->getMessage()}\n\n";
        $errors++;
    }
}

echo "\n=== Résumé ===\n";
echo "Total : {$total}\n";
echo "Succès : {$success}\n";
echo "Erreurs : {$errors}\n";
echo "Déjà migrées : " . ($total - $success - $errors) . "\n\n";

echo "PROCHAINE ÉTAPE : Exécuter migration add_objet_spatial_id_to_mines\n";
echo "  php artisan migrate --path=database/migrations/2025_12_31_140001_add_objet_spatial_id_to_mines.php\n";
