<?php
header('Content-Type: application/json');
define('ALLOW_ACCESS', true);
require_once '../../../function/db_connect.php';

$response = ['success' => false, 'message' => '', 'materials' => []];
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null; // Get the role parameter

try {
    if ($product_name) {
        $filtered_materials = [];

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

        // 3. Apply role-specific filtering
        foreach ($all_materials as $material) {
            $include_material = false;

            if ($role === 'knotter') {
                // Knotter: interested in Piña Loose (raw input)
                if ($material['name'] === 'Piña Loose' && $material['type'] === 'raw') {
                    $include_material = true;
                }
                // Also interested in their output product if it's what they are creating
                if (($product_name === 'Knotted Liniwan' && $material['name'] === 'Knotted Liniwan' && $material['type'] === 'processed') ||
                    ($product_name === 'Knotted Bastos' && $material['name'] === 'Knotted Bastos' && $material['type'] === 'processed')) {
                    $include_material = true;
                }
            } elseif ($role === 'weaver') {
                // Weaver: broader range, includes raw (Piña Loose, Silk) and processed (Knotted Liniwan, Knotted Bastos, Warped Silk)
                // The user specified "Piña Loose (raw)", "Piña Seda (processed)", "Silk (raw)"
                // But also for "Pina Seda" product, they need "Knotted Bastos", "Warped Silk" as per RawMaterialCalculator
                // And for "Pure Pina Cloth" product, they need "Knotted Liniwan" as per RawMaterialCalculator
                
                // For simplicity, let's include all raw materials mentioned in user's example for weaver
                if (($material['name'] === 'Piña Loose' && $material['type'] === 'raw') ||
                    ($material['name'] === 'Silk' && $material['type'] === 'raw')) {
                    $include_material = true;
                }
                // Include processed intermediate products that weavers might use as inputs
                if (($material['name'] === 'Knotted Bastos' && $material['type'] === 'processed') ||
                    ($material['name'] === 'Knotted Liniwan' && $material['type'] === 'processed') ||
                    ($material['name'] === 'Warped Silk' && $material['type'] === 'processed')) {
                    $include_material = true;
                }
                // If the product itself is processed (like Piña Seda), it's also relevant
                if (($product_name === 'Piña Seda' && $material['name'] === 'Piña Seda' && $material['type'] === 'processed') ||
                    ($product_name === 'Pure Piña Cloth' && $material['name'] === 'Pure Piña Cloth' && $material['type'] === 'processed')) {
                    $include_material = true;
                }
            } elseif ($role === 'warper') {
                // Warper: interested in Silk (raw input)
                if ($material['name'] === 'Silk' && $material['type'] === 'raw') {
                    $include_material = true;
                }
                // Also interested in their output product if it's what they are creating
                if ($product_name === 'Warped Silk' && $material['name'] === 'Warped Silk' && $material['type'] === 'processed') {
                    $include_material = true;
                }
            } else {
                // Default: if no role or role not specifically handled, show all materials relevant to the product
                // This part is complex because product_materials only lists direct inputs.
                // For now, let's default to showing all materials which means we need the product_materials table again.
                $stmt = $conn->prepare("SELECT material_type, material_name FROM product_materials WHERE product_name = ?");
                $stmt->bind_param("s", $product_name);
                $stmt->execute();
                $required_materials_for_product = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                foreach ($required_materials_for_product as $req_mat) {
                    if ($material['name'] === $req_mat['material_name'] && $material['type'] === $req_mat['material_type']) {
                        $include_material = true;
                        break;
                    }
                }
                // Also include the product itself if it's a processed material/intermediate
                if (($product_name === 'Knotted Liniwan' || $product_name === 'Knotted Bastos' || $product_name === 'Warped Silk' ||
                     $product_name === 'Piña Seda' || $product_name === 'Pure Piña Cloth') &&
                    $material['name'] === $product_name && $material['type'] === 'processed') {
                    $include_material = true;
                }
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
        $response['materials'] = array_values(array_unique($filtered_materials, SORT_REGULAR)); // Remove duplicates

    } else {
        // Fetch all available raw and processed materials (individual entries)
        $raw_sql = "SELECT id, raw_materials_name as name, 'raw' as type, rm_quantity as available_quantity, category FROM raw_materials WHERE rm_status = 'Available'";
        $raw_result = $conn->query($raw_sql);
        if ($raw_result) {
            while ($row = $raw_result->fetch_assoc()) {
                $response['materials'][] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'category' => 'raw',
                    'sub_category' => $row['category'] ?? null,
                    'available_quantity' => $row['available_quantity'],
                    'unit' => 'unit(s)'
                ];
            }
        }

        $processed_sql = "SELECT id, processed_materials_name as name, 'processed' as type, weight as available_quantity FROM processed_materials WHERE status = 'Available'";
        $processed_result = $conn->query($processed_sql);
        if ($processed_result) {
            while ($row = $processed_result->fetch_assoc()) {
                $response['materials'][] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'category' => 'processed',
                    'sub_category' => null,
                    'available_quantity' => $row['available_quantity'],
                    'unit' => 'g'
                ];
            }
        }
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>