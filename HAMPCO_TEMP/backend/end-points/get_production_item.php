<?php
require_once '../dbconnect.php';
require_once '../class.php';

$db = new DB_con();
$conn = $db->conn;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_GET['prod_line_id'])) {
    $response['message'] = 'Production Line ID is required.';
    echo json_encode($response);
    exit();
}

$prod_line_id = mysqli_real_escape_string($conn, $_GET['prod_line_id']);

$query = "SELECT prod_line_id, product_name, length_m, width_m, weight_g, quantity FROM production_line WHERE prod_line_id = '$prod_line_id'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
    $response['success'] = true;
    $response['product'] = $product;
} else {
    $response['message'] = 'Product not found.';
}

echo json_encode($response);
?>