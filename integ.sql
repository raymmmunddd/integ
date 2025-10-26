-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 04:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `integ`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `treatment_session_type` varchar(255) NOT NULL,
  `appointment_type` enum('online','physical') NOT NULL,
  `status` enum('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `student_id`, `therapist_id`, `appointment_date`, `start_time`, `end_time`, `treatment_session_type`, `appointment_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2025-10-17', '13:00:00', '14:00:00', 'Assessment', 'online', 'completed', '2025-10-25 21:44:09', '2025-10-26 02:08:38'),
(3, 1, 3, '2025-10-29', '11:00:00', '12:00:00', 'Individual Therapy', 'physical', 'completed', '2025-10-25 23:51:54', '2025-10-26 00:17:09'),
(5, 7, 2, '2025-10-27', '21:00:00', '22:00:00', 'Individual Therapy', 'online', 'pending', '2025-10-26 02:47:57', '2025-10-26 02:47:57'),
(6, 1, 2, '2025-10-28', '21:00:00', '22:00:00', 'Individual Therapy', 'online', 'pending', '2025-10-26 03:52:18', '2025-10-26 03:52:18');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-jane@gmail.com|127.0.0.1', 'i:2;', 1761474069),
('laravel-cache-jane@gmail.com|127.0.0.1:timer', 'i:1761474069;', 1761474069),
('laravel-cache-password_reset_202310784@gordoncollege.edu.ph', 'i:2;', 1761491660);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `quality_of_service` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5 rating',
  `responsiveness` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5 rating',
  `effectiveness` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5 rating',
  `organization` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5 rating',
  `recommendation` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5 rating',
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `appointment_id`, `quality_of_service`, `responsiveness`, `effectiveness`, `organization`, `recommendation`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 5, 4, 4, 3, 'Hallo, Thank you I feel better! :D', '2025-10-26 03:53:14', '2025-10-26 03:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_answers`
--

CREATE TABLE `forum_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_answers`
--

