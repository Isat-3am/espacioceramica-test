<?php

// Panel de talleres. Va en la raiz de backend/ (junto a products.php).
// Mismo estilo que voluntarios.php / products.php, pero con consultas preparadas
// y sin eval() sobre $_POST.

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');

$file_main = "talleres";
$sec = 8;

function h($value)
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['form'])) {
  $action = $_POST['action'] ?? '';
  $id = intval($_POST['id'] ?? 0);

  $title = trim($_POST['title'] ?? '');
  $title_en = trim($_POST['title_en'] ?? '');
  $text = trim($_POST['text'] ?? '');
  $text_en = trim($_POST['text_en'] ?? '');
  $fecha = trim($_POST['fecha'] ?? '');
  $fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : null;
  $horario = trim($_POST['horario'] ?? '');
  $cupos = max(0, intval($_POST['cupos'] ?? 0));
  $price_ars = max(0, floatval($_POST['price_ars'] ?? 0));
  $price_usd = max(0, floatval($_POST['price_usd'] ?? 0));
  $form_url = trim($_POST['form_url'] ?? '');
  // Solo se aceptan links http/https (el link se muestra en la web publica).
  if ($form_url !== '' && !preg_match('#^https?://#i', $form_url)) $form_url = '';
  $top = intval($_POST['top'] ?? 0);
  $active = isset($_POST['active']) ? 1 : 0;

  $image = "";
  $data = "userfile1";
  if (!empty($_FILES[$data]['name'])) {
    $image = "ec_" . GenerateString(12) . ".jpg";
    $width = 1080;
    require_once("./utilities/create_image.php");
  }

  if ($title === '') {
    $error = "El titulo es obligatorio.";
  } else {
    try {
      if ($action == 'Agregar') {
        $sql = 'INSERT INTO talleres (title, title_en, text, text_en, fecha, horario, cupos, price_ars, price_usd, image, form_url, top, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $pdo->prepare($sql);
        $statement->execute([$title, $title_en, $text, $text_en, $fecha, $horario, $cupos, $price_ars, $price_usd, $image, $form_url, $top, $active]);
        $new_id = $pdo->lastInsertId();
        // El id va en el slug para que nunca se repita.
        $statement = $pdo->prepare('UPDATE talleres SET slug = ? WHERE id = ?');
        $statement->execute([getSlug($title_en ?: $title) . '-' . $new_id, $new_id]);
      } else {
        $sql = 'UPDATE talleres SET title = ?, title_en = ?, text = ?, text_en = ?, fecha = ?, horario = ?, cupos = ?, price_ars = ?, price_usd = ?, form_url = ?, top = ?, active = ?';
        $params = [$title, $title_en, $text, $text_en, $fecha, $horario, $cupos, $price_ars, $price_usd, $form_url, $top, $active];
        if ($image !== "") {
          $sql .= ', image = ?';
          $params[] = $image;
        }
        $sql .= ' WHERE id = ?';
        $params[] = $id;
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
      }
      header("Location:$file_main");
      die();
    } catch (PDOException $e) {
      $error = "Error en la consulta: " . $e->getMessage();
    }
  }
}

if (isset($_POST['delete'])) {
  $statement = $pdo->prepare('DELETE FROM talleres WHERE id = ?');
  $statement->execute([intval($_POST['id'] ?? 0)]);
  header("Location:$file_main");
  die();
}

if (isset($_GET['add'])) {
  $action = 'Agregar';
  $title_sec = "Agregar Taller";
  $id_select = 0;
  $results = ['active' => 1];
} else if (isset($_GET['edit'])) {
  $action = 'Modificar';
  $title_sec = "Modificar Taller";
  $id_select = intval($_GET['i'] ?? 0);
  $statement = $pdo->prepare('SELECT * FROM talleres WHERE id = ?');
  $statement->execute([$id_select]);
  $results = $statement->fetch();
} else {
  $title_sec = "Talleres";
  $statement = $pdo->prepare('SELECT * FROM talleres ORDER BY top DESC, fecha DESC');
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

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Titulo (Spanish)</label>
              <input type="text" name="title" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['title'] ?? '') ?>" />
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Titulo (English)</label>
              <input type="text" name="title_en" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['title_en'] ?? '') ?>" />
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Detalles (Spanish)</label>
              <textarea name="text" cols="30" rows="4" class="form-control bg-transparent text-white mb-2"><?php echo h($results['text'] ?? '') ?></textarea>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Detalles (English)</label>
              <textarea name="text_en" cols="30" rows="4" class="form-control bg-transparent text-white mb-2"><?php echo h($results['text_en'] ?? '') ?></textarea>
            </div>

            <div class="col col-12 col-md-4 mb-3 pe-md-3">
              <label class="form-label">Fecha</label>
              <input type="date" name="fecha" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['fecha'] ?? '') ?>" />
            </div>
            <div class="col col-12 col-md-4 mb-3 pe-md-3">
              <label class="form-label">Horario</label>
              <input type="text" name="horario" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['horario'] ?? '') ?>" />
              <div class="row text-secondary">Ej: 10 a 13 hs</div>
            </div>
            <div class="col col-12 col-md-4 mb-3">
              <label class="form-label">Cupos</label>
              <input type="number" name="cupos" min="0" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['cupos'] ?? 0) ?>" />
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Precio ARS</label>
              <input type="number" step="0.01" min="0" name="price_ars" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['price_ars'] ?? 0) ?>" />
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Precio USD</label>
              <input type="number" step="0.01" min="0" name="price_usd" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['price_usd'] ?? 0) ?>" />
            </div>

            <div class="col col-12 mb-3">
              <label class="form-label">Link del formulario de inscripcion</label>
              <input type="text" name="form_url" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['form_url'] ?? '') ?>" />
              <div class="row text-secondary">Link de Google Forms. Incluir https://</div>
            </div>

            <div class="col col-12 col-md-6 pe-md-3 mb-3">
              <label class="form-label">Imagen</label>
              <input name="userfile1" class="form-control bg-transparent text-white mb-2" type="file">
              <div class="row text-secondary">Recomendado: 1080 px de ancho.</div>
              <?php if (!empty($results['image'])) echo '<a href="./images/' . h($results['image']) . '" target="_blank" class="opacity"><img src="./images/' . h($results['image']) . '" class="mt-3 img-circle-large mb-3" /></a>'; ?>
            </div>
            <div class="col col-12 col-md-3 mb-3">
              <label class="form-label">Top</label>
              <input type="number" name="top" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['top'] ?? 0) ?>" />
              <div class="row text-secondary">Prioridad en los resultados.</div>
            </div>
            <div class="col col-12 col-md-3 mb-3">
              <label class="form-label">Visible en la web</label>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php if (!empty($results['active'])) echo 'checked' ?>>
                <label class="form-check-label" for="active">Activo</label>
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
                <th scope="col">Taller</th>
                <th scope="col">Fecha</th>
                <th scope="col">Cupos</th>
                <th scope="col">Precio ARS</th>
                <th scope="col">Estado</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row) { ?>
                <tr>
                  <td valign="middle" class="text-primary"><strong><?php echo h($row['title']) ?></strong></td>
                  <td valign="middle"><?php echo h($row['fecha'] ?: '---') ?></td>
                  <td valign="middle"><?php echo h($row['cupos']) ?></td>
                  <td valign="middle"><?php echo h($row['price_ars']) ?></td>
                  <td valign="middle"><?php echo $row['active'] ? 'Activo' : '<span class="text-secondary">Oculto</span>' ?></td>
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
