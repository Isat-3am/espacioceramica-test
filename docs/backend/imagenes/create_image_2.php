<?php

// Va en utilities/create_image_2.php (reemplaza al que estaba). Lo usa solo el panel "Nosotros"
// para la segunda foto. Mismo criterio que create_image.php: guarda WebP con calidad 92 y cambia
// $image_2 a .webp para que la base quede con el nombre real del archivo.

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

$image_2_webp = preg_replace('/\.jpe?g$/i', '.webp', $image_2);
if (function_exists('imagewebp') && imagewebp($thumb, "./images/" . $image_2_webp, 92)) {
  $image_2 = $image_2_webp;
} else {
  imagejpeg($thumb, "./images/" . $image_2, 92);
}
