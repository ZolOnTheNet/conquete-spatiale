#!/usr/bin/env python3
"""
Corrige l'encodage des fichiers .md du dossier docs
Détecte automatiquement l'encodage et convertit en UTF-8
Usage: python fix-encoding.py
"""

import os
from pathlib import Path
import chardet

def detect_encoding(file_path):
    """Détecte l'encodage d'un fichier"""
    with open(file_path, 'rb') as f:
        raw_data = f.read()
        result = chardet.detect(raw_data)
        return result['encoding'], result['confidence']

def fix_encoding(file_path):
    """Corrige l'encodage d'un fichier en UTF-8"""
    try:
        # Détecter l'encodage actuel
        encoding, confidence = detect_encoding(file_path)

        if encoding is None:
            print(f"    [SKIP] Impossible de detecter l'encodage")
            return False

        # Lire avec l'encodage détecté
        with open(file_path, 'r', encoding=encoding, errors='replace') as f:
            content = f.read()

        # Réécrire en UTF-8
        with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
            f.write(content)

        return True, encoding, confidence
    except Exception as e:
        print(f"    [ERREUR] {e}")
        return False, None, None

def main():
    docs_dir = Path(__file__).parent / 'docs'

    if not docs_dir.exists():
        print(f"Erreur: Le dossier {docs_dir} n'existe pas")
        return

    print(f"Correction de l'encodage des fichiers .md en UTF-8...")
    print(f"Dossier: {docs_dir}\n")

    converted = 0
    errors = 0
    skipped = 0

    # Parcourir récursivement tous les .md
    for md_file in docs_dir.rglob('*.md'):
        rel_path = md_file.relative_to(docs_dir)
        print(f"  {rel_path}")

        result = fix_encoding(md_file)

        if result and result[0]:
            encoding, confidence = result[1], result[2]
            print(f"    [OK] {encoding} ({confidence:.0%}) -> UTF-8 + LF")
            converted += 1
        elif result:
            errors += 1
        else:
            skipped += 1

    print(f"\n[OK] Termine!")
    print(f"  Fichiers convertis: {converted}")
    if skipped > 0:
        print(f"  Ignores: {skipped}")
    if errors > 0:
        print(f"  Erreurs: {errors}")

if __name__ == '__main__':
    try:
        import chardet
    except ImportError:
        print("[ERREUR] Le module 'chardet' n'est pas installe.")
        print("Installez-le avec: pip install chardet")
        exit(1)

    main()
