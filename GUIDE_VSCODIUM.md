# 🎨 Guide VSCodium - Configuration Encodage & Fins de Lignes

**Date**: 2025-11-23

---

## ✅ Configuration Automatique Activée

Votre projet est déjà configuré pour **éviter les problèmes d'encodage** dans VSCodium/VS Code !

### 📁 Fichiers de configuration

1. **`.editorconfig`** ✅ (déjà présent)
   - UTF-8 obligatoire
   - Fins de lignes LF (Linux)
   - Reconnu par VSCodium et tous les éditeurs modernes

2. **`.vscode/settings.json`** ✅ (créé)
   - Configuration spécifique VSCodium
   - Force UTF-8 pour tous les fichiers
   - Force LF même sur Windows

3. **`.gitattributes`** ✅ (déjà présent)
   - Normalisation Git automatique
   - LF dans le repository

---

## 🔍 Vérifier l'Encodage dans VSCodium

### Barre de Statut (en bas à droite)

Vous devriez voir :
```
UTF-8    LF    Spaces: 4
```

**Si vous voyez autre chose :**

| Affichage | Signification | Action |
|-----------|---------------|--------|
| `UTF-8` | ✅ Correct | Rien à faire |
| `Windows 1252` | ❌ Mauvais encodage | Cliquer dessus → "Reopen with Encoding" → UTF-8 |
| `ISO-8859-1` | ❌ Latin-1 | Cliquer dessus → "Reopen with Encoding" → UTF-8 |
| `CRLF` | ⚠️ Windows | Cliquer dessus → Sélectionner "LF" |
| `LF` | ✅ Correct | Rien à faire |

---

## 🛠️ Actions Manuelles (si besoin)

### Changer l'encodage d'un fichier

1. **Méthode 1 : Barre de statut**
   - Cliquer sur l'encodage affiché (ex: "Windows 1252")
   - Choisir "Save with Encoding"
   - Sélectionner "UTF-8"

2. **Méthode 2 : Palette de commandes**
   - `Ctrl+Shift+P` (ou `Cmd+Shift+P` sur Mac)
   - Taper: "Change File Encoding"
   - Sélectionner "Save with Encoding"
   - Choisir "UTF-8"

### Changer les fins de lignes

1. **Méthode 1 : Barre de statut**
   - Cliquer sur "CRLF" ou "LF"
   - Sélectionner "LF"

2. **Méthode 2 : Palette de commandes**
   - `Ctrl+Shift+P`
   - Taper: "Change End of Line Sequence"
   - Sélectionner "LF"

---

## 🔧 Extensions VSCodium Recommandées

### Extension EditorConfig

Si pas déjà installée :

1. `Ctrl+Shift+X` (Extensions)
2. Chercher: "EditorConfig for VS Code"
3. Installer (éditeur: EditorConfig)

**Avantage** : Lit automatiquement le fichier `.editorconfig` et applique les règles.

### Extension UTF-8 Health

Pour détecter les problèmes d'encodage :

1. Chercher: "UTF-8 Health"
2. Installer
3. Utilisation: `Ctrl+Shift+P` → "UTF-8 Health: Check Files"

---

## 🎯 Workflow Recommandé

### Lors de l'ouverture d'un fichier

1. **Vérifier la barre de statut** (en bas à droite)
   - Encodage = `UTF-8` ✅
   - Fins de lignes = `LF` ✅

2. **Si mauvais encodage détecté**
   - Cliquer sur l'encodage
   - "Reopen with Encoding" → UTF-8
   - Vérifier que le contenu s'affiche correctement
   - Sauvegarder (`Ctrl+S`)

### Lors de la création d'un fichier

- VSCodium utilisera **automatiquement** UTF-8 et LF
- Grâce à `.vscode/settings.json` et `.editorconfig`

### Lors de la sauvegarde

- **Automatique** :
  - Espaces en fin de ligne supprimés
  - Ligne vide ajoutée à la fin
  - Encodage UTF-8 forcé
  - Fins de lignes LF forcées

