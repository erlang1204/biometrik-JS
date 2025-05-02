-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 10, 2024 at 10:29 AM
-- Server version: 8.0.39-cll-lve
-- PHP Version: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jakapage_bayu`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_jawaban`
--

CREATE TABLE `tbl_jawaban` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `soal_id` int NOT NULL,
  `jawaban` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_jawaban`
--

INSERT INTO `tbl_jawaban` (`id`, `user_id`, `soal_id`, `jawaban`) VALUES
(159, 33, 1, 'b'),
(160, 33, 2, 'c'),
(161, 33, 3, 'b'),
(162, 33, 4, 'd'),
(163, 33, 5, 'e'),
(164, 33, 6, 'd'),
(165, 33, 7, 'a'),
(166, 33, 8, 'b'),
(167, 33, 9, 'e'),
(168, 33, 10, 'c'),
(169, 33, 11, 'e'),
(170, 33, 12, 'f'),
(171, 36, 1, 'a'),
(172, 36, 2, 'b'),
(173, 36, 3, 'c'),
(174, 36, 4, 'f'),
(175, 36, 5, 'c'),
(176, 36, 6, 'b'),
(177, 36, 7, 'c'),
(178, 36, 8, 'f'),
(179, 36, 9, 'b'),
(180, 36, 10, 'a'),
(181, 36, 11, 'b'),
(182, 36, 12, 'c'),
(183, 37, 1, 'a'),
(184, 37, 2, 'b'),
(185, 37, 3, 'e'),
(186, 37, 4, 'f'),
(187, 37, 5, 'b'),
(188, 37, 6, 'a'),
(189, 37, 7, 'c'),
(190, 37, 8, 'b'),
(191, 37, 9, 'e'),
(192, 37, 10, 'f'),
(193, 37, 11, 'a'),
(194, 37, 12, 'c');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_soal`
--

CREATE TABLE `tbl_soal` (
  `id` int NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `jawaban` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_soal`
--

INSERT INTO `tbl_soal` (`id`, `gambar`, `jawaban`) VALUES
(1, 'no1.png', 'B'),
(2, 'no2.png', 'C'),
(3, 'no3.png', 'B'),
(4, 'no4.png', 'D'),
(5, 'no5.png', 'E'),
(6, 'no6.png', 'D'),
(7, 'no7.png', 'A'),
(8, 'no8.png', 'B'),
(9, 'no9.png', 'C'),
(10, 'no10.png', 'D'),
(11, 'no11.png', 'B'),
(12, 'no12.png', 'E');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `verification_code` int NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `finish_test` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `name`, `contact_number`, `email`, `username`, `password`, `verification_code`, `role`, `start_time`, `finish_test`) VALUES
(3, 'admin', '838393', 'erlangbayu2@gmail.com', 'admin', '2f3031575ea493e10429e99c4edefaac', 554541, 'admin', NULL, NULL),
(24, 'bayu', '828373828', 'erlangbayu2@gmail.com', 'bayu', '40d082270f90977bd17472aec54dfbee', 362571, '', '2024-08-08 17:08:54', NULL),
(33, 'juan', '8215544648', 'erlangbayu2@gmail.com', 'juan', 'd39eeaf2407ca460ea64098ae11409c2', 543718, '', '2024-08-08 11:18:09', 1),
(34, 'rijal', '9838339', 'erlangbayu2@gmail.com', 'ijal', '180d470387f8847ec545e93a69f04a9b', 758654, '', NULL, NULL),
(36, 'q', '11', 'erlangbayu2@gmail.com', 'q', 'e4a36824bfd5091de36814a23f0f0048', 137043, 'user', '2024-08-08 21:33:42', 1),
(37, 'p', '7', 'erlangbayu2@gmail.com', 'p', '60b628243cb54334486c73be37eca544', 651500, 'user', '2024-08-09 10:52:12', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_jawaban`
--
ALTER TABLE `tbl_jawaban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_1` (`soal_id`),
  ADD KEY `fk_2` (`user_id`);

--
-- Indexes for table `tbl_soal`
--
ALTER TABLE `tbl_soal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_jawaban`
--
ALTER TABLE `tbl_jawaban`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `tbl_soal`
--
ALTER TABLE `tbl_soal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_jawaban`
--
ALTER TABLE `tbl_jawaban`
  ADD CONSTRAINT `fk_1` FOREIGN KEY (`soal_id`) REFERENCES `tbl_soal` (`id`),
  ADD CONSTRAINT `fk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
