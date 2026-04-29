<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Station;
use App\Services\GaiaCoordinateConverter;
use App\Services\StarNameMatcher;
use App\Helpers\CoordinatesHelper;

class GaiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌟 Import des étoiles GAIA...');

        $csvPath = database_path('data/gaia_nearby_stars.csv');

        if (file_exists($csvPath)) {
            $this->importFromCSV($csvPath);
        } else {
            $this->command->warn('⚠️  Fichier GAIA CSV non trouvé. Import des étoiles connues...');
            $this->seedKnownStars();
        }

        $this->command->info('✅ Import GAIA terminé');
    }

    /**
     * Importer depuis fichier CSV GAIA
     */
    protected function importFromCSV(string $csvPath): void
    {
        // Créer le Système Solaire en premier
        $this->seedSolarSystem();

        $radius = config('universe.gaia_radius_ly', 100);

        // Initialiser le service de matching des noms d'étoiles
        $starMatcher = app(StarNameMatcher::class);

        // Compter le nombre total de lignes pour la barre de progression
        $this->command->info('📊 Analyse du fichier CSV...');
        $totalLines = 0;
        $file = fopen($csvPath, 'r');
        fgetcsv($file); // Skip header
        while (fgets($file) !== false) {
            $totalLines++;
        }
        fclose($file);

        $this->command->info("📦 {$totalLines} étoiles trouvées dans le CSV");

        // Réouvrir le fichier pour l'import
        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file);

        // Créer la barre de progression
        $bar = $this->command->getOutput()->createProgressBar($totalLines);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Démarrage import...');
        $bar->start();

        $count = 0;
        $filtered = 0;
        $batch = [];
        $batchSize = 100; // Insérer par lots de 100
        $now = now();

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            // Filtrer par distance
            if ((float)$data['distance'] > $radius) {
                $filtered++;
                $bar->advance();
                continue;
            }

            // Convertir coordonnées
            $coords = GaiaCoordinateConverter::galacticToGame(
                (float)$data['ra'],
                (float)$data['dec'],
                (float)$data['distance']
            );

            // Déterminer type spectral
            $spectralType = GaiaCoordinateConverter::mapSpectralType($data['spectral_type'] ?? '');

            // Calculer puissance et détectabilité
            [$puissance, $detectabilite] = $this->calculateStarDetectability($spectralType);

            // Chercher nom commun via coordonnées (RA, Dec, Distance)
            $match = $starMatcher->findCommonName(
                (float)$data['ra'],
                (float)$data['dec'],
                (float)$data['distance']
            );

            // Ajouter au batch (SANS générer les planètes maintenant)
            $batch[] = [
                'nom' => $data['name'] ?: "GAIA-" . substr($data['source_id'], 0, 8),
                'nom_commun' => $match['nom_commun'] ?? null,
                'noms_alternatifs' => $match ? json_encode($match['aliases']) : null,
                'secteur_x' => $coords['secteur_x'],
                'secteur_y' => $coords['secteur_y'],
                'secteur_z' => $coords['secteur_z'],
                'position_x' => $coords['position_x'],
                'position_y' => $coords['position_y'],
                'position_z' => $coords['position_z'],
                'type_etoile' => $spectralType,
                'couleur' => GaiaCoordinateConverter::getColorFromType($spectralType),
                'puissance' => $puissance,
                'detectabilite_base' => $detectabilite,
                'poi_connu' => false,
                'source_gaia' => true,
                'gaia_source_id' => $data['source_id'],
                'gaia_ra' => $data['ra'],
                'gaia_dec' => $data['dec'],
                'gaia_distance_ly' => $data['distance'],
                'gaia_magnitude' => $data['magnitude'] ?? null,
                'nb_planetes' => rand(0, 12),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $count++;

            // Insérer le batch quand il atteint la taille limite
            if (count($batch) >= $batchSize) {
                DB::table('systemes_stellaires')->insert($batch);
                $batch = [];
                $bar->setMessage("Importé: {$count} systèmes");
            }

            $bar->advance();
        }

        // Insérer le dernier batch s'il reste des éléments
        if (!empty($batch)) {
            DB::table('systemes_stellaires')->insert($batch);
        }

        $bar->setMessage("Import terminé!");
        $bar->finish();
        $this->command->newLine(2);

        fclose($file);

        $this->command->info("✅ {$count} systèmes GAIA importés depuis CSV (planètes générées à la demande)");
        if ($filtered > 0) {
            $this->command->info("ℹ️  {$filtered} étoiles filtrées (distance > {$radius} AL)");
        }
    }

    /**
     * Seed des étoiles connues (fallback si pas de CSV)
     */
    protected function seedKnownStars(): void
    {
        // Créer le Système Solaire complet en premier
        $this->seedSolarSystem();

        $this->command->info('⭐ Import des étoiles proches connues...');

        // Initialiser le service de matching des noms d'étoiles
        $starMatcher = app(StarNameMatcher::class);

        $etoilesConnues = [
            [
                'nom' => 'Alpha Centauri A',
                'ra' => 219.90205833,
                'dec' => -60.83399269,
                'distance' => 4.37,
                'spectral_type' => 'G2V',
                'magnitude' => -0.01,
                'nb_planetes' => rand(0, 5),
            ],
            [
                'nom' => 'Alpha Centauri B',
                'ra' => 219.90205833,
                'dec' => -60.83399269,
                'distance' => 4.37,
                'spectral_type' => 'K1V',
                'magnitude' => 1.33,
                'nb_planetes' => rand(0, 3),
            ],
            [
                'nom' => 'Proxima Centauri',
                'ra' => 217.42897500,
                'dec' => -62.67978611,
                'distance' => 4.24,
                'spectral_type' => 'M5.5Ve',
                'magnitude' => 11.13,
                'nb_planetes' => 2, // Proxima b et c
            ],
            [
                'nom' => 'Sirius',
                'ra' => 101.28715533,
                'dec' => -16.71611586,
                'distance' => 8.6,
                'spectral_type' => 'A1V',
                'magnitude' => -1.46,
                'nb_planetes' => rand(0, 4),
            ],
            [
                'nom' => 'Epsilon Eridani',
                'ra' => 53.23267083,
                'dec' => -9.45832778,
                'distance' => 10.5,
                'spectral_type' => 'K2V',
                'magnitude' => 3.73,
                'nb_planetes' => rand(1, 6),
            ],
            [
                'nom' => 'Tau Ceti',
                'ra' => 26.01709944,
                'dec' => -15.93745722,
                'distance' => 11.9,
                'spectral_type' => 'G8V',
                'magnitude' => 3.50,
                'nb_planetes' => rand(2, 8),
            ],
            [
                'nom' => 'Wolf 359',
                'ra' => 164.12007083,
                'dec' => 7.00494306,
                'distance' => 7.9,
                'spectral_type' => 'M6V',
                'magnitude' => 13.54,
                'nb_planetes' => rand(0, 2),
            ],
            [
                'nom' => 'Lalande 21185',
                'ra' => 165.93897917,
                'dec' => 35.95637639,
                'distance' => 8.3,
                'spectral_type' => 'M2V',
                'magnitude' => 7.47,
                'nb_planetes' => rand(0, 3),
            ],
            [
                'nom' => 'Luyten 726-8 A',
                'ra' => 25.88805556,
                'dec' => -17.95027778,
                'distance' => 8.7,
                'spectral_type' => 'M5.5V',
                'magnitude' => 12.54,
                'nb_planetes' => rand(0, 1),
            ],
        ];

        // Créer une barre de progression
        $bar = $this->command->getOutput()->createProgressBar(count($etoilesConnues));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->start();

        foreach ($etoilesConnues as $data) {
            $bar->setMessage("Import de {$data['nom']}...");

            // Convertir coordonnées
            $coords = GaiaCoordinateConverter::galacticToGame(
                $data['ra'],
                $data['dec'],
                $data['distance']
            );

            // Déterminer type spectral
            $spectralType = GaiaCoordinateConverter::mapSpectralType($data['spectral_type']);

            // Calculer puissance et détectabilité
            [$puissance, $detectabilite] = $this->calculateStarDetectability($spectralType);

            // Chercher nom commun via coordonnées
            $match = $starMatcher->findCommonName(
                $data['ra'],
                $data['dec'],
                $data['distance']
            );

            // Créer système
            $systeme = SystemeStellaire::create([
                'nom' => $data['nom'],
                'nom_commun' => $match['nom_commun'] ?? $data['nom'], // Utiliser nom existant si match
                'noms_alternatifs' => $match ? json_encode($match['aliases']) : null,
                'secteur_x' => $coords['secteur_x'],
                'secteur_y' => $coords['secteur_y'],
                'secteur_z' => $coords['secteur_z'],
                'position_x' => $coords['position_x'],
                'position_y' => $coords['position_y'],
                'position_z' => $coords['position_z'],
                'type_etoile' => $spectralType,
                'couleur' => GaiaCoordinateConverter::getColorFromType($spectralType),
                'puissance' => $puissance,
                'detectabilite_base' => $detectabilite,
                'poi_connu' => false,
                'source_gaia' => true,
                'gaia_source_id' => 'MANUAL_' . strtoupper(str_replace(' ', '_', $data['nom'])),
                'gaia_ra' => $data['ra'],
                'gaia_dec' => $data['dec'],
                'gaia_distance_ly' => $data['distance'],
                'gaia_magnitude' => $data['magnitude'],
                'nb_planetes' => $data['nb_planetes'],
            ]);

            // Générer planètes si applicable
            if ($systeme->nb_planetes > 0) {
                $this->genererPlanetes($systeme);
            }

            $bar->advance();
        }

        $bar->setMessage("Import terminé!");
        $bar->finish();
        $this->command->newLine(2);

        $this->command->info('✅ ' . count($etoilesConnues) . ' étoiles connues importées');
    }

    /**
     * Créer le Système Solaire complet avec planètes et stations
     */
    protected function seedSolarSystem(): void
    {
        $this->command->info('🌞 Création du Système Solaire...');

        // Créer Sol avec paramètres spéciaux
        $sol = SystemeStellaire::create([
            'nom' => 'Sol',
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'position_x' => 0.0,
            'position_y' => 0.0,
            'position_z' => 0.0,
            'type_etoile' => 'G2V',
            'couleur' => GaiaCoordinateConverter::getColorFromType('G2V'),
            'source_gaia' => true,
            'gaia_source_id' => 'SOL',
            'gaia_ra' => 0.0,
            'gaia_dec' => 0.0,
            'gaia_distance_ly' => 0.0,
            'gaia_magnitude' => -26.74,
            'nb_planetes' => 5, // Terre, Lune, Mars, Jupiter, Neptune pour les tests
            'puissance' => 50,
            'detectabilite_base' => 50.0,
            'poi_connu' => true,
        ]);

        // Créer la Terre (planète inaccessible - surpopulation)
        $rayon_terre = 1.0;
        $terre = Planete::create([
            'systeme_stellaire_id' => $sol->id,
            'nom' => 'Terre',
            'distance_etoile' => CoordinatesHelper::uaToCua(1.0), // 100 cUA = 1.0 UA
            'rayon' => $rayon_terre,
            'masse' => 1.0, // 1 masse terrestre
            'type' => 'terrestre',
            'a_atmosphere' => true,
            'population' => 8000000000,
            'accessible' => false,
            'raison_inaccessible' => 'Surpopulation - Vaisseaux trop gros pour atterrir',
            'detectabilite_base' => $this->calculatePlanetDetectability($rayon_terre),
            'poi_connu' => true,
        ]);

        // Station Terra-Maxi-Hub (ACCESSIBLE)
        Station::create([
            'nom' => 'Terra-Maxi-Hub',
            'type' => 'hub_commercial',
            'planete_id' => $terre->id,
            'systeme_stellaire_id' => $sol->id,
            'orbite_rayon_ua' => 0.001, // Orbite basse
            'orbite_angle' => 0.0,
            'description' => 'Le plus grand hub commercial du système solaire',
            'capacite_amarrage' => 1000,
            'commerciale' => true,
            'industrielle' => true,
            'militaire' => false,
            'reparations' => true,
            'ravitaillement' => true,
            'medical' => true,
            'accessible' => true,
        ]);

        // Créer la Lune (planète inaccessible - surpopulation)
        $rayon_lune = 0.27;
        $lune = Planete::create([
            'systeme_stellaire_id' => $sol->id,
            'nom' => 'Lune',
            'distance_etoile' => CoordinatesHelper::uaToCua(1.00257), // 100.257 cUA (légèrement plus loin que la Terre)
            'rayon' => $rayon_lune,
            'masse' => 0.0123, // 1.23% de la masse terrestre
            'type' => 'naine',
            'a_atmosphere' => false,
            'population' => 0,
            'accessible' => false,
            'raison_inaccessible' => 'Transport - Vaisseaux trop gros pour atterrir',
            'detectabilite_base' => $this->calculatePlanetDetectability($rayon_lune),
            'poi_connu' => true,
        ]);

        // Station Lunastar-station (ACCESSIBLE - station de départ)
        Station::create([
            'nom' => 'Lunastar-station',
            'type' => 'spatiogare',
            'planete_id' => $lune->id,
            'systeme_stellaire_id' => $sol->id,
            'orbite_rayon_ua' => 0.0005,
            'orbite_angle' => 45.0,
            'description' => 'Station de départ pour tous les nouveaux pilotes',
            'capacite_amarrage' => 200,
            'commerciale' => true,
            'industrielle' => false,
            'militaire' => false,
            'reparations' => true,
            'ravitaillement' => true,
            'medical' => true,
            'accessible' => true,
        ]);

        // Créer Mars (planète inaccessible - colonisation)
        $rayon_mars = 0.53;
        $mars = Planete::create([
            'systeme_stellaire_id' => $sol->id,
            'nom' => 'Mars',
            'distance_etoile' => CoordinatesHelper::uaToCua(1.52), // 152 cUA = 1.52 UA
            'rayon' => $rayon_mars,
            'masse' => 0.107, // 10.7% de la masse terrestre
            'type' => 'terrestre',
            'a_atmosphere' => true,
            'population' => 0,
            'accessible' => false,
            'raison_inaccessible' => 'Colonisation - Vaisseaux trop gros pour atterrir',
            'detectabilite_base' => $this->calculatePlanetDetectability($rayon_mars),
            'poi_connu' => true,
        ]);

        // Station Mars-spatiogare (ACCESSIBLE)
        Station::create([
            'nom' => 'Mars-spatiogare',
            'type' => 'spatiogare',
            'planete_id' => $mars->id,
            'systeme_stellaire_id' => $sol->id,
            'orbite_rayon_ua' => 0.0008,
            'orbite_angle' => 90.0,
            'description' => 'Spatiogare de Mars',
            'capacite_amarrage' => 150,
            'commerciale' => true,
            'industrielle' => true,
            'militaire' => false,
            'reparations' => true,
            'ravitaillement' => true,
            'medical' => true,
            'accessible' => true,
        ]);

        // Créer Jupiter (planète gazeuse inaccessible)
        $rayon_jupiter = 11.2;
        $jupiter = Planete::create([
            'systeme_stellaire_id' => $sol->id,
            'nom' => 'Jupiter',
            'distance_etoile' => CoordinatesHelper::uaToCua(5.2), // 520 cUA = 5.2 UA
            'rayon' => $rayon_jupiter,
            'masse' => 317.8, // 317.8 masses terrestres
            'type' => 'gazeuse',
            'a_atmosphere' => true,
            'population' => 0,
            'accessible' => false,
            'raison_inaccessible' => 'Planète gazeuse - Impossible d\'atterrir',
            'detectabilite_base' => $this->calculatePlanetDetectability($rayon_jupiter),
            'poi_connu' => true,
        ]);

        // Station Jupiter-spatiogare (accessible)
        Station::create([
            'nom' => 'Jupiter-spatiogare',
            'type' => 'spatiogare',
            'planete_id' => $jupiter->id,
            'systeme_stellaire_id' => $sol->id,
            'orbite_rayon_ua' => 0.002,
            'orbite_angle' => 180.0,
            'description' => 'Spatiogare de Jupiter',
            'capacite_amarrage' => 100,
            'commerciale' => true,
            'industrielle' => true,
            'militaire' => false,
            'reparations' => true,
            'ravitaillement' => true,
            'medical' => false,
            'accessible' => true,
        ]);

        // Créer Neptune (planète gazeuse inaccessible)
        $rayon_neptune = 3.88;
        $neptune = Planete::create([
            'systeme_stellaire_id' => $sol->id,
            'nom' => 'Neptune',
            'distance_etoile' => CoordinatesHelper::uaToCua(30.1), // 3010 cUA = 30.1 UA
            'rayon' => $rayon_neptune,
            'masse' => 17.15, // 17.15 masses terrestres
            'type' => 'gazeuse',
            'a_atmosphere' => true,
            'population' => 0,
            'accessible' => false,
            'raison_inaccessible' => 'Planète gazeuse - Impossible d\'atterrir',
            'detectabilite_base' => $this->calculatePlanetDetectability($rayon_neptune),
            'poi_connu' => true,
        ]);

        // Station Neptune-spatiogare (accessible)
        Station::create([
            'nom' => 'Neptune-spatiogare',
            'type' => 'spatiogare',
            'planete_id' => $neptune->id,
            'systeme_stellaire_id' => $sol->id,
            'orbite_rayon_ua' => 0.003,
            'orbite_angle' => 270.0,
            'description' => 'Spatiogare de Neptune',
            'capacite_amarrage' => 80,
            'commerciale' => true,
            'industrielle' => false,
            'militaire' => true,
            'reparations' => true,
            'ravitaillement' => true,
            'medical' => false,
            'accessible' => true,
        ]);

        $this->command->info('✅ Système Solaire créé avec 5 planètes et 5 stations');
    }

    /**
     * Générer planètes pour un système
     */
    protected function genererPlanetes(SystemeStellaire $systeme): void
    {
        if ($systeme->nb_planetes === 0) return;

        for ($i = 1; $i <= $systeme->nb_planetes; $i++) {
            // Types correspondant à l'enum de la migration
            $types = ['terrestre', 'gazeuse', 'glacee', 'naine'];
            $type = $types[array_rand($types)];

            // Rayon en rayons terrestres (Terre = 1.0)
            $rayon = match($type) {
                'terrestre' => rand(5, 25) / 10, // 0.5 à 2.5 rayons terrestres
                'gazeuse' => rand(40, 140) / 10, // 4 à 14 rayons terrestres
                'glacee' => rand(3, 13) / 10, // 0.3 à 1.3 rayons terrestres
                'naine' => rand(1, 5) / 10, // 0.1 à 0.5 rayons terrestres
            };

            $planete = Planete::create([
                'systeme_stellaire_id' => $systeme->id,
                'nom' => "{$systeme->nom} {$i}",
                'distance_etoile' => CoordinatesHelper::uaToCua($i * 0.5 + rand(0, 10) / 10),
                'rayon' => $rayon,
                'masse' => match($type) {
                    'terrestre' => rand(5, 30) / 10, // 0.5 à 3 masses terrestres
                    'gazeuse' => rand(50, 3000) / 10, // 5 à 300 masses terrestres
                    'glacee' => rand(1, 15) / 10, // 0.1 à 1.5 masses terrestres
                    'naine' => rand(1, 3) / 100, // 0.01 à 0.03 masses terrestres
                },
                'type' => $type,
                'a_atmosphere' => in_array($type, ['terrestre', 'gazeuse']) ? rand(0, 1) === 1 : false,
                'population' => 0,
                'detectabilite_base' => $this->calculatePlanetDetectability($rayon),
                'poi_connu' => false, // Planètes procédurales non connues par défaut
            ]);

            // Générer gisements pour cette planète
            $planete->genererGisements();
        }
    }

    /**
     * Calculer puissance et détectabilité pour une étoile
     * Formule : D_base = (200 - Puissance) / 3
     *
     * @param string $spectralType Type spectral complet (ex: 'G2V', 'M5.5Ve')
     * @return array [puissance, detectabilite]
     */
    protected function calculateStarDetectability(string $spectralType): array
    {
        // Mapping type spectral → plage de puissance selon GDD_Univers_Generation.md
        $puissances = [
            'O' => [150, 200],
            'B' => [100, 140],
            'A' => [80, 100],
            'F' => [60, 80],
            'G' => [40, 60],
            'K' => [30, 40],
            'M' => [20, 30],
        ];

        // Extraire première lettre du type spectral
        $typeClass = strtoupper(substr($spectralType, 0, 1));

        if (!isset($puissances[$typeClass])) {
            $typeClass = 'G'; // Défaut : type solaire
        }

        // Puissance aléatoire dans la plage du type
        $puissance = rand($puissances[$typeClass][0], $puissances[$typeClass][1]);

        // Formule : detectabilite_base = (200 - puissance) / 3
        $detectabilite = (200 - $puissance) / 3;

        return [$puissance, round($detectabilite, 2)];
    }

    /**
     * Calculer détectabilité pour une planète/objet
     * Formule : D_base = 150 - (Taille × 10)
     *
     * @param float $rayon Rayon en rayons terrestres
     * @return float Détectabilité calculée
     */
    protected function calculatePlanetDetectability(float $rayon): float
    {
        // Formule : D_base = 150 - (Taille × 10)
        $detectabilite = 150 - ($rayon * 10);

        // Limiter entre min et max raisonnable
        // Petits objets (taille <1) : D_base entre 140-150 (difficile à détecter)
        // Gros objets (Jupiter taille ~11) : D_base ~38 (très facile à détecter)
        $detectabilite = max(1, min(150, $detectabilite));

        return round($detectabilite, 2);
    }
}

