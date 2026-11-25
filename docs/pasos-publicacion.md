# Pasos para publicar en el sitio real

Todo lo de abajo se hace recién cuando Marco avise que es el momento y los artesanos hayan revisado el test y dado el OK. Cada paso que toca el sitio real se hace solo con el OK de Marco en ese momento. Nunca se tocan producción, DNS, Vercel ni el repo original sin su confirmación.

## A. Antes de tocar nada real
1. **Revisión de los artesanos:** ven el test, piden cambios si hay y dan el OK.
2. **Aviso de Marco:** avisa que es el momento.
3. **Último backup:** Marco lo hace desde el panel de Ferozo y lo descarga a su PC (Ferozo guarda solo uno a la vez).
4. **Comparación final:** se compara la tienda real contra el test (productos, stock, fotos). Si hubo cambios, se actualiza el test.
5. **Acceso FTP nuevo:** Marco lo crea en Ferozo, limitado a la carpeta del backend real (`public_html/backend`), para no usar el acceso general.

## B. Backend real
6. **Base de datos:** agregar las tablas y columnas nuevas: pedidos (`orders`, `order_items`, `payment_id`), inscripciones (`residencias` con texto, orden y tipo) y newsletter. Scripts en `docs/backend/`.
7. **Archivos:** subir pagos (`pagos.php`), los paneles nuevos y modificados (Productos, Hornos, Nosotros, Galerías, Imágenes, Presentaciones, Residencias y Exp. Formativas, Residentes: selector de posición 1°, 2°, 3°, botones Editar/Eliminar con texto, flechas grandes en números), `utilities/orden.php`, `includes/header.php` (menú), `includes/head.php` y `css/custom.css`, la API (`api/apis.php`), el pasaje de fotos a WebP (`create_image*.php`, `convertir_imagenes.php`) y la traducción automática. Los archivos están en `docs/backend/` (ver los LEEME). Antes se guarda copia de cada archivo original que se reemplaza. Si el panel real tiene diferencias, aplicar solo los cambios en vez de reemplazar el archivo entero.
8. **Credenciales de Mercado Pago:** las carga Marco directamente en `config_pagos.php` del servidor, junto con las URLs reales y el mail de avisos de compra de los artesanos. No se pasan por chat ni van al repo.
9. **Botón "Web" del panel:** en `header.php` apunta al sitio de test; cambiarlo por `https://espacioceramicapatagonia.com`.
10. **Datos:** renombrar la presentación "Experiencias Formativas" (id 76 en la base real), cargar la descripción real de Residencias y las inscripciones reales desde el panel, y arreglar las fotos vacías de los productos `yunomi-small` y `porcelain-yunomi`.

## C. Sitio (frontend)
11. **Código:** traer lo último de `upstream/main` al fork, sacar la marca de prueba invisible (`publicacion-test`) y abrir el PR al repo original para que Marco lo apruebe.
12. **Vercel real:** revisar las variables de entorno y el mail de transferencia bancaria (hoy usa `/api/send-mail` con el servidor de mail del desarrollador anterior).

## D. Verificación y vuelta atrás
13. **Compra real chica con Mercado Pago:** se devuelve después. Verificar que el dinero llegue a la cuenta de los artesanos, el pedido guardado, el stock descontado y los mails. Probar también PayPal y transferencia.
14. **Vuelta atrás:** con el backup y las copias de los archivos originales se puede volver al estado anterior.

## Pendientes que no frenan la publicación
- Cobro de PayPal solo en dólares: requiere la integración con PayPal Business y una guía en PDF (o la opción de la cuenta de bloquear pagos en monedas que no tiene).
- Substack: apagar los "compromisos de pago" (pledges) y probar "Connect Stripe" para Argentina.
- Limpieza opcional: variables sin uso en Vercel (`MP_ACCESS_TOKEN`, `ORDER_API_TOKEN`, `ORDER_NOTIFY_EMAIL`), archivos sobrantes en `Dev\Originales\backup-noviembre`, revocar el token viejo de GitHub.

Estado y decisiones: ver `docs/estado-y-pendientes.md`.
