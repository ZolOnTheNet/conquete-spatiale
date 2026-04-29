<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Universe Generation Mode
    |--------------------------------------------------------------------------
    |
    | Modes disponibles:
    | - 'procedural' : Génération procédurale uniquement
    | - 'gaia' : Étoiles GAIA uniquement
    | - 'hybrid' : GAIA + génération procédurale
    |
    */

    'generation_mode' => env('UNIVERSE_GENERATION_MODE', 'hybrid'),

    /*
    |--------------------------------------------------------------------------
    | GAIA Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'import des étoiles GAIA
    |
    */

    'gaia_enabled' => env('UNIVERSE_GAIA_ENABLED', true),
    'gaia_radius_ly' => env('UNIVERSE_GAIA_RADIUS', 100), // Rayon en années-lumière
    'gaia_csv_path' => env('UNIVERSE_GAIA_CSV_PATH', database_path('data/gaia_nearby_stars.csv')),

    /*
    |--------------------------------------------------------------------------
    | Procedural Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour la génération procédurale
    |
    */

    'procedural_density' => env('UNIVERSE_PROCEDURAL_DENSITY', 0.05), // Densité de systèmes (0-1)
    'procedural_size' => env('UNIVERSE_PROCEDURAL_SIZE', 50), // Taille du cube généré (N×N×N)
    'known_space_radius' => env('UNIVERSE_KNOWN_SPACE_RADIUS', 50), // Rayon espace connu (AL)

    /*
    |--------------------------------------------------------------------------
    | Star Classification Distribution
    |--------------------------------------------------------------------------
    |
    | Distribution des types spectraux (courbe de Gauss)
    | Total: 20 étoiles
    |
    */

    'star_distribution' => [
        'O' => 1,  // Bleue
        'B' => 1,  // Bleue-blanche
        'A' => 3,  // Blanche
        'F' => 4,  // Jaune-blanche
        'G' => 6,  // Jaune (le plus commun)
        'K' => 3,  // Orange
        'M' => 2,  // Rouge
    ],

    /*
    |--------------------------------------------------------------------------
    | Planet Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour la génération des planètes
    |
    */

    'planet_min_per_system' => env('UNIVERSE_PLANET_MIN', 0),
    'planet_max_per_system' => env('UNIVERSE_PLANET_MAX', 12),

    /*
    |--------------------------------------------------------------------------
    | NASA Exoplanet Archive Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'import et l'utilisation des exoplanètes NASA
    |
    */

    'exoplanet_enabled' => env('UNIVERSE_EXOPLANET_ENABLED', true),
    'exoplanet_api_url' => env('UNIVERSE_EXOPLANET_API_URL', 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync'),
    'exoplanet_cache_duration' => env('UNIVERSE_EXOPLANET_CACHE', 86400), // 24 heures
    'exoplanet_api_timeout' => env('UNIVERSE_EXOPLANET_TIMEOUT', 120), // 2 minutes

    /*
    |--------------------------------------------------------------------------
    | Dynamic Universe Generation
    |--------------------------------------------------------------------------
    |
    | Génération dynamique de l'univers pendant le jeu
    | L'univers s'étend automatiquement quand les joueurs explorent
    |
    */

    'dynamic_generation_enabled' => env('UNIVERSE_DYNAMIC_GENERATION', true),
    'dynamic_generation_radius' => env('UNIVERSE_DYNAMIC_RADIUS', 3), // Rayon en secteurs
    'generate_empty_sectors' => env('UNIVERSE_GENERATE_EMPTY', false), // Générer même si pas d'étoile GAIA

    /*
    |--------------------------------------------------------------------------
    | Universe Expansion Strategy
    |--------------------------------------------------------------------------
    |
    | Stratégie de génération dynamique:
    | - 'on_jump': Générer autour de la destination à chaque saut (recommandé)
    | - 'on_scan': Générer uniquement lors d'un scan
    | - 'manual': Pas de génération automatique
    |
    */

    'expansion_strategy' => env('UNIVERSE_EXPANSION_STRATEGY', 'on_jump'),

];
