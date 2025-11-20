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

            $result = $db->AddRawMaterials($raw_materials_name, $category, $rm_qty, $rm_unit, $rm_status);
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

            // Debug log for category
            error_log("Updating raw material - Category details: " . json_encode([
                'raw_post_category' => $_POST['category'],
                'trimmed_category' => $category,
                'material_name' => $raw_materials_name,
                'is_silk' => $raw_materials_name === 'Silk'
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
                'supplier' => $supplier_name
            ]));

            $result = $db->UpdateRawMaterials($rm_id, $raw_materials_name, $category, $rm_quantity, $rm_unit, $rm_status, $supplier_name);
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