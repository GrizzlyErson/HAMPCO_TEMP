<?php
require_once 'class.php';
require_once 'raw_material_calculator.php';
$db = new global_class();
$calculator = new RawMaterialCalculator($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    try {
        // Start transaction
        $db->conn->begin_transaction();
        
        // Validate input data
        if (!isset($_POST['product_name']) || empty($_POST['product_name'])) {
            throw new Exception("Product name is required");
        }
        if (!isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
            throw new Exception("Valid quantity is required");
        }

        // Get and sanitize the form data
        $product_name = trim($_POST['product_name']);
        $quantity = intval($_POST['quantity']);
        
        // Determine if product uses dimensions or weight
        $dimensions_products = ['Piña Seda', 'Pure Piña Cloth'];
        $uses_dimensions = in_array($product_name, $dimensions_products);
        
        // Validate and get measurements based on product type
        if ($uses_dimensions) {
            if (!isset($_POST['length']) || !is_numeric($_POST['length'])) {
                throw new Exception("Valid length is required for this product type");
            }
            if (!isset($_POST['width']) || !is_numeric($_POST['width'])) {
                throw new Exception("Valid width is required for this product type");
            }
            $length = floatval($_POST['length']);
            $width = floatval($_POST['width']);
            // Safeguard: if width is likely in inches, convert to meters
            if ($width > 3) { // unlikely to be >3 meters
                $width = $width * 0.0254;
            }
            $weight = 0; // Default value for dimension-based products
        } else {
            if (!isset($_POST['weight']) || !is_numeric($_POST['weight'])) {
                throw new Exception("Valid weight is required for this product type");
            }
            $weight = floatval($_POST['weight']);
            $length = 0; // Default values for weight-based products
            $width = 0;
        }

        // Validate material availability before proceeding
        $calculator->validateMaterialAvailability($product_name, $quantity, $length, $width, $weight);
        
        // Insert the production item
        $query = "INSERT INTO production_line (
                    product_name, 
                    length_m, 
                    width_m, 
                    weight_g,
                    quantity, 
                    date_created
                ) VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param("sdddi", 
            $product_name,
            $length,
            $width,
            $weight,
            $quantity
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create production item: " . $stmt->error);
        }
        
        $prod_line_id = $stmt->insert_id;
        
        // Calculate and deduct materials
        $result = $calculator->calculateAndDeductMaterials($product_name, $quantity, $length, $width, $weight);
        
        if (!$result['success']) {
            throw new Exception("Failed to deduct materials");
        }
        
        // Commit transaction
        $db->conn->commit();
        
        $response['success'] = true;
        $response['message'] = "Production item created successfully";
        $response['prod_line_id'] = $prod_line_id;
        $response['materials'] = $result['deductions'];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->conn->rollback();
        
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
?> 