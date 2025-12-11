# ÄÅ¸Å¡â¬ GUIDE DE DÃâ°MARRAGE - GDD ConquÃÂªte Galactique

## Comment naviguer dans la documentation ?

### ÄÅ¸ââ Pour dÃÂ©couvrir le projet

**Commencez par :** [GDD_Central.md](./GDD_Central.md)

Ce document vous donne :
- Ã¢Åâ¦ Vue d'ensemble du projet
- Ã¢Åâ¦ Architecture multi-univers
- Ã¢Åâ¦ Index de toutes les sections
- Ã¢Åâ¦ Phases de dÃÂ©veloppement
- Ã¢Åâ¦ Disclaimers importants

**Temps de lecture :** ~15 minutes

---

### ÄÅ¸âÅ¡ Pour une ÃÂ©tude complÃÂ¨te

**Consultez :** [GDD_Conquete_Galactique.md](./GDD_Conquete_Galactique.md)

Document exhaustif contenant :
- SystÃÂ¨me de jeu Daggerheart (2D12)
- Navigation et hyperespace
- Combat et abordages
- Ãâ°conomie et chaÃÂ®ne de production
- GÃÂ©nÃÂ©ration procÃÂ©durale (GAIA)
- Vaisseaux et ÃÂ©quipements
- Architecture technique

**Taille :** 2200+ lignes, ~60 pages
**Temps de lecture :** 2-3 heures

---

## Ã¢Å¡Â Ã¯Â¸ï¿½ IMPORTANT Ãâ¬ SAVOIR

### Toutes les valeurs sont indicatives !

Les **chiffres, coÃÂ»ts, formules** dans ces documents sont des **suggestions**.

Ã¢ï¿½Å Ce ne sont PAS :
- Des choix dÃÂ©finitifs
- Des valeurs testÃÂ©es
- Des engagements du porteur de projet

Ã¢Åâ¦ Ce sont :
- Des propositions pour aider la rÃÂ©flexion
- Des exemples de game design
- Des bases de discussion

**Tout sera ÃÂ  estimer, tester et ÃÂ©quilibrer lors de l'implÃÂ©mentation.**

---

## ÄÅ¸ââ Structure des Fichiers

```
GDD/
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Central.md                    Ã¢Â­ï¿½ INDEX PRINCIPAL (commencer ici)
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Conquete_Galactique.md        ÄÅ¸ââ DOCUMENT COMPLET (rÃÂ©fÃÂ©rence)
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ README_GDD.md                     ÄÅ¸âï¿½ Organisation technique
Ã¢ââÃ¢ââ¬Ã¢ââ¬ GUIDE_DEMARRAGE.md                ÄÅ¸Å¡â¬ Ce fichier (navigation)
```

### Fichiers modulaires (ÃÂ  crÃÂ©er si besoin)
```
GDD/
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Systeme_Jeu.md               ÄÅ¸ï¿½Â² DÃÂ©s, traits, XP
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Navigation.md                ÄÅ¸Å¡â¬ Hyperespace, dÃÂ©placements
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Combat.md                    Ã¢Å¡âÃ¯Â¸ï¿½ Combat, abordages, PvP
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Vaisseaux.md                 ÄÅ¸âºÂ¸ Classes, ÃÂ©quipements
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Economie.md                  ÄÅ¸âÂ° Ressources, production
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Generation_Procedurale.md    ÄÅ¸ÅÅ GAIA, systÃÂ¨mes stellaires
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Detection.md                 ÄÅ¸âÂ­ Exploration, PoV
Ã¢âÅÃ¢ââ¬Ã¢ââ¬ GDD_Reputation.md                ÄÅ¸ï¿½âºÃ¯Â¸ï¿½ Factions, guildes
Ã¢ââÃ¢ââ¬Ã¢ââ¬ GDD_Technique.md                 ÄÅ¸âÂ» Stack, BDD, APIs
```

*Note :* Pour l'instant, tout le contenu est dans `GDD_Conquete_Galactique.md`.
Les fichiers modulaires peuvent ÃÂªtre crÃÂ©ÃÂ©s ultÃÂ©rieurement pour faciliter le travail d'ÃÂ©quipe.

---

## ÄÅ¸ï¿½Â¯ Parcours recommandÃÂ©s

### Pour le porteur de projet
1. Lire **GDD_Central.md** (vue d'ensemble)
2. Parcourir **GDD_Conquete_Galactique.md** (sections pertinentes)
3. Identifier les sections ÃÂ  modifier/valider
4. Demander corrections spÃÂ©cifiques

### Pour un dÃÂ©veloppeur
1. Lire **GDD_Central.md** (contexte)
2. Section "Architecture Technique" dans **GDD_Conquete_Galactique.md**
3. Section "SystÃÂ¨me de Jeu Core" (mÃÂ©canique 2D12)
4. Sections techniques spÃÂ©cifiques au besoin

### Pour un game designer
1. **GDD_Central.md** complet
2. **GDD_Conquete_Galactique.md** complet
3. Focus sur les mÃÂ©caniques de gameplay
4. Tester et proposer ajustements

### Pour un artiste/UI designer
1. **GDD_Central.md** (concept gÃÂ©nÃÂ©ral)
2. Section "Format du jeu" (interface console)
3. Section "Vaisseaux et ÃÂ©quipements"
4. Section "DÃÂ©tection et exploration" (visualisation)

---

## ÄÅ¸ââ Workflow de modification

### Comment demander une modification ?

1. **Identifier la section** dans le document
2. **DÃÂ©crire prÃÂ©cisÃÂ©ment** ce qui doit changer
3. **Donner la nouvelle vision** attendue
4. L'assistant applique les modifications
5. VÃÂ©rification et itÃÂ©ration

### Exemple de bonne demande :
> "Dans la section SystÃÂ¨me d'XP, le coÃÂ»t pour passer niveau 5Ã¢â â6 
> devrait ÃÂªtre 600 XP au lieu de 500. Aussi, enlÃÂ¨ve la formule 
> de progression automatique, on la dÃÂ©terminera par tests."

---

## ÄÅ¸âï¿½ Support

Pour toute question sur l'organisation de ces documents :
- Consulter **README_GDD.md** (dÃÂ©tails techniques)
- Utiliser **GDD_Central.md** comme table des matiÃÂ¨res
- Rechercher dans **GDD_Conquete_Galactique.md** (Ctrl+F)

---

**Version :** 0.3-alpha
**DerniÃÂ¨re mise ÃÂ  jour :** 2025-10-31
**Organisation :** Assistant Claude

---

## ÄÅ¸ï¿½Â® Bon courage avec le projet !

Ce GDD est un outil de travail ÃÂ©volutif. N'hÃÂ©sitez pas ÃÂ  le modifier, 
l'adapter et le faire vivre selon les besoins du projet.

**Le jeu se construit itÃÂ©rativement, le GDD aussi !**

