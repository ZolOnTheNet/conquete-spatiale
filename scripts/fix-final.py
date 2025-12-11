#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Correction FINALE et TOTALE de l'encodage
Supprime systématiquement les caractères parasites √ (e2 88 9a)
"""

import sys
from pathlib import Path

def fix_encoding_final(file_path, dry_run=False):
    """Correction finale de l'encodage"""

    try:
        with open(file_path, 'rb') as f:
            data = f.read()

        original_data = data

        # ÉTAPE 1 : Supprimer TOUS les √ (e2 88 9a) parasites
        # Ce caractère est systématiquement inséré devant les accents
        data = data.replace(b'\xe2\x88\x9a', b'')

        # ÉTAPE 2 : Autres corrections spécifiques
        corrections = {
            # Corriger les séquences d'emojis cassés
            b'\xc5\x93\xe2\x80\xa0\xc3\x94\xe2\x88\x8f\xc3\xa8': b'\xe2\x9a\xa0\xef\xb8\x8f\xf0\x9f\x93\x8b',  # œ†Ô∏è → ⚠️📋
            b'\xef\xa3\xbf\xc3\xbc\xc3\xa9\xc3\x98': b'\xf0\x9f\x93\x8b',  # → 📋
            b'\xef\xa3\xbf\xc3\xbc\xc3\xac\xc3\xa7': b'\xf0\x9f\x94\xa7',  # → 🔧

            # Corriger les € mal encodés
            b'\xe2\x80\x9a': b'\xc5\x93',  # → œ si c'est une ligature, sinon laisser
            b'\xe2\x80\xa0': b'',  # Supprimer espace insécable parasite parfois

            # Corriger ™ → ê
            b'\xe2\x84\xa2': b'\xc3\xaa',
        }

        for bad, good in corrections.items():
            data = data.replace(bad, good)

        # Vérifier si changements
        if data == original_data:
            return False, 'clean'

        if dry_run:
            print(f"  🔄 {file_path.name} [DRY-RUN]")
            bytes_removed = len(original_data) - len(data)
            print(f"      Bytes supprimés/modifiés: {bytes_removed}")
            return True, 'would_fix'

        # Écrire
        with open(file_path, 'wb') as f:
            f.write(data)

        print(f"  ✅ {file_path.name}")

        # Vérifier que c'est du UTF-8 valide maintenant
        try:
            data.decode('utf-8')
            print(f"      ✓ UTF-8 valide")
        except UnicodeDecodeError as e:
            print(f"      ⚠️  Encore des problèmes UTF-8: {e}")

        return True, 'fixed'

    except Exception as e:
        print(f"  ❌ {file_path.name}: {e}")
        return False, 'error'

def main():
    print("="*70)
    print("🔧 CORRECTION FINALE ET TOTALE DE L'ENCODAGE")
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
        success, status = fix_encoding_final(file_path, dry_run)

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
