# Plan Sonnet #02 : Système de Missions

**Objectif :** Créer un système complet de missions avec admin et gameplay

---

## Vue d'ensemble

Le système de missions permet aux joueurs d'accepter des missions (transport, exploration, combat) et de les compléter pour obtenir des récompenses.

---

## Étape 1 : Créer la migration

**Fichier :** `php artisan make:migration create_missions_system_tables`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des missions (templates)
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->enum('type', ['transport', 'exploration', 'combat', 'mining', 'livraison', 'escorte']);
            $table->enum('difficulte', ['facile', 'moyen', 'difficile', 'extreme'])->default('moyen');

            // Origine et destination
            $table->foreignId('station_origine_id')->nullable()->constrained('stations');
            $table->foreignId('station_destination_id')->nullable()->constrained('stations');
            $table->foreignId('systeme_cible_id')->nullable()->constrained('systemes_stellaires');
            $table->foreignId('planete_cible_id')->nullable()->constrained('planetes');

            // Faction associée
            $table->foreignId('faction_id')->nullable()->constrained('factions');

            // Récompenses
            $table->integer('recompense_credits')->default(0);
            $table->integer('recompense_reputation')->default(0);
            $table->json('recompense_objets')->nullable(); // [{"objet_id": 1, "quantite": 5}]

            // Conditions
            $table->integer('niveau_minimum')->default(1);
            $table->integer('reputation_minimum')->default(0);
            $table->integer('duree_limite')->nullable(); // en minutes

            // Objectifs
            $table->json('objectifs')->nullable(); // [{"type": "livrer", "ressource_id": 1, "quantite": 100}]

            // État
            $table->boolean('actif')->default(true);
            $table->boolean('repetable')->default(false);
            $table->integer('cooldown_minutes')->nullable(); // temps avant de pouvoir refaire

            $table->timestamps();

            $table->index('type');
            $table->index('actif');
            $table->index('faction_id');
        });

        // Table des missions acceptées par les joueurs
        Schema::create('missions_personnages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');

            $table->enum('statut', ['en_cours', 'completee', 'echouee', 'abandonnee'])->default('en_cours');
            $table->json('progression')->nullable(); // {"objectif_1": 50, "objectif_2": 100}

            $table->timestamp('acceptee_le');
            $table->timestamp('limite_le')->nullable();
            $table->timestamp('terminee_le')->nullable();

            $table->timestamps();

            $table->index(['personnage_id', 'statut']);
            $table->index(['mission_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions_personnages');
        Schema::dropIfExists('missions');
    }
};
```

---

## Étape 2 : Créer le modèle Mission

**Fichier :** `app/Models/Mission.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'type',
        'difficulte',
        'station_origine_id',
        'station_destination_id',
        'systeme_cible_id',
        'planete_cible_id',
        'faction_id',
        'recompense_credits',
        'recompense_reputation',
        'recompense_objets',
        'niveau_minimum',
        'reputation_minimum',
        'duree_limite',
        'objectifs',
        'actif',
        'repetable',
        'cooldown_minutes',
    ];

    protected $casts = [
        'recompense_objets' => 'array',
        'objectifs' => 'array',
        'actif' => 'boolean',
        'repetable' => 'boolean',
    ];

    // Relations
    public function stationOrigine(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_origine_id');
    }

    public function stationDestination(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_destination_id');
    }

    public function systemeCible(): BelongsTo
    {
        return $this->belongsTo(SystemeStellaire::class, 'systeme_cible_id');
    }

    public function planeteCible(): BelongsTo
    {
        return $this->belongsTo(Planete::class, 'planete_cible_id');
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'missions_personnages')
            ->withPivot(['statut', 'progression', 'acceptee_le', 'limite_le', 'terminee_le'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    public function scopeDeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDisponiblesPour($query, Personnage $personnage)
    {
        return $query->where('actif', true)
            ->where('niveau_minimum', '<=', $personnage->niveau ?? 1);
    }

    // Méthodes
    public function getDifficulteLabel(): string
    {
        return match($this->difficulte) {
            'facile' => 'Facile',
            'moyen' => 'Moyen',
            'difficile' => 'Difficile',
            'extreme' => 'Extrême',
            default => 'Inconnu',
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'transport' => 'Transport',
            'exploration' => 'Exploration',
            'combat' => 'Combat',
            'mining' => 'Minage',
            'livraison' => 'Livraison',
            'escorte' => 'Escorte',
            default => 'Autre',
        };
    }
}
```

---

## Étape 3 : Créer le modèle MissionPersonnage (pivot enrichi)

**Fichier :** `app/Models/MissionPersonnage.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionPersonnage extends Pivot
{
    protected $table = 'missions_personnages';

    protected $fillable = [
        'mission_id',
        'personnage_id',
        'statut',
        'progression',
        'acceptee_le',
        'limite_le',
        'terminee_le',
    ];

    protected $casts = [
        'progression' => 'array',
        'acceptee_le' => 'datetime',
        'limite_le' => 'datetime',
        'terminee_le' => 'datetime',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function estEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    public function estExpiree(): bool
    {
        return $this->limite_le && now()->isAfter($this->limite_le);
    }

    public function getProgressionPourcent(): int
    {
        if (empty($this->progression)) {
            return 0;
        }

        $total = count($this->progression);
        $completed = collect($this->progression)->filter(fn($v) => $v >= 100)->count();

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }
}
```

---

## Étape 4 : Créer le service MissionService

**Fichier :** `app/Services/MissionService.php`

```php
<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\Personnage;
use App\Models\MissionPersonnage;
use Illuminate\Support\Facades\DB;

class MissionService
{
    /**
     * Obtenir les missions disponibles pour un personnage
     */
    public function getMissionsDisponibles(Personnage $personnage, ?string $type = null): array
    {
        $query = Mission::actives()
            ->disponiblesPour($personnage)
            ->with(['stationOrigine', 'faction']);

        if ($type) {
            $query->deType($type);
        }

        // Exclure les missions déjà en cours
        $missionsEnCours = $personnage->missions()
            ->wherePivot('statut', 'en_cours')
            ->pluck('missions.id');

        $query->whereNotIn('id', $missionsEnCours);

        return $query->get()->toArray();
    }

    /**
     * Accepter une mission
     */
    public function accepter(Personnage $personnage, Mission $mission): array
    {
        // Vérifications
        if (!$mission->actif) {
            return ['success' => false, 'message' => 'Cette mission n\'est plus disponible.'];
        }

        if ($mission->niveau_minimum > ($personnage->niveau ?? 1)) {
            return ['success' => false, 'message' => 'Niveau insuffisant pour cette mission.'];
        }

        // Vérifier si déjà acceptée
        $existante = $personnage->missions()
            ->wherePivot('mission_id', $mission->id)
            ->wherePivot('statut', 'en_cours')
            ->first();

        if ($existante) {
            return ['success' => false, 'message' => 'Mission déjà acceptée.'];
        }

        // Limite de missions simultanées (5 max)
        $missionsEnCours = $personnage->missions()
            ->wherePivot('statut', 'en_cours')
            ->count();

        if ($missionsEnCours >= 5) {
            return ['success' => false, 'message' => 'Vous avez déjà 5 missions en cours.'];
        }

        // Initialiser la progression
        $progression = [];
        if ($mission->objectifs) {
            foreach ($mission->objectifs as $index => $objectif) {
                $progression["objectif_{$index}"] = 0;
            }
        }

        // Calculer la limite
        $limiteAt = $mission->duree_limite
            ? now()->addMinutes($mission->duree_limite)
            : null;

        // Créer l'association
        $personnage->missions()->attach($mission->id, [
            'statut' => 'en_cours',
            'progression' => json_encode($progression),
            'acceptee_le' => now(),
            'limite_le' => $limiteAt,
        ]);

        return [
            'success' => true,
            'message' => "Mission '{$mission->titre}' acceptée !",
        ];
    }

    /**
     * Abandonner une mission
     */
    public function abandonner(Personnage $personnage, Mission $mission): array
    {
        $missionPersonnage = MissionPersonnage::where('mission_id', $mission->id)
            ->where('personnage_id', $personnage->id)
            ->where('statut', 'en_cours')
            ->first();

        if (!$missionPersonnage) {
            return ['success' => false, 'message' => 'Mission non trouvée.'];
        }

        $missionPersonnage->update([
            'statut' => 'abandonnee',
            'terminee_le' => now(),
        ]);

        // Pénalité de réputation si faction associée
        if ($mission->faction_id) {
            $this->modifierReputation($personnage, $mission->faction_id, -5);
        }

        return [
            'success' => true,
            'message' => 'Mission abandonnée.',
        ];
    }

    /**
     * Mettre à jour la progression d'une mission
     */
    public function mettreAJourProgression(
        Personnage $personnage,
        Mission $mission,
        string $objectifKey,
        int $valeur
    ): array {
        $missionPersonnage = MissionPersonnage::where('mission_id', $mission->id)
            ->where('personnage_id', $personnage->id)
            ->where('statut', 'en_cours')
            ->first();

        if (!$missionPersonnage) {
            return ['success' => false, 'message' => 'Mission non trouvée.'];
        }

        // Vérifier expiration
        if ($missionPersonnage->estExpiree()) {
            $missionPersonnage->update([
                'statut' => 'echouee',
                'terminee_le' => now(),
            ]);
            return ['success' => false, 'message' => 'Mission expirée.'];
        }

        $progression = $missionPersonnage->progression ?? [];
        $progression[$objectifKey] = min(100, $valeur);
        $missionPersonnage->update(['progression' => $progression]);

        // Vérifier si tous les objectifs sont complétés
        $tousCompletes = collect($progression)->every(fn($v) => $v >= 100);

        if ($tousCompletes) {
            return $this->completer($personnage, $mission);
        }

        return [
            'success' => true,
            'message' => 'Progression mise à jour.',
            'progression' => $missionPersonnage->getProgressionPourcent(),
        ];
    }

    /**
     * Compléter une mission
     */
    public function completer(Personnage $personnage, Mission $mission): array
    {
        $missionPersonnage = MissionPersonnage::where('mission_id', $mission->id)
            ->where('personnage_id', $personnage->id)
            ->where('statut', 'en_cours')
            ->first();

        if (!$missionPersonnage) {
            return ['success' => false, 'message' => 'Mission non trouvée.'];
        }

        return DB::transaction(function () use ($personnage, $mission, $missionPersonnage) {
            // Marquer comme complétée
            $missionPersonnage->update([
                'statut' => 'completee',
                'terminee_le' => now(),
            ]);

            // Donner les récompenses
            $personnage->credits += $mission->recompense_credits;
            $personnage->save();

            // Réputation
            if ($mission->faction_id && $mission->recompense_reputation > 0) {
                $this->modifierReputation(
                    $personnage,
                    $mission->faction_id,
                    $mission->recompense_reputation
                );
            }

            // Objets (à implémenter avec le système d'inventaire)
            // if ($mission->recompense_objets) { ... }

            return [
                'success' => true,
                'message' => "Mission '{$mission->titre}' complétée ! +" . number_format($mission->recompense_credits) . " crédits",
                'recompenses' => [
                    'credits' => $mission->recompense_credits,
                    'reputation' => $mission->recompense_reputation,
                ],
            ];
        });
    }

    /**
     * Modifier la réputation auprès d'une faction
     */
    private function modifierReputation(Personnage $personnage, int $factionId, int $montant): void
    {
        // À implémenter avec le système de réputation (P2-3)
        // Pour l'instant, on log juste
        \Log::info("Réputation: Personnage {$personnage->id} +{$montant} avec faction {$factionId}");
    }

    /**
     * Obtenir les missions en cours d'un personnage
     */
    public function getMissionsEnCours(Personnage $personnage): array
    {
        return $personnage->missions()
            ->wherePivot('statut', 'en_cours')
            ->with(['stationOrigine', 'stationDestination', 'faction'])
            ->get()
            ->map(function ($mission) {
                return [
                    'id' => $mission->id,
                    'titre' => $mission->titre,
                    'type' => $mission->getTypeLabel(),
                    'difficulte' => $mission->getDifficulteLabel(),
                    'statut' => $mission->pivot->statut,
                    'progression' => $mission->pivot->getProgressionPourcent(),
                    'limite' => $mission->pivot->limite_le?->diffForHumans(),
                    'expiree' => $mission->pivot->estExpiree(),
                ];
            })
            ->toArray();
    }
}
```

---

## Étape 5 : Créer le contrôleur Admin

**Fichier :** `app/Http/Controllers/Admin/AdminMissionController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Station;
use App\Models\SystemeStellaire;
use App\Models\Planete;
use App\Models\Faction;
use Illuminate\Http\Request;

class AdminMissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Mission::with(['stationOrigine', 'faction']);

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('actif')) {
            $query->where('actif', $request->actif === '1');
        }

        $missions = $query->orderBy('created_at', 'desc')->paginate(20);
        $types = ['transport', 'exploration', 'combat', 'mining', 'livraison', 'escorte'];

        return view('admin.missions.index', compact('missions', 'types'));
    }

    public function create()
    {
        $stations = Station::orderBy('nom')->get();
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $factions = Faction::orderBy('nom')->get();
        $types = ['transport', 'exploration', 'combat', 'mining', 'livraison', 'escorte'];
        $difficultes = ['facile', 'moyen', 'difficile', 'extreme'];

        return view('admin.missions.create', compact(
            'stations', 'systemes', 'factions', 'types', 'difficultes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:transport,exploration,combat,mining,livraison,escorte',
            'difficulte' => 'required|in:facile,moyen,difficile,extreme',
            'station_origine_id' => 'nullable|exists:stations,id',
            'station_destination_id' => 'nullable|exists:stations,id',
            'systeme_cible_id' => 'nullable|exists:systemes_stellaires,id',
            'faction_id' => 'nullable|exists:factions,id',
            'recompense_credits' => 'required|integer|min:0',
            'recompense_reputation' => 'integer|min:0',
            'niveau_minimum' => 'integer|min:1|max:100',
            'duree_limite' => 'nullable|integer|min:1',
            'actif' => 'boolean',
            'repetable' => 'boolean',
        ]);

        $validated['actif'] = $request->has('actif');
        $validated['repetable'] = $request->has('repetable');

        $mission = Mission::create($validated);

        return redirect()->route('admin.missions.index')
            ->with('success', "Mission '{$mission->titre}' créée.");
    }

    public function edit(Mission $mission)
    {
        $stations = Station::orderBy('nom')->get();
        $systemes = SystemeStellaire::orderBy('nom')->get();
        $factions = Faction::orderBy('nom')->get();
        $types = ['transport', 'exploration', 'combat', 'mining', 'livraison', 'escorte'];
        $difficultes = ['facile', 'moyen', 'difficile', 'extreme'];

        return view('admin.missions.edit', compact(
            'mission', 'stations', 'systemes', 'factions', 'types', 'difficultes'
        ));
    }

    public function update(Request $request, Mission $mission)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:transport,exploration,combat,mining,livraison,escorte',
            'difficulte' => 'required|in:facile,moyen,difficile,extreme',
            'station_origine_id' => 'nullable|exists:stations,id',
            'station_destination_id' => 'nullable|exists:stations,id',
            'systeme_cible_id' => 'nullable|exists:systemes_stellaires,id',
            'faction_id' => 'nullable|exists:factions,id',
            'recompense_credits' => 'required|integer|min:0',
            'recompense_reputation' => 'integer|min:0',
            'niveau_minimum' => 'integer|min:1|max:100',
            'duree_limite' => 'nullable|integer|min:1',
            'actif' => 'boolean',
            'repetable' => 'boolean',
        ]);

        $validated['actif'] = $request->has('actif');
        $validated['repetable'] = $request->has('repetable');

        $mission->update($validated);

        return redirect()->route('admin.missions.index')
            ->with('success', "Mission '{$mission->titre}' mise à jour.");
    }

    public function destroy(Mission $mission)
    {
        $titre = $mission->titre;
        $mission->delete();

        return redirect()->route('admin.missions.index')
            ->with('success', "Mission '{$titre}' supprimée.");
    }

    /**
     * Activer/Désactiver une mission
     */
    public function toggle(Mission $mission)
    {
        $mission->update(['actif' => !$mission->actif]);

        $statut = $mission->actif ? 'activée' : 'désactivée';
        return back()->with('success', "Mission {$statut}.");
    }
}
```

---

## Étape 6 : Ajouter les routes

**Fichier :** `routes/web.php` (dans le groupe admin)

```php
use App\Http\Controllers\Admin\AdminMissionController;

