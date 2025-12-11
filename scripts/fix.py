#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script UNIVERSEL de correction d'encodage UTF-8
Détecte et corrige automatiquement tous les problèmes d'encodage
"""

import sys
from pathlib import Path

EXTENSIONS = {'.md', '.php', '.txt', '.json', '.yml', '.yaml', '.js', '.css', '.html'}
IGNORE = {'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache', '.vscode', '.idea'}

# Patterns de détection
BAD_PATTERNS = ['Ã©', 'Ã¨', 'Ãª', 'Ã ', 'Ã¢', 'Ã´', 'Ã§', 'Ã‰', 'Ã€', 'Ã¹', 'Ã»', 'Ã®', 'Ã¯', 'Å"', 'â€', 'ðŸ', '�']

# Remplacements contextuels
CONTEXT_FIXES = {
    # Emojis courants
    '??? NOTES': '⚠️📋 NOTES',
    '??§ CORRECTIONS': '🔧📝 CORRECTIONS',
    '??? Document': '📄⚠️ Document',
    'â?? Principe': '🎯 Principe',

    # Caractères spéciaux courants
    'âœ ANCIEN': '✗ ANCIEN',
    'âœ NOUVEAU': '✅ NOUVEAU',
    'âœ': '✓',
    'â€': '→',
    'â€™': "'",
    'â€œ': '"',

    # Mots français communs
    'CO?TS': 'COÛTS',
    'DUR?ES': 'DURÉES',
    'NUM?RIQUES': 'NUMÉRIQUES',
    'PR?SENTS': 'PRÉSENTS',
    'T?ches': 'Tâches',
    'cr?er': 'créer',
    'cr?ation': 'création',
    'd?tailler': 'détailler',
    '??': 'œ',
}

def should_process(path):
    """Vérifie si le fichier doit être traité"""
    if any(ig in str(path) for ig in IGNORE):
        return False
    return path.suffix in EXTENSIONS or path.name.endswith('.blade.php')

def has_issues(content):
    """Détecte si le contenu a des problèmes d'encodage"""
    return any(pattern in content for pattern in BAD_PATTERNS)

def fix_file(file_path, dry_run=False, verbose=False):
    """Corrige un fichier avec toutes les méthodes disponibles"""

    try:
        # Lire le fichier en UTF-8
        with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()

        # Vérifier si problèmes
        if not has_issues(content):
            if verbose:
                print(f"  ✓ {file_path.name}")
            return True, 'clean'

        print(f"  🔄 {file_path.name}")

        # Étape 1 : Essayer correction double encodage
        try:
            fixed = content.encode('iso-8859-1').decode('utf-8')
            if not has_issues(fixed):
                if not dry_run:
                    file_path.write_text(fixed, encoding='utf-8')
                print(f"      ✅ Corrigé (double_encoding)")
                return True, 'double_encoding'
        except:
            pass

        # Étape 2 : Force UTF-8 + context fixes
        with open(file_path, 'rb') as f:
            raw = f.read()

        fixed = raw.decode('utf-8', errors='replace')

        # Appliquer les remplacements contextuels
        for bad, good in CONTEXT_FIXES.items():
            fixed = fixed.replace(bad, good)

        # Nettoyer les caractères de remplacement restants
        fixed = fixed.replace('\ufffd', '?')

        if not dry_run:
            file_path.write_text(fixed, encoding='utf-8')

        print(f"      ✅ Corrigé (force_utf8 + context_fixes)")
        return True, 'force_utf8_context'

    except Exception as e:
        print(f"  ❌ {file_path.name}: {e}")
        return False, 'error'

def process_path(target, dry_run=False, verbose=False):
    """Traite un fichier ou dossier"""

    path = Path(target)

    if not path.exists():
        print(f"❌ Chemin introuvable: {path}")
        return

    # Collecter les fichiers
    files = []
    if path.is_file():
        if should_process(path):
            files = [path]
    elif path.is_dir():
        print(f"📁 Traitement du dossier: {path}\n")
        files = [f for f in sorted(path.rglob('*')) if f.is_file() and should_process(f)]

    # Traiter
    stats = {'total': len(files), 'clean': 0, 'fixed': 0, 'error': 0, 'methods': {}}

    for file_path in files:
        success, method = fix_file(file_path, dry_run, verbose)
        if success:
            if method == 'clean':
                stats['clean'] += 1
            else:
                stats['fixed'] += 1
                stats['methods'][method] = stats['methods'].get(method, 0) + 1
        else:
            stats['error'] += 1

    # Résumé
    print(f"\n{'='*70}")
    print(f"📊 RÉSUMÉ")
    print(f"{'='*70}")
    print(f"  Fichiers analysés : {stats['total']}")
    print(f"  Corrects          : {stats['clean']}")
    print(f"  Corrigés          : {stats['fixed']}")
    print(f"  Erreurs           : {stats['error']}")
    if stats['methods']:
        print(f"\n  Méthodes utilisées:")
        for method, count in stats['methods'].items():
            print(f"    - {method}: {count}")
    print(f"{'='*70}\n")

def main():
    print("="*70)
    print("🔧 CORRECTEUR UNIVERSEL D'ENCODAGE UTF-8")
    print("="*70)
    print()

    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv
    verbose = '--verbose' in sys.argv or '-v' in sys.argv

    if dry_run:
        print("⚠️  MODE DRY-RUN - Aucune modification\n")

    # Trouver le chemin cible
    target = '.'
    for arg in sys.argv[1:]:
        if not arg.startswith('-'):
            target = arg
            break

    process_path(target, dry_run, verbose)
    print("✅ Traitement terminé\n")

if __name__ == '__main__':
    main()
