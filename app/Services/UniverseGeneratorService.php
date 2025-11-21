<?php

namespace App\Services;

use App\Models\SystemeStellaire;
use App\Models\Planete;
use Illuminate\Support\Str;

class UniverseGeneratorService
{
    /**
     * Génère un système stellaire procéduralement
     */
    public function genererSysteme(
        ?int $secteurX = null,
        ?int $secteurY = null,
        ?int $secteurZ = null,
        ?string $nom = null
    ): SystemeStellaire {
        // Position aléatoire si non spécifiée
        $secteurX = $secteurX ?? rand(-100, 100);
        $secteurY = $secteurY ?? rand(-100, 100);
        $secteurZ = $secteurZ ?? rand(-50, 50);

        $positionX = rand(0, 1000) / 1000; // 0.000 - 1.000
        $positionY = rand(0, 1000) / 1000;
        $positionZ = rand(0, 1000) / 1000;

        // Générer type d'étoile selon distribution gaussienne
        $typeEtoile = SystemeStellaire::genererTypeAleatoire();
        $proprietes = SystemeStellaire::getProprietesEtoile($typeEtoile);

        // Nom aléatoire si non spécifié
        if (!$nom) {
            $nom = $this->genererNomSysteme($secteurX, $secteurY, $secteurZ);
        }

        // Créer le système
        $systeme = SystemeStellaire::create([
            'nom' => $nom,
            'type_etoile' => $typeEtoile,
            'couleur' => $proprietes['couleur'],
            'temperature' => rand($proprietes['temperature_min'], $proprietes['temperature_max']),
            'puissance_solaire' => rand(
                (int)$proprietes['puissance_min'],
                (int)$proprietes['puissance_max']
            ),
            'masse_solaire' => rand(
                (int)($proprietes['masse_min'] * 100),
                (int)($proprietes['masse_max'] * 100)
            ) / 100,
            'rayon_solaire' => rand(
                (int)($proprietes['masse_min'] * 100),
                (int)($proprietes['masse_max'] * 100)
            ) / 100,
            'secteur_x' => $secteurX,
            'secteur_y' => $secteurY,
            'secteur_z' => $secteurZ,
            'position_x' => $positionX,
            'position_y' => $positionY,
            'position_z' => $positionZ,
            'nb_planetes' => 0, // Sera mis à jour après génération planètes
            'explore' => false,
            'habite' => false,
        ]);

        // Générer les planètes
        $nbPlanetes = $this->determinerNombrePlanetes($typeEtoile);
        $this->genererPlanetes($systeme, $nbPlanetes);

        // Mettre à jour le nombre de planètes
        $systeme->nb_planetes = $nbPlanetes;
        $systeme->save();

        return $systeme;
    }

    /**
     * Génère un nom de système aléatoire
     */
    protected function genererNomSysteme(int $x, int $y, int $z): string
    {
        // Catalogues d'astronomie inspirés
        $prefixes = [
            'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta',
            'Proxima', 'Sirius', 'Vega', 'Altair', 'Rigel', 'Betelgeuse', 'Antares',
            'Arcturus', 'Capella', 'Pollux', 'Aldebaran', 'Spica', 'Deneb', 'Regulus',
        ];

        $suffixes = [
            'Centauri', 'Orionis', 'Cygni', 'Draconis', 'Persei', 'Crucis', 'Aurigae',
            'Leonis', 'Geminorum', 'Ursae', 'Scorpii', 'Tauri', 'Aquilae', 'Lyrae',
        ];

        // Utiliser coordonnées comme seed pour cohérence
        $seed = abs($x * 1000 + $y * 100 + $z);
        srand($seed);

        $nom = $prefixes[array_rand($prefixes)] . ' ' . $suffixes[array_rand($suffixes)];

        // Ajouter numéro de secteur pour unicité
        $designation = sprintf('%+04d%+04d%+04d', $x, $y, $z);

        // Remettre seed aléatoire
        srand();

        // Vérifier unicité
        $original = $nom;
        $counter = 1;
        while (SystemeStellaire::where('nom', $nom)->exists()) {
            $nom = $original . ' ' . $counter;
            $counter++;
        }

        return $nom;
    }

