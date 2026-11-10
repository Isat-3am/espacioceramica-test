# Traducción automática español → inglés en el panel

Botón **Traducir** y traducción automática al salir de cada casilla en español, con DeepL (plan gratuito, 1.000.000 de caracteres por mes).

## Dónde está activa
| Panel | Casillas |
|---|---|
| Productos (`products`) | título, texto, dimensiones, comentarios |
| Hornos (`horns`) | título, texto |
| Nosotros (`about`) | texto |
| Presentaciones (`presentaciones`) | título, texto |
| Newsletter (`newsletter`) | título, descripción (tiene su propio guion, ya incluido en el panel) |

**No se usa en Voluntarios** (la casilla "en inglés" guarda una cuenta de Instagram) **ni en Galerías** (sin textos).

## Reglas
- Solo traduce si la casilla en inglés está **vacía**: nunca pisa lo escrito.
- Al salir de una casilla en español se traduce sola. Al lado de cada casilla en inglés hay un botón **Traducir**, que solo funciona con la casilla vacía. Para volver a traducir, se borra el inglés.
- Siempre conviene revisar el resultado antes de guardar (la traducción automática puede equivocarse con términos de cerámica y nombres propios).
- En Productos, el enlace de cada producto sale del título en inglés: si se edita un producto con el inglés ya cargado, no se traduce nada.

## Archivos (van en el servidor, en `backend/` o `backend-test/`)
| Archivo | Dónde | Qué es |
|---|---|---|
| `traducir.php` | raíz de `backend/` | Recibe un texto y lo traduce con DeepL. Solo responde con la sesión del panel iniciada. |
| `config_deepl.php` | raíz de `backend/` | **Clave de DeepL.** Se crea a mano en el servidor a partir de `config_deepl.example.php`. No va al repo. |
| `includes/traducir_auto.php` | `backend/includes/` | Guion que detecta los pares español/inglés y agrega los botones. |
| `includes/footer.php` | `backend/includes/` | Se le agrega al final el contenido de `footer-snippet.txt`. |

## Para publicarlo en el backend real
1. Hacer backup de `includes/footer.php`.
2. Subir `traducir.php`, `includes/traducir_auto.php` y crear `config_deepl.php` con la clave de DeepL.
3. Agregar el contenido de `footer-snippet.txt` al final de `includes/footer.php`.
4. Probar en cada panel: escribir en español, salir de la casilla y ver el inglés.

## Si algo falla
- "Falta configurar la clave de traducción": falta `config_deepl.php` o está vacío.
- "La clave de traducción no es válida": revisar la clave en la cuenta de DeepL.
- "Se agotó la cuota gratuita de traducción de este mes": el plan gratuito se acabó; se renueva el mes siguiente.
- Para sacar la función: quitar el bloque del final de `footer.php`. Los paneles quedan como antes.
