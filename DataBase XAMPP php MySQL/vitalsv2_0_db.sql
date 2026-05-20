-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 01:39 PM
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
-- Database: `vitalsv2.0_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) DEFAULT 0,
  `age` int(3) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `weight` int(5) DEFAULT NULL,
  `height` int(5) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `scan_command` varchar(20) DEFAULT 'IDLE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `age`, `sex`, `weight`, `height`, `address`, `is_active`, `scan_command`) VALUES
(1, 'EHHHH', 'eh@vitals.com', '123', 0, 52, 'Male', 22, 234, NULL, 0, 'IDLE'),
(2, 'pia', 'e3@vitals.com', '123', 0, 5, 'Male', 6, 12, NULL, 0, 'IDLE'),
(3, 'Admin', 'admin@vitals.com', 'admin123', 1, 24, 'Male', 56, 169, 'Tuguegarao City, Cagayan', 0, 'IDLE'),
(4, 'Leslie', 'leslie@gmail.com', '123', 0, 22, 'Female', 59, 152, NULL, 0, 'IDLE'),
(5, 'sophia', 'sophia@gmail.com', '123', 0, 21, 'Female', 56, 151, NULL, 0, 'IDLE'),
(6, 'Kenneth sedano ventura', 'kennethventura727@gmai.com', '123', 0, 21, 'Male', 70, 168, NULL, 0, 'IDLE'),
(7, 'Lance', 'lance@gmail.com', 'QWERT', 0, 22, 'Male', 62, 170, NULL, 0, 'IDLE'),
(10, 'Leslie', 'policarpio@gmail.com', '12345', 0, NULL, NULL, NULL, NULL, NULL, 0, 'IDLE'),
(11, 'Angel', 'angel@gmail.com', '123', 0, 21, 'Female', 40, 166, NULL, 0, 'IDLE'),
(12, 'Kenneth', 'kenneth@gmail.com', '123', 0, 22, 'Male', 70, 163, NULL, 0, 'IDLE'),
(13, 'Hans', 'hans@gmail.com', 'kupal', 0, 23, 'Male', 68, 161, NULL, 0, 'IDLE'),
(14, 'Mark Edriane', 'markedriane@gmail.com', 'walds', 0, 22, 'Male', 59, 168, NULL, 0, 'IDLE'),
(15, 'Jasmine Manuel', 'jasmine@gmail.com', '123', 0, 22, 'Female', 59, 164, NULL, 0, 'IDLE'),
(16, 'Jazthin', 'jazthin@gmail.com', '123', 0, 22, 'Male', 84, 163, NULL, 0, 'IDLE'),
(17, '123', '123@w.com', '$2y$10$fsGIsoleBvMY.CMQG4YjxeGjXPEOS/86cR0GOZI5toMo8FecTR2.q', 0, NULL, NULL, NULL, NULL, NULL, 0, 'IDLE'),
(18, 'qwe', 'qw@w.com', '12', 0, 12, 'Female', 123, 232, NULL, 0, 'IDLE'),
(19, 'Juzlhyn Yvonne Quizon', 'juzlhynyvonne22@gmail.com', 'juzhlyn123', 0, 18, 'Female', 50, 152, NULL, 0, 'IDLE'),
(20, 'James', 'james@gmail.com', '123', 0, 23, 'Male', 58, 160, NULL, 0, 'IDLE'),
(21, 'Tabulinajames', 'markpogisimangan@gmail.com', '12345678', 0, NULL, NULL, NULL, NULL, NULL, 0, 'IDLE'),
(22, 'Jemar Marcos', 'jemar.marcos@ucv.edu.ph', 'jem123', 0, 23, 'Male', 62, 175, NULL, 0, 'IDLE'),
(23, 'Francis Marcos', 'paul.notar.marcos@gmail.com', '123', 0, NULL, NULL, NULL, NULL, NULL, 0, 'IDLE'),
(24, 'Paul', '123.com@ucv', '123', 0, 22, 'Male', 55, 175, NULL, 0, 'IDLE'),
(25, 'Paul', '123.com@f', '123', 0, 23, 'Male', 67, 168, NULL, 0, 'IDLE'),
(26, 'Sam Lacar', 'jasminelacar2@gmail.com', 'jasminesam', 0, 21, 'Female', 49, 149, NULL, 0, 'IDLE'),
(27, 'Juzthine Paul Quizon', '1234@w.com', '123', 0, 23, 'Male', 87, 168, 'Samonte,Quezon Isabela\r\n', 0, 'IDLE'),
(28, 'Arvin M. Olarte', 'arvin@gmail.com', '123', 0, 22, 'Male', 63, 178, 'Zone 5, Minanga Norte, lasam ,cagayan', 0, 'IDLE'),
(29, 'Ivy B. Dacut', 'dacutivy@gmail.com', '123', 0, 22, 'Female', 80, 150, 'Pagulayan street, lanna enrile cagayan', 0, 'IDLE'),
(32, 'Sophia Caban', 'sophiacaban@gmail.com', '123', 0, 21, 'Female', 56, 151, 'Annafunan East, Tug City, Cagayan', 0, 'IDLE'),
(33, 'Juan dela Cruz, RN', 'juan@vitals.com', '123', 0, 3, 'Male', 2, 2, 'kjbhj', 0, 'IDLE');

-- --------------------------------------------------------

--
-- Table structure for table `vitals_history`
--

CREATE TABLE `vitals_history` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `heart_rate` varchar(10) DEFAULT NULL,
  `temperature` varchar(10) DEFAULT NULL,
  `spo2` varchar(10) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `respiration` varchar(10) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vitals_history`
