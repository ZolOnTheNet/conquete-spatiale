#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Correction des mots français avec mauvais caractères
Remplace les patterns de mots incorrects par les bons mots
"""

import sys
import re
from pathlib import Path

# Dictionnaire de corrections de mots complets
WORD_FIXES = {
    # Mots avec È au lieu de É
    'SYSTàME': 'SYSTÈME',
    'COORDONNêES': 'COORDONNÉES',
    'COORDONNêES': 'COORDONNÉES',
    'DêCIMALE': 'DÉCIMALE',
    'ENTIàRES': 'ENTIÈRES',
    'MULTIPLIêES': 'MULTIPLIÉES',
    'RàGLE': 'RÈGLE',

    # Variations avec accents
    'àviter': 'éviter',
    'êl': 'él',
    'êment': 'ément',
    'êments': 'éments',
    'êes': 'ées',
    'êe': 'ée',

    # Mots courants
    'dêtail': 'détail',
    'dêtails': 'détails',
    'dêtaillê': 'détaillé',
    'dêtaillêe': 'détaillée',
    'dêtaillês': 'détaillés',
    'dêtaillêes': 'détaillées',

    'crêer': 'créer',
    'crêation': 'création',
    'crêê': 'créé',
    'crêêe': 'créée',
    'crêês': 'créés',
    'crêêes': 'créées',

    'gênêral': 'général',
    'gênêrale': 'générale',
    'gênêraux': 'généraux',
    'gênêralement': 'généralement',

    # Avec à mal placés
    'àlêment': 'élément',
    'àlêments': 'éléments',
    'àtê': 'été',
    'àtat': 'état',
    'àtats': 'états',

    # Autres
    'dêfini': 'défini',
    'dêfinie': 'définie',
    'dêfinis': 'définis',
    'dêfinies': 'définies',
    'dêfinition': 'définition',

    'prêsent': 'présent',
    'prêsente': 'présente',
    'prêsents': 'présents',
    'prêsentes': 'présentes',

    # Emojis/caractères spéciaux mal encodés
    '📋Ø': '📋',
    'œ†Ô∏è': '⚠️📋',
}

# Corrections de patterns regex pour attraper les variations
PATTERN_FIXES = [
    (r'\bàt([eé])\b', r'ét\1'),  # état, été
    (r'\bêl([eé])', r'él\1'),     # élément, etc.
    (r'\b([A-ZÀÈÉ])([A-Z]*)à([A-Z]+)\b', lambda m: m.group(1) + m.group(2).replace('à', 'È') + m.group(3)),  # Mots en MAJ avec à
]

def fix_file(file_path, dry_run=False):
    """Corrige les mots dans un fichier"""

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        original = content

        # Appliquer les corrections de mots
        for bad, good in WORD_FIXES.items():
            if bad in content:
                content = content.replace(bad, good)

        # Appliquer les corrections par regex
        for pattern, replacement in PATTERN_FIXES:
            content = re.sub(pattern, replacement, content)

        if content == original:
            return False, 'clean'

        if dry_run:
            changes = sum(1 for b, g in WORD_FIXES.items() if b in original)
            print(f"  🔄 {file_path.name} [DRY-RUN]")
            print(f"      Mots à corriger: {changes}")
            return True, 'would_fix'

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)

        print(f"  ✅ {file_path.name}")
        return True, 'fixed'

    except Exception as e:
        print(f"  ❌ {file_path.name}: {e}")
        return False, 'error'

def main():
    print("="*70)
    print("🔧 CORRECTION DES MOTS FRANÇAIS")
    print("="*70)
    print()

    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv
    verbose = '--verbose' in sys.argv or '-v' in sys.argv

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
        print(f"📁 Traitement du dossier: {path}\n")
        files = [f for f in sorted(path.rglob('*.md')) if f.is_file()]

    stats = {'total': len(files), 'clean': 0, 'fixed': 0, 'error': 0}

    for file_path in files:
        success, status = fix_file(file_path, dry_run)

        if success:
            if status == 'clean':
                stats['clean'] += 1
                if verbose:
                    print(f"  ✓ {file_path.name}")
            else:
                stats['fixed'] += 1
        else:
            stats['error'] += 1

    print(f"\n{'='*70}")
    print(f"📊 RÉSUMÉ")
    print(f"{'='*70}")
    print(f"  Fichiers analysés : {stats['total']}")
    print(f"  Corrects          : {stats['clean']}")
    print(f"  Corrigés          : {stats['fixed']}")
    print(f"  Erreurs           : {stats['error']}")
    print(f"{'='*70}\n")

if __name__ == '__main__':
    main()
