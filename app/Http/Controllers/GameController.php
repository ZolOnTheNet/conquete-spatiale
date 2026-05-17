<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Arme;
use App\Models\Bouclier;
use App\Models\Combat;
use App\Models\Compte;
use App\Models\Decouverte;
use App\Models\Ennemi;
use App\Models\Faction;
use App\Models\Gisement;
use App\Models\Marche;
use App\Models\Mission;
use App\Models\Recette;
use App\Models\Reputation;
use App\Models\Ressource;
use App\Models\SystemeStellaire;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('game.console');
    }

    // Sélection/Création de personnage
    public function selectionPersonnage(Request $request): View
    {
        $compte = $request->user();
        $personnages = $compte->personnages;

        // Récupérer les stations de départ disponibles (système Sol)
        $systemeSol = SystemeStellaire::where('secteur_x', 0)
            ->where('secteur_y', 0)
            ->where('secteur_z', 0)
            ->first();

        $stationsDepart = [];
        if ($systemeSol) {
            $stationsDepart = \App\Models\Station::where('systeme_stellaire_id', $systemeSol->id)
                ->where('accessible', true)
                ->orderBy('nom')
                ->get();
        }

        return view('game.selection-personnage', [
            'personnages' => $personnages,
            'compte' => $compte,
            'stationsDepart' => $stationsDepart,
        ]);
    }

    public function creerPersonnage(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'station_depart_id' => 'nullable|exists:stations,id',
        ]);

        $compte = $request->user();

        // Récupérer la station de départ (par défaut Lunastar Station si non spécifiée)
        $stationDepart = null;
        if (isset($validated['station_depart_id'])) {
            $stationDepart = \App\Models\Station::find($validated['station_depart_id']);
        }

        if (!$stationDepart) {
            // Chercher Lunastar Station par défaut
            $stationDepart = \App\Models\Station::where('nom', 'Lunastar Station')->first();
        }

        // Créer le personnage
        $personnage = Personnage::create([
            'compte_id' => $compte->id,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'] ?? null,
            'dans_station_id' => $stationDepart ? $stationDepart->id : null,
            // Valeurs par défaut depuis config
            'agilite' => config('game.personnage.traits_defaut', 2),
            'force' => config('game.personnage.traits_defaut', 2),
            'finesse' => config('game.personnage.traits_defaut', 2),
            'instinct' => config('game.personnage.traits_defaut', 2),
            'presence' => config('game.personnage.traits_defaut', 2),
            'savoir' => config('game.personnage.traits_defaut', 2),
            'competences' => [],
            'experience' => config('game.personnage.experience_depart', 0),
            'niveau' => config('game.personnage.niveau_depart', 1),
            'jetons_hope' => config('game.personnage.jetons_hope_depart', 0),
            'jetons_fear' => config('game.personnage.jetons_fear_depart', 0),
            // PA depuis config
            'points_action' => config('game.pa.depart', 24),
            'max_points_action' => config('game.pa.max', 36),
            'derniere_recuperation_pa' => null, // Démarre à la première dépense
        ]);

        // Créer automatiquement les découvertes du Système Solaire (PoI connus)
        $this->creerDecouvertesSolaires($personnage);

        // Créer le vaisseau gratuit dans le hangar de la station
        if ($stationDepart) {
            $this->creerVaisseauGratuit($personnage, $stationDepart);
        }

        // Si c'est le premier personnage, le définir comme principal
        if (!$compte->perso_principal) {
            $compte->perso_principal = $personnage->id;
            $compte->save();
        }

        return redirect()->route('personnage.selection')
            ->with('success', 'Personnage créé avec succès !');
    }

    /**
     * Créer automatiquement les découvertes du Système Solaire pour un nouveau personnage
     */
    protected function creerDecouvertesSolaires(Personnage $personnage): void
    {
        // Récupérer tous les systèmes stellaires avec poi_connu = true
        $systemesConnus = SystemeStellaire::where('poi_connu', true)->get();

        foreach ($systemesConnus as $systeme) {
            // Créer la découverte complète (comme si déjà connu au départ)
            Decouverte::create([
                'personnage_id' => $personnage->id,
                'systeme_stellaire_id' => $systeme->id,
                'resultat_scan' => 9999, // Score max pour découverte automatique
                'seuil_detection' => 0, // Pas de seuil pour PoI connus
                'distance_decouverte' => 0.0,
                'decouvert_a' => now(),
                'coordonnees_connues' => true,
                'type_etoile_connu' => true,
                'nb_planetes_connu' => true,
                'visite' => false, // Pas encore visité physiquement
            ]);
        }
    }

    /**
     * Créer le vaisseau gratuit de départ dans le hangar de la station
     */
    protected function creerVaisseauGratuit(Personnage $personnage, \App\Models\Station $station): void
    {
        // Créer l'objet spatial pour le vaisseau
        $objetSpatial = \App\Models\ObjetSpatial::create([
            'type' => 'vaisseau',
            'nom' => "Shuttle de {$personnage->nom}",
            'secteur_x' => $station->systemeStellaire->secteur_x,
            'secteur_y' => $station->systemeStellaire->secteur_y,
            'secteur_z' => $station->systemeStellaire->secteur_z,
            'position_x' => $station->systemeStellaire->position_x,
            'position_y' => $station->systemeStellaire->position_y,
            'position_z' => $station->systemeStellaire->position_z,
        ]);

        // Créer le vaisseau gratuit (shuttle basique)
        $vaisseau = \App\Models\Vaisseau::create([
            'objet_spatial_id' => $objetSpatial->id,
            'nom' => "Shuttle de {$personnage->nom}",
            'modele' => 'Shuttle Standard',
            'proprietaire_id' => $personnage->id,
            'amarree_station_id' => $station->id,
            'capacite_soute' => 10,
            'vitesse_max' => 1.0,
        ]);

        // Définir ce vaisseau comme actif pour le personnage
        $personnage->vaisseau_actif_id = $vaisseau->id;
        $personnage->save();
    }

    public function activerPersonnage(Request $request, Personnage $personnage)
    {
        $compte = $request->user();

        // Vérifier que le personnage appartient bien au compte
        if ($personnage->compte_id !== $compte->id) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Ce personnage ne vous appartient pas.');
        }

        $compte->perso_principal = $personnage->id;
        $compte->save();

        return redirect()->route('dashboard')
            ->with('success', "Personnage {$personnage->nom} activé !");
    }

    public function dashboard(Request $request)
    {
        // Point d'entrée du jeu : rediriger vers la carte (nouvelle interface)
        // La carte est le point d'entrée principal après connexion
        return redirect()->route('carte');
    }

    public function executeCommand(Request $request)
    {
        $command = $request->input('command');

        // Récupérer le personnage depuis le middleware
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun personnage trouvé. Créez un personnage d\'abord.',
            ]);
        }

        $personnage->load(['vaisseauActif.objetSpatial']);

        // Récupération automatique des PA (1 PA/heure)
        $recup = $personnage->recupererPAAutomatique();

        $result = $this->processCommand($command, $personnage);

        // Ajouter message de récupération PA si applicable
        if ($recup['pa_recuperes'] > 0) {
            $message_recup = "\n[INFO] +{$recup['pa_recuperes']} PA récupérés ({$recup['heures_ecoulees']}h écoulées)\n";
            if (isset($result['message'])) {
                $result['message'] = $message_recup . $result['message'];
            } else {
                $result['message'] = $message_recup;
            }
        }

        // Déterminer si l'interface doit être rafraîchie
        // Commandes qui NE nécessitent PAS de refresh : status, vaisseau, lancer, inventaire, marche, prix, recettes, etat-combat, help, history, clear
        $commandeLower = strtolower(trim($command));
        $commandesPasDeRefresh = ['status', 'statut', 'vaisseau', 'ship', 'lancer', 'inventaire', 'inv', 'marche', 'market', 'prix', 'prices', 'recettes', 'etat-combat', 'help', 'aide', 'history', 'clear'];

        $needsRefresh = true;
        foreach ($commandesPasDeRefresh as $cmd) {
            if (strpos($commandeLower, $cmd) === 0) {
                $needsRefresh = false;
                break;
            }
        }

        // Ajouter le flag de rafraîchissement si la commande a réussi et nécessite un refresh
        if ($needsRefresh && isset($result['success']) && $result['success']) {
            $result['refresh_ui'] = true;
        }

        return response()->json($result);
    }

    private function processCommand(string $command, Personnage $personnage): array
    {
        $parts = explode(' ', trim($command));
        $action = strtolower($parts[0] ?? '');

        // Détection commandes admin (préfixe /adm)
        if (str_starts_with($action, '/adm')) {
            return $this->processAdminCommand($command, $personnage);
        }

        return match ($action) {
            'help', 'aide' => $this->showHelp($personnage),
            'status', 'statut' => $this->showStatus($personnage),
            'position', 'pos' => $this->showPosition($personnage),
            'vaisseau', 'ship' => $this->showShip($personnage),
            'lancer', 'roll' => $this->rollDice($personnage, $parts),
            'deplacer', 'move' => $this->moveShip($personnage, $parts),
            'bouger' => $this->moveShipRelative($personnage, $parts),
            'saut', 'jump' => $this->jumpHyperspace($personnage, $parts),
            'scan', 'scanner' => $this->scanSystems($personnage, $parts),
            'carte', 'map' => $this->showMap($personnage),
            // Commandes Phase 2 - Économie
            'scan-planete', 'scanp' => $this->scanPlanete($personnage, $parts),
            'extraire', 'mine' => $this->extraireRessource($personnage, $parts),
            'inventaire', 'inv' => $this->showInventaire($personnage),
            // Commandes Marchés
            'marche', 'market' => $this->showMarche($personnage),
            'acheter', 'buy' => $this->acheterRessource($personnage, $parts),
            'vendre', 'sell' => $this->vendreRessource($personnage, $parts),
            'prix', 'prices' => $this->showPrix($personnage, $parts),
            // Commandes Fabrication
            'recettes', 'recipes' => $this->showRecettes($personnage, $parts),
            'fabriquer', 'craft' => $this->fabriquerRecette($personnage, $parts),
            // Commandes Combat
            'armes', 'weapons' => $this->showArmes($personnage),
            'boucliers', 'shields' => $this->showBoucliers($personnage),
            'equiper', 'equip' => $this->equiperEquipement($personnage, $parts),
            'etat-combat', 'combat' => $this->showEtatCombat($personnage),
            'reparer', 'repair' => $this->reparerVaisseau($personnage, $parts),
            'scanner-ennemis', 'scane' => $this->scannerEnnemis($personnage),
            'ennemis', 'enemies' => $this->showEnnemis(),
            'attaquer', 'attack' => $this->attaquerEnnemi($personnage, $parts),
            'fuir', 'flee' => $this->fuirCombat($personnage),
            // Missions
            'missions', 'quests' => $this->showMissions($personnage, $parts),
            'mission-accepter', 'accept' => $this->accepterMission($personnage, $parts),
            'mission-rendre', 'complete' => $this->rendreMission($personnage, $parts),
            'mission-abandonner', 'abandon' => $this->abandonnerMission($personnage, $parts),
            'factions' => $this->showFactions($personnage),
            'reputation', 'rep' => $this->showReputation($personnage),
            // Stations et Arrimage
            'arrimer', 'dock' => $this->arrimerStation($personnage, $parts),
            'desarrimer', 'undock' => $this->desarrimerStation($personnage),
            'transborder', 'board-station' => $this->transborderStation($personnage),
            'embarquer', 'board-ship' => $this->embarquerVaisseau($personnage),
            'garage' => $this->accederGarage($personnage),
            'comptoirs', 'hub' => $this->accederComptoirs($personnage),
            'hopital', 'hospital' => $this->accederHopital($personnage),
            'industrie', 'industry' => $this->accederIndustrie($personnage),
            'ravitailler', 'refuel' => $this->ravitaillerVaisseau($personnage, $parts),
            'recharger', 'reload', 'recharge' => $this->rechargerVaisseau($personnage, $parts),
            '' => ['success' => true, 'message' => ''],
            default => [
                'success' => false,
                'message' => "Commande inconnue: {$action}. Tapez 'help' pour voir les commandes disponibles.",
            ],
        };
    }

    private function showHelp(Personnage $personnage): array
    {
        $isAdmin = $personnage->compte->is_admin ?? false;

        $help = "[C]COMMANDES DISPONIBLES:[/C]
  [W]help, aide[/W]                     - Afficher cette aide
  [W]status, statut[/W]                 - Afficher le statut du personnage
  [W]position, pos[/W]                  - Afficher la position actuelle
  [W]vaisseau, ship[/W]                 - Afficher les infos du vaisseau
  [W]lancer [competence][/W]            - Lancer les dés (Daggerheart 2d12)
  [W]deplacer [sx] [sy] [sz][/W]        - Déplacer (conventionnel) vers secteur
  [W]saut [sx] [sy] [sz][/W]            - Saut hyperespace vers secteur
  [W]scan[/W]                           - Scanner zone (progressif, 1 PA)
  [W]carte, map[/W]                     - Afficher carte des systèmes découverts

[C]ECONOMIE & RESSOURCES:[/C]
  [W]scan-planete, scanp [nom][/W]      - Scanner gisements d'une planete
  [W]extraire, mine [id] [quantite][/W] - Extraire ressources
  [W]inventaire, inv[/W]                - Afficher inventaire du vaisseau

[C]MARCHES:[/C]
  [W]marche, market[/W]                 - Voir le marche local
  [W]prix, prices [ressource][/W]       - Voir les prix (ou tous)
  [W]acheter, buy [code] [qte][/W]      - Acheter des ressources
  [W]vendre, sell [code] [qte][/W]      - Vendre des ressources

[C]FABRICATION:[/C]
  [W]recettes, recipes [cat][/W]        - Voir les recettes (ou par categorie)
  [W]fabriquer, craft [code] [n][/W]    - Fabriquer une recette (x n fois)

[C]COMBAT:[/C]
  [W]armes, weapons[/W]                 - Voir les armes disponibles
  [W]boucliers, shields[/W]             - Voir les boucliers disponibles
  [W]equiper, equip [type] [code] [slot][/W] - Equiper arme/bouclier
  [W]etat-combat, combat[/W]            - Voir etat combat du vaisseau
  [W]reparer, repair [quantite][/W]     - Reparer la coque
  [W]scanner-ennemis, scane[/W]         - Scanner les ennemis proches
  [W]ennemis, enemies[/W]               - Voir les ennemis détectés
  [W]attaquer, attack [id][/W]          - Attaquer un ennemi
  [W]fuir, flee[/W]                     - Tenter de fuir le combat

[C]MISSIONS:[/C]
  [W]missions, quests[/W]               - Voir les missions disponibles
  [W]mission-accepter, accept [id][/W]  - Accepter une mission
  [W]mission-rendre, complete [id][/W]  - Rendre une mission
  [W]mission-abandonner, abandon [id][/W] - Abandonner une mission
  [W]factions[/W]                       - Voir les factions et réputations
  [W]reputation, rep[/W]                - Voir votre réputation

[C]STATIONS:[/C]
  [W]arrimer, dock [station_id][/W]     - Arrimer à une station
  [W]desarrimer, undock[/W]             - Désarrimer de la station
  [W]transborder, board-station[/W]     - Transborder vers la station
  [W]embarquer, board-ship[/W]          - Embarquer dans le vaisseau
  [W]garage[/W]                         - Accéder au garage
  [W]comptoirs, hub[/W]                 - Accéder aux comptoirs
  [W]hopital, hospital[/W]              - Accéder à l'hôpital
  [W]industrie, industry[/W]            - Accéder à l'industrie
  [W]ravitailler, refuel [quantite][/W] - Ravitailler le vaisseau
  [W]recharger, reload [#PA|full][/W]   - Recharger l'énergie (#PA ou 'full')
";

        // Ajouter les commandes admin si l'utilisateur est admin
        if ($isAdmin) {
            $help .= "[E][ADMIN] COMMANDES D'ADMINISTRATION:[/E]
  [E]/adm scan[/E]                          - Scanner avec infos avancées
  [E]/adm mv perso <id> vaisseau <id>[/E]   - Placer personnage dans vaisseau
  [E]/adm mv perso <id> station <id>[/E]    - Placer personnage dans station
  [E]/adm mv vaisseau <id> station <id>[/E] - Amarrer vaisseau à station
  [E]/adm mv vaisseau <id> <x> <y> <z>[/E] - Téléporter vaisseau (secteur)
  [E]/adm tp <sx> <sy> <sz>[/E]             - Téléporter personnage (sans énergie)
  [E]/adm give pa <quantite>[/E]            - Donner des PA
  [E]/adm give credits <quantite>[/E]       - Donner des crédits
  [E]/adm info perso <id>[/E]               - Info détaillée personnage
  [E]/adm info vaisseau <id>[/E]            - Info détaillée vaisseau
  [E]/adm list persos[/E]                   - Liste tous les personnages
  [E]/adm list vaisseaux[/E]                - Liste tous les vaisseaux
  [E]/adm list comptes [search][/E]         - Liste/recherche des comptes
  [E]/adm print OBJECTS[/E]                 - Liste des objets interrogeables
  [E]/adm print PJ[/E]                      - Debug personnage actuel
  [E]/adm print SHIP[/E]                    - Debug vaisseau actuel
  [E]/adm print <Model> <id>[/E]            - Debug objet par modèle
  [E]/adm su <compte_id>[/E]                - Se substituer à un compte
  [E]/adm su back[/E]                       - Revenir au compte admin original
";
        }

        return [
            'success' => true,
            'message' => $help,
        ];
    }

    /**
     * Traitement des commandes d'administration
     */
    private function processAdminCommand(string $command, Personnage $personnage): array
    {
        // Vérifier que l'utilisateur est admin
        if (!$personnage->compte->is_admin) {
            return [
                'success' => false,
                'message' => '[ERREUR] Accès refusé. Commandes /adm réservées aux administrateurs.',
            ];
        }

        $parts = preg_split('/\s+/', trim($command));
        // Retirer le /adm pour analyser la sous-commande
        array_shift($parts); // Enlève "/adm"
        $subCommand = strtolower($parts[0] ?? '');

        return match ($subCommand) {
            'scan' => $this->adminScan($personnage),
            'mv', 'move' => $this->adminMove($personnage, $parts),
            'tp', 'teleport' => $this->adminTeleport($personnage, $parts),
            'give' => $this->adminGive($personnage, $parts),
            'recharger', 'recharge' => $this->adminRecharger($personnage, $parts),
            'info' => $this->adminInfo($personnage, $parts),
            'list' => $this->adminList($personnage, $parts),
            'print', 'dump' => $this->adminPrint($personnage, $parts),
            'su', 'switch' => $this->adminSwitchUser($personnage, $parts),
            default => [
                'success' => false,
                'message' => "[ADMIN] Sous-commande inconnue: {$subCommand}\nTapez 'help' pour voir les commandes admin.",
            ],
        };
    }

    /**
     * [ADMIN] Scanner avec informations détaillées sur la détection
     */
    private function adminScan(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau || !$vaisseau->objetSpatial) {
            return ['success' => false, 'message' => '[ERREUR] Aucun vaisseau actif.'];
        }

        $result = $this->scanSystems($personnage, ['scan']);

        // Ajouter des informations admin supplémentaires
        $systemes = SystemeStellaire::all();
        $position = $vaisseau->objetSpatial;

        $adminInfo = "\n\n[ADMIN] INFORMATIONS DÉTECTION:\n";
        $adminInfo .= "Position actuelle: ({$position->secteur_x}, {$position->secteur_y}, {$position->secteur_z})\n";
        $adminInfo .= "Systèmes proches de la détection:\n\n";

        foreach ($systemes->take(10) as $systeme) {
            $distance = sqrt(
                pow($systeme->secteur_x - $position->secteur_x, 2) +
                pow($systeme->secteur_y - $position->secteur_y, 2) +
                pow($systeme->secteur_z - $position->secteur_z, 2)
            );

            $seuil = $this->calculerSeuilDetection($distance);
            $dejaDecouvert = Decouverte::where('personnage_id', $personnage->id)
                ->where('systeme_stellaire_id', $systeme->id)
                ->exists();

            if (!$dejaDecouvert && $distance < 50) {
                $adminInfo .= sprintf(
                    "  %s (%.2f AL) - Seuil: %d - %s\n",
                    $systeme->nom,
                    $distance,
                    $seuil,
                    $seuil < 100 ? "PROCHE DÉTECTION!" : "Trop loin"
                );
            }
        }

        $result['message'] .= $adminInfo;
        return $result;
    }

    /**
     * [ADMIN] Déplacer objets (personnage, vaisseau)
     */
    private function adminMove(Personnage $personnage, array $parts): array
    {
        // Format: /adm mv <type> <id> <destination> [<dest_id>|<x> <y> <z>]
        $type = strtolower($parts[1] ?? '');
        $id = intval($parts[2] ?? 0);
        $destination = strtolower($parts[3] ?? '');

        if ($type === 'perso' || $type === 'personnage') {
            return $this->adminMovePersonnage($id, $destination, array_slice($parts, 4));
        } elseif ($type === 'vaisseau' || $type === 'ship') {
            return $this->adminMoveVaisseau($id, $destination, array_slice($parts, 4));
        }

        return [
            'success' => false,
            'message' => "[ADMIN] Usage: /adm mv <perso|vaisseau> <id> <destination> [params]\n" .
                        "Exemples:\n" .
                        "  /adm mv perso 1 vaisseau 5\n" .
                        "  /adm mv perso 1 station 3\n" .
                        "  /adm mv vaisseau 2 station 3\n" .
                        "  /adm mv vaisseau 2 10 20 5  (secteur)",
        ];
    }

    private function adminMovePersonnage(int $persoId, string $destination, array $params): array
    {
        $perso = Personnage::find($persoId);
        if (!$perso) {
            return ['success' => false, 'message' => "[ADMIN] Personnage #{$persoId} introuvable."];
        }

        if ($destination === 'vaisseau' || $destination === 'ship') {
            $vaisseauId = intval($params[0] ?? 0);
            $vaisseau = \App\Models\Vaisseau::find($vaisseauId);
            if (!$vaisseau) {
                return ['success' => false, 'message' => "[ADMIN] Vaisseau #{$vaisseauId} introuvable."];
            }

            $perso->dans_vaisseau_id = $vaisseau->id;
            $perso->dans_station_id = null;
            $perso->save();

            return [
                'success' => true,
                'message' => "[ADMIN] {$perso->nom} placé dans {$vaisseau->nom}.",
            ];
        } elseif ($destination === 'station') {
            $stationId = intval($params[0] ?? 0);
            $station = \App\Models\Station::find($stationId);
            if (!$station) {
                return ['success' => false, 'message' => "[ADMIN] Station #{$stationId} introuvable."];
            }

            $perso->dans_station_id = $station->id;
            $perso->dans_vaisseau_id = null;
            $perso->save();

            return [
                'success' => true,
                'message' => "[ADMIN] {$perso->nom} placé dans {$station->nom}.",
            ];
        }

        return ['success' => false, 'message' => "[ADMIN] Destination invalide: {$destination}"];
    }

    private function adminMoveVaisseau(int $vaisseauId, string $destination, array $params): array
    {
        $vaisseau = \App\Models\Vaisseau::find($vaisseauId);
        if (!$vaisseau || !$vaisseau->objetSpatial) {
            return ['success' => false, 'message' => "[ADMIN] Vaisseau #{$vaisseauId} introuvable."];
        }

        if ($destination === 'station') {
            $stationId = intval($params[0] ?? 0);
            $station = \App\Models\Station::find($stationId);
            if (!$station) {
                return ['success' => false, 'message' => "[ADMIN] Station #{$stationId} introuvable."];
            }

            $vaisseau->amarree_station_id = $station->id;
            $vaisseau->save();

            // Mettre à jour position objet spatial
            $systeme = $station->systemeStellaire;
            $objet = $vaisseau->objetSpatial;
            $objet->secteur_x = $systeme->secteur_x;
            $objet->secteur_y = $systeme->secteur_y;
            $objet->secteur_z = $systeme->secteur_z;
            $objet->position_x = $systeme->position_x;
            $objet->position_y = $systeme->position_y;
            $objet->position_z = $systeme->position_z;
            $objet->save();

            return [
                'success' => true,
                'message' => "[ADMIN] {$vaisseau->nom} amarré à {$station->nom}.",
            ];
        } else {
            // Téléportation vers secteur (x y z)
            $x = intval($destination);
            $y = intval($params[0] ?? 0);
            $z = intval($params[1] ?? 0);

            $objet = $vaisseau->objetSpatial;
            $objet->secteur_x = $x;
            $objet->secteur_y = $y;
            $objet->secteur_z = $z;
            $objet->save();

            $vaisseau->amarree_station_id = null;
            $vaisseau->save();

            return [
                'success' => true,
                'message' => "[ADMIN] {$vaisseau->nom} téléporté en secteur ({$x}, {$y}, {$z}).",
            ];
        }
    }

    /**
     * [ADMIN] Téléporter le personnage actuel
     */
    private function adminTeleport(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau || !$vaisseau->objetSpatial) {
            return ['success' => false, 'message' => '[ADMIN] Aucun vaisseau actif.'];
        }

        $sx = intval($parts[1] ?? 0);
        $sy = intval($parts[2] ?? 0);
        $sz = intval($parts[3] ?? 0);

        $objet = $vaisseau->objetSpatial;
        $objet->secteur_x = $sx;
        $objet->secteur_y = $sy;
        $objet->secteur_z = $sz;
        $objet->save();

        $vaisseau->amarree_station_id = null;
        $vaisseau->save();

        return [
            'success' => true,
            'message' => "[ADMIN] Téléportation vers secteur ({$sx}, {$sy}, {$sz}).\nPas de coût énergétique.",
        ];
    }

    /**
     * [ADMIN] Donner des ressources au personnage
     */
    private function adminGive(Personnage $personnage, array $parts): array
    {
        $type = strtolower($parts[1] ?? '');
        $quantite = intval($parts[2] ?? 0);

        if ($quantite <= 0) {
            return ['success' => false, 'message' => '[ADMIN] Quantité invalide.'];
        }

        if ($type === 'pa') {
            $personnage->points_action = min(
                $personnage->points_action + $quantite,
                $personnage->max_points_action
            );
            $personnage->save();

            return [
                'success' => true,
                'message' => "[ADMIN] +{$quantite} PA donnés. Total: {$personnage->points_action}/{$personnage->max_points_action}",
            ];
        } elseif ($type === 'credits' || $type === 'argent') {
            // TODO: Ajouter système de crédits au personnage
            return [
                'success' => false,
                'message' => '[ADMIN] Système de crédits pas encore implémenté.',
            ];
        }

        return [
            'success' => false,
            'message' => "[ADMIN] Type invalide: {$type}\nTypes disponibles: pa, credits",
        ];
    }

    /**
     * [ADMIN] Informations détaillées
     */
    private function adminInfo(Personnage $personnage, array $parts): array
    {
        $type = strtolower($parts[1] ?? '');
        $id = intval($parts[2] ?? 0);

        if ($type === 'perso' || $type === 'personnage') {
            $perso = Personnage::with(['compte', 'vaisseauActif'])->find($id);
            if (!$perso) {
                return ['success' => false, 'message' => "[ADMIN] Personnage #{$id} introuvable."];
            }

            $msg = "[ADMIN] PERSONNAGE #{$perso->id}\n";
            $msg .= "Nom: {$perso->nom} {$perso->prenom}\n";
            $msg .= "Compte: {$perso->compte->adresse_mail} (ID: {$perso->compte_id})\n";
            $msg .= "Niveau: {$perso->niveau} | XP: {$perso->experience}\n";
            $msg .= "PA: {$perso->points_action}/{$perso->max_points_action}\n";
            $msg .= "Vaisseau actif: " . ($perso->vaisseauActif ? "{$perso->vaisseauActif->nom} (ID: {$perso->vaisseau_actif_id})" : "Aucun") . "\n";
            $msg .= "Dans station: " . ($perso->dans_station_id ?? 'Non') . "\n";
            $msg .= "Dans vaisseau: " . ($perso->dans_vaisseau_id ?? 'Non') . "\n";

            return ['success' => true, 'message' => $msg];
        } elseif ($type === 'vaisseau' || $type === 'ship') {
            $vaisseau = \App\Models\Vaisseau::with(['proprietaire', 'objetSpatial'])->find($id);
            if (!$vaisseau) {
                return ['success' => false, 'message' => "[ADMIN] Vaisseau #{$id} introuvable."];
            }

            $msg = "[ADMIN] VAISSEAU #{$vaisseau->id}\n";
            $msg .= "Nom: {$vaisseau->nom}\n";
            $msg .= "Modèle: {$vaisseau->modele}\n";
            $msg .= "Propriétaire: " . ($vaisseau->proprietaire ? "{$vaisseau->proprietaire->nom} (ID: {$vaisseau->proprietaire_id})" : "Aucun") . "\n";
            if ($vaisseau->objetSpatial) {
                $obj = $vaisseau->objetSpatial;
                $msg .= "Position: Secteur ({$obj->secteur_x}, {$obj->secteur_y}, {$obj->secteur_z})\n";
                $msg .= "          Absolue ({$obj->position_x}, {$obj->position_y}, {$obj->position_z})\n";
            }
            $msg .= "Amarré: " . ($vaisseau->amarree_station_id ? "Station #{$vaisseau->amarree_station_id}" : "Non") . "\n";

            return ['success' => true, 'message' => $msg];
        }

        return [
            'success' => false,
            'message' => "[ADMIN] Usage: /adm info <perso|vaisseau> <id>",
        ];
    }

    /**
     * [ADMIN] Lister objets
     */
    private function adminList(Personnage $personnage, array $parts): array
    {
        $type = strtolower($parts[1] ?? '');

        if ($type === 'persos' || $type === 'personnages') {
            $persos = Personnage::with('compte')->orderBy('id')->get();

            $msg = "[ADMIN] LISTE DES PERSONNAGES (" . $persos->count() . "):\n\n";
            foreach ($persos as $p) {
                $msg .= sprintf(
                    "ID %d: %s %s (Compte: %s) - Niveau %d\n",
                    $p->id,
                    $p->nom,
                    $p->prenom ?? '',
                    $p->compte->adresse_mail ?? 'N/A',
                    $p->niveau
                );
            }

            return ['success' => true, 'message' => $msg];
        } elseif ($type === 'vaisseaux' || $type === 'ships') {
            $vaisseaux = \App\Models\Vaisseau::with('proprietaire')->orderBy('id')->get();

            $msg = "[ADMIN] LISTE DES VAISSEAUX (" . $vaisseaux->count() . "):\n\n";
            foreach ($vaisseaux as $v) {
                $msg .= sprintf(
                    "ID %d: %s (%s) - Propriétaire: %s\n",
                    $v->id,
                    $v->nom,
                    $v->modele,
                    $v->proprietaire ? $v->proprietaire->nom : 'Aucun'
                );
            }

            return ['success' => true, 'message' => $msg];
        } elseif ($type === 'comptes' || $type === 'accounts') {
            $search = $parts[2] ?? '';

            $query = Compte::with(['personnagePrincipal']);

            // Si recherche fournie, filtrer
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('adresse_mail', 'LIKE', "%{$search}%")
                      ->orWhere('nom', 'LIKE', "%{$search}%")
                      ->orWhere('prenom', 'LIKE', "%{$search}%");
                });
            }

            $comptes = $query->orderBy('id')->get();

            $msg = "[ADMIN] LISTE DES COMPTES";
            if ($search) {
                $msg .= " (recherche: '{$search}')";
            }
            $msg .= " (" . $comptes->count() . "):\n\n";

            foreach ($comptes as $c) {
                $persoInfo = '';
                if ($c->personnagePrincipal) {
                    $persoInfo = " - Perso: {$c->personnagePrincipal->nom}";
                }

                $adminBadge = $c->is_admin ? ' [ADMIN]' : '';

                $msg .= sprintf(
                    "ID %d: %s%s%s\n",
                    $c->id,
                    $c->adresse_mail,
                    $adminBadge,
                    $persoInfo
                );
            }

            return ['success' => true, 'message' => $msg];
        }

        return [
            'success' => false,
            'message' => "[ADMIN] Usage: /adm list <persos|vaisseaux|comptes> [recherche]",
        ];
    }

    /**
     * [ADMIN] Debug/print objets Laravel (dump)
     */
    private function adminPrint(Personnage $personnage, array $parts): array
    {
        $target = strtoupper($parts[1] ?? '');

        // Liste des objets disponibles
        if ($target === 'OBJECTS' || $target === 'HELP') {
            return [
                'success' => true,
                'message' => "[ADMIN] OBJETS INTERROGEABLES:\n\n" .
                    "Alias rapides:\n" .
                    "  PJ, PERSO           - Personnage actuel\n" .
                    "  SHIP, VAISSEAU      - Vaisseau actuel\n" .
                    "  COMPTE              - Compte actuel\n\n" .
                    "Modèles Laravel (avec ID):\n" .
                    "  Personnage <id>     - Ex: /adm print Personnage 1\n" .
                    "  Vaisseau <id>       - Ex: /adm print Vaisseau 2\n" .
                    "  Station <id>        - Ex: /adm print Station 1\n" .
                    "  SystemeStellaire <id> - Ex: /adm print SystemeStellaire 1\n" .
                    "  Planete <id>        - Ex: /adm print Planete 5\n" .
                    "  ObjetSpatial <id>   - Ex: /adm print ObjetSpatial 3\n" .
                    "  Ressource <id>      - Ex: /adm print Ressource 1\n" .
                    "  Arme <id>           - Ex: /adm print Arme 1\n" .
                    "  Bouclier <id>       - Ex: /adm print Bouclier 1\n" .
                    "  Combat <id>         - Ex: /adm print Combat 1\n" .
                    "  Ennemi <id>         - Ex: /adm print Ennemi 1\n" .
                    "  Gisement <id>       - Ex: /adm print Gisement 1\n" .
                    "  Marche <id>         - Ex: /adm print Marche 1\n" .
                    "  Recette <id>        - Ex: /adm print Recette 1\n\n" .
                    "Usage:\n" .
                    "  /adm print OBJECTS          - Affiche cette aide\n" .
                    "  /adm print PJ               - Debug personnage actuel\n" .
                    "  /adm print SHIP             - Debug vaisseau actuel\n" .
                    "  /adm print <Model> <id>     - Debug objet spécifique\n",
            ];
        }

        // Alias rapides
        $object = null;
        $objectName = '';

        if (in_array($target, ['PJ', 'PERSO', 'PERSONNAGE'])) {
            $object = $personnage->load(['compte', 'vaisseauActif', 'vaisseauActif.objetSpatial']);
            $objectName = "Personnage #{$personnage->id} (actuel)";
        } elseif (in_array($target, ['SHIP', 'VAISSEAU'])) {
            $vaisseau = $personnage->vaisseauActif;
            if (!$vaisseau) {
                return ['success' => false, 'message' => '[ADMIN] Aucun vaisseau actif.'];
            }
            $object = $vaisseau->load(['proprietaire', 'objetSpatial', 'stationAmarrage']);
            $objectName = "Vaisseau #{$vaisseau->id} (actuel)";
        } elseif ($target === 'COMPTE') {
            $object = $personnage->compte->load(['personnages', 'personnagePrincipal']);
            $objectName = "Compte #{$personnage->compte->id} (actuel)";
        } else {
            // Interrogation par modèle
            $modelName = $target;
            $id = intval($parts[2] ?? 0);

            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => "[ADMIN] Usage: /adm print <Model> <id>\nEx: /adm print Personnage 1\n\nTapez '/adm print OBJECTS' pour voir la liste des objets.",
                ];
            }

            // Mapper les modèles
            $modelMap = [
                'PERSONNAGE' => Personnage::class,
                'VAISSEAU' => \App\Models\Vaisseau::class,
                'STATION' => \App\Models\Station::class,
                'SYSTEMESTELLAIRE' => SystemeStellaire::class,
                'PLANETE' => \App\Models\Planete::class,
                'OBJETSPATIAL' => \App\Models\ObjetSpatial::class,
                'RESSOURCE' => Ressource::class,
                'ARME' => Arme::class,
                'BOUCLIER' => Bouclier::class,
                'COMBAT' => Combat::class,
                'ENNEMI' => Ennemi::class,
                'GISEMENT' => Gisement::class,
                'MARCHE' => Marche::class,
                'RECETTE' => Recette::class,
            ];

            if (!isset($modelMap[$modelName])) {
                return [
                    'success' => false,
                    'message' => "[ADMIN] Modèle inconnu: {$modelName}\nTapez '/adm print OBJECTS' pour voir la liste.",
                ];
            }

            $modelClass = $modelMap[$modelName];
            $object = $modelClass::find($id);

            if (!$object) {
                return [
                    'success' => false,
                    'message' => "[ADMIN] {$modelName} #{$id} introuvable.",
                ];
            }

            $objectName = "{$modelName} #{$id}";
        }

        // Formater l'objet pour affichage
        return [
            'success' => true,
            'message' => $this->formatObjectDebug($object, $objectName),
        ];
    }

    /**
     * Formater un objet Laravel pour affichage debug
     */
    private function formatObjectDebug($object, string $name): string
    {
        $msg = "[ADMIN] DEBUG OBJET: {$name}\n";
        $msg .= str_repeat('=', 60) . "\n\n";

        // Classe
        $msg .= "Classe: " . get_class($object) . "\n\n";

        // Attributs (colonnes de base)
        $msg .= "ATTRIBUTS:\n";
        $attributes = $object->getAttributes();
        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                $displayValue = 'null';
            } elseif (is_bool($value)) {
                $displayValue = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $displayValue = json_encode($value);
            } else {
                $displayValue = (string) $value;
            }

            // Limiter la longueur d'affichage
            if (strlen($displayValue) > 100) {
                $displayValue = substr($displayValue, 0, 97) . '...';
            }

            $msg .= sprintf("  %-25s %s\n", $key . ':', $displayValue);
        }

        // Relations chargées
        $relations = $object->getRelations();
        if (!empty($relations)) {
            $msg .= "\nRELATIONS CHARGEES:\n";
            foreach ($relations as $relationName => $relationValue) {
                if (is_null($relationValue)) {
                    $msg .= sprintf("  %-25s null\n", $relationName . ':');
                } elseif ($relationValue instanceof \Illuminate\Database\Eloquent\Collection) {
                    $count = $relationValue->count();
                    $msg .= sprintf("  %-25s Collection (%d items)\n", $relationName . ':', $count);
                } elseif ($relationValue instanceof \Illuminate\Database\Eloquent\Model) {
                    $relClass = class_basename(get_class($relationValue));
                    $relId = $relationValue->id ?? 'N/A';
                    $msg .= sprintf("  %-25s %s #%s\n", $relationName . ':', $relClass, $relId);
                } else {
                    $msg .= sprintf("  %-25s %s\n", $relationName . ':', gettype($relationValue));
                }
            }
        }

        // Timestamps
        if (method_exists($object, 'getCreatedAtColumn') && $object->created_at) {
            $msg .= "\nTIMESTAMPS:\n";
            $msg .= "  created_at:              {$object->created_at}\n";
            if ($object->updated_at) {
                $msg .= "  updated_at:              {$object->updated_at}\n";
            }
        }

        $msg .= "\n" . str_repeat('=', 60) . "\n";
        $msg .= "Tapez '/adm print OBJECTS' pour voir les autres objets disponibles.\n";

        return $msg;
    }

    /**
     * [ADMIN] Changer de compte (substitute user)
     */
    private function adminSwitchUser(Personnage $personnage, array $parts): array
    {
        $target = strtolower($parts[1] ?? '');

        // Retour au compte admin original
        if ($target === 'back' || $target === 'retour') {
            $originalCompteId = session('admin_original_compte_id');

            if (!$originalCompteId) {
                return [
                    'success' => false,
                    'message' => "[ADMIN] Aucun compte original en mémoire. Vous êtes déjà sur votre compte.",
                ];
            }

            $originalCompte = Compte::find($originalCompteId);
            if (!$originalCompte) {
                return [
                    'success' => false,
                    'message' => "[ADMIN] Compte original #{$originalCompteId} introuvable.",
                ];
            }

            // Restaurer le compte original
            session(['compte_id' => $originalCompteId]);
            session(['perso_principal' => $originalCompte->personnage_principal_id]);
            session()->forget('admin_original_compte_id');

            return [
                'success' => true,
                'message' => "[ADMIN] Retour au compte: {$originalCompte->adresse_mail}\n" .
                            "Vous avez retrouvé votre compte administrateur.",
            ];
        }

        // Substitution vers un autre compte
        $compteId = intval($target);

        if ($compteId <= 0) {
            return [
                'success' => false,
                'message' => "[ADMIN] Usage: /adm su <compte_id>  ou  /adm su back\n" .
                            "Utilisez '/adm list comptes [recherche]' pour trouver un compte.",
            ];
        }

        $targetCompte = Compte::with('personnagePrincipal')->find($compteId);

        if (!$targetCompte) {
            return [
                'success' => false,
                'message' => "[ADMIN] Compte #{$compteId} introuvable.",
            ];
        }

        // Sauvegarder le compte admin actuel si pas déjà sauvegardé
        if (!session()->has('admin_original_compte_id')) {
            session(['admin_original_compte_id' => $personnage->compte_id]);
        }

        // Basculer vers le compte cible
        session(['compte_id' => $targetCompte->id]);

        // Définir le personnage principal si disponible
        if ($targetCompte->personnage_principal_id) {
            session(['perso_principal' => $targetCompte->personnage_principal_id]);
            $persoInfo = $targetCompte->personnagePrincipal
                ? " - Personnage: {$targetCompte->personnagePrincipal->nom}"
                : "";
        } else {
            session()->forget('perso_principal');
            $persoInfo = " - ATTENTION: Aucun personnage principal défini!";
        }

        // Maintenir le flag admin dans la session
        session(['is_admin' => true]);

        return [
            'success' => true,
            'message' => "[ADMIN] Substitution vers compte #{$compteId}: {$targetCompte->adresse_mail}{$persoInfo}\n" .
                        "Vous gardez vos privilèges admin.\n" .
                        "Tapez '/adm su back' pour revenir à votre compte.",
        ];
    }

    /**
     * Calculer le seuil de détection selon la distance (pour admin scan)
     */
    private function calculerSeuilDetection(float $distance): int
    {
        return intval(max(10, min(100, $distance * 2)));
    }

    private function showStatus(Personnage $personnage): array
    {
        // Info prochaine récupération PA
        $prochaine_recup = '';
        if ($personnage->points_action < $personnage->max_points_action && $personnage->derniere_recuperation_pa) {
            $delai = config('game.pa.recuperation_delai', 60);
            $minutes_restantes = $delai - (now()->diffInMinutes($personnage->derniere_recuperation_pa) % $delai);
            $unite = $delai >= 60 ? 'h' : 'min';
            $temps = $delai >= 60 ? round($minutes_restantes / 60, 1) : $minutes_restantes;
            $prochaine_recup = "\nProchain PA dans: {$temps} {$unite}";
        }

        return [
            'success' => true,
            'message' => "
=== STATUT PERSONNAGE ===
Nom: {$personnage->nom} {$personnage->prenom}
Niveau: {$personnage->niveau}
XP: {$personnage->experience}
PA: {$personnage->points_action} / {$personnage->max_points_action} (1 PA/heure){$prochaine_recup}

TRAITS:
  Agilité: {$personnage->agilite}
  Force: {$personnage->force}
  Finesse: {$personnage->finesse}
  Instinct: {$personnage->instinct}
  Présence: {$personnage->presence}
  Savoir: {$personnage->savoir}

JETONS:
  Hope: {$personnage->jetons_hope}
  Fear: {$personnage->jetons_fear}
            ",
        ];
    }

    private function showPosition(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $os = $vaisseau->objetSpatial;
        return [
            'success' => true,
            'message' => "
=== POSITION ===
Secteur: ({$os->secteur_x}, {$os->secteur_y}, {$os->secteur_z})
Position: ({$os->position_x}, {$os->position_y}, {$os->position_z})
            ",
        ];
    }

    private function showShip(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        return [
            'success' => true,
            'message' => "
=== VAISSEAU ===
Modèle: {$vaisseau->modele}
Énergie: {$vaisseau->energie_actuelle} / {$vaisseau->reserve} UE
Vitesse Conv.: {$vaisseau->vitesse_conventionnelle}
Vitesse Saut: {$vaisseau->vitesse_saut}
Résistance: {$vaisseau->objetSpatial->resistance}%
            ",
        ];
    }

    private function rollDice(Personnage $personnage, array $parts): array
    {
        $competence = $parts[1] ?? '';
        $niveau = 0; // TODO: récupérer niveau compétence

        $result = $personnage->lancerDes($niveau);
        $personnage->save(); // Sauvegarder les jetons

        $message = "
=== LANCER DE DÉS ===
Hope (d12): {$result['hope']}
Fear (d12): {$result['fear']}
Total: {$result['total']}
";

        if ($result['critique']) {
            $message .= "\n🎉 CRITIQUE ! Succès avec Hope !";
        } elseif ($result['hope'] > $result['fear']) {
            $message .= "\n✨ +1 jeton Hope";
        } elseif ($result['fear'] > $result['hope']) {
            $message .= "\n⚠️ +1 jeton Fear";
        }
        return ['succes' =>true, "message" => $message];
    }

    private function moveShip(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $objetSpatial = $vaisseau->objetSpatial;

        // Parser coordonnées:
        // - 3 params: deplacer px py pz (déplacement local dans le secteur actuel)
        // - 6 params: deplacer sx sy sz px py pz (déplacement absolu)
        if (count($parts) < 4) {
            return [
                'success' => false,
                'message' => "Usage:\n  deplacer [x] [y] [z] - Déplacement local (UA)\n  deplacer [sx] [sy] [sz] [px] [py] [pz] - Déplacement absolu\nExemple: deplacer 1 0 0 (se déplacer de 1 UA en X)",
            ];
        }

        if (count($parts) == 4) {
            // Mode local: déplacer dans le secteur actuel
            $secteur_x = $objetSpatial->secteur_x;
            $secteur_y = $objetSpatial->secteur_y;
            $secteur_z = $objetSpatial->secteur_z;
            $position_x = (float)($parts[1] ?? 0);
            $position_y = (float)($parts[2] ?? 0);
            $position_z = (float)($parts[3] ?? 0);
        } else {
            // Mode absolu: changer de secteur + position
            $secteur_x = (int)($parts[1] ?? 0);
            $secteur_y = (int)($parts[2] ?? 0);
            $secteur_z = (int)($parts[3] ?? 0);
            $position_x = (float)($parts[4] ?? 0);
            $position_y = (float)($parts[5] ?? 0);
            $position_z = (float)($parts[6] ?? 0);
        }

        // Exécuter déplacement
        $result = $vaisseau->deplacerVers(
            $secteur_x,
            $secteur_y,
            $secteur_z,
            $position_x,
            $position_y,
            $position_z,
            'conventionnel'
        );

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => "Déplacement impossible: {$result['erreur']}\nÉnergie requise: {$result['requis']} UE, manquant: {$result['manquant']} UE",
            ];
        }

        // Consommer PA
        $pa_requis = $result['pa'];
        if (!$personnage->consommerPA($pa_requis)) {
            // Rollback position (annuler le déplacement)
            return [
                'success' => false,
                'message' => "PA insuffisants ! Requis: {$pa_requis} PA, disponible: {$personnage->points_action} PA",
            ];
        }

        // IMPORTANT: Réinitialiser le scan après déplacement
        $vaisseau->reinitialiserScan();

        $personnage->save();
        $vaisseau->save();

        return [
            'success' => true,
            'message' => "
=== DÉPLACEMENT CONVENTIONNEL ===
Distance: {$result['distance']} UC
Énergie consommée: {$result['consommation']} UE
PA consommés: {$pa_requis}
Énergie restante: {$result['energie_restante']} UE
PA restants: {$personnage->points_action} / {$personnage->max_points_action}
Nouvelle position: Secteur ({$secteur_x}, {$secteur_y}, {$secteur_z}) + ({$position_x}, {$position_y}, {$position_z})
            ",
        ];
    }

    private function moveShipRelative(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $objetSpatial = $vaisseau->objetSpatial;

        // Parser déplacement relatif: bouger dx dy dz
        if (count($parts) < 4) {
            return [
                'success' => false,
                'message' => "Usage: bouger [dx] [dy] [dz]\nDéplacement relatif depuis la position actuelle\nExemple: bouger -1 0 0 (se déplacer de -1 UA en X)",
            ];
        }

        $dx = (float)($parts[1] ?? 0);
        $dy = (float)($parts[2] ?? 0);
        $dz = (float)($parts[3] ?? 0);

        // Calculer nouvelle position absolue
        $new_x = $objetSpatial->position_x + $dx;
        $new_y = $objetSpatial->position_y + $dy;
        $new_z = $objetSpatial->position_z + $dz;

        // Exécuter déplacement vers la nouvelle position
        $result = $vaisseau->deplacerVers(
            $objetSpatial->secteur_x,
            $objetSpatial->secteur_y,
            $objetSpatial->secteur_z,
            $new_x,
            $new_y,
            $new_z,
            'conventionnel'
        );

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => "Déplacement impossible: {$result['erreur']}\nÉnergie requise: {$result['requis']} UE, manquant: {$result['manquant']} UE",
            ];
        }

        // Consommer PA
        $pa_requis = $result['pa'];
        if (!$personnage->consommerPA($pa_requis)) {
            return [
                'success' => false,
                'message' => "PA insuffisants ! Requis: {$pa_requis} PA, disponible: {$personnage->points_action} PA",
            ];
        }

        // IMPORTANT: Réinitialiser le scan après déplacement
        $vaisseau->reinitialiserScan();

        $personnage->save();
        $vaisseau->save();

        return [
            'success' => true,
            'message' => "
=== DÉPLACEMENT RELATIF ===
Vecteur: ({$dx}, {$dy}, {$dz})
Distance: {$result['distance']} UC
Énergie consommée: {$result['consommation']} UE
PA consommés: {$pa_requis}
Énergie restante: {$result['energie_restante']} UE
PA restants: {$personnage->points_action} / {$personnage->max_points_action}
Nouvelle position: ({$new_x}, {$new_y}, {$new_z})
            ",
        ];
    }

    private function jumpHyperspace(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Parser coordonnées: saut sx sy sz
        if (count($parts) < 4) {
            return [
                'success' => false,
                'message' => "Usage: saut [secteur_x] [secteur_y] [secteur_z]\nExemple: saut 10 5 3",
            ];
        }

        $secteur_x = (float)($parts[1] ?? 0);
        $secteur_y = (float)($parts[2] ?? 0);
        $secteur_z = (float)($parts[3] ?? 0);

        // Exécuter saut HE (toujours position 0,0,0 après saut selon GDD)
        $result = $vaisseau->deplacerVers(
            $secteur_x,
            $secteur_y,
            $secteur_z,
            0,
            0,
            0,
            'hyperespace'
        );

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => "Saut impossible: {$result['erreur']}\nÉnergie requise: {$result['requis']} UE, manquant: {$result['manquant']} UE",
            ];
        }

        // Consommer PA
        $pa_requis = $result['pa'];
        if (!$personnage->consommerPA($pa_requis)) {
            return [
                'success' => false,
                'message' => "PA insuffisants ! Requis: {$pa_requis} PA, disponible: {$personnage->points_action} PA",
            ];
        }

        $personnage->save();
        $vaisseau->save();

        return [
            'success' => true,
            'message' => "
=== SAUT HYPERESPACE ===
Distance: {$result['distance']} secteurs
Énergie consommée: {$result['consommation']} UE
PA consommés: {$pa_requis}
Énergie restante: {$result['energie_restante']} UE
PA restants: {$personnage->points_action} / {$personnage->max_points_action}
Arrivée: Secteur ({$secteur_x}, {$secteur_y}, {$secteur_z})
[Phase d'orientation requise - TODO]
            ",
        ];
    }

    private function scanSystems(Personnage $personnage, array $parts): array
    {
        // Lancer le scan progressif (utilise les capacités du vaisseau)
        $resultat = $personnage->scannerSystemes();

        if (!$resultat['succes']) {
            return [
                'success' => false,
                'message' => $resultat['message'],
            ];
        }

        // Informations du scan
        $scan_info = $resultat['scan_info'];
        $rayon = $resultat['rayon'];

        // Formater résultat
        $message = "\n=== SCAN SPATIAL ===\n";
        $message .= "Portée scanner: {$rayon} AL\n";
        $message .= "Puissance scan: {$scan_info['puissance_totale']}\n";

        // Afficher progression du scan
        if ($scan_info['ancien_niveau'] > 0) {
            $message .= "Scan en cours amélioré: {$scan_info['ancien_niveau']} → {$scan_info['nouveau_niveau']} (+{$scan_info['niveau_apporte']})\n";
        } else {
            $message .= "Nouveau scan démarré: Niveau {$scan_info['nouveau_niveau']}\n";
        }

        $message .= "\n";

        // Afficher UNIQUEMENT les découvertes (brouillard de guerre)
        if (count($resultat['decouvertes']) > 0) {
            $message .= "--- ✓ SYSTÈMES DÉTECTÉS ---\n";

            foreach ($resultat['decouvertes'] as $decouverte) {
                $message .= "\n• {$decouverte['systeme']} ({$decouverte['distance']} AL)\n";
                $message .= "  Jet: {$decouverte['resultat_des']} + {$decouverte['puissance_scan']} = {$decouverte['resultat_total']} / {$decouverte['seuil']}\n";

                $details = $decouverte['details'];
                $message .= "  Type: Étoile {$details['type_etoile']} ({$details['couleur']})\n";

                // Afficher puissance et détectabilité si disponibles
                if (isset($details['puissance']) && $details['puissance']) {
                    $message .= "  Puissance: {$details['puissance']}\n";
                }
                if (isset($details['detectabilite_base']) && $details['detectabilite_base']) {
                    $message .= "  Détectabilité base: " . number_format($details['detectabilite_base'], 2) . "\n";
                }

                $message .= "  Planètes: {$details['nb_planetes']}\n";
            }
        } else {
            // Ne PAS révéler s'il y a d'autres systèmes
            $message .= "Aucun système détecté.\n";
            $message .= "💡 Scannez à nouveau pour améliorer la détection (scan cumulatif).\n";
        }

        $message .= "\n📍 Le scan est réinitialisé si vous vous déplacez.";
        $message .= "\n🗺️  Utilisez 'carte' pour voir tous vos systèmes découverts.";

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    private function showMap(Personnage $personnage): array
    {
        $systemes = $personnage->getSystemesDecouverts();

        if (count($systemes) === 0) {
            return [
                'success' => true,
                'message' => "\n=== CARTE GALACTIQUE ===\nAucun système découvert. Utilisez 'scan' pour explorer l'espace.",
            ];
        }

        $message = "\n=== CARTE GALACTIQUE ===\n";
        $message .= "Systèmes découverts: " . count($systemes) . "\n\n";

        // Obtenir position actuelle pour calculer distances
        $positionActuelle = $personnage->getPositionActuelle();

        foreach ($systemes as $systeme) {
            $message .= "• {$systeme['nom']}\n";
            $message .= "  Secteur: ({$systeme['secteur_x']}, {$systeme['secteur_y']}, {$systeme['secteur_z']})\n";

            if ($positionActuelle) {
                $distance = $personnage->calculerDistance($positionActuelle, [
                    'secteur_x' => $systeme['secteur_x'],
                    'secteur_y' => $systeme['secteur_y'],
                    'secteur_z' => $systeme['secteur_z'],
                    'position_x' => $systeme['position_x'],
                    'position_y' => $systeme['position_y'],
                    'position_z' => $systeme['position_z'],
                ]);
                $message .= "  Distance: " . round($distance, 2) . " AL\n";
            }

            if (isset($systeme['type_etoile'])) {
                $message .= "  Étoile: Type {$systeme['type_etoile']} ({$systeme['couleur']})\n";

                // Afficher puissance et détectabilité si disponibles
                if (isset($systeme['puissance']) && $systeme['puissance']) {
                    $message .= "  Puissance: {$systeme['puissance']}\n";
                }
                if (isset($systeme['detectabilite_base']) && $systeme['detectabilite_base']) {
                    $message .= "  Détectabilité base: " . number_format($systeme['detectabilite_base'], 2) . "\n";
                }
            }

            if (isset($systeme['nb_planetes'])) {
                $message .= "  Planètes: {$systeme['nb_planetes']}";
                if ($systeme['habite']) {
                    $message .= " (système habité)";
                }
                $message .= "\n";
            }

            if (isset($systeme['visite']) && $systeme['visite']) {
                $message .= "  ✓ VISITÉ\n";
            }

            if (isset($systeme['notes'])) {
                $message .= "  Notes: {$systeme['notes']}\n";
            }

            $message .= "\n";
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    // === COMMANDES ÉCONOMIE (PHASE 2) ===

    /**
     * Scanner les gisements d'une planète
     */
    private function scanPlanete(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        if (count($parts) < 2) {
            return [
                'success' => false,
                'message' => "Usage: scan-planete [nom_planete]\nExemple: scan-planete Sol 3",
            ];
        }

        // Récupérer nom planète (peut contenir des espaces)
        $nom_planete = implode(' ', array_slice($parts, 1));

        // Trouver système actuel
        $os = $vaisseau->objetSpatial;
        $systeme = SystemeStellaire::where('secteur_x', $os->secteur_x)
            ->where('secteur_y', $os->secteur_y)
            ->where('secteur_z', $os->secteur_z)
            ->first();

        if (!$systeme) {
            return ['success' => false, 'message' => 'Vous n\'êtes pas dans un système stellaire.'];
        }

        // Trouver planète
        $planete = $systeme->planetes()
            ->where('nom', 'like', "%{$nom_planete}%")
            ->first();

        if (!$planete) {
            $planetes_dispo = $systeme->planetes->pluck('nom')->join(', ');
            return [
                'success' => false,
                'message' => "Planète '{$nom_planete}' non trouvée.\nPlanètes disponibles: {$planetes_dispo}",
            ];
        }

        // Coût en PA
        if ($personnage->points_action < 1) {
            return ['success' => false, 'message' => 'Pas assez de PA (1 requis)'];
        }
        $personnage->consommerPA(1);

        // Scanner gisements
        $puissance_scan = $vaisseau->getPuissanceScanEffective();
        $gisements = $planete->gisements()->where('decouvert', false)->get();

        $detections = [];
        foreach ($gisements as $gisement) {
            // Formule détection: jet + puissance vs seuil basé sur rareté
            $jet = rand(1, 12) + rand(1, 12); // 2d12
            $resultat = $jet + ($puissance_scan / 10);
            $seuil = 150 - $gisement->ressource->rarete; // Plus rare = plus difficile

            if ($resultat >= $seuil) {
                $gisement->update([
                    'decouvert' => true,
                    'decouvert_le' => now(),
                    'decouvert_par' => $personnage->id,
                ]);

                $detections[] = [
                    'id' => $gisement->id,
                    'ressource' => $gisement->ressource->nom,
                    'code' => $gisement->ressource->code,
                    'richesse' => $gisement->richesse,
                    'quantite' => $gisement->quantite_restante,
                ];
            }
        }

        // Récupérer aussi les gisements déjà découverts
        $gisements_connus = $planete->gisements()
            ->where('decouvert', true)
            ->with('ressource')
            ->get();

        $message = "\n=== SCAN GÉOLOGIQUE : {$planete->nom} ===\n";
        $message .= "Type: {$planete->type_planete}\n";

        // Afficher rayon et détectabilité si disponibles
        if ($planete->rayon) {
            $message .= "Rayon: " . number_format($planete->rayon, 2) . " RT (Rayons Terrestres)\n";
        }
        if ($planete->detectabilite_base) {
            $message .= "Détectabilité base: " . number_format($planete->detectabilite_base, 2) . "\n";
        }

        $message .= "Puissance scan: {$puissance_scan}\n\n";

        if (count($detections) > 0) {
            $message .= "--- NOUVEAUX GISEMENTS DÉTECTÉS ---\n";
            foreach ($detections as $d) {
                $message .= "\n• [{$d['id']}] {$d['ressource']} ({$d['code']})\n";
                $message .= "  Richesse: {$d['richesse']}%\n";
                $message .= "  Quantité: " . number_format($d['quantite']) . " unités\n";
            }
        } else {
            $message .= "Aucun nouveau gisement détecté.\n";
        }

        if ($gisements_connus->count() > 0) {
            $message .= "\n--- GISEMENTS CONNUS ---\n";
            foreach ($gisements_connus as $g) {
                $etat = $g->en_exploitation ? ' [EN EXPLOITATION]' : '';
                $message .= "• [{$g->id}] {$g->ressource->nom}: " . number_format($g->quantite_restante) . " unités ({$g->richesse}%){$etat}\n";
            }
        }

        $message .= "\nUtilisez 'extraire [id] [quantité]' pour miner.";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Extraire ressources d'un gisement
     */
    private function extraireRessource(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => "Usage: extraire [gisement_id] [quantité]\nExemple: extraire 5 1000",
            ];
        }

        $gisement_id = (int)$parts[1];
        $quantite = (int)$parts[2];

        if ($quantite <= 0) {
            return ['success' => false, 'message' => 'Quantité invalide'];
        }

        // Trouver gisement
        $gisement = Gisement::with(['ressource', 'planete'])->find($gisement_id);

        if (!$gisement) {
            return ['success' => false, 'message' => "Gisement #{$gisement_id} introuvable"];
        }

        if (!$gisement->decouvert) {
            return ['success' => false, 'message' => 'Ce gisement n\'a pas encore été découvert'];
        }

        // Vérifier qu'on est dans le bon système
        $os = $vaisseau->objetSpatial;
        $systeme_planete = $gisement->planete->systemeStellaire;

        if ($os->secteur_x != $systeme_planete->secteur_x ||
            $os->secteur_y != $systeme_planete->secteur_y ||
            $os->secteur_z != $systeme_planete->secteur_z) {
            return ['success' => false, 'message' => 'Vous devez être dans le système de cette planète'];
        }

        // Vérifier quantité disponible
        if ($gisement->quantite_restante < $quantite) {
            return [
                'success' => false,
                'message' => "Quantité insuffisante. Disponible: " . number_format($gisement->quantite_restante),
            ];
        }

        // Vérifier capacité soute
        if (!$vaisseau->peutCharger($gisement->ressource_id, $quantite)) {
            $capacite = $vaisseau->getCapaciteRestante();
            $poids = $gisement->ressource->poids_unitaire * $quantite;
            return [
                'success' => false,
                'message' => "Capacité soute insuffisante.\nRequis: {$poids}t | Disponible: {$capacite}t",
            ];
        }

        // Coût en PA (1 PA par tranche de 10000)
        $pa_requis = max(1, (int)ceil($quantite / 10000));
        if ($personnage->points_action < $pa_requis) {
            return ['success' => false, 'message' => "Pas assez de PA ({$pa_requis} requis)"];
        }

        // Extraire !
        $quantite_extraite = $gisement->extraire($quantite);
        $vaisseau->ajouterRessource($gisement->ressource_id, $quantite_extraite);
        $personnage->consommerPA($pa_requis);

        $poids_ajoute = $gisement->ressource->poids_unitaire * $quantite_extraite;

        $message = "\n=== EXTRACTION RÉUSSIE ===\n";
        $message .= "Ressource: {$gisement->ressource->nom}\n";
        $message .= "Quantité: " . number_format($quantite_extraite) . " unités\n";
        $message .= "Poids ajouté: {$poids_ajoute}t\n";
        $message .= "PA utilisés: {$pa_requis}\n\n";
        $message .= "Gisement restant: " . number_format($gisement->quantite_restante) . " unités\n";
        $message .= "Capacité soute: " . round($vaisseau->getCapaciteRestante(), 2) . "t";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Afficher inventaire du vaisseau
     */
    private function showInventaire(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $inventaire = $vaisseau->listerInventaire();
        $capacite_totale = $vaisseau->place_soute ?? 1000;
        $poids_total = $vaisseau->getPoidsInventaire();
        $valeur_totale = $vaisseau->getValeurInventaire();

        $message = "\n=== INVENTAIRE VAISSEAU ===\n";
        $message .= "Capacité: " . round($poids_total, 2) . "t / {$capacite_totale}t\n";
        $message .= "Valeur totale: " . number_format($valeur_totale) . " crédits\n\n";

        if (count($inventaire) === 0) {
            $message .= "Soutes vides.\n";
        } else {
            $message .= "--- RESSOURCES ---\n";
            foreach ($inventaire as $item) {
                $message .= "• {$item['nom']} ({$item['code']})\n";
                $message .= "  Quantité: " . number_format($item['quantite']) . "\n";
                $message .= "  Poids: " . round($item['poids'], 2) . "t | Valeur: " . number_format($item['valeur']) . " cr\n";
            }
        }

        return ['success' => true, 'message' => $message];
    }

    // === COMMANDES MARCHÉS (PHASE 2) ===

    /**
     * Afficher le marché local
     */
    private function showMarche(Personnage $personnage): array
    {
        // Vérifier que le personnage est dans une station
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station. Utilisez "transborder" pour entrer dans une station.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->commerciale) {
            return [
                'success' => false,
                'message' => "{$station->nom} n'a pas de marché.",
            ];
        }

        // Charger les produits disponibles avec leurs données de marché
        $marches = \App\Models\MarcheStation::where('station_id', $station->id)
            ->with('produit')
            ->where(function($q) {
                $q->where('disponible_vente', true)
                  ->orWhere('disponible_achat', true);
            })
            ->get();

        if ($marches->isEmpty()) {
            return [
                'success' => true,
                'message' => "\n=== MARCHÉ DE {$station->nom} ===\n\nLe marché est actuellement vide.\n",
            ];
        }

        $message = "\n=== MARCHÉ DE {$station->nom} ===\n\n";
        $message .= "Code       | Produit              | Type        | Achat    | Vente    | Stock    | Éco\n";
        $message .= "-----------|----------------------|-------------|----------|----------|----------|-------------\n";

        foreach ($marches as $marche) {
            $produit = $marche->produit;

            $code = str_pad(strtoupper($produit->code), 10);
            $nom = str_pad(substr($produit->nom, 0, 20), 20);
            $type = str_pad(substr($produit->type, 0, 11), 11);

            // Prix selon disponibilité
            $prixAchat = $marche->disponible_achat
                ? str_pad(number_format($marche->prix_achat_joueur, 0) . '₡', 8)
                : str_pad('--', 8);

            $prixVente = $marche->disponible_vente
                ? str_pad(number_format($marche->prix_vente_joueur, 0) . '₡', 8)
                : str_pad('--', 8);

            $stock = str_pad(number_format($marche->stock_actuel), 8);

            // Indicateur économique
            $eco = match($marche->type_economique) {
                'producteur' => 'PROD ⬇',
                'consommateur' => 'CONSO ⬆',
                'equilibre' => 'ÉQUIL →',
                'transit' => 'TRANSIT',
                default => '',
            };

            $message .= "{$code} | {$nom} | {$type} | {$prixAchat} | {$prixVente} | {$stock} | {$eco}\n";
        }

        $message .= "\n";
        $message .= "💰 Achat = Station achète AU joueur | Vente = Station vend AU joueur\n";
        $message .= "⬇ PROD = Prix bas | ⬆ CONSO = Prix élevé | → ÉQUIL = Prix moyen\n\n";
        $message .= "Commandes:\n";
        $message .= "- 'acheter <code> <quantité>' : Acheter à la station\n";
        $message .= "- 'vendre <code> <quantité>' : Vendre à la station\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Afficher les prix d'un marché
     */
    private function showPrix(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Trouver système actuel
        $os = $vaisseau->objetSpatial;
        $systeme = SystemeStellaire::where('secteur_x', $os->secteur_x)
            ->where('secteur_y', $os->secteur_y)
            ->where('secteur_z', $os->secteur_z)
            ->first();

        if (!$systeme) {
            return ['success' => false, 'message' => 'Vous devez etre dans un systeme stellaire'];
        }

        // Trouver premier marché actif
        $marche = Marche::whereHasMorph('localisation', [
            \App\Models\Planete::class,
        ], function ($query) use ($systeme) {
            $query->where('systeme_stellaire_id', $systeme->id);
        })->where('actif', true)->first();

        if (!$marche) {
            return ['success' => false, 'message' => 'Aucun marche actif dans ce systeme'];
        }

        // Filtre optionnel par ressource
        $filtre = $parts[1] ?? null;

        $ressourcesMarche = $marche->listerRessources();

        if ($filtre) {
            $ressourcesMarche = array_filter($ressourcesMarche, function ($r) use ($filtre) {
                return stripos($r['code'], $filtre) !== false || stripos($r['nom'], $filtre) !== false;
            });
        }

        $message = "\n=== PRIX - {$marche->nom} ===\n\n";
        $message .= "Code       | Ressource           | Achat    | Vente    | Stock\n";
        $message .= "-----------|---------------------|----------|----------|-------\n";

        foreach ($ressourcesMarche as $r) {
            $code = str_pad($r['code'], 10);
            $nom = str_pad(substr($r['nom'], 0, 19), 19);
            $achat = str_pad(number_format($r['prix_achat'], 0), 8);
            $vente = str_pad(number_format($r['prix_vente'], 0), 8);
            $stock = number_format($r['stock']);

            $message .= "{$code} | {$nom} | {$achat} | {$vente} | {$stock}\n";
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Acheter des produits au marché (la station VEND au joueur)
     */
    private function acheterRessource(Personnage $personnage, array $parts): array
    {
        // Vérifier que dans une station
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station avec un marché.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->commerciale) {
            return [
                'success' => false,
                'message' => "{$station->nom} n'a pas de marché.",
            ];
        }

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => "Usage: acheter <code> <quantité>\nExemple: acheter FER 500",
            ];
        }

        $code = strtoupper($parts[1]);
        $quantite = (int)$parts[2];

        if ($quantite <= 0) {
            return ['success' => false, 'message' => 'Quantité invalide'];
        }

        // Trouver le produit
        $produit = \App\Models\Produit::where('code', $code)->first();
        if (!$produit) {
            return ['success' => false, 'message' => "Produit '{$code}' inconnu"];
        }

        // Trouver l'entrée du marché
        $marche = \App\Models\MarcheStation::where('station_id', $station->id)
            ->where('produit_id', $produit->id)
            ->first();

        if (!$marche || !$marche->disponible_vente) {
            return [
                'success' => false,
                'message' => "{$station->nom} ne vend pas {$produit->nom}.",
            ];
        }

        // Vérifier stock disponible
        if ($quantite > $marche->stock_actuel) {
            return [
                'success' => false,
                'message' => "Stock insuffisant.\nDisponible: " . number_format($marche->stock_actuel) . " unités",
            ];
        }

        // Calculer prix total
        $prixTotal = $marche->prix_vente_joueur * $quantite;

        // Vérifier crédits (TODO: système de crédits à implémenter)
        // For now, on assume que le joueur a assez de crédits

        // TODO: Vérifier capacité de soute du vaisseau
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Effectuer la transaction
        $resultat = $marche->vendreAuJoueur($quantite);

        if (!$resultat['success']) {
            return $resultat;
        }

        // TODO: Débiter crédits
        // TODO: Ajouter au cargo du vaisseau

        $message = "\n=== ACHAT EFFECTUÉ ===\n";
        $message .= "Station: {$station->nom}\n";
        $message .= "Produit: {$produit->nom} ({$code})\n";
        $message .= "Quantité: " . number_format($quantite) . " unités\n";
        $message .= "Prix unitaire: " . number_format($resultat['prix_unitaire'], 2) . "₡\n";
        $message .= "Prix total: " . number_format($resultat['total'], 2) . "₡\n";
        $message .= "Nouveau stock station: " . number_format($marche->stock_actuel) . "\n";
        $message .= "Type économique: {$marche->type_economique}\n\n";
        $message .= "💡 Le prix a été ajusté selon l'offre et la demande.\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Vendre des produits au marché (la station ACHÈTE au joueur)
     */
    private function vendreRessource(Personnage $personnage, array $parts): array
    {
        // Vérifier que dans une station
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station avec un marché.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->commerciale) {
            return [
                'success' => false,
                'message' => "{$station->nom} n'a pas de marché.",
            ];
        }

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => "Usage: vendre <code> <quantité>\nExemple: vendre FER 500",
            ];
        }

        $code = strtoupper($parts[1]);
        $quantite = (int)$parts[2];

        if ($quantite <= 0) {
            return ['success' => false, 'message' => 'Quantité invalide'];
        }

        // Trouver le produit
        $produit = \App\Models\Produit::where('code', $code)->first();
        if (!$produit) {
            return ['success' => false, 'message' => "Produit '{$code}' inconnu"];
        }

        // TODO: Vérifier inventaire du vaisseau
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Trouver l'entrée du marché
        $marche = \App\Models\MarcheStation::where('station_id', $station->id)
            ->where('produit_id', $produit->id)
            ->first();

        if (!$marche || !$marche->disponible_achat) {
            return [
                'success' => false,
                'message' => "{$station->nom} n'achète pas {$produit->nom}.",
            ];
        }

        // Vérifier capacité de stockage de la station
        $espaceDisponible = $marche->stock_max - $marche->stock_actuel;
        if ($quantite > $espaceDisponible) {
            return [
                'success' => false,
                'message' => "La station n'a pas assez d'espace.\nCapacité disponible: " . number_format($espaceDisponible) . " unités",
            ];
        }

        // Effectuer la transaction
        $resultat = $marche->acheterAuJoueur($quantite);

        if (!$resultat['success']) {
            return $resultat;
        }

        // TODO: Retirer du cargo
        // TODO: Créditer le joueur

        $message = "\n=== VENTE EFFECTUÉE ===\n";
        $message .= "Station: {$station->nom}\n";
        $message .= "Produit: {$produit->nom} ({$code})\n";
        $message .= "Quantité: " . number_format($quantite) . " unités\n";
        $message .= "Prix unitaire: " . number_format($resultat['prix_unitaire'], 2) . "₡\n";
        $message .= "Prix total: " . number_format($resultat['total'], 2) . "₡\n";
        $message .= "Nouveau stock station: " . number_format($marche->stock_actuel) . "\n";
        $message .= "Type économique: {$marche->type_economique}\n\n";
        $message .= "💡 Le prix a été ajusté selon l'offre et la demande.\n";

        return ['success' => true, 'message' => $message];
    }

    // === COMMANDES FABRICATION (PHASE 2) ===

    /**
     * Afficher les recettes disponibles
     */
    private function showRecettes(Personnage $personnage, array $parts): array
    {
        $categorie = $parts[1] ?? null;

        $query = Recette::where('actif', true);

        if ($categorie) {
            $query->where('categorie', $categorie);
        }

        $recettes = $query->orderBy('niveau_requis')->orderBy('categorie')->get();

        if ($recettes->isEmpty()) {
            return [
                'success' => true,
                'message' => "\n=== RECETTES ===\nAucune recette trouvee" . ($categorie ? " pour '{$categorie}'" : "") . ".\n",
            ];
        }

        $message = "\n=== RECETTES" . ($categorie ? " ({$categorie})" : "") . " ===\n\n";

        $currentCategorie = '';
        foreach ($recettes as $recette) {
            if ($recette->categorie !== $currentCategorie) {
                $currentCategorie = $recette->categorie;
                $message .= "--- " . strtoupper($currentCategorie) . " ---\n";
            }

            $message .= "\n[{$recette->code}] {$recette->nom}\n";
            $message .= "  Niveau requis: {$recette->niveau_requis}\n";
            $message .= "  Temps: {$recette->temps_fabrication}s | Energie: {$recette->energie_requise}\n";

            // Ingredients
            $ingredients = $recette->getIngredientsDetails();
            $ingList = array_map(fn($i) => "{$i['quantite']} {$i['code']}", $ingredients);
            $message .= "  IN: " . implode(', ', $ingList) . "\n";

            // Produits
            $produits = $recette->getProduitsDetails();
            $prodList = array_map(fn($p) => "{$p['quantite']} {$p['code']}", $produits);
            $message .= "  OUT: " . implode(', ', $prodList) . "\n";
        }

        $message .= "\nUtilisez 'fabriquer [code]' pour produire.";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Fabriquer une recette (transformation dans le vaisseau)
     */
    private function fabriquerRecette(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        if (count($parts) < 2) {
            return [
                'success' => false,
                'message' => "Usage: fabriquer [code_recette] [multiplicateur]\nExemple: fabriquer RAFF_BAUXITE 2",
            ];
        }

        $code = strtoupper($parts[1]);
        $multiplicateur = isset($parts[2]) ? max(1, (int)$parts[2]) : 1;

        // Trouver recette
        $recette = Recette::where('code', $code)->where('actif', true)->first();
        if (!$recette) {
            return ['success' => false, 'message' => "Recette '{$code}' inconnue ou inactive"];
        }

        // Verifier niveau personnage (simplifie: on utilise le niveau du personnage)
        if ($personnage->niveau < $recette->niveau_requis) {
            return [
                'success' => false,
                'message' => "Niveau insuffisant. Requis: {$recette->niveau_requis} | Actuel: {$personnage->niveau}",
            ];
        }

        // Verifier ingredients
        if (!$recette->peutFabriquer($vaisseau, $multiplicateur)) {
            $manquants = $recette->getIngredientsManquants($vaisseau, $multiplicateur);
            $message = "\n=== FABRICATION IMPOSSIBLE ===\nIngredients manquants:\n";
            foreach ($manquants as $m) {
                $message .= "- {$m['nom']}: {$m['disponible']}/{$m['requis']} (manque {$m['manquant']})\n";
            }
            return ['success' => false, 'message' => $message];
        }

        // Cout en PA (1 PA par fabrication)
        $pa_requis = $multiplicateur;
        if ($personnage->points_action < $pa_requis) {
            return ['success' => false, 'message' => "PA insuffisants. Requis: {$pa_requis}"];
        }

        // Fabriquer
        $resultat = $recette->fabriquer($vaisseau, $multiplicateur);

        if (!$resultat['success']) {
            return $resultat;
        }

        // Consommer PA
        $personnage->consommerPA($pa_requis);

        $message = "\n=== FABRICATION REUSSIE ===\n";
        $message .= "Recette: {$recette->nom}\n";
        $message .= "Quantite: x{$multiplicateur}\n";
        $message .= "PA utilises: {$pa_requis}\n\n";

        $message .= "Produits obtenus:\n";
        foreach ($resultat['produits'] as $p) {
            $message .= "- {$p['nom']} ({$p['code']}): {$p['quantite']}\n";
        }

        return ['success' => true, 'message' => $message];
    }

    // === COMMANDES COMBAT (PHASE 3) ===

    /**
     * Afficher les armes disponibles
     */
    private function showArmes(Personnage $personnage): array
    {
        $armes = Arme::where('actif', true)->orderBy('niveau_requis')->orderBy('type')->get();

        $message = "\n=== ARMES DISPONIBLES ===\n\n";

        $currentType = '';
        foreach ($armes as $arme) {
            if ($arme->type !== $currentType) {
                $currentType = $arme->type;
                $message .= "--- " . strtoupper($currentType) . " ---\n";
            }

            $message .= "\n[{$arme->code}] {$arme->nom}\n";
            $message .= "  Degats: {$arme->degats_min}-{$arme->degats_max} | Precision: {$arme->precision}%\n";
            $message .= "  Portee: {$arme->portee} | Cadence: {$arme->cadence}/tour\n";
            $message .= "  Energie/tir: {$arme->energie_tir} | Niveau: {$arme->niveau_requis}\n";
            $message .= "  Prix: " . number_format($arme->prix) . " cr | Taille: {$arme->taille}\n";
        }

        $message .= "\nUtilisez 'equiper arme [code] [slot]' pour equiper.";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Afficher les boucliers disponibles
     */
    private function showBoucliers(Personnage $personnage): array
    {
        $boucliers = Bouclier::where('actif', true)->orderBy('niveau_requis')->orderBy('type')->get();

        $message = "\n=== BOUCLIERS DISPONIBLES ===\n\n";

        $currentType = '';
        foreach ($boucliers as $bouclier) {
            if ($bouclier->type !== $currentType) {
                $currentType = $bouclier->type;
                $message .= "--- " . strtoupper($currentType) . " ---\n";
            }

            $message .= "\n[{$bouclier->code}] {$bouclier->nom}\n";
            $message .= "  Points: {$bouclier->points_max} | Regen: {$bouclier->regeneration}/tour\n";
            $message .= "  Resistance: {$bouclier->resistance}%\n";
            $message .= "  vs Laser: {$bouclier->vs_laser}% | vs Canon: {$bouclier->vs_canon}%\n";
            $message .= "  vs Missile: {$bouclier->vs_missile}% | vs Plasma: {$bouclier->vs_plasma}%\n";
            $message .= "  Energie: {$bouclier->energie_maintien}/tour | Niveau: {$bouclier->niveau_requis}\n";
            $message .= "  Prix: " . number_format($bouclier->prix) . " cr | Taille: {$bouclier->taille}\n";
        }

        $message .= "\nUtilisez 'equiper bouclier [code]' pour equiper.";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Equiper une arme ou un bouclier
     */
    private function equiperEquipement(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => "Usage:\n  equiper arme [code] [slot 1-3]\n  equiper bouclier [code]\nExemple: equiper arme LASER_MK1 1",
            ];
        }

        $type = strtolower($parts[1]);
        $code = strtoupper($parts[2]);

        if ($type === 'arme' || $type === 'weapon') {
            $slot = isset($parts[3]) ? (int)$parts[3] : 1;
            if ($slot < 1 || $slot > 3) {
                return ['success' => false, 'message' => 'Slot invalide (1-3)'];
            }

            $arme = Arme::where('code', $code)->where('actif', true)->first();
            if (!$arme) {
                return ['success' => false, 'message' => "Arme '{$code}' inconnue"];
            }

            if ($personnage->niveau < $arme->niveau_requis) {
                return [
                    'success' => false,
                    'message' => "Niveau insuffisant. Requis: {$arme->niveau_requis} | Actuel: {$personnage->niveau}",
                ];
            }

            $vaisseau->equiperArme($arme->id, $slot);

            $message = "\n=== ARME EQUIPEE ===\n";
            $message .= "Slot {$slot}: {$arme->nom}\n";
            $message .= "Degats: {$arme->degats_min}-{$arme->degats_max}\n";
            $message .= "Precision: {$arme->precision}% | Cadence: {$arme->cadence}\n";

            return ['success' => true, 'message' => $message];

        } elseif ($type === 'bouclier' || $type === 'shield') {
            $bouclier = Bouclier::where('code', $code)->where('actif', true)->first();
            if (!$bouclier) {
                return ['success' => false, 'message' => "Bouclier '{$code}' inconnu"];
            }

            if ($personnage->niveau < $bouclier->niveau_requis) {
                return [
                    'success' => false,
                    'message' => "Niveau insuffisant. Requis: {$bouclier->niveau_requis} | Actuel: {$personnage->niveau}",
                ];
            }

            $vaisseau->equiperBouclier($bouclier->id);

            $message = "\n=== BOUCLIER EQUIPE ===\n";
            $message .= "{$bouclier->nom}\n";
            $message .= "Points: {$bouclier->points_max} | Regen: {$bouclier->regeneration}/tour\n";
            $message .= "Resistance: {$bouclier->resistance}%\n";

            return ['success' => true, 'message' => $message];

        } else {
            return ['success' => false, 'message' => "Type inconnu. Utilisez 'arme' ou 'bouclier'"];
        }
    }

    /**
     * Afficher l'etat de combat du vaisseau
     */
    private function showEtatCombat(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $message = "\n=== ETAT COMBAT - {$vaisseau->modele} ===\n\n";

        // Coque
        $pct_coque = $vaisseau->getPourcentageCoque();
        $message .= "COQUE: {$vaisseau->coque_actuelle}/{$vaisseau->coque_max} ({$pct_coque}%)\n";

        // Bouclier
        if ($vaisseau->bouclier) {
            $pct_bouclier = $vaisseau->getPourcentageBouclier();
            $message .= "BOUCLIER: {$vaisseau->bouclier_actuel}/{$vaisseau->bouclier->points_max} ({$pct_bouclier}%)\n";
            $message .= "  Type: {$vaisseau->bouclier->nom}\n";
            $message .= "  Regen: {$vaisseau->bouclier->regeneration}/tour\n";
        } else {
            $message .= "BOUCLIER: Aucun\n";
        }

        $message .= "\nENERGIE: " . round($vaisseau->energie_actuelle, 0) . "/{$vaisseau->reserve}\n";
        $message .= "ESQUIVE: {$vaisseau->esquive}%\n";
        $message .= "PRECISION BONUS: +{$vaisseau->bonus_precision}%\n";

        // Armes
        $message .= "\n--- ARMEMENT ---\n";
        $armes = $vaisseau->getArmesEquipees();
        if (empty($armes)) {
            $message .= "Aucune arme equipee\n";
        } else {
            $slot = 1;
            foreach ($armes as $arme) {
                $dps = round($arme->getDPS(), 1);
                $message .= "Slot {$slot}: {$arme->nom}\n";
                $message .= "  {$arme->degats_min}-{$arme->degats_max} dmg | {$arme->precision}% | x{$arme->cadence}\n";
                $message .= "  DPS theorique: {$dps} | Energie: {$arme->getCoutEnergieSalve()}/salve\n";
                $slot++;
            }
        }

        // Emplacements vides
        for ($i = count($armes) + 1; $i <= 3; $i++) {
            $message .= "Slot {$i}: [VIDE]\n";
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Reparer la coque du vaisseau
     */
    private function reparerVaisseau(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $quantite = isset($parts[1]) ? (int)$parts[1] : 10;
        if ($quantite <= 0) {
            return ['success' => false, 'message' => 'Quantite invalide'];
        }

        // Cout: 10 credits par point de coque
        $cout = $quantite * 10;

        if ($personnage->credits < $cout) {
            return [
                'success' => false,
                'message' => "Credits insuffisants. Cout: {$cout} cr | Disponible: " . number_format($personnage->credits),
            ];
        }

        // Verifier si reparation necessaire
        if ($vaisseau->coque_actuelle >= $vaisseau->coque_max) {
            return ['success' => false, 'message' => 'La coque est deja en parfait etat'];
        }

        // Limiter a ce qui est necessaire
        $degats = $vaisseau->coque_max - $vaisseau->coque_actuelle;
        $quantite_reelle = min($quantite, $degats);
        $cout_reel = $quantite_reelle * 10;

        // Reparer
        $repare = $vaisseau->reparerCoque($quantite_reelle);
        $personnage->credits -= $cout_reel;
        $personnage->save();

        $message = "\n=== REPARATION EFFECTUEE ===\n";
        $message .= "Points repares: {$repare}\n";
        $message .= "Cout: {$cout_reel} credits\n";
        $message .= "Coque: {$vaisseau->coque_actuelle}/{$vaisseau->coque_max}\n";
        $message .= "Credits restants: " . number_format($personnage->credits) . "\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Scanner les ennemis dans la zone actuelle
     */
    private function scannerEnnemis(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Verifier si en combat
        $combat_actif = Combat::enCours($vaisseau->id);
        if ($combat_actif) {
            return [
                'success' => false,
                'message' => 'Impossible de scanner en combat! Utilisez "attaquer" ou "fuir".',
            ];
        }

        // Determiner le niveau de la zone
        $distance = sqrt(
            pow($vaisseau->coord_x, 2) +
            pow($vaisseau->coord_y, 2) +
            pow($vaisseau->coord_z, 2)
        );
        $niveau_zone = max(1, (int)($distance / 10) + 1);

        // Verifier spawn
        if (!Ennemi::checkSpawn($niveau_zone)) {
            return [
                'success' => true,
                'message' => "\n=== SCAN DE LA ZONE ===\n" .
                    "Niveau de danger: {$niveau_zone}\n" .
                    "Resultat: Aucun ennemi detecte dans la zone.\n",
            ];
        }

        // Spawn un ennemi
        $ennemi = Ennemi::spawnPourZone($niveau_zone);
        if (!$ennemi) {
            return [
                'success' => true,
                'message' => "\n=== SCAN DE LA ZONE ===\n" .
                    "Niveau de danger: {$niveau_zone}\n" .
                    "Resultat: Zone claire.\n",
            ];
        }

        // Demarrer le combat
        $combat = Combat::commencer($vaisseau, $ennemi);
        $vaisseau->en_combat = true;
        $vaisseau->save();

        $message = "\n=== ALERTE! ENNEMI DETECTE! ===\n";
        $message .= "Nom: {$ennemi->nom}\n";
        $message .= "Type: {$ennemi->type} ({$ennemi->faction})\n";
        $message .= "Niveau: {$ennemi->niveau} - Difficulte: {$ennemi->difficulte}\n";
        $message .= "Coque: {$ennemi->coque_max} | Bouclier: {$ennemi->bouclier_max}\n";
        $message .= "Armement: {$ennemi->type_arme} (Degats: {$ennemi->degats_min}-{$ennemi->degats_max})\n";
        $message .= "\nCommandes: 'attaquer' pour combattre, 'fuir' pour tenter de fuir.\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Afficher la liste des types d'ennemis
     */
    private function showEnnemis(): array
    {
        $ennemis = Ennemi::orderBy('niveau')->get();

        if ($ennemis->isEmpty()) {
            return ['success' => true, 'message' => 'Aucun ennemi dans la base de donnees.'];
        }

        $message = "\n=== ENCYCLOPEDIE DES ENNEMIS ===\n\n";

        $par_type = $ennemis->groupBy('type');

        foreach ($par_type as $type => $groupe) {
            $message .= strtoupper($type) . "S:\n";

            foreach ($groupe as $ennemi) {
                $difficulte = match($ennemi->difficulte) {
                    'facile' => '[Facile]',
                    'moyen' => '[Moyen]',
                    'difficile' => '[Difficile]',
                    'boss' => '[BOSS]',
                    default => '',
                };

                $message .= sprintf(
                    "  Niv.%d %s - %s %s\n",
                    $ennemi->niveau,
                    $ennemi->nom,
                    $difficulte,
                    $ennemi->type_arme
                );
                $message .= sprintf(
                    "    Coque:%d Boucl:%d Deg:%d-%d Zones:%d-%d\n",
                    $ennemi->coque_max,
                    $ennemi->bouclier_max,
                    $ennemi->degats_min,
                    $ennemi->degats_max,
                    $ennemi->zone_niveau_min,
                    $ennemi->zone_niveau_max
                );
            }
            $message .= "\n";
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Attaquer l'ennemi en combat
     */
    private function attaquerEnnemi(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Verifier combat en cours
        $combat = Combat::enCours($vaisseau->id);
        if (!$combat) {
            // Pas de combat - tenter d'en initier un
            // Si un code ennemi est fourni, on peut engager directement
            if (isset($parts[0]) && !empty($parts[0])) {
                $ennemi = Ennemi::where('code', strtoupper($parts[0]))->first();
                if (!$ennemi) {
                    return [
                        'success' => false,
                        'message' => "Ennemi inconnu. Utilisez 'scanner-ennemis' pour detecter les menaces.",
                    ];
                }

                // Verifier niveau zone
                $distance = sqrt(
                    pow($vaisseau->coord_x, 2) +
                    pow($vaisseau->coord_y, 2) +
                    pow($vaisseau->coord_z, 2)
                );
                $niveau_zone = max(1, (int)($distance / 10) + 1);

                if ($ennemi->zone_niveau_min > $niveau_zone || $ennemi->zone_niveau_max < $niveau_zone) {
                    return [
                        'success' => false,
                        'message' => "Cet ennemi n'est pas present dans cette zone (niveau {$niveau_zone}).",
                    ];
                }

                $combat = Combat::commencer($vaisseau, $ennemi);
                $vaisseau->en_combat = true;
                $vaisseau->save();
            } else {
                return [
                    'success' => false,
                    'message' => "Aucun combat en cours. Utilisez 'scanner-ennemis' pour detecter les menaces.",
                ];
            }
        }

        // Executer le tour de combat
        $resultat = $combat->executerTour();
        $ennemi = $combat->ennemi;

        $message = "\n=== TOUR {$resultat['tour']} ===\n\n";

        // Attaques du joueur
        $message .= "Vos attaques:\n";
        $total_joueur = 0;
        foreach ($resultat['joueur'] as $i => $att) {
            if ($att['touche']) {
                $total_joueur += $att['degats_effectifs'] ?? $att['degats'];
                $message .= sprintf(
                    "  Tir %d: TOUCHE! %d degats (bouclier: %d, coque: %d)\n",
                    $i + 1,
                    $att['degats'],
                    $att['degats_bouclier'] ?? 0,
                    $att['degats_coque'] ?? 0
                );
            } else {
                $message .= "  Tir " . ($i + 1) . ": Rate!\n";
            }
        }
        $message .= "Total inflige: {$total_joueur} degats\n\n";

        // Actions de l'ennemi
        if ($resultat['statut'] !== 'victoire' && $resultat['statut'] !== 'fuite_ennemi') {
            $message .= "Attaques de {$ennemi->nom}:\n";
            $total_ennemi = 0;
            foreach ($resultat['ennemi'] as $i => $att) {
                if (isset($att['action']) && $att['action'] === 'regeneration') {
                    $message .= "  Regeneration: +{$att['valeur']} bouclier\n";
                } elseif ($att['touche']) {
                    $total_ennemi += ($att['degats_bouclier'] ?? 0) + ($att['degats_coque'] ?? 0);
                    $message .= sprintf(
                        "  Tir %d: TOUCHE! %d degats (bouclier: %d, coque: %d)\n",
                        $i + 1,
                        $att['degats'],
                        $att['degats_bouclier'] ?? 0,
                        $att['degats_coque'] ?? 0
                    );
                } else {
                    $message .= "  Tir " . ($i + 1) . ": Rate!\n";
                }
            }
            if ($total_ennemi > 0) {
                $message .= "Total subi: {$total_ennemi} degats\n";
            }
        }

        // Regeneration
        if (isset($resultat['regeneration'])) {
            $message .= "\nRegeneration:\n";
            if ($resultat['regeneration']['joueur'] > 0) {
                $message .= "  Votre bouclier: +{$resultat['regeneration']['joueur']}\n";
            }
            if ($resultat['regeneration']['ennemi'] > 0) {
                $message .= "  Ennemi: +{$resultat['regeneration']['ennemi']}\n";
            }
        }

        // Etat actuel
        $message .= "\n--- ETAT ---\n";
        $message .= sprintf(
            "Vous: Coque %d/%d (%.0f%%) | Bouclier %d\n",
            $vaisseau->coque_actuelle,
            $vaisseau->coque_max,
            $vaisseau->getPourcentageCoque(),
            $vaisseau->bouclier_actuel
        );
        $message .= sprintf(
            "Ennemi: Coque %d/%d (%.0f%%) | Bouclier %d/%d\n",
            $combat->ennemi_coque,
            $ennemi->coque_max,
            ($combat->ennemi_coque / $ennemi->coque_max) * 100,
            $combat->ennemi_bouclier,
            $ennemi->bouclier_max
        );

        // Resultat final
        if ($resultat['statut'] === 'victoire') {
            $message .= "\n*** VICTOIRE! ***\n";
            $message .= "Recompenses:\n";
            $message .= "  Credits: +" . number_format($resultat['recompenses']['credits']) . "\n";
            $message .= "  XP: +{$resultat['recompenses']['xp']}\n";

            // Donner les recompenses
            $personnage->credits += $resultat['recompenses']['credits'];
            $personnage->ajouterExperience($resultat['recompenses']['xp']);
            $personnage->save();
        } elseif ($resultat['statut'] === 'fuite_ennemi') {
            $message .= "\n*** L'ENNEMI PREND LA FUITE! ***\n";
            $message .= "Recompenses partielles:\n";
            $message .= "  Credits: +" . number_format($resultat['recompenses']['credits']) . "\n";
            $message .= "  XP: +{$resultat['recompenses']['xp']}\n";

            $personnage->credits += $resultat['recompenses']['credits'];
            $personnage->ajouterExperience($resultat['recompenses']['xp']);
            $personnage->save();
        } elseif ($resultat['statut'] === 'defaite') {
            $message .= "\n*** DEFAITE! ***\n";
            $message .= "Votre vaisseau est detruit.\n";

            // Penalites de defaite
            $perte_credits = (int)($personnage->credits * 0.1);
            $personnage->credits -= $perte_credits;
            $personnage->save();

            $message .= "Perte: {$perte_credits} credits\n";
            $message .= "Utilisez 'reparer' pour reparer votre vaisseau.\n";

            // Restaurer un minimum de coque
            $vaisseau->coque_actuelle = 1;
            $vaisseau->save();
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Fuir le combat
     */
    private function fuirCombat(Personnage $personnage): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        $combat = Combat::enCours($vaisseau->id);
        if (!$combat) {
            return ['success' => false, 'message' => 'Aucun combat en cours.'];
        }

        $resultat = $combat->fuir();

        $message = "\n=== TENTATIVE DE FUITE ===\n";

        if ($resultat['reussie']) {
            $message .= "SUCCES! Vous echappez au combat.\n";
        } else {
            $message .= "ECHEC! L'ennemi profite de votre fuite.\n";
            $message .= "Degats subis: {$resultat['degats_subis']}\n";
            $message .= sprintf(
                "Votre etat: Coque %d/%d | Bouclier %d\n",
                $vaisseau->coque_actuelle,
                $vaisseau->coque_max,
                $vaisseau->bouclier_actuel
            );

            if ($vaisseau->isDetruit()) {
                $message .= "\nVotre vaisseau est detruit!\n";

                // Marquer defaite
                $combat->statut = 'defaite';
                $combat->save();
                $vaisseau->en_combat = false;
                $vaisseau->coque_actuelle = 1;
                $vaisseau->save();

                // Penalite
                $perte = (int)($personnage->credits * 0.15);
                $personnage->credits -= $perte;
                $personnage->save();
                $message .= "Perte: {$perte} credits\n";
            } else {
                $message .= "\nLe combat continue. Commandes: 'attaquer' ou 'fuir'\n";
            }
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Afficher les missions disponibles ou en cours
     */
    private function showMissions(Personnage $personnage, array $parts): array
    {
        $filter = $parts[0] ?? 'disponibles';

        if ($filter === 'encours' || $filter === 'actives') {
            // Missions en cours
            $missions = $personnage->missions()
                ->whereIn('mission_personnage.statut', ['en_cours', 'completee'])
                ->with('faction')
                ->get();

            if ($missions->isEmpty()) {
                return ['success' => true, 'message' => "Aucune mission en cours.\nUtilisez 'missions' pour voir les disponibles."];
            }

            $message = "\n=== MISSIONS EN COURS ===\n\n";

            foreach ($missions as $mission) {
                $statut = $mission->pivot->statut === 'completee' ? '[COMPLETEE]' : '[EN COURS]';
                $faction = $mission->faction ? $mission->faction->nom : 'Independant';

                $message .= "{$statut} {$mission->titre}\n";
                $message .= "  Code: {$mission->code} | Faction: {$faction}\n";

                // Progression
                $progression = json_decode($mission->pivot->progression, true);
                foreach ($mission->objectifs as $i => $obj) {
                    $actuel = $progression[$i]['actuel'] ?? 0;
                    $requis = $obj['quantite'] ?? 1;
                    $type = $obj['type'];
                    $message .= "  - {$type}: {$actuel}/{$requis}\n";
                }

                if ($mission->pivot->statut === 'completee') {
                    $message .= "  -> Utilisez 'mission-rendre {$mission->code}' pour les recompenses\n";
                }
                $message .= "\n";
            }

            return ['success' => true, 'message' => $message];
        }

        // Missions disponibles
        $missions = Mission::where('actif', true)
            ->with('faction')
            ->get();

        if ($missions->isEmpty()) {
            return ['success' => true, 'message' => 'Aucune mission disponible.'];
        }

        $message = "\n=== MISSIONS DISPONIBLES ===\n\n";

        foreach ($missions as $mission) {
            $check = $mission->peutEtreAcceptee($personnage);
            $disponible = $check['peut_accepter'] ? '' : ' [INDISPONIBLE]';
            $faction = $mission->faction ? $mission->faction->nom : 'Independant';
            $difficulte = ucfirst($mission->difficulte);

            $message .= "[{$mission->code}] {$mission->titre}{$disponible}\n";
            $message .= "  {$faction} | {$difficulte} | Niv.{$mission->niveau_requis}\n";
            $message .= "  Recompenses: {$mission->recompense_credits} cr, {$mission->recompense_xp} XP\n";

            if (!$check['peut_accepter']) {
                $message .= "  Raison: " . implode(', ', $check['raisons']) . "\n";
            }
            $message .= "\n";
        }

        $message .= "Utilisez 'mission-accepter [CODE]' pour accepter une mission\n";
        $message .= "Utilisez 'missions encours' pour voir vos missions actives\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Accepter une mission
     */
    private function accepterMission(Personnage $personnage, array $parts): array
    {
        if (empty($parts[0])) {
            return ['success' => false, 'message' => "Usage: mission-accepter [CODE]\nExemple: mission-accepter FED_LIVRAISON_01"];
        }

        $mission = Mission::where('code', strtoupper($parts[0]))->first();
        if (!$mission) {
            return ['success' => false, 'message' => "Mission '{$parts[0]}' introuvable."];
        }

        $result = $mission->accepter($personnage);

        if ($result['success']) {
            $message = "\n=== MISSION ACCEPTEE ===\n";
            $message .= "Titre: {$mission->titre}\n";
            $message .= "Description: {$mission->description}\n\n";
            $message .= "Objectifs:\n";
            foreach ($mission->objectifs as $obj) {
                $message .= "  - {$obj['type']}: {$obj['quantite']}\n";
            }
            $message .= "\nRecompenses:\n";
            $message .= "  Credits: {$mission->recompense_credits}\n";
            $message .= "  XP: {$mission->recompense_xp}\n";
            $message .= "  Reputation: +{$mission->recompense_reputation}\n";

            return ['success' => true, 'message' => $message];
        }

        return $result;
    }

    /**
     * Rendre une mission completee
     */
    private function rendreMission(Personnage $personnage, array $parts): array
    {
        if (empty($parts[0])) {
            return ['success' => false, 'message' => "Usage: mission-rendre [CODE]\nExemple: mission-rendre FED_LIVRAISON_01"];
        }

        $mission = Mission::where('code', strtoupper($parts[0]))->first();
        if (!$mission) {
            return ['success' => false, 'message' => "Mission '{$parts[0]}' introuvable."];
        }

        $result = $mission->rendre($personnage);

        if ($result['success']) {
            $message = "\n=== MISSION RENDUE ===\n";
            $message .= "'{$mission->titre}' completee!\n\n";
            $message .= "Recompenses recues:\n";
            $message .= "  Credits: +{$result['recompenses']['credits']}\n";
            $message .= "  XP: +{$result['recompenses']['xp']}\n";
            $message .= "  Reputation: +{$result['recompenses']['reputation']}\n";

            return ['success' => true, 'message' => $message];
        }

        return $result;
    }

    /**
     * Abandonner une mission
     */
    private function abandonnerMission(Personnage $personnage, array $parts): array
    {
        if (empty($parts[0])) {
            return ['success' => false, 'message' => "Usage: mission-abandonner [CODE]"];
        }

        $mission = Mission::where('code', strtoupper($parts[0]))->first();
        if (!$mission) {
            return ['success' => false, 'message' => "Mission '{$parts[0]}' introuvable."];
        }

        return $mission->abandonner($personnage);
    }

    /**
     * Afficher les factions
     */
    private function showFactions(Personnage $personnage): array
    {
        $factions = Faction::where('actif', true)->get();

        if ($factions->isEmpty()) {
            return ['success' => true, 'message' => 'Aucune faction.'];
        }

        $message = "\n=== FACTIONS ===\n\n";

        foreach ($factions as $faction) {
            $rep = Reputation::getOuCreer($personnage->id, $faction->id);

            $message .= "{$faction->nom} [{$faction->code}]\n";
            $message .= "  Type: {$faction->type} | Alignement: {$faction->alignement}\n";
            $message .= "  Votre reputation: {$rep->valeur} ({$rep->rang})\n";
            $message .= "  {$faction->description}\n\n";
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Afficher la reputation du personnage
     */
    private function showReputation(Personnage $personnage): array
    {
        $factions = Faction::where('actif', true)->get();

        $message = "\n=== REPUTATION ===\n\n";

        foreach ($factions as $faction) {
            $rep = Reputation::getOuCreer($personnage->id, $faction->id);
            $pourcent = $rep->getPourcentageVersProchainRang();

            // Barre de progression
            $filled = (int)($pourcent / 10);
            $empty = 10 - $filled;
            $bar = str_repeat('█', $filled) . str_repeat('░', $empty);

            $message .= "{$faction->nom}\n";
            $message .= "  {$rep->rang} ({$rep->valeur}) [{$bar}] {$pourcent}%\n";
            $message .= "  Missions: {$rep->missions_completees} completees, {$rep->missions_echouees} echouees\n\n";
        }

        return ['success' => true, 'message' => $message];
    }

    // === API AJAX POUR PANNEAUX ===

    /**
     * API: Récupère le statut du personnage (PA, position, jetons)
     */
    public function apiGetStatus(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response()->json([
                'success' => false,
                'message' => 'Personnage introuvable',
            ], 404);
        }

        // Récupération auto des PA
        $recup = $personnage->recupererPAAutomatique();

        // Calcul prochaine récupération
        $prochaine_recup = null;
        if ($personnage->points_action < $personnage->max_points_action && $personnage->derniere_recuperation_pa) {
            $delai = config('game.pa.recuperation_delai', 60);
            $minutes_restantes = $delai - (now()->diffInMinutes($personnage->derniere_recuperation_pa) % $delai);
            $prochaine_recup = [
                'minutes' => $minutes_restantes,
                'secondes' => $minutes_restantes * 60,
            ];
        }

        // Position
        $position = $personnage->getPositionActuelle();

        return response()->json([
            'success' => true,
            'personnage' => [
                'nom' => $personnage->nom,
                'prenom' => $personnage->prenom,
                'niveau' => $personnage->niveau,
                'experience' => $personnage->experience,
            ],
            'pa' => [
                'actuel' => $personnage->points_action,
                'max' => $personnage->max_points_action,
                'prochaine_recup' => $prochaine_recup,
            ],
            'jetons' => [
                'hope' => $personnage->jetons_hope,
                'fear' => $personnage->jetons_fear,
            ],
            'position' => $position,
        ]);
    }

    /**
     * API: Récupère les infos du vaisseau
     */
    public function apiGetVaisseau(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || !$personnage->vaisseauActif) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun vaisseau actif',
            ], 404);
        }

        $vaisseau = $personnage->vaisseauActif;

        return response()->json([
            'success' => true,
            'vaisseau' => [
                'modele' => $vaisseau->modele,
                'energie' => [
                    'actuelle' => round($vaisseau->energie_actuelle, 2),
                    'max' => round($vaisseau->reserve, 2),
                    'pourcentage' => round(($vaisseau->energie_actuelle / $vaisseau->reserve) * 100, 1),
                ],
                'scan' => [
                    'portee' => $vaisseau->portee_scan,
                    'puissance' => $vaisseau->puissance_scan,
                    'bonus' => $vaisseau->bonus_scan,
                    'niveau_actuel' => $vaisseau->scan_niveau_actuel,
                    'puissance_effective' => $vaisseau->getPuissanceScanEffective(),
                ],
                'vitesses' => [
                    'conventionnelle' => $vaisseau->vitesse_conventionnelle,
                    'saut' => $vaisseau->vitesse_saut,
                ],
            ],
        ]);
    }

    /**
     * API: Récupère la carte des systèmes découverts
     */
    public function apiGetCarte(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response()->json([
                'success' => false,
                'message' => 'Personnage introuvable',
            ], 404);
        }

        $systemes = $personnage->getSystemesDecouverts();
        $position = $personnage->getPositionActuelle();

        // Enrichir avec distances actuelles
        foreach ($systemes as &$systeme) {
            if ($position) {
                $distance = $personnage->calculerDistance($position, [
                    'secteur_x' => $systeme['secteur_x'],
                    'secteur_y' => $systeme['secteur_y'],
                    'secteur_z' => $systeme['secteur_z'],
                    'position_x' => $systeme['position_x'],
                    'position_y' => $systeme['position_y'],
                    'position_z' => $systeme['position_z'],
                ]);
                $systeme['distance_actuelle'] = round($distance, 2);
            }
        }

        return response()->json([
            'success' => true,
            'systemes' => $systemes,
            'total' => count($systemes),
        ]);
    }

    // ========== SYSTÈME DE STATIONS ==========

    /**
     * Arrimer à une station avec jet de pilotage
     */
    private function arrimerStation(Personnage $personnage, array $parts): array
    {
        if (!$personnage->vaisseauActif) {
            return [
                'success' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau.',
            ];
        }

        $vaisseau = $personnage->vaisseauActif;

        // Vérifier si déjà arrimé
        if ($vaisseau->arrime_a_station_id) {
            $station = \App\Models\Station::find($vaisseau->arrime_a_station_id);
            return [
                'success' => false,
                'message' => "Vous êtes déjà arrimé à {$station->nom}.",
            ];
        }

        // Vérifier si dans une station
        if ($personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être à bord de votre vaisseau pour arrimer.',
            ];
        }

        // Trouver station dans le secteur
        $position = $personnage->getPositionActuelle();
        if (!$position) {
            return [
                'success' => false,
                'message' => 'Position du vaisseau introuvable.',
            ];
        }

        // Chercher stations dans le même système
        $stations = \App\Models\Station::where('systeme_stellaire_id', $position['systeme_stellaire_id'])
            ->where('accessible', true)
            ->get();

        if ($stations->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucune station accessible dans ce système stellaire.',
            ];
        }

        // Si nom station spécifié
        $nomStation = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : null;
        if ($nomStation) {
            $station = $stations->firstWhere('nom', 'like', "%{$nomStation}%");
            if (!$station) {
                return [
                    'success' => false,
                    'message' => "Station '{$nomStation}' introuvable dans ce système.",
                ];
            }
        } else {
            // Prendre la première station
            $station = $stations->first();
        }

        // Vérifier capacité d'amarrage
        $vaisseauxArrimes = \App\Models\Vaisseau::where('arrime_a_station_id', $station->id)->count();
        if ($vaisseauxArrimes >= $station->capacite_amarrage) {
            return [
                'success' => false,
                'message' => "{$station->nom} est complète (capacité: {$station->capacite_amarrage} vaisseaux).",
            ];
        }

        // JET DE PILOTAGE DAGGERHEART
        $competence = $personnage->competences['pilotage'] ?? 0;
        $jet = $personnage->lancerDes($competence);
        $personnage->save();

        $vaisseau->dernier_jet_pilotage = $jet;

        // Interpréter le résultat
        $message = "\n=== MANŒUVRE D'AMARRAGE ===\n";
        $message .= "Station: {$station->nom}\n";
        $message .= "Hope: {$jet['hope']} | Fear: {$jet['fear']} | Compétence: +{$competence}\n";
        $message .= "Total: {$jet['total']}\n\n";

        if ($jet['critique']) {
            // Critique ! Peut être très bon ou très mauvais
            if ($jet['hope'] >= 10) {
                // Critique positif
                $vaisseau->arrime_a_station_id = $station->id;
                $vaisseau->arrime_le = now();
                $vaisseau->save();

                $message .= "🎯 CRITIQUE AVEC HOPE! Amarrage parfait!\n";
                $message .= "Manœuvre d'amarrage exceptionnelle. Vous gagnez 1 jeton HOPE.\n";
                $message .= "Amarré avec succès à {$station->nom}.\n";

                return ['success' => true, 'message' => $message];
            } else {
                // Critique négatif
                $dommages = rand(5, 15);
                $message .= "💥 CRITIQUE AVEC FEAR! Collision lors de l'amarrage!\n";
                $message .= "Vous heurtez la station. Dommages: -{$dommages}% intégrité coque.\n";
                $message .= "Vous gagnez 1 jeton FEAR.\n";
                $message .= "Amarrage échoué. Tentez à nouveau.\n";

                return ['success' => false, 'message' => $message];
            }
        }

        if ($jet['total'] >= 12) {
            // Succès franc
            $vaisseau->arrime_a_station_id = $station->id;
            $vaisseau->arrime_le = now();
            $vaisseau->save();

            $message .= "✅ SUCCÈS! Amarrage réussi.\n";
            $message .= "Votre vaisseau est maintenant arrimé à {$station->nom}.\n";
            $message .= "Utilisez 'transborder' pour entrer dans la station.\n";

            return ['success' => true, 'message' => $message];
        } elseif ($jet['total'] >= 9) {
            // Succès partiel
            $vaisseau->arrime_a_station_id = $station->id;
            $vaisseau->arrime_le = now();
            $vaisseau->save();

            $message .= "⚠️  SUCCÈS PARTIEL. Amarrage compliqué.\n";
            $message .= "Quelques à-coups, mais vous parvenez à vous arrimer.\n";
            $message .= "Coût PA: +1 (manœuvre difficile).\n";
            $personnage->consommerPA(1);
            $personnage->save();

            return ['success' => true, 'message' => $message];
        } else {
            // Échec
            $message .= "❌ ÉCHEC. Impossible de s'arrimer.\n";
            $message .= "Votre approche est trop erratique. Repositionnez-vous.\n";
            $message .= "Tentez à nouveau quand vous serez prêt.\n";

            return ['success' => false, 'message' => $message];
        }
    }

    /**
     * Désamarrer d'une station avec jet de pilotage
     */
    private function desarrimerStation(Personnage $personnage): array
    {
        if (!$personnage->vaisseauActif) {
            return [
                'success' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau.',
            ];
        }

        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau->arrime_a_station_id) {
            return [
                'success' => false,
                'message' => 'Votre vaisseau n\'est pas arrimé à une station.',
            ];
        }

        if ($personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être à bord de votre vaisseau. Utilisez "embarquer" d\'abord.',
            ];
        }

        $station = \App\Models\Station::find($vaisseau->arrime_a_station_id);

        // JET DE PILOTAGE DAGGERHEART
        $competence = $personnage->competences['pilotage'] ?? 0;
        $jet = $personnage->lancerDes($competence);
        $personnage->save();

        $message = "\n=== MANŒUVRE DE DÉSAMARRAGE ===\n";
        $message .= "Station: {$station->nom}\n";
        $message .= "Hope: {$jet['hope']} | Fear: {$jet['fear']} | Compétence: +{$competence}\n";
        $message .= "Total: {$jet['total']}\n\n";

        if ($jet['critique']) {
            if ($jet['hope'] >= 10) {
                // Critique positif
                $vaisseau->arrime_a_station_id = null;
                $vaisseau->arrime_le = null;
                $vaisseau->dernier_jet_pilotage = null;
                $vaisseau->save();

                $message .= "🎯 CRITIQUE AVEC HOPE! Départ parfait!\n";
                $message .= "Manœuvre de désamarrage impeccable. Navigation libre.\n";

                return ['success' => true, 'message' => $message];
            } else {
                // Critique négatif
                $dommages = rand(10, 20);
                $message .= "💥 CRITIQUE AVEC FEAR! Collision au départ!\n";
                $message .= "Vous arrachez les amarres trop brutalement.\n";
                $message .= "Dommages: -{$dommages}% intégrité coque.\n";
                $message .= "Désamarrage forcé. Vérifiez vos systèmes.\n";

                $vaisseau->arrime_a_station_id = null;
                $vaisseau->arrime_le = null;
                $vaisseau->save();

                return ['success' => true, 'message' => $message];
            }
        }

        if ($jet['total'] >= 12) {
            // Succès franc
            $vaisseau->arrime_a_station_id = null;
            $vaisseau->arrime_le = null;
            $vaisseau->dernier_jet_pilotage = null;
            $vaisseau->save();

            $message .= "✅ SUCCÈS! Désamarrage réussi.\n";
            $message .= "Vous quittez {$station->nom}. Navigation libre.\n";

            return ['success' => true, 'message' => $message];
        } elseif ($jet['total'] >= 9) {
            // Succès partiel
            $vaisseau->arrime_a_station_id = null;
            $vaisseau->arrime_le = null;
            $vaisseau->save();

            $message .= "⚠️  SUCCÈS PARTIEL. Départ laborieux.\n";
            $message .= "Vous parvenez à vous dégager après quelques manœuvres.\n";
            $message .= "Coût PA: +1.\n";
            $personnage->consommerPA(1);
            $personnage->save();

            return ['success' => true, 'message' => $message];
        } else {
            // Échec
            $message .= "❌ ÉCHEC. Impossible de désamarrer.\n";
            $message .= "Les amarres restent bloquées. Tentez à nouveau.\n";

            return ['success' => false, 'message' => $message];
        }
    }

    /**
     * Transborder du vaisseau vers la station
     */
    private function transborderStation(Personnage $personnage): array
    {
        if (!$personnage->vaisseauActif) {
            return [
                'success' => false,
                'message' => 'Vous devez être à bord d\'un vaisseau.',
            ];
        }

        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau->arrime_a_station_id) {
            return [
                'success' => false,
                'message' => 'Votre vaisseau doit être arrimé à une station. Utilisez "arrimer" d\'abord.',
            ];
        }

        if ($personnage->dans_station_id) {
            $station = \App\Models\Station::find($personnage->dans_station_id);
            return [
                'success' => false,
                'message' => "Vous êtes déjà dans {$station->nom}.",
            ];
        }

        $station = \App\Models\Station::find($vaisseau->arrime_a_station_id);

        $personnage->dans_station_id = $station->id;
        $personnage->save();

        $message = "\n=== TRANSBORDEMENT ===\n";
        $message .= "Vous quittez votre vaisseau et entrez dans {$station->nom}.\n\n";
        $message .= "Services disponibles:\n";

        $services = [];
        if ($station->commerciale) $services[] = "- 'marche' : Acheter/vendre des marchandises";
        if ($station->reparations) $services[] = "- 'garage' : Réparer et améliorer votre vaisseau";
        if ($station->medical) $services[] = "- 'hopital' : Soins médicaux";
        if ($station->industrielle) $services[] = "- 'industrie' : Raffinage et fabrication";
        if ($station->ravitaillement) $services[] = "- 'ravitailler' : Recharger carburant et provisions";
        $services[] = "- 'comptoirs' : Missions, guildes, informations";
        $services[] = "- 'embarquer' : Retourner à votre vaisseau";

        $message .= implode("\n", $services);

        return ['success' => true, 'message' => $message];
    }

    /**
     * Embarquer de la station vers le vaisseau
     */
    private function embarquerVaisseau(Personnage $personnage): array
    {
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous êtes déjà à bord de votre vaisseau.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau || $vaisseau->arrime_a_station_id != $station->id) {
            return [
                'success' => false,
                'message' => 'Votre vaisseau n\'est pas arrimé à cette station.',
            ];
        }

        $personnage->dans_station_id = null;
        $personnage->save();

        $message = "\n=== EMBARQUEMENT ===\n";
        $message .= "Vous quittez {$station->nom} et retournez à bord de votre vaisseau.\n";
        $message .= "Utilisez 'desarrimer' pour quitter la station.\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Accéder au garage
     */
    private function accederGarage(Personnage $personnage): array
    {
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station. Utilisez "transborder" d\'abord.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->reparations) {
            return [
                'success' => false,
                'message' => "{$station->nom} n\'a pas de garage.",
            ];
        }

        $vaisseau = $personnage->vaisseauActif;

        $message = "\n=== GARAGE DE {$station->nom} ===\n\n";
        $message .= "Bienvenue au garage !\n\n";
        $message .= "État de votre vaisseau:\n";
        $message .= "- Nom: {$vaisseau->nom}\n";
        $message .= "- Intégrité coque: 100%\n"; // TODO: système de dommages
        $message .= "- Moteurs: Opérationnels\n";
        $message .= "- Boucliers: Opérationnels\n\n";
        $message .= "Services disponibles:\n";
        $message .= "- 'reparer [système]' : Réparer un système endommagé\n";
        $message .= "- Améliorations disponibles prochainement\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Accéder aux comptoirs
     */
    private function accederComptoirs(Personnage $personnage): array
    {
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station. Utilisez "transborder" d\'abord.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        $message = "\n=== QUARTIER DES COMPTOIRS - {$station->nom} ===\n\n";
        $message .= "Vous entrez dans le quartier des comptoirs, lieu d'affaires et de rencontres.\n\n";
        $message .= "Lieux disponibles:\n";
        $message .= "- 'missions' : Consulter les missions disponibles\n";
        $message .= "- 'guildes' : Parler aux représentants des guildes\n";
        $message .= "- 'factions' : Voir votre réputation\n";
        $message .= "- 'bar' : Se rendre au bar (rumeurs, informations)\n";
        $message .= "- Boutiques spécialisées (bientôt disponibles)\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Accéder à l'hôpital
     */
    private function accederHopital(Personnage $personnage): array
    {
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station. Utilisez "transborder" d\'abord.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->medical) {
            return [
                'success' => false,
                'message' => "{$station->nom} n\'a pas d\'hôpital.",
            ];
        }

        $message = "\n=== HÔPITAL DE {$station->nom} ===\n\n";
        $message .= "Bienvenue au centre médical.\n\n";
        $message .= "Votre état de santé:\n";
        $message .= "- Santé: 100%\n"; // TODO: système de santé
        $message .= "- Aucune blessure\n\n";
        $message .= "Services disponibles:\n";
        $message .= "- 'soigner' : Soigner toutes les blessures (50 crédits)\n";
        $message .= "- Cybernétique disponible prochainement\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Accéder au quartier industriel
     */
    private function accederIndustrie(Personnage $personnage): array
    {
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station. Utilisez "transborder" d\'abord.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->industrielle) {
            return [
                'success' => false,
                'message' => "{$station->nom} n\'a pas de quartier industriel.",
            ];
        }

        $message = "\n=== QUARTIER INDUSTRIEL - {$station->nom} ===\n\n";
        $message .= "Vous entrez dans le quartier industriel, cœur de la production.\n\n";
        $message .= "Services disponibles:\n";
        $message .= "- 'recettes' : Voir les recettes de fabrication\n";
        $message .= "- 'fabriquer [recette]' : Fabriquer un objet\n";
        $message .= "- Raffinage disponible prochainement\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Ravitailler le vaisseau
     */
    private function ravitaillerVaisseau(Personnage $personnage, array $parts): array
    {
        if (!$personnage->dans_station_id) {
            return [
                'success' => false,
                'message' => 'Vous devez être dans une station. Utilisez "transborder" d\'abord.',
            ];
        }

        $station = \App\Models\Station::find($personnage->dans_station_id);

        if (!$station->ravitaillement) {
            return [
                'success' => false,
                'message' => "{$station->nom} n\'offre pas de services de ravitaillement.",
            ];
        }

        $vaisseau = $personnage->vaisseauActif;
        $coutTotal = 100; // TODO: calculer selon besoins réels

        $message = "\n=== RAVITAILLEMENT - {$station->nom} ===\n\n";
        $message .= "Services de ravitaillement:\n";
        $message .= "- Carburant: Complet\n";
        $message .= "- Eau potable: Rechargée\n";
        $message .= "- Oxygène: Réservoirs pleins\n";
        $message .= "- Rations: Stock complet\n\n";
        $message .= "Votre vaisseau est prêt pour un long voyage !\n";

        return ['success' => true, 'message' => $message];

    }

    /**
     * Recharger l'énergie du vaisseau depuis une étoile (Type B uniquement)
     */
    private function rechargerVaisseau(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return [
                'success' => false,
                'message' => '[ERREUR] Aucun vaisseau actif.',
            ];
        }

        // Parser le nombre de PA (défaut: 1) — 'full'/'max'/'tout' = tous les PA disponibles
        $arg = $parts[1] ?? '1';
        $nb_pa = in_array(strtolower($arg), ['full', 'max', 'tout'])
            ? $personnage->points_action
            : (int)$arg;

        if ($nb_pa < 1) {
            return [
                'success' => false,
                'message' => '[ERREUR] Le nombre de PA doit être >= 1.',
            ];
        }

        // Vérifier que le personnage a assez de PA
        if ($personnage->points_action < $nb_pa) {
            return [
                'success' => false,
                'message' => "[ERREUR] PA insuffisants. Vous avez {$personnage->points_action} PA, il faut {$nb_pa} PA.",
            ];
        }

        // Effectuer le rechargement
        $result = $vaisseau->rechargerDepuisEtoile($nb_pa, false);

        // Si réussi, dépenser les PA
        if ($result['success']) {
            $personnage->consommerPA($nb_pa);
            $personnage->save();

            // Ajouter info PA au message
            $result['message'] .= "\n\nPA restants: {$personnage->points_action}/{$personnage->max_points_action}";
        }

        return $result;
    }

    /**
     * [ADMIN] Recharger l'énergie d'un vaisseau (fonctionne pour tous types)
     */
    private function adminRecharger(Personnage $personnage, array $parts): array
    {
        // Parser le nombre de PA (défaut: 1) — 'full'/'max'/'tout' = tous les PA disponibles
        $arg = $parts[1] ?? '1';
        $nb_pa = in_array(strtolower($arg), ['full', 'max', 'tout'])
            ? $personnage->points_action
            : (int)$arg;

        if ($nb_pa < 1) {
            return [
                'success' => false,
                'message' => '[ERREUR] Le nombre de PA doit être >= 1.',
            ];
        }

        $vaisseau = $personnage->vaisseauActif;

        if (!$vaisseau) {
            return [
                'success' => false,
                'message' => '[ERREUR] Aucun vaisseau actif.',
            ];
        }

        // Effectuer le rechargement en mode admin (bypass restrictions)
        $result = $vaisseau->rechargerDepuisEtoile($nb_pa, true);

        if ($result['success']) {
            $result['message'] = "[ADMIN] " . $result['message'];
            $result['message'] .= "\n[ADMIN] Mode administrateur - Restrictions de type ignorées.";
        }

        return $result;
    }

    // ========== CARTE DE L'UNIVERS ==========

    /**
     * Afficher la carte de l'univers (systèmes découverts uniquement)
     */
    public function carte(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return redirect()->route('personnage.selection')
                ->with('error', 'Veuillez sélectionner un personnage');
        }

        // Position par défaut : position du vaisseau du joueur
        $defaultX = 0;
        $defaultY = 0;
        $defaultZ = 0;
        if ($personnage->vaisseauActif && $personnage->vaisseauActif->objetSpatial) {
            $os = $personnage->vaisseauActif->objetSpatial;
            $defaultX = $os->secteur_x;
            $defaultY = $os->secteur_y;
            $defaultZ = $os->secteur_z;
        }

        // Paramètres de la carte
        $plan = $request->get('plan', 'Y'); // Y, X ou Z
        $centerX = (int) $request->get('x', $defaultX);
        $centerY = (int) $request->get('y', $defaultY);
        $centerZ = (int) $request->get('z', $defaultZ);

        // Taille de la carte pour les joueurs : toujours 21 AL × 21 AL (halfSize = 10)
        // La carte admin (route admin.index) a une taille différente
        $isAdmin = false; // Sur la route joueur, toujours en mode joueur
        $halfSize = 10; // 21×21 AL pour les joueurs

        // Récupérer tous les systèmes découverts par le personnage
        $decouvertes = $personnage->decouvertes()->with('systemeStellaire')->get();

        // Construire la grille des systèmes découverts
        $grille = [];
        foreach ($decouvertes as $decouverte) {
            $systeme = $decouverte->systemeStellaire;
            if ($systeme) {
                $grille[$systeme->secteur_x][$systeme->secteur_y][$systeme->secteur_z] = $systeme;
            }
        }

        // Position actuelle du personnage (si disponible)
        $positionActuelle = null;
        if ($personnage->vaisseauActif) {
            $objet = $personnage->vaisseauActif->objetSpatial;
            if ($objet) {
                $positionActuelle = [
                    'x' => $objet->secteur_x,
                    'y' => $objet->secteur_y,
                    'z' => $objet->secteur_z,
                ];
            }
        }

        // Secteurs connus = uniquement les secteurs qui contiennent un système découvert
        // Pas de calcul de rayon, c'est la base de données qui décide
        $knownSectors = [];
        foreach ($grille as $gx => $gridX) {
            foreach ($gridX as $gy => $gridY) {
                foreach ($gridY as $gz => $sys) {
                    // Marquer uniquement le secteur exact du système comme connu
                    $knownSectors["{$gx},{$gy},{$gz}"] = true;
                }
            }
        }

        // Si requête AJAX, retourner seulement le contenu de la carte
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('game.partials.carte-content', compact(
                'grille',
                'plan',
                'centerX',
                'centerY',
                'centerZ',
                'positionActuelle',
                'personnage',
                'halfSize',
                'isAdmin',
                'knownSectors'
            ));
        }

        return view('game.carte', compact(
            'grille',
            'plan',
            'centerX',
            'centerY',
            'centerZ',
            'positionActuelle',
            'personnage',
            'halfSize',
            'isAdmin',
            'knownSectors'
        ));
    }

    /**
     * Afficher les détails d'un secteur (systèmes découverts uniquement)
     */
    public function carteSecteur(Request $request, $x, $y, $z)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response('Personnage introuvable', 404);
        }

        // Récupérer tous les systèmes découverts dans ce secteur
        $systemesDecouverts = $personnage->decouvertes()
            ->with('systemeStellaire')
            ->get()
            ->pluck('systemeStellaire')
            ->filter(function($systeme) use ($x, $y, $z) {
                return $systeme &&
                       $systeme->secteur_x == $x &&
                       $systeme->secteur_y == $y &&
                       $systeme->secteur_z == $z;
            });

        // Charger les relations pour chaque système découvert
        $systemes = collect();
        foreach ($systemesDecouverts as $systeme) {
            $systemeWithRelations = \App\Models\SystemeStellaire::where('id', $systeme->id)
                ->with([
                    'planetes.gisements.ressource',
                    'planetes.stations'
                ])
                ->first();
            if ($systemeWithRelations) {
                $systemes->push($systemeWithRelations);
            }
        }

        return view('game.carte-secteur', compact('x', 'y', 'z', 'systemes', 'personnage'));
    }
}
