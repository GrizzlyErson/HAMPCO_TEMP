-- First, let's create a view that shows completed tasks without duplicates
DROP VIEW IF EXISTS completed_tasks_view;

CREATE VIEW completed_tasks_view AS
SELECT DISTINCT
    pl.product_name,
    um.fullname AS member_name,
    um.role,
    pl.length_m,
    pl.width_m,
    pl.weight_g,
    pl.quantity,
    ta.updated_at AS completed_date
FROM task_assignments ta
INNER JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
INNER JOIN user_member um ON ta.member_id = um.id
WHERE ta.status = 'completed'
UNION
SELECT DISTINCT
    mst.product_name,
    um.fullname AS member_name,
    um.role,
    NULL as length_m,
    NULL as width_m,
    mst.weight_g,
    1 as quantity,
    mst.date_submitted AS completed_date
FROM member_self_tasks mst
INNER JOIN user_member um ON mst.member_id = um.id
WHERE mst.status = 'completed';

-- Drop existing trigger if it exists
DROP TRIGGER IF EXISTS after_task_assignment_completion;

DELIMITER //

-- Create new trigger to handle admin-assigned task completions
CREATE TRIGGER after_task_assignment_completion
AFTER UPDATE ON task_assignments
FOR EACH ROW
BEGIN
    -- Only handle transitions to completed status
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Delete any existing completion records for this task
        DELETE FROM task_completion_confirmations 
        WHERE production_id = CAST(NEW.prod_line_id AS CHAR)
        AND member_id = NEW.member_id;
        
        -- Insert a single completion record
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
        )
        SELECT 
            CAST(NEW.prod_line_id AS CHAR),
            NEW.member_id,
            um.fullname,
            um.role,
            pl.product_name,
            pl.weight_g,
            NEW.created_at,
            NOW(),
            'completed',
            NOW(),
            NOW()
        FROM user_member um
        JOIN production_line pl ON pl.prod_line_id = NEW.prod_line_id
        WHERE um.id = NEW.member_id;
    END IF;
END //

DELIMITER ;

-- Clean up existing duplicate completion records for admin-assigned tasks
DELETE t1 FROM task_completion_confirmations t1
INNER JOIN task_completion_confirmations t2
WHERE t1.id < t2.id
AND t1.production_id = t2.production_id
AND t1.member_id = t2.member_id
AND t1.status = t2.status
AND t1.production_id NOT LIKE 'PL%'; -- Only clean up admin-assigned tasks (non-PL prefixed IDs)