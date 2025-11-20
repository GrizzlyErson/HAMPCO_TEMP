-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS after_insert_self_task;
DROP TRIGGER IF EXISTS after_update_approval_status;

CREATE TABLE IF NOT EXISTS `task_approval_requests` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `production_id` VARCHAR(10) NOT NULL,
    `member_id` INT NOT NULL,
    `member_name` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL,
    `product_name` ENUM('Knotted Liniwan', 'Knotted Bastos', 'Warped Silk') NOT NULL,
    `weight_g` DECIMAL(10,2) NOT NULL,
    `quantity` INT DEFAULT 1,
    `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (production_id) REFERENCES member_self_tasks(production_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES user_member(id) ON DELETE CASCADE,
    INDEX idx_production_id (production_id),
    INDEX idx_member_id (member_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create a trigger to automatically insert into task_approval_requests when a new self-task is created
DELIMITER //
CREATE TRIGGER after_insert_self_task
AFTER INSERT ON member_self_tasks
FOR EACH ROW
BEGIN
    INSERT INTO task_approval_requests (
        production_id,
        member_id,
        member_name,
        role,
        product_name,
        weight_g,
        date_created
    )
    SELECT 
        NEW.production_id,
        NEW.member_id,
        um.fullname,
        um.role,
        NEW.product_name,
        NEW.weight_g,
        NEW.date_created
    FROM user_member um
    WHERE um.id = NEW.member_id;
END//
DELIMITER ;

-- Create a trigger to update member_self_tasks status when task is approved/rejected
DELIMITER //
CREATE TRIGGER after_update_approval_status
AFTER UPDATE ON task_approval_requests
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE member_self_tasks
        SET status = 'in_progress'
        WHERE production_id = NEW.production_id;
    ELSEIF NEW.status = 'rejected' THEN
        UPDATE member_self_tasks
        SET status = 'rejected'
        WHERE production_id = NEW.production_id;
    END IF;
END//
DELIMITER ; 