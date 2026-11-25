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

// Tipos de inscripcion: se eligen en el panel y en la web salen en dos columnas.
$TIPOS = ['experiencia' => 'Experiencias formativas', 'residencia' => 'Residencias'];

// Si la tabla es de la version anterior (sin la columna "tipo"), se agrega sola.
// Lo que ya estaba cargado queda como "Residencias".
try {
  if (!$pdo->query("SHOW COLUMNS FROM residencias LIKE 'tipo'")->fetch()) {
    $pdo->exec("ALTER TABLE residencias ADD COLUMN tipo varchar(20) NOT NULL DEFAULT 'residencia' AFTER form_url");
  }
} catch (PDOException $e) {
  error_log('residencias.php (columna tipo): ' . $e->getMessage());
}

// Orden manual: la posicion 1 es la primera que se ve en la web.
// Cada tipo (residencia / experiencia formativa) tiene su propio orden.
// Se guarda en la columna `top` (el valor mas alto va primero), renumerando las del mismo tipo.
function ids_del_tipo($pdo, $tipo, $excluir = 0)
{
  $statement = $pdo->prepare('SELECT id FROM residencias WHERE tipo = ? ORDER BY top DESC, id DESC');
  $statement->execute([$tipo]);
  $ids = $statement->fetchAll(PDO::FETCH_COLUMN);
  return array_values(array_filter($ids, fn($x) => intval($x) !== intval($excluir)));
}

function guardar_orden($pdo, $ids)
{
  $n = count($ids);
  $statement = $pdo->prepare('UPDATE residencias SET top = ? WHERE id = ?');
  foreach ($ids as $i => $rid) {
    $statement->execute([$n - $i, intval($rid)]);
  }
}

function reordenar($pdo, $id, $posicion, $tipo)
{
  $ids = ids_del_tipo($pdo, $tipo, $id);
  $posicion = max(1, min(count($ids) + 1, intval($posicion)));
  array_splice($ids, $posicion - 1, 0, [intval($id)]);
  guardar_orden($pdo, $ids);
}

