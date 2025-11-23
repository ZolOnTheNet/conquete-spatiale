<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddGaiaRadiusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gaia:add-radius
                            {x : Centre X en années-lumière}
                            {y : Centre Y en années-lumière}
                            {z : Centre Z en années-lumière}
                            {radius : Rayon en années-lumière}
                            {--count=50 : Nombre d\'étoiles à générer dans ce rayon}
                            {--csv= : Fichier CSV (défaut: database/data/gaia_nearby_stars.csv)}';

    /**
     * The console command description.
     */
    protected $description = 'Génère des étoiles dans une sphère définie par (x,y,z) + rayon en AL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $centerX = (float) $this->argument('x');
        $centerY = (float) $this->argument('y');
        $centerZ = (float) $this->argument('z');
        $radius = (float) $this->argument('radius');
        $count = (int) $this->option('count');
        $csvPath = $this->option('csv') ?: database_path('data/gaia_nearby_stars.csv');

        $this->info("🌟 Génération de {$count} étoiles");
        $this->info("📍 Centre: X={$centerX}, Y={$centerY}, Z={$centerZ} AL");
        $this->info("📏 Rayon: {$radius} AL");

        // Créer le répertoire si nécessaire
        $dir = dirname($csvPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Charger étoiles existantes
        $existingStars = [];
        if (file_exists($csvPath)) {
            $existingStars = $this->loadExistingStars($csvPath);
            $this->info('Chargé ' . count($existingStars) . ' étoiles existantes.');
        }

        // Extraire coordonnées existantes
        $existingCoords = $this->extractCoordinates($existingStars);
        $existingNames = array_column($existingStars, 'name');

        // Distribution des types spectraux
        $spectralTypes = [
            'O' => 0.00003,
            'B' => 0.13,
            'A' => 0.6,
            'F' => 3.0,
            'G' => 7.6,
            'K' => 12.1,
            'M' => 76.45,
        ];

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $newStars = [];
        $generated = 0;
        $attempts = 0;
        $maxAttempts = $count * 10;

        while ($generated < $count && $attempts < $maxAttempts) {
            $attempts++;

            // Générer point aléatoire dans la sphère centrée sur (centerX, centerY, centerZ)
            list($offsetX, $offsetY, $offsetZ) = $this->randomPointInSphere($radius);
            $x = $centerX + $offsetX;
            $y = $centerY + $offsetY;
            $z = $centerZ + $offsetZ;

            // Vérifier doublon par proximité (< 0.01 AL)
            if ($this->isNearExistingPoint($x, $y, $z, $existingCoords, 0.01)) {
                continue;
            }

            // Calculer distance depuis Terre (0,0,0)
            $distance = sqrt($x * $x + $y * $y + $z * $z);

            // Convertir en RA/Dec
            list($ra, $dec) = $this->cartesianToSpherical($x, $y, $z);

            // Type spectral
            $spectralType = $this->weightedRandomSpectralType($spectralTypes);
            $subclass = rand(0, 9);
            $luminosity = $this->randomLuminosityClass();
            $fullSpectralType = $spectralType . $subclass . $luminosity;

            // Nom unique
            $name = $this->generateUniqueName($existingNames, $generated);
            $existingNames[] = $name;

            // Source ID
            $sourceId = 'RADIUS-' . str_pad(count($existingStars) + $generated, 12, '0', STR_PAD_LEFT);

            // Magnitude
            $absoluteMag = $this->getAbsoluteMagnitude($spectralType);
            $magnitude = $absoluteMag + 5 * (log10(max(0.1, $distance)) - 1);

            $newStars[] = [
                'source_id' => $sourceId,
                'name' => $name,
                'ra' => number_format($ra, 8),
                'dec' => number_format($dec, 8),
                'distance' => number_format($distance, 4),
                'spectral_type' => $fullSpectralType,
                'magnitude' => number_format($magnitude, 2),
            ];

            $existingCoords[] = [$x, $y, $z];
            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($generated < $count) {
            $this->warn("⚠️  Seulement {$generated}/{$count} étoiles générées (limite de tentatives atteinte)");
        }

        // Fusionner et écrire
        $allStars = array_merge($existingStars, $newStars);
        $this->writeCSV($csvPath, $allStars);

        $this->info("✅ {$generated} nouvelles étoiles ajoutées !");
        $this->info("📁 Fichier: {$csvPath}");
        $this->info("📊 Total: " . count($allStars) . " étoiles");

        return 0;
    }

    /**
     * Charger les étoiles existantes depuis le CSV
     */
    protected function loadExistingStars(string $path): array
    {
        $stars = [];
        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $stars[] = array_combine($header, $row);
        }

        fclose($file);
        return $stars;
    }

    /**
     * Générer un point aléatoire dans une sphère (distribution uniforme)
     */
    protected function randomPointInSphere(float $radius): array
    {
        do {
            $x = (mt_rand() / mt_getrandmax()) * 2 - 1;
            $y = (mt_rand() / mt_getrandmax()) * 2 - 1;
            $z = (mt_rand() / mt_getrandmax()) * 2 - 1;
            $distSq = $x * $x + $y * $y + $z * $z;
        } while ($distSq > 1);

        $scale = $radius * pow(mt_rand() / mt_getrandmax(), 1.0 / 3.0);
        $norm = sqrt($distSq);

        return [
            $x / $norm * $scale,
            $y / $norm * $scale,
            $z / $norm * $scale,
        ];
    }

    /**
     * Convertir coordonnées cartésiennes en sphériques (RA, Dec)
     */
    protected function cartesianToSpherical(float $x, float $y, float $z): array
    {
        $distance = sqrt($x * $x + $y * $y + $z * $z);

        if ($distance < 0.0001) {
            return [0.0, 0.0];
        }

        $dec = rad2deg(asin($z / $distance));
        $ra = rad2deg(atan2($y, $x));
        if ($ra < 0) {
            $ra += 360;
        }

        return [$ra, $dec];
    }

    /**
     * Vérifier si un point est proche d'un point existant
     */
    protected function isNearExistingPoint(float $x, float $y, float $z, array $existingCoords, float $threshold): bool
    {
        foreach ($existingCoords as $coord) {
            $dx = $x - $coord[0];
            $dy = $y - $coord[1];
            $dz = $z - $coord[2];
            $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);

            if ($dist < $threshold) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extraire coordonnées cartésiennes des étoiles existantes
     */
    protected function extractCoordinates(array $stars): array
    {
        $coords = [];

        foreach ($stars as $star) {
            $ra = (float) $star['ra'];
            $dec = (float) $star['dec'];
            $distance = (float) $star['distance'];

            $raRad = deg2rad($ra);
            $decRad = deg2rad($dec);

            $x = $distance * cos($decRad) * cos($raRad);
            $y = $distance * cos($decRad) * sin($raRad);
            $z = $distance * sin($decRad);

            $coords[] = [$x, $y, $z];
        }

        return $coords;
    }

    /**
     * Choisir un type spectral selon distribution pondérée
     */
    protected function weightedRandomSpectralType(array $weights): string
    {
        $rand = mt_rand() / mt_getrandmax() * 100;
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return 'M';
    }

    /**
     * Classe de luminosité aléatoire
     */
    protected function randomLuminosityClass(): string
    {
        $classes = ['V', 'V', 'V', 'V', 'IV', 'III'];
        return $classes[array_rand($classes)];
    }

    /**
     * Magnitude absolue selon type spectral
     */
    protected function getAbsoluteMagnitude(string $type): float
    {
        $magnitudes = [
            'O' => -5.0,
            'B' => -1.0,
            'A' => 1.5,
            'F' => 3.0,
            'G' => 5.0,
            'K' => 7.0,
            'M' => 10.0,
        ];

        return $magnitudes[$type] ?? 5.0;
    }

    /**
     * Générer un nom unique pour l'étoile
     */
    protected function generateUniqueName(array $existingNames, int $index): string
    {
        $prefixes = [
            'Proxima', 'Kepler', 'Trappist', 'Gliese', 'Ross', 'Wolf',
            'Luyten', 'Lacaille', 'Groombridge', 'Kruger', 'Barnard',
        ];

        $attempts = 0;
        do {
            if ($attempts === 0) {
                $prefix = $prefixes[array_rand($prefixes)];
                $number = rand(100, 9999);
                $letter = chr(65 + rand(0, 25));
                $name = "{$prefix} {$number}{$letter}";
            } else {
                $name = "STAR-" . str_pad($index + $attempts, 6, '0', STR_PAD_LEFT);
            }
            $attempts++;
        } while (in_array($name, $existingNames) && $attempts < 100);

        return $name;
    }

    /**
     * Écrire le CSV
     */
    protected function writeCSV(string $path, array $stars): void
    {
        $file = fopen($path, 'w');
        fputcsv($file, ['source_id', 'name', 'ra', 'dec', 'distance', 'spectral_type', 'magnitude']);

        foreach ($stars as $star) {
            fputcsv($file, [
                $star['source_id'],
                $star['name'],
                $star['ra'],
                $star['dec'],
                $star['distance'],
                $star['spectral_type'],
                $star['magnitude'],
            ]);
        }

        fclose($file);
    }
}
