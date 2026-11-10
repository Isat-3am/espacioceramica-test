-- Tabla de la seccion Newsletter (un solo registro, id = 1).
-- No hace falta ejecutarla a mano: el panel newsletter.php la crea y carga los textos por defecto
-- la primera vez que se abre. Se deja como referencia.

CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int NOT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title_en` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text COLLATE utf8mb4_unicode_ci,
  `text_en` text COLLATE utf8mb4_unicode_ci,
  `substack_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
