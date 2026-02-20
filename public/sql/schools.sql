-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2026 at 06:17 AM
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
-- Database: `sbfp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` varchar(255) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `district` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `school_id`, `school_name`, `district`, `created_at`, `updated_at`) VALUES
(1, '126557', 'Aglayan Central School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(2, '126574', 'Airport Village Elementary School', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(3, '325505', 'Apo Macote National High School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(4, '408493', 'Apu Palamguwan Cultural Education Center, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(5, '126575', 'Baganao Elementary School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(6, '126534', 'Bagong Silang Elementary School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(7, '126535', 'Balangbang Elementary School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(8, '126558', 'Bangcud Central School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(9, '303946', 'Bangcud National High School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(10, '199510', 'Barangay 9 Elementary School', 4, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(11, '126576', 'BCT Elementary School', 4, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(12, '126559', 'Bendolan Elementary School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(13, '404995', 'Bethel Baptist Christian Academy', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(14, '409078', 'Bible Baptist Malaybalay Christian Academy, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(15, '126560', 'Binalbagan Elementary School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(16, '400609', 'Brightspark Christian Academy', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(17, '400625', 'BUGEMCO Learning Center', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(18, '303950', 'Bukidnon National High School', 3, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(19, '341061', 'Bukidnon National High School Senior High School Campus', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(20, '600130', 'Bukidnon State University', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(21, '500409', 'Busdi Integrated School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(22, '126561', 'Cabangahan Elementary School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(23, '126537', 'Caburacanan Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(24, '126562', 'Calawag Elementary School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(25, '500245', 'Can-ayan Integrated School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(26, '501195', 'Candiisan Integrated School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(27, '501189', 'Capitan Angel Integrated School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(28, '501580', 'Casisang Central Integrated School', 4, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(29, '404997', 'Casisang International Christian School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(30, '314914', 'Casisang National High School', 4, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(31, '126580', 'Dalwangan Elementary School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(32, '325504', 'Dalwangan National High School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(33, '103610', 'Damitan Elementary School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(34, '502945', 'Dapulan Integrated School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(35, '126539', 'Dumayas Elementary School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(36, '407945', 'El Gibbor Faithhouse Academy Incorporated', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(37, '404998', 'Heights Kinderland Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(38, '126581', 'Imbayao Elementary School', 3, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(39, '325503', 'Imbayao National High School', 3, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(40, '103677', 'Incalbog Elementary School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(41, '126540', 'Indalasa Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(42, '126568', 'Isabela Ayala Gonzales Elementary School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(43, '126582', 'Kalasungay Central School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(44, '314915', 'Kalasungay National High School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(45, '199518', 'Kibalabag Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(46, '501188', 'Kibalabag Integrated School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(47, '501579', 'Kilap-agan Integrated School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(48, '483543', 'Kitanglad View Adventist Elementary School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(49, '503036', 'Kulaman Integrated School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(50, '126564', 'Laguitas Elementary School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(51, '126542', 'Lalawan Elementary School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(52, '325501', 'Lalawan National High School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(53, '405000', 'Lalawan SDA Elementary School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(54, '126543', 'Langasihan Elementary School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(55, '126544', 'Linabo Central School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(56, '459520', 'Little Gems Learning Center, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(57, '410928', 'Little Orchard School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(58, '502744', 'Lunokan Integrated School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(59, '300507', 'Luyungan High School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(60, '501852', 'Mabuhay Integrated School', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(61, '126566', 'Macote Elementary School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(62, '501190', 'Magsaysay Integrated School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(63, '405001', 'Malaybalay City Adventist Elementary School, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(64, '126586', 'Malaybalay City Central School', 4, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(65, '314916', 'Malaybalay City National High School', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(66, '314904', 'Malaybalay City National Science High School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(67, '502800', 'Maligaya Integrated School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(68, '126547', 'Managok Central School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(69, '303973', 'Managok National High School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(70, '501854', 'Manalog Integrated School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(71, '501853', 'Mapayag Integrated School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(72, '126548', 'Mapulo Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(73, '459522', 'Marywoods Academy Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(74, '126549', 'Matangpatang Elementary School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(75, '126550', 'Miglamin Elementary School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(76, '314920', 'Miglamin National High School', 9, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(77, '405003', 'Mindanao Arts & Technological Institute, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(78, '126588', 'Natid-asan Elementary School', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(79, '126589', 'New Ilocos Elementary School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(80, '126570', 'Padernal Elementary School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(81, '199511', 'Paiwaig Elementary School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(82, '126551', 'Panamucan Elementary School', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(83, '126590', 'Patpat Elementary School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(84, '306338', 'Patpat National High School', 1, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(85, '103629', 'Pighalugan Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(86, '502740', 'Pigpamulahan Integrated School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(87, '405004', 'Saint Isidore High School', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(88, '405006', 'San Isidro College', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(89, '126591', 'San Jose Elementary School', 5, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(90, '303982', 'San Martin Agro-Industrial National High School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(91, '126571', 'San Martin–Sinanglanan Elementary School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(92, '502738', 'San Roque Integrated School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(93, '126552', 'Sawaga Elementary School', 8, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(94, '126553', 'Silae Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(95, '303984', 'Silae National High School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(96, '501582', 'Simaya Integrated School', 7, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(97, '405008', 'St. Isidore Academy of Bukidnon, Inc. Sinanglanan', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(98, '459521', 'St. John\'s School of Malaybalay City, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(99, '405009', 'St. Michael High School Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(100, '126554', 'St. Peter Elementary School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(101, '314905', 'St. Peter National High School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(102, '126592', 'Sta. Ana Elementary School', 3, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(103, '403708', 'STI Malaybalay', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(104, '126593', 'Sumpong Central School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(105, '405010', 'Sunbeam Christian Academy of Bangcud, Inc.', NULL, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(106, '126594', 'Tag-ilanao Elementary School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(107, '137297', 'Tamogawe Elementary School', 6, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(108, '126595', 'Tintinaan Elementary School', 2, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(109, '501581', 'Tuburan Integrated School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53'),
(110, '126556', 'Zamboanguita Central School', 10, '2026-02-01 03:24:53', '2026-02-01 03:24:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schools_school_id_index` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
