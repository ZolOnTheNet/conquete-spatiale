<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AdminController;

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
        Route::get('/backup', [AdminController::class, 'backup'])->name('backup');
    });
});

// Routes auth API
require __DIR__.'/auth.php';
