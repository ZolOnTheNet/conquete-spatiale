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

    // ====== SYSTÈME DE SCAN (v2.0) ======
    'scan' => [
        // Difficulté des jets de compétence
        'difficulte_reglage' => 12,         // Difficulté jet Finesse (Réglage fin)
        'difficulte_astro' => 12,           // Difficulté jet Savoir (Astronomie)

        // Paliers de bonus selon marge de réussite
        'paliers_bonus' => [
            [0, 2, 4],      // Marge 0-2  → +1d4
            [3, 5, 6],      // Marge 3-5  → +1d6
            [6, 8, 8],      // Marge 6-8  → +1d8
            [9, 11, 10],    // Marge 9-11 → +1d10
            [12, 999, 12],  // Marge 12+  → +1d12
        ],
        'echec_malus' => 6,                 // Échec → -1d6

        // Formules de détectabilité de base
        // (Utilisées si detectabilite_base == 0 ou -1)
        'detectabilite' => [
            'systeme' => [
                'formule' => '(200 - puissance) / 3',
                'min' => 1,
                'max' => 200,
            ],
            'planete' => [
                'formule' => 'floor((30 - taille) / 2)',
                'min' => 1,
                'max' => 100,
            ],
            'station' => [
                'formule' => '150 - modules - (10 × nb_mines) - (population / 1000)',
                'min' => 1,
                'max' => 150,
            ],
            'mine' => [
                'formule' => '100 - (taux_extraction × 10) - (capacite / 100)',
                'min' => 10,
                'max' => 120,
            ],
        ],

        // Score de détection = (distance / 10) × detectabilite_base
        'coef_distance' => 10,              // Diviseur de distance dans la formule
    ],

    // ====== GÉNÉRATION PROCÉDURALE - UNIVERS ======
    'univers' => [
        'systemes_initiaux' => env('GAME_UNIVERS_SYSTEMS', 10), // Nombre de systèmes voisins générés au départ
        'rayon_initial' => env('GAME_UNIVERS_RADIUS', 10.0),    // Rayon de génération initiale (années-lumière)
    ],
];
