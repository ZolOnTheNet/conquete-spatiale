# Plan Sonnet #00B : Système de Dés Daggerheart Adapté

**PRIORITÉ : HAUTE**
**Objectif :** Implémenter un système de résolution basé sur Daggerheart avec compétences spatiales

---

## Rappel du système Daggerheart

### Principe des jets
- **2d12** : Un dé "Espoir" (Hope) et un dé "Peur" (Fear)
- Si Espoir > Peur → Succès avec Espoir (bonus narratif)
- Si Peur > Espoir → Succès avec Peur (complication)
- Si Espoir = Peur → Résultat critique (positif ou négatif selon contexte)

### Adaptation pour Conquête Spatiale
- Les compétences ajoutent des bonus au résultat
- Le MJ peut fixer une difficulté (seuil à atteindre)
- Le joueur choisit quelle compétence utiliser (si applicable)

---

## Les 10 Compétences Spatiales

| Compétence | Code | Description | Utilisations typiques |
|------------|------|-------------|----------------------|
| **Pilotage** | PIL | Manœuvrer un vaisseau | Combat spatial, atterrissage, poursuite |
| **Navigation** | NAV | Tracer des routes, lire des cartes stellaires | Sauts FTL, éviter obstacles, trouver chemins |
| **Ingénierie** | ING | Réparer, modifier, construire | Réparations urgentes, améliorations |
| **Systèmes** | SYS | Opérer les systèmes du vaisseau | Scanners, boucliers, communications |
| **Artillerie** | ART | Utiliser les armes du vaisseau | Tir de précision, barrages |
| **Survie** | SUR | Survivre dans l'espace/environnements hostiles | EVA, gestion ressources, premiers soins |
| **Commerce** | COM | Négocier, estimer valeurs | Négociation prix, contrebande |
| **Perception** | PER | Observer, détecter | Repérer dangers, analyser situations |
| **Influence** | INF | Convaincre, intimider, mentir | Diplomatie, bluff, commandement |
| **Piratage** | PIR | Pirater systèmes informatiques | Intrusion, désactiver sécurités |

---

## Étape 1 : Migration

