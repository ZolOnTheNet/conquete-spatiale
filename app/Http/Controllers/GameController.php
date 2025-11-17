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
            // Valeurs par défaut
            'agilite' => 2,
            'force' => 2,
            'finesse' => 2,
            'instinct' => 2,
            'presence' => 2,
            'savoir' => 2,
            'competences' => [],
            'experience' => 0,
            'niveau' => 1,
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

        $result = $this->processCommand($command, $personnage);

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
  help, aide           - Afficher cette aide
  status, statut       - Afficher le statut du personnage
  position, pos        - Afficher la position actuelle
  vaisseau, ship       - Afficher les infos du vaisseau
  lancer [competence]  - Lancer les dés (système Daggerheart 2d12)
  deplacer [x] [y] [z] - Déplacer le vaisseau (mode conventionnel)
            ",
        ];
    }

    private function showStatus(Personnage $personnage): array
    {
        return [
            'success' => true,
            'message' => "
=== STATUT PERSONNAGE ===
Nom: {$personnage->nom} {$personnage->prenom}
Niveau: {$personnage->niveau}
XP: {$personnage->experience}

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

        // TODO: implémenter déplacement réel
        return [
            'success' => false,
            'message' => 'Fonctionnalité en développement',
        ];
    }
}
