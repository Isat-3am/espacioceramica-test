# Espacio Cerámica: contexto para Claude Code

Sitio de Espacio Cerámica Patagonia (artesanías en cerámica, Chubut). Este repo es el **fork de pruebas** del sitio original. Idioma de trabajo: español. Dueño del trabajo: Marco (pasa a ser el responsable técnico del sitio).

Lectura obligatoria al empezar: `docs/estado-y-pendientes.md` (infraestructura, hallazgos, decisiones y próximos pasos).

Antes de crear o cambiar cualquier pantalla: leer `docs/design.md` (criterios de diseño del sitio). Todo lo nuevo debe respetarlo; si hay que romper una regla, anotar el motivo ahí.

## Stack
- Next.js 16 (App Router), React 19, Tailwind 4, next-intl (es/en), react-hook-form.
- El contenido (productos, galerías, textos, hornos, voluntariados) viene de una API PHP/MySQL externa en Ferozo/DonWeb, leída con `NEXT_PUBLIC_API_URL`. El código de esa API y del panel de carga de productos NO está en este repo.
- Pagos: transferencia y PayPal (`NEXT_PUBLIC_PAYPAL_CLIENT_ID`). Sin Mercado Pago todavía.
- Mails de pedido: `src/app/api/send-mail/route.ts` (nodemailer, variables `SMTP_USER` y `SMTP_PASS`).
- Producción: frontend en Vercel; DNS en Cloudflare; backend y mail en Ferozo/DonWeb.

## Reglas
- Este repo es público: **nunca** commitear claves, contraseñas, tokens ni usuarios de paneles. Las variables secretas van en Vercel o en `.env.local` (ignorado por git).
- No tocar producción, DNS, Vercel ni el repo original `marianoariassoy/espacioceramica` sin confirmación explícita de Marco.
- Trabajar en ramas y abrir PR en este fork. Los cambios aprobados se llevan al repo original con un PR aparte.
- Marco prefiere el código entregado completo en un solo bloque copiable, y explicaciones cortas y simples.
