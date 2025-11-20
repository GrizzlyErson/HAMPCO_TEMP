<?php
require_once "class.php";

$db = new global_class();

try {
    // Create sql directory if it doesn't exist
    if (!file_exists(__DIR__ . '/sql')) {
        mkdir(__DIR__ . '/sql', 0777, true);
    }

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/sql/create_task_assignments.sql');
    
    // Execute the SQL
    if ($db->conn->multi_query($sql)) {
        echo "Task assignments table created successfully\n";
    } else {
        throw new Exception("Error creating table: " . $db->conn->error);
    }

    // Clear any remaining results
    while ($db->conn->more_results() && $db->conn->next_result());

    // Create task_assignments table
    $sql = "CREATE TABLE IF NOT EXISTS `task_assignments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `prod_line_id` int(11) NOT NULL,
        `member_id` int(11) NOT NULL,
        `role` varchar(20) NOT NULL,
        `status` varchar(20) NOT NULL DEFAULT 'pending',
        `estimated_time` int NOT NULL COMMENT 'Estimated time in days',
        `deadline` date NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `prod_line_id` (`prod_line_id`),
        KEY `member_id` (`member_id`),
        CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`prod_line_id`) REFERENCES `production_line` (`prod_line_id`) ON DELETE CASCADE,
        CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if ($db->conn->query($sql) === TRUE) {
        echo "Task assignments table created successfully\n";
    } else {
        throw new Exception("Error creating task_assignments table: " . $db->conn->error);
    }

    // Clear any remaining results
    while ($db->conn->more_results() && $db->conn->next_result());

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 