<?php

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');

$file_main = "voluntarios";
$table = "items";
$sec = 3;

if (isset($_POST['form'])) {

  foreach ($_POST as $campo => $valor) {
    $cadena = "\$" . $campo . "='" . addslashes($valor) . "';";
    eval($cadena);
  }

  if ($action == 'Agregar') {

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
      $sql = 'INSERT INTO items (`type`, `title`, `title_en`, `top`, `text`)
      VALUES ( ?, ?, ?, ?, ? )';

      $statement = $pdo->prepare($sql);
      $statement->execute([
        $sec,
        $title,
        $title_en,
        $top,
        $text
      ]);

      header("Location:$file_main");
    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
    }
  } else {
    $sql = 'UPDATE items SET `title` = "' . $title  . '", `title_en` = "' . $title_en . '", `top` = "' . $top . '", `text` = "' . $text . '" ';

    $sql .= " WHERE `id` = $id;";
    $statement = $pdo->prepare($sql);
    $statement->execute();
    header("Location:$file_main");
  }
}
if (isset($_GET['add'])) {

  $action = 'Agregar';
  $title_sec = "Agregar Residente";
} else if (isset($_GET['edit'])) {

  $action = 'Modificar';
  $title_sec = "Modificar Residente";
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

  $title_sec = "Residentes";
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
              <label class="form-label">Nombre</label>
              <input type="text" name="title" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['title'])) echo ($results['title']) ?>" />
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Cuenta</label>
              <input type="text" name="title_en" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['title_en'])) echo ($results['title_en']) ?>" />
            </div>
            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">URL</label>
              <input type="text" name="text" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['text'])) echo ($results['text']) ?>" />
              <div class="row text-secondary">Incluir https//</div>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Top</label>
              <input type="number" name="top" class="form-control bg-transparent text-white mb-2" value="<?php if (isset($results['top'])) echo ($results['top']) ?>" />
              <div class="row text-secondary">Número de prioridad en los resultados.</div>
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
          <div class="col-md-2">
            <a href="?mod=1&add=1" class="rounded-2 btn btn-primary mybtn w-100">Agregar</a>
          </div>
        </div>

        <?php
        if ($total) {
        ?>
          <table class="table table-dark table-hover">
            <thead>
              <tr class="text-secondary">
                <th scope="col">Nombre</th>
                <th scope="col">Cuenta</th>
                <th scope="col">Top</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>

              <?php
              foreach ($results as $row) {
                $id = $row['id'];
                $title = $row['title'];
                $cuenta = $row['title_en'];
                $url = $row['text'];
                $top = $row['top'];

              ?>
                <tr>
                  <td valign="middle" class="text-primary">
                    <strong><?php echo $title; ?></strong>
                  </td>
                  <td valign="middle">
                    <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $cuenta; ?></a>
                  </td>
                  <td valign="middle">
                    <?php echo $top; ?>
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