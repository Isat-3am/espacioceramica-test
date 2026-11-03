<?php

// Panel de residencias. Va en la raiz de backend/ (junto a products.php).
// Mismo estilo que voluntarios.php / products.php, pero con consultas preparadas
// y sin eval() sobre $_POST.

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');

$file_main = "residencias";
$sec = 8;

function h($value)
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Campos con version en español y en ingles: [columna, etiqueta, tipo, filas]
$campos = [
  ['title', 'Título', 'input', 1],
  ['text', 'Descripción corta', 'textarea', 4],
  ['duracion', 'Duración', 'input', 1],
  ['llegada', 'Llegada anticipada (opcional)', 'input', 1],
  ['lugar', 'Lugar', 'input', 1],
  ['cupos', 'Cupos', 'input', 1],
  ['pago', 'Forma de pago', 'textarea', 3],
  ['incluye', 'Qué incluye', 'textarea', 3],
  ['no_incluye', 'Qué NO incluye', 'textarea', 3],
  ['condiciones', 'Condiciones', 'textarea', 4],
];

function campo($col, $label, $tipo, $filas, $results, $lang)
{
  $name = $lang == 'en' ? $col . '_en' : $col;
  $value = h($results[$name] ?? '');
  $sufijo = $lang == 'en' ? ' (English)' : ' (Spanish)';
  echo '<div class="col col-12 col-md-6 mb-3' . ($lang == 'es' ? ' pe-md-3' : '') . '">';
  echo '<label class="form-label">' . h($label) . $sufijo . '</label>';
  if ($tipo == 'textarea') {
    echo '<textarea name="' . $name . '" cols="30" rows="' . $filas . '" class="form-control bg-transparent text-white mb-2">' . $value . '</textarea>';
  } else {
    echo '<input type="text" name="' . $name . '" class="form-control bg-transparent text-white mb-2" value="' . $value . '" />';
  }
  echo '</div>';
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function guardar_imagen($data)
{
  // create_image.php lee $data, $image y $width del ambito actual.
  global $width;
  $image = "ec_" . GenerateString(12) . ".jpg";
  $width = 1080;
  require("./utilities/create_image.php");
  return $image;
}

if (isset($_POST['form'])) {
  $action = $_POST['action'] ?? '';
  $id = intval($_POST['id'] ?? 0);

  $valores = [];
  foreach ($campos as $c) {
    $valores[$c[0]] = trim($_POST[$c[0]] ?? '');
    $valores[$c[0] . '_en'] = trim($_POST[$c[0] . '_en'] ?? '');
  }
  $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
  $fecha_inicio = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) ? $fecha_inicio : null;
  $fecha_fin = trim($_POST['fecha_fin'] ?? '');
  $fecha_fin = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin) ? $fecha_fin : null;
  $valores['fecha_inicio'] = $fecha_inicio;
  $valores['fecha_fin'] = $fecha_fin;
  $valores['price_ars'] = max(0, floatval($_POST['price_ars'] ?? 0));
  $valores['price_usd'] = max(0, floatval($_POST['price_usd'] ?? 0));
  $form_url = trim($_POST['form_url'] ?? '');
  // Solo se aceptan links http/https (el link se muestra en la web publica).
  $valores['form_url'] = preg_match('#^https?://#i', $form_url) ? $form_url : '';
  $valores['top'] = intval($_POST['top'] ?? 0);
  $valores['active'] = isset($_POST['active']) ? 1 : 0;

  if (!empty($_FILES['userfile1']['name'])) {
    $valores['image'] = guardar_imagen('userfile1');
  }

  if ($valores['title'] === '') {
    $error = "El título es obligatorio.";
  } else {
    try {
      // Los nombres de columna salen de $valores (armado arriba), nunca del navegador.
      $columnas = array_keys($valores);
      if ($action == 'Agregar') {
        $sql = 'INSERT INTO residencias (' . implode(', ', array_map(fn($c) => "`$c`", $columnas)) . ')
                VALUES (' . implode(', ', array_fill(0, count($columnas), '?')) . ')';
        $statement = $pdo->prepare($sql);
        $statement->execute(array_values($valores));
        $id = $pdo->lastInsertId();
        // El id va en el slug para que nunca se repita.
        $statement = $pdo->prepare('UPDATE residencias SET slug = ? WHERE id = ?');
        $statement->execute([getSlug($valores['title_en'] ?: $valores['title']) . '-' . $id, $id]);
      } else {
        $sql = 'UPDATE residencias SET ' . implode(', ', array_map(fn($c) => "`$c` = ?", $columnas)) . ' WHERE id = ?';
        $statement = $pdo->prepare($sql);
        $statement->execute(array_merge(array_values($valores), [$id]));
      }

      // Fotos de la galeria que se marcaron para borrar
      if (!empty($_POST['del_img']) && is_array($_POST['del_img'])) {
        $statement = $pdo->prepare('DELETE FROM residencia_images WHERE id = ? AND residencia_id = ?');
        foreach ($_POST['del_img'] as $del) {
          $statement->execute([intval($del), $id]);
        }
      }

      // Fotos nuevas de la galeria (se pueden elegir varias a la vez)
      if (!empty($_FILES['gallery']['name'][0])) {
        $statement = $pdo->prepare('INSERT INTO residencia_images (residencia_id, image, top) VALUES (?, ?, ?)');
        foreach ($_FILES['gallery']['name'] as $i => $nombre) {
          if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) continue;
          $_FILES['gallery_one'] = ['name' => $nombre, 'tmp_name' => $_FILES['gallery']['tmp_name'][$i]];
          $statement->execute([$id, guardar_imagen('gallery_one'), 0]);
        }
      }

      header("Location:$file_main");
      die();
    } catch (PDOException $e) {
      $error = "Error en la consulta: " . $e->getMessage();
    }
  }
  $results = $_POST;
  $action = $action ?: 'Agregar';
  $title_sec = $action == 'Agregar' ? "Agregar Residencia" : "Modificar Residencia";
  $id_select = $id;
  $_GET['mod'] = 1;
  $gallery = [];
} else if (isset($_POST['delete'])) {
  $id = intval($_POST['id'] ?? 0);
  $pdo->prepare('DELETE FROM residencia_images WHERE residencia_id = ?')->execute([$id]);
  $pdo->prepare('DELETE FROM residencias WHERE id = ?')->execute([$id]);
  header("Location:$file_main");
  die();
} else if (isset($_GET['add'])) {
  $action = 'Agregar';
  $title_sec = "Agregar Residencia";
  $id_select = 0;
  $results = ['active' => 1];
  $gallery = [];
} else if (isset($_GET['edit'])) {
  $action = 'Modificar';
  $title_sec = "Modificar Residencia";
  $id_select = intval($_GET['i'] ?? 0);
  $statement = $pdo->prepare('SELECT * FROM residencias WHERE id = ?');
  $statement->execute([$id_select]);
  $results = $statement->fetch();
  $statement = $pdo->prepare('SELECT * FROM residencia_images WHERE residencia_id = ? ORDER BY id');
  $statement->execute([$id_select]);
  $gallery = $statement->fetchAll();
} else {
  $title_sec = "Residencias";
  $statement = $pdo->prepare('SELECT * FROM residencias ORDER BY top DESC, fecha_inicio DESC');
  $statement->execute();
  $results = $statement->fetchAll();
  $total = count($results);
}
require("includes/head.php");

