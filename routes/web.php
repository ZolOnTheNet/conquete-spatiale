<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStationController;
use App\Http\Controllers\Admin\AdminMineController;
use App\Http\Controllers\Admin\AdminResourceController;
use App\Http\Controllers\VaisseauController;
use App\Http\Controllers\ComController;
use App\Http\Controllers\PersonnageController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\JeuController;
use App\Http\Controllers\TimonerieController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\MarcheController;
use App\Http\Controllers\RavitaillementController;
use App\Http\Controllers\GarageController;

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
        Route::get('/carte/3d', [PersonnageController::class, 'carte3d'])->name('carte.3d');

        // Routes Menu Personnage
        Route::prefix('personnage')->name('personnage.')->group(function () {
            Route::get('/dossier', [PersonnageController::class, 'dossier'])->name('dossier');
            Route::get('/spatiocarte', [PersonnageController::class, 'spatiocarte'])->name('spatiocarte');
            Route::get('/gestion', [PersonnageController::class, 'gestion'])->name('gestion');
        });

        // Routes Menu Navire
        Route::prefix('navire')->name('navire.')->group(function () {
            Route::get('/timonerie', [TimonerieController::class, 'index'])->name('timonerie');
            Route::post('/timonerie/calculer-saut', [TimonerieController::class, 'calculerSaut'])->name('timonerie.calculer-saut');
            Route::post('/timonerie/effectuer-saut', [TimonerieController::class, 'effectuerSaut'])->name('timonerie.effectuer-saut');
            Route::post('/timonerie/s-approcher', [TimonerieController::class, 'sApprocher'])->name('timonerie.s-approcher');
            Route::post('/timonerie/s-amarrer', [TimonerieController::class, 'sAmarrer'])->name('timonerie.s-amarrer');
            Route::post('/timonerie/annuler-calcul', [TimonerieController::class, 'annulerCalculSaut'])->name('timonerie.annuler-calcul');
            Route::post('/timonerie/ameliorer-calcul', [TimonerieController::class, 'ameliorerCalculSaut'])->name('timonerie.ameliorer-calcul');
            Route::post('/timonerie/s-orbiter', [TimonerieController::class, 'sOrbiter'])->name('timonerie.s-orbiter');
            Route::post('/timonerie/atterrir', [TimonerieController::class, 'atterrir'])->name('timonerie.atterrir');
            Route::post('/timonerie/tourner', [TimonerieController::class, 'tourner'])->name('timonerie.tourner');

            // Routes de scan
            Route::post('/scan/simple', [ScanController::class, 'scanSimple'])->name('scan.simple');
            Route::post('/scan/reglage', [ScanController::class, 'scanAvecReglage'])->name('scan.reglage');
            Route::post('/scan/astro', [ScanController::class, 'scanAvecAstro'])->name('scan.astro');
            Route::post('/scan/reinitialiser-bonus', [ScanController::class, 'reinitialiserBonus'])->name('scan.reinitialiser-bonus');
            Route::post('/scan/reinitialiser-tout', [ScanController::class, 'reinitialiserTousLesScans'])->name('scan.reinitialiser-tout');
            Route::get('/scan/liste', [ScanController::class, 'listeScans'])->name('scan.liste');

            Route::get('/ingenierie', [VaisseauController::class, 'etat'])->name('ingenierie');
            Route::get('/com', [ComController::class, 'databases'])->name('com');
            Route::get('/soute', [VaisseauController::class, 'cargaison'])->name('soute');
            Route::get('/equipage', [VaisseauController::class, 'equipage'])->name('equipage');
        });

        // Routes Menu Station
        Route::prefix('station')->name('station.')->group(function () {
            // Transbordement et embarquement
            Route::post('/transborder', [StationController::class, 'transborder'])->name('transborder');
            Route::post('/embarquer', [StationController::class, 'embarquer'])->name('embarquer');

            // Menu principal
            Route::get('/menu', [StationController::class, 'menu'])->name('menu');

            // Services existants
            Route::get('/hall', [StationController::class, 'hall'])->name('hall');
            Route::get('/hangar', [StationController::class, 'hangar'])->name('hangar');
            Route::get('/marche', [StationController::class, 'marche'])->name('marche');
            Route::get('/missions', [StationController::class, 'missions'])->name('missions');
            Route::get('/cantina', [StationController::class, 'cantina'])->name('cantina');

            // Services en construction
            Route::get('/hopital', [StationController::class, 'hopital'])->name('hopital');
            Route::get('/industrie', [StationController::class, 'industrie'])->name('industrie');

            // Information station
            Route::get('/{station}', [StationController::class, 'show'])->name('show');
        });

        // Routes Marché
        Route::prefix('marche')->name('marche.')->group(function () {
            Route::get('/', [MarcheController::class, 'index'])->name('index');
            Route::post('/acheter', [MarcheController::class, 'acheter'])->name('acheter');
            Route::post('/vendre', [MarcheController::class, 'vendre'])->name('vendre');
        });

        // Routes Ravitaillement
        Route::prefix('ravitaillement')->name('ravitaillement.')->group(function () {
            Route::get('/', [RavitaillementController::class, 'index'])->name('index');
            Route::post('/complet', [RavitaillementController::class, 'ravitaillerComplet'])->name('complet');
            Route::post('/carburant', [RavitaillementController::class, 'ravitaillerCarburant'])->name('carburant');
        });

        // Routes Garage
        Route::prefix('garage')->name('garage.')->group(function () {
            Route::get('/', [GarageController::class, 'index'])->name('index');
            Route::post('/reparer-coque', [GarageController::class, 'reparerCoque'])->name('reparer-coque');
            Route::post('/reparer-panne', [GarageController::class, 'reparerPanne'])->name('reparer-panne');
            Route::post('/reparer-tout', [GarageController::class, 'reparerTout'])->name('reparer-tout');
        });

        // Routes Menu Jeu
        Route::prefix('jeu')->name('jeu.')->group(function () {
            Route::get('/profil', [JeuController::class, 'profil'])->name('profil');
        });

        // Routes Vaisseau (anciennes, à garder pour compatibilité)
        Route::prefix('vaisseau')->name('vaisseau.')->group(function () {
            // Timonerie
            Route::get('/position', [VaisseauController::class, 'position'])->name('position');

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
        Route::get('/api/carte/systeme/{id}', [PersonnageController::class, 'carte3dSysteme'])->name('api.carte.3d.systeme');
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

        // Routes pour la gestion des systèmes stellaires
        Route::post('/systeme/{id}/generer-planetes', [AdminController::class, 'genererPlanetes'])->name('systeme.generer-planetes');
        Route::post('/systeme/creer', [AdminController::class, 'creerSystemeSolaire'])->name('systeme.creer');

        // Nouveaux contrôleurs Admin (refactoring)
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Stations admin CRUD
        Route::resource('stations', AdminStationController::class)->except(['show']);

        // Mines admin CRUD + actions
        Route::resource('mines-admin', AdminMineController::class)->except(['show'])->parameters(['mines-admin' => 'mine']);
        Route::post('/mines-admin/{mine}/ravitailler', [AdminMineController::class, 'ravitailler'])->name('mines-admin.ravitailler');
        Route::post('/mines-admin/{mine}/maintenance', [AdminMineController::class, 'maintenance'])->name('mines-admin.maintenance');

        // Ressources et gisements
        Route::resource('ressources', AdminResourceController::class)->except(['show', 'destroy']);
        Route::get('/gisements', [AdminResourceController::class, 'gisements'])->name('gisements.index');
        Route::get('/gisements/create', [AdminResourceController::class, 'createGisement'])->name('gisements.create');
        Route::post('/gisements', [AdminResourceController::class, 'storeGisement'])->name('gisements.store');
    });
});

// Routes auth API
require __DIR__.'/auth.php';
