<?php
session_start();
header('Content-Type: application/json');

require_once "../dbconnect.php";

try {
    // Check if user is logged in
    if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }

    // Ensure the decline_reason and decline_status columns exist
    $alterTableQuery = "
        ALTER TABLE task_assignments 
        ADD COLUMN IF NOT EXISTS decline_reason TEXT NULL,
        ADD COLUMN IF NOT EXISTS decline_status VARCHAR(20) NULL DEFAULT NULL
    ";
    $db->conn->query($alterTableQuery);

    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    switch ($action) {
        case 'list':
        default:
            $statusFilter = $_GET['status'] ?? 'all';
            
            // Get declined tasks from task_assignments table
            $query = "
                SELECT 
                    ta.id,
                    ta.prod_line_id,
                    CONCAT('PL', LPAD(ta.prod_line_id, 4, '0')) AS production_code,
                    pl.product_name,
                    um.fullname AS member_name,
                    um.member_role,
                    ta.decline_reason AS member_reason,
                    ta.updated_at AS declined_at
                FROM task_assignments ta
                LEFT JOIN production_line pl ON ta.prod_line_id = pl.prod_line_id
                LEFT JOIN user_member um ON ta.member_id = um.id
                WHERE ta.status = 'declined'
            ";

            if ($statusFilter === 'pending') {
                $query .= " AND ta.decline_status IS NULL ";
            } elseif ($statusFilter === 'cleared') {
                $query .= " AND ta.decline_status = 'cleared' ";
            }

            $query .= " ORDER BY ta.updated_at DESC LIMIT 50";

            $stmt = $db->conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare query: ' . $db->conn->error);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $declines = [];
            
            while ($row = $result->fetch_assoc()) {
                $declines[] = $row;
            }
            
            $stmt->close();

            echo json_encode([
                'success' => true,
                'declines' => $declines,
                'count' => count($declines)
            ]);
            break;

        case 'clear_all':
            // Mark all declined notifications as cleared
            $updateQuery = "UPDATE task_assignments SET decline_status = 'cleared' WHERE status = 'declined' AND decline_status IS NULL";
            $stmt = $db->conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception('Failed to prepare update query: ' . $db->conn->error);
            }
            
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => 'Declined notifications cleared',
                'affected' => $affected
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    error_log('task_declines.php error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

