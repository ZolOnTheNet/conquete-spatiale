#!/bin/bash
# Script de synchronisation de la branche claude vers dev
# Compatible Ubuntu et Debian 12

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Synchronisation branche Claude → Dev${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"

# Vérifier qu'on est dans un repo git
if [ ! -d .git ]; then
    echo -e "${RED}❌ Erreur: Ce n'est pas un dépôt Git${NC}"
    exit 1
fi

# Récupérer le nom de la branche actuelle
CURRENT_BRANCH=$(git branch --show-current)
echo -e "${YELLOW}📍 Branche actuelle: ${CURRENT_BRANCH}${NC}"

# Vérifier qu'il n'y a pas de modifications non commitées
if [[ -n $(git status -s) ]]; then
    echo -e "${YELLOW}⚠️  Modifications non commitées détectées${NC}"
    echo -e "${YELLOW}Voulez-vous les stasher ? (o/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Oo]$ ]]; then
        git stash save "Auto-stash avant sync vers dev"
        echo -e "${GREEN}✓ Modifications stashées${NC}"
        STASHED=1
    else
        echo -e "${RED}❌ Annulation${NC}"
        exit 1
    fi
fi

# Récupérer les dernières modifications
echo -e "${YELLOW}📥 Fetch des branches distantes...${NC}"
git fetch origin

# Vérifier si la branche dev existe
if git show-ref --verify --quiet refs/remotes/origin/dev; then
    echo -e "${GREEN}✓ Branche dev trouvée${NC}"
else
    echo -e "${YELLOW}⚠️  Branche dev n'existe pas sur origin${NC}"
    echo -e "${YELLOW}Voulez-vous la créer ? (o/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Oo]$ ]]; then
        git checkout -b dev
        git push -u origin dev
        echo -e "${GREEN}✓ Branche dev créée${NC}"
    else
        echo -e "${RED}❌ Annulation${NC}"
        exit 1
    fi
fi

# Basculer sur dev
echo -e "${YELLOW}🔄 Bascule sur la branche dev...${NC}"
git checkout dev

# Merger la branche claude
echo -e "${YELLOW}🔀 Fusion de ${CURRENT_BRANCH} dans dev...${NC}"
if git merge "$CURRENT_BRANCH" --no-edit; then
    echo -e "${GREEN}✓ Fusion réussie${NC}"
else
    echo -e "${RED}❌ Conflits détectés${NC}"
    echo -e "${YELLOW}Résolvez les conflits, puis exécutez:${NC}"
    echo -e "  git add ."
    echo -e "  git commit"
    echo -e "  git push origin dev"
    exit 1
fi

# Push vers origin/dev
echo -e "${YELLOW}📤 Push vers origin/dev...${NC}"
if git push origin dev; then
    echo -e "${GREEN}✓ Push réussi${NC}"
else
    echo -e "${RED}❌ Erreur lors du push${NC}"
    echo -e "${YELLOW}Essayez manuellement: git push origin dev${NC}"
    exit 1
fi

# Retour sur la branche d'origine si souhaité
echo -e "${YELLOW}Voulez-vous retourner sur ${CURRENT_BRANCH} ? (o/n)${NC}"
read -r response
if [[ "$response" =~ ^[Oo]$ ]]; then
    git checkout "$CURRENT_BRANCH"
    echo -e "${GREEN}✓ Retour sur ${CURRENT_BRANCH}${NC}"
fi

# Restaurer le stash si nécessaire
if [ "${STASHED:-0}" -eq 1 ]; then
    echo -e "${YELLOW}Voulez-vous restaurer les modifications stashées ? (o/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Oo]$ ]]; then
        git stash pop
        echo -e "${GREEN}✓ Modifications restaurées${NC}"
    fi
fi

echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✓ Synchronisation terminée avec succès !${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
