-- First, clear any existing self-assigned payment records to avoid duplicates
DELETE FROM payment_records WHERE is_self_assigned = 1;

-- Insert all completed self-assigned tasks into payment_records
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
    mst.weight_g,
    1 as quantity,
    CASE 
        WHEN mst.product_name = 'Knotted Liniwan' THEN 50.00
        WHEN mst.product_name = 'Knotted Bastos' THEN 45.00
        WHEN mst.product_name = 'Warped Silk' THEN 60.00
        ELSE 0.00
    END as unit_rate,
    mst.weight_g * CASE 
        WHEN mst.product_name = 'Knotted Liniwan' THEN 50.00
        WHEN mst.product_name = 'Knotted Bastos' THEN 45.00
        WHEN mst.product_name = 'Warped Silk' THEN 60.00
        ELSE 0.00
    END as total_amount,
    1 as is_self_assigned,
    'Pending' as payment_status,
    mst.date_submitted as date_created
FROM member_self_tasks mst
WHERE mst.status = 'completed';

-- Drop and recreate the trigger for self-assigned tasks
DROP TRIGGER IF EXISTS after_self_task_completion;

DELIMITER //

CREATE TRIGGER after_self_task_completion
AFTER UPDATE ON member_self_tasks
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
END //

DELIMITER ;