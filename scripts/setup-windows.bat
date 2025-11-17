@echo off
REM Script d'installation automatique pour Windows 11
REM Conquête Spatiale - Configuration du projet Laravel

echo ========================================
echo   Conquête Spatiale - Setup Windows 11
echo ========================================
echo.

echo [1/8] Verification des prerequis...

REM Vérifier PHP
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo   X PHP non trouve
    echo     Consultez docs/INSTALLATION_WINDOWS.md pour installer PHP
    pause
    exit /b 1
) else (
    php -r "if (version_compare(PHP_VERSION, '8.2.0', '<')) { echo '  X PHP 8.2+ requis. Version actuelle: ' . PHP_VERSION; exit(1); } echo '  √ PHP ' . PHP_VERSION . ' detecte';"
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo     Consultez docs/INSTALLATION_WINDOWS.md pour installer PHP 8.2+
        pause
        exit /b 1
    )
)

REM Vérifier Composer
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo   X Composer non trouve
    echo     Telechargez depuis https://getcomposer.org/
    pause
    exit /b 1
) else (
    echo   √ Composer detecte
)

REM Vérifier Node.js
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo   X Node.js non trouve
    echo     Telechargez depuis https://nodejs.org/
    pause
    exit /b 1
) else (
    for /f "tokens=*" %%i in ('node -v') do set NODE_VERSION=%%i
    echo   √ Node.js !NODE_VERSION! detecte
)

REM Vérifier npm
where npm >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo   X npm non trouve (devrait etre installe avec Node.js)
    pause
    exit /b 1
) else (
    for /f "tokens=*" %%i in ('npm -v') do set NPM_VERSION=%%i
    echo   √ npm !NPM_VERSION! detecte
)

echo.

REM Installation des dépendances Composer
echo [2/8] Installation des dependances PHP (Composer)...
call composer install
if %ERRORLEVEL% NEQ 0 (
    echo   X Erreur lors de l'installation Composer
    pause
    exit /b 1
)
echo   √ Dependances PHP installees
echo.

REM Copie du fichier .env
echo [3/8] Configuration de l'environnement...
if not exist ".env" (
    copy .env.example .env >nul
    echo   √ Fichier .env cree
) else (
    echo   i Fichier .env existe deja (non modifie)
)
echo.

REM Génération de la clé d'application
echo [4/8] Generation de la cle d'application...
php artisan key:generate --force
echo   √ Cle d'application generee
echo.

REM Création de la base de données SQLite
echo [5/8] Creation de la base de donnees SQLite...
if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
    echo   √ Base de donnees creee: database\database.sqlite
) else (
    echo   i Base de donnees existe deja: database\database.sqlite
)
echo.

REM Exécution des migrations
echo [6/8] Execution des migrations...
php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo   ! Erreur lors des migrations (peut-etre deja executees)
) else (
    echo   √ Migrations executees
)
echo.

REM Installation des dépendances Node.js
echo [7/8] Installation des dependances Node.js...
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo   X Erreur lors de l'installation npm
    pause
    exit /b 1
)
echo   √ Dependances Node.js installees
echo.

REM Compilation des assets
echo [8/8] Compilation des assets...
call npm run build
if %ERRORLEVEL% NEQ 0 (
    echo   ! Erreur lors de la compilation des assets
) else (
    echo   √ Assets compiles
)
echo.

REM Vérification de la version PHP et extensions
echo [Info] Verification de la configuration PHP...
php scripts\check-php-version.php
echo.

REM Résumé
echo ========================================
echo   Installation terminee avec succes!
echo ========================================
echo.
echo Prochaines etapes:
echo   1. Demarrer le serveur: php artisan serve
echo   2. Ou utiliser le script: scripts\start-dev.bat
echo   3. Ouvrir dans le navigateur: http://localhost:8000
echo.
echo Pour plus d'informations, consultez:
echo   docs/INSTALLATION_WINDOWS.md
echo   docs/game-design/GUIDE_DEMARRAGE.md
echo.

pause
