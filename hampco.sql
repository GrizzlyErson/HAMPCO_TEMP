-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 02:25 PM
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
-- Table structure for table `finished_products`
--

CREATE TABLE `finished_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `length_m` decimal(10,3) NOT NULL,
  `width_m` decimal(10,3) NOT NULL,
  `quantity` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_products`
--

INSERT INTO `finished_products` (`id`, `product_name`, `length_m`, `width_m`, `quantity`, `updated_at`) VALUES
(1, 'Knotted Liniwan', 0.000, 0.000, 1, '2025-07-18 08:40:19'),
(2, 'Piña Seda', 1.000, 30.000, 7, '2025-07-27 14:07:15'),
(4, 'Pure Piña Cloth', 1.000, 30.000, 2, '2025-07-18 10:10:07');

-- --------------------------------------------------------

--
-- Stand-in structure for view `member_balance_summary`
-- (See below for the actual view)
--
CREATE TABLE `member_balance_summary` (
`id` int(11)
,`member_id` int(11)
,`product_name` varchar(255)
,`weight_g` decimal(10,3)
,`measurement` varchar(29)
,`quantity` int(11)
,`unit_rate` decimal(10,2)
,`total` decimal(10,2)
,`payment_status` enum('Pending','Paid','Adjusted')
,`date_paid` datetime
,`date_created` timestamp
,`member_role` varchar(20)
);

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
,`measurements` varchar(30)
,`quantity` varchar(11)
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
  `product_name` enum('Knotted Liniwan','Knotted Bastos','Warped Silk') NOT NULL,
  `weight_g` decimal(10,2) NOT NULL,
  `status` enum('pending','in_progress','submitted','completed') NOT NULL DEFAULT 'pending',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `raw_materials` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_submitted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_self_tasks`
--

INSERT INTO `member_self_tasks` (`id`, `production_id`, `member_id`, `product_name`, `weight_g`, `status`, `approval_status`, `raw_materials`, `date_created`, `date_submitted`) VALUES
(32, 'PL0003', 1, 'Knotted Liniwan', 12.00, 'completed', 'pending', NULL, '2025-07-29 11:21:10', '2025-07-29 11:21:29'),
(33, 'PL0004', 1, 'Knotted Bastos', 12.00, 'completed', 'pending', NULL, '2025-07-29 11:25:07', '2025-07-29 11:25:21'),
(35, 'PL0005', 1, 'Knotted Liniwan', 12.00, 'completed', 'pending', NULL, '2025-07-29 11:38:16', '2025-07-29 11:38:46'),
(36, 'PL0006', 2, 'Warped Silk', 12.00, 'completed', 'pending', NULL, '2025-07-29 11:40:12', '2025-07-29 11:40:26'),
(37, 'PL0007', 1, 'Knotted Liniwan', 12.00, 'completed', 'pending', NULL, '2025-07-30 12:30:06', '2025-07-30 12:32:55'),
(39, 'PL0008', 1, 'Knotted Bastos', 1.00, 'completed', 'pending', NULL, '2025-07-30 12:34:14', '2025-07-30 12:34:27'),
(40, 'PL0009', 1, 'Knotted Bastos', 13.00, 'completed', 'pending', NULL, '2025-07-30 12:40:48', '2025-07-30 12:40:58'),
(41, 'PL0010', 1, 'Knotted Liniwan', 2.00, 'completed', 'pending', NULL, '2025-07-30 12:45:08', '2025-07-30 12:45:18'),
(42, 'PL0011', 1, 'Knotted Bastos', 4.00, 'completed', 'pending', NULL, '2025-07-30 12:50:21', '2025-07-30 12:50:31'),
(43, 'PL0012', 1, 'Knotted Liniwan', 12.00, 'completed', 'pending', NULL, '2025-07-30 12:54:20', '2025-07-30 12:54:31'),
(44, 'PL0013', 1, 'Knotted Bastos', 2.00, 'completed', 'pending', NULL, '2025-07-30 12:56:27', '2025-07-30 12:56:39'),
(45, 'PL0014', 1, 'Knotted Bastos', 3.00, 'completed', 'pending', NULL, '2025-07-30 12:57:03', '2025-07-30 12:57:15'),
(46, 'PL0015', 1, 'Knotted Liniwan', 5.00, 'completed', 'pending', NULL, '2025-07-30 13:00:50', '2025-07-30 13:01:00'),
(47, 'PL0016', 1, 'Knotted Bastos', 1.00, 'completed', 'pending', NULL, '2025-07-31 02:58:15', '2025-07-31 02:58:28'),
(48, 'PL0017', 1, 'Knotted Bastos', 4.00, 'completed', 'pending', NULL, '2025-07-31 03:02:27', '2025-07-31 03:02:37'),
(49, 'PL0018', 2, 'Warped Silk', 12.00, 'completed', 'pending', NULL, '2025-07-31 03:27:52', '2025-07-31 03:28:12'),
(50, 'PL0019', 1, 'Knotted Liniwan', 12.00, 'completed', 'pending', NULL, '2025-07-31 11:14:43', '2025-07-31 11:15:19'),
(51, 'PL0020', 2, 'Warped Silk', 12.00, 'completed', 'pending', NULL, '2025-07-31 11:20:46', '2025-07-31 11:20:55'),
(52, 'PL0021', 2, 'Warped Silk', 12.00, 'completed', 'pending', NULL, '2025-07-31 11:25:13', '2025-07-31 11:25:29'),
(53, 'PL0022', 1, 'Knotted Bastos', 4.00, 'completed', 'pending', NULL, '2025-07-31 12:23:52', '2025-07-31 12:24:07');

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
                WHEN NEW.product_name = 'Knotted Bastos' THEN 45.00
                WHEN NEW.product_name = 'Warped Silk' THEN 60.00
                ELSE 0.00
            END,
            NEW.weight_g * CASE 
                WHEN NEW.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN NEW.product_name = 'Knotted Bastos' THEN 45.00
                WHEN NEW.product_name = 'Warped Silk' THEN 60.00
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
(27, 1, NULL, '81', NULL, NULL, 1.000, 1, 45.00, 45.00, 'Pending', NULL, '2025-07-31 02:55:35', 0),
(28, 1, NULL, '81', NULL, NULL, 1.000, 1, 45.00, 45.00, 'Pending', NULL, '2025-07-31 02:55:35', 0),
(29, 1, NULL, '82', NULL, NULL, 2.000, 1, 45.00, 90.00, 'Pending', NULL, '2025-07-31 02:57:48', 0),
(30, 1, NULL, 'PL0016', NULL, NULL, 1.000, 1, 45.00, 45.00, 'Pending', NULL, '2025-07-31 02:58:28', 1),
(31, 1, NULL, 'PL0017', NULL, NULL, 4.000, 1, 45.00, 180.00, 'Pending', NULL, '2025-07-31 03:02:37', 1),
(32, 2, NULL, '83', NULL, NULL, 12.000, 1, 60.00, 720.00, 'Pending', NULL, '2025-07-31 03:27:15', 0),
(33, 2, NULL, 'PL0018', NULL, NULL, 12.000, 1, 60.00, 720.00, 'Paid', '2025-07-31 11:29:20', '2025-07-31 03:28:12', 1),
(34, 4, NULL, '84', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Pending', NULL, '2025-07-31 10:12:38', 0),
(35, 4, NULL, '85', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Pending', NULL, '2025-07-31 10:13:23', 0),
(36, 4, NULL, '85', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Pending', NULL, '2025-07-31 10:13:23', 0),
(37, 4, NULL, '86', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Pending', NULL, '2025-07-31 10:15:31', 0),
(38, 4, NULL, '87', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Pending', NULL, '2025-07-31 11:09:47', 0),
(39, 4, NULL, '88', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Paid', '2025-07-31 20:21:39', '2025-07-31 11:11:02', 0),
(40, 4, NULL, '88', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Pending', NULL, '2025-07-31 11:11:02', 0),
(41, 1, NULL, '89', NULL, NULL, 12.000, 1, 50.00, 600.00, 'Pending', NULL, '2025-07-31 11:12:47', 0),
(42, 1, NULL, '90', NULL, NULL, 12.000, 1, 45.00, 540.00, 'Pending', NULL, '2025-07-31 11:13:22', 0),
(43, 1, NULL, 'PL0019', NULL, NULL, 12.000, 1, 50.00, 600.00, 'Pending', NULL, '2025-07-31 11:15:19', 1),
(44, 2, NULL, 'PL0020', NULL, NULL, 12.000, 1, 60.00, 720.00, 'Pending', NULL, '2025-07-31 11:20:55', 1),
(45, 2, NULL, 'PL0021', NULL, NULL, 12.000, 1, 60.00, 720.00, 'Paid', '2025-07-31 20:21:25', '2025-07-31 11:25:29', 1),
(46, 4, NULL, '92', NULL, NULL, 0.000, 1, 0.00, 0.00, 'Paid', '2025-07-31 20:23:09', '2025-07-31 12:22:52', 0),
(47, 1, NULL, 'PL0022', NULL, NULL, 4.000, 1, 45.00, 180.00, 'Pending', NULL, '2025-07-31 12:24:07', 1);

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
,`weight_g` decimal(10,3)
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `processed_materials`
--

INSERT INTO `processed_materials` (`id`, `processed_materials_name`, `weight`, `status`, `updated_at`) VALUES
(1, 'Knotted Bastos', 948.000, 'Available', '2025-07-31 12:24:11'),
(2, 'Knotted Liniwan', 1134.000, 'Available', '2025-07-31 12:22:43'),
(3, 'Warped Silk', 1036.000, 'Available', '2025-07-31 11:25:33'),
(4, 'Piña Seda', 0.000, 'Available', '2025-07-31 11:09:47'),
(5, 'Pure Piña Cloth', 0.000, 'Available', '2025-07-31 12:22:52');

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
(7, 1, 'Plain and design', 'product_683851b61f1762.82027357.jpg', 5, 4680.00, 'Piña Seda Dyed 36\"W', 1),
(8, 2, 'Barong tagalog', 'product_6838526ad1dff7.75750727.jpg', 12, 999.00, '', 1);

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
(84, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-31 10:12:16', ''),
(85, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-07-31 10:13:00', 'in_progress'),
(86, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-07-31 10:15:11', ''),
(87, 'Piña Seda', 1.000, 30.000, 0.000, 1, '2025-07-31 11:07:34', ''),
(88, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-07-31 11:10:29', 'in_progress'),
(89, 'Knotted Liniwan', 0.000, 0.000, 12.000, 1, '2025-07-31 11:11:32', ''),
(90, 'Knotted Bastos', 0.000, 0.000, 12.000, 1, '2025-07-31 11:12:58', ''),
(92, 'Pure Piña Cloth', 1.000, 30.000, 0.000, 1, '2025-07-31 12:22:33', '');

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
(4, 1, '8', 'Stock In', 100, '0 -> 100', '2025-05-29 12:57:17');

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
  `supplier_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_materials`
--

INSERT INTO `raw_materials` (`id`, `raw_materials_name`, `category`, `rm_quantity`, `rm_unit`, `rm_status`, `supplier_name`) VALUES
(14, 'Piña Loose', 'Bastos', 98590.900, 'gram', 'Available', 'Ryan'),
(15, 'Piña Loose', 'Liniwan/Washout', 98038.240, 'gram', 'Available', 'Ryan'),
(16, 'Silk', '', 99812.800, 'gram', 'Available', 'Ryan');

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
(141, 'member', 1, 4, 'Stock Out', 15.00, '962.000 -> 947.000', '2025-07-31 10:12:29', 1),
(142, 'member', 3, 4, 'Stock Out', 7.00, '1026.000 -> 1019.000', '2025-07-31 10:12:29', 1),
(143, 'member', 2, 4, 'Stock Out', 22.00, '1198.000 -> 1176.000', '2025-07-31 10:13:15', 1),
(144, 'member', 2, 4, 'Stock Out', 22.00, '1176.000 -> 1154.000', '2025-07-31 10:15:24', 1),
(145, 'member', 1, 4, 'Stock Out', 15.00, '947.000 -> 932.000', '2025-07-31 11:09:39', 1),
(146, 'member', 3, 4, 'Stock Out', 7.00, '1019.000 -> 1012.000', '2025-07-31 11:09:39', 1),
(147, 'member', 2, 4, 'Stock Out', 22.00, '1154.000 -> 1132.000', '2025-07-31 11:10:56', 1),
(148, 'member', 15, 1, 'Stock Out', 14.64, '98067.520 -> 98052.880', '2025-07-31 11:12:12', 0),
(149, 'member', 14, 1, 'Stock Out', 14.64, '98610.420 -> 98595.780', '2025-07-31 11:13:09', 0),
(150, 'member', 15, 1, 'Stock Out', 14.64, '98052.88 -> 98038.240', '2025-07-31 11:15:14', 0),
(151, 'member', 16, 2, 'Stock Out', 14.40, '99841.59999999999 -> 99827.200', '2025-07-31 11:20:53', 0),
(152, 'member', 16, 2, 'Stock Out', 14.40, '99827.2 -> 99812.800', '2025-07-31 11:25:24', 0),
(153, 'member', 2, 4, 'Stock Out', 22.00, '1156.000 -> 1134.000', '2025-07-31 12:22:43', 1),
(154, 'member', 14, 1, 'Stock Out', 4.88, '98595.78 -> 98590.900', '2025-07-31 12:24:02', 0);

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
(43, 'PL0019', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, 1, '2025-07-31 11:14:43', NULL, 'approved'),
(44, 'PL0020', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, 1, '2025-07-31 11:20:46', NULL, 'approved'),
(45, 'PL0021', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, 1, '2025-07-31 11:25:13', NULL, 'approved'),
(46, 'PL0022', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 4.00, 1, '2025-07-31 12:23:52', NULL, 'approved');

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
  `estimated_time` int(11) NOT NULL COMMENT 'Estimated time in days',
  `deadline` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `prod_line_id`, `member_id`, `role`, `status`, `estimated_time`, `deadline`, `created_at`, `updated_at`) VALUES
(8, 8, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-17 11:04:37', '2025-07-17 11:09:50'),
(9, 9, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-17 11:10:46', '2025-07-17 11:11:01'),
(11, 11, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-17 12:20:05', '2025-07-17 12:20:29'),
(17, 15, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 08:29:54', '2025-07-18 08:30:08'),
(19, 16, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 08:40:07', '2025-07-18 08:40:19'),
(22, 18, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 08:52:07', '2025-07-18 08:52:17'),
(23, 19, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 08:57:04', '2025-07-18 08:57:32'),
(24, 20, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 08:58:12', '2025-07-18 08:58:29'),
(27, 22, 4, 'weaver', 'completed', 0, '2025-07-20', '2025-07-18 09:02:04', '2025-07-18 09:02:51'),
(30, 24, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 09:08:22', '2025-07-18 09:08:41'),
(31, 25, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 09:09:14', '2025-07-18 09:09:25'),
(36, 28, 1, 'knotter', 'completed', 0, '2025-07-19', '2025-07-18 09:17:28', '2025-07-18 09:17:48'),
(37, 29, 4, 'weaver', 'completed', 0, '2025-07-19', '2025-07-18 09:18:30', '2025-07-18 09:18:59'),
(38, 30, 4, 'weaver', 'completed', 0, '2025-07-19', '2025-07-18 09:21:22', '2025-07-18 09:21:35'),
(43, 33, 4, 'weaver', 'completed', 0, '2025-07-19', '2025-07-18 09:30:24', '2025-07-18 09:30:38'),
(48, 36, 4, 'weaver', 'completed', 0, '2025-07-19', '2025-07-18 09:55:32', '2025-07-18 09:55:44'),
(50, 38, 4, 'weaver', 'completed', 0, '2025-07-19', '2025-07-18 10:09:37', '2025-07-18 10:10:07'),
(63, 48, 1, 'knotter', 'completed', 0, '2025-07-25', '2025-07-24 08:49:35', '2025-07-24 08:49:46'),
(64, 49, 1, 'knotter', 'completed', 0, '2025-07-25', '2025-07-24 08:59:27', '2025-07-24 08:59:46'),
(65, 50, 1, 'knotter', 'completed', 0, '2025-07-25', '2025-07-24 09:10:41', '2025-07-24 09:10:55'),
(66, 53, 1, 'knotter', 'completed', 0, '2025-07-25', '2025-07-24 11:37:04', '2025-07-24 11:37:14'),
(67, 54, 1, 'knotter', 'completed', 0, '2025-07-25', '2025-07-24 11:41:19', '2025-07-24 11:41:29'),
(68, 55, 1, 'knotter', 'completed', 0, '2025-07-26', '2025-07-24 12:10:32', '2025-07-24 12:10:43'),
(69, 57, 1, 'knotter', 'completed', 0, '2025-07-25', '2025-07-24 12:23:59', '2025-07-24 12:24:42'),
(73, 59, 2, 'warper', 'completed', 0, '2025-07-26', '2025-07-24 12:26:09', '2025-07-24 12:26:20'),
(74, 60, 4, 'weaver', 'completed', 0, '2025-07-25', '2025-07-24 12:26:37', '2025-07-24 12:27:10'),
(75, 61, 1, 'knotter', 'completed', 0, '2025-07-29', '2025-07-27 13:18:00', '2025-07-27 13:18:11'),
(77, 62, 1, 'knotter', 'completed', 0, '2025-07-28', '2025-07-27 13:58:49', '2025-07-27 13:59:04'),
(81, 65, 1, 'knotter', 'completed', 0, '2025-07-30', '2025-07-27 14:05:34', '2025-07-27 14:05:48'),
(82, 66, 2, 'warper', 'completed', 0, '2025-07-29', '2025-07-27 14:06:18', '2025-07-27 14:06:32'),
(83, 67, 4, 'weaver', 'completed', 0, '2025-07-29', '2025-07-27 14:07:03', '2025-07-27 14:07:15'),
(84, 68, 1, 'knotter', 'completed', 0, '2025-07-30', '2025-07-28 10:14:15', '2025-07-28 10:14:28'),
(89, 71, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-29 11:29:45', '2025-07-29 11:38:05'),
(90, 72, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-29 11:30:47', '2025-07-29 11:38:02'),
(91, 72, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-29 11:30:47', '2025-07-29 11:38:02'),
(92, 73, 2, 'warper', 'completed', 0, '2025-07-31', '2025-07-29 11:31:25', '2025-07-29 11:37:11'),
(93, 74, 2, 'warper', 'completed', 0, '2025-07-30', '2025-07-29 11:39:50', '2025-07-29 11:40:01'),
(96, 76, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 12:22:53', '2025-07-30 12:23:07'),
(97, 76, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 12:22:53', '2025-07-30 12:23:07'),
(98, 77, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 12:24:05', '2025-07-30 12:24:17'),
(99, 77, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 12:24:05', '2025-07-30 12:24:17'),
(100, 78, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 12:26:32', '2025-07-30 12:26:47'),
(102, 79, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 12:55:47', '2025-07-30 12:56:00'),
(103, 80, 1, 'knotter', 'completed', 0, '2025-07-31', '2025-07-30 13:01:48', '2025-07-30 13:02:00'),
(104, 81, 1, 'knotter', 'completed', 0, '2025-08-01', '2025-07-31 02:55:21', '2025-07-31 02:55:35'),
(105, 81, 1, 'knotter', 'completed', 0, '2025-08-01', '2025-07-31 02:55:21', '2025-07-31 02:55:35'),
(106, 82, 1, 'knotter', 'completed', 0, '2025-08-01', '2025-07-31 02:57:36', '2025-07-31 02:57:48'),
(107, 83, 2, 'warper', 'completed', 0, '2025-08-01', '2025-07-31 03:27:03', '2025-07-31 03:27:15'),
(108, 84, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 10:12:24', '2025-07-31 10:12:38'),
(109, 85, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 10:13:12', '2025-07-31 10:13:23'),
(110, 85, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 10:13:12', '2025-07-31 10:13:23'),
(111, 86, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 10:15:16', '2025-07-31 10:15:31'),
(112, 87, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 11:09:33', '2025-07-31 11:09:47'),
(113, 88, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 11:10:53', '2025-07-31 11:11:02'),
(114, 88, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 11:10:53', '2025-07-31 11:11:02'),
(115, 89, 1, 'knotter', 'completed', 0, '2025-08-01', '2025-07-31 11:12:02', '2025-07-31 11:12:47'),
(116, 90, 1, 'knotter', 'completed', 0, '2025-08-01', '2025-07-31 11:13:04', '2025-07-31 11:13:22'),
(119, 92, 4, 'weaver', 'completed', 0, '2025-08-01', '2025-07-31 12:22:37', '2025-07-31 12:22:52');

--
-- Triggers `task_assignments`
--
DELIMITER $$
CREATE TRIGGER `after_task_completion` AFTER UPDATE ON `task_assignments` FOR EACH ROW BEGIN
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
        SELECT 
            NEW.member_id,
            CAST(NEW.prod_line_id AS CHAR),
            pl.weight_g,
            1,
            CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                WHEN pl.product_name = 'Warped Silk' THEN 60.00
                ELSE 0.00
            END,
            pl.weight_g * CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                WHEN pl.product_name = 'Warped Silk' THEN 60.00
                ELSE 0.00
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
(58, 'PL0019', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, '2025-07-31 19:15:11', '2025-07-31 19:15:19', 'completed', '2025-07-31 11:15:11', '2025-07-31 11:15:24'),
(59, 'PL0019', 1, 'jenny rose montille', 'knotter', 'Knotted Liniwan', 12.00, '2025-07-31 19:15:14', '2025-07-31 19:15:19', 'completed', '2025-07-31 11:15:14', '2025-07-31 11:15:24'),
(60, 'PL0020', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, '2025-07-31 19:20:50', '2025-07-31 19:20:55', 'completed', '2025-07-31 11:20:50', '2025-07-31 11:20:58'),
(61, 'PL0020', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, '2025-07-31 19:20:53', '2025-07-31 19:20:55', 'completed', '2025-07-31 11:20:53', '2025-07-31 11:20:58'),
(62, 'PL0021', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, '2025-07-31 19:25:21', '2025-07-31 19:25:29', 'completed', '2025-07-31 11:25:21', '2025-07-31 11:25:33'),
(63, 'PL0021', 2, 'thea 213', 'warper', 'Warped Silk', 12.00, '2025-07-31 19:25:24', '2025-07-31 19:25:29', 'completed', '2025-07-31 11:25:24', '2025-07-31 11:25:33'),
(64, 'PL0022', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 4.00, '2025-07-31 20:23:59', '2025-07-31 20:24:07', 'completed', '2025-07-31 12:23:59', '2025-07-31 12:24:11'),
(65, 'PL0022', 1, 'jenny rose montille', 'knotter', 'Knotted Bastos', 4.00, '2025-07-31 20:24:02', '2025-07-31 20:24:07', 'completed', '2025-07-31 12:24:02', '2025-07-31 12:24:11');

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
  `customer_status` int(11) NOT NULL DEFAULT 1 COMMENT '0=restricted,1=active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_customer`
