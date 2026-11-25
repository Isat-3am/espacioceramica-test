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
