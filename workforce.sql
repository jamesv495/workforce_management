-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 03:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

CREATE DATABASE IF NOT EXISTS `workforce_management`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `workforce_management`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workforce_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `employee_id`, `email`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, '26011001', 'faingasonjhonie@gmail.com', '$2y$12$Mq.XRV0ZMrAJN3kkzlbKseU60VZw1SuJ4z0CBl2oVl7c.AO4Xc0.m', 'admin', 'active', '2026-09-25 12:31:43', '2026-09-25 14:30:28'),
(2, '26011002', 'nicolemayo059@gmail.com', '$2y$12$7YWgsAhO1Odtn3bSYMneeOpz5oh4axNzUWG4oNCBvNOhSFq4zriXe', 'manager', 'active', '2026-09-25 12:31:43', '2026-09-25 14:30:28'),
(13, '26011003', 'gogetsunaiyaba03@gmail.com', '$2y$10$RGJ/qcr5z64Xgg0PNXFCHuutSiSb8hutnWNLSXuCmA59i3MwGR6U2', 'employee', 'active', '2026-09-30 21:57:19', '2026-09-30 21:57:19');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_corrections`
--

CREATE TABLE `attendance_corrections` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `attendance_record_id` int(10) UNSIGNED DEFAULT NULL,
  `correction_date` date NOT NULL,
  `requested_clock_in` datetime DEFAULT NULL,
  `requested_clock_out` datetime DEFAULT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `status` enum('present','late','absent','half_day','on_leave') NOT NULL DEFAULT 'present',
  `source` enum('kiosk','manual','system') NOT NULL DEFAULT 'system',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `employee_id`, `attendance_date`, `time_in`, `time_out`, `status`, `source`, `notes`, `created_at`, `updated_at`) VALUES
(5, '26011001', '2026-09-27', '2026-09-27 13:30:52', NULL, 'present', 'kiosk', 'Recorded by RFID kiosk.', '2026-09-27 13:30:52', '2026-09-27 13:30:52'),
(6, '26011002', '2026-09-27', '2026-09-27 13:31:04', NULL, 'present', 'kiosk', 'Recorded by RFID kiosk.', '2026-09-27 13:31:04', '2026-09-27 13:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `department_name` varchar(150) DEFAULT NULL,
  `position_name` varchar(150) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_name` varchar(150) DEFAULT NULL,
  `emergency_relationship` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(50) DEFAULT NULL,
  `employment_type` varchar(100) DEFAULT NULL,
  `employment_status` enum('active','inactive','on_leave','terminated') NOT NULL DEFAULT 'active',
  `manager_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `avatar_url`, `date_of_birth`, `gender`, `email`, `phone`, `department_name`, `position_name`, `hire_date`, `address`, `emergency_name`, `emergency_relationship`, `emergency_phone`, `employment_type`, `employment_status`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, '26011001', 'Admin', NULL, 'Account', NULL, NULL, NULL, 'faingasonjhonie@gmail.com', NULL, 'Management', 'System Administrator', NULL, NULL, NULL, NULL, NULL, 'REGULAR', 'active', NULL, '2026-09-25 14:29:43', '2026-09-25 14:29:43'),
(2, '26011002', 'Nicole', 'Jane Anne Q.', 'Mayo', 'uploads/profile_photos/manager_26011002_6e2082db66f61cd21e161b41.jpg', NULL, NULL, 'nicolemayo059@gmail.com', NULL, 'Operations', 'Operations Manager', NULL, NULL, NULL, NULL, NULL, 'REGULAR', 'active', NULL, '2026-09-25 14:29:43', '2026-09-30 19:10:37'),
(12, '26011003', 'Hibino', 'Kai', 'Kafka', 'uploads/profile_photos/manager_26011003_355746f846a613519e644dab.jpg', '1999-12-23', 'Male', 'gogetsunaiyaba03@gmail.com', '09360118322', 'HR', 'Office Assistant', '2026-09-30', 'Maria Huzawa St. Poblacion Caloocan City', 'April Ticalo', 'Cousin', '09193329657', 'REGULAR', 'active', 2, '2026-09-30 21:57:19', '2026-09-30 23:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `employee_leave_balances`
--

CREATE TABLE `employee_leave_balances` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `balance_year` year(4) NOT NULL,
  `vacation_days` decimal(6,2) NOT NULL DEFAULT 8.00,
  `sick_days` decimal(6,2) NOT NULL DEFAULT 5.00,
  `emergency_days` decimal(6,2) NOT NULL DEFAULT 2.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_log_entries`
