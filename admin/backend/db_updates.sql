-- Add a unique identifier column to production_line (nullable first to avoid conflicts)
ALTER TABLE production_line
ADD COLUMN IF NOT EXISTS production_code VARCHAR(20) AFTER prod_line_id;

-- Add product_type column
ALTER TABLE production_line
ADD COLUMN IF NOT EXISTS product_type VARCHAR(100) AFTER product_name;

-- Update the existing records with unique production codes
UPDATE production_line
SET production_code = CONCAT('PROD-', LPAD(prod_line_id, 6, '0'), '-', SUBSTRING(MD5(CONCAT(prod_line_id, RAND())), 1, 3))
WHERE production_code IS NULL OR production_code = '';

-- Now add the unique constraint after populating the data
ALTER TABLE production_line
ADD UNIQUE INDEX IF NOT EXISTS idx_production_code (production_code);

-- Add status column if not exists
ALTER TABLE production_line
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';

-- Add date_created column if not exists
ALTER TABLE production_line
ADD COLUMN IF NOT EXISTS date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update task_approval_requests table to include all product types
-- This fixes the issue where weaver tasks with 'Piña Seda' or 'Pure Piña Cloth' products weren't being captured
ALTER TABLE task_approval_requests
MODIFY COLUMN product_name enum('Knotted Liniwan','Knotted Bastos','Warped Silk','Piña Seda','Pure Piña Cloth') NOT NULL; 