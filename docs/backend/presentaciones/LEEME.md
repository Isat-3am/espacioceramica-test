# Panel de Presentaciones (presentaciones.php)

Cambios sobre el archivo original del panel (probado en `backend-test`, 24/11/2026):

- **Top con flechas:** el campo "Top" ya no se escribe a mano. Es un número con flechas, de 0 a N-1 (N = cantidad de presentaciones; si se agregan más, el máximo sube solo). Muestra la posición actual en la lista.
- **Orden sin repetidos:** al guardar, todas las presentaciones se renumeran 0 a N-1 (el número más alto va primero), así que nunca quedan dos con el mismo número. Antes estaban todas en 0 y el orden dependía de la base de datos.
- **Etiqueta:** "Pasantias" pasa a "Experiencias Formativas" (desplegable y lista).

Importante: el sitio usa la posición en la lista (`/intros/{idioma}`, ordenada por `top` de mayor a menor): 0 = Inicio, 1 = Espacio Cerámica, 2 = Residencias, 3 = Hornos, 4 = Experiencias Formativas. No conviene mover las existentes. El orden de desempate es por `id`, igual al que ya se veía.

Publicación en el sitio real: subir este archivo como `presentaciones.php` (guardar antes una copia del actual). No toca la estructura de la base.
Volver atrás: subir de nuevo la copia del archivo anterior (los números `top` guardados no molestan).
