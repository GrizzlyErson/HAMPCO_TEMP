-- Add processed_date column to task_approval_requests table
ALTER TABLE task_approval_requests
ADD COLUMN processed_date TIMESTAMP NULL DEFAULT NULL AFTER date_created; 