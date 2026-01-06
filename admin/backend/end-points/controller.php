<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../class.php');

$db = new global_class();

if (!isset($_POST['requestType'])) {
    echo json_encode(['status' => 'error', 'message' => 'Request type not specified']);
    exit;
}

$requestType = $_POST['requestType'];

try {
    switch ($requestType) {
        case 'MemberVerification':
            if (!isset($_POST['actionType']) || !isset($_POST['userId'])) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
                exit;
            }

            $actionType = $_POST['actionType'];
            $userId = (int)$_POST['userId'];

            if ($actionType === 'remove') {
                $result = $db->remove_member($userId);
                $message = 'Member removed successfully';
            } else {
                $result = $db->RegisterMember($actionType, $userId);
                $message = $actionType === 'verify' ? 'Member verified successfully' : 'Member declined successfully';
            }
            
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => $message]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to process member ' . $actionType]);
            }
            break;

        case 'AddRawMaterials':
            $raw_materials_name = $_POST['rm_name'];
            $category = $_POST['category'];
            $rm_qty = $_POST['rm_qty'];
            $rm_unit = 'gram';
            $rm_status = $_POST['rm_status'];
            $unit_cost = isset($_POST['unit_cost']) ? floatval($_POST['unit_cost']) : 0;

            // Debug log
            error_log("Adding raw material: " . json_encode($_POST));

            // Validate required fields
            if (empty($raw_materials_name) || empty($rm_qty) || empty($rm_status)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Please fill in all required fields'
                ]);
                exit;
            }

            // Validate status
            $valid_statuses = ['Available', 'Not Available'];
            if (!in_array($rm_status, $valid_statuses)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid status value. Must be either "Available" or "Not Available"'
                ]);
                exit;
            }

            // Validate quantity is numeric and positive
            if (!is_numeric($rm_qty) || $rm_qty < 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Quantity must be a valid positive number'
                ]);
                exit;
            }

            $result = $db->AddRawMaterials($raw_materials_name, $category, $rm_qty, $rm_unit, $rm_status, $unit_cost);
            error_log("Add raw material result: " . json_encode($result));
            echo json_encode($result);
            break;

        case 'UpdateRawMaterials':
            $rm_id = $_POST['rm_id'];
            $raw_materials_name = trim($_POST['rm_name']);
            $category = trim($_POST['category']);
            $rm_quantity = trim($_POST['rm_quantity']);
            $rm_unit = 'gram';
            $rm_status = trim($_POST['rm_status']);
            $supplier_name = isset($_POST['supplier_name']) ? trim($_POST['supplier_name']) : '';
            $unit_cost = isset($_POST['unit_cost']) ? floatval($_POST['unit_cost']) : 0;

            // Debug log for category
            error_log("Updating raw material - Category details: " . json_encode([
                'raw_post_category' => $_POST['category'],
                'trimmed_category' => $category,
                'material_name' => $raw_materials_name,
                'is_silk' => $raw_materials_name === 'Silk',
                'unit_cost' => $unit_cost
            ]));

            // Validate required fields
            if (empty($rm_id) || empty($raw_materials_name) || empty($rm_quantity) || empty($rm_status)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'All required fields must be filled'
                ]);
                exit;
            }

            // Validate status
            $valid_statuses = ['Available', 'Not Available'];
            if (!in_array($rm_status, $valid_statuses)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid status value. Must be either "Available" or "Not Available"'
                ]);
                exit;
            }

            // Validate quantity is numeric and positive
            if (!is_numeric($rm_quantity) || $rm_quantity < 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Quantity must be a valid positive number'
                ]);
                exit;
            }

            // Debug log before update
            error_log("About to update raw material with data: " . json_encode([
                'id' => $rm_id,
                'name' => $raw_materials_name,
                'category' => $category,
                'quantity' => $rm_quantity,
                'unit' => $rm_unit,
                'status' => $rm_status,
                'supplier' => $supplier_name,
                'unit_cost' => $unit_cost
            ]));

            $result = $db->UpdateRawMaterials($rm_id, $raw_materials_name, $category, $rm_quantity, $rm_unit, $rm_status, $supplier_name, $unit_cost);
            error_log("Update raw material result: " . json_encode($result));
            echo json_encode($result);
            break;

        case 'DeleteRawMaterials':
            $rm_id = $_POST['rm_id'];
            
            if (!is_numeric($rm_id)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid raw material ID'
                ]);
                exit;
            }
            
            $result = $db->delete_raw_material($rm_id);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Raw material deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete raw material',
                    'details' => [
                        'php_error' => error_get_last() ? error_get_last()['message'] : null
                    ]
                ]);
            }
            break;

        case 'UpdateProcessedMaterial':
            $processed_id = $_POST['processed_id'];
            $processed_unit_cost = isset($_POST['processed_unit_cost']) ? floatval($_POST['processed_unit_cost']) : 0;

            if (empty($processed_id)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Processed material ID is required'
                ]);
                exit;
            }

            if (!is_numeric($processed_unit_cost) || $processed_unit_cost < 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Unit cost must be a valid positive number'
                ]);
                exit;
            }

            try {
                $query = $db->conn->prepare("
                    UPDATE processed_materials 
                    SET unit_cost = ?
                    WHERE id = ?
                ");
                
                if (!$query) {
                    throw new Exception("Prepare failed: " . $db->conn->error);
                }
                
                $query->bind_param("di", $processed_unit_cost, $processed_id);
                $result = $query->execute();
                
                if ($result) {
                    $query->close();
                    echo json_encode(['status' => 'success', 'message' => 'Processed material unit cost updated successfully']);
                } else {
                    throw new Exception("Execute failed: " . $query->error);
                }
            } catch (Exception $e) {
                error_log("Error in UpdateProcessedMaterial: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Failed to update processed material: ' . $e->getMessage()]);
            }
            break;

        case 'DeleteProcessedMaterial':
            $processed_id = $_POST['processed_id'];
            
            if (!is_numeric($processed_id)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid processed material ID'
                ]);
                exit;
            }
            
            try {
                $query = $db->conn->prepare("DELETE FROM processed_materials WHERE id = ?");
                if (!$query) {
                    throw new Exception("Prepare failed: " . $db->conn->error);
                }
                
                $query->bind_param("i", $processed_id);
                $result = $query->execute();
                
                if ($result) {
                    $query->close();
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Processed material deleted successfully'
                    ]);
                } else {
                    throw new Exception("Execute failed: " . $query->error);
                }
            } catch (Exception $e) {
                error_log("Error in DeleteProcessedMaterial: " . $e->getMessage());
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete processed material: ' . $e->getMessage()
                ]);
            }
            break;

        case 'UpdateFinishedProduct':
            $finished_id = $_POST['finished_id'];
            $finished_unit_cost = isset($_POST['finished_unit_cost']) ? floatval($_POST['finished_unit_cost']) : 0;

            if (empty($finished_id)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Finished product ID is required'
                ]);
                exit;
            }

            if (!is_numeric($finished_unit_cost) || $finished_unit_cost < 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Unit cost must be a valid positive number'
                ]);
                exit;
            }

            try {
                $query = $db->conn->prepare("
                    UPDATE finished_products 
                    SET unit_cost = ?
                    WHERE id = ?
                ");
                
                if (!$query) {
                    throw new Exception("Prepare failed: " . $db->conn->error);
                }
                
                $query->bind_param("di", $finished_unit_cost, $finished_id);
                $result = $query->execute();
                
                if ($result) {
                    $query->close();
                    echo json_encode(['status' => 'success', 'message' => 'Finished product unit cost updated successfully']);
                } else {
                    throw new Exception("Execute failed: " . $query->error);
                }
            } catch (Exception $e) {
                error_log("Error in UpdateFinishedProduct: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Failed to update finished product: ' . $e->getMessage()]);
            }
            break;

        case 'DeleteFinishedProduct':
            $finished_id = $_POST['finished_id'];
            
            if (!is_numeric($finished_id)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid finished product ID'
                ]);
                exit;
            }
            
            try {
                $query = $db->conn->prepare("DELETE FROM finished_products WHERE id = ?");
                if (!$query) {
                    throw new Exception("Prepare failed: " . $db->conn->error);
                }
                
                $query->bind_param("i", $finished_id);
                $result = $query->execute();
                
                if ($result) {
                    $query->close();
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Finished product deleted successfully'
                    ]);
                } else {
                    throw new Exception("Execute failed: " . $query->error);
                }
            } catch (Exception $e) {
                error_log("Error in DeleteFinishedProduct: " . $e->getMessage());
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete finished product: ' . $e->getMessage()
                ]);
            }
            break;

        case 'DeleteProduct':
            $prod_id = isset($_POST['prod_id']) ? intval($_POST['prod_id']) : 0;
            if ($prod_id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid product id']);
                exit;
            }

            $result = $db->DeleteProduct($prod_id);
            if ($result === 'success' || $result === true) {
                echo json_encode(['status' => 'success', 'message' => 'Product removed successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => is_string($result) ? $result : 'Failed to remove product']);
            }
            break;

        case 'AddProduct':
            // Expected fields from form: rm_name, rm_description, rm_price, rm_product_Category, rm_product_image (file)
            $name = isset($_POST['rm_name']) ? trim($_POST['rm_name']) : '';
            $description = isset($_POST['rm_description']) ? trim($_POST['rm_description']) : '';
            $price = isset($_POST['rm_price']) ? $_POST['rm_price'] : 0;
            $category = isset($_POST['rm_product_Category']) ? $_POST['rm_product_Category'] : null;
            $image = isset($_FILES['rm_product_image']) ? $_FILES['rm_product_image'] : null;

            // Basic validation
            if (empty($name) || empty($price) || empty($category)) {
                echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
                exit;
            }

            // Normalize price
            if (!is_numeric($price)) {
                $price = floatval(str_replace(',', '', $price));
            } else {
                $price = floatval($price);
            }

            // Pass default stocks = 0 for new products
            $result = $db->AddProduct($name, $description, $price, $category, $image, 0);
            // Ensure we return JSON with status/message
            echo json_encode($result);
            break;

        case 'UpdateProduct':
            // Expected fields from form: rm_id, rm_name, rm_description, rm_price, rm_product_Category, rm_product_image (file - optional)
            $prod_id = isset($_POST['rm_id']) ? intval($_POST['rm_id']) : 0;
            $name = isset($_POST['rm_name']) ? trim($_POST['rm_name']) : '';
            $description = isset($_POST['rm_description']) ? trim($_POST['rm_description']) : '';
            $price = isset($_POST['rm_price']) ? $_POST['rm_price'] : 0;
            $category = isset($_POST['rm_product_Category']) ? $_POST['rm_product_Category'] : null;
            $image = isset($_FILES['rm_product_image']) && $_FILES['rm_product_image']['size'] > 0 ? $_FILES['rm_product_image'] : null;

            // Basic validation
            if ($prod_id <= 0 || empty($name) || empty($price) || empty($category)) {
                echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
                exit;
            }

            // Normalize price
            if (!is_numeric($price)) {
                $price = floatval(str_replace(',', '', $price));
            } else {
                $price = floatval($price);
            }

            $result = $db->UpdateProduct($prod_id, $name, $description, $price, $category, $image);
            echo json_encode($result);
            break;

        case 'ProdStockin':
            // Handle product stock in
            $prod_id = isset($_POST['prod_id']) ? intval($_POST['prod_id']) : 0;
            $rm_quantity = isset($_POST['rm_quantity']) ? intval($_POST['rm_quantity']) : 0;

            if ($prod_id <= 0 || $rm_quantity <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid product ID or quantity']);
                exit;
            }

            // Update product stocks
            $update_query = "UPDATE product SET prod_stocks = prod_stocks + ? WHERE prod_id = ?";
            $stmt = $db->conn->prepare($update_query);
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $db->conn->error]);
                exit;
            }

            $stmt->bind_param("ii", $rm_quantity, $prod_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Stock updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update stock: ' . $stmt->error]);
            }
            $stmt->close();
            break;

        case 'GetProductMaterials':
            // Get materials for a product
            $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
            
            if (empty($product_name)) {
                echo json_encode(['status' => 'error', 'message' => 'Product name required']);
                exit;
            }

            $query = "SELECT id, product_name, material_type, material_name, material_qty FROM product_materials WHERE product_name = ?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param("s", $product_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $materials = [];

            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }
            $stmt->close();

            echo json_encode(['status' => 'success', 'materials' => $materials]);
            break;

        case 'AddProductMaterial':
            // Add material for a product
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $material_type = isset($_POST['material_type']) ? $_POST['material_type'] : '';
            $material_name = isset($_POST['material_name']) ? $_POST['material_name'] : '';
            $material_qty = isset($_POST['material_qty']) ? floatval($_POST['material_qty']) : 0;

            if ($product_id <= 0 || empty($material_type) || empty($material_name) || $material_qty <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
                exit;
            }

            // Get product name
            $prodQuery = "SELECT prod_name FROM product WHERE prod_id = ?";
            $prodStmt = $db->conn->prepare($prodQuery);
            $prodStmt->bind_param("i", $product_id);
            $prodStmt->execute();
            $prodResult = $prodStmt->get_result();
            $prodRow = $prodResult->fetch_assoc();
            $prodStmt->close();

            if (!$prodRow) {
                echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                exit;
            }

            $product_name = $prodRow['prod_name'];

            // Insert material
            $insert_query = "INSERT INTO product_materials (product_name, material_type, material_name, material_qty) VALUES (?, ?, ?, ?)";
            $stmt = $db->conn->prepare($insert_query);
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $db->conn->error]);
                exit;
            }

            $stmt->bind_param("sssd", $product_name, $material_type, $material_name, $material_qty);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Material added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add material: ' . $stmt->error]);
            }
            $stmt->close();
            break;

        case 'RemoveProductMaterial':
            // Remove material for a product
            $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;

            if ($material_id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid material ID']);
                exit;
            }

            $delete_query = "DELETE FROM product_materials WHERE id = ?";
            $stmt = $db->conn->prepare($delete_query);
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $db->conn->error]);
                exit;
            }

            $stmt->bind_param("i", $material_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Material removed successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Material not found']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove material']);
            }
            $stmt->close();
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid request type']);
            break;
    }
} catch (Exception $e) {
    error_log("Controller error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>