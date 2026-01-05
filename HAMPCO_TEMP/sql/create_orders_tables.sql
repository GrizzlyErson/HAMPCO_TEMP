-- Create orders table
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `order_user_id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'COD',
  `payment_proof` varchar(255) NULL,
  `order_notes` longtext NULL,
  `total_amount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`order_user_id`) REFERENCES `user_customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create order_items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `prod_id` int NOT NULL,
  `product_name` varchar(255) NULL,
  `quantity` int NOT NULL DEFAULT 1,
  `unit_price` decimal(10, 2) NOT NULL,
  `subtotal` decimal(10, 2) NOT NULL,
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_item_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster queries
CREATE INDEX idx_order_user_id ON `orders` (`order_user_id`);
CREATE INDEX idx_order_status ON `orders` (`order_status`);
CREATE INDEX idx_order_id ON `order_items` (`order_id`);

-- Create product_materials table for mapping products to materials
CREATE TABLE IF NOT EXISTS `product_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `material_type` varchar(50) NOT NULL COMMENT 'raw, finished, etc',
  `material_name` varchar(255) NOT NULL,
  `material_qty` decimal(10, 3) NOT NULL DEFAULT 1.000 COMMENT 'quantity of material per unit product',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_name` (`product_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