--

INSERT INTO `user_customer` (`customer_id`, `customer_fullname`, `customer_email`, `customer_phone`, `customer_password`, `customer_status`) VALUES
(6, 'Joshua Anderson Padilla', 'jcustom@gmail.com', '09454454741', '$2y$10$Ehvc1AwmVnjhfMT.arbdEuseTS2l9bR9P0eQRjpDOXHHh7eZ9Shx6', 1);

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
(4, 'WEA-2025-001', 'bem nov pornel', '1234@gmail.com', '123456789', 'weaver', 'female', '$2y$10$.sGlAXAGBCdzVccPHFezIOQAodpEHxvJPtRjjBEqrLwGzXdjADzz2', 1, 'available', '2025-07-17 07:33:30');

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
-- Structure for view `member_balance_summary`
--
DROP TABLE IF EXISTS `member_balance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `member_balance_summary`  AS SELECT `pr`.`id` AS `id`, `pr`.`member_id` AS `member_id`, CASE WHEN `pr`.`is_self_assigned` = 1 THEN `mst`.`product_name` ELSE `pl`.`product_name` END AS `product_name`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN NULL ELSE `pl`.`weight_g` END AS `weight_g`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN concat(`pl`.`length_m`,'m x ',`pl`.`width_m`,'m') ELSE NULL END AS `measurement`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN `pl`.`quantity` ELSE NULL END AS `quantity`, `pr`.`unit_rate` AS `unit_rate`, `pr`.`total_amount` AS `total`, `pr`.`payment_status` AS `payment_status`, `pr`.`date_paid` AS `date_paid`, `pr`.`date_created` AS `date_created`, `um`.`role` AS `member_role` FROM (((`payment_records` `pr` join `user_member` `um` on(`pr`.`member_id` = `um`.`id`)) left join `member_self_tasks` `mst` on(`pr`.`production_id` = `mst`.`production_id` and `pr`.`is_self_assigned` = 1)) left join `production_line` `pl` on(case when `pr`.`is_self_assigned` = 0 then `pl`.`prod_line_id` = cast(`pr`.`production_id` as unsigned) else `pl`.`prod_line_id` = cast(substr(`pr`.`production_id`,3) as unsigned) end = `pl`.`prod_line_id`)) ORDER BY `pr`.`date_created` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `member_balance_view`
--
DROP TABLE IF EXISTS `member_balance_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `member_balance_view`  AS SELECT `pr`.`id` AS `id`, `pr`.`member_id` AS `member_id`, CASE WHEN `pr`.`is_self_assigned` = 1 THEN `mst`.`product_name` ELSE `pl`.`product_name` END AS `product_name`, `pr`.`weight_g` AS `weight_g`, CASE WHEN `pr`.`is_self_assigned` = 1 AND `mst`.`product_name` in ('Piña Seda','Pure Piña Cloth') OR `pr`.`is_self_assigned` = 0 AND `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN concat(`pl`.`length_m`,'m x ',`pl`.`width_m`,'in') ELSE '-' END AS `measurements`, CASE WHEN `pr`.`is_self_assigned` = 1 AND `mst`.`product_name` in ('Piña Seda','Pure Piña Cloth') OR `pr`.`is_self_assigned` = 0 AND `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN `pl`.`quantity` ELSE '-' END AS `quantity`, `pr`.`unit_rate` AS `unit_rate`, `pr`.`total_amount` AS `total_amount`, `pr`.`payment_status` AS `payment_status`, `pr`.`date_paid` AS `date_paid`, `pr`.`date_created` AS `date_created`, `um`.`role` AS `member_role` FROM (((`payment_records` `pr` join `user_member` `um` on(`pr`.`member_id` = `um`.`id`)) left join `production_line` `pl` on(case when `pr`.`is_self_assigned` = 0 then `pl`.`prod_line_id` = cast(`pr`.`production_id` as unsigned) else `pl`.`prod_line_id` = cast(substr(`pr`.`production_id`,1,locate('_',`pr`.`production_id`) - 1) as unsigned) end)) left join `member_self_tasks` `mst` on(`pr`.`production_id` = `mst`.`production_id` and `pr`.`is_self_assigned` = 1)) WHERE `pr`.`payment_status` in ('Pending','Paid','Adjusted') ORDER BY `pr`.`date_created` DESC ;

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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_records_view`  AS SELECT `pr`.`id` AS `id`, `pr`.`production_id` AS `production_id`, `um`.`fullname` AS `member_name`, `pl`.`product_name` AS `product_name`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN concat(coalesce(`pr`.`length_m`,0),'m x ',coalesce(`pr`.`width_m`,0),'m') ELSE '' END AS `measurements`, `pr`.`weight_g` AS `weight_g`, CASE WHEN `pl`.`product_name` in ('Piña Seda','Pure Piña Cloth') THEN `pr`.`quantity` ELSE NULL END AS `quantity`, `pr`.`unit_rate` AS `unit_rate`, `pr`.`total_amount` AS `total_amount`, `pr`.`payment_status` AS `payment_status`, `pr`.`date_paid` AS `date_paid`, `pr`.`is_self_assigned` AS `is_self_assigned` FROM ((`payment_records` `pr` join `user_member` `um` on(`pr`.`member_id` = `um`.`id`)) join `production_line` `pl` on(`pr`.`production_id` = `pl`.`prod_line_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

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
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_id` (`production_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_date_paid` (`date_paid`),
  ADD KEY `idx_payment_records_member_id` (`member_id`);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `finished_products`
--
ALTER TABLE `finished_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `member_self_tasks`
--
ALTER TABLE `member_self_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `processed_materials`
--
ALTER TABLE `processed_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `production_line`
--
ALTER TABLE `production_line`
  MODIFY `prod_line_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `product_category`
--
ALTER TABLE `product_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_raw_materials`
--
ALTER TABLE `product_raw_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `pstock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stock_history`
--
ALTER TABLE `stock_history`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_approval_requests`
--
ALTER TABLE `task_approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `task_completion_confirmations`
--
ALTER TABLE `task_completion_confirmations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `user_admin`
--
ALTER TABLE `user_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_customer`
--
ALTER TABLE `user_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_member`
--
ALTER TABLE `user_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `weaver`
--
ALTER TABLE `weaver`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `member_self_tasks`
--
ALTER TABLE `member_self_tasks`
  ADD CONSTRAINT `member_self_tasks_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
