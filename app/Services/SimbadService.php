<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service pour interroger SIMBAD et obtenir les noms alternatifs des étoiles
 * SIMBAD = Base de données astronomique de référence (CDS Strasbourg)
 */
class SimbadService
{
    /**
     * URL de l'API SIMBAD TAP
     */
    protected string $tapUrl = 'http://simbad.u-strasbg.fr/simbad/sim-tap/sync';

    /**
     * Durée du cache (7 jours car les noms d'étoiles changent rarement)
     */
    protected int $cacheDuration = 604800;

    /**
     * Obtenir le nom principal (commun) d'une étoile depuis son identifiant GAIA DR3
     *
     * @param string $gaiaId Identifiant GAIA DR3 (ex: "GAIA DR3 5853498713190525696")
     * @return string|null Nom principal de l'étoile ou null si non trouvé
     */
    public function getMainNameFromGaiaId(string $gaiaId): ?string
    {
        // Nettoyer l'ID (retirer "GAIA DR3 " si présent)
        $cleanId = str_replace(['GAIA DR3 ', 'Gaia DR3 ', 'gaia dr3 '], '', $gaiaId);

        $cacheKey = "simbad_main_name_{$cleanId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($cleanId) {
            try {
                // Query ADQL pour chercher dans SIMBAD
                $query = "SELECT main_id, otype_txt " .
                         "FROM basic " .
                         "WHERE oid IN (" .
                         "  SELECT oidref FROM ids WHERE id = 'Gaia DR3 {$cleanId}'" .
                         ")";

                $response = Http::timeout(30)->get($this->tapUrl, [
                    'request' => 'doQuery',
                    'lang' => 'ADQL',
                    'format' => 'json',
                    'query' => $query,
                ]);

                if (!$response->successful()) {
                    Log::warning("Erreur SIMBAD API: " . $response->status());
                    return null;
                }

                $data = $response->json();

                if (isset($data['data']) && count($data['data']) > 0) {
                    $mainName = $data['data'][0][0]; // main_id
                    Log::info("SIMBAD: GAIA {$cleanId} → {$mainName}");
                    return $mainName;
                }

                return null;

            } catch (\Exception $e) {
                Log::error("Erreur SIMBAD pour GAIA {$cleanId}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Obtenir tous les noms alternatifs d'une étoile GAIA
     *
     * @param string $gaiaId Identifiant GAIA DR3
     * @return array Liste de noms alternatifs
     */
    public function getAllNamesFromGaiaId(string $gaiaId): array
    {
        $cleanId = str_replace(['GAIA DR3 ', 'Gaia DR3 ', 'gaia dr3 '], '', $gaiaId);

        $cacheKey = "simbad_all_names_{$cleanId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($cleanId) {
            try {
                // Query pour récupérer tous les identifiants
                $query = "SELECT id " .
                         "FROM ids " .
                         "WHERE oidref IN (" .
                         "  SELECT oidref FROM ids WHERE id = 'Gaia DR3 {$cleanId}'" .
                         ")";

                $response = Http::timeout(30)->get($this->tapUrl, [
                    'request' => 'doQuery',
                    'lang' => 'ADQL',
                    'format' => 'json',
                    'query' => $query,
                ]);

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();

                if (isset($data['data']) && count($data['data']) > 0) {
                    $names = array_map(fn($row) => $row[0], $data['data']);
                    Log::info("SIMBAD: GAIA {$cleanId} a " . count($names) . " noms alternatifs");
                    return $names;
                }

                return [];

            } catch (\Exception $e) {
                Log::error("Erreur SIMBAD getAllNames pour GAIA {$cleanId}: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Rechercher une étoile par nom commun et obtenir son ID GAIA DR3
     *
     * @param string $commonName Nom commun (ex: "Proxima Centauri", "Sirius")
     * @return string|null Identifiant GAIA DR3 ou null
     */
    public function getGaiaIdFromCommonName(string $commonName): ?string
    {
        $cacheKey = "simbad_gaia_id_" . md5($commonName);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($commonName) {
            try {
                // Query pour chercher l'ID GAIA à partir du nom commun
                $query = "SELECT id " .
                         "FROM ids " .
                         "WHERE oidref IN (" .
                         "  SELECT oid FROM basic WHERE main_id = '{$commonName}' OR oid IN (" .
                         "    SELECT oidref FROM ids WHERE id = '{$commonName}'" .
                         "  )" .
                         ") AND id LIKE 'Gaia DR3%'";

                $response = Http::timeout(30)->get($this->tapUrl, [
                    'request' => 'doQuery',
                    'lang' => 'ADQL',
                    'format' => 'json',
                    'query' => $query,
                ]);

                if (!$response->successful()) {
                    return null;
                }

                $data = $response->json();

                if (isset($data['data']) && count($data['data']) > 0) {
                    $gaiaId = $data['data'][0][0]; // Premier résultat
                    Log::info("SIMBAD: {$commonName} → {$gaiaId}");
                    return $gaiaId;
                }

                return null;

            } catch (\Exception $e) {
                Log::error("Erreur SIMBAD getGaiaId pour {$commonName}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Enrichir un système stellaire avec son nom commun depuis SIMBAD
     *
     * @param \App\Models\SystemeStellaire $systeme
     * @return bool Succès de l'enrichissement
     */
    public function enrichSystemWithCommonName(\App\Models\SystemeStellaire $systeme): bool
    {
        if (!$systeme->source_gaia || !$systeme->gaia_source_id) {
            return false;
        }

        // Obtenir le nom principal
        $mainName = $this->getMainNameFromGaiaId($systeme->gaia_source_id);

        if ($mainName) {
            // Mettre à jour le nom du système si c'est un nom plus "commun"
            if ($this->isCommonName($mainName) && $systeme->nom !== $mainName) {
                $oldName = $systeme->nom;
                $systeme->nom = $mainName;
                $systeme->save();

                Log::info("Système enrichi: {$oldName} → {$mainName}");
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier si un nom est un nom commun (vs identifiant technique)
     *
     * @param string $name
     * @return bool
     */
    protected function isCommonName(string $name): bool
    {
        // Noms communs contiennent généralement des lettres grecques ou noms propres
        $commonPatterns = [
            '/^(Alpha|Beta|Gamma|Delta|Epsilon|Zeta|Eta|Theta|Iota|Kappa)/i',
            '/^(Proxima|Sirius|Vega|Altair|Rigel|Betelgeuse|Antares)/i',
            '/^(Tau|Epsilon|Sigma) (Ceti|Eridani|Centauri)/i',
            '/^(HD|HR|HIP|TYC|2MASS|WISE|GAIA) /',
        ];

        foreach ($commonPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                // Si c'est un nom grec ou célèbre, c'est commun
                if (!preg_match('/^(HD|HR|HIP|TYC|2MASS|WISE|GAIA)/', $name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Vider le cache SIMBAD
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
