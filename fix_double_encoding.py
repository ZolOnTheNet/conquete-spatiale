#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script pour corriger le DOUBLE encodage UTF-8
(quand un fichier UTF-8 a été relu comme ISO-8859-1 puis réencodé)
"""

import sys
from pathlib import Path

# Extensions de fichiers à traiter
EXTENSIONS = {'.php', '.js', '.css', '.md', '.txt', '.json', '.yml', '.yaml'}

IGNORE_PATTERNS = {
    'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache',
    '.vscode', '.idea', 'public/build', 'composer.lock', 'package-lock.json'
}

def should_process(file_path):
    """Vérifie si le fichier doit être traité"""
    for pattern in IGNORE_PATTERNS:
        if pattern in str(file_path):
            return False

    if file_path.suffix in EXTENSIONS:
        return True

    if file_path.name.endswith('.blade.php'):
        return True

    return False

def fix_double_encoding(file_path, dry_run=False):
    """Corrige le double encodage UTF-8 -> ISO-8859-1 -> UTF-8"""

    try:
        # Lire le fichier en UTF-8 (qui contient des caractères incorrects)
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        # Détecter si le fichier a des problèmes de double encodage
        bad_chars = ['Ã©', 'Ã ', 'Ã¨', 'Ãª', 'Ã´', 'Ã§', 'Å"', 'Ã‰', 'ðŸ', 'Ã€', 'Ã‚', 'âš']

        has_double_encoding = any(bad_char in content for bad_char in bad_chars)

        if not has_double_encoding:
            print(f"✓ {file_path} - Pas de double encodage détecté")
            return True

        print(f"🔄 {file_path}")
        print(f"   ⚠️  Double encodage détecté")

        if dry_run:
            print(f"   [DRY-RUN] Serait corrigé")
            # Montrer un exemple de correction
            for bad_char in bad_chars[:3]:
                if bad_char in content:
                    print(f"   Exemple: '{bad_char}' sera corrigé")
            return True

        try:
            # La solution : encoder en ISO-8859-1 (pour retrouver les bytes originaux)
            # puis décoder en UTF-8 (pour interpréter correctement)
            bytes_content = content.encode('iso-8859-1')
            fixed_content = bytes_content.decode('utf-8')

            # Réécrire le fichier
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(fixed_content)

            print(f"   ✅ Corrigé")

            # Afficher quelques exemples de corrections
            if 'Ã©' in content:
                print(f"   Ã© → é")
            if 'Ã ' in content:
                print(f"   Ã  → à")
            if 'ðŸ' in content:
                print(f"   ðŸ → 🎯 (emoji)")

            return True

        except (UnicodeDecodeError, UnicodeEncodeError) as e:
            print(f"   ❌ Erreur de conversion: {e}")
            return False

    except Exception as e:
        print(f"❌ {file_path} - Erreur: {e}")
        return False

def process_path(path, dry_run=False):
    """Traite un fichier ou un dossier"""
    path = Path(path)

    if path.is_file():
        if should_process(path):
            fix_double_encoding(path, dry_run)
    elif path.is_dir():
        print(f"\n📁 Traitement du dossier: {path}\n")
        files_processed = 0
        files_fixed = 0

        for file_path in path.rglob('*'):
            if file_path.is_file() and should_process(file_path):
                files_processed += 1
                if fix_double_encoding(file_path, dry_run):
                    files_fixed += 1

        print(f"\n📊 Résumé:")
        print(f"   Fichiers traités: {files_processed}")
        print(f"   Fichiers corrigés: {files_fixed}")
    else:
        print(f"❌ Chemin invalide: {path}")

def main():
    print("=" * 70)
    print("🔧 Correcteur de DOUBLE ENCODAGE UTF-8")
    print("   (UTF-8 → ISO-8859-1 → UTF-8)")
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
