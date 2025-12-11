#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Restaure les emojis et caractères spéciaux perdus
"""

import sys
from pathlib import Path

# Mappings de restauration basés sur le contexte
REPLACEMENTS = {
    # Emojis courants dans la documentation
    '??? NOTES IMPORTANTES': '⚠️📋 NOTES IMPORTANTES',
    '??§ CORRECTIONS IMPORTANTES': '🔧📝 CORRECTIONS IMPORTANTES',
    '??? Document de Référence': '📄⚠️ Document de Référence',
    'â?? Principe PJ': '🎯 Principe PJ',
    'CO?TS': 'COÛTS',
    'DUR?ES': 'DURÉES',
    'NUM?RIQUES': 'NUMÉRIQUES',
    'PR?SENTS': 'PRÉSENTS',
    'T?ches': 'Tâches',
    'cr?er': 'créer',
    'cr?ation': 'création',
    'd?tailler': 'détailler',
    '??': 'œ',  # œ ligature
}

def fix_emojis(file_path, dry_run=False):
    """Restaure les emojis et caractères spéciaux"""

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        original_content = content
        changed = False

        for bad, good in REPLACEMENTS.items():
            if bad in content:
                content = content.replace(bad, good)
                changed = True

        if not changed:
            return False

        if dry_run:
            print(f"  🔄 {file_path.name} [DRY-RUN]")
            return True

        with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
            f.write(content)

        print(f"  ✅ {file_path.name}")
        return True

    except Exception as e:
        print(f"  ❌ {file_path.name}: {e}")
        return False

def main():
    print("="*70)
    print("🔧 RESTAURATION DES EMOJIS ET CARACTÈRES SPÉCIAUX")
    print("="*70)
    print()

    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv

    if dry_run:
        print("⚠️  MODE DRY-RUN\n")

    target = '.'
    for arg in sys.argv[1:]:
        if not arg.startswith('-'):
            target = arg
            break

    path = Path(target)

    files = []
    if path.is_file():
        files = [path]
    elif path.is_dir():
        files = [f for f in path.rglob('*.md') if f.is_file()]

    fixed = 0
    for file_path in sorted(files):
        if fix_emojis(file_path, dry_run):
            fixed += 1

    print(f"\n📊 {fixed} fichier(s) modifié(s)\n")

if __name__ == '__main__':
    main()