// Dans le groupe admin
Route::resource('missions', AdminMissionController::class);
Route::post('/missions/{mission}/toggle', [AdminMissionController::class, 'toggle'])->name('missions.toggle');
```

---

## Étape 7 : Créer les vues Admin

### Vue index : `resources/views/admin/missions/index.blade.php`

```blade
@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion des Missions</h1>
        <a href="{{ route('admin.missions.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            + Nouvelle Mission
        </a>
    </div>

    <!-- Filtres -->
    <form method="GET" class="mb-6 flex gap-4">
        <select name="type" class="bg-gray-700 rounded px-3 py-2">
            <option value="">Tous les types</option>
            @foreach($types as $type)
            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                {{ ucfirst($type) }}
            </option>
            @endforeach
        </select>
        <select name="actif" class="bg-gray-700 rounded px-3 py-2">
            <option value="">Tous les statuts</option>
            <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actives</option>
            <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactives</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Filtrer</button>
    </form>

    <div class="bg-gray-800 rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Titre</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Difficulté</th>
                    <th class="px-4 py-3 text-left">Récompense</th>
                    <th class="px-4 py-3 text-left">Faction</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($missions as $mission)
                <tr class="border-b border-gray-700 hover:bg-gray-750">
                    <td class="px-4 py-3">{{ $mission->titre }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs bg-blue-600">
                            {{ $mission->getTypeLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $mission->getDifficulteLabel() }}</td>
                    <td class="px-4 py-3">{{ number_format($mission->recompense_credits) }} Cr</td>
                    <td class="px-4 py-3">{{ $mission->faction->nom ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs {{ $mission->actif ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ $mission->actif ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('admin.missions.edit', $mission) }}" class="text-blue-400 hover:underline">Modifier</a>
                        <form action="{{ route('admin.missions.toggle', $mission) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-400 hover:underline">
                                {{ $mission->actif ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.missions.destroy', $mission) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:underline" onclick="return confirm('Supprimer cette mission ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        Aucune mission trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $missions->links() }}
    </div>
</div>
@endsection
```

### Vue create : `resources/views/admin/missions/create.blade.php`

```blade
@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <h1 class="text-2xl font-bold mb-6">Nouvelle Mission</h1>

    <form action="{{ route('admin.missions.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-2 gap-6">
            <!-- Titre -->
            <div class="col-span-2">
                <label class="block mb-2">Titre *</label>
                <input type="text" name="titre" value="{{ old('titre') }}" required
                    class="w-full bg-gray-700 rounded px-4 py-2 @error('titre') border-red-500 @enderror">
                @error('titre')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Description -->
            <div class="col-span-2">
                <label class="block mb-2">Description *</label>
                <textarea name="description" rows="4" required
                    class="w-full bg-gray-700 rounded px-4 py-2">{{ old('description') }}</textarea>
            </div>

            <!-- Type -->
            <div>
                <label class="block mb-2">Type *</label>
                <select name="type" required class="w-full bg-gray-700 rounded px-4 py-2">
                    @foreach($types as $type)
                    <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Difficulté -->
            <div>
                <label class="block mb-2">Difficulté *</label>
                <select name="difficulte" required class="w-full bg-gray-700 rounded px-4 py-2">
                    @foreach($difficultes as $diff)
                    <option value="{{ $diff }}" {{ old('difficulte', 'moyen') == $diff ? 'selected' : '' }}>
                        {{ ucfirst($diff) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Station Origine -->
            <div>
                <label class="block mb-2">Station d'origine</label>
                <select name="station_origine_id" class="w-full bg-gray-700 rounded px-4 py-2">
                    <option value="">-- Aucune --</option>
                    @foreach($stations as $station)
                    <option value="{{ $station->id }}">{{ $station->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Station Destination -->
            <div>
                <label class="block mb-2">Station de destination</label>
                <select name="station_destination_id" class="w-full bg-gray-700 rounded px-4 py-2">
                    <option value="">-- Aucune --</option>
                    @foreach($stations as $station)
                    <option value="{{ $station->id }}">{{ $station->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Faction -->
            <div>
                <label class="block mb-2">Faction</label>
                <select name="faction_id" class="w-full bg-gray-700 rounded px-4 py-2">
                    <option value="">-- Aucune --</option>
                    @foreach($factions as $faction)
                    <option value="{{ $faction->id }}">{{ $faction->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Niveau minimum -->
            <div>
                <label class="block mb-2">Niveau minimum</label>
                <input type="number" name="niveau_minimum" value="{{ old('niveau_minimum', 1) }}" min="1" max="100"
                    class="w-full bg-gray-700 rounded px-4 py-2">
            </div>

            <!-- Récompenses -->
            <div>
                <label class="block mb-2">Récompense (crédits) *</label>
                <input type="number" name="recompense_credits" value="{{ old('recompense_credits', 1000) }}" min="0" required
                    class="w-full bg-gray-700 rounded px-4 py-2">
            </div>

            <div>
                <label class="block mb-2">Récompense (réputation)</label>
                <input type="number" name="recompense_reputation" value="{{ old('recompense_reputation', 10) }}" min="0"
                    class="w-full bg-gray-700 rounded px-4 py-2">
            </div>

            <!-- Durée limite -->
            <div>
                <label class="block mb-2">Durée limite (minutes)</label>
                <input type="number" name="duree_limite" value="{{ old('duree_limite') }}" min="1"
                    class="w-full bg-gray-700 rounded px-4 py-2" placeholder="Laisser vide = pas de limite">
            </div>

            <!-- Options -->
            <div class="col-span-2 flex gap-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="actif" value="1" {{ old('actif', true) ? 'checked' : '' }}
                        class="rounded bg-gray-700">
                    <span>Mission active</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="repetable" value="1" {{ old('repetable') ? 'checked' : '' }}
                        class="rounded bg-gray-700">
                    <span>Mission répétable</span>
                </label>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded">
                Créer la mission
            </button>
            <a href="{{ route('admin.missions.index') }}" class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
```

### Vue edit : `resources/views/admin/missions/edit.blade.php`

(Similaire à create, avec les valeurs pré-remplies et `@method('PUT')`)

---

## Étape 8 : Ajouter la relation dans Personnage

**Fichier :** `app/Models/Personnage.php`

Ajouter cette méthode :

```php
use App\Models\MissionPersonnage;

public function missions(): BelongsToMany
{
    return $this->belongsToMany(Mission::class, 'missions_personnages')
        ->using(MissionPersonnage::class)
        ->withPivot(['statut', 'progression', 'acceptee_le', 'limite_le', 'terminee_le'])
        ->withTimestamps();
}

public function missionsEnCours()
{
    return $this->missions()->wherePivot('statut', 'en_cours');
}
```

---

## Vérification finale

```bash
# Créer la migration
php artisan make:migration create_missions_system_tables

# Exécuter
php artisan migrate

# Vérifier syntaxe
php -l app/Models/Mission.php
php -l app/Models/MissionPersonnage.php
php -l app/Services/MissionService.php
php -l app/Http/Controllers/Admin/AdminMissionController.php

# Vérifier routes
php artisan route:list --path=admin/missions
```

---

## Critères de succès

- [ ] Tables `missions` et `missions_personnages` créées
- [ ] Modèles créés et fonctionnels
- [ ] Service MissionService créé
- [ ] CRUD admin fonctionnel
- [ ] Vues affichées sans erreur

---

*Plan créé le 3 janvier 2026 pour Claude Code Sonnet*
