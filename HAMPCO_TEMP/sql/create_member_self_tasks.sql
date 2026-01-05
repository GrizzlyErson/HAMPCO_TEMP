CREATE TABLE IF NOT EXISTS `member_self_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_id` varchar(10) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `weight_g` decimal(10,2) NOT NULL,
  `status` enum('pending', 'in_progress', 'submitted') NOT NULL DEFAULT 'pending',
  `approval_status` enum('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `raw_materials` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_submitted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `member_self_tasks_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trigger to automatically generate production_id in PLXXXX format
DELIMITER //
CREATE TRIGGER before_insert_member_self_tasks
BEFORE INSERT ON member_self_tasks
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SET next_id = (SELECT IFNULL(MAX(CAST(SUBSTRING(production_id, 3) AS UNSIGNED)), 0) + 1 FROM member_self_tasks);
    SET NEW.production_id = CONCAT('PL', LPAD(next_id, 4, '0'));
END//
DELIMITER ;

-- Add a unique constraint to ensure production_id is unique
ALTER TABLE member_self_tasks
ADD CONSTRAINT unique_production_id UNIQUE (production_id); 