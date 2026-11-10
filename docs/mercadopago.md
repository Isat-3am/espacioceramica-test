# Pago con Mercado Pago (Checkout Pro)

Estado: código escrito y compilado. Creación del pago probada contra la cuenta de prueba de Marco. **Falta probar el pago completo** (pagar con el comprador de prueba y ver que el webhook descuente el stock), que necesita el sitio publicado en Vercel.

## Cómo funciona
1. En `/checkout` el comprador elige "Pagar con Mercado Pago" y completa sus datos.
2. El sitio llama a `POST /api/mp/create-preference`. Esa ruta **lee precio y stock de la API del backend** (no confía en el navegador), crea el pago en Mercado Pago y devuelve el link. El comprador va a la página de pago de Mercado Pago.
3. Cuando el pago queda aprobado, Mercado Pago avisa a `POST /api/mp/webhook`. Esa ruta **vuelve a consultar el pago a Mercado Pago** con el token (no confía en el aviso), y llama a `POST /order/create` del backend: descuenta el stock y guarda el pedido. Después manda los mails (comprador y tienda).
4. El comprador vuelve a `/checkout/mp?r=success|pending|failure`, que solo muestra un mensaje (y vacía el carrito si salió bien).

Transferencia bancaria y PayPal siguen igual que antes (no descuentan stock).

## Variables de entorno (en Vercel, nunca en el repo)
| Variable | Qué es | Dónde se saca |
|---|---|---|
| `NEXT_PUBLIC_API_URL` | URL de la API del backend | test: `https://api-test.espacioceramicapatagonia.com` |
| `MP_ACCESS_TOKEN` | Token de acceso de Mercado Pago (secreto) | `Dev\MERCADOPAGO_TEST.txt` (de prueba) |
| `ORDER_API_TOKEN` | Token que protege `POST /order/create` (secreto, el mismo que está en `api/order_token.php` del servidor) | se genera al subir el backend |
| `SITE_URL` | (opcional) URL pública del sitio, si no se quiere usar la del pedido | ej. `https://mi-proyecto.vercel.app` |
| `SMTP_USER`, `SMTP_PASS` | Casilla para mandar los mails | en pruebas se pueden dejar vacías: el pago se registra igual y solo falla el mail |

Para probar en la compu: crear `.env.local` en la raíz con esas variables (está ignorado por git).

## Pasos para probar el pago completo
1. Backend de test: ejecutar `docs/backend/orders_add_payment_id.sql` en phpMyAdmin (base `c2821514_ec_test`) y subir el `apis.php` con el `order-create` actualizado.
2. Vercel (cuenta de Marco): importar el fork, cargar las variables de arriba, desplegar. Usar la URL de **producción** del proyecto (`nombre.vercel.app`): las URLs de vista previa pueden estar protegidas y bloquear el aviso de Mercado Pago.
3. Comprar un producto con stock en esa URL, elegir Mercado Pago, y pagar con el **usuario comprador de prueba** (sus datos están en el panel de Mercado Pago: Developers, cuentas de prueba) y las tarjetas de prueba de Mercado Pago.
4. Verificar: stock del producto baja en 1, aparece el pedido en la tabla `orders` con `payment_id`, y la compra repetida del mismo producto sin stock se rechaza.

## Límites conocidos
- El stock se descuenta **cuando el pago se aprueba**, no al empezar a pagar. Si dos personas pagan a la vez la última unidad, la segunda queda con el pago aprobado y sin pedido: el sitio manda un mail de aviso a la tienda con el id del pago para reembolsarlo desde Mercado Pago. Para piezas únicas con poco tráfico es aceptable; si se vuelve un problema, se puede reservar el stock al empezar el pago.
- Si falla el mail, el pedido igual queda registrado.
- Mercado Pago reintenta el aviso hasta que se responde 200: `order/create` es idempotente por `payment_id` (columna UNIQUE), así que un aviso repetido no descuenta dos veces.
- Cloudflare está delante de la API: si bloquea los pedidos del servidor de Vercel (desafío anti-bots), habrá que permitirlos con una regla. Se ve en los registros de Vercel como error 403 al llamar a `/order/create`.
- Pasar a producción: crear las credenciales reales de la cuenta de la artesana, cambiar `MP_ACCESS_TOKEN`, probar con una compra real de bajo monto.
