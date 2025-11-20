-- First, clean up any potential duplicate records
DELETE FROM payment_records WHERE is_self_assigned = 1;

-- Insert payment records for all completed self-assigned tasks
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
INNER JOIN production_line pl ON mst.production_id = pl.prod_line_id
WHERE mst.status = 'completed';

-- Drop and recreate the trigger with updated logic
DROP TRIGGER IF EXISTS after_self_task_completion;

DELIMITER //

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
        WHERE pl.prod_line_id = NEW.production_id
        AND NOT EXISTS (
            SELECT 1 FROM payment_records 
            WHERE production_id = NEW.production_id 
            AND is_self_assigned = 1
        );
    END IF;
END //

DELIMITER ;