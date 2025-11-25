#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script simple pour corriger les problèmes d'encodage UTF-8 dans les fichiers du projet.
Ne nécessite pas de dépendances externes.
Usage: python3 fix_encoding_simple.py [chemin_du_fichier_ou_dossier]
"""

import os
import sys
from pathlib import Path

# Extensions de fichiers à traiter
EXTENSIONS = {'.php', '.js', '.css', '.md', '.txt', '.json', '.yml', '.yaml'}

# Fichiers/dossiers à ignorer
IGNORE_PATTERNS = {
    'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache',
    '.vscode', '.idea', 'public/build', 'composer.lock', 'package-lock.json'
}

def should_process(file_path):
    """Vérifie si le fichier doit être traité"""
    # Ignorer les patterns
    for pattern in IGNORE_PATTERNS:
        if pattern in str(file_path):
            return False

    # Vérifier l'extension
    if file_path.suffix in EXTENSIONS:
        return True

    # Cas spécial pour .blade.php
    if file_path.name.endswith('.blade.php'):
        return True

    return False

def fix_encoding(file_path, dry_run=False):
    """Tente de corriger l'encodage d'un fichier"""
    # Liste des encodages à tester
    encodings = ['utf-8', 'iso-8859-1', 'windows-1252', 'latin-1']

    content = None
    source_encoding = None

    # Essayer de lire avec différents encodages
    for encoding in encodings:
        try:
            with open(file_path, 'r', encoding=encoding) as f:
                content = f.read()
                source_encoding = encoding
                break
        except (UnicodeDecodeError, LookupError):
            continue

    if content is None:
        print(f"❌ {file_path} - Impossible de lire le fichier")
        return False

    # Vérifier s'il y a des caractères mal encodés (comme Ã©, Ã , etc.)
    has_encoding_issues = any(char in content for char in ['Ã©', 'Ã ', 'Ã¨', 'Ãª', 'Ã´', 'Ã§', 'Å"', 'Ã‰', 'ðŸ'])

    if source_encoding == 'utf-8' and not has_encoding_issues:
        print(f"✓ {file_path} - Déjà en UTF-8 correct")
        return True

    if has_encoding_issues or source_encoding != 'utf-8':
        print(f"🔄 {file_path}")
        print(f"   Encodage source: {source_encoding}")

        if has_encoding_issues:
            print(f"   ⚠️  Caractères mal encodés détectés")

        if dry_run:
            print(f"   [DRY-RUN] Serait converti en UTF-8")
            return True

        try:
            # Réécrire en UTF-8
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"   ✅ Converti en UTF-8")
            return True
        except Exception as e:
            print(f"   ❌ Erreur: {e}")
            return False

    return True

def process_path(path, dry_run=False):
    """Traite un fichier ou un dossier"""
    path = Path(path)

    if path.is_file():
        if should_process(path):
            fix_encoding(path, dry_run)
    elif path.is_dir():
        print(f"\n📁 Traitement du dossier: {path}\n")
        files_processed = 0
        files_converted = 0

        for file_path in path.rglob('*'):
            if file_path.is_file() and should_process(file_path):
                files_processed += 1
                result = fix_encoding(file_path, dry_run)
                if result:
                    files_converted += 1

        print(f"\n📊 Résumé:")
        print(f"   Fichiers traités: {files_processed}")
        print(f"   Fichiers OK/convertis: {files_converted}")
    else:
        print(f"❌ Chemin invalide: {path}")

def main():
    print("=" * 60)
    print("🔧 Correcteur d'encodage UTF-8 (version simple)")
    print("=" * 60)

    # Arguments
    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv

    if dry_run:
        print("\n⚠️  MODE DRY-RUN - Aucune modification ne sera effectuée\n")

    # Chemin à traiter
    if len(sys.argv) > 1 and not sys.argv[1].startswith('-'):
        target = sys.argv[1]
    else:
        target = '.'

    process_path(target, dry_run)

    print("\n" + "=" * 60)
    print("✅ Traitement terminé")
    print("=" * 60)

if __name__ == '__main__':
    main()
