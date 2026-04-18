/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-viniciusbretasavezani@gmail.com|177.190.68.79', 'i:1;', 1771507789),
	('laravel-cache-viniciusbretasavezani@gmail.com|177.190.68.79:timer', 'i:1771507789;', 1771507789);

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `certificate_code` varchar(50) NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `verification_url` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `status` enum('valid','revoked') NOT NULL DEFAULT 'valid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificates_certificate_code_unique` (`certificate_code`),
  KEY `certificates_student_id_foreign` (`student_id`),
  KEY `certificates_course_id_foreign` (`course_id`),
  KEY `certificates_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `certificates_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `certificates` (`id`, `certificate_code`, `student_id`, `course_id`, `instructor_id`, `issue_date`, `start_date`, `end_date`, `verification_url`, `qr_code_path`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'CRT-4W36NMME', 1, 1, 1, '2026-01-29', '2025-12-18', '2026-01-29', NULL, NULL, 'valid', '2026-01-30 05:11:48', '2026-01-30 05:11:48'),
	(2, 'CRT-DE6YMDC5', 2, 1, 1, '2026-02-01', '2025-11-05', '2026-02-01', NULL, NULL, 'valid', '2026-02-01 17:20:10', '2026-02-01 17:20:10'),
	(3, 'CRT-NRBXS3F3', 1, 1, 2, '2026-02-07', '2026-01-02', '2026-02-27', NULL, NULL, 'valid', '2026-02-07 16:02:40', '2026-02-07 16:02:40'),
	(4, 'CRT-B22WV3WB', 2, 1, 2, '2026-03-13', NULL, NULL, NULL, NULL, 'valid', '2026-03-13 19:50:50', '2026-03-13 19:50:50'),
	(5, 'CRT-OI4212C2', 2, 1, 2, '2026-03-13', '2026-03-13', '2026-03-13', NULL, NULL, 'valid', '2026-03-13 20:01:49', '2026-03-13 20:01:49');

CREATE TABLE IF NOT EXISTS `certificate_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `frame_color` varchar(255) NOT NULL DEFAULT '#1f2937',
  `border_width` varchar(255) NOT NULL DEFAULT '8px',
  `font_family` varchar(255) NOT NULL DEFAULT '''Georgia'', ''Times New Roman'', serif',
  `title` varchar(255) NOT NULL DEFAULT 'Certificate of Completion',
  `signature_max_width` varchar(255) NOT NULL DEFAULT '220px',
  `watermark_opacity` decimal(3,2) NOT NULL DEFAULT 0.06,
  `custom_css` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `certificate_settings` (`id`, `frame_color`, `border_width`, `font_family`, `title`, `signature_max_width`, `watermark_opacity`, `custom_css`, `created_at`, `updated_at`) VALUES
	(1, '#691b1b', '18px', '\'Georgia\', \'Times New Roman\', serif', 'Certificado de conclusão', '220px', 0.83, NULL, '2026-02-13 14:41:32', '2026-02-23 19:26:35');

CREATE TABLE IF NOT EXISTS `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `workload_hours` int(11) NOT NULL,
  `modality` enum('online','in_person','hybrid') NOT NULL DEFAULT 'online',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `courses` (`id`, `title`, `description`, `workload_hours`, `modality`, `created_at`, `updated_at`) VALUES
	(1, 'A1-A2 English Grammar', NULL, 20, 'online', '2026-01-30 04:57:35', '2026-01-30 04:57:35');

CREATE TABLE IF NOT EXISTS `course_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `instructor_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_classes_course_id_name_unique` (`course_id`,`name`),
  KEY `course_classes_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `course_classes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_classes_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `course_classes` (`id`, `course_id`, `instructor_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 1, 2, 'English A1 A2 A', NULL, '2026-03-13 18:40:46', '2026-03-13 19:49:01');

