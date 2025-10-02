-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 03:58 PM
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
-- Database: `gravengel`
--

-- --------------------------------------------------------

--
-- Table structure for table `burials`
--

CREATE TABLE `burials` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `burial_id` varchar(50) NOT NULL,
  `transaction_id` varchar(32) DEFAULT NULL,
  `deceased_first_name` varchar(80) DEFAULT NULL,
  `deceased_middle_name` varchar(80) DEFAULT NULL,
  `deceased_last_name` varchar(80) DEFAULT NULL,
  `deceased_suffix` varchar(20) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `sex` enum('male','female','other') DEFAULT NULL,
  `date_born` date DEFAULT NULL,
  `date_died` date DEFAULT NULL,
  `cause_of_death` varchar(255) DEFAULT NULL,
  `grave_level` varchar(50) DEFAULT NULL,
  `grave_type` varchar(50) DEFAULT NULL,
  `interment_full_name` varchar(255) DEFAULT NULL,
  `interment_relationship` varchar(100) DEFAULT NULL,
  `interment_contact_number` varchar(40) DEFAULT NULL,
  `interment_email` varchar(150) DEFAULT NULL,
  `interment_address` varchar(255) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `rental_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by_user_id` int(11) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `burials`
--

INSERT INTO `burials` (`id`, `plot_id`, `burial_id`, `transaction_id`, `deceased_first_name`, `deceased_middle_name`, `deceased_last_name`, `deceased_suffix`, `age`, `sex`, `date_born`, `date_died`, `cause_of_death`, `grave_level`, `grave_type`, `interment_full_name`, `interment_relationship`, `interment_contact_number`, `interment_email`, `interment_address`, `payment_amount`, `rental_date`, `expiry_date`, `requirements`, `is_active`, `created_by_user_id`, `updated_by_user_id`, `created_at`) VALUES
(17, 544, 'B-933270', '20251001-485', 'cascas', 'asc', 'csacas', 'I', '60', 'male', '2012-04-11', '2025-10-02', 'cascasc', 'Level 1', 'Apartment', 'cascasc', 'Parent', '0987 654 3211', NULL, 'dfsgvdsfggdf, Brgy. Balaquid, Cabucgayan, Biliran, 3445', 5000.00, '2025-10-02 12:00:00', '2030-10-02 12:00:00', 'Death Certificate with registry number, Barangay Indigency for Burial Assistance', 1, 1, NULL, '2025-10-02 05:55:44'),
(18, 543, 'B-90E339', '20251002-211', 'ascasc', 'ascasc', 'ascasc', 'Sr.', '33', 'male', '2025-10-02', '2025-10-01', 'accident', 'Level 1', 'Apartment', 'vdvsd', 'Child', '0987 654 3211', 'minion.new002@gmail.com', 'fsdgg, Brgy. Santa Cruz, Pamplona, Cagayan, 4543', 5000.00, '2025-10-02 12:00:00', '2030-10-02 12:00:00', 'Death Certificate with registry number, Barangay Indigency for Burial Assistance, Voter&#039;s ID, Cedula', 1, 1, NULL, '2025-10-02 06:15:08'),
(20, 545, 'B-9E7ADF', '20251002-908', 'saca', 'm', 'scac', 'Sr.', '60', 'male', '2025-10-02', '2025-10-02', 'asc', 'Level 1', 'Apartment', 'sacas', 'Spouse', '0987 654 3212', 'mdctechservices@gmail.com', 'sadasd, Brgy. Minsuro, Manolo Fortich, Bukidnon, 6456', 5000.00, '2025-09-02 10:13:00', '2025-11-02 02:15:00', 'Death Certificate with registry number, Barangay Indigency for Burial Assistance, Voter&#039;s ID, Cedula, Sulat Kahilingan', 1, 1, NULL, '2025-10-02 07:44:51');

-- --------------------------------------------------------

--
-- Table structure for table `map_blocks`
--

