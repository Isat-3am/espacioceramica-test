# Estado y pendientes (actualizado 2026-11-03, tras revisar la PC de Marco)

## Objetivo
Que Marco sea dueño y administrador técnico del sitio espacioceramicapatagonia.com, pueda publicar cambios sin depender del dev anterior (Mariano Arias) y sume: control de stock con descuento automático, una sección de talleres administrable, y más adelante Mercado Pago.

## Mapa de la infraestructura
| Pieza | Dónde está | Acceso actual |
|---|---|---|
| Dominio espacioceramicapatagonia.com | Registrador desconocido (probablemente el dev) | Sin acceso. Pendiente averiguar. |
| DNS | Cloudflare, cuenta del dev. 21 de 200 registros usados | Marco entra y ve los registros (capturas del 03/11). Falta confirmar si su rol permite editar. |
| Frontend (este código) | Vercel (apex `@` A 216.198.79.1; `www` y `weekend` CNAME a vercel-dns; TXT `_vercel`) | Sin acceso. Pendiente. |
| Backend, API, panel de productos, base MySQL, mail | Ferozo/DonWeb, cuenta `c2821514`, IP 200.58.111.118 | Marco tiene el panel de hosting. Claves NO guardadas acá. |
| Panel de carga de productos | `public_html/backend` en Ferozo | Los artesanos ya tienen link y clave. |
| Repo original | github.com/marianoariassoy/espacioceramica | Verificar que Marco tenga push. |

### DNS visto en Cloudflare (03/11)
- Con proxy (nube naranja), apuntan a 200.58.111.118: `api`, `api-test`, `backend`, `backend-test`. **Los registros de test ya existen.**
- Solo DNS: `@` (A a Vercel), `www` y `weekend` (CNAME a Vercel), `ftp` y `mail` (A y AAAA), `mx1`, MX, SPF (hostmar), DKIM, DMARC (`p=none`), TXT `_vercel`.
- Aviso de Cloudflare: la IP del servidor queda parcialmente expuesta porque `ftp` y `mail` comparten IP sin proxy. No urgente; el correo y el FTP no pueden ir con proxy.
- `weekend.espacioceramicapatagonia.com` apunta a otro proyecto de Vercel: preguntar qué es.

## Qué hay en la PC de Marco (`...\2026\Espacio Cerámica`)
- `Dev/Originales/backup-c2821514-...tar.gz` (127 MB): backup completo del hosting. Adentro `public_html.tar.gz` con **525 archivos del backend**: panel admin (`products.php`, `presentaciones.php`, `about.php`, `horns.php`, `galleries.php`, `voluntarios.php`, `images.php`), API (`api/index.php`, `api/apis.php`, micro-framework Flight) y helpers. **El código del backend y del panel SÍ lo tenemos.**
- `Dev/*.md`: hoja de ruta, plan general, spec de handoff, mensaje de pedido de accesos (07/09), contexto comercial.
- `Dev/Test/`: endpoint `POST /order/create` y SQL de tablas `orders` / `order_items`. Copia sin claves en `docs/backend/` de este repo.
- Archivos `.txt` con claves (Ferozo, FTP, base de test, GitHub). Son sensibles: no copiar al repo. Recomendación: pasarlas a un gestor de claves y revocar el token de GitHub que está en texto plano si alguna vez se compartió.
- Falta en este documento y se completó acá: la existencia del backup, de `backend-test` (carpeta, base `c2821514_ec_test`, FTP acotado) y del endpoint de stock ya escrito.

## Hallazgos del código
- `items` ya tiene `stock`. El panel (`products.php`) **ya permite cargarlo**, y la API lo expone. Antes no se sabía.
- Al comprar no se descuenta stock: el checkout solo manda mails.
- La API (`api/apis.php`) tiene 8 rutas, todas GET. No hay endpoints de escritura en producción.
- No existe tabla de pedidos.
- Seguridad (avisar, fuera de alcance inmediato): la tabla `user` guarda la clave del panel en texto plano; `products.php` arma el SQL concatenando texto (riesgo de inyección SQL).
- `api-test.espacioceramicapatagonia.com` **ya responde** (probado con lecturas GET, devuelve categorías).
- `next.config.ts` tiene un `remotePatterns` heredado de otro proyecto: revisar de dónde salen las imágenes.
- El dump `sql/c2760245_ec.sql` es viejo; el sitio usa más tablas. La base real está en el backup.

## Respuestas a las preguntas abiertas
1. **¿Está el backend en Ferozo?** Sí, y ya tenemos copia completa en el backup. Se puede editar sin el dev.
2. **¿Quién tiene el dominio?** Sigue sin respuesta. Dato a buscar: en el panel de Ferozo, sección Dominios, o con consulta WHOIS (el registrador figura ahí).
3. **¿Vercel del dev permite miembros?** Sin respuesta; hay que preguntar al dev qué plan tiene.
4. **Valores públicos de `NEXT_PUBLIC_API_URL`** y PayPal: se ven en Network del sitio en vivo. Casi seguro `https://api.espacioceramicapatagonia.com`.
5. **Carpeta de Marco:** revisada, ver arriba.

