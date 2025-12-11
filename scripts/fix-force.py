#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Correcteur FORCÉ de double encodage UTF-8
Force la transformation : lire UTF-8 -> encoder ISO-8859-1 -> décoder UTF-8
"""

import sys
from pathlib import Path

EXTENSIONS = {'.md', '.php', '.txt', '.json', '.yml', '.yaml', '.js', '.css', '.html'}
IGNORE = {'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache'}

def should_process(path):
    """Vérifie si on doit traiter le fichier"""
    if any(ig in str(path) for ig in IGNORE):
        return False
    return path.suffix in EXTENSIONS or path.name.endswith('.blade.php')

def has_encoding_issues(text):
    """Détecte si le texte a des problèmes d'encodage"""
    # Chercher des patterns typiques de double encodage
    bad_patterns = [
        'Ã©', 'Ã¨', 'Ãª', 'Ã ', 'Ã¢', 'Ã´', 'Ã§', 'Ã‰', 'Ã€',
        'Ã¹', 'Ã»', 'Ã®', 'Ã¯', 'Ã‡', 'Å"', 'â€', 'ðŸ', '�'
    ]
    return any(pattern in text for pattern in bad_patterns)

def fix_double_encoding(file_path, dry_run=False):
    """Force la correction du double encodage"""

    try:
        # Étape 1 : Lire en UTF-8 (contient les caractères mal encodés)
        with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
            original = f.read()

        # Vérifier si besoin de correction
        if not has_encoding_issues(original):
            print(f"  ✓ {file_path.name}")
            return True

        print(f"  🔄 {file_path.name}")
        print(f"      ⚠️  Problèmes d'encodage détectés")

        if dry_run:
            print(f"      [DRY-RUN] Serait corrigé")
            return True

        # Étape 2 : Méthode 1 - Double encodage classique
        try:
            # Encoder en ISO-8859-1 pour récupérer les bytes originaux
            bytes_data = original.encode('iso-8859-1')
            # Décoder en UTF-8 pour avoir les vrais caractères
            fixed = bytes_data.decode('utf-8')

            # Vérifier que c'est mieux
            if has_encoding_issues(fixed):
                raise Exception("Correction insuffisante")

            # Écrire le fichier corrigé
            with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
                f.write(fixed)

            print(f"      ✅ Corrigé (méthode: double_encoding)")
            return True

        except Exception as e1:
            print(f"      ⚠️  Méthode 1 échouée: {e1}")

            # Étape 3 : Méthode 2 - Relecture binaire
            try:
                with open(file_path, 'rb') as f:
                    raw_bytes = f.read()

                # Essayer plusieurs décodages
                for encoding in ['utf-8', 'iso-8859-1', 'windows-1252', 'cp1252']:
                    try:
                        decoded = raw_bytes.decode(encoding)
                        if not has_encoding_issues(decoded):
                            # Semble bon, écrire
                            with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
                                f.write(decoded)
                            print(f"      ✅ Corrigé (méthode: {encoding})")
                            return True
                    except:
                        continue

                # Si rien ne marche, forcer UTF-8 avec remplacement
                fixed = raw_bytes.decode('utf-8', errors='replace')
                fixed = fixed.replace('\ufffd', '?')  # Remplacer caractères invalides

                with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
                    f.write(fixed)

                print(f"      ⚠️  Corrigé (méthode: force_utf8_replace)")
                return True

            except Exception as e2:
                print(f"      ❌ Toutes méthodes échouées: {e2}")
                return False

    except Exception as e:
        print(f"  ❌ {file_path.name}: {e}")
        return False

def process_path(target, dry_run=False):
    """Traite un fichier ou dossier"""

    path = Path(target)

    if not path.exists():
        print(f"❌ Chemin introuvable: {path}")
        return

    files_to_process = []

    if path.is_file():
        if should_process(path):
            files_to_process.append(path)
    elif path.is_dir():
        print(f"📁 Traitement du dossier: {path}\n")
        files_to_process = [f for f in sorted(path.rglob('*'))
                           if f.is_file() and should_process(f)]

    if not files_to_process:
        print("Aucun fichier à traiter")
        return

    # Statistiques
    total = len(files_to_process)
    success = 0
    errors = 0

    for file_path in files_to_process:
        if fix_double_encoding(file_path, dry_run):
            success += 1
        else:
            errors += 1

    # Résumé
    print(f"\n{'='*70}")
    print(f"📊 RÉSUMÉ")
    print(f"{'='*70}")
    print(f"  Fichiers traités : {total}")
    print(f"  Succès           : {success}")
    print(f"  Erreurs          : {errors}")
    print(f"{'='*70}\n")

def main():
    print("="*70)
    print("🔧 CORRECTEUR FORCÉ DE DOUBLE ENCODAGE UTF-8")
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

    process_path(target, dry_run)

    print("✅ Traitement terminé\n")

if __name__ == '__main__':
    main()
