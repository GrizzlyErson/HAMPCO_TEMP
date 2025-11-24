-- Add material_qty column to product_materials for per-unit material requirements
ALTER TABLE product_materials
ADD COLUMN IF NOT EXISTS material_qty DOUBLE DEFAULT 1;

-- If your MySQL version doesn't support ADD COLUMN IF NOT EXISTS, run this instead:
-- ALTER TABLE product_materials ADD COLUMN material_qty DOUBLE DEFAULT 1;