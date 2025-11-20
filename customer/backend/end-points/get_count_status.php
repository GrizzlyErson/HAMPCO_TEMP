<?php
include('../class.php');
$db = new global_class();

session_start();

    $user_id=$_SESSION['customer_id'];

    $orders = $db->getOrderStatusCounts($user_id);

    