# Fotos en WebP (más livianas, sin perder definición)

Las fotos se guardaban en JPG con calidad 100 (el máximo), por eso pesaban en promedio 240 KB cada una
(la tienda de test: 13,6 MB en total). Ahora el panel las guarda en **WebP, mismo tamaño en píxeles y
calidad 92**: se ven igual (se comparó al 100 % contra la original) y pesan alrededor de un 85 % menos.
No se achica ninguna foto.

## Archivos (van en el servidor, en `backend/` o `backend-test/`)
| Archivo | Dónde | Qué hace |
|---|---|---|
| `create_image.php` | `utilities/` (reemplaza al actual) | Al subir una foto en Productos, Galerías, Nosotros, Presentaciones e Imágenes, la guarda en WebP. |
| `create_image_2.php` | `utilities/` (reemplaza al actual) | Lo mismo para la segunda foto de Nosotros. |
| `convertir_imagenes.php` | raíz (junto a `products.php`) | Convierte **una vez** las fotos que ya están cargadas. |

Los paneles no se tocan: el cambio de nombre `.jpg` → `.webp` se hace dentro de `create_image.php`.

## Para publicarlo en el backend real
1. Hacer backup de `utilities/create_image.php` y `create_image_2.php`, y de la base de datos.
2. Subir los tres archivos.
3. Subir una foto de prueba en un producto y ver que se guarda en `images/` como `.webp` y se ve en la tienda.
4. Abrir `convertir_imagenes.php` (con la sesión del panel iniciada): sin parámetros solo **muestra** lo que haría.
5. Abrir `convertir_imagenes.php?aplicar=1` las veces que haga falta (convierte de a 30 fotos) hasta que diga
   "Listo, no quedan fotos JPG por convertir".
6. Revisar la tienda. Los `.jpg` viejos quedan en `images/` como respaldo; se pueden borrar cuando todo esté bien.
7. Quitar `convertir_imagenes.php` del servidor cuando termine.

## Si algo falla
- Para volver atrás con una foto: en la base, cambiar el nombre de `ec_xxxx.webp` a `ec_xxxx.jpg` (el JPG sigue en `images/`).
- Para volver atrás con las subidas nuevas: restaurar los `create_image*.php` del backup.
