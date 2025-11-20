-- Create a temporary table to store unique task completions
CREATE TEMPORARY TABLE temp_unique_completions AS
SELECT DISTINCT
    production_id,
    member_id,
    member_name,
    role,
    product_name,
    weight,
    MAX(date_submitted) as date_submitted,
    status
FROM task_completion_confirmations
WHERE status = 'completed'
GROUP BY production_id, member_id, member_name, role, product_name, weight, status;

-- Delete all existing completed records
DELETE FROM task_completion_confirmations 
WHERE status = 'completed';

-- Insert back only the unique records
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
    production_id,
    member_id,
    member_name,
    role,
    product_name,
    weight,
    date_submitted, -- Using submission date as start date since we lost the original
    date_submitted,
    status,
    NOW(),
    NOW()
FROM temp_unique_completions;

-- Drop the temporary table
DROP TEMPORARY TABLE IF EXISTS temp_unique_completions;

-- Create a unique index to prevent future duplicates
ALTER TABLE task_completion_confirmations 
ADD CONSTRAINT unique_completion 
UNIQUE (production_id, member_id, status);