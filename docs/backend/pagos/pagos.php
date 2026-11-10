<?php

// Pagos con Mercado Pago (Checkout Pro) y mails de pedidos, todo en el backend.
// Va en api/pagos.php y se carga desde api/apis.php (ver README.md de esta carpeta).
//
// La configuracion privada (token de Mercado Pago, direcciones) esta en api/config_pagos.php,
// que NO va en el repo (ver config_pagos.example.php).
//
// Rutas:
//   POST /mp/create-preference  crea el pago y devuelve el link de Mercado Pago
//   POST /mp/webhook            aviso de Mercado Pago: registra el pedido, descuenta stock y manda los mails

if (file_exists(__DIR__ . '/config_pagos.php')) {
  require_once __DIR__ . '/config_pagos.php';
}

function pagos_e($value)
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function pagos_ars($value)
{
  return '$' . number_format((float)$value, 0, ',', '.');
}

function pagos_config_ok()
{
  foreach (['MP_ACCESS_TOKEN', 'SITE_URL', 'API_PUBLIC_URL', 'MAIL_FROM', 'NOTIFY_EMAIL'] as $c) {
    if (!defined($c) || constant($c) === '') return false;
  }
  return true;
}

function pagos_body()
{
  $body = json_decode(Flight::request()->getBody(), true);
  return is_array($body) ? $body : [];
}

// Llamada a la API de Mercado Pago. Devuelve [codigo_http, respuesta_decodificada].
function pagos_mp($method, $path, $payload = null)
{
  $ch = curl_init('https://api.mercadopago.com' . $path);
  curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . MP_ACCESS_TOKEN, 'Content-Type: application/json'],
  ]);
  if ($payload !== null) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
  }
  $res = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$code, $res === false ? null : json_decode($res, true)];
}

// Mail HTML desde una casilla del dominio, mandado por el servidor del hosting. Sin claves.
function pagos_mail($to, $subject, $html, $reply_to = '')
{
  $to = trim($to);
  if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
  $from = MAIL_FROM;
  $headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: =?UTF-8?B?' . base64_encode('Espacio Cerámica') . '?= <' . $from . '>',
  ];
  if ($reply_to !== '' && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
    $headers[] = 'Reply-To: ' . $reply_to;
  }
  $subject = str_replace(["\r", "\n"], ' ', $subject);
  // Sin el 5to parametro de mail() (-f): la proteccion del hosting (snuffleupagus) lo bloquea.
  return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, implode("\r\n", $headers));
}

function pagos_html_pedido($titulo, $buyer, $lines, $total, $order_id, $nota = '')
{
  $filas = '';
  foreach ($lines as $l) {
    $filas .= '<tr><td>' . pagos_e($l['title']) . '</td><td>' . intval($l['quantity']) . '</td><td>'
      . pagos_ars($l['price']) . '</td><td>' . pagos_ars($l['price'] * $l['quantity']) . '</td></tr>';
  }
  $nota_html = $nota !== '' ? '<p><b>' . pagos_e($nota) . '</b></p>' : '';
  return '<div style="background-color:#f6f6f7;padding:20px;color:#000000;font-family:Arial,sans-serif;">'
    . $nota_html
    . '<h3>' . pagos_e($titulo) . '</h3>'
    . '<h2>Número de pedido: #' . intval($order_id) . '</h2><hr />'
    . '<h3>Detalles de la compra:</h3>'
    . '<p><b>Forma de pago:</b> Mercado Pago <br />'
    . '<b>Nombre:</b> ' . pagos_e($buyer['name'] ?? '') . ' <br />'
    . '<b>Email:</b> ' . pagos_e($buyer['email'] ?? '') . ' <br />'
    . '<b>Teléfono:</b> ' . pagos_e($buyer['phone'] ?? '') . ' <br />'
    . '<b>Dirección:</b> ' . pagos_e($buyer['address'] ?? '') . ' <br />'
    . '<b>Ciudad:</b> ' . pagos_e($buyer['city'] ?? '') . ' <br />'
    . '<b>Código postal:</b> ' . pagos_e($buyer['zip'] ?? '') . '</p><hr />'
    . '<h3>Resumen del pedido:</h3>'
    . '<table border="1" cellpadding="6" cellspacing="0"><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead><tbody>'
    . $filas . '</tbody></table>'
    . '<h3>Total: ' . pagos_ars($total) . '</h3><hr />'
    . '<p>Recibimos tu pago por Mercado Pago. No tenés que hacer nada más: nos vamos a comunicar con vos para coordinar la entrega.</p><hr />'
    . '<p>Espacio Cerámica <br />@espacioceramica <br />espacioceramicapatagonia@gmail.com <br />Chubut, Patagonia Argentina.</p></div>';
}

