# Plan Sonnet #03 : Système de Factions et Réputation

**Objectif :** Créer un système de factions avec gestion de la réputation des joueurs

---

## Vue d'ensemble

Les factions sont des organisations dans l'univers du jeu. Les joueurs gagnent ou perdent de la réputation auprès de ces factions, ce qui débloque des missions, des marchés et des avantages.

---

## Étape 1 : Vérifier le modèle Faction existant

Vérifier si `app/Models/Faction.php` existe. Si non, le créer.

**Fichier :** `app/Models/Faction.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faction extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'type',
        'couleur',
        'logo_url',
        'systeme_base_id',
        'hostile_envers', // JSON array d'IDs de factions hostiles
        'allie_avec',     // JSON array d'IDs de factions alliées
        'actif',
    ];

    protected $casts = [
        'hostile_envers' => 'array',
        'allie_avec' => 'array',
        'actif' => 'boolean',
    ];

    // Relations
    public function systemeBase()
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_base_id');
    }

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'reputations')
            ->withPivot(['valeur', 'rang'])
            ->withTimestamps();
    }

    // Méthodes
    public function estHostileEnvers(Faction $faction): bool
    {
        return in_array($faction->id, $this->hostile_envers ?? []);
    }

    public function estAllieAvec(Faction $faction): bool
    {
        return in_array($faction->id, $this->allie_avec ?? []);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'gouvernement' => 'Gouvernement',
            'corporation' => 'Corporation',
            'pirate' => 'Pirates',
            'guilde' => 'Guilde',
            'militaire' => 'Militaire',
            'religieux' => 'Ordre Religieux',
            'scientifique' => 'Institut Scientifique',
            default => 'Autre',
        };
    }
}
```

---

## Étape 2 : Créer la migration pour le système de réputation

**Commande :** `php artisan make:migration create_reputations_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table de réputation (pivot enrichi)
        Schema::create('reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');
            $table->foreignId('faction_id')->constrained('factions')->onDelete('cascade');

            // Valeur de réputation (-1000 à +1000)
            $table->integer('valeur')->default(0);

            // Rang dérivé de la valeur
            $table->enum('rang', [
                'ennemi',      // -1000 à -500
                'hostile',     // -499 à -100
                'neutre',      // -99 à +99
                'amical',      // +100 à +499
                'allie',       // +500 à +799
                'venere',      // +800 à +1000
            ])->default('neutre');

            // Historique
            $table->json('historique')->nullable(); // [{date, montant, raison}]

            $table->timestamps();

            $table->unique(['personnage_id', 'faction_id']);
            $table->index('rang');
        });

        // Ajouter des colonnes à factions si nécessaires
        if (!Schema::hasColumn('factions', 'hostile_envers')) {
            Schema::table('factions', function (Blueprint $table) {
                $table->json('hostile_envers')->nullable()->after('description');
                $table->json('allie_avec')->nullable()->after('hostile_envers');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reputations');
    }
};
```

---

## Étape 3 : Créer le modèle Reputation

