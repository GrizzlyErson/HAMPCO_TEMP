-- Create the payment_records table
CREATE TABLE IF NOT EXISTS payment_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    product_id INT,
    production_id INT NOT NULL,
    length_m DECIMAL(10,3) NULL,
    width_m DECIMAL(10,3) NULL,
    weight_g DECIMAL(10,3) NULL,
    quantity INT NULL,
    unit_rate DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Adjusted') DEFAULT 'Pending',
    date_paid DATETIME NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_self_assigned BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (member_id) REFERENCES user_member(id),
    FOREIGN KEY (production_id) REFERENCES production_line(prod_line_id) ON DELETE CASCADE
);

-- Create a view to display payment records with member and product details
CREATE OR REPLACE VIEW payment_records_view AS
SELECT 
    pr.id,
    pr.production_id,
    um.fullname AS member_name,
    pl.product_name,
    CASE 
        WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN
            CONCAT(
                COALESCE(pr.length_m, 0), 'm x ',
                COALESCE(pr.width_m, 0), 'm'
            )
        ELSE ''
    END AS measurements,
    pr.weight_g,
    CASE 
        WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN pr.quantity
        ELSE NULL
    END AS quantity,
    pr.unit_rate,
    pr.total_amount,
    pr.payment_status,
    pr.date_paid,
    pr.is_self_assigned
FROM 
    payment_records pr
    INNER JOIN user_member um ON pr.member_id = um.id
    INNER JOIN production_line pl ON pr.production_id = pl.prod_line_id;

-- Create a trigger to automatically create payment record when a task is completed
DELIMITER //

CREATE TRIGGER after_task_completion
AFTER UPDATE ON task_assignments
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Get product details
        INSERT INTO payment_records (
            member_id,
            production_id,
            length_m,
            width_m,
            weight_g,
            quantity,
            unit_rate,
            total_amount,
            is_self_assigned
        )
        SELECT 
            NEW.member_id,
            NEW.prod_line_id,
            pl.length_m,
            pl.width_m,
            pl.weight_g,
            pl.quantity,
            CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                WHEN pl.product_name = 'Warped Silk' THEN 60.00
                WHEN pl.product_name = 'Piña Seda' THEN 75.00
                WHEN pl.product_name = 'Pure Piña Cloth' THEN 80.00
                ELSE 0.00
            END AS unit_rate,
            CASE 
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN
                    pl.length_m * pl.width_m * pl.quantity * 
                    CASE 
                        WHEN pl.product_name = 'Piña Seda' THEN 75.00
                        ELSE 80.00
                    END
                ELSE
                    pl.weight_g * 
                    CASE 
                        WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                        WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                        WHEN pl.product_name = 'Warped Silk' THEN 60.00
                        ELSE 0.00
                    END
            END AS total_amount,
            FALSE
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.prod_line_id;
    END IF;
END //

-- Create a trigger for self-assigned tasks
CREATE TRIGGER after_self_task_completion
AFTER UPDATE ON member_self_tasks
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        INSERT INTO payment_records (
            member_id,
            production_id,
            length_m,
            width_m,
            weight_g,
            quantity,
            unit_rate,
            total_amount,
            is_self_assigned
        )
        SELECT 
            NEW.member_id,
            NEW.production_id,
            pl.length_m,
            pl.width_m,
            pl.weight_g,
            pl.quantity,
            CASE 
                WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                WHEN pl.product_name = 'Warped Silk' THEN 60.00
                WHEN pl.product_name = 'Piña Seda' THEN 75.00
                WHEN pl.product_name = 'Pure Piña Cloth' THEN 80.00
                ELSE 0.00
            END AS unit_rate,
            CASE 
                WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') THEN
                    pl.length_m * pl.width_m * pl.quantity * 
                    CASE 
                        WHEN pl.product_name = 'Piña Seda' THEN 75.00
                        ELSE 80.00
                    END
                ELSE
                    pl.weight_g * 
                    CASE 
                        WHEN pl.product_name = 'Knotted Liniwan' THEN 50.00
                        WHEN pl.product_name = 'Knotted Bastos' THEN 45.00
                        WHEN pl.product_name = 'Warped Silk' THEN 60.00
                        ELSE 0.00
                    END
            END AS total_amount,
            TRUE
        FROM production_line pl
        WHERE pl.prod_line_id = NEW.production_id;
    END IF;
END //

DELIMITER ;

-- Create indexes for better performance
CREATE INDEX idx_payment_status ON payment_records(payment_status);
CREATE INDEX idx_member_id ON payment_records(member_id);
CREATE INDEX idx_product_id ON payment_records(product_id);
CREATE INDEX idx_date_paid ON payment_records(date_paid); 