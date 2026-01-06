ALTER TABLE `task_assignments`
ADD COLUMN `decline_status` VARCHAR(20) NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `decline_reason` TEXT NULL DEFAULT NULL AFTER `decline_status`;