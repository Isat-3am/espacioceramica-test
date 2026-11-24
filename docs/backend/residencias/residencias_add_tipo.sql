-- Tipo de inscripcion: 'experiencia' (Experiencias formativas) o 'residencia'.
-- Para una tabla `residencias` que ya existe (sin la columna). El panel residencias.php tambien la agrega sola
-- al abrirse, asi que este archivo es opcional. Lo que ya estaba cargado queda como 'residencia'.
ALTER TABLE `residencias`
  ADD COLUMN `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'residencia' AFTER `form_url`;