?>

<body class="bg-dark">
  <main>
    <?php
    require("includes/header.php");
    if (isset($_GET['mod'])) {
    ?>
      <section class="p-3 p-lg-5 p-md-4 text-white">
        <div class="row mb-5">
          <h2><strong><?php echo h($title_sec) ?></strong> </h2>
        </div>
        <?php if (!empty($error)) echo '<div class="alert alert-danger">' . h($error) . '</div>'; ?>
        <form method="POST" name="form" enctype="multipart/form-data">
          <div class="row mb-5">

            <?php
            foreach ($campos as $c) {
              campo($c[0], $c[1], $c[2], $c[3], $results, 'es');
              campo($c[0], $c[1], $c[2], $c[3], $results, 'en');
            }
            ?>

            <div class="col col-12 col-md-3 mb-3 pe-md-3">
              <label class="form-label">Fecha de inicio</label>
              <input type="date" name="fecha_inicio" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['fecha_inicio'] ?? '') ?>" />
            </div>
            <div class="col col-12 col-md-3 mb-3 pe-md-3">
              <label class="form-label">Fecha de fin</label>
              <input type="date" name="fecha_fin" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['fecha_fin'] ?? '') ?>" />
            </div>
            <div class="col col-12 col-md-3 mb-3 pe-md-3">
              <label class="form-label">Precio ARS</label>
              <input type="number" step="0.01" min="0" name="price_ars" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['price_ars'] ?? 0) ?>" />
            </div>
            <div class="col col-12 col-md-3 mb-3">
              <label class="form-label">Precio USD</label>
              <input type="number" step="0.01" min="0" name="price_usd" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['price_usd'] ?? 0) ?>" />
            </div>

            <div class="col col-12 mb-3">
              <label class="form-label">Link del formulario de inscripción</label>
              <input type="text" name="form_url" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['form_url'] ?? '') ?>" />
              <div class="row text-secondary">Link de Google Forms. Incluir https://</div>
            </div>

            <div class="col col-12 col-md-6 pe-md-3 mb-3">
              <label class="form-label">Foto principal</label>
              <input name="userfile1" class="form-control bg-transparent text-white mb-2" type="file" accept="image/*">
              <div class="row text-secondary">Recomendado: 1080 px de ancho.</div>
              <?php if (!empty($results['image'])) echo '<a href="./images/' . h($results['image']) . '" target="_blank" class="opacity"><img src="./images/' . h($results['image']) . '" class="mt-3 img-circle-large mb-3" /></a>'; ?>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Fotos de la galería (se pueden elegir varias)</label>
              <input name="gallery[]" class="form-control bg-transparent text-white mb-2" type="file" accept="image/*" multiple>
              <?php
              foreach ($gallery as $g) {
                echo '<div class="d-inline-block me-3 mt-2 text-center">';
                echo '<img src="./images/' . h($g['image']) . '" class="img-circle-large mb-1" /><br>';
                echo '<label class="small"><input type="checkbox" name="del_img[]" value="' . intval($g['id']) . '"> Borrar</label>';
                echo '</div>';
              }
              ?>
            </div>

            <div class="col col-12 col-md-6 pe-md-3 mb-3">
              <label class="form-label">Top</label>
              <input type="number" name="top" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['top'] ?? 0) ?>" />
              <div class="row text-secondary">Número de prioridad: el más alto aparece primero.</div>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Visible en la web</label>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php if (!empty($results['active'])) echo 'checked' ?>>
                <label class="form-check-label" for="active">Activa</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col col-12">
              <button type="button" class="rounded-2 btn btn-primary mybtn" onClick="javascript:document.form.submit();"><?php echo h($action) ?></button>
              <button type="button" class="rounded-2 btn btn-secondary mybtn mx-2" onClick="javascript:history.back();">Cancelar</button>
            </div>
          </div>
          <input type="hidden" name="form" value="1">
          <input type="hidden" name="action" value="<?php echo h($action) ?>">
          <input type="hidden" name="id" value="<?php echo h($id_select) ?>">
        </form>
      </section>

    <?php } else { ?>

      <section class="p-3 p-lg-5 p-md-4 text-white">
        <div class="row mb-4">
          <div class="col-md-10 mb-2">
            <h2><strong><?php echo h($title_sec) ?></strong> (<?php echo $total ?>)</h2>
          </div>
          <div class="col-md-2">
            <a href="?mod=1&add=1" class="rounded-2 btn btn-primary mybtn w-100">Agregar</a>
          </div>
        </div>

        <?php if ($total) { ?>
          <table class="table table-dark table-hover">
            <thead>
              <tr class="text-secondary">
                <th scope="col">Residencia</th>
                <th scope="col">Inicio</th>
                <th scope="col">Fin</th>
                <th scope="col">Precio ARS</th>
                <th scope="col">Estado</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row) { ?>
                <tr>
                  <td valign="middle" class="text-primary"><strong><?php echo h($row['title']) ?></strong></td>
                  <td valign="middle"><?php echo h($row['fecha_inicio'] ?: '---') ?></td>
                  <td valign="middle"><?php echo h($row['fecha_fin'] ?: '---') ?></td>
                  <td valign="middle"><?php echo h($row['price_ars']) ?></td>
                  <td valign="middle"><?php echo $row['active'] ? 'Activa' : '<span class="text-secondary">Oculta</span>' ?></td>
                  <td valign="middle" align="right">
                    <a href="?mod=1&edit=1&i=<?php echo intval($row['id']) ?>" class="circle bg-transparent"><i class="bi bi-file-earmark-text"></i></a>
                    <a href="#" class="circle bg-transparent delete" data-id="<?php echo intval($row['id']) ?>"><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </section>
    <?php
    }
    require("includes/footer.php");
    ?>
  </main>
</body>

</html>
