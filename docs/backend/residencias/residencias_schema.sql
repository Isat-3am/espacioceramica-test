-- Tabla de residencias (version minima: titulo y link del formulario).
-- Ejecutar en phpMyAdmin sobre la base real cuando se publique (en test ya existe la version completa,
-- que tambien sirve: la version minima solo usa estas columnas).
-- Version completa en pausa: ../residencias-pausado/residencias_schema_completo.sql

CREATE TABLE `residencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `top` int NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `slug` (`slug`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
