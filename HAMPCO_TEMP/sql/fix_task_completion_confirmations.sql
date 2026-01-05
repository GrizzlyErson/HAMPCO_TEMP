-- First, let's add missing task completion confirmations for submitted tasks
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
    mst.production_id,
    mst.member_id,
    um.fullname,
    um.role,
    mst.product_name,
    mst.weight_g,
    mst.date_created,
    mst.date_submitted,
    'submitted',
    NOW(),
    NOW()
FROM member_self_tasks mst
INNER JOIN user_member um ON mst.member_id = um.id
WHERE mst.status = 'submitted'
AND NOT EXISTS (
    SELECT 1 
    FROM task_completion_confirmations tcc 
    WHERE tcc.production_id = mst.production_id 
    AND tcc.member_id = mst.member_id
    AND tcc.status = 'submitted'
);

-- Drop existing trigger
DROP TRIGGER IF EXISTS after_self_task_submit;

DELIMITER //

-- Create updated trigger for task submissions
CREATE TRIGGER after_self_task_submit
AFTER UPDATE ON member_self_tasks
FOR EACH ROW
BEGIN
    DECLARE v_member_name VARCHAR(100);
    DECLARE v_role VARCHAR(50);
    
    IF NEW.status = 'submitted' AND OLD.status = 'in_progress' THEN
        -- Get member details
        SELECT fullname, role 
        INTO v_member_name, v_role
        FROM user_member 
        WHERE id = NEW.member_id;
        
        -- First try to update existing record
        UPDATE task_completion_confirmations
        SET 
            status = 'submitted',
            date_submitted = NOW(),
            updated_at = NOW()
        WHERE production_id = NEW.production_id
        AND member_id = NEW.member_id
        AND status = 'in_progress';
        
        -- If no record was updated (no existing record), create a new one
        IF ROW_COUNT() = 0 THEN
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
            VALUES (
                NEW.production_id,
                NEW.member_id,
                v_member_name,
                v_role,
                NEW.product_name,
                NEW.weight_g,
                NEW.date_created,
                NOW(),
                'submitted',
                NOW(),
                NOW()
            );
        END IF;
    END IF;
END //

DELIMITER ;