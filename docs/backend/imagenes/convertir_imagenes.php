<?php

// Convierte las fotos que ya estan cargadas (JPG) a WebP, una sola vez.
// Va en la raiz de backend/ (junto a products.php). Solo se abre con la sesion del panel iniciada:
//   https://<panel>/convertir_imagenes.php           -> solo MUESTRA lo que haria (no cambia nada)
//   https://<panel>/convertir_imagenes.php?aplicar=1 -> convierte de a 30 fotos por vez
//
// Que hace con cada foto "ec_xxxx.jpg" que figura en la base de datos:
//   1. crea "ec_xxxx.webp" en images/, con el mismo tamano en pixeles y calidad 92;
//   2. cambia el nombre en la base ("ec_xxxx.jpg" -> "ec_xxxx.webp");
//   3. NO borra el JPG original: queda guardado en images/ por si hay que volver atras.
// Se puede ejecutar varias veces: salta lo que ya esta convertido. Cuando dice "Listo", termino.
// Cuando todo este revisado y funcionando, los .jpg viejos se pueden borrar a mano de images/.

require_once('./utilities/session.php');
require_once('./db/connection.php');

header('Content-Type: text/plain; charset=utf-8');
set_time_limit(55);

const CALIDAD = 92;
const POR_VEZ = 30;

$aplicar = isset($_GET['aplicar']) && $_GET['aplicar'] === '1';
$dir = __DIR__ . '/images/';

if (!function_exists('imagewebp')) die("Este servidor no puede crear WebP.\n");

// Todas las columnas de texto de la base donde puede haber nombres de foto.
$cols = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE IN ('varchar','char','text','tinytext','mediumtext')")->fetchAll(PDO::FETCH_ASSOC);

$pendientes = []; // [tabla, columna, nombre]
foreach ($cols as $c) {
  $t = str_replace('`', '', $c['TABLE_NAME']);
  $col = str_replace('`', '', $c['COLUMN_NAME']);
  $q = $pdo->query("SELECT DISTINCT `$col` AS v FROM `$t` WHERE `$col` REGEXP '^ec_[A-Za-z0-9]+\\\\.jpe?g$'");
  foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $v) $pendientes[] = [$t, $col, $v];
}

echo $aplicar ? "MODO: APLICAR\n" : "MODO: SOLO MOSTRAR (agregar ?aplicar=1 para convertir)\n";
echo "Pendientes: " . count($pendientes) . "\n\n";

$hechas = 0;
$sin_archivo = 0;
$kb_antes = 0;
$kb_despues = 0;
foreach ($pendientes as [$t, $col, $nombre]) {
  if ($hechas >= POR_VEZ) break;
  $origen = $dir . $nombre;
  $nuevo = preg_replace('/\.jpe?g$/i', '.webp', $nombre);
  $destino = $dir . $nuevo;

  if (!is_file($origen)) {
    echo "[falta el archivo] $t.$col = $nombre\n";
    $sin_archivo++;
    continue;
  }
  echo ($aplicar ? "convierto" : "convertiria") . " $t.$col: $nombre -> $nuevo\n";
  if (!$aplicar) {
    $hechas++;
    continue;
  }

  if (!is_file($destino)) {
    $img = @imagecreatefromjpeg($origen);
    if (!$img || !imagewebp($img, $destino, CALIDAD)) {
      echo "  ERROR al convertir, se deja como estaba.\n";
      continue;
    }
    imagedestroy($img);
  }
  $kb_antes += filesize($origen) / 1024;
  $kb_despues += filesize($destino) / 1024;

  $up = $pdo->prepare("UPDATE `$t` SET `$col` = :nuevo WHERE `$col` = :viejo");
  $up->execute([':nuevo' => $nuevo, ':viejo' => $nombre]);
  $hechas++;
}

echo "\nProcesadas en esta pasada: $hechas\n";
if ($aplicar && $hechas) {
  printf("Peso de esas fotos: %.0f KB antes, %.0f KB despues\n", $kb_antes, $kb_despues);
}
if (count($pendientes) - $sin_archivo > $hechas) echo "Faltan mas: volver a abrir esta misma direccion.\n";
else echo "Listo, no quedan fotos JPG por convertir.\n";