--

CREATE TABLE `kiosk_log_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `rfid_card_id` int(10) UNSIGNED DEFAULT NULL,
  `rfid_uid` varchar(100) NOT NULL,
  `kiosk_name` varchar(100) NOT NULL DEFAULT 'Main Kiosk',
  `scan_status` enum('APPROVED','DENIED') NOT NULL,
  `scanned_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kiosk_log_entries`
--

INSERT INTO `kiosk_log_entries` (`id`, `employee_id`, `rfid_card_id`, `rfid_uid`, `kiosk_name`, `scan_status`, `scanned_at`) VALUES
(5, '26011001', 1, '3219410377', 'Main Kiosk', 'APPROVED', '2026-09-27 13:30:52'),
(6, '26011002', 2, '3219246537', 'Main Kiosk', 'APPROVED', '2026-09-27 13:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(18, '26011002', 'Vacation Leave', '2026-10-01', '2026-10-05', 'try lng', 'rejected', 1, '2026-09-30 19:52:47', '2026-09-30 19:33:19', '2026-09-30 19:52:47'),
(19, '26011003', 'Vacation Leave', '2026-10-03', '2026-10-30', 'May gala kame nyan. \n-Testing', 'rejected', 1, '2026-09-30 23:45:44', '2026-09-30 22:16:15', '2026-09-30 23:45:44');

-- --------------------------------------------------------

--
-- Table structure for table `manager_notification_reads`
--

CREATE TABLE `manager_notification_reads` (
  `id` int(10) UNSIGNED NOT NULL,
  `manager_id` int(10) UNSIGNED NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `read_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `manager_notification_reads`
--

