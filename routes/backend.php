<?php

use App\Http\Controllers\BackendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend Routes (Administration)
|--------------------------------------------------------------------------
|
| Routes protégées par le middleware 'admin' pour l'interface backend.
| Toutes les routes sont préfixées par '/backend' et nécessitent is_admin = true.
|
*/

// Dashboard principal
Route::get('/', [BackendController::class, 'dashboard'])->name('dashboard');

// Carte stellaire 3D
Route::get('/carte', [BackendController::class, 'carte'])->name('carte');

// APIs JSON pour la carte
Route::prefix('api')->name('api.')->group(function () {
    // Récupérer tous les systèmes stellaires
    Route::get('/systemes', [BackendController::class, 'apiSystemes'])->name('systemes');

    // Récupérer positions des joueurs
    Route::get('/joueurs', [BackendController::class, 'apiJoueurs'])->name('joueurs');

    // Téléporter un personnage
    Route::post('/teleport/{personnage}', [BackendController::class, 'apiTeleport'])->name('teleport');
});

// Backup & Restore
Route::prefix('backup')->name('backup.')->group(function () {
    // Interface de gestion
    Route::get('/', [BackendController::class, 'backupIndex'])->name('index');

    // Créer une sauvegarde
    Route::post('/create', [BackendController::class, 'backupCreate'])->name('create');

    // Télécharger une sauvegarde
    Route::get('/download/{filename}', [BackendController::class, 'backupDownload'])->name('download');

    // Restaurer une sauvegarde
    Route::post('/restore/{filename}', [BackendController::class, 'backupRestore'])->name('restore');

    // Supprimer une sauvegarde
    Route::delete('/delete/{filename}', [BackendController::class, 'backupDelete'])->name('delete');

    // Lister les sauvegardes disponibles
    Route::get('/list', [BackendController::class, 'backupList'])->name('list');
});
