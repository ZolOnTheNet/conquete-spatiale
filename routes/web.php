<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VaisseauController;
use App\Http\Controllers\ComController;

// Page d'accueil avec login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Page d'inscription
Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register.form');

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Sélection/création de personnage
    Route::get('/personnage/selection', [GameController::class, 'selectionPersonnage'])->name('personnage.selection');
    Route::post('/personnage/creer', [GameController::class, 'creerPersonnage'])->name('personnage.creer');
    Route::post('/personnage/activer/{personnage}', [GameController::class, 'activerPersonnage'])->name('personnage.activer');

    // Interface de jeu (nécessite un personnage actif)
    Route::middleware('personnage.actif')->group(function () {
        Route::get('/dashboard', [GameController::class, 'dashboard'])->name('dashboard');
        Route::post('/command', [GameController::class, 'executeCommand'])->name('command');

        // Carte de l'univers (systèmes découverts)
        Route::get('/carte', [GameController::class, 'carte'])->name('carte');
        Route::get('/carte/secteur/{x}/{y}/{z}', [GameController::class, 'carteSecteur'])->name('carte.secteur');

        // Routes Vaisseau (Timonerie, Ingénierie, Soute, Armement)
        Route::prefix('vaisseau')->name('vaisseau.')->group(function () {
            // Timonerie
            Route::get('/position', [VaisseauController::class, 'position'])->name('position');
            Route::get('/scanner', [VaisseauController::class, 'scanner'])->name('scanner');

            // Ingénierie
            Route::get('/etat', [VaisseauController::class, 'etat'])->name('etat');
            Route::get('/reparations', [VaisseauController::class, 'reparations'])->name('reparations');

            // Soute
            Route::get('/cargaison', [VaisseauController::class, 'cargaison'])->name('cargaison');

            // Armement
            Route::get('/armes', [VaisseauController::class, 'armes'])->name('armes');
        });

        // Inventaire (accessible depuis vaisseau ou station)
        Route::get('/inventaire', [VaisseauController::class, 'inventaire'])->name('inventaire');

        // Routes COM (Communications)
        Route::prefix('com')->name('com.')->group(function () {
            Route::get('/databases', [ComController::class, 'databases'])->name('databases');
            Route::get('/prix', [ComController::class, 'prix'])->name('prix');
            Route::get('/demandes', [ComController::class, 'demandes'])->name('demandes');
            Route::get('/messages', [ComController::class, 'messages'])->name('messages');
        });

        // API AJAX pour panneaux
        Route::get('/api/status', [GameController::class, 'apiGetStatus'])->name('api.status');
        Route::get('/api/vaisseau', [GameController::class, 'apiGetVaisseau'])->name('api.vaisseau');
        Route::get('/api/carte', [GameController::class, 'apiGetCarte'])->name('api.carte');
    });

    // Routes Admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/comptes', [AdminController::class, 'comptes'])->name('comptes');
        Route::get('/univers', [AdminController::class, 'univers'])->name('univers');
        Route::get('/univers/{id}', [AdminController::class, 'showSysteme'])->name('univers.show');
        Route::post('/univers/{id}/update-puissance', [AdminController::class, 'updatePuissance'])->name('univers.update-puissance');
        Route::post('/univers/{id}/recalculer-puissance', [AdminController::class, 'recalculerPuissance'])->name('univers.recalculer-puissance');
        Route::get('/planetes', [AdminController::class, 'planetes'])->name('planetes');
        Route::get('/planetes/{id}', [AdminController::class, 'showPlanete'])->name('planetes.show');
        Route::post('/planetes/{id}', [AdminController::class, 'updatePlanete'])->name('planetes.update');
        Route::get('/production', [AdminController::class, 'production'])->name('production');
        Route::post('/production/gisement/{id}', [AdminController::class, 'updateGisement'])->name('production.update');
        Route::post('/production/gisement', [AdminController::class, 'storeGisement'])->name('production.gisement.store');
        Route::get('/carte', [AdminController::class, 'carte'])->name('carte');
        Route::get('/carte/secteur/{x}/{y}/{z}', [AdminController::class, 'carteSecteur'])->name('carte.secteur');
        Route::get('/backup', [AdminController::class, 'backup'])->name('backup');

        // Routes pour les mines (MAME)
        Route::get('/mines', [AdminController::class, 'mines'])->name('mines');
        Route::post('/mines', [AdminController::class, 'storeMine'])->name('mines.store');
        Route::post('/mines/{id}', [AdminController::class, 'updateMine'])->name('mines.update');
        Route::delete('/mines/{id}', [AdminController::class, 'destroyMine'])->name('mines.destroy');
        Route::post('/mines/{id}/ravitailler', [AdminController::class, 'ravitaillerMine'])->name('mines.ravitailler');
        Route::post('/mines/{id}/maintenance', [AdminController::class, 'maintenanceMine'])->name('mines.maintenance');
    });
});

// Routes auth API
require __DIR__.'/auth.php';
