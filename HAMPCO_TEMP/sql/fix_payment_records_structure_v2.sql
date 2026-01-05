-- First, backup existing payment records
CREATE TABLE IF NOT EXISTS payment_records_backup AS SELECT * FROM payment_records;

-- Drop existing triggers
DROP TRIGGER IF EXISTS after_task_completion;
DROP TRIGGER IF EXISTS after_self_task_completion;

-- Find and drop the foreign key constraint
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'payment_records' 
    AND COLUMN_NAME = 'production_id' 
    AND CONSTRAINT_NAME != 'PRIMARY'
    AND TABLE_SCHEMA = DATABASE()
);

SET @drop_fk_sql = CONCAT('ALTER TABLE payment_records DROP FOREIGN KEY ', @constraint_name);
PREPARE stmt FROM @drop_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Now modify the column
ALTER TABLE payment_records 
    MODIFY COLUMN production_id VARCHAR(20) NOT NULL;

-- Recreate triggers with correct data types
DELIMITER //

CREATE TRIGGER after_task_completion
AFTER UPDATE ON task_assignments
FOR EACH ROW
BEGIN
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
END //

CREATE TRIGGER after_self_task_completion
AFTER UPDATE ON member_self_tasks
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND (OLD.status != 'completed' OR OLD.status IS NULL) THEN
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
            NEW.production_id,
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
            1,
            'Pending',
            NEW.date_submitted
        FROM production_line pl
        WHERE pl.prod_line_id = CAST(SUBSTRING(NEW.production_id, 3) AS UNSIGNED);
    END IF;
END //

DELIMITER ;

-- Clear existing payment records for self-assigned tasks to avoid duplicates
DELETE FROM payment_records WHERE is_self_assigned = 1;

-- Reinsert the payment records for completed self-assigned tasks
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
    mst.member_id,
    mst.production_id,
    pl.weight_g,
    1 as quantity,
    CASE 
        WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
        WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
        WHEN pl.product_name = 'Warped Silk' THEN 60.00
        ELSE 0.00
    END as unit_rate,
    pl.weight_g * CASE 
        WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
        WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
        WHEN pl.product_name = 'Warped Silk' THEN 60.00
        ELSE 0.00
    END as total_amount,
    1 as is_self_assigned,
    'Pending' as payment_status,
    mst.date_submitted as date_created
FROM member_self_tasks mst
INNER JOIN production_line pl ON CAST(SUBSTRING(mst.production_id, 3) AS UNSIGNED) = pl.prod_line_id
WHERE mst.status = 'completed';