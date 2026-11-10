# Residencias: versión completa (EN PAUSA)

Decisión de Marco (10/11/2026): por ahora las residencias solo tienen **título y link del formulario de Google** (ver `../residencias/`). Todo lo de esta carpeta se hizo y se probó en `backend-test`, pero queda guardado para una **etapa siguiente**, que hay que presupuestar aparte. No se usa ni se publica.

## Qué tiene
- `residencias_schema_completo.sql`: tablas `residencias` (con todos los campos en español e inglés) y `residencia_images`.
- `residencias.php`: panel completo con:
  - todos los campos (fechas, lugar, cupos, precios, forma de pago, qué incluye, condiciones),
  - cada campo en español e inglés, con **traducción automática** (DeepL) si el inglés está vacío,
  - **un solo cargador de fotos** (hasta 6, la primera es la principal),
  - selector de **posición** (1, 2, 3...) en lugar de un número "top",
  - textos emergentes en los botones de editar y eliminar.
- `residencias-routes.php.txt`: rutas `GET /residencias/@lang` y `GET /residencia/@slug/@lang` con todos los datos y la galería.
- `traducir.php` y `config_deepl.example.php`: traducción con DeepL (la clave va solo en el servidor).

## Estado en el servidor de test
- Las tablas completas **ya existen** en `c2821514_ec_test` (la versión mínima usa la misma tabla `residencias` y solo llena el título y el link, el resto queda con valores por defecto).
- `traducir.php` y `config_deepl.php` (con la clave de DeepL) siguen subidos a `backend-test` pero **no los usa nada**: solo responden con sesión iniciada en el panel. Si se quiere dejar el servidor limpio, se pueden borrar desde el administrador de archivos de Ferozo.

## Cómo retomarlo
1. Presupuestar y definir qué campos se muestran (ver `docs/design.md`, sección 8).
2. Si la base es nueva, ejecutar `residencias_schema_completo.sql`.
3. Volver a subir `residencias.php` y `traducir.php` en lugar de la versión mínima, y reemplazar las rutas de `residencias-routes.php.txt` en `api/apis.php`.
4. Armar en el frontend la página de cada residencia con todos los datos (hoy solo muestra el formulario).
