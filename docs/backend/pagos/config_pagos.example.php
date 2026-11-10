<?php

// EJEMPLO. Copiar como api/config_pagos.php en el servidor (backend/ o backend-test/) y completar.
// El archivo con los valores reales NO se sube al repo.

// Token de acceso de Mercado Pago (de prueba o de produccion).
define('MP_ACCESS_TOKEN', 'PEGAR-ACA-EL-TOKEN');

// Direccion publica del sitio (a donde vuelve el comprador despues de pagar).
define('SITE_URL', 'https://www.espacioceramicapatagonia.com');

// Direccion publica de esta API (a donde Mercado Pago manda el aviso del pago).
define('API_PUBLIC_URL', 'https://api.espacioceramicapatagonia.com');

// Casilla del dominio desde la que salen los mails (el servidor del hosting los manda, sin claves).
define('MAIL_FROM', 'pedidos@espacioceramicapatagonia.com');

// Casilla que recibe el aviso de cada compra (y las alertas).
define('NOTIFY_EMAIL', 'espacioceramicapatagonia@gmail.com');

// (Opcional) A donde van las respuestas del comprador. Si no se define, usa NOTIFY_EMAIL.
// define('REPLY_TO', 'espacioceramicapatagonia@gmail.com');
