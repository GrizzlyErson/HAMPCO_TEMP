-- First, standardize the collation for task_assignments table
ALTER TABLE task_assignments 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Standardize the collation for production_line table
ALTER TABLE production_line 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Standardize the collation for task_completion_confirmations table
ALTER TABLE task_completion_confirmations 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Drop existing trigger
DROP TRIGGER IF EXISTS after_task_assignment_completion;

DELIMITER //

-- Recreate the trigger with proper collation handling
CREATE TRIGGER after_task_assignment_completion
AFTER UPDATE ON task_assignments
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Get member details with proper collation
        SELECT 
            fullname, 
            role 
        INTO 
            @member_name, 
            @member_role
        FROM user_member 
        WHERE id = NEW.member_id;

        -- Get product details with proper collation
        SELECT 
            product_name,
            weight_g
        INTO 
            @product_name,
            @weight_g
        FROM production_line 
        WHERE prod_line_id = NEW.prod_line_id;

        -- Insert completion record with consistent collation
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
    END IF;
END //

DELIMITER ;