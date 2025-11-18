<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\Arme;
use App\Models\Bouclier;
use App\Models\Compte;
use App\Models\Gisement;
use App\Models\Marche;
use App\Models\Recette;
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
  saut [sx] [sy] [sz]         - Saut hyperespace vers secteur
  scan                        - Scanner zone (scan progressif, 1 PA)
  carte, map                  - Afficher carte des systèmes découverts

ECONOMIE & RESSOURCES:
  scan-planete, scanp [nom]   - Scanner gisements d'une planete
  extraire, mine [gisement_id] [quantite] - Extraire ressources
  inventaire, inv             - Afficher inventaire du vaisseau

MARCHES:
  marche, market              - Voir le marche local
  prix, prices [ressource]    - Voir les prix (ou tous)
  acheter, buy [code] [qte]   - Acheter des ressources
  vendre, sell [code] [qte]   - Vendre des ressources

FABRICATION:
  recettes, recipes [cat]     - Voir les recettes (ou par categorie)
  fabriquer, craft [code] [n] - Fabriquer une recette (x n fois)

COMBAT:
  armes, weapons              - Voir les armes disponibles
  boucliers, shields          - Voir les boucliers disponibles
  equiper, equip [type] [code] [slot] - Equiper arme/bouclier
  etat-combat, combat         - Voir etat combat du vaisseau
  reparer, repair [quantite]  - Reparer la coque
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

        // Trouver marchés sur les planètes du système
        $marches = Marche::whereHasMorph('localisation', [
            \App\Models\Planete::class,
        ], function ($query) use ($systeme) {
            $query->where('systeme_stellaire_id', $systeme->id);
        })->where('actif', true)->get();

        if ($marches->isEmpty()) {
            return [
                'success' => true,
                'message' => "\n=== MARCHES LOCAUX ===\nAucun marche actif dans ce systeme.\n",
            ];
        }

        $message = "\n=== MARCHES LOCAUX ({$systeme->nom}) ===\n\n";

        foreach ($marches as $marche) {
            $localisation = $marche->localisation;
            $nomLieu = $localisation ? $localisation->nom : 'Inconnu';

            $message .= "--- {$marche->nom} ---\n";
            $message .= "Type: {$marche->type}\n";
            $message .= "Localisation: {$nomLieu}\n";
            $message .= "Taxe: " . ($marche->taxe * 100) . "%\n";

            if ($marche->description) {
                $message .= "Info: {$marche->description}\n";
            }

            $message .= "\n";
        }

        $message .= "Utilisez 'prix' pour voir les tarifs ou 'acheter/vendre' pour commercer.";

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
     * Acheter des ressources au marché
     */
    private function acheterRessource(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => "Usage: acheter [code_ressource] [quantite]\nExemple: acheter FER 500",
            ];
        }

        $code = strtoupper($parts[1]);
        $quantite = (int)$parts[2];

        if ($quantite <= 0) {
            return ['success' => false, 'message' => 'Quantite invalide'];
        }

        // Trouver ressource
        $ressource = Ressource::where('code', $code)->first();
        if (!$ressource) {
            return ['success' => false, 'message' => "Ressource '{$code}' inconnue"];
        }

        // Trouver marché local
        $os = $vaisseau->objetSpatial;
        $systeme = SystemeStellaire::where('secteur_x', $os->secteur_x)
            ->where('secteur_y', $os->secteur_y)
            ->where('secteur_z', $os->secteur_z)
            ->first();

        if (!$systeme) {
            return ['success' => false, 'message' => 'Vous devez etre dans un systeme stellaire'];
        }

        $marche = Marche::whereHasMorph('localisation', [
            \App\Models\Planete::class,
        ], function ($query) use ($systeme) {
            $query->where('systeme_stellaire_id', $systeme->id);
        })->where('actif', true)->first();

        if (!$marche) {
            return ['success' => false, 'message' => 'Aucun marche actif dans ce systeme'];
        }

        // Vérifier capacité soute
        if (!$vaisseau->peutCharger($ressource->id, $quantite)) {
            $capacite = $vaisseau->getCapaciteRestante();
            $poids = $ressource->poids_unitaire * $quantite;
            return [
                'success' => false,
                'message' => "Capacite soute insuffisante.\nRequis: {$poids}t | Disponible: {$capacite}t",
            ];
        }

        // Calculer prix
        $prix_total = $marche->getPrixAchat($ressource->id) * $quantite;

        // Vérifier crédits
        if ($personnage->credits < $prix_total) {
            return [
                'success' => false,
                'message' => "Credits insuffisants.\nRequis: " . number_format($prix_total) . " | Disponible: " . number_format($personnage->credits),
            ];
        }

        // Effectuer l'achat
        $resultat = $marche->acheter($ressource->id, $quantite);

        if (!$resultat['success']) {
            return $resultat;
        }

        // Débiter crédits et ajouter au vaisseau
        $personnage->credits -= $resultat['prix_total'];
        $personnage->save();

        $vaisseau->ajouterRessource($ressource->id, $quantite);

        $message = "\n=== ACHAT EFFECTUE ===\n";
        $message .= "Ressource: {$ressource->nom} ({$code})\n";
        $message .= "Quantite: " . number_format($quantite) . "\n";
        $message .= "Prix total: " . number_format($resultat['prix_total']) . " credits\n";
        $message .= "Credits restants: " . number_format($personnage->credits) . "\n";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Vendre des ressources au marché
     */
    private function vendreRessource(Personnage $personnage, array $parts): array
    {
        $vaisseau = $personnage->vaisseauActif;
        if (!$vaisseau) {
            return ['success' => false, 'message' => 'Aucun vaisseau actif'];
        }

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => "Usage: vendre [code_ressource] [quantite]\nExemple: vendre FER 500",
            ];
        }

        $code = strtoupper($parts[1]);
        $quantite = (int)$parts[2];

        if ($quantite <= 0) {
            return ['success' => false, 'message' => 'Quantite invalide'];
        }

        // Trouver ressource
        $ressource = Ressource::where('code', $code)->first();
        if (!$ressource) {
            return ['success' => false, 'message' => "Ressource '{$code}' inconnue"];
        }

        // Vérifier inventaire
        $quantite_dispo = $vaisseau->getQuantiteRessource($ressource->id);
        if ($quantite_dispo < $quantite) {
            return [
                'success' => false,
                'message' => "Quantite insuffisante.\nDisponible: " . number_format($quantite_dispo),
            ];
        }

        // Trouver marché local
        $os = $vaisseau->objetSpatial;
        $systeme = SystemeStellaire::where('secteur_x', $os->secteur_x)
            ->where('secteur_y', $os->secteur_y)
            ->where('secteur_z', $os->secteur_z)
            ->first();

        if (!$systeme) {
            return ['success' => false, 'message' => 'Vous devez etre dans un systeme stellaire'];
        }

        $marche = Marche::whereHasMorph('localisation', [
            \App\Models\Planete::class,
        ], function ($query) use ($systeme) {
            $query->where('systeme_stellaire_id', $systeme->id);
        })->where('actif', true)->first();

        if (!$marche) {
            return ['success' => false, 'message' => 'Aucun marche actif dans ce systeme'];
        }

        // Effectuer la vente
        $resultat = $marche->vendre($ressource->id, $quantite);

        if (!$resultat['success']) {
            return $resultat;
        }

        // Retirer du vaisseau et créditer
        $vaisseau->retirerRessource($ressource->id, $quantite);
        $personnage->credits += $resultat['prix_total'];
        $personnage->save();

        $message = "\n=== VENTE EFFECTUEE ===\n";
        $message .= "Ressource: {$ressource->nom} ({$code})\n";
        $message .= "Quantite: " . number_format($quantite) . "\n";
        $message .= "Prix total: " . number_format($resultat['prix_total']) . " credits\n";
        $message .= "Credits: " . number_format($personnage->credits) . "\n";

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
}
