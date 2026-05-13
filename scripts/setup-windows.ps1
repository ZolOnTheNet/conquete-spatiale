# Script d'installation automatique pour Windows 11
# Conquête Spatiale - Configuration du projet Laravel

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Conquête Spatiale - Setup Windows 11" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérification des prérequis
Write-Host "[1/6] Vérification des prérequis..." -ForegroundColor Yellow

# Vérifier PHP
try {
    $phpVersion = php -r "echo PHP_VERSION;"
    if ([version]$phpVersion -lt [version]"8.2.0") {
        Write-Host "ERREUR: PHP 8.2+ requis. Version actuelle: $phpVersion" -ForegroundColor Red
        Write-Host "Consultez docs/INSTALLATION_WINDOWS.md pour installer PHP" -ForegroundColor Yellow
        exit 1
    }
    Write-Host "  ✓ PHP $phpVersion détecté" -ForegroundColor Green
} catch {
    Write-Host "  ✗ PHP non trouvé" -ForegroundColor Red
    Write-Host "    Consultez docs/INSTALLATION_WINDOWS.md pour installer PHP" -ForegroundColor Yellow
    exit 1
}

# Vérifier Composer
try {
    $composerVersion = composer --version 2>&1 | Select-String -Pattern "Composer version" | Out-String
    Write-Host "  ✓ Composer détecté" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Composer non trouvé" -ForegroundColor Red
    Write-Host "    Téléchargez depuis https://getcomposer.org/" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Installation des dépendances Composer
Write-Host "[2/6] Installation des dépendances PHP (Composer)..." -ForegroundColor Yellow
composer install
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ✗ Erreur lors de l'installation Composer" -ForegroundColor Red
    exit 1
}
Write-Host "  ✓ Dépendances PHP installées" -ForegroundColor Green
Write-Host ""

# Copie du fichier .env
Write-Host "[3/6] Configuration de l'environnement..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "  ✓ Fichier .env créé" -ForegroundColor Green
} else {
    Write-Host "  ℹ Fichier .env existe déjà (non modifié)" -ForegroundColor Cyan
}
Write-Host ""

# Génération de la clé d'application
Write-Host "[4/6] Génération de la clé d'application..." -ForegroundColor Yellow
php artisan key:generate --force
Write-Host "  ✓ Clé d'application générée" -ForegroundColor Green
Write-Host ""

# Création de la base de données SQLite
Write-Host "[5/6] Création de la base de données SQLite..." -ForegroundColor Yellow
$dbPath = "database\database.sqlite"
if (-not (Test-Path $dbPath)) {
    New-Item -Path $dbPath -ItemType File | Out-Null
    Write-Host "  ✓ Base de données créée: $dbPath" -ForegroundColor Green
} else {
    Write-Host "  ℹ Base de données existe déjà: $dbPath" -ForegroundColor Cyan
}
Write-Host ""

# Exécution des migrations
Write-Host "[6/6] Exécution des migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ⚠ Erreur lors des migrations (peut-être déjà exécutées)" -ForegroundColor Yellow
} else {
    Write-Host "  ✓ Migrations exécutées" -ForegroundColor Green
}
Write-Host ""

# Vérification de la configuration PHP
Write-Host "[Info] Vérification de la configuration PHP..." -ForegroundColor Cyan
php scripts\check-php-version.php
Write-Host ""

# Résumé
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Installation terminée avec succès!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Prochaines étapes:" -ForegroundColor Yellow
Write-Host "  1. Démarrer le serveur: " -NoNewline
Write-Host "php artisan serve" -ForegroundColor Cyan
Write-Host "  2. Ou utiliser le script: " -NoNewline
Write-Host ".\scripts\start-dev.bat" -ForegroundColor Cyan
Write-Host "  3. Ouvrir dans le navigateur: " -NoNewline
Write-Host "http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Pour plus d'informations, consultez:" -ForegroundColor Yellow
Write-Host "  docs/INSTALLATION_WINDOWS.md" -ForegroundColor Cyan
Write-Host "  docs/game-design/GUIDE_DEMARRAGE.md" -ForegroundColor Cyan
Write-Host ""
