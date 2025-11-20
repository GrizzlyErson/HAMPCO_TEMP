-- Drop existing trigger
DROP TRIGGER IF EXISTS after_self_task_start;

DELIMITER //

-- Create updated trigger with duplicate prevention
CREATE TRIGGER after_self_task_start 
AFTER UPDATE ON member_self_tasks 
FOR EACH ROW 
BEGIN
    DECLARE v_member_name VARCHAR(100);
    DECLARE v_role VARCHAR(50);
    DECLARE v_existing_count INT;

    IF NEW.status = 'in_progress' AND OLD.status = 'pending' THEN
        -- Check for existing in_progress record
        SELECT COUNT(*) INTO v_existing_count
        FROM task_completion_confirmations
        WHERE production_id = NEW.production_id 
        AND member_id = NEW.member_id
        AND status = 'in_progress';

        -- Only proceed if no existing record found
        IF v_existing_count = 0 THEN
            -- Get member details
            SELECT fullname, role 
            INTO v_member_name, v_role
            FROM user_member 
            WHERE id = NEW.member_id;
            
            -- Insert into task_completion_confirmations
            INSERT INTO task_completion_confirmations (
                production_id,
                member_id,
                member_name,
                role,
                product_name,
                weight,
                date_started,
                status,
                created_at,
                updated_at
            )
            VALUES (
                NEW.production_id,
                NEW.member_id,
                v_member_name,
                v_role,
                NEW.product_name,
                NEW.weight_g,
                NOW(),
                'in_progress',
                NOW(),
                NOW()
            );
        END IF;
    END IF;
END //

DELIMITER ;

-- Add unique constraint to prevent duplicates
ALTER TABLE task_completion_confirmations 
ADD CONSTRAINT unique_task_completion 
UNIQUE (production_id, member_id, status);