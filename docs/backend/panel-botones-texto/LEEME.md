# Panel: botones "Editar" y "Eliminar" con texto (25/11/2026)

En todas las listas del panel, el ícono del papel ahora dice "Editar" y el del cesto dice "Eliminar" al lado, como botones ovalados. No cambia nada de la lógica.

- Los 8 `.php` (about, galleries, horns, images, presentaciones, products, residencias, voluntarios) van en la raíz del panel.
- `custom.css` va en `css/custom.css` (agrega la clase `.circle-btn` al final).
- `presentaciones.php` ya incluye lo de "Top con flechas"; `voluntarios.php` ya incluye "Residentes". Si el panel real tiene otras diferencias, aplicar solo el cambio de los íconos en vez de reemplazar el archivo.

Aplicado en backend-test el 25/11/2026. Para el sitio real se sube junto con el resto de la publicación. Copias de los originales en el scratchpad de la sesión, no en el repo.

## Residencias y Experiencias Formativas: orden por tipo (25/11/2026)
- El menú del panel dice "Residencias y Exp. Formativas" ("Residencias" arriba y "y Exp. Formativas" abajo, sin tercer renglón; además el título "Espacio Cerámica Backend" es más chico y los botones del menú más compactos, ver `custom.css`); `docs/backend/panel-residentes/header.php` ya lo incluye.
- Los títulos pasan a "Agregar / Modificar Residencia o Experiencia Formativa" y la lista a "Residencias y Experiencias Formativas".
- Cada tipo tiene su propio orden: el selector de posición cambia al elegir el tipo (solo ofrece las posiciones de ese tipo), al guardar solo se renumeran las del mismo tipo y, si una inscripción cambia de tipo, el tipo que deja se renumera sin hueco. La lista muestra la posición dentro de su tipo.
- Probado en backend-test con una simulación dentro de una transacción que se revirtió (3 experiencias de prueba, reordenar, volver atrás); las 2 residencias quedaron igual.

## Campos de número: flechas grandes (25/11/2026)
Se sacaron los botones − y + (quedaban mal ubicados). Ahora las flechas nativas de subir y bajar de los campos de número están siempre visibles y son más grandes (ocupan todo el alto de la casilla): ver el final de `custom.css`. `footer.php` vuelve a ser el original (sin el script de botones). Funciona en Chrome y Edge; en el celular los campos de número usan el teclado numérico.

## Orden por posición (1°, 2°, 3°...) en lugar de "Top" (25/11/2026)
- En Productos, Hornos, Residentes, Nosotros, Galerías, Imágenes y Presentaciones el campo "Top" (número libre) pasó a un selector **"Posición en la web"**: 1° es la primera, las demás se acomodan solas. La lista muestra la posición ("1°", "2°"...).
- Antes el número más alto aparecía primero y muchos productos compartían el 0 (orden sin criterio). Ahora se numera seguido y no hay empates.
- `orden.php` va en `utilities/orden.php` (funciones compartidas) y los 7 paneles van en la raíz del panel.
- Al guardar, se numera el grupo con la misma consulta que usa la API (`ORDER BY top DESC`), así el orden que ya se ve en la web **no cambia** la primera vez. Probado en backend-test con una simulación que se revirtió: guardar un registro en su propia posición deja el mismo orden en todos los grupos (69 productos, 23 residentes, 17 y 8 imágenes de galería, etc.) y mover a la posición 1 funciona.
- Cada grupo tiene su propio orden: productos, hornos, residentes, Nosotros, cada sección de galería, y las fotos de cada producto/horno.
- Presentaciones sigue guardando el mismo número de siempre (el sitio las toma por posición); solo cambia cómo se elige.
- Para el sitio real: antes de subir, comparar cada archivo con el del panel real y aplicar solo estos cambios si hay otras diferencias.

`head.php` va en `includes/head.php`: la hoja de estilos se pide con `?v=20261125b` (custom.css?v=...) para que el navegador y Cloudflare no sigan mostrando la versión vieja (el CSS se guardaba 7 días en caché). Cada vez que se cambie `custom.css`, cambiar ese número.
