#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys

file_path = sys.argv[1] if len(sys.argv) > 1 else 'docs/game-design/GDD_Vaisseaux_Complet.md'

# Lire le fichier mal encodé
with open(file_path, 'rb') as f:
    content_bytes = f.read()

# Essayer de décoder et ré-encoder correctement
try:
    # Le fichier a été encodé en UTF-8, lu comme Latin-1, puis ré-encodé en UTF-8
    # On fait l'inverse: décoder UTF-8, encoder Latin-1, décoder UTF-8
    decoded = content_bytes.decode('utf-8')
    fixed = decoded.encode('latin-1').decode('utf-8')
    
    # Écrire le fichier corrigé
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(fixed)
    
    print(f"OK Fichier {file_path} corrige")
    
except Exception as e:
    print(f"Erreur: {e}")
    sys.exit(1)
