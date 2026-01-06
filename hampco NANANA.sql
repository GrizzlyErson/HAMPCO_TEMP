-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 11:37 PM
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
-- Database: `hampco`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `cart_user_id` int(11) NOT NULL,
  `cart_prod_id` int(11) NOT NULL,
  `cart_Qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `cart_user_id`, `cart_prod_id`, `cart_Qty`) VALUES
(4, 6, 8, 12),
(5, 6, 7, 5);

-- --------------------------------------------------------

--
-- Table structure for table `customer_messages`
--

CREATE TABLE `customer_messages` (
  `message_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `sender_type` varchar(20) NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_messages`
--

INSERT INTO `customer_messages` (`message_id`, `customer_id`, `subject`, `message`, `sender_type`, `created_at`, `is_read`) VALUES
(1, 7, 'DAMIT KO MADUMi', 'baho amoy putok', 'customer', '2025-11-27 15:29:16', 1),
(2, 7, NULL, 'OKAY', 'admin', '2025-11-27 15:32:10', 0),
(3, 7, 'hello', 'hello', 'customer', '2025-11-27 15:32:33', 0);

-- --------------------------------------------------------

--
-- Table structure for table `finished_products`
--

CREATE TABLE `finished_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `length_m` decimal(10,3) NOT NULL,
  `width_m` decimal(10,3) NOT NULL,
  `quantity` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unit_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_products`
--

INSERT INTO `finished_products` (`id`, `product_name`, `length_m`, `width_m`, `quantity`, `updated_at`, `unit_cost`) VALUES
(1, 'Knotted Liniwan', 0.000, 0.000, 1, '2025-07-18 08:40:19', 0.00),
(2, 'Piña Seda', 1.000, 30.000, 7, '2026-01-06 21:20:20', 12.00),
(3, 'Piña Seda', 1.000, 30.000, 1, '2025-07-18 09:18:59', 0.00),
(4, 'Pure Piña Cloth', 1.000, 30.000, 2, '2025-07-18 10:10:07', 0.00);

-- --------------------------------------------------------

--
-- Stand-in structure for view `member_balance_view`
-- (See below for the actual view)
--
CREATE TABLE `member_balance_view` (
`id` int(11)
,`member_id` int(11)
,`product_name` varchar(255)
,`weight_g` decimal(10,3)
,`measurements` varchar(29)
,`quantity` int(11)
,`unit_rate` decimal(10,2)
,`total_amount` decimal(10,2)
,`payment_status` enum('Pending','Paid','Adjusted')
,`date_paid` datetime
,`date_created` timestamp
,`member_role` varchar(20)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `member_earnings_summary`
-- (See below for the actual view)
--
CREATE TABLE `member_earnings_summary` (
`member_id` int(11)
,`total_tasks` bigint(21)
,`pending_payments` decimal(32,2)
,`completed_payments` decimal(32,2)
,`total_earnings` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `member_self_tasks`
--

CREATE TABLE `member_self_tasks` (
  `id` int(11) NOT NULL,
  `production_id` varchar(10) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_name` enum('Knotted Liniwan','Knotted Bastos','Warped Silk','Piña Seda','Pure Piña Cloth') NOT NULL,
  `weight_g` decimal(10,2) NOT NULL,
  `status` enum('pending','in_progress','submitted','rejected','completed') NOT NULL DEFAULT 'pending',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `raw_materials` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_submitted` timestamp NULL DEFAULT NULL,
  `length_m` decimal(10,2) DEFAULT NULL,
  `width_in` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_self_tasks`
--

