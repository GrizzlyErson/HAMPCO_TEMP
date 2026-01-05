<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Define a base path for includes
define('BASE_PATH', realpath(__DIR__ . '/../../../'));

require_once BASE_PATH . '/admin/backend/dbconnect.php';

$response = ['success' => false, 'message' => '', 'materials' => []];
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;

// Check if the database connection is valid
if (!isset($db) || !$db->conn) {
    $response['message'] = "Database connection failed.";
    echo json_encode($response);
    exit;
}

$conn = $db->conn;

try {
    if ($product_name) {
        // Fetch required materials for the given product
        $stmt = $conn->prepare("SELECT material_type, material_name FROM product_materials WHERE product_name = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $product_name);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $required_materials_result = $stmt->get_result();
        $required_materials = [];
        while ($row = $required_materials_result->fetch_assoc()) {
            $required_materials[] = $row;
        }
        $stmt->close();

        if (empty($required_materials)) {
            $response['message'] = "No material requirements found for this product.";
            $response['success'] = true;
            echo json_encode($response);
            exit;
        }

        foreach ($required_materials as $req_material) {
            $material_type = $req_material['material_type'];
            $material_name = $req_material['material_name'];
            $available_quantity = 0;
            $unit = '';

            if ($material_type == 'raw') {
                $stmt = $conn->prepare("SELECT SUM(rm_quantity) as total_stocks FROM raw_materials WHERE raw_materials_name = ? AND rm_status = 'Available'");
                if (!$stmt) {
                    throw new Exception("Prepare failed for raw materials: " . $conn->error);
                }
                $stmt->bind_param("s", $material_name);
                $stmt->execute();
                $raw_material_result = $stmt->get_result();
                if ($raw_material_result && $row = $raw_material_result->fetch_assoc()) {
                    $available_quantity = (int)($row['total_stocks'] ?? 0);
                    $unit = 'unit(s)'; // Assuming raw materials are in units
                }
                $stmt->close();
            } else if ($material_type == 'processed') {
                $stmt = $conn->prepare("SELECT SUM(weight) as total_weight FROM processed_materials WHERE processed_materials_name = ? AND status = 'Available'");
                if (!$stmt) {
                    throw new Exception("Prepare failed for processed materials: " . $conn->error);
                }
                $stmt->bind_param("s", $material_name);
                $stmt->execute();
                $processed_material_result = $stmt->get_result();
                if ($processed_material_result && $row = $processed_material_result->fetch_assoc()) {
                    $available_quantity = (int)($row['total_weight'] ?? 0);
                    $unit = 'g'; // Assuming processed materials are in grams
                }
                $stmt->close();
            }

            $response['materials'][] = [
                'name' => $material_name,
                'category' => $material_type,
                'available_quantity' => $available_quantity,
                'unit' => $unit,
                'required' => true // Mark as required for the product
            ];
        }
    } else {
        // Original behavior: fetch all available raw and processed materials
        // Get raw materials
        $raw_sql = "SELECT raw_materials_name as name, SUM(rm_quantity) as available_quantity FROM raw_materials WHERE rm_status = 'Available' GROUP BY raw_materials_name";
        $raw_result = $conn->query($raw_sql);
        if ($raw_result) {
            while ($row = $raw_result->fetch_assoc()) {
                $response['materials'][] = ['name' => $row['name'], 'category' => 'raw', 'available_quantity' => $row['available_quantity'], 'unit' => 'unit(s)'];
            }
        }

        // Get processed materials
        $processed_sql = "SELECT processed_materials_name as name, SUM(weight) as available_quantity FROM processed_materials WHERE status = 'Available' GROUP BY processed_materials_name";
        $processed_result = $conn->query($processed_sql);
        if ($processed_result) {
            while ($row = $processed_result->fetch_assoc()) {
                $response['materials'][] = ['name' => $row['name'], 'category' => 'processed', 'available_quantity' => $row['available_quantity'], 'unit' => 'g'];
            }
        }
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
    $response['success'] = false;
}

echo json_encode($response);
?>