// Lista [{id, quantity}, ...] -> [id => cantidad], o null si hay algo invalido.
function pagos_items($items)
{
  if (!is_array($items) || count($items) < 1 || count($items) > 20) return null;
  $map = [];
  foreach ($items as $i) {
    $id = intval($i['id'] ?? 0);
    $q = intval($i['quantity'] ?? 0);
    if ($id <= 0 || $q < 1 || $q > 20) return null;
    $map[$id] = ($map[$id] ?? 0) + $q;
    if ($map[$id] > 20) return null;
  }
  return $map;
}

// Registra el pedido y descuenta el stock en una transaccion (con bloqueo de fila para evitar sobreventa).
// Idempotente por pago_id. Devuelve:
//   ['ok' => true, 'duplicate' => bool, 'order_id' => n, 'total' => n, 'lines' => [...]]
//   ['ok' => false, 'error' => texto, 'retry' => bool]   (retry = error del servidor: conviene reintentar)
function pagos_crear_pedido($db, $buyer, $map, $pago_id)
{
  $nombre = sanitize($buyer['name'] ?? '');
  $email = sanitize($buyer['email'] ?? '');
  if ($nombre === '' || $email === '' || empty($map)) {
    return ['ok' => false, 'error' => 'Faltan datos del pedido', 'retry' => false];
  }
  $telefono = sanitize($buyer['phone'] ?? '');
  $direccion = sanitize(implode(', ', array_filter([$buyer['address'] ?? '', $buyer['city'] ?? '', $buyer['zip'] ?? ''])));

  $statement = $db->prepare('SELECT id, total FROM orders WHERE payment_id = ?');
  $statement->execute([$pago_id]);
  $existente = $statement->fetch();
  if ($existente) {
    return ['ok' => true, 'duplicate' => true, 'order_id' => intval($existente['id']), 'total' => $existente['total'], 'lines' => []];
  }

  try {
    $db->beginTransaction();
    $total = 0;
    $lines = [];
    foreach ($map as $item_id => $cantidad) {
      $statement = $db->prepare('SELECT id, title, price_ars, stock FROM items WHERE id = ? AND type = 1 FOR UPDATE');
      $statement->execute([$item_id]);
      $item = $statement->fetch();
      if (!$item) throw new Exception('El producto ' . $item_id . ' no existe');
      if ($item['stock'] < $cantidad) throw new Exception('Sin stock suficiente para ' . $item['title']);
      $total += $item['price_ars'] * $cantidad;
      $lines[] = ['id' => intval($item['id']), 'title' => $item['title'], 'quantity' => $cantidad, 'price' => $item['price_ars']];
    }
    foreach ($lines as $l) {
      $db->prepare('UPDATE items SET stock = stock - ? WHERE id = ?')->execute([$l['quantity'], $l['id']]);
    }
    $db->prepare(
      'INSERT INTO orders (comprador_nombre, comprador_email, comprador_telefono, comprador_direccion, total, estado, medio_pago, payment_id)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$nombre, $email, $telefono, $direccion, $total, 'approved', 'mercadopago', $pago_id]);
    $order_id = $db->lastInsertId();
    $ins = $db->prepare('INSERT INTO order_items (order_id, item_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
    foreach ($lines as $l) {
      $ins->execute([$order_id, $l['id'], $l['quantity'], $l['price']]);
    }
    $db->commit();
    return ['ok' => true, 'duplicate' => false, 'order_id' => intval($order_id), 'total' => $total, 'lines' => $lines];
  } catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    // 23000 = pago_id repetido (dos avisos casi simultaneos): el primero ya lo registro.
    if ($e->getCode() == '23000') {
      return ['ok' => true, 'duplicate' => true, 'order_id' => 0, 'total' => 0, 'lines' => []];
    }
    error_log('pagos_crear_pedido: ' . $e->getMessage());
    return ['ok' => false, 'error' => 'Error interno', 'retry' => true];
  } catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    return ['ok' => false, 'error' => $e->getMessage(), 'retry' => false];
  }
}

