-- Minimal login-focused schema for the restaurante database
-- Compatible with MariaDB 10.4+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `restaurante` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `restaurante`;

DROP TABLE IF EXISTS `login_audit`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `users_login`;

CREATE TABLE `users_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_login_username` (`username`),
  UNIQUE KEY `uq_users_login_email` (`email`),
  CONSTRAINT `chk_users_login_role` CHECK (`role` BETWEEN 1 AND 3),
  CONSTRAINT `chk_users_login_failed_attempts` CHECK (`failed_attempts` <= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `login_audit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `success` tinyint(1) NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_login_audit_user_id` (`user_id`),
  CONSTRAINT `fk_login_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users_login` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pedidos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `valor` decimal(10,2) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users_login` (`username`, `email`, `password_hash`, `role`, `is_active`) VALUES
('marcos', 'marcos@email.com', '$2y$10$I/9rEVM6N4qMPgN.2BifO.ROT3qWpQOJsl3BYxoOG0uiJuTt1XH9y', 1, 1),
('renata', 'renata@email.com', '$2y$10$Y0YyYI3MC.eQtyV4A5YAqeAyojA.Y46hoArjhubhFiiS0Xrt.KqY.', 2, 1),
('lucas', 'lucas@email.com', '$2y$10$AGzfi4rkW2Z9b2h/NXbqGeKwwtdpgqeoBwYyQspKWaNGS.A6I2AQq', 3, 1);

COMMIT;
