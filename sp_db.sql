-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for eduadapt_db
CREATE DATABASE IF NOT EXISTS `eduadapt_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `eduadapt_db`;

-- Dumping structure for table eduadapt_db.admin_profiles
CREATE TABLE IF NOT EXISTS `admin_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `admin_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.admin_profiles: ~0 rows (approximately)
INSERT IGNORE INTO `admin_profiles` (`id`, `user_id`, `full_name`, `email`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Admin User', 'admin@eduadapt.edu.ph', '2026-07-24 07:07:40', '2026-07-24 07:07:40');

-- Dumping structure for table eduadapt_db.classes
CREATE TABLE IF NOT EXISTS `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned NOT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classes_school_year_id_grade_level_section_name_unique` (`school_year_id`,`grade_level`,`section_name`),
  CONSTRAINT `classes_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.classes: ~3 rows (approximately)
INSERT IGNORE INTO `classes` (`id`, `school_year_id`, `grade_level`, `section_name`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Grade 5', 'DIAMOND', '2026-07-25 08:29:12', '2026-07-25 08:29:12'),
	(2, 1, 'Grade 5', 'JADE', '2026-07-25 21:03:49', '2026-07-25 21:03:49'),
	(3, 1, 'Grade 6', 'MARS', '2026-07-25 21:04:10', '2026-07-25 21:04:10'),
	(4, 1, 'Grade 6', 'VENUS', '2026-07-25 21:04:25', '2026-07-25 21:04:25');

-- Dumping structure for table eduadapt_db.content_items
CREATE TABLE IF NOT EXISTS `content_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('learning_material') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'learning_material',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_items_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `content_items_subject_id_foreign` (`subject_id`),
  CONSTRAINT `content_items_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `content_items_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.content_items: ~0 rows (approximately)
INSERT IGNORE INTO `content_items` (`id`, `teacher_profile_id`, `subject_id`, `grade_level`, `term`, `week`, `type`, `title`, `description`, `file_path`, `created_at`, `updated_at`) VALUES
	(1, 2, 2, 'Grade 5', 'Term 1', 'Week 1', 'learning_material', 'dfjdfj', 'dfjdj', 'content/2/Grade 5/Term 1/Science/Week 1/dfjdfj-1785166398.pdf', '2026-07-27 07:33:19', '2026-07-27 07:33:19'),
	(3, 2, 2, 'Grade 5', 'Term 1', 'Week 1', 'learning_material', 'sample again', 'kjdhsgukshdghkdhgss', 'learning_materials/2/Grade 5/Term 1/Science/Week 1/copy reading and headline writing filipino.pdf', '2026-08-24 05:32:45', '2026-08-24 05:32:45');

-- Dumping structure for table eduadapt_db.content_releases
CREATE TABLE IF NOT EXISTS `content_releases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `release_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_releases_content_type_content_id_class_id_unique` (`content_type`,`content_id`,`class_id`),
  KEY `content_releases_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `content_releases_class_id_foreign` (`class_id`),
  KEY `content_releases_subject_id_foreign` (`subject_id`),
  CONSTRAINT `content_releases_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_releases_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_releases_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.content_releases: ~6 rows (approximately)
INSERT IGNORE INTO `content_releases` (`id`, `teacher_profile_id`, `content_type`, `content_id`, `class_id`, `release_date`, `due_date`, `created_at`, `updated_at`, `subject_id`) VALUES
	(9, 2, 'learningMaterial', 3, 1, '2026-09-05 20:48:00', '2026-09-07 20:48:00', '2026-09-05 04:48:38', '2026-09-05 04:48:38', 2),
	(10, 2, 'learningMaterial', 1, 1, '2026-09-05 20:48:00', '2026-09-07 20:48:00', '2026-09-05 04:48:52', '2026-09-05 04:48:52', 2),
	(11, 2, 'preAssessment', 5, 1, '2026-09-06 20:49:00', '2026-09-10 20:49:00', '2026-09-05 04:49:04', '2026-09-05 04:49:04', 2),
	(12, 2, 'postAssessment', 3, 1, '2026-09-05 22:17:00', '2026-09-12 22:17:00', '2026-09-05 06:17:48', '2026-09-05 06:17:48', 2),
	(13, 2, 'interventionVideo', 5, 1, '2026-09-05 22:38:00', '2026-09-13 22:38:00', '2026-09-05 06:38:43', '2026-09-05 06:38:43', 2),
	(14, 2, 'interventionMaterial', 12, 1, '2026-09-05 22:38:00', '2026-09-07 22:38:00', '2026-09-05 06:38:58', '2026-09-05 06:38:58', 2),
	(15, 2, 'interventionQuiz', 2, 1, '2026-09-05 22:39:00', '2026-09-07 22:39:00', '2026-09-05 06:39:10', '2026-09-05 06:39:10', 2);

-- Dumping structure for table eduadapt_db.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table eduadapt_db.intervention_materials
CREATE TABLE IF NOT EXISTS `intervention_materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` enum('basic','standard','advanced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intervention_materials_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `intervention_materials_subject_id_foreign` (`subject_id`),
  KEY `intervention_materials_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `intervention_materials_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intervention_materials_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intervention_materials_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.intervention_materials: ~1 rows (approximately)
INSERT IGNORE INTO `intervention_materials` (`id`, `teacher_profile_id`, `subject_id`, `school_year_id`, `grade_level`, `term`, `week`, `level`, `file_name`, `file_path`, `created_at`, `updated_at`) VALUES
	(12, 2, 2, 1, 'Grade 5', 'Term 1', 'Week 1', 'basic', 'EO_031_2026-SUSPENSION_MAYMAY.pdf', NULL, '2026-08-11 06:38:36', '2026-08-11 06:38:36');

-- Dumping structure for table eduadapt_db.intervention_quizzes
CREATE TABLE IF NOT EXISTS `intervention_quizzes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` enum('basic','standard','advanced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_type` enum('multipleChoice','trueFalse','matchingType','mixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `input_method` enum('upload','manual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `questions` json NOT NULL,
  `settings` json DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intervention_quizzes_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `intervention_quizzes_subject_id_foreign` (`subject_id`),
  KEY `intervention_quizzes_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `intervention_quizzes_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intervention_quizzes_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intervention_quizzes_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.intervention_quizzes: ~0 rows (approximately)
INSERT IGNORE INTO `intervention_quizzes` (`id`, `teacher_profile_id`, `subject_id`, `school_year_id`, `grade_level`, `term`, `week`, `level`, `exam_type`, `input_method`, `questions`, `settings`, `file_name`, `created_at`, `updated_at`) VALUES
	(2, 2, 2, 1, 'Grade 5', 'Term 1', 'Week 1', 'basic', 'mixed', 'manual', '[{"type": "multipleChoice", "choices": ["et", "wet", "wetwe", "wse"], "question": "ergt", "correctAnswer": "wetwe"}, {"type": "trueFalse", "choices": ["True", "False"], "question": "wet", "correctAnswer": "True"}, {"type": "matchingType", "pairs": [{"answer": "safasf", "question": "ksdhj"}], "question": "wetw"}]', '{"timer": "10:10:10", "shuffle_choices": false, "shuffle_questions": false}', NULL, '2026-08-11 06:38:38', '2026-08-11 06:38:38');

-- Dumping structure for table eduadapt_db.intervention_videos
CREATE TABLE IF NOT EXISTS `intervention_videos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` enum('basic','standard','advanced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sequence` int unsigned NOT NULL DEFAULT '0',
  `video_type` enum('link','file') COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intervention_videos_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `intervention_videos_subject_id_foreign` (`subject_id`),
  KEY `intervention_videos_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `intervention_videos_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intervention_videos_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intervention_videos_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.intervention_videos: ~1 rows (approximately)
INSERT IGNORE INTO `intervention_videos` (`id`, `teacher_profile_id`, `subject_id`, `school_year_id`, `grade_level`, `term`, `week`, `level`, `sequence`, `video_type`, `video_url`, `file_name`, `file_path`, `created_at`, `updated_at`) VALUES
	(5, 2, 2, 1, 'Grade 5', 'Term 1', 'Week 1', 'basic', 1, 'link', 'https://youtu.be/v1m3d_y5D6M?si=fySZIzaw728bii7B', NULL, NULL, '2026-08-11 06:38:37', '2026-08-11 06:38:37');

-- Dumping structure for table eduadapt_db.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.migrations: ~18 rows (approximately)
INSERT IGNORE INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_resets_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2026_07_24_144712_create_admin_profiles_table', 2),
	(6, '2026_07_24_144730_create_student_profiles_table', 2),
	(7, '2026_07_24_144746_create_teacher_profiles_table', 2),
	(8, '2026_07_24_145123_create_user_login_table', 3),
	(9, '2026_07_25_145451_create_student_class_records_table', 4),
	(10, '2026_07_25_145555_add_missing_fields_to_student_profiles_table', 4),
	(11, '2026_07_25_151157_fix_employee_id_in_teacher_profiles', 5),
	(12, '2026_07_25_152057_create_school_years_table', 6),
	(13, '2026_07_25_154506_create_section_names', 7),
	(14, '2026_07_25_154604_create_teacher_class_assignments', 8),
	(15, '2026_07_25_163332_create_student_class', 9),
	(16, '2026_07_26_053215_fix_student_profiles_table', 10),
	(17, '2026_07_26_130655_create_subjects_table', 11),
	(18, '2026_07_26_130923_create_subjects_table_again', 12),
	(19, '2026_07_27_145802_create_content_items_table', 13),
	(20, '2026_07_28_135556_create_pre_assessments_table', 14),
	(21, '2026_07_28_140735_create_post_assessments_table', 15),
	(22, '2026_07_29_133943_add_school_year_id_to_pre_assessments', 16),
	(23, '2026_08_10_133254_update_assessment_table', 17),
	(24, '2026_08_11_134425_create_intervention_materials', 18),
	(25, '2026_08_11_134432_create_intervention_videos', 18),
	(26, '2026_08_11_134438_create_intervention_quizzes', 18),
	(27, '2026_08_18_131830_create_student_answers_table', 19),
	(28, '2026_08_25_085204_create_content_releases_table', 20),
	(29, '2026_09_01_145253_add_materials_violations_to_student_class_records', 21),
	(30, '2026_09_02_133252_add_subject_id_to_content_releases', 22);

-- Dumping structure for table eduadapt_db.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.password_resets: ~0 rows (approximately)

-- Dumping structure for table eduadapt_db.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.personal_access_tokens: ~0 rows (approximately)

-- Dumping structure for table eduadapt_db.post_assessments
CREATE TABLE IF NOT EXISTS `post_assessments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned DEFAULT NULL,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_type` enum('multipleChoice','trueFalse','matchingType','mixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `input_method` enum('upload','manual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `questions` json NOT NULL,
  `settings` json DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_assessments_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `post_assessments_subject_id_foreign` (`subject_id`),
  KEY `fk_post_assessments_school_year_id` (`school_year_id`),
  CONSTRAINT `fk_post_assessments_school_year_id` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_assessments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `post_assessments_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.post_assessments: ~0 rows (approximately)
INSERT IGNORE INTO `post_assessments` (`id`, `school_year_id`, `teacher_profile_id`, `subject_id`, `grade_level`, `term`, `week`, `exam_type`, `input_method`, `questions`, `settings`, `file_name`, `created_at`, `updated_at`) VALUES
	(3, 1, 2, 2, 'Grade 5', 'Term 1', 'Week 1', 'multipleChoice', 'manual', '[{"type": "multipleChoice", "image": null, "choices": ["sa", "sam", "samp", "sampl"], "question": "sample", "choiceImages": [], "correctAnswer": "samp"}]', '{"timer": "00:10:00", "shuffle_choices": false, "shuffle_questions": true}', NULL, '2026-09-05 05:47:09', '2026-09-05 06:11:34');

-- Dumping structure for table eduadapt_db.pre_assessments
CREATE TABLE IF NOT EXISTS `pre_assessments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned DEFAULT NULL,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_type` enum('multipleChoice','trueFalse','matchingType','mixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `input_method` enum('upload','manual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `questions` json NOT NULL,
  `settings` json DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pre_assessments_teacher_profile_id_foreign` (`teacher_profile_id`),
  KEY `pre_assessments_subject_id_foreign` (`subject_id`),
  KEY `pre_assessments_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `pre_assessments_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pre_assessments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `pre_assessments_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.pre_assessments: ~0 rows (approximately)
INSERT IGNORE INTO `pre_assessments` (`id`, `school_year_id`, `teacher_profile_id`, `subject_id`, `grade_level`, `term`, `week`, `exam_type`, `input_method`, `questions`, `settings`, `file_name`, `created_at`, `updated_at`) VALUES
	(5, 1, 2, 2, 'Grade 5', 'Term 1', 'Week 1', 'mixed', 'manual', '[{"type": "multipleChoice", "choices": ["sfas", "asf", "asf", "asf"], "question": "question1", "correctAnswer": "sfas"}, {"type": "trueFalse", "choices": ["True", "False"], "question": "safasf", "correctAnswer": "True"}, {"type": "matchingType", "pairs": [{"answer": "asf", "question": "asf"}], "question": "safasf"}, {"type": "trueFalse", "choices": ["True", "False"], "question": "sample question1", "correctAnswer": "True"}]', '{"timer": "00:10:00", "status": "updating", "due_date": "2026-08-11", "exam_type": "mixed", "shuffle_choices": false, "shuffle_questions": true}', NULL, '2026-08-10 06:31:03', '2026-08-10 06:44:40');

-- Dumping structure for table eduadapt_db.school_years
CREATE TABLE IF NOT EXISTS `school_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.school_years: ~2 rows (approximately)
INSERT IGNORE INTO `school_years` (`id`, `year`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, '2026-2027', 1, '2026-07-25 07:27:32', '2026-07-25 07:27:36'),
	(2, '2025-2026', 0, '2026-07-25 07:28:59', '2026-07-25 07:28:59');

-- Dumping structure for table eduadapt_db.student_answers
CREATE TABLE IF NOT EXISTS `student_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned NOT NULL,
  `exam_type` enum('pre','post','intervention') COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answers` json DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_answers_school_year_id_foreign` (`school_year_id`),
  KEY `student_answers_student_id_foreign` (`student_id`),
  KEY `student_answers_class_id_foreign` (`class_id`),
  KEY `student_answers_subject_id_foreign` (`subject_id`),
  CONSTRAINT `student_answers_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.student_answers: ~0 rows (approximately)

-- Dumping structure for table eduadapt_db.student_class_records
CREATE TABLE IF NOT EXISTS `student_class_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `materials_locked` tinyint(1) NOT NULL DEFAULT '0',
  `intervention_materials_locked` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_class_records_student_profile_id_class_id_unique` (`student_profile_id`,`class_id`),
  KEY `student_class_records_class_id_foreign` (`class_id`),
  CONSTRAINT `student_class_records_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_class_records_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.student_class_records: ~39 rows (approximately)
INSERT IGNORE INTO `student_class_records` (`id`, `student_profile_id`, `class_id`, `materials_locked`, `intervention_materials_locked`, `created_at`, `updated_at`) VALUES
	(42, 47, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(43, 48, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(44, 49, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(45, 50, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(46, 51, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(47, 52, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(48, 53, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(49, 54, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(50, 55, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(51, 56, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(52, 57, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(53, 58, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(54, 59, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(55, 60, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(56, 61, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(57, 62, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(58, 63, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(59, 64, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(60, 65, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(61, 66, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(62, 67, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(63, 68, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(64, 69, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(65, 70, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(66, 71, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(67, 72, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(68, 73, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(69, 74, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(70, 75, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(71, 76, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(72, 77, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(73, 78, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(74, 79, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(75, 80, 1, 0, 0, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(76, 81, 1, 0, 0, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(77, 82, 1, 0, 0, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(78, 83, 1, 0, 0, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(79, 84, 1, 0, 0, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(80, 85, 1, 0, 0, '2026-07-25 22:20:59', '2026-09-01 09:52:07');

-- Dumping structure for table eduadapt_db.student_content_progress
CREATE TABLE IF NOT EXISTS `student_content_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint unsigned NOT NULL,
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Fully qualified class name, e.g. App\\Models\\ContentItem',
  `content_id` bigint unsigned NOT NULL,
  `status` enum('pending','viewed','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `score` int unsigned DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `video_path` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_content_progress_unique` (`student_profile_id`,`content_type`,`content_id`),
  KEY `student_content_progress_student_profile_id_foreign` (`student_profile_id`),
  CONSTRAINT `student_content_progress_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.student_content_progress: ~8 rows (approximately)
INSERT IGNORE INTO `student_content_progress` (`id`, `student_profile_id`, `content_type`, `content_id`, `status`, `score`, `answers`, `started_at`, `completed_at`, `created_at`, `updated_at`, `video_path`) VALUES
	(22, 85, 'App\\Models\\ContentItem', 1, 'completed', NULL, NULL, NULL, '2026-09-05 07:39:09', '2026-09-05 07:39:06', '2026-09-05 07:39:09', NULL),
	(23, 85, 'App\\Models\\ContentItem', 3, 'completed', NULL, NULL, NULL, '2026-09-05 07:39:09', '2026-09-05 07:39:07', '2026-09-05 07:39:09', NULL),
	(24, 85, 'App\\Models\\PreAssessment', 5, 'completed', 2, '["sfas", null, ["asf"], null]', NULL, '2026-09-05 07:39:20', '2026-09-05 07:39:20', '2026-09-05 07:39:20', NULL),
	(25, 85, 'App\\Models\\PostAssessment', 3, 'completed', 0, '["sam"]', NULL, '2026-09-05 07:39:27', '2026-09-05 07:39:27', '2026-09-05 07:39:27', NULL),
	(27, 85, 'App\\Models\\InterventionVideo', 5, 'completed', NULL, NULL, NULL, '2026-09-05 07:58:27', '2026-09-05 07:39:45', '2026-09-05 07:58:27', NULL),
	(28, 85, 'App\\Models\\InterventionMaterial', 12, 'completed', NULL, NULL, NULL, '2026-09-05 07:58:27', '2026-09-05 07:58:27', '2026-09-05 07:58:27', NULL),
	(29, 85, 'App\\Models\\InterventionQuiz', 2, 'completed', 1, '["et", "False", ["safasf"]]', NULL, '2026-09-05 07:58:41', '2026-09-05 07:58:41', '2026-09-05 07:58:41', NULL);

-- Dumping structure for table eduadapt_db.student_profiles
CREATE TABLE IF NOT EXISTS `student_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `lrn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `sex` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_tongue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_ethnic_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_house` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_barangay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_municipality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_maiden_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_relationship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `learning_modality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `contact_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `student_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.student_profiles: ~39 rows (approximately)
INSERT IGNORE INTO `student_profiles` (`id`, `user_id`, `lrn`, `profile_picture`, `first_name`, `middle_name`, `last_name`, `suffix_name`, `birth_date`, `sex`, `mother_tongue`, `ip_ethnic_group`, `religion`, `address_house`, `address_barangay`, `address_municipality`, `address_province`, `father_name`, `mother_maiden_name`, `guardian_name`, `guardian_relationship`, `contact_number`, `learning_modality`, `remarks`, `contact_no`, `address`, `created_at`, `updated_at`) VALUES
	(47, 12, '103871210023', NULL, 'JUAN MIGUEL', 'AGGABAO', 'ARIBBAY', NULL, '2016-07-05', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'ARIBBAY, JAYSON PABLO PAGULAYAN', 'AGGABAO,JOYLYN,MALSI,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(48, 13, '103871210024', NULL, 'AUSTIN AERRIEL', 'SALAS', 'ARIOLA', NULL, '2016-06-07', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'ARIOLA, ARIEL ZIPAGAN', 'SALAS,REA,GUMARU,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(49, 14, '103871210058', NULL, 'TITUS ALEXUS', 'PACCARANGAN', 'BARCOMA', NULL, '2016-04-04', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'BARCOMA, ESTEBAN ABLES', 'PACCARANGAN,JUDILYN IVY,CASITA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(50, 15, '103871210014', NULL, 'ZEE JEI', 'GUMARU', 'BAUTISTA', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'BAUTISTA, ZIMARS BULAUAN', 'GUMARU,JELLY ROSE,BANGUG,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(51, 16, '103871210059', NULL, 'MARK DANIEL', 'PACCARANGAN', 'BUCAD', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'BUCAD, JUANCHO QUIRABU', 'PACCARANGAN,JENYCEL,UMAYAM,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(52, 17, '103871210015', NULL, 'CLAY JHON', 'TANDAYU', 'CABASAG', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'CABASAG, TOMAS CUDAL JR', 'TANDAYU,ANTONETTE,LUMAUIG,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(53, 18, '103871210002', NULL, 'ACCEL', 'MADDAWIN', 'CALDERON', NULL, '2015-03-12', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'CALDERON, JESREEL LAPAT', 'MADDAWIN,CECILLE,SAQUING,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(54, 19, '103871210051', NULL, 'ALEXANDER', '-', 'FERRER', NULL, '2016-01-09', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'INSIGNE, EDMUNDO TOVILLO', 'FERRER,EDERLINA,UBIÑA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(55, 20, '155513210002', NULL, 'PRINCE ANGELLO', 'SORIANO', 'GAZZINGAN', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'SAN RAFAEL ALTO', 'SANTO TOMAS', 'ISABELA', 'GAZZINGAN, ALBERT AGABIN', 'SORIANO,PRINCESS,ASIS,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(56, 21, '103871210075', NULL, 'JOHN MARK', 'GALINGANA', 'GUMARU', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'GUMARU, MARLON CRISOSTOMO', 'GALINGANA,JUDY,SALAS,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(57, 22, '103871210017', NULL, 'AEJHAY', 'SAQUING', 'MADDAWIN', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'MADDAWIN, JEFFREY PALASIGUE', 'SAQUING,ANGELIKA,TURINGAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(58, 23, '103871210052', NULL, 'PRINCE GIDEON', 'GATAN', 'MADDAWIN', NULL, '2016-01-02', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'MADDAWIN, MARCOS GUINGAB', 'GATAN,EMILY,CANCERAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(59, 24, '103871210044', NULL, 'DRUZE JAVIN', 'MORILLO', 'MALABUG', NULL, '2015-04-11', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'MALABUG, DERRIE CHRIS SYJONGTIAN', 'MORILLO,JOVY,BULAUAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(60, 25, '103871210018', NULL, 'PRINCE CYRUS', '-', 'MARAYAG', NULL, '1970-01-01', 'M', 'Tagalog', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BARANGAY 186', 'KALOOKAN CITY', 'NCR   THIRD DISTRICT', NULL, 'MARAYAG,LINA,CABADDU,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(61, 26, '108127210040', NULL, 'MARK JAKE', 'PACCARANGAN', 'REGACHUELO', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'REGACHUELO, MARLON OPEÑA', 'PACCARANGAN,NECY,DUCLAY,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(62, 27, '136638210568', NULL, 'IAN JADE', 'DELA CRUZ', 'REYNANTE', NULL, '2016-12-07', 'M', 'Tagalog', NULL, 'Christianity', NULL, 'BARANGAY 173', 'KALOOKAN CITY', 'NCR   THIRD DISTRICT', 'REYNANTE, ISAIAS LUZON JR', 'DELA CRUZ,MARY JANE,ARIATE,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(63, 28, '103871210066', NULL, 'FRANCISCO AISHA', 'BAUTISTA', 'RUIZ', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'RUIZ, JENESES MONSANTO', 'BAUTISTA,MARLA,BURAGA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(64, 29, '103871210027', NULL, 'PRINCE AIZEN', 'HERNANDEZ', 'SABULARSE', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'SABULARSE, JOHN KENNETH MACHADO', 'HERNANDEZ,ARLYN,LIMBAWAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(65, 30, '103872210005', NULL, 'JAYLORD', 'DANAO', 'SALAS', NULL, '2016-11-07', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'AMMUGAUAN', 'SANTO TOMAS', 'ISABELA', 'SALAS, GILBERT CABASAG', 'DANAO,MARIPERT,ACOSTA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(66, 31, '103871210040', NULL, 'JHON MIGUEL', 'MANAUD', 'TAGAPAN', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'TAGAPAN, ESPEDITO TARUN JR', 'MANAUD,REGINE,CANCERAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(67, 32, '103872210010', NULL, 'KHEN', 'LAGUTAO', 'TAHOYNON', NULL, '1970-01-01', 'M', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'AMMUGAUAN', 'SANTO TOMAS', 'ISABELA', 'TAHOYNON, EDUARDO LAGUTIN', 'LAGUTAO,HERTRUDES,CABASAG,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(68, 33, '103871210019', NULL, 'NATHALIE ROSE', 'ANGELES', 'BUCAD', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'BUCAD, JACKSON GUMARU', 'ANGELES,ABIGAIL,AMARO,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(69, 34, '103871210060', NULL, 'DHANIELLA YVONNE', 'MENDOZA', 'CABEL', NULL, '2015-03-12', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'CABEL, RAFFY TELAN', 'MENDOZA,MARICON,PAGULAYAN,', NULL, NULL, NULL, 'Face to Face', 'Pending TI', NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(70, 35, '103871210020', NULL, 'PRECIOUS-ALTHEA', 'FUGABAN', 'CAMMAYO', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'CAMMAYO, ROMEL RAMOS', 'FUGABAN,JUVILYN,SAQUING,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(71, 36, '103871210009', NULL, 'KRISTEL JOYCE', 'PAGULAYAN', 'CATAGGATAN', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'CATAGGATAN, JOEL ULEP', 'PAGULAYAN,MARIA AIZA,MAGAUAY,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(72, 37, '136649210297', NULL, 'MARY LAINE', 'NOYA', 'CUDAL', NULL, '2014-06-11', 'F', 'Tagalog', NULL, 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'CUDAL, MARK ANTHONY BULAN', 'NOYA,ELAINE,ACIBIES,', NULL, NULL, NULL, 'Blended', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(73, 38, '103872210006', NULL, 'JOANA ROSE', 'RAQUIZA', 'DANAO', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'AMMUGAUAN', 'SANTO TOMAS', 'ISABELA', 'DANAO, JOJO ACOSTA', 'RAQUIZA,ROSALYN,FUENTES,', NULL, NULL, NULL, 'Face to Face', 'Pending TI', NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(74, 39, '103871210055', NULL, 'MA. ISABELLE', 'BACCAY', 'DELA CRUZ', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'DELA CRUZ, BONIFACIO CAPILI', 'BACCAY,ANGELINE,SALAS,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(75, 40, '103871210068', NULL, 'JANELLA', 'BACCAY', 'FERNANDEZ', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'FERNANDEZ, JOHNNY TOMAS JR', 'BACCAY,JOAN,PARAGUA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(76, 41, '103871210063', NULL, 'ROSE MARIE', 'DELA FUENTE', 'GUINGAB', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'GUINGAB, MELCHOR CABADDU JR', 'DELA FUENTE,MERCY,DARANTAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(77, 42, '103871210022', NULL, 'CRISTINE-JOY', 'BUCAD', 'MAGAUAY', NULL, '2015-06-11', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'MAGAUAY, JEFERSON CABACCAN', 'BUCAD,CECIL,GUMARU,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(78, 43, '103871210048', NULL, 'PRINCESS KAYE', 'TADONG', 'MALABAD', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'MALABAD, REYMUND ALLAUIGAN', 'TADONG,CRISTINA,TALOSIG,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(79, 44, '103871210042', NULL, 'RUTH MAE', 'BACSA', 'MAMAUAG', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'MAMAUAG, BELRODY SORIANO', 'BACSA,MARVIE,PANAGA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(80, 45, '103871210046', NULL, 'KATHERENE', 'MABBAYAD', 'MANAUD', NULL, '2016-08-08', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'MANAUD, WAREN ALLAUIGAN', 'MABBAYAD,KARLA,BAUI,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:58', '2026-07-25 22:20:58'),
	(81, 46, '103871210031', NULL, 'MARIA JOSEPHINE', 'CEA', 'MANNAG', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'MANNAG, JONEL BALISI', 'CEA,MARINA ISABEL,TALAUE,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(82, 47, '103871210056', NULL, 'JOANA', 'BAUI', 'MASIDDO', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'MASIDDO, JOVANY BULAN', 'BAUI,RUBY ANN,BANGAYAN,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(83, 48, '103871210043', NULL, 'ANGEL', 'TOMAZAR', 'SALAS', NULL, '2015-12-09', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'CENTRO', 'SANTO TOMAS', 'ISABELA', 'SALAS, RONALD BAUI', 'TOMAZAR,YOLINA,CANOY,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(84, 49, '155513210018', NULL, 'BELLA-MAE', 'GALAPON', 'SORIANO', NULL, '2016-06-05', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BARUMBONG', 'SANTO TOMAS', 'ISABELA', 'SORIANO, BERNARD FROGOSO', 'GALAPON,SHIELA,DELICA,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:59', '2026-07-25 22:20:59'),
	(85, 50, '103871210049', 'student_profiles/oQtDKRUIDNjXz8kaRJuPjaXMBHGZBaJskGA7lcXV.jpg', 'ANGEL RIANA', 'DATUL', 'TALOSIG', NULL, '1970-01-01', 'F', 'Ibanag', 'Ibanag / Ybanag / Iabanag', 'Christianity', NULL, 'BOLINAO-CULALABO', 'SANTO TOMAS', 'ISABELA', 'TALOSIG, DANIEL CABAUATAN', 'DATUL,JAY,SORIANO,', NULL, NULL, NULL, 'Face to Face', NULL, NULL, NULL, '2026-07-25 22:20:59', '2026-09-07 05:39:59');

-- Dumping structure for table eduadapt_db.subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_grade_level_name_unique` (`grade_level`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.subjects: ~2 rows (approximately)
INSERT IGNORE INTO `subjects` (`id`, `grade_level`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'Grade 5', 'Mathematics', NULL, NULL, '2026-07-26 05:29:18', '2026-07-26 05:29:18'),
	(2, 'Grade 5', 'Science', NULL, NULL, '2026-07-26 05:29:24', '2026-07-26 05:29:24'),
	(3, 'Grade 6', 'Mathematics', NULL, NULL, '2026-07-29 05:56:24', '2026-07-29 05:56:24');

-- Dumping structure for table eduadapt_db.teacher_class_assignments
CREATE TABLE IF NOT EXISTS `teacher_class_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_class_assignments_teacher_profile_id_class_id_unique` (`teacher_profile_id`,`class_id`),
  KEY `teacher_class_assignments_class_id_foreign` (`class_id`),
  KEY `teacher_class_assignments_subject_id_foreign` (`subject_id`),
  CONSTRAINT `teacher_class_assignments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_assignments_teacher_profile_id_foreign` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.teacher_class_assignments: ~2 rows (approximately)
INSERT IGNORE INTO `teacher_class_assignments` (`id`, `teacher_profile_id`, `class_id`, `subject_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, '2026-07-26 05:29:35', '2026-07-26 05:29:35'),
	(2, 2, 1, 2, '2026-07-26 05:30:30', '2026-07-26 05:30:30'),
	(3, 1, 3, 3, '2026-07-29 05:56:32', '2026-07-29 05:56:32');

-- Dumping structure for table eduadapt_db.teacher_profiles
CREATE TABLE IF NOT EXISTS `teacher_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_profiles_employee_id_unique` (`employee_id`),
  KEY `teacher_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `teacher_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.teacher_profiles: ~3 rows (approximately)
INSERT IGNORE INTO `teacher_profiles` (`id`, `user_id`, `profile_picture`, `employee_id`, `prefix_name`, `first_name`, `middle_name`, `last_name`, `suffix_name`, `contact_no`, `address`, `created_at`, `updated_at`) VALUES
	(1, 2, NULL, 'TCH-001', 'Ms.', 'Grace', 'R.', 'Santos', NULL, '09171234567', '123 Teacher St., Quezon City', '2026-07-24 07:07:40', '2026-07-24 07:07:40'),
	(2, 3, 'profile_pictures/tYHOldc4f91ZHWN8PAtxc2xVFT5IMmRX22wDxduE.png', 'TCH-002', 'Mr.', 'Mark', 'V.', 'Villegas', 'Jr.', '09181234567', '456 Educator Ave., Manila', '2026-07-24 07:07:40', '2026-09-07 05:16:35'),
	(3, 4, NULL, 'TCH-003', 'Dr.', 'Maria', 'L.', 'Cruz', NULL, '09191234567', '789 Academic Rd., Pasig', '2026-07-24 07:07:40', '2026-07-24 07:07:40');

-- Dumping structure for table eduadapt_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `login_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teacher','student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_login_id_unique` (`login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table eduadapt_db.users: ~44 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `login_id`, `password`, `role`, `disabled`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'admin', '$2y$10$KoszL8GxHkq212HUEba8pe35Uj7keJk05XrOv1cuXEoJTv9PBCWOK', 'admin', 0, NULL, '2026-07-24 07:07:40', '2026-07-24 07:07:40'),
	(2, 'TCH-001', '$2y$10$/z6/NRbe8b9335JYwu92leq6NYlfHbS2JyFATFT7cvxFHyEBwMHKe', 'teacher', 0, NULL, '2026-07-24 07:07:40', '2026-07-24 07:07:40'),
	(3, 'TCH-002', '$2y$10$5rvg3FfPvTfCvrKiQn4RXedr4dQZP5gaB.XpgKblJgsFUhKSGD3mm', 'teacher', 0, NULL, '2026-07-24 07:07:40', '2026-07-24 07:07:40'),
	(4, 'TCH-003', '$2y$10$tBu1fYNUVFwcmaSNmhvQ3.Kzfa958G6UHONojBYZjNLWAWFSyWE8O', 'teacher', 0, NULL, '2026-07-24 07:07:40', '2026-07-24 07:07:40'),
	(12, '103871210023', '$2y$10$ltrTCfBKFIedOjoVGyiM7.KA1raq5BwnCETrg5FX.Qtz1lAubPiPG', 'student', 0, NULL, '2026-07-25 21:44:31', '2026-07-25 21:44:31'),
	(13, '103871210024', '$2y$10$dkSvJogpsRw/NAnVVWaFWOvQTu4Crgm5R3BtkwB1yVdsUXMNTrhyG', 'student', 0, NULL, '2026-07-25 21:44:31', '2026-07-25 21:44:31'),
	(14, '103871210058', '$2y$10$Sm7L3hB1vVBTZe/XzAIVS.Tc81juK6pJNzVwRdGtEPuM4Vov0ou/a', 'student', 0, NULL, '2026-07-25 21:44:31', '2026-07-25 21:44:31'),
	(15, '103871210014', '$2y$10$aShSHx75HgOZx1uZ/VC89eTtN4ukCGrXRLE9gyDZ4M.TMR4cs60fS', 'student', 0, NULL, '2026-07-25 21:44:31', '2026-07-25 21:44:31'),
	(16, '103871210059', '$2y$10$bC7.d838OLRvTx8zHfjn8e9HaTrAf1ktjaD3Zy86kgtfDDacokUxq', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(17, '103871210015', '$2y$10$KS6wxnXe4xfG0XzjApL8KepgQ4pdljBnqJeQAJrKCF0C3uqqOwwoq', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(18, '103871210002', '$2y$10$gClnfdG9OxSWvUf1.dngVeY8fuq2ZupZiZw9NBFwxUYBFWWSsGfY6', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(19, '103871210051', '$2y$10$WbCXGno7kq5sQlzTwIuCEeGOL/8Antcsl7wJy6ktkObEcPVvg2exm', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(20, '155513210002', '$2y$10$JLBy9TSku1A1tWANYjtxBeS5B6iFmW3OnwHb2cC5I3K5AztO7h2zq', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(21, '103871210075', '$2y$10$3GNP4jW5Xm3YDdfudhq7C.HnvhO95iD.pdKDFZTRHoX3PI3sprgjq', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(22, '103871210017', '$2y$10$2nDyFuIE4tdKeYS3H7vi/e/DnkH.zHFZ/X5.IfX3wDbbvZFiU0jDm', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(23, '103871210052', '$2y$10$nd3H/y3CPc/jdJJHXXqlgO9w7Rt2mRPUohn7CPJzSxAG0nYextQGK', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(24, '103871210044', '$2y$10$xhR.bphzVwmcJpBUOH9niOdk.41LvKFG4JM0Os2hwfDZp.SZSmbV2', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(25, '103871210018', '$2y$10$Xa4RFFkg1E8IgmGXLJjcA.lO5jBc1Wvo1ic7vyxwZH3lKo157r6Jq', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(26, '108127210040', '$2y$10$hzZPw68UbgdUNyZcjENxEeEE9MsCXzb.76lZySWcY2jQ22usQKZfa', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(27, '136638210568', '$2y$10$ph82YjhhM.JcVHJnwUkNxO/r/ULNezJ/QHprLipZibX860pJHRfcK', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(28, '103871210066', '$2y$10$9QiUWYSSI1Uy6vst/NFK8O5qRPLNIKsH7O1yt0CE4FHubj2sWXgva', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(29, '103871210027', '$2y$10$NCVmcTfh.PywxZoIFh0JRubQTeUvr3MJQbrF6wxT5svW8VMPsmF6a', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(30, '103872210005', '$2y$10$hu76iAhTYAmBFoWNoqywa.J9v9iWN1YswVldqMMfVROLtyfD66Khm', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(31, '103871210040', '$2y$10$fF1bJY9YvrgQkPBRAuCu7eXRqAnbQFZXuK4FLHRmrx/knrycPsTwO', 'student', 0, NULL, '2026-07-25 21:44:32', '2026-07-25 21:44:32'),
	(32, '103872210010', '$2y$10$QXmRSGAG.64ZaQwosUwOCeWanooSTAxGlG8ZRU8K8ZapSqhyIt872', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(33, '103871210019', '$2y$10$n3xd44pCJchFwB0g34LSaOBM7C484vDMTf6RFqUki/ytEpYODKB2C', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(34, '103871210060', '$2y$10$bMAPMdbRD5rFDP.SpEzxhuRIazHN3kr7CMXzcjIO0VZE1JlMqHd1e', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(35, '103871210020', '$2y$10$Kzl4OyKhrDEQkVAcZfr.AeTMJT/yx9rWWubpOWm8gse3DehfbYV6a', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(36, '103871210009', '$2y$10$vLC2w2o43VbHuu7byRAg3.Sei4bfJvSmgIwohFSMShCB.gsu1kQeq', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(37, '136649210297', '$2y$10$UktUposqHedGRK2IKnbfQevX/OIkYTLVkC2x6EVmxkXknPBlwjTzy', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(38, '103872210006', '$2y$10$dL29kXVPu81.5HCn7.eYu.Ufgd8XJy/9g4R5V3IEM4WaOE4rRjLDS', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(39, '103871210055', '$2y$10$MyVNTNl/sarAaVqC6p8A2eJBBoIfSx2zfpehpydVImq6JM.0lYk9W', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(40, '103871210068', '$2y$10$cISGg5EMlwmMG5sJ2L2sveHQ6qRIvvnBs0UNAzT5KG44DpZuVy/q6', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(41, '103871210063', '$2y$10$/nBBPjQ/dFlHR1DMsRkB2.PXiyyuoOdO6/P/W20M.y6AYngDJj5rG', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(42, '103871210022', '$2y$10$itU4metckS6G0tLPIB6jF.0i4rfODJ0eLmXMaNpeiIQYxe4gl/6AO', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(43, '103871210048', '$2y$10$ZgdN/sEUaphGUybzvZb29elVLD1ILha9BP0K9RMwepnwYhylFZoaW', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(44, '103871210042', '$2y$10$1PFR.7MkB8xpNRuYlfwm0OQek.qjf3EDUoi475egmr5hon0/hTEVC', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(45, '103871210046', '$2y$10$S8bi6ruYk/5gLe/OVQOjaOAIK6BORwhcLipE41bItqbAkdym56iqG', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(46, '103871210031', '$2y$10$YVir/VYCq/YbbeNeSUVeh.PTrFk4e8qOfMMfhZf0UIh/y9oj4XJpe', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(47, '103871210056', '$2y$10$MougQOaMDrFkRvfHtKm7zucwyjTcnBelfoxYAssrfdstYne8gRtwy', 'student', 0, NULL, '2026-07-25 21:44:33', '2026-07-25 21:44:33'),
	(48, '103871210043', '$2y$10$u.vOvD/bpuo9Cm9CPc81y.Ch7nHsfhw9tWT1KuW0USlpLtH8jSkJK', 'student', 0, NULL, '2026-07-25 21:44:34', '2026-07-25 21:44:34'),
	(49, '155513210018', '$2y$10$8s/mpx8XxSm//pTKl7Vt5ezdqgC94FBT2StH9Ji140Gf3oy6p5dOC', 'student', 0, NULL, '2026-07-25 21:44:34', '2026-07-25 21:44:34'),
	(50, '103871210049', '$2y$10$3IzBeiTFGp9lxgsa9tQScO20u3n909d5JiHOEJYG70pkVZHc1ep1K', 'student', 0, NULL, '2026-07-25 21:44:34', '2026-07-25 21:44:34');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
