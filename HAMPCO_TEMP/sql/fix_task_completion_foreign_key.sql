-- Drop existing foreign key constraints
ALTER TABLE task_completion_confirmations
DROP FOREIGN KEY IF EXISTS task_completion_confirmations_ibfk_1;

-- Drop existing trigger
DROP TRIGGER IF EXISTS after_task_assignment_completion;

DELIMITER //

-- Create updated trigger that handles both types of production IDs
CREATE TRIGGER after_task_assignment_completion
AFTER UPDATE ON task_assignments
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Get member details
        SELECT 
            fullname, 
            role 
        INTO 
            @member_name, 
            @member_role
        FROM user_member 
        WHERE id = NEW.member_id;

        -- Get product details
        SELECT 
            product_name,
            weight_g
        INTO 
            @product_name,
            @weight_g
        FROM production_line 
        WHERE prod_line_id = NEW.prod_line_id;

        -- Insert completion record with string production_id
        INSERT INTO task_completion_confirmations (
            production_id,
            member_id,
            member_name,
            role,
            product_name,
            weight,
            date_started,
            date_submitted,
            status,
            created_at,
            updated_at
        ) VALUES (
            CAST(NEW.prod_line_id AS CHAR),
            NEW.member_id,
            @member_name,
            @member_role,
            @product_name,
            @weight_g,
            NEW.created_at,
            NOW(),
            'completed',
            NOW(),
            NOW()
        );

        -- Update the task status in production_line table
        UPDATE production_line 
        SET status = 'completed'
        WHERE prod_line_id = NEW.prod_line_id;
    END IF;
END //

DELIMITER ;

-- Add back the foreign key with proper configuration
ALTER TABLE task_completion_confirmations
ADD CONSTRAINT task_completion_member_fk
FOREIGN KEY (member_id) REFERENCES user_member(id),
ADD CONSTRAINT task_completion_status_check
CHECK (status IN ('in_progress', 'submitted', 'completed'));