CREATE TABLE IF NOT EXISTS `course_class_attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_class_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `duration_hours` decimal(8,2) NOT NULL DEFAULT 1.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_class_attendances_course_class_id_foreign` (`course_class_id`),
  CONSTRAINT `course_class_attendances_course_class_id_foreign` FOREIGN KEY (`course_class_id`) REFERENCES `course_classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `course_class_attendances` (`id`, `course_class_id`, `name`, `attendance_date`, `duration_hours`, `created_at`, `updated_at`) VALUES
	(1, 1, 'English A1 A2 A 1', '2026-03-16', 1.00, '2026-03-16 14:59:13', '2026-03-16 14:59:13'),
	(2, 1, 'Aula 2', '2026-03-16', 1.00, '2026-03-16 15:50:02', '2026-03-16 15:50:02');

CREATE TABLE IF NOT EXISTS `course_class_attendance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_class_attendance_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `course_class_attendance_records` (`id`, `course_class_attendance_id`, `student_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '2026-03-16 14:59:13', '2026-03-16 14:59:13'),
	(4, 2, 1, '2026-03-16 15:50:02', '2026-03-16 15:50:02'),
	(5, 2, 2, '2026-03-16 15:50:02', '2026-03-16 15:50:02');

CREATE TABLE IF NOT EXISTS `course_enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `course_class_id` bigint(20) unsigned DEFAULT NULL,
  `progress_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `grade` decimal(5,2) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_enrollments_student_id_course_id_unique` (`student_id`,`course_id`),
  KEY `course_enrollments_course_class_id_foreign` (`course_class_id`),
  KEY `course_enrollments_course_id_course_class_id_index` (`course_id`,`course_class_id`),
  CONSTRAINT `course_enrollments_course_class_id_foreign` FOREIGN KEY (`course_class_id`) REFERENCES `course_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `course_enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `course_enrollments` (`id`, `student_id`, `course_id`, `course_class_id`, `progress_hours`, `grade`, `completed`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 2.00, 10.00, 0, '2026-03-13 18:41:30', '2026-03-16 15:50:02'),
	(2, 2, 1, 1, 1.00, 0.01, 0, '2026-03-13 18:43:20', '2026-03-16 15:50:02');

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `instructors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `cpf_cnpj` varchar(30) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `instructors` (`id`, `full_name`, `email`, `cpf_cnpj`, `signature_image`, `created_at`, `updated_at`) VALUES
	(1, 'Luzia', 'luzia@gmail.com', '123.456.789-01', NULL, '2026-01-30 04:54:10', '2026-01-30 04:54:18'),
	(2, 'Vitor Prateate', 'vit@gmail.com', '1872638716298', NULL, '2026-02-07 15:59:55', '2026-02-07 15:59:55');

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_01_30_010012_create_students_table', 2),
	(5, '2026_01_30_010014_create_courses_table', 2),
	(6, '2026_01_30_010018_create_instructors_table', 2),
	(7, '2026_01_30_010022_create_certificates_table', 2),
	(8, '2026_01_30_010026_create_course_enrollments_table', 2),
	(9, '2026_01_30_010031_create_verification_logs_table', 2),
	(10, '2026_01_30_011248_create_personal_access_tokens_table', 3),
	(11, '2026_02_13_000000_create_certificate_settings_table', 4),
	(12, '2026_02_13_000001_add_custom_css_to_certificate_settings_table', 5),
	(13, '2026_02_19_000001_create_permission_tables', 6),
	(14, '2026_02_24_000000_drop_background_image_url_from_certificate_settings_table', 7),
	(15, '2026_03_13_000000_create_course_classes_table', 7),
	(16, '2026_03_13_000001_add_course_class_id_to_course_enrollments_table', 7),
	(17, '2026_03_13_000002_add_instructor_id_to_course_classes_table', 8),
	(18, '2026_03_16_000000_replace_progress_percent_with_progress_hours_in_course_enrollments_table', 9),
	(19, '2026_03_16_000001_create_course_class_attendances_table', 9);

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 1),
	(1, 'App\\Models\\User', 2);

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'web', '2026-02-19 16:27:56', '2026-02-19 16:27:56');

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('sHiwbfMwvYKAfd8y4XYwCa0tRJUR1gmOOm4unKo5', 1, '177.190.68.79', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoia0Z6QnZMR1BCTTlhVWVsSWpjeXA2OW9CekUxR3d5MGk4ZVlDTnBaayI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo3NjoiaHR0cDovLzE3Ny4xOTAuNjguNzk6MzAwMC9DZXJ0aURpZ2l0YWwvcHVibGljL2NvdXJzZS1jbGFzc2VzLzEvZW5yb2xsbWVudHMvMiI7czo1OiJyb3V0ZSI7czoyOToiY291cnNlLWNsYXNzLWVucm9sbG1lbnRzLnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1773666151);

CREATE TABLE IF NOT EXISTS `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `document_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `students` (`id`, `full_name`, `email`, `document_id`, `created_at`, `updated_at`) VALUES
	(1, 'Vinicius Bretas', 'cosmoskitsune@hotmail.com', NULL, '2026-01-30 04:55:58', '2026-01-30 04:55:58'),
	(2, 'Arianna Pinto', 'luzialeal76@gmail.com', NULL, '2026-02-01 17:19:21', '2026-02-01 17:19:21'),
	(3, 'Vitor Prateate', 'ddd@d.c', NULL, '2026-02-19 17:12:10', '2026-02-19 17:12:10');

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'cosmosgc', 'cosmoskitsune@hotmail.com', NULL, '$2y$12$.S8YX8IvwSXYPVevM5z98ufSvxj6/cl1whgh6lTVROs1nfzllGd1C', 'r7vG6eL4zZAIa1xJvqvKyz8HDtuOasN9RZJo9oxsp5kbYZOLjMclDKOcZjei', '2026-01-30 04:24:26', '2026-01-30 04:24:26'),
	(2, 'Test User', 'test@example.com', '2026-02-19 16:27:56', '$2y$12$LxQKLGZUu9vFI9BN9ONfeOmSV7ttSzDFg28kddBrELU7sbalDAcly', 'Hi2q2VvGC2nRw7Qad7nitq4agpyK2Rg2dOT1IktxYTqFsVWVVv1J7UQvRcf0', '2026-02-19 16:27:56', '2026-02-19 16:27:56');

CREATE TABLE IF NOT EXISTS `verification_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `certificate_id` bigint(20) unsigned NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `verification_logs_certificate_id_foreign` (`certificate_id`),
  CONSTRAINT `verification_logs_certificate_id_foreign` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