// Crea el pago en Mercado Pago. Los precios y el stock salen de la base, nunca del navegador.
// Se llama desde el sitio con Content-Type text/plain (evita el preflight de CORS) y el JSON en el cuerpo.
Flight::route('POST /mp/create-preference', function () {
  if (!pagos_config_ok()) {
    error_log('pagos: falta config_pagos.php');
    Flight::json(['ok' => false, 'error' => 'Mercado Pago no está configurado.'], 500);
    return;
  }
  $body = pagos_body();
  $buyer = is_array($body['buyer'] ?? null) ? $body['buyer'] : [];
  $name = trim((string)($buyer['name'] ?? ''));
  $email = trim((string)($buyer['email'] ?? ''));
  if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Flight::json(['ok' => false, 'error' => 'Faltan los datos del comprador.'], 400);
    return;
  }
  $map = pagos_items($body['items'] ?? null);
  if ($map === null) {
    Flight::json(['ok' => false, 'error' => 'El carrito está vacío o tiene un producto inválido.'], 400);
    return;
  }

  $db = Flight::db();
  $lines = [];
  foreach ($map as $id => $q) {
    $statement = $db->prepare('SELECT id, title, price_ars, stock FROM items WHERE id = ? AND type = 1');
    $statement->execute([$id]);
    $item = $statement->fetch();
    if (!$item) {
      Flight::json(['ok' => false, 'error' => 'Un producto del carrito ya no está disponible.'], 400);
      return;
    }
    if ($item['stock'] < $q) {
      Flight::json(['ok' => false, 'error' => 'Sin stock suficiente: ' . $item['title']], 400);
      return;
    }
    if (!is_numeric($item['price_ars']) || $item['price_ars'] <= 0) {
      Flight::json(['ok' => false, 'error' => 'Producto sin precio: ' . $item['title']], 400);
      return;
    }
    $lines[] = ['id' => intval($item['id']), 'title' => $item['title'], 'quantity' => $q, 'price' => (float)$item['price_ars']];
  }

  $lang = (($body['locale'] ?? '') === 'en') ? 'en' : 'es';
  $site = rtrim(SITE_URL, '/');
  $preference = [
    'items' => array_map(fn($l) => [
      'id' => (string)$l['id'],
      'title' => $l['title'],
      'quantity' => $l['quantity'],
      'unit_price' => $l['price'],
      'currency_id' => 'ARS',
    ], $lines),
    'payer' => ['name' => $name, 'email' => $email],
    'back_urls' => [
      'success' => $site . '/' . $lang . '/checkout/mp?r=success',
      'pending' => $site . '/' . $lang . '/checkout/mp?r=pending',
      'failure' => $site . '/' . $lang . '/checkout/mp?r=failure',
    ],
    'auto_return' => 'approved',
    'notification_url' => rtrim(API_PUBLIC_URL, '/') . '/mp/webhook',
    'external_reference' => bin2hex(random_bytes(16)),
    'statement_descriptor' => 'ESPACIO CERAMICA',
    // El aviso de Mercado Pago usa esto para registrar el pedido.
    'metadata' => [
      'buyer' => [
        'name' => $name,
        'email' => $email,
        'phone' => (string)($buyer['phone'] ?? ''),
        'address' => (string)($buyer['address'] ?? ''),
        'city' => (string)($buyer['city'] ?? ''),
        'zip' => (string)($buyer['zip'] ?? ''),
      ],
      'items' => array_map(fn($l) => ['id' => $l['id'], 'quantity' => $l['quantity']], $lines),
    ],
  ];

  [$code, $res] = pagos_mp('POST', '/checkout/preferences', $preference);
  if ($code < 200 || $code >= 300 || empty($res['init_point'])) {
    error_log('pagos: Mercado Pago rechazo la preferencia (' . $code . ') ' . json_encode($res));
    Flight::json(['ok' => false, 'error' => 'No se pudo iniciar el pago.'], 502);
    return;
  }
  Flight::json(['ok' => true, 'url' => $res['init_point']]);
});

