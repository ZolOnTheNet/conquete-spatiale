#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Corrige le problème spécifique où √ (racine carrée) a remplacé des accents
Pattern: √© → é, √è → è, etc.
"""

import sys
from pathlib import Path

def fix_sqrt_encoding(file_path, dry_run=False):
    """Corrige les caractères √ qui ont remplacé les accents"""

    try:
        # Lire en binaire
        with open(file_path, 'rb') as f:
            data = f.read()

        # Patterns à remplacer (bytes)
        # √© = e2 88 9a c2 a9 → é = c3 a9
        # √è = e2 88 9a c2 ae → è = c3 a8
        # √à = e2 88 9a c3 a0 → à = c3 a0
        # √â = e2 88 9a c3 a2 → ê = c3 aa
        # etc.

        replacements = {
            # Minuscules
            b'\xe2\x88\x9a\xc2\xa9': b'\xc3\xa9',  # √© → é
            b'\xe2\x88\x9a\xc2\xae': b'\xc3\xa8',  # √® → è
            b'\xe2\x88\x9a\xc3\xa0': b'\xc3\xa0',  # √à → à
            b'\xe2\x88\x9a\xc3\xa2': b'\xc3\xaa',  # √â → ê
            b'\xe2\x88\x9a\xc3\xb4': b'\xc3\xb4',  # √ô → ô
            b'\xe2\x88\x9a\xc3\xbb': b'\xc3\xbb',  # √û → û
            b'\xe2\x88\x9a\xc3\xa7': b'\xc3\xa7',  # √ç → ç
            b'\xe2\x88\x9a\xc3\xac': b'\xc3\xae',  # √¬ → î
            b'\xe2\x88\x9a\xc3\xaf': b'\xc3\xaf',  # √¯ → ï
            b'\xe2\x88\x9a\xc3\xb9': b'\xc3\xb9',  # √ù → ù

            # Majuscules
            b'\xe2\x88\x9a\xc3\x89': b'\xc3\x89',  # √É → É
            b'\xe2\x88\x9a\xc3\x88': b'\xc3\x88',  # √È → È
            b'\xe2\x88\x9a\xc3\x80': b'\xc3\x80',  # √À → À
            b'\xe2\x88\x9a\xc3\x8a': b'\xc3\x8a',  # √Ê → Ê
            b'\xe2\x88\x9a\xc3\x94': b'\xc3\x94',  # √Ô → Ô
            b'\xe2\x88\x9a\xc3\x87': b'\xc3\x87',  # √Ç → Ç

            # Autres caractères spéciaux
            b'\xe2\x80\x9a\xc3\xb6': b'\xc5\x93',  # œ
            b'\xef\xa3\xbf\xc3\xbc\xc3\xa9': b'\xf0\x9f\x93\x8b',  # 📋
            b'\xef\xa3\xbf\xc3\xbc\xc3\xac\xc3\xa7': b'\xf0\x9f\x94\xa7',  # 🔧
            b'\xe2\x80\x9a\xc3\xb6\xe2\x80\xa0\xc3\x94\xe2\x88\x8f\xc3\xa8': b'\xe2\x9a\xa0\xef\xb8\x8f\xf0\x9f\x93\x8b',  # ⚠️📋
            b'\xe2\x80\x9a\xe2\x80\xa0': b'\xe2\x9a\xa0\xef\xb8\x8f',  # ⚠️
            b'\xe2\x84\xa2': b'\xc3\xaa',  # ™ → ê (erreur commune)
        }

        original_data = data

        # Appliquer les remplacements
        for bad_bytes, good_bytes in replacements.items():
            data = data.replace(bad_bytes, good_bytes)

        # Vérifier si des changements ont été faits
        if data == original_data:
            return False, 'clean'

        if dry_run:
            print(f"  🔄 {file_path.name} [DRY-RUN]")
            print(f"      Nombre de corrections: {len(original_data) - len(data) + sum(data.count(g) for g in replacements.values())}")
            return True, 'would_fix'

        # Écrire le fichier corrigé
        with open(file_path, 'wb') as f:
            f.write(data)

        print(f"  ✅ {file_path.name}")
        return True, 'fixed'

    except Exception as e:
        print(f"  ❌ {file_path.name}: {e}")
        return False, 'error'

def process_path(target, dry_run=False, verbose=False):
    """Traite un fichier ou dossier"""

    path = Path(target)

    if not path.exists():
        print(f"❌ Chemin introuvable: {path}")
        return

    # Collecter fichiers
    files = []
    if path.is_file():
        files = [path]
    elif path.is_dir():
        print(f"📁 Traitement du dossier: {path}\n")
        files = [f for f in sorted(path.rglob('*.md')) if f.is_file()]

    # Statistiques
    stats = {'total': len(files), 'clean': 0, 'fixed': 0, 'error': 0}

    for file_path in files:
        success, status = fix_sqrt_encoding(file_path, dry_run)

        if success:
            if status == 'clean':
                stats['clean'] += 1
                if verbose:
                    print(f"  ✓ {file_path.name}")
            else:
                stats['fixed'] += 1
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
    print(f"{'='*70}\n")

def main():
    print("="*70)
    print("🔧 CORRECTEUR √ (RACINE CARRÉE) → ACCENTS")
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

    process_path(target, dry_run, verbose)
    print("✅ Traitement terminé\n")

if __name__ == '__main__':
    main()
