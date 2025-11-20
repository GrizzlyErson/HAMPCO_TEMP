<?php
require_once '../class.php';
$db = new global_class();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['payment_id'])) {
            throw new Exception('Payment ID is required');
        }

        $payment_id = intval($data['payment_id']);

        // Start transaction
        $db->conn->begin_transaction();

        // Update payment status and date
        $update_query = "UPDATE payment_records 
                        SET payment_status = 'Paid',
                            date_paid = NOW() 
                        WHERE id = ? AND payment_status = 'Pending'";
        
        $stmt = $db->conn->prepare($update_query);
        $stmt->bind_param('i', $payment_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update payment status');
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception('Payment record not found or already processed');
        }

        // Commit transaction
        $db->conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($db->conn->inTransaction()) {
            $db->conn->rollback();
        }

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>