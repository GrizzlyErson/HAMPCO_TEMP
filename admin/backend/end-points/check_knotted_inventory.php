<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";

header('Content-Type: application/json');
$response = array();

try {
    $product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;
    $required_qty = isset($_GET['required_qty']) ? floatval($_GET['required_qty']) : 0;

    if (!$product_name || $required_qty <= 0) {
        throw new Exception('Missing or invalid parameters');
    }

    $db = new global_class();
    $material_name = null;
    $category = null;

    // Determine which material to check
    if ($product_name === 'Piña Seda') {
        $material_name = 'Knotted Bastos';
        $required_amount = 15 * floatval($weight) * intval($quantity);
        $second_material_name = 'Warped Silk';
        $second_required_amount = 7 * floatval($weight) * intval($quantity);
    } elseif ($product_name === 'Pure Piña Cloth') {
        $material_name = 'Knotted Liniwan';
        $required_amount = 22 * floatval($weight) * intval($quantity);
        $second_material_name = null;
        $second_required_amount = 0;
    } else {
        throw new Exception('This product does not require processed material check');
    }

    // Query inventory for the first processed material
    $query = "SELECT weight as available_qty FROM processed_materials WHERE processed_materials_name = ? AND status = 'Available'";
    $stmt = $db->conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $db->conn->error);
    }
    $stmt->bind_param("s", $material_name);
    if (!$stmt->execute()) {
        throw new Exception("Failed to check inventory: " . $stmt->error);
    }
    $stmt->bind_result($available_qty);
    $stmt->fetch();
    $stmt->close();

    $available_qty = $available_qty ?: 0;
    if ($available_qty < $required_amount) {
        throw new Exception("Not enough {$material_name} in inventory. Required: {$required_amount}g, Available: {$available_qty}g");
    }

    // Check second material if needed (for Piña Seda)
    if ($second_material_name !== null) {
        $query = "SELECT weight as available_qty FROM processed_materials WHERE processed_materials_name = ? AND status = 'Available'";
        $stmt = $db->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: " . $db->conn->error);
        }
        $stmt->bind_param("s", $second_material_name);
        if (!$stmt->execute()) {
            throw new Exception("Failed to check inventory: " . $stmt->error);
        }
        $stmt->bind_result($second_available_qty);
        $stmt->fetch();
        $stmt->close();

        $second_available_qty = $second_available_qty ?: 0;
        if ($second_available_qty < $second_required_amount) {
            throw new Exception("Not enough {$second_material_name} in inventory. Required: {$second_required_amount}g, Available: {$second_available_qty}g");
        }
    }

    // If we get here, there's enough inventory
    $response['success'] = true;
    $response['message'] = 'Sufficient inventory available';
    echo json_encode($response);
    return;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    return;
} 