**Commande :** `php artisan make:migration create_competences_system_tables`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Table des compétences (définitions)
        Schema::create('competences', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->string('code', 3)->unique();
            $table->text('description');
            $table->string('icone')->nullable(); // Pour affichage UI
            $table->json('exemples_utilisation')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Table pivot personnage-compétence
        Schema::create('personnage_competences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');
            $table->foreignId('competence_id')->constrained('competences')->onDelete('cascade');
            $table->integer('niveau')->default(0); // 0 à 5
            $table->integer('experience')->default(0); // XP vers niveau suivant
            $table->timestamps();

            $table->unique(['personnage_id', 'competence_id']);
        });

        // Table des jets de dés (historique)
        Schema::create('jets_des', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');
            $table->foreignId('competence_id')->nullable()->constrained('competences');

            // Résultats des dés
            $table->integer('de_espoir'); // Résultat du d12 Espoir
            $table->integer('de_peur');   // Résultat du d12 Peur
            $table->integer('bonus')->default(0);
            $table->integer('difficulte')->nullable();

            // Résultat
            $table->enum('resultat', ['succes_espoir', 'succes_peur', 'critique', 'echec']);
            $table->integer('total'); // de dominant + bonus
            $table->boolean('reussi')->default(false);

            // Contexte
            $table->string('contexte')->nullable(); // Description de l'action
            $table->text('notes_mj')->nullable();

            $table->timestamps();

            $table->index(['personnage_id', 'created_at']);
        });

        // Table des demandes de jet (pour interaction joueur)
        Schema::create('demandes_jet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');
            $table->foreignId('demandeur_id')->nullable()->constrained('personnages'); // MJ ou système

            // Configuration du jet
            $table->string('contexte'); // "Atterrissage d'urgence sur astéroïde"
            $table->json('competences_possibles'); // [1, 2, 5] IDs des compétences utilisables
            $table->integer('difficulte')->default(10);
            $table->json('consequences')->nullable(); // {succes: "...", echec: "..."}

            // État
            $table->enum('statut', ['en_attente', 'choix_fait', 'resolu', 'annule'])->default('en_attente');
            $table->foreignId('competence_choisie_id')->nullable()->constrained('competences');
            $table->foreignId('jet_id')->nullable()->constrained('jets_des');

            $table->timestamp('expire_at')->nullable();
            $table->timestamps();

            $table->index(['personnage_id', 'statut']);
        });

        // Insérer les 10 compétences
        $competences = [
            [
                'nom' => 'Pilotage',
                'code' => 'PIL',
                'description' => 'Capacité à manœuvrer un vaisseau spatial avec précision et rapidité.',
                'exemples_utilisation' => json_encode([
                    'Combat spatial',
                    'Atterrissage difficile',
                    'Poursuite',
                    'Évitement de débris',
                ]),
            ],
            [
                'nom' => 'Navigation',
                'code' => 'NAV',
                'description' => 'Art de tracer des routes à travers l\'espace et de lire les cartes stellaires.',
                'exemples_utilisation' => json_encode([
                    'Calcul de saut FTL',
                    'Trouver un raccourci',
                    'Éviter une zone dangereuse',
                    'Localiser un système',
                ]),
            ],
            [
                'nom' => 'Ingénierie',
                'code' => 'ING',
                'description' => 'Compétence technique pour réparer, modifier et construire des équipements.',
                'exemples_utilisation' => json_encode([
                    'Réparation urgente',
                    'Amélioration de systèmes',
                    'Bricolage improvisé',
                    'Maintenance préventive',
                ]),
            ],
            [
                'nom' => 'Systèmes',
                'code' => 'SYS',
                'description' => 'Maîtrise des systèmes embarqués : scanners, boucliers, communications.',
                'exemples_utilisation' => json_encode([
                    'Scan détaillé',
                    'Gestion des boucliers',
                    'Brouillage',
                    'Boost des systèmes',
                ]),
            ],
            [
                'nom' => 'Artillerie',
                'code' => 'ART',
                'description' => 'Précision et efficacité dans l\'utilisation des armes du vaisseau.',
                'exemples_utilisation' => json_encode([
                    'Tir de précision',
                    'Barrage défensif',
                    'Destruction d\'astéroïde',
                    'Tir de suppression',
                ]),
            ],
            [
                'nom' => 'Survie',
                'code' => 'SUR',
                'description' => 'Capacité à survivre dans des environnements hostiles et gérer les ressources.',
                'exemples_utilisation' => json_encode([
                    'Sortie EVA',
                    'Gestion O2/énergie',
                    'Premiers soins',
                    'Résistance aux radiations',
                ]),
            ],
            [
                'nom' => 'Commerce',
                'code' => 'COM',
                'description' => 'Talent pour négocier, estimer les valeurs et faire des affaires.',
                'exemples_utilisation' => json_encode([
                    'Négocier un prix',
                    'Évaluer une cargaison',
                    'Trouver un acheteur',
                    'Contrebande',
                ]),
            ],
            [
                'nom' => 'Perception',
                'code' => 'PER',
                'description' => 'Sens de l\'observation et capacité à détecter les détails importants.',
                'exemples_utilisation' => json_encode([
                    'Repérer une embuscade',
                    'Analyser une situation',
                    'Lire le langage corporel',
                    'Trouver des indices',
                ]),
            ],
            [
                'nom' => 'Influence',
                'code' => 'INF',
                'description' => 'Art de convaincre, intimider ou manipuler les autres.',
                'exemples_utilisation' => json_encode([
                    'Diplomatie',
                    'Intimidation',
                    'Bluff',
                    'Commandement d\'équipage',
                ]),
            ],
            [
                'nom' => 'Piratage',
                'code' => 'PIR',
                'description' => 'Compétence pour infiltrer et manipuler les systèmes informatiques.',
                'exemples_utilisation' => json_encode([
                    'Cracker une serrure',
                    'Désactiver une alarme',
                    'Voler des données',
                    'Prendre le contrôle',
                ]),
            ],
        ];

        foreach ($competences as $comp) {
            DB::table('competences')->insert(array_merge($comp, [
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_jet');
        Schema::dropIfExists('jets_des');
        Schema::dropIfExists('personnage_competences');
        Schema::dropIfExists('competences');
    }
};
```

---

## Étape 2 : Modèle Competence

**Fichier :** `app/Models/Competence.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competence extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'description',
        'icone',
        'exemples_utilisation',
        'actif',
    ];

    protected $casts = [
        'exemples_utilisation' => 'array',
        'actif' => 'boolean',
    ];

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'personnage_competences')
            ->withPivot(['niveau', 'experience'])
            ->withTimestamps();
    }

    /**
     * Bonus accordé par niveau
     * Niveau 0 = +0, Niveau 1 = +1, ..., Niveau 5 = +5
     */
    public static function bonusPourNiveau(int $niveau): int
    {
        return min(5, max(0, $niveau));
    }

    /**
     * XP nécessaire pour passer au niveau suivant
     */
    public static function xpPourNiveau(int $niveau): int
    {
        // Progression : 10, 25, 50, 100, 200
        return match($niveau) {
            0 => 10,
            1 => 25,
            2 => 50,
            3 => 100,
            4 => 200,
            default => 999999, // Max atteint
        };
    }
}
```

---

## Étape 3 : Modèle JetDes

**Fichier :** `app/Models/JetDes.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JetDes extends Model
{
    protected $table = 'jets_des';

    protected $fillable = [
        'personnage_id',
        'competence_id',
        'de_espoir',
        'de_peur',
        'bonus',
        'difficulte',
        'resultat',
        'total',
        'reussi',
        'contexte',
        'notes_mj',
    ];

    protected $casts = [
        'reussi' => 'boolean',
    ];

    public const RESULTAT_SUCCES_ESPOIR = 'succes_espoir';
    public const RESULTAT_SUCCES_PEUR = 'succes_peur';
    public const RESULTAT_CRITIQUE = 'critique';
    public const RESULTAT_ECHEC = 'echec';

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function competence(): BelongsTo
    {
        return $this->belongsTo(Competence::class);
    }

    /**
     * Obtenir la description du résultat
     */
    public function getResultatDescriptionAttribute(): string
    {
        return match($this->resultat) {
            self::RESULTAT_SUCCES_ESPOIR => 'Succès avec Espoir ! Bonus narratif positif.',
            self::RESULTAT_SUCCES_PEUR => 'Succès avec Peur. Une complication survient.',
            self::RESULTAT_CRITIQUE => $this->reussi
                ? 'Critique ! Succès exceptionnel !'
                : 'Critique... Échec catastrophique.',
            self::RESULTAT_ECHEC => 'Échec.',
            default => 'Résultat inconnu',
        };
    }

    /**
     * Couleur pour affichage
     */
    public function getCouleurResultatAttribute(): string
    {
        return match($this->resultat) {
            self::RESULTAT_SUCCES_ESPOIR => 'green',
            self::RESULTAT_SUCCES_PEUR => 'yellow',
            self::RESULTAT_CRITIQUE => $this->reussi ? 'blue' : 'red',
            self::RESULTAT_ECHEC => 'red',
            default => 'gray',
        };
    }
}
```

---

## Étape 4 : Modèle DemandeJet

**Fichier :** `app/Models/DemandeJet.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeJet extends Model
{
    protected $table = 'demandes_jet';

    protected $fillable = [
        'personnage_id',
        'demandeur_id',
        'contexte',
        'competences_possibles',
        'difficulte',
        'consequences',
        'statut',
        'competence_choisie_id',
        'jet_id',
        'expire_at',
    ];

    protected $casts = [
        'competences_possibles' => 'array',
        'consequences' => 'array',
        'expire_at' => 'datetime',
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_CHOIX_FAIT = 'choix_fait';
    public const STATUT_RESOLU = 'resolu';
    public const STATUT_ANNULE = 'annule';

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'demandeur_id');
    }

    public function competenceChoisie(): BelongsTo
    {
        return $this->belongsTo(Competence::class, 'competence_choisie_id');
    }

    public function jet(): BelongsTo
    {
        return $this->belongsTo(JetDes::class, 'jet_id');
    }

    /**
     * Obtenir les objets Competence possibles
     */
    public function getCompetencesPossiblesObjetsAttribute()
    {
        return Competence::whereIn('id', $this->competences_possibles)->get();
    }

    /**
     * Vérifier si la demande est expirée
     */
    public function estExpiree(): bool
    {
        return $this->expire_at && now()->isAfter($this->expire_at);
    }

    /**
     * Vérifier si en attente du choix joueur
     */
    public function attenteChoix(): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE && !$this->estExpiree();
    }
}
```

---

## Étape 5 : Service DaggerheartService

**Fichier :** `app/Services/DaggerheartService.php`

```php
<?php

