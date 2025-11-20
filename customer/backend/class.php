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
    
    
     

}