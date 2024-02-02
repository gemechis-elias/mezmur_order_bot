-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 02, 2024 at 08:26 PM
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
-- Table structure for table `mezmur_album_orders`
--

CREATE TABLE `mezmur_album_orders` (
  `id` int(11) NOT NULL,
  `chatid` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mezmur_album_orders`
--

INSERT INTO `mezmur_album_orders` (`id`, `chatid`, `name`, `phone`, `type`, `image`, `state`) VALUES
(44, '1468513798', 'Abebe Kebede', '091122334455', 'album', 'https://www.horansoftware.com/bots/mezmur_album_screenshots/2024-02-20-19-11-file_6.jpg', 'paid'),
(45, '1468513798', 'Gemechis', '09102342127', 'book', 'https://www.horansoftware.com/bots/mezmur_album_screenshots/2024-02-20-19-50-file_7.jpg', 'paid'),
(46, '1468513798', 'Abel', '0956435212', 'album', 'https://www.horansoftware.com/bots/mezmur_album_screenshots/2024-02-20-20-50-file_8.jpg', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mezmur_album_orders`
--
ALTER TABLE `mezmur_album_orders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mezmur_album_orders`
--
ALTER TABLE `mezmur_album_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
