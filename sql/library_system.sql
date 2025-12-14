-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 05:49 PM
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
-- Database: `library_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(202, 2, 'borrow_request', 'Submitted borrow request for book ID 9, copies: 1', '::1', '2025-12-13 13:55:11'),
(203, 2, 'cancel_request', 'Cancelled borrow request ID 4 for book ID 9', '::1', '2025-12-13 13:55:22'),
(204, 2, 'borrow_request', 'Submitted borrow request for book ID 1, copies: 1', '::1', '2025-12-13 13:56:02'),
(205, 1, 'approve_request', 'Approved borrow request ID 5 for user 2', '::1', '2025-12-13 13:56:19'),
(206, 2, 'borrow_request', 'Submitted borrow request for book ID 9, copies: 1', '::1', '2025-12-13 14:59:26'),
(207, 2, 'cancel_request', 'Cancelled borrow request ID 6 for book ID 9', '::1', '2025-12-13 14:59:31'),
(208, 2, 'borrow_request', 'Submitted borrow request for book ID 8, copies: 1', '::1', '2025-12-13 17:18:25'),
(209, 2, 'cancel_request', 'Cancelled borrow request ID 7 for book ID 8', '::1', '2025-12-13 17:18:32'),
(210, 2, 'borrow_request', 'Submitted borrow request for book ID 10, copies: 1', '::1', '2025-12-14 12:38:56'),
(211, 2, 'return_book', 'Returned book ID 1, borrow ID 5', '::1', '2025-12-14 12:39:03'),
(212, 2, 'cancel_request', 'Cancelled borrow request ID 8 for book ID 10', '::1', '2025-12-14 12:39:26'),
(213, 4, 'borrow_request', 'Submitted borrow request for book ID 5, copies: 1', '::1', '2025-12-14 12:49:05'),
(214, 4, 'cancel_request', 'Cancelled borrow request ID 9 for book ID 5', '::1', '2025-12-14 12:49:11'),
(215, 2, 'borrow_request', 'Submitted borrow request for book ID 1, copies: 1', '::1', '2025-12-14 14:38:55'),
(216, 2, 'borrow_request', 'Submitted borrow request for book ID 3, copies: 1', '::1', '2025-12-14 14:44:48'),
(217, 2, 'cancel_request', 'Cancelled borrow request ID 11 for book ID 3', '::1', '2025-12-14 14:44:55');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `total_copies` int(11) NOT NULL DEFAULT 1,
  `available_copies` int(11) NOT NULL DEFAULT 1,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `genre`, `publication_date`, `total_copies`, `available_copies`, `deleted`) VALUES
(1, 'Clean Code', 'Robert C. Martin', 'Programming', '2008-08-01', 2, 2, 0),
(2, 'PHP & MySQL Web Development', 'Luke Welling', 'Programming', '2016-05-15', 1, 1, 0),
(3, 'To Kill a Mockingbird', 'Harper Lee', 'Fiction', '1960-07-11', 3, 3, 0),
(4, 'Dune', 'Frank Herbert', 'Science Fiction', '1965-08-01', 2, 2, 0),
(5, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Classic', '1925-04-10', 4, 4, 0),
(6, 'Sherlock Holmes: A Study in Scarlet', 'Arthur Conan Doyle', 'Mystery', '1887-11-01', 2, 2, 0),
(7, 'Pride and Prejudice', 'Jane Austen', 'Romance', '1813-01-28', 3, 3, 0),
(8, 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'History', '2011-09-04', 1, 1, 0),
(9, 'The Autobiography of Malcolm X', 'Malcolm X', 'Biography', '1965-10-29', 2, 2, 0),
(10, 'The Hobbit', 'J.R.R. Tolkien', 'Fantasy', '1937-09-21', 3, 3, 0),
(11, 'food', 'kriper', 'horror', '2014-06-11', 5, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `book_reviews`
--

CREATE TABLE `book_reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_reviews`
--

INSERT INTO `book_reviews` (`id`, `user_id`, `book_id`, `rating`, `review`, `created_at`) VALUES
(13, 2, 1, 5, 'cool', '2025-12-14 12:39:08');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_history`
--

