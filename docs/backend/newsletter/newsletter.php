<?php

// Panel de la seccion Newsletter: titulo, descripcion (espanol e ingles) y link de Substack.
// Va en la raiz de backend/ (junto a products.php). Con consultas preparadas y sin eval().
// Es un solo registro (id = 1). La tabla se crea sola la primera vez que se abre este panel.

require_once('./utilities/session.php');
require_once('./utilities/functions.php');
require_once('./db/connection.php');

$file_main = "newsletter";
$sec = 9;

function h($value)
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Solo se aceptan direcciones de Substack que se puedan incrustar:
// la publicacion (https://algo.substack.com) o el perfil con arroba (https://substack.com/@algo).
function url_valida($url)
{
  if (!preg_match('#^https://#i', $url)) return false;
  $host = strtolower((string)parse_url($url, PHP_URL_HOST));
  $path = (string)parse_url($url, PHP_URL_PATH);
  if ($host === 'substack.com') return (bool)preg_match('#^/@[A-Za-z0-9_-]+#', $path);
  return substr($host, -14) === '.substack.com' && strlen($host) > 14;
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int NOT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title_en` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text COLLATE utf8mb4_unicode_ci,
  `text_en` text COLLATE utf8mb4_unicode_ci,
  `substack_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$statement = $pdo->prepare('INSERT IGNORE INTO newsletter (id, title, title_en, text, text_en, substack_url) VALUES (1, ?, ?, ?, ?, ?)');
$statement->execute([
  'Newsletter',
  'Newsletter',
  'Suscribite para recibir la crónica del taller y las novedades y promociones de la tienda.',
  "Subscribe to receive the workshop's chronicle, news and store promotions.",
  'https://espacioceramica.substack.com',
]);

if (isset($_POST['form'])) {
  $title = trim($_POST['title'] ?? '');
  $title_en = trim($_POST['title_en'] ?? '');
  $text = trim($_POST['text'] ?? '');
  $text_en = trim($_POST['text_en'] ?? '');
  $substack_url = trim($_POST['substack_url'] ?? '');

  if ($title === '') {
    $error = "El título es obligatorio.";
  } else if (!url_valida($substack_url)) {
    $error = "El link tiene que ser de Substack: la publicación (https://nombre.substack.com) o el perfil con arroba (https://substack.com/@nombre).";
  } else {
    try {
      $statement = $pdo->prepare('UPDATE newsletter SET title = ?, title_en = ?, text = ?, text_en = ?, substack_url = ? WHERE id = 1');
      $statement->execute([$title, $title_en, $text, $text_en, $substack_url]);
      header("Location:$file_main?ok=1");
      die();
    } catch (PDOException $e) {
      error_log('newsletter.php: ' . $e->getMessage());
      $error = "No se pudo guardar. Probá de nuevo.";
    }
  }
  $results = ['title' => $title, 'title_en' => $title_en, 'text' => $text, 'text_en' => $text_en, 'substack_url' => $substack_url];
} else {
  $results = $pdo->query('SELECT * FROM newsletter WHERE id = 1')->fetch() ?: [];
}
require("includes/head.php");

?>

<body class="bg-dark">
  <main>
    <?php require("includes/header.php"); ?>
    <section class="p-3 p-lg-5 p-md-4 text-white">
      <div class="row mb-5">
        <h2><strong>Newsletter</strong></h2>
      </div>
      <?php
      if (!empty($error)) echo '<div class="alert alert-danger">' . h($error) . '</div>';
      if (isset($_GET['ok'])) echo '<div class="alert alert-success">Guardado. Ya se ve en la web.</div>';
      ?>
      <form method="POST" name="form">
        <div class="row mb-5">
          <div class="col col-12 col-md-6 mb-3 pe-md-3">
            <label class="form-label">Título (Spanish)</label>
            <input type="text" name="title" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['title'] ?? '') ?>" />
          </div>
          <div class="col col-12 col-md-6 mb-3">
            <label class="form-label">Título (English)</label>
            <input type="text" name="title_en" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['title_en'] ?? '') ?>" />
          </div>
          <div class="col col-12 col-md-6 mb-3 pe-md-3">
            <label class="form-label">Descripción (Spanish)</label>
            <textarea name="text" cols="30" rows="4" class="form-control bg-transparent text-white mb-2"><?php echo h($results['text'] ?? '') ?></textarea>
          </div>
          <div class="col col-12 col-md-6 mb-3">
            <label class="form-label">Descripción (English)</label>
            <textarea name="text_en" cols="30" rows="4" class="form-control bg-transparent text-white mb-2"><?php echo h($results['text_en'] ?? '') ?></textarea>
            <div class="row text-secondary">Si se deja vacío, se muestra el texto por defecto en inglés.</div>
          </div>
          <div class="col col-12 mb-3">
            <label class="form-label">Link de Substack</label>
            <input type="text" name="substack_url" class="form-control bg-transparent text-white mb-2" value="<?php echo h($results['substack_url'] ?? '') ?>" />
            <div class="row text-secondary">La dirección de la publicación, por ejemplo https://espacioceramica.substack.com. También sirve el perfil con arroba (https://substack.com/@espacioceramica). El formulario de suscripción se muestra dentro de la página.</div>
          </div>
        </div>
        <div class="row">
          <div class="col col-12">
            <button type="button" class="rounded-2 btn btn-primary mybtn" onClick="javascript:document.form.submit();">Guardar</button>
          </div>
        </div>
        <input type="hidden" name="form" value="1">
      </form>
    </section>
    <?php require("includes/footer.php"); ?>
  </main>
</body>

</html>