// Aviso de Mercado Pago. No se confia en el contenido: se consulta el pago con nuestro token.
// Responde 200 cuando esta listo (o no hay nada que hacer) y 5xx para que Mercado Pago reintente.
Flight::route('POST /mp/webhook', function () {
  if (!pagos_config_ok()) {
    error_log('pagos: falta config_pagos.php (webhook)');
    Flight::json(['ok' => false], 500);
    return;
  }
  $body = pagos_body();
  $type = $body['type'] ?? ($_GET['type'] ?? ($_GET['topic'] ?? ''));
  // PHP cambia el punto por guion bajo en los nombres de la URL: data.id llega como data_id.
  $pago_id = (string)($body['data']['id'] ?? ($_GET['data_id'] ?? ($_GET['id'] ?? '')));
  if ($type !== 'payment' || !preg_match('/^\d+$/', $pago_id)) {
    Flight::json(['ok' => true]);
    return;
  }

  [$code, $payment] = pagos_mp('GET', '/v1/payments/' . $pago_id);
  if ($code == 404) { // aviso de prueba o pago de otra cuenta
    Flight::json(['ok' => true]);
    return;
  }
  if ($code < 200 || $code >= 300 || !is_array($payment)) {
    error_log('pagos: no se pudo leer el pago ' . $pago_id . ' (' . $code . ')');
    Flight::json(['ok' => false], 502);
    return;
  }
  if (($payment['status'] ?? '') !== 'approved') {
    Flight::json(['ok' => true]);
    return;
  }

  $buyer = $payment['metadata']['buyer'] ?? null;
  $map = pagos_items($payment['metadata']['items'] ?? null);
  if (!is_array($buyer) || empty($buyer['email']) || $map === null) {
    error_log('pagos: pago aprobado sin datos del pedido ' . $pago_id);
    Flight::json(['ok' => true]);
    return;
  }

  $pedido = pagos_crear_pedido(Flight::db(), $buyer, $map, $pago_id);

  if (!$pedido['ok']) {
    if ($pedido['retry']) {
      Flight::json(['ok' => false], 502);
      return;
    }
    // Pago aprobado pero no se pudo registrar (por ejemplo, se acabo el stock): avisar a los duenos.
    $texto = 'Pago aprobado en Mercado Pago (id ' . $pago_id . ') de ' . ($buyer['name'] ?? '') . ' (' . ($buyer['email'] ?? '')
      . ') por ' . pagos_ars($payment['transaction_amount'] ?? 0) . ', pero no se pudo registrar el pedido: ' . $pedido['error']
      . '. Revisá el pago en Mercado Pago: puede que haya que reembolsarlo.';
    error_log('pagos: ' . $texto);
    pagos_mail(NOTIFY_EMAIL, 'ATENCIÓN: pago aprobado sin pedido registrado', '<p>' . pagos_e($texto) . '</p>');
    Flight::json(['ok' => true]);
    return;
  }

  if ($pedido['duplicate']) { // ya estaba registrado: no se repite nada
    Flight::json(['ok' => true]);
    return;
  }

  $total = $pedido['total'];
  $nota = 'Pago Mercado Pago id ' . $pago_id . '.';
  if (abs((float)$total - (float)($payment['transaction_amount'] ?? 0)) > 1) {
    $nota = 'Revisar: el monto pagado en Mercado Pago (' . pagos_ars($payment['transaction_amount'] ?? 0)
      . ') no coincide con el total del pedido (' . pagos_ars($total) . '). Pago id ' . $pago_id . '.';
  }
  // Si falla el mail, el pedido igual quedo registrado: no se pide reintento.
  try {
    $reply = defined('REPLY_TO') ? REPLY_TO : NOTIFY_EMAIL;
    pagos_mail($buyer['email'], 'Confirmación de compra', pagos_html_pedido('¡Gracias por tu compra, ' . ($buyer['name'] ?? '') . '!', $buyer, $pedido['lines'], $total, $pedido['order_id']), $reply);
    pagos_mail(NOTIFY_EMAIL, 'Nueva compra de ' . ($buyer['name'] ?? ''), pagos_html_pedido('Nueva compra de ' . ($buyer['name'] ?? ''), $buyer, $pedido['lines'], $total, $pedido['order_id'], $nota), $buyer['email']);
  } catch (Throwable $e) {
    error_log('pagos: no se pudo mandar el mail del pedido: ' . $e->getMessage());
  }
  Flight::json(['ok' => true]);
});
