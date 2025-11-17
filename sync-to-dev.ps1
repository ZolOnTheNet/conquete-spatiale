# Script PowerShell de synchronisation de la branche claude vers dev
# Compatible Windows PowerShell 5.1+ et PowerShell Core 7+

param(
    [switch]$Force = $false
)

$ErrorActionPreference = "Stop"

function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

function Write-Header {
    param([string]$Text)
    Write-ColorOutput "═══════════════════════════════════════════════════════════" "Green"
    Write-ColorOutput "  $Text" "Green"
    Write-ColorOutput "═══════════════════════════════════════════════════════════" "Green"
}

Write-Header "Synchronisation branche Claude → Dev"

# Vérifier qu'on est dans un repo Git
if (-not (Test-Path ".git")) {
    Write-ColorOutput "❌ Erreur: Ce n'est pas un dépôt Git" "Red"
    exit 1
}

# Récupérer le nom de la branche actuelle
try {
    $currentBranch = git branch --show-current
    Write-ColorOutput "📍 Branche actuelle: $currentBranch" "Yellow"
} catch {
    Write-ColorOutput "❌ Impossible de récupérer la branche actuelle" "Red"
    exit 1
}

# Vérifier qu'il n'y a pas de modifications non commitées
$status = git status --short
if ($status) {
    Write-ColorOutput "⚠️  Modifications non commitées détectées" "Yellow"

    if (-not $Force) {
        $response = Read-Host "Voulez-vous les stasher ? (O/N)"
        if ($response -match "^[Oo]$") {
            git stash save "Auto-stash avant sync vers dev"
            Write-ColorOutput "✓ Modifications stashées" "Green"
            $stashed = $true
        } else {
            Write-ColorOutput "❌ Annulation" "Red"
            exit 1
        }
    } else {
        git stash save "Auto-stash avant sync vers dev"
        Write-ColorOutput "✓ Modifications stashées (mode force)" "Green"
        $stashed = $true
    }
}

# Récupérer les dernières modifications
Write-ColorOutput "📥 Fetch des branches distantes..." "Yellow"
git fetch origin

# Vérifier si la branche dev existe
$devExists = git show-ref --verify --quiet refs/remotes/origin/dev
if ($LASTEXITCODE -eq 0) {
    Write-ColorOutput "✓ Branche dev trouvée" "Green"
} else {
    Write-ColorOutput "⚠️  Branche dev n'existe pas sur origin" "Yellow"

    if (-not $Force) {
        $response = Read-Host "Voulez-vous la créer ? (O/N)"
        if ($response -match "^[Oo]$") {
            git checkout -b dev
            git push -u origin dev
            Write-ColorOutput "✓ Branche dev créée" "Green"
        } else {
            Write-ColorOutput "❌ Annulation" "Red"
            exit 1
        }
    } else {
        git checkout -b dev
        git push -u origin dev
        Write-ColorOutput "✓ Branche dev créée (mode force)" "Green"
    }
}

# Basculer sur dev
Write-ColorOutput "🔄 Bascule sur la branche dev..." "Yellow"
git checkout dev

# Merger la branche claude
Write-ColorOutput "🔀 Fusion de $currentBranch dans dev..." "Yellow"
try {
    git merge $currentBranch --no-edit
    Write-ColorOutput "✓ Fusion réussie" "Green"
} catch {
    Write-ColorOutput "❌ Conflits détectés" "Red"
    Write-ColorOutput "Résolvez les conflits, puis exécutez:" "Yellow"
    Write-ColorOutput "  git add ." "White"
    Write-ColorOutput "  git commit" "White"
    Write-ColorOutput "  git push origin dev" "White"
    exit 1
}

# Push vers origin/dev
Write-ColorOutput "📤 Push vers origin/dev..." "Yellow"
try {
    git push origin dev
    Write-ColorOutput "✓ Push réussi" "Green"
} catch {
    Write-ColorOutput "❌ Erreur lors du push" "Red"
    Write-ColorOutput "Essayez manuellement: git push origin dev" "Yellow"
    exit 1
}

# Retour sur la branche d'origine si souhaité
if (-not $Force) {
    $response = Read-Host "Voulez-vous retourner sur $currentBranch ? (O/N)"
    if ($response -match "^[Oo]$") {
        git checkout $currentBranch
        Write-ColorOutput "✓ Retour sur $currentBranch" "Green"
    }
} else {
    git checkout $currentBranch
    Write-ColorOutput "✓ Retour sur $currentBranch (mode force)" "Green"
}

# Restaurer le stash si nécessaire
if ($stashed) {
    if (-not $Force) {
        $response = Read-Host "Voulez-vous restaurer les modifications stashées ? (O/N)"
        if ($response -match "^[Oo]$") {
            git stash pop
            Write-ColorOutput "✓ Modifications restaurées" "Green"
        }
    } else {
        git stash pop
        Write-ColorOutput "✓ Modifications restaurées (mode force)" "Green"
    }
}

Write-Header "✓ Synchronisation terminée avec succès !"

# Usage avec le flag -Force pour exécuter sans questions
# .\sync-to-dev.ps1 -Force
