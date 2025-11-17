<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', [GameController::class, 'index'])->name('home');
Route::get('/dashboard', [GameController::class, 'dashboard'])->name('dashboard');
Route::post('/command', [GameController::class, 'executeCommand'])->name('command');
