# AGENTS.md

Guía para agentes que trabajen en este repositorio.

## Contexto

Portal de noticias "El Campus" (Laravel 12, PHP 8.2, Blade, Tailwind, Livewire/Jetstream).
Las interfaces, rutas, controladores, vistas y parámetros van **en español** (`noticias`,
`eventos`, `categorias`, `autores`, `usuarios`); el código de dominio no se traduce al inglés.

## Comandos

```bash
php artisan test                 # suite completa (SQLite en memoria, AI_DRIVER=fake)
php artisan test --filter=Nombre
vendor\bin\pint                 # formato (PSR-12 + reglas Laravel)
vendor\bin\pint --test          # solo verificar
```

No hay analizador estático configurado; Pint y la suite son la verificación mínima.

## Reglas de trabajo

- Un cambio = un commit atómico, con test, y con la suite en verde antes de commitear.
- Antes de cada commit: leer `git diff` completo y resumir en una línea qué se revisó.
- No modificar tests existentes salvo que el cambio de comportamiento los invalide; en ese
  caso hay que decírselo al usuario (cuál, por qué) antes de tocarlos.
- No usar `git reset --hard`, `git clean` ni descartar cambios ajenos.
- No tocar migraciones ni el esquema sin pedido explícito; los estados del dominio viven en
  las tablas `estados_noticia` y `estados_evento`.
- Las decisiones pendientes de diseño/seguridad están en `docs/PENDIENTES.md`.

## Pruebas

- `phpunit.xml` fija `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `AI_DRIVER=fake`.
- **GD no está instalada**: no usar `UploadedFile::fake()->image()`; usar
  `UploadedFile::fake()->create('x.jpg', 10, 'image/jpeg')`.
- Los tests que esperan un 500 deben fijar `config(['app.debug' => false])` para evitar el
  render lento de Ignition.
- Los seeders usan imágenes demo reales de `public/imagenes/*.svg`; no sustituirlas por rutas
  inexistentes.

## Seguridad (invariantes)

- Toda ruta de escritura exige `role:administrador,editor` o `role:administrador`; cubierto
  por `tests/Feature/MiddlewareRolRutasTest.php`.
- La ruta de texto de una imagen solo se acepta si cumple
  `^imagenes/[A-Za-z0-9_\-/]+\.(jpg|jpeg|png|webp|avif|svg)$` y existe bajo
  `public/imagenes` (`Medio::desdeRuta()` valida con `realpath`).
- `GestorMedios::limpiar()` no borra filas ni archivos con rutas que contengan `..` o que
  queden fuera de `imagenes/`.
- Nunca imprimir en vistas contenido de usuario sin `{{ }}`/`e()`.

## Entorno (Windows)

PowerShell 5.1 interpreta UTF-8 como ANSI: **no** reescribir archivos con
`Get-Content`/`Set-Content`; se corrompen los acentos. Usar las herramientas de edición.
