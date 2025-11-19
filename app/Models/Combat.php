<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Combat extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaisseau_id',
        'ennemi_id',
        'statut',
        'tour',
        'ennemi_coque',
        'ennemi_bouclier',
        'log',
        'credits_gagnes',
        'xp_gagne',
        'butin',
        'coord_x',
        'coord_y',
        'coord_z',
    ];

    protected $casts = [
        'tour' => 'integer',
        'ennemi_coque' => 'integer',
        'ennemi_bouclier' => 'integer',
        'log' => 'array',
        'credits_gagnes' => 'integer',
        'xp_gagne' => 'integer',
        'butin' => 'array',
        'coord_x' => 'integer',
        'coord_y' => 'integer',
        'coord_z' => 'integer',
    ];

    /**
     * Vaisseau du joueur
     */
    public function vaisseau(): BelongsTo
    {
        return $this->belongsTo(Vaisseau::class);
    }

    /**
     * Ennemi combattu
     */
    public function ennemi(): BelongsTo
    {
        return $this->belongsTo(Ennemi::class);
    }

    /**
     * Combat en cours pour un vaisseau
     */
    public static function enCours(int $vaisseau_id): ?self
    {
        return self::where('vaisseau_id', $vaisseau_id)
            ->where('statut', 'en_cours')
            ->first();
    }

    /**
     * Commencer un nouveau combat
     */
    public static function commencer(Vaisseau $vaisseau, Ennemi $ennemi): self
    {
        return self::create([
            'vaisseau_id' => $vaisseau->id,
            'ennemi_id' => $ennemi->id,
            'statut' => 'en_cours',
            'tour' => 1,
            'ennemi_coque' => $ennemi->coque_max,
            'ennemi_bouclier' => $ennemi->bouclier_max,
            'log' => [],
            'coord_x' => $vaisseau->coord_x,
            'coord_y' => $vaisseau->coord_y,
            'coord_z' => $vaisseau->coord_z,
        ]);
    }

    /**
     * Ajouter une entree au log
     */
    public function ajouterLog(string $message, string $type = 'info'): void
    {
        $log = $this->log ?? [];
        $log[] = [
            'tour' => $this->tour,
            'type' => $type,
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ];
        $this->log = $log;
        $this->save();
    }

    /**
     * Executer un tour de combat
     */
    public function executerTour(): array
    {
        $vaisseau = $this->vaisseau;
        $ennemi = $this->ennemi;
        $resultat = [
            'tour' => $this->tour,
            'joueur' => [],
            'ennemi' => [],
            'statut' => 'en_cours',
        ];

        // Phase 1: Attaque du joueur
        $attaques_joueur = $vaisseau->tirerSalve($ennemi->esquive);
        $degats_total_joueur = 0;

        foreach ($attaques_joueur as $attaque) {
            if ($attaque['touche'] && $attaque['degats'] > 0) {
                $type_arme = $attaque['type_arme'] ?? 'laser';
                $impact = $ennemi->calculerDegatsRecus(
                    $attaque['degats'],
                    $type_arme,
                    $this->ennemi_bouclier
                );

                $this->ennemi_bouclier = $impact['nouveau_bouclier'];
                $this->ennemi_coque -= $impact['degats_coque'];
                $degats_total_joueur += $impact['degats_coque'] + $impact['degats_bouclier'];

                $resultat['joueur'][] = [
                    'touche' => true,
                    'degats' => $attaque['degats'],
                    'degats_effectifs' => $impact['degats_reduits'],
                    'degats_bouclier' => $impact['degats_bouclier'],
                    'degats_coque' => $impact['degats_coque'],
                ];
            } else {
                $resultat['joueur'][] = [
                    'touche' => false,
                    'degats' => 0,
                ];
            }
        }

        // Verifier destruction ennemi
        if ($this->ennemi_coque <= 0) {
            $this->ennemi_coque = 0;
            $this->statut = 'victoire';
            $recompenses = $ennemi->genererRecompenses();
            $this->credits_gagnes = $recompenses['credits'];
            $this->xp_gagne = $recompenses['xp'];
            $resultat['statut'] = 'victoire';
            $resultat['recompenses'] = $recompenses;

            $this->ajouterLog("Victoire! Ennemi detruit.", 'victoire');
            $this->save();

            // Mettre a jour vaisseau
            $vaisseau->en_combat = false;
            $vaisseau->save();

            return $resultat;
        }

        // Phase 2: Riposte de l'ennemi
        $action_ennemi = $ennemi->deciderAction(
            $this->ennemi_coque,
            $this->ennemi_bouclier,
            $vaisseau->coque_actuelle
        );

        if ($action_ennemi === 'fuir') {
            $this->statut = 'victoire';
            $recompenses = [
                'credits' => (int)($ennemi->genererRecompenses()['credits'] * 0.5),
                'xp' => (int)($ennemi->xp_recompense * 0.3),
            ];
            $this->credits_gagnes = $recompenses['credits'];
            $this->xp_gagne = $recompenses['xp'];
            $resultat['statut'] = 'fuite_ennemi';
            $resultat['recompenses'] = $recompenses;

            $this->ajouterLog("L'ennemi prend la fuite!", 'fuite');
            $this->save();

            $vaisseau->en_combat = false;
            $vaisseau->save();

            return $resultat;
        }

        if ($action_ennemi === 'regenerer') {
            $regen = min(
                $ennemi->bouclier_regen * 2,
                $ennemi->bouclier_max - $this->ennemi_bouclier
            );
            $this->ennemi_bouclier += $regen;
            $resultat['ennemi'][] = [
                'action' => 'regeneration',
                'valeur' => $regen,
            ];
            $this->ajouterLog("L'ennemi regenere {$regen} points de bouclier.", 'ennemi');
        } else {
            // Attaque
            $attaques_ennemi = $ennemi->attaquer($vaisseau->esquive);

            foreach ($attaques_ennemi as $attaque) {
                if ($attaque['touche'] && $attaque['degats'] > 0) {
                    $impact = $vaisseau->recevoirDegats($attaque['degats'], $ennemi->type_arme);

                    $resultat['ennemi'][] = [
                        'touche' => true,
                        'degats' => $attaque['degats'],
                        'degats_bouclier' => $impact['degats_bouclier'],
                        'degats_coque' => $impact['degats_coque'],
                    ];
                } else {
                    $resultat['ennemi'][] = [
                        'touche' => false,
                        'degats' => 0,
                    ];
                }
            }
        }

        // Verifier destruction joueur
        if ($vaisseau->isDetruit()) {
            $this->statut = 'defaite';
            $resultat['statut'] = 'defaite';
            $this->ajouterLog("Defaite! Votre vaisseau est detruit.", 'defaite');
            $this->save();

            $vaisseau->en_combat = false;
            $vaisseau->save();

            return $resultat;
        }

        // Regeneration boucliers
        $regen_joueur = $vaisseau->regenererBouclier();
        $regen_ennemi = min(
            $ennemi->bouclier_regen,
            $ennemi->bouclier_max - $this->ennemi_bouclier
        );
        $this->ennemi_bouclier += $regen_ennemi;

        $resultat['regeneration'] = [
            'joueur' => $regen_joueur,
            'ennemi' => $regen_ennemi,
        ];

        // Passer au tour suivant
        $this->tour++;
        $this->save();

        // Log du tour
        $this->ajouterLog("Tour {$this->tour}: Degats infliges: {$degats_total_joueur}", 'combat');

        return $resultat;
    }

    /**
     * Fuir le combat
     */
    public function fuir(): array
    {
        $vaisseau = $this->vaisseau;

        // Chance de fuite basee sur l'esquive
        $chance_fuite = 30 + ($vaisseau->esquive / 2);

        // Penalite si ennemi agressif
        if ($this->ennemi->tactique === 'agressif') {
            $chance_fuite -= 20;
        }

        $reussie = rand(1, 100) <= $chance_fuite;

        if ($reussie) {
            $this->statut = 'fuite';
            $this->ajouterLog("Fuite reussie!", 'fuite');
            $this->save();

            $vaisseau->en_combat = false;
            $vaisseau->save();

            return [
                'reussie' => true,
                'message' => 'Fuite reussie! Vous echappez au combat.',
            ];
        }

        // Echec: l'ennemi attaque gratuitement
        $attaques = $this->ennemi->attaquer($vaisseau->esquive);
        $degats_subis = 0;

        foreach ($attaques as $attaque) {
            if ($attaque['touche']) {
                $impact = $vaisseau->recevoirDegats($attaque['degats'], $this->ennemi->type_arme);
                $degats_subis += $impact['degats_coque'] + $impact['degats_bouclier'];
            }
        }

        $this->ajouterLog("Tentative de fuite echouee! L'ennemi inflige {$degats_subis} degats.", 'combat');
        $this->save();

        return [
            'reussie' => false,
            'degats_subis' => $degats_subis,
            'message' => "Fuite echouee! L'ennemi vous inflige {$degats_subis} degats.",
        ];
    }

    /**
     * Obtenir l'etat actuel du combat
     */
    public function getEtat(): array
    {
        $vaisseau = $this->vaisseau;
        $ennemi = $this->ennemi;

        return [
            'tour' => $this->tour,
            'statut' => $this->statut,
            'joueur' => [
                'nom' => $vaisseau->nom,
                'coque' => $vaisseau->coque_actuelle,
                'coque_max' => $vaisseau->coque_max,
                'coque_pourcent' => $vaisseau->getPourcentageCoque(),
                'bouclier' => $vaisseau->bouclier_actuel,
                'bouclier_max' => $vaisseau->bouclier ? $vaisseau->bouclier->points_max : 0,
                'bouclier_pourcent' => $vaisseau->getPourcentageBouclier(),
            ],
            'ennemi' => [
                'nom' => $ennemi->nom,
                'type' => $ennemi->type,
                'niveau' => $ennemi->niveau,
                'coque' => $this->ennemi_coque,
                'coque_max' => $ennemi->coque_max,
                'coque_pourcent' => ($this->ennemi_coque / $ennemi->coque_max) * 100,
                'bouclier' => $this->ennemi_bouclier,
                'bouclier_max' => $ennemi->bouclier_max,
                'bouclier_pourcent' => $ennemi->bouclier_max > 0
                    ? ($this->ennemi_bouclier / $ennemi->bouclier_max) * 100
                    : 0,
            ],
        ];
    }
}
