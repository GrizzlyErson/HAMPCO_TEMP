<?php
include('../class.php');

$db = new global_class();
$materials = $db->fetch_all_materials();

header('Content-Type: application/json');
echo json_encode($materials->fetch_all());
?>
