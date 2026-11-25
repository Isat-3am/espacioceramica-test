<?php

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');
require_once('./utilities/orden.php');

$file_main = "images.php";
$table = "images";

if (isset($_GET['id_sec'])) {
  $id_sec = $_GET['id_sec'];
} else if (isset($_POST['id_sec'])) {
  $id_sec = $_POST['id_sec'];
} else {
  header("Location:inversiones");
}

$sql = "SELECT items.title
FROM items 
WHERE items.id = $id_sec";
$statement = $pdo->prepare($sql);
$statement->execute();
$results = $statement->fetch();
$item_name = $results['title'];

if ($_POST) {

  foreach ($_POST as $campo => $valor) {
    $cadena = "\$" . $campo . "='" . addslashes($valor) . "';";
    eval($cadena);
  }

  $data = "userfile1";
  if ($_FILES[$data]['name']) {
    $image = "ec_" . GenerateString(10) . ".jpg";
    $width = 1280;
    require_once("./utilities/create_image.php");
  } else $image = "";

  if ($action == 'Agregar') {

    $sql = 'INSERT INTO ' . $table . ' 
		(`item`, `image`, `top` ) 
		VALUES 
		("' . $id_sec  . '", "' . $image . '", "0" )';

    $statement = $pdo->prepare($sql);
    $statement->execute();
    orden_mover($pdo, 'images', 'item = ?', [$id_sec], $pdo->lastInsertId(), $top);
    header("Location:$file_main?id_sec=$id_sec");
  } else if ($action == 'Modificar') {

    $sql = 'UPDATE ' . $table . ' SET  `top` = `top` ';
    if ($_FILES['userfile1']['name']) $sql .= ", `image` = '$image' ";
    $sql .= " WHERE `id` = $id;";

    $statement = $pdo->prepare($sql);
    $statement->execute();
    orden_mover($pdo, 'images', 'item = ?', [$id_sec], intval($id), $top);
    header("Location:$file_main?id_sec=$id_sec");
  }
}
if (isset($_GET['add'])) {

  $action = 'Agregar';
  $title_sec = "Agregar imagen para $item_name";
} else if (isset($_GET['edit'])) {

  $action = 'Modificar';
  $title_sec = "Modificar imagen para $item_name";
  $id_select = $_GET['i'];
  $sql = "SELECT * FROM $table WHERE id = $id_select ";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  $results = $statement->fetch();
} else if (isset($_POST['delete'])) {

  $id_select = $_POST['id'];
  $sql = "DELETE FROM $table WHERE id = $id_select";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  header("Location:$file_main?id_sec=$id_sec");
} else {

  $title_sec = "Imágenes para $item_name";
  $sql = "SELECT * 
  FROM $table 
  WHERE $table.item = $id_sec
  ORDER BY $table.top DESC";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  $results = $statement->fetchAll();
  $total = count($results);
}

// Orden por posicion (1 = primera).
if (isset($_GET['mod'])) {
  $orden_ids = orden_ids($pdo, 'images', 'item = ?', [$id_sec]);
  $orden_idx = (($action ?? '') == 'Modificar' && isset($id_select)) ? array_search(intval($id_select), $orden_ids, true) : false;
  $orden_n = $orden_idx !== false ? count($orden_ids) : count($orden_ids) + 1;
  $orden_sel = $orden_idx !== false ? $orden_idx + 1 : $orden_n;
} else {
  $pos_map = array_flip(orden_ids($pdo, 'images', 'item = ?', [$id_sec]));
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
          <h2> <strong><?php echo $title_sec ?></strong> </h2>
        </div>
        <form method="POST" name="form" enctype="multipart/form-data">

          <div class="row mb-5">
            <div class="col col-12 mb-3">
              <label class="form-label">Imagen</label>
              <input name="userfile1" class="form-control bg-transparent text-white mb-2" type="file">
              <div class="row text-secondary">Recomendado: 1280 px de ancho. </div>
              <?php if (isset($results['image'])) if ($results['image'])  echo '<a href="./images/' . $results['image'] . '" target="_blank" class="opacity"><img src="./images/' . $results['image'] . '" class="mt-3 img-circle-large mb-3" /></a>'; ?>
            </div>
            <div class="col col-12 mb-3">
              <label class="form-label">Posición en la web</label>
              <?php echo orden_selector($orden_n, $orden_sel); ?>
              <div class="row text-secondary">Elegí en qué lugar se muestra: 1° es la primera. Las demás se acomodan solas.</div>
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
          <input type="hidden" name="id_sec" value="<?php echo $id_sec ?>">
        </form>
      </section>

    <?php } else { ?>

      <section class="p-3 p-lg-5 p-md-4 text-white">

        <div class="row mb-4">
          <div class="col-10 col-md-10 mb-2">
            <h2> <strong><?php echo $title_sec ?></strong> (<?php echo $total ?>)</h2>
          </div>
          <div class="col-md-2 mb-2 pe-md-2">
            <a href="?mod=1&add=1&id_sec=<?php echo $id_sec ?>" class="rounded-2 btn btn-primary mybtn w-100">Agregar</a>
          </div>
        </div>
        <?php
        if ($total) {
        ?>
          <table class="table table-dark table-hover">
            <thead>
              <tr class="text-secondary">
                <th scope="col">Imagen</th>
                <th scope="col">Posición</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($results as $row) {
                $id = $row['id'];
                $image = $row['image'];
                $top = $row['top'];
              ?>
                <tr>
                  <td valign="middle">
                    <?php
                    if (isset($image)) if ($image)  echo '<img src="./images/' . $image . '" class="img-circle-large" />';
                    ?>
                  </td>
                  <td valign="middle">
                    <?php echo isset($pos_map[$id]) ? ($pos_map[$id] + 1) . '&deg;' : '-'; ?>
                  </td>
                  <td valign="middle" align="right">
                    <a href="?mod=1&edit=1&i=<?= $id ?>&id_sec=<?= $id_sec ?>" class="circle circle-btn bg-transparent"><i class="bi bi-file-earmark-text"></i> Editar</a>
                    <a href="#" class="circle circle-btn bg-transparent delete" data-id="<?= $id ?>" data-sec="<?= $id_sec ?>" data-file="<?= $imagen ?>"><i class="bi bi-trash-fill"></i> Eliminar</a>
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