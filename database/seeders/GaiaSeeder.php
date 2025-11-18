<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Services\GaiaCoordinateConverter;

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
        $radius = config('universe.gaia_radius_ly', 100);

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            // Filtrer par distance
            if ((float)$data['distance'] > $radius) continue;

            // Convertir coordonnées
            $coords = GaiaCoordinateConverter::galacticToGame(
                (float)$data['ra'],
                (float)$data['dec'],
                (float)$data['distance']
            );

            // Déterminer type spectral
            $spectralType = GaiaCoordinateConverter::mapSpectralType($data['spectral_type'] ?? '');

            // Créer système
            $systeme = SystemeStellaire::create([
                'nom' => $data['name'] ?: "GAIA-" . substr($data['source_id'], 0, 8),
                'secteur_x' => $coords['secteur_x'],
                'secteur_y' => $coords['secteur_y'],
                'secteur_z' => $coords['secteur_z'],
                'position_x' => $coords['position_x'],
                'position_y' => $coords['position_y'],
                'position_z' => $coords['position_z'],
                'type_etoile' => $spectralType,
                'couleur' => GaiaCoordinateConverter::getColorFromType($spectralType),
                'source_gaia' => true,
                'gaia_source_id' => $data['source_id'],
                'gaia_ra' => $data['ra'],
                'gaia_dec' => $data['dec'],
                'gaia_distance_ly' => $data['distance'],
                'gaia_magnitude' => $data['magnitude'] ?? null,
                'nb_planetes' => rand(0, 12),
            ]);

            // Générer planètes
            $this->genererPlanetes($systeme);

            $count++;
        }

        fclose($file);

        $this->command->info("✅ {$count} systèmes GAIA importés depuis CSV");
    }

    /**
     * Seed des étoiles connues (fallback si pas de CSV)
     */
    protected function seedKnownStars(): void
    {
        $etoilesConnues = [
            [
                'nom' => 'Sol',
                'ra' => 0.0,
                'dec' => 0.0,
                'distance' => 0.0,
                'spectral_type' => 'G2V',
                'magnitude' => -26.74,
                'nb_planetes' => 8, // Système solaire
            ],
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

        foreach ($etoilesConnues as $data) {
            // Convertir coordonnées
            $coords = GaiaCoordinateConverter::galacticToGame(
                $data['ra'],
                $data['dec'],
                $data['distance']
            );

            // Déterminer type spectral
            $spectralType = GaiaCoordinateConverter::mapSpectralType($data['spectral_type']);

            // Créer système
            $systeme = SystemeStellaire::create([
                'nom' => $data['nom'],
                'secteur_x' => $coords['secteur_x'],
                'secteur_y' => $coords['secteur_y'],
                'secteur_z' => $coords['secteur_z'],
                'position_x' => $coords['position_x'],
                'position_y' => $coords['position_y'],
                'position_z' => $coords['position_z'],
                'type_etoile' => $spectralType,
                'couleur' => GaiaCoordinateConverter::getColorFromType($spectralType),
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
        }

        $this->command->info('✅ ' . count($etoilesConnues) . ' étoiles connues importées');
    }

    /**
     * Générer planètes pour un système
     */
    protected function genererPlanetes(SystemeStellaire $systeme): void
    {
        if ($systeme->nb_planetes === 0) return;

        for ($i = 1; $i <= $systeme->nb_planetes; $i++) {
            $types = ['Tellurique', 'Gazeuse', 'Glacée', 'Naine'];
            $type = $types[array_rand($types)];

            Planete::create([
                'systeme_stellaire_id' => $systeme->id,
                'nom' => "{$systeme->nom} {$i}",
                'distance_etoile' => $i * 0.5 + rand(0, 10) / 10,
                'rayon_km' => match($type) {
                    'Tellurique' => rand(3000, 15000),
                    'Gazeuse' => rand(40000, 140000),
                    'Glacée' => rand(2000, 8000),
                    'Naine' => rand(1000, 3000),
                },
                'masse' => rand(1, 1000),
                'type_planete' => $type,
                'atmosphere' => rand(0, 1) === 1,
                'population' => 0,
            ]);
        }
    }
}

