# 🛠️ ASTUCES DE DÉVELOPPEMENT - Éviter les Erreurs Courantes

**Document pour maintenir la qualité du code et éviter les pièges de Laravel/Eloquent**

---

## 🚨 Problème #1 : Attributs Temporaires sur Modèles Eloquent

### ❌ MAUVAISE APPROCHE
```php
$planete = Planete::find(1);

// Ajouter des attributs temporaires directement au modèle
$planete->icone = '🌍';
$planete->distance = 90.5;
$planete->type_poi = 'planete';

// DANGER : Si on appelle une méthode qui fait save()/update()
$planete->getDonneesOrbitales($personnage); // Appelle recalculerPositionCache() qui fait update()

// ❌ ERREUR : Laravel va essayer de sauvegarder icone, distance, type_poi
// SQLSTATE[42S22]: Column not found: 1054 Unknown column 'icone' in 'field list'
```

### ✅ BONNE APPROCHE
```php
$planete = Planete::find(1);

// Créer un objet séparé pour les données d'affichage
$poi = (object)[
    'id' => $planete->id,
    'nom' => $planete->nom,
    'icone' => '🌍',
    'distance' => 90.5,
    'type_poi' => 'planete',
    'donneesOrbitales' => $planete->getDonneesOrbitales($personnage),
];

// ✅ Le modèle n'est pas pollué, pas de risque de sauvegarde accidentelle
```

### 📋 Règles à Suivre
1. **Ne jamais assigner d'attributs temporaires à un modèle Eloquent** si on va appeler des méthodes qui font `save()` ou `update()` après
2. **Utiliser des objets DTO (Data Transfer Objects)** : `(object)[...]` ou des classes dédiées
3. **Ou cloner le modèle** : `$temp = clone $planete;` puis assigner les attributs au clone
4. **Ou utiliser `$guarded`** dans le modèle pour bloquer ces attributs (moins flexible)

---

## 🚨 Problème #2 : Relations Inexistantes

### ❌ MAUVAISE APPROCHE
```php
// Dans le contrôleur
$planetes = $systeme->planetes()
    ->where('poi_connu', true)
    ->orWhereHas('decouvertes', function($q) use ($personnage) {
        $q->where('personnage_id', $personnage->id);
    })
    ->get();

// ❌ ERREUR si la relation 'decouvertes' n'existe pas dans la table
// SQLSTATE[42S22]: Column not found: 1054 Unknown column 'decouvertes.planete_id'
```

### ✅ BONNE APPROCHE
```php
// 1. Vérifier la structure de la base de données AVANT d'écrire le code
// php artisan tinker
// DB::select('DESCRIBE decouvertes');

// 2. Si la colonne n'existe pas, ne pas utiliser la relation
$planetes = $systeme->planetes()
    ->where('poi_connu', true)
    ->get();

// 3. Commenter la relation dans le modèle si elle n'est pas implémentée
// Dans Planete.php :
// TODO: Système de découverte de planètes à implémenter
// public function decouvertes() { ... }
```

### 📋 Règles à Suivre
1. **Toujours vérifier la structure de la table** avant d'utiliser une relation
2. **Commenter les relations non implémentées** avec un TODO explicite
3. **Créer les migrations** si la relation est nécessaire
4. **Documenter** ce qui existe vs ce qui est prévu

---

## 🚨 Problème #3 : Propriétés Null Non Gérées

### ❌ MAUVAISE APPROCHE
```php
protected function getPoISecteur($vaisseau): array
{
    $personnage = $vaisseau->personnage; // Peut être null si relation non chargée

    // ❌ ERREUR : Attempt to read property "id" on null
    $query->where('personnage_id', $personnage->id);
}
```

### ✅ BONNE APPROCHE
```php
// Passer le personnage en paramètre depuis une méthode où il est déjà chargé
protected function getPoISecteur($vaisseau, $personnage): array
{
    // ✅ Le personnage est garanti non-null
    $query->where('personnage_id', $personnage->id);
}

// Ou vérifier explicitement
protected function getPoISecteur($vaisseau): array
{
    $personnage = $vaisseau->personnage;

    if (!$personnage) {
        throw new \Exception('Personnage non trouvé');
    }

    // ...
}
```

### 📋 Règles à Suivre
1. **Passer les objets requis en paramètres** plutôt que les récupérer via relations
2. **Vérifier les null explicitement** si on doit utiliser une relation
3. **Eager load les relations** si nécessaire : `$vaisseau->load('personnage')`
4. **Typer les paramètres** : `Personnage $personnage` pour forcer non-null

---

## 🛡️ Bonnes Pratiques Générales

### 1. Tests Systématiques

Après chaque modification importante :
```bash
# Vérifier la syntaxe PHP
php -l app/Http/Controllers/MonController.php

# Tester dans tinker
php artisan tinker
>>> $personnage = App\Models\Personnage::first();
>>> // Reproduire le code problématique
```

### 2. Séparation des Responsabilités

```php
// ❌ Mauvais : Logique mixte
public function index() {
    $planetes = Planete::all();
    foreach ($planetes as $p) {
        $p->icone = '🌍';
        $p->distance = 90;
    }
    return view('...', compact('planetes'));
}

// ✅ Bon : Séparation claire
public function index() {
    $planetes = Planete::all();
    $poisForDisplay = $this->preparePoisForDisplay($planetes);
    return view('...', compact('poisForDisplay'));
}

private function preparePoisForDisplay($planetes) {
    return $planetes->map(fn($p) => (object)[
        'id' => $p->id,
        'nom' => $p->nom,
        'icone' => $this->getIconePlanete($p->type),
        'distance' => $this->calculerDistance($p),
    ]);
}
```

