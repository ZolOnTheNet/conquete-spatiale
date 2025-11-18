<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Compte;
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

        return view('game.selection-personnage', [
            'personnages' => $personnages,
            'compte' => $compte,
        ]);
    }

    public function creerPersonnage(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
        ]);

        $compte = $request->user();

        // Créer le personnage
        $personnage = Personnage::create([
            'compte_id' => $compte->id,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'] ?? null,
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

        // Si c'est le premier personnage, le définir comme principal
        if (!$compte->perso_principal) {
            $compte->perso_principal = $personnage->id;
            $compte->save();
        }

        return redirect()->route('personnage.selection')
            ->with('success', 'Personnage créé avec succès !');
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

    public function dashboard(Request $request): View
    {
        // Récupérer le personnage depuis le middleware
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            // Fallback si middleware pas utilisé
            $compte = $request->user();
            $personnage = $compte->personnagePrincipal;
        }

        $personnage->load(['vaisseauActif.objetSpatial']);

        return view('game.console', [
            'personnage' => $personnage,
        ]);
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

        return response()->json($result);
    }

    private function processCommand(string $command, Personnage $personnage): array
    {
        $parts = explode(' ', trim($command));
        $action = strtolower($parts[0] ?? '');

        return match ($action) {
            'help', 'aide' => $this->showHelp(),
            'status', 'statut' => $this->showStatus($personnage),
            'position', 'pos' => $this->showPosition($personnage),
            'vaisseau', 'ship' => $this->showShip($personnage),
            'lancer', 'roll' => $this->rollDice($personnage, $parts),
            'deplacer', 'move' => $this->moveShip($personnage, $parts),
            'saut', 'jump' => $this->jumpHyperspace($personnage, $parts),
            'scan', 'scanner' => $this->scanSystems($personnage, $parts),
            'carte', 'map' => $this->showMap($personnage),
            '' => ['success' => true, 'message' => ''],
            default => [
                'success' => false,
                'message' => "Commande inconnue: {$action}. Tapez 'help' pour voir les commandes disponibles.",
            ],
        };
    }

    private function showHelp(): array
    {
        return [
            'success' => true,
            'message' => "
COMMANDES DISPONIBLES:
  help, aide                  - Afficher cette aide
  status, statut              - Afficher le statut du personnage
  position, pos               - Afficher la position actuelle
  vaisseau, ship              - Afficher les infos du vaisseau
  lancer [competence]         - Lancer les dés (système Daggerheart 2d12)
  deplacer [sx] [sy] [sz]     - Déplacer (conventionnel) vers secteur
  deplacer [sx] [sy] [sz] [px] [py] [pz] - Déplacer avec position précise
  saut [sx] [sy] [sz]         - Saut hyperespace vers secteur
  scan [rayon]                - Scanner systèmes dans un rayon (défaut: 5 AL)
  carte, map                  - Afficher carte des systèmes découverts
            ",
        ];
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

        return ['success' => true, 'message' => $message];
    }

    private function moveShip(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        // Parser coordonnées: deplacer sx sy sz [px py pz]
        if (count($parts) < 4) {
            return [
                'success' => false,
                'message' => "Usage: deplacer [secteur_x] [secteur_y] [secteur_z] [position_x] [position_y] [position_z]\nExemple: deplacer 0 0 0 ou deplacer 1 2 3 0.5 0.3 0.1",
            ];
        }

        $secteur_x = (float)($parts[1] ?? 0);
        $secteur_y = (float)($parts[2] ?? 0);
        $secteur_z = (float)($parts[3] ?? 0);
        $position_x = (float)($parts[4] ?? 0);
        $position_y = (float)($parts[5] ?? 0);
        $position_z = (float)($parts[6] ?? 0);

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
        // Paramètre optionnel: rayon de scan
        $rayon = isset($parts[1]) && is_numeric($parts[1]) ? (float)$parts[1] : 5.0;

        if ($rayon <= 0 || $rayon > 50) {
            return [
                'success' => false,
                'message' => 'Rayon invalide. Utilisez un rayon entre 0.1 et 50 années-lumière.',
            ];
        }

        // Lancer le scan
        $resultat = $personnage->scannerSystemes($rayon);

        if (!$resultat['succes']) {
            return [
                'success' => false,
                'message' => $resultat['message'],
            ];
        }

        // Formater résultat
        $message = "\n=== SCAN SPATIAL (Rayon: {$rayon} AL) ===\n";
        $message .= "Systèmes trouvés: {$resultat['systemes_trouves']}\n";

        if ($resultat['deja_connus'] > 0) {
            $message .= "Déjà connus: {$resultat['deja_connus']}\n";
        }

        if (count($resultat['decouvertes']) > 0) {
            $message .= "\n--- NOUVELLES DÉCOUVERTES ---\n";

            foreach ($resultat['decouvertes'] as $decouverte) {
                $message .= "\n• {$decouverte['systeme']} ({$decouverte['distance']} AL)\n";
                $message .= "  Jet: {$decouverte['resultat_jet']} / Seuil: {$decouverte['seuil']}\n";

                if ($decouverte['detecte']) {
                    $details = $decouverte['details'];
                    $message .= "  ✓ DÉTECTÉ\n";
                    $message .= "  Type: Étoile {$details['type_etoile']} ({$details['couleur']})\n";
                    $message .= "  Planètes: {$details['nb_planetes']}\n";
                } else {
                    $message .= "  ○ Signal faible (coordonnées enregistrées)\n";
                }
            }
        } else {
            $message .= "\nAucun nouveau système découvert dans ce rayon.\n";
        }

        $message .= "\nUtilisez 'carte' pour voir tous vos systèmes découverts.";

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
}