**Fichier :** `app/Models/Reputation.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reputation extends Pivot
{
    protected $table = 'reputations';

    protected $fillable = [
        'personnage_id',
        'faction_id',
        'valeur',
        'rang',
        'historique',
    ];

    protected $casts = [
        'historique' => 'array',
    ];

    public const RANG_ENNEMI = 'ennemi';
    public const RANG_HOSTILE = 'hostile';
    public const RANG_NEUTRE = 'neutre';
    public const RANG_AMICAL = 'amical';
    public const RANG_ALLIE = 'allie';
    public const RANG_VENERE = 'venere';

    public const SEUILS = [
        self::RANG_ENNEMI => -500,
        self::RANG_HOSTILE => -100,
        self::RANG_NEUTRE => 100,
        self::RANG_AMICAL => 500,
        self::RANG_ALLIE => 800,
        self::RANG_VENERE => 1001, // Jamais atteint par valeur seule
    ];

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * Calculer le rang basé sur la valeur
     */
    public static function calculerRang(int $valeur): string
    {
        if ($valeur <= -500) return self::RANG_ENNEMI;
        if ($valeur <= -100) return self::RANG_HOSTILE;
        if ($valeur < 100) return self::RANG_NEUTRE;
        if ($valeur < 500) return self::RANG_AMICAL;
        if ($valeur < 800) return self::RANG_ALLIE;
        return self::RANG_VENERE;
    }

    /**
     * Libellé du rang
     */
    public function getRangLabel(): string
    {
        return match($this->rang) {
            self::RANG_ENNEMI => 'Ennemi juré',
            self::RANG_HOSTILE => 'Hostile',
            self::RANG_NEUTRE => 'Neutre',
            self::RANG_AMICAL => 'Amical',
            self::RANG_ALLIE => 'Allié',
            self::RANG_VENERE => 'Vénéré',
            default => 'Inconnu',
        };
    }

    /**
     * Couleur du rang pour l'affichage
     */
    public function getRangCouleur(): string
    {
        return match($this->rang) {
            self::RANG_ENNEMI => 'red-800',
            self::RANG_HOSTILE => 'red-500',
            self::RANG_NEUTRE => 'gray-500',
            self::RANG_AMICAL => 'blue-500',
            self::RANG_ALLIE => 'green-500',
            self::RANG_VENERE => 'yellow-500',
            default => 'gray-500',
        };
    }

    /**
     * Progression vers le rang suivant (%)
     */
    public function getProgressionVersProchainRang(): int
    {
        $rangs = array_keys(self::SEUILS);
        $indexActuel = array_search($this->rang, $rangs);

        if ($indexActuel === false || $indexActuel >= count($rangs) - 1) {
            return 100; // Déjà au max
        }

        $seuilActuel = $indexActuel > 0 ? self::SEUILS[$rangs[$indexActuel - 1]] : -1000;
        $seuilProchain = self::SEUILS[$rangs[$indexActuel]];
        $plage = $seuilProchain - $seuilActuel;

        if ($plage <= 0) return 100;

        return (int) min(100, max(0, (($this->valeur - $seuilActuel) / $plage) * 100));
    }
}
```

---

## Étape 4 : Créer le service ReputationService

**Fichier :** `app/Services/ReputationService.php`

