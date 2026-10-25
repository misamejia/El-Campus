#!/usr/bin/env python3
import subprocess
import os

base = "63b5e65"
files = [
    "resources/views/auth/login.blade.php",
    "resources/views/auth/register.blade.php",
    "resources/views/autores/index.blade.php",
    "resources/views/categorias/index.blade.php",
    "resources/views/components/tarjeta-estadistica.blade.php",
    "resources/views/components/tarjeta-evento.blade.php",
    "resources/views/components/titular.blade.php",
    "resources/views/errors/403.blade.php",
    "resources/views/eventos/index.blade.php",
    "resources/views/layouts/guest.blade.php",
    "resources/views/noticias/index.blade.php",
    "resources/views/public/evento.blade.php",
    "resources/views/public/noticia.blade.php",
    "resources/views/public/perfil.blade.php",
    "resources/views/usuarios/index.blade.php",
]

def apply_colors(content):
    content = content.replace('text-oro-600', 'text-oro-900')
    content = content.replace('text-oro-700', 'text-oro-900')
    content = content.replace('hover:text-oro-500', 'hover:text-oro-700')
    content = content.replace('text-neutral-400', 'text-neutral-600')
    content = content.replace('text-neutral-500', 'text-neutral-600')
    return content

differs = []
for f in files:
    try:
        result = subprocess.run(['git', 'show', f'{base}:{f}'], 
                              cwd=r'C:\Users\misae\OneDrive\Escritorio\el_campus',
                              stdout=subprocess.PIPE)
        base_decoded = result.stdout.decode('utf-8')
    except Exception as e:
        print(f'ERR base {f}: {e}')
        continue
    
    expected = apply_colors(base_decoded)
    try:
        with open(os.path.join(r'C:\Users\misae\OneDrive\Escritorio\el_campus', f), 'r', encoding='utf-8') as fh:
            current = fh.read()
    except Exception as e:
        print(f'ERR current {f}: {e}')
        continue
    
    if expected != current:
        differs.append(f)
        print(f'DIFF: {f}')
    else:
        print(f'OK: {f}')

print('---')
print(f'Files differing: {len(differs)}')
for d in differs:
    print(d)