INSERT INTO `forum_answers` (`id`, `question_id`, `therapist_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Relax and chill a bit!', '2025-10-26 05:54:15', '2025-10-26 05:54:15'),
(2, 2, 2, 'Take a few deep breaths to calm your mind and try grounding yourself by focusing on the present moment. Breaking your study material into small, manageable chunks can also reduce stress and help you feel more in control.', '2025-10-26 06:13:57', '2025-10-26 06:13:57'),
(3, 1, 3, 'Start by taking a short walk or doing some light stretches to release tension. Then, try writing down your worries to organize your thoughts and make them feel more manageable.', '2025-10-26 06:14:49', '2025-10-26 06:14:49');

-- --------------------------------------------------------

--
-- Table structure for table `forum_questions`
--

CREATE TABLE `forum_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','answered') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_questions`
--

INSERT INTO `forum_questions` (`id`, `user_id`, `content`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'I\'m stressed, what do I do?', 'answered', '2025-10-26 05:52:58', '2025-10-26 05:54:15'),
(2, 7, 'What are some quick techniques to manage anxiety during exams?', 'answered', '2025-10-26 06:12:38', '2025-10-26 06:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journals`
--

CREATE TABLE `journals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `journal_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `journals`
--

INSERT INTO `journals` (`id`, `user_id`, `content`, `journal_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'testogg', '2025-10-26', '2025-10-25 23:22:14', '2025-10-26 02:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE `licenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`id`, `therapist_id`, `license_number`, `created_at`, `updated_at`) VALUES
(4, 2, '0931-319', '2025-10-26 05:23:59', '2025-10-26 05:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `symptoms` varchar(255) NOT NULL,
  `specialist` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `student_id`, `therapist_id`, `date`, `symptoms`, `specialist`, `file_path`, `created_at`, `updated_at`) VALUES
(2, 1, 2, '2025-10-26', 'Cough', 'Dr. Willie', 'medical_records/PkYRlRR81zpwVkSkdMMpg9tdwgGDSYLV6BAIe8yo.txt', '2025-10-26 04:27:44', '2025-10-26 04:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '0001_01_01_000003_create_therapists_table', 1),
(5, '0001_01_01_000004_create_appointments_table', 1),
(6, '2025_09_30_095712_create_personal_access_tokens_table', 1),
(7, '2025_09_30_121138_create_journals_table', 1),
(8, '2025_10_05_100613_create_notifications_table', 1),
(9, '2025_10_05_112153_create_medical_records_table', 1),
(10, '2025_10_05_112642_create_notes_table', 1),
(11, '2025_10_05_125131_create_evaluations_table', 1),
(12, '2025_10_06_075252_create_years_experience_table', 1),
(13, '2025_10_06_080629_add_bio_to_users_table', 1),
(14, '2025_10_07_010610_add_password_reset_otp_to_users_table', 1),
(15, '2025_10_26_124634_create_licenses_table', 2),
(16, '2025_10_26_134334_create_forum_questions_table', 3),
(17, '2025_10_26_134351_create_forum_answers_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `student_id`, `therapist_id`, `date`, `file_path`, `created_at`, `updated_at`) VALUES
(4, 1, 2, '2025-10-26', 'notes/Ha7yY52R5IMXpXLCNopyA84tUEZ846FMSwnMyb64.pdf', '2025-10-26 04:40:28', '2025-10-26 04:40:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `sender_id`, `appointment_id`, `type`, `message`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'appointment_created', 'You have an appointment with Willie Revillame on October 27, 2025 at 9:00 AM - 10:00 AM.', 1, '2025-10-26 06:03:07', '2025-10-25 21:44:09', '2025-10-26 06:03:07'),
(2, 2, 1, 1, 'appointment_created', 'Juan D. Cruz has an appointment on October 27, 2025 at 9:00 AM - 10:00 AM.', 1, '2025-10-26 05:35:14', '2025-10-25 21:44:09', '2025-10-26 05:35:14'),
(7, 3, 1, 3, 'appointment_created', 'Juan D. Cruz has an appointment on October 31, 2025 at 11:00 AM - 12:00 PM.', 1, '2025-10-26 06:14:21', '2025-10-25 23:51:54', '2025-10-26 06:14:21'),
(8, 3, 1, 3, 'appointment_updated', 'Appointment updated to October 29, 2025 at 11:00 AM - 12:00 PM.', 1, '2025-10-26 06:14:21', '2025-10-26 00:17:09', '2025-10-26 06:14:21'),
(12, 2, 1, 1, 'appointment_updated', 'Appointment updated to October 29, 2025 at 6:00 PM - 7:00 PM.', 1, '2025-10-26 05:35:14', '2025-10-26 02:07:41', '2025-10-26 05:35:14'),
(13, 2, 1, 1, 'appointment_updated', 'Appointment updated to October 31, 2025 at 1:00 PM - 2:00 PM.', 1, '2025-10-26 05:35:14', '2025-10-26 02:08:38', '2025-10-26 05:35:14'),
(14, 7, 2, 5, 'appointment_created', 'You have an appointment with Willie Revillame on October 31, 2025 at 9:00 PM - 10:00 PM.', 0, NULL, '2025-10-26 02:47:57', '2025-10-26 02:47:57'),
(15, 2, 7, 5, 'appointment_created', 'Raymund Leean C. Caolboy has an appointment on October 31, 2025 at 9:00 PM - 10:00 PM.', 1, '2025-10-26 05:35:14', '2025-10-26 02:47:57', '2025-10-26 05:35:14'),
(16, 1, 2, 6, 'appointment_created', 'You have an appointment with Willie Revillame on October 28, 2025 at 9:00 PM - 10:00 PM.', 1, '2025-10-26 06:03:07', '2025-10-26 03:52:18', '2025-10-26 06:03:07'),
(17, 2, 1, 6, 'appointment_created', 'Juan D. Cruz has an appointment on October 28, 2025 at 9:00 PM - 10:00 PM.', 1, '2025-10-26 05:31:37', '2025-10-26 03:52:18', '2025-10-26 05:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Physical Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(2, 'Occupational Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(3, 'Speech Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(4, 'Respiratory Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(5, 'Psychotherapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(6, 'Cognitive Behavioral Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(7, 'Family/Marriage Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(8, 'Rehabilitation Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(9, 'Sports Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27'),
(10, 'Child/Developmental Therapist', '2025-10-25 20:59:27', '2025-10-25 20:59:27');

-- --------------------------------------------------------

--
-- Table structure for table `therapist_availabilities`
--

CREATE TABLE `therapist_availabilities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `therapist_availabilities`
--

INSERT INTO `therapist_availabilities` (`id`, `therapist_id`, `day_of_week`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(6, 3, 'Monday', '10:00:00', '18:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(7, 3, 'Wednesday', '10:00:00', '18:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(8, 3, 'Friday', '10:00:00', '18:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(9, 4, 'Tuesday', '08:00:00', '16:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(10, 4, 'Thursday', '08:00:00', '16:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(11, 5, 'Monday', '13:00:00', '20:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(12, 5, 'Tuesday', '13:00:00', '20:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(13, 5, 'Wednesday', '13:00:00', '20:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(14, 5, 'Thursday', '13:00:00', '20:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(15, 6, 'Monday', '09:00:00', '15:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(16, 6, 'Wednesday', '09:00:00', '15:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(17, 6, 'Friday', '09:00:00', '15:00:00', '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(38, 2, 'Monday', '07:00:00', '22:00:00', '2025-10-26 05:23:59', '2025-10-26 05:23:59'),
(39, 2, 'Tuesday', '07:00:00', '22:00:00', '2025-10-26 05:23:59', '2025-10-26 05:23:59'),
(40, 2, 'Wednesday', '07:00:00', '22:00:00', '2025-10-26 05:23:59', '2025-10-26 05:23:59'),
(41, 2, 'Thursday', '07:00:00', '22:00:00', '2025-10-26 05:23:59', '2025-10-26 05:23:59'),
(42, 2, 'Friday', '07:00:00', '22:00:00', '2025-10-26 05:23:59', '2025-10-26 05:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `therapist_specialization`
--

CREATE TABLE `therapist_specialization` (
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `specialization_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `therapist_specialization`
--

INSERT INTO `therapist_specialization` (`therapist_id`, `specialization_id`) VALUES
(2, 1),
(3, 4),
(4, 9),
(5, 5),
(6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(1) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL COMMENT 'BSIT, BSCS, BSEMC',
  `email` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(255) NOT NULL,
  `house_number` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) NOT NULL,
  `city_municipality` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('therapist','student') NOT NULL DEFAULT 'student',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `password_reset_otp` varchar(255) DEFAULT NULL,
  `password_reset_otp_expires_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_initial`, `last_name`, `program`, `email`, `date_of_birth`, `gender`, `house_number`, `barangay`, `city_municipality`, `phone_number`, `image`, `bio`, `password`, `role`, `email_verified_at`, `otp`, `otp_expires_at`, `password_reset_otp`, `password_reset_otp_expires_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Juan', 'D', 'Cruz', 'BSIT', 'test@gmail.com', '2000-01-15', 'Male', '54', 'East Tapinac', 'Olongapo City', '09123456789', 'profile_images/IcOzza7h1Im9TEKseUPDjXdiSNkecYggBr3hzf3z.jpg', NULL, '$2y$12$szdzhT9HGQYn8oyyoTODzux7XRpD2eLhC1Q9mO/A/TirMDc9cqsuq', 'student', '2025-10-25 20:59:28', NULL, NULL, NULL, NULL, NULL, '2025-10-25 20:59:28', '2025-10-26 02:17:37'),
(2, 'Willie', NULL, 'Revillame', 'N/A', 'willie.revillame@example.com', '1985-05-20', 'Male', '456', 'Barangay 2', 'Olongapo City', '09987654321', NULL, 'adada', '$2y$12$NE4p.fPmd4ZvD1v45Ob1NurctBFi5izPzNajOoqMfJ5OPqibGFRSq', 'therapist', '2025-10-25 20:59:28', NULL, NULL, NULL, NULL, NULL, '2025-10-25 20:59:28', '2025-10-26 05:23:08'),
(3, 'Jane', NULL, 'Doe', 'N/A', 'jane.doe@example.com', '1985-05-20', 'Male', '456', 'Barangay 2', 'Olongapo City', '09987654321', NULL, NULL, '$2y$12$HiuCpMnIHWDYhRrjj/gW9ukk65.pO6ms5EUQptQ6zx.j6jBgydnby', 'therapist', '2025-10-25 20:59:29', NULL, NULL, NULL, NULL, NULL, '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(4, 'John', NULL, 'Smith', 'N/A', 'john.smith@example.com', '1985-05-20', 'Male', '456', 'Barangay 2', 'Olongapo City', '09987654321', NULL, NULL, '$2y$12$9308YI6HyjiW0EL4ETQ/CujVCZx40kG94nDZk/uQ9h6iKuCHTONIm', 'therapist', '2025-10-25 20:59:29', NULL, NULL, NULL, NULL, NULL, '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(5, 'Maria', NULL, 'Santos', 'N/A', 'maria.santos@example.com', '1985-05-20', 'Male', '456', 'Barangay 2', 'Olongapo City', '09987654321', NULL, NULL, '$2y$12$MJjZ5CeIE3U9dybeJQNN3.e/vzNBvyok5l1xuKF3zGxCS8MwHZXEK', 'therapist', '2025-10-25 20:59:29', NULL, NULL, NULL, NULL, NULL, '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(6, 'Pedro', NULL, 'Gonzales', 'N/A', 'pedro.gonzales@example.com', '1985-05-20', 'Male', '456', 'Barangay 2', 'Olongapo City', '09987654321', NULL, NULL, '$2y$12$AJw99Ebgk0P8BneZp7yK7.gTmXCy6tEuFejTWj97IYSFcvzUinxya', 'therapist', '2025-10-25 20:59:29', NULL, NULL, NULL, NULL, NULL, '2025-10-25 20:59:29', '2025-10-25 20:59:29'),
(7, 'Raymund Leean', 'C', 'Caolboy', 'BSIT', '202310785@gordoncollege.edu.ph', '2005-03-30', 'Male', NULL, 'East Tapinac', 'Olongapo', '09179251372', NULL, NULL, '$2y$12$Pcsv9p8CVlrgcEUeiTy0feNxxYpXz4jNkkbhgRSTrfz7kZtgEYsUq', 'student', '2025-10-26 02:47:21', NULL, NULL, NULL, NULL, NULL, '2025-10-26 02:47:21', '2025-10-26 02:47:21'),
(8, 'Klein', NULL, 'Moretti', 'BSIT', '202310784@gordoncollege.edu.ph', '2025-10-06', 'Male', NULL, 'West Tapinac', 'Olongapo', '09325901532', NULL, NULL, '$2y$12$mNfhxiSVDNLdbutaG3sv/.aFl/IvWPV2C3Gnyue07MxNG2hFsRFy2', 'student', '2025-10-26 06:52:22', NULL, NULL, NULL, NULL, 'WbooJh0iO3e6aOFkVJgtiYV0k0ChuWH85Nh30MiMXBIalHMWBMKKyMBSHrBU', '2025-10-26 06:52:22', '2025-10-26 07:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `years_experience`
--

CREATE TABLE `years_experience` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `therapist_id` bigint(20) UNSIGNED NOT NULL,
  `years_of_experience` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `years_experience`
--

INSERT INTO `years_experience` (`id`, `therapist_id`, `years_of_experience`, `created_at`, `updated_at`) VALUES
(1, 2, '1-5 years', '2025-10-25 23:49:41', '2025-10-25 23:49:41'),
(2, 3, '1-5 years', NULL, NULL),
(3, 4, '1-5 years', NULL, NULL),
(4, 5, '1-5 years', NULL, NULL),
(5, 6, '1-5 years', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_student_id_foreign` (`student_id`),
  ADD KEY `appointments_therapist_id_foreign` (`therapist_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluations_appointment_id_foreign` (`appointment_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `forum_answers`
--
ALTER TABLE `forum_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_answers_question_id_foreign` (`question_id`),
  ADD KEY `forum_answers_therapist_id_foreign` (`therapist_id`);

--
-- Indexes for table `forum_questions`
--
ALTER TABLE `forum_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_questions_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journals_user_id_foreign` (`user_id`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `licenses_therapist_id_foreign` (`therapist_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medical_records_student_id_foreign` (`student_id`),
  ADD KEY `medical_records_therapist_id_foreign` (`therapist_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notes_student_id_foreign` (`student_id`),
  ADD KEY `notes_therapist_id_foreign` (`therapist_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_sender_id_foreign` (`sender_id`),
  ADD KEY `notifications_appointment_id_foreign` (`appointment_id`),
  ADD KEY `notifications_user_id_type_index` (`user_id`,`type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `therapist_availabilities`
--
ALTER TABLE `therapist_availabilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `therapist_availabilities_therapist_id_foreign` (`therapist_id`);

--
-- Indexes for table `therapist_specialization`
--
ALTER TABLE `therapist_specialization`
  ADD PRIMARY KEY (`therapist_id`,`specialization_id`),
  ADD KEY `therapist_specialization_specialization_id_foreign` (`specialization_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `years_experience`
--
ALTER TABLE `years_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `years_experience_therapist_id_foreign` (`therapist_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_answers`
--
ALTER TABLE `forum_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forum_questions`
--
ALTER TABLE `forum_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `journals`
--
ALTER TABLE `journals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `therapist_availabilities`
--
ALTER TABLE `therapist_availabilities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `years_experience`
--
ALTER TABLE `years_experience`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_answers`
--
ALTER TABLE `forum_answers`
  ADD CONSTRAINT `forum_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `forum_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_answers_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_questions`
--
ALTER TABLE `forum_questions`
  ADD CONSTRAINT `forum_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `journals`
--
ALTER TABLE `journals`
  ADD CONSTRAINT `journals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `licenses`
--
ALTER TABLE `licenses`
  ADD CONSTRAINT `licenses_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapist_availabilities`
--
ALTER TABLE `therapist_availabilities`
  ADD CONSTRAINT `therapist_availabilities_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapist_specialization`
--
ALTER TABLE `therapist_specialization`
  ADD CONSTRAINT `therapist_specialization_specialization_id_foreign` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `therapist_specialization_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `years_experience`
--
ALTER TABLE `years_experience`
  ADD CONSTRAINT `years_experience_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