namespace App\Services;

use App\Models\Competence;
use App\Models\DemandeJet;
use App\Models\JetDes;
use App\Models\Personnage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DaggerheartService
{
    /**
     * Lancer un d12
     */
    private function lancerD12(): int
    {
        return random_int(1, 12);
    }

    /**
     * Obtenir le niveau de compétence d'un personnage
     */
    public function getNiveauCompetence(Personnage $personnage, Competence $competence): int
    {
        $pivot = $personnage->competences()->find($competence->id);
        return $pivot?->pivot->niveau ?? 0;
    }

    /**
     * Calculer le bonus total pour un jet
     */
    public function calculerBonus(Personnage $personnage, ?Competence $competence): int
    {
        if (!$competence) {
            return 0;
        }

        $niveau = $this->getNiveauCompetence($personnage, $competence);
        return Competence::bonusPourNiveau($niveau);
    }

    /**
     * Effectuer un jet de dés
     */
    public function effectuerJet(
        Personnage $personnage,
        ?Competence $competence = null,
        ?int $difficulte = null,
        ?string $contexte = null
    ): JetDes {
        $deEspoir = $this->lancerD12();
        $dePeur = $this->lancerD12();
        $bonus = $this->calculerBonus($personnage, $competence);

        // Déterminer le résultat
        $resultat = $this->determinerResultat($deEspoir, $dePeur);

        // Calculer le total (dé dominant + bonus)
        $deDominant = $deEspoir >= $dePeur ? $deEspoir : $dePeur;
        $total = $deDominant + $bonus;

        // Vérifier si réussi (si difficulté définie)
        $reussi = true;
        if ($difficulte !== null) {
            $reussi = $total >= $difficulte;

            // En cas de critique (égalité), c'est toujours spécial
            if ($resultat === JetDes::RESULTAT_CRITIQUE) {
                // Critique = succès ou échec amplifié selon le contexte
                // On considère réussi si total >= difficulte - 2
                $reussi = $total >= ($difficulte - 2);
            }
        }

        // Créer l'enregistrement
        $jet = JetDes::create([
            'personnage_id' => $personnage->id,
            'competence_id' => $competence?->id,
            'de_espoir' => $deEspoir,
            'de_peur' => $dePeur,
            'bonus' => $bonus,
            'difficulte' => $difficulte,
            'resultat' => $resultat,
            'total' => $total,
            'reussi' => $reussi,
            'contexte' => $contexte,
        ]);

        Log::info("Jet de dés", [
            'personnage' => $personnage->id,
            'competence' => $competence?->code,
            'espoir' => $deEspoir,
            'peur' => $dePeur,
            'bonus' => $bonus,
            'total' => $total,
            'resultat' => $resultat,
            'reussi' => $reussi,
        ]);

        return $jet;
    }

    /**
     * Déterminer le type de résultat
     */
    private function determinerResultat(int $deEspoir, int $dePeur): string
    {
        if ($deEspoir === $dePeur) {
            return JetDes::RESULTAT_CRITIQUE;
        }

        if ($deEspoir > $dePeur) {
            return JetDes::RESULTAT_SUCCES_ESPOIR;
        }

        return JetDes::RESULTAT_SUCCES_PEUR;
    }

    /**
     * Créer une demande de jet (pour interroger le joueur)
     */
    public function creerDemandeJet(
        Personnage $personnage,
        string $contexte,
        array $competencesPossibles,
        int $difficulte = 10,
        ?array $consequences = null,
        ?Personnage $demandeur = null,
        ?int $expireMinutes = 5
    ): DemandeJet {
        return DemandeJet::create([
            'personnage_id' => $personnage->id,
            'demandeur_id' => $demandeur?->id,
            'contexte' => $contexte,
            'competences_possibles' => $competencesPossibles,
            'difficulte' => $difficulte,
            'consequences' => $consequences,
            'statut' => DemandeJet::STATUT_EN_ATTENTE,
            'expire_at' => $expireMinutes ? now()->addMinutes($expireMinutes) : null,
        ]);
    }

    /**
     * Le joueur choisit sa compétence et lance les dés
     */
    public function resoudreDemande(
        DemandeJet $demande,
        int $competenceId
    ): array {
        // Vérifications
        if ($demande->statut !== DemandeJet::STATUT_EN_ATTENTE) {
            return ['success' => false, 'message' => 'Cette demande a déjà été traitée.'];
        }

        if ($demande->estExpiree()) {
            $demande->update(['statut' => DemandeJet::STATUT_ANNULE]);
            return ['success' => false, 'message' => 'La demande a expiré.'];
        }

        if (!in_array($competenceId, $demande->competences_possibles)) {
            return ['success' => false, 'message' => 'Compétence non autorisée pour cette action.'];
        }

        $competence = Competence::find($competenceId);
        if (!$competence) {
            return ['success' => false, 'message' => 'Compétence introuvable.'];
        }

        return DB::transaction(function () use ($demande, $competence) {
            // Effectuer le jet
            $jet = $this->effectuerJet(
                $demande->personnage,
                $competence,
                $demande->difficulte,
                $demande->contexte
            );

            // Mettre à jour la demande
            $demande->update([
                'statut' => DemandeJet::STATUT_RESOLU,
                'competence_choisie_id' => $competence->id,
                'jet_id' => $jet->id,
            ]);

            // Donner de l'XP pour la compétence utilisée
            $this->ajouterExperience($demande->personnage, $competence, $jet->reussi ? 2 : 1);

            return [
                'success' => true,
                'jet' => [
                    'id' => $jet->id,
                    'de_espoir' => $jet->de_espoir,
                    'de_peur' => $jet->de_peur,
                    'bonus' => $jet->bonus,
                    'total' => $jet->total,
                    'difficulte' => $jet->difficulte,
                    'resultat' => $jet->resultat,
                    'resultat_description' => $jet->resultat_description,
                    'reussi' => $jet->reussi,
                    'couleur' => $jet->couleur_resultat,
                ],
                'competence_utilisee' => $competence->nom,
                'consequences' => $jet->reussi
                    ? ($demande->consequences['succes'] ?? null)
                    : ($demande->consequences['echec'] ?? null),
            ];
        });
    }

    /**
     * Ajouter de l'expérience à une compétence
     */
    public function ajouterExperience(Personnage $personnage, Competence $competence, int $xp): array
    {
        $pivot = $personnage->competences()->find($competence->id)?->pivot;

        if (!$pivot) {
            // Créer l'entrée si elle n'existe pas
            $personnage->competences()->attach($competence->id, [
                'niveau' => 0,
                'experience' => $xp,
            ]);
            return ['niveau' => 0, 'xp' => $xp, 'monte' => false];
        }

        $niveauActuel = $pivot->niveau;
        $xpActuel = $pivot->experience + $xp;
        $xpRequis = Competence::xpPourNiveau($niveauActuel);
        $monte = false;

        // Vérifier si on monte de niveau
        while ($xpActuel >= $xpRequis && $niveauActuel < 5) {
            $xpActuel -= $xpRequis;
            $niveauActuel++;
            $xpRequis = Competence::xpPourNiveau($niveauActuel);
            $monte = true;
        }

        $personnage->competences()->updateExistingPivot($competence->id, [
            'niveau' => $niveauActuel,
            'experience' => $xpActuel,
        ]);

        if ($monte) {
            Log::info("Montée de niveau", [
                'personnage' => $personnage->id,
                'competence' => $competence->nom,
                'nouveau_niveau' => $niveauActuel,
            ]);
        }

        return ['niveau' => $niveauActuel, 'xp' => $xpActuel, 'monte' => $monte];
    }

    /**
     * Obtenir les demandes en attente pour un personnage
     */
    public function getDemandesEnAttente(Personnage $personnage): array
    {
        return DemandeJet::where('personnage_id', $personnage->id)
            ->where('statut', DemandeJet::STATUT_EN_ATTENTE)
            ->where(function ($q) {
                $q->whereNull('expire_at')
                  ->orWhere('expire_at', '>', now());
            })
            ->with('competencesPossiblesObjets')
            ->get()
            ->map(function ($demande) {
                return [
                    'id' => $demande->id,
                    'contexte' => $demande->contexte,
                    'difficulte' => $demande->difficulte,
                    'competences' => $demande->competences_possibles_objets->map(fn($c) => [
                        'id' => $c->id,
                        'nom' => $c->nom,
                        'code' => $c->code,
                        'description' => $c->description,
                    ]),
                    'consequences' => $demande->consequences,
                    'expire_dans' => $demande->expire_at?->diffForHumans(),
                ];
            })
            ->toArray();
    }

    /**
     * Obtenir les compétences d'un personnage avec leurs niveaux
     */
    public function getCompetencesPersonnage(Personnage $personnage): array
    {
        $competences = Competence::where('actif', true)->get();

        return $competences->map(function ($comp) use ($personnage) {
            $pivot = $personnage->competences()->find($comp->id)?->pivot;
            $niveau = $pivot?->niveau ?? 0;
            $xp = $pivot?->experience ?? 0;
            $xpRequis = Competence::xpPourNiveau($niveau);

            return [
                'id' => $comp->id,
                'nom' => $comp->nom,
                'code' => $comp->code,
                'description' => $comp->description,
                'exemples' => $comp->exemples_utilisation,
                'niveau' => $niveau,
                'bonus' => Competence::bonusPourNiveau($niveau),
                'xp' => $xp,
                'xp_requis' => $xpRequis,
                'progression' => $xpRequis > 0 ? round(($xp / $xpRequis) * 100) : 100,
            ];
        })->toArray();
    }

    /**
     * Jet rapide sans demande (pour MJ ou système)
     */
    public function jetRapide(
        Personnage $personnage,
        string $codeCompetence,
        int $difficulte = 10,
        ?string $contexte = null
    ): JetDes {
        $competence = Competence::where('code', $codeCompetence)->first();
        return $this->effectuerJet($personnage, $competence, $difficulte, $contexte);
    }
}
```

---

## Étape 6 : Ajouter la relation dans Personnage

**Fichier :** `app/Models/Personnage.php`

Ajouter :

```php
use App\Models\Competence;
use App\Models\JetDes;
use App\Models\DemandeJet;

