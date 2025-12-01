<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ['success' => false, 'message' => '', 'materials' => []];
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;

try {
    // 1. Fetch all available raw materials (individual entries)
    $raw_materials_query = "SELECT id, raw_materials_name as name, 'raw' as type, rm_quantity as available_quantity, category FROM raw_materials WHERE rm_status = 'Available'";
    $raw_materials_result = $conn->query($raw_materials_query);
    $all_raw_materials = [];
    if ($raw_materials_result) {
        while ($row = $raw_materials_result->fetch_assoc()) {
            $all_raw_materials[] = $row;
        }
    }

    // 2. Fetch all available processed materials (individual entries)
    $processed_materials_query = "SELECT id, processed_materials_name as name, 'processed' as type, weight as available_quantity FROM processed_materials WHERE status = 'Available'";
    $processed_materials_result = $conn->query($processed_materials_query);
    $all_processed_materials = [];
    if ($processed_materials_result) {
        while ($row = $processed_materials_result->fetch_assoc()) {
            $all_processed_materials[] = $row;
        }
    }

    // Combine all materials for initial filtering
    $all_materials = array_merge($all_raw_materials, $all_processed_materials);
    $filtered_materials = [];

    if ($product_name) {
        // Get materials required for the specific product from product_materials table
        $stmt = $conn->prepare("SELECT material_type, material_name FROM product_materials WHERE product_name = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $required_materials_result = $stmt->get_result();
        $required_materials_for_product = [];
        while ($row = $required_materials_result->fetch_assoc()) {
            $required_materials_for_product[] = $row;
        }
        $stmt->close();

        $product_material_names = array_column($required_materials_for_product, 'material_name');
        
        foreach ($all_materials as $material) {
            $include_material = false;

            // Check if the material is a required input for the product
            if (in_array($material['name'], $product_material_names)) {
                // Further check for matching type if there are same-named raw/processed materials
                foreach ($required_materials_for_product as $req_mat) {
                    if ($material['name'] === $req_mat['material_name'] && $material['type'] === $req_mat['material_type']) {
                        $include_material = true;
                        break;
                    }
                }
            }
            
            // Also include the product itself if it's a processed intermediate/final product
            // This is crucial if we expect to see, for example, "Knotted Liniwan" itself
            // as an available material *if* it's already processed and in stock.
            if ($material['name'] === $product_name && $material['type'] === 'processed') {
                $include_material = true;
            }

            if ($include_material) {
                $unit = ($material['type'] === 'raw') ? 'unit(s)' : 'g'; // Assuming processed materials are in grams
                $filtered_materials[] = [
                    'id' => $material['id'], // Include the material ID
                    'name' => $material['name'],
                    'category' => $material['type'], // Using 'type' as 'category' for consistency
                    'sub_category' => $material['category'] ?? null, // For Piña Loose specific categories
                    'available_quantity' => $material['available_quantity'],
                    'unit' => $unit
                ];
            }
        }
        // Remove duplicates and re-index
        $response['materials'] = array_values(array_map("unserialize", array_unique(array_map("serialize", $filtered_materials))));

    } else {
        // If no product name, return all available raw and processed materials
        foreach ($all_materials as $material) {
            $unit = ($material['type'] === 'raw') ? 'unit(s)' : 'g';
            $response['materials'][] = [
                'id' => $material['id'],
                'name' => $material['name'],
                'category' => $material['type'],
                'sub_category' => $material['category'] ?? null,
                'available_quantity' => $material['available_quantity'],
                'unit' => $unit
            ];
        }
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>