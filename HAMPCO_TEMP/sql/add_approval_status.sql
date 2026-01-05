-- Add approval_status column if it doesn't exist
ALTER TABLE member_self_tasks 
ADD COLUMN IF NOT EXISTS approval_status 
ENUM('pending', 'approved', 'rejected') 
NOT NULL DEFAULT 'pending' 
AFTER status;

-- Update existing records to match their task_approval_requests status
UPDATE member_self_tasks mst 
LEFT JOIN task_approval_requests tar 
ON mst.production_id = tar.production_id 
SET mst.approval_status = COALESCE(tar.status, 'pending'); 