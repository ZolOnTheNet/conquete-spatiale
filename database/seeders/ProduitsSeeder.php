<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produit;
use App\Models\Station;
use App\Models\MarcheStation;

class ProduitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📦 Création des produits...');

        // Créer les produits de base
        $produits = $this->creerProduits();

        $this->command->info('✅ ' . count($produits) . ' produits créés');

        // Peupler les marchés des stations
        $this->peuplerMarchesStations($produits);

        $this->command->info('✅ Marchés des stations peuplés');
    }

    /**
     * Créer les produits de base
     */
    protected function creerProduits(): array
    {
        $produits = [];

        // === MATIÈRES PREMIÈRES ===
        $produits[] = Produit::create([
            'nom' => 'Minerai de Fer',
            'code' => 'FER',
            'type' => 'matiere_premiere',
            'description' => 'Minerai de fer brut, utilisé dans la fabrication de métaux',
            'volume_unite' => 1.0,
            'masse_unite' => 2.5,
            'prix_base' => 50,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Minerai de Cuivre',
            'code' => 'CUIVRE',
            'type' => 'matiere_premiere',
            'description' => 'Minerai de cuivre brut, utilisé en électronique',
            'volume_unite' => 1.0,
            'masse_unite' => 2.2,
            'prix_base' => 80,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Silicium',
            'code' => 'SI',
            'type' => 'matiere_premiere',
            'description' => 'Silicium brut pour composants électroniques',
            'volume_unite' => 0.8,
            'masse_unite' => 1.8,
            'prix_base' => 120,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Eau',
            'code' => 'H2O',
            'type' => 'consommable',
            'description' => 'Eau potable purifiée',
            'volume_unite' => 1.0,
            'masse_unite' => 1.0,
            'prix_base' => 10,
        ]);

        // === MATIÈRES RAFFINÉES ===
        $produits[] = Produit::create([
            'nom' => 'Acier',
            'code' => 'ACIER',
            'type' => 'matiere_raffinee',
            'description' => 'Acier raffiné de haute qualité',
            'volume_unite' => 1.0,
            'masse_unite' => 3.0,
            'prix_base' => 150,
            'niveau_technologique' => 2,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Aluminium',
            'code' => 'ALU',
            'type' => 'matiere_raffinee',
            'description' => 'Aluminium raffiné léger et résistant',
            'volume_unite' => 1.2,
            'masse_unite' => 1.5,
            'prix_base' => 180,
            'niveau_technologique' => 2,
        ]);

        // === CARBURANTS ===
        $produits[] = Produit::create([
            'nom' => 'Hydrogène',
            'code' => 'H2',
            'type' => 'carburant',
            'description' => 'Hydrogène liquide pour propulsion',
            'volume_unite' => 2.0,
            'masse_unite' => 0.5,
            'prix_base' => 200,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Deutérium',
            'code' => 'D2',
            'type' => 'carburant',
            'description' => 'Deutérium pour réacteurs à fusion',
            'volume_unite' => 1.5,
            'masse_unite' => 0.6,
            'prix_base' => 500,
            'niveau_technologique' => 3,
        ]);

        // === COMPOSANTS ===
        $produits[] = Produit::create([
            'nom' => 'Circuit Électronique',
            'code' => 'CIRCUIT',
            'type' => 'composant',
            'description' => 'Circuit électronique de base',
            'volume_unite' => 0.1,
            'masse_unite' => 0.05,
            'prix_base' => 300,
            'niveau_technologique' => 3,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Processeur Quantique',
            'code' => 'QPROC',
            'type' => 'composant',
            'description' => 'Processeur quantique avancé',
            'volume_unite' => 0.05,
            'masse_unite' => 0.02,
            'prix_base' => 2000,
            'niveau_technologique' => 5,
        ]);

        // === CONSOMMABLES ===
        $produits[] = Produit::create([
            'nom' => 'Rations Alimentaires',
            'code' => 'FOOD',
            'type' => 'consommable',
            'description' => 'Rations alimentaires longue conservation',
            'volume_unite' => 0.5,
            'masse_unite' => 0.3,
            'prix_base' => 15,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Médicaments',
            'code' => 'MED',
            'type' => 'consommable',
            'description' => 'Kit médical de base',
            'volume_unite' => 0.2,
            'masse_unite' => 0.1,
            'prix_base' => 100,
            'niveau_technologique' => 2,
        ]);

        // === MANUFACTURÉS ===
        $produits[] = Produit::create([
            'nom' => 'Drone Minier',
            'code' => 'DRONE',
            'type' => 'manufacture',
            'description' => 'Drone autonome pour extraction minière',
            'volume_unite' => 5.0,
            'masse_unite' => 2.0,
            'prix_base' => 5000,
            'niveau_technologique' => 4,
        ]);

        $produits[] = Produit::create([
            'nom' => 'Pièces Détachées',
            'code' => 'PARTS',
            'type' => 'manufacture',
            'description' => 'Pièces détachées universelles',
            'volume_unite' => 1.0,
            'masse_unite' => 0.8,
            'prix_base' => 250,
            'niveau_technologique' => 2,
        ]);

        // === LUXE ===
        $produits[] = Produit::create([
            'nom' => 'Vin Terrien',
            'code' => 'VIN',
            'type' => 'luxe',
            'description' => 'Vin rare importé de la Terre',
            'volume_unite' => 0.1,
            'masse_unite' => 0.15,
            'prix_base' => 500,
            'niveau_technologique' => 1,
        ]);

        return $produits;
    }

    /**
     * Peupler les marchés des stations du Système Solaire
     */
    protected function peuplerMarchesStations(array $produits): void
    {
        // Récupérer les stations du Système Solaire
        $stations = Station::whereHas('systemeStellaire', function ($query) {
            $query->where('nom', 'Sol');
        })->get();

        foreach ($stations as $station) {
            $this->command->info("  Peuplement du marché de {$station->nom}...");

            // Configuration économique par station
            $config = $this->getConfigEconomique($station->nom);

            foreach ($produits as $produit) {
                // Déterminer si la station commerce ce produit
                if (!in_array($produit->type, $config['types_commerces'])) {
                    continue;
                }

                // Stock initial aléatoire
                $stockMax = rand(5000, 20000);
                $stockActuel = rand((int)($stockMax * 0.3), (int)($stockMax * 0.9));

                // Production/Consommation selon le type de station
                [$production, $consommation] = $this->getProductionConsommation(
                    $station->nom,
                    $produit->type
                );

                // Créer l'entrée de marché
                $marche = MarcheStation::create([
                    'station_id' => $station->id,
                    'produit_id' => $produit->id,
                    'stock_actuel' => $stockActuel,
                    'stock_min' => (int)($stockMax * 0.1),
                    'stock_max' => $stockMax,
                    'production_par_jour' => $production,
                    'consommation_par_jour' => $consommation,
                    'disponible_achat' => true,
                    'disponible_vente' => true,
                    'prix_achat_joueur' => 0, // Sera calculé
                    'prix_vente_joueur' => 0, // Sera calculé
                ]);

                // Calculer les prix initiaux
                $marche->calculerPrix();
                $marche->save();
            }
        }
    }

    /**
     * Configuration économique par station
     */
    protected function getConfigEconomique(string $nomStation): array
    {
        return match($nomStation) {
            'Terra-Maxi-Hub' => [
                'types_commerces' => ['matiere_premiere', 'matiere_raffinee', 'composant', 'manufacture', 'consommable', 'carburant', 'luxe'],
            ],
            'Lunastar-station' => [
                'types_commerces' => ['matiere_premiere', 'consommable', 'carburant', 'manufacture'],
            ],
            'Mars-spatiogare' => [
                'types_commerces' => ['matiere_premiere', 'matiere_raffinee', 'consommable', 'carburant'],
            ],
            'Jupiter-spatiogare' => [
                'types_commerces' => ['carburant', 'matiere_premiere', 'consommable'],
            ],
            'Neptune-spatiogare' => [
                'types_commerces' => ['carburant', 'matiere_raffinee', 'manufacture'],
            ],
            default => [
                'types_commerces' => ['matiere_premiere', 'consommable', 'carburant'],
            ],
        };
    }

    /**
     * Déterminer production et consommation par station/produit
     */
    protected function getProductionConsommation(string $nomStation, string $typeProduit): array
    {
        // Terra-Maxi-Hub : Consommateur de tout
        if ($nomStation === 'Terra-Maxi-Hub') {
            return match($typeProduit) {
                'matiere_premiere' => [50, 500],    // Peu de prod, beaucoup de conso
                'matiere_raffinee' => [200, 300],   // Équilibré
                'composant' => [300, 200],          // Producteur
                'manufacture' => [400, 100],        // Gros producteur
                'consommable' => [500, 800],        // Gros consommateur
                'carburant' => [100, 400],          // Consommateur
                'luxe' => [50, 200],                // Consommateur
                default => [100, 100],
            };
        }

        // Mars-spatiogare : Producteur de minerais
        if ($nomStation === 'Mars-spatiogare') {
            return match($typeProduit) {
                'matiere_premiere' => [800, 50],    // Gros producteur
                'matiere_raffinee' => [300, 100],   // Producteur
                'consommable' => [50, 200],         // Consommateur
                'carburant' => [100, 150],          // Léger consommateur
                default => [100, 100],
            };
        }

        // Jupiter-spatiogare : Producteur de carburant
        if ($nomStation === 'Jupiter-spatiogare') {
            return match($typeProduit) {
                'carburant' => [1000, 50],          // Énorme producteur
                'matiere_premiere' => [200, 100],   // Léger producteur
                'consommable' => [50, 150],         // Consommateur
                default => [100, 100],
            };
        }

        // Neptune-spatiogare : Producteur de matières raffinées
        if ($nomStation === 'Neptune-spatiogare') {
            return match($typeProduit) {
                'matiere_raffinee' => [600, 50],    // Gros producteur
                'carburant' => [400, 100],          // Producteur
                'manufacture' => [200, 100],        // Producteur
                default => [100, 100],
            };
        }

        // Lunastar-station : Équilibré (station de départ)
        return [100, 100];
    }
}