```php
<?php

namespace App\Services;

use App\Models\Faction;
use App\Models\Personnage;
use App\Models\Reputation;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    /**
     * Obtenir la réputation d'un personnage auprès d'une faction
     */
    public function getReputation(Personnage $personnage, Faction $faction): Reputation
    {
        $reputation = Reputation::where('personnage_id', $personnage->id)
            ->where('faction_id', $faction->id)
            ->first();

        if (!$reputation) {
            // Créer une réputation neutre
            $reputation = Reputation::create([
                'personnage_id' => $personnage->id,
                'faction_id' => $faction->id,
                'valeur' => 0,
                'rang' => Reputation::RANG_NEUTRE,
                'historique' => [],
            ]);
        }

        return $reputation;
    }

    /**
     * Obtenir toutes les réputations d'un personnage
     */
    public function getToutesReputations(Personnage $personnage): array
    {
        $factions = Faction::where('actif', true)->get();
        $reputations = [];

        foreach ($factions as $faction) {
            $rep = $this->getReputation($personnage, $faction);
            $reputations[] = [
                'faction' => $faction,
                'valeur' => $rep->valeur,
                'rang' => $rep->rang,
                'rang_label' => $rep->getRangLabel(),
                'couleur' => $rep->getRangCouleur(),
                'progression' => $rep->getProgressionVersProchainRang(),
            ];
        }

        return $reputations;
    }

    /**
     * Modifier la réputation
     */
    public function modifier(
        Personnage $personnage,
        Faction $faction,
        int $montant,
        string $raison = ''
    ): array {
        return DB::transaction(function () use ($personnage, $faction, $montant, $raison) {
            $reputation = $this->getReputation($personnage, $faction);
            $ancienneValeur = $reputation->valeur;
            $ancienRang = $reputation->rang;

            // Appliquer la modification (borné entre -1000 et +1000)
            $nouvelleValeur = max(-1000, min(1000, $ancienneValeur + $montant));
            $nouveauRang = Reputation::calculerRang($nouvelleValeur);

            // Ajouter à l'historique
            $historique = $reputation->historique ?? [];
            $historique[] = [
                'date' => now()->toISOString(),
                'montant' => $montant,
                'raison' => $raison,
                'valeur_avant' => $ancienneValeur,
                'valeur_apres' => $nouvelleValeur,
            ];

            // Garder seulement les 50 dernières entrées
            $historique = array_slice($historique, -50);

            $reputation->update([
                'valeur' => $nouvelleValeur,
                'rang' => $nouveauRang,
                'historique' => $historique,
            ]);

            // Propagation aux factions liées
            $this->propagerAuxAllies($personnage, $faction, $montant);
            $this->propagerAuxHostiles($personnage, $faction, $montant);

            // Construire le message
            $changementRang = $ancienRang !== $nouveauRang;
            $message = $montant >= 0
                ? "+{$montant} réputation avec {$faction->nom}"
                : "{$montant} réputation avec {$faction->nom}";

            if ($changementRang) {
                $message .= " (Nouveau rang: {$reputation->getRangLabel()})";
            }

            return [
                'success' => true,
                'message' => $message,
                'valeur' => $nouvelleValeur,
                'rang' => $nouveauRang,
                'changement_rang' => $changementRang,
            ];
        });
    }

    /**
     * Propager le changement aux factions alliées (50% du montant)
     */
    private function propagerAuxAllies(Personnage $personnage, Faction $faction, int $montant): void
    {
        if (empty($faction->allie_avec)) return;

        $montantPropage = (int) round($montant * 0.5);
        if ($montantPropage === 0) return;

        foreach ($faction->allie_avec as $allieId) {
            $allie = Faction::find($allieId);
            if ($allie) {
                $rep = $this->getReputation($personnage, $allie);
                $nouvelleValeur = max(-1000, min(1000, $rep->valeur + $montantPropage));
                $rep->update([
                    'valeur' => $nouvelleValeur,
                    'rang' => Reputation::calculerRang($nouvelleValeur),
                ]);
            }
        }
    }

    /**
     * Propager le changement aux factions hostiles (inverse, 25% du montant)
     */
    private function propagerAuxHostiles(Personnage $personnage, Faction $faction, int $montant): void
    {
        if (empty($faction->hostile_envers)) return;

        $montantPropage = (int) round($montant * -0.25);
        if ($montantPropage === 0) return;

        foreach ($faction->hostile_envers as $hostileId) {
            $hostile = Faction::find($hostileId);
            if ($hostile) {
                $rep = $this->getReputation($personnage, $hostile);
                $nouvelleValeur = max(-1000, min(1000, $rep->valeur + $montantPropage));
                $rep->update([
                    'valeur' => $nouvelleValeur,
                    'rang' => Reputation::calculerRang($nouvelleValeur),
                ]);
            }
        }
    }

    /**
     * Vérifier si un personnage a accès à un contenu réservé à un rang
     */
    public function aAcces(Personnage $personnage, Faction $faction, string $rangMinimum): bool
    {
        $reputation = $this->getReputation($personnage, $faction);

        $rangs = [
            Reputation::RANG_ENNEMI => 0,
            Reputation::RANG_HOSTILE => 1,
            Reputation::RANG_NEUTRE => 2,
            Reputation::RANG_AMICAL => 3,
            Reputation::RANG_ALLIE => 4,
            Reputation::RANG_VENERE => 5,
        ];

        $rangActuel = $rangs[$reputation->rang] ?? 2;
        $rangRequis = $rangs[$rangMinimum] ?? 2;

        return $rangActuel >= $rangRequis;
    }

    /**
     * Obtenir les bonus/malus liés au rang
     */
    public function getBonusRang(Reputation $reputation): array
    {
        return match($reputation->rang) {
            Reputation::RANG_ENNEMI => [
                'prix_achat' => 1.5,     // +50% prix
                'prix_vente' => 0.5,     // -50% prix vente
                'acces_missions' => false,
                'acces_marche' => false,
            ],
            Reputation::RANG_HOSTILE => [
                'prix_achat' => 1.25,
                'prix_vente' => 0.75,
                'acces_missions' => false,
                'acces_marche' => true,
            ],
            Reputation::RANG_NEUTRE => [
                'prix_achat' => 1.0,
                'prix_vente' => 1.0,
                'acces_missions' => true,
                'acces_marche' => true,
            ],
            Reputation::RANG_AMICAL => [
                'prix_achat' => 0.95,
                'prix_vente' => 1.05,
                'acces_missions' => true,
                'acces_marche' => true,
            ],
            Reputation::RANG_ALLIE => [
                'prix_achat' => 0.9,
                'prix_vente' => 1.1,
                'acces_missions' => true,
                'acces_marche' => true,
            ],
            Reputation::RANG_VENERE => [
                'prix_achat' => 0.8,
                'prix_vente' => 1.2,
                'acces_missions' => true,
                'acces_marche' => true,
            ],
            default => [
                'prix_achat' => 1.0,
                'prix_vente' => 1.0,
                'acces_missions' => true,
                'acces_marche' => true,
            ],
        };
    }
}
```

