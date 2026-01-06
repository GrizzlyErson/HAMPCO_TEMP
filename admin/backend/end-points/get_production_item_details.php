<?php
session_start();
header('Content-Type: application/json');

define('ALLOW_ACCESS', true);
require_once dirname(__DIR__, 3) . '/function/db_connect.php'; // Adjust path as necessary
require_once '../class.php'; // Adjust path as necessary

$response = ["success" => false, "message" => "Unknown error.", "data" => null];

// Check if user is authenticated and authorized (e.g., admin)
// This is a placeholder, implement actual authentication/authorization
if (!isset($_SESSION['id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    $response["message"] = "Unauthorized access.";
    echo json_encode($response);
    exit();
}

if (!isset($_GET['prod_line_id'])) {
    $response["message"] = "Missing production line ID.";
    echo json_encode($response);
    exit();
}

$prod_line_id = intval($_GET['prod_line_id']);

if ($prod_line_id <= 0) {
    $response["message"] = "Invalid production line ID.";
    echo json_encode($response);
    exit();
}

try {
    $db = new global_class();
    $conn = $db->conn;

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    $stmt = $conn->prepare("SELECT * FROM production_line WHERE prod_line_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $prod_line_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response["success"] = true;
        $response["data"] = $result->fetch_assoc();
        $response["message"] = "Production item details fetched successfully.";
    } else {
        $response["message"] = "Production item not found.";
    }

    $stmt->close();
} catch (Exception $e) {
    $response["message"] = "Error: " . $e->getMessage();
    error_log("Error in get_production_item_details.php: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
?>