<?php

// Panel de residencias (version minima): titulo y link del formulario de Google.
// Va en la raiz de backend/ (junto a products.php). Con consultas preparadas y sin eval().
// Version completa en pausa: ../residencias-pausado/

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');

$file_main = "residencias";
$sec = 8;

function h($value)
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Solo se aceptan formularios de Google (el link se muestra incrustado en la web publica).
function link_valido($url)
{
  if (!preg_match('#^https://#i', $url)) return false;
  $host = strtolower((string)parse_url($url, PHP_URL_HOST));
  return in_array($host, ['docs.google.com', 'forms.gle', 'forms.google.com'], true);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['form'])) {
  $action = $_POST['action'] ?? '';
  $id = intval($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $form_url = trim($_POST['form_url'] ?? '');

  if ($title === '') {
    $error = "El título es obligatorio.";
  } else if (!link_valido($form_url)) {
    $error = "El link tiene que ser de un formulario de Google (empieza con https://docs.google.com/forms/...).";
  } else {
    try {
      if ($action == 'Agregar') {
        $statement = $pdo->prepare('INSERT INTO residencias (title, form_url) VALUES (?, ?)');
        $statement->execute([$title, $form_url]);
        $new_id = $pdo->lastInsertId();
        // El id va en el slug para que nunca se repita.
        $statement = $pdo->prepare('UPDATE residencias SET slug = ? WHERE id = ?');
        $statement->execute([getSlug($title) . '-' . $new_id, $new_id]);
      } else {
        $statement = $pdo->prepare('UPDATE residencias SET title = ?, form_url = ? WHERE id = ?');
        $statement->execute([$title, $form_url, $id]);
      }
      header("Location:$file_main");
      die();
    } catch (PDOException $e) {
      error_log('residencias.php: ' . $e->getMessage());
      $error = "No se pudo guardar. Probá de nuevo.";
    }
  }
  // Hubo un error: se vuelve a mostrar el formulario con lo que se habia escrito.
  $results = ['title' => $title, 'form_url' => $form_url];
  $action = $action ?: 'Agregar';
  $title_sec = $action == 'Agregar' ? "Agregar Residencia" : "Modificar Residencia";
  $id_select = $id;
  $_GET['mod'] = 1;
} else if (isset($_POST['delete'])) {
  $pdo->prepare('DELETE FROM residencias WHERE id = ?')->execute([intval($_POST['id'] ?? 0)]);
  header("Location:$file_main");
  die();
} else if (isset($_GET['add'])) {
  $action = 'Agregar';
  $title_sec = "Agregar Residencia";
  $id_select = 0;
  $results = [];
} else if (isset($_GET['edit'])) {
  $action = 'Modificar';
  $title_sec = "Modificar Residencia";
  $id_select = intval($_GET['i'] ?? 0);
  $statement = $pdo->prepare('SELECT * FROM residencias WHERE id = ?');
  $statement->execute([$id_select]);
  $results = $statement->fetch() ?: [];
} else {
  $title_sec = "Residencias";
  $statement = $pdo->prepare('SELECT * FROM residencias ORDER BY id DESC');
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
        <form method="POST" name="form">
          <div class="row mb-5">
            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Título</label>
              <input type="text" name="title" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['title'] ?? '') ?>" />
              <div class="row text-secondary">Ej: Residencia 1 Enero 2027</div>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Link del formulario de inscripción</label>
              <input type="text" name="form_url" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['form_url'] ?? '') ?>" />
              <div class="row text-secondary">Link de Google Forms (https://docs.google.com/forms/...). Se muestra dentro de la página de la residencia.</div>
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
                <th scope="col">Formulario</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row) { ?>
                <tr>
                  <td valign="middle" class="text-primary"><strong><?php echo h($row['title']) ?></strong></td>
                  <td valign="middle"><a href="<?php echo h($row['form_url']) ?>" target="_blank" rel="noopener noreferrer">Ver formulario</a></td>
                  <td valign="middle" align="right">
                    <a href="?mod=1&edit=1&i=<?php echo intval($row['id']) ?>" class="circle bg-transparent" title="Editar" aria-label="Editar"><i class="bi bi-file-earmark-text"></i></a>
                    <a href="#" class="circle bg-transparent delete" data-id="<?php echo intval($row['id']) ?>" title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash-fill"></i></a>
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
