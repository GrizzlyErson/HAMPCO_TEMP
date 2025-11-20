<?php
require_once 'backend/create_product_raw_materials.php';

// Update raw material names and ensure proper categories
$sql = "UPDATE raw_materials SET 
        raw_materials_name = 'Piña Loose',
        category = 'Bastos',
        rm_unit = 'gram',
        rm_quantity = 10000.000
        WHERE raw_materials_name = 'pina loose liniwan';
        
        UPDATE raw_materials SET 
        raw_materials_name = 'Silk',
        category = NULL,
        rm_unit = 'gram',
        rm_quantity = 10000.000
        WHERE raw_materials_name = 'silk 21d';";
mysqli_multi_query($db->conn, $sql);

// Ensure decimal precision for measurements
$sql = "ALTER TABLE production_line 
        MODIFY COLUMN length_m DECIMAL(10,3) NOT NULL,
        MODIFY COLUMN width_m DECIMAL(10,3) NOT NULL,
        MODIFY COLUMN weight_g DECIMAL(10,3) NOT NULL DEFAULT 0;";
mysqli_query($db->conn, $sql);

// Ensure decimal precision for inventory quantities
$sql = "ALTER TABLE raw_materials 
        MODIFY COLUMN rm_quantity DECIMAL(10,3) NOT NULL;";
mysqli_query($db->conn, $sql);

// Clear existing production items
$sql = "TRUNCATE TABLE production_line;";
mysqli_query($db->conn, $sql);

// Update product raw materials with correct consumption rates
$sql = "TRUNCATE TABLE product_raw_materials;
        INSERT INTO product_raw_materials (product_name, raw_material_name, raw_material_category, consumption_rate, consumption_unit) VALUES
        ('Piña Seda', 'Piña Loose', 'Bastos', 15.000, 'g/m²'),
        ('Piña Seda', 'Silk', NULL, 7.000, 'g/m²'),
        ('Pure Piña Cloth', 'Piña Loose', 'Liniwan/Washout', 22.000, 'g/m²'),
        ('Knotted Liniwan', 'Piña Loose', 'Liniwan/Washout', 1.400, 'g/g'),
        ('Knotted Bastos', 'Piña Loose', 'Bastos', 1.100, 'g/g');";
mysqli_query($db->conn, $sql);

// Create assigned_tasks table
$sql = "CREATE TABLE IF NOT EXISTS assigned_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prod_line_id INT NOT NULL,
    member_id INT NOT NULL,
    role VARCHAR(50) NOT NULL,
    status ENUM('Pending', 'Accepted', 'Declined', 'In Progress', 'Completed') DEFAULT 'Pending',
    deadline DATE NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prod_line_id) REFERENCES production_line(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
)";

if ($db->conn->query($sql) === TRUE) {
    echo "Table assigned_tasks created successfully<br>";
} else {
    echo "Error creating table: " . $db->conn->error . "<br>";
}

// Create task_progress table
$sql = "DROP TABLE IF EXISTS task_progress;
CREATE TABLE task_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assigned_task_id INT NOT NULL,
    progress_note TEXT,
    completion_percentage INT DEFAULT 0,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_task_id) REFERENCES assigned_tasks(id) ON DELETE CASCADE
)";

if ($db->conn->query($sql) === TRUE) {
    echo "Table task_progress created successfully<br>";
} else {
    echo "Error creating table: " . $db->conn->error . "<br>";
}

// Create task_requests table
$sql = "CREATE TABLE IF NOT EXISTS task_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    product_type VARCHAR(100) NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_date TIMESTAMP NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (member_id) REFERENCES user_member(id) ON DELETE CASCADE
)";

if ($db->conn->query($sql) === TRUE) {
    echo "Table task_requests created successfully<br>";
} else {
    echo "Error creating table: " . $db->conn->error . "<br>";
}
?> 