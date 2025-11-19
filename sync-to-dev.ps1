# Script PowerShell de synchronisation d'une branche vers dev
# Compatible Windows PowerShell 5.1+ et PowerShell Core 7+
# Usage: .\sync-to-dev.ps1 [-SourceBranch <branche>] [-Force]

param(
    [string]$SourceBranch = "",
    [switch]$Force = $false
)

$ErrorActionPreference = "Stop"
$stashed = $false

function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

function Write-Header {
    param([string]$Text)
    Write-ColorOutput "======================================================" "Green"
    Write-ColorOutput "  $Text" "Green"
    Write-ColorOutput "======================================================" "Green"
}

Write-Header "Synchronisation branche vers Dev"

# Verifier qu'on est dans un repo Git
if (-not (Test-Path ".git")) {
    Write-ColorOutput "Erreur: Ce n'est pas un depot Git" "Red"
    exit 1
}

# Determiner la branche source
if ($SourceBranch -ne "") {
    Write-ColorOutput "Branche source (parametre): $SourceBranch" "Yellow"

    # Verifier que la branche existe
    git show-ref --verify --quiet "refs/heads/$SourceBranch" 2>$null
    if ($LASTEXITCODE -ne 0) {
        # Verifier sur remote
        git show-ref --verify --quiet "refs/remotes/origin/$SourceBranch" 2>$null
        if ($LASTEXITCODE -ne 0) {
            Write-ColorOutput "Erreur: La branche $SourceBranch n'existe pas" "Red"
            exit 1
        }
        else {
            Write-ColorOutput "Branche trouvee sur remote, checkout..." "Yellow"
            git checkout $SourceBranch
        }
    }
}
else {
    $SourceBranch = git branch --show-current
    if ($LASTEXITCODE -ne 0) {
        Write-ColorOutput "Impossible de recuperer la branche actuelle" "Red"
        exit 1
    }
    Write-ColorOutput "Branche source (actuelle): $SourceBranch" "Yellow"
}

# Sauvegarder la branche actuelle pour le retour
$currentBranch = git branch --show-current

# Verifier qu'il n'y a pas de modifications non commitees
$status = git status --short
if ($status) {
    Write-ColorOutput "Modifications non commitees detectees" "Yellow"

    if (-not $Force) {
        $response = Read-Host "Voulez-vous les stasher ? (O/N)"
        if ($response -match "^[Oo]$") {
            git stash save "Auto-stash avant sync vers dev"
            Write-ColorOutput "Modifications stashees" "Green"
            $stashed = $true
        }
        else {
            Write-ColorOutput "Annulation" "Red"
            exit 1
        }
    }
    else {
        git stash save "Auto-stash avant sync vers dev"
        Write-ColorOutput "Modifications stashees (mode force)" "Green"
        $stashed = $true
    }
}

# Recuperer les dernieres modifications
Write-ColorOutput "Fetch des branches distantes..." "Yellow"
git fetch origin

# Verifier si la branche dev existe
git show-ref --verify --quiet "refs/remotes/origin/dev" 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-ColorOutput "Branche dev trouvee" "Green"
}
else {
    Write-ColorOutput "Branche dev n'existe pas sur origin" "Yellow"

    if (-not $Force) {
        $response = Read-Host "Voulez-vous la creer ? (O/N)"
        if ($response -match "^[Oo]$") {
            git checkout -b dev
            git push -u origin dev
            Write-ColorOutput "Branche dev creee" "Green"
        }
        else {
            Write-ColorOutput "Annulation" "Red"
            exit 1
        }
    }
    else {
        git checkout -b dev
        git push -u origin dev
        Write-ColorOutput "Branche dev creee (mode force)" "Green"
    }
}

# Basculer sur dev
Write-ColorOutput "Bascule sur la branche dev..." "Yellow"
git checkout dev
if ($LASTEXITCODE -ne 0) {
    Write-ColorOutput "Erreur lors du checkout de dev" "Red"
    exit 1
}

# Merger la branche source
Write-ColorOutput "Fusion de $SourceBranch dans dev..." "Yellow"
git merge $SourceBranch --no-edit
if ($LASTEXITCODE -ne 0) {
    Write-ColorOutput "Conflits detectes" "Red"
    Write-ColorOutput "Resolvez les conflits, puis executez:" "Yellow"
    Write-ColorOutput "  git add ." "White"
    Write-ColorOutput "  git commit" "White"
    Write-ColorOutput "  git push origin dev" "White"
    exit 1
}
Write-ColorOutput "Fusion reussie" "Green"

# Push vers origin/dev
Write-ColorOutput "Push vers origin/dev..." "Yellow"
git push origin dev
if ($LASTEXITCODE -ne 0) {
    Write-ColorOutput "Erreur lors du push" "Red"
    Write-ColorOutput "Essayez manuellement: git push origin dev" "Yellow"
    exit 1
}
Write-ColorOutput "Push reussi" "Green"

# Retour sur la branche d'origine
if (-not $Force) {
    $response = Read-Host "Voulez-vous retourner sur $currentBranch ? (O/N)"
    if ($response -match "^[Oo]$") {
        git checkout $currentBranch
        Write-ColorOutput "Retour sur $currentBranch" "Green"
    }
}
else {
    git checkout $currentBranch
    Write-ColorOutput "Retour sur $currentBranch (mode force)" "Green"
}

# Restaurer le stash si necessaire
if ($stashed) {
    if (-not $Force) {
        $response = Read-Host "Voulez-vous restaurer les modifications stashees ? (O/N)"
        if ($response -match "^[Oo]$") {
            git stash pop
            Write-ColorOutput "Modifications restaurees" "Green"
        }
    }
    else {
        git stash pop
        Write-ColorOutput "Modifications restaurees (mode force)" "Green"
    }
}

Write-Header "Synchronisation terminee avec succes !"