## ¿Qué publicamos solos y qué depende del dev?
| Pieza | ¿Solos? | Qué falta |
|---|---|---|
| Backend (API y panel) | **Sí** | Nada. Código completo, backup hecho, `backend-test` y DNS de test listos. Se publica por FTP o administrador de archivos de Ferozo. |
| Base de datos | **Sí** | Nada. phpMyAdmin en Ferozo; probar primero en `c2821514_ec_test`. |
| Mail de pedidos | Sí | Clave de aplicación de Gmail para espacioceramicapatagonia@gmail.com. |
| Frontend (Vercel) | **No** | Acceso al proyecto Vercel, o que el dev publique el PR. |
| Dominio | **No** | Saber quién lo registró. |
| DNS | Parcial | Confirmar que el rol de Marco en Cloudflare permite editar. |

Mensaje corto al dev:
> Hola Mariano. Para publicar los cambios del sitio sin molestarte cada vez, ¿podés invitar a merino.marco.c@gmail.com y espacioceramicapatagonia@gmail.com al proyecto de Vercel (y decirme en qué registrador está el dominio)? Si preferís, también podés mergear los PR que te mande. Gracias.

## Propuesta: stock automático y talleres (probar siempre en `backend-test`)

### Stock con descuento automático
- Ya hecho en test (sin probar): `docs/backend/orders_schema.sql` y `docs/backend/order-create.php.txt`. Valida y descuenta stock dentro de una transacción con bloqueo de fila, y guarda el pedido.
- **Problemas a resolver antes de publicar:**
  1. El endpoint es público y sin autenticación: cualquiera podría vaciar el stock. Exigir un token secreto en un header (guardado en Vercel y en el servidor), y que lo llame solo el servidor de Next.js, nunca el navegador.
  2. Hoy marca el pedido como `approved` siempre. Para transferencia debería quedar `pendiente` (y descontar o reservar según se decida), y pasar a `approved` al confirmarse el pago.
  3. Cambiar `send-mail` del frontend para llamar al endpoint antes de mandar el mail, y mostrar "Sin stock" si falla.
  4. El descuento solo cubre lo que pase por la web. Ventas en persona se siguen ajustando a mano desde el panel.
- Orden: probar en `api-test` con un producto de prueba, luego frontend de prueba en Vercel propio, y recién después copiar a producción.

### Sección de talleres
- Tabla `talleres` (nombre, nombre_en, descripción, descripción_en, fecha, cupos, precio_ars, precio_usd, imagen, slug, activo).
- Panel `talleres.php` calcado de `products.php` (usar consultas preparadas, no concatenar).
- Rutas GET `/talleres/@lang` y `/taller/@slug/@lang` en la API.
- Página nueva en el frontend. Primero todo en `backend-test`.

## Flujo de trabajo para no romper producción
1. **Backend:** copia de seguridad (ya hay una del 07/09; hacer otra antes de publicar) → cambiar `backend-test` y su base de test → probar con `api-test` → recién ahí repetir los mismos cambios en `backend` y en la base real. Los cambios de base (tablas nuevas) se aplican primero en test, después en real.
2. **Frontend:** rama en este fork → PR → Vercel propio de Marco con `NEXT_PUBLIC_API_URL` apuntando a `api-test` → aprobado → PR al repo original → el dueño del Vercel de producción publica.
3. **DNS:** no tocar sin confirmación de Marco.

## Pendientes
- [ ] Probar `POST /order/create` en `api-test` (no se pudo desde esta sesión: bloqueado por permisos).
- [ ] Agregar token de seguridad al endpoint.
- [ ] Crear proyecto Vercel propio conectado a este fork.
- [ ] Confirmar rol en Cloudflare y registrador del dominio.
- [ ] Enviar el mensaje al dev.
- [ ] Revisar de dónde salen las imágenes (`remotePatterns`).
- [ ] Rotar la clave de la base y del panel (texto plano) cuando Marco decida.

## Avance (03/11/2026)
- [x] Stock: `POST /order/create` con token (`X-Order-Token`) probado en `api-test`: descuenta stock, guarda el pedido y rechaza compras sin stock. Falta llamarlo desde el frontend y cargar `ORDER_API_TOKEN` en Vercel.
- [x] Talleres pasa a ser **Residencias** (el Linktree y los formularios de Google hablan de residencias; la sección de la página dice "Pasantías", a unificar con la artesana).
- [x] `docs/design.md`: criterios de diseño del sitio.

## Avance (10/11/2026)