CREATE TABLE `map_blocks` (
  `id` int(11) NOT NULL,
  `block_key` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `coords` varchar(255) NOT NULL COMMENT 'Format: x1,y1,x2,y2',
  `offset_x` int(11) NOT NULL DEFAULT 0,
  `offset_y` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `map_blocks`
--

INSERT INTO `map_blocks` (`id`, `block_key`, `title`, `coords`, `offset_x`, `offset_y`) VALUES
(1, 'ltls-001', 'Block-A', '166,243,216,652', 0, 0),
(2, 'ltrs-002', 'Block-B', '256,247,216,645', 0, 0),
(3, 'ltls-003', 'Block-C', '317,238,284,640', 0, 0),
(4, 'ltrs-004', 'ltrs-004', '362,638,319,238', 0, 0),
(5, 'ltls-005', 'ltls-005', '428,235,390,647', 0, 0),
(6, 'ltrs-006', 'ltrs-006', '470,640,428,238', 0, 0),
(7, 'ltls-007', 'ltls-007', '489,247,541,650', 0, 0),
(8, 'ltrs-008', 'ltrs-008', '583,643,527,240', 0, 0),
(9, 'ltls-009', 'ltls-009', '602,247,644,647', 0, 0),
(10, 'ltrs-010', 'ltrs-010', '684,247,649,645', 0, 0),
(11, 'ltls-011', 'ltls-011', '701,240,750,643', 0, 0),
(12, 'ltrs-012', 'block-E', '790,250,755,650', 0, 0),
(13, 'ltls-013', 'ltls-013', '856,235,814,638', 0, 0),
(14, 'ltrs-014', 'ltrs-014', '903,640,856,235', 0, 0),
(15, 'ltls-015', 'ltls-015', '922,235,962,652', 0, 0),
(16, 'ltrs-016', 'ltrs-016', '1000,240,969,650', 0, 0),
(17, 'rtls-017', 'rtls-017', '1353,245,1394,640', 0, 0),
(18, 'rtrs-018', 'rtrs-018', '1431,243,1394,643', 0, 0),
(19, 'rtls-019', 'rtls-019', '1460,231,1502,640', 0, 0),
(20, 'rtrs-020', 'rtrs-020', '1537,238,1502,645', 0, 0),
(21, 'rtls-021', 'rtls-021', '1610,238,1573,636', 0, 0),
(22, 'rtrs-022', 'rtrs-022', '1650,245,1613,643', 0, 0),
(23, 'rtls-023', 'rtls-023', '1721,226,1681,645', 0, 0),
(24, 'rtrs-024', 'rtrs-024', '1754,243,1719,647', 0, 0),
(25, 'rtls-025', 'rtls-025', '1829,238,1789,643', 0, 0),
(26, 'rtrs-026', 'rtrs-026', '1865,228,1827,643', 0, 0),
(27, 'rtls-027', 'rtls-027', '1933,240,1893,650', 0, 0),
(28, 'rtrs-028', 'rtrs-028', '1971,238,1933,640', 0, 0),
(29, 'rtls-029', 'rtls-029', '1997,640,2044,228', 0, 0),
(30, 'rtrs-030', 'rtrs-030', '2077,243,2044,645', 0, 0),
(31, 'rtls-031', 'rtls-031', '2147,231,2103,645', 0, 0),
(32, 'rtrs-032', 'rtrs-032', '2188,245,2147,647', 0, 0),
(33, 'rdls-033', 'rdls-033', '1170,1034,1222,1373', 0, 0),
(34, 'rdls-034', 'rdls-034', '1278,1029,1243,1373', 0, 0),
(35, 'rdrs-035', 'rdrs-035', '1323,1034,1288,1380', 0, 0),
(36, 'rdls-036', 'rdls-036', '1386,1034,1351,1373', 0, 0),
(37, 'rdrs-037', 'rdrs-037', '1427,1031,1391,1377', 0, 0),
(38, 'rdls-038', 'rdls-038', '1492,1034,1457,1377', 0, 0),
(39, 'rdrs-039', 'rdrs-039', '1530,1036,1497,1377', 0, 0),
(40, 'rdls-040', 'rdls-040', '1599,1043,1566,1380', 0, 0),
(41, 'rdrs-041', 'rdrs-041', '1648,1043,1610,1380', 0, 0),
(42, 'rdls-042', 'rdls-042', '1709,1045,1669,1384', 0, 0),
(43, 'rdrs-043', 'rdrs-043', '1752,1045,1716,1382', 0, 0),
(44, 'rdls-044', 'rdls-044', '1775,1055,1822,1375', 0, 0),
(45, 'rdrs-045', 'rdrs-045', '1860,1384,1815,1045', 0, 0),
(46, 'rdls-046', 'rdls-046', '1919,1048,1886,1384', 0, 0),
(47, 'rdrs-047', 'rdrs-047', '1964,1387,1926,1050', 0, 0),
(48, 'rdls-048', 'rdls-048', '2027,1052,1983,1382', 0, 0),
(49, 'rdrs-049', 'rdrs-049', '2070,1052,2030,1396', 0, 0),
(50, 'rdls-050', 'rdls-050', '2129,1057,2095,1394', 0, 0),
(51, 'rdrs-051', 'rdrs-051', '2169,1057,2138,1396', 0, 0),
(52, 'rdls-052', 'rdls-052', '2188,1060,2237,1399', 0, 0),
(53, 'rdrs-053', 'rdrs-053', '2272,1067,2239,1399', 0, 0),
(54, 'rdtop-054', 'rdtop-054', '1259,897,2605,977', 0, 0),
(55, 'rdbottom-055', 'rdbottom-055', '2317,1613,899,1488', 0, 0),
(56, 'rdbotR-056', 'rdbotR-056', '2633,1610,2317,1547', 0, 0),
(57, 'dts-057', 'dts-057', '2352,1001,2612,1048', 0, 0),
(58, 'dds-058', 'dds-058', '2612,1090,2357,1052', 0, 0),
(59, 'dts-059', 'dts-059', '2352,1116,2616,1158', 0, 0),
(60, 'dds-060', 'dds-060', '2360,1161,2614,1203', 0, 0),
(61, 'dts-061', 'dts-061', '2371,1224,2609,1276', 0, 0),
(62, 'dds-062', 'dds-062', '2364,1276,2616,1314', 0, 0),
(63, 'dts-063', 'dts-063', '2369,1333,2619,1382', 0, 0),
(64, 'dds-064', 'dds-064', '2371,1387,2626,1420', 0, 0),
(65, 'dts-065', 'dts-065', '2378,1443,2623,1493', 0, 0),
(66, 'dds-066', 'dds-066', '2376,1497,2621,1528', 0, 0),
(67, 'c-077', 'c-077', '732,961,762,1024', 0, 0),
(68, 'c-078', 'c-078', '729,1029,764,1090', 0, 0),
(69, 'c-079', 'c-079', '736,1097,769,1156', 0, 0),
(70, 'c-080', 'c-080', '739,1163,767,1224', 0, 0),
(71, 'c-081', 'c-081', '743,1229,772,1293', 0, 0),
(72, 'c-082', 'c-082', '746,1297,772,1356', 0, 0),
(73, 'c-083', 'c-083', '774,963,802,1024', 0, 0),
(74, 'c-084', 'c-084', '774,1031,805,1090', 0, 0),
(75, 'c-085', 'c-085', '781,1100,805,1151', 0, 0),
(76, 'c-086', 'c-086', '783,1165,809,1217', 0, 0),
(77, 'c-087', 'c-087', '786,1227,814,1293', 0, 0),
(78, 'c-088', 'c-088', '786,1300,816,1359', 0, 0),
(79, 'c-089', 'c-089', '814,958,847,1019', 0, 0),
(80, 'c-090', 'c-090', '816,1029,849,1090', 0, 0),
(81, 'c-091', 'c-091', '821,1095,852,1158', 0, 0),
(82, 'c-092', 'c-092', '823,1163,852,1222', 0, 0),
(83, 'c-093', 'c-093', '826,1229,849,1288', 0, 0),
(84, 'c-094', 'c-094', '828,1295,856,1354', 0, 0),
(85, 'office-001', 'office-001', '969,1118,1132,1264', 0, 0),
(86, 'crstorage-002', 'crstorage-002', '508,59,670,144', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `burial_id` varchar(32) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `severity` enum('info','warning','danger') NOT NULL DEFAULT 'warning',
  `due_date` date DEFAULT NULL,
  `kind` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `burial_id`, `title`, `message`, `severity`, `due_date`, `kind`, `created_at`) VALUES
(473, 'B-9E7ADF', 'Rental expires in 30 days', 'Grave Block-A (Block-A003) rental expires on Nov 02, 2025.', 'warning', '2025-11-02', 'expiry_30', '2025-10-02 17:05:15');

-- --------------------------------------------------------

--
-- Table structure for table `notification_email_status`
--

CREATE TABLE `notification_email_status` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_type` enum('admin','staff','interment') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_error` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_email_status`
--

INSERT INTO `notification_email_status` (`id`, `notification_id`, `recipient_type`, `recipient_email`, `sent`, `last_attempt_at`, `attempts`, `last_error`, `created_at`, `updated_at`) VALUES
(403, 473, 'admin', 'menarddelacruz.basc@gmail.com', 1, '2025-10-02 17:05:21', 1, NULL, '2025-10-02 17:05:15', '2025-10-02 17:05:21'),
(404, 473, 'staff', 'minionm219@gmail.com', 1, '2025-10-02 17:05:27', 1, NULL, '2025-10-02 17:05:15', '2025-10-02 17:05:27'),
(405, 473, 'staff', 'lovecano30@gmail.com', 1, '2025-10-02 17:05:33', 1, NULL, '2025-10-02 17:05:15', '2025-10-02 17:05:33'),
(407, 473, 'interment', 'mdctechservices@gmail.com', 1, '2025-10-02 17:05:39', 1, NULL, '2025-10-02 17:05:15', '2025-10-02 17:05:39');

-- --------------------------------------------------------

--
-- Table structure for table `notification_user`
--

CREATE TABLE `notification_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_user`
--

INSERT INTO `notification_user` (`id`, `notification_id`, `user_id`, `is_read`, `read_at`, `created_at`) VALUES
(103, 473, 1, 0, NULL, '2025-10-02 17:05:15'),
(104, 473, 7, 1, '2025-10-02 19:38:32', '2025-10-02 17:05:15'),
(105, 473, 8, 0, NULL, '2025-10-02 17:05:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 7, '2210cb1832c245cc40219c6a876d97856c7469211fc117db67c70f8d6803ca30', '2025-09-20 22:45:54', '2025-09-20 04:46:43', '2025-09-20 04:45:54'),
(2, 7, 'f446ee7e049a717eb78b6a6d0a351c7d80b5048c5b4e40b84ad1512c833422e7', '2025-09-21 02:43:38', '2025-09-20 08:44:11', '2025-09-20 08:43:38'),
(3, 7, '8cccfb7218b1b23734f519ba938a9dfcae70c6c64eca4936f81f78eb3b8b18e3', '2025-09-21 03:04:46', '2025-09-20 09:06:48', '2025-09-20 09:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `plots`
--

CREATE TABLE `plots` (
  `id` int(11) NOT NULL,
  `map_block_id` int(11) NOT NULL,
  `plot_number` varchar(50) NOT NULL,
  `status` enum('vacant','occupied','reserved','bone') NOT NULL DEFAULT 'vacant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plots`
--

INSERT INTO `plots` (`id`, `map_block_id`, `plot_number`, `status`) VALUES
(407, 2, '1', 'vacant'),
(408, 2, '2', 'vacant'),
(409, 2, '3', 'vacant'),
(410, 2, '4', 'vacant'),
(411, 2, '5', 'vacant'),
(412, 2, '6', 'vacant'),
(413, 2, '7', 'vacant'),
(414, 2, '8', 'vacant'),
(415, 2, '9', 'vacant'),
(416, 2, '10', 'vacant'),
(417, 2, '11', 'vacant'),
(418, 2, '12', 'vacant'),
(419, 2, '13', 'vacant'),
(420, 2, '14', 'vacant'),
(421, 2, '15', 'vacant'),
(422, 2, '16', 'vacant'),
(423, 2, '17', 'vacant'),
(424, 2, '18', 'vacant'),
(425, 2, '19', 'vacant'),
(426, 2, '20', 'vacant'),
(427, 2, '21', 'vacant'),
(428, 2, '22', 'vacant'),
(429, 2, '23', 'vacant'),
(430, 2, '24', 'vacant'),
(543, 1, 'Block-A001', 'occupied'),
(544, 1, 'Block-A002', 'occupied'),
(545, 1, 'Block-A003', 'occupied'),
(546, 1, 'Block-A004', 'vacant'),
(547, 1, 'Block-A005', 'vacant'),
(548, 1, 'Block-A006', 'vacant'),
(549, 1, 'Block-A007', 'vacant'),
(550, 1, 'Block-A008', 'vacant'),
(551, 1, 'Block-A009', 'vacant'),
(552, 1, 'Block-A010', 'vacant'),
(553, 1, 'Block-A011', 'vacant'),
(554, 1, 'Block-A012', 'vacant'),
(555, 1, 'Block-A013', 'vacant'),
(556, 1, 'Block-A014', 'vacant'),
(557, 1, 'Block-A015', 'vacant'),
(558, 1, 'Block-A016', 'vacant'),
(559, 1, 'Block-A017', 'vacant'),
(560, 1, 'Block-A018', 'vacant'),
(561, 1, 'Block-A019', 'vacant'),
(562, 1, 'Block-A020', 'vacant'),
(563, 1, 'Block-A021', 'vacant'),
(564, 1, 'Block-A022', 'vacant'),
(565, 1, 'Block-A023', 'vacant'),
(566, 1, 'Block-A024', 'vacant'),
(567, 1, 'Block-A025', 'vacant'),
(568, 1, 'Block-A026', 'vacant'),
(569, 1, 'Block-A027', 'vacant'),
(570, 1, 'Block-A028', 'vacant'),
(571, 1, 'Block-A029', 'vacant'),
(572, 1, 'Block-A030', 'vacant'),
(573, 1, 'Block-A031', 'vacant'),
(574, 1, 'Block-A032', 'vacant'),
(575, 1, 'Block-A033', 'vacant'),
(576, 1, 'Block-A034', 'vacant'),
(577, 1, 'Block-A035', 'vacant'),
(578, 1, 'Block-A036', 'vacant'),
(579, 1, 'Block-A037', 'vacant'),
(580, 1, 'Block-A038', 'vacant'),
(581, 1, 'Block-A039', 'vacant'),
(582, 1, 'Block-A040', 'vacant'),
(583, 1, 'Block-A041', 'vacant'),
(584, 1, 'Block-A042', 'vacant'),
(585, 1, 'Block-A043', 'vacant'),
(586, 1, 'Block-A044', 'vacant'),
(587, 1, 'Block-A045', 'vacant'),
(588, 1, 'Block-A046', 'vacant'),
(589, 1, 'Block-A047', 'vacant'),
(590, 1, 'Block-A048', 'vacant'),
(591, 1, 'Block-A049', 'vacant'),
(592, 1, 'Block-A050', 'vacant'),
(593, 1, 'Block-A051', 'vacant'),
(594, 1, 'Block-A052', 'vacant'),
(595, 1, 'Block-A053', 'vacant'),
(596, 1, 'Block-A054', 'vacant'),
(597, 1, 'Block-A055', 'vacant'),
(598, 1, 'Block-A056', 'vacant'),
(599, 1, 'Block-A057', 'vacant'),
(600, 1, 'Block-A058', 'vacant'),
(601, 1, 'Block-A059', 'vacant'),
(602, 1, 'Block-A060', 'vacant'),
(603, 1, 'Block-A061', 'vacant'),
(604, 1, 'Block-A062', 'vacant'),
(605, 1, 'Block-A063', 'vacant'),
(606, 1, 'Block-A064', 'vacant'),
(607, 1, 'Block-A065', 'vacant'),
(608, 1, 'Block-A066', 'vacant'),
(621, 13, 'ltls-013001', 'vacant'),
(622, 13, 'ltls-013002', 'vacant'),
(623, 13, 'ltls-013003', 'vacant'),
(624, 13, 'ltls-013004', 'vacant'),
(625, 13, 'ltls-013005', 'vacant'),
(626, 13, 'ltls-013006', 'vacant'),
(627, 13, 'ltls-013007', 'vacant'),
(628, 13, 'ltls-013008', 'vacant'),
(629, 13, 'ltls-013009', 'vacant'),
(630, 13, 'ltls-013010', 'vacant'),
(631, 13, 'ltls-013011', 'vacant'),
(632, 13, 'ltls-013012', 'vacant'),
(633, 13, 'ltls-013013', 'vacant'),
(634, 13, 'ltls-013014', 'vacant'),
(635, 13, 'ltls-013015', 'vacant'),
(636, 13, 'ltls-013016', 'vacant'),
(637, 13, 'ltls-013017', 'vacant'),
(638, 13, 'ltls-013018', 'vacant'),
(639, 13, 'ltls-013019', 'vacant'),
(640, 13, 'ltls-013020', 'vacant'),
(641, 13, 'ltls-013021', 'vacant'),
(642, 13, 'ltls-013022', 'vacant'),
(643, 13, 'ltls-013023', 'vacant'),
(644, 13, 'ltls-013024', 'vacant'),
(645, 13, 'ltls-013025', 'vacant'),
(646, 13, 'ltls-013026', 'vacant'),
(647, 13, 'ltls-013027', 'vacant'),
(648, 13, 'ltls-013028', 'vacant'),
(649, 13, 'ltls-013029', 'vacant'),
(650, 13, 'ltls-013030', 'vacant'),
(651, 13, 'ltls-013031', 'vacant'),
(652, 13, 'ltls-013032', 'vacant'),
(653, 18, 'rtrs-018001', 'vacant'),
(654, 18, 'rtrs-018002', 'vacant'),
(655, 18, 'rtrs-018003', 'vacant'),
(656, 18, 'rtrs-018004', 'vacant'),
(657, 18, 'rtrs-018005', 'vacant'),
(658, 18, 'rtrs-018006', 'vacant'),
(659, 18, 'rtrs-018007', 'vacant'),
(660, 18, 'rtrs-018008', 'vacant'),
(661, 18, 'rtrs-018009', 'vacant'),
(662, 18, 'rtrs-018010', 'vacant'),
(663, 18, 'rtrs-018011', 'vacant'),
(664, 18, 'rtrs-018012', 'vacant'),
(665, 18, 'rtrs-018013', 'vacant'),
(666, 18, 'rtrs-018014', 'vacant'),
(667, 18, 'rtrs-018015', 'vacant'),
(668, 18, 'rtrs-018016', 'vacant'),
(669, 18, 'rtrs-018017', 'vacant'),
(670, 18, 'rtrs-018018', 'vacant'),
(671, 18, 'rtrs-018019', 'vacant'),
(672, 18, 'rtrs-018020', 'vacant'),
(673, 18, 'rtrs-018021', 'vacant'),
(674, 18, 'rtrs-018022', 'vacant'),
(675, 18, 'rtrs-018023', 'vacant'),
(676, 18, 'rtrs-018024', 'vacant'),
(677, 18, 'rtrs-018025', 'vacant'),
(678, 18, 'rtrs-018026', 'vacant'),
(679, 18, 'rtrs-018027', 'vacant'),
(680, 18, 'rtrs-018028', 'vacant'),
(681, 18, 'rtrs-018029', 'vacant'),
(682, 18, 'rtrs-018030', 'vacant'),
(683, 18, 'rtrs-018031', 'vacant'),
(684, 18, 'rtrs-018032', 'vacant'),
(685, 3, 'Block-C001', 'vacant'),
(686, 3, 'Block-C002', 'vacant'),
(687, 3, 'Block-C003', 'vacant'),
(688, 3, 'Block-C004', 'vacant'),
(689, 3, 'Block-C005', 'vacant'),
(690, 3, 'Block-C006', 'vacant'),
(691, 3, 'Block-C007', 'vacant'),
(692, 3, 'Block-C008', 'vacant'),
(693, 3, 'Block-C009', 'vacant'),
(694, 12, 'block-E001', 'vacant'),
(695, 12, 'block-E002', 'vacant'),
(696, 12, 'block-E003', 'vacant'),
(697, 12, 'block-E004', 'vacant'),
(698, 12, 'block-E005', 'vacant'),
(699, 12, 'block-E006', 'vacant'),
(700, 12, 'block-E007', 'vacant'),
(701, 12, 'block-E008', 'vacant');

-- --------------------------------------------------------

--
-- Table structure for table `plot_layouts`
--

CREATE TABLE `plot_layouts` (
  `id` int(11) NOT NULL,
  `map_block_id` int(11) NOT NULL,
  `modal_rows` int(11) NOT NULL DEFAULT 4,
  `modal_cols` int(11) NOT NULL DEFAULT 8
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plot_layouts`
--

INSERT INTO `plot_layouts` (`id`, `map_block_id`, `modal_rows`, `modal_cols`) VALUES
(1, 1, 3, 22),
(10, 2, 3, 8),
(12, 3, 3, 3),
(17, 13, 4, 8),
(18, 18, 4, 8),
(20, 12, 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `staff_details`
--

CREATE TABLE `staff_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_id` varchar(100) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_details`
--

INSERT INTO `staff_details` (`id`, `user_id`, `staff_id`, `designation`) VALUES
(1, 1, 'S-001', 'System Administrator'),
(7, 7, 'S-002', 'Manager Staff'),
(8, 8, 'S-003', 'developer');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `sex` enum('male','female','other') DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_pwd` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `sex`, `phone`, `address`, `profile_image`, `is_active`, `must_change_pwd`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'menarddelacruz.basc@gmail.com', '$2y$10$dHdP2hrbUX/zeM07zDeAc.UbTjYmrwUrX61aoTAE7BgGlTg28yKia', 'admin', 'Genesys', 'X', NULL, '', '', 'cemeteryMap_73469a.png', 1, 1, '2025-10-02 18:38:55', '2025-09-19 13:20:45', '2025-10-02 18:38:55'),
(7, 'minionm2', 'minionm219@gmail.com', '$2y$10$dHdP2hrbUX/zeM07zDeAc.UbTjYmrwUrX61aoTAE7BgGlTg28yKia', 'staff', 'minion', 'minion', NULL, '0987 654 3221', '', NULL, 1, 0, '2025-10-02 17:52:57', '2025-09-20 04:31:29', '2025-10-02 17:52:57'),
(8, 'Orion', 'lovecano30@gmail.com', '$2y$10$cQ.ZuO8jQykPGmCmhY1mbuaL9AhdiRyh7HSjEVKnWQy/ODtm6y1P6', 'staff', 'Orion Seal', 'Cano', NULL, '0987 654 3765', NULL, NULL, 1, 1, NULL, '2025-09-20 09:17:55', '2025-09-20 09:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `login_at` datetime NOT NULL DEFAULT current_timestamp(),
  `logout_at` datetime DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `login_at`, `logout_at`, `ip_address`, `user_agent`, `is_active`) VALUES
(1, 1, '58047ketcs9e1egni3b7dfgueg', '2025-09-19 20:12:56', '2025-09-19 21:02:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(2, 1, '453ok2scshunsr6mnmb5iqfrae', '2025-09-19 21:03:06', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(3, 1, 'tqtjo0chhssefjj8stg4ql4pct', '2025-09-19 21:03:06', '2025-09-19 21:05:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(4, 1, 'vritle0ag9674abvfe7137lfd6', '2025-09-19 21:13:53', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(5, 1, '7cr6uo81giocarp3717c32nf5m', '2025-09-19 21:13:53', '2025-09-19 22:09:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(6, 1, '9egv1eo9cte7sb1a0pefo4h40a', '2025-09-20 00:39:37', '2025-09-20 04:44:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(7, 7, 'bh4iusgsmlnkclv84u34sucqv3', '2025-09-20 04:45:27', '2025-09-20 08:43:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(8, 1, 'des37cdf0ajp9fq9cuguh63n2u', '2025-09-20 04:48:52', '2025-09-20 05:56:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(9, 1, 'b33s5f9tigalmpd86c51prngv8', '2025-09-20 05:56:19', '2025-09-20 06:58:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(10, 1, '5q05479as317fvduivmf0ts9ro', '2025-09-20 06:59:02', '2025-09-20 08:31:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(11, 1, 'vmjldc67706v1vsvmlcpugmag6', '2025-09-20 08:33:56', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(12, 7, 'qtaftii1o1sgm2smelfd6i38qv', '2025-09-20 08:44:24', '2025-09-20 08:44:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(13, 7, 'uv8dt8424mt9rqpeoq2700ukiv', '2025-09-20 09:02:07', '2025-09-20 09:04:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(14, 7, '4uurns34k73e0cbr4r8n28rali', '2025-09-20 09:07:56', '2025-09-20 10:29:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(15, 1, 'ursbnjeo3gmvb8s2hhke8hir0e', '2025-09-20 10:55:35', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(16, 1, '5q3sk9f1jvtl0qmdk6t603g6e4', '2025-09-29 19:52:38', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(17, 1, 'bnd7qgahq43hpgd73m8suns3c6', '2025-09-30 14:59:04', '2025-10-01 22:03:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(18, 1, 'h3qvg1sepakuqjnqhts6eqeu00', '2025-10-01 22:04:15', '2025-10-01 22:27:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(19, 1, 'nvqokpene8hv1nip6kd47rvpj3', '2025-10-01 22:28:53', '2025-10-01 22:34:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(20, 1, '5ofr0nva071vgnbskuvdcof4ld', '2025-10-02 05:18:28', '2025-10-02 13:36:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(21, 1, 'ebvumunglemin495o01s4egvtq', '2025-10-02 13:38:12', '2025-10-02 13:41:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(22, 1, '6v9knupl09pima25gcr22c6oti', '2025-10-02 13:42:21', '2025-10-02 15:28:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(23, 1, 't230ocvlshtchb989s1i5qs3m4', '2025-10-02 15:51:47', '2025-10-02 15:56:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(24, 1, 'q3o82j458mi80ch2a090egpc01', '2025-10-02 15:56:50', '2025-10-02 16:03:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(25, 1, 'ieqr37cb2pv6o35uouhv5bv546', '2025-10-02 16:08:16', '2025-10-02 16:31:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(26, 1, 'nk50rouakb10g4ds360n3gm79f', '2025-10-02 17:05:14', '2025-10-02 17:51:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0),
(27, 7, 'ku855b718nqj64cj2phjphcgs7', '2025-10-02 17:52:57', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 1),
(28, 1, '62i7q5m76dn7f335817tp7oceg', '2025-10-02 18:38:55', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `burials`
--
ALTER TABLE `burials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `burial_id` (`burial_id`),
  ADD UNIQUE KEY `uniq_transaction_id` (`transaction_id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`),
  ADD KEY `idx_interment_email` (`interment_email`);

--
-- Indexes for table `map_blocks`
--
ALTER TABLE `map_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `block_key` (`block_key`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_notif` (`kind`,`burial_id`,`due_date`),
  ADD UNIQUE KEY `uniq_kind_burial_due` (`kind`,`burial_id`,`due_date`);

--
-- Indexes for table `notification_email_status`
--
ALTER TABLE `notification_email_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_notif_recipient` (`notification_id`,`recipient_type`,`recipient_email`),
  ADD KEY `idx_status_pending` (`sent`,`last_attempt_at`);

--
-- Indexes for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `idx_user_isread` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token_2` (`token`);

--
-- Indexes for table `plots`
--
ALTER TABLE `plots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `map_block_id` (`map_block_id`);

--
-- Indexes for table `plot_layouts`
--
ALTER TABLE `plot_layouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `map_block_id_unique` (`map_block_id`);

--
-- Indexes for table `staff_details`
--
ALTER TABLE `staff_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `burials`
--
ALTER TABLE `burials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `map_blocks`
--
ALTER TABLE `map_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1556;

--
-- AUTO_INCREMENT for table `notification_email_status`
--
ALTER TABLE `notification_email_status`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3654;

--
-- AUTO_INCREMENT for table `notification_user`
--
ALTER TABLE `notification_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `plots`
--
ALTER TABLE `plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=702;

--
-- AUTO_INCREMENT for table `plot_layouts`
--
ALTER TABLE `plot_layouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `staff_details`
--
ALTER TABLE `staff_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `burials`
--
ALTER TABLE `burials`
  ADD CONSTRAINT `burials_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `burials_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `burials_ibfk_3` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_email_status`
--
ALTER TABLE `notification_email_status`
  ADD CONSTRAINT `fk_nes_notif` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD CONSTRAINT `notification_user_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plots`
--
ALTER TABLE `plots`
  ADD CONSTRAINT `plots_ibfk_1` FOREIGN KEY (`map_block_id`) REFERENCES `map_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plot_layouts`
--
ALTER TABLE `plot_layouts`
  ADD CONSTRAINT `fk_map_block` FOREIGN KEY (`map_block_id`) REFERENCES `map_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_details`
--
ALTER TABLE `staff_details`
  ADD CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
