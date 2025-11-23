#!/usr/bin/env python3
"""
Convertit tous les fichiers .md du dossier docs en format Windows (CRLF) + UTF-8
Usage: python text2win.py
"""

import os
from pathlib import Path

def convert_to_crlf(file_path):
    """Convertit un fichier en format Windows (CRLF) avec encodage UTF-8"""
    try:
        # Lire en mode texte avec gestion d'encodage
        encodings = ['utf-8', 'utf-8-sig', 'latin-1', 'cp1252', 'iso-8859-1']
        content = None

        for encoding in encodings:
            try:
                with open(file_path, 'r', encoding=encoding) as f:
                    content = f.read()
                break
            except (UnicodeDecodeError, UnicodeError):
                continue

        if content is None:
            # Fallback: lire en binaire et forcer le remplacement
            with open(file_path, 'rb') as f:
                raw_content = f.read()
            # Normaliser puis convertir en CRLF
            raw_content = raw_content.replace(b'\r\n', b'\n')
            raw_content = raw_content.replace(b'\n', b'\r\n')
            with open(file_path, 'wb') as f:
                f.write(raw_content)
            return True

        # Écrire en UTF-8 avec CRLF
        with open(file_path, 'w', encoding='utf-8', newline='\r\n') as f:
            f.write(content)

        return True
    except Exception as e:
        print(f"    Erreur: {e}")
        return False

def main():
    docs_dir = Path(__file__).parent / 'docs'

    if not docs_dir.exists():
        print(f"Erreur: Le dossier {docs_dir} n'existe pas")
        return

    print(f"Conversion des fichiers .md en format Windows (CRLF)...")
    print(f"Dossier: {docs_dir}\n")

    converted = 0
    errors = 0

    # Parcourir récursivement tous les .md
    for md_file in docs_dir.rglob('*.md'):
        print(f"  Traitement: {md_file.relative_to(docs_dir)}")
        if convert_to_crlf(md_file):
            converted += 1
        else:
            errors += 1

    print(f"\n[OK] Termine!")
    print(f"  Fichiers convertis: {converted}")
    if errors > 0:
        print(f"  Erreurs: {errors}")

if __name__ == '__main__':
    main()
