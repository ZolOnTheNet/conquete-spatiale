#!/usr/bin/env python3
import sys
from pathlib import Path

REPLACEMENTS = {
    'Ã©': 'é', 'Ã¨': 'è', 'Ãª': 'ê', 'Ã‰': 'É',
    'Ã ': 'à', 'Ã¢': 'â', 'Ã€': 'À', 'Ã‚': 'Â',
    'Ã´': 'ô', 'Ã¹': 'ù', 'Ã»': 'û', 'Ã®': 'î', 'Ã¯': 'ï',
    'Ã§': 'ç', 'Ã‡': 'Ç',
    'Ã›': 'Û', 'Ã': 'Ï',
    'âš ï¸': '⚠️', 'ðŸ': '🎯', 'ðŸš€': '🚀',
}

IGNORE = {'vendor', 'node_modules', '.git', 'storage', 'bootstrap'}

def fix(path):
    if any(ig in str(path) for ig in IGNORE):
        return
    
    if not (path.suffix in {'.md', '.php', '.txt'} or path.name.endswith('.blade.php')):
        return
    
    try:
        content = path.read_text(encoding='utf-8', errors='replace')
        changed = False
        for bad, good in REPLACEMENTS.items():
            if bad in content:
                content = content.replace(bad, good)
                changed = True
        
        if changed:
            path.write_text(content, encoding='utf-8')
            print(f"✅ {path}")
    except Exception as e:
        print(f"❌ {path}: {e}")

target = Path(sys.argv[1] if len(sys.argv) > 1 else '.')
if target.is_file():
    fix(target)
else:
    for p in target.rglob('*'):
        if p.is_file():
            fix(p)