---

## 📊 Paramètres VSCodium Actifs

Grâce à `.vscode/settings.json`, ces paramètres sont actifs :

```json
{
  "files.encoding": "utf8",           // UTF-8 par défaut
  "files.eol": "\n",                  // LF par défaut
  "files.autoGuessEncoding": false,   // Pas de détection auto (forcer UTF-8)
  "files.insertFinalNewline": true,   // Ligne vide à la fin
  "files.trimTrailingWhitespace": true // Nettoyer espaces
}
```

---

## 🚨 Cas Particuliers

### Fichier avec double encodage (COÃ›TS → COÛTS)

Si vous ouvrez un fichier et voyez des caractères bizarres :

1. **Ne pas sauvegarder !**
2. Fermer le fichier
3. Exécuter : `python fix-double-encoding.py`
4. Rouvrir le fichier

### Fichier créé sur Windows avec mauvais encodage

1. Ouvrir le fichier
2. Vérifier barre de statut → Si pas UTF-8 :
3. Cliquer encodage → "Reopen with Encoding" → UTF-8
4. Si ça affiche bien → "Save with Encoding" → UTF-8

---

## 🎨 Visualiser les Caractères Invisibles

Pour voir les espaces, tabulations et fins de lignes :

1. `Ctrl+Shift+P`
2. "View: Toggle Render Whitespace"

Ou dans `.vscode/settings.json` :
```json
"editor.renderWhitespace": "all"
```

Affichage :
- `·` = espace
- `→` = tabulation
- `↓` = fin de ligne (LF)
- `↵` = fin de ligne (CRLF)

---

## 💡 Raccourcis Utiles

| Raccourci | Action |
|-----------|--------|
| `Ctrl+Shift+P` | Palette de commandes |
| `Ctrl+K M` | Changer le langage du fichier |
| `Ctrl+S` | Sauvegarder (applique les règles) |
| `Ctrl+K Ctrl+S` | Raccourcis clavier |

---

## 🔍 Diagnostiquer un Problème

### Fichier s'affiche mal dans VSCodium

**Symptômes** : Caractères étranges (Ã©, Ã , etc.)

**Causes possibles** :
1. Mauvais encodage (pas UTF-8)
2. Double encodage (UTF-8 mal interprété)

**Solution** :
```bash
# Identifier l'encodage
python -c "import chardet; print(chardet.detect(open('fichier.md', 'rb').read()))"

# Si double encodage détecté
python fix-double-encoding.py

# Sinon, forcer UTF-8 dans VSCodium
# (voir section "Changer l'encodage")
```

---

## ✅ Checklist Configuration

- [x] `.editorconfig` présent et configuré
- [x] `.vscode/settings.json` créé avec règles UTF-8 + LF
- [x] `.gitattributes` configuré pour Git
- [x] Extension EditorConfig installée (recommandé)
- [ ] Vérifier barre de statut : UTF-8 + LF
- [ ] Tester avec un nouveau fichier `.md`

---

## 📚 Références

- [EditorConfig](https://editorconfig.org/)
- [VSCodium Encoding](https://vscodium.com/)
- [UTF-8 Everywhere](https://utf8everywhere.org/)

---

## 🎯 Résumé : Comment Éviter les Problèmes

### ✅ Configuration Actuelle (Automatique)

Grâce aux 3 fichiers de config, **vous n'avez rien à faire** :
- Tous les nouveaux fichiers → UTF-8 + LF automatiquement
- Tous les fichiers existants → Respectent les règles à la sauvegarde
- Git → Normalise automatiquement

### ⚠️ Vigilance Minimale

**Uniquement quand vous ouvrez un fichier créé ailleurs** :
1. Regarder barre de statut (2 secondes)
2. Si pas UTF-8 → "Reopen with Encoding" → UTF-8
3. Sauvegarder

**C'est tout !** 🎉

---

**Dernière mise à jour** : 2025-11-23
