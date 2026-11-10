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
- [x] **Residencias, etapa 1 (alcance chico, decidido por Marco):** solo título y link del formulario de Google. Panel `residencias.php` mínimo, rutas `GET /residencias/@lang` y `GET /residencia/@slug/@lang`, botones "Inscripción" en Pasantías y página `/residencias/[slug]` con el formulario incrustado. Código en `docs/backend/residencias/`. Subido a `backend-test` y probado en la API; la residencia de prueba ya aparece. Compila.
- [x] **Residencias, etapa 2 en pausa:** panel completo (todos los campos, traducción DeepL, fotos hasta 6, posición), probado en `backend-test`. Guardado en `docs/backend/residencias-pausado/` (ver `LEEME.md`). **Sin presupuestar**: se retoma cuando se acuerde con los dueños. Quedan subidos a `backend-test` `traducir.php` y `config_deepl.php` (con la clave de DeepL), sin uso.
- [x] **Mercado Pago (Checkout Pro):** aplicación de prueba `EspacioCeramicaTest` creada en la cuenta de Marco, con vendedor y comprador de prueba. Código en el PR #7 (`/api/mp/create-preference`, `/api/mp/webhook`, página de retorno, opción en el checkout). La creación del pago se probó contra Mercado Pago y funciona; el stock lo descuenta el webhook. Detalle y límites en `docs/mercadopago.md`.
- [ ] **Falta probar el pago completo:** hace falta el sitio en Vercel (el webhook necesita una URL pública). Antes: ejecutar `docs/backend/orders_add_payment_id.sql` en `c2821514_ec_test` y subir el `order-create` actualizado.
- [ ] Proyecto Vercel propio conectado a este fork (con `api-test`, `MP_ACCESS_TOKEN`, `ORDER_API_TOKEN`).
- [ ] Probar el panel de residencias mínimo en `backend-test` (cargar, editar, borrar).
- [ ] Mercado Pago real: credenciales de la cuenta de la artesana, después de que funcione la prueba.
- [ ] Confirmar con la artesana: nombre de la sección (Residencias o Pasantías) y si el stock va en la lista de la tienda.
- [ ] Presupuesto: el trabajo "formulario de inscripción a pasantías" ($50.000) cubre probablemente el formulario incrustado; el catálogo completo de residencias no está presupuestado.
