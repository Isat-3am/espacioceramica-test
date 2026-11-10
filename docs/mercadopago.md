# Pago con Mercado Pago (Checkout Pro)

Estado: el pago y los mails corren en el **backend propio** (Ferozo). Probado en `backend-test`: crear el pago, rechazos y mail real. Falta repetir la prueba de pago completo con el sitio apuntando al backend (ver "Pasos para probar").

**Idea central:** las claves de Mercado Pago y los mails están en el servidor de Ferozo, que controla Marco. En Vercel **no hace falta ninguna variable nueva** (solo `NEXT_PUBLIC_API_URL`, que ya existe). Así publicar el sitio no depende de cargar claves en el Vercel del dev.

## Cómo funciona
1. En `/checkout` el comprador elige "Pagar con Mercado Pago" y completa sus datos.
2. El sitio llama a `POST {API}/mp/create-preference` (con `Content-Type: text/plain` para evitar el preflight de CORS). El backend lee **precio y stock de su propia base** (solo productos, `type = 1`), crea el pago en Mercado Pago y devuelve el link. El comprador va a la página de pago de Mercado Pago.
3. Cuando el pago queda aprobado, Mercado Pago avisa a `POST {API}/mp/webhook`. El backend **vuelve a consultar el pago a Mercado Pago** con el token (no confía en el aviso), registra el pedido y descuenta el stock en una transacción, y manda los mails.
4. El comprador vuelve a `/checkout/mp?r=success|pending|failure`, que solo muestra un mensaje (y vacía el carrito si salió bien).

Transferencia bancaria sigue igual que antes (mail por `/api/send-mail`, sin descontar stock). PayPal está **oculto** (`PAYPAL_ENABLED = false` en `checkout/page.tsx`).

## Archivos
- `docs/backend/pagos/pagos.php` → va en `api/pagos.php` del servidor. Se carga desde `api/apis.php` con `require_once` dentro de `try/catch` (si falla, el resto de la API sigue funcionando).
- `docs/backend/pagos/config_pagos.example.php` → se copia como `api/config_pagos.php` **en el servidor** con los valores reales. No va al repo.
- Columna `orders.payment_id` (UNIQUE): `docs/backend/orders_add_payment_id.sql`. Evita registrar dos veces el mismo pago.

## Configuración del servidor (`api/config_pagos.php`)
| Constante | Qué es |
|---|---|
| `MP_ACCESS_TOKEN` | Token de Mercado Pago (de prueba o de producción) |
| `SITE_URL` | Dirección pública del sitio (a dónde vuelve el comprador) |
| `API_PUBLIC_URL` | Dirección pública de la API (a dónde Mercado Pago manda el aviso) |
| `MAIL_FROM` | Casilla del dominio desde la que salen los mails (ej. `pedidos@espacioceramicapatagonia.com`) |
| `NOTIFY_EMAIL` | Casilla que recibe el aviso de cada compra y las alertas. En pruebas: el mail de Marco |
| `REPLY_TO` | (opcional) a dónde van las respuestas de los compradores. Por defecto `NOTIFY_EMAIL` |

Los mails los manda el propio servidor con `mail()`: **sin contraseñas ni claves de Gmail**. Limitación del hosting: la protección (snuffleupagus) bloquea el 5to parámetro de `mail()`, por eso no se usa. El dominio ya tiene SPF y DKIM del hosting.

## Pasos para probar (backend-test)
1. Configuración de prueba en `backend-test/api/config_pagos.php` (ya subida).
2. Comprar un producto con stock en el sitio, elegir Mercado Pago y pagar con el comprador de prueba o con la tarjeta de prueba (titular `APRO`, ver documentación de Mercado Pago, tarjetas de prueba).
3. Verificar: el stock baja en 1, el pedido está en `orders` con `payment_id`, llegan el mail de confirmación (a la casilla del comprador) y el aviso (a `NOTIFY_EMAIL`).
4. Casos para probar: pago rechazado (titular `OTHE`), pendiente (`CONT`), y un aviso repetido (no debe descontar dos veces).

## Pasar a producción
1. Backup nuevo del hosting.
2. Base real: crear `orders` y `order_items` (`docs/backend/orders_schema.sql`) y agregar `payment_id` (`orders_add_payment_id.sql`).
3. Subir `pagos.php` y la línea de `require` en `api/apis.php`; crear `api/config_pagos.php` con el token **real**, `SITE_URL` y `API_PUBLIC_URL` reales, y `NOTIFY_EMAIL` de los artesanos.
4. Frontend: PR al repo original con `NEXT_PUBLIC_API_URL` real (ya configurada en el Vercel real).
5. Comprar algo real de bajo monto y verificar todo el circuito.

## Límites conocidos
- El stock se descuenta **cuando el pago se aprueba**, no al empezar a pagar. Si dos personas pagan a la vez la última unidad, la segunda queda con el pago aprobado y sin pedido: el servidor manda una alerta a `NOTIFY_EMAIL` con el id del pago para reembolsarlo desde Mercado Pago.
- Si falla el mail, el pedido igual queda registrado.
- Mercado Pago reintenta el aviso hasta recibir 200; `payment_id` UNIQUE evita descontar dos veces.
- El aviso llega al backend a través de Cloudflare. Si Cloudflare lo bloqueara (desafío anti-bots), hay que permitir `/mp/webhook`. Se nota porque no se descuenta el stock tras un pago aprobado.
- El servidor registra errores en el `error_log` del hosting.
