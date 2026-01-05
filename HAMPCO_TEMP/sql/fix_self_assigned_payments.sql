-- First, let's add any missing payment records for existing completed self-assigned tasks
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
        WHEN pl.product_name = 'Piña Seda' THEN 75.00
        WHEN pl.product_name = 'Pure Piña Cloth' THEN 80.00
        ELSE 0.00
    END as unit_rate,
    CASE 
        WHEN pl.product_name = 'Knotted Liniwan' THEN pl.weight_g * 50.00
        WHEN pl.product_name = 'Knotted Bastos' THEN pl.weight_g * 45.00
        WHEN pl.product_name = 'Warped Silk' THEN pl.weight_g * 60.00
        WHEN pl.product_name = 'Piña Seda' THEN pl.weight_g * 75.00
        WHEN pl.product_name = 'Pure Piña Cloth' THEN pl.weight_g * 80.00
        ELSE 0.00
    END as total_amount,
    1 as is_self_assigned,
    'Pending' as payment_status,
    mst.date_submitted as date_created
FROM member_self_tasks mst
INNER JOIN production_line pl ON mst.production_id = pl.prod_line_id
WHERE mst.status = 'completed'
AND NOT EXISTS (
    SELECT 1 FROM payment_records pr 
    WHERE pr.production_id = mst.production_id 
    AND pr.is_self_assigned = 1
);

-- Drop and recreate the trigger for self-assigned tasks
DROP TRIGGER IF EXISTS after_self_task_completion;

DELIMITER //

CREATE TRIGGER after_self_task_completion
AFTER UPDATE ON member_self_tasks
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Get the product details
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
                WHEN pl.product_name = 'Piña Seda' THEN 75.00
                WHEN pl.product_name = 'Pure Piña Cloth' THEN 80.00
                ELSE 0.00
            END,
            CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN pl.weight_g * 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN pl.weight_g * 45.00
                WHEN pl.product_name = 'Warped Silk' THEN pl.weight_g * 60.00
                WHEN pl.product_name = 'Piña Seda' THEN pl.weight_g * 75.00
                WHEN pl.product_name = 'Pure Piña Cloth' THEN pl.weight_g * 80.00
                ELSE 0.00
            END,
            1,
            'Pending',
            NOW()
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.production_id;
    END IF;
END //

DELIMITER ;