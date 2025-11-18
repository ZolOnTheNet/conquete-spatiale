<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paramètres de Jeu - Conquête Galactique
    |--------------------------------------------------------------------------
    |
    | Configuration centralisée de tous les paramètres numériques du jeu.
    | Permet d'ajuster facilement le rythme et l'équilibrage pour les tests.
    |
    */

    // ====== POINTS D'ACTION (PA) ======
    'pa' => [
        'max' => env('GAME_PA_MAX', 36),                    // Capital maximum de PA (1,5 jours)
        'depart' => env('GAME_PA_START', 24),               // PA au démarrage (1 journée)
        'recuperation_montant' => env('GAME_PA_RECUP', 1),  // Nombre de PA récupérés
        'recuperation_delai' => env('GAME_PA_RECUP_DELAY', 60), // Délai en MINUTES (60 = 1h)
    ],

    // ====== PERSONNAGE - VALEURS PAR DÉFAUT ======
    'personnage' => [
        'traits_defaut' => 2,           // Valeur par défaut des 6 traits (Agilité, Force, etc.)
        'niveau_depart' => 1,           // Niveau initial
        'experience_depart' => 0,       // XP de départ
        'jetons_hope_depart' => 0,      // Jetons Hope initiaux
        'jetons_fear_depart' => 0,      // Jetons Fear initiaux
    ],

    // ====== DÉPLACEMENT - COEFFICIENTS ======
    'deplacement' => [
        // Mode Conventionnel
        'conventionnel' => [
            'init' => 0,                    // Coût initial (Init_Conventionnel)
            'coef' => 1.0,                  // Coefficient multiplicateur (Coef_Conventionnel / 100)
            'coef_pa' => 1.0,               // Coefficient PA (Coef_PAMN / 100)
        ],

        // Mode Hyperespace
        'hyperespace' => [
            'init' => 200,                  // Coût initial (Init_Hyperespace)
            'coef' => 0.5,                  // Coefficient multiplicateur (Coef_HE / 100)
            'coef_pa' => 0.2,               // Coefficient PA (Coef_PAHE / 100)
            'pa_base' => 1,                 // PA de base pour un saut HE
        ],
    ],

    // ====== VAISSEAU - VALEURS PAR DÉFAUT (Modèle A-1) ======
    'vaisseau' => [
        'a1' => [
            'modele' => 'A-1',
            'type_propulsion' => 1,
            'mode' => 'energetique',
            'masse' => 5000,                // Masse en tonnes
            'reserve_energie' => 600,       // UE stockables
            'energie_depart' => 600,        // UE au démarrage
            'vitesse_conventionnelle' => 100,
            'vitesse_saut' => 10,
            'soutes' => 3,                  // Nombre de cargos
            'volume' => 10,
            'resistance' => 100,            // Points de structure
        ],
    ],

    // ====== DISTANCES ET UNITÉS ======
    'distances' => [
        'ue_par_100millions_km' => 1,       // 1 UE = 100 millions de km (0.1 UA)
        'ua_en_milliards_km' => 1.5,        // 1 UA = 1,5 milliards de km
        'systeme_solaire_ua' => 50,         // Taille moyenne système solaire
    ],

    // ====== SYSTÈME DE DÉCOUVERTE ======
    'decouverte' => [
        'seuil_base' => 500,                // Seuil de base pour découverte
        'seuil_par_distance' => 100,        // Multiplicateur distance (Seuil = 500 + Distance × 100)
        'puissance_solaire_min' => 10,      // Puissance solaire minimum pour détecter
    ],

    // ====== DAGGERHEART - DÉS ======
    'daggerheart' => [
        'des_hope' => 12,                   // Nombre de faces dé Hope
        'des_fear' => 12,                   // Nombre de faces dé Fear
    ],

    // ====== GÉNÉRATION PROCÉDURALE - UNIVERS ======
    'univers' => [
        'systemes_initiaux' => env('GAME_UNIVERS_SYSTEMS', 10), // Nombre de systèmes voisins générés au départ
        'rayon_initial' => env('GAME_UNIVERS_RADIUS', 10.0),    // Rayon de génération initiale (années-lumière)
    ],
];
