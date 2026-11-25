# Panel: "Voluntarios" pasa a "Residentes" (25/11/2026)

Solo cambia el texto que se ve en el panel (menú y títulos). El archivo sigue llamándose `voluntarios.php`, la dirección `/voluntarios` y la tabla de la base no cambian, así no se rompe nada.

- `header.php` -> subir a `includes/header.php` (menú: "Residentes").
- `voluntarios.php` -> subir a la raíz del panel (títulos: Residentes, Agregar Residente, Modificar Residente).

Aplicado en backend-test el 25/11/2026. Para el sitio real se sube junto con el resto de la publicación. Antes de subir, comparar con el `header.php` del panel real (puede tener otros cambios).
