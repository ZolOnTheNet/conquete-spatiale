<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Personnage;
use App\Models\ObjetSpatial;
use App\Models\Vaisseau;
use App\Models\SystemeStellaire;
use App\Models\Decouverte;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('🚀 INITIALISATION DU JEU - CONQUÊTE SPATIALE');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');

        // 1. Créer les données de base
        $this->command->info('📦 Étape 1/4 : Création des ressources et factions...');
        $this->call([
            RessourceSeeder::class,
            FactionSeeder::class,
        ]);
        $this->command->info('');

        // 2. Générer l'univers avec données GAIA réelles
        $this->command->info('📦 Étape 2/4 : Import des étoiles GAIA réelles...');
        $this->call(GaiaSeeder::class);
        $this->command->info('');

        // 3. Créer le compte de test
        $this->command->info('📦 Étape 3/4 : Création du compte de test...');

        $compte = Compte::create([
            'nom_login' => 'test',
            'mot_de_passe' => bcrypt('password'),
            'adresse_mail' => 'test@test.com',
            'est_verifie' => true,
            'is_admin' => true, // Compte admin pour tests
        ]);
        $this->command->info('   ✅ Compte créé: test@test.com');

        // Créer un personnage
        $personnage = Personnage::create([
            'compte_id' => $compte->id,
            'nom' => 'Stark',
            'prenom' => 'John',
            // Traits Daggerheart
            'agilite' => 2,
            'force' => 1,
            'finesse' => 3,
            'instinct' => 2,
            'presence' => 1,
            'savoir' => 3,
            // Compétences (exemple)
            'competences' => [
                'pilotage' => 3,
                'navigation' => 2,
                'detection' => 2,
                'negociation' => 1,
            ],
            'experience' => 0,
            'niveau' => 1,
            'jetons_hope' => 0,
            'jetons_fear' => 0,
            // Points d'action (24 PA = 1 journée)
            'points_action' => 24,
            'max_points_action' => 36,
        ]);
        $this->command->info('   ✅ Personnage créé: John Stark');

        // Récupérer le système solaire
        $soleil = SystemeStellaire::where('nom', 'Sol')->first();

        if (!$soleil) {
            $this->command->error('   ❌ ERREUR: Le Système Solaire n\'a pas été créé !');
            return;
        }

        // Créer un objet spatial pour le vaisseau (positionné dans le système solaire)
        $objetSpatial = ObjetSpatial::create([
            'nom' => 'Explorer-01',
            'type' => 'vaisseau',
            // Position dans le système solaire (près de la Terre)
            'secteur_x' => (int)$soleil->x,
            'secteur_y' => (int)$soleil->y,
            'secteur_z' => (int)$soleil->z,
            'position_x' => $soleil->x - floor($soleil->x) + 0.01,
            'position_y' => $soleil->y - floor($soleil->y) + 0.01,
            'position_z' => $soleil->z - floor($soleil->z) + 0.01,
            // Propriétaire
            'proprietaire_id' => $personnage->id,
            // Physique
            'volume' => 100,
            'masse' => 50,
            'resistance' => 100,
            'coef_dommages' => 0,
        ]);

        // Créer un vaisseau de départ (modèle A-0)
        $vaisseau = Vaisseau::create([
            'objet_spatial_id' => $objetSpatial->id,
            'modele' => 'A-0',
            // Propulsion (selon GDD)
            'type_propulsion' => 1,
            'mode' => 'energetique',
            'reserve' => 1000,
            'energie_actuelle' => 1000,
            'vitesse_conventionnelle' => 1.0,
            'vitesse_saut' => 10.0,
            // Coefficients (valeurs de base)
            'init_conventionnel' => 0,
            'init_hyperespace' => 200,
            'coef_conventionnel' => 1.0,
            'coef_hyperespace' => 1.0,
            'coef_pa_mn' => 1.0,
            'coef_pa_he' => 0.2,
            // Soute
            'max_soutes' => 5,
            'place_soute' => 5,
            'masse_variable' => 0,
            // Maintenance
            'vetuste' => 0,
            'complexite_fct' => 1,
            'score_panne' => 0,
            'score_entretien' => 0,
            // Informatique
            'system_informatique' => 1,
            // Scan
            'puissance_scan' => 10,
        ]);

        // Associer le vaisseau au personnage
        $personnage->vaisseau_actif_id = $vaisseau->id;
        $personnage->save();

        // Mettre à jour le personnage principal du compte
        $compte->perso_principal = $personnage->id;
        $compte->save();

        $this->command->info('   ✅ Vaisseau créé: Explorer-01 (modèle A-0)');
        $this->command->info('');

        // 4. Créer les découvertes initiales
        $this->command->info('📦 Étape 4/4 : Initialisation des découvertes...');

        // Découvrir automatiquement le système solaire (complètement connu)
        Decouverte::create([
            'personnage_id' => $personnage->id,
            'systeme_stellaire_id' => $soleil->id,
            'decouvert_a' => now(),
            'coordonnees_connues' => true,
            'type_etoile_connu' => true,
            'nb_planetes_connu' => true,
            'visite' => true,
            'distance_decouverte' => 0.0,
        ]);
        $this->command->info("   ✅ Système Solaire découvert automatiquement");

        // Découvrir automatiquement tous les systèmes dans un rayon de 5 AL
        $systemesProches = SystemeStellaire::where('id', '!=', $soleil->id)
            ->get()
            ->filter(function ($systeme) use ($soleil) {
                $distance = sqrt(
                    pow($systeme->x - $soleil->x, 2) +
                    pow($systeme->y - $soleil->y, 2) +
                    pow($systeme->z - $soleil->z, 2)
                );
                return $distance <= 5.0;
            });

        foreach ($systemesProches as $systeme) {
            $distance = sqrt(
                pow($systeme->x - $soleil->x, 2) +
                pow($systeme->y - $soleil->y, 2) +
                pow($systeme->z - $soleil->z, 2)
            );

            Decouverte::create([
                'personnage_id' => $personnage->id,
                'systeme_stellaire_id' => $systeme->id,
                'decouvert_a' => now(),
                'coordonnees_connues' => true,
                'type_etoile_connu' => true,
                'nb_planetes_connu' => true,
                'visite' => false,
                'distance_decouverte' => $distance,
            ]);
        }

        $nbDecouvertes = $systemesProches->count() + 1; // +1 pour le Soleil
        $this->command->info("   ✅ {$nbDecouvertes} systèmes découverts dans un rayon de 5 AL");
        $this->command->info('');

        // Résumé final
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('✨ INITIALISATION TERMINÉE AVEC SUCCÈS !');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('🔐 Identifiants de connexion:');
        $this->command->info('   Login    : test');
        $this->command->info('   Email    : test@test.com');
        $this->command->info('   Password : password');
        $this->command->info('');
        $this->command->info('🚀 Personnage:');
        $this->command->info('   Nom      : John Stark');
        $this->command->info('   Vaisseau : Explorer-01 (A-0)');
        $this->command->info('   Position : Système Solaire');
        $this->command->info('   PA       : 24/36 (1 journée)');
        $this->command->info('');
        // Statistiques de l'univers créé
        $totalSystemes = SystemeStellaire::count();
        $totalPlanetes = DB::table('planetes')->count();
        $totalStations = DB::table('stations')->count();
        $systemesGaia = SystemeStellaire::where('source_gaia', true)->count();

        $this->command->info('🌌 Univers (Données GAIA Réelles):');
        $this->command->info("   Systèmes stellaires : {$totalSystemes}");
        $this->command->info("   └─ Étoiles GAIA     : {$systemesGaia}");
        $this->command->info("   Planètes            : {$totalPlanetes}");
        $this->command->info("   Stations spatiales  : {$totalStations}");
        $this->command->info("   Systèmes découverts : {$nbDecouvertes}");
        $this->command->info('');
        $this->command->info('⭐ Systèmes célèbres disponibles:');
        $this->command->info('   - Sol (Système Solaire) avec Terre, Lune, Mars, Jupiter, Neptune');
        $this->command->info('   - Alpha Centauri, Proxima Centauri, Sirius, Epsilon Eridani...');
        $this->command->info('   - Et ' . ($systemesGaia - 10) . ' autres étoiles du catalogue GAIA DR3 !');
        $this->command->info('');
        $this->command->info('📝 Prochaines étapes:');
        $this->command->info('   1. Lancer le serveur : php artisan serve');
        $this->command->info('   2. Accéder au jeu    : http://localhost:8000');
        $this->command->info('   3. Se connecter avec les identifiants ci-dessus');
        $this->command->info('   4. Explorer les vraies étoiles de notre voisinage galactique !');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
    }
}
