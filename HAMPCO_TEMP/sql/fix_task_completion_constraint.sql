-- First, remove any existing constraints
ALTER TABLE task_completion_confirmations 
DROP INDEX IF EXISTS unique_task_completion,
DROP INDEX IF EXISTS unique_completion,
DROP INDEX IF EXISTS unique_task_completion_tracking;

-- Add the new unique constraint with a different name
ALTER TABLE task_completion_confirmations 
ADD CONSTRAINT unique_task_status_tracking 
UNIQUE (production_id, member_id, date_submitted);

-- Drop existing trigger if it exists
DELIMITER //

DROP TRIGGER IF EXISTS after_task_completion_confirm //

-- Create the trigger with correct syntax
CREATE TRIGGER after_task_completion_confirm
BEFORE UPDATE ON task_completion_confirmations
FOR EACH ROW
BEGIN
    -- Only handle transitions to completed status
    IF NEW.status = 'completed' AND OLD.status = 'submitted' THEN
        SET NEW.date_submitted = NOW();
    END IF;
END //

DELIMITER ;

-- Clean up any existing duplicate records, keeping only the most recent one
CREATE TEMPORARY TABLE temp_latest_confirmations AS
SELECT 
    production_id,
    member_id,
    MAX(id) as latest_id
FROM task_completion_confirmations
GROUP BY production_id, member_id;

DELETE tcc FROM task_completion_confirmations tcc
LEFT JOIN temp_latest_confirmations tlc 
    ON tcc.id = tlc.latest_id
WHERE tlc.latest_id IS NULL;

DROP TEMPORARY TABLE IF EXISTS temp_latest_confirmations;