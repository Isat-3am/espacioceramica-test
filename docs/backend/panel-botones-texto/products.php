<?php

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');
require_once('./utilities/orden.php');

$file_main = "products";
$table = "items";
$sec = 1;

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
      $sql = 'INSERT INTO items  (`type`, `title`, `title_en`, `text`, `text_en`, `author`, `category`, `category_2`, `image`, `top`, `slug`, `size`, `size_en`, `text_2`, `text_2_en`, `price_ars`, `price_usd`, `stock`)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )';

      $statement = $pdo->prepare($sql);
      $statement->execute([
        $sec,
        $title,
        $title_en,
        $text,
        $text_en,
        $author,
        $category,
        $category_2,
        $image,
        0,
        getSlug($title),
        $size,
        $size_en,
        $text_2,
        $text_2_en,
        $price_ars,
        $price_usd,
        $stock
      ]);
orden_mover($pdo, 'items', 'type = ?', [$sec], $pdo->lastInsertId(), $top);
header("Location:$file_main");
    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
    }
  } else {
    $sql = 'UPDATE items SET `title` = "' . $title  . '", `title_en` = "' . $title_en . '", `text` = "' . $text . '", `text_en` = "' . $text_en . '" , `author` = "' . $author . '" , `slug` = "' . getSlug($title_en) . '", `category` = "' . $category . '", `category_2` = "' . $category_2 . '", `size` = "' . $size . '", `size_en` = "' . $size_en . '", `text_2` = "' . $text_2 . '", `text_2_en` = "' . $text_2_en . '", `price_ars` = "' . $price_ars . '", `price_usd` = "' . $price_usd . '", `stock` = "' . $stock . '"';

    if ($_FILES['userfile1']['name']) $sql .= ", `image` = '$image' ";

    $sql .= " WHERE `id` = $id;";
    $statement = $pdo->prepare($sql);
    $statement->execute();
    orden_mover($pdo, 'items', 'type = ?', [$sec], intval($id), $top);
    header("Location:$file_main");
  }
}
if (isset($_GET['add'])) {

  $action = 'Agregar';
  $title_sec = "Agregar Producto";
} else if (isset($_GET['edit'])) {

  $action = 'Modificar';
  $title_sec = "Modificar Producto";
  $id_select = $_GET['i'];
  $sql = "SELECT * FROM items WHERE id = $id_select";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  $results = $statement->fetch();
} else if (isset($_POST['delete'])) {

  $id_select = $_POST['id'];
  $sql = "DELETE FROM items WHERE id = $id_select";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  header("Location:$file_main");
} else {

  $title_sec = "Productos";

  $order = "top";
  $order2 = "DESC";
  $page = 1;
  $numreg = 80;
  $category = 0;

  if ($_POST) {
    $order = $_POST['order'];
    $order2 = $_POST['order2'];
    $page = $_POST['page'];
    $category = $_POST['category'];
  }

  $where = "WHERE type = $sec";
  if ($category) {
    $where .= " AND category = $category";
  }

  $sql_pages = "SELECT id FROM items $where";
  require_once("./utilities/pagination.php");

  $sql = "SELECT items.*, categories.title as category_name, authors.title as author_name
  FROM items INNER JOIN categories ON items.category = categories.id INNER JOIN authors ON items.author = authors.id $where
  ORDER BY $order $order2, top DESC LIMIT " . $limitinf . "," . $numreg . " ";
  $statement = $pdo->prepare($sql);
  $statement->execute();
  $results = $statement->fetchAll();
  $total = $statement->rowCount();
  $order_list = array('top' => 'Posición', 'price_ars' => 'Precio', 'category_name' => 'Categoría', 'author_name' => 'Autor');
}
// Orden por posicion (1 = primera).
if (isset($_GET['mod'])) {
  $orden_ids = orden_ids($pdo, 'items', 'type = ?', [$sec]);
  $orden_idx = (($action ?? '') == 'Modificar' && isset($id_select)) ? array_search(intval($id_select), $orden_ids, true) : false;
  $orden_n = $orden_idx !== false ? count($orden_ids) : count($orden_ids) + 1;
  $orden_sel = $orden_idx !== false ? $orden_idx + 1 : $orden_n;
} else {
  $pos_map = array_flip(orden_ids($pdo, 'items', 'type = ?', [$sec]));
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

            <div class="col col-12 col-md-6 mb-3 pe-md-3 ">
              <label class="form-label">Categoría</label>
              <select class="form-select bg-transparent text-primary border-primary rounded-0" name="category">
                <?php
                $sql2 = "SELECT * FROM categories";
                $statement = $pdo->prepare($sql2);
                $statement->execute();
                $results_categories = $statement->fetchAll();
                foreach ($results_categories as $row_category) {
                  $indice = $row_category['id'];
                  $value = $row_category['title'];
                  if ($results['category'] == $indice) $aux = "selected";
                  else $aux = false;
                  echo "<option $aux value='$indice'>$value</option> ";
                }
                ?>
              </select>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Categoría Secundaria</label>
              <select class="form-select bg-transparent text-primary border-primar rounded-0" name="category_2">
                <?php
                $sql2 = "SELECT * FROM categories";
                $statement = $pdo->prepare($sql2);
                $statement->execute();
                $results_categories = $statement->fetchAll();
                foreach ($results_categories as $row_category) {
                  $indice = $row_category['id'];
                  $value = $row_category['title'];
                  if ($results['category_2']  == $indice) $aux = "selected";
                  else $aux = false;
                  echo "<option $aux value='$indice'>$value</option> ";
                }
                ?>
              </select>
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Detalles (Spanish)</label>
              <textarea name="text" cols="30" rows="3" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['text'])) echo ($results['text']) ?></textarea>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Detalles (English)</label>
              <textarea name="text_en" cols="30" rows="3" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['text_en'])) echo ($results['text_en']) ?></textarea>
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Precio ARS </label>
              <input type="number" name="price_ars" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['price_ars'])) echo ($results['price_ars']) ?>" />
              <div class="row text-secondary">Solo números. </div>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Precio USD</label>
              <input type="number" name="price_usd" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['price_usd'])) echo ($results['price_usd']) ?>" />
              <div class="row text-secondary">Solo números. </div>
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Dimensiones (Spanish)</label>
              <textarea name="size" cols="30" rows="3" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['size'])) echo ($results['size']) ?></textarea>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Dimensiones (English)</label>
              <textarea name="size_en" cols="30" rows="3" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['size_en'])) echo ($results['size_en']) ?></textarea>
            </div>

            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Comentarios (Spanish)</label>
              <textarea name="text_2" cols="30" rows="3" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['text_2'])) echo ($results['text_2']) ?></textarea>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Comentarios (English)</label>
              <textarea name="text_2_en" cols="30" rows="3" class="form-control bg-transparent text-white mb-2"><?php if (isset($results['text_2_en'])) echo ($results['text_2_en']) ?></textarea>
            </div>

            <div class="col col-12 col-md-6 pe-md-3 mb-3">
              <label class="form-label">Autor</label>
              <select class="form-select bg-transparent text-primary border-primary rounded-0" name="author">
                <?php
                $sql2 = "SELECT * FROM authors";
                $statement = $pdo->prepare($sql2);
                $statement->execute();
                $results_authors = $statement->fetchAll();
                foreach ($results_authors as $row_author) {
                  $indice = $row_author['id'];
                  $value = $row_author['title'];
                  if ($results['author']  == $indice) $aux = "selected";
                  else $aux = false;
                  echo "<option $aux value='$indice'>$value</option> ";
                }
                ?>
              </select>
            </div>
            <div class="col col-12 col-md-6 pe-md-3 mb-3">
              <label class="form-label">Stock</label>
              <input type="number" name="stock" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['stock'])) echo ($results['stock']) ?>" />
            </div>

            <div class="col col-12 col-md-6 pe-md-3">
              <label class="form-label">Imagen</label>
              <input name="userfile1" class="form-control bg-transparent text-white mb-2" type="file">
              <div class="row text-secondary">Recomendado: 1080 px de ancho. </div>
              <?php if (isset($results['image'])) if ($results['image'])  echo '<a href="./images/' . $results['image'] . '" target="_blank" class="opacity"><img src="./images/' . $results['image'] . '" class="mt-3 img-circle-large mb-3" /></a>'; ?>
            </div>
            <div class="col col-12 col-md-6 ">
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
        </form>
      </section>

    <?php } else { ?>

      <section class="p-3 p-lg-5 p-md-4 text-white">
        <div class="row mb-4">
          <h2><strong><?php echo $title_sec ?></strong> (<?php echo $total ?>)</h2>
        </div>
        <?php
        require_once("./includes/order.php");
        if ($total) {
        ?>
          <table class="table table-dark table-hover">
            <thead>
              <tr class="text-secondary">
                <th scope="col">Imagen</th>
                <th scope="col">Título</th>
                <th scope="col">Precio</th>
                <th scope="col">Categoría</th>
                <th scope="col">Autor</th>
                <th scope="col">Posición</th>
                <th scope="col">Galería</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>

              <?php
              foreach ($results as $row) {
                $id = $row['id'];
                $title = $row['title'];
                $image = $row['image'];
                $top = $row['top'];
                $price = $row['price_ars'];
                $category = $row['category_name'];
                $author = $row['author_name'];

                $sql_images = "SELECT images.id FROM images WHERE images.item = $id";
                $statement = $pdo->prepare($sql_images);
                $statement->execute();
                $total_images = $statement->rowCount();
              ?>
                <tr>
                  <td valign="middle">
                    <?php if ($image) echo '<img src="./images/' . $image . '" class="img-circle-large" />'; ?>
                  </td>
                  <td valign="middle" class="text-primary">
                    <strong><?php echo $title; ?></strong>
                  </td>
                  <td valign="middle">
                    ARS <?php echo $price; ?>
                  </td>
                  <td valign="middle">
                    <?php echo $category; ?>
                  </td>
                  <td valign="middle">
                    <?php echo $author; ?>
                  </td>
                  <td valign="middle">
                    <?php echo isset($pos_map[$id]) ? ($pos_map[$id] + 1) . '&deg;' : '-'; ?>
                  </td>
                  <td valign="middle">
                    <a href="images.php?id_sec=<?php echo $id; ?>">Imágenes (<?php echo $total_images; ?>)</a>
                  </td>
                  <td valign="middle" align="right">
                    <a href="?mod=1&edit=1&i=<?php echo $id ?>" class="circle circle-btn bg-transparent"><i class="bi bi-file-earmark-text"></i> Editar</a>
                    <a href="#" class="circle circle-btn bg-transparent delete" data-id="<?php echo $id ?>"><i class="bi bi-trash-fill"></i> Eliminar</a>
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