public function competences(): BelongsToMany
{
    return $this->belongsToMany(Competence::class, 'personnage_competences')
        ->withPivot(['niveau', 'experience'])
        ->withTimestamps();
}

public function jetsDes(): HasMany
{
    return $this->hasMany(JetDes::class);
}

public function demandesJet(): HasMany
{
    return $this->hasMany(DemandeJet::class);
}

public function demandesJetEnAttente(): HasMany
{
    return $this->hasMany(DemandeJet::class)
        ->where('statut', 'en_attente')
        ->where(fn($q) => $q->whereNull('expire_at')->orWhere('expire_at', '>', now()));
}
```

---

## Étape 7 : Contrôleur API pour les jets

**Fichier :** `app/Http/Controllers/JetDesController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\DaggerheartService;
use App\Models\DemandeJet;
use Illuminate\Http\Request;

class JetDesController extends Controller
{
    public function __construct(
        private DaggerheartService $daggerheart
    ) {}

    /**
     * Obtenir les compétences du personnage
     */
    public function competences(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response()->json(['error' => 'Personnage requis'], 401);
        }

        return response()->json([
            'competences' => $this->daggerheart->getCompetencesPersonnage($personnage),
        ]);
    }

    /**
     * Obtenir les demandes de jet en attente
     */
    public function demandesEnAttente(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response()->json(['error' => 'Personnage requis'], 401);
        }

        return response()->json([
            'demandes' => $this->daggerheart->getDemandesEnAttente($personnage),
        ]);
    }

    /**
     * Résoudre une demande de jet
     */
    public function resoudre(Request $request, DemandeJet $demande)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage || $demande->personnage_id !== $personnage->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'competence_id' => 'required|exists:competences,id',
        ]);

        $resultat = $this->daggerheart->resoudreDemande(
            $demande,
            $request->input('competence_id')
        );

        return response()->json($resultat);
    }

    /**
     * Historique des jets
     */
    public function historique(Request $request)
    {
        $personnage = $request->attributes->get('personnage');

        if (!$personnage) {
            return response()->json(['error' => 'Personnage requis'], 401);
        }

        $jets = $personnage->jetsDes()
            ->with('competence')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($jet) => [
                'id' => $jet->id,
                'date' => $jet->created_at->format('d/m H:i'),
                'contexte' => $jet->contexte,
                'competence' => $jet->competence?->nom,
                'de_espoir' => $jet->de_espoir,
                'de_peur' => $jet->de_peur,
                'bonus' => $jet->bonus,
                'total' => $jet->total,
                'difficulte' => $jet->difficulte,
                'resultat' => $jet->resultat,
                'reussi' => $jet->reussi,
            ]);

        return response()->json(['jets' => $jets]);
    }
}
```

---

## Étape 8 : Routes API

**Ajouter à `routes/api.php` :**

```php
use App\Http\Controllers\JetDesController;

