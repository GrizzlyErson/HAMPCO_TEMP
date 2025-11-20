<?php
// Prevent any output before JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Define constant to allow access to db_connect.php
define('ALLOW_ACCESS', true);

// Get the absolute path to the database connection file
$db_path = __DIR__ . '/../../../function/db_connect.php';
if (!file_exists($db_path)) {
    error_log("Database connection file not found at: " . $db_path);
    echo json_encode([
        "success" => false,
        "message" => "Database configuration error: Connection file not found"
    ]);
    exit;
}

require_once $db_path;

$response = ["success" => false, "message" => "Unknown error."];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
        $length = isset($_POST['length']) && $_POST['length'] !== '' ? floatval($_POST['length']) : 0;
        $width = isset($_POST['width']) && $_POST['width'] !== '' ? floatval($_POST['width']) : 0;
        $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        $status = 'pending';

        if ($product_name === '' || !$quantity) {
            $response['message'] = 'Product name and quantity are required.';
            echo json_encode($response);
            exit;
        }

        // Set fields based on product type
        if (in_array($product_name, ['Piña Seda', 'Pure Piña Cloth'])) {
            // Require length and width, weight is 0
            if ($length <= 0 || $width <= 0) {
                $response['message'] = 'Length and width are required for this product.';
                echo json_encode($response);
                exit;
            }
            $weight = 0;
        } else if (in_array($product_name, ['Knotted Liniwan', 'Knotted Bastos', 'Warped Silk'])) {
            // Require weight, length and width are 0
            if ($weight <= 0) {
                $response['message'] = 'Weight is required for this product.';
                echo json_encode($response);
                exit;
            }
            $length = 0;
            $width = 0;
        }

        // Check if database connection is successful
        if (!$conn) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        $sql = "INSERT INTO production_line (product_name, length_m, width_m, weight_g, quantity, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param('sdddis', $product_name, $length, $width, $weight, $quantity, $status);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $response['success'] = true;
        $response['message'] = 'Production item created successfully!';
        $stmt->close();
        $conn->close();
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (Exception $e) {
    error_log("Production item creation error: " . $e->getMessage());
    $response['message'] = 'An error occurred while processing your request: ' . $e->getMessage();
    
    // Close connections if they exist
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }
}

echo json_encode($response); 