INSERT INTO `member_self_tasks` (`id`, `production_id`, `member_id`, `product_name`, `weight_g`, `status`, `approval_status`, `raw_materials`, `date_created`, `date_submitted`, `length_m`, `width_in`, `quantity`) VALUES
(32, 'PL0003', 1, 'Knotted Liniwan', 12.00, '', 'pending', NULL, '2025-07-29 11:21:10', '2025-07-29 11:21:29', NULL, NULL, NULL),
(33, 'PL0004', 1, 'Knotted Bastos', 12.00, '', 'pending', NULL, '2025-07-29 11:25:07', '2025-07-29 11:25:21', NULL, NULL, NULL),
(35, 'PL0005', 1, 'Knotted Liniwan', 12.00, '', 'pending', NULL, '2025-07-29 11:38:16', '2025-07-29 11:38:46', NULL, NULL, NULL),
(36, 'PL0006', 2, 'Warped Silk', 12.00, '', 'pending', NULL, '2025-07-29 11:40:12', '2025-07-29 11:40:26', NULL, NULL, NULL),
(37, 'PL0007', 1, 'Knotted Liniwan', 12.00, '', 'pending', NULL, '2025-07-30 12:30:06', '2025-07-30 12:32:55', NULL, NULL, NULL),
(39, 'PL0008', 1, 'Knotted Bastos', 1.00, '', 'pending', NULL, '2025-07-30 12:34:14', '2025-07-30 12:34:27', NULL, NULL, NULL),
(40, 'PL0009', 1, 'Knotted Bastos', 13.00, '', 'pending', NULL, '2025-07-30 12:40:48', '2025-07-30 12:40:58', NULL, NULL, NULL),
(41, 'PL0010', 1, 'Knotted Liniwan', 2.00, '', 'pending', NULL, '2025-07-30 12:45:08', '2025-07-30 12:45:18', NULL, NULL, NULL),
(42, 'PL0011', 1, 'Knotted Bastos', 4.00, '', 'pending', NULL, '2025-07-30 12:50:21', '2025-07-30 12:50:31', NULL, NULL, NULL),
(43, 'PL0012', 1, 'Knotted Liniwan', 12.00, '', 'pending', NULL, '2025-07-30 12:54:20', '2025-07-30 12:54:31', NULL, NULL, NULL),
(44, 'PL0013', 1, 'Knotted Bastos', 2.00, '', 'pending', NULL, '2025-07-30 12:56:27', '2025-07-30 12:56:39', NULL, NULL, NULL),
(45, 'PL0014', 1, 'Knotted Bastos', 3.00, '', 'pending', NULL, '2025-07-30 12:57:03', '2025-07-30 12:57:15', NULL, NULL, NULL),
(46, 'PL0015', 1, 'Knotted Liniwan', 5.00, '', 'pending', NULL, '2025-07-30 13:00:50', '2025-07-30 13:01:00', NULL, NULL, NULL),
(47, 'PL0016', 1, 'Knotted Bastos', 1.00, '', 'pending', NULL, '2025-07-31 02:58:15', '2025-07-31 02:58:28', NULL, NULL, NULL),
(48, 'PL0017', 1, 'Knotted Bastos', 4.00, '', 'pending', NULL, '2025-07-31 03:02:27', '2025-07-31 03:02:37', NULL, NULL, NULL),
(49, 'PL0018', 2, 'Warped Silk', 12.00, '', 'pending', NULL, '2025-07-31 03:27:52', '2025-07-31 03:28:12', NULL, NULL, NULL),
(50, 'PL0019', 1, 'Knotted Bastos', 15.00, '', 'pending', NULL, '2025-07-31 12:39:41', '2025-07-31 12:40:02', NULL, NULL, NULL),
(51, 'PL0020', 1, 'Knotted Liniwan', 15.00, '', 'pending', NULL, '2025-07-31 13:37:00', '2025-07-31 13:37:24', NULL, NULL, NULL),
(52, 'PL0021', 1, 'Knotted Bastos', 15.00, '', 'pending', NULL, '2025-07-31 14:30:36', '2025-07-31 14:31:34', NULL, NULL, NULL),
(53, 'PL0022', 2, 'Warped Silk', 20.00, '', 'pending', NULL, '2025-07-31 14:33:29', '2025-07-31 14:33:55', NULL, NULL, NULL),
(54, 'PL0023', 1, 'Knotted Bastos', 10.00, '', 'pending', NULL, '2025-07-31 21:28:48', '2025-07-31 21:29:28', NULL, NULL, NULL),
(70, 'PL0029', 5, 'Knotted Bastos', 123.00, '', 'pending', NULL, '2025-11-27 19:29:32', '2025-11-29 19:25:54', NULL, NULL, NULL),
(71, 'PL0030', 5, 'Knotted Liniwan', 321.00, '', 'pending', NULL, '2025-11-27 19:40:11', '2025-11-27 19:50:40', NULL, NULL, NULL),
(72, 'PL0031', 5, 'Knotted Liniwan', 123.00, '', 'pending', NULL, '2025-11-27 21:36:06', '2025-11-27 21:40:14', NULL, NULL, NULL),
(73, 'PL0032', 5, 'Knotted Liniwan', 12.00, '', 'pending', NULL, '2025-11-27 21:40:31', '2025-11-30 06:42:06', NULL, NULL, NULL),
(75, 'PL0033', 5, 'Knotted Liniwan', 123.00, 'pending', 'pending', NULL, '2025-11-30 19:23:45', NULL, NULL, NULL, NULL),
(76, 'PL0034', 5, 'Knotted Liniwan', 123.00, 'pending', 'pending', NULL, '2026-01-06 03:07:44', NULL, NULL, NULL, NULL),
(77, 'PL0035', 5, 'Knotted Bastos', 123.00, '', 'pending', NULL, '2026-01-06 03:18:23', '2026-01-06 03:26:12', NULL, NULL, NULL),
(78, 'PL0036', 5, 'Knotted Bastos', 1212.00, '', 'pending', NULL, '2026-01-06 03:23:41', '2026-01-06 03:26:08', NULL, NULL, NULL),
(79, 'PL0037', 5, 'Knotted Liniwan', 123423.00, 'pending', 'pending', NULL, '2026-01-06 03:42:37', NULL, NULL, NULL, NULL),
(100, 'PL0040', 14, '', 12.00, 'pending', 'pending', NULL, '2026-01-06 13:48:53', NULL, 1.00, 1.00, 1),
(101, 'PL0041', 14, '', 1.00, 'pending', 'pending', NULL, '2026-01-06 13:51:18', NULL, 1.00, 1.00, 1),
(102, 'PL0042', 5, 'Knotted Liniwan', 1.00, 'pending', 'pending', NULL, '2026-01-06 13:52:15', NULL, NULL, NULL, NULL),
(103, 'PL0043', 5, 'Knotted Liniwan', 1.00, 'pending', 'pending', NULL, '2026-01-06 14:46:55', NULL, NULL, NULL, NULL),
(104, 'PL0044', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 14:55:15', NULL, 1.00, 1.00, 1),
(105, 'PL0045', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 14:55:31', NULL, 1.00, 1.00, 1),
(106, 'PL0046', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 14:56:27', NULL, 1.00, 1.00, 1),
(107, 'PL0047', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 14:56:28', NULL, 1.00, 1.00, 1),
(109, 'PL0049', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 14:58:16', NULL, 1.00, 1.00, 1),
(110, 'PL0050', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 14:59:09', NULL, 1.00, 1.00, 1),
(111, 'PL0051', 5, 'Knotted Liniwan', 1.00, 'pending', 'pending', NULL, '2026-01-06 14:59:30', NULL, NULL, NULL, NULL),
(112, 'PL0052', 5, 'Knotted Liniwan', 1.00, 'pending', 'pending', NULL, '2026-01-06 15:00:31', NULL, 0.00, 0.00, 0),
(113, 'PL0053', 14, '', 0.00, 'pending', 'pending', NULL, '2026-01-06 15:11:38', NULL, 1.00, 1.00, 1),
(114, 'PL0054', 14, '', 0.00, 'rejected', 'pending', NULL, '2026-01-06 15:11:44', NULL, 1.00, 1.00, 1),
(117, 'PL0057', 14, '', 0.00, 'rejected', 'pending', NULL, '2026-01-06 15:19:27', NULL, 1.00, 1.00, 1),
(121, 'PL0061', 5, 'Knotted Liniwan', 1.00, 'pending', 'pending', NULL, '2026-01-06 15:21:51', NULL, NULL, NULL, NULL),
(123, 'PL0063', 14, '', 0.00, 'rejected', 'pending', NULL, '2026-01-06 15:28:28', NULL, 1.00, 1.00, 1),
(124, 'PL0064', 14, 'Piña Seda', 0.00, 'pending', 'pending', NULL, '2026-01-06 15:41:14', NULL, 1.00, 1.00, 1),
(125, 'PL0065', 14, 'Pure Piña Cloth', 0.00, 'rejected', 'pending', NULL, '2026-01-06 16:43:16', NULL, 1.00, 1.00, 1),
(126, 'PL0066', 14, 'Piña Seda', 0.00, 'rejected', 'pending', NULL, '2026-01-06 16:43:33', NULL, 1.00, 1.00, 1),
(127, 'PL0067', 14, 'Piña Seda', 0.00, 'rejected', 'pending', NULL, '2026-01-06 16:51:20', NULL, 1.00, 1.00, 1),
(128, 'PL0068', 5, 'Knotted Liniwan', 1.00, 'rejected', 'pending', NULL, '2026-01-06 16:52:11', NULL, NULL, NULL, NULL),
(129, 'PL0069', 14, 'Piña Seda', 0.00, 'rejected', 'pending', NULL, '2026-01-06 16:53:19', NULL, 1.00, 1.00, 1),
(130, 'PL0070', 14, 'Piña Seda', 0.00, 'pending', 'pending', NULL, '2026-01-06 17:03:33', NULL, 1.00, 1.00, 1),
(131, 'PL0071', 14, 'Piña Seda', 0.00, 'pending', 'pending', NULL, '2026-01-06 17:06:14', NULL, 1.00, 1.00, 1),
(132, 'PL0072', 14, 'Piña Seda', 0.00, 'rejected', 'pending', NULL, '2026-01-06 17:13:57', NULL, 1.00, 1.00, 1),
(133, 'PL0073', 13, 'Warped Silk', 123.00, 'rejected', 'pending', NULL, '2026-01-06 20:54:54', NULL, NULL, NULL, NULL),
(134, 'PL0074', 13, 'Warped Silk', 12.00, 'pending', 'pending', NULL, '2026-01-06 20:55:39', NULL, NULL, NULL, NULL),
(135, 'PL0075', 17, 'Piña Seda', 0.00, '', 'pending', NULL, '2026-01-06 21:51:56', '2026-01-06 21:52:41', 1.00, 1.00, 1),
(136, 'PL0076', 17, 'Piña Seda', 0.00, '', 'pending', NULL, '2026-01-06 21:57:13', '2026-01-06 21:58:10', 1.00, 1.00, 1),
(137, 'PL0077', 18, 'Knotted Liniwan', 1.00, '', 'pending', NULL, '2026-01-06 22:03:48', '2026-01-06 22:04:11', NULL, NULL, NULL),
(138, 'PL0078', 18, 'Knotted Liniwan', 1.00, '', 'pending', NULL, '2026-01-06 22:07:21', '2026-01-06 22:09:00', NULL, NULL, NULL),
(139, 'PL0079', 18, 'Knotted Liniwan', 1.00, '', 'pending', NULL, '2026-01-06 22:25:50', '2026-01-06 22:26:11', NULL, NULL, NULL),
(140, 'PL0080', 18, 'Knotted Liniwan', 1.00, '', 'pending', NULL, '2026-01-06 22:30:46', '2026-01-06 22:31:21', NULL, NULL, NULL),
(141, 'PL0081', 18, 'Knotted Bastos', 123.00, 'completed', 'pending', NULL, '2026-01-06 22:36:01', '2026-01-06 22:36:17', NULL, NULL, NULL);

--
-- Triggers `member_self_tasks`
--
DELIMITER $$
CREATE TRIGGER `after_insert_self_task` AFTER INSERT ON `member_self_tasks` FOR EACH ROW BEGIN
    INSERT INTO task_approval_requests (
        production_id,
        member_id,
        member_name,
        role,
        product_name,
        weight_g,
        date_created
    )
    SELECT 
        NEW.production_id,
        NEW.member_id,
        um.fullname,
        um.role,
        NEW.product_name,
        NEW.weight_g,
        NEW.date_created
    FROM user_member um
    WHERE um.id = NEW.member_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_self_task_completion` AFTER UPDATE ON `member_self_tasks` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        INSERT INTO payment_records (
            member_id,
            production_id,
            weight_g,
            quantity,
            unit_rate,
            total_amount,
            is_self_assigned,
            payment_status,
            date_created
        )
        VALUES (
            NEW.member_id,
            NEW.production_id,
            NEW.weight_g,
            1,
            CASE 
                WHEN NEW.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN NEW.product_name = 'Knotted Bastos' THEN 50.00
                WHEN NEW.product_name = 'Warped Silk' THEN 19.00
                ELSE 0.00
            END,
            NEW.weight_g * 
            CASE 
                WHEN NEW.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN NEW.product_name = 'Knotted Bastos' THEN 50.00
                WHEN NEW.product_name = 'Warped Silk' THEN 19.00
                ELSE 0.00
            END,
            1,
            'Pending',
            NEW.date_submitted
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_self_task_start` AFTER UPDATE ON `member_self_tasks` FOR EACH ROW BEGIN
    DECLARE v_member_name VARCHAR(100);
    DECLARE v_role VARCHAR(50);

    IF NEW.status = 'in_progress' AND OLD.status = 'pending' THEN
        -- Get member details
        SELECT fullname, role 
        INTO v_member_name, v_role
        FROM user_member 
        WHERE id = NEW.member_id;
        
        -- Insert into task_completion_confirmations
        INSERT INTO task_completion_confirmations (
            production_id,
            member_id,
            member_name,
            role,
            product_name,
            weight,
            date_started,
            status
        )
        VALUES (
            NEW.production_id,
            NEW.member_id,
            v_member_name,
            v_role,
            NEW.product_name,
            NEW.weight_g,
            NOW(),
            'in_progress'
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_self_task_submit` AFTER UPDATE ON `member_self_tasks` FOR EACH ROW BEGIN
    IF NEW.status = 'submitted' AND OLD.status = 'in_progress' THEN
        UPDATE task_completion_confirmations
        SET 
            status = 'submitted',
            date_submitted = NOW()
        WHERE production_id = NEW.production_id
        AND member_id = NEW.member_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_member_self_tasks` BEFORE INSERT ON `member_self_tasks` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    SET next_id = (SELECT IFNULL(MAX(CAST(SUBSTRING(production_id, 3) AS UNSIGNED)), 0) + 1 FROM member_self_tasks);
    SET NEW.production_id = CONCAT('PL', LPAD(next_id, 4, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'COD',
  `payment_proof` varchar(255) DEFAULT NULL,
  `order_notes` longtext DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_user_id`, `full_name`, `contact_number`, `delivery_address`, `payment_method`, `payment_proof`, `order_notes`, `total_amount`, `order_status`, `date_created`, `updated_at`) VALUES
(4, 7, 'john b', '09475633165', '123123123', 'COD', NULL, NULL, 123.00, 'Cancelled', '2025-11-24 07:53:06', '2025-11-25 02:41:02'),
(8, 7, 'john b', '09475633165', '123123123', 'GCash', 'checkout_proof_7_1763978020.png', NULL, 1123122.00, 'Delivered', '2025-11-24 09:53:40', '2025-11-26 05:20:22'),
(13, 7, 'Sample', '09475316387', '123', 'COD', NULL, NULL, 246.00, 'Accepted', '2025-11-25 05:26:27', '2025-11-26 14:38:18'),
(19, 7, 'Erson Hayes', '09475633165', '123123', 'COD', NULL, NULL, 599.00, 'Pending', '2025-11-29 19:43:54', '2025-11-29 19:43:54'),
(21, 7, 'Erson Hayes', '09475633165', '123123', 'COD', NULL, NULL, 599.00, 'Accepted', '2025-11-29 19:47:18', '2026-01-06 14:57:44'),
(25, 7, 'Erson Hayes', '09475633165', '123', 'COD', NULL, NULL, 1721.00, 'Accepted', '2025-11-29 19:53:05', '2026-01-06 07:42:18'),
(26, 7, 'Erson Hayes', '09475633165', '123', 'COD', NULL, NULL, 3594.00, 'Delivered', '2025-11-29 19:55:59', '2025-11-29 19:56:11'),
(27, 7, 'Erson Hayes', '09475633165', '123123', 'COD', NULL, NULL, 1797.00, 'Accepted', '2025-11-29 19:59:33', '2026-01-06 07:42:15'),
(45, 7, '123', '123', '123123', 'COD', NULL, NULL, 123.00, 'Accepted', '2026-01-05 13:15:23', '2026-01-06 07:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `prod_id`, `product_name`, `quantity`, `unit_price`, `subtotal`, `date_created`) VALUES
(4, 4, 9, NULL, 1, 123.00, 123.00, '2025-11-24 07:53:06'),
(9, 8, 12, NULL, 1, 123123.00, 123123.00, '2025-11-24 09:53:40'),
(10, 8, 14, NULL, 1, 999999.00, 999999.00, '2025-11-24 09:53:40'),
(15, 13, 16, NULL, 2, 123.00, 246.00, '2025-11-25 05:26:27'),
(21, 19, 17, NULL, 1, 599.00, 599.00, '2025-11-29 19:43:54'),
(23, 21, 17, NULL, 1, 599.00, 599.00, '2025-11-29 19:47:18'),
(27, 25, 18, NULL, 1, 999.00, 999.00, '2025-11-29 19:53:05'),
(28, 25, 19, NULL, 1, 123.00, 123.00, '2025-11-29 19:53:05'),
(29, 25, 17, NULL, 1, 599.00, 599.00, '2025-11-29 19:53:05'),
(30, 26, 17, NULL, 6, 599.00, 3594.00, '2025-11-29 19:55:59'),
(31, 27, 17, NULL, 3, 599.00, 1797.00, '2025-11-29 19:59:33'),
(49, 45, 19, NULL, 1, 123.00, 123.00, '2026-01-05 13:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

CREATE TABLE `payment_records` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `production_id` varchar(20) NOT NULL,
  `length_m` decimal(10,3) DEFAULT NULL,
  `width_m` decimal(10,3) DEFAULT NULL,
  `weight_g` decimal(10,3) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Adjusted') DEFAULT 'Pending',
  `date_paid` datetime DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_self_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_records`
--

INSERT INTO `payment_records` (`id`, `member_id`, `product_id`, `production_id`, `length_m`, `width_m`, `weight_g`, `quantity`, `unit_rate`, `total_amount`, `payment_status`, `date_paid`, `date_created`, `is_self_assigned`) VALUES
(27, 1, NULL, '81', NULL, NULL, 1.000, 1, 50.00, 50.00, 'Pending', NULL, '2025-07-31 02:55:35', 0),
(28, 1, NULL, '81', NULL, NULL, 1.000, 1, 50.00, 50.00, 'Paid', '2025-11-26 16:22:42', '2025-07-31 02:55:35', 0),
(29, 1, NULL, '82', NULL, NULL, 2.000, 1, 50.00, 100.00, 'Pending', NULL, '2025-07-31 02:57:48', 0),
(30, 1, NULL, 'PL0016', NULL, NULL, 1.000, 1, 50.00, 50.00, 'Pending', NULL, '2025-07-31 02:58:28', 1),
(31, 1, NULL, 'PL0017', NULL, NULL, 4.000, 1, 50.00, 200.00, 'Pending', NULL, '2025-07-31 03:02:37', 1),
(32, 2, NULL, '83', NULL, NULL, 12.000, 1, 19.00, 228.00, 'Pending', NULL, '2025-07-31 03:27:15', 0),
(33, 2, NULL, 'PL0018', NULL, NULL, 12.000, 1, 19.00, 228.00, 'Paid', '2025-07-31 11:29:20', '2025-07-31 03:28:12', 1),
(34, 4, NULL, '84', NULL, NULL, 0.000, 1, 550.00, 550.00, 'Paid', '2025-07-31 16:09:41', '2025-07-31 08:09:23', 0),
(35, 4, NULL, '85', 1.000, 30.000, 0.000, 1, 550.00, 550.00, 'Paid', '2025-07-31 20:40:38', '2025-07-31 12:38:32', 0),
(36, 1, NULL, '86', 0.000, 0.000, 15.000, 1, 50.00, 750.00, 'Pending', NULL, '2025-07-31 13:29:03', 0),
(37, 1, NULL, 'PL0020', NULL, NULL, 15.000, 1, 50.00, 750.00, 'Paid', '2025-07-31 21:45:14', '2025-07-31 13:37:24', 1),
(38, 1, NULL, '87', 0.000, 0.000, 15.000, 1, 50.00, 750.00, 'Pending', NULL, '2025-07-31 14:28:54', 0),
(39, 1, NULL, 'PL0021', NULL, NULL, 15.000, 1, 50.00, 750.00, 'Paid', '2025-07-31 22:32:25', '2025-07-31 14:31:34', 1),
(40, 2, NULL, 'PL0022', NULL, NULL, 20.000, 1, 19.00, 380.00, 'Pending', NULL, '2025-07-31 14:33:55', 1),
(41, 2, NULL, '91', 0.000, 0.000, 16.000, 1, 19.00, 304.00, 'Pending', NULL, '2025-07-31 14:57:21', 0),
(42, 2, NULL, '91', 0.000, 0.000, 16.000, 1, 19.00, 304.00, 'Pending', NULL, '2025-07-31 14:57:21', 0),
(43, 2, NULL, '90', 0.000, 0.000, 16.000, 1, 19.00, 304.00, 'Pending', NULL, '2025-07-31 21:27:51', 0),
(44, 1, NULL, 'PL0023', NULL, NULL, 10.000, 1, 50.00, 500.00, 'Pending', NULL, '2025-07-31 21:29:28', 1),
(45, 5, NULL, '93', 0.000, 0.000, 12.000, 1, 50.00, 600.00, 'Paid', '2025-08-01 12:01:23', '2025-08-01 03:51:09', 0),
(46, 2, NULL, '88', 0.000, 0.000, 11.000, 1, 19.00, 209.00, 'Pending', NULL, '2025-11-24 12:44:56', 0),
(47, 5, NULL, '100', 0.000, 0.000, 12.000, 1, 50.00, 600.00, 'Paid', '2025-11-26 14:32:00', '2025-11-25 03:59:45', 0),
(48, 5, NULL, 'PL0025', NULL, NULL, 1234.000, 1, 50.00, 61700.00, 'Paid', '2025-11-30 04:00:12', '2025-11-24 15:08:21', 1),
(49, 5, NULL, 'PL0032', NULL, NULL, 12.000, 1, 50.00, 600.00, 'Pending', NULL, '2025-11-30 06:42:06', 1),
(50, 5, NULL, 'PL0029', NULL, NULL, 123.000, 1, 50.00, 6150.00, 'Pending', NULL, '2025-11-29 19:25:54', 1),
(51, 5, NULL, '133', 0.000, 0.000, 123.000, 1, 50.00, 6150.00, 'Pending', NULL, '2025-11-30 20:22:15', 0),
(52, 5, NULL, '133', 0.000, 0.000, 123.000, 1, 50.00, 6150.00, 'Pending', NULL, '2025-11-30 20:22:15', 0),
(53, 5, NULL, 'PL0036', NULL, NULL, 1212.000, 1, 50.00, 60600.00, 'Paid', '2026-01-06 13:57:31', '2026-01-06 03:26:08', 1),
(54, 5, NULL, 'PL0035', NULL, NULL, 123.000, 1, 50.00, 6150.00, 'Pending', NULL, '2026-01-06 03:26:12', 1),
(55, 14, NULL, '198', 12.000, 12.000, 0.000, 1, 550.00, 6600.00, 'Paid', '2026-01-06 19:43:16', '2026-01-06 07:24:50', 0),
(56, 14, NULL, '197', 1.000, 1.000, 0.000, 1, 550.00, 550.00, 'Paid', '2026-01-06 19:42:34', '2026-01-06 07:24:53', 0),
(57, 18, NULL, '209', 0.000, 0.000, 1.000, 1, 50.00, 50.00, 'Pending', NULL, '2026-01-06 22:18:50', 0),
(58, 18, NULL, 'PL0081', NULL, NULL, 123.000, 1, 50.00, 6150.00, 'Pending', NULL, '2026-01-06 22:36:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment_records_backup`
--

CREATE TABLE `payment_records_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `member_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `production_id` int(11) NOT NULL,
  `length_m` decimal(10,3) DEFAULT NULL,
  `width_m` decimal(10,3) DEFAULT NULL,
  `weight_g` decimal(10,3) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Adjusted') DEFAULT 'Pending',
  `date_paid` datetime DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_self_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_records_backup`
--

INSERT INTO `payment_records_backup` (`id`, `member_id`, `product_id`, `production_id`, `length_m`, `width_m`, `weight_g`, `quantity`, `unit_rate`, `total_amount`, `payment_status`, `date_paid`, `date_created`, `is_self_assigned`) VALUES
(1, 1, NULL, 76, 0.000, 0.000, 12.000, 1, 50.00, 600.00, 'Pending', NULL, '2025-07-30 12:23:07', 0),
(2, 1, NULL, 76, 0.000, 0.000, 12.000, 1, 50.00, 600.00, 'Pending', NULL, '2025-07-30 12:23:07', 0),
(3, 1, NULL, 77, 0.000, 0.000, 12.000, 1, 45.00, 540.00, 'Pending', NULL, '2025-07-30 12:24:17', 0),
(4, 1, NULL, 77, 0.000, 0.000, 12.000, 1, 45.00, 540.00, 'Pending', NULL, '2025-07-30 12:24:17', 0),
(5, 1, NULL, 78, 0.000, 0.000, 12.000, 1, 50.00, 600.00, 'Pending', NULL, '2025-07-30 12:26:47', 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `payment_records_view`
-- (See below for the actual view)
--
CREATE TABLE `payment_records_view` (
`id` int(11)
,`production_id` varchar(20)
,`member_name` varchar(60)
,`product_name` varchar(255)
,`measurements` varchar(29)
,`weight_g` decimal(11,3)
,`quantity` int(11)
,`unit_rate` decimal(10,2)
,`total_amount` decimal(10,2)
,`payment_status` enum('Pending','Paid','Adjusted')
,`date_paid` datetime
,`is_self_assigned` tinyint(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `processed_materials`
--

CREATE TABLE `processed_materials` (
  `id` int(11) NOT NULL,
  `processed_materials_name` varchar(60) NOT NULL,
  `weight` decimal(10,3) NOT NULL DEFAULT 0.000,
  `status` varchar(60) NOT NULL DEFAULT 'Available',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unit_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `processed_materials`
--

INSERT INTO `processed_materials` (`id`, `processed_materials_name`, `weight`, `status`, `updated_at`, `unit_cost`) VALUES
(1, 'Knotted Bastos', 2451.000, 'Available', '2026-01-06 22:36:34', 0.00),
(2, 'Knotted Liniwan', 2924.000, 'Available', '2026-01-06 22:31:37', 0.00),
(3, 'Warped Silk', 963.000, 'Available', '2026-01-06 17:20:38', 0.00),
(4, 'Piña Seda', 0.000, 'Available', '2026-01-06 22:00:02', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `prod_id` int(11) NOT NULL,
  `prod_category_id` int(11) NOT NULL,
  `prod_name` varchar(255) NOT NULL,
  `prod_image` varchar(255) NOT NULL,
  `prod_stocks` int(11) NOT NULL,
  `prod_price` decimal(10,2) NOT NULL,
  `prod_description` text DEFAULT NULL,
  `prod_status` int(11) NOT NULL DEFAULT 1 COMMENT '0=archived,1=exist'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`prod_id`, `prod_category_id`, `prod_name`, `prod_image`, `prod_stocks`, `prod_price`, `prod_description`, `prod_status`) VALUES
(7, 1, 'Plain and design', '0', 17, 4680.00, 'Piña Seda Dyed 36\"W', 0),
(8, 2, 'Barong tagalog', '0', 135, 999.00, 'size 3', 0),
(9, 3, 'Banana Outfit', '0', 122, 123.00, 'Para sa Bading na sinaing sa suka', 0),
(10, 1, 'Banana Outfit', 'prod_69230642e3a5a.jpg', 0, 999999.00, 'Para sa Bading na sinaing sa suka', 0),
(11, 2, 'Banana Outfit2', 'prod_69241626474fe.png', 0, 123.00, '123', 0),
(12, 3, 'Banana Outfit123', 'prod_6924171b330c1.jpg', 21, 123123.00, '123', 0),
(13, 1, 'TEST', '0', 2, 123.00, '123', 0),
(14, 3, 'TEST', 'prod_69241972ebdfa.png', 7, 999999.00, 'TESTING', 0),
(15, 2, 'BARONG GEISLER', 'prod_692455a2a2bea.jpg', 0, 123.00, 'SABAW', 0),
(16, 3, 'TEST', 'prod_692458c655dfd.jpg', 22, 123.00, 'TESTING', 0),
(17, 3, 'Bastos', 'prod_69257970d9854.png', 12, 599.00, 'Bastos', 1),
(18, 1, 'Banana Outfit', 'prod_692b4ed1382f0.jpg', 11, 999.00, '123', 0),
(19, 2, 'TEST', 'prod_692b4ee95a1eb.jpg', 121, 123.00, '123', 1),
(20, 1, 'Piña loose (liniwan)', 'prod_695cb5e9d6e12.png', 0, 12.00, 'TESTING', 1),
(21, 1, 'Piña loose (liniwan)', 'prod_695d74d0e331a.png', 0, 123.00, '123', 0),
(22, 1, 'Knotted liniwan', 'prod_695d7b94c8770.png', 0, 123.00, '123', 1);

-- --------------------------------------------------------

--
-- Table structure for table `production_line`
--

CREATE TABLE `production_line` (
  `prod_line_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `length_m` decimal(10,3) NOT NULL,
  `width_m` decimal(10,3) NOT NULL,
  `weight_g` decimal(10,3) NOT NULL DEFAULT 0.000,
  `quantity` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_line`
--

INSERT INTO `production_line` (`prod_line_id`, `product_name`, `length_m`, `width_m`, `weight_g`, `quantity`, `date_created`, `status`) VALUES
(8, 'Knotted Bastos', 0.000, 0.000, 1000.000, 1, '2025-07-17 11:04:30', ''),
(9, 'Knotted Liniwan', 0.000, 0.000, 1000.000, 1, '2025-07-17 11:10:11', ''),
(11, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-17 12:19:59', ''),
(15, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 08:29:49', ''),
(16, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 08:40:03', ''),
(18, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 08:52:02', ''),
(19, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 08:56:48', ''),
(20, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 08:58:03', ''),
(22, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-18 09:01:58', ''),
(24, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 09:08:17', ''),
(25, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 09:09:09', ''),
(28, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 09:17:15', 'completed'),
(29, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-18 09:18:25', 'completed'),
(30, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-18 09:21:16', 'completed'),
(33, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-18 09:30:17', 'completed'),
(36, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-18 09:55:26', 'completed'),
(37, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-07-18 10:08:52', 'completed'),
(38, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-07-18 10:09:30', 'completed'),
(40, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-18 10:12:56', 'completed'),
(41, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-18 10:13:36', 'completed'),
(42, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 05:30:27', 'completed'),
(44, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 05:37:53', 'completed'),
(45, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 05:39:50', 'completed'),
(47, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-24 05:40:44', 'completed'),
(48, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 08:49:24', 'completed'),
(49, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 08:59:18', 'completed'),
(50, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 09:10:35', 'completed'),
(53, 'Knotted Liniwan', 0.000, 0.000, 10.000, 1, '2025-07-24 11:34:30', 'completed'),
(54, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-24 11:41:15', 'completed'),
(55, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-24 12:10:19', 'completed'),
(57, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-24 12:23:35', 'completed'),
(59, 'Warped Silk', 0.000, 0.000, 12.000, 1, '2025-07-24 12:26:05', 'completed'),
(60, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-24 12:26:28', 'completed'),
(61, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-27 13:17:50', 'completed'),
(62, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-27 13:58:44', 'completed'),
(65, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-27 14:05:29', 'completed'),
(66, 'Warped Silk', 0.000, 0.000, 12.000, 1, '2025-07-27 14:06:12', 'completed'),
(67, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-27 14:06:50', 'completed'),
(68, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-28 10:14:08', 'completed'),
(71, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-29 11:29:40', ''),
(72, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-29 11:30:43', 'in_progress'),
(73, 'Warped Silk', 0.000, 0.000, 12.000, 1, '2025-07-29 11:31:20', ''),
(74, 'Warped Silk', 0.000, 0.000, 12.000, 1, '2025-07-29 11:39:13', ''),
(76, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-30 12:22:48', 'in_progress'),
(77, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-30 12:23:56', 'in_progress'),
(78, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-30 12:26:27', ''),
(79, 'Knotted Liniwan', 0.000, 0.000, 1.000, 1, '2025-07-30 12:55:42', ''),
(80, 'Knotted Liniwan', 0.000, 0.000, 2.000, 1, '2025-07-30 13:01:43', ''),
(81, 'Knotted Bastos', 0.000, 0.000, 1.000, 1, '2025-07-31 02:55:16', 'in_progress'),
(82, 'Knotted Bastos', 0.000, 0.000, 2.000, 1, '2025-07-31 02:57:31', ''),
(83, 'Warped Silk', 0.000, 0.000, 12.000, 1, '2025-07-31 03:26:58', ''),
(84, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-31 08:08:19', ''),
(85, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-31 12:37:53', ''),
(86, 'Knotted Bastos', 0.000, 0.000, 15.000, 1, '2025-07-31 13:28:34', ''),
(87, 'Knotted Liniwan', 0.000, 0.000, 15.000, 1, '2025-07-31 14:27:41', ''),
(88, 'Warped Silk', 0.000, 0.000, 11.000, 1, '2025-07-31 14:49:03', ''),
(89, 'Warped Silk', 0.000, 0.000, 14.000, 1, '2025-07-31 14:50:37', 'in_progress'),
(90, 'Warped Silk', 0.000, 0.000, 16.000, 1, '2025-07-31 14:52:52', ''),
(91, 'Warped Silk', 0.000, 0.000, 16.000, 1, '2025-07-31 14:56:37', 'in_progress'),
(93, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-08-01 03:49:04', ''),
(94, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-08-01 04:10:45', 'in_progress'),
(95, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-08-01 04:11:22', 'in_progress'),
(99, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 123, '2025-11-24 13:01:06', 'in_progress'),
(100, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-11-24 13:38:13', ''),
(101, 'Pure Piña Cloth', 30.000, 30.000, 0.000, 3, '2025-11-25 04:15:15', 'pending'),
(102, 'Warped Silk', 0.000, 0.000, 123.000, 1, '2025-11-26 05:21:21', 'in_progress'),
(103, 'Piña Seda', 123.000, 123.000, 0.000, 123, '2025-11-26 05:21:29', 'in_progress'),
(106, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-27 21:39:33', 'pending'),
(107, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-27 21:39:33', 'pending'),
(108, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(109, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(110, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(111, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(112, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(113, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(114, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(115, 'Piña Seda', 30.000, 30.000, 0.000, 1, '2025-11-29 21:16:31', 'pending'),
(116, 'Piña Seda', 12.000, 12.000, 0.000, 1, '2025-11-29 21:17:36', 'in_progress'),
(117, 'Piña Seda', 12.000, 12.000, 0.000, 1, '2025-11-29 21:17:36', 'pending'),
(118, 'Piña Seda', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:23:01', 'in_progress'),
(119, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:07', 'pending'),
(120, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:07', 'pending'),
(121, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:07', 'pending'),
(122, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:07', 'pending'),
(123, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:07', 'pending'),
(124, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:07', 'pending'),
(125, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'in_progress'),
(126, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'in_progress'),
(127, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'pending'),
(128, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'pending'),
(130, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'pending'),
(131, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'pending'),
(132, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2025-11-29 21:55:08', 'pending'),
(133, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-11-29 22:21:30', 'in_progress'),
(134, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-11-29 22:21:30', 'pending'),
(135, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 17:52:06', 'pending'),
(136, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 17:52:06', 'pending'),
(137, 'Knotted Liniwan', 0.000, 0.000, 12321.000, 1, '2025-11-30 19:38:43', 'in_progress'),
(138, 'Knotted Liniwan', 0.000, 0.000, 12321.000, 1, '2025-11-30 19:38:43', 'in_progress'),
(139, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 22:45:47', 'pending'),
(140, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 22:45:47', 'pending'),
(141, 'Piña Seda', 23.000, 23.000, 0.000, 12, '2025-11-30 22:52:05', 'pending'),
(142, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-11-30 22:55:37', 'pending'),
(143, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 22:59:21', 'pending'),
(144, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-11-30 23:02:14', 'pending'),
(145, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 23:09:20', 'pending'),
(146, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-11-30 23:13:15', 'pending'),
(147, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 23:23:32', 'pending'),
(148, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-11-30 23:23:32', 'pending'),
(149, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-11-30 23:26:52', 'pending'),
(150, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-11-30 23:26:52', 'pending'),
(151, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-12-01 00:04:54', 'pending'),
(152, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-12-01 00:04:54', 'pending'),
(153, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:06:50', 'pending'),
(154, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:06:50', 'pending'),
(155, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:06:50', 'pending'),
(156, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:06:50', 'pending'),
(157, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:12:29', 'pending'),
(158, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:12:29', 'pending'),
(159, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-12-01 00:12:56', 'pending'),
(160, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2025-12-01 00:12:56', 'pending'),
(161, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1, '2025-12-01 00:18:59', 'pending'),
(162, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1, '2025-12-01 00:18:59', 'pending'),
(163, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:25:55', 'pending'),
(164, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:25:55', 'pending'),
(165, 'Pure Piña Cloth', 2213.000, 213.000, 0.000, 12, '2025-12-01 00:33:22', 'pending'),
(166, 'Pure Piña Cloth', 2213.000, 213.000, 0.000, 12, '2025-12-01 00:33:22', 'pending'),
(167, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:47:54', 'pending'),
(168, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 00:47:54', 'pending'),
(169, 'Knotted Liniwan', 0.000, 0.000, 34.000, 1, '2025-12-01 00:55:23', 'pending'),
(171, 'Knotted Liniwan', 0.000, 0.000, 10.000, 1, '2025-12-01 13:43:12', 'pending'),
(172, 'Knotted Liniwan', 0.000, 0.000, 10.000, 1, '2025-12-01 13:43:12', 'pending'),
(173, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 13:44:03', 'pending'),
(174, 'Knotted Liniwan', 12.000, 12.000, 123.000, 112, '2025-12-01 13:44:03', 'pending'),
(175, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-12-01 13:48:46', 'pending'),
(176, 'Knotted Bastos', 12.000, 12.000, 12.000, 1, '2025-12-01 13:48:46', 'pending'),
(177, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 13:50:13', 'in_progress'),
(178, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2025-12-01 13:50:13', 'pending'),
(179, 'Knotted Liniwan', 0.000, 0.000, 213.000, 1, '2025-12-01 13:55:28', 'in_progress'),
(180, 'Knotted Liniwan', 0.000, 0.000, 213.000, 1, '2025-12-01 13:55:28', 'pending'),
(181, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2026-01-05 08:21:08', 'pending'),
(182, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1123, '2026-01-05 08:21:08', 'pending'),
(183, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2026-01-05 11:25:23', 'pending'),
(184, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2026-01-05 11:25:23', 'pending'),
(185, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2026-01-05 11:33:00', 'pending'),
(186, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2026-01-05 11:33:00', 'pending'),
(187, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1, '2026-01-05 11:41:15', 'pending'),
(188, 'Piña Seda', 123.000, 123.000, 0.000, 1123, '2026-01-05 11:44:05', 'pending'),
(189, 'Pure Piña Cloth', 123.000, 123.000, 0.000, 1, '2026-01-05 11:44:15', 'pending'),
(190, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2026-01-05 11:44:24', 'pending'),
(191, 'Knotted Bastos', 0.000, 0.000, 123.000, 1, '2026-01-05 11:44:34', 'pending'),
(192, 'Warped Silk', 0.000, 0.000, 123.000, 1, '2026-01-05 11:48:31', 'pending'),
(193, 'Knotted Liniwan', 0.000, 0.000, 123.000, 1, '2026-01-06 06:26:14', 'pending'),
(194, 'Piña Seda', 123.000, 123.000, 0.000, 1123, '2026-01-06 06:30:21', 'pending'),
(195, 'Piña Seda', 123.000, 123.000, 0.000, 1123, '2026-01-06 06:30:43', 'pending'),
(196, 'Knotted Bastos', 0.000, 0.000, 1.000, 1, '2026-01-06 06:40:29', 'pending'),
(197, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 06:40:49', ''),
(198, 'Piña Seda', 12.000, 12.000, 0.000, 1, '2026-01-06 07:24:10', ''),
(199, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 07:43:03', 'pending'),
(200, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 11:37:43', ''),
(201, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 11:45:31', ''),
(202, 'Piña Seda', 12.000, 12.000, 0.000, 12, '2026-01-06 11:47:16', 'pending'),
(203, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 16:04:56', 'pending'),
(204, 'Pure Piña Cloth', 1.000, 1.000, 0.000, 1, '2026-01-06 16:12:59', 'pending'),
(205, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 16:26:34', ''),
(206, 'Pure Piña Cloth', 1.000, 1.000, 0.000, 1, '2026-01-06 19:58:58', 'pending'),
(207, 'Warped Silk', 0.000, 0.000, 123.000, 1, '2026-01-06 20:55:16', 'pending'),
(208, 'Piña Seda', 1.000, 1.000, 0.000, 1, '2026-01-06 22:12:28', 'pending'),
(209, 'Knotted Liniwan', 0.000, 0.000, 1.000, 1, '2026-01-06 22:18:07', '');

-- --------------------------------------------------------

--
-- Table structure for table `product_category`
--

CREATE TABLE `product_category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(60) NOT NULL,
  `category_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_category`
--

INSERT INTO `product_category` (`category_id`, `category_name`, `category_description`) VALUES
(1, 'Linawan', NULL),
(2, 'Pina fiber', NULL),
(3, 'Bastos', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_materials`
--

CREATE TABLE `product_materials` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `material_type` varchar(50) NOT NULL COMMENT 'raw, finished, etc',
  `material_name` varchar(255) NOT NULL,
  `material_qty` decimal(10,3) NOT NULL DEFAULT 1.000 COMMENT 'quantity of material per unit product',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_materials`
--

INSERT INTO `product_materials` (`id`, `product_name`, `material_type`, `material_name`, `material_qty`, `created_at`) VALUES
(1, 'Plain and design', 'raw', 'Piña Cloth', 1.500, '2025-11-23 16:27:30'),
(2, 'Barong tagalog', 'raw', 'Piña Cloth', 2.000, '2025-11-23 16:27:30'),
(3, 'Piña Seda', 'processed', 'Knotted Bastos', 15.000, '2026-01-05 00:11:36'),
(4, 'Piña Seda', 'processed', 'Warped Silk', 7.000, '2026-01-05 00:11:36'),
(5, 'Pure Piña Cloth', 'processed', 'Knotted Liniwan', 22.000, '2026-01-05 00:11:36'),
(6, 'Knotted Liniwan', 'raw', 'Piña Loose', 1.220, '2026-01-05 00:11:36'),
(7, 'Knotted Bastos', 'raw', 'Piña Loose', 1.220, '2026-01-05 00:11:36'),
(8, 'Warped Silk', 'raw', 'Silk', 1.200, '2026-01-05 00:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `product_raw_materials`
--

CREATE TABLE `product_raw_materials` (
  `id` int(11) NOT NULL,
  `product_name` varchar(60) NOT NULL,
  `raw_material_name` varchar(60) NOT NULL,
  `raw_material_category` varchar(60) DEFAULT NULL,
  `consumption_rate` decimal(10,3) NOT NULL,
  `consumption_unit` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_raw_materials`
--

INSERT INTO `product_raw_materials` (`id`, `product_name`, `raw_material_name`, `raw_material_category`, `consumption_rate`, `consumption_unit`) VALUES
(1, 'Piña Seda', 'Piña Loose', 'Bastos', 20.000, 'g/m²'),
(2, 'Piña Seda', 'Silk', NULL, 9.000, 'g/m²'),
(3, 'Pure Piña Cloth', 'Piña Loose', 'Liniwan/Washout', 30.000, 'g/m²'),
(4, 'Knotted Piña Loose', 'Piña Loose', 'Bastos', 1.000, 'g/g'),
(5, 'Warped Silk', 'Silk', NULL, 1.000, 'g/g');

-- --------------------------------------------------------

--
-- Table structure for table `product_stock`
--

CREATE TABLE `product_stock` (
  `pstock_id` int(11) NOT NULL,
  `pstock_user_id` int(11) NOT NULL,
  `pstock_prod_id` varchar(60) NOT NULL,
  `pstock_stock_type` varchar(60) NOT NULL,
  `pstock_stock_outQty` int(11) NOT NULL,
  `pstock_stock_changes` text NOT NULL,
  `pstock_stock_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`pstock_id`, `pstock_user_id`, `pstock_prod_id`, `pstock_stock_type`, `pstock_stock_outQty`, `pstock_stock_changes`, `pstock_stock_date`) VALUES
(3, 1, '7', 'Stock In', 100, '0 -> 100', '2025-05-29 12:57:07'),
(4, 1, '8', 'Stock In', 100, '0 -> 100', '2025-05-29 12:57:17'),
(14, 7, '9', 'Stock Out', 1, '123 -> 122', '2025-11-24 07:53:06'),
(16, 7, '12', 'Stock Out', 1, '22 -> 21', '2025-11-24 09:53:40'),
(17, 7, '14', 'Stock Out', 1, '8 -> 7', '2025-11-24 09:53:40'),
(21, 7, '16', 'Stock Out', 2, '24 -> 22', '2025-11-25 05:26:27'),
(27, 7, '17', 'Stock Out', 1, '12 -> 11', '2025-11-29 19:43:54'),
(29, 7, '17', 'Stock Out', 1, '11 -> 10', '2025-11-29 19:47:18'),
(32, 7, '18', 'Stock Out', 1, '12 -> 11', '2025-11-29 19:53:05'),
(33, 7, '19', 'Stock Out', 1, '123 -> 122', '2025-11-29 19:53:05'),
(34, 7, '17', 'Stock Out', 1, '10 -> 9', '2025-11-29 19:53:05'),
(35, 7, '17', 'Stock Out', 6, '9 -> 3', '2025-11-29 19:55:59'),
(36, 7, '17', 'Stock Out', 3, '3 -> 0', '2025-11-29 19:59:33'),
(37, 7, '19', 'Stock Out', 1, '122 -> 121', '2026-01-05 13:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `raw_materials`
--

CREATE TABLE `raw_materials` (
  `id` int(11) NOT NULL,
  `raw_materials_name` varchar(60) NOT NULL,
  `category` text DEFAULT NULL,
  `rm_quantity` decimal(10,3) NOT NULL,
  `rm_unit` varchar(20) NOT NULL,
  `rm_status` varchar(60) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_materials`
--

INSERT INTO `raw_materials` (`id`, `raw_materials_name`, `category`, `rm_quantity`, `rm_unit`, `rm_status`, `supplier_name`, `unit_cost`) VALUES
(20, 'Silk', '', 123123.000, 'gram', 'Available', '123123', 9.50),
(21, 'Piña Loose', 'Bastos', 321170.940, 'gram', 'Available', 'Happy', 4.50),
(22, 'Piña Loose', 'Liniwan/Washout', 25.900, 'gram', 'Available', '52', 3811.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock_history`
--

CREATE TABLE `stock_history` (
  `stock_id` int(11) NOT NULL,
  `stock_user_type` varchar(60) NOT NULL,
  `stock_raw_id` int(11) NOT NULL,
  `stock_user_id` int(11) NOT NULL,
  `stock_type` varchar(60) NOT NULL,
  `stock_outQty` decimal(10,2) NOT NULL,
  `stock_changes` text NOT NULL,
  `stock_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_processed_material` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_history`
--

INSERT INTO `stock_history` (`stock_id`, `stock_user_type`, `stock_raw_id`, `stock_user_id`, `stock_type`, `stock_outQty`, `stock_changes`, `stock_date`, `is_processed_material`) VALUES
(31, 'Administrator', 9, 1, 'Stock In', 10.00, '792 -> 802', '2025-05-29 12:42:13', 0),
(32, 'member', 16, 2, 'Stock Out', 14.40, '1000.000 -> 985.600', '2025-07-17 07:34:30', 0),
(33, 'member', 14, 1, 'Stock Out', 1220.00, '100000.000 -> 98780.000', '2025-07-17 11:09:30', 0),
(34, 'member', 15, 1, 'Stock Out', 1220.00, '100000.000 -> 98780.000', '2025-07-17 11:10:54', 0),
(35, 'member', 15, 1, 'Stock Out', 14.64, '98780.000 -> 98765.360', '2025-07-17 12:20:21', 0),
(36, 'member', 15, 1, 'Stock Out', 14.64, '98765.360 -> 98750.720', '2025-07-18 08:28:42', 0),
(37, 'member', 15, 1, 'Stock Out', 14.64, '98750.720 -> 98736.080', '2025-07-18 08:29:59', 0),
(38, 'member', 15, 1, 'Stock Out', 14.64, '98736.080 -> 98721.440', '2025-07-18 08:40:12', 0),
(39, 'member', 15, 1, 'Stock Out', 14.64, '98721.440 -> 98706.800', '2025-07-18 08:43:43', 0),
(40, 'member', 15, 1, 'Stock Out', 14.64, '98706.800 -> 98692.160', '2025-07-18 08:52:10', 0),
(41, 'member', 15, 1, 'Stock Out', 14.64, '98692.160 -> 98677.520', '2025-07-18 08:57:16', 0),
(42, 'member', 15, 1, 'Stock Out', 14.64, '98677.520 -> 98662.880', '2025-07-18 08:58:17', 0),
(43, 'member', 1, 4, 'Stock Out', 15.00, '985.000 -> 970.000', '2025-07-18 09:02:08', 1),
(44, 'member', 3, 4, 'Stock Out', 7.00, '1000.000 -> 993.000', '2025-07-18 09:02:08', 1),
(45, 'member', 1, 4, 'Stock Out', 15.00, '970.000 -> 955.000', '2025-07-18 09:05:33', 1),
(46, 'member', 3, 4, 'Stock Out', 7.00, '993.000 -> 986.000', '2025-07-18 09:05:33', 1),
(47, 'member', 15, 1, 'Stock Out', 14.64, '98662.880 -> 98648.240', '2025-07-18 09:08:32', 0),
(48, 'member', 15, 1, 'Stock Out', 14.64, '98648.240 -> 98633.600', '2025-07-18 09:09:18', 0),
(49, 'member', 15, 1, 'Stock Out', 14.64, '98633.600 -> 98618.960', '2025-07-18 09:10:15', 0),
(50, 'member', 15, 1, 'Stock Out', 14.64, '98618.960 -> 98604.320', '2025-07-18 09:14:41', 0),
(51, 'member', 15, 1, 'Stock Out', 14.64, '98604.320 -> 98589.680', '2025-07-18 09:17:31', 0),
(52, 'member', 1, 4, 'Stock Out', 15.00, '955.000 -> 940.000', '2025-07-18 09:18:36', 1),
(53, 'member', 3, 4, 'Stock Out', 7.00, '986.000 -> 979.000', '2025-07-18 09:18:36', 1),
(54, 'member', 1, 4, 'Stock Out', 15.00, '940.000 -> 925.000', '2025-07-18 09:21:26', 1),
(55, 'member', 3, 4, 'Stock Out', 7.00, '979.000 -> 972.000', '2025-07-18 09:21:26', 1),
(56, 'member', 2, 4, 'Stock Out', 22.00, '1012.000 -> 990.000', '2025-07-18 09:22:18', 1),
(57, 'member', 2, 4, 'Stock Out', 22.00, '990.000 -> 968.000', '2025-07-18 09:27:58', 1),
(58, 'member', 1, 4, 'Stock Out', 15.00, '925.000 -> 910.000', '2025-07-18 09:30:28', 1),
(59, 'member', 3, 4, 'Stock Out', 7.00, '972.000 -> 965.000', '2025-07-18 09:30:28', 1),
(60, 'member', 2, 4, 'Stock Out', 22.00, '968.000 -> 946.000', '2025-07-18 09:31:50', 1),
(61, 'member', 1, 4, 'Stock Out', 15.00, '910.000 -> 895.000', '2025-07-18 09:54:55', 1),
(62, 'member', 3, 4, 'Stock Out', 7.00, '965.000 -> 958.000', '2025-07-18 09:54:55', 1),
(63, 'member', 1, 4, 'Stock Out', 15.00, '895.000 -> 880.000', '2025-07-18 09:55:36', 1),
(64, 'member', 3, 4, 'Stock Out', 7.00, '958.000 -> 951.000', '2025-07-18 09:55:36', 1),
(65, 'member', 2, 4, 'Stock Out', 22.00, '946.000 -> 924.000', '2025-07-18 10:09:03', 1),
(66, 'member', 2, 4, 'Stock Out', 22.00, '924.000 -> 902.000', '2025-07-18 10:09:43', 1),
(67, 'member', 1, 4, 'Stock Out', 15.00, '880.000 -> 865.000', '2025-07-18 10:13:04', 1),
(68, 'member', 3, 4, 'Stock Out', 7.00, '951.000 -> 944.000', '2025-07-18 10:13:04', 1),
(69, 'member', 15, 1, 'Stock Out', 14.64, '98589.680 -> 98575.040', '2025-07-18 10:13:53', 0),
(70, 'member', 15, 1, 'Stock Out', 14.64, '98575.040 -> 98560.400', '2025-07-24 05:30:38', 0),
(71, 'member', 15, 1, 'Stock Out', 14.64, '98560.400 -> 98545.760', '2025-07-24 05:37:06', 0),
(72, 'member', 15, 1, 'Stock Out', 14.64, '98545.760 -> 98531.120', '2025-07-24 05:38:01', 0),
(73, 'member', 15, 1, 'Stock Out', 14.64, '98531.120 -> 98516.480', '2025-07-24 05:40:03', 0),
(74, 'member', 14, 1, 'Stock Out', 14.64, '98780.000 -> 98765.360', '2025-07-24 05:40:25', 0),
(75, 'member', 14, 1, 'Stock Out', 14.64, '98765.360 -> 98750.720', '2025-07-24 05:40:55', 0),
(76, 'member', 15, 1, 'Stock Out', 14.64, '98516.480 -> 98501.840', '2025-07-24 08:49:40', 0),
(77, 'member', 15, 1, 'Stock Out', 14.64, '98501.840 -> 98487.200', '2025-07-24 08:59:41', 0),
(78, 'member', 15, 1, 'Stock Out', 14.64, '98487.200 -> 98472.560', '2025-07-24 09:10:48', 0),
(79, 'member', 15, 1, 'Stock Out', 12.20, '98472.560 -> 98460.360', '2025-07-24 11:37:08', 0),
(80, 'member', 14, 1, 'Stock Out', 14.64, '98750.720 -> 98736.080', '2025-07-24 11:41:22', 0),
(81, 'member', 14, 1, 'Stock Out', 14.64, '98736.080 -> 98721.440', '2025-07-24 12:10:35', 0),
(82, 'member', 15, 1, 'Stock Out', 14.64, '98460.360 -> 98445.720', '2025-07-24 12:24:02', 0),
(83, 'member', 16, 2, 'Stock Out', 14.40, '100000.000 -> 99985.600', '2025-07-24 12:25:37', 0),
(84, 'member', 16, 2, 'Stock Out', 14.40, '99985.600 -> 99971.200', '2025-07-24 12:26:13', 0),
(85, 'member', 1, 4, 'Stock Out', 15.00, '901.000 -> 886.000', '2025-07-24 12:26:49', 1),
(86, 'member', 3, 4, 'Stock Out', 7.00, '956.000 -> 949.000', '2025-07-24 12:26:49', 1),
(87, 'member', 15, 1, 'Stock Out', 14.64, '98445.720 -> 98431.080', '2025-07-27 13:18:04', 0),
(88, 'member', 15, 1, 'Stock Out', 14.64, '98431.08 -> 98416.440', '2025-07-27 13:56:45', 0),
(89, 'member', 14, 1, 'Stock Out', 14.64, '98721.440 -> 98706.800', '2025-07-27 13:58:57', 0),
(90, 'member', 16, 2, 'Stock Out', 14.40, '99971.2 -> 99956.800', '2025-07-27 14:02:47', 0),
(91, 'member', 15, 1, 'Stock Out', 14.64, '98416.440 -> 98401.800', '2025-07-27 14:04:14', 0),
(92, 'member', 15, 1, 'Stock Out', 14.64, '98401.800 -> 98387.160', '2025-07-27 14:05:39', 0),
(93, 'member', 16, 2, 'Stock Out', 14.40, '99956.800 -> 99942.400', '2025-07-27 14:06:25', 0),
(94, 'member', 1, 4, 'Stock Out', 15.00, '898.000 -> 883.000', '2025-07-27 14:07:09', 1),
(95, 'member', 3, 4, 'Stock Out', 7.00, '961.000 -> 954.000', '2025-07-27 14:07:09', 1),
(96, 'member', 15, 1, 'Stock Out', 14.64, '98387.16 -> 98372.520', '2025-07-27 14:09:07', 0),
(97, 'member', 15, 1, 'Stock Out', 14.64, '98372.52 -> 98357.880', '2025-07-28 06:59:37', 0),
(98, 'member', 15, 1, 'Stock Out', 14.64, '98357.88 -> 98343.240', '2025-07-28 07:40:33', 0),
(99, 'member', 15, 1, 'Stock Out', 14.64, '98343.24 -> 98328.600', '2025-07-28 10:09:11', 0),
(100, 'member', 15, 1, 'Stock Out', 14.64, '98328.6 -> 98313.960', '2025-07-28 10:13:55', 0),
(101, 'member', 15, 1, 'Stock Out', 14.64, '98313.960 -> 98299.320', '2025-07-28 10:14:21', 0),
(102, 'member', 16, 2, 'Stock Out', 14.40, '99942.4 -> 99928.000', '2025-07-28 10:15:15', 0),
(103, 'member', 15, 1, 'Stock Out', 14.64, '98299.31999999999 -> 98284.680', '2025-07-28 10:27:44', 0),
(104, 'member', 15, 1, 'Stock Out', 14.64, '98284.68 -> 98270.040', '2025-07-28 10:35:08', 0),
(105, 'member', 15, 1, 'Stock Out', 14.64, '98270.04 -> 98255.400', '2025-07-28 10:53:00', 0),
(106, 'member', 15, 1, 'Stock Out', 14.64, '98255.4 -> 98240.760', '2025-07-28 10:53:41', 0),
(107, 'member', 14, 1, 'Stock Out', 14.64, '98706.8 -> 98692.160', '2025-07-28 11:01:23', 0),
(108, 'member', 15, 1, 'Stock Out', 14.64, '98240.76 -> 98226.120', '2025-07-28 11:03:11', 0),
(109, 'member', 15, 1, 'Stock Out', 14.64, '98226.12 -> 98211.480', '2025-07-28 11:13:04', 0),
(110, 'member', 16, 2, 'Stock Out', 14.40, '99928 -> 99913.600', '2025-07-28 11:14:05', 0),
(111, 'member', 15, 1, 'Stock Out', 14.64, '98211.48 -> 98196.840', '2025-07-29 11:21:20', 0),
(112, 'member', 14, 1, 'Stock Out', 14.64, '98692.16 -> 98677.520', '2025-07-29 11:25:18', 0),
(113, 'member', 15, 1, 'Stock Out', 14.64, '98196.840 -> 98182.200', '2025-07-29 11:28:02', 0),
(114, 'member', 15, 1, 'Stock Out', 14.64, '98182.200 -> 98167.560', '2025-07-29 11:28:29', 0),
(115, 'member', 15, 1, 'Stock Out', 14.64, '98167.560 -> 98152.920', '2025-07-29 11:30:00', 0),
(116, 'member', 14, 1, 'Stock Out', 14.64, '98677.520 -> 98662.880', '2025-07-29 11:30:51', 0),
(117, 'member', 16, 2, 'Stock Out', 14.40, '99913.600 -> 99899.200', '2025-07-29 11:31:29', 0),
(118, 'member', 15, 1, 'Stock Out', 14.64, '98152.92 -> 98138.280', '2025-07-29 11:38:37', 0),
(119, 'member', 16, 2, 'Stock Out', 14.40, '99899.200 -> 99884.800', '2025-07-29 11:39:54', 0),
(120, 'member', 16, 2, 'Stock Out', 14.40, '99884.79999999999 -> 99870.400', '2025-07-29 11:40:23', 0),
(121, 'member', 15, 1, 'Stock Out', 14.64, '98138.280 -> 98123.640', '2025-07-30 12:23:02', 0),
(122, 'member', 14, 1, 'Stock Out', 14.64, '98662.880 -> 98648.240', '2025-07-30 12:24:07', 0),
(123, 'member', 15, 1, 'Stock Out', 14.64, '98123.640 -> 98109.000', '2025-07-30 12:26:39', 0),
(124, 'member', 15, 1, 'Stock Out', 14.64, '98109 -> 98094.360', '2025-07-30 12:32:52', 0),
(125, 'member', 14, 1, 'Stock Out', 1.22, '98648.24 -> 98647.020', '2025-07-30 12:34:24', 0),
(126, 'member', 14, 1, 'Stock Out', 15.86, '98647.02 -> 98631.160', '2025-07-30 12:40:56', 0),
(127, 'member', 15, 1, 'Stock Out', 2.44, '98094.36 -> 98091.920', '2025-07-30 12:45:16', 0),
(128, 'member', 14, 1, 'Stock Out', 4.88, '98631.16 -> 98626.280', '2025-07-30 12:50:29', 0),
(129, 'member', 15, 1, 'Stock Out', 14.64, '98091.92 -> 98077.280', '2025-07-30 12:54:29', 0),
(130, 'member', 15, 1, 'Stock Out', 1.22, '98077.280 -> 98076.060', '2025-07-30 12:55:51', 0),
(131, 'member', 14, 1, 'Stock Out', 2.44, '98626.28 -> 98623.840', '2025-07-30 12:56:37', 0),
(132, 'member', 14, 1, 'Stock Out', 3.66, '98623.84 -> 98620.180', '2025-07-30 12:57:13', 0),
(133, 'member', 15, 1, 'Stock Out', 6.10, '98076.06000000001 -> 98069.960', '2025-07-30 13:00:58', 0),
(134, 'member', 15, 1, 'Stock Out', 2.44, '98069.960 -> 98067.520', '2025-07-30 13:01:53', 0),
(135, 'member', 14, 1, 'Stock Out', 1.22, '98620.180 -> 98618.960', '2025-07-31 02:55:27', 0),
(136, 'member', 14, 1, 'Stock Out', 2.44, '98618.960 -> 98616.520', '2025-07-31 02:57:40', 0),
(137, 'member', 14, 1, 'Stock Out', 1.22, '98616.52 -> 98615.300', '2025-07-31 02:58:26', 0),
(138, 'member', 14, 1, 'Stock Out', 4.88, '98615.3 -> 98610.420', '2025-07-31 03:02:34', 0),
(139, 'member', 16, 2, 'Stock Out', 14.40, '99870.400 -> 99856.000', '2025-07-31 03:27:08', 0),
(140, 'member', 16, 2, 'Stock Out', 14.40, '99856 -> 99841.600', '2025-07-31 03:28:04', 0),
(141, 'member', 1, 4, 'Stock Out', 15.00, '962.000 -> 947.000', '2025-07-31 08:09:09', 1),
(142, 'member', 3, 4, 'Stock Out', 7.00, '1026.000 -> 1019.000', '2025-07-31 08:09:09', 1),
(143, 'member', 1, 4, 'Stock Out', 15.00, '947.000 -> 932.000', '2025-07-31 12:38:18', 1),
(144, 'member', 3, 4, 'Stock Out', 7.00, '1019.000 -> 1012.000', '2025-07-31 12:38:18', 1),
(145, 'member', 14, 1, 'Stock Out', 18.30, '98610.42 -> 98592.120', '2025-07-31 12:39:57', 0),
(146, 'member', 14, 1, 'Stock Out', 18.30, '98592.120 -> 98573.820', '2025-07-31 13:28:50', 0),
(147, 'member', 15, 1, 'Stock Out', 18.30, '98067.52 -> 98049.220', '2025-07-31 13:37:17', 0),
(148, 'member', 15, 1, 'Stock Out', 18.30, '98049.220 -> 98030.920', '2025-07-31 14:28:35', 0),
(149, 'member', 14, 1, 'Stock Out', 18.30, '98573.82 -> 98555.520', '2025-07-31 14:31:17', 0),
(150, 'member', 16, 2, 'Stock Out', 24.00, '99841.6 -> 99817.600', '2025-07-31 14:33:45', 0),
(151, 'member', 16, 2, 'Stock Out', 13.20, '99817.600 -> 99804.400', '2025-07-31 14:49:34', 0),
(152, 'member', 16, 2, 'Stock Out', 16.80, '99804.400 -> 99787.600', '2025-07-31 14:50:50', 0),
(153, 'member', 16, 2, 'Stock Out', 19.20, '99787.600 -> 99768.400', '2025-07-31 14:53:08', 0),
(154, 'member', 16, 2, 'Stock Out', 19.20, '99768.400 -> 99749.200', '2025-07-31 14:56:55', 0),
(155, 'member', 14, 1, 'Stock Out', 12.20, '98555.52 -> 98543.320', '2025-07-31 21:29:08', 0),
(156, 'member', 15, 5, 'Stock Out', 14.64, '98030.920 -> 98016.280', '2025-08-01 03:49:29', 0),
(157, 'member', 2, 4, 'Stock Out', 22.00, '1240.000 -> 1218.000', '2025-08-01 04:12:31', 1),
(158, 'member', 15, 5, 'Stock Out', 1505.48, '98016.28 -> 96510.800', '2025-11-24 13:42:25', 0),
(159, 'member', 15, 5, 'Stock Out', 14.64, '96510.800 -> 96496.160', '2025-11-24 14:48:18', 0),
(160, 'member', 15, 5, 'Stock Out', 391.62, '96496.15999999999 -> 96104.540', '2025-11-27 19:40:42', 0),
(161, 'member', 15, 5, 'Stock Out', 150.06, '96104.54 -> 95954.480', '2025-11-27 21:38:28', 0),
(162, 'member', 14, 5, 'Stock Out', 150.06, '98543.31999999999 -> 98393.260', '2025-11-27 21:40:24', 0),
(163, 'member', 15, 5, 'Stock Out', 14.64, '95954.48 -> 95939.840', '2025-11-30 01:34:33', 0),
(164, 'member', 14, 5, 'Stock Out', 150.06, '98393.260 -> 98243.200', '2025-11-30 06:59:38', 0),
(165, 'member', 15, 5, 'Stock Out', 15031.62, '95939.840 -> 80908.220', '2025-11-30 21:24:07', 0),
(166, 'member', 15, 5, 'Stock Out', 150.06, '80908.220 -> 80758.160', '2025-12-01 13:51:25', 0),
(167, 'member', 14, 5, 'Stock Out', 150.06, '98238.2 -> 98088.140', '2026-01-06 03:19:29', 0),
(168, 'member', 14, 5, 'Stock Out', 1478.64, '98088.14 -> 96609.500', '2026-01-06 03:24:23', 0),
(169, 'member', 15, 5, 'Stock Out', 259.86, '80758.160 -> 80498.300', '2026-01-06 03:37:33', 0),
(170, 'member', 1, 14, 'Stock Out', 15.00, '2445.000 -> 2430.000', '2026-01-06 06:40:55', 1),
(171, 'member', 3, 14, 'Stock Out', 7.00, '1075.000 -> 1068.000', '2026-01-06 06:40:55', 1),
(172, 'member', 1, 14, 'Stock Out', 180.00, '2553.000 -> 2373.000', '2026-01-06 07:24:19', 1),
(173, 'member', 3, 14, 'Stock Out', 84.00, '1068.000 -> 984.000', '2026-01-06 07:24:19', 1),
(174, 'member', 1, 14, 'Stock Out', 15.00, '2373.000 -> 2358.000', '2026-01-06 11:37:51', 1),
(175, 'member', 3, 14, 'Stock Out', 7.00, '984.000 -> 977.000', '2026-01-06 11:37:51', 1),
(176, 'member', 1, 14, 'Stock Out', 15.00, '2358.000 -> 2343.000', '2026-01-06 14:51:22', 1),
(177, 'member', 3, 14, 'Stock Out', 7.00, '977.000 -> 970.000', '2026-01-06 14:51:22', 1),
(178, 'member', 1, 14, 'Stock Out', 15.00, '2343.000 -> 2328.000', '2026-01-06 17:20:38', 1),
(179, 'member', 3, 14, 'Stock Out', 7.00, '970.000 -> 963.000', '2026-01-06 17:20:38', 1),
(180, 'member', 22, 18, 'Stock Out', 1.22, '32 -> 30.780', '2026-01-06 22:04:04', 0),
(181, 'member', 22, 18, 'Stock Out', 1.22, '30.779999999999998 -> 29.560', '2026-01-06 22:08:55', 0),
(182, 'member', 22, 18, 'Stock Out', 1.22, '29.560 -> 28.340', '2026-01-06 22:18:16', 0),
(183, 'member', 22, 18, 'Stock Out', 1.22, '28.34 -> 27.120', '2026-01-06 22:26:07', 0),
(184, 'member', 22, 18, 'Stock Out', 1.22, '27.119999999999997 -> 25.900', '2026-01-06 22:31:19', 0),
(185, 'member', 21, 18, 'Stock Out', 150.06, '321321 -> 321170.940', '2026-01-06 22:36:14', 0);

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `task_id` int(11) NOT NULL,
  `task_user_id` int(11) NOT NULL,
  `task_name` varchar(60) NOT NULL,
  `task_material` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`task_material`)),
  `task_category` varchar(60) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date DEFAULT NULL,
  `status` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_approval_requests`
--

CREATE TABLE `task_approval_requests` (
  `id` int(11) NOT NULL,
  `production_id` varchar(10) NOT NULL,
  `member_id` int(11) NOT NULL,
  `member_name` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `product_name` enum('Knotted Liniwan','Knotted Bastos','Warped Silk') NOT NULL,
  `weight_g` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_date` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_approval_requests`
--

INSERT INTO `task_approval_requests` (`id`, `production_id`, `member_id`, `member_name`, `role`, `product_name`, `weight_g`, `quantity`, `date_created`, `processed_date`, `status`) VALUES
(25, 'PL0003', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, 1, '2025-07-29 11:21:10', NULL, 'approved'),
(26, 'PL0004', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 12.00, 1, '2025-07-29 11:25:07', NULL, 'approved'),
(28, 'PL0005', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, 1, '2025-07-29 11:38:16', NULL, 'approved'),
(29, 'PL0006', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, 1, '2025-07-29 11:40:12', NULL, 'approved'),
(30, 'PL0007', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, 1, '2025-07-30 12:30:06', NULL, 'approved'),
(32, 'PL0008', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 1.00, 1, '2025-07-30 12:34:14', NULL, 'approved'),
(33, 'PL0009', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 13.00, 1, '2025-07-30 12:40:48', NULL, 'approved'),
(34, 'PL0010', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 2.00, 1, '2025-07-30 12:45:08', NULL, 'approved'),
(35, 'PL0011', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 4.00, 1, '2025-07-30 12:50:21', NULL, 'approved'),
(36, 'PL0012', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, 1, '2025-07-30 12:54:20', NULL, 'approved'),
(37, 'PL0013', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 2.00, 1, '2025-07-30 12:56:27', NULL, 'approved'),
(38, 'PL0014', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 3.00, 1, '2025-07-30 12:57:03', NULL, 'approved'),
(39, 'PL0015', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 5.00, 1, '2025-07-30 13:00:50', NULL, 'approved'),
(40, 'PL0016', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 1.00, 1, '2025-07-31 02:58:15', NULL, 'approved'),
(41, 'PL0017', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 4.00, 1, '2025-07-31 03:02:27', NULL, 'approved'),
(42, 'PL0018', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, 1, '2025-07-31 03:27:52', NULL, 'approved'),
(43, 'PL0019', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 15.00, 1, '2025-07-31 12:39:41', NULL, 'approved'),
(44, 'PL0020', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 15.00, 1, '2025-07-31 13:37:00', NULL, 'approved'),
(45, 'PL0021', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 15.00, 1, '2025-07-31 14:30:36', NULL, 'approved'),
(46, 'PL0022', 2, 'thea 213', 'warper', 'Warped Silk', 20.00, 1, '2025-07-31 14:33:29', NULL, 'approved'),
(47, 'PL0023', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 10.00, 1, '2025-07-31 21:28:48', NULL, 'approved'),
(63, 'PL0029', 5, 'jenrose  mon', 'knotter', 'Knotted Bastos', 123.00, 1, '2025-11-27 19:29:32', NULL, 'approved'),
(64, 'PL0030', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 321.00, 1, '2025-11-27 19:40:11', NULL, 'approved'),
(65, 'PL0031', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 123.00, 1, '2025-11-27 21:36:06', NULL, 'approved'),
(66, 'PL0032', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 12.00, 1, '2025-11-27 21:40:31', NULL, 'approved'),
(68, 'PL0033', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 123.00, 1, '2025-11-30 19:23:45', NULL, 'approved'),
(69, 'PL0034', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 123.00, 1, '2026-01-06 03:07:44', NULL, 'approved'),
(70, 'PL0035', 5, 'jenrose  mon123', 'knotter', 'Knotted Bastos', 123.00, 1, '2026-01-06 03:18:23', NULL, 'approved'),
(71, 'PL0036', 5, 'jenrose  mon123', 'knotter', 'Knotted Bastos', 1212.00, 1, '2026-01-06 03:23:41', NULL, 'approved'),
(72, 'PL0037', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 123423.00, 1, '2026-01-06 03:42:37', NULL, 'approved'),
(93, 'PL0040', 14, 'john b', 'weaver', '', 12.00, 1, '2026-01-06 13:48:53', NULL, 'pending'),
(94, 'PL0041', 14, 'john b', 'weaver', '', 1.00, 1, '2026-01-06 13:51:18', NULL, 'pending'),
(95, 'PL0042', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 13:52:15', NULL, 'pending'),
(96, 'PL0043', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 14:46:55', NULL, 'pending'),
(97, 'PL0044', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 14:55:15', NULL, 'pending'),
(98, 'PL0045', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 14:55:31', NULL, 'pending'),
(99, 'PL0046', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 14:56:27', NULL, 'pending'),
(100, 'PL0047', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 14:56:28', NULL, 'pending'),
(102, 'PL0049', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 14:58:16', NULL, 'pending'),
(103, 'PL0050', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 14:59:09', NULL, 'pending'),
(104, 'PL0051', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 14:59:30', NULL, 'pending'),
(105, 'PL0052', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 15:00:31', NULL, 'pending'),
(106, 'PL0053', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 15:11:38', NULL, 'pending'),
(107, 'PL0054', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 15:11:44', NULL, 'rejected'),
(110, 'PL0057', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 15:19:27', NULL, 'rejected'),
(114, 'PL0061', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 15:21:51', NULL, 'approved'),
(116, 'PL0063', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 15:28:28', NULL, 'rejected'),
(117, 'PL0064', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 15:41:14', NULL, 'approved'),
(118, 'PL0065', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 16:43:16', NULL, 'rejected'),
(119, 'PL0066', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 16:43:33', NULL, 'rejected'),
(120, 'PL0067', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 16:51:20', NULL, 'rejected'),
(121, 'PL0068', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 16:52:11', NULL, 'rejected'),
(122, 'PL0069', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 16:53:19', NULL, 'rejected'),
(123, 'PL0070', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 17:03:33', NULL, 'pending'),
(124, 'PL0071', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 17:06:14', NULL, 'pending'),
(125, 'PL0072', 14, 'john b', 'weaver', '', 0.00, 1, '2026-01-06 17:13:57', NULL, 'rejected'),
(126, 'PL0073', 13, 'Erson Hayes', 'warper', 'Warped Silk', 123.00, 1, '2026-01-06 20:54:54', NULL, 'rejected'),
(127, 'PL0074', 13, 'Erson Hayes', 'warper', 'Warped Silk', 12.00, 1, '2026-01-06 20:55:39', NULL, 'approved'),
(128, 'PL0075', 17, 'shibo lee', 'weaver', '', 0.00, 1, '2026-01-06 21:51:56', NULL, 'approved'),
(129, 'PL0076', 17, 'shibo lee', 'weaver', '', 0.00, 1, '2026-01-06 21:57:13', NULL, 'approved'),
(130, 'PL0077', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 22:03:48', NULL, 'approved'),
(131, 'PL0078', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 22:07:21', NULL, 'approved'),
(132, 'PL0079', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 22:25:50', NULL, 'approved'),
(133, 'PL0080', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, 1, '2026-01-06 22:30:46', NULL, 'approved'),
(134, 'PL0081', 18, 'shibo lee', 'knotter', 'Knotted Bastos', 123.00, 1, '2026-01-06 22:36:01', NULL, 'approved');

--
-- Triggers `task_approval_requests`
--
DELIMITER $$
CREATE TRIGGER `after_update_approval_status` AFTER UPDATE ON `task_approval_requests` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE member_self_tasks
        SET status = 'in_progress'
        WHERE production_id = NEW.production_id;
    ELSEIF NEW.status = 'rejected' THEN
        UPDATE member_self_tasks
        SET status = 'rejected'
        WHERE production_id = NEW.production_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int(11) NOT NULL,
  `prod_line_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `role` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `decline_status` varchar(20) DEFAULT NULL,
  `decline_reason` text DEFAULT NULL,
  `estimated_time` int(11) NOT NULL COMMENT 'Estimated time in days',
  `deadline` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `prod_line_id`, `member_id`, `role`, `status`, `decline_status`, `decline_reason`, `estimated_time`, `deadline`, `created_at`, `updated_at`) VALUES
(8, 8, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-17 11:04:37', '2025-07-17 11:09:50'),
(9, 9, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-17 11:10:46', '2025-07-17 11:11:01'),
(11, 11, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-17 12:20:05', '2025-07-17 12:20:29'),
(17, 15, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 08:29:54', '2025-07-18 08:30:08'),
(19, 16, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 08:40:07', '2025-07-18 08:40:19'),
(22, 18, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 08:52:07', '2025-07-18 08:52:17'),
(23, 19, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 08:57:04', '2025-07-18 08:57:32'),
(24, 20, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 08:58:12', '2025-07-18 08:58:29'),
(27, 22, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-20', '2025-07-18 09:02:04', '2025-07-18 09:02:51'),
(30, 24, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:08:22', '2025-07-18 09:08:41'),
(31, 25, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:09:14', '2025-07-18 09:09:25'),
(36, 28, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:17:28', '2025-07-18 09:17:48'),
(37, 29, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:18:30', '2025-07-18 09:18:59'),
(38, 30, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:21:22', '2025-07-18 09:21:35'),
(43, 33, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:30:24', '2025-07-18 09:30:38'),
(48, 36, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 09:55:32', '2025-07-18 09:55:44'),
(50, 38, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-19', '2025-07-18 10:09:37', '2025-07-18 10:10:07'),
(63, 48, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 08:49:35', '2025-07-24 08:49:46'),
(64, 49, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 08:59:27', '2025-07-24 08:59:46'),
(65, 50, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 09:10:41', '2025-07-24 09:10:55'),
(66, 53, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 11:37:04', '2025-07-24 11:37:14'),
(67, 54, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 11:41:19', '2025-07-24 11:41:29'),
(68, 55, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-26', '2025-07-24 12:10:32', '2025-07-24 12:10:43'),
(69, 57, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 12:23:59', '2025-07-24 12:24:42'),
(73, 59, 2, 'warper', 'completed', NULL, NULL, 0, '2025-07-26', '2025-07-24 12:26:09', '2025-07-24 12:26:20'),
(74, 60, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-25', '2025-07-24 12:26:37', '2025-07-24 12:27:10'),
(75, 61, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-29', '2025-07-27 13:18:00', '2025-07-27 13:18:11'),
(77, 62, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-28', '2025-07-27 13:58:49', '2025-07-27 13:59:04'),
(81, 65, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-30', '2025-07-27 14:05:34', '2025-07-27 14:05:48'),
(82, 66, 2, 'warper', 'completed', NULL, NULL, 0, '2025-07-29', '2025-07-27 14:06:18', '2025-07-27 14:06:32'),
(83, 67, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-29', '2025-07-27 14:07:03', '2025-07-27 14:07:15'),
(84, 68, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-30', '2025-07-28 10:14:15', '2025-07-28 10:14:28'),
(89, 71, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-29 11:29:45', '2025-07-29 11:38:05'),
(90, 72, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-29 11:30:47', '2025-07-29 11:38:02'),
(91, 72, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-29 11:30:47', '2025-07-29 11:38:02'),
(92, 73, 2, 'warper', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-29 11:31:25', '2025-07-29 11:37:11'),
(93, 74, 2, 'warper', 'completed', NULL, NULL, 0, '2025-07-30', '2025-07-29 11:39:50', '2025-07-29 11:40:01'),
(96, 76, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 12:22:53', '2025-07-30 12:23:07'),
(97, 76, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 12:22:53', '2025-07-30 12:23:07'),
(98, 77, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 12:24:05', '2025-07-30 12:24:17'),
(99, 77, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 12:24:05', '2025-07-30 12:24:17'),
(100, 78, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 12:26:32', '2025-07-30 12:26:47'),
(102, 79, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 12:55:47', '2025-07-30 12:56:00'),
(103, 80, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-31', '2025-07-30 13:01:48', '2025-07-30 13:02:00'),
(104, 81, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-08-01', '2025-07-31 02:55:21', '2025-07-31 02:55:35'),
(105, 81, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-08-01', '2025-07-31 02:55:21', '2025-07-31 02:55:35'),
(106, 82, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-08-01', '2025-07-31 02:57:36', '2025-07-31 02:57:48'),
(107, 83, 2, 'warper', 'completed', NULL, NULL, 0, '2025-08-01', '2025-07-31 03:27:03', '2025-07-31 03:27:15'),
(108, 84, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-28', '2025-07-31 08:08:27', '2025-07-31 08:09:23'),
(109, 85, 4, 'weaver', 'completed', NULL, NULL, 0, '2025-07-29', '2025-07-31 12:38:12', '2025-07-31 12:38:32'),
(110, 86, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-29', '2025-07-31 13:28:40', '2025-07-31 13:29:03'),
(112, 87, 1, 'knotter', 'completed', NULL, NULL, 0, '2025-07-29', '2025-07-31 14:27:49', '2025-07-31 14:28:54'),
(113, 88, 2, 'warper', 'completed', NULL, NULL, 0, '2025-08-06', '2025-07-31 14:49:12', '2025-11-24 12:44:56'),
(114, 89, 2, 'warper', 'in_progress', NULL, NULL, 0, '2025-08-05', '2025-07-31 14:50:44', '2025-07-31 14:50:50'),
(115, 90, 2, 'warper', 'completed', NULL, NULL, 0, '2025-08-08', '2025-07-31 14:53:01', '2025-07-31 21:27:51'),
(116, 91, 2, 'warper', 'completed', NULL, NULL, 0, '2025-08-09', '2025-07-31 14:56:47', '2025-07-31 14:57:21'),
(117, 91, 2, 'warper', 'completed', NULL, NULL, 0, '2025-08-09', '2025-07-31 14:56:47', '2025-07-31 14:57:21'),
(120, 93, 5, 'knotter', 'completed', NULL, NULL, 0, '2025-08-03', '2025-08-01 03:49:15', '2025-08-01 03:51:09'),
(121, 95, 4, 'weaver', 'in_progress', NULL, NULL, 0, '2025-08-03', '2025-08-01 04:12:03', '2025-08-01 04:12:31'),
(125, 94, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-11-28', '2025-11-24 12:44:18', '2025-11-24 12:44:18'),
(127, 99, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-11-29', '2025-11-24 13:01:49', '2025-11-24 13:01:49'),
(128, 100, 5, 'knotter', 'completed', NULL, NULL, 0, '2025-12-03', '2025-11-24 13:39:18', '2025-11-25 03:59:45'),
(129, 103, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-11-28', '2025-11-27 20:21:54', '2025-11-27 20:21:54'),
(130, 102, 2, 'warper', 'pending', NULL, NULL, 0, '2025-12-04', '2025-11-27 21:33:44', '2025-11-27 21:33:44'),
(132, 118, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-11-24', '2025-11-29 21:53:35', '2025-11-29 21:53:35'),
(133, 116, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-12-02', '2025-11-29 21:54:20', '2025-11-29 21:54:20'),
(134, 125, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-11-30', '2025-11-29 21:55:54', '2025-11-29 21:55:54'),
(136, 126, 4, 'weaver', 'pending', NULL, NULL, 0, '2025-11-30', '2025-11-29 21:57:14', '2025-11-29 21:57:14'),
(137, 133, 5, 'knotter', 'completed', NULL, NULL, 0, '2025-12-04', '2025-11-30 06:59:19', '2025-11-30 20:22:15'),
(138, 133, 5, 'knotter', 'completed', NULL, NULL, 0, '2025-12-04', '2025-11-30 06:59:19', '2025-11-30 20:22:15'),
(139, 137, 5, 'knotter', 'in_progress', NULL, NULL, 0, '2025-12-04', '2025-11-30 19:39:29', '2025-11-30 21:24:07'),
(140, 138, 5, 'knotter', 'declined', NULL, NULL, 0, '2025-12-02', '2025-11-30 21:24:48', '2026-01-06 03:37:45'),
(141, 177, 5, 'knotter', 'in_progress', NULL, NULL, 0, '2025-12-03', '2025-12-01 13:50:34', '2025-12-01 13:51:25'),
(142, 179, 1, 'knotter', 'pending', NULL, NULL, 0, '2026-01-11', '2025-12-02 06:23:36', '2026-01-04 23:53:47'),
(143, 179, 5, 'knotter', 'submitted', NULL, NULL, 0, '2026-01-02', '2025-12-02 06:23:36', '2026-01-06 05:50:53'),
(144, 187, 14, 'weaver', 'pending', NULL, NULL, 0, '2026-01-12', '2026-01-05 11:41:15', '2026-01-05 11:41:15'),
(145, 188, 14, 'weaver', 'declined', NULL, NULL, 0, '2026-01-12', '2026-01-05 11:44:05', '2026-01-06 16:04:27'),
(146, 189, 14, 'weaver', 'declined', NULL, NULL, 0, '2026-01-12', '2026-01-05 11:44:15', '2026-01-06 14:52:07'),
(147, 190, 9, 'knotter', 'pending', NULL, NULL, 0, '2026-01-12', '2026-01-05 11:44:24', '2026-01-05 11:44:24'),
(148, 191, 1, 'knotter', 'pending', NULL, NULL, 0, '2026-01-12', '2026-01-05 11:44:34', '2026-01-05 14:16:28'),
(149, 192, 2, 'warper', 'pending', NULL, NULL, 0, '2026-01-12', '2026-01-05 11:48:31', '2026-01-05 11:55:36'),
(150, 193, 1, 'knotter', 'declined', NULL, NULL, 0, '2026-01-13', '2026-01-06 06:26:14', '2026-01-06 06:27:50'),
(151, 194, 14, 'weaver', 'declined', NULL, NULL, 0, '2026-01-13', '2026-01-06 06:30:21', '2026-01-06 12:01:46'),
(152, 195, 14, 'weaver', 'declined', NULL, NULL, 0, '2026-01-13', '2026-01-06 06:30:43', '2026-01-06 06:41:24'),
(153, 196, 5, 'knotter', 'pending', NULL, NULL, 0, '2026-01-13', '2026-01-06 06:40:29', '2026-01-06 06:40:29'),
(154, 197, 14, 'weaver', 'completed', NULL, NULL, 0, '2026-01-13', '2026-01-06 06:40:49', '2026-01-06 07:24:53'),
(155, 198, 14, 'weaver', 'completed', NULL, NULL, 0, '2026-01-13', '2026-01-06 07:24:10', '2026-01-06 07:24:50'),
(156, 199, 14, 'weaver', 'declined', NULL, NULL, 0, '2026-01-13', '2026-01-06 07:43:03', '2026-01-06 07:43:18'),
(157, 200, 14, 'weaver', 'submitted', NULL, NULL, 0, '2026-01-13', '2026-01-06 11:37:43', '2026-01-06 11:49:27'),
(158, 201, 14, 'weaver', 'submitted', NULL, NULL, 0, '2026-01-13', '2026-01-06 11:45:31', '2026-01-06 16:05:37'),
(159, 202, 4, 'weaver', 'reassigned', NULL, NULL, 0, '2026-01-13', '2026-01-06 11:47:16', '2026-01-06 16:02:49'),
(160, 203, 14, 'weaver', 'reassigned', NULL, NULL, 0, '2026-01-13', '2026-01-06 16:04:56', '2026-01-06 16:50:36'),
(161, 204, 4, 'weaver', 'reassigned', NULL, NULL, 0, '2026-01-13', '2026-01-06 16:12:59', '2026-01-06 16:13:19'),
(162, 205, 14, 'weaver', 'submitted', NULL, NULL, 0, '2026-01-13', '2026-01-06 16:26:34', '2026-01-06 17:20:48'),
(163, 206, 4, 'weaver', 'reassigned', NULL, NULL, 0, '2026-01-13', '2026-01-06 19:58:58', '2026-01-06 19:59:32'),
(164, 207, 13, 'warper', 'pending', NULL, NULL, 0, '2026-01-13', '2026-01-06 20:55:16', '2026-01-06 20:55:16'),
(165, 208, 17, 'weaver', 'pending', NULL, NULL, 0, '2026-01-13', '2026-01-06 22:12:28', '2026-01-06 22:12:28'),
(166, 209, 18, 'knotter', 'completed', NULL, NULL, 0, '2026-01-13', '2026-01-06 22:18:07', '2026-01-06 22:18:50');

--
-- Triggers `task_assignments`
--
DELIMITER $$
CREATE TRIGGER `after_task_completion` AFTER UPDATE ON `task_assignments` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        INSERT INTO payment_records (
            member_id,
            production_id,
            length_m,
            width_m,
            weight_g,
            quantity,
            unit_rate,
            total_amount,
            is_self_assigned,
            payment_status,
            date_created
        )
        SELECT 
            NEW.member_id,
            CAST(NEW.prod_line_id AS CHAR),
            pl.length_m,
            pl.width_m,
            pl.weight_g,
            CASE 
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN pl.quantity
                ELSE 1
            END,
            CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN 50.00
                WHEN pl.product_name = 'Warped Silk' THEN 19.00
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN 550.00
                ELSE 0.00
            END,
            CASE 
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN
                    pl.length_m * 550.00 * pl.quantity
                ELSE
                    pl.weight_g * 
                    CASE 
                        WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                        WHEN pl.product_name = 'Knotted Bastos' THEN 50.00
                        WHEN pl.product_name = 'Warped Silk' THEN 19.00
                        ELSE 0.00
                    END
            END,
            0,
            'Pending',
            NOW()
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.prod_line_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `task_completion_confirmations`
--

CREATE TABLE `task_completion_confirmations` (
  `id` int(11) NOT NULL,
  `production_id` varchar(10) NOT NULL,
  `member_id` int(11) NOT NULL,
  `member_name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `date_started` datetime NOT NULL,
  `date_submitted` datetime DEFAULT NULL,
  `status` enum('in_progress','submitted','completed') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_completion_confirmations`
--

INSERT INTO `task_completion_confirmations` (`id`, `production_id`, `member_id`, `member_name`, `role`, `product_name`, `weight`, `date_started`, `date_submitted`, `status`, `created_at`, `updated_at`) VALUES
(56, 'PL0018', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, '2025-07-31 11:28:00', '2025-07-31 11:28:12', 'completed', '2025-07-31 03:28:00', '2025-07-31 03:28:16'),
(57, 'PL0018', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, '2025-07-31 11:28:04', '2025-07-31 11:28:12', 'completed', '2025-07-31 03:28:04', '2025-07-31 03:28:16'),
(58, 'PL0019', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 15.00, '2025-07-31 20:39:49', '2025-07-31 20:40:02', 'completed', '2025-07-31 12:39:49', '2025-07-31 12:40:09'),
(59, 'PL0019', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 15.00, '2025-07-31 20:39:57', '2025-07-31 20:40:02', 'completed', '2025-07-31 12:39:57', '2025-07-31 12:40:09'),
(60, 'PL0020', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 15.00, '2025-07-31 21:37:09', '2025-07-31 21:37:24', 'completed', '2025-07-31 13:37:09', '2025-07-31 13:37:31'),
(61, 'PL0020', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 15.00, '2025-07-31 21:37:17', '2025-07-31 21:37:24', 'completed', '2025-07-31 13:37:17', '2025-07-31 13:37:31'),
(62, 'PL0021', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 15.00, '2025-07-31 22:30:49', '2025-07-31 22:31:34', 'completed', '2025-07-31 14:30:49', '2025-07-31 14:31:54'),
(63, 'PL0021', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 15.00, '2025-07-31 22:31:17', '2025-07-31 22:31:34', 'completed', '2025-07-31 14:31:17', '2025-07-31 14:31:54'),
(64, 'PL0022', 2, 'thea 213', 'warper', 'Warped Silk', 20.00, '2025-07-31 22:33:38', '2025-07-31 22:33:55', 'completed', '2025-07-31 14:33:38', '2025-07-31 14:34:00'),
(65, 'PL0022', 2, 'thea 213', 'warper', 'Warped Silk', 20.00, '2025-07-31 22:33:45', '2025-07-31 22:33:55', 'completed', '2025-07-31 14:33:45', '2025-07-31 14:34:00'),
(66, 'PL0023', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 10.00, '2025-08-01 05:28:59', '2025-08-01 05:29:28', 'completed', '2025-07-31 21:28:59', '2025-07-31 21:29:41'),
(67, 'PL0023', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 10.00, '2025-08-01 05:29:08', '2025-08-01 05:29:28', 'completed', '2025-07-31 21:29:08', '2025-07-31 21:29:41'),
(76, 'PL0030', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 321.00, '2025-11-28 03:40:25', '2025-11-28 03:50:40', 'completed', '2025-11-27 19:40:25', '2026-01-06 22:27:55'),
(77, 'PL0030', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 321.00, '2025-11-28 03:40:42', '2025-11-28 03:50:40', 'completed', '2025-11-27 19:40:42', '2026-01-06 22:27:55'),
(78, 'PL0029', 5, 'jenrose  mon', 'knotter', 'Knotted Bastos', 123.00, '2025-11-28 03:59:56', '2025-11-30 03:25:54', 'completed', '2025-11-27 19:59:56', '2025-11-30 17:55:53'),
(79, 'PL0031', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 123.00, '2025-11-28 05:38:00', '2025-11-28 05:40:14', 'completed', '2025-11-27 21:38:00', '2026-01-06 20:57:30'),
(80, 'PL0031', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 123.00, '2025-11-28 05:38:28', '2025-11-28 05:40:14', 'completed', '2025-11-27 21:38:28', '2026-01-06 20:57:30'),
(81, 'PL0029', 5, 'jenrose  mon', 'knotter', 'Knotted Bastos', 123.00, '2025-11-28 05:40:24', '2025-11-30 03:25:54', 'completed', '2025-11-27 21:40:24', '2025-11-30 17:55:53'),
(82, 'PL0032', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 12.00, '2025-11-28 05:41:08', '2025-11-30 14:42:06', 'completed', '2025-11-27 21:41:08', '2025-11-30 17:53:58'),
(83, 'PL0032', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 12.00, '2025-11-30 09:34:33', '2025-11-30 14:42:06', 'completed', '2025-11-30 01:34:33', '2025-11-30 17:53:58'),
(84, 'PL0033', 5, 'jenrose  mon', 'knotter', 'Knotted Liniwan', 123.00, '2025-12-01 04:22:10', NULL, 'in_progress', '2025-11-30 20:22:10', '2025-11-30 20:22:10'),
(85, 'PL0034', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 123.00, '2026-01-06 11:07:54', NULL, 'in_progress', '2026-01-06 03:07:54', '2026-01-06 03:07:54'),
(86, 'PL0035', 5, 'jenrose  mon123', 'knotter', 'Knotted Bastos', 123.00, '2026-01-06 11:18:31', '2026-01-06 11:26:12', 'completed', '2026-01-06 03:18:31', '2026-01-06 07:23:51'),
(87, 'PL0035', 5, 'jenrose  mon123', 'knotter', 'Knotted Bastos', 123.00, '2026-01-06 11:19:29', '2026-01-06 11:26:12', 'completed', '2026-01-06 03:19:29', '2026-01-06 07:23:51'),
(88, 'PL0036', 5, 'jenrose  mon123', 'knotter', 'Knotted Bastos', 1212.00, '2026-01-06 11:24:01', '2026-01-06 11:26:08', 'completed', '2026-01-06 03:24:01', '2026-01-06 03:27:51'),
(89, 'PL0036', 5, 'jenrose  mon123', 'knotter', 'Knotted Bastos', 1212.00, '2026-01-06 11:24:23', '2026-01-06 11:26:08', 'completed', '2026-01-06 03:24:23', '2026-01-06 03:27:51'),
(90, 'PL0037', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 123423.00, '2026-01-06 11:44:12', NULL, 'in_progress', '2026-01-06 03:44:12', '2026-01-06 03:44:12'),
(93, 'PL0064', 14, 'john b', 'weaver', 'Piña Seda', 0.00, '2026-01-07 00:42:14', NULL, 'in_progress', '2026-01-06 16:42:14', '2026-01-06 16:42:14'),
(94, 'PL0061', 5, 'jenrose  mon123', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 00:42:26', NULL, 'in_progress', '2026-01-06 16:42:26', '2026-01-06 16:42:26'),
(95, 'PL0074', 13, 'Erson Hayes', 'warper', 'Warped Silk', 12.00, '2026-01-07 04:55:46', NULL, 'in_progress', '2026-01-06 20:55:46', '2026-01-06 20:55:46'),
(96, 'PL0075', 17, 'shibo lee', 'weaver', 'Piña Seda', 0.00, '2026-01-07 05:52:17', '2026-01-07 05:52:41', 'completed', '2026-01-06 21:52:17', '2026-01-06 21:53:00'),
(97, 'PL0075', 17, 'shibo lee', 'weaver', 'Piña Seda', 0.00, '2026-01-07 05:52:31', '2026-01-07 05:52:41', 'completed', '2026-01-06 21:52:31', '2026-01-06 21:53:00'),
(98, 'PL0076', 17, 'shibo lee', 'weaver', 'Piña Seda', 0.00, '2026-01-07 05:57:27', '2026-01-07 05:58:10', 'completed', '2026-01-06 21:57:27', '2026-01-06 22:00:02'),
(99, 'PL0076', 17, 'shibo lee', 'weaver', 'Piña Seda', 0.00, '2026-01-07 05:57:35', '2026-01-07 05:58:10', 'completed', '2026-01-06 21:57:35', '2026-01-06 22:00:02'),
(100, 'PL0077', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:03:56', '2026-01-07 06:04:11', 'completed', '2026-01-06 22:03:56', '2026-01-06 22:04:23'),
(101, 'PL0077', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:04:04', '2026-01-07 06:04:11', 'completed', '2026-01-06 22:04:04', '2026-01-06 22:04:23'),
(102, 'PL0078', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:08:48', '2026-01-07 06:09:00', 'completed', '2026-01-06 22:08:48', '2026-01-06 22:09:32'),
(103, 'PL0078', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:08:55', '2026-01-07 06:09:00', 'completed', '2026-01-06 22:08:55', '2026-01-06 22:09:32'),
(104, 'PL0079', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:25:59', '2026-01-07 06:26:11', 'completed', '2026-01-06 22:25:59', '2026-01-06 22:27:58'),
(105, 'PL0079', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:26:07', '2026-01-07 06:26:11', 'completed', '2026-01-06 22:26:07', '2026-01-06 22:27:58'),
(106, 'PL0080', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:31:07', '2026-01-07 06:31:21', 'completed', '2026-01-06 22:31:07', '2026-01-06 22:31:37'),
(107, 'PL0080', 18, 'shibo lee', 'knotter', 'Knotted Liniwan', 1.00, '2026-01-07 06:31:19', '2026-01-07 06:31:21', 'completed', '2026-01-06 22:31:19', '2026-01-06 22:31:37'),
(108, 'PL0081', 18, 'shibo lee', 'knotter', 'Knotted Bastos', 123.00, '2026-01-07 06:36:08', '2026-01-07 06:36:17', 'completed', '2026-01-06 22:36:08', '2026-01-06 22:36:34'),
(109, 'PL0081', 18, 'shibo lee', 'knotter', 'Knotted Bastos', 123.00, '2026-01-07 06:36:14', '2026-01-07 06:36:17', 'completed', '2026-01-06 22:36:14', '2026-01-06 22:36:34');

-- --------------------------------------------------------

--
-- Table structure for table `task_decline_notifications`
--

CREATE TABLE `task_decline_notifications` (
  `id` int(11) NOT NULL,
  `task_assignment_id` int(11) NOT NULL,
  `prod_line_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `member_reason` text DEFAULT NULL,
  `admin_message` text DEFAULT NULL,
  `status` enum('pending','responded','acknowledged') NOT NULL DEFAULT 'pending',
  `declined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_message_at` timestamp NULL DEFAULT NULL,
  `member_ack_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_decline_notifications`
--

INSERT INTO `task_decline_notifications` (`id`, `task_assignment_id`, `prod_line_id`, `member_id`, `member_reason`, `admin_message`, `status`, `declined_at`, `admin_message_at`, `member_ack_at`, `created_at`, `updated_at`) VALUES
(2, 140, 138, 5, 'cute kase ako', NULL, 'pending', '2026-01-06 03:37:45', NULL, NULL, '2026-01-06 03:37:45', '2026-01-06 03:37:45'),
(3, 150, 193, 5, 'BINAHA KAMI', NULL, 'pending', '2026-01-06 06:26:42', NULL, NULL, '2026-01-06 06:26:42', '2026-01-06 06:26:42'),
(4, 152, 195, 14, 'KULANG DAW YUNG MATERIALS ANTE', NULL, 'pending', '2026-01-06 06:41:24', NULL, NULL, '2026-01-06 06:41:24', '2026-01-06 06:41:24'),
(5, 156, 199, 14, 'ambaho te', NULL, 'pending', '2026-01-06 07:43:18', NULL, NULL, '2026-01-06 07:43:18', '2026-01-06 07:43:18'),
(6, 159, 202, 14, 'kulang ng tela teh', NULL, 'pending', '2026-01-06 11:48:29', NULL, NULL, '2026-01-06 11:48:29', '2026-01-06 11:48:29'),
(7, 151, 194, 14, 'ante kulet mo', NULL, 'pending', '2026-01-06 12:01:46', NULL, NULL, '2026-01-06 12:01:46', '2026-01-06 12:01:46'),
(8, 146, 189, 14, 'binaha ang aklan', NULL, 'pending', '2026-01-06 14:52:07', NULL, NULL, '2026-01-06 14:52:07', '2026-01-06 14:52:07'),
(9, 145, 188, 14, 'baho te', NULL, 'pending', '2026-01-06 16:04:27', NULL, NULL, '2026-01-06 16:04:27', '2026-01-06 16:04:27'),
(10, 160, 203, 14, '123', NULL, 'pending', '2026-01-06 16:05:06', NULL, NULL, '2026-01-06 16:05:06', '2026-01-06 16:05:06'),
(11, 161, 204, 14, 'baho', NULL, 'pending', '2026-01-06 16:13:08', NULL, NULL, '2026-01-06 16:13:08', '2026-01-06 16:13:08'),
(12, 163, 206, 14, '123', NULL, 'pending', '2026-01-06 19:59:08', NULL, NULL, '2026-01-06 19:59:08', '2026-01-06 19:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_admin`
--

CREATE TABLE `user_admin` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_admin`
--

INSERT INTO `user_admin` (`id`, `fullname`, `username`, `password`, `date_created`) VALUES
(1, 'John Doe', 'admin', '$2y$10$JQ1lmgWTeqdSVD3DFIibqeE.0BAjjBrhaBNt5qdLOXV5Fa6os7me.', '2025-05-04 09:54:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_customer`
--

CREATE TABLE `user_customer` (
  `customer_id` int(11) NOT NULL,
  `customer_fullname` varchar(60) NOT NULL,
  `customer_email` varchar(60) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_password` varchar(255) NOT NULL,
  `customer_status` int(11) NOT NULL DEFAULT 1 COMMENT '0=restricted,1=active',
  `customer_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_customer`
--

INSERT INTO `user_customer` (`customer_id`, `customer_fullname`, `customer_email`, `customer_phone`, `customer_password`, `customer_status`, `customer_address`) VALUES
(6, 'Joshua Anderson Padilla', 'jcustom@gmail.com', '09454454741', '$2y$10$Ehvc1AwmVnjhfMT.arbdEuseTS2l9bR9P0eQRjpDOXHHh7eZ9Shx6', 1, NULL),
(7, 'johnny bravo', 'asejo.32@gmail.com', '09475316387', '$2y$10$tCmYP3KwwNjEJ69PrLXHoe9cVwB2reFAlR/FrvCRB5.DgGRZ7sMIi', 1, 'bahay'),
(8, 'johnny sins', 'asejo.32@gmail.comp', '2345346356534', '$2y$10$by5dyptWPzIL5wOzrXxjWOoKnmQZjXE1SrSZ55QI/xS70G7CCZA8.', 1, NULL),
(9, 'johnny sins', 'asejojeff975@gmail.com', '12312312312312', '$2y$10$VwVepjPdAIT0F5ij0rYthu3Ah4CyuzL.t8134NTprc9UicBnPDGm6', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_member`
--

CREATE TABLE `user_member` (
  `id` int(11) NOT NULL,
  `id_number` varchar(20) DEFAULT NULL,
  `fullname` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `availability_status` enum('available','unavailable') DEFAULT 'available',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_member`
--

INSERT INTO `user_member` (`id`, `id_number`, `fullname`, `email`, `phone`, `role`, `sex`, `password`, `status`, `availability_status`, `date_created`) VALUES
(1, 'KNO-2025-001', 'jenny rose montille', 'jenny@gmail.com', '123456789', 'knotter', 'female', '$2y$10$lmMac2q73h6u0Eg5DT5ktOOe2r48L3pYo/xmQJmRKhoZlC6CgsQM2', 1, 'available', '2025-07-02 05:54:12'),
(2, 'WAR-2025-001', 'thea 213', '213@gmail.com', '123456789', 'warper', 'female', '$2y$10$IIAht6dF37swUgccBmDEmuTxAMSTFR69L14m7CgsskgAlE7s0fune', 1, 'available', '2025-07-02 05:55:48'),
(4, 'WEA-2025-001', 'bem nov pornel', '1234@gmail.com', '123456789', 'weaver', 'female', '$2y$10$.sGlAXAGBCdzVccPHFezIOQAodpEHxvJPtRjjBEqrLwGzXdjADzz2', 1, 'available', '2025-07-17 07:33:30'),
(5, 'KNO-2025-002', 'jenrose  mon123', 'jen123@gmail.com', '7867867867856', 'knotter', 'female', '$2y$10$PGlUQf.FC9MaLDDrRRTy2uYHTeBmhtAJDnUWn55A.3h./eOGTlW4q', 1, 'available', '2025-08-01 03:42:50'),
(9, 'KNO-2025-004', 'asdasdasd asdasdas', 'dasdasdasd@gmail.copm234', '123123123123123', 'knotter', 'female', '$2y$10$ZjL06EOXw2/ok4TSlkD4ZOUuCqqs98L/TTQemr2lrvsXT6W/y382y', 1, 'available', '2025-11-23 06:47:15'),
(12, 'WAR-2025-002', 'Erson Hayes', 'jeffersonasejo@yahoo.com', '09475633165', 'warper', 'male', '$2y$10$mKNIKarGSHAritjKNB5ue.Kdm1U/RfSC9VqDoTbUr7jRSsNkbrbr6', 0, 'available', '2025-11-30 18:39:58'),
(13, 'WAR-2026-001', 'Erson Hayes', 'erer@yahoo.com', '09475633165', 'warper', 'male', '$2y$10$LjFHu4dmmkhnJsQY.HDd9e763eCRCA/bJLgrdRaeG.s/8dfamsbie', 1, 'available', '2026-01-04 23:56:43'),
(14, 'WEA-2026-001', 'john b', 'jljamero.32@gmail.com', '09475633165', 'weaver', 'male', '$2y$10$RVOrCTKKlKkOx3q47I2vA.3c.ACcHjvAnqAXdU3y.V/fI7LvsYoDe', 1, 'available', '2026-01-04 23:57:56'),
(15, 'KNO-2026-001', 'j sur baygon', '123123123@gmail.com', '09475633165', 'knotter', 'male', '$2y$10$kbLeuC0RDKfIwz0q49BfeuLnn0OhsOYLcPI7eP2A/RW8pBOkSPP96', 0, 'available', '2026-01-05 12:13:39'),
(16, 'WEA-2026-002', 'johnny bravo', '123123@123.com', '12312312', 'weaver', 'female', '$2y$10$PtIAj2ikU1UOggkIzAtIueCtiC7egP9aCurtkSf/wVqCzWXcJKnqu', 0, 'available', '2026-01-06 03:50:20'),
(17, 'WEA-2026-003', 'shibo lee', '12312qwe3@123.com', '12312312', 'weaver', 'male', '$2y$10$/4tyazVLmFP4HBbKgmbo6ulOJIs7j2PsETjfngQSgVe21nY5SN/2y', 1, 'available', '2026-01-06 21:50:57'),
(18, 'KNO-2026-002', 'shibo lee', '123123123@123.com', '12312312123', 'knotter', 'male', '$2y$10$vLCB9XM8CJ15dknxZ4/.1.3Ak247Sld2xDuuRjwQD4VY43Vpv8oh6', 1, 'available', '2026-01-06 22:02:56');

-- --------------------------------------------------------

--
-- Table structure for table `weaver`
--

CREATE TABLE `weaver` (
  `id` int(11) NOT NULL,
  `category` varchar(60) NOT NULL,
  `product` varchar(60) NOT NULL,
  `product_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`product_details`)),
  `status` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `member_balance_view`
--
DROP TABLE IF EXISTS `member_balance_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `member_balance_view`  AS SELECT `pr`.`id` AS `id`, `pr`.`member_id` AS `member_id`, CASE WHEN `pr`.`is_self_assigned` = 1 THEN `mst`.`product_name` ELSE `pl`.`product_name` END AS `product_name`, `pr`.`weight_g` AS `weight_g`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN concat(coalesce(`pl`.`length_m`,0),'m x ',coalesce(`pl`.`width_m`,0),'m') ELSE '-' END AS `measurements`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN `pr`.`quantity` ELSE 1 END AS `quantity`, `pr`.`unit_rate` AS `unit_rate`, `pr`.`total_amount` AS `total_amount`, `pr`.`payment_status` AS `payment_status`, `pr`.`date_paid` AS `date_paid`, `pr`.`date_created` AS `date_created`, `um`.`role` AS `member_role` FROM (((`payment_records` `pr` join `user_member` `um` on(`pr`.`member_id` = `um`.`id`)) left join `member_self_tasks` `mst` on(`pr`.`production_id` = `mst`.`production_id` and `pr`.`is_self_assigned` = 1)) left join `production_line` `pl` on(`pr`.`is_self_assigned` = 0 and `pl`.`prod_line_id` = cast(`pr`.`production_id` as unsigned) or `pr`.`is_self_assigned` = 1 and `pl`.`prod_line_id` = cast(substr(`pr`.`production_id`,3) as unsigned))) WHERE `pr`.`payment_status` in ('Pending','Paid','Adjusted') ORDER BY `pr`.`date_created` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `member_earnings_summary`
--
DROP TABLE IF EXISTS `member_earnings_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `member_earnings_summary`  AS SELECT `payment_records`.`member_id` AS `member_id`, count(0) AS `total_tasks`, sum(case when `payment_records`.`payment_status` = 'Pending' then `payment_records`.`total_amount` else 0 end) AS `pending_payments`, sum(case when `payment_records`.`payment_status` = 'Paid' then `payment_records`.`total_amount` else 0 end) AS `completed_payments`, sum(`payment_records`.`total_amount`) AS `total_earnings` FROM `payment_records` GROUP BY `payment_records`.`member_id` ;

-- --------------------------------------------------------

--
-- Structure for view `payment_records_view`
--
DROP TABLE IF EXISTS `payment_records_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_records_view`  AS SELECT `pr`.`id` AS `id`, `pr`.`production_id` AS `production_id`, `um`.`fullname` AS `member_name`, CASE WHEN `pr`.`is_self_assigned` = 1 THEN `mst`.`product_name` ELSE `pl`.`product_name` END AS `product_name`, CASE WHEN `pr`.`is_self_assigned` = 0 AND `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN concat(coalesce(`pl`.`length_m`,0),'m x ',coalesce(`pl`.`width_m`,0),'m') ELSE '-' END AS `measurements`, CASE WHEN `pr`.`is_self_assigned` = 1 THEN `mst`.`weight_g` ELSE `pl`.`weight_g` END AS `weight_g`, CASE WHEN `pr`.`is_self_assigned` = 0 AND `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') OR `pr`.`is_self_assigned` = 1 AND `mst`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN `pr`.`quantity` ELSE NULL END AS `quantity`, `pr`.`unit_rate` AS `unit_rate`, `pr`.`total_amount` AS `total_amount`, `pr`.`payment_status` AS `payment_status`, `pr`.`date_paid` AS `date_paid`, `pr`.`is_self_assigned` AS `is_self_assigned` FROM (((`payment_records` `pr` join `user_member` `um` on(`pr`.`member_id` = `um`.`id`)) left join `member_self_tasks` `mst` on(`pr`.`production_id` = `mst`.`production_id` and `pr`.`is_self_assigned` = 1)) left join `production_line` `pl` on(`pr`.`is_self_assigned` = 0 and `pl`.`prod_line_id` = cast(`pr`.`production_id` as unsigned) or `pr`.`is_self_assigned` = 1 and `pl`.`prod_line_id` = cast(substr(`pr`.`production_id`,3) as unsigned))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `customer_messages`
--
ALTER TABLE `customer_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `finished_products`
--
ALTER TABLE `finished_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `member_self_tasks`
--
ALTER TABLE `member_self_tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_production_id` (`production_id`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_production_id` (`production_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_user_id` (`order_user_id`),
  ADD KEY `idx_order_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `prod_id` (`prod_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_id` (`production_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_date_paid` (`date_paid`);

--
-- Indexes for table `processed_materials`
--
ALTER TABLE `processed_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`);

--
-- Indexes for table `production_line`
--
ALTER TABLE `production_line`
  ADD PRIMARY KEY (`prod_line_id`);

--
-- Indexes for table `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_name` (`product_name`);

--
-- Indexes for table `product_raw_materials`
--
ALTER TABLE `product_raw_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`pstock_id`);

--
-- Indexes for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_history`
--
ALTER TABLE `stock_history`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `stock_raw_id` (`stock_raw_id`),
  ADD KEY `stock_user_id` (`stock_user_id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `task_approval_requests`
--
ALTER TABLE `task_approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_production_id` (`production_id`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prod_line_id` (`prod_line_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `task_completion_confirmations`
--
ALTER TABLE `task_completion_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_id` (`production_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `task_decline_notifications`
--
ALTER TABLE `task_decline_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_decline_task` (`task_assignment_id`),
  ADD KEY `fk_decline_prod` (`prod_line_id`),
  ADD KEY `fk_decline_member` (`member_id`);

--
-- Indexes for table `user_admin`
--
ALTER TABLE `user_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_customer`
--
ALTER TABLE `user_customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `user_member`
--
ALTER TABLE `user_member`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weaver`
--
ALTER TABLE `weaver`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `customer_messages`
--
ALTER TABLE `customer_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `finished_products`
--
ALTER TABLE `finished_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `member_self_tasks`
--
ALTER TABLE `member_self_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `processed_materials`
--
ALTER TABLE `processed_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `production_line`
--
ALTER TABLE `production_line`
  MODIFY `prod_line_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT for table `product_category`
--
ALTER TABLE `product_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_raw_materials`
--
ALTER TABLE `product_raw_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `pstock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stock_history`
--
ALTER TABLE `stock_history`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_approval_requests`
--
ALTER TABLE `task_approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `task_completion_confirmations`
--
ALTER TABLE `task_completion_confirmations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `task_decline_notifications`
--
ALTER TABLE `task_decline_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_admin`
--
ALTER TABLE `user_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_customer`
--
ALTER TABLE `user_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_member`
--
ALTER TABLE `user_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `weaver`
--
ALTER TABLE `weaver`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_messages`
--
ALTER TABLE `customer_messages`
  ADD CONSTRAINT `customer_messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `user_customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `member_self_tasks`
--
ALTER TABLE `member_self_tasks`
  ADD CONSTRAINT `member_self_tasks_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`order_user_id`) REFERENCES `user_customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`);

--
-- Constraints for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD CONSTRAINT `payment_records_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`);

--
-- Constraints for table `task_approval_requests`
--
ALTER TABLE `task_approval_requests`
  ADD CONSTRAINT `task_approval_requests_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `member_self_tasks` (`production_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_approval_requests_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`prod_line_id`) REFERENCES `production_line` (`prod_line_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_completion_confirmations`
--
ALTER TABLE `task_completion_confirmations`
  ADD CONSTRAINT `task_completion_confirmations_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `member_self_tasks` (`production_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_completion_confirmations_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_decline_notifications`
--
ALTER TABLE `task_decline_notifications`
  ADD CONSTRAINT `fk_decline_member` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_decline_prod` FOREIGN KEY (`prod_line_id`) REFERENCES `production_line` (`prod_line_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_decline_task` FOREIGN KEY (`task_assignment_id`) REFERENCES `task_assignments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
