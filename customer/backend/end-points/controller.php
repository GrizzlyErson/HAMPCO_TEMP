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

        } else if ($_POST['requestType'] == 'Checkout') {
            // validate required fields
            $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
            $delivery_address = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
            $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';

            if ($userId <= 0 || $full_name === '' || $contact_number === '' || $delivery_address === '') {
                echo json_encode(['status' => 'error', 'message' => 'Missing required checkout fields.']);
                exit;
            }

            $proofFilename = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == UPLOAD_ERR_OK) {
                $up = $_FILES['payment_proof'];
                $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
                $destName = 'checkout_proof_' . $userId . '_' . time() . '.' . $ext;
                $destDir = __DIR__ . '/../../../upload/';
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                $destPath = $destDir . $destName;
                if (move_uploaded_file($up['tmp_name'], $destPath)) {
                    $proofFilename = $destName;
                }
            }

            // Call ProcessCheckout with order details
            $response = $db->ProcessCheckout($userId, $paymentMethod, $proofFilename, [
                'full_name' => $full_name,
                'contact_number' => $contact_number,
                'delivery_address' => $delivery_address
            ]);

            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'requestType NOT FOUND']);
        }
    } else {
        echo json_encode(['error' => 'Access Denied! No Request Type.']);
    }
} 
?>