INSERT INTO `manager_notification_reads` (`id`, `manager_id`, `notification_type`, `read_at`) VALUES
(1, 2, 'analytics', '2026-09-30 15:09:54'),
(2, 2, 'leave', '2026-09-29 17:34:10'),
(6, 2, 'schedule', '2026-09-29 17:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `otp_challenges`
--

CREATE TABLE `otp_challenges` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `consumed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_challenges`
--

INSERT INTO `otp_challenges` (`id`, `account_id`, `code_hash`, `expires_at`, `attempts`, `consumed_at`, `created_at`) VALUES
(93, 2, '$2y$10$0nUAJUkeIWtVt2xO5FHRXej4bwK4QPPnS.u2ujdfmrCkBszOZbGoO', '2026-09-29 11:38:30', 0, '2026-09-29 17:29:00', '2026-09-29 17:28:30'),
(94, 1, '$2y$10$YmkAm.WLADUmSXU0cNZjAOMoI55EH4nc5EoxxmeJnPM8ck7YIydXi', '2026-09-29 11:52:35', 0, '2026-09-29 17:42:59', '2026-09-29 17:42:35'),
(95, 2, '$2y$10$qdYAskq3oyK28AGX9vXfLea8BrBDIZA7DaUBhMYuoVfv9AUAeIVa6', '2026-09-30 08:49:20', 0, '2026-09-30 14:39:58', '2026-09-30 14:39:20'),
(96, 1, '$2y$10$oKsYFaVROAwoG3BRj7s7eeqX7DoBrzQBsdpxlUTb19M41i81fbdU2', '2026-09-30 09:49:53', 0, '2026-09-30 15:40:16', '2026-09-30 15:39:53'),
(97, 2, '$2y$10$3/Hoh/UBA7AwYoks93ljb.sfv6RYe9m3WUQIEha/ael5kD.SGCLDe', '2026-09-30 09:57:59', 0, '2026-09-30 15:48:15', '2026-09-30 15:47:59'),
(98, 1, '$2y$10$VnFRFA0T.Lol6nXH9rJgOuT5P53w/UPBHQj0Bi8RD92/4lfF412hC', '2026-09-30 13:09:41', 0, '2026-09-30 19:00:19', '2026-09-30 18:59:41'),
(99, 2, '$2y$10$lvaU5BYMQp0XnyCTjL/zk.37O2s/t.HXrK09mgjKC91jwApC0eUr.', '2026-09-30 13:16:50', 0, '2026-09-30 19:07:17', '2026-09-30 19:06:50'),
(100, 1, '$2y$10$nJVqvYFdL0.NSjsCMXJip.DPx.ZBSFew7Nj78YTiZdAtpyOSHN6La', '2026-09-30 13:27:15', 0, '2026-09-30 19:17:45', '2026-09-30 19:17:15'),
(101, 13, '$2y$10$c1f6VW19xiyRV8KOZcpZZ.RQQVtfpmyYQ74bw4OZ1AEXDytHx/oJ6', '2026-09-30 16:13:12', 0, '2026-09-30 22:03:35', '2026-09-30 22:03:12'),
(102, 1, '$2y$10$xANBOkXq0u9CcITJnYm8sufsPa7T974UobKM9UnO.K9GcbBtWnCna', '2026-09-30 17:54:14', 0, '2026-09-30 23:44:40', '2026-09-30 23:44:14');

-- --------------------------------------------------------

--
-- Table structure for table `overtime_requests`
--

CREATE TABLE `overtime_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `attendance_record_id` int(10) UNSIGNED DEFAULT NULL,
  `overtime_date` date NOT NULL,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `rfid_uid` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rfid_cards`
--

INSERT INTO `rfid_cards` (`id`, `employee_id`, `rfid_uid`, `status`, `created_at`) VALUES
(1, '26011001', '3219410377', 'active', '2026-09-25 14:35:04'),
(2, '26011002', '3219246537', 'active', '2026-09-25 14:35:04'),
(8, '26011003', '3215545033', 'active', '2026-09-30 21:57:35');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `schedule_date` date NOT NULL,
  `shift_name` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `work_type` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shift_requests`
--

CREATE TABLE `shift_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `requested_date` date NOT NULL,
  `requested_shift` varchar(100) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timesheet_reviews`
--

CREATE TABLE `timesheet_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timesheet_review_history`
--

CREATE TABLE `timesheet_review_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `timesheet_review_id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `previous_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trusted_devices`
--

CREATE TABLE `trusted_devices` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `device_token_hash` varchar(255) NOT NULL,
  `verified_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trusted_devices`
--

INSERT INTO `trusted_devices` (`id`, `account_id`, `device_token_hash`, `verified_at`, `expires_at`, `created_at`, `last_used_at`) VALUES
(77, 13, '872475e75ae9e806520e8e34d46b11c05218ec0c06e0ac3c5039d8c80052bd6f', '2026-09-30 22:03:35', '2026-10-01 22:03:35', '2026-09-30 22:03:35', '2026-09-30 23:30:21'),
(78, 1, '0a1a2a5d879c776d2b436d2fd5a80911773632f3ce5e950f678d10aa613438ab', '2026-09-30 23:44:40', '2026-10-01 23:44:40', '2026-09-30 23:44:40', '2026-09-30 23:44:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_accounts_employee_id` (`employee_id`),
  ADD KEY `idx_accounts_role` (`role`),
  ADD KEY `idx_accounts_status` (`status`);

--
-- Indexes for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_correction_employee` (`employee_id`),
  ADD KEY `idx_correction_date` (`correction_date`),
  ADD KEY `idx_correction_status` (`status`),
  ADD KEY `idx_correction_record` (`attendance_record_id`),
  ADD KEY `idx_correction_reviewer` (`reviewed_by`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attendance_employee` (`employee_id`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_attendance_status` (`status`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_employees_employee_id` (`employee_id`),
  ADD KEY `idx_employees_department` (`department_name`),
  ADD KEY `idx_employees_status` (`employment_status`),
  ADD KEY `fk_employee_manager` (`manager_id`);

--
-- Indexes for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employee_leave_balance` (`employee_id`,`balance_year`),
  ADD KEY `idx_leave_balance_employee` (`employee_id`);

--
-- Indexes for table `kiosk_log_entries`
--
ALTER TABLE `kiosk_log_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kiosk_employee` (`employee_id`),
  ADD KEY `idx_kiosk_rfid` (`rfid_uid`),
  ADD KEY `idx_kiosk_status` (`scan_status`),
  ADD KEY `idx_kiosk_time` (`scanned_at`),
  ADD KEY `fk_kiosk_rfid` (`rfid_card_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_employee` (`employee_id`),
  ADD KEY `idx_leave_status` (`status`),
  ADD KEY `idx_leave_dates` (`start_date`,`end_date`),
  ADD KEY `fk_leave_approver` (`approved_by`);

--
-- Indexes for table `manager_notification_reads`
--
ALTER TABLE `manager_notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_manager_notification_type` (`manager_id`,`notification_type`),
  ADD KEY `idx_manager_notification_manager` (`manager_id`);

--
-- Indexes for table `otp_challenges`
--
ALTER TABLE `otp_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_account` (`account_id`),
  ADD KEY `idx_otp_expiration` (`expires_at`);

--
-- Indexes for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_overtime_employee` (`employee_id`),
  ADD KEY `idx_overtime_date` (`overtime_date`),
  ADD KEY `idx_overtime_status` (`status`),
  ADD KEY `idx_overtime_attendance` (`attendance_record_id`),
  ADD KEY `idx_overtime_reviewer` (`reviewed_by`);

--
-- Indexes for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`),
  ADD KEY `idx_rfid_employee_id` (`employee_id`),
  ADD KEY `idx_rfid_status` (`status`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_employee` (`employee_id`),
  ADD KEY `idx_schedule_date` (`schedule_date`);

--
-- Indexes for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shift_employee` (`employee_id`),
  ADD KEY `idx_shift_status` (`status`),
  ADD KEY `fk_shift_reviewer` (`reviewed_by`);

--
-- Indexes for table `timesheet_reviews`
--
ALTER TABLE `timesheet_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_timesheet_employee` (`employee_id`),
  ADD KEY `idx_timesheet_period` (`period_start`,`period_end`),
  ADD KEY `idx_timesheet_status` (`status`),
  ADD KEY `fk_timesheet_reviewer` (`reviewed_by`);

--
-- Indexes for table `timesheet_review_history`
--
ALTER TABLE `timesheet_review_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ts_history_review` (`timesheet_review_id`),
  ADD KEY `idx_ts_history_employee` (`employee_id`),
  ADD KEY `idx_ts_history_period` (`period_start`,`period_end`),
  ADD KEY `idx_ts_history_reviewer` (`reviewed_by`);

--
-- Indexes for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trusted_account` (`account_id`),
  ADD KEY `idx_trusted_expiration` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kiosk_log_entries`
--
ALTER TABLE `kiosk_log_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `manager_notification_reads`
--
ALTER TABLE `manager_notification_reads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `otp_challenges`
--
ALTER TABLE `otp_challenges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `shift_requests`
--
ALTER TABLE `shift_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `timesheet_reviews`
--
ALTER TABLE `timesheet_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `timesheet_review_history`
--
ALTER TABLE `timesheet_review_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_accounts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  ADD CONSTRAINT `fk_correction_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_correction_record` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_correction_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employee_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kiosk_log_entries`
--
ALTER TABLE `kiosk_log_entries`
  ADD CONSTRAINT `fk_kiosk_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kiosk_rfid` FOREIGN KEY (`rfid_card_id`) REFERENCES `rfid_cards` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_leave_approver` FOREIGN KEY (`approved_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `otp_challenges`
--
ALTER TABLE `otp_challenges`
  ADD CONSTRAINT `fk_otp_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD CONSTRAINT `fk_overtime_attendance` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_overtime_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_overtime_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `fk_rfid_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedule_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD CONSTRAINT `fk_shift_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_shift_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `timesheet_reviews`
--
ALTER TABLE `timesheet_reviews`
  ADD CONSTRAINT `fk_timesheet_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timesheet_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `timesheet_review_history`
--
ALTER TABLE `timesheet_review_history`
  ADD CONSTRAINT `fk_ts_history_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ts_history_review` FOREIGN KEY (`timesheet_review_id`) REFERENCES `timesheet_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ts_history_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD CONSTRAINT `fk_trusted_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
