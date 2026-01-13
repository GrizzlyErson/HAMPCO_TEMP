<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ['success' => false, 'message' => '', 'materials' => []];
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;

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
                // Handle mapping for specific material names that imply categories
                $query = "SELECT SUM(rm_quantity) as total_stocks FROM raw_materials WHERE rm_status = 'Available'";
                $params = [];
                $types = "";

                if (stripos($material_name, 'Liniwan') !== false && stripos($material_name, 'Pina') !== false) {
                    $query .= " AND raw_materials_name = 'Piña Loose' AND category = 'Liniwan/Washout'";
                } elseif (stripos($material_name, 'Bastos') !== false && stripos($material_name, 'Pina') !== false) {
                    $query .= " AND raw_materials_name = 'Piña Loose' AND category = 'Bastos'";
                } elseif ($material_name === 'Pina Loose' || $material_name === 'Piña Loose') {
                    $query .= " AND raw_materials_name = 'Piña Loose'";
                } else {
                    $query .= " AND raw_materials_name = ?";
                    $params[] = $material_name;
                    $types .= "s";
                }

                $stmt = $conn->prepare($query);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $raw_material_result = $stmt->get_result();
                if ($raw_material_result && $row = $raw_material_result->fetch_assoc()) {
                    $available_quantity = (float)($row['total_stocks'] ?? 0);
                    $unit = 'g';
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
                    $available_quantity = (float)($row['total_weight'] ?? 0);
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
        $raw_sql = "SELECT raw_materials_name as name, category, SUM(rm_quantity) as available_quantity FROM raw_materials WHERE rm_status = 'Available' GROUP BY raw_materials_name, category";
        $raw_result = $conn->query($raw_sql);
        if ($raw_result) {
            while ($row = $raw_result->fetch_assoc()) {
                $name = $row['name'];
                if (!empty($row['category'])) {
                    $name .= " (" . $row['category'] . ")";
                }
                $response['materials'][] = ['name' => $name, 'category' => 'raw', 'available_quantity' => (float)$row['available_quantity'], 'unit' => 'g'];
            }
        }

        // Get processed materials
        $processed_sql = "SELECT processed_materials_name as name, SUM(weight) as available_quantity FROM processed_materials WHERE status = 'Available' GROUP BY processed_materials_name";
        $processed_result = $conn->query($processed_sql);
        if ($processed_result) {
            while ($row = $processed_result->fetch_assoc()) {
                $response['materials'][] = ['name' => $row['name'], 'category' => 'processed', 'available_quantity' => (float)$row['available_quantity'], 'unit' => 'g'];
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