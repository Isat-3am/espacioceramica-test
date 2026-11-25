<?php

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');

$file_main = "presentaciones";
$table = "items";
$sec = 7;

// Reordena todas las presentaciones para que el campo "Top" quede como numeros seguidos (0 a N-1,
// el 0 es el ultimo de la lista) y la presentacion editada quede en la posicion elegida.
function renumerar_presentaciones($pdo, $sec, $id, $top)
{
  $ids = $pdo->query("SELECT id FROM items WHERE type = " . (int)$sec . " AND id <> " . (int)$id . " ORDER BY top DESC, id ASC")->fetchAll(PDO::FETCH_COLUMN);
  $n = count($ids) + 1;
  $top = max(0, min($n - 1, (int)$top));
  array_splice($ids, $n - 1 - $top, 0, [(int)$id]);
  $statement = $pdo->prepare("UPDATE items SET top = ? WHERE id = ?");
  foreach ($ids as $i => $x) $statement->execute([$n - 1 - $i, $x]);
}

if (isset($_POST['form'])) {

  foreach ($_POST as $campo => $valor) {
    $cadena = "\$" . $campo . "='" . addslashes($valor) . "';";
    eval($cadena);
  }

  $data = "userfile1";
  if ($_FILES[$data]['name']) {
    $image = "ec_" . GenerateString(12) . ".jpg";
    $width = 1080;
    require_once("./utilities/create_image.php");
  } else $image = "";

  if ($action == 'Agregar') {

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
      $sql = 'INSERT INTO items (`type`, `title`, `title_en`, `top`, `text`, `text_en`, `image`, `category`)
      VALUES ( ?, ?, ?, ?, ?, ?, ? , ? )';

      $statement = $pdo->prepare($sql);
      $statement->execute([
        $sec,
        $title,
        $title_en,
        $top,
        $text,
        $text_en,
        $image,
        $category
      ]);
      renumerar_presentaciones($pdo, $sec, $pdo->lastInsertId(), $top);

      header("Location:$file_main");
    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
    }
  } else {
    $sql = 'UPDATE items SET `title` = "' . $title  . '", `title_en` = "' . $title_en . '", `top` = "' . $top . '", `text` = "' . $text . '", `text_en` = "' . $text_en . '" , `category` = "' . $category . '"';

    if ($_FILES['userfile1']['name']) $sql .= ", `image` = '$image' ";

    $sql .= " WHERE `id` = $id;";
    $statement = $pdo->prepare($sql);
    $statement->execute();
    renumerar_presentaciones($pdo, $sec, (int)$id, $top);
    header("Location:$file_main");
  }
}
if (isset($_GET['add'])) {

  $action = 'Agregar';
  $title_sec = "Agregar Presentación";
  $top_max = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type = $sec")->fetchColumn();
  $top_val = 0;
} else if (isset($_GET['edit'])) {

  $action = 'Modificar';
  $title_sec = "Modificar Presentación";
  $id_select = $_GET['i'];
  $sql = "SELECT * FROM items WHERE id = $id_select";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  $results = $statement->fetch();
  // Posicion actual en la lista, de 0 a N-1 (el que sale primero tiene el numero mas alto).
  $ids_orden = $pdo->query("SELECT id FROM items WHERE type = $sec ORDER BY top DESC, id ASC")->fetchAll(PDO::FETCH_COLUMN);
  $top_max = count($ids_orden) - 1;
  $pos = array_search($results['id'], $ids_orden);
  $top_val = $pos === false ? 0 : $top_max - $pos;
} else if (isset($_POST['delete'])) {

  $id_select = $_POST['id'];
  $sql = "DELETE FROM items WHERE id = $id_select";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  header("Location:$file_main");
} else {

  $title_sec = "Presentaciones";
  $sql = "SELECT * FROM items WHERE type = $sec ORDER BY top DESC";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  $results = $statement->fetchAll();
  $total = $statement->rowCount();
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
          <h2><strong><?php echo $title_sec ?></strong> </h2>
        </div>
        <form method="POST" name="form" enctype="multipart/form-data">
          <div class="row mb-5">

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Título (Spanish)</label>
              <input type="text" name="title" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['title'])) echo ($results['title']) ?>" />
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Título (English)</label>
              <input type="text" name="title_en" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['title_en'])) echo ($results['title_en']) ?>" />
            </div>

            <div class="col col-12 mb-3">
              <label class="form-label">Sección</label>
              <select class="form-select bg-transparent text-primary border-primary rounded-0" name="category">
                <?php
                $options2 = array(1 => 'Inicio', 2 => 'Espacio Cerámica', 3 => 'Residencias', 4 => 'Hornos', 5 => 'Experiencias Formativas');
                foreach ($options2 as $indice => $value) {
                  if ($results['category'] == $indice) $aux = "selected";
                  else $aux = "";
                  echo "<option value='$indice' $aux>$value</option> ";
                }
                ?>
              </select>
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Descripción (Spanish)</label>
              <textarea name="text" cols="30" rows="5" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['text'])) echo ($results['text']) ?></textarea>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Descripción (English)</label>
              <textarea name="text_en" cols="30" rows="5" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['text_en'])) echo ($results['text_en']) ?></textarea>
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Imagen Presentación</label>
              <input name="userfile1" class="form-control bg-transparent text-white mb-2" type="file">
              <div class="row text-secondary">Recomendado: 1080 px de ancho. </div>
              <?php if (isset($results['image'])) if ($results['image'])  echo '<a href="./images/' . $results['image'] . '" target="_blank" class="opacity"><img src="./images/' . $results['image'] . '" class="mt-3 img-circle-large mb-3" /></a>'; ?>
            </div>

            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Top</label>
              <input type="number" name="top" min="0" max="<?php echo $top_max ?>" step="1" class="form-control bg-transparent text-white mb-2" value="<?php echo $top_val ?>" onkeydown="if(['ArrowUp','ArrowDown','Tab'].indexOf(event.key)<0) return false;" onpaste="return false" />
              <div class="row text-secondary">Posición en la lista, de 0 a <?php echo $top_max ?> (usar las flechas). El número más alto va primero. El sitio toma las fotos y los textos según este orden: conviene no cambiarlo en las presentaciones que ya existen.</div>
            </div>

          </div>
          <div class="row">
            <div class="col col-12">
              <button type="button" class="rounded-2 btn btn-primary mybtn" onClick="javascript:document.form.submit();"><?php echo $action ?></button>
              <button type="button" class="rounded-2 btn btn-secondary mybtn mx-2" onClick="javascript:history.back();">Cancelar</button>
            </div>
          </div>
          <input type="hidden" name="form" value="1">
          <input type="hidden" name="action" value="<?php echo $action ?>">
          <input type="hidden" name="id" value="<?php echo $id_select ?>">
        </form>
      </section>

    <?php } else { ?>

      <section class="p-3 p-lg-5 p-md-4 text-white">
        <div class="row mb-4">
          <div class="col-md-10 mb-2">
            <h2><strong><?php echo $title_sec ?></strong> (<?php echo $total ?>)</h2>
          </div>
          <!-- <div class="col-md-2">
            <a href="?mod=1&add=1" class="rounded-2 btn btn-primary mybtn w-100">Agregar</a>
          </div> -->
        </div>

        <?php
        if ($total) {
        ?>
          <table class="table table-dark table-hover">
            <thead>
              <tr class="text-secondary">
                <th scope="col">Título</th>
                <th scope="col">Sección</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>

              <?php
              foreach ($results as $row) {
                $id = $row['id'];
                $title = $row['title'];
                $top = $row['top'];
                $section = $row['category'];

                if ($section == 1) {
                  $section = "Inicio";
                } else if ($section == 2) {
                  $section = "Espacio Cerámica";
                } else if ($section == 3) {
                  $section = "Residencias";
                } else if ($section == 4) {
                  $section = "Hornos";
                } else if ($section == 5) {
                  $section = "Experiencias Formativas";
                } else {
                  $section = "Inicio";
                }

              ?>
                <tr>
                  <td valign="middle" class="text-primary">
                    <strong><?php echo $title; ?></strong>
                  </td>
                  <td valign="middle">
                    <strong><?php echo $section; ?></strong>
                  </td>

                  <td valign="middle" align="right">
                    <a href="?mod=1&edit=1&i=<?php echo $id ?>" class="circle circle-btn bg-transparent"><i class="bi bi-file-earmark-text"></i> Editar</a>
                    <!-- <a href="#" class="circle circle-btn bg-transparent delete" data-id="<?php echo $id ?>"><i class="bi bi-trash-fill"></i> Eliminar</a> -->
                  </td>
                </tr>
              <?php
              }
              ?>
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