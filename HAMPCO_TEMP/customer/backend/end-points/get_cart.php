<?php 
include('../class.php');
$db = new global_class();

session_start();
$userId = $_SESSION['customer_id'];
$getCartlist = $db->getCartlist($userId); 


// echo "<pre>";
// print_r($getCartlist);
// echo "</pre>";

echo json_encode($getCartlist);
?>