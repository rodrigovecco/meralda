-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 03, 2024 at 01:41 AM
-- Server version: 10.4.13-MariaDB-log
-- PHP Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mwphplib`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `complete_name` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `secpass` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `last_login_date` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) NOT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `rol_admin` tinyint(1) NOT NULL DEFAULT 0,
  `reset_pass_code` varchar(255) NOT NULL,
  `reset_pass_enabled` tinyint(1) NOT NULL,
  `reset_pass_expires` datetime NOT NULL,
  `must_change_pass` tinyint(1) NOT NULL,
  `image` varchar(255) NOT NULL,
  `phonenumber` varchar(100) NOT NULL,
  `rol_consult` tinyint(1) NOT NULL DEFAULT 0,
  `rol_user` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name_UNIQUE` (`name`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `complete_name`, `pass`, `secpass`, `active`, `last_login_date`, `last_login_ip`, `is_main`, `rol_admin`, `reset_pass_code`, `reset_pass_enabled`, `reset_pass_expires`, `must_change_pass`, `image`, `phonenumber`, `rol_consult`, `rol_user`) VALUES
(1, 'admin@novoingenios.com', 'Admin', '$2y$10$8QTvkfaJdugQUHz3f3lxK.a48cuegnDhko46MNE/07QIzYxnn/5P6', 1, 1, '2024-03-02 23:50:27', '::1', 1, 0, '', 0, '0000-00-00 00:00:00', 0, '', '', 0, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
