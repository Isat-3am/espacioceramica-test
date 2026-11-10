<?php

// Traduce un texto de español a ingles con DeepL (plan gratuito).
// Va en la raiz de backend/, junto a residencias.php.
//
// La clave de DeepL NO va en el repo. Se guarda solo en el servidor, en un archivo
// config_deepl.php (ver config_deepl.example.php) que se crea a mano en Ferozo.

require_once('./utilities/session.php'); // sin sesion iniciada en el panel, redirige al login

header('Content-Type: application/json; charset=utf-8');

function responder($data, $status = 200)
{
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  die();
}

// Solo POST, y solo desde el panel (el header lo agrega el JavaScript del panel).
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'fetch') {
  responder(['ok' => false, 'error' => 'Pedido no valido.'], 400);
}

if (file_exists(__DIR__ . '/config_deepl.php')) {
  require_once(__DIR__ . '/config_deepl.php');
}
if (!defined('DEEPL_API_KEY') || DEEPL_API_KEY === '') {
  responder(['ok' => false, 'error' => 'Falta configurar la clave de traducción.'], 500);
}

$text = trim($_POST['text'] ?? '');
if ($text === '') {
  responder(['ok' => false, 'error' => 'No hay texto para traducir.'], 400);
}
if (mb_strlen($text) > 5000) {
  responder(['ok' => false, 'error' => 'El texto es demasiado largo (máximo 5000 caracteres).'], 400);
}

$ch = curl_init('https://api-free.deepl.com/v2/translate');
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 20,
  CURLOPT_HTTPHEADER => ['Authorization: DeepL-Auth-Key ' . DEEPL_API_KEY],
  CURLOPT_POSTFIELDS => http_build_query([
    'text' => $text,
    'source_lang' => 'ES',
    'target_lang' => 'EN-US',
    'preserve_formatting' => '1',
  ]),
]);
$respuesta = curl_exec($ch);
$codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($respuesta === false || $codigo !== 200) {
  // 456 = se acabo la cuota gratuita del mes; 403 = clave incorrecta.
  $motivo = $codigo === 456 ? 'Se agotó la cuota gratuita de traducción de este mes.' : ($codigo === 403 ? 'La clave de traducción no es válida.' : 'No se pudo traducir.');
  responder(['ok' => false, 'error' => $motivo], 502);
}

$data = json_decode($respuesta, true);
$traducido = $data['translations'][0]['text'] ?? '';
if ($traducido === '') {
  responder(['ok' => false, 'error' => 'No se pudo traducir.'], 502);
}

responder(['ok' => true, 'text' => $traducido]);
