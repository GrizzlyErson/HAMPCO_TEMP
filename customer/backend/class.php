<?php
include ('dbconnect.php');
date_default_timezone_set('Asia/Manila');

class global_class extends db_connect
{
    public function __construct()
    {
        $this->connect();
    }





public function getCartlist($userID)
    {
        // Directly insert the userID into the query (no prepared statements)
        $query = "SELECT cart.*,product.*
            FROM `cart`
            LEFT JOIN product ON cart.cart_prod_id = product.prod_id
            WHERE cart.cart_user_id = '$userID'
            GROUP BY cart.cart_id, product.prod_id;
            ";
    
        $result = $this->conn->query($query);
        
        if ($result) {
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = $row;
            }
            return $cartItems;
        }
    }












      public function check_account($user_id ) {
        $user_id  = intval($user_id);
        $query = "SELECT * FROM user_customer WHERE customer_id  = $user_id";
        $result = $this->conn->query($query);
        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }


    

    public function fetch_user_info($userID){
        $query = $this->conn->prepare("SELECT * FROM user_customer where customer_id = '$userID'");
        if ($query->execute()) {
            $result = $query->get_result();
            return $result;
        }
    }
    
    

      public function fetch_all_categories(){
        $query = $this->conn->prepare("SELECT * FROM product_category");

        if ($query->execute()) {
            $result = $query->get_result();
            return $result;
        }
    }
    




 public function AddToCart($userId, $productId)
{
    $userId = mysqli_real_escape_string($this->conn, $userId);
    $productId = mysqli_real_escape_string($this->conn, $productId);

    // Fetch product info
    $productInfoResult = $this->conn->query("SELECT * FROM product WHERE prod_id='$productId'");
    $productInfo = $productInfoResult->fetch_assoc();

    // Fetch cart info
    $cartInfoResult = $this->conn->query("SELECT * FROM cart WHERE cart_user_id='$userId' AND cart_prod_id='$productId'");
    $cartInfo = $cartInfoResult->fetch_assoc();

   
    // Check if cart quantity exceeds product stock
    if (isset($cartInfo['cart_Qty']) && $cartInfo['cart_Qty'] >= $productInfo['prod_stocks']) {
        return "MaximumExceed";
    }
    $checkProductInCart = $this->conn->query("SELECT * FROM cart WHERE cart_user_id='$userId' AND cart_prod_id='$productId'");

    if ($checkProductInCart->num_rows > 0) {
        $query = "UPDATE `cart` SET `cart_Qty` = `cart_Qty` + 1 WHERE `cart_user_id` = '$userId' AND `cart_prod_id` = '$productId'";
        $response = 'Cart Updated!';
    } else {
        $query = "INSERT INTO `cart` (`cart_prod_id`, `cart_Qty`, `cart_user_id`) VALUES ('$productId', 1, '$userId')";
        $response = 'Added To Cart!';
    }
    if ($this->conn->query($query)) {
        return $response;
    } else {
        return 400; 
    }
}





 public function getOrderStatusCounts($userID)
    {
        $query = " 
            SELECT 
                (SELECT COUNT(*) FROM `cart` WHERE cart_user_id = $userID) AS cartCount
        ";

        $result = $this->conn->query($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            
            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'Failed to retrieve counts']);
        }
    }





public function IncreaseQty($cart_id)
{
    $stmt = $this->conn->prepare("UPDATE cart SET cart_Qty = cart_Qty + 1 WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        return 'Quantity increased';
    } else {
        return 400;
    }
}

public function DecreaseQty($cart_id)
{
    // Decrease only if quantity > 1 to avoid zero or negative qty
    $stmt = $this->conn->prepare("UPDATE cart SET cart_Qty = cart_Qty - 1 WHERE cart_id = ? AND cart_Qty > 1");
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return 'Quantity decreased';
        } else {
            return 'Minimum quantity reached';
        }
    } else {
        return 400;
    }
}











public function RemoveCart($cart_id)
{
    // Prepare DELETE query to remove the cart item with the given cart_id
    $query = "DELETE FROM `cart` WHERE `cart_id` = '$cart_id'";

    if ($this->conn->query($query)) {
        return 'Item removed from cart!';
    } else {
        return 400; 
    }
}







