-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2025 at 06:09 AM
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
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(80) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(80) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `status` enum('success','failure') NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `burials`
--

CREATE TABLE `burials` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `burial_id` varchar(50) NOT NULL,
  `transaction_id` varchar(32) DEFAULT NULL,
  `parent_burial_id` varchar(50) DEFAULT NULL,
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
(1, 'ltls-001', 'Blk-A', '166,243,216,652', 0, 0),
(2, 'ltrs-002', 'Blk-B', '256,247,216,645', 0, 0),
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
(701, 12, 'block-E008', 'vacant'),
(768, 2, 'Blk-B001', 'vacant'),
(769, 2, 'Blk-B002', 'vacant'),
(770, 2, 'Blk-B003', 'vacant'),
(771, 2, 'Blk-B004', 'vacant'),
(772, 2, 'Blk-B005', 'vacant'),
(773, 2, 'Blk-B006', 'vacant'),
(774, 2, 'Blk-B007', 'vacant'),
(775, 2, 'Blk-B008', 'vacant'),
(776, 2, 'Blk-B009', 'vacant'),
(777, 2, 'Blk-B010', 'vacant'),
(778, 2, 'Blk-B011', 'vacant'),
(779, 2, 'Blk-B012', 'vacant'),
(780, 2, 'Blk-B013', 'vacant'),
(781, 2, 'Blk-B014', 'vacant'),
(782, 2, 'Blk-B015', 'vacant'),
(783, 2, 'Blk-B016', 'vacant'),
(784, 2, 'Blk-B017', 'vacant'),
(785, 2, 'Blk-B018', 'vacant'),
(786, 2, 'Blk-B019', 'vacant'),
(787, 2, 'Blk-B020', 'vacant'),
(788, 2, 'Blk-B021', 'vacant'),
(789, 2, 'Blk-B022', 'vacant'),
(790, 2, 'Blk-B023', 'vacant'),
(791, 2, 'Blk-B024', 'vacant'),
(1124, 1, 'Blk-A001', 'vacant'),
(1125, 1, 'Blk-A002', 'vacant'),
(1126, 1, 'Blk-A003', 'vacant'),
(1127, 1, 'Blk-A004', 'vacant'),
(1128, 1, 'Blk-A005', 'vacant'),
(1129, 1, 'Blk-A006', 'vacant'),
(1130, 1, 'Blk-A007', 'vacant'),
(1131, 1, 'Blk-A008', 'vacant'),
(1132, 1, 'Blk-A009', 'vacant'),
(1133, 1, 'Blk-A010', 'vacant'),
(1134, 1, 'Blk-A011', 'vacant'),
(1135, 1, 'Blk-A012', 'vacant'),
(1136, 1, 'Blk-A013', 'vacant'),
(1137, 1, 'Blk-A014', 'vacant'),
(1138, 1, 'Blk-A015', 'vacant'),
(1139, 1, 'Blk-A016', 'vacant'),
(1140, 1, 'Blk-A017', 'vacant'),
(1141, 1, 'Blk-A018', 'vacant'),
(1142, 1, 'Blk-A019', 'vacant'),
(1143, 1, 'Blk-A020', 'vacant'),
(1144, 1, 'Blk-A021', 'vacant'),
(1145, 1, 'Blk-A022', 'vacant'),
(1146, 1, 'Blk-A023', 'vacant'),
(1147, 1, 'Blk-A024', 'vacant'),
(1148, 1, 'Blk-A025', 'vacant'),
(1149, 1, 'Blk-A026', 'vacant'),
(1150, 1, 'Blk-A027', 'vacant'),
(1151, 1, 'Blk-A028', 'vacant'),
(1152, 1, 'Blk-A029', 'vacant'),
(1153, 1, 'Blk-A030', 'vacant'),
(1154, 1, 'Blk-A031', 'vacant'),
(1155, 1, 'Blk-A032', 'vacant'),
(1156, 1, 'Blk-A033', 'vacant'),
(1157, 1, 'Blk-A034', 'vacant'),
(1158, 1, 'Blk-A035', 'vacant'),
(1159, 1, 'Blk-A036', 'vacant'),
(1160, 1, 'Blk-A037', 'vacant'),
(1161, 1, 'Blk-A038', 'vacant'),
(1162, 1, 'Blk-A039', 'vacant'),
(1163, 1, 'Blk-A040', 'vacant'),
(1164, 1, 'Blk-A041', 'vacant'),
(1165, 1, 'Blk-A042', 'vacant'),
(1166, 1, 'Blk-A043', 'vacant'),
(1167, 1, 'Blk-A044', 'vacant'),
(1168, 1, 'Blk-A045', 'vacant'),
(1190, 1, 'Blk-A046', 'vacant'),
(1191, 1, 'Blk-A047', 'vacant'),
(1192, 1, 'Blk-A048', 'vacant'),
(1193, 1, 'Blk-A049', 'vacant'),
(1194, 1, 'Blk-A050', 'vacant'),
(1195, 1, 'Blk-A051', 'vacant'),
(1196, 1, 'Blk-A052', 'vacant'),
(1197, 1, 'Blk-A053', 'vacant'),
(1198, 1, 'Blk-A054', 'vacant'),
(1199, 1, 'Blk-A055', 'vacant'),
(1200, 1, 'Blk-A056', 'vacant'),
(1201, 1, 'Blk-A057', 'vacant'),
(1202, 1, 'Blk-A058', 'vacant'),
(1203, 1, 'Blk-A059', 'vacant'),
(1204, 1, 'Blk-A060', 'vacant'),
(1205, 1, 'Blk-A061', 'vacant'),
(1206, 1, 'Blk-A062', 'vacant'),
(1207, 1, 'Blk-A063', 'vacant'),
(1208, 1, 'Blk-A064', 'vacant'),
(1209, 1, 'Blk-A065', 'vacant'),
(1210, 1, 'Blk-A066', 'vacant');

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
-- Table structure for table `remember_me_tokens`
--

