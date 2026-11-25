# Panel: botones "Editar" y "Eliminar" con texto (25/11/2026)

En todas las listas del panel, el ícono del papel ahora dice "Editar" y el del cesto dice "Eliminar" al lado, como botones ovalados. No cambia nada de la lógica.

- Los 8 `.php` (about, galleries, horns, images, presentaciones, products, residencias, voluntarios) van en la raíz del panel.
- `custom.css` va en `css/custom.css` (agrega la clase `.circle-btn` al final).
- `presentaciones.php` ya incluye lo de "Top con flechas"; `voluntarios.php` ya incluye "Residentes". Si el panel real tiene otras diferencias, aplicar solo el cambio de los íconos en vez de reemplazar el archivo.

Aplicado en backend-test el 25/11/2026. Para el sitio real se sube junto con el resto de la publicación. Copias de los originales en el scratchpad de la sesión, no en el repo.
