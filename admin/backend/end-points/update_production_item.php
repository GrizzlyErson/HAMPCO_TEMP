<?php
session_start();
header('Content-Type: application/json');

define('ALLOW_ACCESS', true);
require_once dirname(__DIR__, 3) . '/function/db_connect.php'; // Adjust path as necessary
require_once '../class.php'; // Adjust path as necessary

$response = ["success" => false, "message" => "Unknown error."];

// Check if user is authenticated and authorized (e.g., admin)
// This is a placeholder, implement actual authentication/authorization
if (!isset($_SESSION['id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    $response["message"] = "Unauthorized access.";
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response["message"] = "Invalid request method.";
    echo json_encode($response);
    exit();
}

$prod_line_id = intval($_POST['prod_line_id'] ?? 0);
$product_name = trim($_POST['product_name'] ?? '');
$length_m = floatval($_POST['length'] ?? 0);
$width_m = floatval($_POST['width'] ?? 0);
$weight_g = floatval($_POST['weight'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);

if ($prod_line_id <= 0 || empty($product_name)) {
    $response["message"] = "Missing required parameters (Production ID, Product Name).";
    echo json_encode($response);
    exit();
}

try {
    $db = new global_class();
    $conn = $db->conn;

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Determine which fields to update based on product type
    $update_fields = [];
    $bind_types = "";
    $bind_values = [];

    $isDimensionsProduct = in_array($product_name, ['Piña Seda', 'Pure Piña Cloth']);
    $isWeightProduct = in_array($product_name, ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk']);

    $update_fields[] = "product_name = ?";
    $bind_types .= "s";
    $bind_values[] = $product_name;

    if ($isDimensionsProduct) {
        $update_fields[] = "length_m = ?";
        $bind_types .= "d";
        $bind_values[] = $length_m;

        $update_fields[] = "width_m = ?";
        $bind_types .= "d";
        $bind_values[] = $width_m;

        $update_fields[] = "weight_g = 0"; // Clear weight for dimension products
        $update_fields[] = "quantity = ?";
        $bind_types .= "i";
        $bind_values[] = $quantity;
    } elseif ($isWeightProduct) {
        $update_fields[] = "length_m = 0"; // Clear length for weight products
        $update_fields[] = "width_m = 0";  // Clear width for weight products
        $update_fields[] = "weight_g = ?";
        $bind_types .= "d";
        $bind_values[] = $weight_g;
        $update_fields[] = "quantity = ?"; // Always 1 for these
        $bind_types .= "i";
        $bind_values[] = 1;
    } else { // Generic product, prioritize quantity, clear dimensions/weight if not applicable
        $update_fields[] = "length_m = 0";
        $update_fields[] = "width_m = 0";
        $update_fields[] = "weight_g = 0";
        $update_fields[] = "quantity = ?";
        $bind_types .= "i";
        $bind_values[] = $quantity;
    }

    $update_fields[] = "updated_at = NOW()";

    $sql = "UPDATE production_line SET " . implode(', ', $update_fields) . " WHERE prod_line_id = ?";
    $bind_types .= "i";
    $bind_values[] = $prod_line_id;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param($bind_types, ...$bind_values);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response["success"] = true;
            $response["message"] = "Production item updated successfully.";
        } else {
            $response["message"] = "No changes detected or item not found.";
        }
    } else {
        throw new Exception("Failed to update production item: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    $response["message"] = "Error: " . $e->getMessage();
    error_log("Error in update_production_item.php: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
?>