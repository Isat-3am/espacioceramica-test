-- Tablas de residencias. Ejecutar en phpMyAdmin sobre c2821514_ec_test (primero), despues sobre la base real.
-- Mismo estilo del resto del esquema: InnoDB, utf8mb4_unicode_ci, sin FOREIGN KEY.

CREATE TABLE `residencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text COLLATE utf8mb4_unicode_ci,
  `text_en` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `llegada` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `llegada_en` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `duracion` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `duracion_en` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lugar` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lugar_en` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cupos` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cupos_en` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `price_ars` decimal(10,2) NOT NULL DEFAULT 0,
  `price_usd` decimal(10,2) NOT NULL DEFAULT 0,
  `pago` text COLLATE utf8mb4_unicode_ci,
  `pago_en` text COLLATE utf8mb4_unicode_ci,
  `incluye` text COLLATE utf8mb4_unicode_ci,
  `incluye_en` text COLLATE utf8mb4_unicode_ci,
  `no_incluye` text COLLATE utf8mb4_unicode_ci,
  `no_incluye_en` text COLLATE utf8mb4_unicode_ci,
  `condiciones` text COLLATE utf8mb4_unicode_ci,
  `condiciones_en` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `form_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `top` int NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `active_fecha` (`active`, `fecha_inicio`),
  KEY `slug` (`slug`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `residencia_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `residencia_id` int NOT NULL,
  `image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `top` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `residencia_id` (`residencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