CREATE TABLE `remember_me_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` char(24) NOT NULL,
  `validator_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `renewals`
--

CREATE TABLE `renewals` (
  `id` int(11) NOT NULL,
  `burial_id` varchar(50) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `previous_expiry_date` datetime NOT NULL,
  `new_expiry_date` datetime NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payer_name` varchar(255) NOT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `receipt_email_status` varchar(255) DEFAULT NULL,
  `processed_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 'S-001', 'Fullstack Developer');

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
(1, 'admin', 'cano.orionseal.bsit@gmail.com', '$2y$10$Si22RpmgHwLc2OyUSYmv1O0JfgTjka9qYyclYiaow4PdtMFtgf30y', 'admin', 'Genesys', 'Xx', NULL, '', '', NULL, 1, 1, NULL, '2025-09-19 13:20:45', '2025-10-09 12:08:45');

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
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`);

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
  ADD KEY `idx_interment_email` (`interment_email`),
  ADD KEY `idx_parent_burial_id` (`parent_burial_id`);

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
-- Indexes for table `remember_me_tokens`
--
ALTER TABLE `remember_me_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `fk_rmt_user` (`user_id`);

--
-- Indexes for table `renewals`
--
ALTER TABLE `renewals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `burial_id` (`burial_id`),
  ADD KEY `processed_by_user_id` (`processed_by_user_id`);

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
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `burials`
--
ALTER TABLE `burials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `map_blocks`
--
ALTER TABLE `map_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2561;

--
-- AUTO_INCREMENT for table `notification_email_status`
--
ALTER TABLE `notification_email_status`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6671;

--
-- AUTO_INCREMENT for table `notification_user`
--
ALTER TABLE `notification_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `plots`
--
ALTER TABLE `plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1233;

--
-- AUTO_INCREMENT for table `plot_layouts`
--
ALTER TABLE `plot_layouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `remember_me_tokens`
--
ALTER TABLE `remember_me_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `renewals`
--
ALTER TABLE `renewals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `staff_details`
--
ALTER TABLE `staff_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

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
-- Constraints for table `remember_me_tokens`
--
ALTER TABLE `remember_me_tokens`
  ADD CONSTRAINT `fk_rmt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
