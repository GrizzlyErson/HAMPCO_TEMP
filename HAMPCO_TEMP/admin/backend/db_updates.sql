-- Add a unique identifier column to production_line
ALTER TABLE production_line
ADD COLUMN production_code VARCHAR(20) NOT NULL AFTER prod_line_id,
ADD COLUMN product_type VARCHAR(100) AFTER product_name,
ADD UNIQUE INDEX idx_production_code (production_code);

-- Update the existing records with a unique production code (if any exist)
UPDATE production_line
SET production_code = CONCAT('PROD-', LPAD(prod_line_id, 6, '0'), '-', SUBSTRING(MD5(RAND()), 1, 3))
WHERE production_code IS NULL OR production_code = '';

-- Add status column if not exists
ALTER TABLE production_line
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';

-- Add date_created column if not exists
ALTER TABLE production_line
ADD COLUMN IF NOT EXISTS date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP; 