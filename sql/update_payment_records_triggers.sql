-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS after_task_completion;
DROP TRIGGER IF EXISTS after_self_task_completion;

DELIMITER //

-- Trigger for regular task assignments
CREATE TRIGGER after_task_completion
AFTER UPDATE ON task_assignments
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        INSERT INTO payment_records (
            member_id,
            production_id,
            length_m,
            width_m,
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
            NEW.prod_line_id,
            pl.length_m,
            pl.width_m,
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
            FALSE,
            'Pending',
            NOW()
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.prod_line_id;
    END IF;
END //

-- Trigger for self-assigned tasks
CREATE TRIGGER after_self_task_completion
AFTER UPDATE ON member_self_tasks
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        INSERT INTO payment_records (
            member_id,
            production_id,
            length_m,
            width_m,
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
            pl.length_m,
            pl.width_m,
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
            TRUE,
            'Pending',
            NOW()
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.production_id;
    END IF;
END //

DELIMITER ;