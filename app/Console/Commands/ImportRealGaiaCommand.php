<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportRealGaiaCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gaia:import-real
                            {--radius=100 : Rayon maximum en années-lumière depuis le centre}
                            {--limit=2000 : Nombre maximum d\'étoiles à importer}
                            {--min-magnitude=15 : Magnitude apparente maximale (plus bas = plus lumineux)}
                            {--csv= : Fichier de sortie (défaut: database/data/gaia_nearby_stars.csv)}
                            {--merge : Fusionner avec les étoiles existantes au lieu de remplacer}
                            {--insecure : Désactiver la vérification SSL (utile si erreur certificat)}
                            {--center-ra= : Ascension droite du centre (en degrés, défaut: Soleil)}
                            {--center-dec= : Déclinaison du centre (en degrés, défaut: Soleil)}
                            {--center-distance= : Distance du centre en AL (défaut: 0 = Soleil)}
                            {--min-distance=0 : Distance minimale depuis le centre (pour zones concentriques)}
                            {--system-id= : Utiliser un système existant comme centre}';

    /**
     * The console command description.
     */
    protected $description = 'Import les vraies données du catalogue GAIA DR3 (ESA) via l\'API TAP';

    /**
     * GAIA TAP service endpoint
     */
    protected const GAIA_TAP_URL = 'https://gea.esac.esa.int/tap-server/tap/sync';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $radius = (float) $this->option('radius');
        $limit = (int) $this->option('limit');
        $minMag = (float) $this->option('min-magnitude');
        $csvPath = $this->option('csv') ?: database_path('data/gaia_nearby_stars.csv');
        $merge = $this->option('merge');
        $insecure = $this->option('insecure');
        $minDistance = (float) $this->option('min-distance');
        $systemId = $this->option('system-id');

        // Déterminer le centre
        $centerRA = $this->option('center-ra');
        $centerDec = $this->option('center-dec');
        $centerDistance = $this->option('center-distance');

        // Si system-id est fourni, récupérer les coordonnées du système
        if ($systemId) {
            $systeme = \App\Models\SystemeStellaire::find($systemId);
            if (!$systeme) {
                $this->error("❌ Système avec ID {$systemId} non trouvé.");
                return 1;
            }

            if (!$systeme->gaia_ra || !$systeme->gaia_dec) {
                $this->error("❌ Le système {$systeme->nom} n'a pas de coordonnées GAIA.");
                return 1;
            }

            $centerRA = $systeme->gaia_ra;
            $centerDec = $systeme->gaia_dec;
            $centerDistance = $systeme->gaia_distance_ly;
            $this->info("🎯 Centre: Système {$systeme->nom} (ID: {$systemId})");
        }

        // Mode zone personnalisée ou depuis le Soleil
        $customCenter = ($centerRA !== null && $centerDec !== null);

        $this->info('🌟 IMPORT GAIA DR3 - Données réelles ESA');
        if ($customCenter) {
            $this->info("🎯 Centre personnalisé: RA={$centerRA}°, Dec={$centerDec}°, Distance=" . ($centerDistance ?? 0) . " AL");
        } else {
            $this->info("🌞 Centre: Soleil (0, 0, 0)");
        }
        $this->info("📏 Rayon: {$radius} AL");
        if ($minDistance > 0) {
            $this->info("📏 Distance minimale: {$minDistance} AL (zone concentrique)");
        }
        $this->info("🔢 Limite: {$limit} étoiles");
        $this->info("💫 Magnitude max: {$minMag}");
        if ($insecure) {
            $this->warn('⚠️  Mode insecure : Vérification SSL désactivée');
        }
        $this->newLine();

        // Créer le répertoire si nécessaire
        $dir = dirname($csvPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Charger étoiles existantes si merge
        $existingStars = [];
        if ($merge && file_exists($csvPath)) {
            $existingStars = $this->loadExistingStars($csvPath);
            $this->info('📂 Chargé ' . count($existingStars) . ' étoiles existantes.');
        }

        // Convertir rayons en parsecs (1 AL ≈ 0.306601 pc)
        $radiusParsecs = $radius * 0.306601;
        $minDistanceParsecs = $minDistance * 0.306601;

        // Construire requête ADQL
        if ($customCenter) {
            $query = $this->buildADQLQueryWithCenter(
                $centerRA,
                $centerDec,
                $centerDistance ?? 0,
                $radiusParsecs,
                $minDistanceParsecs,
                $minMag,
                $limit
            );
        } else {
            $query = $this->buildADQLQuery($radiusParsecs, $minMag, $limit);
        }

        $this->info('🔍 Interrogation de la base GAIA DR3...');
        $this->line("Query: " . substr($query, 0, 100) . '...');
        $this->newLine();

        // Interroger GAIA TAP
        try {
            $gaiaData = $this->queryGaiaTAP($query, $insecure);

            if (empty($gaiaData)) {
                $this->error('❌ Aucune donnée reçue de GAIA. Vérifiez votre connexion internet.');
                return 1;
            }

            $this->info("✅ Reçu " . count($gaiaData) . " étoiles de GAIA DR3");
            $this->newLine();

            // Convertir au format du jeu
            $bar = $this->output->createProgressBar(count($gaiaData));
            $bar->setFormat('verbose');
            $bar->start();

            $convertedStars = [];
            $skipped = 0;

            foreach ($gaiaData as $star) {
                $converted = $this->convertGaiaToGameFormat($star);

                if ($converted) {
                    // Vérifier doublon si merge
                    if ($merge && $this->isDuplicate($converted, $existingStars)) {
                        $skipped++;
                    } else {
                        $convertedStars[] = $converted;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if ($skipped > 0) {
                $this->warn("⚠️  {$skipped} doublons ignorés");
            }

            // Fusionner avec existantes si merge
            if ($merge) {
                $convertedStars = array_merge($existingStars, $convertedStars);
            }

            // Écrire le CSV
            $this->writeCSV($csvPath, $convertedStars);

            $this->info("✅ Import terminé !");
            $this->info("📁 Fichier: {$csvPath}");
            $this->info("📊 Total: " . count($convertedStars) . " étoiles");
            $this->newLine();
            $this->info("💡 Lancez 'php artisan migrate:fresh --seed' pour importer en base de données");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de l\'import GAIA:');
            $this->error($e->getMessage());
            return 1;
        }
    }

    /**
     * Construire la requête ADQL pour GAIA
     */
    protected function buildADQLQuery(float $radiusParsecs, float $minMag, int $limit): string
    {
        // ADQL Query pour étoiles proches avec données complètes
        // Note: ADQL utilise || pour la concaténation (pas CONCAT)
        // Note: CAST en VARCHAR nécessaire pour concaténer avec source_id (BIGINT)
        return "SELECT TOP {$limit}
            source_id,
            'GAIA DR3 ' || CAST(source_id AS VARCHAR) as designation,
            ra,
            dec,
            parallax,
            phot_g_mean_mag,
            bp_rp,
            teff_gspphot as teff_val
        FROM gaiadr3.gaia_source
        WHERE parallax > " . (1000 / $radiusParsecs) . "
            AND parallax_over_error > 5
            AND phot_g_mean_mag < {$minMag}
            AND ra IS NOT NULL
            AND dec IS NOT NULL
        ORDER BY parallax DESC";
    }

    /**
     * Construire la requête ADQL pour GAIA avec centre personnalisé
     */
    protected function buildADQLQueryWithCenter(
        float $centerRA,
        float $centerDec,
        float $centerDistanceAL,
        float $radiusParsecs,
        float $minDistanceParsecs,
        float $minMag,
        int $limit
    ): string {
        // Convertir centre en parsecs
        $centerDistanceParsecs = $centerDistanceAL * 0.306601;

        // Calculer rayon angulaire maximal en degrés
        // Pour une zone sphérique autour du centre
        // On utilise DISTANCE() qui calcule la séparation angulaire
        $angularRadiusDeg = rad2deg(atan($radiusParsecs / max(1, $centerDistanceParsecs)));

        // Pour éviter des requêtes trop larges, limiter à 90 degrés
        $angularRadiusDeg = min(90, $angularRadiusDeg);

        $query = "SELECT TOP {$limit}
            source_id,
            'GAIA DR3 ' || CAST(source_id AS VARCHAR) as designation,
            ra,
            dec,
            parallax,
            phot_g_mean_mag,
            bp_rp,
            teff_gspphot as teff_val,
            DISTANCE(
                POINT('ICRS', ra, dec),
                POINT('ICRS', {$centerRA}, {$centerDec})
            ) as angular_separation
        FROM gaiadr3.gaia_source
        WHERE 1=CONTAINS(
                POINT('ICRS', ra, dec),
                CIRCLE('ICRS', {$centerRA}, {$centerDec}, {$angularRadiusDeg})
            )
            AND parallax_over_error > 5
            AND phot_g_mean_mag < {$minMag}
            AND ra IS NOT NULL
            AND dec IS NOT NULL";

        // Ajouter filtre de distance minimale si spécifié (zone concentrique)
        if ($minDistanceParsecs > 0) {
            $minParallax = 1000 / ($centerDistanceParsecs + $radiusParsecs);
            $maxParallax = 1000 / max(1, $centerDistanceParsecs - $minDistanceParsecs);
            $query .= "\n            AND parallax BETWEEN {$minParallax} AND {$maxParallax}";
        }

        $query .= "\n        ORDER BY angular_separation ASC";

        return $query;
    }

    /**
     * Interroger le service TAP GAIA
     */
    protected function queryGaiaTAP(string $query, bool $insecure = false): array
    {
        $http = Http::timeout(120);

        // Désactiver vérification SSL si demandé (utile pour certificats auto-signés)
        if ($insecure) {
            $http = $http->withOptions(['verify' => false]);
        }

        $response = $http->asForm()
            ->post(self::GAIA_TAP_URL, [
                'REQUEST' => 'doQuery',
                'LANG' => 'ADQL',
                'FORMAT' => 'json',
                'QUERY' => $query,
            ]);

        if (!$response->successful()) {
            throw new \Exception("Erreur HTTP " . $response->status() . ": " . $response->body());
        }

        $data = $response->json();

        // Parser le format VOTable JSON de GAIA
        if (!isset($data['data'])) {
            throw new \Exception("Format de réponse GAIA inattendu");
        }

        return $data['data'];
    }

    /**
     * Convertir une étoile GAIA au format du jeu
     */
    protected function convertGaiaToGameFormat(array $gaiaStar): ?array
    {
        // Extraire données
        $sourceId = $gaiaStar[0] ?? null;
        $designation = $gaiaStar[1] ?? "GAIA {$sourceId}";
        $ra = $gaiaStar[2] ?? null;
        $dec = $gaiaStar[3] ?? null;
        $parallax = $gaiaStar[4] ?? null;
        $magnitude = $gaiaStar[5] ?? null;
        $bpRp = $gaiaStar[6] ?? null;
        $teff = $gaiaStar[7] ?? null;

        // Vérifier données essentielles
        if (!$sourceId || !$ra || !$dec || !$parallax || $parallax <= 0) {
            return null;
        }

        // Calculer distance en années-lumière (distance = 1000/parallax parsecs)
        $distanceParsecs = 1000 / $parallax;
        $distanceAL = $distanceParsecs / 0.306601;

        // Déterminer type spectral depuis bp_rp (indice de couleur) et température
        $spectralType = $this->estimateSpectralType($bpRp, $teff);

        // Nettoyer le nom
        $name = $this->cleanStarName($designation);

        return [
            'source_id' => 'GAIA-DR3-' . $sourceId,
            'name' => $name,
            'ra' => number_format($ra, 8),
            'dec' => number_format($dec, 8),
            'distance' => number_format($distanceAL, 4),
            'spectral_type' => $spectralType,
            'magnitude' => number_format($magnitude ?? 99.99, 2),
        ];
    }

    /**
     * Estimer le type spectral depuis l'indice de couleur BP-RP et la température
     */
    protected function estimateSpectralType(?float $bpRp, ?float $teff): string
    {
        // Priorité à la température effective si disponible
        if ($teff && $teff > 0) {
            if ($teff >= 30000) return $this->randomSubclass('O');
            if ($teff >= 10000) return $this->randomSubclass('B');
            if ($teff >= 7500) return $this->randomSubclass('A');
            if ($teff >= 6000) return $this->randomSubclass('F');
            if ($teff >= 5200) return $this->randomSubclass('G');
            if ($teff >= 3700) return $this->randomSubclass('K');
            return $this->randomSubclass('M');
        }

        // Sinon utiliser BP-RP (indice de couleur)
        if ($bpRp !== null) {
            if ($bpRp < 0.5) return $this->randomSubclass('A');
            if ($bpRp < 0.8) return $this->randomSubclass('F');
            if ($bpRp < 1.2) return $this->randomSubclass('G');
            if ($bpRp < 1.8) return $this->randomSubclass('K');
            return $this->randomSubclass('M');
        }

        // Défaut : étoile de type G (comme le Soleil)
        return 'G2V';
    }

    /**
     * Générer sous-classe aléatoire (0-9) et classe de luminosité
     */
    protected function randomSubclass(string $type): string
    {
        $subclass = rand(0, 9);
        $luminosity = ['V', 'V', 'V', 'IV']; // Majorité de naines
        return $type . $subclass . $luminosity[array_rand($luminosity)];
    }

    /**
     * Nettoyer le nom de l'étoile
     */
    protected function cleanStarName(string $name): string
    {
        // Extraire nom court si trop long
        if (strlen($name) > 50) {
            // Garder juste l'ID GAIA
            if (preg_match('/(\d{10,})/', $name, $matches)) {
                return 'GAIA ' . substr($matches[1], 0, 12);
            }
        }

        return trim($name);
    }

    /**
     * Vérifier si étoile est un doublon
     */
    protected function isDuplicate(array $star, array $existingStars): bool
    {
        foreach ($existingStars as $existing) {
            // Doublon par source_id
            if ($existing['source_id'] === $star['source_id']) {
                return true;
            }

            // Doublon par proximité (< 0.001 AL = très proche)
            $distance = $this->calculateAngularDistance(
                (float) $star['ra'],
                (float) $star['dec'],
                (float) $star['distance'],
                (float) $existing['ra'],
                (float) $existing['dec'],
                (float) $existing['distance']
            );

            if ($distance < 0.001) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculer distance entre deux étoiles (approximation)
     */
    protected function calculateAngularDistance(
        float $ra1, float $dec1, float $dist1,
        float $ra2, float $dec2, float $dist2
    ): float {
        // Conversion en cartésien et calcul de distance euclidienne
        $x1 = $dist1 * cos(deg2rad($dec1)) * cos(deg2rad($ra1));
        $y1 = $dist1 * cos(deg2rad($dec1)) * sin(deg2rad($ra1));
        $z1 = $dist1 * sin(deg2rad($dec1));

        $x2 = $dist2 * cos(deg2rad($dec2)) * cos(deg2rad($ra2));
        $y2 = $dist2 * cos(deg2rad($dec2)) * sin(deg2rad($ra2));
        $z2 = $dist2 * sin(deg2rad($dec2));

        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2) + pow($z2 - $z1, 2));
    }

    /**
     * Charger les étoiles existantes
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
