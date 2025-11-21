<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddGaiaStarCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gaia:add
                            {name : Nom de l\'étoile}
                            {x : Coordonnée X en années-lumière (vers la Terre)}
                            {y : Coordonnée Y en années-lumière (vers la gauche)}
                            {z : Coordonnée Z en années-lumière (vers le pôle nord galactique)}
                            {--spectral-type=G2V : Type spectral (ex: G2V, M5V, K0III)}
                            {--magnitude= : Magnitude apparente (calculée automatiquement si omis)}
                            {--csv= : Fichier CSV (défaut: database/data/gaia_nearby_stars.csv)}';

    /**
     * The console command description.
     */
    protected $description = 'Ajoute une étoile au CSV GAIA à partir de coordonnées cartésiennes (x, y, z en AL)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $x = (float) $this->argument('x');
        $y = (float) $this->argument('y');
        $z = (float) $this->argument('z');
        $spectralType = $this->option('spectral-type');
        $csvPath = $this->option('csv') ?: database_path('data/gaia_nearby_stars.csv');

        $this->info("🌟 Ajout de l'étoile: {$name}");
        $this->info("📍 Coordonnées: X={$x}, Y={$y}, Z={$z} AL");

        // Créer le répertoire si nécessaire
        $dir = dirname($csvPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Charger étoiles existantes
        $existingStars = [];
        $fileExists = file_exists($csvPath);

        if ($fileExists) {
            $existingStars = $this->loadExistingStars($csvPath);

            // Vérifier doublon par nom
            foreach ($existingStars as $star) {
                if (strtolower($star['name']) === strtolower($name)) {
                    $this->error("❌ Une étoile nommée '{$name}' existe déjà !");
                    return 1;
                }
            }

            // Vérifier doublon par proximité (< 0.01 AL)
            $existingCoords = $this->extractCoordinates($existingStars);
            if ($this->isNearExistingPoint($x, $y, $z, $existingCoords, 0.01)) {
                $this->error("❌ Une étoile existe déjà à moins de 0.01 AL de cette position !");
                return 1;
            }
        }

        // Calculer distance
        $distance = sqrt($x * $x + $y * $y + $z * $z);

        // Convertir en RA/Dec
        list($ra, $dec) = $this->cartesianToSpherical($x, $y, $z);

        // Magnitude
        $magnitude = $this->option('magnitude');
        if (!$magnitude) {
            $typeClass = strtoupper(substr($spectralType, 0, 1));
            $absoluteMag = $this->getAbsoluteMagnitude($typeClass);
            $magnitude = $absoluteMag + 5 * (log10($distance) - 1);
        }

        // Source ID unique
        $sourceId = 'CUSTOM-' . strtoupper(substr(md5($name . $x . $y . $z), 0, 12));

        // Créer nouvelle étoile
        $newStar = [
            'source_id' => $sourceId,
            'name' => $name,
            'ra' => number_format($ra, 8),
            'dec' => number_format($dec, 8),
            'distance' => number_format($distance, 4),
            'spectral_type' => $spectralType,
            'magnitude' => number_format($magnitude, 2),
        ];

        // Ajouter au tableau
        $existingStars[] = $newStar;

        // Écrire le CSV
        $this->writeCSV($csvPath, $existingStars);

        $this->info("✅ Étoile ajoutée avec succès !");
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Nom', $name],
                ['Source ID', $sourceId],
                ['Distance', number_format($distance, 4) . ' AL'],
                ['RA', number_format($ra, 4) . '°'],
                ['Dec', number_format($dec, 4) . '°'],
                ['Type Spectral', $spectralType],
                ['Magnitude', number_format($magnitude, 2)],
            ]
        );

        $this->info("📁 Fichier: {$csvPath}");
        $this->info("📊 Total: " . count($existingStars) . " étoiles");

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
     * Convertir coordonnées cartésiennes (x,y,z) en sphériques (RA, Dec)
     */
    protected function cartesianToSpherical(float $x, float $y, float $z): array
    {
        $distance = sqrt($x * $x + $y * $y + $z * $z);

        if ($distance < 0.0001) {
            return [0.0, 0.0];
        }

        // Déclinaison (latitude céleste) en degrés
        $dec = rad2deg(asin($z / $distance));

        // Ascension droite (longitude céleste) en degrés
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

            // Convertir RA/Dec → Cartésien
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
     * Écrire le CSV
     */
    protected function writeCSV(string $path, array $stars): void
    {
        $file = fopen($path, 'w');

        // Header
        fputcsv($file, ['source_id', 'name', 'ra', 'dec', 'distance', 'spectral_type', 'magnitude']);

        // Données
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
