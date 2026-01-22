-- Script SQL para crear la tabla email_logs directamente
-- Ejecutar este script en phpMyAdmin o en la terminal MySQL

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `to` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `provider` enum('smtp','zoho') NOT NULL DEFAULT 'smtp',
  `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_type` varchar(255) DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_test` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_logs_status_created_at_index` (`status`,`created_at`),
  KEY `email_logs_user_id_index` (`user_id`),
  KEY `email_logs_related_type_related_id_index` (`related_type`,`related_id`),
  CONSTRAINT `email_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