Route::middleware(['auth:sanctum', 'personnage.actif'])->group(function () {
    Route::prefix('jets')->name('jets.')->group(function () {
        Route::get('/competences', [JetDesController::class, 'competences'])->name('competences');
        Route::get('/demandes', [JetDesController::class, 'demandesEnAttente'])->name('demandes');
        Route::post('/demandes/{demande}/resoudre', [JetDesController::class, 'resoudre'])->name('resoudre');
        Route::get('/historique', [JetDesController::class, 'historique'])->name('historique');
    });
});
```

---

## Étape 9 : Contrôleur Admin pour les jets

**Fichier :** `app/Http/Controllers/Admin/AdminJetDesController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competence;
use App\Models\Personnage;
use App\Services\DaggerheartService;
use Illuminate\Http\Request;

class AdminJetDesController extends Controller
{
    public function __construct(
        private DaggerheartService $daggerheart
    ) {}

    /**
     * Liste des compétences
     */
    public function competences()
    {
        $competences = Competence::all();
        return view('admin.jets.competences', compact('competences'));
    }

    /**
     * Créer une demande de jet pour un personnage
     */
    public function creerDemande(Request $request)
    {
        $validated = $request->validate([
            'personnage_id' => 'required|exists:personnages,id',
            'contexte' => 'required|string|max:255',
            'competences' => 'required|array|min:1',
            'competences.*' => 'exists:competences,id',
            'difficulte' => 'required|integer|min:1|max:30',
            'consequence_succes' => 'nullable|string',
            'consequence_echec' => 'nullable|string',
        ]);

        $personnage = Personnage::find($validated['personnage_id']);

        $demande = $this->daggerheart->creerDemandeJet(
            $personnage,
            $validated['contexte'],
            $validated['competences'],
            $validated['difficulte'],
            [
                'succes' => $validated['consequence_succes'],
                'echec' => $validated['consequence_echec'],
            ]
        );

        return back()->with('success', "Demande de jet créée pour {$personnage->nom}");
    }