---

## Étape 5 : Créer le contrôleur Admin pour les Factions

**Fichier :** `app/Http/Controllers/Admin/AdminFactionController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\SystemeStellaire;
use Illuminate\Http\Request;

class AdminFactionController extends Controller
{
    public function index()
    {
        $factions = Faction::withCount(['stations', 'missions'])
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.factions.index', compact('factions'));
    }

    public function create()
    {
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $factions = Faction::orderBy('nom')->get(); // Pour relations hostiles/alliées
        $types = ['gouvernement', 'corporation', 'pirate', 'guilde', 'militaire', 'religieux', 'scientifique'];

        return view('admin.factions.create', compact('systemes', 'factions', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:factions',
            'code' => 'required|string|max:10|unique:factions',
            'description' => 'nullable|string',
            'type' => 'required|in:gouvernement,corporation,pirate,guilde,militaire,religieux,scientifique',
            'couleur' => 'nullable|string|max:7',
            'systeme_base_id' => 'nullable|exists:systemes_stellaires,id',
            'hostile_envers' => 'nullable|array',
            'allie_avec' => 'nullable|array',
            'actif' => 'boolean',
        ]);

        $validated['actif'] = $request->has('actif');

        $faction = Faction::create($validated);

        return redirect()->route('admin.factions.index')
            ->with('success', "Faction '{$faction->nom}' créée.");
    }

    public function edit(Faction $faction)
    {
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $factions = Faction::where('id', '!=', $faction->id)->orderBy('nom')->get();
        $types = ['gouvernement', 'corporation', 'pirate', 'guilde', 'militaire', 'religieux', 'scientifique'];

        return view('admin.factions.edit', compact('faction', 'systemes', 'factions', 'types'));
    }

    public function update(Request $request, Faction $faction)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:factions,nom,' . $faction->id,
            'code' => 'required|string|max:10|unique:factions,code,' . $faction->id,
            'description' => 'nullable|string',
            'type' => 'required|in:gouvernement,corporation,pirate,guilde,militaire,religieux,scientifique',
            'couleur' => 'nullable|string|max:7',
            'systeme_base_id' => 'nullable|exists:systemes_stellaires,id',
            'hostile_envers' => 'nullable|array',
            'allie_avec' => 'nullable|array',
            'actif' => 'boolean',
        ]);

        $validated['actif'] = $request->has('actif');

        $faction->update($validated);

        return redirect()->route('admin.factions.index')
            ->with('success', "Faction '{$faction->nom}' mise à jour.");
    }

    public function destroy(Faction $faction)
    {
        if ($faction->stations()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une faction avec des stations.');
        }

        $nom = $faction->nom;
        $faction->delete();

        return redirect()->route('admin.factions.index')
            ->with('success', "Faction '{$nom}' supprimée.");
    }

    /**
     * Voir les détails d'une faction et ses réputations
     */
    public function show(Faction $faction)
    {
        $faction->load(['stations', 'missions', 'systemeBase']);

        // Top 10 personnages avec meilleure réputation
        $topReputations = $faction->personnages()
            ->orderByPivot('valeur', 'desc')
            ->limit(10)
            ->get();

        return view('admin.factions.show', compact('faction', 'topReputations'));
    }
}
```

