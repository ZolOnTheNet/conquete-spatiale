<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Personnage;
use App\Models\ObjetSpatial;
use App\Models\Vaisseau;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un compte de test
        $compte = Compte::create([
            'nom_login' => 'test',
            'mot_de_passe' => bcrypt('password'),
            'adresse_mail' => 'test@test.com',
            'est_verifie' => true,
            'is_admin' => true, // Compte admin pour tests
        ]);

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
        ]);

        // Créer un objet spatial pour le vaisseau
        $objetSpatial = ObjetSpatial::create([
            'nom' => 'Explorer-01',
            'type' => 'vaisseau',
            // Position de départ (secteur central)
            'secteur_x' => 0,
            'secteur_y' => 0,
            'secteur_z' => 0,
            'position_x' => 0.5,
            'position_y' => 0.5,
            'position_z' => 0.5,
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
        ]);

        // Associer le vaisseau au personnage
        $personnage->vaisseau_actif_id = $vaisseau->id;
        $personnage->save();

        // Mettre à jour le personnage principal du compte
        $compte->perso_principal = $personnage->id;
        $compte->save();

        $this->command->info('Données de test créées avec succès !');
        $this->command->info('Login: test');
        $this->command->info('Password: password');
    }
}
