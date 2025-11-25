#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script robuste pour corriger TOUS les types d'encodages cassés
Utilise ftfy (fix text for you) - une bibliothèque spécialisée
"""

import sys
from pathlib import Path

EXTENSIONS = {'.php', '.js', '.css', '.md', '.txt', '.json', '.yml', '.yaml'}

IGNORE_PATTERNS = {
    'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache',
    '.vscode', '.idea', 'public/build', 'composer.lock', 'package-lock.json'
}

def should_process(file_path):
    for pattern in IGNORE_PATTERNS:
        if pattern in str(file_path):
            return False
    if file_path.suffix in EXTENSIONS or file_path.name.endswith('.blade.php'):
        return True
    return False

def fix_with_ftfy(file_path, dry_run=False):
    """Utilise ftfy pour corriger intelligemment le texte"""
    try:
        import ftfy
    except ImportError:
        print("❌ Le module 'ftfy' n'est pas installé.")
        print("   Installez-le avec: pip3 install ftfy")
        return False

    try:
        with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()

        # ftfy corrige automatiquement tous les problèmes d'encodage
        fixed_content = ftfy.fix_text(content)

        if content == fixed_content:
            print(f"✓ {file_path} - Déjà correct")
            return True

        print(f"🔄 {file_path}")
        print(f"   ⚠️  Problèmes d'encodage détectés et corrigés")

        if dry_run:
            print(f"   [DRY-RUN] Serait corrigé")
            # Montrer un échantillon
            lines_before = content.split('\n')[:3]
            lines_after = fixed_content.split('\n')[:3]
            for i, (before, after) in enumerate(zip(lines_before, lines_after)):
                if before != after:
                    print(f"   Ligne {i+1}:")
                    print(f"     Avant: {before[:80]}")
                    print(f"     Après: {after[:80]}")
            return True

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(fixed_content)

        print(f"   ✅ Corrigé avec succès")
        return True

    except Exception as e:
        print(f"❌ {file_path} - Erreur: {e}")
        return False

def fix_manual(file_path, dry_run=False):
    """Correction manuelle sans ftfy - essaie plusieurs stratégies"""
    try:
        with open(file_path, 'rb') as f:
            raw_bytes = f.read()

        # Stratégie 1: Tenter UTF-8 direct
        try:
            content = raw_bytes.decode('utf-8')
            if not any(bad in content for bad in ['Ã©', 'Ã ', 'ðŸ', 'âš']):
                print(f"✓ {file_path} - UTF-8 correct")
                return True
        except UnicodeDecodeError:
            pass

        # Stratégie 2: Windows-1252 -> UTF-8
        try:
            content = raw_bytes.decode('windows-1252')
            print(f"🔄 {file_path}")
            print(f"   Détecté: Windows-1252")

            if dry_run:
                print(f"   [DRY-RUN] Serait converti en UTF-8")
                return True

            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"   ✅ Converti en UTF-8")
            return True
        except:
            pass

        # Stratégie 3: ISO-8859-1 -> UTF-8
        try:
            content = raw_bytes.decode('iso-8859-1')
            print(f"🔄 {file_path}")
            print(f"   Détecté: ISO-8859-1")

            if dry_run:
                print(f"   [DRY-RUN] Serait converti en UTF-8")
                return True

            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"   ✅ Converti en UTF-8")
            return True
        except:
            pass

        print(f"❌ {file_path} - Impossible de détecter l'encodage")
        return False

    except Exception as e:
        print(f"❌ {file_path} - Erreur: {e}")
        return False

def process_path(path, dry_run=False, use_ftfy=True):
    path = Path(path)

    # Vérifier si ftfy est disponible
    try:
        import ftfy
        has_ftfy = True
    except ImportError:
        has_ftfy = False
        if use_ftfy:
            print("⚠️  Module 'ftfy' non disponible, utilisation de la méthode manuelle\n")

    fix_func = fix_with_ftfy if (use_ftfy and has_ftfy) else fix_manual

    if path.is_file():
        if should_process(path):
            fix_func(path, dry_run)
    elif path.is_dir():
        print(f"\n📁 Traitement du dossier: {path}\n")
        files_processed = 0
        files_fixed = 0

        for file_path in path.rglob('*'):
            if file_path.is_file() and should_process(file_path):
                files_processed += 1
                if fix_func(file_path, dry_run):
                    files_fixed += 1

        print(f"\n📊 Résumé:")
        print(f"   Fichiers traités: {files_processed}")
        print(f"   Fichiers corrigés: {files_fixed}")
    else:
        print(f"❌ Chemin invalide: {path}")

def main():
    print("=" * 70)
    print("🔧 Correcteur d'encodage ROBUSTE")
    print("=" * 70)

    dry_run = '--dry-run' in sys.argv or '-n' in sys.argv
    use_manual = '--manual' in sys.argv

    if dry_run:
        print("\n⚠️  MODE DRY-RUN - Aucune modification ne sera effectuée\n")

    if len(sys.argv) > 1 and not sys.argv[1].startswith('-'):
        target = sys.argv[1]
    else:
        target = '.'

    process_path(target, dry_run, use_ftfy=not use_manual)

    print("\n" + "=" * 70)
    print("✅ Traitement terminé")
    print("=" * 70)

if __name__ == '__main__':
    main()