---

## Étape 6 : Ajouter les routes

**Fichier :** `routes/web.php`

```php
use App\Http\Controllers\Admin\AdminFactionController;

// Dans le groupe admin
Route::resource('factions', AdminFactionController::class);
```

---

## Étape 7 : Créer les vues

### Vue index : `resources/views/admin/factions/index.blade.php`

```blade
@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion des Factions</h1>
        <a href="{{ route('admin.factions.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            + Nouvelle Faction
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Stations</th>
                    <th class="px-4 py-3 text-left">Missions</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factions as $faction)
                <tr class="border-b border-gray-700">
                    <td class="px-4 py-3 flex items-center gap-2">
                        @if($faction->couleur)
                        <span class="w-4 h-4 rounded" style="background-color: {{ $faction->couleur }}"></span>
                        @endif
                        {{ $faction->nom }}
                    </td>
                    <td class="px-4 py-3 font-mono">{{ $faction->code }}</td>
                    <td class="px-4 py-3">{{ $faction->getTypeLabel() }}</td>
                    <td class="px-4 py-3">{{ $faction->stations_count }}</td>
                    <td class="px-4 py-3">{{ $faction->missions_count }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs {{ $faction->actif ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ $faction->actif ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('admin.factions.show', $faction) }}" class="text-green-400 hover:underline">Détails</a>
                        <a href="{{ route('admin.factions.edit', $faction) }}" class="text-blue-400 hover:underline">Modifier</a>
                        <form action="{{ route('admin.factions.destroy', $faction) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:underline" onclick="return confirm('Supprimer ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        Aucune faction
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $factions->links() }}
</div>
@endsection
```

---

## Étape 8 : Ajouter relation dans Personnage

**Fichier :** `app/Models/Personnage.php`

```php
use App\Models\Reputation;

public function factions(): BelongsToMany
{
    return $this->belongsToMany(Faction::class, 'reputations')
        ->using(Reputation::class)
        ->withPivot(['valeur', 'rang', 'historique'])
        ->withTimestamps();
}

public function getReputationAvec(Faction $faction): ?Reputation
{
    return Reputation::where('personnage_id', $this->id)
        ->where('faction_id', $faction->id)
        ->first();
}
```

---

## Vérification finale

```bash
# Migrations
php artisan migrate

# Syntaxe
php -l app/Models/Faction.php
php -l app/Models/Reputation.php
php -l app/Services/ReputationService.php
php -l app/Http/Controllers/Admin/AdminFactionController.php

# Routes
php artisan route:list --path=admin/factions
```

---

## Critères de succès

- [ ] Table `reputations` créée
- [ ] Modèle Faction complet avec relations
- [ ] Modèle Reputation fonctionnel
- [ ] Service ReputationService avec propagation
- [ ] CRUD admin factions fonctionnel

---

*Plan créé le 3 janvier 2026 pour Claude Code Sonnet*
