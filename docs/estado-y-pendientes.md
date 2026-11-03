# Estado y pendientes (traspaso desde el hilo del proyecto, 2026-11-03)

## Objetivo
Que Marco sea dueño y administrador técnico del sitio espacioceramicapatagonia.com, pueda publicar cambios sin depender del dev anterior (Mariano Arias) y sume: control de stock con descuento automático, una sección de talleres administrable, y más adelante Mercado Pago.

## Mapa de la infraestructura (verificado por capturas y mails de Marco)
| Pieza | Dónde está | Acceso actual |
|---|---|---|
| Dominio espacioceramicapatagonia.com | Registrador desconocido (probablemente comprado por el dev) | Sin acceso. Pendiente averiguar. |
| DNS | Cloudflare, cuenta del dev (Hola@marianoarias.soy's Account) | Marco es miembro desde el 28/09/2026. Rol exacto sin verificar. |
| Frontend (este código) | Vercel (apex `@` A 216.198.79.1; `www` y `weekend` CNAME a vercel-dns; TXT `_vercel`) | Sin acceso. Pendiente. |
| Backend, API, panel de productos, base MySQL, mail | Ferozo/DonWeb, IP 200.58.111.118 (`api`, `backend`, `api-test`, `backend-test`, `mail`, `ftp`) | Marco tiene usuario y clave del panel de hosting (ver mail "Fwd: consulta programador" del 07/09/2026 en su Gmail). Clave NO guardada acá. |
| Panel de carga de productos | Parte del backend | Los artesanos ya tienen link y clave. |
| Repo del sitio original | github.com/marianoariassoy/espacioceramica | Marco es colaborador con push (verificar). |
| Cloudflare Workers / Pages | No se usa para el sitio (0 proyectos en la cuenta del dev) | n/a |

Entorno de pruebas ya armado por Marco en Ferozo: `backend-test` con su carpeta, base de datos y subdominios. En Cloudflare ya existen los registros `api-test` y `backend-test` (con proxy).

## Hallazgos del código
- La tabla `items` (dump en `sql/c2760245_ec.sql`) ya tiene campo `stock`. El sitio lo usa: muestra "Sin stock" y limita el carrito. Falta confirmar si el panel permite cargarlo.
- Al comprar no se descuenta stock: el checkout solo manda mails (`/api/send-mail`) y no escribe en la base. Hay que construirlo en el backend.
- El mail de pedido envía confirmación al comprador y aviso a espacioceramicapatagonia@gmail.com (fijo en el código). El remitente hoy sale de una casilla Ferozo del dev.
- `next.config.ts` tiene un `remotePatterns` heredado de otro proyecto (backend.ligadecapitanes.com.ar): revisar de dónde salen realmente las imágenes.
- El dump SQL solo trae 5 tablas (authors, categories, images, items, user), pero el sitio consulta más endpoints (intros, galleries, horns, volunteers, about): el dump está incompleto o desactualizado.

## Decisiones de Marco
- Marco pasa a ser el dev del sitio.
- Remitente y aviso de compras: usar espacioceramicapatagonia@gmail.com con clave de aplicación de Gmail (requiere verificación en dos pasos). No usar la casilla Ferozo del dev ni el mail personal de Marco.
- Entorno de prueba del frontend: proyecto Vercel propio de Marco conectado a este fork, URL `*.vercel.app`, apuntando a `api-test`. Sin SMTP real en la prueba. PayPal en sandbox.
- Pedir lo mínimo posible al dev. Mensaje propuesto al dev: (1) código del backend y del panel; (2) acceso a Ferozo y a Vercel, o que él publique los cambios cuando estén listos; accesos para merino.marco.c@gmail.com y espacioceramicapatagonia@gmail.com.
- Para talleres: si no se consigue el código del backend, construir un backend propio chico con panel solo para talleres.

## Preguntas abiertas
1. ¿Están en el hosting de Ferozo los archivos del backend (carpetas `api` o `backend`) y phpMyAdmin? Si sí, se puede descargar el código y editarlo, y ya no hace falta pedírselo al dev.
2. ¿Quién tiene registrado el dominio?
3. ¿El plan de Vercel del dev permite sumar miembros? (Si es gratuito, probablemente no.)
4. Valores públicos reales de `NEXT_PUBLIC_API_URL` y del client id de PayPal: se ven en la pestaña Network del sitio en vivo.
5. ¿Qué hay en `C:\Users\Usuario\Documents\2026\Espacio Cerámica` (la PC de Marco)? Revisar por información faltante.

## ¿Se puede publicar sin el dev?
- **Backend:** sí, probablemente, con el panel de Ferozo (administrador de archivos o FTP y phpMyAdmin), si el código está ahí. Probar primero en `backend-test`.
- **Frontend:** hoy no, porque el proyecto de Vercel es del dev. Opciones: (a) que el dev invite a Marco; (b) que el dev publique un PR; (c) montar un proyecto Vercel propio y apuntar el dominio ahí desde Cloudflare (requiere poder editar DNS y verificar el dominio; es un cambio en producción, hacerlo coordinado y con confirmación).
- **Dominio:** depende de quién lo registró.

## Próximos pasos sugeridos
1. Revisar la carpeta de Marco y completar las preguntas abiertas.
2. Entrar al panel de Ferozo y listar qué hay en las carpetas de `api`, `backend`, `api-test` y `backend-test`.
3. Crear el proyecto Vercel de prueba conectado a este fork (variables: `NEXT_PUBLIC_API_URL` apuntando a api-test, `NEXT_PUBLIC_PAYPAL_CLIENT_ID` sandbox).
4. Diseñar el control de stock y la sección de talleres (campos del taller por definir: nombre, fecha, cupos, precio, fotos, descripción, es/en).
5. Recién con eso, decidir con el dev: acceso a Vercel o publicación por PR.
