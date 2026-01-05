# Plan Sonnet #01 : Validation et Consolidation

**Objectif :** Vérifier que tout le travail P0/P1 fonctionne correctement

---

## Tâche 1 : Exécuter la migration des indexes

```bash
php artisan migrate:status
php artisan migrate
```

Si erreur, vérifier le fichier `database/migrations/2026_01_01_185617_add_missing_indexes.php`

---

## Tâche 2 : Vérifier la syntaxe PHP de tous les fichiers créés

```bash
php -l app/Traits/Detectable.php
php -l app/Http/Middleware/InjectPersonnage.php
php -l app/Http/Controllers/Admin/AdminDashboardController.php
php -l app/Http/Controllers/Admin/AdminStationController.php
php -l app/Http/Controllers/Admin/AdminMineController.php
php -l app/Http/Controllers/Admin/AdminResourceController.php
php -l app/Services/MarcheService.php
```

---

## Tâche 3 : Vérifier les routes admin

```bash
php artisan route:list --path=admin
```

Les routes attendues :
- `GET /admin` - Dashboard
- `GET/POST /admin/stations` - CRUD stations
- `GET/POST /admin/mines` - CRUD mines
- `GET/POST /admin/ressources` - CRUD ressources
- `GET/POST /admin/gisements` - CRUD gisements

---

## Tâche 4 : Vérifier le layout admin

Le fichier `resources/views/layouts/admin.blade.php` doit exister et contenir :
- Le menu de navigation admin
- Les slots pour le contenu
- Les styles Tailwind

Si absent, le créer basé sur ce template :

```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Conquête Spatiale')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.index') }}" class="text-xl font-bold text-blue-400">
                    Admin Panel
                </a>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.comptes') }}" class="hover:text-blue-400">Comptes</a>
                    <a href="{{ route('admin.univers') }}" class="hover:text-blue-400">Univers</a>
                    <a href="{{ route('admin.planetes') }}" class="hover:text-blue-400">Planètes</a>
                    <a href="{{ route('admin.stations.index') }}" class="hover:text-blue-400">Stations</a>
                    <a href="{{ route('admin.mines.index') }}" class="hover:text-blue-400">Mines</a>
                    <a href="{{ route('admin.ressources.index') }}" class="hover:text-blue-400">Ressources</a>
                    <a href="{{ route('admin.gisements.index') }}" class="hover:text-blue-400">Gisements</a>
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
    <div class="container mx-auto px-4 mt-4">
        <div class="bg-green-600 text-white px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="container mx-auto px-4 mt-4">
        <div class="bg-red-600 text-white px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    </div>
    @endif

    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>
</body>
</html>
```

---

## Tâche 5 : Vider les caches et tester

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan serve --port=8001
```

Tester dans le navigateur :
1. Aller sur http://localhost:8001/admin
2. Naviguer vers Stations, Mines, Ressources
3. Créer une entrée test dans chaque section
4. Vérifier que les listes s'affichent correctement

---

## Critères de succès

- [ ] Migration exécutée sans erreur
- [ ] Tous les fichiers PHP valides syntaxiquement
- [ ] Toutes les routes admin visibles
- [ ] Layout admin fonctionnel
- [ ] Navigation entre les pages sans erreur 500

---

*Plan créé le 3 janvier 2026 pour Claude Code Sonnet*
