<?php
include('../class.php');

$db = new global_class();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['requestType'])) {
        if ($_POST['requestType'] == 'AddToCart') {
            $userId = $_POST['cart_user_id'];
            $productId = $_POST['cart_prod_id'];

            $response = $db->AddToCart($userId, $productId);

            echo json_encode(['status' => $response]);

        } else if ($_POST['requestType'] == 'RemoveCart') {
            $cart_id = $_POST['cart_id'];

            $response = $db->RemoveCart($cart_id);

            echo json_encode(['status' => $response]);

        } else if ($_POST['requestType'] == 'IncreaseQty') {
            $cart_id = $_POST['cart_id'];

            $response = $db->IncreaseQty($cart_id); 

            echo json_encode(['status' => $response]);

        } else if ($_POST['requestType'] == 'DecreaseQty') {
            $cart_id = $_POST['cart_id'];

            $response = $db->DecreaseQty($cart_id); 

            echo json_encode(['status' => $response]);

        } else {
            echo json_encode(['error' => 'requestType NOT FOUND']);
        }
    } else {
        echo json_encode(['error' => 'Access Denied! No Request Type.']);
    }
} 
?>
