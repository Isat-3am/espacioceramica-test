-- Agrega el id de pago de Mercado Pago a los pedidos (evita procesar dos veces el mismo pago).
-- Ejecutar en phpMyAdmin sobre c2821514_ec_test (primero), despues sobre la base real.
-- Los pedidos que no vienen de Mercado Pago dejan payment_id en NULL (el indice UNIQUE permite varios NULL).

ALTER TABLE `orders`
  ADD COLUMN `payment_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD UNIQUE KEY `payment_id` (`payment_id`);