CREATE TABLE `borrow_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `copies_borrowed` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','approved','borrowed','returned','rejected','overdue','cancelled') NOT NULL DEFAULT 'pending',
  `request_date` date DEFAULT NULL,
  `borrow_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_history`
--

INSERT INTO `borrow_history` (`id`, `user_id`, `book_id`, `copies_borrowed`, `status`, `request_date`, `borrow_date`, `due_date`, `return_date`) VALUES
(1, 3, 1, 1, 'overdue', NULL, '2025-12-03', '2025-12-08', NULL),
(2, 3, 3, 1, 'overdue', NULL, '2025-12-05', '2025-12-10', NULL),
(3, 3, 4, 1, 'overdue', NULL, '2025-12-01', '2025-12-06', NULL),
(4, 2, 9, 1, 'cancelled', '2025-12-13', NULL, NULL, NULL),
(5, 2, 1, 1, 'returned', '2025-12-13', '2025-12-13', '2025-12-27', '2025-12-14'),
(6, 2, 9, 1, 'cancelled', '2025-12-13', NULL, NULL, NULL),
(7, 2, 8, 1, 'cancelled', '2025-12-13', NULL, NULL, NULL),
(8, 2, 10, 1, 'cancelled', '2025-12-14', NULL, NULL, NULL),
(9, 4, 5, 1, 'cancelled', '2025-12-14', NULL, NULL, NULL),
(10, 2, 1, 1, 'pending', '2025-12-14', NULL, NULL, NULL),
(11, 2, 3, 1, 'cancelled', '2025-12-14', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrow_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`id`, `user_id`, `borrow_id`, `amount`, `status`, `created_at`, `paid_at`) VALUES
(1, 3, 1, 30.00, 'pending', '2025-12-13 04:13:50', NULL),
(2, 3, 2, 20.00, 'pending', '2025-12-13 04:13:50', NULL),
(3, 3, 3, 40.00, 'pending', '2025-12-13 04:13:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('approval','rejection','due_reminder','overdue','fine','reservation_available','cancellation') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(736, 3, 'overdue', 'Your borrowed book \'Clean Code\' is overdue. Due date was 2025-12-08. Please return it immediately to avoid additional fines.', 0, '2025-12-13 13:54:34'),
(737, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 13:54:34'),
(738, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 13:55:11'),
(739, 2, 'cancellation', 'Your borrow request for book \'The Autobiography of Malcolm X\' has been cancelled as requested. You may submit a new request if needed.', 1, '2025-12-13 13:55:22'),
(740, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 13:55:22'),
(741, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 13:56:02'),
(742, 2, 'approval', 'Your borrow request for book ID 1 has been approved. Due date: 2025-12-27', 1, '2025-12-13 13:56:19'),
(743, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 13:56:26'),
(744, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 14:21:54'),
(745, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 14:27:37'),
(746, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 14:54:38'),
(747, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 14:59:26'),
(748, 2, 'cancellation', 'Your borrow request for book \'The Autobiography of Malcolm X\' has been cancelled as requested. You may submit a new request if needed.', 1, '2025-12-13 14:59:31'),
(749, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 14:59:32'),
(750, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 15:11:50'),
(751, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 15:38:01'),
(752, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 15:39:46'),
(753, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:28:21'),
(754, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:28:37'),
(755, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:33:30'),
(756, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:35:23'),
(757, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:35:29'),
(758, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:35:51'),
(759, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:35:52'),
(760, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:36:05'),
(761, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:38:01'),
(762, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:38:04'),
(763, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:38:39'),
(764, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:38:40'),
(765, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:39:25'),
(766, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:39:29'),
(767, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:39:29'),
(768, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:39:31'),
(769, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 16:51:40'),
(770, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:07:08'),
(771, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:13:04'),
(772, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:13:17'),
(773, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:13:51'),
(774, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:14:09'),
(775, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:15:49'),
(776, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:16:46'),
(777, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:18:19'),
(778, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:18:25'),
(779, 2, 'cancellation', 'Your borrow request for book \'Sapiens: A Brief History of Humankind\' has been cancelled as requested. You may submit a new request if needed.', 1, '2025-12-13 17:18:32'),
(780, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:18:32'),
(781, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:19:53'),
(782, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:42:34'),
(783, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:50:29'),
(784, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:54:16'),
(785, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:58:07'),
(786, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:59:44'),
(787, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 17:59:55'),
(788, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:01:27'),
(789, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:02:31'),
(790, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:16:06'),
(791, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:19'),
(792, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:20'),
(793, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:21'),
(794, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:28'),
(795, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:28'),
(796, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:29'),
(797, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:29'),
(798, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:29'),
(799, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:30'),
(800, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:18:31'),
(801, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:20:02'),
(802, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:20:03'),
(803, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:21:48'),
(804, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:21:49'),
(805, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:23:11'),
(806, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:25:57'),
(807, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:26:21'),
(808, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:26:40'),
(809, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-13 18:30:56'),
(810, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:27:33'),
(811, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:28:33'),
(812, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:31:00'),
(813, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:32:15'),
(814, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:44:46'),
(815, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:50:53'),
(816, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 09:54:56'),
(817, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:00:38'),
(818, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:04:21'),
(819, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:07:43'),
(820, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:19:43'),
(821, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:46:04'),
(822, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:47:50'),
(823, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:50:16'),
(824, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:53:59'),
(825, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:54:00'),
(826, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:54:00'),
(827, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:57:37'),
(828, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 10:58:08'),
(829, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 11:16:33'),
(830, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 11:18:28'),
(831, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 11:20:14'),
(832, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:29:32'),
(833, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:33:05'),
(834, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:33:54'),
(835, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:36:27'),
(836, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:37:07'),
(837, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:38:53'),
(838, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:38:56'),
(839, 2, '', 'You have successfully returned the book \'Clean Code\'. Return status: returned. Thank you for using the library!', 1, '2025-12-14 12:39:03'),
(840, 1, '', 'New review submitted for \'Clean Code\' by   . Rating: 5/5. Review: cool', 0, '2025-12-14 12:39:08'),
(841, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:39:13'),
(842, 2, 'cancellation', 'Your borrow request for book \'The Hobbit\' has been cancelled as requested. You may submit a new request if needed.', 1, '2025-12-14 12:39:26'),
(843, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:39:26'),
(844, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:43:31'),
(845, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:43:31'),
(846, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:44:24'),
(847, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:44:37'),
(848, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:46:21'),
(849, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:49:01'),
(850, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:49:05'),
(851, 4, 'cancellation', 'Your borrow request for book \'The Great Gatsby\' has been cancelled as requested. You may submit a new request if needed.', 0, '2025-12-14 12:49:11'),
(852, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 12:49:11'),
(853, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:01:52'),
(854, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:04:34'),
(855, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:04:40'),
(856, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:05:00'),
(857, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:05:25'),
(858, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:05:26'),
(859, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:05:26'),
(860, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:06:28'),
(861, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:07:05'),
(862, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:07:33'),
(863, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:12:32'),
(864, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:12:34'),
(865, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:15:25'),
(866, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:21:13'),
(867, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:21:14'),
(868, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 13:27:52'),
(869, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:01:35'),
(870, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:06:52'),
(871, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:08:17'),
(872, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:11:35'),
(873, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:24:58'),
(874, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:26:48'),
(875, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:26:49'),
(876, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:31:17'),
(877, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:31:18'),
(878, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:32:10'),
(879, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:38:34'),
(880, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:38:55'),
(881, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:42:46'),
(882, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:44:41'),
(883, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:44:48'),
(884, 2, 'cancellation', 'Your borrow request for book \'To Kill a Mockingbird\' has been cancelled as requested. You may submit a new request if needed.', 1, '2025-12-14 14:44:55'),
(885, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:44:55'),
(886, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 14:46:47'),
(887, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:15:12'),
(888, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:24:08'),
(889, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:25:07'),
(890, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:25:19'),
(891, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:28:19'),
(892, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:28:35'),
(893, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:29:03'),
(894, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:30:09'),
(895, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:31:59'),
(896, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:39:09'),
(897, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:39:12'),
(898, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:49:06'),
(899, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:57:03'),
(900, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 15:57:27'),
(901, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:01:34'),
(902, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:06:29'),
(903, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:07:15'),
(904, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:10:13'),
(905, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:12:56'),
(906, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:13:33'),
(907, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:14:42'),
(908, 3, 'overdue', 'Your borrowed book \'Dune\' is overdue. Due date was 2025-12-06. Please return it immediately to avoid additional fines.', 0, '2025-12-14 16:32:43');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 4, '0450832e57fd0cd0cbb0b16ad7f6751b9f4c6abdc62a4786f1662c99cd0e71cc', '2025-12-14 08:38:14', '2025-12-14 06:38:14'),
(2, 4, '34e6a557161853071db31b7dcadf06d2b88993cb0ec9667f2cffa6d0c5d9abc0', '2025-12-14 08:40:40', '2025-12-14 06:40:40'),
(3, 4, '6609f290b72567bc351695ae87887a602e9d2f151f8be13b8d64e203f3dd71b5', '2025-12-14 08:52:33', '2025-12-14 06:52:33'),
(4, 4, '302eded0737531ebdad886db0fa1193bc97ff949d34ac4093963b900027faf15', '2025-12-14 08:56:28', '2025-12-14 06:56:28'),
(5, 4, '14ab7f28bb3c9b7e36e3fb5bf590658e438527d9ab94f70c0421115a476f0830', '2025-12-14 08:56:33', '2025-12-14 06:56:33'),
(6, 4, '26497639866ad0076502daa5982447d68489d2cdadafb2fea8782e243da8480a', '2025-12-14 09:04:12', '2025-12-14 07:04:12'),
(7, 5, '41d09e0f5064cc16de035a5845abcab8f9e4f0634d9e50efeec345b59765c44b', '2025-12-14 09:16:54', '2025-12-14 07:16:54'),
(8, 5, 'a0f39db8772fb4cdb0f25d21d0e15eb5d98cb284a65509005918bd8a19bd1458', '2025-12-14 09:46:33', '2025-12-14 07:46:33'),
(9, 5, '5fa0b93c927daf968fc6dc49b9c02974d6b738dac12fbcc45e0ac7a9acdc8648', '2025-12-14 09:57:00', '2025-12-14 07:57:00'),
(10, 5, '5e7704d427af3f303c8511214e3d2f7ac663a3a2c1b85f541b5c2a576e4df9f5', '2025-12-14 09:58:47', '2025-12-14 07:58:47'),
(11, 5, '15431d86b85453960c2dc01d91845c6bed0b39094128b48806724760b3b46b99', '2025-12-14 10:41:02', '2025-12-14 08:41:02'),
(12, 5, '6221db4fe1e0de355837762cf5df8d51ae531392ee232079773913a0c894597a', '2025-12-14 10:41:35', '2025-12-14 08:41:35'),
(13, 5, '0d32c53c436a138acde1cb97b2f7f5ffe35125a7b161a572e57fd509ab145d8f', '2025-12-14 10:52:31', '2025-12-14 08:52:31'),
(14, 5, '922bfedfeb045ee3e062b7f223d4287489ca33763e2e8237f0add45056e26238', '2025-12-14 11:08:10', '2025-12-14 09:08:10'),
(15, 5, '78a9c41ec1df49a21e372795470af25e07e48db031e4b40749b2fbf9db9c0ef9', '2025-12-14 11:08:21', '2025-12-14 09:08:21'),
(16, 5, '82a1545f4d32b299e04eb07a19ec743e2c45c63541a3568149534eb276dc2381', '2025-12-14 11:10:11', '2025-12-14 09:10:11'),
(17, 6, '4402aa03d8702d41a8a8741968e68fb636f61c074d65dc35bb9d80895c865be7', '2025-12-14 11:18:20', '2025-12-14 09:18:20'),
(18, 6, 'fbc26127b30f703ff585457102f2d09418572d432e61b6295b408dbf09f294ad', '2025-12-14 11:18:53', '2025-12-14 09:18:53');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notified` tinyint(1) DEFAULT 0,
  `status` enum('active','fulfilled','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','student') NOT NULL,
  `penalty_status` enum('none','blocked') DEFAULT 'none',
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `middle_name`, `last_name`, `role`, `penalty_status`, `email`) VALUES
(1, 'angelobadi124@gmail.com', '$2y$10$6lzuHP5WgWDCK52KXyBEfuK6tQSv0tyEhndYcEdxpEjTpHGbwxvoq', '', NULL, '', 'admin', 'none', 'angelobadi124@gmail.com'),
(2, 'Student@wmsu.com', '$2y$10$a.gfw53oMxc2H1sKhkVGeuC19HbSf0zeU8F324AxQP0plTcB54iAS', '', NULL, '', 'student', 'none', 'student@wmsu.edu.ph'),
(3, 'Student1@wmsu.com', '$2y$10$eURnmTnDVC4YZq.5hLS3KOaajMejpHNiyaokXWMF8Ei5mZ7j0aA0C', '', NULL, '', 'student', 'blocked', 'student1@wmsu.edu.ph'),
(4, 'angel', '$2y$10$kylI7FnNNcfDdXfJlYnBPOvM2Ot1oUx4AL/j/Un4LnfiN0nA0emLe', 'angelo', '', 'alerioa', 'student', 'none', 'angelo0227@gmail.com'),
(5, 'fasfa', '$2y$10$KKHihd3X77UdriB1fj/kROpjEovDIn2RTBGRU78XKSQ2Yk4XO2u0.', 'asd', '', 'asd', 'student', 'none', 'angeloa0227@gmail.com'),
(6, 'abdul', '$2y$10$AYge7VIf3lb08zrpcGHOpOjmEdYIdf8R0W3h6A7fRoVITkzZ2uLdu', 'rawr', '', 'qwerty', 'student', 'none', 'hz202301203@wmsu.edu.ph');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_reviews`
--
ALTER TABLE `book_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `borrow_history`
--
ALTER TABLE `borrow_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `borrow_id` (`borrow_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `book_reviews`
--
ALTER TABLE `book_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `borrow_history`
--
ALTER TABLE `borrow_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=909;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `book_reviews`
--
ALTER TABLE `book_reviews`
  ADD CONSTRAINT `book_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_reviews_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_history`
--
ALTER TABLE `borrow_history`
  ADD CONSTRAINT `borrow_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_history_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
