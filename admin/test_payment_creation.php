<?php
/**
 * Test script to verify payment records are created when confirming task completion
 * This script simulates the AJAX call from the admin dashboard
 */

// Start session and check authentication
session_start();
require_once '../../function/connection.php';

// Create a simple database connection for testing
$db = new mysqli($host, $username, $password, $dbname);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "<h2>Payment Record Creation Test</h2>";

// Get a pending task assignment to test with
$get_task = $db->query("
    SELECT ta.prod_line_id, ta.member_id, pl.product_name, pl.weight_g, ta.status
    FROM task_assignments ta
    JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
    WHERE ta.status = 'submitted'
    LIMIT 1
");

if ($get_task && $get_task->num_rows > 0) {
    $task = $get_task->fetch_assoc();
    echo "<p><strong>Found test task:</strong></p>";
    echo "<ul>";
    echo "<li>Production ID: " . htmlspecialchars($task['prod_line_id']) . "</li>";
    echo "<li>Member ID: " . htmlspecialchars($task['member_id']) . "</li>";
    echo "<li>Product: " . htmlspecialchars($task['product_name']) . "</li>";
    echo "<li>Weight: " . htmlspecialchars($task['weight_g']) . "g</li>";
    echo "<li>Current Status: " . htmlspecialchars($task['status']) . "</li>";
    echo "</ul>";

    // Check current payment records for this task
    echo "<p><strong>Before confirmation:</strong></p>";
    $check_before = $db->query("
        SELECT COUNT(*) as count FROM payment_records 
        WHERE production_id = '" . $db->escape_string($task['prod_line_id']) . "' 
        AND member_id = " . intval($task['member_id'])
    );
    $before_count = $check_before->fetch_assoc();
    echo "<p>Payment records: " . $before_count['count'] . "</p>";

    // Test the endpoint directly
    echo "<p><strong>Testing endpoint:</strong></p>";
    
    $_POST['production_id'] = $task['prod_line_id'];
    $_SESSION['id'] = 1; // Assume admin user
    $_SESSION['user_type'] = 'admin';

    // Include and run the endpoint
    ob_start();
    include 'backend/end-points/confirm_task_completion.php';
    $response = ob_get_clean();
    
    echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

    // Check payment records after confirmation
    echo "<p><strong>After confirmation:</strong></p>";
    $check_after = $db->query("
        SELECT COUNT(*) as count FROM payment_records 
        WHERE production_id = '" . $db->escape_string($task['prod_line_id']) . "' 
        AND member_id = " . intval($task['member_id'])
    );
    $after_count = $check_after->fetch_assoc();
    echo "<p>Payment records: " . $after_count['count'] . "</p>";

    if ($after_count['count'] > $before_count['count']) {
        echo "<p style='color: green;'><strong>✓ SUCCESS: Payment record was created!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ FAILED: No new payment record was created</strong></p>";
    }

} else {
    echo "<p style='color: orange;'>No pending task assignments found to test with. Please ensure there are tasks with 'submitted' status.</p>";
}

$db->close();
?>
