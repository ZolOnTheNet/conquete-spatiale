<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateGaiaCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gaia:generate
                            {--count=100 : Nombre d\'étoiles à générer}
                            {--radius=100 : Rayon maximum en années-lumière}
                            {--output= : Fichier de sortie (défaut: database/data/gaia_nearby_stars.csv)}';

    /**
     * The console command description.
     */
    protected $description = 'Génère un fichier CSV d\'étoiles procédurales au format GAIA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $radius = (float) $this->option('radius');
        $outputPath = $this->option('output') ?: database_path('data/gaia_nearby_stars.csv');

        // Créer le répertoire si nécessaire
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->info("🌟 Génération de {$count} étoiles dans un rayon de {$radius} AL...");

        // Vérifier si le fichier existe déjà
        $existingStars = [];
        if (file_exists($outputPath)) {
            $answer = $this->choice(
                'Le fichier existe déjà. Que voulez-vous faire ?',
                ['Remplacer', 'Enrichir (ajouter)', 'Annuler'],
                2
            );

            if ($answer === 'Annuler') {
                $this->warn('Opération annulée.');
                return 0;
            }

            if ($answer === 'Enrichir (ajouter)') {
                $existingStars = $this->loadExistingStars($outputPath);
                $this->info('Chargé ' . count($existingStars) . ' étoiles existantes.');
            }
        }

        // Générer les étoiles
        $stars = $this->generateStars($count, $radius, $existingStars);

        // Fusionner avec étoiles existantes si enrichissement
        if (!empty($existingStars)) {
            $stars = array_merge($existingStars, $stars);
        }

        // Écrire le CSV
        $this->writeCSV($outputPath, $stars);

        $this->info("✅ {$count} nouvelles étoiles générées !");
        $this->info("📁 Fichier: {$outputPath}");
        $this->info("📊 Total: " . count($stars) . " étoiles");

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
     * Générer des étoiles procédurales
     */
    protected function generateStars(int $count, float $radius, array $existingStars): array
    {
        $stars = [];
        $existingNames = array_column($existingStars, 'name');
        $existingCoords = $this->extractCoordinates($existingStars);

        $spectralTypes = [
            'O' => 0.00003, // Très rare
            'B' => 0.13,
            'A' => 0.6,
            'F' => 3.0,
            'G' => 7.6,
            'K' => 12.1,
            'M' => 76.45,    // Très commun
        ];

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $generated = 0;
        $attempts = 0;
        $maxAttempts = $count * 10; // Éviter boucle infinie

        while ($generated < $count && $attempts < $maxAttempts) {
            $attempts++;

            // Générer coordonnées cartésiennes aléatoires dans une sphère
            list($x, $y, $z) = $this->randomPointInSphere($radius);
            $distance = sqrt($x * $x + $y * $y + $z * $z);

            // Vérifier doublon par proximité (< 0.01 AL)
            if ($this->isNearExistingPoint($x, $y, $z, $existingCoords, 0.01)) {
                continue;
            }

            // Convertir en coordonnées sphériques (RA/Dec)
            list($ra, $dec) = $this->cartesianToSpherical($x, $y, $z);

            // Choisir type spectral selon distribution
            $spectralType = $this->weightedRandomSpectralType($spectralTypes);
            $subclass = rand(0, 9);
            $luminosity = $this->randomLuminosityClass();
            $fullSpectralType = $spectralType . $subclass . $luminosity;

            // Générer nom unique
            $name = $this->generateUniqueName($existingNames, $generated);
            $existingNames[] = $name;

            // Source ID unique
            $sourceId = 'PROC-' . str_pad($generated + count($existingStars), 12, '0', STR_PAD_LEFT);

            // Magnitude apparente (plus loin = plus faible)
            $absoluteMag = $this->getAbsoluteMagnitude($spectralType);
            $apparentMag = $absoluteMag + 5 * (log10($distance) - 1);

            $stars[] = [
                'source_id' => $sourceId,
                'name' => $name,
                'ra' => number_format($ra, 8),
                'dec' => number_format($dec, 8),
                'distance' => number_format($distance, 4),
                'spectral_type' => $fullSpectralType,
                'magnitude' => number_format($apparentMag, 2),
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

        return $stars;
    }

    /**
     * Générer un point aléatoire dans une sphère (distribution uniforme)
     */
    protected function randomPointInSphere(float $radius): array
    {
        // Méthode de rejection sampling pour distribution uniforme
        do {
            $x = (mt_rand() / mt_getrandmax()) * 2 - 1;
            $y = (mt_rand() / mt_getrandmax()) * 2 - 1;
            $z = (mt_rand() / mt_getrandmax()) * 2 - 1;
            $distSq = $x * $x + $y * $y + $z * $z;
        } while ($distSq > 1);

        // Échelle au rayon souhaité
        $scale = $radius * pow(mt_rand() / mt_getrandmax(), 1.0 / 3.0);
        $norm = sqrt($distSq);

        return [
            $x / $norm * $scale,
            $y / $norm * $scale,
            $z / $norm * $scale,
        ];
    }

    /**
     * Convertir coordonnées cartésiennes (x,y,z) en sphériques (RA, Dec)
     * Système: X vers Soleil, Y vers gauche, Z vers pôle nord galactique
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

        return 'M'; // Défaut
    }

    /**
     * Classe de luminosité aléatoire
     */
    protected function randomLuminosityClass(): string
    {
        $classes = ['V', 'V', 'V', 'V', 'IV', 'III']; // Majorité de naines (V)
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
        // Préfixes variés pour les étoiles procédurales
        $prefixes = [
            'Proxima', 'Kepler', 'Trappist', 'Gliese', 'Ross', 'Wolf',
            'Luyten', 'Lacaille', 'Groombridge', 'Kruger', 'Barnard',
            'HD', 'HR', 'HIP', 'TYC', 'WISE',
        ];

        $attempts = 0;
        do {
            if ($attempts === 0) {
                $prefix = $prefixes[array_rand($prefixes)];
                $number = rand(100, 9999);
                $letter = chr(65 + rand(0, 25)); // A-Z
                $name = "{$prefix} {$number}{$letter}";
            } else {
                // Si conflit, utiliser ID unique
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
