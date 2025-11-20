<?php
include('../class.php');

$db = new global_class();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['requestType'])) {
        if ($_POST['requestType'] == 'StockOut') {
            session_start();

            $user_id = $_SESSION['id'];
            $raw_id = $_POST['raw_id'];
            $stock_out_qty = $_POST['rm_quantity'];
            $result = $db->StockOut($user_id, $raw_id, $stock_out_qty);
            echo json_encode([
                "status" => $result ? "success" : "error",
                "message" => $result ? "Material updated successfully." : "Update failed."
            ]);
        }
    }
}
?>