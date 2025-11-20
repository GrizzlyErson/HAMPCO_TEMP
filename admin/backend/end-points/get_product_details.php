<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/class.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/hampco_memberFix/admin/backend/raw_material_calculator.php";

$db = new global_class();
$calculator = new RawMaterialCalculator($db);
$response = array();

try {
    if (!isset($_GET['prod_line_id'])) {
        throw new Exception("Production line ID is required");
    }

    $prod_line_id = $_GET['prod_line_id'];

    // Get product details
    $query = "SELECT * FROM production_line WHERE prod_line_id = ?";
    
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param("i", $prod_line_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Production item not found");
    }

    $row = $result->fetch_assoc();
    $productDetails = array(
        'prod_line_id' => $row['prod_line_id'],
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'length_m' => $row['length_m'],
        'width_m' => $row['width_m'],
        'weight_g' => $row['weight_g']
    );

    // Calculate materials needed
    $materials = $calculator->calculateMaterialsNeeded(
        $productDetails['product_name'],
        $productDetails['quantity'],
        $productDetails['length_m'],
        $productDetails['width_m'],
        $productDetails['weight_g']
    );

    $productDetails['materials'] = $materials;
    $response['success'] = true;
    $response['data'] = $productDetails;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response); 