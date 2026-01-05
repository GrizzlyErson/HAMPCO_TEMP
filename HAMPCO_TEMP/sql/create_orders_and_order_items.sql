-- Create orders and order_items tables
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT NOT NULL AUTO_INCREMENT,
  `order_user_id` INT NOT NULL,
  `full_name` VARCHAR(255) DEFAULT NULL,
  `contact_number` VARCHAR(100) DEFAULT NULL,
  `delivery_address` TEXT DEFAULT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `total_amount` DECIMAL(20,2) DEFAULT 0,
  `order_status` VARCHAR(50) DEFAULT 'Pending',
  `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `prod_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(20,2) NOT NULL,
  `subtotal` DECIMAL(20,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`order_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
