<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

// Routes publiques
Route::get('/', [GameController::class, 'index'])->name('home');

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Sélection/création de personnage
    Route::get('/personnage/selection', [GameController::class, 'selectionPersonnage'])->name('personnage.selection');
    Route::post('/personnage/creer', [GameController::class, 'creerPersonnage'])->name('personnage.creer');
    Route::post('/personnage/activer/{personnage}', [GameController::class, 'activerPersonnage'])->name('personnage.activer');

    // Interface de jeu (nécessite un personnage actif)
    Route::middleware('personnage.actif')->group(function () {
        Route::get('/dashboard', [GameController::class, 'dashboard'])->name('dashboard');
        Route::post('/command', [GameController::class, 'executeCommand'])->name('command');
    });
});

// Routes auth API
require __DIR__.'/auth.php';