### 3. Documentation Inline

```php
// ✅ Bon : Documenter les décisions et les limites
/**
 * Récupérer les POI du secteur actuel (PLANÈTES + STATIONS)
 *
 * IMPORTANT: Les objets retournés sont des stdClass, pas des modèles Eloquent,
 * pour éviter de polluer les modèles avec des attributs temporaires.
 *
 * TODO: Implémenter système de découverte de planètes individuelles
 *
 * @param Vaisseau $vaisseau
 * @param Personnage $personnage
 * @return array<object> Liste d'objets POI avec propriétés : id, nom, icone, distance, etc.
 */
protected function getPoISecteur($vaisseau, $personnage): array
```

### 4. Utiliser des Value Objects ou DTO

Pour des projets plus complexes :
```php
// Créer une classe dédiée
class PlanetePOI {
    public function __construct(
        public int $id,
        public string $nom,
        public string $icone,
        public float $distance,
        public string $type_poi,
        public array $donneesOrbitales,
    ) {}

    public static function fromPlanete(Planete $planete, Vaisseau $vaisseau, Personnage $personnage): self
    {
        return new self(
            id: $planete->id,
            nom: $planete->nom,
            icone: self::getIcone($planete->type),
            distance: $planete->getDistanceDepuisVaisseau($vaisseau),
            type_poi: 'planete',
            donneesOrbitales: $planete->getDonneesOrbitales($personnage),
        );
    }
}
```

---

## 🔍 Checklist Avant de Coder une Relation

- [ ] La colonne existe-t-elle dans la base ? (`DESCRIBE table_name`)
- [ ] La relation est-elle définie dans le modèle ?
- [ ] La relation est-elle chargée (eager loading) si nécessaire ?
- [ ] Les clés étrangères correspondent-elles (nom de colonne) ?
- [ ] Y a-t-il des cas où la relation peut être null ?

---

## 🔍 Checklist Avant de Modifier un Modèle

- [ ] Vais-je appeler `save()` ou `update()` après ?
- [ ] Si oui, est-ce que j'ai ajouté des attributs temporaires ?
- [ ] Si oui, les ai-je isolés dans un objet séparé ?
- [ ] Les nouveaux attributs sont-ils dans `$fillable` ou `$guarded` ?

---

## 🚀 Outils pour Débugger

### 1. Voir les Requêtes SQL
```php
// Avant la requête problématique
\DB::enableQueryLog();

// Code qui fait la requête
$planetes = $systeme->planetes()->where(...)->get();

// Voir les requêtes
dd(\DB::getQueryLog());
```

### 2. Voir les Attributs d'un Modèle
```php
$planete = Planete::find(1);
dd($planete->getAttributes()); // Seulement les colonnes DB
dd($planete->toArray()); // Inclut les accesseurs
```

### 3. Vérifier les Relations
```php
$planete = Planete::find(1);
dd($planete->getRelations()); // Relations chargées
dd($planete->relationLoaded('decouvertes')); // true/false
```

---

## 🚨 Problème #4 : Syntaxe @json() dans Blade

### ❌ MAUVAISE APPROCHE
```blade
{{-- ❌ ERREUR : @json() ne peut pas contenir de ternaire avec tableaux --}}
const data = @json($var ? ['id' => $var->id, 'nom' => $var->nom] : null);
// Parse Error: Unclosed '[' on line X does not match ')'
```

### ✅ BONNE APPROCHE

**Option 1 : Utiliser @if/@else Blade**
```blade
@if($systemeActuel)
const systemeData = {
    id: {{ $systemeActuel->id }},
    nom: "{{ $systemeActuel->nom }}",
    position_x: {{ $systemeActuel->position_x }}
};
@else
const systemeData = null;
@endif
```

**Option 2 : Préparer dans le contrôleur**
```php
// Dans le contrôleur
$systemeData = $systemeActuel ? [
    'id' => $systemeActuel->id,
    'nom' => $systemeActuel->nom,
    'position_x' => $systemeActuel->position_x,
] : null;

return view('...', compact('systemeData'));
```

```blade
{{-- Dans la vue --}}
const systemeData = @json($systemeData);
```

**Option 3 : Utiliser {!! json_encode() !!}**
```blade
const systemeData = {!! json_encode($systemeActuel ? [
    'id' => $systemeActuel->id,
    'nom' => $systemeActuel->nom,
] : null) !!};
```

### 📋 Règles à Suivre
1. **@json() doit recevoir une variable simple**, pas une expression complexe
2. **Préférer @if/@else** pour la lisibilité
3. **Ou préparer les données dans le contrôleur** (meilleure pratique)
4. **{!! json_encode() !!}** si vraiment nécessaire, mais attention au XSS

---

## 📚 Ressources

- [Laravel Eloquent Best Practices](https://laravel.com/docs/11.x/eloquent)
- [DTO Pattern in Laravel](https://laravel-news.com/data-transfer-object)
- [Debugging Laravel Queries](https://laravel.com/docs/11.x/queries#debugging)

---

**Dernière mise à jour : 2025-12-11**