    /**
     * Faire un jet pour un personnage (MJ)
     */
    public function jetMJ(Request $request)
    {
        $validated = $request->validate([
            'personnage_id' => 'required|exists:personnages,id',
            'competence_code' => 'required|exists:competences,code',
            'difficulte' => 'required|integer|min:1|max:30',
            'contexte' => 'nullable|string',
        ]);

        $personnage = Personnage::find($validated['personnage_id']);

        $jet = $this->daggerheart->jetRapide(
            $personnage,
            $validated['competence_code'],
            $validated['difficulte'],
            $validated['contexte']
        );

        return back()->with('success', sprintf(
            "Jet effectué: %s (%d+%d=%d) - %s",
            $jet->resultat,
            max($jet->de_espoir, $jet->de_peur),
            $jet->bonus,
            $jet->total,
            $jet->reussi ? 'RÉUSSI' : 'ÉCHOUÉ'
        ));
    }
}
```

---

## Étape 10 : Vue Blade pour l'interface joueur

**Fichier :** `resources/views/components/demande-jet.blade.php`

```blade
@props(['demande'])

<div class="bg-gray-800 border border-yellow-500 rounded-lg p-4 mb-4 demande-jet" data-demande-id="{{ $demande['id'] }}">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-yellow-400 text-2xl">🎲</span>
        <h3 class="text-lg font-bold text-yellow-400">Jet de dés requis</h3>
    </div>

    <p class="text-white mb-3">{{ $demande['contexte'] }}</p>

    <div class="text-sm text-gray-400 mb-3">
        Difficulté : <span class="font-bold text-white">{{ $demande['difficulte'] }}</span>
        @if($demande['expire_dans'])
            | Expire {{ $demande['expire_dans'] }}
        @endif
    </div>

    <p class="text-sm mb-4">Choisissez la compétence à utiliser :</p>

    <div class="grid grid-cols-2 gap-2 mb-4">
        @foreach($demande['competences'] as $comp)
        <button type="button"
                class="competence-btn bg-gray-700 hover:bg-blue-600 rounded p-3 text-left transition"
                data-competence-id="{{ $comp['id'] }}">
            <div class="font-bold">{{ $comp['nom'] }}</div>
            <div class="text-xs text-gray-400">{{ $comp['code'] }}</div>
        </button>
        @endforeach
    </div>

    @if(!empty($demande['consequences']))
    <div class="text-xs text-gray-500 border-t border-gray-700 pt-2">
        @if(!empty($demande['consequences']['succes']))
        <div class="text-green-400">✓ {{ $demande['consequences']['succes'] }}</div>
        @endif
        @if(!empty($demande['consequences']['echec']))
        <div class="text-red-400">✗ {{ $demande['consequences']['echec'] }}</div>
        @endif
    </div>
    @endif
