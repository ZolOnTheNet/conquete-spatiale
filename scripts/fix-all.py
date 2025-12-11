#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script TOUT-EN-UN de correction d'encodage UTF-8
Applique toutes les corrections dans le bon ordre
"""

import sys
import subprocess
from pathlib import Path

def run_script(script_name, target, dry_run=False):
    """Execute un script de correction"""

    cmd = ['python3', f'scripts/{script_name}', target]
    if dry_run:
        cmd.append('--dry-run')

    print(f"\n{'='*70}")
    print(f"Exécution de {script_name}...")
    print(f"{'='*70}\n")

    result = subprocess.run(cmd, capture_output=False)
    return result.returncode == 0

def main():
    print("="*70)
    print("🔧 CORRECTION COMPLÈTE D'ENCODAGE UTF-8")
    print("   (Applique tous les scripts dans le bon ordre)")
    print("="*70)
    print()

    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv

    if dry_run:
        print("⚠️  MODE DRY-RUN - Aucune modification\n")

    # Trouver le chemin cible
    target = '.'
    for arg in sys.argv[1:]:
        if not arg.startswith('-'):
            target = arg
            break

    print(f"📁 Cible: {target}\n")
    print("Les scripts seront exécutés dans cet ordre:")
    print("  1. fix-final.py   - Supprime les √ parasites")
    print("  2. fix-words.py   - Corrige les mots français")
    print()

    if not dry_run:
        response = input("Continuer? (o/N) ")
        if response.lower() not in ['o', 'oui', 'y', 'yes']:
            print("Annulé.")
            return

    # Exécuter les scripts dans l'ordre
    scripts = [
        'fix-final.py',
        'fix-words.py',
    ]

    for script in scripts:
        success = run_script(script, target, dry_run)
        if not success:
            print(f"\n❌ Erreur lors de l'exécution de {script}")
            return

    print("\n" + "="*70)
    print("✅ TOUTES LES CORRECTIONS ONT ÉTÉ APPLIQUÉES")
    print("="*70)
    print()
    print("Vérification recommandée:")
    print(f"  file -bi {target}/*.md")
    print(f"  head {target}/SYSTEME_COORDONNEES.md")
    print()

if __name__ == '__main__':
    main()