### Hecho y probado
- **Sitio de prueba público:** https://espacioceramica-test.vercel.app (cuenta gratuita de Vercel de Marco, proyecto `espacioceramica-test`, rama `main` del fork; cada push a una rama genera una vista previa). Protección de Vercel desactivada. `NEXT_PUBLIC_API_URL` apunta a `api-test`. Conector de Vercel (MCP) conectado.
- **Pago con Mercado Pago (Checkout Pro) en el backend propio**, sin claves en Vercel: `api/pagos.php` en Ferozo crea el pago (precio y stock salen de la base), recibe el aviso de Mercado Pago, registra el pedido, descuenta stock y manda los mails. **Probado de punta a punta** con la tarjeta de prueba: pago aprobado, aviso al backend (pasó Cloudflare), stock bajó y llegaron los dos mails. Detalle, límites y pasos para producción en `docs/mercadopago.md`.
- **Mails desde el servidor** (`mail()` del hosting, desde `pedidos@espacioceramicapatagonia.com`, sin claves de Gmail). Salen: confirmación al comprador y aviso a `NOTIFY_EMAIL` (hoy el mail de Marco; en producción, el de los artesanos).
- **Ficha de producto:** fila de Stock y columna de datos fija y siempre visible (solo se mueven las fotos).
- **Residencias, etapa 1:** panel con título, reseña, link de Google Forms y posición; botones en Pasantías; página en dos columnas (título y reseña a la izquierda, formulario a la derecha; en celular, en fila). Etapa 2 (campos completos, fotos, traducción) **en pausa y sin presupuestar**: `docs/backend/residencias-pausado/`.
- **Newsletter:** página con la suscripción de Substack incrustada (fondo igual al de la página), lista de las últimas 6 publicaciones (opción A) y panel editable (título y descripción en español e inglés, link de Substack) con **traducción automática** a inglés (DeepL).
- **PayPal oculto** en el checkout (`PAYPAL_ENABLED = false`). Hoy era solo un link fijo a `paypal.me`, sin integración.
- Pagos de prueba: la compra se hace con la cuenta de prueba `EspacioCeramicaTest` (credenciales en `Dev\MERCADOPAGO_TEST.txt`, fuera del repo).

### Pendiente
- [ ] **Traducción automática en los paneles Productos, Hornos, Nosotros y Presentaciones:** aprobada por Marco, todavía no hecha (no en Voluntarios ni Galerías). Probar primero en `backend-test`.
- [ ] Probar pago rechazado (titular `OTHE`), pendiente (`CONT`) y un aviso repetido (no debe descontar dos veces).
- [ ] **Pedir a los artesanos** (videollamada de 20 min): crear la aplicación de Mercado Pago y compartir credenciales con la cuenta de Developers de Marco (de prueba y después de producción), sin usuario ni contraseña. Guía: `guia-mercadopago-espacio-ceramica.html`.
- [ ] **PayPal Business:** cuando se quiera integrar, armar con los artesanos una guía en PDF con la documentación vigente de PayPal (aplicación con client id y secret, guardados en el backend como los de Mercado Pago).
- [ ] **Publicar en el sitio real** (ver `docs/mercadopago.md`): backup nuevo; en la base real crear `orders`, `order_items` y `payment_id`, `residencias` (con `text` y `top`); la tabla `newsletter` se crea sola; subir `pagos.php`, `config_pagos.php` con el token real y las direcciones reales, paneles `residencias.php` y `newsletter.php`, enlaces del menú y rutas de `apis.php`; frontend: PR al repo original.
- [ ] **Prueba de publicar desde el repo original:** un cambio mínimo y visible (etiqueta `meta`) por PR a `marianoariassoy/espacioceramica`, para ver si el Vercel del dev publica al fusionar. Solo con confirmación de Marco. Si no, plan A/B/C con el dev (invitación a Vercel, que él publique, o mover el dominio).
- [ ] Transferencia bancaria: su mail sigue saliendo por `/api/send-mail` con el SMTP del dev. Falta pasarlo al backend (con cuidado de que no sirva para enviar spam).
- [ ] Decisiones con los artesanos: nombre de la sección (Residencias o Pasantías), si el stock va en la lista de la tienda, y si las residencias llevan título y reseña en inglés.
- [ ] Presupuesto: el trabajo de $50.000 (formulario de pasantías) cubre el formulario incrustado; la lista de publicaciones de Substack, la etapa 2 de residencias y la traducción en paneles son ampliaciones.
- [ ] Limpieza de la base de test (stock de los productos de prueba y `DELETE` de `orders` y `order_items`).
- [ ] Cerrar el PR #11 (aviso de compra configurable): quedó obsoleto con el pago en el backend.
- [ ] Siguen en manos del dev: dominio, cuenta de Cloudflare y proyecto de Vercel del sitio real.
