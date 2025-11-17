# 🚀 GUIDE DE DÉMARRAGE - GDD Conquête Galactique

## Comment naviguer dans la documentation ?

### 📖 Pour découvrir le projet

**Commencez par :** [GDD_Central.md](./GDD_Central.md)

Ce document vous donne :
- ✅ Vue d'ensemble du projet
- ✅ Architecture multi-univers
- ✅ Index de toutes les sections
- ✅ Phases de développement
- ✅ Disclaimers importants

**Temps de lecture :** ~15 minutes

---

### 📚 Pour une étude complète

**Consultez :** [GDD_Conquete_Galactique.md](./GDD_Conquete_Galactique.md)

Document exhaustif contenant :
- Système de jeu Daggerheart (2D12)
- Navigation et hyperespace
- Combat et abordages
- Économie et chaîne de production
- Génération procédurale (GAIA)
- Vaisseaux et équipements
- Architecture technique

**Taille :** 2200+ lignes, ~60 pages
**Temps de lecture :** 2-3 heures

---

## ⚠️ IMPORTANT À SAVOIR

### Toutes les valeurs sont indicatives !

Les **chiffres, coûts, formules** dans ces documents sont des **suggestions**.

❌ Ce ne sont PAS :
- Des choix définitifs
- Des valeurs testées
- Des engagements du porteur de projet

✅ Ce sont :
- Des propositions pour aider la réflexion
- Des exemples de game design
- Des bases de discussion

**Tout sera à estimer, tester et équilibrer lors de l'implémentation.**

---

## 📂 Structure des Fichiers

```
GDD/
├── GDD_Central.md                    ⭐ INDEX PRINCIPAL (commencer ici)
├── GDD_Conquete_Galactique.md        📖 DOCUMENT COMPLET (référence)
├── README_GDD.md                     📝 Organisation technique
└── GUIDE_DEMARRAGE.md                🚀 Ce fichier (navigation)
```

### Fichiers modulaires (à créer si besoin)
```
GDD/
├── GDD_Systeme_Jeu.md               🎲 Dés, traits, XP
├── GDD_Navigation.md                🚀 Hyperespace, déplacements
├── GDD_Combat.md                    ⚔️ Combat, abordages, PvP
├── GDD_Vaisseaux.md                 🛸 Classes, équipements
├── GDD_Economie.md                  💰 Ressources, production
├── GDD_Generation_Procedurale.md    🌌 GAIA, systèmes stellaires
├── GDD_Detection.md                 🔭 Exploration, PoV
├── GDD_Reputation.md                🏛️ Factions, guildes
└── GDD_Technique.md                 💻 Stack, BDD, APIs
```

*Note :* Pour l'instant, tout le contenu est dans `GDD_Conquete_Galactique.md`.
Les fichiers modulaires peuvent être créés ultérieurement pour faciliter le travail d'équipe.

---

## 🎯 Parcours recommandés

### Pour le porteur de projet
1. Lire **GDD_Central.md** (vue d'ensemble)
2. Parcourir **GDD_Conquete_Galactique.md** (sections pertinentes)
3. Identifier les sections à modifier/valider
4. Demander corrections spécifiques

### Pour un développeur
1. Lire **GDD_Central.md** (contexte)
2. Section "Architecture Technique" dans **GDD_Conquete_Galactique.md**
3. Section "Système de Jeu Core" (mécanique 2D12)
4. Sections techniques spécifiques au besoin

### Pour un game designer
1. **GDD_Central.md** complet
2. **GDD_Conquete_Galactique.md** complet
3. Focus sur les mécaniques de gameplay
4. Tester et proposer ajustements

### Pour un artiste/UI designer
1. **GDD_Central.md** (concept général)
2. Section "Format du jeu" (interface console)
3. Section "Vaisseaux et équipements"
4. Section "Détection et exploration" (visualisation)

---

## 🔄 Workflow de modification

### Comment demander une modification ?

1. **Identifier la section** dans le document
2. **Décrire précisément** ce qui doit changer
3. **Donner la nouvelle vision** attendue
4. L'assistant applique les modifications
5. Vérification et itération

### Exemple de bonne demande :
> "Dans la section Système d'XP, le coût pour passer niveau 5→6 
> devrait être 600 XP au lieu de 500. Aussi, enlève la formule 
> de progression automatique, on la déterminera par tests."

---

## 📞 Support

Pour toute question sur l'organisation de ces documents :
- Consulter **README_GDD.md** (détails techniques)
- Utiliser **GDD_Central.md** comme table des matières
- Rechercher dans **GDD_Conquete_Galactique.md** (Ctrl+F)

---

**Version :** 0.3-alpha
**Dernière mise à jour :** 2025-10-31
**Organisation :** Assistant Claude

---

## 🎮 Bon courage avec le projet !

Ce GDD est un outil de travail évolutif. N'hésitez pas à le modifier, 
l'adapter et le faire vivre selon les besoins du projet.

**Le jeu se construit itérativement, le GDD aussi !**