    /**
     * Détermine le nombre de planètes selon le type d'étoile
     */
    protected function determinerNombrePlanetes(string $typeEtoile): int
    {
        // Distribution réaliste selon type
        $ranges = match ($typeEtoile) {
            'O', 'B' => [0, 3],      // Étoiles massives, peu de planètes
            'A' => [1, 5],           // Étoiles chaudes
            'F', 'G' => [3, 10],     // Type solaire, favorables
            'K' => [2, 8],           // Naines orange
            'M' => [1, 6],           // Naines rouges
            default => [2, 8],
        };

        return rand($ranges[0], $ranges[1]);
    }

    /**
     * Génère les planètes d'un système
     */
    public function genererPlanetes(SystemeStellaire $systeme, int $nombre): array
    {
        $planetes = [];
        $zoneHabitable = Planete::getZoneHabitable($systeme->type_etoile);

        // Distribuer les planètes selon loi de Titius-Bode simplifiée
        $distances = $this->calculerDistancesPlanetes($nombre, $zoneHabitable);

        for ($i = 1; $i <= $nombre; $i++) {
            $distance = $distances[$i - 1];

            // Générer type selon distance
            $type = Planete::genererType($distance, $systeme->type_etoile);

            // Créer la planète
            $planete = new Planete([
                'systeme_stellaire_id' => $systeme->id,
                'nom' => Planete::genererNom($systeme->nom, $i),
                'type' => $type,
                'distance_etoile' => $distance,
                'periode_orbitale' => (int)round(365.25 * sqrt($distance ** 3)), // Loi de Kepler
            ]);

            // Générer propriétés physiques
            $planete->genererProprietes();

            // Calculer température
            $planete->calculerTemperature($systeme->puissance_solaire);

            // Sauvegarder pour avoir l'ID
            $planete->save();

            // Générer gisements (après save pour avoir l'ID)
            $planete->genererGisements();

            // Charger la relation et calculer habitabilité
            $planete->load('systemeStellaire');
            $planete->calculerHabitabilite();
            $planete->save();

            // Marquer système comme habité si au moins une planète habitable
            if ($planete->habitable && !$systeme->habite) {
                $systeme->habite = true;
            }

            $planetes[] = $planete;
        }

        $systeme->save();

        return $planetes;
    }

    /**
     * Calcule les distances orbitales selon loi de Titius-Bode modifiée
     */
    protected function calculerDistancesPlanetes(int $nombre, array $zoneHabitable): array
    {
        $distances = [];

        // Loi de Titius-Bode: a_n = 0.4 + 0.3 * 2^n
        // Modifiée pour centrer sur la zone habitable
        $centre_zh = ($zoneHabitable['min'] + $zoneHabitable['max']) / 2;

        for ($n = 0; $n < $nombre; $n++) {
            // Formule modifiée pour répartir autour de la zone habitable
            $facteur = 0.4 + 0.3 * pow(2, $n - 2);
            $distance = $centre_zh * $facteur;

            // Ajouter variation aléatoire (±20%)
            $variation = 1 + (rand(-20, 20) / 100);
            $distance *= $variation;

            // Minimum 0.1 UA
            $distance = max(0.1, $distance);

            $distances[] = round($distance, 4);
        }

        // Trier par distance croissante
        sort($distances);

        return $distances;
    }

