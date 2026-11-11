<?php

// Va en utilities/create_image.php (reemplaza al que estaba).
// Antes guardaba las fotos en JPG con calidad 100 (el maximo, muy pesado). Ahora las guarda en WebP
// con calidad 92: mismo tamano en pixeles y se ve igual, pero pesa alrededor de un 85% menos.
// Los paneles que lo usan (products, galleries, about, presentaciones, images) no cambian: arman
// $image con extension .jpg, incluyen este archivo y despues guardan $image en la base. Aca se
// cambia $image a .webp, asi la base queda con el nombre real del archivo.

$userfile = $_FILES[$data]['tmp_name'];

$file_name = basename($userfile);
$path = dirname($userfile) . "/";

$file = $_FILES[$data]['name'];
$ext = substr($file, -3);

if (strtolower($ext) == "jpg") $img = @imagecreatefromjpeg($path . $file_name);
else if (strtolower($ext) == "gif") $img = @imagecreatefromgif($path . $file_name);
else if (strtolower($ext) == "png") $img = @imagecreatefrompng($path . $file_name);
else if (strtolower($ext) == "peg") $img = @imagecreatefromjpeg($path . $file_name);

list($original_width, $original_height) = getimagesize($path . $file_name);

$ratio = ($original_width / $width);
$height = round($original_height / $ratio);
$thumb = imagecreatetruecolor($width, $height);
imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $height, $original_width, $original_height);

$exif = @exif_read_data($path . $file_name);

if (!empty($exif['Orientation'])) {
  switch ($exif['Orientation']) {
    case 3:
      $thumb = imagerotate($thumb, 180, 0);
      break;
    case 6:
      $thumb = imagerotate($thumb, -90, 0);
      break;
    case 8:
      $thumb = imagerotate($thumb, 90, 0);
      break;
  }
}

$image_webp = preg_replace('/\.jpe?g$/i', '.webp', $image);
if (function_exists('imagewebp') && imagewebp($thumb, "images/" . $image_webp, 92)) {
  $image = $image_webp; // queda guardado en la base con el nombre .webp
} else {
  // Si el servidor no pudiera crear WebP, se guarda como antes (JPG), pero con calidad 92.
  imagejpeg($thumb, "images/" . $image, 92);
}
