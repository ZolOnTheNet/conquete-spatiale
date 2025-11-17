@echo off
REM Script de démarrage pour l'environnement de développement
REM Conquête Spatiale - Laravel 12

echo ========================================
echo   Conquête Spatiale - Dev Server
echo ========================================
echo.

REM Vérifier si Composer est installé
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERREUR] Composer n'est pas installe
    echo Consultez docs/INSTALLATION_WINDOWS.md
    pause
    exit /b 1
)

REM Vérifier si node est installé
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERREUR] Node.js n'est pas installe
    echo Consultez docs/INSTALLATION_WINDOWS.md
    pause
    exit /b 1
)

REM Vérifier si les dépendances sont installées
if not exist "vendor\" (
    echo [INFO] Dossier vendor/ manquant. Lancement de l'installation...
    call composer install
)

if not exist "node_modules\" (
    echo [INFO] Dossier node_modules/ manquant. Lancement de l'installation...
    call npm install
)

REM Vérifier si .env existe
if not exist ".env" (
    echo [INFO] Fichier .env manquant. Copie depuis .env.example...
    copy .env.example .env
    echo [INFO] Generation de la cle d'application...
    php artisan key:generate
)

REM Vérifier si la base de données existe
if not exist "database\database.sqlite" (
    echo [INFO] Base de donnees manquante. Creation...
    type nul > database\database.sqlite
    echo [INFO] Execution des migrations...
    php artisan migrate --force
)

echo.
echo ========================================
echo   Demarrage des services...
echo ========================================
echo.
echo Services qui vont demarrer:
echo   - Laravel Server  : http://localhost:8000
echo   - Vite Dev Server : http://localhost:5173
echo   - Queue Worker    : En arriere-plan
echo   - Laravel Pail    : Logs en temps reel
echo.
echo Appuyez sur Ctrl+C pour arreter tous les services
echo.

REM Lancer composer dev (qui lance tous les services via concurrently)
composer dev

pause