</div>

<script>
document.querySelectorAll('.demande-jet').forEach(container => {
    container.querySelectorAll('.competence-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const demandeId = container.dataset.demandeId;
            const competenceId = btn.dataset.competenceId;

            try {
                const response = await fetch(`/api/jets/demandes/${demandeId}/resoudre`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ competence_id: competenceId })
                });

                const data = await response.json();

                if (data.success) {
                    afficherResultatJet(data.jet);
                    container.remove();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    });
});

function afficherResultatJet(jet) {
    // Créer un modal ou une notification avec animation des dés
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-gray-800 rounded-lg p-6 max-w-md text-center">
            <div class="flex justify-center gap-4 mb-4">
                <div class="text-4xl p-4 rounded bg-blue-600">🎲 ${jet.de_espoir}</div>
                <div class="text-4xl p-4 rounded bg-red-600">🎲 ${jet.de_peur}</div>
            </div>
            <div class="text-xl mb-2">
                ${jet.total} (${Math.max(jet.de_espoir, jet.de_peur)} + ${jet.bonus})
                vs Difficulté ${jet.difficulte}
            </div>
            <div class="text-2xl font-bold mb-4 text-${jet.couleur}-400">
                ${jet.reussi ? 'RÉUSSI !' : 'ÉCHOUÉ'}
            </div>
            <div class="text-sm text-gray-400 mb-4">${jet.resultat_description}</div>
            <button onclick="this.parentElement.parentElement.remove()"
                    class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded">
                Fermer
            </button>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>
```

---

## Vérification

```bash
php artisan make:migration create_competences_system_tables
php artisan migrate

php -l app/Models/Competence.php
php -l app/Models/JetDes.php
php -l app/Models/DemandeJet.php
php -l app/Services/DaggerheartService.php
php -l app/Http/Controllers/JetDesController.php

php artisan route:list --path=jets
```

---

## Utilisation par le MJ

```php
// Créer une demande de jet (le joueur choisira sa compétence)
$service = app(DaggerheartService::class);

$demande = $service->creerDemandeJet(
    $personnage,
    "Atterrissage d'urgence sur un astéroïde instable",
    [1, 2, 4], // Pilotage, Navigation, Systèmes
    14, // Difficulté
    [
        'succes' => 'Vous posez le vaisseau en douceur.',
        'echec' => 'Le vaisseau subit des dommages à l\'atterrissage.',
    ]
);

// OU jet rapide sans choix du joueur
$jet = $service->jetRapide($personnage, 'PIL', 12, "Évitement d'un débris");
```

---

## Critères de succès

- [ ] 10 compétences créées dans la BDD
- [ ] Système de jet 2d12 Espoir/Peur fonctionnel
- [ ] Demandes de jet avec choix du joueur
- [ ] XP et progression des compétences
- [ ] Interface de résolution pour le joueur
- [ ] Panel MJ pour créer des demandes

---

*Plan créé le 3 janvier 2026 pour Claude Code Sonnet*
