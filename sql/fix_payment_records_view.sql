-- Drop existing view if it exists
DROP VIEW IF EXISTS payment_records_view;

-- Create updated view that handles both regular and self-assigned tasks
CREATE OR REPLACE VIEW payment_records_view AS
SELECT 
    pr.id,
    pr.production_id,
    um.fullname AS member_name,
    CASE 
        WHEN pr.is_self_assigned = 1 THEN mst.product_name 
        ELSE pl.product_name 
    END AS product_name,
    CASE 
        WHEN (pr.is_self_assigned = 1 AND mst.product_name IN ('Piña Seda', 'Pure Piña Cloth')) OR
             (pr.is_self_assigned = 0 AND pl.product_name IN ('Piña Seda', 'Pure Piña Cloth')) 
        THEN CONCAT(COALESCE(pr.length_m, 0), 'm x ', COALESCE(pr.width_m, 0), 'm')
        ELSE ''
    END AS measurements,
    pr.weight_g,
    CASE 
        WHEN (pr.is_self_assigned = 1 AND mst.product_name IN ('Piña Seda', 'Pure Piña Cloth')) OR
             (pr.is_self_assigned = 0 AND pl.product_name IN ('Piña Seda', 'Pure Piña Cloth'))
        THEN pr.quantity
        ELSE NULL
    END AS quantity,
    pr.unit_rate,
    pr.total_amount,
    pr.payment_status,
    pr.date_paid,
    pr.is_self_assigned
FROM 
    payment_records pr
    INNER JOIN user_member um ON pr.member_id = um.id
    LEFT JOIN production_line pl ON 
        CASE 
            WHEN pr.is_self_assigned = 0 THEN pl.prod_line_id = CAST(pr.production_id AS UNSIGNED)
            ELSE pl.prod_line_id = CAST(SUBSTRING(pr.production_id, 3) AS UNSIGNED)
        END
    LEFT JOIN member_self_tasks mst ON 
        pr.production_id = mst.production_id AND pr.is_self_assigned = 1;

-- Drop existing trigger if it exists
DROP TRIGGER IF EXISTS after_task_completion;

-- Create updated trigger for task_assignments
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
            CASE 
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN pl.quantity
                ELSE 1
            END,
            CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                WHEN pl.product_name = 'Warped Silk' THEN 60.00
                WHEN pl.product_name = 'Piña Seda' THEN 75.00
                WHEN pl.product_name = 'Pure Piña Cloth' THEN 80.00
                ELSE 0.00
            END,
            CASE 
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN
                    pl.length_m * pl.width_m * pl.quantity * 
                    CASE 
                        WHEN pl.product_name = 'Piña Seda' THEN 75.00
                        ELSE 80.00
                    END
                ELSE
                    pl.weight_g * 
                    CASE 
                        WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                        WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                        WHEN pl.product_name = 'Warped Silk' THEN 60.00
                        ELSE 0.00
                    END
            END,
            0,
            'Pending',
            NOW()
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.prod_line_id;
    END IF;
END //

DELIMITER ;