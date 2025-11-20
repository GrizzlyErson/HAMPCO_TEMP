<?php
require_once "../class.php";

header('Content-Type: application/json');

try {
    $db = new global_class();
    $action = isset($_GET['action']) ? $_GET['action'] : 'get';
    
    if ($action === 'get') {
        // Return empty notifications array - no notifications table yet
        echo json_encode([
            'success' => true,
            'notifications' => []
        ]);
    } elseif ($action === 'mark-read') {
        // Handle marking notifications as read
        $data = json_decode(file_get_contents('php://input'), true);
        
        // For now, just return success as there's no notifications table
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
