-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 05:16 AM
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
-- Database: `loan_management_system`
--

-- --------------------------------------------------------
--
-- Table structure for table `admin_notifications`
--
-----------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `user_name`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'System Administrator', 'login', 'Audit log system initialized', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:00:32'),
(2, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:25'),
(3, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:26'),
(4, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:26'),
(5, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:26'),
(6, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:26'),
(7, 1, 'System Administrator', 'view', 'Accessed page: Analytics & Reports', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:31'),
(8, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:31'),
(9, 1, 'System Administrator', 'view', 'Accessed page: Admin Dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:34'),
(10, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:02:49'),
(11, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:03:50'),
(12, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:03:51'),
(13, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:03:51'),
(14, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:03:51'),
(15, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:11'),
(16, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:11'),
(17, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:14'),
(18, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:15'),
(19, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:15'),
(20, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:15'),
(21, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:15'),
(22, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:15'),
(23, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:16'),
(24, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:16'),
(25, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:16'),
(26, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:16'),
(27, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:17'),
(28, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:17'),
(29, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:17'),
(30, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:23'),
(31, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:24'),
(32, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:24'),
(33, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:24'),
(34, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:25'),
(35, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:25'),
(36, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:26'),
(37, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:26'),
(38, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:26'),
(39, 1, 'System Administrator', 'view', 'Accessed page: Payment Processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:27'),
(40, 1, 'System Administrator', 'view', 'Accessed page: Analytics & Reports', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:27'),
(41, 1, 'System Administrator', 'view', 'Accessed page: Payment Processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:29'),
(42, 1, 'System Administrator', 'view', 'Accessed page: Payment Processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:31'),
(43, 1, 'System Administrator', 'view', 'Accessed page: Payment Processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:35'),
(44, 1, 'System Administrator', 'payment', 'Payment #PAY202603091504352578 processed for System Administrator - Amount: ₱4,375.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:35'),
(45, 1, 'System Administrator', 'view', 'Accessed page: Payment Processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:35'),
(46, 1, 'System Administrator', 'view', 'Accessed page: Audit Log', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:04:48'),
(47, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:06:19'),
(48, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:06:26'),
(49, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:06:28'),
(50, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:06:34'),
(51, 1, 'System Administrator', 'export', 'Data exported: Audit Log - 50 records', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:11:04'),
(52, 1, 'System Administrator', 'export', 'Data exported: Audit Log - 51 records', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:11:04'),
(53, 1, 'System Administrator', 'export', 'Data exported: Audit Log - 52 records', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:11:10'),
(54, 1, 'System Administrator', 'export', 'Data exported: Audit Log - 53 records', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:11:18'),
(55, 1, 'System Administrator', 'export', 'Data exported: Audit Log - 54 records', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:12:34'),
(56, 1, 'System Administrator', 'export', 'Data exported: Audit Log - 55 records', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:12:40'),
(57, 1, 'System Administrator', 'update', 'Setting changed: Loan Products from \'{\"min_amount\":\"1000.00\",\"max_amount\":\"50000.00\",\"interest_rate\":\"5.00\",\"min_term\":\"1\",\"max_term\":\"12\",\"processing_fee\":\"0.00\",\"late_fee\":\"0.00\"}\' to \'{\"min_amount\":\"1000.00\",\"max_amount\":\"50000.00\",\"interest_rate\":\"5.00\",\"min_term\":1,\"max_term\":12,\"processing_fee\":\"0.00\",\"late_fee\":\"0.00\"}\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:29:09'),
(58, 1, 'System Administrator', 'update', 'Setting changed: Loan Products from \'{\"min_amount\":\"1000.00\",\"max_amount\":\"50000.00\",\"interest_rate\":\"5.00\",\"min_term\":\"1\",\"max_term\":\"12\",\"processing_fee\":\"0.00\",\"late_fee\":\"0.00\"}\' to \'{\"min_amount\":\"1000.00\",\"max_amount\":\"50000.00\",\"interest_rate\":\"5.00\",\"min_term\":1,\"max_term\":12,\"processing_fee\":\"0.00\",\"late_fee\":\"0.00\"}\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:29:11'),
(59, 1, 'System Administrator', 'update', 'Setting changed: Business Rules from \'{\"auto_approve_limit\":\"0.00\",\"credit_check_required\":\"no\",\"document_verification\":\"yes\",\"reminder_days\":\"3\",\"overdue_grace\":\"7\"}\' to \'{\"auto_approve_limit\":\"0.00\",\"credit_check_required\":\"no\",\"document_verification\":\"no\",\"reminder_days\":3,\"overdue_grace\":7}\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:29:23'),
(60, 1, 'System Administrator', 'update', 'Setting changed: System Settings from \'{\"session_timeout\":\"30\",\"max_login_attempts\":\"5\",\"password_min_length\":\"8\",\"require_2fa\":\"no\",\"backup_enabled\":\"no\",\"backup_frequency\":\"daily\"}\' to \'{\"session_timeout\":30,\"max_login_attempts\":5,\"password_min_length\":8,\"require_2fa\":\"no\",\"backup_enabled\":\"no\",\"backup_frequency\":\"daily\"}\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:29:27'),
(61, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:38:38'),
(62, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:38:49'),
(63, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:39:21'),
(64, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:39:27'),
(65, 1, 'System Administrator', 'payment', 'Payment #PAY202603091547526905 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 14:47:52'),
(66, 0, 'System', 'failed', 'Failed login: Email: lorenteromejoseph@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:10:28'),
(67, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:10:36'),
(68, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:19:12'),
(69, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:20:21'),
(70, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:22:32'),
(71, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:23:23'),
(72, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:23:24'),
(73, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:23:24'),
(74, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:24:38'),
(75, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:24:39'),
(76, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:24:42'),
(77, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:24:44'),
(78, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:25:06'),
(79, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:25:17'),
(80, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:27:33'),
(81, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:29:58'),
(82, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:30:15'),
(83, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:30:36'),
(84, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:30:43'),
(85, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:31:14'),
(86, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:33:25'),
(87, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:33:49'),
(88, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:34:41'),
(89, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:35:31'),
(90, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:36:49'),
(91, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:37:32'),
(92, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:38:38'),
(93, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:39:09'),
(94, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:39:17'),
(95, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:39:57'),
(96, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:40:02'),
(97, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:40:08'),
(98, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407016 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:40:11'),
(99, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:40:25'),
(100, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:40:40'),
(101, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:41:37'),
(102, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:41:44'),
(103, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:41:51'),
(104, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:42:44'),
(105, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:42:46'),
(106, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:42:48'),
(107, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:42:52'),
(108, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407016 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:42:55'),
(109, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407007 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:43:22'),
(110, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:43:35'),
(111, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:43:42'),
(112, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:44:30'),
(113, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:45:01'),
(114, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:51:39'),
(115, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:51:44'),
(116, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 00:08:07'),
(117, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 00:22:38'),
(118, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407017 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 00:22:47'),
(119, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 00:48:14'),
(120, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 00:48:21'),
(121, 1, 'System Administrator', 'login', 'Audit log system initialized', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 01:03:54'),
(122, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 01:13:28'),
(123, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 01:13:34'),
(124, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:23:45'),
(125, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:33:29'),
(126, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:33:37'),
(127, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:37:37'),
(128, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:37:42'),
(129, 1, 'System Administrator', 'payment', 'Payment #PAY202603100340301713 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:40:30'),
(130, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:40:41'),
(131, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 02:40:51'),
(132, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:12:12'),
(133, 1, '', 'update_interest_rates', '{\"daily\":6,\"weekly\":4.5,\"monthly\":3.5}', NULL, NULL, '2026-03-10 13:32:08'),
(134, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:47:09'),
(135, 0, 'System', 'failed', 'Failed login: Email: lorenteromejoseph@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:50:14'),
(136, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:50:31'),
(137, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:52:36'),
(138, 9, 'cryptical rome', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:53:32'),
(139, 9, 'cryptical rome', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:59:09'),
(140, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:59:16'),
(141, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 14:00:53'),
(142, 9, 'cryptical rome', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 14:44:34'),
(143, 9, 'cryptical rome', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 15:21:40'),
(144, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 15:21:50'),
(145, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 15:34:56'),
(146, 9, 'cryptical rome', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 15:35:02'),
(147, 9, 'cryptical rome', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 15:36:25'),
(148, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 15:36:31'),
(149, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 16:00:19'),
(150, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 21:42:15'),
(151, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:09:24'),
(152, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:09:38'),
(153, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:09:57'),
(154, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:10:05'),
(155, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:12:15'),
(156, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:12:21'),
(157, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:13:13'),
(158, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:13:18'),
(159, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:19:26'),
(160, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 22:19:32'),
(161, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 22:56:32'),
(162, 1, 'System Administrator', 'payment', 'Payment #PAY202603120009173788 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 23:09:17'),
(163, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 07:03:10'),
(164, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:21:32'),
(165, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:22:41'),
(166, 10, 'Abigail Nery', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:23:10'),
(167, 10, 'Abigail Nery', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:32:35'),
(168, 0, 'System', 'failed', 'Failed login: Email: lorenteromejoseph@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:32:41'),
(169, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:32:47'),
(170, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407019 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 11:37:56'),
(171, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 12:16:31'),
(172, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 12:16:39'),
(173, 1, 'System Administrator', 'payment', 'Payment #PAY202603121317191941 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 12:17:19'),
(174, 1, 'System Administrator', 'payment', 'Payment #PAY202603121317267760 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 12:17:26'),
(175, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 00:27:05'),
(176, 1, 'System Administrator', 'payment', 'Payment #PAY202603130127389770 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 00:27:38'),
(177, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 00:28:26'),
(178, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 00:28:32'),
(179, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407020 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 00:29:23'),
(180, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 00:29:47'),
(181, 10, 'Abigail Nery', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:26:44'),
(182, 10, 'Abigail Nery', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:34:44'),
(183, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:34:49'),
(184, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:50:10'),
(185, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:50:15'),
(186, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:16:18'),
(187, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:16:25'),
(188, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:17:49'),
(189, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:51:27'),
(190, 1, 'System Administrator', 'assess_late_fees', 'Assessed 22 late fees', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:58:27'),
(191, 1, 'System Administrator', 'waive_late_fee', 'Waived fee ID: 11, Reason: Administrative waiver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:58:38'),
(192, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:59:25'),
(193, 2, 'Coastal Enterprises', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 21:59:30'),
(194, 2, 'Coastal Enterprises', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:09:10'),
(195, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:09:15'),
(196, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:09:39'),
(197, 2, 'Coastal Enterprises', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:09:49'),
(198, 2, 'Coastal Enterprises', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:12:17'),
(199, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:12:23'),
(200, 1, 'System Administrator', 'update_late_fee_settings', '{\"id\":\"1\",\"fee_type\":\"percentage\",\"percentage_rate\":5,\"fixed_amount\":100,\"grace_period_days\":3,\"max_fee_percentage\":25,\"compound_daily\":false,\"apply_weekends\":true,\"min_fee_amount\":50,\"description\":\"Default late fee: 5% of payment amount after 3-day grace period\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:12:59'),
(201, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:24:40'),
(202, 2, 'Coastal Enterprises', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:24:45'),
(203, 2, 'Coastal Enterprises', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:37:33'),
(204, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:37:37'),
(205, 1, 'System Administrator', 'payment', 'Payment #PAY202603132337523838 processed for System Administrator - Amount: ₱145.83 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:37:52'),
(206, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:38:20'),
(207, 2, 'Coastal Enterprises', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 22:38:33'),
(208, 2, 'Coastal Enterprises', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:18:57'),
(209, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:19:04'),
(210, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:20:27'),
(211, 2, 'Coastal Enterprises', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:20:36');
INSERT INTO `audit_log` (`id`, `user_id`, `user_name`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(212, 2, 'Coastal Enterprises', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:21:22'),
(213, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:21:44'),
(214, 1, 'System Administrator', 'assess_late_fees', 'Assessed 0 late fees', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 23:26:13'),
(215, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:53:23'),
(216, 1, 'System Administrator', 'assess_late_fees', 'Assessed 4 late fees', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:57:25'),
(217, 1, 'System Administrator', 'payment', 'Payment #PAY202603140258005280 processed for Coastal Enterprises - Amount: ₱1,312.50 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:58:00'),
(218, 1, 'System Administrator', 'payment', 'Payment #PAY202603140258043567 processed for Coastal Enterprises - Amount: ₱1,312.50 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:58:04'),
(219, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:58:21'),
(220, 2, 'Coastal Enterprises', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:58:26'),
(221, 2, 'Coastal Enterprises', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:58:36'),
(222, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:58:47'),
(223, 1, 'System Administrator', 'payment', 'Payment #PAY202603140301502992 processed for Coastal Enterprises - Amount: ₱1,640.63 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:01:50'),
(224, 1, 'System Administrator', 'payment', 'Payment #PAY202603140306058450 processed for System Administrator - Amount: ₱62.29 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:06:05'),
(225, 1, 'System Administrator', 'payment', 'Payment #PAY202603140306298178 processed for System Administrator - Amount: ₱68.22 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:06:29'),
(226, 1, 'System Administrator', 'payment', 'Payment #PAY202603140306442021 processed for Coastal Enterprises - Amount: ₱1,968.76 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:06:44'),
(227, 1, 'System Administrator', 'payment', 'Payment #PAY202603140306595489 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:06:59'),
(228, 1, 'System Administrator', 'payment', 'Payment #PAY202603140307103037 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:07:10'),
(229, 1, 'System Administrator', 'payment', 'Payment #PAY202603140307164462 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:07:16'),
(230, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:09:24'),
(231, 1, 'System Administrator', 'payment', 'Payment #PAY202603140309313634 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:09:31'),
(232, 1, 'System Administrator', 'payment', 'Payment #PAY202603140309507102 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:09:50'),
(233, 1, 'System Administrator', 'payment', 'Payment #PAY202603140311145093 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:11:14'),
(234, 1, 'System Administrator', 'payment', 'Payment #PAY202603140311248034 processed for System Administrator - Amount: ₱65.25 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:11:24'),
(235, 1, 'System Administrator', 'update_late_fee_settings', '{\"id\":\"1\",\"fee_type\":\"percentage\",\"percentage_rate\":5,\"fixed_amount\":100,\"grace_period_days\":3,\"max_fee_percentage\":25,\"compound_daily\":false,\"apply_weekends\":true,\"min_fee_amount\":50,\"description\":\"Default late fee: 5% of payment amount after 3-day grace period\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:12:50'),
(236, 1, 'System Administrator', 'payment', 'Payment #PAY202603140314304249 processed for System Administrator - Amount: ₱62.29 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:14:30'),
(237, 1, 'System Administrator', 'assess_late_fees', 'Assessed 0 late fees', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:14:50'),
(238, 1, 'System Administrator', 'update_late_fee_settings', '{\"id\":\"1\",\"fee_type\":\"percentage\",\"percentage_rate\":5,\"fixed_amount\":100,\"grace_period_days\":3,\"max_fee_percentage\":25,\"compound_daily\":false,\"apply_weekends\":true,\"min_fee_amount\":50,\"description\":\"Default late fee: 5% of payment amount after 3-day grace period\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:15:58'),
(239, 1, 'System Administrator', 'assess_late_fees', 'Assessed 0 late fees', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:16:00'),
(240, 1, 'System Administrator', 'assess_late_fees', 'Assessed 0 late fees', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:17:06'),
(241, 1, 'System Administrator', 'waive_late_fee', 'Waived fee ID: 24, Reason: Administrative waiver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:19:10'),
(242, 1, 'System Administrator', 'waive_late_fee', 'Waived fee ID: 25, Reason: Administrative waiver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:19:10'),
(243, 1, 'System Administrator', 'waive_late_fee', 'Waived fee ID: 24, Reason: Administrative waiver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:19:10'),
(244, 1, 'System Administrator', 'waive_late_fee', 'Waived fee ID: 24, Reason: Administrative waiver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:19:10'),
(245, 1, 'System Administrator', 'payment', 'Payment #PAY202603140327479440 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 02:27:47'),
(246, 1, 'System Administrator', 'payment', 'Payment #PAY202603140402052772 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:02:05'),
(247, 1, 'System Administrator', 'payment', 'Payment #PAY202603140407256374 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:07:25'),
(248, 1, 'System Administrator', 'payment', 'Payment #PAY202603140413282600 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:13:29'),
(249, 1, 'System Administrator', 'payment', 'Payment #PAY202603140419132655 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:19:13'),
(250, 1, 'System Administrator', 'payment', 'Payment #PAY202603140420315316 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:20:31'),
(251, 1, 'System Administrator', 'payment', 'Payment #PAY202603140420456592 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:20:45'),
(252, 1, 'System Administrator', 'payment', 'Payment #PAY202603140423237314 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:23:23'),
(253, 1, 'System Administrator', 'payment', 'Payment #PAY202603140444314630 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:44:31'),
(254, 1, 'System Administrator', 'payment', 'Payment #PAY202603140447393517 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:47:39'),
(255, 1, 'System Administrator', 'payment', 'Payment #PAY202603140448038471 processed for Rome Joseph Lorente - Amount: ₱225.00 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:48:03'),
(256, 1, 'System Administrator', 'payment', 'Payment #PAY202603140454139991 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:54:13'),
(257, 1, 'System Administrator', 'payment', 'Payment #PAY202603140455303795 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:55:30'),
(258, 1, 'System Administrator', 'payment', 'Payment #PAY202603140457575314 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:57:57'),
(259, 1, 'System Administrator', 'payment', 'Payment #PAY202603140458094793 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 03:58:09'),
(260, 1, 'System Administrator', 'payment', 'Payment #PAY202603140501441020 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:01:44'),
(261, 1, 'System Administrator', 'payment', 'Payment #PAY202603140506054683 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:06:05'),
(262, 1, 'System Administrator', 'payment', 'Payment #PAY202603140516293733 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:16:29'),
(263, 1, 'System Administrator', 'payment', 'Payment #PAY202603140517166599 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:17:16'),
(264, 1, 'System Administrator', 'payment', 'Payment #PAY202603140517381216 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:17:38'),
(265, 1, 'System Administrator', 'payment', 'Payment #PAY202603140517525452 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:17:53'),
(266, 1, 'System Administrator', 'payment', 'Payment #PAY202603140518502489 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:18:50'),
(267, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:19:12'),
(268, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:19:19'),
(269, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407031 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:19:32'),
(270, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:19:45'),
(271, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:23:52'),
(272, 1, 'System Administrator', 'payment', 'Payment #PAY202603140524032237 processed for System Administrator - Amount: ₱59.32 - Status: completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:24:03'),
(273, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:24:12'),
(274, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:24:18'),
(275, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407030 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:25:59'),
(276, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407030 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 04:27:13'),
(277, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:55:45'),
(278, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:56:21'),
(279, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:57:28'),
(280, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:57:32'),
(281, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:57:36'),
(282, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:58:03'),
(283, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:58:11'),
(284, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:58:17'),
(285, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:59:13'),
(286, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:59:16'),
(287, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:59:20'),
(288, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:59:22'),
(289, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 00:59:55'),
(290, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:00:19'),
(291, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:00:37'),
(292, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:05:19'),
(293, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:05:42'),
(294, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407032 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:05:50'),
(295, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407044 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:13:07'),
(296, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407044 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:13:51'),
(297, 8, 'Rome Joseph Lorente', 'download', 'Receipt downloaded for payment #PAY20260308084407048 - Amount: ₱225.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:16:15'),
(298, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:34:06'),
(299, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:34:12'),
(300, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:34:53'),
(301, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:35:07'),
(302, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:13:52'),
(303, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:16:38'),
(304, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:16:44'),
(305, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:18:15'),
(306, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:18:20'),
(307, 1, 'System Administrator', 'update_late_fee_settings', '{\"id\":\"1\",\"fee_type\":\"percentage\",\"percentage_rate\":5,\"fixed_amount\":100,\"grace_period_days\":3,\"max_fee_percentage\":25,\"compound_daily\":false,\"apply_weekends\":true,\"min_fee_amount\":50,\"description\":\"Default late fee: 5% of payment amount after 3-day grace period\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:25:00'),
(308, 1, 'System Administrator', 'update_late_fee_settings', '{\"id\":\"1\",\"fee_type\":\"percentage\",\"percentage_rate\":5,\"fixed_amount\":100,\"grace_period_days\":3,\"max_fee_percentage\":25,\"compound_daily\":true,\"apply_weekends\":true,\"min_fee_amount\":50,\"description\":\"Default late fee: 5% of payment amount after 3-day grace period\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 02:31:01'),
(309, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:00:07'),
(310, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:00:20'),
(311, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:00:36'),
(312, 1, 'System Administrator', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:20:43'),
(313, 1, 'System Administrator', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:31:45'),
(314, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:31:57'),
(315, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:32:19'),
(316, 8, 'Rome Joseph Lorente', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 03:38:02'),
(317, 8, 'Rome Joseph Lorente', 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 04:15:37');

-- --------------------------------------------------------

--
-- Table structure for table `late_fees`
--

CREATE TABLE `late_fees` (
  `id` int(11) NOT NULL,
  `loan_id` varchar(50) NOT NULL,
  `payment_schedule_id` int(11) NOT NULL,
  `original_due_date` date NOT NULL,
  `days_late` int(11) NOT NULL,
  `fee_type` enum('percentage','fixed','tiered') NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `fee_percentage` decimal(5,2) DEFAULT NULL,
  `calculation_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`calculation_details`)),
  `status` enum('pending','applied','waived','paid') DEFAULT 'pending',
  `applied_date` timestamp NULL DEFAULT NULL,
  `waived_by` int(11) DEFAULT NULL,
  `waiver_reason` text DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `late_fees`
--

INSERT INTO `late_fees` (`id`, `loan_id`, `payment_schedule_id`, `original_due_date`, `days_late`, `fee_type`, `fee_amount`, `fee_percentage`, `calculation_details`, `status`, `applied_date`, `waived_by`, `waiver_reason`, `payment_id`, `created_at`, `updated_at`) VALUES
(23, 'L2024002', 41, '2024-07-26', 596, 'percentage', 328.13, 5.00, '{\"method\":\"percentage_accumulated\",\"daily_rate\":\"5.00\",\"daily_fee\":65.625,\"base_amount\":\"1312.50\",\"days_charged\":593,\"final_fee\":328.125,\"days_late\":596,\"grace_period\":3,\"note\":\"Accumulated fee for 593 days\"}', 'pending', NULL, NULL, NULL, NULL, '2026-03-14 01:57:24', '2026-03-14 01:57:24'),
(24, 'L2024002', 42, '2024-08-02', 589, 'percentage', 328.13, 5.00, '{\"method\":\"percentage_accumulated\",\"daily_rate\":\"5.00\",\"daily_fee\":65.625,\"base_amount\":\"1312.50\",\"days_charged\":586,\"final_fee\":328.125,\"days_late\":589,\"grace_period\":3,\"note\":\"Accumulated fee for 586 days\"}', 'waived', NULL, 1, 'Administrative waiver', NULL, '2026-03-14 01:57:25', '2026-03-14 02:19:10'),
(25, 'L2024002', 43, '2024-08-09', 582, 'percentage', 328.13, 5.00, '{\"method\":\"percentage_accumulated\",\"daily_rate\":\"5.00\",\"daily_fee\":65.625,\"base_amount\":\"1312.50\",\"days_charged\":579,\"final_fee\":328.125,\"days_late\":582,\"grace_period\":3,\"note\":\"Accumulated fee for 579 days\"}', 'waived', NULL, 1, 'Administrative waiver', NULL, '2026-03-14 01:57:25', '2026-03-14 02:19:10'),
(26, 'L2024002', 44, '2024-08-16', 575, 'percentage', 328.13, 5.00, '{\"method\":\"percentage_accumulated\",\"daily_rate\":\"5.00\",\"daily_fee\":65.625,\"base_amount\":\"1312.50\",\"days_charged\":572,\"final_fee\":328.125,\"days_late\":575,\"grace_period\":3,\"note\":\"Accumulated fee for 572 days\"}', 'pending', NULL, NULL, NULL, NULL, '2026-03-14 01:57:25', '2026-03-14 01:57:25');

-- --------------------------------------------------------

--
-- Table structure for table `late_fee_notifications`
--

CREATE TABLE `late_fee_notifications` (
  `id` int(11) NOT NULL,
  `loan_id` varchar(50) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `notification_type` enum('fee_assessed','reminder','final_notice') NOT NULL,
  `notification_method` enum('email','sms','system') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','pending') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `late_fee_notifications`
--

INSERT INTO `late_fee_notifications` (`id`, `loan_id`, `fee_id`, `notification_type`, `notification_method`, `recipient`, `message`, `sent_at`, `status`) VALUES
(1, 'L2024002', 1, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Mar 29, 2024\nDays Late: 714\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(2, 'L2024002', 2, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Apr 5, 2024\nDays Late: 707\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(3, 'L2024003', 3, 'fee_assessed', 'email', 'admin@blueledger.com', 'Dear System Administrator,\n\nA late fee of ₱36.46 has been assessed on your loan payment.\n\nLoan ID: L2024003\nOriginal Due Date: Apr 7, 2024\nDays Late: 705\nLate Fee Amount: ₱36.46\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(4, 'L2024002', 4, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Apr 12, 2024\nDays Late: 700\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(5, 'L2024002', 5, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Apr 19, 2024\nDays Late: 693\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(6, 'L2024002', 6, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Apr 26, 2024\nDays Late: 686\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(7, 'L2024002', 7, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: May 3, 2024\nDays Late: 679\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(8, 'L2024002', 8, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: May 10, 2024\nDays Late: 672\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(9, 'L2024002', 9, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: May 17, 2024\nDays Late: 665\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(10, 'L2024002', 10, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: May 24, 2024\nDays Late: 658\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:26', 'sent'),
(11, 'L2024002', 11, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: May 31, 2024\nDays Late: 651\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(12, 'L2024002', 12, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jun 7, 2024\nDays Late: 644\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(13, 'L2024002', 13, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jun 14, 2024\nDays Late: 637\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(14, 'L2024002', 14, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jun 21, 2024\nDays Late: 630\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(15, 'L2024002', 15, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jun 28, 2024\nDays Late: 623\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(16, 'L2024002', 16, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jul 5, 2024\nDays Late: 616\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(17, 'L2024002', 17, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jul 12, 2024\nDays Late: 609\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(18, 'L2024002', 18, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jul 19, 2024\nDays Late: 602\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(19, 'L2024002', 19, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jul 26, 2024\nDays Late: 595\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(20, 'L2024002', 20, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Aug 2, 2024\nDays Late: 588\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(21, 'L2024002', 21, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Aug 9, 2024\nDays Late: 581\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(22, 'L2024002', 22, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱65.63 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Aug 16, 2024\nDays Late: 574\nLate Fee Amount: ₱65.63\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-13 21:58:27', 'sent'),
(23, 'L2024002', 0, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱328.13 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Jul 26, 2024\nDays Late: 596\nLate Fee Amount: ₱328.13\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-14 01:57:24', 'sent'),
(24, 'L2024002', 0, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱328.13 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Aug 2, 2024\nDays Late: 589\nLate Fee Amount: ₱328.13\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-14 01:57:25', 'sent'),
(25, 'L2024002', 0, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱328.13 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Aug 9, 2024\nDays Late: 582\nLate Fee Amount: ₱328.13\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-14 01:57:25', 'sent'),
(26, 'L2024002', 0, 'fee_assessed', 'email', 'coastal@marketvendor.com', 'Dear Coastal Enterprises,\n\nA late fee of ₱328.13 has been assessed on your loan payment.\n\nLoan ID: L2024002\nOriginal Due Date: Aug 16, 2024\nDays Late: 575\nLate Fee Amount: ₱328.13\n\nPlease make your payment as soon as possible to avoid additional fees.\n\nThank you,\nMarket Vendor Loan System', '2026-03-14 01:57:25', 'sent');

-- --------------------------------------------------------

--
-- Table structure for table `late_fee_settings`
--

CREATE TABLE `late_fee_settings` (
  `id` int(11) NOT NULL,
  `fee_type` enum('percentage','fixed','tiered') DEFAULT 'percentage',
  `percentage_rate` decimal(5,2) DEFAULT 5.00,
  `fixed_amount` decimal(10,2) DEFAULT 100.00,
  `grace_period_days` int(11) DEFAULT 0,
  `max_fee_percentage` decimal(5,2) DEFAULT 25.00,
  `compound_daily` tinyint(1) DEFAULT 0,
  `apply_weekends` tinyint(1) DEFAULT 1,
  `min_fee_amount` decimal(10,2) DEFAULT 50.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `late_fee_settings`
--

INSERT INTO `late_fee_settings` (`id`, `fee_type`, `percentage_rate`, `fixed_amount`, `grace_period_days`, `max_fee_percentage`, `compound_daily`, `apply_weekends`, `min_fee_amount`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'percentage', 5.00, 100.00, 3, 25.00, 1, 1, 50.00, 'Default late fee: 5% of payment amount after 3-day grace period', 1, '2026-03-13 22:21:11', '2026-03-15 02:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `late_fee_tiers`
--

CREATE TABLE `late_fee_tiers` (
  `id` int(11) NOT NULL,
  `days_from` int(11) NOT NULL,
  `days_to` int(11) NOT NULL,
  `fee_type` enum('percentage','fixed') NOT NULL,
  `fee_value` decimal(10,2) NOT NULL,
  `max_fee_amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `late_fee_tiers`
--

INSERT INTO `late_fee_tiers` (`id`, `days_from`, `days_to`, `fee_type`, `fee_value`, `max_fee_amount`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 'percentage', 2.00, 500.00, 1, '2026-03-13 22:21:20', '2026-03-13 22:21:20'),
(2, 8, 14, 'percentage', 3.00, 1000.00, 1, '2026-03-13 22:21:20', '2026-03-13 22:21:20'),
(3, 15, 30, 'percentage', 5.00, 2500.00, 1, '2026-03-13 22:21:20', '2026-03-13 22:21:20'),
(4, 31, 60, 'percentage', 7.50, 5000.00, 1, '2026-03-13 22:21:20', '2026-03-13 22:21:20'),
(5, 61, 999, 'percentage', 10.00, 10000.00, 1, '2026-03-13 22:21:20', '2026-03-13 22:21:20');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `loan_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `address` text NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_type` varchar(50) NOT NULL,
  `business_address` text NOT NULL,
  `monthly_revenue` decimal(12,2) NOT NULL,
  `business_description` text NOT NULL,
  `payment_frequency` varchar(20) NOT NULL,
  `custom_loan_amount` decimal(12,2) DEFAULT 0.00,
  `loan_amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 5.00,
  `loan_purpose` varchar(50) NOT NULL,
  `preferred_term` int(11) NOT NULL,
  `term_months` int(11) DEFAULT 12,
  `remaining_balance` decimal(12,2) DEFAULT NULL,
  `total_paid` decimal(12,2) DEFAULT 0.00,
  `collateral` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','active','completed','defaulted','rejected') DEFAULT 'pending',
  `loan_start_date` date DEFAULT NULL,
  `first_payment_date` date DEFAULT NULL,
  `next_payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `loan_id`, `user_id`, `full_name`, `email`, `phone`, `birthdate`, `address`, `civil_status`, `business_name`, `business_type`, `business_address`, `monthly_revenue`, `business_description`, `payment_frequency`, `custom_loan_amount`, `loan_amount`, `interest_rate`, `loan_purpose`, `preferred_term`, `term_months`, `remaining_balance`, `total_paid`, `collateral`, `status`, `loan_start_date`, `first_payment_date`, `next_payment_date`, `created_at`, `updated_at`) VALUES
(8, 'L2024002', 2, 'Jane Smith', 'jane.smith@email.com', '09198765432', '1985-08-22', '789 Commerce St, Manila', 'Married', 'Jane\'s Food Stall', 'Food Service', '321 Food Ave, Manila', 18000.00, 'Purchase cooking equipment', 'weekly', 0.00, 23765.61, 5.00, 'Equipment purchase', 0, 6, -421.95, 31171.95, '6', 'completed', '2024-03-01', '2024-03-08', '2024-07-26', '2026-03-08 07:27:42', '2026-03-14 02:06:44'),
(9, 'L2024003', 1, 'John Doe', 'john.doe@email.com', '09123456789', '1990-05-15', '123 Market St, Manila', 'Single', 'John\'s Sari-Sari Store', 'Retail', '456 Market Ave, Manila', 25000.00, 'Additional working capital', 'daily', 0.00, 19180.65, 5.00, 'Daily operations', 0, 0, 21354.25, 0.00, '6', 'active', '2026-03-10', '2026-03-11', '2026-03-11', '2026-03-08 07:27:42', '2026-03-14 04:24:03'),
(13, 'LOAN202600089273', 8, 'Rome Joseph Lorente', 'lorenteromejoseph@gmail.com', '09124181359', '1997-01-02', 'test city', 'married', 'cqwecqwcq', 'retail', 'cqwecqwecqwcqecqw', 20000.00, 'cqwecqwcqwcqwcqeqc', 'daily', 0.00, 17300.00, 5.00, 'inventory', 3, 3, 11925.00, 8325.00, 'cqwcewqcwqcw', 'active', '2026-03-08', '2026-03-09', '2026-04-27', '2026-03-08 07:43:00', '2026-03-15 01:16:25'),
(14, 'LOAN202600094955', 9, 'cryptical rome', 'crypticalrome@gmail.com', '09124181358', '2000-02-10', 'test city', 'single', 'cqwecqwqwecqwcqw', 'wholesale', 'cqwecqwecqwcqecqw', 10000.00, 'qwcewqvqwljkqovqjkwevqwkvqw', 'daily', 0.00, 20000.00, 5.00, 'equipment', 3, 12, NULL, 0.00, 'cqwecqwcwqcqwcqec', 'rejected', NULL, NULL, NULL, '2026-03-10 15:21:03', '2026-03-10 15:22:09');

-- --------------------------------------------------------

--
-- Table structure for table `loan_documents`
--

CREATE TABLE `loan_documents` (
  `id` int(11) NOT NULL,
  `loan_id` varchar(50) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `file_path` text NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loan_documents`
--

INSERT INTO `loan_documents` (`id`, `loan_id`, `document_type`, `file_path`, `file_name`, `file_size`, `mime_type`, `uploaded_at`) VALUES
(1, 'LOAN202600095716', 'business_permit', 'uploads/loan_documents/2026/03/LOAN202600095716_business_permit_1773156972.png', 'Gemini_Generated_Image_suvauosuvauosuva.png', 2114076, 'image/png', '2026-03-10 15:36:12'),
(2, 'LOAN202600095716', 'government_id', 'uploads/loan_documents/2026/03/LOAN202600095716_government_id_1773156972.jpg', 'unnamed.jpg', 185342, 'image/jpeg', '2026-03-10 15:36:12'),
(3, 'LOAN202600095716', 'proof_of_income', 'uploads/loan_documents/2026/03/LOAN202600095716_proof_of_income_1773156972.png', 'Gemini_Generated_Image_hrbej4hrbej4hrbe.png', 2083597, 'image/png', '2026-03-10 15:36:12'),
(4, 'LOAN202600095716', 'proof_of_address', 'uploads/loan_documents/2026/03/LOAN202600095716_proof_of_address_1773156972.png', 'Gemini_Generated_Image_hc4vmghc4vmghc4v.png', 1645478, 'image/png', '2026-03-10 15:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `notification_type` enum('payment_reminder','due_today','advance_reminder') DEFAULT 'payment_reminder',
  `loan_id` varchar(50) DEFAULT NULL,
  `days_advance` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `token_expiry` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `payment_id` varchar(50) NOT NULL,
  `loan_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `principal_paid` decimal(12,2) NOT NULL,
  `interest_paid` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `transaction_id` varchar(100) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `status` enum('completed','partial','failed') DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `payment_id`, `loan_id`, `user_id`, `borrower_name`, `payment_date`, `amount_paid`, `principal_paid`, `interest_paid`, `payment_method`, `transaction_id`, `receipt_number`, `status`, `notes`, `created_at`) VALUES
(3, 'PAY20240308001', 'L2024002', 2, 'Jane Smith', '2024-03-08', 1312.50, 1250.00, 62.50, 'GCash', 'TXN202403080001', 'RCP202403080001', 'completed', NULL, '2026-03-08 07:29:34'),
(4, 'PAY20240315002', 'L2024002', 2, 'Jane Smith', '2024-03-15', 1312.50, 1250.00, 62.50, 'Bank Transfer', 'TXN202403150002', 'RCP202403150002', 'completed', NULL, '2026-03-08 07:29:34'),
(5, 'PAY20240322003', 'L2024002', 2, 'Jane Smith', '2024-03-22', 1312.50, 1250.00, 62.50, 'GCash', 'TXN202403220003', 'RCP202403220003', 'completed', NULL, '2026-03-08 07:29:34'),
(6, 'PAY20260308084407001', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'GCash', 'TXN202603080900496940', 'RCP202603080900494650', 'completed', NULL, '2026-03-08 08:00:49'),
(7, 'PAY20260308084407002', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'GCash', 'TXN202603080900566612', 'RCP202603080900564512', 'completed', NULL, '2026-03-08 08:00:56'),
(8, 'PAY20260308084407003', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'Cash', 'TXN202603080900585061', 'RCP202603080900584302', 'completed', NULL, '2026-03-08 08:00:58'),
(9, 'PAY20260308084407004', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'GCash', 'TXN202603080901015887', 'RCP202603080901018199', 'completed', NULL, '2026-03-08 08:01:01'),
(10, 'PAY20260308084407005', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'cash', 'TXN202603081622588304', 'RCP202603081622585154', 'completed', NULL, '2026-03-08 15:22:58'),
(11, 'PAY20260308084407006', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'cash', 'TXN202603081623098809', 'RCP202603081623097710', 'completed', NULL, '2026-03-08 15:23:09'),
(12, 'PAY20260308084407007', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'cash', 'TXN202603081623189405', 'RCP202603081623182959', 'completed', NULL, '2026-03-08 15:23:18'),
(13, 'PAY20260308084407008', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'cash', 'TXN202603081624033747', 'RCP202603081624031265', 'completed', NULL, '2026-03-08 15:24:03'),
(14, 'PAY20260308084407009', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603081624074418', 'RCP202603081624076834', 'completed', NULL, '2026-03-08 15:24:07'),
(15, 'PAY20260308084407010', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-08', 225.00, 222.22, 2.78, 'cash', 'TXN202603081630043315', 'RCP202603081630045192', 'completed', NULL, '2026-03-08 15:30:04'),
(37, 'PAY202603091251244052', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091251249663', 'completed', '', '2026-03-09 11:51:24'),
(38, 'PAY202603091253471162', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091253474499', 'completed', '', '2026-03-09 11:53:47'),
(39, 'PAY202603091254192905', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091254199126', 'completed', '', '2026-03-09 11:54:19'),
(40, 'PAY202603091254252769', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091254256152', 'completed', '', '2026-03-09 11:54:25'),
(41, 'PAY202603091254596522', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091254597183', 'completed', '', '2026-03-09 11:54:59'),
(42, 'PAY202603091255355640', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091255358564', 'completed', '', '2026-03-09 11:55:35'),
(43, 'PAY202603091256248614', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091256249690', 'completed', '', '2026-03-09 11:56:24'),
(44, 'PAY202603091258052856', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091258051537', 'completed', '', '2026-03-09 11:58:05'),
(45, 'PAY202603091259141726', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091259143539', 'completed', '', '2026-03-09 11:59:14'),
(46, 'PAY202603091259278733', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091259275229', 'completed', '', '2026-03-09 11:59:27'),
(47, 'PAY202603091300044950', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-09', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603091300046727', 'completed', '', '2026-03-09 12:00:04'),
(48, 'PAY202603091304038283', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-09', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603091304034858', 'completed', '', '2026-03-09 12:04:03'),
(49, 'PAY202603091304223363', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091304223673', 'completed', '', '2026-03-09 12:04:22'),
(50, 'PAY202603091310147568', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091310143246', 'completed', '', '2026-03-09 12:10:14'),
(51, 'PAY202603091311539451', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091311539627', 'completed', '', '2026-03-09 12:11:53'),
(52, 'PAY202603091312175759', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091312174313', 'completed', '', '2026-03-09 12:12:17'),
(53, 'PAY202603091315223640', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091315227917', 'completed', '', '2026-03-09 12:15:22'),
(54, 'PAY202603091316001792', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-09', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603091316006221', 'completed', '', '2026-03-09 12:16:00'),
(55, 'PAY202603091320076185', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091320078136', 'completed', '', '2026-03-09 12:20:07'),
(56, 'PAY202603091321152094', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091321157678', 'completed', '', '2026-03-09 12:21:15'),
(57, 'PAY202603091321586454', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091321587970', 'completed', '', '2026-03-09 12:21:58'),
(58, 'PAY202603091322202311', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091322206387', 'completed', '', '2026-03-09 12:22:20'),
(59, 'PAY202603091333054529', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091333055059', 'completed', '', '2026-03-09 12:33:05'),
(60, 'PAY20260308084407014', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-09', 225.00, 222.22, 2.78, 'cash', 'TXN202603091426546184', 'RCP202603091426543553', 'completed', NULL, '2026-03-09 13:26:54'),
(61, 'PAY20260308084407015', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-09', 225.00, 222.22, 2.78, 'cash', 'TXN202603091427056264', 'RCP202603091427053648', 'completed', NULL, '2026-03-09 13:27:05'),
(62, 'PAY202603091433395657', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091433392154', 'completed', '', '2026-03-09 13:33:39'),
(63, 'PAY202603091500568239', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091500563498', 'completed', '', '2026-03-09 14:00:56'),
(64, 'PAY202603091502478036', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091502479785', 'completed', '', '2026-03-09 14:02:47'),
(65, 'PAY202603091504352578', 'L2024001', 1, 'System Administrator', '2026-03-09', 4375.00, 0.00, 0.00, 'cash', NULL, 'RCP202603091504358848', 'completed', '', '2026-03-09 14:04:35'),
(66, 'PAY202603091547526905', 'L2024003', 1, 'System Administrator', '2026-03-09', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603091547524461', 'completed', '', '2026-03-09 14:47:52'),
(67, 'PAY20260308084407016', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-10', 225.00, 222.22, 2.78, 'cash', 'TXN202603100018232867', 'RCP202603100018238509', 'completed', NULL, '2026-03-09 23:18:23'),
(68, 'PAY20260308084407017', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-10', 225.00, 222.22, 2.78, 'cash', 'TXN202603100018293050', 'RCP202603100018292973', 'completed', NULL, '2026-03-09 23:18:29'),
(69, 'PAY20260308084407038', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-10', 225.00, 222.22, 2.78, 'cash', 'TXN202603100337226264', 'RCP202603100337229137', 'completed', NULL, '2026-03-10 02:37:22'),
(70, 'PAY202603100340301713', 'L2024003', 1, 'System Administrator', '2026-03-10', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603100340309794', 'completed', '', '2026-03-10 02:40:30'),
(71, 'PAY20260308084407018', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-10', 225.00, 222.22, 2.78, 'cash', 'TXN202603100343185728', 'RCP202603100343181160', 'completed', NULL, '2026-03-10 02:43:18'),
(72, 'PAY20260308084407019', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-10', 225.00, 222.22, 2.78, 'cash', 'TXN202603100344339960', 'RCP202603100344332469', 'completed', NULL, '2026-03-10 02:44:33'),
(73, 'PAY202603120009173788', 'L2024003', 1, 'System Administrator', '2026-03-12', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603120009173568', 'completed', '', '2026-03-11 23:09:17'),
(74, 'PAY202603121317191941', 'L2024003', 1, 'System Administrator', '2026-03-12', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603121317198212', 'completed', '', '2026-03-12 12:17:19'),
(75, 'PAY202603121317267760', 'L2024003', 1, 'System Administrator', '2026-03-12', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603121317263566', 'completed', '', '2026-03-12 12:17:26'),
(76, 'PAY202603130127389770', 'L2024003', 1, 'System Administrator', '2026-03-13', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603130127381239', 'completed', '', '2026-03-13 00:27:38'),
(77, 'PAY20260308084407020', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-13', 225.00, 222.22, 2.78, 'cash', 'TXN202603130129044543', 'RCP202603130129048466', 'completed', NULL, '2026-03-13 00:29:04'),
(78, 'PAY20240607014', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1312.50, 1250.00, 62.50, 'cash', 'TXN202603132300395643', 'RCP202603132300391536', 'completed', NULL, '2026-03-13 22:00:39'),
(79, 'PAY20240329004', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1312.50, 1250.00, 62.50, 'bank_transfer', 'TXN202603132303123019', 'RCP202603132303121069', 'completed', NULL, '2026-03-13 22:03:12'),
(80, 'PAY202603132337523838', 'L2024003', 1, 'System Administrator', '2026-03-13', 145.83, 0.00, 0.00, 'cash', NULL, 'RCP202603132337526349', 'completed', '', '2026-03-13 22:37:52'),
(81, 'PAY20240405005', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140014373268', 'RCP202603140014372507', 'completed', NULL, '2026-03-13 23:14:37'),
(82, 'PAY20240412006', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140015566587', 'RCP202603140015565980', 'completed', NULL, '2026-03-13 23:15:56'),
(83, 'PAY20240419007', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140015594336', 'RCP202603140015597645', 'completed', NULL, '2026-03-13 23:15:59'),
(84, 'PAY20240426008', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016022590', 'RCP202603140016026403', 'completed', NULL, '2026-03-13 23:16:02'),
(85, 'PAY20240503009', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016052633', 'RCP202603140016056626', 'completed', NULL, '2026-03-13 23:16:05'),
(86, 'PAY20240510010', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016087886', 'RCP202603140016086241', 'completed', NULL, '2026-03-13 23:16:08'),
(87, 'PAY20240517011', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016106863', 'RCP202603140016105453', 'completed', NULL, '2026-03-13 23:16:10'),
(88, 'PAY20240524012', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016138602', 'RCP202603140016137496', 'completed', NULL, '2026-03-13 23:16:13'),
(89, 'PAY20240531013', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016161446', 'RCP202603140016162910', 'completed', NULL, '2026-03-13 23:16:16'),
(90, 'PAY20240614015', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016199153', 'RCP202603140016195924', 'completed', NULL, '2026-03-13 23:16:19'),
(91, 'PAY20240621016', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016213637', 'RCP202603140016217375', 'completed', NULL, '2026-03-13 23:16:21'),
(92, 'PAY20240628017', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016258415', 'RCP202603140016254921', 'completed', NULL, '2026-03-13 23:16:25'),
(93, 'PAY20240705018', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016275649', 'RCP202603140016277120', 'completed', NULL, '2026-03-13 23:16:27'),
(94, 'PAY20240712019', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016302728', 'RCP202603140016308669', 'completed', NULL, '2026-03-13 23:16:30'),
(95, 'PAY20240719020', 'L2024002', 2, 'Jane Smith', '2026-03-14', 1640.63, 1250.00, 62.50, 'cash', 'TXN202603140016337796', 'RCP202603140016336865', 'completed', NULL, '2026-03-13 23:16:33'),
(96, 'PAY202603140258005280', 'L2024002', 2, 'Coastal Enterprises', '2026-03-14', 1312.50, 0.00, 0.00, 'cash', NULL, 'RCP202603140258009971', 'completed', '', '2026-03-14 01:58:00'),
(97, 'PAY202603140258043567', 'L2024002', 2, 'Coastal Enterprises', '2026-03-14', 1312.50, 0.00, 0.00, 'cash', NULL, 'RCP202603140258049554', 'completed', '', '2026-03-14 01:58:04'),
(98, 'PAY202603140301502992', 'L2024002', 2, 'Coastal Enterprises', '2026-03-14', 1640.63, 0.00, 0.00, 'cash', NULL, 'RCP202603140301506873', 'completed', '', '2026-03-14 02:01:50'),
(99, 'PAY202603140306058450', 'L2024003', 1, 'System Administrator', '2026-03-14', 62.29, 0.00, 0.00, 'cash', NULL, 'RCP202603140306058079', 'completed', '', '2026-03-14 02:06:05'),
(100, 'PAY202603140306298178', 'L2024003', 1, 'System Administrator', '2026-03-14', 68.22, 0.00, 0.00, 'cash', NULL, 'RCP202603140306292183', 'completed', '', '2026-03-14 02:06:29'),
(101, 'PAY202603140306442021', 'L2024002', 2, 'Coastal Enterprises', '2026-03-14', 1968.76, 0.00, 0.00, 'cash', NULL, 'RCP202603140306446676', 'completed', '', '2026-03-14 02:06:44'),
(102, 'PAY202603140306595489', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140306592097', 'completed', '', '2026-03-14 02:06:59'),
(103, 'PAY202603140307103037', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140307101731', 'completed', '', '2026-03-14 02:07:10'),
(104, 'PAY202603140307164462', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140307169120', 'completed', '', '2026-03-14 02:07:16'),
(105, 'PAY202603140309313634', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140309311154', 'completed', '', '2026-03-14 02:09:31'),
(106, 'PAY202603140309507102', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140309504519', 'completed', '', '2026-03-14 02:09:50'),
(107, 'PAY202603140311145093', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140311148318', 'completed', '', '2026-03-14 02:11:14'),
(108, 'PAY202603140311248034', 'L2024003', 1, 'System Administrator', '2026-03-14', 65.25, 0.00, 0.00, 'cash', NULL, 'RCP202603140311249822', 'completed', '', '2026-03-14 02:11:24'),
(109, 'PAY202603140314304249', 'L2024003', 1, 'System Administrator', '2026-03-14', 62.29, 0.00, 0.00, 'cash', NULL, 'RCP202603140314306318', 'completed', '', '2026-03-14 02:14:30'),
(110, 'PAY202603140327479440', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 0.00, 0.00, 'cash', NULL, 'RCP202603140327471359', 'completed', '', '2026-03-14 02:27:47'),
(111, 'PAY202603140402052772', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 0.00, 0.00, 'cash', NULL, 'RCP202603140402057601', 'completed', '', '2026-03-14 03:02:05'),
(112, 'PAY202603140407256374', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 0.00, 0.00, 'cash', NULL, 'RCP202603140407251166', 'completed', '', '2026-03-14 03:07:25'),
(113, 'PAY202603140413282600', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140413281251', 'completed', '', '2026-03-14 03:13:28'),
(114, 'PAY202603140419132655', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 0.00, 0.00, 'cash', NULL, 'RCP202603140419136576', 'completed', '', '2026-03-14 03:19:13'),
(115, 'PAY202603140420315316', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140420313946', 'completed', '', '2026-03-14 03:20:31'),
(116, 'PAY202603140420456592', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 224.97, 0.03, 'cash', NULL, 'RCP202603140420459847', 'completed', '', '2026-03-14 03:20:45'),
(117, 'PAY202603140423237314', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140423232001', 'completed', '', '2026-03-14 03:23:23'),
(118, 'PAY202603140444314630', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140444316331', 'completed', '', '2026-03-14 03:44:31'),
(119, 'PAY202603140447393517', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140447391779', 'completed', '', '2026-03-14 03:47:39'),
(120, 'PAY202603140448038471', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 224.97, 0.03, 'cash', NULL, 'RCP202603140448034697', 'completed', '', '2026-03-14 03:48:03'),
(121, 'PAY202603140454139991', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140454132460', 'completed', '', '2026-03-14 03:54:13'),
(122, 'PAY202603140455303795', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140455303712', 'completed', '', '2026-03-14 03:55:30'),
(123, 'PAY202603140457575314', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140457573826', 'completed', '', '2026-03-14 03:57:57'),
(124, 'PAY202603140458094793', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140458097786', 'completed', '', '2026-03-14 03:58:09'),
(125, 'PAY202603140501441020', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140501449667', 'completed', '', '2026-03-14 04:01:44'),
(126, 'PAY202603140506054683', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140506059453', 'completed', '', '2026-03-14 04:06:05'),
(127, 'PAY202603140516293733', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140516291205', 'completed', '', '2026-03-14 04:16:29'),
(128, 'PAY202603140517166599', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140517166757', 'completed', '', '2026-03-14 04:17:16'),
(129, 'PAY202603140517381216', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140517381325', 'completed', '', '2026-03-14 04:17:38'),
(130, 'PAY202603140517525452', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140517521151', 'completed', '', '2026-03-14 04:17:52'),
(131, 'PAY202603140518502489', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140518502008', 'completed', '', '2026-03-14 04:18:50'),
(132, 'PAY20260308084407031', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 222.22, 2.78, 'cash', 'TXN202603140519244546', 'RCP202603140519248498', 'completed', NULL, '2026-03-14 04:19:24'),
(133, 'PAY202603140524032237', 'L2024003', 1, 'System Administrator', '2026-03-14', 59.32, 59.31, 0.01, 'cash', NULL, 'RCP202603140524031242', 'completed', '', '2026-03-14 04:24:03'),
(134, 'PAY20260308084407030', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603140525395676', 'RCP202603140525399949', 'completed', NULL, '2026-03-14 04:25:39'),
(135, 'PAY20260308084407032', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150155585102', 'RCP202603150155586956', 'completed', NULL, '2026-03-15 00:55:58'),
(136, 'PAY20260308084407033', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150207325410', 'RCP202603150207321792', 'completed', NULL, '2026-03-15 01:07:32'),
(137, 'PAY20260308084407034', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150208086976', 'RCP202603150208088096', 'completed', NULL, '2026-03-15 01:08:08'),
(138, 'PAY20260308084407035', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150208166729', 'RCP202603150208168837', 'completed', NULL, '2026-03-15 01:08:16'),
(139, 'PAY20260308084407036', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150208568142', 'RCP202603150208566367', 'completed', NULL, '2026-03-15 01:08:56'),
(140, 'PAY20260308084407037', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150209322738', 'RCP202603150209326279', 'completed', NULL, '2026-03-15 01:09:32'),
(141, 'PAY20260308084407046', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150209527493', 'RCP202603150209522078', 'completed', NULL, '2026-03-15 01:09:52'),
(142, 'PAY20260308084407039', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150210039734', 'RCP202603150210033902', 'completed', NULL, '2026-03-15 01:10:03'),
(143, 'PAY20260308084407040', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'gcash', 'TXN202603150210087712', 'RCP202603150210086288', 'completed', NULL, '2026-03-15 01:10:08'),
(144, 'PAY20260308084407041', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150210181680', 'RCP202603150210188467', 'completed', NULL, '2026-03-15 01:10:18'),
(145, 'PAY20260308084407042', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150212319663', 'RCP202603150212317322', 'completed', NULL, '2026-03-15 01:12:31'),
(146, 'PAY20260308084407043', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150212519464', 'RCP202603150212514150', 'completed', NULL, '2026-03-15 01:12:51'),
(147, 'PAY20260308084407044', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150213024814', 'RCP202603150213021867', 'completed', NULL, '2026-03-15 01:13:02'),
(148, 'PAY20260308084407045', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150214142864', 'RCP202603150214145713', 'completed', NULL, '2026-03-15 01:14:14'),
(149, 'PAY20260308084407047', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150214426665', 'RCP202603150214426729', 'completed', NULL, '2026-03-15 01:14:42'),
(150, 'PAY20260308084407048', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150215569992', 'RCP202603150215565641', 'completed', NULL, '2026-03-15 01:15:56'),
(151, 'PAY20260308084407049', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 225.00, 222.22, 2.78, 'bank_transfer', 'TXN202603150216257930', 'RCP202603150216253504', 'completed', NULL, '2026-03-15 01:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `payment_schedules`
--

CREATE TABLE `payment_schedules` (
  `id` int(11) NOT NULL,
  `payment_id` varchar(50) NOT NULL,
  `loan_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `principal_amount` decimal(12,2) NOT NULL,
  `interest_amount` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) DEFAULT 0.00,
  `status` enum('pending','paid','overdue','scheduled') DEFAULT 'pending',
  `days_overdue` int(11) DEFAULT 0,
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_schedules`
--

INSERT INTO `payment_schedules` (`id`, `payment_id`, `loan_id`, `user_id`, `borrower_name`, `due_date`, `principal_amount`, `interest_amount`, `total_amount`, `amount_paid`, `status`, `days_overdue`, `payment_date`, `created_at`, `updated_at`) VALUES
(21, 'PAY20240308001', 'L2024002', 2, 'Jane Smith', '2024-03-08', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, '2026-03-08 07:29:34', '2026-03-08 07:29:34', '2026-03-08 07:29:34'),
(22, 'PAY20240315002', 'L2024002', 2, 'Jane Smith', '2024-03-15', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, '2026-03-08 07:29:34', '2026-03-08 07:29:34', '2026-03-08 07:29:34'),
(23, 'PAY20240322003', 'L2024002', 2, 'Jane Smith', '2024-03-22', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, '2026-03-08 07:29:34', '2026-03-08 07:29:34', '2026-03-08 07:29:34'),
(24, 'PAY20240329004', 'L2024002', 2, 'Jane Smith', '2024-03-29', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 22:03:12'),
(25, 'PAY20240405005', 'L2024002', 2, 'Jane Smith', '2024-04-05', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:14:37'),
(26, 'PAY20240412006', 'L2024002', 2, 'Jane Smith', '2024-04-12', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:15:56'),
(27, 'PAY20240419007', 'L2024002', 2, 'Jane Smith', '2024-04-19', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:15:59'),
(28, 'PAY20240426008', 'L2024002', 2, 'Jane Smith', '2024-04-26', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:02'),
(29, 'PAY20240503009', 'L2024002', 2, 'Jane Smith', '2024-05-03', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:05'),
(30, 'PAY20240510010', 'L2024002', 2, 'Jane Smith', '2024-05-10', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:08'),
(31, 'PAY20240517011', 'L2024002', 2, 'Jane Smith', '2024-05-17', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:10'),
(32, 'PAY20240524012', 'L2024002', 2, 'Jane Smith', '2024-05-24', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:13'),
(33, 'PAY20240531013', 'L2024002', 2, 'Jane Smith', '2024-05-31', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:16'),
(34, 'PAY20240607014', 'L2024002', 2, 'Jane Smith', '2024-06-07', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 22:00:39'),
(35, 'PAY20240614015', 'L2024002', 2, 'Jane Smith', '2024-06-14', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:19'),
(36, 'PAY20240621016', 'L2024002', 2, 'Jane Smith', '2024-06-21', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:21'),
(37, 'PAY20240628017', 'L2024002', 2, 'Jane Smith', '2024-06-28', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:25'),
(38, 'PAY20240705018', 'L2024002', 2, 'Jane Smith', '2024-07-05', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:27'),
(39, 'PAY20240712019', 'L2024002', 2, 'Jane Smith', '2024-07-12', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:30'),
(40, 'PAY20240719020', 'L2024002', 2, 'Jane Smith', '2024-07-19', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 23:16:33'),
(41, 'PAY20240726021', 'L2024002', 2, 'Jane Smith', '2024-07-26', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-14 01:58:00'),
(42, 'PAY20240802022', 'L2024002', 2, 'Jane Smith', '2024-08-02', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-14 01:58:04'),
(43, 'PAY20240809023', 'L2024002', 2, 'Jane Smith', '2024-08-09', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-14 02:01:50'),
(44, 'PAY20240816024', 'L2024002', 2, 'Jane Smith', '2024-08-16', 1250.00, 62.50, 1312.50, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-14 02:06:44'),
(45, 'PAY20240309001', 'L2024003', 1, 'John Doe', '2024-03-09', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:51:24'),
(46, 'PAY20240310002', 'L2024003', 1, 'John Doe', '2024-03-10', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:53:47'),
(47, 'PAY20240311003', 'L2024003', 1, 'John Doe', '2024-03-11', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:54:19'),
(48, 'PAY20240312004', 'L2024003', 1, 'John Doe', '2024-03-12', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:54:25'),
(49, 'PAY20240313005', 'L2024003', 1, 'John Doe', '2024-03-13', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:54:59'),
(50, 'PAY20240314006', 'L2024003', 1, 'John Doe', '2024-03-14', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:55:35'),
(51, 'PAY20240315007', 'L2024003', 1, 'John Doe', '2024-03-15', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:56:24'),
(52, 'PAY20240316008', 'L2024003', 1, 'John Doe', '2024-03-16', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:58:05'),
(53, 'PAY20240317009', 'L2024003', 1, 'John Doe', '2024-03-17', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:59:14'),
(54, 'PAY20240318010', 'L2024003', 1, 'John Doe', '2024-03-18', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 11:59:27'),
(55, 'PAY20240319011', 'L2024003', 1, 'John Doe', '2024-03-19', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:04:22'),
(56, 'PAY20240320012', 'L2024003', 1, 'John Doe', '2024-03-20', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:10:14'),
(57, 'PAY20240321013', 'L2024003', 1, 'John Doe', '2024-03-21', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:11:53'),
(58, 'PAY20240322014', 'L2024003', 1, 'John Doe', '2024-03-22', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:12:17'),
(59, 'PAY20240323015', 'L2024003', 1, 'John Doe', '2024-03-23', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:15:22'),
(60, 'PAY20240324016', 'L2024003', 1, 'John Doe', '2024-03-24', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:20:07'),
(61, 'PAY20240325017', 'L2024003', 1, 'John Doe', '2024-03-25', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:21:15'),
(62, 'PAY20240326018', 'L2024003', 1, 'John Doe', '2024-03-26', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:21:58'),
(63, 'PAY20240327019', 'L2024003', 1, 'John Doe', '2024-03-27', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:22:20'),
(64, 'PAY20240328020', 'L2024003', 1, 'John Doe', '2024-03-28', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 12:33:05'),
(65, 'PAY20240329021', 'L2024003', 1, 'John Doe', '2024-03-29', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 13:33:39'),
(66, 'PAY20240330022', 'L2024003', 1, 'John Doe', '2024-03-30', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 14:00:56'),
(67, 'PAY20240331023', 'L2024003', 1, 'John Doe', '2024-03-31', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 14:02:47'),
(68, 'PAY20240401024', 'L2024003', 1, 'John Doe', '2024-04-01', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-09 14:47:52'),
(69, 'PAY20240402025', 'L2024003', 1, 'John Doe', '2024-04-02', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-10 02:40:30'),
(70, 'PAY20240403026', 'L2024003', 1, 'John Doe', '2024-04-03', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-11 23:09:17'),
(71, 'PAY20240404027', 'L2024003', 1, 'John Doe', '2024-04-04', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-12 12:17:19'),
(72, 'PAY20240405028', 'L2024003', 1, 'John Doe', '2024-04-05', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-12 12:17:26'),
(73, 'PAY20240406029', 'L2024003', 1, 'John Doe', '2024-04-06', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 00:27:38'),
(74, 'PAY20240407030', 'L2024003', 1, 'John Doe', '2024-04-07', 138.89, 6.94, 145.83, 0.00, 'paid', 0, NULL, '2026-03-08 07:29:34', '2026-03-13 22:37:52'),
(75, 'PAY20260308084407001', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-09', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 08:00:49'),
(76, 'PAY20260308084407002', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-10', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 08:00:56'),
(77, 'PAY20260308084407003', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-11', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 08:00:58'),
(78, 'PAY20260308084407004', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-12', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 08:01:01'),
(79, 'PAY20260308084407005', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-13', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 15:22:58'),
(80, 'PAY20260308084407006', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-14', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 15:23:09'),
(81, 'PAY20260308084407007', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-15', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 15:23:18'),
(82, 'PAY20260308084407008', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-16', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 15:24:03'),
(83, 'PAY20260308084407009', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-17', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 15:24:07'),
(84, 'PAY20260308084407010', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-18', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 15:30:04'),
(85, 'PAY20260308084407011', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-19', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 12:00:04'),
(86, 'PAY20260308084407012', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-20', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 12:04:03'),
(87, 'PAY20260308084407013', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-21', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 12:16:00'),
(88, 'PAY20260308084407014', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-22', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 13:26:54'),
(89, 'PAY20260308084407015', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-23', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 13:27:05'),
(90, 'PAY20260308084407016', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-24', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 23:18:23'),
(91, 'PAY20260308084407017', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-25', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-09 23:18:29'),
(92, 'PAY20260308084407018', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-26', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-10 02:43:18'),
(93, 'PAY20260308084407019', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-27', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-10 02:44:33'),
(94, 'PAY20260308084407020', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-28', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-13 00:29:04'),
(95, 'PAY20260308084407021', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-29', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 02:06:59'),
(96, 'PAY20260308084407022', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-30', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 02:07:10'),
(97, 'PAY20260308084407023', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-03-31', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 02:07:16'),
(98, 'PAY20260308084407024', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-01', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 02:09:31'),
(99, 'PAY20260308084407025', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-02', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 02:09:50'),
(100, 'PAY20260308084407026', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-03', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 02:11:14'),
(101, 'PAY20260308084407027', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-04', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 03:19:13'),
(102, 'PAY20260308084407028', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-05', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 03:20:45'),
(103, 'PAY20260308084407029', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-06', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 03:48:03'),
(104, 'PAY20260308084407030', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-07', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 04:25:39'),
(105, 'PAY20260308084407031', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-08', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-14 04:19:24'),
(106, 'PAY20260308084407032', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-09', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 00:55:58'),
(107, 'PAY20260308084407033', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-10', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:07:32'),
(108, 'PAY20260308084407034', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-11', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:08:08'),
(109, 'PAY20260308084407035', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-12', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:08:16'),
(110, 'PAY20260308084407036', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-13', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:08:56'),
(111, 'PAY20260308084407037', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-14', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:09:32'),
(112, 'PAY20260308084407038', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-15', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-10 02:37:22'),
(113, 'PAY20260308084407039', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-16', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:10:03'),
(114, 'PAY20260308084407040', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-17', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:10:08'),
(115, 'PAY20260308084407041', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-18', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:10:18'),
(116, 'PAY20260308084407042', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-19', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:12:31'),
(117, 'PAY20260308084407043', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-20', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:12:51'),
(118, 'PAY20260308084407044', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-21', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:13:02'),
(119, 'PAY20260308084407045', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-22', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:14:14'),
(120, 'PAY20260308084407046', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-23', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:09:52'),
(121, 'PAY20260308084407047', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-24', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:14:42'),
(122, 'PAY20260308084407048', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-25', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:15:56'),
(123, 'PAY20260308084407049', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-26', 222.22, 2.78, 225.00, 0.00, 'paid', 0, NULL, '2026-03-08 07:44:07', '2026-03-15 01:16:25'),
(124, 'PAY20260308084407050', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-27', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(125, 'PAY20260308084407051', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-28', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(126, 'PAY20260308084407052', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-29', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(127, 'PAY20260308084407053', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-04-30', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(128, 'PAY20260308084407054', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-01', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(129, 'PAY20260308084407055', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-02', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(130, 'PAY20260308084407056', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-03', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(131, 'PAY20260308084407057', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-04', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(132, 'PAY20260308084407058', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-05', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(133, 'PAY20260308084407059', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-06', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(134, 'PAY20260308084407060', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-07', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(135, 'PAY20260308084407061', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-08', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(136, 'PAY20260308084407062', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-09', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(137, 'PAY20260308084407063', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-10', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(138, 'PAY20260308084407064', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-11', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(139, 'PAY20260308084407065', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-12', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(140, 'PAY20260308084407066', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-13', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(141, 'PAY20260308084407067', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-14', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(142, 'PAY20260308084407068', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-15', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(143, 'PAY20260308084407069', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-16', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(144, 'PAY20260308084407070', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-17', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(145, 'PAY20260308084407071', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-18', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(146, 'PAY20260308084407072', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-19', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(147, 'PAY20260308084407073', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-20', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(148, 'PAY20260308084407074', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-21', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(149, 'PAY20260308084407075', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-22', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(150, 'PAY20260308084407076', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-23', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(151, 'PAY20260308084407077', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-24', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(152, 'PAY20260308084407078', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-25', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(153, 'PAY20260308084407079', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-26', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(154, 'PAY20260308084407080', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-27', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(155, 'PAY20260308084407081', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-28', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(156, 'PAY20260308084407082', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-29', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(157, 'PAY20260308084407083', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-30', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(158, 'PAY20260308084407084', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-05-31', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(159, 'PAY20260308084407085', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-06-01', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(160, 'PAY20260308084407086', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-06-02', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(161, 'PAY20260308084407087', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-06-03', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(162, 'PAY20260308084407088', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-06-04', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(163, 'PAY20260308084407089', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-06-05', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(164, 'PAY20260308084407090', 'LOAN202600089273', 8, 'Rome Joseph Lorente', '2026-06-06', 222.22, 2.78, 225.00, 0.00, 'pending', 0, NULL, '2026-03-08 07:44:07', '2026-03-08 07:44:07'),
(165, 'PAY20260310141359001', 'L2024003', 1, 'John Doe', '2026-03-11', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 02:06:05'),
(166, 'PAY20260310141359002', 'L2024003', 1, 'John Doe', '2026-03-12', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 02:06:29'),
(167, 'PAY20260310141359003', 'L2024003', 1, 'John Doe', '2026-03-13', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 02:11:24'),
(168, 'PAY20260310141359004', 'L2024003', 1, 'John Doe', '2026-03-14', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 02:14:30'),
(169, 'PAY20260310141359005', 'L2024003', 1, 'John Doe', '2026-03-15', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 02:27:47'),
(170, 'PAY20260310141359006', 'L2024003', 1, 'John Doe', '2026-03-16', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:02:05'),
(171, 'PAY20260310141359007', 'L2024003', 1, 'John Doe', '2026-03-17', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:07:25'),
(172, 'PAY20260310141359008', 'L2024003', 1, 'John Doe', '2026-03-18', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:13:29'),
(173, 'PAY20260310141359009', 'L2024003', 1, 'John Doe', '2026-03-19', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:20:31'),
(174, 'PAY20260310141359010', 'L2024003', 1, 'John Doe', '2026-03-20', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:23:23'),
(175, 'PAY20260310141359011', 'L2024003', 1, 'John Doe', '2026-03-21', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:44:31'),
(176, 'PAY20260310141359012', 'L2024003', 1, 'John Doe', '2026-03-22', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:47:39'),
(177, 'PAY20260310141359013', 'L2024003', 1, 'John Doe', '2026-03-23', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:54:13'),
(178, 'PAY20260310141359014', 'L2024003', 1, 'John Doe', '2026-03-24', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:55:30'),
(179, 'PAY20260310141359015', 'L2024003', 1, 'John Doe', '2026-03-25', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:57:57'),
(180, 'PAY20260310141359016', 'L2024003', 1, 'John Doe', '2026-03-26', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:13:59', '2026-03-14 03:58:09'),
(181, 'PAY20260310141400017', 'L2024003', 1, 'John Doe', '2026-03-27', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:01:44'),
(182, 'PAY20260310141400018', 'L2024003', 1, 'John Doe', '2026-03-28', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:06:05'),
(183, 'PAY20260310141400019', 'L2024003', 1, 'John Doe', '2026-03-29', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:16:29'),
(184, 'PAY20260310141400020', 'L2024003', 1, 'John Doe', '2026-03-30', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:17:16'),
(185, 'PAY20260310141400021', 'L2024003', 1, 'John Doe', '2026-03-31', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:17:38'),
(186, 'PAY20260310141400022', 'L2024003', 1, 'John Doe', '2026-04-01', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:17:53'),
(187, 'PAY20260310141400023', 'L2024003', 1, 'John Doe', '2026-04-02', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:18:50'),
(188, 'PAY20260310141400024', 'L2024003', 1, 'John Doe', '2026-04-03', 59.32, 0.00, 59.32, 0.00, 'paid', 0, NULL, '2026-03-10 13:14:00', '2026-03-14 04:24:03'),
(189, 'PAY20260310141400025', 'L2024003', 1, 'John Doe', '2026-04-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(190, 'PAY20260310141400026', 'L2024003', 1, 'John Doe', '2026-04-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(191, 'PAY20260310141400027', 'L2024003', 1, 'John Doe', '2026-04-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(192, 'PAY20260310141400028', 'L2024003', 1, 'John Doe', '2026-04-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(193, 'PAY20260310141400029', 'L2024003', 1, 'John Doe', '2026-04-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(194, 'PAY20260310141400030', 'L2024003', 1, 'John Doe', '2026-04-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(195, 'PAY20260310141400031', 'L2024003', 1, 'John Doe', '2026-04-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(196, 'PAY20260310141400032', 'L2024003', 1, 'John Doe', '2026-04-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(197, 'PAY20260310141400033', 'L2024003', 1, 'John Doe', '2026-04-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(198, 'PAY20260310141400034', 'L2024003', 1, 'John Doe', '2026-04-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(199, 'PAY20260310141400035', 'L2024003', 1, 'John Doe', '2026-04-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(200, 'PAY20260310141400036', 'L2024003', 1, 'John Doe', '2026-04-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(201, 'PAY20260310141400037', 'L2024003', 1, 'John Doe', '2026-04-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(202, 'PAY20260310141400038', 'L2024003', 1, 'John Doe', '2026-04-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(203, 'PAY20260310141400039', 'L2024003', 1, 'John Doe', '2026-04-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(204, 'PAY20260310141400040', 'L2024003', 1, 'John Doe', '2026-04-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(205, 'PAY20260310141400041', 'L2024003', 1, 'John Doe', '2026-04-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(206, 'PAY20260310141400042', 'L2024003', 1, 'John Doe', '2026-04-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(207, 'PAY20260310141400043', 'L2024003', 1, 'John Doe', '2026-04-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(208, 'PAY20260310141400044', 'L2024003', 1, 'John Doe', '2026-04-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(209, 'PAY20260310141400045', 'L2024003', 1, 'John Doe', '2026-04-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(210, 'PAY20260310141400046', 'L2024003', 1, 'John Doe', '2026-04-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(211, 'PAY20260310141400047', 'L2024003', 1, 'John Doe', '2026-04-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(212, 'PAY20260310141400048', 'L2024003', 1, 'John Doe', '2026-04-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(213, 'PAY20260310141400049', 'L2024003', 1, 'John Doe', '2026-04-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(214, 'PAY20260310141400050', 'L2024003', 1, 'John Doe', '2026-04-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(215, 'PAY20260310141400051', 'L2024003', 1, 'John Doe', '2026-04-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(216, 'PAY20260310141400052', 'L2024003', 1, 'John Doe', '2026-05-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(217, 'PAY20260310141400053', 'L2024003', 1, 'John Doe', '2026-05-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(218, 'PAY20260310141400054', 'L2024003', 1, 'John Doe', '2026-05-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(219, 'PAY20260310141400055', 'L2024003', 1, 'John Doe', '2026-05-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(220, 'PAY20260310141400056', 'L2024003', 1, 'John Doe', '2026-05-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(221, 'PAY20260310141400057', 'L2024003', 1, 'John Doe', '2026-05-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(222, 'PAY20260310141400058', 'L2024003', 1, 'John Doe', '2026-05-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(223, 'PAY20260310141400059', 'L2024003', 1, 'John Doe', '2026-05-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(224, 'PAY20260310141400060', 'L2024003', 1, 'John Doe', '2026-05-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(225, 'PAY20260310141400061', 'L2024003', 1, 'John Doe', '2026-05-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(226, 'PAY20260310141400062', 'L2024003', 1, 'John Doe', '2026-05-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(227, 'PAY20260310141400063', 'L2024003', 1, 'John Doe', '2026-05-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(228, 'PAY20260310141400064', 'L2024003', 1, 'John Doe', '2026-05-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(229, 'PAY20260310141400065', 'L2024003', 1, 'John Doe', '2026-05-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(230, 'PAY20260310141400066', 'L2024003', 1, 'John Doe', '2026-05-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(231, 'PAY20260310141400067', 'L2024003', 1, 'John Doe', '2026-05-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(232, 'PAY20260310141400068', 'L2024003', 1, 'John Doe', '2026-05-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(233, 'PAY20260310141400069', 'L2024003', 1, 'John Doe', '2026-05-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(234, 'PAY20260310141400070', 'L2024003', 1, 'John Doe', '2026-05-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(235, 'PAY20260310141400071', 'L2024003', 1, 'John Doe', '2026-05-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(236, 'PAY20260310141400072', 'L2024003', 1, 'John Doe', '2026-05-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(237, 'PAY20260310141400073', 'L2024003', 1, 'John Doe', '2026-05-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(238, 'PAY20260310141400074', 'L2024003', 1, 'John Doe', '2026-05-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(239, 'PAY20260310141400075', 'L2024003', 1, 'John Doe', '2026-05-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(240, 'PAY20260310141400076', 'L2024003', 1, 'John Doe', '2026-05-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(241, 'PAY20260310141400077', 'L2024003', 1, 'John Doe', '2026-05-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(242, 'PAY20260310141400078', 'L2024003', 1, 'John Doe', '2026-05-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(243, 'PAY20260310141400079', 'L2024003', 1, 'John Doe', '2026-05-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(244, 'PAY20260310141400080', 'L2024003', 1, 'John Doe', '2026-05-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(245, 'PAY20260310141400081', 'L2024003', 1, 'John Doe', '2026-05-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(246, 'PAY20260310141400082', 'L2024003', 1, 'John Doe', '2026-05-31', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(247, 'PAY20260310141400083', 'L2024003', 1, 'John Doe', '2026-06-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(248, 'PAY20260310141400084', 'L2024003', 1, 'John Doe', '2026-06-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(249, 'PAY20260310141400085', 'L2024003', 1, 'John Doe', '2026-06-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(250, 'PAY20260310141400086', 'L2024003', 1, 'John Doe', '2026-06-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(251, 'PAY20260310141400087', 'L2024003', 1, 'John Doe', '2026-06-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(252, 'PAY20260310141400088', 'L2024003', 1, 'John Doe', '2026-06-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(253, 'PAY20260310141400089', 'L2024003', 1, 'John Doe', '2026-06-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(254, 'PAY20260310141400090', 'L2024003', 1, 'John Doe', '2026-06-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(255, 'PAY20260310141400091', 'L2024003', 1, 'John Doe', '2026-06-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(256, 'PAY20260310141400092', 'L2024003', 1, 'John Doe', '2026-06-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(257, 'PAY20260310141400093', 'L2024003', 1, 'John Doe', '2026-06-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(258, 'PAY20260310141400094', 'L2024003', 1, 'John Doe', '2026-06-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(259, 'PAY20260310141400095', 'L2024003', 1, 'John Doe', '2026-06-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(260, 'PAY20260310141400096', 'L2024003', 1, 'John Doe', '2026-06-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(261, 'PAY20260310141400097', 'L2024003', 1, 'John Doe', '2026-06-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(262, 'PAY20260310141400098', 'L2024003', 1, 'John Doe', '2026-06-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(263, 'PAY20260310141400099', 'L2024003', 1, 'John Doe', '2026-06-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(264, 'PAY20260310141400100', 'L2024003', 1, 'John Doe', '2026-06-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(265, 'PAY20260310141400101', 'L2024003', 1, 'John Doe', '2026-06-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(266, 'PAY20260310141400102', 'L2024003', 1, 'John Doe', '2026-06-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(267, 'PAY20260310141400103', 'L2024003', 1, 'John Doe', '2026-06-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(268, 'PAY20260310141400104', 'L2024003', 1, 'John Doe', '2026-06-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(269, 'PAY20260310141400105', 'L2024003', 1, 'John Doe', '2026-06-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(270, 'PAY20260310141400106', 'L2024003', 1, 'John Doe', '2026-06-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(271, 'PAY20260310141400107', 'L2024003', 1, 'John Doe', '2026-06-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(272, 'PAY20260310141400108', 'L2024003', 1, 'John Doe', '2026-06-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(273, 'PAY20260310141400109', 'L2024003', 1, 'John Doe', '2026-06-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(274, 'PAY20260310141400110', 'L2024003', 1, 'John Doe', '2026-06-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(275, 'PAY20260310141400111', 'L2024003', 1, 'John Doe', '2026-06-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(276, 'PAY20260310141400112', 'L2024003', 1, 'John Doe', '2026-06-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(277, 'PAY20260310141400113', 'L2024003', 1, 'John Doe', '2026-07-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(278, 'PAY20260310141400114', 'L2024003', 1, 'John Doe', '2026-07-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(279, 'PAY20260310141400115', 'L2024003', 1, 'John Doe', '2026-07-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(280, 'PAY20260310141400116', 'L2024003', 1, 'John Doe', '2026-07-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(281, 'PAY20260310141400117', 'L2024003', 1, 'John Doe', '2026-07-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(282, 'PAY20260310141400118', 'L2024003', 1, 'John Doe', '2026-07-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(283, 'PAY20260310141400119', 'L2024003', 1, 'John Doe', '2026-07-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(284, 'PAY20260310141400120', 'L2024003', 1, 'John Doe', '2026-07-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(285, 'PAY20260310141400121', 'L2024003', 1, 'John Doe', '2026-07-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(286, 'PAY20260310141400122', 'L2024003', 1, 'John Doe', '2026-07-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(287, 'PAY20260310141400123', 'L2024003', 1, 'John Doe', '2026-07-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(288, 'PAY20260310141400124', 'L2024003', 1, 'John Doe', '2026-07-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(289, 'PAY20260310141400125', 'L2024003', 1, 'John Doe', '2026-07-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(290, 'PAY20260310141400126', 'L2024003', 1, 'John Doe', '2026-07-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(291, 'PAY20260310141400127', 'L2024003', 1, 'John Doe', '2026-07-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(292, 'PAY20260310141400128', 'L2024003', 1, 'John Doe', '2026-07-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(293, 'PAY20260310141400129', 'L2024003', 1, 'John Doe', '2026-07-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(294, 'PAY20260310141400130', 'L2024003', 1, 'John Doe', '2026-07-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(295, 'PAY20260310141400131', 'L2024003', 1, 'John Doe', '2026-07-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(296, 'PAY20260310141400132', 'L2024003', 1, 'John Doe', '2026-07-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(297, 'PAY20260310141400133', 'L2024003', 1, 'John Doe', '2026-07-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(298, 'PAY20260310141400134', 'L2024003', 1, 'John Doe', '2026-07-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(299, 'PAY20260310141400135', 'L2024003', 1, 'John Doe', '2026-07-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(300, 'PAY20260310141400136', 'L2024003', 1, 'John Doe', '2026-07-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(301, 'PAY20260310141400137', 'L2024003', 1, 'John Doe', '2026-07-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(302, 'PAY20260310141400138', 'L2024003', 1, 'John Doe', '2026-07-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(303, 'PAY20260310141400139', 'L2024003', 1, 'John Doe', '2026-07-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(304, 'PAY20260310141400140', 'L2024003', 1, 'John Doe', '2026-07-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(305, 'PAY20260310141400141', 'L2024003', 1, 'John Doe', '2026-07-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(306, 'PAY20260310141400142', 'L2024003', 1, 'John Doe', '2026-07-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(307, 'PAY20260310141400143', 'L2024003', 1, 'John Doe', '2026-07-31', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(308, 'PAY20260310141400144', 'L2024003', 1, 'John Doe', '2026-08-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(309, 'PAY20260310141400145', 'L2024003', 1, 'John Doe', '2026-08-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(310, 'PAY20260310141400146', 'L2024003', 1, 'John Doe', '2026-08-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(311, 'PAY20260310141400147', 'L2024003', 1, 'John Doe', '2026-08-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(312, 'PAY20260310141400148', 'L2024003', 1, 'John Doe', '2026-08-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(313, 'PAY20260310141400149', 'L2024003', 1, 'John Doe', '2026-08-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(314, 'PAY20260310141400150', 'L2024003', 1, 'John Doe', '2026-08-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(315, 'PAY20260310141400151', 'L2024003', 1, 'John Doe', '2026-08-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(316, 'PAY20260310141400152', 'L2024003', 1, 'John Doe', '2026-08-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(317, 'PAY20260310141400153', 'L2024003', 1, 'John Doe', '2026-08-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(318, 'PAY20260310141400154', 'L2024003', 1, 'John Doe', '2026-08-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00');
INSERT INTO `payment_schedules` (`id`, `payment_id`, `loan_id`, `user_id`, `borrower_name`, `due_date`, `principal_amount`, `interest_amount`, `total_amount`, `amount_paid`, `status`, `days_overdue`, `payment_date`, `created_at`, `updated_at`) VALUES
(319, 'PAY20260310141400155', 'L2024003', 1, 'John Doe', '2026-08-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(320, 'PAY20260310141400156', 'L2024003', 1, 'John Doe', '2026-08-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(321, 'PAY20260310141400157', 'L2024003', 1, 'John Doe', '2026-08-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(322, 'PAY20260310141400158', 'L2024003', 1, 'John Doe', '2026-08-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(323, 'PAY20260310141400159', 'L2024003', 1, 'John Doe', '2026-08-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(324, 'PAY20260310141400160', 'L2024003', 1, 'John Doe', '2026-08-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(325, 'PAY20260310141400161', 'L2024003', 1, 'John Doe', '2026-08-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(326, 'PAY20260310141400162', 'L2024003', 1, 'John Doe', '2026-08-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(327, 'PAY20260310141400163', 'L2024003', 1, 'John Doe', '2026-08-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(328, 'PAY20260310141400164', 'L2024003', 1, 'John Doe', '2026-08-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(329, 'PAY20260310141400165', 'L2024003', 1, 'John Doe', '2026-08-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(330, 'PAY20260310141400166', 'L2024003', 1, 'John Doe', '2026-08-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(331, 'PAY20260310141400167', 'L2024003', 1, 'John Doe', '2026-08-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(332, 'PAY20260310141400168', 'L2024003', 1, 'John Doe', '2026-08-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(333, 'PAY20260310141400169', 'L2024003', 1, 'John Doe', '2026-08-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(334, 'PAY20260310141400170', 'L2024003', 1, 'John Doe', '2026-08-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(335, 'PAY20260310141400171', 'L2024003', 1, 'John Doe', '2026-08-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(336, 'PAY20260310141400172', 'L2024003', 1, 'John Doe', '2026-08-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(337, 'PAY20260310141400173', 'L2024003', 1, 'John Doe', '2026-08-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(338, 'PAY20260310141400174', 'L2024003', 1, 'John Doe', '2026-08-31', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(339, 'PAY20260310141400175', 'L2024003', 1, 'John Doe', '2026-09-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(340, 'PAY20260310141400176', 'L2024003', 1, 'John Doe', '2026-09-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(341, 'PAY20260310141400177', 'L2024003', 1, 'John Doe', '2026-09-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(342, 'PAY20260310141400178', 'L2024003', 1, 'John Doe', '2026-09-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(343, 'PAY20260310141400179', 'L2024003', 1, 'John Doe', '2026-09-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(344, 'PAY20260310141400180', 'L2024003', 1, 'John Doe', '2026-09-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(345, 'PAY20260310141400181', 'L2024003', 1, 'John Doe', '2026-09-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(346, 'PAY20260310141400182', 'L2024003', 1, 'John Doe', '2026-09-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(347, 'PAY20260310141400183', 'L2024003', 1, 'John Doe', '2026-09-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(348, 'PAY20260310141400184', 'L2024003', 1, 'John Doe', '2026-09-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(349, 'PAY20260310141400185', 'L2024003', 1, 'John Doe', '2026-09-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(350, 'PAY20260310141400186', 'L2024003', 1, 'John Doe', '2026-09-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(351, 'PAY20260310141400187', 'L2024003', 1, 'John Doe', '2026-09-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(352, 'PAY20260310141400188', 'L2024003', 1, 'John Doe', '2026-09-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(353, 'PAY20260310141400189', 'L2024003', 1, 'John Doe', '2026-09-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(354, 'PAY20260310141400190', 'L2024003', 1, 'John Doe', '2026-09-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(355, 'PAY20260310141400191', 'L2024003', 1, 'John Doe', '2026-09-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(356, 'PAY20260310141400192', 'L2024003', 1, 'John Doe', '2026-09-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(357, 'PAY20260310141400193', 'L2024003', 1, 'John Doe', '2026-09-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(358, 'PAY20260310141400194', 'L2024003', 1, 'John Doe', '2026-09-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(359, 'PAY20260310141400195', 'L2024003', 1, 'John Doe', '2026-09-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(360, 'PAY20260310141400196', 'L2024003', 1, 'John Doe', '2026-09-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(361, 'PAY20260310141400197', 'L2024003', 1, 'John Doe', '2026-09-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(362, 'PAY20260310141400198', 'L2024003', 1, 'John Doe', '2026-09-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(363, 'PAY20260310141400199', 'L2024003', 1, 'John Doe', '2026-09-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(364, 'PAY20260310141400200', 'L2024003', 1, 'John Doe', '2026-09-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(365, 'PAY20260310141400201', 'L2024003', 1, 'John Doe', '2026-09-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(366, 'PAY20260310141400202', 'L2024003', 1, 'John Doe', '2026-09-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(367, 'PAY20260310141400203', 'L2024003', 1, 'John Doe', '2026-09-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(368, 'PAY20260310141400204', 'L2024003', 1, 'John Doe', '2026-09-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(369, 'PAY20260310141400205', 'L2024003', 1, 'John Doe', '2026-10-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(370, 'PAY20260310141400206', 'L2024003', 1, 'John Doe', '2026-10-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(371, 'PAY20260310141400207', 'L2024003', 1, 'John Doe', '2026-10-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(372, 'PAY20260310141400208', 'L2024003', 1, 'John Doe', '2026-10-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(373, 'PAY20260310141400209', 'L2024003', 1, 'John Doe', '2026-10-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(374, 'PAY20260310141400210', 'L2024003', 1, 'John Doe', '2026-10-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(375, 'PAY20260310141400211', 'L2024003', 1, 'John Doe', '2026-10-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(376, 'PAY20260310141400212', 'L2024003', 1, 'John Doe', '2026-10-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(377, 'PAY20260310141400213', 'L2024003', 1, 'John Doe', '2026-10-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(378, 'PAY20260310141400214', 'L2024003', 1, 'John Doe', '2026-10-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(379, 'PAY20260310141400215', 'L2024003', 1, 'John Doe', '2026-10-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(380, 'PAY20260310141400216', 'L2024003', 1, 'John Doe', '2026-10-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(381, 'PAY20260310141400217', 'L2024003', 1, 'John Doe', '2026-10-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(382, 'PAY20260310141400218', 'L2024003', 1, 'John Doe', '2026-10-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(383, 'PAY20260310141400219', 'L2024003', 1, 'John Doe', '2026-10-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(384, 'PAY20260310141400220', 'L2024003', 1, 'John Doe', '2026-10-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(385, 'PAY20260310141400221', 'L2024003', 1, 'John Doe', '2026-10-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(386, 'PAY20260310141400222', 'L2024003', 1, 'John Doe', '2026-10-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(387, 'PAY20260310141400223', 'L2024003', 1, 'John Doe', '2026-10-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(388, 'PAY20260310141400224', 'L2024003', 1, 'John Doe', '2026-10-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(389, 'PAY20260310141400225', 'L2024003', 1, 'John Doe', '2026-10-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(390, 'PAY20260310141400226', 'L2024003', 1, 'John Doe', '2026-10-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(391, 'PAY20260310141400227', 'L2024003', 1, 'John Doe', '2026-10-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(392, 'PAY20260310141400228', 'L2024003', 1, 'John Doe', '2026-10-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(393, 'PAY20260310141400229', 'L2024003', 1, 'John Doe', '2026-10-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(394, 'PAY20260310141400230', 'L2024003', 1, 'John Doe', '2026-10-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(395, 'PAY20260310141400231', 'L2024003', 1, 'John Doe', '2026-10-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(396, 'PAY20260310141400232', 'L2024003', 1, 'John Doe', '2026-10-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(397, 'PAY20260310141400233', 'L2024003', 1, 'John Doe', '2026-10-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(398, 'PAY20260310141400234', 'L2024003', 1, 'John Doe', '2026-10-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(399, 'PAY20260310141400235', 'L2024003', 1, 'John Doe', '2026-10-31', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(400, 'PAY20260310141400236', 'L2024003', 1, 'John Doe', '2026-11-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(401, 'PAY20260310141400237', 'L2024003', 1, 'John Doe', '2026-11-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(402, 'PAY20260310141400238', 'L2024003', 1, 'John Doe', '2026-11-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(403, 'PAY20260310141400239', 'L2024003', 1, 'John Doe', '2026-11-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(404, 'PAY20260310141400240', 'L2024003', 1, 'John Doe', '2026-11-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(405, 'PAY20260310141400241', 'L2024003', 1, 'John Doe', '2026-11-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(406, 'PAY20260310141400242', 'L2024003', 1, 'John Doe', '2026-11-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(407, 'PAY20260310141400243', 'L2024003', 1, 'John Doe', '2026-11-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(408, 'PAY20260310141400244', 'L2024003', 1, 'John Doe', '2026-11-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(409, 'PAY20260310141400245', 'L2024003', 1, 'John Doe', '2026-11-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(410, 'PAY20260310141400246', 'L2024003', 1, 'John Doe', '2026-11-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(411, 'PAY20260310141400247', 'L2024003', 1, 'John Doe', '2026-11-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(412, 'PAY20260310141400248', 'L2024003', 1, 'John Doe', '2026-11-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(413, 'PAY20260310141400249', 'L2024003', 1, 'John Doe', '2026-11-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(414, 'PAY20260310141400250', 'L2024003', 1, 'John Doe', '2026-11-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(415, 'PAY20260310141400251', 'L2024003', 1, 'John Doe', '2026-11-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(416, 'PAY20260310141400252', 'L2024003', 1, 'John Doe', '2026-11-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(417, 'PAY20260310141400253', 'L2024003', 1, 'John Doe', '2026-11-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(418, 'PAY20260310141400254', 'L2024003', 1, 'John Doe', '2026-11-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(419, 'PAY20260310141400255', 'L2024003', 1, 'John Doe', '2026-11-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(420, 'PAY20260310141400256', 'L2024003', 1, 'John Doe', '2026-11-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(421, 'PAY20260310141400257', 'L2024003', 1, 'John Doe', '2026-11-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(422, 'PAY20260310141400258', 'L2024003', 1, 'John Doe', '2026-11-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(423, 'PAY20260310141400259', 'L2024003', 1, 'John Doe', '2026-11-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(424, 'PAY20260310141400260', 'L2024003', 1, 'John Doe', '2026-11-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(425, 'PAY20260310141400261', 'L2024003', 1, 'John Doe', '2026-11-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(426, 'PAY20260310141400262', 'L2024003', 1, 'John Doe', '2026-11-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(427, 'PAY20260310141400263', 'L2024003', 1, 'John Doe', '2026-11-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(428, 'PAY20260310141400264', 'L2024003', 1, 'John Doe', '2026-11-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(429, 'PAY20260310141400265', 'L2024003', 1, 'John Doe', '2026-11-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(430, 'PAY20260310141400266', 'L2024003', 1, 'John Doe', '2026-12-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(431, 'PAY20260310141400267', 'L2024003', 1, 'John Doe', '2026-12-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(432, 'PAY20260310141400268', 'L2024003', 1, 'John Doe', '2026-12-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(433, 'PAY20260310141400269', 'L2024003', 1, 'John Doe', '2026-12-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(434, 'PAY20260310141400270', 'L2024003', 1, 'John Doe', '2026-12-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(435, 'PAY20260310141400271', 'L2024003', 1, 'John Doe', '2026-12-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(436, 'PAY20260310141400272', 'L2024003', 1, 'John Doe', '2026-12-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(437, 'PAY20260310141400273', 'L2024003', 1, 'John Doe', '2026-12-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(438, 'PAY20260310141400274', 'L2024003', 1, 'John Doe', '2026-12-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(439, 'PAY20260310141400275', 'L2024003', 1, 'John Doe', '2026-12-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(440, 'PAY20260310141400276', 'L2024003', 1, 'John Doe', '2026-12-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(441, 'PAY20260310141400277', 'L2024003', 1, 'John Doe', '2026-12-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(442, 'PAY20260310141400278', 'L2024003', 1, 'John Doe', '2026-12-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(443, 'PAY20260310141400279', 'L2024003', 1, 'John Doe', '2026-12-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(444, 'PAY20260310141400280', 'L2024003', 1, 'John Doe', '2026-12-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(445, 'PAY20260310141400281', 'L2024003', 1, 'John Doe', '2026-12-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(446, 'PAY20260310141400282', 'L2024003', 1, 'John Doe', '2026-12-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(447, 'PAY20260310141400283', 'L2024003', 1, 'John Doe', '2026-12-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(448, 'PAY20260310141400284', 'L2024003', 1, 'John Doe', '2026-12-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(449, 'PAY20260310141400285', 'L2024003', 1, 'John Doe', '2026-12-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(450, 'PAY20260310141400286', 'L2024003', 1, 'John Doe', '2026-12-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(451, 'PAY20260310141400287', 'L2024003', 1, 'John Doe', '2026-12-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(452, 'PAY20260310141400288', 'L2024003', 1, 'John Doe', '2026-12-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(453, 'PAY20260310141400289', 'L2024003', 1, 'John Doe', '2026-12-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(454, 'PAY20260310141400290', 'L2024003', 1, 'John Doe', '2026-12-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(455, 'PAY20260310141400291', 'L2024003', 1, 'John Doe', '2026-12-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(456, 'PAY20260310141400292', 'L2024003', 1, 'John Doe', '2026-12-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(457, 'PAY20260310141400293', 'L2024003', 1, 'John Doe', '2026-12-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(458, 'PAY20260310141400294', 'L2024003', 1, 'John Doe', '2026-12-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(459, 'PAY20260310141400295', 'L2024003', 1, 'John Doe', '2026-12-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(460, 'PAY20260310141400296', 'L2024003', 1, 'John Doe', '2026-12-31', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(461, 'PAY20260310141400297', 'L2024003', 1, 'John Doe', '2027-01-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(462, 'PAY20260310141400298', 'L2024003', 1, 'John Doe', '2027-01-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(463, 'PAY20260310141400299', 'L2024003', 1, 'John Doe', '2027-01-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(464, 'PAY20260310141400300', 'L2024003', 1, 'John Doe', '2027-01-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(465, 'PAY20260310141400301', 'L2024003', 1, 'John Doe', '2027-01-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(466, 'PAY20260310141400302', 'L2024003', 1, 'John Doe', '2027-01-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(467, 'PAY20260310141400303', 'L2024003', 1, 'John Doe', '2027-01-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(468, 'PAY20260310141400304', 'L2024003', 1, 'John Doe', '2027-01-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(469, 'PAY20260310141400305', 'L2024003', 1, 'John Doe', '2027-01-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(470, 'PAY20260310141400306', 'L2024003', 1, 'John Doe', '2027-01-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(471, 'PAY20260310141400307', 'L2024003', 1, 'John Doe', '2027-01-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(472, 'PAY20260310141400308', 'L2024003', 1, 'John Doe', '2027-01-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(473, 'PAY20260310141400309', 'L2024003', 1, 'John Doe', '2027-01-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(474, 'PAY20260310141400310', 'L2024003', 1, 'John Doe', '2027-01-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(475, 'PAY20260310141400311', 'L2024003', 1, 'John Doe', '2027-01-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(476, 'PAY20260310141400312', 'L2024003', 1, 'John Doe', '2027-01-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(477, 'PAY20260310141400313', 'L2024003', 1, 'John Doe', '2027-01-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(478, 'PAY20260310141400314', 'L2024003', 1, 'John Doe', '2027-01-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(479, 'PAY20260310141400315', 'L2024003', 1, 'John Doe', '2027-01-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(480, 'PAY20260310141400316', 'L2024003', 1, 'John Doe', '2027-01-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(481, 'PAY20260310141400317', 'L2024003', 1, 'John Doe', '2027-01-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(482, 'PAY20260310141400318', 'L2024003', 1, 'John Doe', '2027-01-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(483, 'PAY20260310141400319', 'L2024003', 1, 'John Doe', '2027-01-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(484, 'PAY20260310141400320', 'L2024003', 1, 'John Doe', '2027-01-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(485, 'PAY20260310141400321', 'L2024003', 1, 'John Doe', '2027-01-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(486, 'PAY20260310141400322', 'L2024003', 1, 'John Doe', '2027-01-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(487, 'PAY20260310141400323', 'L2024003', 1, 'John Doe', '2027-01-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(488, 'PAY20260310141400324', 'L2024003', 1, 'John Doe', '2027-01-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(489, 'PAY20260310141400325', 'L2024003', 1, 'John Doe', '2027-01-29', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(490, 'PAY20260310141400326', 'L2024003', 1, 'John Doe', '2027-01-30', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(491, 'PAY20260310141400327', 'L2024003', 1, 'John Doe', '2027-01-31', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(492, 'PAY20260310141400328', 'L2024003', 1, 'John Doe', '2027-02-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(493, 'PAY20260310141400329', 'L2024003', 1, 'John Doe', '2027-02-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(494, 'PAY20260310141400330', 'L2024003', 1, 'John Doe', '2027-02-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(495, 'PAY20260310141400331', 'L2024003', 1, 'John Doe', '2027-02-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(496, 'PAY20260310141400332', 'L2024003', 1, 'John Doe', '2027-02-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(497, 'PAY20260310141400333', 'L2024003', 1, 'John Doe', '2027-02-06', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(498, 'PAY20260310141400334', 'L2024003', 1, 'John Doe', '2027-02-07', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(499, 'PAY20260310141400335', 'L2024003', 1, 'John Doe', '2027-02-08', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(500, 'PAY20260310141400336', 'L2024003', 1, 'John Doe', '2027-02-09', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(501, 'PAY20260310141400337', 'L2024003', 1, 'John Doe', '2027-02-10', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(502, 'PAY20260310141400338', 'L2024003', 1, 'John Doe', '2027-02-11', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(503, 'PAY20260310141400339', 'L2024003', 1, 'John Doe', '2027-02-12', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(504, 'PAY20260310141400340', 'L2024003', 1, 'John Doe', '2027-02-13', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(505, 'PAY20260310141400341', 'L2024003', 1, 'John Doe', '2027-02-14', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(506, 'PAY20260310141400342', 'L2024003', 1, 'John Doe', '2027-02-15', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(507, 'PAY20260310141400343', 'L2024003', 1, 'John Doe', '2027-02-16', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(508, 'PAY20260310141400344', 'L2024003', 1, 'John Doe', '2027-02-17', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(509, 'PAY20260310141400345', 'L2024003', 1, 'John Doe', '2027-02-18', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(510, 'PAY20260310141400346', 'L2024003', 1, 'John Doe', '2027-02-19', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(511, 'PAY20260310141400347', 'L2024003', 1, 'John Doe', '2027-02-20', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(512, 'PAY20260310141400348', 'L2024003', 1, 'John Doe', '2027-02-21', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(513, 'PAY20260310141400349', 'L2024003', 1, 'John Doe', '2027-02-22', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(514, 'PAY20260310141400350', 'L2024003', 1, 'John Doe', '2027-02-23', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(515, 'PAY20260310141400351', 'L2024003', 1, 'John Doe', '2027-02-24', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(516, 'PAY20260310141400352', 'L2024003', 1, 'John Doe', '2027-02-25', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(517, 'PAY20260310141400353', 'L2024003', 1, 'John Doe', '2027-02-26', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(518, 'PAY20260310141400354', 'L2024003', 1, 'John Doe', '2027-02-27', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(519, 'PAY20260310141400355', 'L2024003', 1, 'John Doe', '2027-02-28', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(520, 'PAY20260310141400356', 'L2024003', 1, 'John Doe', '2027-03-01', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(521, 'PAY20260310141400357', 'L2024003', 1, 'John Doe', '2027-03-02', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(522, 'PAY20260310141400358', 'L2024003', 1, 'John Doe', '2027-03-03', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(523, 'PAY20260310141400359', 'L2024003', 1, 'John Doe', '2027-03-04', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00'),
(524, 'PAY20260310141400360', 'L2024003', 1, 'John Doe', '2027-03-05', 59.32, 0.00, 59.32, 0.00, 'pending', 0, NULL, '2026-03-10 13:14:00', '2026-03-10 13:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'interest_rate_daily', '6', 'percentage', 'Interest rate for daily payments', '2026-03-10 13:29:55', '2026-03-10 13:32:08'),
(2, 'interest_rate_weekly', '4.5', 'percentage', 'Interest rate for weekly payments', '2026-03-10 13:29:55', '2026-03-10 13:32:08'),
(3, 'interest_rate_monthly', '3.5', 'percentage', 'Interest rate for monthly payments', '2026-03-10 13:29:55', '2026-03-10 13:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','vendor') DEFAULT 'vendor',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@blueledger.com', '$2y$10$drj2LLIKTyPIbhoQXYsVlOjSjNhC36YWhrKNqnJ1SBR2KWeopbelO', 'admin', NULL, NULL, '2026-03-08 07:05:52', '2026-03-08 07:05:52'),
(2, 'Coastal Enterprises', 'coastal@marketvendor.com', '$2y$10$drj2LLIKTyPIbhoQXYsVlOjSjNhC36YWhrKNqnJ1SBR2KWeopbelO', 'vendor', '09123456789', NULL, '2026-03-08 07:05:52', '2026-03-13 21:59:20'),
(3, 'Summit Retail Corp', 'summit@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456788', NULL, '2026-03-08 07:05:52', '2026-03-08 07:05:52'),
(4, 'Global Trading Co.', 'global@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456787', NULL, '2026-03-08 07:05:52', '2026-03-08 07:05:52'),
(5, 'Northline Foods', 'northline@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456786', NULL, '2026-03-08 07:05:52', '2026-03-08 07:05:52'),
(6, 'Blue Harbor Trading', 'blueharbor@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456785', NULL, '2026-03-08 07:05:52', '2026-03-08 07:05:52'),
(7, 'Metro Farm Supply', 'metrofarm@marketvendor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', '09123456784', NULL, '2026-03-08 07:05:52', '2026-03-08 07:05:52'),
(8, 'Rome Joseph Lorente', 'lorenteromejoseph@gmail.com', '$2y$10$JS3uCzVveL2rymioJ03peeeCQmnG4lFrCT3ZyJe7rSimJDBSEkf/G', 'vendor', '09124181358', 'test city', '2026-03-08 07:07:26', '2026-03-12 11:40:43'),
(9, 'cryptical rome', 'crypticalrome@gmail.com', '$2y$10$sxtfTjdDgfwTGsg/80Rlcu2s8fuD2Dif8gv69vBjV/wu8AmsbWQDu', 'vendor', NULL, NULL, '2026-03-10 13:53:22', '2026-03-10 13:53:22'),
(10, 'Abigail Nery', 'abigil@gmail.com', '$2y$10$szHbSNtrueoqo3048KaYJeBZJyxKf1wscpAI.IkBBxoopAtCXKqrm', 'vendor', NULL, NULL, '2026-03-12 11:23:02', '2026-03-12 11:23:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `late_fees`
--
ALTER TABLE `late_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_id` (`loan_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`original_due_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `late_fee_notifications`
--
ALTER TABLE `late_fee_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_id` (`loan_id`),
  ADD KEY `idx_fee_id` (`fee_id`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `late_fee_settings`
--
ALTER TABLE `late_fee_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `late_fee_tiers`
--
ALTER TABLE `late_fee_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_days_range` (`days_from`,`days_to`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_id` (`loan_id`),
  ADD KEY `idx_loan_id` (`loan_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_loan_start_date` (`loan_start_date`),
  ADD KEY `idx_next_payment_date` (`next_payment_date`),
  ADD KEY `idx_remaining_balance` (`remaining_balance`);

--
-- Indexes for table `loan_documents`
--
ALTER TABLE `loan_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_id` (`loan_id`),
  ADD KEY `idx_document_type` (`document_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendor_status` (`vendor_id`,`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_vendor_type` (`vendor_id`,`notification_type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`reset_token`),
  ADD KEY `idx_expiry` (`token_expiry`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_loan_id` (`loan_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `payment_schedules`
--
ALTER TABLE `payment_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_id` (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_loan_id` (`loan_id`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=318;

--
-- AUTO_INCREMENT for table `late_fees`
--
ALTER TABLE `late_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `late_fee_notifications`
--
ALTER TABLE `late_fee_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `late_fee_settings`
--
ALTER TABLE `late_fee_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `late_fee_tiers`
--
ALTER TABLE `late_fee_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `loan_documents`
--
ALTER TABLE `loan_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `payment_schedules`
--
ALTER TABLE `payment_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=531;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `late_fees`
--
ALTER TABLE `late_fees`
  ADD CONSTRAINT `fk_late_fees_loan_id` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`);

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payment_schedules`
--
ALTER TABLE `payment_schedules`
  ADD CONSTRAINT `payment_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
