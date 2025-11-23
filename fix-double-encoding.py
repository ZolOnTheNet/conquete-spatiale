#!/usr/bin/env python3
"""
Corrige le double encodage UTF-8 des fichiers .md
(UTF-8 interprété comme Latin-1/Windows-1252)

Usage: python fix-double-encoding.py
"""

import os
from pathlib import Path

def fix_double_encoding(file_path):
    """
    Corrige le double encodage UTF-8

    Exemple: "COÃ›TS" -> "COÛTS"
    Cela arrive quand du UTF-8 est mal interprété comme Latin-1/Windows-1252
    """
    try:
        # Lire le fichier comme si c'était du Latin-1
        with open(file_path, 'r', encoding='latin-1') as f:
            content = f.read()

        # Vérifier s'il y a des caractères suspects de double encodage
        suspicious_patterns = ['Ã©', 'Ã¨', 'Ã ', 'Ã´', 'Ã®', 'Ã«', 'Ã¯', 'Ã¼', 'Ã¢', 'Ãª', 'Ã§', 'Ã¹', 'Ã»', 'Ã']

        has_double_encoding = any(pattern in content for pattern in suspicious_patterns)

        if not has_double_encoding:
            # Pas de double encodage détecté, essayer de lire en UTF-8
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                    # Réécrire en UTF-8 avec LF pour normaliser
                    with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
                        f.write(content)
                    return "OK (deja UTF-8)"
            except:
                # Si ça échoue, on garde le contenu latin-1
                pass

        # Le contenu en latin-1 contient en réalité du UTF-8 mal encodé
        # On le réencode en bytes (latin-1), puis on le décode en UTF-8
        try:
            content_bytes = content.encode('latin-1')
            content_fixed = content_bytes.decode('utf-8')
        except:
            # Si ça échoue, essayer avec Windows-1252
            try:
                content_bytes = content.encode('windows-1252')
                content_fixed = content_bytes.decode('utf-8')
            except:
                return "ERREUR (impossible de corriger)"

        # Écrire le contenu corrigé en UTF-8 avec LF
        with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
            f.write(content_fixed)

        return "CORRIGE"

    except Exception as e:
        return f"ERREUR ({e})"

def main():
    docs_dir = Path(__file__).parent / 'docs'

    if not docs_dir.exists():
        print(f"Erreur: Le dossier {docs_dir} n'existe pas")
        return

    print(f"Correction du double encodage UTF-8...")
    print(f"Dossier: {docs_dir}\n")

    corrected = 0
    already_ok = 0
    errors = 0

    # Parcourir récursivement tous les .md
    for md_file in docs_dir.rglob('*.md'):
        rel_path = md_file.relative_to(docs_dir)

        result = fix_double_encoding(md_file)

        if result == "CORRIGE":
            print(f"  [CORRIGE] {rel_path}")
            corrected += 1
        elif result.startswith("OK"):
            print(f"  [OK] {rel_path}")
            already_ok += 1
        else:
            print(f"  [ERREUR] {rel_path} - {result}")
            errors += 1

    print(f"\n[OK] Termine!")
    print(f"  Fichiers corriges: {corrected}")
    print(f"  Deja OK: {already_ok}")
    if errors > 0:
        print(f"  Erreurs: {errors}")

if __name__ == '__main__':
    main()