public function fetch_product_info($product_id){
        $query = $this->conn->prepare("SELECT 
                product.*, 
                product_category.*
            FROM product
            LEFT JOIN product_category
            ON product.prod_category_id = product_category.category_id
        WHERE product.prod_id = $product_id
        "    
    );
        if ($query->execute()) {
            $result = $query->get_result();
            return $result;
        }
    }



    
    


     public function fetch_all_product() {
        $query = $this->conn->prepare("SELECT 
                product.*, 
                product_category.*
            FROM product
            LEFT JOIN product_category
            ON product.prod_category_id = product_category.category_id
            where prod_status='1'
        ");
    
        if ($query->execute()) {
            $result = $query->get_result();
            return $result;
        }
    }

    /**
     * Process checkout for a customer: deduct product stocks and related materials, clear cart.
     * Note: product -> materials mapping comes from `product_materials` table. This implementation
     * deducts material quantities by the ordered product quantity (1 unit per product * qty).
     * If your products require specific material weights per unit, extend `product_materials`
     * to include a `material_qty` column.
     */
    public function ProcessCheckout($userID, $paymentMethod = 'cod', $proofFile = null, $orderDetails = [])
    {
        $this->conn->begin_transaction();
        try {
            $cartItems = $this->getCartlist($userID);
            if (empty($cartItems)) {
                throw new Exception('Cart is empty');
            }

            // compute total
            $totalAmount = 0.0;
            foreach ($cartItems as $ci) {
                $qty = intval($ci['cart_Qty']);
                $price = floatval($ci['prod_price']);
                $totalAmount += $qty * $price;
            }

            // insert order header
            $full_name = isset($orderDetails['full_name']) ? $orderDetails['full_name'] : null;
            $contact_number = isset($orderDetails['contact_number']) ? $orderDetails['contact_number'] : null;
            $delivery_address = isset($orderDetails['delivery_address']) ? $orderDetails['delivery_address'] : null;

            $orderStatus = 'Pending';
            $insOrder = $this->conn->prepare("INSERT INTO orders (order_user_id, full_name, contact_number, delivery_address, payment_method, payment_proof, total_amount, order_status, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $insOrder->bind_param("isssssds", $userID, $full_name, $contact_number, $delivery_address, $paymentMethod, $proofFile, $totalAmount, $orderStatus);
            if (!$insOrder->execute()) {
                throw new Exception('Failed to create order header');
            }
            $orderId = $this->conn->insert_id;
            $insOrder->close();

            foreach ($cartItems as $item) {
                $prod_id = intval($item['prod_id']);
                $qty = intval($item['cart_Qty']);

                // insert order item
                $unit_price = floatval($item['prod_price']);
                $subtotal = $unit_price * $qty;
                $insItem = $this->conn->prepare("INSERT INTO order_items (order_id, prod_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $insItem->bind_param("iiidd", $orderId, $prod_id, $qty, $unit_price, $subtotal);
                if (!$insItem->execute()) {
                    throw new Exception('Failed to insert order item');
                }
                $insItem->close();

                // Lock product row
                $stmt = $this->conn->prepare("SELECT prod_stocks, prod_name FROM product WHERE prod_id = ? FOR UPDATE");
                $stmt->bind_param("i", $prod_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $product = $res->fetch_assoc();
                $stmt->close();

                if (!$product) {
                    throw new Exception('Product not found');
                }

                $current_stock = intval($product['prod_stocks']);
                $product_name = $product['prod_name'];

                if ($current_stock < $qty) {
                    throw new Exception(sprintf('Insufficient stock for %s', $product_name));
                }

                $new_stock = $current_stock - $qty;
                $upd = $this->conn->prepare("UPDATE product SET prod_stocks = ? WHERE prod_id = ?");
                $upd->bind_param("ii", $new_stock, $prod_id);
                if (!$upd->execute()) {
                    throw new Exception('Failed to update product stock');
                }
                $upd->close();

                // Log product stock change into product_stock table if exists
                $change_log = sprintf("%d -> %d", $current_stock, $new_stock);
                $log = $this->conn->prepare("INSERT INTO product_stock (pstock_user_id, pstock_prod_id, pstock_stock_type, pstock_stock_outQty, pstock_stock_changes) VALUES (?, ?, 'Stock Out', ?, ?)");
                $log->bind_param("iiis", $userID, $prod_id, $qty, $change_log);
                $log->execute();
                $log->close();

                // Deduct mapped materials (product_materials)
                $colCheck = $this->conn->query("SHOW COLUMNS FROM product_materials LIKE 'material_qty'");
                $hasMaterialQty = ($colCheck && $colCheck->num_rows > 0);
                if ($hasMaterialQty) {
                    $pm = $this->conn->prepare("SELECT material_type, material_name, material_qty FROM product_materials WHERE product_name = ?");
                } else {
                    $pm = $this->conn->prepare("SELECT material_type, material_name FROM product_materials WHERE product_name = ?");
                }
                $pm->bind_param("s", $product_name);
                $pm->execute();
                $pmRes = $pm->get_result();
                while ($mat = $pmRes->fetch_assoc()) {
                    $mtype = $mat['material_type'];
                    $mname = $mat['material_name'];
                    $perUnit = 1;
                    if ($hasMaterialQty && isset($mat['material_qty'])) {
                        $perUnit = floatval($mat['material_qty']);
                    }

                    if ($mtype === 'raw') {
                        // find raw material id
                        $q = $this->conn->prepare("SELECT id, rm_quantity, raw_materials_name FROM raw_materials WHERE raw_materials_name = ? FOR UPDATE");
                        $q->bind_param("s", $mname);
                        $q->execute();
                        $r = $q->get_result();
                        $raw = $r->fetch_assoc();
                        $q->close();

                        if (!$raw) {
                            throw new Exception('Raw material not found: ' . $mname);
                        }

                        $current_rm_qty = floatval($raw['rm_quantity']);
                        $deduct = $perUnit * floatval($qty);
                        $new_rm_qty = $current_rm_qty - $deduct;
                        if ($new_rm_qty < 0) {
                            throw new Exception('Insufficient raw material: ' . $mname);
                        }

                        $u = $this->conn->prepare("UPDATE raw_materials SET rm_quantity = ? WHERE id = ?");
                        $u->bind_param("di", $new_rm_qty, $raw['id']);
                        $u->execute();
                        $u->close();

                        $change_log = sprintf("%.3f -> %.3f", $current_rm_qty, $new_rm_qty);
                        $insertLog = $this->conn->prepare("INSERT INTO stock_history (stock_raw_id,stock_user_type, stock_type,stock_outQty, stock_changes, stock_user_id) VALUES (?,'Customer', 'Stock Out',?, ?, ?)");
                        $insertLog->bind_param("idsi", $raw['id'], $deduct, $change_log, $userID);
                        $insertLog->execute();
                        $insertLog->close();

                    } else if ($mtype === 'processed') {
                        // processed_materials table
                        $q = $this->conn->prepare("SELECT id, weight FROM processed_materials WHERE processed_materials_name = ? FOR UPDATE");
                        $q->bind_param("s", $mname);
                        $q->execute();
                        $r = $q->get_result();
                        $proc = $r->fetch_assoc();
                        $q->close();

                        if (!$proc) {
                            throw new Exception('Processed material not found: ' . $mname);
                        }

                        $current_weight = floatval($proc['weight']);
                        $deduct = $perUnit * floatval($qty);
                        $new_weight = $current_weight - $deduct;
                        if ($new_weight < 0) {
                            throw new Exception('Insufficient processed material: ' . $mname);
                        }

                        $u = $this->conn->prepare("UPDATE processed_materials SET weight = ? WHERE id = ?");
                        $u->bind_param("di", $new_weight, $proc['id']);
                        $u->execute();
                        $u->close();

                        $change_log = sprintf("%.3f -> %.3f", $current_weight, $new_weight);
                        $insertLog = $this->conn->prepare("INSERT INTO stock_history (stock_raw_id,stock_user_type, stock_type,stock_outQty, stock_changes, stock_user_id, is_processed_material) VALUES (?,'Customer', 'Stock Out',?, ?, ?, 1)");
                        $insertLog->bind_param("idsi", $proc['id'], $deduct, $change_log, $userID);
                        $insertLog->execute();
                        $insertLog->close();
                    }
                }
                $pm->close();
            }

            // Clear cart for user
            $del = $this->conn->prepare("DELETE FROM cart WHERE cart_user_id = ?");
            $del->bind_param("i", $userID);
            $del->execute();
            $del->close();

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Checkout completed'];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('Checkout failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    
     

}