    /**
     * Génère le Système Solaire (système de départ)
     */
    public function genererSystemeSolaire(): SystemeStellaire
    {
        // Créer le Soleil (type G)
        $soleil = SystemeStellaire::create([
            'nom' => 'Sol',
            'type_etoile' => 'G',
            'couleur' => 'Jaune',
            'temperature' => 5778,
            'puissance_solaire' => 50.0,
            'masse_solaire' => 1.0,
            'rayon_solaire' => 1.0,
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'position_x' => 0.500,
            'position_y' => 0.500,
            'position_z' => 0.500,
            'nb_planetes' => 8,
            'explore' => true,
            'habite' => true,
        ]);

        // Données réelles du Système Solaire
        $planetes_sol = [
            ['nom' => 'Mercure', 'type' => 'terrestre', 'distance' => 0.39, 'rayon' => 0.38, 'masse' => 0.055],
            ['nom' => 'Vénus', 'type' => 'terrestre', 'distance' => 0.72, 'rayon' => 0.95, 'masse' => 0.815],
            ['nom' => 'Terre', 'type' => 'terrestre', 'distance' => 1.00, 'rayon' => 1.00, 'masse' => 1.00],
            ['nom' => 'Mars', 'type' => 'terrestre', 'distance' => 1.52, 'rayon' => 0.53, 'masse' => 0.107],
            ['nom' => 'Jupiter', 'type' => 'gazeuse', 'distance' => 5.20, 'rayon' => 11.21, 'masse' => 317.8],
            ['nom' => 'Saturne', 'type' => 'gazeuse', 'distance' => 9.54, 'rayon' => 9.45, 'masse' => 95.2],
            ['nom' => 'Uranus', 'type' => 'gazeuse', 'distance' => 19.19, 'rayon' => 4.01, 'masse' => 14.5],
            ['nom' => 'Neptune', 'type' => 'gazeuse', 'distance' => 30.07, 'rayon' => 3.88, 'masse' => 17.1],
        ];

        foreach ($planetes_sol as $data) {
            $planete = Planete::create([
                'systeme_stellaire_id' => $soleil->id,
                'nom' => $data['nom'],
                'type' => $data['type'],
                'rayon' => $data['rayon'],
                'masse' => $data['masse'],
                'gravite' => round($data['masse'] / ($data['rayon'] ** 2), 2),
                'distance_etoile' => $data['distance'],
                'periode_orbitale' => (int)round(365.25 * sqrt($data['distance'] ** 3)),
                'a_atmosphere' => in_array($data['nom'], ['Vénus', 'Terre', 'Mars', 'Jupiter', 'Saturne', 'Uranus', 'Neptune']),
                'composition_atmosphere' => match ($data['nom']) {
                    'Vénus' => 'CO2',
                    'Terre' => 'N2, O2',
                    'Mars' => 'CO2',
                    default => $data['type'] === 'gazeuse' ? 'H2, He' : null,
                },
                'habitable' => $data['nom'] === 'Terre',
                'habitee' => $data['nom'] === 'Terre',
                'population' => $data['nom'] === 'Terre' ? 8000000000 : 0,
                'temperature_moyenne' => match ($data['nom']) {
                    'Mercure' => 167,
                    'Vénus' => 464,
                    'Terre' => 15,
                    'Mars' => -65,
                    'Jupiter' => -110,
                    'Saturne' => -140,
                    'Uranus' => -195,
                    'Neptune' => -200,
                    default => 0,
                },
            ]);

            // Générer gisements pour planètes terrestres
            if (in_array($data['nom'], ['Terre', 'Mars', 'Mercure'])) {
                $planete->genererGisements();
                $planete->save();
            }
        }

        return $soleil;
    }

    /**
     * Génère plusieurs systèmes autour du Système Solaire
     */
    public function genererSystemesVoisins(int $nombre = 5, float $rayon = 10.0): array
    {
        $systemes = [];

        for ($i = 0; $i < $nombre; $i++) {
            // Position aléatoire autour du Soleil (secteur 0,0,0)
            $angle1 = rand(0, 360) * pi() / 180;
            $angle2 = rand(-90, 90) * pi() / 180;
            $distance = rand((int)($rayon * 30), (int)($rayon * 100)) / 100;

            $secteurX = (int)round($distance * cos($angle2) * cos($angle1));
            $secteurY = (int)round($distance * cos($angle2) * sin($angle1));
            $secteurZ = (int)round($distance * sin($angle2));

            $systemes[] = $this->genererSysteme($secteurX, $secteurY, $secteurZ);
        }

        return $systemes;
    }
}
