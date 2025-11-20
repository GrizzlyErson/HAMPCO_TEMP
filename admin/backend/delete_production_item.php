<?php
require_once 'class.php';
$db = new global_class();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    try {
        // Validate input
        if (!isset($_POST['prod_line_id']) || empty($_POST['prod_line_id'])) {
            throw new Exception("Production line ID is required");
        }

        $prod_line_id = intval($_POST['prod_line_id']);

        // Delete the production line item
        $delete_prod = "DELETE FROM production_line WHERE prod_line_id = ?";
        $stmt_prod = $db->conn->prepare($delete_prod);
        
        if (!$stmt_prod) {
            throw new Exception("Error preparing deletion statement: " . $db->conn->error);
        }

        $stmt_prod->bind_param("i", $prod_line_id);
        
        if (!$stmt_prod->execute()) {
            throw new Exception("Error deleting production item: " . $stmt_prod->error);
        }

        if ($stmt_prod->affected_rows === 0) {
            throw new Exception("Production item not found");
        }
        
        $stmt_prod->close();

        $response['success'] = true;
        $response['message'] = 'Production item deleted successfully';

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 