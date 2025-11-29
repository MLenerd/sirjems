-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 29, 2025 at 08:03 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itc`
--

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

DROP TABLE IF EXISTS `stocks`;
CREATE TABLE IF NOT EXISTS `stocks` (
  `item` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `price` float(255,2) NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `stock` int DEFAULT NULL,
  `notified` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`item`, `bar`, `category`, `location`, `price`, `id`, `stock`, `notified`) VALUES
('MOUSE', 'SKU-401', 'ELECTRONICS', 'RACK 1A', 120.00, 1, 193, 0),
('keyboard', 'SKU-402', 'ELECTRONICS', 'RACK 1A', 400.00, 2, 120, 0),
('cpu', 'SKU-403', 'ELECTRONICS', 'RACK 1A', 6000.00, 3, 356, 0),
('headset', 'SKU-406', 'ELECTRONICS', 'RACK 1A', 800.00, 4, 359, 0),
('DOOR', 'SKU-303', 'APPLIANCES', 'RACK 2A', 4300.00, 5, 700, 0),
('door knob', 'SKU-304', 'APPLIANCES', 'RACK 2A', 500.00, 6, 5, 1),
('WINDOW', 'SKU-305', 'APPLIANCES', 'RACK 2A', 2300.00, 7, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `failed_attempts` int DEFAULT '0',
  `last_failed_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `failed_attempts`, `last_failed_time`) VALUES
(1, 'admin', '$2y$10$aE0gHDDDm7/5kU1gTclG9uNJGe.FN33IalwaBM7PH6QlTA1GAUrw6', 'aaaaa@gmail.com', '2025-11-29 03:23:12', 4, '2025-11-29 03:29:42');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