if (isset($_POST['form'])) {
  $action = $_POST['action'] ?? '';
  $id = intval($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $text = trim($_POST['text'] ?? '');
  $form_url = trim($_POST['form_url'] ?? '');
  $tipo = array_key_exists($_POST['tipo'] ?? '', $TIPOS) ? $_POST['tipo'] : 'residencia';
  $posicion = intval($_POST['posicion'] ?? 1);

  if ($title === '') {
    $error = "El título es obligatorio.";
  } else if (!link_valido($form_url)) {
    $error = "El link tiene que ser de un formulario de Google (empieza con https://docs.google.com/forms/...).";
  } else {
    try {
      if ($action == 'Agregar') {
        $statement = $pdo->prepare('INSERT INTO residencias (title, text, form_url, tipo) VALUES (?, ?, ?, ?)');
        $statement->execute([$title, $text, $form_url, $tipo]);
        $new_id = $pdo->lastInsertId();
        // El id va en el slug para que nunca se repita.
        $statement = $pdo->prepare('UPDATE residencias SET slug = ? WHERE id = ?');
        $statement->execute([getSlug($title) . '-' . $new_id, $new_id]);
        $id = $new_id;
      } else {
        $statement = $pdo->prepare('SELECT tipo FROM residencias WHERE id = ?');
        $statement->execute([$id]);
        $tipo_anterior = $statement->fetchColumn();
        $statement = $pdo->prepare('UPDATE residencias SET title = ?, text = ?, form_url = ?, tipo = ? WHERE id = ?');
        $statement->execute([$title, $text, $form_url, $tipo, $id]);
      }
      reordenar($pdo, $id, $posicion, $tipo);
      // Si cambio de tipo, el tipo que dejo se vuelve a numerar sin hueco.
      if (!empty($tipo_anterior) && $tipo_anterior !== $tipo) {
        guardar_orden($pdo, ids_del_tipo($pdo, $tipo_anterior));
      }
      header("Location:$file_main");
      die();
    } catch (PDOException $e) {
      error_log('residencias.php: ' . $e->getMessage());
      $error = "No se pudo guardar. Probá de nuevo.";
    }
  }
  // Hubo un error: se vuelve a mostrar el formulario con lo que se habia escrito.
  $results = ['title' => $title, 'text' => $text, 'form_url' => $form_url, 'tipo' => $tipo];
  $action = $action ?: 'Agregar';
  $title_sec = $action == 'Agregar' ? "Agregar Residencia o Experiencia Formativa" : "Modificar Residencia o Experiencia Formativa";
  $id_select = $id;
  $_GET['mod'] = 1;
} else if (isset($_POST['delete'])) {
  $pdo->prepare('DELETE FROM residencias WHERE id = ?')->execute([intval($_POST['id'] ?? 0)]);
  header("Location:$file_main");
  die();
} else if (isset($_GET['add'])) {
  $action = 'Agregar';
  $title_sec = "Agregar Residencia o Experiencia Formativa";
  $id_select = 0;
  $results = [];
} else if (isset($_GET['edit'])) {
  $action = 'Modificar';
  $title_sec = "Modificar Residencia o Experiencia Formativa";
  $id_select = intval($_GET['i'] ?? 0);
  $statement = $pdo->prepare('SELECT * FROM residencias WHERE id = ?');
  $statement->execute([$id_select]);
  $results = $statement->fetch() ?: [];
} else {
  $title_sec = "Residencias y Experiencias Formativas";
  // Primero las residencias y despues las experiencias formativas (como en la web), cada una con su orden.
  $statement = $pdo->prepare("SELECT * FROM residencias ORDER BY (tipo = 'experiencia'), top DESC, id DESC");
  $statement->execute();
  $results = $statement->fetchAll();
  $total = count($results);
}

// Opciones del selector de posicion: dependen del tipo elegido (cada tipo tiene su orden).
if (isset($_GET['mod'])) {
  $tipo_form = $results['tipo'] ?? 'residencia';
  $cant_por_tipo = [];   // cantidad de posiciones que ofrece cada tipo (incluyendo esta inscripcion)
  $pos_por_tipo = [];    // posicion preseleccionada para cada tipo
  foreach ($TIPOS as $valor => $nombre) {
    $ids = ids_del_tipo($pdo, $valor, $id_select);
    $cant_por_tipo[$valor] = count($ids) + 1;
    $pos_por_tipo[$valor] = count($ids) + 1;   // por defecto va al final
  }
  if ($action != 'Agregar') {
    // En una modificacion, el tipo guardado conserva su posicion actual.
    $statement = $pdo->prepare('SELECT tipo FROM residencias WHERE id = ?');
    $statement->execute([$id_select]);
    $tipo_guardado = $statement->fetchColumn();
    if ($tipo_guardado && isset($TIPOS[$tipo_guardado])) {
      $todos = ids_del_tipo($pdo, $tipo_guardado);
      $idx = array_search((string)$id_select, array_map('strval', $todos), true);
      $pos_por_tipo[$tipo_guardado] = $idx === false ? 1 : $idx + 1;
    }
  }
  if (isset($_POST['posicion']) && isset($pos_por_tipo[$tipo_form])) {
    $pos_por_tipo[$tipo_form] = max(1, min($cant_por_tipo[$tipo_form], intval($_POST['posicion'])));
  }
  $opciones = $cant_por_tipo[$tipo_form] ?? 1;
  $pos_actual = $pos_por_tipo[$tipo_form] ?? $opciones;
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
              <label class="form-label">Tipo de inscripción</label>
              <select name="tipo" id="tipo" class="form-select bg-transparent text-white mb-2">
                <?php
                $tipo_actual = $results['tipo'] ?? 'residencia';
                foreach ($TIPOS as $valor => $nombre) {
                  echo '<option value="' . h($valor) . '"' . ($valor == $tipo_actual ? ' selected' : '') . '>' . h($nombre) . '</option>';
                }
                ?>
              </select>
              <div class="row text-secondary">En la web, la inscripción sale en la columna de su tipo.</div>
            </div>
            <div class="col col-12 col-md-6 mb-3">&nbsp;</div>
            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Título</label>
              <input type="text" name="title" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['title'] ?? '') ?>" />
              <div class="row text-secondary">Ej: Residencia 1 Enero 2027</div>
            </div>
            <div class="col col-12 col-md-6 mb-3">
              <label class="form-label">Reseña</label>
              <textarea name="text" cols="30" rows="4" class="form-control bg-transparent text-white mb-2"><?php echo h($results['text'] ?? '') ?></textarea>
              <div class="row text-secondary">Texto corto que se ve debajo del título, a la izquierda del formulario. Puede quedar vacío.</div>
            </div>
            <div class="col col-12 mb-3">
              <label class="form-label">Link del formulario de inscripción</label>
              <input type="text" name="form_url" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['form_url'] ?? '') ?>" />
              <div class="row text-secondary">Link de Google Forms (https://docs.google.com/forms/...). Se muestra dentro de la página de la residencia.</div>
            </div>
            <div class="col col-12 col-md-6 mb-3 pe-md-3">
              <label class="form-label">Posición en la web (dentro de su tipo)</label>
              <select name="posicion" id="posicion" class="form-select bg-transparent text-white mb-2">
                <?php
                for ($p = 1; $p <= $opciones; $p++) {
                  $etiqueta = $p == 1 ? 'Posición 1 (la primera)' : 'Posición ' . $p;
                  echo '<option value="' . $p . '"' . ($p == $pos_actual ? ' selected' : '') . '>' . $etiqueta . '</option>';
                }
                ?>
              </select>
              <div class="row text-secondary">Cada tipo tiene su propio orden. Las demás inscripciones del mismo tipo se acomodan solas.</div>
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
        <script>
          // Al cambiar el tipo, las posiciones disponibles cambian porque cada tipo tiene su propio orden.
          (function() {
            var cantidades = <?php echo json_encode($cant_por_tipo) ?>;
            var preseleccion = <?php echo json_encode($pos_por_tipo) ?>;
            var tipo = document.getElementById('tipo');
            var pos = document.getElementById('posicion');
            function armar() {
              var n = cantidades[tipo.value] || 1;
              var sel = preseleccion[tipo.value] || n;
              pos.innerHTML = '';
              for (var p = 1; p <= n; p++) {
                var o = document.createElement('option');
                o.value = p;
                o.textContent = p === 1 ? 'Posición 1 (la primera)' : 'Posición ' + p;
                if (p === sel) o.selected = true;
                pos.appendChild(o);
              }
            }
            tipo.addEventListener('change', armar);
            armar();
          })();
        </script>
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
                <th scope="col">Pos.</th>
                <th scope="col">Inscripción</th>
                <th scope="col">Tipo</th>
                <th scope="col">Formulario</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <?php $contador = [];
              foreach ($results as $row) {
                $tk = $row['tipo'] ?? 'residencia';
                $contador[$tk] = ($contador[$tk] ?? 0) + 1; ?>
                <tr>
                  <td valign="middle"><?php echo $contador[$tk] ?></td>
                  <td valign="middle" class="text-primary"><strong><?php echo h($row['title']) ?></strong></td>
                  <td valign="middle"><?php echo h($TIPOS[$row['tipo'] ?? 'residencia'] ?? 'Residencias') ?></td>
                  <td valign="middle"><a href="<?php echo h($row['form_url']) ?>" target="_blank" rel="noopener noreferrer">Ver formulario</a></td>
                  <td valign="middle" align="right">
                    <a href="?mod=1&edit=1&i=<?php echo intval($row['id']) ?>" class="circle circle-btn bg-transparent" title="Editar" aria-label="Editar"><i class="bi bi-file-earmark-text"></i> Editar</a>
                    <a href="#" class="circle circle-btn bg-transparent delete" data-id="<?php echo intval($row['id']) ?>" title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash-fill"></i> Eliminar</a>
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
