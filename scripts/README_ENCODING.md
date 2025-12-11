# 🔧 Scripts de Correction d'Encodage UTF-8

Ce dossier contient des scripts Python pour corriger les problèmes d'encodage dans les fichiers du projet.

## 📋 Scripts Disponibles

### `fix.py` - Script Universel (RECOMMANDÉ) ⭐

Script principal qui combine toutes les méthodes de correction.

**Usage :**
```bash
# Corriger tous les fichiers dans docs/game-design
python3 scripts/fix.py docs/game-design

# Mode dry-run (test sans modification)
python3 scripts/fix.py docs/game-design --dry-run

# Mode verbose (affiche les fichiers OK)
python3 scripts/fix.py docs/game-design --verbose

# Corriger un seul fichier
python3 scripts/fix.py docs/game-design/GDD_Central.md
```

**Fonctionnalités :**
- ✅ Détection automatique du type de problème
- ✅ Correction du double encodage UTF-8
- ✅ Restauration des emojis communs
- ✅ Remplacement contextuel des caractères
- ✅ Force UTF-8 propre en dernier recours

---

### `fix-force.py` - Correction Forcée

Force la correction en testant plusieurs méthodes.

**Usage :**
```bash
python3 scripts/fix-force.py docs/game-design
```

---

### `fix-emojis.py` - Restauration des Emojis

Restaure spécifiquement les emojis et caractères spéciaux perdus.

**Usage :**
```bash
python3 scripts/fix-emojis.py docs/game-design
```

---

## 🎯 Utilisation Recommandée

### Pour corriger des fichiers mal encodés :

1. **Test en dry-run** (pour voir ce qui sera fait) :
   ```bash
   python3 scripts/fix.py docs/game-design --dry-run
   ```

2. **Correction réelle** :
   ```bash
   python3 scripts/fix.py docs/game-design
   ```

3. **Vérification** :
   ```bash
   file -bi docs/game-design/*.md
   ```

---

## 🔍 Types de Problèmes Détectés

### Double Encodage UTF-8
Quand un fichier UTF-8 est relu comme ISO-8859-1 puis réencodé :
- `é` devient `Ã©`
- `à` devient `Ã `
- `ê` devient `Ãª`

**Solution :** Encoder en ISO-8859-1 puis décoder en UTF-8

### Emojis Cassés
Les emojis peuvent devenir :
- `���` (caractères de remplacement)
- `ðŸ` (séquence UTF-8 mal interprétée)

**Solution :** Remplacement contextuel basé sur le GDD

### Caractères de Remplacement
Les caractères `�` (U+FFFD) indiquent des octets invalides.

**Solution :** Force UTF-8 + nettoyage

---

## ⚙️ Configuration

Les scripts peuvent être personnalisés via :

### Extensions traitées
```python
EXTENSIONS = {'.md', '.php', '.txt', '.json', '.yml', '.yaml', '.js', '.css', '.html'}
```

### Dossiers ignorés
```python
IGNORE = {'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache', '.vscode', '.idea'}
```

### Remplacements contextuels
Modifiez le dictionnaire `CONTEXT_FIXES` dans `fix.py` :
```python
CONTEXT_FIXES = {
    '??? NOTES': '⚠️📋 NOTES',
    'CO?TS': 'COÛTS',
    # Ajoutez vos patterns ici
}
```

---

## 📊 Exemple de Résultat

```
======================================================================
🔧 CORRECTEUR UNIVERSEL D'ENCODAGE UTF-8
======================================================================

📁 Traitement du dossier: docs/game-design

  ✓ CONTEXT.md
  🔄 GDD_Central.md
      ✅ Corrigé (double_encoding)
  ✓ GDD_Architecture_Technique.md

======================================================================
📊 RÉSUMÉ
======================================================================
  Fichiers analysés : 25
  Corrects          : 12
  Corrigés          : 13
  Erreurs           : 0

  Méthodes utilisées:
    - double_encoding: 4
    - force_utf8_context: 9
======================================================================
```

---

## 🆘 Dépannage

### Le script ne corrige pas correctement

1. Vérifiez l'encodage actuel :
   ```bash
   file -bi votre_fichier.md
   ```

2. Essayez en mode verbose :
   ```bash
   python3 scripts/fix.py votre_fichier.md --verbose
   ```

3. Essayez le script force :
   ```bash
   python3 scripts/fix-force.py votre_fichier.md
   ```

### Caractères toujours incorrects après correction

Si certains caractères sont toujours mauvais, ajoutez-les dans `CONTEXT_FIXES` :
```python
'mauvais_texte': 'bon_texte',
```

---

## ✅ Vérification Finale

Pour vérifier que tous les fichiers sont en UTF-8 :

```bash
# Afficher l'encodage de tous les .md
file -bi docs/game-design/*.md

# Compter les fichiers UTF-8
file -bi docs/game-design/*.md | grep -c "utf-8"

# Chercher des patterns de problèmes
grep -r "Ã©\|Ã \|ðŸ" docs/game-design/
```

Si cette commande ne retourne rien, tous les fichiers sont corrects ! ✨

---

**Note :** Ces scripts ont été créés pour résoudre les problèmes d'encodage spécifiques rencontrés dans le projet Conquête Galactique.
