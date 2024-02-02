-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 02, 2024 at 08:25 PM
-- Server version: 10.5.19-MariaDB-cll-lve
-- PHP Version: 8.1.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gujisoft_bots`
--

-- --------------------------------------------------------

--
-- Table structure for table `mezmur_album_bot`
--

CREATE TABLE `mezmur_album_bot` (
  `id` int(11) NOT NULL,
  `chatid` varchar(255) DEFAULT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `buy_album` varchar(255) DEFAULT NULL,
  `buy_book` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `order_type` varchar(255) DEFAULT NULL,
  `lang` varchar(255) DEFAULT 'am'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mezmur_album_bot`
--

INSERT INTO `mezmur_album_bot` (`id`, `chatid`, `fname`, `buy_album`, `buy_book`, `phone`, `state`, `order_type`, `lang`) VALUES
(17, '1468513798', 'Abel', 'yes', 'yes', '0956435212', 'photo', 'album', 'am'),
(18, '', '', NULL, NULL, NULL, NULL, NULL, 'am'),
(19, '6800563832', 'Nahom Mulugeta', NULL, NULL, NULL, 'start', 'album', 'en');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mezmur_album_bot`
--
ALTER TABLE `mezmur_album_bot`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mezmur_album_bot`
--
ALTER TABLE `mezmur_album_bot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
