#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de correction directe par remplacement de caractères mal encodés
"""

import sys
from pathlib import Path

# Mapping des caractères mal encodés vers les bons caractères
REPLACEMENTS = {
    # Accents sur e
    'Ã©': 'é',
    'Ã¨': 'è',
    'Ãª': 'ê',
    'Ã‰': 'É',
    'Ãˆ': 'È',
    'ÃŠ': 'Ê',

    # Accents sur a
    'Ã ': 'à',
    'Ã¢': 'â',
    'Ã€': 'À',
    'Ã‚': 'Â',

    # Accents sur o
    'Ã´': 'ô',
    'Ã¶': 'ö',
    'Ã"': 'Ô',
    'Ã–': 'Ö',

    # Accents sur u
    'Ã¹': 'ù',
    'Ã»': 'û',
    'Ã¼': 'ü',
    'Ã™': 'Ù',
    'Ã›': 'Û',
    'Ãœ': 'Ü',

    # Accents sur i
    'Ã®': 'î',
    'Ã¯': 'ï',
    'ÃŽ': 'Î',
    'Ã': 'Ï',

    # Autres
    'Ã§': 'ç',
    'Ã‡': 'Ç',
    'Å"': 'œ',
    'Å'': 'Œ',

    # Emojis
    'ðŸ': '🎯',
    'âš ï¸': '⚠️',
    'âœ…': '✅',
    'ðŸš€': '🚀',
    'ðŸ"': '📊',
    'ðŸ'¡': '💡',
    'ðŸ"§': '🔧',
    'ðŸ"': '📝',
    'ðŸ"–': '📖',
    'ðŸ—': '🗺',
    'ðŸ›°': '🛰',

    # Caractères spéciaux
    'â€"': '—',
    'â€"': '–',
    'â€˜': ''',
    'â€™': ''',
    'â€œ': '"',
    'â€': '"',
    'â€¦': '…',
    'â†'': '→',
    'â†"': '←',
    'âˆ'': '−',
    'Ã—': '×',
    'Ã·': '÷',
}

EXTENSIONS = {'.php', '.js', '.css', '.md', '.txt', '.json', '.yml', '.yaml'}

IGNORE_PATTERNS = {
    'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache',
    '.vscode', '.idea', 'public/build', 'composer.lock', 'package-lock.json',
    'fix_encoding'
}

def should_process(file_path):
    for pattern in IGNORE_PATTERNS:
        if pattern in str(file_path):
            return False
    if file_path.suffix in EXTENSIONS or file_path.name.endswith('.blade.php'):
        return True
    return False

def fix_file(file_path, dry_run=False):
    try:
        with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()

        original_content = content
        replacements_made = {}

        # Appliquer tous les remplacements
        for bad, good in REPLACEMENTS.items():
            if bad in content:
                count = content.count(bad)
                content = content.replace(bad, good)
                replacements_made[bad] = (good, count)

        if not replacements_made:
            print(f"✓ {file_path} - Aucun problème détecté")
            return True

        print(f"🔄 {file_path}")
        print(f"   ⚠️  Caractères corrigés:")
        for bad, (good, count) in replacements_made.items():
            print(f"      '{bad}' → '{good}' ({count}x)")

        if dry_run:
            print(f"   [DRY-RUN] Serait corrigé")
            return True

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)

        print(f"   ✅ Corrigé avec succès")
        return True

    except Exception as e:
        print(f"❌ {file_path} - Erreur: {e}")
        return False

def process_path(path, dry_run=False):
    path = Path(path)

    if path.is_file():
        if should_process(path):
            fix_file(path, dry_run)
    elif path.is_dir():
        print(f"\n📁 Traitement du dossier: {path}\n")
        files_processed = 0
        files_fixed = 0

        for file_path in path.rglob('*'):
            if file_path.is_file() and should_process(file_path):
                files_processed += 1
                if fix_file(file_path, dry_run):
                    files_fixed += 1

        print(f"\n📊 Résumé:")
        print(f"   Fichiers traités: {files_processed}")
        print(f"   Fichiers OK/corrigés: {files_fixed}")
    else:
        print(f"❌ Chemin invalide: {path}")

def main():
    print("=" * 70)
    print("🔧 Correcteur d'encodage par REMPLACEMENT DIRECT")
    print("=" * 70)

    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv

    if dry_run:
        print("\n⚠️  MODE DRY-RUN - Aucune modification ne sera effectuée\n")

    if len(sys.argv) > 1 and not sys.argv[1].startswith('-'):
        target = sys.argv[1]
    else:
        target = '.'

    process_path(target, dry_run)

    print("\n" + "=" * 70)
    print("✅ Traitement terminé")
    print("=" * 70)

if __name__ == '__main__':
    main()
