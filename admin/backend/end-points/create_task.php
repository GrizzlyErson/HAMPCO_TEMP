<?php
require_once '../class.php';
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$db = new global_class();
$conn = $db->conn;

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $length = $_POST['length'] ?? null;
    $width = $_POST['width'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    $knotter_ids = $_POST['knotter_id'] ?? [];
    $warper_id = $_POST['warper_id'] ?? null;
    $weaver_id = $_POST['weaver_id'] ?? null;
    
    $knotter_deadline = $_POST['deadline'] ?? null;
    $warper_deadline = $_POST['warper_deadline'] ?? null;
    $weaver_deadline = $_POST['weaver_deadline'] ?? null;

    $conn->begin_transaction();

    try {
        $sql = "INSERT INTO production_line (product_name, length_m, width_m, weight_g, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdddi', $product_name, $length, $width, $weight, $quantity);
        $stmt->execute();
        $prod_line_id = $conn->insert_id;

        $assignments = [];
        if (!empty($knotter_ids[0]) && !empty($knotter_deadline)) {
            $assignments[] = ['member_id' => $knotter_ids[0], 'role' => 'knotter', 'deadline' => $knotter_deadline];
        }
        if (!empty($warper_id) && !empty($warper_deadline)) {
            $assignments[] = ['member_id' => $warper_id, 'role' => 'warper', 'deadline' => $warper_deadline];
        }
        if (!empty($weaver_id) && !empty($weaver_deadline)) {
            $assignments[] = ['member_id' => $weaver_id, 'role' => 'weaver', 'deadline' => $weaver_deadline];
        }

        if (!empty($assignments)) {
            $sql = "INSERT INTO task_assignments (prod_line_id, member_id, role, status, deadline) VALUES (?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);

            foreach ($assignments as $assignment) {
                $stmt->bind_param('iiss', $prod_line_id, $assignment['member_id'], $assignment['role'], $assignment['deadline']);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Task created successfully!';

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }

    echo json_encode($response);
}
?>