-- Tabla de talleres. Ejecutar en phpMyAdmin sobre c2821514_ec_test (primero), despues sobre la base real.
-- Mismo estilo del resto del esquema: InnoDB, utf8mb4_unicode_ci, sin FOREIGN KEY.

CREATE TABLE `talleres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text COLLATE utf8mb4_unicode_ci,
  `text_en` text COLLATE utf8mb4_unicode_ci,
  `fecha` date DEFAULT NULL,
  `horario` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cupos` int NOT NULL DEFAULT 0,
  `price_ars` decimal(10,2) NOT NULL DEFAULT 0,
  `price_usd` decimal(10,2) NOT NULL DEFAULT 0,
  `image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `form_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `top` int NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `active_fecha` (`active`, `fecha`),
  KEY `slug` (`slug`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
