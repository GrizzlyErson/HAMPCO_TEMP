-- Create product_materials table if it doesn't exist
CREATE TABLE IF NOT EXISTS `product_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `member_role` varchar(20) NOT NULL,
  `material_type` enum('raw','processed') NOT NULL,
  `material_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_name` (`product_name`),
  KEY `member_role` (`member_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert material requirements
INSERT INTO `product_materials` (`product_name`, `member_role`, `material_type`, `material_name`) VALUES
-- Knotter materials
('Knotted Liniwan', 'knotter', 'raw', 'Pina Loose Liniwan'),
('Knotted Bastos', 'knotter', 'raw', 'Pina Loose Bastos'),

-- Weaver materials
('Pina Seda', 'weaver', 'processed', 'Knotted Bastos'),
('Pina Seda', 'weaver', 'processed', 'Warped Silk'),
('Pure Pina Cloth', 'weaver', 'processed', 'Knotted Liniwan'); 