--

INSERT INTO `vitals_history` (`id`, `patient_id`, `heart_rate`, `temperature`, `spo2`, `blood_pressure`, `respiration`, `notes`, `created_at`) VALUES
(53, 1, '125', '35.2', '99', '160/96', '36', NULL, '2026-05-14 14:50:46'),
(54, 7, '166', '31.3', '100', '0/0', '0', NULL, '2026-05-14 14:54:00'),
(55, 25, '60', '36.5', '100', '120/80', '16', NULL, '2026-05-14 15:15:13'),
(56, 1, '125', '39.3', '100', '105/63', '0', NULL, '2026-05-14 16:02:45'),
(57, 1, '50', '31.2', '99', '96/60', '140', NULL, '2026-05-14 17:07:21'),
(58, 1, '83', '31.3', '100', '98/60', '192', NULL, '2026-05-14 18:24:34'),
(59, 13, '93', '34.7', '99', '160/96', '108', NULL, '2026-05-15 02:37:31'),
(60, 28, '108', '34.9', '100', '116/68', '32', NULL, '2026-05-15 03:15:14'),
(61, 29, '120', '35.6', '100', '196/60', '36', NULL, '2026-05-15 03:24:44'),
(62, 29, '125', '34.6', '0', '0/0', '0', NULL, '2026-05-15 03:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `vitals_temp`
--

CREATE TABLE `vitals_temp` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `heart_rate` varchar(10) DEFAULT '0',
  `temperature` varchar(10) DEFAULT '0',
  `spo2` varchar(10) DEFAULT '0',
  `blood_pressure` varchar(20) DEFAULT '0/0',
  `respiration` varchar(10) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vitals_temp`
--

INSERT INTO `vitals_temp` (`id`, `patient_id`, `heart_rate`, `temperature`, `spo2`, `blood_pressure`, `respiration`, `created_at`) VALUES
(84, 32, '115', '36.6', '100', '160/96', '35', '2026-05-15 08:47:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vitals_history`
--
ALTER TABLE `vitals_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `vitals_temp`
--
ALTER TABLE `vitals_temp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `vitals_history`
--
ALTER TABLE `vitals_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `vitals_temp`
--
ALTER TABLE `vitals_temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vitals_history`
--
ALTER TABLE `vitals_history`
  ADD CONSTRAINT `vitals_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vitals_temp`
--
ALTER TABLE `vitals_temp`
  ADD CONSTRAINT `vitals_temp_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
