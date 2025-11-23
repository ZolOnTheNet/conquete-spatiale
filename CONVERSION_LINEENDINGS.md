# 🔄 Gestion des Fins de Lignes (Line Endings)

## 🚨 Problème

Lorsque vous travaillez entre Windows et Linux/Ubuntu, les fins de lignes sont différentes :
- **Windows** : CRLF (`\r\n`)
- **Linux/Mac** : LF (`\n`)

Cela peut causer des problèmes de formatage dans les fichiers `.md`, `.php`, etc.

---

## ✅ Solution Définitive : Git Auto-normalisation

Le fichier **`.gitattributes`** est déjà configuré pour normaliser automatiquement les fins de lignes :

```gitattributes
* text=auto eol=lf
```

Cela signifie :
- **Dans le repository Git** : Tous les fichiers texte sont en LF
- **Dans votre copie locale** : Git convertit automatiquement selon votre OS

### Configuration Git (à faire une fois)

Sur **Windows** :
```bash
git config --global core.autocrlf true
```

Sur **Linux/Ubuntu** :
```bash
git config --global core.autocrlf input
```

### Renormaliser tous les fichiers existants

Si vous avez déjà des fichiers avec de mauvaises fins de lignes :

```bash
# 1. Sauvegarder vos changements
git add --all
git commit -m "Sauvegarde avant renormalisation"

# 2. Supprimer le cache Git
git rm --cached -r .

# 3. Renormaliser
git reset --hard

# 4. Ré-ajouter tous les fichiers (Git appliquera .gitattributes)
git add --all

# 5. Committer
git commit -m "Normalisation des fins de lignes"
```

---

## 🛠️ Scripts de Conversion Manuel

Si vous devez convertir manuellement (rarement nécessaire) :

### Convertir en format Linux (LF)

```bash
python text2linux.py
```

Convertit tous les fichiers `.md` du dossier `docs/` en format Linux.

### Convertir en format Windows (CRLF)

```bash
python text2win.py
```

Convertit tous les fichiers `.md` du dossier `docs/` en format Windows.

---

## 🎯 Workflow Recommandé

### Sur Windows

1. **Configuration Git** :
   ```bash
   git config --global core.autocrlf true
   ```

2. **Travailler normalement** :
   - Git convertit automatiquement LF → CRLF en checkout
   - Git convertit automatiquement CRLF → LF en commit

3. **Vérifier** :
   ```bash
   git config core.autocrlf
   # Devrait afficher: true
   ```

### Sur Linux/Ubuntu

1. **Configuration Git** :
   ```bash
   git config --global core.autocrlf input
   ```

2. **Travailler normalement** :
   - Git garde LF en checkout
   - Git convertit automatiquement CRLF → LF en commit (si présent)

3. **Vérifier** :
   ```bash
   git config core.autocrlf
   # Devrait afficher: input
   ```

---

## 🔍 Vérifier les Fins de Lignes

### Avec Git

```bash
# Voir les fichiers avec CRLF
git ls-files --eol

# Voir uniquement les problèmes
git ls-files --eol | grep "w/crlf"
```

### Avec VSCode

1. Ouvrir un fichier
2. Regarder en bas à droite : `LF` ou `CRLF`
3. Cliquer dessus pour changer

### Avec la commande `file` (Linux)

```bash
file docs/GDD_Central.md
# Devrait afficher: ASCII text (si LF)
# Ou: ASCII text, with CRLF line terminators (si CRLF)
```

---

## 📝 Résumé

**Meilleure approche** : Laisser Git gérer automatiquement avec `.gitattributes` + `core.autocrlf`

**Scripts Python** : Uniquement si besoin de conversion manuelle ponctuelle

**À ne PAS faire** :
- ❌ Committer des fichiers avec de mauvaises fins de lignes
- ❌ Mélanger LF et CRLF dans le même fichier
- ❌ Désactiver `core.autocrlf` sans raison

---

## 🆘 Dépannage

### Problème : Git montre tous les fichiers comme modifiés

```bash
# Réinitialiser les fins de lignes
git rm --cached -r .
git reset --hard
```

### Problème : Fichier avec fins de lignes mixtes

```bash
# Utiliser le script Python
python text2linux.py

# Puis committer
git add docs/
git commit -m "Fix: normalisation fins de lignes"
```

---

**Date de création** : 2025-11-23
**Dernière mise à jour** : 2025-11-23
