<?php

namespace App\Services;

use App\Models\Vaisseau;
use App\Models\SystemeStellaire;
use App\Models\ObjetSpatial;

class NavigationService
{
    /**
     * Calcule la distance entre deux objets spatiaux ou un objet et un système
     *
     * @param ObjetSpatial $origine
     * @param SystemeStellaire|ObjetSpatial $destination
     * @return float Distance en AL
     */
    public function calculerDistance($origine, $destination): float
    {
        if ($destination instanceof SystemeStellaire) {
            // Distance vers un système stellaire
            $dx = $destination->secteur_x - $origine->secteur_x;
            $dy = $destination->secteur_y - $origine->secteur_y;
            $dz = $destination->secteur_z - $origine->secteur_z;
        } else {
            // Distance vers un autre objet spatial
            $dx = $destination->secteur_x - $origine->secteur_x;
            $dy = $destination->secteur_y - $origine->secteur_y;
            $dz = $destination->secteur_z - $origine->secteur_z;
        }

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Calcule le coût en énergie d'un saut
     *
     * @param float $distance Distance en AL
     * @return int Énergie requise
     */
    public function calculerCoutEnergie(float $distance): int
    {
        // Formule : 50 énergie par AL + 100 de base
        return (int)(100 + ($distance * 50));
    }

    /**
     * Calcule le coût en PA d'un saut
     *
     * @param float $distance Distance en AL
     * @return int PA requis
     */
    public function calculerCoutPA(float $distance): int
    {
        // Formule : 1 PA par 2 AL (minimum 1)
        return max(1, (int)ceil($distance / 2));
    }

    /**
     * Calcule la position d'arrivée après un saut hyperespace
     *
     * @param Vaisseau $vaisseau
     * @param SystemeStellaire $destination
     * @param int $jetNavigation Résultat du jet de compétence (1-100)
     * @return array Position d'arrivée ['secteur_x', 'secteur_y', 'secteur_z', 'position_x', 'position_y', 'position_z']
     */
    public function calculerArrivee(Vaisseau $vaisseau, SystemeStellaire $destination, int $jetNavigation): array
    {
        // Précision basée sur le jet (plus le jet est bon, plus c'est précis)
        $ecartPourcentage = (50 - $jetNavigation) / 100;
        if ($ecartPourcentage < 0) {
            $ecartPourcentage = 0; // Jet supérieur à 50 = arrivée parfaite
        }

        // Taille du système cible (en UA)
        $tailleSysteme = $this->getTailleSysteme($destination);

        // Écart maximum en UA
        $ecartMax = (int)round($tailleSysteme * $ecartPourcentage);

        // Position aléatoire pour chaque dimension
        $position = [];
        foreach (['x', 'y', 'z'] as $dim) {
            if ($ecartMax > 0) {
                // 1d(2*ecartMax) - ecartMax → entre -ecartMax et +ecartMax
                $position[$dim] = rand(1, $ecartMax * 2) - $ecartMax;
            } else {
                $position[$dim] = 0;
            }
        }

        // Normaliser les positions pour qu'elles restent dans le secteur (0-9.99)
        $posX = $destination->position_x + $position['x'];
        $posY = $destination->position_y + $position['y'];
        $posZ = $destination->position_z + $position['z'];

        // Gérer les débordements de secteur
        $secteurX = $destination->secteur_x;
        $secteurY = $destination->secteur_y;
        $secteurZ = $destination->secteur_z;

        while ($posX < 0) {
            $posX += 10;
            $secteurX--;
        }
        while ($posX >= 10) {
            $posX -= 10;
            $secteurX++;
        }

        while ($posY < 0) {
            $posY += 10;
            $secteurY--;
        }
        while ($posY >= 10) {
            $posY -= 10;
            $secteurY++;
        }

        while ($posZ < 0) {
            $posZ += 10;
            $secteurZ--;
        }
        while ($posZ >= 10) {
            $posZ -= 10;
            $secteurZ++;
        }

        return [
            'secteur_x' => $secteurX,
            'secteur_y' => $secteurY,
            'secteur_z' => $secteurZ,
            'position_x' => round($posX, 2),
            'position_y' => round($posY, 2),
            'position_z' => round($posZ, 2),
            'precision' => $ecartPourcentage,
            'ecart_max_ua' => $ecartMax,
        ];
    }

    /**
     * Calcule un déplacement partiel si ressources insuffisantes
     *
     * @param array $positionActuelle
     * @param array $positionCible
     * @param float $pourcentageTrajet Pourcentage du trajet réalisable (0-1)
     * @return array Nouvelle position
     */
    public function calculerDeplacementPartiel(
        array $positionActuelle,
        array $positionCible,
        float $pourcentageTrajet
    ): array {
        $nouvellePosX = $positionActuelle['position_x'] + (($positionCible['position_x'] - $positionActuelle['position_x']) * $pourcentageTrajet);
        $nouvellePosY = $positionActuelle['position_y'] + (($positionCible['position_y'] - $positionActuelle['position_y']) * $pourcentageTrajet);
        $nouvellePosZ = $positionActuelle['position_z'] + (($positionCible['position_z'] - $positionActuelle['position_z']) * $pourcentageTrajet);

        return [
            'secteur_x' => $positionActuelle['secteur_x'],
            'secteur_y' => $positionActuelle['secteur_y'],
            'secteur_z' => $positionActuelle['secteur_z'],
            'position_x' => round($nouvellePosX, 2),
            'position_y' => round($nouvellePosY, 2),
            'position_z' => round($nouvellePosZ, 2),
        ];
    }

    /**
     * Retourne la taille approximative d'un système stellaire (en UA)
     *
     * @param SystemeStellaire $systeme
     * @return float Taille en UA
     */
    protected function getTailleSysteme(SystemeStellaire $systeme): float
    {
        // Taille basée sur le type d'étoile
        // Type O, B : 50-80 UA
        // Type A, F : 40-60 UA
        // Type G (Soleil) : 30-50 UA
        // Type K, M : 20-40 UA

        $taillesParType = [
            'O' => [50, 80],
            'B' => [50, 80],
            'A' => [40, 60],
            'F' => [40, 60],
            'G' => [30, 50],
            'K' => [20, 40],
            'M' => [20, 40],
        ];

        $type = $systeme->type_etoile ?? 'G';
        $range = $taillesParType[$type] ?? [30, 50];

        // Moyenne de la plage
        return ($range[0] + $range[1]) / 2;
    }
}
